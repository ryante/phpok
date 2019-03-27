<?php
/**
 * 會員財富管理
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年07月25日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class wealth_model_base extends phpok_model
{
	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 取得全部財富規則
	 * @引數 $status 狀態，為1時只讀有效狀態，為0讀全部，為2只讀無效狀態
	 * @引數 $pri 主鍵值
	 * @返回 二維陣列
	**/
	public function get_all($status=0,$pri='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."wealth WHERE site_id='".$this->site_id."' ";
		if($status){
			$sql .= " AND status='".($status == 1 ? 1 : 0)."' ";
		}
		$sql .= " ORDER BY taxis ASC";
		return $this->db->get_all($sql,$pri);
	}

	/**
	 * 取得某個財富規則配置資訊
	 * @引數 $id 財富ID
	 * @引數 $typeid 欄位ID
	 * @返回 一維陣列
	**/
	public function get_one($id,$typeid='id')
	{
		if(!$typeid){
			$typeid = 'id';
		}
		$sql = "SELECT * FROM ".$this->db->prefix."wealth WHERE `".$typeid."`='".$id."'";
		if($this->site_id){
			$sql .= " AND site_id='".$this->site_id."'";
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 根據查詢條件，獲取財富規則，條件為空獲取全部財富規則
	 * @引數 $condition 查詢條件
	 * @返回 二給維組
	**/
	public function rule_all($condition='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."wealth_rule ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$sql .= " ORDER BY taxis ASC";
		return $this->db->get_all($sql);
	}

	/**
	 * 儲存財富日誌
	 * @引數 $data 一維陣列
	 * @返回 插入的ID或false
	**/
	public function save_log($data)
	{
		return $this->db->insert_array($data,'wealth_log');
	}

	/**
	 * 更新會員財富資訊
	 * @引數 $data 一維陣列，替代式更新
	 * @返回 插入的ID或false
	**/
	public function save_info($data)
	{
		return $this->db->insert_array($data,'wealth_info','replace');
	}

	/**
	 * 獲取指定的會員及指定的財富方案對應的財富內容
	 * @引數 $uid 會員ID
	 * @引數 $wid 財富ID
	 * @返回 0 或 財富值（數字或浮點）
	**/
	public function get_val($uid,$wid)
	{
		if(!$uid || !$wid){
			return 0;
		}
		$sql = "SELECT val FROM ".$this->db->prefix."wealth_info WHERE uid='".$uid."' AND wid='".$wid."'";
		$rs = $this->db->get_one($sql);
		if($rs){
			return $rs['val'];
		}
		return 0;
	}

	/**
	 * 根據查詢條件獲取財富值資訊
	 * @引數 $condition 查詢條件
	**/
	public function vals($condition='')
	{
		$sql = "SELECT wid,uid,val FROM ".$this->db->prefix."wealth_info ";
		if($condition){
			$sql.= "WHERE ".$condition;
		}
		return $this->db->get_all($sql);
	}

	/**
	 * 取得目標使用者列表
	**/
	public function goal_userlist()
	{
		$xmlfile = $this->dir_data.'xml/user_agent.xml';
		if(!file_exists($xmlfile)){
			return array('user'=>'使用者','introducer'=>'一級推薦人','introducer2'=>'二級推薦人','introducer3'=>'三級推薦人');
		}
		$rslist = $this->lib('xml')->read($xmlfile);
		if(isset($rslist[$this->langid])){
			return $rslist[$this->langid];
		}
		if($rslist['default']){
			return $rslist['default'];
		}
		return $rslist;
	}

	/**
	 * 獲取一條規則
	 * @引數 $id 規則ID
	 * @返回 false或陣列
	**/
	public function rule_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."wealth_rule WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 財富日誌，系統自動生成的
	 * @引數 $note 備註
	 * @返回 陣列
	**/
	private function _data($note='')
	{
		$data = array('dateline'=>$this->time,'appid'=>$this->app_id);
		$data['ctrlid'] = $this->config['ctrl'];
		$data['funcid'] = $this->config['func'];
		if($this->app_id == 'admin'){
			$data['admin_id'] = $this->session->val('admin_id');
		}else{
			$data['user_id'] = $this->session->val('user_id');
		}
		$data['note'] = $note;
		return $data;
	}

	/**
	 * 目標ID
	 * @引數 $id 當前會員ID號
	 * @引數 $goal 目標型別
	 * @返回 
	 * @更新時間 
	**/
	private function _goal($id,$goal='user')
	{
		if(!$goal || $goal == 'user'){
			return $id;
		}
		if($goal == 'introducer'){
			return $this->model('user')->get_relation($id);
		}
		$num = str_replace('introducer','',$goal);
		if(!$num || !intval($num)){
			return $this->model('user')->get_relation($id);
		}
		$num = intval($num);
		for($i=0;$i<$num;$i++){
			$id = $this->model('user')->get_relation($id);
			if(!$id){
				return false;
			}
		}
		return $id;
	}

	/**
	 * 訂單支付送財富
	 * @引數 $id 訂單ID號，以防止重複贈送
	 * @引數 $note 備註
	 * @返回 true 或 false
	 * @更新時間 2016年08月16日
	**/
	public function order($id,$note='')
	{
		if(!$id){
			return false;
		}
		$order = $this->model('order')->get_one($id);
		if(!$order){
			return false;
		}
		if(!$order['user_id']){
			return false;
		}
		$uid = $order['user_id'];
		$tmplist = $this->_rule_list('payment');
		if(!$tmplist){
			return false;
		}
		$data = $this->_data($note);
		$integral = $this->model('order')->integral($id);
		foreach($tmplist as $key=>$value){
			$log = $data;
			$log['status'] = $value['ifcheck'] ? 0 : 1;
			$log['rule_id'] = $value['id'];
			$log['wid'] = $value['wid'];
			$log['goal_id'] = $this->_goal($uid,$value['goal']);
			$tmpprice = str_replace('price',$order['price'],$value['val']);
			$tmpprice = str_replace('integral',$integral,$tmpprice);
			eval('$value[\'val\'] = '.$tmpprice.';');
			$val = round($value['val'],$value['dnum']);
			$log['val'] = $val;
			$log['mid'] = $id;
			$chk = $this->chk_log($log);
			if(!$chk){
				continue;
			}
			$this->save_log($log);
			if($log['status']){
				$get_val = $this->get_val($log['goal_id'],$log['wid']);
				$val2 = $get_val + $val;
				if($val2<0){
					$val2 = 0;
				}
				$array = array('wid'=>$log['wid'],'lasttime'=>$this->time,'uid'=>$log['goal_id'],'val'=>$val2);
				$this->save_info($array);
			}
		}
		return true;
	}

	/**
	 * 充值到賬對積分進行轉換
	 * @引數 $logid 支付ID
	**/
	public function recharge($logid)
	{
		$order = $this->model('payment')->log_one($logid);
		if(!$order || !$order['status'] || !$order['user_id']){
			return false;
		}
		$ext = $order['ext'] ? unserialize($order['ext']) : array();
		//如果充值成功
		if($ext['phpok_status'] || !$ext['goal']){
			return true;
		}
		//檢視金額
		$data = $this->_data(P_Lang('線上充值'));
		$rs = $this->get_one($ext['goal']);
		if(!$rs || !$rs['status'] || !$rs['ifpay']){
			return false;
		}
		$data['status'] = $rs['ifcheck'] ? 0 : 1;
		$data['rule_id'] = 0;
		$data['wid'] = $rs['id'];
		$data['goal_id'] = $order['user_id'];
		$data['val'] = round($order['price'] * $rs['pay_ratio'],2);
		$data['mid'] = 0;
		$this->save_log($data);
		if($data['status']){
			$get_val = $this->get_val($data['goal_id'],$data['wid']);
			$val2 = $get_val + $data['val'];
			if($val2<0){
				$val2 = 0;
			}
			$array = array('wid'=>$data['wid'],'lasttime'=>$this->time,'uid'=>$data['goal_id'],'val'=>$val2);
			$this->save_info($array);
		}
		$ext['phpok_status'] = true;
		$tmp = serialize($ext);
		$this->model('payment')->log_update(array('ext'=>$tmp),$logid);
		return true;
	}

	/**
	 * 閱讀/釋出/評論贈送財富
	 * @引數 $id 主題ID
	 * @引數 $uid 會員ID
	 * @引數 $type 型別，content讀主題，comment評論主題，post釋出主題
	 * @引數 $note 備註
	 * @返回 true 或 false
	 * @更新時間 2016年08月16日
	**/
	public function add_integral($id=0,$uid=0,$type='content',$note='')
	{
		if(!$id || !$uid || !intval($id) || !intval($uid)){
			return false;
		}
		if(!in_array($type,array('content','comment','post'))){
			return false;
		}
		$id = intval($id);
		$uid = intval($uid);
		$rs = $this->model('list')->simple_one($id);
		if(!$rs || !$rs['status']){
			return false;
		}
		if($type == 'post' && $rs['user_id'] != $uid){
			return false;
		}
		if($type == 'comment'){
			$condition = "tid='".$id."' AND uid='".$uid."' AND status=1";
			$comment_list = $this->model('reply')->get_list($condition,0,1);
			if(!$comment_list){
				return false;
			}
		}
		$tmplist = $this->_rule_list($type);
		if(!$tmplist){
			return false;
		}
		$data = $this->_data($note);
		$integral = $this->model('list')->integral($id);
		foreach($tmplist as $key=>$value){
			$log = $data;
			$log['status'] = $value['ifcheck'] ? 0 : 1;
			$log['rule_id'] = $value['id'];
			$log['wid'] = $value['wid'];
			$log['goal_id'] = $this->_goal($uid,$value['goal']);
			$tmpprice = str_replace('integral',$integral,$value['val']);
			eval('$value[\'val\'] = '.$tmpprice.';');
			$val = round($value['val'],$value['dnum']);
			$log['val'] = $val;
			$log['mid'] = $id;
			$chk = $this->chk_log($log);
			if(!$chk){
				continue;
			}
			$this->save_log($log);
			if($log['status']){
				$get_val = $this->get_val($log['goal_id'],$log['wid']);
				$val2 = $get_val + $val;
				if($val2<0){
					$val2 = 0;
				}
				$array = array('wid'=>$log['wid'],'lasttime'=>$this->time,'uid'=>$log['goal_id'],'val'=>$val2);
				$this->save_info($array);
			}
		}
		return true;
	}

	/**
	 * 註冊送財富（如果規則的值是負值，表示扣除）
	 * @引數 $uid 會員ID
	 * @引數 $note 備註
	 * @返回 true 或者 false
	 * @更新時間 2016年07月30日
	**/
	public function register($uid,$note='')
	{
		$tmplist = $this->_rule_list('register');
		if(!$tmplist){
			return false;
		}
		$data = $this->_data($note);
		foreach($tmplist as $key=>$value){
			$log = $data;
			$log['status'] = $value['ifcheck'] ? 0 : 1;
			$log['rule_id'] = $value['id'];
			$log['wid'] = $value['wid'];
			$log['goal_id'] = $this->_goal($uid,$value['goal']);
			$val = round($value['val'],$value['dnum']);
			$log['val'] = $val;
			$this->save_log($log);
			if($log['status']){
				$get_val = $this->get_val($log['goal_id'],$log['wid']);
				$val2 = $get_val + $val;
				if($val2<0){
					$val2 = 0;
				}
				$array = array('wid'=>$log['wid'],'lasttime'=>$this->time,'uid'=>$log['goal_id'],'val'=>$val2);
				$this->save_info($array);
			}
		}
		return true;
	}

	/**
	 * 登入送財富，一天僅一次有效（如果規則的值是負值，表示扣除）
	 * @引數 $uid 會員ID
	 * @引數 $note 備註
	 * @返回 true 或 false
	 * @更新時間 2016年07月25日
	**/
	public function login($uid,$note='')
	{
		$tmplist = $this->_rule_list('login');
		if(!$tmplist){
			return false;
		}
		$data = $this->_data($note);
		foreach($tmplist as $key=>$value){
			$log = $data;
			$log['status'] = $value['ifcheck'] ? 0 : 1;
			$log['rule_id'] = $value['id'];
			$log['wid'] = $value['wid'];
			$log['goal_id'] = $this->_goal($uid,$value['goal']);
			$chk = $this->chk_log($log);
			if(!$chk){
				continue;
			}
			$val = round($value['val'],$value['dnum']);
			$log['val'] = $val;
			$this->save_log($log);
			if($log['status']){
				$get_val = $this->get_val($log['goal_id'],$log['wid']);
				$val2 = $get_val + $val;
				if($val2<0){
					$val2 = 0;
				}
				$array = array('wid'=>$log['wid'],'lasttime'=>$this->time,'uid'=>$log['goal_id'],'val'=>$val2);
				$this->save_info($array);
			}
		}
		return true;
	}

	/**
	 * 手動增加或減去財富
	 * @引數 $wid 財富ID，支援數字ID或標識
	 * @引數 $uid 目標會員ID，僅支援數字
	 * @引數 $val 要增加多少，為負數時表示減去
	 * @引數 $note 備註
	**/
	public function save_val($wid,$uid,$val=0,$note='')
	{
		if(!$wid || !$uid || !$val || !$note){
			return false;
		}
		if(is_numeric($wid)){
			$rs = $this->get_one($wid,'id');
		}else{
			$rs = $this->get_one($wid,'identifier');
		}
		if(!$rs){
			return false;
		}
		$log = $this->_data($note);
		$log['status'] = 1;
		$log['rule_id'] = 0;
		$log['wid'] = $rs['id'];
		$log['goal_id'] = $uid;
		$log['val'] = round($val,$rs['dnum']);
		$this->save_log($log);
		$get_val = $this->get_val($uid,$rs['id']);
		$val2 = $get_val + $val;
		if($val2<0){
			$val2 = 0;
		}
		$array = array('wid'=>$rs['id'],'lasttime'=>$this->time,'uid'=>$uid,'val'=>$val2);
		$this->save_info($array);
		return true;
	}

	private function _rule_list($type='')
	{
		if(!$type){
			return false;
		}
		$sql = "SELECT r.*,w.dnum,w.ifcheck FROM ".$this->db->prefix."wealth_rule r LEFT JOIN ".$this->db->prefix."wealth w ";
		$sql.= "ON(r.wid=w.id) WHERE w.site_id='".$this->site_id."' AND w.status=1 AND r.action='".$type."' ";
		$sql.= "ORDER BY w.taxis,r.taxis ASC";
		return $this->db->get_all($sql);
	}

	/**
	 * 檢查日誌，主要是檢查是否有記錄，如主題防止多次刷新，登入24小時內只計一次等
	 * @引數 $data 一維陣列
	 * @返回 
	 * @更新時間 
	**/
	private function chk_log($data)
	{
		if(!$data){
			return false;
		}
		if(!$data['wid'] || !$data['goal_id'] || !$data['rule_id']){
			return false;
		}
		$sql = " SELECT id FROM ".$this->db->prefix."wealth_log WHERE wid='".$data['wid']."' ";
		$sql.= " AND goal_id='".$data['goal_id']."' AND rule_id='".$data['rule_id']."' ";
		if($data['ctrlid'] == 'login'){
			$time1 = strtotime(date("Y-m-d",$this->time));
			$time2 = $time1 + 24 * 60 * 60;
			$sql .= " AND dateline>='".$time1."' AND dateline<'".$time2."' ";
		}
		if($data['mid']){
			$sql .= " AND mid='".$data['mid']."' ";
		}
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return true;
		}
		return false;
	}

	public function log_list($condition='',$offset=0,$psize=30)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."wealth_log ";
		if($condition){
			$sql .= "WHERE ".$condition." ";
		}
		$sql.= "ORDER BY dateline DESC,id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	public function log_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."wealth_log WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}
}