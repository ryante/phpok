<?php
/**
 * Cookie 資訊處理
 * @package phpok\libs
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年08月11日
**/
class cookie_lib
{
	private $domain;

	/**
	 * Cookie 字首
	**/
	private $prefix = 'phpok_';

	/**
	 * 超時時間，單位是秒
	**/
	private $expire_time = 3600;

	/**
	 * 金鑰
	**/
	private $encode_key = '';

	public function __construct($prefix='',$expire_time='',$encode_key='')
	{
		if($prefix && is_string($prefix)){
			$this->prefix = $prefix;
		}

		if($expire_time && is_numeric($expire_time)){
			$this->expire_time = $expire_time;
		}

		if($encode_key && is_string($encode_key)){
			$this->encode_key = $encode_key;
		}
	}

	/**
	 * Cookie 字首設定及讀取
	 * @引數 $prefix 字首，可用於實現
	**/
	public function prefix($prefix='')
	{
		if($prefix && is_string($prefix)){
			$this->prefix = $prefix;
		}
		return $this->prefix;
	}


	/**
	 * 過期時間
	 * @引數 $expire_time 時間，單位秒
	**/
	public function expire_time($expire_time='')
	{
		if($expire_time && is_numeric($expire_time)){
			$this->expire_time = $expire_time;
		}
		return $this->expire_time;
	}

	/**
	 * 設定cookie
	 * @引數 $name   cookie 名稱
	 * @引數 $value  cookie 值 可以是字串,陣列,物件等
	 * @引數 $expire 過期時間
	**/
	public function set($name, $value, $expire_time=0)
	{
		$cookie_name = $this->_name($name);
		$cookie_expire = time() + ($expire_time? $expire_time : $this->expire_time);
		$cookie_value = $this->_pack($value, $cookie_expire);
		$cookie_value = $this->_authcode($cookie_value, 'ENCODE');
		if($cookie_name && $cookie_value && $cookie_expire){
			setcookie($cookie_name, $cookie_value, $cookie_expire);
		}
	}

	/**
	 * 讀取cookie
	 * @引數 $name Cookie的名稱
	**/
	public function get($name)
	{
		$cookie_name = $this->_name($name);
		if(isset($_COOKIE[$cookie_name])){
			$cookie_value = $this->_authcode($_COOKIE[$cookie_name], 'DECODE');
			$cookie_value = $this->_unpack($cookie_value);
			return isset($cookie_value[0]) ? $cookie_value[0] : null;
		}
		return false;
	}

	/**
	 * 清除cookie
	 * @引數 $name cookie name
	**/
	public function clear($name)
	{
		$cookie_name = $this->_name($name);
		setcookie($cookie_name,'');
	}

	/**
	 * 獲取cookie name
	 * @引數  String $name
	**/
	private function _name($name)
	{
		if($this->prefix){
			$name = strpos($name,$this->prefix) === false ? $this->prefix.'_'.$name : $name;
			return $name;
		}
		return $name;
	}

	/**
	 * 打包時間
	 * @引數 $data 資料
	 * @引數 $expire 過期時間 用於判斷
	**/
	private function _pack($data, $expire)
	{
		if($data===''){
			return '';
		}
		$cookie_data = array();
		$cookie_data['value'] = $data;
		$cookie_data['expire'] = $expire;
		return serialize($cookie_data);
	}

	/**
	 * 解包
	 * @引數 $data 資料
	**/
	private function _unpack($data)
	{
		if($data===''){
			return array('', 0);
		}
		$cookie_data = unserialize($data);
		if(isset($cookie_data['value']) && isset($cookie_data['expire'])){
			if(time()<$cookie_data['expire']){
				return array($cookie_data['value'], $cookie_data['expire']);
			}
		}
		return array('', 0);
	}

	/**
	 * 加密/解密資料
	 * @引數 $string 原文或密文
	 * @引數 $operation ENCODE 或 DECODE
	**/
	private function _authcode($string, $operation = 'DECODE')
	{
		$ckey_length = 4;   // 隨機金鑰長度 取值 0-32;
		$key = $this->encode_key;
		$key = md5($key);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);
		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);
		$result = '';
		$box = range(0, 255);
		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}
		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
		if($operation == 'DECODE') {
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return '';
			}
		} else {
			return $keyc.str_replace('=', '', base64_encode($result));
		}
	}
}