<?php
/**
 *
 */
trait Check{
	
	//验证信息完整
	public function filledOut($post,$exceptions=NULL){
		$result = NULL;

		foreach($post as $key => $value){
			if(is_array($exceptions)){
				foreach($exceptions as $exception){
					if($key=$exception){
						break;
					}else if((!isset($key))||($value=='')){
						$result[] = $key;
					}
				}	
			}else{
				if((!isset($key))||($value=='')){
					$result[] = $key;
				}
			}
		}
		return $result;
	}
}