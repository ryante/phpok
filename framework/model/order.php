<?php
/**
 * 訂單資訊及管理
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年08月13日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class order_model_base extends phpok_model
{
	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 取得訂單列表
	 * @引數 $condition 查詢條件，僅限主表查詢
	 * @引數 $offset 起始位置，從第一個開始查為0
	 * @引數 $psize 查詢數量，預設是20頁
	 * @返回 false/結果集陣列
	**/
	public function get_list($condition='',$offset=0,$psize=20)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$offset = intval($offset);
		$psize = intval($psize);
		$sql .= " ORDER BY addtime DESC,id DESC LIMIT ".$offset.",".$psize;
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if($value['ext']){
				$value['ext'] = unserialize($value['ext']);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	/**
	 * 查詢訂單數量
	 * @引數 $condition 查詢條件，僅限主表中使用
	 * @返回 具體訂單數量
	**/
	public function get_count($condition="")
	{
		$sql = "SELECT count(o.id) FROM ".$this->db->prefix."order o ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 取得訂單的最大ID號，再此基礎上+1
	**/
	public function maxid()
	{
		$sql = "SELECT MAX(id) id FROM ".$this->db->prefix."order";
		$rs = $this->db->get_one($sql);
		if(!$rs) return '1';
		return ($rs['id']+1);
	}

	/**
	 * 儲存訂單資訊
	 * @引數 $data 陣列
	 * @引數 $id 為0或空表示新增新訂單
	 * @返回 true/false/訂單ID號
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($data['ext'] && is_array($data['ext'])){
			$data['ext'] = serialize($data['ext']);
		}
		if($id){
			return $this->db->update_array($data,"order",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"order");
		}
	}

	/**
	 * 儲存商品資訊
	 * @引數 $data 產品資訊，陣列
	 * @引數 $id order_product表中的主鍵ID，為0為空表示新增
	**/
	public function save_product($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($data['ext'] && is_array($data['ext'])){
			$data['ext'] = serialize($data['ext']);
		}
		if($id){
			return $this->db->update_array($data,"order_product",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"order_product");
		}
	}

	/**
	 * 儲存收件人地址
	 * @引數 $data 地址資訊，陣列
	 * @引數 $id order_address表中的主鍵ID，為0為空表示新增
	**/
	public function save_address($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"order_address",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"order_address");
		}
	}

	/**
	 * 儲存發票資訊
	 * @引數 $data 訂單中的發票資訊
	**/
	public function save_invoice($data)
	{
		return $this->db->insert_array($data,'order_invoice','replace');
	}

	/**
	 * 通過訂單號取得單個訂單資訊
	 * @引數 $sn 訂單編號
	 * @引數 $user 會員ID
	 * @返回 陣列
	**/
	public function get_one_from_sn($sn,$user='')
	{
		return $this->get_one($sn,'sn',$user);
	}

	/**
	 * 取得訂單資訊
	 * @引數 $id 訂單ID號或訂單SN號
	 * @引數 $type 預設是id，支援sn和id
	 * @引數 $user 會員ID
	 * @返回 陣列
	**/
	public function get_one($id,$type='id',$user='')
	{
		if(!$id){
			return false;
		}
		if($type != 'id' && $type != 'sn'){
			$type = 'id';
		}
		$sql = "SELECT * FROM ".$this->db->prefix."order WHERE ".$type."='".$id."'";
		if($user){
			$sql .= " AND user_id='".$user."' ";
		}
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['ext']){
			$rs['ext'] = unserialize($rs['ext']);
		}
		return $rs;
	}

	/**
	 * 取得訂單中的地址資訊
	 * @引數 $id 訂單號ID
	**/
	public function address($id)
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."order_address WHERE order_id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得會員最後一次訂單的地址
	 * @引數 $user_id 會員ID
	 * @引數 $is_virtual 是否使用虛擬服務裡的地址，true不讀地址，
	 * @返回 地址資訊或false
	 * @更新時間 2016年09月08日
	**/
	public function last_address($user_id,$is_virtual=false)
	{
		if(!$user_id){
			return false;
		}
		if($is_virtual){
			$sql = "SELECT * FROM ".$this->db->prefix."order WHERE user_id='".$user_id."' ORDER BY id DESC LIMIT 1";
			$chk = $this->db->get_one($sql);
			$user = $this->model('user')->get_one($user_id);
			$email = ($chk && $chk['email']) ? $chk['email'] : $user['email'];
			$mobile = ($chk && $chk['mobile']) ? $chk['mobile'] : $user['mobile'];
			return array('email'=>$email,'mobile'=>$mobile);
		}
		$sql = "SELECT a.* FROM ".$this->db->prefix."order_address a ";
		$sql.= "LEFT JOIN ".$this->db->prefix."order o ON(a.order_id=o.id) ";
		$sql.= "WHERE o.user_id='".$user_id."' ORDER BY o.id DESC";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得訂單下的產品資訊
	 * @引數 $id 訂單ID號
	 * @返回 陣列
	**/
	public function product_list($id)
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."order_product WHERE order_id='".$id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if($value['ext']){
				$value['ext'] = unserialize($value['ext']);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	/**
	 * 取得訂單中的發票資訊
	 * @引數 $id 訂單ID
	**/
	public function invoice($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_invoice WHERE order_id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 刪除訂單產品
	 * @引數 $id order_product中的主鍵ID，不是產品ID，也不是訂單ID
	**/
	public function product_delete($id)
	{
		if(!$id){
			return false;
		}
		$rs = $this->product_one($id);
		if(!$rs){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."order_product WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得產品資訊，僅限訂單表order_product中
	 * @引數 $id order_product中的主鍵ID，不是產品ID，也不是訂單ID
	**/
	public function product_one($id)
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."order_product WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['ext']){
			$rs['ext'] = unserialize($rs['ext']);
		}
		return $rs;
	}

	/**
	 * 更新訂單狀態
	 * @引數 $id 訂單ID
	 * @引數 $status 訂單狀態
	 * @引數 $note 訂單狀態日誌說明
	 * @返回 true
	 * @更新時間 
	**/
	public function update_order_status($id,$status='',$note='')
	{
		$status_list = $this->model('site')->order_status_all();
		$status_info = $status_list[$status] ? $status_list[$status] : array('title'=>$status);
		$sql = "UPDATE ".$this->db->prefix."order SET status='".$status."',status_title='".$status_info['title']."' WHERE id='".$id."'";
		$this->db->query($sql);
		$param = 'id='.$id."&status=".$status;
		$this->model('task')->add_once('order',$param);
		$rs = $this->get_one($id);
		if(!$note){
			$note = P_Lang('訂單（{sn}）狀態變更為：{status}',array('sn'=>$rs['sn'],'status'=>$status_info['title']));
		}
		$who = $this->session->val('user_name') ? $this->session->val('user_name') : P_Lang('遊客');
		$log = array('order_id'=>$id,'addtime'=>$this->time,'who'=>$who,'note'=>$note);
		$this->log_save($log);
		return true;
	}

	/**
	 * 訂單日誌
	 * @引數 $data 一維陣列
	 * @返回 true 或 false 或 外掛的日誌ID
	 * @更新時間 2016年08月16日
	**/
	public function log_save($data)
	{
		if(!$data){
			return false;
		}
		if(!$data['addtime']){
			$data['addtime'] = $this->time;
		}
		if($this->app_id != 'admin' && $this->session->val('user_id')){
			$data['user_id'] = $this->session->val('user_id');
			if(!$data['who']){
				$data['who'] = $this->session->val('user_name');
			}
		}
		if($this->app_id == 'admin'){
			$data['admin_id'] = $this->session->val('admin_id');
			if(!$data['who']){
				$data['who'] = $this->session->val('admin_account');
			}
		}
		return $this->db->insert_array($data,'order_log');
	}

	/**
	 * 儲存訂單中的支付方式，對應表order_payment
	 * @引數 $data 陣列
	 * @引數 $id 主鍵ID
	**/
	public function save_payment($data,$id=0)
	{
		if(!$data){
			return false;
		}
		if($data['ext'] && is_array($data['ext'])){
			$data['ext'] = serialize($data['ext']);
		}
		if($id){
			return $this->db->update_array($data,'order_payment',array('id'=>$id));
		}else{
			return $this->db->insert_array($data,'order_payment');
		}
	}

	/**
	 * 取得訂單中的支付方式資訊
	 * @引數 $order_id 訂單ID
	 * @引數 $payment_id 支付方式ID，此項用於訂單中有多條支付方式
	**/
	public function order_payment($order_id,$payment_id=0)
	{
		if(!$order_id){
			return false;
		}
		$condition = "";
		if($payment_id){
			$condition = "payment_id='".$payment_id."' ";
		}
		return $this->_order_payment($order_id,$condition);
	}

	public function delete_not_end_order($order_id)
	{
		if(!$order_id){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."order_payment WHERE order_id='".$order_id."' AND dateline<1";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 取得訂單中的未完工的支付方式資訊
	 * @引數 $order_id 訂單ID
	 * @引數 $payment_id 支付方式ID，此項用於訂單中有多條支付方式
	**/
	public function order_payment_notend($order_id,$payment_id=0)
	{
		if(!$order_id){
			return false;
		}
		$condition = "dateline<1";
		if($payment_id){
			$condition .= " AND payment_id='".$payment_id."' ";
		}
		return $this->_order_payment($order_id,$condition);
	}

	protected function _order_payment($id,$condition="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_payment WHERE order_id='".$id."'";
		if($condition){
			$sql .= " AND ".$condition;
		}
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['ext']){
			$rs['ext'] = unserialize($rs['ext']);
		}
		return $rs;
	}

	/**
	 * 訂單全部支付記錄
	 * @引數 $id 訂單ID
	 * @返回 false 或 多組陣列
	 * @更新時間 2016年10月03日
	**/
	public function payment_all($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_payment WHERE order_id='".intval($id)."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if($value['ext'] && is_string($value['ext'])){
				$tmp = unserialize($value['ext']);
				$value['ext'] = $tmp;
				if($tmp['wealth'] && $tmp['wealth_val']){
					$w = $this->model('wealth')->get_one($tmp['wealth']);
					$value['ext'] = P_Lang('使用財富（{title}）{price}{unit}支付',array('title'=>$w['title'],'price'=>$tmp['wealth_val'],'unit'=>$w['unit']));
				}
				$rslist[$key] = $value;
			}
		}
		return $rslist;
	}

	/**
	 * 檢查訂單是否是完成
	 * @引數 $order_id，訂單ID號，如果訂單未結束
	 * @返回 true 完成 或 false 未完成
	 * @更新時間 2016年08月13日
	**/
	public function check_payment_is_end($order_id)
	{
		$paid_price = $this->paid_price($order_id);
		if(!$paid_price){
			return false;
		}
		$rs = $this->get_one($order_id);
		if(!$rs){
			return false;
		}
		$price = $rs['price'];
		if(round($paid_price,2) != round($price,2)){
			return false;
		}
		return true;
	}

	/**
	 * 訂單已支付金額
	 * @引數 $order_id 訂單ID
	 * @返回 訂單金額
	 * @更新時間 2016年08月13日
	**/
	public function paid_price($order_id)
	{
		$rs = $this->get_one($order_id);
		if(!$rs){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."order_payment WHERE order_id='".$order_id."' AND dateline>0";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return 0;
		}
		$paid_price = 0;
		foreach($rslist as $key=>$value){
			if(!$value['price']){
				continue;
			}
			$currency_id = (isset($value['currency_id']) && $value['currency_id']) ? $value['currency_id'] : $rs['currency_id'];
			$currency_rate = (isset($value['currency_rate']) && $value['currency_rate']) ? $value['currency_rate'] : 0;
			$price_val = price_format_val($value['price'],$currency_id,$rs['currency_id'],$rs['currency_rate'],$currency_rate);
			$paid_price += floatval($price_val);
		}
		return $paid_price;
	}

	/**
	 * 未支付的訂單金額
	 * @引數 $order_id 訂單ID
	 * @返回 訂單金額
	 * @更新時間 2016年10月03日
	**/
	public function unpaid_price($order_id)
	{
		$paid_price = $this->paid_price($order_id);
		$rs = $this->get_one($order_id);
		if(round($paid_price,2) != round($rs['price'],2)){
			return round(($rs['price'] - $paid_price),4);
		}
		return '0.00';
	}

	/**
	 * 刪除支付資訊
	 * @引數 $id order_payment裡的主鍵ID
	**/
	public function order_payment_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."order_payment WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 訂單價格資訊 order_price中使用
	 * @引數 $order_id 訂單ID
	**/
	public function order_price($order_id)
	{
		$sql = "SELECT code,price FROM ".$this->db->prefix."order_price WHERE order_id='".$order_id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$rs = array();
		foreach($rslist as $key=>$value){
			$rs[$value['code']] = $value['price'];
		}
		return $rs;
	}

	/**
	 * 訂單狀態列表
	**/
	public function status_list()
	{
		$list = $this->model('site')->order_status_all(false);
		if(!$list){
			return false;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			$rslist[$key] = $value['title'];
		}
		return $rslist;
	}

	/**
	 * 取得指定會員下的餘額及積分
	 * @引數 $user_id 會員ID
	 * @返回 false 或 餘額多維陣列列表
	 * @更新時間 2016年11月26日
	**/
	public function balance($user_id)
	{
		if(!$user_id){
			return false;
		}
		$wealthlist = $this->model('wealth')->get_all(1);
		if(!$wealthlist){
			return false;
		}
		$wlist = false;
		foreach($wealthlist as $key=>$value){
			if(!$value['ifcash']){
				continue;
			}
			$val = $this->model('wealth')->get_val($user_id,$value['id']);
			if(!$val){
				continue;
			}
			if($value['min_val'] && $val < $value['min_val']){
				continue;
			}
			$tmp = $value;
			$tmp['val'] = $val;
			$tmp['price'] = round($val*$value['cash_ratio']/100,$value['dnum']);
			if(!$wlist){
				$wlist = array();
			}
			if($value['ifpay']){
				$wlist['balance'][$tmp['id']] = $tmp;
			}else{
				$wlist['integral'][$tmp['id']] = $tmp;
			}
		}
		if(!$wlist){
			return false;
		}
		return $wlist;
	}

	/**
	 * 獲取訂單編號
	**/
	public function create_sn()
	{
		$sntype = $this->site['biz_sn'];
		if(!$sntype){
			$sntype = 'year-month-date-number';
		}
		$sn = '';
		$list = explode('-',$sntype);
		foreach($list AS $key=>$value){
			if($value == 'year'){
				$sn.= date("Y",$this->time);
			}
			if($value == 'month'){
				$sn.= date("m",$this->time);
			}
			if($value == 'date'){
				$sn.= date("d",$this->time);
			}
			if($value == 'hour'){
				$sn.= date('H',$this->time);
			}
			if($value == 'minute' || $value == 'minutes'){
				$sn.= date("i",$this->time);
			}
			if($value == 'second' || $value == 'seconds'){
				$sn.= date("s",$this->time);
			}
			if($value == 'rand' || $value == 'rands'){
				$sn .= rand(10,99);
			}
			if($value == 'time' || $value == 'times'){
				$sn .= $this->time;
			}
			if($value == 'number'){
				$condition = "FROM_UNIXTIME(addtime,'%Y-%m-%d')='".date("Y-m-d",$this->time)."'";
				$total = $this->model('order')->get_count($condition);
				if(!$total){
					$total = '0';
				}
				$total++;
				$sn .= str_pad($total,3,'0',STR_PAD_LEFT);
			}
			if($value == 'id'){
				//$sql = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA='".$this->db->database()."' AND TABLE_NAME ='".$this->db->prefix."order'";
				$sql = "SELECT max(id) FROM ".$this->db->prefix."order";
				$maxid = $this->db->count($sql);
				$maxid++;
				$sn .= str_pad($maxid,5,'0',STR_PAD_LEFT);
			}
			//包含會員資訊
			if($value == 'user'){
				$sn .= $this->session->val('user_id') ? 'U'.str_pad($this->session->val('user_id'),5,'0',STR_PAD_LEFT) : 'G';
			}
			if(substr($value,0,6) == 'prefix'){
				$sn .= str_replace(array('prefix','[',']'),'',$value);
			}
		}
		return $sn;
	}

	/**
	 * 儲存訂單各種狀態下的價格，使用表order_price
	 * @引數 $data 陣列
	**/
	public function save_order_price($data)
	{
		return $this->db->insert_array($data,'order_price');
	}

	/**
	 * 積分抵扣費用
	 * @引數 $order_id 訂單ID
	 * @引數 $price 價格
	**/
	public function integral_discount($order_id,$price=0)
	{
		if(!$price || !$order_id){
			return false;
		}
		$price = floatval($price);
		if($price<0){
			if(function_exists('abs')){
				$price = abs($price);
			}else{
				$price = -$price;
			}
		}
		$sql = "UPDATE ".$this->db->prefix."order_price SET price=price-".$price." WHERE code='discount' AND order_id='".$order_id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得訂單裡的財富基數
	 * @引數 $id 訂單ID，整數
	 * @返回 false/數字
	 * @更新時間 2016年11月28日
	**/
	public function integral($id)
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT id,tid,qty FROM ".$this->db->prefix."order_product WHERE order_id='".$id."' AND tid>0";
		$list = $this->db->get_all($sql);
		if(!$list){
			return false;
		}
		$idlist = array();
		foreach($list as $key=>$value){
			$idlist[] = $value['tid'];
		}
		$integral_list = $this->model('list')->integral_list($idlist);
		if(!$integral_list){
			return false;
		}
		$integral = 0;
		foreach($list as $key=>$value){
			if($integral_list[$value['tid']]){
				$integral += intval($integral_list[$value['tid']]) * intval($value['qty']);
			}
		}
		return $integral;
	}


	public function log_list($order_id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_log WHERE order_id='".$order_id."' ORDER BY addtime ASC,id ASC";
		return $this->db->get_all($sql);
	}

	public function log_all($order_id)
	{
		return $this->log_list($order_id);
	}
}