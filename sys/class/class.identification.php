<?php
/**
 * 账户操作
 */
class Identification extends Db{
	use Check;
	private $log;
	//盐长度
	private $saltLength = 7;
	
	private $username;
	private $password;
	private $password1;
	private $email;

	//连接数据库
	public function __construct($db=NULL){
		$this->log = new Log('identification');
		parent::__construct($db);
		$this->username = isset($_POST['username']) ? htmlentities($_POST['username'],ENT_QUOTES) : NULL;
		$this->password = isset($_POST['password']) ? htmlentities($_POST['password'],ENT_QUOTES) : NULL;
		$this->password1 = isset($_POST['password1']) ? htmlentities($_POST['password1'],ENT_QUOTES) : NULL;
		$this->email = isset($_POST['email']) ? trim($_POST['email']) : NULL;
	}

	//注册
	public function register(){
		//验证信息完整
		$result = $this->filledOut($_POST);
		if($result!=NULL){
			foreach($result as $value){
				switch($value){
					case 'username':
						echo '用户不能为空'."<br>";
						break;
					case 'password':
						echo '密码不能为空'."<br>";
						break;
					case 'password1':
						echo '请再次输入密码'."<br>";
						break;
					case 'email':
						echo '邮箱不能为空'."<br>";
						break;
				}
			}
			return;
		}

		//邮箱激活信息
		$regtime = time();
		$token = md5($this->username.$this->password.$regtime);
		$token_exptime = $regtime+60*60*24;

		//检查邮箱格式，两次密码是否一致
		if($this->validMail($this->email)){
			return $this->validMail($this->email);
		}
		if($this->validPassword($this->password,$this->password1)){
			return $this->validPassword($this->password,$this->password1);
		}
		//检查用户名重复
		if($this->checkUsername($this->username)){
			return '用户名已存在，请注册其他用户名';
		}

		//检查邮箱重复
		if($this->checkEmail($this->email)){
			return '邮箱已注册，使用其他邮箱注册';
		}

		//密码加盐
		$hash = $this->getSaltedHash($this->password);

		//保存信息到数据库
		$sql = 'INSERT INTO
			`customers` (`username`,`password`,`email`,`token`,`token_exptime`)
			VALUES
			(:username,:password,:email,:token,:token_exptime)';

		try{
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':username',$this->username,PDO::PARAM_STR);
			$stmt->bindParam(':password',$hash,PDO::PARAM_STR);
			$stmt->bindParam(':email',$this->email,PDO::PARAM_STR);
			$stmt->bindParam(':token',$token,PDO::PARAM_STR);
			$stmt->bindParam(':token_exptime',$token_exptime,PDO::PARAM_STR);
			$stmt->execute();
			$stmt->closeCursor();

			//邮件内容
			$subject = '注册激活连接';
			$content = '亲爱的'.$this->username.'：<br/>请点击链接激活您的帐号。<br/><a href="http://'.$_SERVER['SERVER_NAME'].'/identification/active.php?verify='.$token.'" target="_blank">http://'.$_SERVER['SERVER_NAME'].'/identification/active.php?verify='.$token.'</a><br/>如果以上链接无法点击，请将它复制到你的浏览器地址栏中进入访问，该链接24小时内有效。';
			$type = 'text/html';

			//发送邮件
			new Mail($this->email,$subject,$content,$type);
			return '注册成功，请打开邮箱连接激活账号';
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}

	//注册邮箱验证
	public function verify($verify){
		$result = $this->checkToken($verify);
		if($result){
			if($result['confirm']==1){
				return '账号已激活，请登陆';
			}
			if(time() > $result['token_exptime']){
				return '激活连接已经过期，请登陆账号重新发送激活邮件';
			}

			$sql = "UPDATE
				`customers`
				SET
				`confirm` = 1,
				`token` = '',
				`token_exptime` = ''
				WHERE
				`userid` = :userid";
			try{
				$stmt = $this->db->prepare($sql);
				$stmt->bindParam(':userid',$result['userid'],PDO::PARAM_INT);
				$stmt->execute();
				$stmt->closeCursor();
				return '账号激活成功';
			}catch(Exception $e){
				$this->log->ERROR($e);
				return;
			}
		}else{
			return '激活连接错误，请登陆账号重新发送激活邮件';
		}
	}

	//登陆
	public function login(){
		//检查用户存在
		$result = $this->checkUsername($this->username);
		if(!$result){
			return '该用户名不存在';
		}
		if($result['confirm']!=1){
			return '账号尚未激活，请点击邮箱验证激活连接激活账号';
		}
		//加盐密码
		$hash = $this->getSaltedHash($this->password,$result['password']);
		if($result['password']==$hash){
			$_SESSION['user'] = array(
				'id' => $result['userid'],
				'name' => $result['username']
			);
			return 'TRUE';
		}else{
			return '用户名或密码错误';
		}
	}

