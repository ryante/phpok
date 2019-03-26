<?php
/**
 * 支付通知頁
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年10月13日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class yuansfer_notice
{
	var $paydir;
	var $order;
	var $payment;
	public function __construct($order,$param)
	{
		global $app;
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/yuansfer/';
		$this->baseurl = $GLOBALS['app']->url;
		require_once($this->paydir."vendor/autoload.php");
	}

	//獲取訂單資訊
	public function submit()
	{
		global $app;
		//如果非同步通知已通驗證，同步通知就不需要再次驗收
		if($this->order['status']){
			return true;
		}
		if(!isset($_GET['status']) || $_GET['status'] !== 'success'){
			$app->error('付款失敗，請檢查',$app->url);
		}
		$price = $app->get('amount','float');

		$array = array('status'=>1);
		$app->db->update_array($array,'payment_log',array('id'=>$this->order['id']));
		//如果當前支付操作是訂單
		if($this->order['type'] == 'order'){
			$order = $app->model('order')->get_one_from_sn($this->order['sn']);
			if($order){
				$payinfo = $app->model('order')->order_payment_notend($order['id']);
				if($payinfo){
					$payment_data = array('dateline'=>$app->time);
					$app->model('order')->save_payment($payment_data,$payinfo['id']);
					//更新訂單日誌
					$app->model('order')->update_order_status($order['id'],'paid');
					$note = P_Lang('訂單支付完成，編號：{sn}',array('sn'=>$order['sn']));
					$log = array('order_id'=>$order['id'],'addtime'=>$app->time,'who'=>$app->user['user'],'note'=>$note);
					$app->model('order')->log_save($log);
				}
			}
		}
		//充值操作
		if($this->order['type'] == 'recharge'){
			$app->model('wealth')->recharge($this->order['id']);
		}
		$GLOBALS['app']->plugin('payment-notice',$this->order['id']);
		return true;
	}
}