<?php
/**
 * Paypal信用卡支付操作
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年04月07日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class paypalcc_payment
{
	private $cc_data = array();
	private $currency = "USD";
	private $invoice = 0;
	private $api_version = '60.0';
	private $api_username;
	private $api_password;
	private $api_signature;
	private $server_url = 'https://api-3t.paypal.com/nvp';
	private $price;

	/**
	 * 建構函式
	**/
	public function __construct($act_type='product')
	{
		if($act_type == 'demo'){
			$this->server_url = 'https://api-3t.sandbox.paypal.com/nvp';
		}else{
			$this->server_url = 'https://api-3t.paypal.com/nvp';
		}
		$this->cc_data['countrycode'] = "CN";
	}

	/**
	 * 信用卡資訊設定
	 * @引數 $name 引數名稱（也支援陣列寫法），支援以下引數：
	 *             firstname 名
	 *             lastname 姓
	 *             cvv2 CVV2 號碼（信用卡背面三位數）
	 *             type 型別
	 *             number 號碼
	 *             expdate 過期時間
	 *             street 街道
	 *             city 城市
	 *             state 省份
	 *             zipcode 郵編
	 *             countrycode 國家編碼
	 * @引數 $val 值
	**/
	public function cc($name,$val='')
	{
		if($name && is_array($name)){
			foreach($name as $key=>$value){
				$this->cc_data[$key] = $value;
			}
			return true;
		}
		if($name && $val == '' && isset($this->cc_data[$name])){
			return $this->cc_data[$name];
		}
		if($name && $val != ''){
			$this->cc_data[$name] = $val;
		}
		return false;
	}

	/**
	 * 價格
	 * @引數 $val 值，留空返回預設值
	**/
	public function price($val='')
	{
		if($val != ''){
			$this->price = $val;
		}
		return $this->price;
	}
	/**
	 * 貨幣
	 * @引數 $val 值，留空返回預設值
	**/
	public function currency($val='')
	{
		if($val != ''){
			$this->currency = $val;
		}
		return $this->currency;
	}

	/**
	 * 賬號
	 * @引數 $val 值，留空返回預設值
	**/
	public function api_username($val='')
	{
		if($val != ''){
			$this->api_username = $val;
		}
		return $this->api_username;
	}

	/**
	 * 密碼
	 * @引數 $val 值，留空返回預設值
	**/
	public function api_password($val='')
	{
		if($val != ''){
			$this->api_password = $val;
		}
		return $this->api_password;
	}

	/**
	 * 簽名
	 * @引數 $val 值，留空返回預設值
	**/
	public function api_signature($val='')
	{
		if($val != ''){
			$this->api_signature = $val;
		}
		return $this->api_signature;
	}

	/**
	 * 版本
	 * @引數 $val 值，留空返回預設值
	**/
	public function api_version($val='')
	{
		if($val != ''){
			$this->api_version = $val;
		}
		return $this->api_version;
	}

	/**
	 * 付款地址
	 * @引數 $val 值，留空返回預設值
	**/
	public function server_url($val='')
	{
		if($val != ''){
			$this->server_url = $val;
		}
		return $this->server_url;
	}

	public function act_type($type="product")
	{
		if($act_type == 'demo'){
			$this->server_url = 'https://api-3t.sandbox.paypal.com/nvp';
		}else{
			$this->server_url = 'https://api-3t.paypal.com/nvp';
		}
		return true;
	}

	public function submit()
	{
		global $app;
		$post = array('METHOD'=>'doDirectPayment');
		$post['PAYMENTACTION'] = 'sale';
		$post['USER'] = $this->api_username;
		$post['PWD'] = $this->api_password;
		$post['SIGNATURE'] = $this->api_signature;
		$post['VERSION'] = $this->api_version;
		$post['AMT'] = $this->price;
		$post['CREDITCARDTYPE'] = $this->cc_data['type'];
		$post['ACCT'] = $this->cc_data['number'];
		$post['EXPDATE'] = $this->cc_data['expdate'];
		$post['CVV2'] = $this->cc_data['cvv2'];
		$post['FIRSTNAME'] = $this->cc_data['firstname'];
		$post['LASTNAME'] = $this->cc_data['lastname'];
		if($this->cc_data['street']){
			$post['STREET'] = $this->cc_data['street'];
		}
		if($this->cc_data['city']){
			$post['CITY'] = $this->cc_data['city'];
		}
		if($this->cc_data['state']){
			$post['STATE'] = $this->cc_data['state'];
		}
		if($this->cc_data['zipcode']){
			$post['ZIP'] = $this->cc_data['zipcode'];
		}
		if($this->cc_data['countrycode']){
			$post['COUNTRYCODE'] = $this->cc_data['countrycode'];
		}
		if($this->currency){
			$post['CURRENCYCODE'] = $this->currency;
		}
		if($this->invoice){
			$post['INVNUM'] = $this->invoice;
		}
		$app->lib('curl')->is_post(true);
		foreach($post as $key=>$value){
			$app->lib('curl')->post_data($key,$value);
		}
		phpok_log($post);
		$info = $app->lib('curl')->get_content($this->server_url);
		if(!$info){
			return false;
		}
		parse_str($info,$data);
		return $data;
	}
}