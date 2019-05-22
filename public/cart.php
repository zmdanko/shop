<?php

//包含初始化文件
include_once $_SERVER['DOCUMENT_ROOT'].'/../sys/core/init.inc.php';

//载入顶部
$page_title = '购物车';
include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/header.php';

$view = new View($db);

if(!isset($_SESSION['cart'])){
	$_SESSION['cart'] = array();
}
?>

<div id="content">
	<?php 
	echo $view->displayCart($_SESSION['cart'],true);
	if(!$_SESSION['cart'] == array()){
		echo $view->displayButton('checkout','确认购买');
	}	
	?>
	<div style="clear:both"></div>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/footer.php'; ?>