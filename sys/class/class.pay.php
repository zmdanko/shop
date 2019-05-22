<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/../sys/lib/WxPay/WxPay.Api.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/../sys/config/WxPay.Config.php';
class Pay{
	private $log;
	private $prefix;

	public function __construct(){
		$this->log = new Log('pay');
		$this->prefix = 'CXK';
	}

	//设定微信支付API
	public function setInput($id,$price){
		$outTradeNo = $this->prefix.str_pad($id,5,'0',STR_PAD_LEFT);
		$input = new WxPayUnifiedOrder();
		$input->SetBody("shop-微信付款");
		$input->SetOut_trade_no($outTradeNo);
		$input->SetTotal_fee($price);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 7200));
		$input->SetNotify_url($_SERVER['SERVER_NAME'].'/notify.php');
		$input->SetTrade_type("NATIVE");
		$input->SetProduct_id($outTradeNo);
		return $input;
	}

	public function getPayUrl($id,$price){
		$input = $this->setInput($id,$price);
		try{
			$config = new WxPayConfig();
			$result = WxPayApi::unifiedOrder($config, $input);
			$url = $result["code_url"];
			return $url;
		}catch(Exception $e){
			$this->log->ERROR($e);
		}
		return false;
	}

	public function queryOrder($id){
		$input = new WxPayOrderQuery();
		$outTradeNo = $this->prefix.str_pad($id,5,'0',STR_PAD_LEFT);
		$input->SetOut_trade_no($outTradeNo);
		$config = new WxPayConfig();
		$result = WxPayApi::orderQuery($config,$input);
		return $result;
	}
}