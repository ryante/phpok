<?php
/**
 * Token加密解密
 * @package phpok\libs
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年09月28日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class token_lib
{
	private $keyid = '';
	private $keyc_length = 6;
	private $keya;
	private $keyb;
	private $time;
	private $expiry = 3600;
	
	public function __construct()
	{
		$this->time = time();
	}

	/**
	 * 自定義金鑰
	 * @引數 $keyid 金鑰內容
	**/
	public function keyid($keyid='')
	{
		if(!$keyid){
			return $this->keyid;
		}
		$this->keyid = strtolower(md5($keyid));
		$this->config();
		return $this->keyid;
	}

	private function config()
	{
		if(!$this->keyid){
			return false;
		}
		$this->keya = md5(substr($this->keyid, 0, 16));
		$this->keyb = md5(substr($this->keyid, 16, 16));
	}

	/**
	 * 設定超時
	 * @引數 $time 超時時間，單位是秒
	**/
	public function expiry($time=0)
	{
		if($time && $time > 0){
			$this->expiry = $time;
		}
		return $this->expiry;
	}

	/**
	 * 加密資料
	 * @引數 $string 要加密的資料，陣列或字元
	**/
	public function encode($string)
	{
		if(!$this->keyid){
			return false;
		}
		$string = serialize($string);
		$expiry_time = $this->expiry ? $this->expiry : 365*24*3600;
		$string = sprintf('%010d',($expiry_time + $this->time)).substr(md5($string.$this->keyb), 0, 16).$string;	
		$keyc = substr(md5(microtime().rand(1000,9999)), -$this->keyc_length);
		$cryptkey = $this->keya.md5($this->keya.$keyc);
		$rs = $this->core($string,$cryptkey);
		return $keyc.str_replace('=', '', base64_encode($rs));
		//return $keyc.base64_encode($rs);
	}

	/**
	 * 解密
	 * @引數 $string 要解密的字串
	**/
	public function decode($string)
	{
		if(!$this->keyid){
			return false;
		}
		$string = str_replace(' ','+',$string);
		$keyc = substr($string, 0, $this->keyc_length);
		$string = base64_decode(substr($string, $this->keyc_length));
		$cryptkey = $this->keya.md5($this->keya.$keyc);
		$rs = $this->core($string,$cryptkey);
		$chkb = substr(md5(substr($rs,26).$this->keyb),0,16);
		if((substr($rs, 0, 10) - $this->time > 0) && substr($rs, 10, 16) == $chkb){
			$info = substr($rs, 26);
			return unserialize($info);
		}
		return false;
	}

	private function core($string,$cryptkey)
	{
		$key_length = strlen($cryptkey);
		$string_length = strlen($string);
		$result = '';
		$box = range(0, 255);
		$rndkey = array();
		// 產生密匙簿
		for($i = 0; $i <= 255; $i++){
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}
		// 用固定的演算法，打亂密匙簿，增加隨機性，好像很複雜，實際上並不會增加密文的強度
		for($j = $i = 0; $i < 256; $i++){
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		// 核心加解密部分
		for($a = $j = $i = 0; $i < $string_length; $i++){
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
		return $result;
	}
}