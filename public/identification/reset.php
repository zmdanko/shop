<?php
/**
 * 邮箱验证页面
 */
if(isset($_GET['verify'])){
	$verify = trim($_GET['verify']);
	if(empty($verify)){
		header('Location:/');
		exit;
	}
}else{
	header("Location:/");
	exit;
}

//包含初始化文件
include_once $_SERVER['DOCUMENT_ROOT'].'/../sys/core/init.inc.php';
$page_title = '更改密码';
include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/header.php';

$identification = new Identification($db);
$result = $identification->checkToken($verify);
if(!$result){
	die('连接错误');
}else{
	if(time() > $result['token_exptime']){
		die('激活连接已经过期，请重新发送激活邮件');
	}
}
?>

<div id="content">
	<form action="/assets/inc/process.php" method="post">
		<fieldset>
			<legend>更改密码</legend>
			<label for="password">新密码</label>
			<input type="password" name="password" id="password" />
			<label for="password1">再次输入密码</label>
			<input type="password" name="password1" id="password1" />
			<input type="hidden" name="username" value="<?php echo $result['username']; ?>" />
			<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
			<input type="hidden" name="action" value="reset_password" />
			<input type="submit" name="reset_submit" value="确定" />
		</fieldset>
	</form>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/footer.php'; ?>