	//注销
	public function logout(){
		if(isset($_SESSION['user'])){
			unset($_SESSION['user']);			
		}
		if(isset($_SESSION['admin'])){
			unset($_SESSION['admin']);
		}
		return 'TRUE';
	}
	
	//发送更改密码连接
	public function forgetPassword(){
		if($this->email!=NULL){
			$result = $this->checkEmail($this->email);
		}else{
			return "请输入邮箱";
		}	
		if(!$result){
			return '该邮箱没有注册';
		}
		//邮件内容
		$time = time();
		$token = md5($this->email.$time);
		$token_exptime = $time+60*60*24;

		$sql = "UPDATE
			`customers`
			SET
			`token` = :token,
			`token_exptime` = :token_exptime
			WHERE
			`userid` = :userid";
		try{
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam('token',$token,PDO::PARAM_STR);
			$stmt->bindParam('token_exptime',$token_exptime,PDO::PARAM_STR);
			$stmt->bindParam('userid',$result['userid'],PDO::PARAM_INT);
			$stmt->execute();
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}

		//邮件信息
		$subject = '更改密码连接';
		$content = '亲爱的'.$result['username'].'：<br/>请点击链接更改你的密码。<br/><a href="http://'.$_SERVER['SERVER_NAME'].'/identification/reset.php?verify='.$token.'" target="_blank">http://'.$_SERVER['SERVER_NAME'].'/identification/reset.php?verify='.$token.'</a><br/>如果以上链接无法点击，请将它复制到你的浏览器地址栏中进入访问，该链接24小时内有效。';
		$type = 'text/html';

		//发送邮件
		new Mail($this->email,$subject,$content,$type);

		return '已发送密码更改连接，请在邮箱内查看';
	}

	//更改密码
	public function changePassword(){
		if($this->validPassword($this->password,$this->password1)){
			return $this->validPassword($this->password,$this->password1);
		}

		//密码加盐
		$hash = $this->getSaltedHash($this->password);
		$sql = "UPDATE
			`customers`
			SET
			`password` = :password,
			`token` = '',
			`token_exptime` = ''
			WHERE
			`username` = :username";
		try{
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':password',$hash,PDO::PARAM_STR);
			$stmt->bindParam(':username',$this->username,PDO::PARAM_STR);
			$stmt->execute();
			return '密码更改成功';
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}
	
	//密码加盐
	public function getSaltedHash($string,$salt=NULL){
		//没有盐则生成盐
		if($salt==NULL){
			$salt = substr(md5(time()), 0, $this->saltLength);
		}else{
			$salt = substr($salt, 0, $this->saltLength);
		}
		return $salt.sha1($salt.$string);
	}

	//检查token
	public function checkToken($verify){
		$sql = 'SELECT
			*
			FROM
			`customers`
			WHERE
			`token` = :token
			LIMIT 1';
		try{
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':token',$verify,PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch();
			$stmt->closeCursor();
			return $result;
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}

	//检查用户名存在
	private function checkUsername($username,$admin=FALSE){
		$sql = 'SELECT
			*';
		if($admin==TRUE){
			$sql .= 'FROM `admin`';
		}else{
			$sql .= 'FROM `customers`';
		}
		$sql .= 'WHERE
			`username` = :username
			LIMIT 1';
		try{
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':username',$username,PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch();
			$stmt->closeCursor();
			return $result;
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}

	//检查邮箱存在
	private function checkEmail($email){
		$sql = 'SELECT
			*
			FROM `customers` 
			WHERE
			`email` = :email
			LIMIT 1';
		try{
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':email',$email,PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch();
			$stmt->closeCursor();
			return $result;
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}

	//验证邮箱
	private function validMail($email){
		$pattern = '/^.+@(.+\.)+[A-Za-z]+$/';
		if(!preg_match($pattern, $email)){
			return '邮箱格式错误';
		}
	}

	//验证密码
	private function validPassword($password,$password1){
		if($password != $password1){
			return '输入密码不一致';
		}
	}

	public function adminLogin(){
		$result = $this->checkUsername($this->username,TRUE);
		if($result){
			$hash = $this->getSaltedHash($this->password,$result['password']);
			if($result['password']==$hash){
				$_SESSION['admin'] = $result['admin_id'];
				return 'TRUE';
			}
		}else{
			return '用户不存在';
		}
	}
}
?>