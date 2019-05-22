<?php
/**
 * 查看变量
 */
class debug{
	public function __construct(){
		echo '<pre>';
		for($i = 0;$i < func_num_args();$i++){
       		var_dump(func_get_arg($i));
  		}
   		echo "</pre>";
    	exit;
	}
}