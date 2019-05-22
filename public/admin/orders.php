<?php

/**
 * 查询订单
 */

include_once  $_SERVER['DOCUMENT_ROOT'].'/../sys/core/init.inc.php';

if(!isset($_SESSION['admin'])){
	header("Location:/");
}
$page_title = '查询订单';
include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/header.php';
if(isset($_POST['condition'])){
	$condition = $_POST['condition'];
}else{
	$condition = NULL;
}
$admin = new Admin($db);

?>

<div id="content">
	<?php echo $admin->showOrderList($condition); ?>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/footer.php'; ?>