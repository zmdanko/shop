<?php
header("Content-type:text/html;charset=utf-8");
date_default_timezone_set('PRC');
/**
 * 创建初始化文件
 */
//启用session
session_start();
if(!isset($_SESSION['token'])){
	$_SESSION['token'] = sha1(uniqid(mt_rand(),TRUE));
}
//包含配置信息
include_once dirname(__FILE__).'/../config/db_inc.php';
include_once dirname(__FILE__).'/../../vendor/autoload.php';
//为配置信息定义常量
foreach($connect as $key => $val){
	define($key,$val);
}

//自动加载类
spl_autoload_register(function($class){
	$arr = array();
	$file = dirname(__FILE__).'/../class/class.'.$class.'.php';
	if(is_file($file)){
		include_once $file;
	}else{
		foreach($arr as $value){
			$class = explode('\\',$class);
			$file = dirname(__FILE__).'/../lib/'.$value.'/'.end($class).'.php';
			if(is_file($file)){
				include_once $file;
				break;
			}
		}	
	}
});

try{
	//生成PDO对象
	$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
	$db = new PDO($dsn,DB_USER,DB_PASS);
}catch(Exception $e){
	$log = new Log('db');
	$log->ERROR($e);
	return;
}

?>