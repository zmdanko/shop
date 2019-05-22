<?php

/**
 * 登陆页面
 */
//包含初始化文件
include_once $_SERVER['DOCUMENT_ROOT'].'/../sys/core/init.inc.php';

//载入顶部
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
			<input type="hidden" name="action" value="user_login" />
			<input type="submit" value="登陆" />
			  <a href="/identification/register.php">注册</a><br>
			  <a href="/identification/forget_password.php">忘记密码</a>
		</fieldset>
	</form>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/footer.php'; ?>