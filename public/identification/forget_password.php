<?php

/**
 * 重置密码
 */
//包含初始化文件
include_once $_SERVER['DOCUMENT_ROOT'].'/../sys/core/init.inc.php';

//载入顶部
$page_title = '重置密码';
include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/header.php';

?>

<div id="content">
	<form action="/assets/inc/process.php" method="post">
		<fieldset>
			<legend>重置密码</legend>
			<label for="email">邮箱</label>
			<input type="text" name="email" id="email" />
			<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
			<input type="hidden" name="action" value="forget_password" />
			<input type="submit" name="forget_submit" value="发送更改密码邮件" />
		</fieldset>
	</form>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/footer.php'; ?>