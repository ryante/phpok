<?php
/**
 * 支付相關操作
 * @package phpok\www
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年08月02日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class payment_control extends phpok_control
{
	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::control();
		$this->model('url')->nocache(true);
	}

	/**
	 * 線上充值
	 * @引數 id 充值的目標標識，如果存在必須是字串或整型數字
	 * @引數 val 充值金額
	**/
	public function index_f()
	{
		$back = $this->get('back');
		if(!$back){
			$back = $this->lib('server')->referer();
		}
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('請先登入，非會員沒有此許可權'),$this->url('login','','_back='.rawurlencode($this->url('payment'))));
		}
		$id = $this->get('id');
		if($id){
			$typeid = intval($id) > 0 ? 'id' : 'identifier';
			$rs = $this->model('wealth')->get_one($id,$typeid);
			if(!$rs){
				$this->error(P_Lang('要支付的目標不存在，請檢查'),$back);
			}
			if(!$rs['status']){
				$this->error(P_Lang('財富：{title} 未啟用',array('title'=>$rs['title'])),$back);
			}
			if(!$rs['ifpay']){
				$this->error(P_Lang('{title}不支援線上充值',array('title'=>$rs['title'])),$back);
			}
			$this->assign('rs',$rs);
			$this->assign('id',$rs['id']);
		}else{
			$wealthlist = $this->model('wealth')->get_all(1);
			if(!$wealthlist){
				$this->error(P_Lang('沒有可以充值的財富方案'),$back);
			}
			$wlist = false;
			foreach($wealthlist as $key=>$value){
				if(!$value['ifpay']){
					continue;
				}
				if(!$wlist){
					$wlist = array();
				}
				$wlist[$value['identifier']] = $value;
			}
			$this->assign('rslist',$wlist);
		}
		$paylist = $this->model('payment')->get_all($this->site['id'],1,($this->is_mobile ? 1 : 0));
		$this->assign("paylist",$paylist);
		$price = $this->get('price','float');
		if($price){
			$this->assign('price',$price);
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'payment_index';
		}
		$this->view($tplfile);
	}

	/**
	 * 財富明細資訊
	 * @引數 id 給哪個財富ID充值
	 * @引數 val 充值的金額
	**/
	public function wealth_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('請先登入，非會員沒有此許可權'),$this->url('login','','_back='.rawurlencode($this->url('payment'))));
		}
		//建立充值頁面
		$id = $this->get('id','int');
		if(!id){
			$this->error(P_Lang('未指定要充值的財富ID'));
		}
		$rs = $this->model('wealth')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('財富方案不存在'));
		}
		if(!$rs['status']){
			$this->error(P_Lang('財富方案未啟用'));
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'payment_wealth';
		}
		$this->view($tplfile);
	}

	/**
	 * 建立支付連結
	 * @引數 id 訂單ID，僅限會員登入時有效
	 * @引數 sn 訂單編號，遊客購買有效
	 * @引數 type 支付鏈型別
	 * @引數 passwd 訂單密碼，遊客購買有效
	 * @引數 balance 財富支付，僅限會員登入時有效
	 * @引數 payment 支付方式 僅限財富支付不能完全抵消或是遊客購買時有效
	 * @更新時間 2016年08月16日
	**/
	public function create_f()
	{
		$userid = $this->session->val('user_id');
		$type = $this->get('type');
		if(!$type){
			$type = 'order';
		}
		if($type == 'order'){
			$this->_create_order();
		}
		$backurl = $this->lib('server')->referer();
		$wealth = $this->get('wealth','int');
		if(!$wealth){
			$this->error(P_Lang('未指定充值目標'),$backurl);
		}
		$price = $this->get('price','float');
		if(!$price){
			$this->error(P_Lang('未指定充值金額'),$backurl);
		}
		if($price < 1){
			$this->error(P_Lang('充值金額不能少於1元'),$backurl);
		}
		$payment = $this->get('payment','int');
		if(!$payment){
			$this->error(P_Lang('未指定支付方式'),$backurl);
		}
		$sn = uniqid('CZ');
		$array = array('type'=>$type,'price'=>$price,'currency_id'=>$this->site['currency_id'],'sn'=>$sn);
		$array['title'] = P_Lang('線上充值');
		$array['content'] = P_Lang('充值編號：{sn}',array('sn'=>$sn));
		$array['payment_id'] = $payment;
		$array['dateline'] = $this->time;
		$array['user_id'] = $userid;
		$array['status'] = 0;
		$tmp = array('goal'=>$wealth);
		$array['ext'] = serialize($tmp);
		$insert_id = $this->model('payment')->log_create($array);
		if(!$insert_id){
			$this->error(P_Lang('支付記錄建立失敗，請聯絡管理員'),$backurl);
		}
		$this->success(P_Lang('成功建立支付鏈，請稍候，即將為您跳轉支付頁面…'),$this->url('payment','submit','id='.$insert_id));
	}

	/**
	 * 建立訂單的支付鏈
	 * @引數 id 訂單ID，僅限會員登入時有效
	 * @引數 sn 訂單編號，遊客購買有效
	 * @引數 passwd 訂單密碼，遊客購買有效
	 * @引數 payment 支付方式 僅限財富支付不能完全抵消或是遊客購買時有效
	 * @返回 
	 * @更新時間 
	**/
	private function _create_order()
	{
		$userid = $this->session->val('user_id');
		if($userid){
			$id = $this->get('id','int');
			if(!$id){
				$this->error(P_Lang('未指定訂單號ID'));
			}
			$rs = $this->model('order')->get_one($id);
			$order_url = $this->url('order','info','id='.$id);
			$error_url = $this->url('order');
		}else{
			$sn = $this->get('sn');
			if(!$sn){
				$this->error(P_Lang('未指定定單編號'));
			}
			$passwd = $this->get('passwd');
			if(!$passwd){
				$this->error(P_Lang('訂單密碼不能為空'));
			}
			$rs = $this->model('order')->get_one_from_sn($sn);
			if(!$rs){
				$this->error(P_Lang('訂單資訊不存在'));
			}
			if($rs['passwd'] != $passwd){
				$this->error(P_Lang('訂單許可權驗證不通過'));
			}
			$order_url = $this->url('order','info','sn='.$sn.'&passwd='.$passwd);
			$error_url = $this->config['url'];
		}
		if($this->model('order')->check_payment_is_end($rs['id'])){
			$this->error(P_Lang('訂單已支付過，不能重複操作'),$order_url);
		}
		//若積分抵扣完全能滿足支付，則跳過支付
		if($userid && $this->integral_minus($rs,true)){
			//如果積分超出
			$this->integral_minus($rs);
			$array = array('order_id'=>$rs['id'],'payment_id'=>0);
			$array['title'] = P_Lang('積分抵扣支付');
			$array['price'] = 0;
			$array['startdate'] = $this->time;
			$array['dateline'] = $this->time;
			$array['ext'] = serialize(array('備註'=>'積分抵現完全支付'));
			$this->model('order')->save_payment($array);
			//登記支付鏈
			$array = array('type'=>'order','price'=>'0.00','currency_id'=>$rs['currency_id'],'sn'=>$rs['sn']);
			$array['content'] = $array['title'] = P_Lang('訂單：{sn}',array('sn'=>$rs['sn']));
			$array['payment_id'] = 0;
			$array['dateline'] = $this->time;
			$array['user_id'] = $this->session->val('user_id');
			$array['status'] = 1;
			$chk = $this->model('payment')->log_check($rs['sn'],'order');
			if($chk){
				if(!$chk['status']){
					$this->model('payment')->log_update($array,$chk['id']);
				}
				$this->model('order')->update_order_status($rs['id'],'paid');
				$this->success(P_Lang('訂單{sn}支付成功',array('sn'=>$rs['sn'])),$order_url);
			}
			$this->model('payment')->log_create($array);
			$this->model('order')->update_order_status($rs['id'],'paid');
			$this->success(P_Lang('訂單{rs}支付成功',array('sn'=>$rs['sn'])),$order_url);
		}
		if($userid){
			$this->integral_minus($rs);
			$rs = $this->model('order')->get_one($id);
		}
		$payment = $this->get('payment');
		if(!$payment){
			$this->error(P_Lang('未指定支付方式'),$error_url);
		}
		if(is_numeric($payment)){
			$payment = intval($payment);
			$payment_rs = $this->model('payment')->get_one($payment);
			$currency_id = $payment_rs['currency'] ? $payment_rs['currency']['id'] : $rs['currency_id'];
		}else{
			if(!$userid){
				$this->error(P_Lang('非會員不支援餘額支付功能'));
			}
			$payment_rs = $this->model('wealth')->get_one($payment,'identifier');
		}
		//訂單未支付完成建立生成連結
		$price_paid = $this->model('order')->paid_price($rs['id']);
		$price = $rs['price'] - $price_paid;
		if(!is_numeric($payment)){
			//檢測餘額是否充足
			$my_integral = $this->model('wealth')->get_val($userid,$payment_rs['id']);
			$my_price = round($my_integral*$payment_rs['cash_ratio']/100,$payment_rs['dnum']);
			if(floatval($my_price) < floatval($price)){
				$this->error(P_Lang('您的餘額不足，請先充值或使用其他方式支付'),$error_url);
			}
		}
		
		$array = array('type'=>'order','price'=>price_format_val($price,$rs['currency_id'],$currency_id),'currency_id'=>$currency_id,'sn'=>$rs['sn']);
		$array['content'] = $array['title'] = P_Lang('訂單：{sn}',array('sn'=>$rs['sn']));
		$array['payment_id'] = $payment;
		$array['dateline'] = $this->time;
		$array['user_id'] = $this->session->val('user_id');
		if(!is_numeric($payment)){
			$array['ext'] = serialize(array('wealth'=>$payment_rs['id']));
		}
		//刪除未完成的支付日誌
		$this->model('payment')->log_delete_notstatus($rs['sn'],'order');
		$insert_id = $this->model('payment')->log_create($array);
		if(!$insert_id){
			$this->error(P_Lang('支付記錄建立失敗，請聯絡管理員'),$order_url);
		}
		$this->model('order')->update_order_status($rs['id'],'unpaid');
		//增加order_payment
		$array = array('order_id'=>$rs['id'],'payment_id'=>$payment);
		$array['title'] = $payment_rs['title'];
		$array['price'] = price_format_val($price,$rs['currency_id'],$currency_id);
		$array['currency_id'] = $currency_id;
		$array['startdate'] = $this->time;
		$this->model('order')->delete_not_end_order($rs['id']);
		$this->model('order')->save_payment($array);
		$this->success(P_Lang('成功建立支付鏈，請稍候，即將為您跳轉支付頁面…'),$this->url('payment','submit','id='.$insert_id));
	}

	/**
	 * 積分抵扣
	 * @引數 integral 積分ID，陣列，可疊加使用
	 * @引數 integral_val 要處理的積分數值
	 * @引數 $order 訂單資訊，陣列
	 * @引數 $check 是否檢測，為true時僅作檢測，為false時表示直接扣除
	 * @返回 true/false
	 * @更新時間 
	**/
	private function integral_minus($order,$check=false)
	{
		if(!$this->session->val('user_id')){
			return false;
		}
		$integral_val = $this->get('integral_val','int');
		if(!$integral_val){
			return false;
		}
		$wlist = $this->model('order')->balance($this->session->val('user_id'));
		if(!$wlist){
			return false;
		}
		if(!$wlist['integral']){
			return false;
		}
		$wlist = $wlist['integral'];
		$totalprice = price_format_val($order['price'],$order['currency_id'],$order['currency_id']);
		$tmpprice = 0;
		foreach($integral_val as $key=>$value){
			if(!$value || !intval($value) || !$key || !intval($key) || !$wlist[$key]){
				continue;
			}
			if($value > $wlist[$key]['val']){
				continue;
			}
			$useprice = round($value*$wlist[$key]['cash_ratio']/100,$wlist[$key]['dnum']);
			if($check){
				$tmpprice += price_format_val($useprice,$order['currency_id'],$order['currency_id']);
			}else{
				//$tmporder = array('id'=>$order['id'],'sn'=>$order['sn'],'price'=>$totalprice,'currency_id'=>$order['currency_id']);
				$tmp = $this->integral_order_payment($order,$wlist[$key],$value);
				if($tmp && $tmp['status'] && $tmp['price']){
					$totalprice = $tmp['price'];
					//更新訂單總額
					$data = array('price'=>$tmp['price']);
					$this->model('order')->save($data,$order['id']);
				}
			}
		}
		if($check){
			if($tmpprice >= $totalprice){
				return true;
			}
			return false;
		}
		return false;
	}

	/**
	 * 積分抵扣
	 * @引數 $order 訂單資訊，陣列
	 * @引數 $info 使用者積分資訊
	 * @引數 $integral 積分
	 * @返回 陣列 或 false
	 * @更新時間 2016年11月27日
	**/
	private function integral_order_payment($order,$info,$integral=0)
	{
		if(!$this->session->val('user_id')){
			return false;
		}
		if(!$order || !$info || !$integral){
			return false;
		}
		$totalprice = price_format_val($order['price'],$order['currency_id'],$order['currency_id']);
		$price = round($integral*$info['cash_ratio']/100,$info['dnum']);
		$balance = price_format_val($price,$order['currency_id'],$order['currency_id']);
		$surplus = $balance >= $totalprice ? 0 : floatval($totalprice - $balance);
		//扣除會員積分
		$savelogs = array('wid'=>$info['id'],'goal_id'=>$this->session->val('user_id'),'mid'=>0,'val'=>'-'.$integral);
		$savelogs['appid'] = $this->app_id;
		$savelogs['dateline'] = $this->time;
		$savelogs['user_id'] = $this->session->val('user_id');
		$savelogs['ctrlid'] = 'payment';
		$savelogs['funcid'] = 'create';
		$savelogs['url'] = 'index.php';
		$savelogs['note'] = P_Lang('財富（{title}）抵現',array('title'=>$info['title']));
		$savelogs['status'] = 1;
		$savelogs['val'] = -$integral;
		$data = array('wid'=>$info['id'],'uid'=>$this->session->val('user_id'),'lasttime'=>$this->time);
		$data['val'] = intval($info['val'] - $integral);
		//剩餘積分
		if($surplus){
			$paid_price = price_format($price,$order['currency_id'],$order['currency_id']);
		}else{
			$paid_price = price_format($order['price'],$order['currency_id'],$order['currency_id']);
		}
		$this->model('wealth')->save_log($savelogs);
		$this->model('wealth')->save_info($data);
		//建立訂單日誌，記錄支付資訊
		$tmparray = array('price'=>$paid_price,'payment'=>$info['title'],'integral'=>$integral,'unit'=>$info['unit']);
		$note = P_Lang('使用{payment}抵扣{price}，共消耗{payment}{integral}{unit}',$tmparray);
		$who = $this->session->val('user_name');
		$log = array('order_id'=>$order['id'],'addtime'=>$this->time,'who'=>$who,'note'=>$note);
		$this->model('order')->log_save($log);
		$this->model('order')->integral_discount($order['id'],$balance);
		return array('price'=>$surplus,'status'=>true);
	}

	/**
	 * 提交支付
	 * @引數 id 支付ID號
	**/
	public function submit_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定支付訂單ID'));
		}
		$log = $this->model('payment')->log_one($id);
		if(!$log){
			$this->error(P_Lang('訂單資訊不存在'));
		}
		if($log['status']){
			$this->error(P_Lang('訂單已支付過了，不能再次執行'));
		}
		if($log['type'] == 'order'){
			$orderinfo = $this->model('order')->get_one($log['sn'],'sn');
			$paid_price = $this->model('order')->paid_price($orderinfo['id']);
			$unpaid_price = $this->model('order')->unpaid_price($orderinfo['id']);
			$this->assign('paid_price',$paid_price);
			$this->assign('unpaid_price',$unpaid_price);
			$this->assign('orderinfo',$orderinfo);
		}
		
		if($log['payment_id'] && is_numeric($log['payment_id'])){
			$payment_rs = $this->model('payment')->get_one($log['payment_id']);
			if(!$payment_rs){
				$this->error(P_Lang('支付方式不存在'));
			}
			if(!$payment_rs['status']){
				$this->error(P_Lang('支付方式未啟用'));
			}
			$file = $this->dir_root.'gateway/payment/'.$payment_rs['code'].'/submit.php';
			if(!file_exists($file)){
				$tmpfile = str_replace($this->dir_root,'',$file);
				$this->error(P_Lang('支付介面異常，檔案{file}不存在',array('file'=>$tmpfile)));
			}
			include($file);
			$name = $payment_rs['code']."_submit";
			$payment = new $name($log,$payment_rs);
			$payment->submit();
			exit;
		}
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非會員不支援餘額支付，請登入再執行此操作'));
		}
		if(!$log['payment_id']){
			$ext = $log['ext'] ? unserialize($log['ext']) : false;
			if(!$ext || !$ext['wealth'] || !is_numeric($ext['wealth'])){
				$this->error(P_Lang('支付資訊異常，無法找到支付方法，請聯絡管理員'));
			}
			$wealth = $this->model('wealth')->get_one($ext['wealth']);
			$log['payment_id'] = $wealth['identifier'];
			if(!$log['payment_id']){
				$this->error(P_Lang('支付資訊異常，無法找到支付方法，請聯絡管理員'));
			}
		}
		$wealth = $this->model('wealth')->get_one($log['payment_id'],'identifier');
		//取得要扣除的積分
		$integral = round(($log['price'] * 100)/$wealth['cash_ratio'],$wealth['dnum']);
		$my_integral = $this->model('wealth')->get_val($this->session->val('user_id'),$wealth['id']);
		if(!$my_integral){
			$this->error(P_Lang('您的餘額為空，請先充值'),$this->url('payment','index','id='.$wealth['id']));
		}
		if(floatval($my_integral) < floatval($integral)){
			$this->error(P_Lang('您的餘額不足，請先充值'),$this->url('payment','index','id='.$wealth['id']));
		}
		//扣除會員積分
		$savelogs = array('wid'=>$wealth['id'],'goal_id'=>$this->session->val('user_id'),'mid'=>0,'val'=>'-'.$integral);
		$savelogs['appid'] = $this->app_id;
		$savelogs['dateline'] = $this->time;
		$savelogs['user_id'] = $this->session->val('user_id');
		$savelogs['ctrlid'] = 'payment';
		$savelogs['funcid'] = 'create';
		$savelogs['url'] = 'index.php';
		$savelogs['note'] = P_Lang('支付訂單：{sn}',array('sn'=>$log['sn']));
		$savelogs['status'] = 1;
		$savelogs['val'] = -$integral;
		$data = array('wid'=>$wealth['id'],'uid'=>$this->session->val('user_id'),'lasttime'=>$this->time);
		$data['val'] = intval($my_integral - $integral);
		$this->model('wealth')->save_log($savelogs);
		$this->model('wealth')->save_info($data);
		//更新payment_log日誌
		$array = array('status'=>1);
		$array['ext'] = serialize(array($wealth['title']=>$integral));
		$this->model('payment')->log_update($array,$log['id']);
		//更新訂單狀態
		if($log['type'] == 'order'){
			$order = $this->model('order')->get_one_from_sn($log['sn']);
			$this->model('order')->update_order_status($order['id'],'paid',P_Lang('支付完成'));
			$order_payment = $this->model('order')->order_payment($order['id'],$log['payment_id']);
			if($order_payment){
				$array = array('dateline'=>$this->time);
				$array['ext'] = serialize(array($wealth['title']=>$integral));
				$this->model('order')->save_payment($array,$order_payment['id']);
			}
		}
		$price = price_format($log['price'],$this->site['currency_id']);
		$this->success(P_Lang('支付操作完成，您共支付：{price}',array('price'=>$price)),$this->url('payment','show','id='.$id));
	}

	public function notice_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('執行異常，請檢查，缺少引數ID'));
		}
		$rs = $this->model('payment')->log_one($id);
		if(!$rs){
			$this->error(P_Lang('訂單資訊不存在'),$this->url('index'));
		}
		if($rs['type'] == 'order'){
			$order = $this->model('order')->get_one_from_sn($rs['sn']);
			$url = $this->url('order','info','sn='.$rs['sn'].'&passwd='.$order['passwd']);
		}elseif($rs['type'] == 'recharge'){
			$url = $this->url('usercp','wealth','sn='.$rs['sn']);
		}else{
			$url = $this->url('payment','show','id='.$id);
		}
		//同步通知
		if($rs['status']){
			$this->success(P_Lang('您的訂單付款成功，請稍候…'),$url);
		}
		$payment_rs = $this->model('payment')->get_one($rs['payment_id']);
		$file = $this->dir_root.'gateway/payment/'.$payment_rs['code'].'/notice.php';
		if(!file_exists($file)){
			$tmpfile = str_replace($this->dir_root,'',$file);
			$this->error(P_Lang('支付介面異常，檔案{file}不存在',array('file'=>$tmpfile)));
		}
		include($file);
		$name = $payment_rs['code'].'_notice';
		$cls = new $name($rs,$payment_rs);
		$cls->submit();
		$this->success(P_Lang('您的訂單付款成功，請稍候…'),$url);
	}

	//非同步通知方案
	//考慮到非同步通知存在讀不到$_SESSION問題，使用sn和pass組合
	public function notify_f()
	{
		$sn = $this->get('sn');
		if(!$sn){
			exit('error');
		}
		if(strpos($sn,'-') !== false){
			$tmp = explode("-",$sn);
			$sn = $tmp[0];
			$rs = $this->model('payment')->log_one($tmp[1]);
		}else{
			$rs = $this->model('payment')->log_check_notstatus($sn);
		}
		if(!$rs){
			exit('error');
		}
		$payment_rs = $this->model('payment')->get_one($rs['payment_id']);
		if(!$payment_rs){
			exit('error');
		}
		if(!$payment_rs['status']){
			exit('error');
		}
		$file = $this->dir_root.'gateway/payment/'.$payment_rs['code'].'/notify.php';
		if(!file_exists($file)){
			exit('error');
		}
		include($file);
		$name = $payment_rs['code'].'_notify';
		$cls = new $name($rs,$payment_rs);
		$cls->submit();
		exit('success');
	}

	public function show_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('payment')->log_one($id);
		if(!$rs){
			$this->error(P_Lang('資料不存在，請檢查'));
		}
		if($rs['type'] == 'order'){
			if($this->session->val('user_id')){
				$order = $this->model('order')->get_one_from_sn($rs['sn']);
				$url = $this->url('order','info','id='.$order['id']);
				$this->_location($url);
			}else{
				$this->success(P_Lang('訂單{sn}支付完成',array('sn'=>$rs['sn'])),$this->url);
			}
		}
		if($this->session->val('user_id')){
			$this->_location($this->url('usercp','wealth'));
		}else{
			$this->_location($this->url);
		}
	}
}
