<?php

Class Admin extends Db{
	use Check;
	private $log;
	private $search;
	/**
	 *
	 *连接数据库
	 *@param $db 数据库对象
	 */
	public function __construct($db=NULL){
		parent::__construct($db);
		$this->search = new Search($db);
		$this->log = new Log('admin');
	}

	public function itemForm(){
		if(isset($_GET['item'])){
			$itemId = $_GET['item'];
			$item = $this->search->searchItem($itemId,NULL)['0'];
		}else{
			$itemId = NULL;
			$item = NULL;
		}
		$form = <<<EOF
			<div id='itemForm'>
				<form action="/assets/inc/process.php" method="post">
					<fieldset>
						<legend>商品信息</legend>
						<label for="item">商品名称</label>
						<input type="text" name="item" id="item" value="$item[item]" />
						<label for="price">单价</label>
						<input type="text" name="price" id="price" value="$item[price]" />
						<label for="category">种类</label>
						<input type="text" name="category" id="category" value="$item[category]" />
						<label for="quantity">库存</label>
						<input type="text" name="quantity" id="quantity" value="$item[quantity]" />
						<label for="description">详情</label>
						<textarea name="description" id="description">$item[description]</textarea>
						<br>
						<input type="hidden" name="item_id" value="$itemId" />
						<input type="hidden" name="token" value="$_SESSION[token]" />
						<input type="hidden" name="action" value="edit_item" />
						<input type="submit" value="确认" />
					</fieldset>
				</form>
			</div>
EOF;
		if(isset($_GET['item'])){
			$form .= <<<EOF
				<fieldset>
					<legend>删除商品</legend>
					<form action="confirmDelete.php" method="post">
						<input type="submit" value="删除商品" />
						<input type="hidden" name="item_id" value="$itemId" />
					</form>
				</fieldset>
EOF;
			$form .= $this->editPictures($_GET['item']);
		}

		return $form;
	}

	private function editPictures($id){
		$pictures = $this->search->searchPic($id);
		$form = <<<EOF
			<div id="editPic">
				<form action="/assets/inc/process.php" method="post" enctype="multipart/form-data">
						<fieldset>
							<legend>商品图片</legend>
							<input type="file" name="itemPic" id="itemPic" />
							<input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
							<input type="hidden" name="item_id" value="$id" />
							<input type="hidden" name="token" value="$_SESSION[token]" />
							<input type="hidden" name="action" value="add_pic" />
							<input type="submit" value="上传" />
						</fieldset>
				</form>
				<ul>
EOF;
		if(!empty($pictures)){
			foreach($pictures as $picture){
				$form .= <<<EOF
					<li>
						<div>
							<img src="/images/items/$id/$picture">
						</div>
						<p>$picture</p>
						<form action="confirmDelete.php" method="post">
							<input type="submit" value="删除" />
							<input type="hidden" name="item_id" value="$id">
							<input type="hidden" name="picture" value="$picture" />
						</form>
					</li>		
EOF;
			}
		}
		$form .= '<div style="clear:both"></div></ul></div>';
		return $form;
	}

	private function checkItemForm(){
		$exception = ['item_id','description'];
		//验证信息完整
		$result = $this->filledOut($_POST,$exception);
		if($result!=NULL){
			foreach($result as $value){
				switch($value){
					case 'item':
						echo '商品名称不能为空'."<br>";
						break;
					case 'price':
						echo '单价不能为空'."<br>";
						break;
					case 'category':
						echo '种类不能为空'."<br>";
						break;
					case 'quantity':
						echo '库存不能为空'."<br>";
						break;
				}
			}
		return FALSE;
		}
		if((!is_numeric($_POST['price']))||((floor($_POST['quantity']))-($_POST['quantity'])!=0)){
			echo '请输入正确的单价和库存数量';
			return FALSE;
		}
		return TRUE;
	}

	public function editItem(){
		$check = $this->checkItemForm();
		if($check===TRUE){
			if(empty($_POST['item_id'])){
				$sql = 'INSERT INTO
				`items` (`item`,`price`,`category`,`description`,`quantity`)
				VALUES
				(:item,:price,:category,:description,:quantity)';
			}else{
				$id = $_POST['item_id'];
				$sql = "UPDATE
					`items`
					SET
					`item` = :item,
					`price` = :price,
					`category` = :category,
					`description` = :description,
					`quantity` = :quantity
					WHERE 
					`item_id` = $id";
			}		
			try{
				$stmt = $this->db->prepare($sql);
				$stmt->bindParam(':item',$_POST['item'],PDO::PARAM_STR);
				$stmt->bindParam(':price',$_POST['price'],PDO::PARAM_STR);
				$stmt->bindParam(':category',$_POST['category'],PDO::PARAM_STR);
				$stmt->bindParam(':description',$_POST['description'],PDO::PARAM_STR);
				$stmt->bindParam(':quantity',$_POST['quantity'],PDO::PARAM_INT);
				$stmt->execute();
				$itemId = $this->db->lastInsertId();
				$stmt->closeCursor();
				$this->addCategory($_POST['category']);
				return '保存商品成功';	
			}catch(Exception $e){
				$this->log->ERROR($e);
				return;
			}
		}
	}

	public function addCategory($category){
		$result = $this->search->searchCategory(NULL,$category);
		if(!$result){
			$sql = 'INSERT INTO
				`categories`(`category`)
				VALUES
				(:category)';
			try{
				$stmt=$this->db->prepare($sql);
				$stmt->bindParam(':category',$category,PDO::PARAM_STR);
				$stmt->execute();
			}catch(Exception $e){
				$this->log->ERROR($e);
				return;
			}
		}
	}

	private function deleteItem($itemId){
		$sql = 'DELETE FROM
			`items`
			WHERE
			`item_id`=:item_id
			LIMIT 1';
		try{
			$stmt=$this->db->prepare($sql);
			$stmt->bindParam(':item_id',$itemId,PDO::PARAM_INT);
			$stmt->execute();
			$stmt->closeCursor();
			$this->deletePic($itemId);
			header('location:/');
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}

	private function deleteCategory($category){
		$sql = 'DELETE FROM
			`categories`
			WHERE
			`category_id` = :category_id
			LIMIT 1';
		try{
			$stmt=$this->db->prepare($sql);
			$stmt->bindParam(':category_id',$category,PDO::PARAM_INT);
			$stmt->execute();
			$stmt->closeCursor();
			header('Location:/');
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}

	public function confirmDelete($obj,$type){
		if(isset($_POST['sure_delete'])&&$_POST['token']==$_SESSION['token']){
			if($type=='item'){
				$this->deleteItem($obj);
			}
			if($type=='pic'){
				$item = $_POST['item_id'];
				$this->deletePic($item,$obj);
			}
			if($type=='category'){
				$this->deleteCategory($obj);
			}
		}else{
			$item = NULL;
			$picture = NULL;
			$category = NULL;
			if($type=='item'){
				$objName = $this->search->searchItem($obj,NULL)['0']['item'];
				$item = $_POST['item_id'];
			}
			if($type=='pic'){
				$objName = $obj;
				$item = $_POST['item_id'];
				$picture = $obj;
			}
			if($type=='category'){
				$objName = $this->search->searchCategory($obj)['0']['category'];
				$category = $obj;
			}
			$form = <<<EOF
				<form id="delete" action="/admin/confirmDelete.php" method="post">
				<h2>确定删除 $objName?</h2>
				<input type="submit" value="确认删除" />
				/<a href='/admin/item.php?item=$item'>取消</a>
				<input type="hidden" name="sure_delete" value="sure_delete" />
				<input type="hidden" name="category_id" value="$category" />
				<input type="hidden" name="item_id" value="$item" />
				<input type="hidden" name="picture" value="$picture" />
				<input type="hidden" name="token" value=$_SESSION[token] />
			</form>	
EOF;
		return $form;
		}
	}

	private function deletePic($item,$pic=NULL){
		if($pic!=NULL){
			$file = $_SERVER['DOCUMENT_ROOT'].'/images/items/'.$item.'/'.$pic;
			unlink($file);
			header('Location:/admin/item.php?item='.$item);
		}else{
			$dir = $_SERVER['DOCUMENT_ROOT'].'/images/items/'.$item;
			if(is_dir($dir)){
				$handler = opendir($dir);
				while(($filename = readdir($handler))!==FALSE){
					if($filename!='.'&&$filename!='..'){
						$file = $dir.'/'.$filename;
						unlink($file);
					}
				}
				closedir($handler);
				rmdir($dir);
			}		
			return '删除成功';
		}
	}

	public function addItemPic(){
		if((isset($_FILES['itemPic']))&&(($_FILES['itemPic']['type'] == 'image/gif')
			||($_FILES['itemPic']['type'] == 'image/jpeg')
			||($_FILES['itemPic']['type'] == 'image/pjpeg')
			&&($_FILES['itemPic']['size'] < 20000000))){
			if($_FILES['itemPic']['error'] > 0){
				switch($_FILES['itemPic']['error']){
					case 1: return '文件不能超过10MB';
					case 2: return '文件不能超过10MB';
					case 3: return '文件没有完全上传';
					case 4: return '没有上传文件';
					case 6: return '文件上传失败';
					case 7: return '文件上传失败';
				}
			}else{
				$itemId = $_POST['item_id'];
				$dir = $_SERVER['DOCUMENT_ROOT'].'/images/items/'.$itemId;
				if(!is_dir($dir)){
					mkdir($dir,0777,TRUE);
				}
				$filename = $itemId.date("Ymdhis");
				move_uploaded_file($_FILES['itemPic']['tmp_name'],$dir.'/'.$filename.'.jpg');
				return 'TRUE?item='.$itemId;
			}
		}else{
			return '请上传png/jpeg/pjpeg格式图片（20M以内）';
		}
	}

	public function showOrderList($condition=NULL){
		$this->checkOrder();
		$result = $this->search->searchOrder(NULL,$condition);
		$form = <<<EOF
			<form action="/admin/orders.php" method="post" id='condition'>
				<select name='condition' form='condition'>
					<option value=''>全部订单</option>
					<option value='订单未支付'>订单未支付</option>
					<option value='支付成功'>支付成功</option>
					<option value='订单已关闭'>订单已关闭</option>
				</select>
				<input type='submit' value='查询'>
			</form>
			<table id='order_list'>
				<tr>
					<th>订单</th>
					<th>用户名</th>
					<th>订单状态</th>
				</tr>
EOF;
		foreach($result as $order){
			$customerId = $this->search->searchAddress($order['address_id'])[0]['customer_id'];
			$userName = $this->search->searchCustomer($customerId)['username'];
			$form .= <<<EOF
				<tr>
					<td><a href='/orderItem.php?id=$order[order_id]'>$order[order_id]</a></td>
					<td>$userName</td>
					<td>$order[condition]</td>
				</tr>
EOF;
		}
		$form .= '</table>';		
		return $form;
	}

	public function checkOrder(){
		$orders = $this->search->searchOrder();
		try{
			foreach($orders as $order){
				$id = $order['order_id'];
				$pay = new Pay();
				$result = $pay->queryOrder($id);
				if($result['result_code']=='FAIL'){
					$this->deleteOrder($id);
				}else{
					$this->setPurchase($id,$result['trade_state_desc']);
				}
			}
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}

	private function setPurchase($id,$condition){
		$sql = 'UPDATE
			`orders`
			SET
			`condition`=:condition
			WHERE
			`order_id`=:id';
		try{
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':condition',$condition,PDO::PARAM_STR);
			$stmt->bindParam('id',$id,PDO::PARAM_INT);
			$stmt->execute();
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}

	private function deleteOrder($id){
		$sql = 'DELETE FROM
			`orders`
			WHERE
			`order_id`=:id
			LIMIT 1';
		try{
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id',$id,PDO::PARAM_INT);
			$stmt->execute();
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}
}
