<?php
/**
 * JSON 編碼解碼操作
 * @package phpok\framework\libs
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK 開源授權協議：GNU Lesser General Public License
 * @時間 2017年11月14日
**/

class json_lib
{
	public function __construct()
	{
		
	}

	/**
	 * 將陣列轉成JSON資料
	 * @引數 $var 要轉換的資料
	 * @引數 $unicode 是否轉換中文等非字母資料
	 * @引數 $pretty 設定為true時表示優雅輸出，可視效果
	**/
	public function encode($var,$unicode=true,$pretty=false)
	{
		if(!$unicode){
			if($pretty){
				return json_encode($var,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
			}
			return json_encode($var,JSON_UNESCAPED_UNICODE);
		}else{
			if($pretty){
				return json_encode($var,JSON_PRETTY_PRINT);
			}
			return json_encode($var);
		}
	}

	/**
	 * JSON資料轉化為陣列或對像
	 * @引數 $str 要解碼的資料
	 * @引數 $is_array 是否轉成陣列，為否將轉成對像
	**/
	public function decode($str,$is_array=true)
	{
		if(!$str){
			return false;
		}
		return json_decode($str,$is_array);
	}
}