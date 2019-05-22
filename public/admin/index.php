<?php

/**
 * 登陆管理界面
 */

include_once  $_SERVER['DOCUMENT_ROOT'].'/../sys/core/init.inc.php';

$page_title = '登陆';
include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/header.php';
?>

<div id="content">
	<form action="/assets/inc/process.php" method="post">
		<fieldset>
			<legend>登陆</legend>
			<label for="username">用户</label>
			<input type="text" name="username" id="username" />
			<label for="password">密码</label>
			<input type="password" name="password" id="password" />
			<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
			<input type="hidden" name="action" value="admin_login" />
			<input type="submit" value="登陆" />
		</fieldset>
	</form>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/footer.php'; ?>