<?php
/**
 * 常用資訊呼叫
 * @package phpok\libs
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年07月26日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class common_lib
{
	/**
	 * 建構函式
	**/
	public function __construct()
	{
		//
	}

	/**
	 * 取得IP地址
	**/
	public function ip()
	{
		$cip = (isset($_SERVER['HTTP_CLIENT_IP']) AND $_SERVER['HTTP_CLIENT_IP'] != "") ? $_SERVER['HTTP_CLIENT_IP'] : false;
		$rip = (isset($_SERVER['REMOTE_ADDR']) AND $_SERVER['REMOTE_ADDR'] != "") ? $_SERVER['REMOTE_ADDR'] : false;
		$fip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND $_SERVER['HTTP_X_FORWARDED_FOR'] != "") ? $_SERVER['HTTP_X_FORWARDED_FOR'] : false;
		$ip = "Unknown";
		if($cip && $rip){
			$ip = $cip;
		}elseif($rip){
			$ip = $rip;
		}elseif($cip){
			$ip = $cip;
		}elseif($fip){
			$ip = $fip;
		}

		if (strstr($ip, ',')){
			$x = explode(',', $ip);
			$ip = end($x);
		}
		if(filter_var($ip,FILTER_VALIDATE_IP)){
			return $ip;
		}
		return false;
	}

	/**
	 * CSS格式化或清理，去除無意義的CSS
	 * @引數 $info 要格式化的CSS資訊
	**/
	public function css_format($info='')
	{
		if(!$info) return false;
		$list = explode(';',$info);
		$array = array();
		foreach($list AS $key=>$value){
			$ext = explode(':',$value);
			if($ext[0] && $ext[1]){
				$array[$ext[0]] = $ext[1];
			}
		}
		$info = array();
		foreach($array AS $key=>$value){
			$info[] = $key.':'.$value;
		}
		return implode(";",$info);
	}

	/**
	 * 郵箱合法性驗證，此驗證僅僅只是簡單判斷
	 * @引數 $email 郵箱
	 * @返回 true 或 false
	**/
	public function email_check($email)
	{
		$atIndex = strrpos($email, "@");
		if (is_bool($atIndex) && !$atIndex){
			return false;
		}
		$domain = substr($email, $atIndex+1);
		$local = substr($email, 0, $atIndex);
		$localLen = strlen($local);
		$domainLen = strlen($domain);
		if($localLen < 1 || $localLen > 64){
			return false;
		}
		if($domainLen < 1 || $domainLen > 255){
			return false;
		}
		if($local[0] == '.' || $local[$localLen-1] == '.'){
			return false;
		}
		if(preg_match('/\\.\\./', $local)){
			return false;
		}
		if(!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)){
			return false;
		}
		if(preg_match('/\\.\\./', $domain)){
			return false;
		}
		if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',str_replace("\\\\","",$local))){
			if(!preg_match('/^"(\\\\"|[^"])+"$/',str_replace("\\\\","",$local))){
				return false;
			}
		}
		return true;
	}

	/**
	 * 身份證驗證，僅限中國大陸
	 * @引數 $idcard 身份證號碼
	 * @返回 true 或 false
	**/
	public function idcard_check($idcard)
	{
		if(!$idcard || strlen($idcard) != 15 && strlen($idcard) != 18){
			return false;
		}
		if(strlen($idcard) == 15){
			$idcard = $this->_idcard_15to18($idcard);
		}
		//身份
		$year = substr($idcard,6,4);
		$month = 'a'.substr($idcard,10,2);
		$day = substr($idcard,12,2);
		//日期永遠不可能超過31天
		if($day>31){
			return false;
		}
		//月份永遠不可能超過13，也不可能小於00
		//前面加一個字元a，讓資料以字串形式來比較
		$array = array('a01','a02','a03','a04','a05','a06','a07','a08','a09','a10','a11','a12');
		if(!in_array($month,$array)){
			return false;
		}
		//年份時間的比較，考慮伺服器時間異常，及存檔問題，故將比較年份適當增減範圍
		//正常情況下，正確的身份證這一關是能驗證通過的
		$minyear = date("Y") - 300;
		$maxyear = date("Y") + 20;
		if($year<$minyear || $year>$maxyear){
			return false;
		}
		//驗證身份證是否合法
		$idcard_base = substr($idcard, 0, 17); 
		if ($this->_idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))){ 
			return false; 
		}
		return true;
	}

	/**
	 * 升級15位身份證到18位
	 * @引數 $idcard 15位號碼
	 * @返回 18位身份證號碼
	**/
	private function _idcard_15to18($idcard)
	{
		if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false){ 
			$idcard = substr($idcard, 0, 6) . '18'. substr($idcard, 6, 9); 
		}else{ 
			$idcard = substr($idcard, 0, 6) . '19'. substr($idcard, 6, 9); 
		}
		$idcard = $idcard . $this->_idcard_verify_number($idcard);
		return $idcard;
	}

	/**
	 * 15位身份證升級到17位後，通過規則，取得第18位數字
	 * @引數 $idcard_base 15位號碼
	 * @返回 第18位號碼
	**/
	private function _idcard_verify_number($idcard_base) 
	{ 
		//加權因子 
		$factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2); 
		//校驗碼對應值 
		$verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'); 
		$checksum = 0; 
		for($i = 0; $i < strlen($idcard_base); $i++){ 
			$checksum += substr($idcard_base, $i, 1) * $factor[$i]; 
		}
		$mod = $checksum % 11; 
		$verify_number = $verify_number_list[$mod]; 
		return $verify_number; 
	}

	/**
	 * 是否電話判斷，支援國際電話判斷
	 * @引數 $tel 電話號碼
	 * @引數 $type 型別，支援 mobile tel 和 400電話，留空只要一個符合即通過
	 * @返回 true 或 false
	**/
	public function tel_check($tel,$type='')
	{
		$regxArr = array(
			'mobile'  =>  '/^(\+?\(?[0-9]+\)?\-?)?([0-9\-\s]+)+(-\d+)?$/',
			'tel' =>  '/^(\+?\(?[0-9]+\)?\-?)?([0-9\-\s]+)+(-\d+)?$/',
			'400' =>  '/^400(-?\d{3,4}){2}$/',
		);
		if($type && isset($regxArr[$type])){
			return preg_match($regxArr[$type], $tel) ? true:false;
		}
		foreach($regxArr as $regx){
			if(preg_match($regx, $tel )){
				return true;
			}
		}
		return false;
	}

	/**
	 * 獲取隨機字串
	 * @引數 $length 長度，預設是10
	 * @引數 $type 型別，支援：letter 字母，number 數字，all 全部
	 * @返回 隨機字元
	 * @更新時間 2016年07月26日
	**/
	public function str_rand($length=10,$type='')
	{
		$a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		if($type == 'number'){
			$a = '0123456789';
		}
		if($type == 'letter'){
			$a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		}
		$maxlength = strlen($a)-1;
		$rand_str = '';
		for($i=0;$i<$length;++$i){
			$rand_str .= $a[rand(0,$maxlength)];
		}
		return $rand_str;
	}

	/**
	 * 位元組數格式化為帶單位的資料，預設保留兩位小數
	 * @引數 $a 要格式化的資料
	 * @引數 $ext 保留位數
	 * @引數 $min_kb 小於1KB是否直接顯示1kb還是顯示位元組
	 * @返回 格式化後的字串
	**/
	public function num_format($a='',$ext=2,$min_kb=true)
	{
		if(!$a || $a == 0){
			return false;
		}
		if($a <= 1024){
			if($min_kb){
				$a = "1 KB";
			}else{
				return $a." B";
			}
		}elseif($a>1024 && $a<(1024*1024)){
			$a = round(($a/1024),$ext)." KB";
		}elseif($a>=(1024*1024) && $a<(1024*1024*1024)){
			$a = round(($a/(1024*1024)),$ext)." MB";
		}else{
			$a = round(($a/(1024*1024*1024)),$ext)." GB";
		}
		return $a;
	}

	/**
	 * 將非字母數字的字元轉化為ASCII字元，以實現任意編碼正常顯示
	 * @引數 $c 字串，要轉化的字串
	 * @返回 轉化後的字串
	**/
	public function ascii($c='')
	{
		if(!$c){
			return false;
		}
		$len = strlen($c);
		$a = 0;
		$scill = '';
		while ($a < $len){
			$ud = 0;
			if(ord($c{$a}) >=0 && ord($c{$a})<= 127){
				$ud = ord($c{$a});
				$a += 1;
			}elseif (ord($c{$a}) >= 192 && ord($c{$a})<= 223){
				$ud = (ord($c{$a})-192)*64 + (ord($c{$a+1})-128);
				$a += 2;
			}else if (ord($c{$a}) >=224 && ord($c{$a})<= 239){
				$ud = (ord($c{$a})-224)*4096 + (ord($c{$a+1})-128)*64 + (ord($c{$a+2})-128);
				$a += 3;
			}else if (ord($c{$a}) >=240 && ord($c{$a})<=247){
				$ud = (ord($c{$a})-240)*262144 + (ord($c{$a+1})-128)*4096 + (ord($c{$a+2})-128)*64 + (ord($c{$a+3})-128);
				$a += 4;
			}else if (ord($c{$a}) >=248 && ord($c{$a})<=251){
				$ud = (ord($c{$a})-248)*16777216 + (ord($c{$a+1})-128)*262144 + (ord($c{$a+2})-128)*4096 + (ord($c{$a+3})-128)*64 + (ord($c{$a+4})-128);
				$a += 5;
			}else if (ord($c{$a}) >=252 && ord($c{$a})<=253){
				$ud = (ord($c{$a})-252)*1073741824 + (ord($c{$a+1})-128)*16777216 + (ord($c{$a+2})-128)*262144 + (ord($c{$a+3})-128)*4096 + (ord($c{$a+4})-128)*64 + (ord($c{$a+5})-128);
				$a += 6;
			}else if (ord($c{$a}) >=254 && ord($c{$a})<=255){
				$ud = false;
			}
			$scill .= "&#$ud;";
		}
		return $scill;
	}

	public function urlsafe_b64encode($string)
	{
		$data = base64_encode($string);
		$data = str_replace(array('+','/','='),array('-','_',''),$data);
		return $data;
	}

	public function urlsafe_b64decode($str)
	{
		$data = str_replace(array('-','_'),array('+','/'),$str);
		$mod4 = strlen($data) % 4;
		if($mod4){
			$data .= substr('====', $mod4);
		}
		return base64_decode($data);
	}
}