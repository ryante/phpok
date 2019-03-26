<?php
/**
 * 訂單管理，編輯和刪除等相關操作
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年10月03日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class order_control extends phpok_control
{
	/**
	 * 許可權
	**/
	private $popedom;

	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("order");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 顯示訂單列表
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$offset = ($pageid-1)*$psize;
		$pageurl = $this->url('order');
		$statuslist = $this->model('order')->status_list();
		$this->assign('statuslist',$statuslist);

		$condition = "1=1";
		$status = $this->get("status");
		if($status){
			$condition .= " AND o.status='".$status."'";
			$pageurl .= "&status=".rawurlencode($status);
			$this->assign('status',$status);
		}
		$keytype = $this->get('keytype');
		if(!$keytype){
			$keytype = 'sn';
		}
		$keywords = $this->get('keywords');
		if($keywords){
			if($keytype == 'sn' || $keytype == 'email'){
				$condition .= " AND o.".$keytype." LIKE '%".$keywords."%'";
			}elseif($keytype == 'user'){
				$condition .= " AND u.user LIKE '%".$keywords."%'";
			}elseif($keytype == 'protitle'){
				$condition .= " AND o.id IN(SELECT order_id FROM ".$this->db->prefix."order_product WHERE title LIKE '%".$keywords."%')";
			}
			$pageurl .= "&keywords=".rawurlencode($keywords)."&keytype=".$keytype;
			$this->assign('keytype',$keytype);
			$this->assign('keywords',$keywords);
		}
		$price_min = $this->get('price_min');
		if($price_min != ''){
			$condition .= " AND o.price>=".$price_min;
			$pageurl .= "&price_min=".rawurlencode($price_min);
			$this->assign('price_min',$price_min);
		}
		$price_max = $this->get('price_max');
		if($price_max != ''){
			$condition .= " AND o.price<=".$price_max;
			$pageurl .= "&price_max=".rawurlencode($price_max);
			$this->assign('price_max',$price_max);
		}
		$date_start = $this->get('date_start');
		if($date_start){
			$condition .= " AND o.addtime>=".strtotime($date_start);
			$pageurl .= "&date_start=".rawurlencode($date_start);
			$this->assign('date_start',$date_start);
		}
		$date_stop = $this->get('date_stop');
		if($date_stop){
			$condition .= " AND o.addtime<=".strtotime($date_stop);
			$pageurl .= "&date_stop=".rawurlencode($date_stop);
			$this->assign('date_stop',$date_stop);
		}
		$paytype = $this->get('paytype','int');
		if($paytype){
			$condition .= " AND o.id IN(SELECT order_id FROM ".$this->db->prefix."order_payment WHERE payment_id='".$paytype."') ";
			$pageurl .= "&paytype=".$paytype;
			$this->assign('paytype',$paytype);
		}
		$total = $this->model('order')->get_count($condition);
		if(!$total){
			$this->view("order_list");
		}
		$paylist = $this->model('payment')->get_all('','id');
		if(!$paylist){
			$paylist = array();
		}
		$tmplist = array();
		foreach($paylist as $key=>$value){
			if(!$tmplist[$value['gid']]){
				$tmplist[$value['gid']] = array('title'=>$value['group_title'],'wap'=>$value['group_wap'],'rslist'=>array($value['id']=>$value));
			}else{
				$tmplist[$value['gid']]['rslist'][$value['id']] = $value;
			}
		}
		$this->assign('paylist',$tmplist);
		$rslist = $this->model('order')->get_list($condition,$offset,$psize);
		if(!$rslist){
			$rslist = array();
		}
		foreach($rslist as $key=>$value){
			if(!$value['status_title']){
				$value['status_title'] = $statuslist[$value['status']] ? $statuslist[$value['status']] : $value['status'];
				$rslist[$key] = $value;
			}
		}
		$this->assign('rslist',$rslist);
		$this->assign('total',$total);
		$string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上一頁').'&next='.P_Lang('下一頁').'&last='.P_Lang('尾頁').'&half=5';
		$string.= '&add='.P_Lang('數量：').'(total)/(psize)'.P_Lang('，').P_Lang('頁碼：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign('pagelist',$pagelist);
		$this->view("order_list");
	}

	/**
	 * 結束訂單操作
	 * @引數 id 訂單ID號
	 * @引數 act 動作，僅限cancel，stop，end
	 * @返回 JSON資料
	 * @更新時間 2016年10月04日
	**/
	public function end_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定訂單ID'));
		}
		if(!$this->popedom['end']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$rs = $this->model('order')->get_one($id);
		if($rs['status'] == 'stop'){
			$this->error(P_Lang('訂單已結束，不能執行此操作'));
		}
		if($rs['status'] == 'end'){
			$this->error(P_Lang('訂單已完成，不能重複執行了'));
		}
		if($rs['status'] == 'cancel'){
			$this->error(P_Lang('訂單已取消，不能執行此操作'));
		}
		$statuslist = $this->model('order')->status_list();
		$this->model('order')->update_order_status($id,$act,$statuslist[$act]);
		if($act == 'end'){
			$this->model('wealth')->order($id,P_Lang('訂單完成賺送積分'));
		}
		$this->success();
	}

	public function stop_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定訂單ID'));
		}
		if(!$this->popedom['cancel']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$rs = $this->model('order')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('訂單不存在'));
		}
		if($rs['status'] == 'stop'){
			$this->error(P_Lang('訂單已結束，不能重複執行了'));
		}
		if($rs['status'] == 'end'){
			$this->error(P_Lang('訂單已完成，不能執行此操作'));
		}
		if($rs['status'] == 'cancel'){
			$this->error(P_Lang('訂單已取消，不能執行此操作'));
		}
		$statuslist = $this->model('order')->status_list();
		$this->model('order')->update_order_status($id,$act,$statuslist[$act]);
		$this->success();
	}

	/**
	 * 取消訂單操作（如果已操作訂單完成，訂單結束後，將不能執行取消訂單）
	 * @引數 id 訂單ID號
	**/
	public function cancel_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定訂單ID'));
		}
		if(!$this->popedom['cancel']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$rs = $this->model('order')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('訂單不存在'));
		}
		if($rs['status'] == 'cancel'){
			$this->error(P_Lang('已執行取消訂單操作，不能重複執行了'));
		}
		if($rs['status'] == 'end'){
			$this->error(P_Lang('訂單已完成，不能執行取消操作'));
		}
		if($rs['status'] == 'stop'){
			$this->error(P_Lang('訂單已結束，不能執行取消操作'));
		}
		$note = $this->get('note');
		$this->model('order')->update_order_status($id,'cancel',$note);
		$this->success();
	}

	//物流維護管理
	public function express_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('order')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('訂單資訊不存在'));
		}
		$this->assign('rs',$rs);
		//判斷訂單是否需要物流
		$address = $this->model('order')->address($id);
		if(!$address){
			$this->error(P_Lang('該訂單未設定收貨地址，請先設定'));
		}
		$rslist = $this->model('order')->express_all($id);
		$loglist = $this->model('order')->log_list($id);
		if($rslist && $loglist){
			foreach($rslist as $key=>$value){
				foreach($loglist as $k=>$v){
					if($v['order_express_id'] == $value['id']){
						$rslist[$key]['invoicelist'][] = $v;
					}
				}
				$rslist[$key]['invoice_total'] = $rslist[$key]['invoicelist'] ? (count($rslist[$key]['invoicelist']) + 1) : 1;
			}
		}
		$this->assign('rslist',$rslist);
		$expresslist = $this->model('express')->get_all();
		$this->assign('expresslist',$expresslist);
		$this->view('order_express');
	}

	public function express_save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$express_id = $this->get('express_id','int');
		if(!$express_id){
			$this->error(P_Lang('未指定物流公司'));
		}
		$code = $this->get('code');
		if(!$code){
			$this->error(P_Lang('未填寫物流單號'));
		}
		$express = $this->model('express')->get_one($express_id);
		$array = array('order_id'=>$id,'express_id'=>$express_id,'code'=>$code,'addtime'=>$this->time);
		$array['title'] = $express['title'];
		$array['homepage'] = $express['homepage'];
		$array['company'] = $express['company'];
		$insert = $this->model('order')->express_save($array);
		if(!$insert){
			$this->error(P_Lang('寫入失敗'));
		}
		//增加訂單日誌
		$tip = P_Lang('您的訂單已經揀貨完畢，待出庫交付{title}，運單號為：{code}',array('title'=>$express['title'],'code'=>$code));
		$data = array('order_id'=>$id,'order_express_id'=>$insert,'note'=>$tip);
		$rs = $this->model('order')->get_one($id);
		if($rs['status'] != 'shipping'){
			$this->model('order')->log_save($data);
			$this->model('order')->update_order_status($id,'shipping');
		}
		$this->success();
	}

	public function express_delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('order')->express_one($id);
		if(!$rs){
			$this->error(P_Lang('物流資訊不存在'));
		}
		$this->model('order')->express_delete($id);
		$this->success();
	}
	//刪除訂單操作
	public function delete_f()
	{
		$id = $this->get('id');
		if(!$this->popedom['delete']){
			$this->json(P_Lang('您沒有許可權執行此操作'));
		}
		//刪除訂單
		if(!$id) $this->json(P_Lang('未指定訂單ID號'));
		$this->model('order')->delete($id);
		$this->json(P_Lang('刪除成功'),true);
	}

	//檢視訂單資訊
	public function info_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}

		$rs = $this->model('order')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('訂單資訊不存在'));
		}
		//訂單狀態
		$statuslist = $this->model('order')->status_list();
		if(!$rs['status_title']){
			$rs['status_title'] = $statuslist[$rs['status']] ? $statuslist[$rs['status']] : $rs['status'];
		}
		$this->assign('rs',$rs);
		$this->assign('statuslist',$statuslist);
		//取得訂單的地址
		$address = $this->model('order')->address($id);
		$this->assign('shipping',$address);
		//訂單下的產品列表
		$rslist = $this->model('order')->product_list($id);
		$this->assign('rslist',$rslist);
		//取得支付記錄
		$paylist = $this->model('order')->payment_all($id);
		$this->assign('paylist',$paylist);
		$paid_price = $this->model('order')->paid_price($id);
		$this->assign('paid_price',$paid_price);
		$unpaid_price = $this->model('order')->unpaid_price($id);
		$this->assign('unpaid_price',$unpaid_price);
		if($rs['user_id']){
			$user = $this->model('user')->get_one($rs['user_id']);
			$this->assign('user',$user);
		}
		$loglist = $this->model('order')->log_list($id);
		$this->assign('loglist',$loglist);

		$this->view("order_info");
	}

	public function set_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			if(!$this->popedom['add']){
				error(P_Lang('您沒有許可權執行此操作'),$this->url('order'),'error');
			}
			$rs = array('status'=>'create');
		}else{
			if(!$this->popedom['modify']){
				error(P_Lang('您沒有許可權執行此操作'),$this->url('order'),'error');
			}
			$this->assign('id',$id);
			$rs = $this->model('order')->get_one($id);
			$rs['price'] = price_format_val($rs['price'],$rs['currency_id']);
			$address = $this->model('order')->address($id);
			$this->assign('shipping',$address);
		}
		$this->assign('rs',$rs);
		$site_rs = $this->model('site')->get_one($_SESSION['admin_site_id']);
		$this->assign("site_rs",$site_rs);

		//讀取網站貨幣
		$currency_list = $this->model('currency')->get_list();
		$this->assign("currency_list",$currency_list);
		//付款方式
		$paylist = $this->model('payment')->opt_all($this->session->val('admin_site_id'));
		$this->assign('paylist',$paylist);

		$statuslist = $this->model('site')->order_status_all(true);
		if($statuslist){
			foreach($statuslist as $key=>$value){
				if(in_array($value['identifier'],array('cancel','stop','end'))){
					unset($statuslist[$key]);
					continue;
				}
			}
			$this->assign('statuslist',$statuslist);
		}
		

		//讀取訂單價格迴圈
		$price = $this->model('order')->order_price($id);
		$this->assign('price',$price);
		
		$pricelist = $this->model('site')->price_status_all(true);
		if($pricelist){
			foreach($pricelist as $key=>$value){
				if(!$value['status']){
					unset($pricelist[$key]);
					continue;
				}
				if($price && $price[$value['identifier']] && $rs && $rs['currency_id']){
					$value['price'] = price_format_val($price[$value['identifier']],$rs['currency_id']);
					$value['price'] = abs($value['price']);
					$pricelist[$key] = $value;
				}
			}
		}
		
		$this->assign('pricelist',$pricelist);


		$payinfo = $this->model('order')->order_payment($id);
		$this->assign('payinfo',$payinfo);

		$loglist = $this->model('order')->log_list($id);
		$this->assign('loglist',$loglist);

		$this->view("order_set");
	}

	//刪除產品資訊
	public function product_delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$id = $this->get('id');
			if($id){
				$rslist = $this->session->val('admin_order_productlist');
				foreach($rslist as $key=>$value){
					if($value['id'] == $id){
						unset($rslist[$key]);
						break;
					}
				}
				$this->session->assign('admin_order_productlist',$rslist);
				$this->success();
			}
			$this->error(P_Lang('未指定產品ID'));
		}
		if(!$this->popedom['modify']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$product_info = $this->model('order')->product_one($id);
		$this->model('order')->product_delete($id);
		$log = array('order_id'=>$product_info['order_id'],'note'=>P_Lang('管理員刪除訂單產品'));
		$this->model('order')->log_save($log);
		$this->success();
	}

	//選擇商品
	public function prolist_f()
	{
		$currency_id = $this->get('currency_id','int');
		$this->assign('currency_id',$currency_id);
		$formurl = $pageurl = $this->url('order','prolist','id='.$id.'&currency_id='.$currency_id);
		$project = $this->model('project')->project_all($this->session->val('admin_site_id'),'id','is_biz != 0');
		if(!$project){
			$this->error(P_Lang('站點沒有啟用電子商務功能專案'));
		}
		$condition = "l.site_id IN(0,".$_SESSION['admin_site_id'].") AND l.status=1";
		$idlist = array_keys($project);
		$condition.= " AND l.project_id IN(".implode(',',$idlist).")";
		$keywords = $this->get('keywords');
		if($keywords){
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$condition .= " AND l.title LIKE '%".$keywords."%' ";
			$this->assign('keywords',$keywords);
		}
		$this->assign('pageurl',$pageurl);
		$this->assign('formurl',$formurl);
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = 20;
		$offset = ($pageid - 1) * $psize;
		$total = $this->model('list')->get_all_total($condition);
		if($total<1){
			$this->error(P_Lang('沒有產品資訊'));
		}
		$rslist = $this->model('list')->get_all($condition,$offset,$psize,'id');
		if($rslist){
			$bizlist = $this->model('list')->biz_all(array_keys($rslist));
			foreach($bizlist as $key=>$value){
				foreach($value as $k=>$v){
					$rslist[$key][$k] = $v;
				}
			}
		}
		$string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上一頁').'&next='.P_Lang('下一頁').'&last='.P_Lang('尾頁').'&half=5';
		$string.= '&add='.P_Lang('數量：').'(total)/(psize)'.P_Lang('，').P_Lang('頁碼：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign('pagelist',$pagelist);
		$this->assign('rslist',$rslist);
		$this->assign('id',$id);
		$this->view("order_prolist");
	}

	//取得產品資訊
	public function product_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定產品ID'));
		}

		$rs = $this->model('list')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('產品資訊不存在'));
		}
		$currency_id = $this->get("currency_id",'int');
		if($currency_id){
			$rs['price'] = price_format_val($rs['price'],$rs['currency_id'],$currency_id);
		}else{
			$rs['price'] = price_format_val($rs['price'],$rs['currency_id']);
		}
		if($rs['thumb'] && is_array($rs['thumb'])){
			$rs['thumb'] = $rs['thumb']['filename'];
		}
		$this->success($rs);
	}

	//圖片庫
	public function thumb_f()
	{
		$id = $this->get('id');
		if(!$id) $id = 'add';
		$formurl = $pageurl = $this->url("order","thumb","id=".$id);
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = 32;
		$offset = ($pageid - 1) * $psize;
		$condition = "ext IN ('gif','jpg','png','jpeg') ";
		$cate_id = $this->get("cate_id","int");
		if($cate_id)
		{
			$condition .= " AND cate_id='".$cate_id."' ";
			$pageurl .= "&cate_id=".$cate_id;
			$this->assign("cate_id",$cate_id);
		}
		$keywords = $this->get("keywords");
		if($keywords)
		{
			$condition .= " AND (title LIKE '%".$keywords."%' OR name LIKE '%".$keywords."%' OR id LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$total = $this->model('res')->get_count($condition);
		if($total>0)
		{
			$rslist = $this->model('res')->get_list($condition,$offset,$psize,false);
			$this->assign("rslist",$rslist);
			$this->assign("pageurl",$pageurl);
			$string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上一頁').'&next='.P_Lang('下一頁').'&last='.P_Lang('尾頁').'&half=4';
			$string.= '&add='.P_Lang('數量：').'(total)/(psize)'.P_Lang('，').P_Lang('頁碼：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->assign("formurl",$formurl);
		$this->assign("id",$id);
		//讀取附件分類
		$catelist = $this->model('res')->cate_all();
		$this->assign("catelist",$catelist);
		$config = $this->model('res')->type_list();
		$file_type = "*.*";
		$file_type_desc = P_Lang('檔案');
		if($type && $config['picture'])
		{
			$file_type = $config[$type]["type"];
			$file_type_desc = $config[$type]["name"];
		}
		$this->assign("file_type",$file_type);
		$this->assign("file_type_desc",$file_type_desc);
		$this->view("order_picture");
	}

	/**
	 * 儲存訂單資訊
	**/
	public function save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			if(!$this->popedom['add']){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
			$sn = $this->get('sn');
			if(!$sn){
				$this->error(P_Lang('訂單編號不能為空'));
			}
			if(!preg_match('/^[a-z0-9A-Z\_\-]+$/u',$sn)){
				$this->error(P_Lang('訂單編號不合要求，限字母、數字、下劃線及中劃線'));
			}
			$rs = $this->model('order')->get_one_from_sn($sn);
			if($rs){
				$this->error(P_Lang('訂單編號已被使用，請換個編號'));
			}
		}else{
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
			$old = $this->model('order')->get_one($id);
			if(!$old){
				$this->error(P_Lang('訂單資訊不存在'));
			}
		}
		$main = array();
		$main['user_id'] = $this->get('user_id','int');
		$main['price'] = $this->get('price','float');
		$main['currency_id'] = $this->get('currency_id','int');
		if($main['currency_id']){
			$main['currency_rate'] = $this->model('currency')->rate($main['currency_id']);
		}
		$main['status'] = $id ? $this->get('status') : 'create';
		if($main['status']){
			$statuslist = $this->model('order')->status_list();
			$main['status_title'] = $statuslist[$main['status']] ? $statuslist[$main['status']] : '';
		}
		$passwd = $this->get('passwd');
		if(!$passwd){
			$this->error(P_Lang('訂單密碼不能為空'));
		}
		if(!preg_match('/^[a-z0-9A-Z\_\-]+$/u',$passwd)){
			$this->error(P_Lang('訂單密碼不合要求，限字母、數字、下劃線及中劃線'));
		}
		$main['passwd'] = $passwd;
		$main['note'] = $this->get('note');
		$main['email'] = $this->get('email');
		$main['mobile'] = $this->get('mobile');
		if(!$id){
			$main['sn'] = $sn;
			$main['addtime'] = $this->time;
		}
		$main['ext'] = '';
		$extkey = $this->get('extkey');
		$extval = $this->get('extval');
		if($extkey && $extval){
			$tmp_main_ext = false;
			foreach($extkey as $key=>$value){
				if($extval[$key] && $value){
					if(!$tmp_main_ext){
						$tmp_main_ext = array();
					}
					$tmp_main_ext[$value] = $extval[$key];
				}
			}
			if($tmp_main_ext){
				$main['ext'] = serialize($tmp_main_ext);
			}
		}
		//檢查產品資料是否完整
		if(!$id){
			if(!$this->session->val('admin_order_productlist')){
				$this->error(P_Lang('產品資訊為空，訂單異常，請新增產品或刪除訂單資訊'));
			}
			$order_id = $this->model('order')->save($main);
			if(!$order_id){
				$this->error(P_Lang('訂單建立失敗，請檢查'));
			}
			$act_id = $order_id;
			$this->_save_products($order_id);
		}else{
			$this->model('order')->save($main,$id);
			$act_id = $id;
		}
		$this->_save_address($act_id);
		$this->_save_price($act_id);
		if($id){
			if($main['status'] && $main['status'] != $old['status']){
				$this->model('order')->update_order_status($id,$main['status'],$main['status_title']);
			}else{
				$log = array('order_id'=>$order_id,'note'=>P_Lang('管理員編輯訂單'));
				$this->model('order')->log_save($log);
			}
		}else{
			$log = array('order_id'=>$order_id,'note'=>P_Lang('管理員建立訂單'));
			$this->model('order')->log_save($log);
			$param = 'id='.$order_id."&status=".$main['status'];
			$this->model('task')->add_once('order',$param);
		}
		$this->success();
	}

	private function _save_price($order_id=0)
	{
		$this->model('order')->delete_order_price($order_id);
		$pricelist = $this->model('site')->price_status_all(false);
		$ext_price = $this->get('ext_price');
		if($ext_price && is_array($ext_price) && $pricelist){
			foreach($ext_price as $key=>$value){
				$value = abs($value);
				if($pricelist[$key] && $pricelist[$key]['action'] != 'add'){
					$value = -$value;
				}
				$tmp = array('order_id'=>$order_id,'code'=>$key,'price'=>$value);
				$this->model('order')->save_order_price($tmp);
			}
		}
		return true;
	}



	private function _save_products($order_id=0)
	{
		$rslist = $this->session->val('admin_order_productlist');
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			unset($value['id']);
			$value['order_id'] = $order_id;
			$this->model('order')->save_product($value);
		}
		$this->session->unassign('admin_order_productlist');
		return true;
	}

	/**
	 * 儲存訂單地址
	 * @引數 $order_id 訂單ID號
	 * @返回 true
	**/
	private function _save_address($order_id)
	{
		$array = array();
		$array['country'] = $this->get("s-country");
		$array['province'] = $this->get("s-province");
		$array['city'] = $this->get("s-city");
		$array['county'] = $this->get("s-county");
		$array['address'] = $this->get("s-address");
		$array['mobile'] = $this->get("s-mobile");
		$array['tel'] = $this->get("s-tel");
		$array['email'] = $this->get("s-email");
		$array['fullname'] = $this->get("s-fullname");
		$array['order_id'] = $order_id;
		$sid = $this->get('s-id','int');
		$this->model('order')->save_address($array,$sid);
		return true;
	}

	public function payment_delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		if(!$_SESSION['admin_rs']['if_system']){
			$this->error(P_Lang('您沒有許可權，僅限系統管理員操作'));
		}
		$this->model('order')->order_payment_delete($id);
		$this->success();
	}

	/**
	 * 檢測是否支援物流
	 * @引數 id 訂單ID
	**/
	public function express_check_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定訂單ID'));
		}
		$total = $this->model('order')->check_need_express($id);
		$this->success($total);
	}

	/**
	 * 支付記錄
	 * @引數 id 訂單ID
	**/
	public function payment_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定訂單ID'));
		}
		$rs = $this->model('order')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('訂單不存在'));
		}
		$this->assign('rs',$rs);
		if($rs['currency_id']){
			$currency = $this->model('currency')->get_one($rs['currency_id']);
			$this->assign('currency',$currency);
		}
		$loglist = $this->model('order')->payment_all($id);
		$this->assign('loglist',$loglist);
		$paylist = $this->model('payment')->get_all('','id');
		if(!$paylist){
			$paylist = array();
		}
		$tmplist = array();
		foreach($paylist as $key=>$value){
			if(!$tmplist[$value['gid']]){
				$tmplist[$value['gid']] = array('title'=>$value['group_title'],'wap'=>$value['group_wap'],'rslist'=>array($value['id']=>$value));
			}else{
				$tmplist[$value['gid']]['rslist'][$value['id']] = $value;
			}
		}
		$this->assign('paylist',$tmplist);
		//隱藏錄入
		$payend = $this->model('order')->check_payment_is_end($id);
		if(!$payend){
			$unpaid_price = $this->model('order')->unpaid_price($id);
			$this->assign('unpaid_price',$unpaid_price);
		}
		$this->assign('payend',$payend);
		$this->view('order_payment');
	}

	/**
	 * 儲存支付方法
	 * @引數 id 訂單ID
	 * @引數 payment_id 支付方法
	 * @引數 title 自設支付方法名稱
	 * @引數 price 支付金額
	 * @引數 dateline 支付時間
	 * @返回 JSON資料
	**/
	public function payment_save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定訂單ID'));
		}
		$rs = $this->model('order')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('訂單不存在'));
		}
		if(in_array($rs['status'],array('cancel','stop','end'))){
			$this->error(P_Lang('訂單已結束/取消/完成，不能執行此操作'));
		}
		$payment_id = $this->get('payment_id');
		if(!$payment_id){
			$this->error(P_Lang('未設定支付方式'));
		}
		if($payment_id && $payment_id != 'other'){
			$payment_rs = $this->model('payment')->get_one($payment_id);
			$title = $payment_rs ? $payment_rs['title'] : '';
		}
		if(!$title){
			$title = $this->get('title');
			if(!$title){
				$this->error(P_Lang('支付方式名稱不能為空'));
			}
		}
		if($this->model('order')->check_payment_is_end($id)){
			$this->error(P_Lang('訂單金額已付清，不能再錄入'));
		}
		$dateline = $this->get('dateline');
		if($dateline){
			$dateline.= " ".date("H:i:s",$this->time);
			$dateline = strtotime($dateline);
		}
		if(!$dateline){
			$dateline = $this->time;
		}
		$price = $this->get('price','float');
		if($price<0.01){
			$this->error(P_Lang('支付金額不能少於0.01元'));
		}
		$unpaid_price = $this->model('order')->unpaid_price($id);
		if($unpaid_price < $price){
			$price = $unpaid_price;
		}
		$array = array('order_id'=>$id,'payment_id'=>intval($payment_id),'title'=>$title);
		$array['price'] = $price;
		$array['startdate'] = $dateline;
		$array['dateline'] = $dateline;
		$note = $this->get('note');
		if($note){
			$tmp = array(P_Lang('備註')=>$note);
			$array['ext'] = serialize($tmp);
		}
		$this->model('order')->save_payment($array);
		//判斷
		if($rs['status'] == 'create'){
			$act = $this->model('order')->check_payment_is_end($id) ? 'paid' : 'unpaid';
			$this->model('order')->update_order_status($id,$act);
		}
		if($rs['status'] == 'unpaid' && $this->model('order')->check_payment_is_end($id)){
			$this->model('order')->update_order_status($id,'paid');
		}
		$tmp = array('title'=>$title,'price'=>price_format($price,$rs['currency_id'],$rs['currency_id']));
		$tip = P_Lang('錄入支付資訊，支付方式：{title}，支付金額：{price}',$tmp);
		$data = array('order_id'=>$id,'order_express_id'=>0,'note'=>$tip);
		$this->model('order')->log_save($data);
		$this->success();
	}

	/**
	 * 獲取會員郵箱或手機號或賬號等
	 * @引數 id 會員ID
	 * @引數 type 要取得的型別
	**/
	public function user_f()
	{
		$id = $this->get("id",'int');
		if(!$id){
			$this->error(P_Lang('未指定會員ID'));
		}
		$type = $this->get('type','system');
		if(!$type){
			$type = 'email';
		}
		$rs = $this->model('user')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('會員不存在'));
		}
		if(!$rs[$type]){
			$this->error(P_Lang('會員資訊無此欄位不存在'));
		}
		$this->success($rs[$type]);
	}

	/**
	 * 獲取訂單的價格
	 * @引數 id 指定訂單ID，為空取得當前session中的訂單價格
	**/
	public function product_price_f()
	{
		$id = $this->get('id','int');
		if($id){
			$rs = $this->model('order')->get_one($id);
			$rslist = $this->model('order')->product_list($id);
			
			$currency_id = $rs['currency_id'];
		}else{
			$rslist = $this->session->val('admin_order_productlist');
			$currency_id = $this->get('currency_id','int');
		}
		if(!$rslist){
			$this->success('0.00');
		}
		$price = 0;
		foreach($rslist as $key=>$value){
			$price += $value['price'] * $value['qty'];
		}
		$price = price_format_val($price,$currency_id);
		$this->success($price);
	}

	/**
	 * 編輯訂單或建立訂單時執行產品編輯
	**/
	public function product_set_f()
	{
		$id = $this->get('id','int');
		if($id){
			$rs = $this->model('order')->product_one($id);
		}else{
			$id = $this->get('id');
			if($id){
				$tmplist = $this->session->val('admin_order_productlist');
				if($tmplist){
					$rs = array();
					foreach($tmplist as $key=>$value){
						if($value['id'] == $id){
							$rs = $value;
							break;
						}
					}
				}
			}
		}
		if($rs){
			$this->assign('rs',$rs);
		}
		$order_id = $this->get('order_id','int');
		if($order_id){
			$order = $this->model('order')->get_one($order_id);
			$this->assign('order',$order);
			$currency_id = $order['currency_id'];
		}else{
			$currency_id = $this->get('currency_id','int');
		}
		if($currency_id){
			$currency = $this->model('currency')->get_one($currency_id);
			$this->assign('currency',$currency);
		}
		$this->view("order_product_set");
	}

	/**
	 * 獲取訂單下的產品
	**/
	public function productlist_f()
	{
		$id = $this->get('id','int');
		if($id){
			$rslist = $this->model('order')->product_list($id);
			$rs = $this->model('order')->get_one($id);
		}else{
			$rslist = $this->session->val('admin_order_productlist');
			$currency_id = $this->get('currency_id','int');
			$rs = array('currency_id'=>$currency_id);
		}
		$this->assign('rs',$rs);
		$this->assign('rslist',$rslist);
		$info = $this->fetch("order_product_info");
		$this->success($info);
	}

	/**
	 * 儲存產品資訊
	**/
	public function product_save_f()
	{
		$data = array();
		$data['title'] = $this->get('title');
		if(!$data['title']){
			$this->error(P_Lang('產品名稱為不能為空'));
		}
		$data['tid'] = $this->get('tid','int');
		$data['price'] = $this->get('price','float');
		$data['qty'] = $this->get('qty','int');
		if(!$data['qty']){
			$data['qty'] = 1;
		}
		$data['thumb'] = $this->get('thumb');
		$data['weight'] = $this->get("weight");
		$data['volume'] = $this->get('volume');
		$data['unit'] = $this->get('unit');
		$data['note'] = $this->get('note');
		$data['is_virtual'] = $this->get('is_virtual','int');
		$extkey = $this->get('extkey');
		$extval = $this->get('extval');
		$tmpdata = array();
		if($extkey && $extval){
			foreach($extkey as $key=>$value){
				if($value == '' || $extval[$key] == ''){
					continue;
				}
				$tmpdata[] = array('title'=>$value,'content'=>$extval[$key]);
			}
		}
		if($tmpdata){
			$data['ext'] = $tmpdata;
		}
 		$id = $this->get('id','int');
 		//編輯產品
		if($id){
			$this->model('order')->save_product($data,$id);
			$this->success();
		}
		//在已建立好的訂單中加產品
		$order_id = $this->get('order_id','int');
		if($order_id){
			$data['order_id'] = $order_id;
			$this->model('order')->save_product($data);
			$this->success();
		}
		//未生成的訂單對產品進行增刪查改
		$id = $this->get('id');
		$tmplist = $this->session->val('admin_order_productlist');
		if(!$tmplist){
			$tmplist = array();
		}
		if($id){
			foreach($tmplist as $key=>$value){
				if($value['id'] == $id){
					unset($tmplist[$key]);
					continue;
				}
			}
			$data['id'] = $id;
			$tmplist[] = $data;
		}else{
			$data['id'] = 'a-'.$this->time.'-'.rand(1000,9999);
			$tmplist[] = $data;
		}
		$this->session->assign('admin_order_productlist',$tmplist);
		$this->success();
	}
}