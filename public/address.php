<?php

//包含初始化文件
include_once $_SERVER['DOCUMENT_ROOT'].'/../sys/core/init.inc.php';

//载入顶部
$page_title = '收货地址';
include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/header.php';
$view = new View($db);
$address_id = isset($_GET['address']) ? $_GET['address'] : NULL;
?>

<div id="content">
	<?php echo $view->addressForm($address_id) ?>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/footer.php'; ?>