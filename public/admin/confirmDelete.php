<?php

/**
 * 确认删除界面
 */
include_once  $_SERVER['DOCUMENT_ROOT'].'/../sys/core/init.inc.php';
if(!isset($_SESSION['admin'])){
	header("Location:/");
}
$page_title = '确认删除';
include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/header.php';

$admin = new Admin($db);
if(!empty($_POST['item_id'])){
	if(!empty($_POST['picture'])){
		$obj = $_POST['picture'];
		$type = 'pic';
	}else{
		$obj = $_POST['item_id'];
		$type = 'item';
	}
}
if(!empty($_POST['category_id'])){
	$obj = $_POST['category_id'];
	$type = 'category';
}
?>

<div id="content">
	<?php echo $admin->confirmDelete($obj,$type); ?>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/footer.php'; ?>