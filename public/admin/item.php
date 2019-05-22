<?php

/**
 * 管理界面
 */

include_once  $_SERVER['DOCUMENT_ROOT'].'/../sys/core/init.inc.php';

if(!isset($_SESSION['admin'])){
	header("Location:/");
}
$page_title = '管理商品';
include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/header.php';
$admin = new Admin($db);

?>

<div id="content">
	<?php echo $admin->itemForm(); ?>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/footer.php'; ?>