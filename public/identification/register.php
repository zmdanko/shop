<?php

/**
 * 注册页面
 */
//包含初始化文件
include_once $_SERVER['DOCUMENT_ROOT'].'/../sys/core/init.inc.php';


//载入顶部
$page_title = '注册';
include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/header.php';

?>

<div id="content">
	<form action="/assets/inc/process.php" method="post">
		<fieldset>
			<legend>注册</legend>
			<label for="username">用户</label>
			<input type="text" name="username" id="username" />
			<label for="password">密码</label>
			<input type="password" name="password" id="password" />
			<label for="password1">再次输入密码</label>
			<input type="password" name="password1" id="password1" />
			<label for="email">邮箱</label>
			<input type="text" name="email" id="email" />
			<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
			<input type="hidden" name="action" value="register" />
			<input type="submit" name="register_submit" value="注册" />
		</fieldset>
	</form>
</div>

<?php include_once $_SERVER['DOCUMENT_ROOT'].'/assets/common/footer.php'; ?>