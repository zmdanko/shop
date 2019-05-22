<?php
/**
 * 邮箱验证页面
 */
if(isset($_GET['verify'])){
	$verify = trim($_GET['verify']);
	if(empty($verify)){
		header('Location:/');
		exit;
	}
}else{
	header("Location:/");
	exit;
}

//包含初始化文件
include_once $_SERVER['DOCUMENT_ROOT'].'/../sys/core/init.inc.php';
$page_title = '注册验证';
include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/header.php';

$identification = new Identification($db);

?>

<div id='content'>
	<?php echo $identification->verify($verify); ?>
	<br>
	<a href="/index.php">返回首页</a>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/footer.php'; ?>