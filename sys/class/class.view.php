<?php
Class View extends Db{
	use Check;
	private $search;
	private $log;
	/**
	 *连接数据库
	 *@param $db 数据库对象
	 */
	public function __construct($db=NULL){
		parent::__construct($db);
		$this->search = new Search($db);
		$this->log = new Log('view');
	}

	public function headerInfo(){
		if((!isset($_SESSION['user']))&&(!isset($_SESSION['admin']))){
			$header = $this->displayButton('identification/login','登陆');
		}else{
			if(isset($_SESSION['user'])){
				$header = $this->displayButton('cart','购物车');
			}else{
				$header = $this->displayButton('admin/orders','查询订单');
				$header .= $this->displayButton('admin/item','添加商品');
			}
			$header .= <<<EOF
      			<form action="/assets/inc/process.php" method="post">
   					<input type="submit" value="注销" />
					<input type="hidden" name="token" value="$_SESSION[token]" />
					<input type="hidden" name="action" value="user_logout" />
				</form>
EOF;
		}
	return $header;
	}
		
	/**
	 * 添加a连接
	 * @param  [text] $button 连接的页面
	 * @param  [text] $alt    图片alt
	 * @return [html]         html标签
	 */
	public function displayButton($link,$alt){
		$num = substr_count($link,'/');
		if($num){
			$button = explode('/',$link)[$num];
		}else{
			$button = $link;
		}	
  		return "<a href='/$link.php'><img src='/images/$button.png' alt='$alt' title='$alt'></a>";
	}

	/**
	 * 显示商品类别列表
	 * @return [html]
	 */
	public function showCategories(){
		$categories = $this->search->searchCategory();
		$list = "<ul>";
		foreach($categories as $category){
			$list .= '<a href="index.php?category='.$category['category'].'"><li>'.$category['category'];
			if(isset($_SESSION['admin'])){
				$list .= <<<EOF
					<form action="/admin/confirmDelete.php" method="post">
						<input type="submit" value="删除" />
						<input type="hidden" name="category_id" value="$category[category_id]">
					</form>
EOF;
			}
			$list .= '</li></a>';
		}
		return $list.'</ul>';
	}

	//显示商品
	public function showItem($item_id,$category,$num=8,$showPage=TRUE){
		//搜索商品
		$items = $this->search->searchItem($item_id,$category);
		
		//输出页面内容
		$list = '';
		if(!empty($category)){
		$list .= '<h3>类别：'.$category.'</h3>';
		}

		//显示选定商品
		if(!empty($item_id)){
			$item = $items['0'];
			$list .= $this->showItemById($item);
			
		//显示类别商品
		}else{
			$list .= $this->showItemByCategory($items,$category,$num,$showPage);
		}
	return $list;
	}

	private function showRandomPic($itemId){
		$pictures = $this->search->searchPic($itemId);
		if(is_array($pictures)){
			$random_key = array_rand($pictures,1);
			$picture = $pictures[$random_key];
		}else{
			$picture = '';
		}
		return $picture;
	}

	private function showItemById($item){
		$picture = $this->showRandomPic($item['item_id']);
		$list = <<<EOF
			<div id="item_pick">
				<div class="item_pic">
					<img src="/images/items/$item[item_id]/$picture" alt="$item[item]">
				</div>
				<div id="select">
					<h3>$item[item]</h3>
EOF;
		if(isset($_SESSION['admin'])){
			$list .= <<<EOF
				<a href='/admin/item.php?item=$item[item_id]'>编辑</a>
EOF;
		}
		$list .= <<<EOF
			<h4>价格 $item[price]</h4>
				<form action="/assets/inc/process.php" method="post">
					<label for="buy_quantity">数量</label>
					<input type="text" name="buy_quantity" id="buy_quantity">
					<p>库存数量 $item[quantity]</p>
					<input type="hidden" name="item" value="$item[item_id]">
					<input type="hidden" name="token" value=$_SESSION[token] />
					<input type="hidden" name="action" value="add_cart" />
					<input type="submit" name="submit" value="加入购物车" />
				</form>
			</div>
			<div id="recommend">
				{$this->showItem(NULL,$item['category'],4,FALSE)}
			</div>
			<div style="clear:both"></div>
		</div>
EOF;
		if($item['description']){
			$list .= "<h4>商品详情</h4><div id='description'>$item[description]</div>";		
		}
		$list .= <<<EOF
			<h4>评论</h4>
			<div id="comment">
				{$this->comment($item['item_id'])}
			</div>
EOF;
	return $list;
	}

	private function showItemByCategory($items,$category,$num=8,$showPage=TRUE){
		//当前页
		$cpage = isset($_GET['page']) ? $_GET['page'] : 1;
		//返回数据位置
		$offset = ($cpage-1)*$num;
		$totalNum = count($items);
		$totalPage = ceil($totalNum/$num);
		$list = '<div id="itemForm"><ul>';
		for($i=$offset;$i<($cpage*$num)&&$i<$totalNum;$i++){
			$item = $items[$i];
			$pic = $this->search->searchPic($item['item_id']);
			$list .= <<<EOF
				<li>
					<div>
						<a href="/index.php?item=$item[item_id]">
							<div class="item_pic"><img src="/images/items/$item[item_id]/$pic[0]" alt="$item[item]"></div>
						</a>
						<a href="/index.php?item=$item[item_id]">
							<div>$item[item]</div>
						</a>
					</div>
				</li>
EOF;
		}
		$list .= '</ul></div>';
		if($showPage){
			$list .= '<div id="page"><ul>';
			for($i=1;$i<=$totalPage;$i++){
				$list .= '<li><a href="/index.php?category='.$category.'&page='.$i.'">'.$i.'</a></li>';
			}
			$list .= '</ul></div><div style="clear:both"></div>';
		}
		return $list;
	}

	private function comment($item_id,$num=5){
		//当前页
		$cpage = isset($_GET['commentPage']) ? $_GET['commentPage'] : 1;
		//返回数据位置
		$offset = ($cpage-1)*$num;
		//查询评论总条数
		$results = $this->search->searchComments($item_id);
		$totalNum = count($results);
		$totalPage = ceil($totalNum/$num);
		$list = '当前没有评论';
		if($cpage<=$totalPage){
			$list = '<div><ul>';
			for($i=$offset;$i<($cpage*$num)&&$i<$totalNum;$i++){
				$result = $results[$i];
				$list .= <<<EOF
					<li>
						$result[comment_id]  $result[username]<hr>$result[comment]
					</li>
EOF;
			}
			$list .= '</ul></div><div id="page"><ul>';
			for($i=1;$i<=$totalPage;$i++){
				$list .= '<li><a href="/index.php?item='.$item_id.'&commentPage='.$i.'">'.$i.'</a></li>';
			}
			$list .= '</ul></div><div style="clear:both"></div>';
		}
		return $list;
	}

	//加入购物车
	public function addCart(){
		if((is_numeric($_POST['buy_quantity']))&&($_POST>0)){
			$item = $_POST['item'];
			if(isset($_SESSION['cart'][$item])){
				$_SESSION['cart'][$item] += $_POST['buy_quantity'];
			}else{
				$_SESSION['cart'][$item] = $_POST['buy_quantity'];
			}
			$this->sessionPrice($_SESSION['cart']);
			return '加入购物车成功';
		}else{
			return '数量错误';
		}
	}

	//
	public function displayCart($cart,$form=false){
		//表头
		$list = '';
		if($form == true){
			$list .= '<form action="/assets/inc/process.php" method="post">';
		}
		$list .= <<<EOF
			<table id='cart'>
				<tr>
					<th></th>
					<th>商品</th>
					<th>数量</th>
					<th>价格</th>
					<th>合计</th>
				</tr>
EOF;
		//表内容
		$total = 0;
		foreach($cart as $itemId => $quantity){
			//查询商品信息
			$itemArr = $this->search->searchItem($itemId,NULL);
			$item = $itemArr['0'];
			$itemTotal = $item['price']*$quantity;
			$total += $itemTotal;
			$picture = $this->showRandomPic($itemId);
			$list .= <<<EOF
				<tr>
					<td>
						<a href="/index.php?item=$item[item_id]">
							<img src="/images/items/$itemId/$picture" alt="$item[item]">
						</a>
					</td>
					<td>
						<a href="/index.php?item=$item[item_id]">$item[item]</a>
					</td>
EOF;
			if($form == true){
				$list .= "<td><input type='text' id=$item[item_id] name=$item[item_id] value=$quantity /></td>";
			}else{
				$list .= "<td>$quantity</td>";	
			}
			$list .= <<<EOF
				<td>$item[price]</td>
				<td>$itemTotal</td>
			</tr>
EOF;
		}
		$list .= <<<EOF
			<tr>
				<td colspan=5>商品总价$total</td>
			</tr>
		</table>
EOF;
		if($form == true){
			$list .= <<<EOF
				<input type="hidden" name="token" value=$_SESSION[token] />
				<input type="hidden" name="action" value="save_cart" />
				<input type="submit" value="保存更改" />
			</form>
EOF;
		}
		return $list;
	}

	//
	public function saveCart(){
		foreach($_SESSION['cart'] as $item => $quantity){
			if(is_numeric($_POST[$item])){
				if($_POST[$item]==0){
					unset($_SESSION['cart'][$item]);
				}else{
					$_SESSION['cart'][$item] = $_POST[$item];
				}				
			}else{
				return '数量错误';
			}		
		}
		$this->sessionPrice($_SESSION['cart']);
		return '保存成功';
	}

	//保存购物车商品总价到SESSION
	public function sessionPrice($cart){
		$total_price = 0;
		if(is_array($cart)){
			foreach ($cart as $cart_item => $quantity) {
				$item = $this->search->searchItem($cart_item,NULL);
				$price = $item['0']['price'];
				$total_price += $price*$quantity;
			}
		}
		$_SESSION['total_price'] = $total_price;
	}

	public function showAddress($id=NULL){
		$results = $this->search->searchAddress($id);
		if($id == NULL){
			$list = <<<EOF
				<form action='/assets/inc/process.php' method='post'>
					<a href="/address.php">新增地址</a>
					<ul>
EOF;
			foreach($results as $address){
				$list .= <<<EOF
					<li>
						<label for="$address[address_id]" />
						<input type='radio' name='address' value="$address[address_id]" />
						$address[address] ($address[name]收) $address[phone]
						<a href='/address.php?address=$address[address_id]'>更改</a>
					</li>	
EOF;
			}
			$list .= <<<EOF
				</ul>		
				<input type="hidden" name="token" value=$_SESSION[token] />
				<input type="hidden" name="action" value="save_order" />
				<input type='submit' value='支付'>
		</form>
		<div style="clear:both"></div>
EOF;
		}else{
			$address = $results[0];
			$list = <<<EOF
				<div id='order_address'>$address[address] ($address[name]收) $address[phone]</div>
EOF;
		}
		return $list;
	}

	//
	public function addressForm($address_id=NULL){
		$addressId = '';
		$division = '';
		$address = '';
		$name = '';
		$phone = '';
		if($address_id != NULL){
			$result = $this->search->searchAddress($address_id);
			//如果地址客户id不等于SESSION客户id则返回
			if($result['0']['customer_id'] != $_SESSION['user']['id']){
				return '数据错误';
			}
			$addressId = $result['0']['address_id'];
			$division = explode(' ',$result['0']['address'])['0'];
			$address = explode(' ',$result['0']['address'])['1'];
			$name = $result['0']['name'];
			$phone = $result['0']['phone'];
		}
		
		$form = <<<EOF
			<form action="/assets/inc/process.php" method="post">		
				<legend>收货地址</legend>
				<label for="division">地址信息</label>
				<input type="text" name="division" id="division" value="$division" />
				<label for="address">详细地址</label>
				<input type="text" name="address" id="address" value="$address" />
				<label for="name">收货人</label>
				<input type="text" name="name" id="name" value="$name" />
				<label for="phone">联系号码</label>
				<input type="text" name="phone" id="phone" value="$phone" />
				<input type="hidden" name="token" value=$_SESSION[token] />
				<input type="hidden" name="addressId" value="$addressId" />
				<input type="hidden" name="action" value="add_address" />
				<input type="submit" value="保存地址" />			
			</form>
EOF;
		if($addressId!=''){
			$form .= <<<EOF
				<form action="/assets/inc/process.php" method="post">	
					<input type="hidden" name="token" value=$_SESSION[token] />
					<input type="hidden" name="addressId" value="$addressId" />
					<input type="hidden" name="action" value="delete_address" />
					<input type="submit" value="删除地址" />		
				</form>
EOF;
		}
		$form .= '<div style="clear:both"></div>';
	return $form;
	}

	//新增地址
	public function addAddress(){
		//验证信息完整
		$exceptions = ['addressId'];
		$result = $this->filledOut($_POST,$exceptions);
		if($result!=NULL){
			foreach($result as $value){
				switch($value){
					case 'divsion':
						echo '地址信息不能为空'."<br>";
						break;
					case 'address':
						echo '详细地址不能为空'."<br>";
						break;
					case 'name':
						echo '收货人不能为空'."<br>";
						break;
					case 'phone':
						echo '联系号码不能为空'."<br>";
						break;
				}
			}
			return;
		}
		$address = $_POST['division'].' '.$_POST['address'];
		if($_POST['addressId']==''){
			$sql = 'INSERT INTO
				`address` (`customer_id`,`name`,`address`,`phone`)
				VALUES
				(:customer_id,:name,:address,:phone)';
		}else{
			$id = $_POST['addressId'];
			$sql = "UPDATE
				`address`
				SET
				`customer_id` = :customer_id,
				`name` = :name,
				`address` = :address,
				`phone` = :phone
				WHERE
				`address_id` = $id";
		}
		try{
			$stmt = $this->db->prepare($sql);	
			$stmt->bindParam(':customer_id',$_SESSION['user']['id'],PDO::PARAM_INT);
			$stmt->bindParam(':name',$_POST['name'],PDO::PARAM_STR);
			$stmt->bindParam(':address',$address,PDO::PARAM_STR);
			$stmt->bindParam(':phone',$_POST['phone'],PDO::PARAM_INT);
			$stmt->execute();
			$stmt->closeCursor();
			return 'TRUE';
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}

	//删除地址
	public function deleteAddress(){
		$id = $_POST['addressId'];
		$sql = "DELETE FROM
			`address`
			WHERE
			`address_id` = $id
			LIMIT 1";
		try{
			$stmt = $this->db->prepare($sql);
			$stmt->execute();
			$stmt->closeCursor();
			return 'TRUE';
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}

	//保存订单信息
	public function saveOrder(){
		if(!isset($_POST['address'])){
			return  '请选择地址';
		}
		//保存订单地址总价
		$sql = 'INSERT INTO
			`orders` (`address_id`,`total_price`)
			VALUES
			(:address_id,:total_price)';
		try{
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':address_id',$_POST['address'],PDO::PARAM_INT);
			$stmt->bindParam(':total_price',$_SESSION['total_price'],PDO::PARAM_INT);
			$stmt->execute();
			$orderId = $this->db->lastInsertId();
			$stmt->closeCursor();
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}

		//保存订单商品信息
		$sql = 'INSERT INTO
			`order_items` (`order_id`,`item`,`quantity`,`total_price`)
			VALUES
			(:order_id,:item,:quantity,:total_price)';
		foreach($_SESSION['cart'] as $item => $quantity){
			$price = $this->search->searchItem($item,'')['0']['price'];
			$total_price = $price*$quantity;
			try{
				$stmt = $this->db->prepare($sql);
				$stmt->bindParam(':order_id',$orderId,PDO::PARAM_INT);	
				$stmt->bindParam(':item',$item,PDO::PARAM_INT);
				$stmt->bindParam(':quantity',$quantity,PDO::PARAM_INT);
				$stmt->bindParam(':total_price',$total_price,PDO::PARAM_INT);
				$stmt->execute();
				$stmt->closeCursor();
			}catch(Exception $e){
				$this->log->ERROR($e);
				return;
			}
		}
		return "TRUE?id=$orderId";
	}

	//生成支付二维码
	public function creatQr($id){
		$result = $this->search->searchOrder($id)[0];
		$total_price = $result['total_price']*100;
		try{
			$pay = new Pay();
			$url = $pay->getPayUrl($id,$total_price);
			new creatQrCode($url,$id);
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}	
	}

	public function showOrder($id){
		$list = '';
		if(!isset($_SESSION['admin'])){
			$this->creatQR($id);
			$list .= <<<EOF
				<div id='QRCode'>
					<h5>订单已提交，2小时内支付订单有效，请使用微信支付</h5>
					<img src='/images/QR/$id.png'>			
				</div>
EOF;
		}
		$items = $this->search->searchOrderItem($id);
		$list .= <<<EOF
			<div id='order_info'>
				<table id='order'>
					<tr>
						<th></th>
						<th>商品</th>
						<th>数量</th>
						<th>价格</th>
						<th>合计</th>
					</tr>
EOF;
		$amount = 0;
		foreach($items as $item){
			$itemInfo = $this->search->searchItem($item['item'],'')['0'];
			$itemName = $itemInfo['item'];
			$itemId = $itemInfo['item_id'];
			$total_price = $item['total_price'];
			$quantity = $item['quantity'];
			$price = $total_price/$quantity;
			$amount += $total_price;
			$picture = $this->showRandomPic($itemId);
			$list .= <<<EOF
				<tr>
					<td><img src='/images/items/$itemId/$picture'></td>
					<td>$itemName</td>
					<td>$quantity</td>
					<td>$price</td>
					<td>$total_price</td>
				</tr>
EOF;
		}
		$list .= <<<EOF
			<tr>
				<td cowspan=5>商品总价$amount</td>
			</tr>
		</table>
	</div>
EOF;
		$address_id = $this->search->searchOrder($id)[0]['address_id'];
		$list .= $this->showAddress($address_id);
		return $list;
	}
}