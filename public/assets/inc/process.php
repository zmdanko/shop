<?php
/**
 * 处理表单提交数据
 */
include_once '../../../sys/core/init.inc.php';
//创建关联数组
$actions = array(
	'user_login'=>array(
		'object'=>'identification',
		'method'=>'login',
		'header'=>'/'
	),
	'register'=>array(
		'object'=>'identification',
		'method'=>'register',
		'header'=>'/'
	),
	'forget_password'=>array(
		'object'=>'identification',
		'method'=>'forgetPassword',
		'header'=>'/'
	),
	'reset_password'=>array(
		'object'=>'identification',
		'method'=>'changePassword',
		'header'=>'/'
	),
	'user_logout'=>array(
		'object'=>'identification',
		'method'=>'logout',
		'header'=>'/'
	),
	'add_cart'=>array(
		'object'=>'View',
		'method'=>'addCart',
		'header'=>'/'
	),
	'save_cart'=>array(
		'object'=>'View',
		'method'=>'saveCart',
		'header'=>'/'
	),
	'add_address'=>array(
		'object'=>'View',
		'method'=>'addAddress',
		'header'=>'/checkout.php'
	),
	'delete_address'=>array(
		'object'=>'View',
		'method'=>'deleteAddress',
		'header'=>'/checkout.php'
	),
	'save_order'=>array(
		'object'=>'View',
		'method'=>'saveOrder',
		'header'=>'/orderItem.php'
	),
	'admin_login'=>array(
		'object'=>'Identification',
		'method'=>'adminLogin',
		'header'=>'/'
	),
	'edit_item'=>array(
		'object'=>'Admin',
		'method'=>'editItem',
		'header'=>'/admin/item.php'
	),
	'add_pic'=>array(
		'object'=>'Admin',
		'method'=>'addItemPic',
		'header'=>'/admin/item.php'
	)
);

if($_POST['token']==$_SESSION['token'] && isset($actions[$_POST['action']])){
	$use_array = $actions[$_POST['action']];
	$obj = new $use_array['object']($db);
	$method = $use_array['method'];
	$msg = $obj->$method();
	$preg = '/^(TRUE){1}(.+)?/i';
	if(preg_match($preg,$msg)){
		$msg = isset(explode('TRUE',$msg)['1']) ? explode('TRUE',$msg)['1'] : '';
		$href = $use_array['header'].$msg;
		if((isset($_POST['ajax']))&&($_POST['ajax']==TRUE)){
			echo $href;
		}else{
			header('Location:'.$href);
			exit;
		}		
	}else{
		echo $msg;
		exit;
	}
}else{
	echo '信息提交错误';
	exit;
}
?>