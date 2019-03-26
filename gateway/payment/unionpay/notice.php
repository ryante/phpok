<?php
/*****************************************************************************************
	檔案： payment/unionpay/notice.php
	備註： 支付通知頁
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2014年5月3日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class unionpay_notice
{
	private $paydir;
	private $order;
	private $payment;
	public function __construct($order,$payment)
	{
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/unionpay/';
		$this->order = $order;
		$this->param = $payment;
		include_once($this->paydir."unionpay.php");
	}

	//獲取訂單資訊
	public function submit()
	{
		global $app;
		if($this->order['status']){
			return true;
		}
		$payment = new unionpay_lib();
		$payment->set_verify_id($app->dir_root.$this->param['param']['verify_cert_file']);
		$params = $_POST;
		if($params['respCode'] != '00'){
			error("付款失敗，錯誤資訊：".$params['respMsg'],'','error');
		}
		$chk = $payment->verify($params);
		if(!$chk){
			error('付款簽名驗證失敗，請登入支付平臺檢查','','error');
		}
		if(!$params['respMsg'] || $params['respMsg'] != 'success'){
			return false;
		}
		$pay_date = $app->time;
		$price = round(($params['settleAmt']/100),2);
		//
		$data = $this->order['ext'] ? unserialize($this->order['ext']) : array();
		$data['traceNo'] = $params['traceNo'];
		$data['traceTime'] = $params['traceTime'];
		$data['queryId'] = $params['queryId'];
		$data['currencyCode'] = $params['currencyCode'];
		$array = array('status'=>1,'ext'=>serialize($data));
		$app->db->update_array($array,'payment_log',array('id'=>$this->order['id']));
		if($this->order['type'] == 'order'){
			$order = $app->model('order')->get_one_from_sn($this->order['sn']);
			if($order){
				$payinfo = $app->model('order')->order_payment_notend($order['id']);
				if($payinfo){
					//增加order_payment
					$array = array('order_id'=>$order['id'],'payment_id'=>$this->param['id']);
					$array['title'] = $this->param['title'];
					$array['price'] = $price;
					$array['dateline'] = $app->time;
					$array['ext'] = serialize($data);
					$app->model('order')->save_payment($array,$payinfo['id']);
					$app->model('order')->update_order_status($order['id'],'paid');
					$note = P_Lang('訂單支付完成，編號：{sn}',array('sn'=>$order['sn']));
					$log = array('order_id'=>$order['id'],'addtime'=>$app->time,'who'=>$app->user['user'],'note'=>$note);
					$app->model('order')->log_save($log);
				}
			}
		}
		if($this->order['type'] == 'recharge' && $data['goal']){
			$app->model('wealth')->recharge($this->order['id']);
		}
		$GLOBALS['app']->plugin('payment-notice',$this->order['id']);
		return true;
	}
}