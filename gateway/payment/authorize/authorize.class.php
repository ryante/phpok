<?php
/**
 * Authorize 支付類
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年08月21日
**/

class authorize_lib
{
	private $url = '';
	private $url_test = 'https://apitest.authorize.net/xml/v1/request.api';
	private $url_product = 'https://api.authorize.net/xml/v1/request.api';
	private $api_name = '';
	private $api_key = '';
	private $ref_id = '';
	private $amount = 0; //訂單金額
	private $sandbox = false;
	private $request_data = array();
	
	public function __construct($name='',$key='',$test=false)
	{
		$this->api_name($name);
		$this->api_key($key);
		$this->sandbox($test);
		$this->request_data['transactionRequest'] = array();
		$this->request_data['transactionRequest']['transactionType'] = 'authCaptureTransaction';
	}

	public function config($name='',$key='')
	{
		$this->api_name($name);
		$this->api_key($key);
		$this->request_data['merchantAuthentication'] = array('name'=>$this->api_name,'transactionKey'=>$this->api_key);
	}

	public function act_type($val='')
	{
		if($val){
			$this->request_data['transactionRequest']['transactionType'] = $val;
		}
		return $this->request_data['transactionRequest']['transactionType'];
	}

	public function api_name($name='')
	{
		if($name){
			$this->api_name = $name;
		}
		return $this->api_name;
	}

	public function api_key($key='')
	{
		if($key){
			$this->api_key = $key;
		}
		return $this->api_key;
	}

	public function sandbox($test='')
	{
		if(isset($test) && is_bool($test)){
			$this->sandbox = $test;
		}
		$this->url = $this->sandbox ? $this->url_test : $this->url_product;
		return $this->sandbox;
	}

	public function url($url='')
	{
		if($url){
			$this->url = $url;
		}
		return $this->url;
	}

	/**
	 * 設定要Post的資料，僅限一級
	**/
	public function post($key,$data)
	{
		$this->request_data['transactionRequest'][$key] = $data;
	}

	public function unpost($key)
	{
		if(isset($this->request_data['transactionRequest'][$key])){
			unset($this->request_data['transactionRequest'][$key]);
		}
	}

	/**
	 * 訂單編號，用於連線本地和遠端的ID
	 * @引數 $id 唯一的訂單編號
	**/
	public function ref_id($id='')
	{
		if($id){
			$this->ref_id = $id;
		}
		$this->request_data['refId'] = $this->ref_id;
		return $this->ref_id;
	}

	public function amount($price='')
	{
		if($price != ''){
			$this->amount = $price;
		}
		$this->request_data['transactionRequest']['amount'] = $this->amount;
		return $this->amount;
	}

	public function to_json()
	{
		ksort($this->request_data);
		$data = array('createTransactionRequest'=>$this->request_data);
		return json_encode($data,JSON_UNESCAPED_UNICODE);
	}
}