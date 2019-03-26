<?php
/**
 * 訂單資訊管理
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年08月01日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class order_control extends phpok_control
{
	/**
	 * 購物車ID，該ID將貫穿整個購物過程
	**/
	private $cart_id = 0;

	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::control();
		$this->cart_id = $this->model('cart')->cart_id($this->session->sessid(),$this->session->val('user_id'));
	}

	/**
	 * 取得訂單列表
	 * @引數 pageid 頁碼ID
	**/
	public function index_f()
	{
		$backurl = $this->url('order');
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您還未登入，請先登入'),$this->url('login','','_back='.rawurlencode($backurl)));
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 20;
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		$condition = "user_id='".$this->session->val('user_id')."'";
		$pageurl = $this->url('order');
		$status = $this->get('status');
		if($status){
			$tmp = explode(",",$status);
			$condition .= " AND status IN('".implode("','",$tmp)."')";
			$pageurl = $this->url('order','','status='.rawurlencode($status));
			$this->assign('status',$status);
		}
		$total = $this->model('order')->get_count($condition);
		if($total){
			$rslist = $this->model('order')->get_list($condition,$offset,$psize);
			foreach ($rslist as $key => $value){
			    $product = $this->model('order')->product_list($value['id']);
			    $rslist[$key]['product'] = $product;
			    $unpaid_price = $this->model('order')->unpaid_price($value['id']);
		        $paid_price = $this->model('order')->paid_price($value['id']);
		        if($unpaid_price > 0){
			        if($paid_price>0){
				        $rslist[$key]['pay_info'] = '部分支付';
			        }else{
				        $rslist[$key]['pay_info'] = '未支付';
			        }
		        }else{
			        $rslist[$key]['pay_info'] = '已支付';
		        }
            }
			$this->assign('rslist',$rslist);
			$this->assign('pageid',$pageid);
			$this->assign('pageurl',$pageurl);
			$this->assign('total',$total);
			$this->assign('psize',$psize);
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'order_list';
		}
		$this->view($tplfile);
	}

	/**
	 * 檢視訂單資訊
	 * @引數 back 返回上一級，未指定時，會員返回HTTP_REFERER或訂單列表，遊客返回HTTP_REFERER或首頁
	 * @引數 id 訂單ID號，僅限已登入會員使用
	 * @引數 sn 訂單編號，如果訂單ID為空時，使用SN來查詢
	 * @引數 passwd 訂單密碼，僅限遊客查閱時需要使用
	**/
	public function info_f()
	{
		$back = $this->get('back');
		if(!$back){
			$back = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : ($this->session->val('user_id') ? $this->url('order') : $this->url);
		}
		$order = $this->_order();
		if(!$order['status']){
			$this->error($order['error'],$back);
		}
		$rs = $order['info'];
		unset($order);
		$status_list = $this->model('order')->status_list();
		$unpaid_price = $this->model('order')->unpaid_price($rs['id']);
		$paid_price = $this->model('order')->paid_price($rs['id']);
		if($unpaid_price > 0){
			if($paid_price>0){
				$rs['pay_info'] = '部分支付';
			}else{
				$rs['pay_info'] = '未支付';
			}
		}else{
			$rs['pay_info'] = '已支付';
		}
		$rs['status_info'] = ($status_list && $status_list[$rs['status']]) ? $status_list[$rs['status']] : $rs['status'];
		$this->assign('rs',$rs);
		$address = $this->model('order')->address($rs['id']);
		$this->assign('address',$address);
		$rslist = $this->model('order')->product_list($rs['id']);
		$this->assign('rslist',$rslist);
		//獲取發票資訊
		$invoice = $this->model('order')->invoice($rs['id']);
		$this->assign('invoice',$invoice);
		//獲取價格
		$price_tpl_list = $this->model('site')->price_status_all();
		$order_price = $this->model('order')->order_price($rs['id']);
		if($price_tpl_list && $order_price){
			$pricelist = array();
			foreach($price_tpl_list as $key=>$value){
				$tmpval = floatval($order_price[$key]);
				if(!$value['status'] || !$tmpval){
					continue;
				}
				$tmp = array('val'=>$tmpval);
				$tmp['price'] = price_format($order_price[$key],$rs['currency_id']);
				$tmp['title'] = $value['title'];
				$pricelist[$key] = $tmp;
			}
			$this->assign('pricelist',$pricelist);
		}
		if($this->model('order')->check_payment_is_end($rs['id'])){
			$this->assign('pay_end',true);
		}
		$loglist = $this->model('order')->log_list($rs['id']);
		$this->assign('loglist',$loglist);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'order_info';
		}
		$this->view($tplfile);
	}

	/**
	 * 訂單支付頁
	 * @引數 
	 * @返回 
	 * @更新時間 
	**/
	public function payment_f()
	{
		$back = $this->get('back');
		if(!$back){
			$back = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : ($this->session->val('user_id') ? $this->url('order') : $this->url);
		}
		$order = $this->_order();
		if(!$order['status']){
			$this->error($order['error'],$back);
		}
		$rs = $order['info'];
		$this->assign('rs',$rs);
		unset($order);
		if($this->model('order')->check_payment_is_end($rs['id'])){
			$url = $this->session->val('user_id') ? $this->url('order','info','id='.$rs['id']) : $this->url('order','info','sn='.$rs['sn'].'&passwd='.$rs['passwd']);
			$this->success(P_Lang('您的訂單 {sn} 已經支付完成，無需再支付',array('sn'=>$rs['sn'])),$url);
		}
		$mobile = $this->is_mobile ? 1 : 0;
		$paylist = $this->model('payment')->get_all($this->site['id'],1,$mobile);
		$this->assign("paylist",$paylist);
		$this->balance();
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'order_payment';
		}
		$price_paid = $this->model('order')->paid_price($rs['id']);
		$this->assign('price_paid',$price_paid);
		$price_unpaid = $this->model('order')->unpaid_price($rs['id']);
		$this->assign('price_unpaid',$price_unpaid);
		$this->assign('rs',$rs);
		$this->view($tplfile);
	}

	/**
	 * 獲取訂單資訊，無論成功或是失敗均返回資料或布林值
	 * @引數 id 訂單ID號
	 * @引數 sn 訂單編號
	 * @引數 passwd 訂單密碼
	**/
	private function _order()
	{
		$userid = $this->session->val('user_id');
		if($userid){
			$id = $this->get('id','int');
			if(!$id){
				$sn = $this->get('sn');
				if(!$sn){
					return array('status'=>false,'error'=>P_Lang('未指定訂單ID或訂單號'));
				}
				$rs = $this->model('order')->get_one_from_sn($sn);
			}else{
				$rs = $this->model('order')->get_one($id);
			}
			if(!$rs){
				return array('status'=>false,'error'=>P_Lang('訂單資訊不存在'));
			}
			if($rs['user_id'] != $userid){
				$passwd = $this->get('passwd');
				if(!$passwd || ($passwd && $passwd != $rs['passwd'])){
					return array('status'=>false,'error'=>P_Lang('您沒有許可權檢視此訂單'));
				}
			}
		}else{
			$sn = $this->get('sn');
			$passwd = $this->get('passwd');
			if(!$sn || !$passwd){
				return array('status'=>false,'error'=>P_Lang('引數不完整'));
			}
			$rs = $this->model('order')->get_one_from_sn($sn);
			if(!$rs){
				return array('status'=>false,'error'=>P_Lang('訂單資訊不存在'));
			}
			if($passwd != $rs['passwd']){
				return array('status'=>false,'error'=>P_Lang('您沒有許可權檢視此訂單'));
			}
		}
		return array('status'=>true,'info'=>$rs);
	}

	/**
	 * 餘額支付，無餘額不使用
	**/
	private function balance()
	{
		if(!$this->session->val('user_id')){
			return false;
		}
		$wlist = $this->model('order')->balance($this->session->val('user_id'));
		if(!$wlist){
			return false;
		}
		if($wlist['balance']){
			$this->assign('balance',$wlist['balance']);
		}
		if($wlist['integral']){
			$this->assign('integral',$wlist['integral']);
		}
		return true;
	}

	/**
	 * 訂單評論
	 * @引數 $id 訂單ID
	 * @更新時間 
	**/
	public function comment_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('訂單ID不能為空'),$this->url('order'));
		}
		$userid = $this->session->val('user_id');
		if(!$userid){
			$this->error(P_Lang('非會員賬號不能執行此操作'),$this->url('login','index','_back='.rawurlencode($this->url('order','comment','id='.$id))));
		}
		$backurl = $this->lib('server')->referer();
		if(!$backurl){
			$backurl = $this->url('order');
		}
		$rs = $this->model('order')->get_one($id);
		if($rs['user_id'] != $userid){
			$this->error(P_Lang('您沒有許可權評論此訂單資訊'),$backurl);
		}
		if(!$rs['endtime']){
			$this->error(P_Lang('訂單未結束，暫不支援評論'),$backurl);
		}
		if($rs['status'] == 'cancel'){
			$this->error(P_Lang('訂單已取消，不支援評論'),$backurl);
		}
		$plist = $this->model('order')->product_list($id);
		if(!$plist){
			$this->error(P_Lang('訂單中無法找到相關產品資訊'),$backurl);
		}
		$rslist = false;
		foreach($plist as $key=>$value){
			if(!$value['tid']){
				continue;
			}
			if(!$rslist){
				$rslist = array();
			}
			$condition = "tid='".$value['tid']."' AND uid='".$userid."' AND order_id='".$id."'";
			$commentlist = $this->model('reply')->get_list($condition,0,100,"","addtime ASC,id ASC");
			if($commentlist){
				$value['comment'] = $commentlist;
			}
			$rslist[] = $value;
		}
		if(!$rslist){
			$this->error(P_Lang('訂單中沒有找到可以關聯的產品資訊，所以不支援評論'),$backurl);
		}
		$this->assign('rslist',$rslist);
		$this->assign('rs',$rs);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'order_comment';
		}
		$this->view($tplfile);
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
		if($this->session->val('user_id')){
			$error_url = $this->url('order','info','id='.$rs['id']);
		}else{
			$error_url = $this->url('order','info','sn='.$rs['sn'].'&passwd='.$rs['passwd']);
		}
		
		if(!$rs['status']){
			$this->error(P_Lang('訂單狀態異常，請聯絡客服'),$error_url);
		}
		$array = array('create','unpaid');
		if(in_array($rs['status'],$array)){
			$this->error(P_Lang('僅限已支付的訂單才能檢視物流'),$error_url);
		}
		$is_virtual = true;
		$plist = $this->model('order')->product_list($rs['id']);
		if(!$plist){
			$this->error(P_Lang('這是一張空白訂單，沒有產品，無法獲得物流資訊'),$error_url);
		}
		foreach($plist as $key=>$value){
			if(!$value['is_virtual']){
				$is_virtual = false;
				break;
			}
		}
		if($is_virtual){
			$this->error(P_Lang('服務類訂單沒有物流資訊'),$error_url);
		}
		$express_list = $this->model('order')->express_all($rs['id']);
		if(!$express_list){
			$this->error(P_Lang('訂單還未錄入物流資訊'),$error_url);
		}
		//更新遠端連結
		$rslist = array();
		foreach($express_list as $key=>$value){
			$value['express_info'] = $this->model('express')->get_one($value['express_id']);
			$url = $this->url('express','remote','id='.$value['id'],'api',true);
			if($this->config['self_connect_ip']){
				$this->lib('curl')->host_ip($this->config['self_connect_ip']);
			}
			$this->lib('curl')->connect_timeout(5);
			$this->lib('curl')->get_content($url);
			$rslist[$value['id']] = $value;
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
		$this->assign('rslist',$rslist);
		$this->assign('rs',$rs);
		$this->view('order_logistics');
	}
}