<?php
/*****************************************************************************************
	檔案： gateway/payment/wxpay/wxpay.php
	備註： 微信支付類
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2015年11月04日 14時22分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class wxpay_lib
{
	private $appid = '';
	private $mch_id = '';
	private $app_key = '';
	private $app_secret = '';
	private $pem_cert = '';
	private $pem_key = '';
	private $pem_ca = '';
	private $proxy_host = '0.0.0.0';
	private $proxy_port = 0;
	private $nonce_str = '';
	private $timeout = 6;
	private $errmsg = '';
	private $trade_type = 'native';
	private $red_config = array();
	private $timeStamp = '';
	
	public function __construct()
	{
		$this->nonce_str = md5(time().'-'.rand(100,999).'-phpok');
		$this->time_stamp = time();
	}

	public function config($config,$id='')
	{
		if($config && is_array($config)){
			foreach($config as $key=>$value){
				if($value != ''){
					$this->$key = $value;
				}
			}
		}else{
			if($config && $id){
				$this->$id = $config;
			}
		}
	}

	//定義公眾賬號ID
	public function appid($val='')
	{
		if($val){
			$this->appid = $val;
		}
		return $this->appid;
	}

	public function nonce_str()
	{
		return $this->nonce_str;
	}

	public function mch_id($val='')
	{
		if($val){
			$this->mch_id = $val;
		}
		return $this->mch_id;
	}

	public function app_key($val='')
	{
		if($val){
			$this->app_key = $val;
		}
		return $this->app_key;
	}

	public function app_secret($val='')
	{
		if($val){
			$this->app_secret = $val;
		}
		return $this->app_secret;
	}

	public function pem_cert($val='')
	{
		if($val){
			$this->pem_cert = $val;
		}
		return $this->pem_cert;
	}

	public function pem_key($val='')
	{
		if($val){
			$this->pem_key = $val;
		}
		return $this->pem_key;
	}

	public function pem_ca($val='')
	{
		if($val){
			$this->pem_ca = $val;
		}
		return $this->pem_ca;
	}

	public function proxy_host($val='')
	{
		if($val){
			$this->proxy_host = $val;
		}
		return $this->proxy_host;
	}

	public function proxy_port($val='')
	{
		if($val){
			$this->proxy_port = $val;
		}
		return $this->proxy_port;
	}

	public function trade_type($val='')
	{
		if($val){
			$this->trade_type = $val;
		}
		return $this->trade_type;
	}

	public function timeout($val=0)
	{
		if($val){
			$this->timeout = $val;
		}
		return $this->timeout;
	}

	public function time_stamp($time='')
	{
		if($time){
			$this->time_stamp = $time;
		}
		return $this->time_stamp;
	}

	public function errmsg($val='')
	{
		if($val){
			$this->errmsg = $val;
			$this->_log($val);
		}
		return $this->errmsg;
	}

	public function get_openid()
	{
		if (!isset($_GET['code'])){
			$baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
			$url = $this->__CreateOauthUrlForCode($baseUrl);
			header("Location: $url");
			exit;
		}else{
		    $code = $_GET['code'];
			$openid = $this->getOpenidFromMp($code);
			return $openid;
		}
	}

	/**
	 * 
	 * 構造獲取code的url連線
	 * @param string $redirectUrl 微信伺服器回跳的url，需要url編碼
	 * @return 返回構造好的url
	 */
	private function __CreateOauthUrlForCode($redirectUrl)
	{
		$urlObj["appid"] = $this->appid;
		$urlObj["redirect_uri"] = "$redirectUrl";
		$urlObj["response_type"] = "code";
		$urlObj["scope"] = "snsapi_base";
		$urlObj["state"] = "STATE"."#wechat_redirect";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
	}


	/**
	 * 
	 * 通過code從工作平臺獲取openid機器access_token
	 * @param string $code 微信跳轉回來帶上的code
	 * 
	 * @return openid
	 */
	public function GetOpenidFromMp($code)
	{
		$url = $this->__CreateOauthUrlForOpenid($code);
		//初始化curl
		$ch = curl_init();
		//設定超時
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if($this->proxy_host != "0.0.0.0" && $this->proxy_port){
			curl_setopt($ch,CURLOPT_PROXY,$this->proxy_host);
			curl_setopt($ch,CURLOPT_PROXYPORT,$this->proxy_port);
		}
		//執行curl，結果以jason形式返回
		$res = curl_exec($ch);
		curl_close($ch);
		//取出openid
		$data = json_decode($res,true);
		$this->data = $data;
		$openid = $data['openid'];
		return $openid;
	}

	private function __CreateOauthUrlForOpenid($code)
	{
		$urlObj["appid"] = $this->appid;
		$urlObj["secret"] = $this->app_secret;
		$urlObj["code"] = $code;
		$urlObj["grant_type"] = "authorization_code";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
	}

	private function ToUrlParams($urlObj)
	{
		$buff = "";
		foreach ($urlObj as $k => $v){
			if($k != "sign"){
				$buff .= $k . "=" . $v . "&";
			}
		}
		$buff = trim($buff, "&");
		return $buff;
	}


	public function query($sn)
	{
		if(!$sn){
			$this->errmsg('未指定訂單編號');
			return false;
		}
		$url = "https://api.mch.weixin.qq.com/pay/orderquery";
		$data = array('appid'=>$this->appid,'mch_id'=>$this->mch_id,'nonce_str'=>$this->nonce_str);
		$data['out_trade_no'] = $sn;
		$sign = $this->create_sign($data);
		$data['sign'] = $sign;
		$xml = $this->ToXml($data);
		$response = $this->postXmlCurl($xml, $url, false, $this->timeout);
		$rs = $this->FromXml($response);
		if($rs['return_code'] != 'SUCCESS'){
			 return false;
		}
		return $rs;
	}

	//建立訂單
	public function create($data)
	{
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		if(!$data['out_trade_no'] || !$data['total_fee'] || !$data['body']){
			$this->errmsg('引數不完整：價格，訂單號，訂單內容');
			return false;
		}
		if($this->trade_type == 'native' && !$data['product_id']){
			$this->errmsg('統一支付介面中，缺少必填引數product_id！trade_type為NATIVE時，product_id為必填引數');
			return false;
		}
		if($this->trade_type == 'jsapi' && !$data['openid']){
			$this->errmsg('統一支付介面中，缺少必填引數openid！trade_type為JSAPI時，openid為必填引數！');
			return false;
		}
		$data['appid'] = $this->appid;
		$data['mch_id'] = $this->mch_id;
		$data['nonce_str'] = $this->nonce_str;
		$data['spbill_create_ip'] = $GLOBALS['app']->lib('common')->ip();
		if(!$data['spbill_create_ip']){
			$data['spbill_create_ip'] = '0.0.0.0';
		}
		$data['trade_type'] = strtoupper($this->trade_type());
		$sign = $this->create_sign($data);
		$data['sign'] = $sign;
		$xml = $this->ToXml($data);
		$response = $this->postXmlCurl($xml, $url, false, $this->timeout);
		$rs = $this->FromXml($response);
		if($rs['return_code'] != 'SUCCESS'){
			$this->_log($rs);
			return false;
		}
		$sign = $rs['sign'];
		$chksign = $this->create_sign($rs);
		if($sign == $chksign){
			return $rs;
		}
		return false;
	}

	private function _log($info)
	{
		if(is_array($info) || is_object($info)){
			$info = print_r($info,true);
		}
		if(!$info){
			$info = time();
		}
		phpok_log($info);
		return true;
	}

	public function FromXml($xml)
	{	
		if(!$xml){
			return false;
		}
		return $GLOBALS['app']->lib('xml')->read($xml,false);
	}

	public function ToXml($data)
	{
		if(!is_array($data) || count($data) <= 0){
			return false;
    	}
    	
    	$xml = "<xml>";
    	foreach ($data as $key=>$val){
    		if (is_numeric($val)){
    			$xml.="<".$key.">".$val."</".$key.">";
    		}else{
    			$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
    		}
        }
        $xml.="</xml>";
        return $xml; 
	}


	public function create_sign($array)
	{
		ksort($array);
		$string = $this->array_to_string($array);
		$string = $string . "&key=".$this->app_key;
		$string = md5($string);
		$result = strtoupper($string);
		return $result;
	}

	private function array_to_string($list)
	{
		$buff = "";
		foreach ($list as $k => $v){
			if($k != "sign" && $v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}
		
		$buff = trim($buff, "&");
		return $buff;
	}

	public function postXmlCurl($xml, $url, $useCert = false, $second = 30)
	{		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		if($this->proxy_host && $this->proxy_host != '0.0.0.0' && $this->proxy_port){
			curl_setopt($ch,CURLOPT_PROXY,$this->proxy_host);
			curl_setopt($ch,CURLOPT_PROXYPORT,$this->proxy_port);
		}
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if($useCert){
			if($this->pem_ca){
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // 只信任CA頒佈的證書 
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 檢查證書中是否設定域名，並且是否與提供的主機名匹配
				curl_setopt($ch, CURLOPT_CAINFO, $this->pem_ca); // CA根證書（用來驗證的網站證書是否是CA頒佈）
			}else{
				curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
				curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
			}
			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLCERT, $this->pem_cert);
			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLKEY, $this->pem_key);
		}else{
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		}
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		$data = curl_exec($ch);
		if($data){
			curl_close($ch);
			return $data;
		} else { 
			$error = curl_errno($ch);
			curl_close($ch);
			throw new Exception("curl出錯，錯誤碼：".$error);
		}
	}

	private function getMillisecond()
	{
		//獲取毫秒的時間戳
		$time = explode ( " ", microtime () );
		$time = $time[1] . ($time[0] * 1000);
		$time2 = explode( ".", $time );
		$time = $time2[0];
		return $time;
	}


	public function GetJsApiParameters($data)
	{
		if(!array_key_exists("appid", $data) || !array_key_exists("prepay_id", $data) || $data['prepay_id'] == ""){
			$this->errmsg('引數錯誤');
			return false;
		}
		$values = array();
		$time = time();
		$values['appId'] = $data['appid'];
		$values['timeStamp'] = $this->time_stamp;
		$values['nonceStr'] = $this->nonce_str;
		$values['package'] = "prepay_id=".$data['prepay_id'];
		$values['signType'] = 'MD5';
		$values['paySign'] = $this->create_sign($values);
		return json_encode($values);
	}

	public function get_jsapi_param($data)
	{
		if(!array_key_exists("appid", $data) || !array_key_exists("prepay_id", $data) || $data['prepay_id'] == ""){
			$this->errmsg('引數錯誤');
			return false;
		}
		$values = array();
		$time = time();
		$values['appId'] = $data['appid'];
		$values['timeStamp'] = $this->time_stamp;
		$values['nonceStr'] = $this->nonce_str;
		$values['package'] = "prepay_id=".$data['prepay_id'];
		$values['signType'] = 'MD5';
		$values['paySign'] = $this->create_sign($values);
		return $values;
	}

	//紅包活動引數
	//支援引數有：act_name：活動名稱，wishing：活動祝願
	public function red_config($config='')
	{
		if($config && is_array($config)){
			$this->red_config = $config;
		}
		return $this->red_config;
	}

	//傳送紅包給商戶
	//openid，目標ID
	//price，價格，單位是：分
	public function hongbao($openid,$price)
	{
		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
		$data = $this->red_config();
		$data["max_value"] = $price;
		$data["min_value"] = $price;
		$data['re_openid'] = $openid;
		$sign = $this->create_sign($data);
		$data['sign'] = $sign;
		$xml = $this->ToXml($data);
		$response = $this->postXmlCurl($xml, $url, true,$this->timeout);
		$rs = $this->FromXml($response);
		if($rs['return_code'] != 'SUCCESS'){
			 return false;
		}
		return true;
	}
}
?>