<?php

//包含初始化文件
include_once $_SERVER['DOCUMENT_ROOT'].'/../sys/core/init.inc.php';

//载入顶部
$page_title = '主页';
include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/header.php';
$view = new View($db);
$item_id = isset($_GET['item']) ? $_GET['item'] : NULL;
$category = isset($_GET['category']) ? $_GET['category'] : NULL;
?>

<div id="content">
	<div id='categories'>
		<?php echo $view->showCategories(); ?>
	</div>
	<div id='item'>
		<?php echo $view->showItem($item_id,$category); ?>
	</div>
	<div style="clear:both"></div>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/footer.php'; ?>