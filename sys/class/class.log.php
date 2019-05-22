<?php

class Log{
	private $handle = NULL;

	public function __construct($file = ''){
		$this->handle = fopen(dirname(__FILE__).'/../logs/'.$file.date('Y-m-d').'.log','a');
	}

	public function ERROR($msg){
		$debugInfo = debug_backtrace();
		$stack = "[";
		foreach($debugInfo as $key => $val){
			if(array_key_exists("file", $val)){
				$stack .= ",file:" . $val["file"];
			}
			if(array_key_exists("line", $val)){
				$stack .= ",line:" . $val["line"];
			}
			if(array_key_exists("function", $val)){
				$stack .= ",function:" . $val["function"];
			}
		}
		$stack .= "]";
		$this->write($stack.$msg);
	}
	
	protected function write($msg){
		$msg = '['.date('Y-m-d H:i:s').']'.$msg."\r\n";
		fwrite($this->handle,$msg);
	}

	public function __destruct(){
		fclose($this->handle);
	}
}