<?php
class Search extends Db{
	private $log;

	public function __construct($db=NULL){	
		parent::__construct($db);
		$this->log = new Log('search');
	}

	/**
	 * 从数据库搜索商品
	 * @param  [int] $item_id  物品id
	 * @param  [int] $category 类别id
	 * @return [arr]           输入物品id返回物品信息，输入类别id返回类别内的物品
	 */
	public function searchItem($item_id,$category){
		$sql = 'SELECT
			*
			FROM
			`items` ';
		if(!empty($item_id)){
			$sql .= 'WHERE `item_id` = :item_id LIMIT 1 ';
		}else{
			if(!empty($category)){
				$sql .= 'WHERE `category` = :category ';
			}
			$sql .= 'ORDER BY `sell` DESC';
		}
		try{
			$stmt = $this->db->prepare($sql);
			if(!empty($item_id)){
				$stmt->bindParam(':item_id',$item_id,PDO::PARAM_INT);
			}
			if(!empty($category)){
				$stmt->bindParam(':category',$category,PDO::PARAM_STR);
			}
			$stmt->execute();
			$items = $stmt->fetchALL(PDO::FETCH_ASSOC);
			$stmt->closeCursor();
			return $items;
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}

	public function searchPic($itemId){
		$dir = $_SERVER['DOCUMENT_ROOT'].'/images/items/'.$itemId;
		if(is_dir($dir)){
			$handler = opendir($dir);
			while(($filename = readdir($handler))!==FALSE){
				if($filename!='.'&&$filename!='..'){
					$files[] = $filename;
				}
			}
			closedir($handler);
			if(isset($files)){
				return $files;
			}
		}		
	}

	public function searchCategory($id=NULL,$category=NULL){
		$sql = 'SELECT
			*
			FROM
			`categories` ';
		if($id!=NULL){
			$sql .= 'WHERE `category_id` = :category_id ';
		}
		if($category!=NULL){
			$sql .= 'WHERE `category`=:category ';
		}
		$sql .= 'ORDER BY `category`';
		try{
			$stmt = $this->db->prepare($sql);
			if($id!=NULL){
				$stmt->bindParam(':category_id',$id,PDO::PARAM_INT);
			}
			if($category!=NULL){
				$stmt->bindParam(':category',$category,PDO::PARAM_STR);
			}
			$stmt->execute();
			$result = $stmt->fetchALL(PDO::FETCH_ASSOC);
			$stmt->closeCursor();
			return $result;
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}

	//查询地址
	public function searchAddress($address_id=NULL){
		$sql = 'SELECT
			*
			FROM
			`address`';
		if($address_id == NULL){
			$sql .= 'WHERE
				`customer_id` = :customer_id';
		}else{
			$sql .= 'WHERE
				`address_id` = :address_id
				LIMIT 1';
		}		
		try{
			$stmt = $this->db->prepare($sql);
			if($address_id == NULL){
				$stmt->bindParam(':customer_id',$_SESSION['user']['id'],PDO::PARAM_INT);
			}else{
				$stmt->bindParam(':address_id',$address_id,PDO::PARAM_INT);
			}
			$stmt->execute();
			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt->closeCursor();
			return $results;
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}

	//查询订单
	public function searchOrder($id=NULL,$condition=NULL){
		$sql = 'SELECT
			*
			FROM
			`orders` ';
		if($condition!=NULL){
			$sql .=  'WHERE `condition` = :condition ';
		}
		if($id!=NULL){
			$sql .= 'WHERE `order_id` = :order_id LIMIT 1 ';
		}else{
			$sql .= 'ORDER BY `order_id`';
		}
		try{
			$stmt =$this->db->prepare($sql);
			if($id!=NULL){
				$stmt->bindParam(':order_id',$id,PDO::PARAM_INT);
			}
			if($condition!=NULL){
				$stmt->bindParam(':condition',$condition,PDO::PARAM_STR);
			}
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt->closeCursor();
			return $result;
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}

	public function searchOrderItem($id){
		$sql = 'SELECT
			*
			FROM
			`order_items`
			WHERE
			`order_id` = :order_id';
		try{
			$stmt=$this->db->prepare($sql);
			$stmt->bindParam(':order_id',$id);
			$stmt->execute();
			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt->closeCursor();
			return $results;
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}

	public function searchComments($item_id){
		$sql = 'SELECT
			*
			FROM
			`comment`
			WHERE
			`item_id` = :item_id';
		try{
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':item_id',$item_id,PDO::PARAM_INT);
			$stmt->execute();
			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);		
			$stmt->closeCursor();
			return $results;
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}

	public function searchCustomer($id){
		$sql = 'SELECT
			*
			FROM
			`customers`
			WHERE
			`userid` = :id
			LIMIT 1';
		try{
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id',$id,PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch();		
			$stmt->closeCursor();
			return $result;
		}catch(Exception $e){
			$this->log->ERROR($e);
			return;
		}
	}
}
?>