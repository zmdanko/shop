<?php
/**
 * 数据库操作
 */
class DB{
	// 保存数据库对象
	protected $db;
	private $log;
	//检查数据库对象，不存在则生成
	protected function __construct($db=NULL){
		$this->log = new Log('db');
		if(is_object($db)){
			$this->db = $db;
		}else{
			//在/sys/config/db.inc.php中定义常量
			$dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME;
			try{
				$this->db = new PDO($dsn,DB_USER,DB_PASS);
			}
			catch(Exception $e){
				$this->log->ERROR($e);
				return;
			}
		}
	}
}
?>