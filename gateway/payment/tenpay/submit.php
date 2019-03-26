<?php
/*****************************************************************************************
	檔案： payment/tenpay/submit.php
	備註： 財付通確認支付
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2014年5月3日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tenpay_submit
{
	//支付介面初始化
	var $param;
	var $order;
	var $paydir;
	var $baseurl;
	function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/tenpay/';
		$this->baseurl = $GLOBALS['app']->url;
		include_once($this->paydir."tenpay.php");
	}

	function submit()
	{
		global $app;
		$tenpay = new tenpay_lib();
		$tenpay->set_key($this->param['param']['key']);
		$tenpay->set_biz($this->param['param']['pid']);
		$tenpay->set_email($this->param['param']['email']);
		$tenpay->set_url('https://gw.tenpay.com/gateway/pay.htm');
		$notify_url = $this->baseurl."gateway/payment/tenpay/notify_url.php";
        $return_url = $app->url('payment','notice','id='.$this->order['id'],'www',true);
		$currency_id = $this->param['currency'] ? $this->param['currency']['id'] : $this->order['currency_id'];
		$total_fee = price_format_val($this->order['price'],$this->order['currency_id'],$currency_id);
		//商戶編號
		$desc = $this->order['title'];
		$tenpay->param('partner',$this->param['param']['pid']);
		$tenpay->param("out_trade_no", $this->order['sn']);
		$tenpay->param("total_fee", $total_fee * 100);
		$tenpay->param("return_url", $return_url);
		$tenpay->param("notify_url", $notify_url);
		$tenpay->param("body",$desc);
		if($this->param['param']['bank']){
			$tenpay->param("bank_type",trim(strtoupper($this->param['param']['bank'])));
		}else{
			$tenpay->param("bank_type", "DEFAULT");
		}
		$tenpay->param("spbill_create_ip",$app->lib('common')->ip());//客戶端IP
		$tenpay->param("fee_type", "1");
		$tenpay->param("subject",$desc);

		//系統可選引數
		$tenpay->param("sign_type", "MD5");  	 	  //簽名方式，預設為MD5，可選RSA
		$tenpay->param("service_version", "1.0"); 	  //介面版本號
		$tenpay->param("input_charset", "utf-8");   	  //字符集
		$tenpay->param("sign_key_index", "1");    	  //金鑰序號

		//業務可選引數
		$ptype = $this->param['param']['ptype'] == 'create_direct_pay_by_user' ? 1 : 2;
		$tenpay->param("attach", $this->order['passwd']);      //附件資料，原樣返回就可以了
		$tenpay->param("product_fee", "");        	  //商品手續費用
		$tenpay->param("transport_fee", "0");      	  //物流費用
		$tenpay->param("time_start", date("YmdHis",$this->time));  //訂單生成時間
		$tenpay->param("time_expire", "");             //訂單失效時間
		$tenpay->param("buyer_id", "");                //買方財付通帳號
		$tenpay->param("goods_tag", "");               //商品標記
		$tenpay->param("trade_mode",$ptype);      //交易模式（1.即時到帳模式，2.中介擔保模式，3.後臺選擇（賣家進入支付中心列表選擇））
		$tenpay->param("transport_desc","");              //物流說明
		$tenpay->param("trans_type","1");              //交易型別
		$tenpay->param("agentid","");                  //平臺ID
		$tenpay->param("agent_type",0);               //代理模式（0.無代理，1.表示卡易售模式，2.表示網店模式）
		$tenpay->param("seller_id","");                //賣家的商戶號
		$url = $tenpay->url();
		$app->_location($url);
		exit;
	}
}
?>