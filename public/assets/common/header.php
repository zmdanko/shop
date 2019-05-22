<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo $page_title; ?></title>
	<?php
		$view = new View($db);
		$css_files = array('style.css','jq.css');
		foreach ($css_files as $css): 
	?>
	<link rel="stylesheet" type="text/css" href="<?php echo '/assets/css/'.$css; ?>" />
	<?php endforeach; ?>
</head>
<body>
	<div id="header">
		<div id="logo">
			<a href="/index.php"><img src="/images/logo.png" title='logo' alt="logo"></a>
		</div>
		<div id="info">
			<?php echo $view->headerInfo();	?>
		</div>
		<div style="clear: both;"></div>
	</div>