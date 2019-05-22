<?php

use Endroid\QrCode\QrCode;

class creatQrCode{

	public function __construct($url,$png){
		$qrCode = new QrCode($url);	
		$qrCode->writeFile($_SERVER['DOCUMENT_ROOT'].'/images/QR/'.$png.'.png');
	}
}
