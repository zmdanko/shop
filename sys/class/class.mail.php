<?php
/**
 * 邮件发送
 */
include_once dirname(__FILE__).'/../config/mail_inc.php';
//为配置信息定义常量
foreach($connect as $key => $val){
	define($key,$val);
}

class Mail{
	public function __construct($toEmail,$subject,$content,$type){
		$cmd = [
			'ehlo '.SMTP_USER."\r\n",
			"auth login\r\n",
			base64_encode(SMTP_USER)."\r\n",
			base64_encode(SMTP_PASS)."\r\n",
			'mail from:<'.SMTP_USER.">\r\n",
			'rcpt to:<'.$toEmail.">\r\n",
			"data\r\n",
			'Content-Type:'.$type."\r\n",
			'from:<'.SMTP_USER.">\r\n",
			'subject:'.$subject."\r\n",
			"\r\n",
			$content."\r\n",
			".\r\n",
			"quit\r\n"
		];
		$fp = fsockopen(SMTP_HOST,SMTP_PORT);
		foreach ($cmd as $val) {
			fputs($fp,$val);
			sleep(1);
		}
	}
}
?>