<?php

//包含初始化文件
include_once $_SERVER['DOCUMENT_ROOT'].'/../sys/core/init.inc.php';

//载入顶部
$page_title = '支付';
include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/header.php';

$view = new View($db);

?>

<div id="content">
	<?php echo $view->showOrder($_GET['id']); ?>
	<div style="clear: both;"></div>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/footer.php'; ?>