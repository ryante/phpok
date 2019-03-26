<?php
/***********************************************************
	Filename: {phpok}/api/order_control.php
	Note	: 建立訂單操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年12月8日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class order_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$this->cart_id = $this->model('cart')->cart_id($this->session->sessid(),$_SESSION['user_id']);
	}

	/**
	 * 建立訂單
	**/
	public function create_f()
	{
		$user = array();
		if($this->session->val('user_id')){
			$user = $this->model('user')->get_one($this->session->val('user_id'));
		}
		$id = $this->get('id','int');
		if(!$id || !is_array($id)){
			$this->error(P_Lang('沒有要結算的產品'));
		}
		$rslist = $this->model('cart')->get_all($this->cart_id,$id);
		if(!$rslist){
			$this->json(P_Lang("沒有要結算的產品"));
		}
		$is_virtual = true;
		foreach($rslist as $key=>$value){
			if(!$value['is_virtual']){
				$is_virtual = false;
				break;
			}
		}
		if($is_virtual){
			$mobile = $this->get('mobile');
			$email = $this->get('email');
			if(!$mobile){
				$this->error(P_Lang('請填寫手機號'));
			}
			if(!$this->lib('common')->tel_check($mobile,'mobile')){
				$this->error(P_Lang('手機號不合法'));
			}
		}else{
			$address_id = $this->get('address_id','int');
			if($this->session->val('user_id') && $address_id){
				$address = $this->model('address')->get_one($address_id);
				if(!$address){
					$this->error(P_Lang('收件人資訊不存在，請檢查'));
				}
				if($address['user_id'] != $this->session->val('user_id')){
					$this->error(P_Lang('收件人資訊與賬號不匹配，請檢查'));
				}
			}
			if(!isset($address) || !$address){
				$tmp = $this->form_address();
				if(!$tmp['status']){
					$this->error($tmp['info']);
				}
				$address = $tmp['info'];
			}
			if(!$address){
				$this->error(P_Lang('地址資訊不完整'));
			}
			$mobile = $address['mobile'];
			$email = $address['email'];
		}
		//運費
		$shipping = 0;
		//產品價格
		$price = 0;
		
		foreach($rslist as $key=>$value){
			$price += floatval($value['price']) * intval($value['qty']);
			if(!$value['is_virtual'] && ($value['weight'] || $value['volume']) && $address && $address['province'] && $address['city']){
				$tmp = array('number'=>intval($value['qty']));
				$tmp['weight'] = floatval($value['weight']) * intval($value['qty']);
				$tmp['volume'] = floatval($value['volume']) * intval($value['qty']);
				$tmp_shipping = $this->model('order')->freight_price($tmp,$address['province'],$address['city']);
				if($tmp_shipping){
					$shipping += floatval($tmp_shipping);
				}
			}
		}

		//檢測是否有coupon
		$coupon = $this->_coupon($rslist);
		$allprice = floatval($price) + floatval($shipping) - floatval($coupon);

		$sn = $this->model('order')->create_sn();
		$main = array('sn'=>$sn);
		$main['user_id'] = $user ? $user['id'] : 0;
		$main['addtime'] = $this->time;
		$main['price'] = $allprice;
		$main['currency_id'] = $this->site['currency_id'];
		$main['status'] = 'create';
		$main['passwd'] = md5(str_rand(10));
		$main['email'] = $email;
		$main['mobile'] = $mobile;
		$main['note'] = $this->get('note');
		//儲存擴充套件欄位資訊
		$tmpext = $this->get('ext');
		if($tmpext){
			foreach($tmpext as $key=>$value){
				$key = $this->format($key);
				if(!$key || !$value){
					unset($tmpext[$key]);
					continue;
				}
			}
			if($tmpext){
				$main['ext'] = serialize($tmpext);
			}
		}
		$order_id = $this->model('order')->save($main);
		if(!$order_id){
			$this->error(P_Lang('訂單建立失敗'));
		}
		foreach($rslist as $key=>$value){
			$tmp = array('order_id'=>$order_id,'tid'=>$value['tid']);
			$tmp['title'] = $value['title'];
			$tmp['price'] = price_format_val($value['price'],$this->site['currency_id']);
			$tmp['qty'] = $value['qty'];
			$tmp['weight'] = $value['weight'];
			$tmp['volume'] = $value['volume'];
			$tmp['unit'] = $value['unit'];
			$tmp['thumb'] = $value['thumb'] ? $value['thumb'] : '';
			$tmp['ext'] = $value['_attrlist'] ? serialize($value['_attrlist']) : '';
			$tmp['is_virtual'] = $value['is_virtual'];
			$this->model('order')->save_product($tmp);
		}
		if($address){
			$tmp = array('order_id'=>$order_id);
			$tmp['country'] = $address['country'];
			$tmp['province'] = $address['province'];
			$tmp['city'] = $address['city'];
			$tmp['county'] = $address['county'];
			$tmp['address'] = $address['address'];
			$tmp['mobile'] = $address['mobile'];
			$tmp['tel'] = $address['tel'];
			$tmp['email'] = $address['email'];
			$tmp['fullname'] = $address['fullname'];
			$this->model('order')->save_address($tmp);
		}
		$pricelist = $this->model('site')->price_status_all();
		if($pricelist){
			foreach($pricelist as $key=>$value){
				$tmp_price = '0.00';
				if($key == 'product'){
					$tmp_price = $price;
				}elseif($key == 'shipping'){
					$tmp_price = $shipping;
				}elseif($key == 'discount' && $coupon){
					$tmp_price = -$coupon;
				}
				$tmp = array('order_id'=>$order_id,'code'=>$key,'price'=>$tmp_price);
				$this->model('order')->save_order_price($tmp);
			}
		}
		//刪除購物車資訊
		$this->model('cart')->delete($this->cart_id,$id);
		//填寫訂單日誌
		$note = P_Lang('訂單建立成功，訂單編號：{sn}',array('sn'=>$sn));
		$log = array('order_id'=>$order_id,'addtime'=>$this->time,'who'=>$user['user'],'note'=>$note);
		$this->model('order')->log_save($log);
		//增加訂單通知
		$param = 'id='.$order_id."&status=create";
		$this->model('task')->add_once('order',$param);
		$rs = array('sn'=>$sn,'passwd'=>$main['passwd'],'id'=>$order_id);
		$this->success($rs);
	}

	/**
	 * 優惠碼功能
	**/
	private function _coupon($rslist)
	{
		return false;
	}

	/**
	 * 獲取表單地址
	 * @返回 陣列
	**/
	private function form_address()
	{
		$array = array();
		$country = $this->get('country');
		if(!$country){
			$country = '中國';
		}
		$array['country'] = $country;
		$array['province'] = $this->get('pca_p');
		$array['city'] = $this->get('pca_c');
		$array['county'] = $this->get('pca_a');
		$array['fullname'] = $this->get('fullname');
		if(!$array['fullname']){
			return array('status'=>false,'info'=>P_Lang('收件人姓名不能為空'));
		}
		$array['address'] = $this->get('address');
		$array['mobile'] = $this->get('mobile');
		$array['tel'] = $this->get('tel');
		if(!$array['mobile'] && !$array['tel']){
			return array('status'=>false,'info'=>P_Lang('手機或固定電話必須有填寫一項'));
		}
		if($array['mobile']){
			if(!$this->lib('common')->tel_check($array['mobile'],'mobile')){
				return array('status'=>false,'info'=>P_Lang('手機號格式不對'));
			}
		}
		if($array['tel']){
			if(!$this->lib('common')->tel_check($array['tel'],'tel')){
				return array('status'=>false,'info'=>P_Lang('電話格式不對'));
			}
		}
		$array['email'] = $this->get('email');
		if($array['email']){
			if(!$this->lib('common')->email_check($array['email'])){
				return array('status'=>false,'info'=>P_Lang('郵箱格式不對'));
			}
		}
		return array('status'=>true,'info'=>$array);
	}

	public function info_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$sn = $this->get('sn');
			if(!$sn){
				$this->json(P_Lang('未指定訂單ID或SN號'));
			}
			$rs = $this->model('order')->get_one_from_sn($sn);
		}else{
			$rs = $this->model('order')->get_one($id);
		}
		if(!$rs){
			$this->json(P_Lang('訂單資訊不存在'));
		}
		if($_SESSION['user_id']){
			if($rs['user_id'] != $_SESSION['user_id']){
				$this->json(P_Lang('您沒有許可權獲取此訂單資訊'));
			}
		}else{
			$passwd = $this->get('passwd');
			if(!$passwd){
				$this->json(P_Lang('查詢密碼不能留空'));
			}
			if($passwd != $rs['passwd']){
				$this->json(P_Lang('密碼不正確'));
			}
		}
		$paycheck = $this->model('order')->check_payment_is_end($rs['id']);
		if($paycheck){
			$rs['pay_end'] = true;
		}else{
			$rs['pay_end'] = false;
		}
		$this->json($rs,true);
	}

	/**
	 * 訂單取消
	 * @引數 $id 訂單ID號
	 * @引數 $sn 訂單SN碼
	 * @引數 $passwd 訂單密碼
	**/
	public function cancel_f()
	{
		$rs = $this->_get_order();
		if(!$rs['status']){
			$this->error(P_Lang('訂單狀態異常，請聯絡客服'));
		}
		$array = array('create','unpaid');
		if(!in_array($rs['status'],$array)){
			$this->error(P_Lang('僅限訂單未支付才能取消訂單'));
		}
		//更新訂單日誌
		$this->model('order')->update_order_status($rs['id'],'cancel');
		
		$who = '';
		if($rs['user_id']){
			$user = $this->model('user')->get_one($rs['user_id']);
			$who = $user['user'];
		}else{
			$address = $this->model('order')->address($rs['id']);
			if($address){
				$who = $address['fullname'];
			}
		}
		$log = array('order_id'=>$rs['id']);
		$log['addtime'] = $this->time;
		if($who){
			$log['who'] = $who;
		}
		$log['note'] = P_Lang('會員取消訂單');
		$log['user_id'] = $rs['user_id'];
		$this->model('order')->log_save($log);
		$this->success();
	}

	/**
	 * 確認收貨
	 * @引數 $id 訂單ID號
	 * @引數 $sn 訂單SN碼
	 * @引數 $passwd 訂單密碼
	**/
	public function received_f()
	{
		$rs = $this->_get_order();
		if(!$rs['status']){
			$this->error(P_Lang('訂單狀態異常，請聯絡客服'));
		}
		$array = array('shipping','paid');
		if(!in_array($rs['status'],$array)){
			$this->error(P_Lang('訂單僅限已付款或已發貨狀態下能確認收貨'));
		}
		$this->model('order')->update_order_status($rs['id'],'received');
		$who = '';
		if($rs['user_id']){
			$user = $this->model('user')->get_one($rs['user_id']);
			$who = $user['user'];
		}else{
			$address = $this->model('order')->address($rs['id']);
			if($address){
				$who = $address['fullname'];
			}
		}
		$log = array('order_id'=>$rs['id']);
		$log['addtime'] = $this->time;
		if($who){
			$log['who'] = $who;
		}
		$log['note'] = P_Lang('會員確認訂單已收');
		$log['user_id'] = $rs['user_id'];
		$this->model('order')->log_save($log);
		$this->success();
	}

	/**
	 * 獲取物流資訊
	 * @引數 $id 訂單ID號
	 * @引數 $sn 訂單SN碼
	 * @引數 $passwd 訂單密碼
	 * @引數 $sort 值為ASC或DESC
	**/
	public function logistics_f()
	{
		$rs = $this->_get_order();
		if(!$rs['status']){
			$this->error(P_Lang('訂單狀態異常，請聯絡客服'));
		}
		$array = array('create','unpaid');
		if(in_array($rs['status'],$array)){
			$this->error(P_Lang('僅限已支付的訂單才能檢視物流'));
		}
		$is_virtual = true;
		$plist = $this->model('order')->product_list($rs['id']);
		if(!$plist){
			$this->error(P_Lang('這是一張空白訂單，沒有產品，無法獲得物流資訊'));
		}
		foreach($plist as $key=>$value){
			if(!$value['is_virtual']){
				$is_virtual = false;
				break;
			}
		}
		if($is_virtual){
			$this->error(P_Lang('服務類訂單沒有物流資訊'));
		}
		$express_list = $this->model('order')->express_all($rs['id']);
		if(!$express_list){
			$this->error(P_Lang('訂單還未錄入物流資訊'));
		}
		foreach($express_list as $key=>$value){
			$url = $this->url('express','remote','id='.$value['id'],'api',true);
			if($this->config['self_connect_ip']){
				$this->lib('curl')->host_ip($this->config['self_connect_ip']);
			}
			$this->lib('curl')->connect_timeout(5);
			$this->lib('curl')->get_content($url);
		}
		$loglist = $this->model('order')->log_list($rs['id']);
		if(!$loglist){
			$this->error(P_Lang('訂單中找不到相關物流資訊，請聯絡客服'),$error_url);
		}
		foreach($loglist as $key=>$value){
			if(!$value['order_express_id']){
				continue;
			}
			$rslist[$value['order_express_id']]['rslist'][] = $value;
		}
		$sort = $this->get('sort');
		if($sort && strtoupper($sort) == 'DESC'){
			foreach($rslist as $key=>$value){
				krsort($value['rslist']);
				$rslist[$key] = $value;
			}
		}
		$this->success($rslist);
	}

	private function _get_order()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->session->val('user_id')){
				$this->error(P_Lang('非會員不能執行此操作'));
			}
			$rs = $this->model('order')->get_one($id);
			if(!$rs){
				$this->error(P_Lang('訂單不存在'));
			}
			if($rs['user_id'] != $this->session->val('user_id')){
				$this->error(P_Lang('您沒有許可權操作此訂單'));
			}
		}else{
			$sn = $this->get('sn');
			$passwd = $this->get('passwd');
			if(!$sn || !$passwd){
				$this->error(P_Lang('引數不完整，不能執行此操作'));
			}
			$rs = $this->model('order')->get_one($sn,'sn');
			if(!$rs){
				$this->error(P_Lang('訂單不存在'));
			}
			if($rs['passwd'] != $passwd){
				$this->error(P_Lang('訂單密碼不正確'));
			}
		}
		return $rs;
	}
}