<?php

//包含初始化文件
include_once $_SERVER['DOCUMENT_ROOT'].'/../sys/core/init.inc.php';

if((!isset($_SESSION['cart']))||($_SESSION['cart'] == array())){
	header("Location:/");
	exit;
}

$view = new View($db);
//载入顶部
$page_title = '确认购买';
include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/header.php';
?>

<div id="content">
	<div id="purchaseItem">
		<h4>确认订单信息</h4>
		<?php echo $view->displayCart($_SESSION['cart']); ?>
	</div>
	<div id="address">
		<h4>确认收货地址</h4>
		<?php echo $view->showAddress(); ?>
	</div>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/footer.php'; ?>