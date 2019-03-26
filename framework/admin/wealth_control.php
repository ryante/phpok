<?php
/**
 * 財富規則管理
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年07月25日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class wealth_control extends phpok_control
{
	private $popedom;

	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('wealth');
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 全部財富規則
	**/
	public function index_f()
	{
		$rslist = $this->model('wealth')->get_all();
		$this->assign('rslist',$rslist);
		$this->view('wealth_index');
	}

	/**
	 * 財富明細，會員下的財宣清單
	 * @引數 id 財富ID
	**/
	public function info_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'),$this->url('wealth'));
		}
		$rs = $this->model('wealth')->get_one($id);
		$this->assign('rs',$rs);
		$condition = "w.wid=".$id;
		$pageurl = $this->url('wealth','list','id='.$id);
		$keywords = $this->get('keywords');
		if($keywords){
			$condition .= " AND u.user LIKE '%".$keywords."%'";
			$pageurl .= "&keywords=".rawurlencode($keywords);
		}
		$total = $this->model('wealth')->info_total($condition);
		if($total){
			$psize = $this->config['psize'];
			$pageid = $this->get($this->config['pageid'],'int');
			if(!$pageid){
				$pageid = 1;
			}
			$offset = ($pageid-1) * $psize;
			$rslist = $this->model('wealth')->info_list($condition,$offset,$psize);
			$string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上一頁').'&next='.P_Lang('下一頁').'&last='.P_Lang('尾頁').'&half=5';
			$string.= '&add='.P_Lang('數量：').'(total)/(psize)'.P_Lang('，').P_Lang('頁碼：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign('pagelist',$pagelist);
			$this->assign('rslist',$rslist);
			$this->assign('offset',$offset);
			$this->assign('psize',$psize);
		}
		$this->view('wealth_info');
	}

	/**
	 * 每個會員的財富日誌
	 * @引數 wid 財富ID
	 * @引數 uid 會員ID
	**/
	public function log_f()
	{
		$wid = $this->get('wid','int');
		$uid = $this->get('uid','int');
		if(!$wid){
			$this->error(P_Lang('未指定財富ID'));
		}
		if(!$uid){
			$this->error(P_Lang('未指定會員ID'));
		}
		$rs = $this->model('wealth')->get_one($wid);
		$this->assign('rs',$rs);
		$pageurl = $this->url('wealth','log','wid='.$wid.'&uid='.$uid);
		$condition = "wid='".$wid."' AND goal_id='".$uid."' AND status=1";
		$total = $this->model('wealth')->log_total($condition);
		if($total){
			$psize = $this->config['psize'];
			$pageid = $this->get($this->config['pageid'],'int');
			if(!$pageid){
				$pageid = 1;
			}
			$offset = ($pageid-1) * $psize;
			$rslist = $this->model('wealth')->log_list($condition,$offset,$psize);
			$string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上一頁').'&next='.P_Lang('下一頁').'&last='.P_Lang('尾頁').'&half=3&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign('pagelist',$pagelist);
			$this->assign('rslist',$rslist);
			$this->assign('offset',$offset);
			$this->assign('psize',$psize);
		}
		$this->view('wealth_log');
	}

	/**
	 * 新增或扣除財富
	 * @引數 wid 財富ID
	 * @引數 uid 會員ID
	 * @引數 note 備註
	 * @引數 val 財富值
	 * @引數 type 為-號時表示扣除
	**/
	public function val_f()
	{
		$wid = $this->get('wid','int');
		$uid = $this->get('uid','int');
		if(!$wid){
			$this->json(P_Lang('未指定財富ID'));
		}
		if(!$uid){
			$this->json(P_Lang('未指定會員ID'));
		}
		$note = $this->get('note');
		$val = $this->get('val');
		if(!$val){
			$this->json(P_Lang('未指定數值'));
		}
		$val = abs($val);
		$type = $this->get('type');
		$from_uid = $this->get('from_uid');
		$vouch_user_id = 0;
		if($from_uid == 'vouch'){
			$vouch_user_id = $this->get('vouch','int');
			if(!$vouch_user_id){
				$this->json('未指定推薦人');
			}
		}
		if($from_uid == 'other'){
			$tmp = $this->get('username');
			if(!$tmp){
				$this->json('請填寫會員賬號');
			}
			$tmp_rs = $this->model('user')->chk_name($tmp);
			if(!$tmp_rs){
				$this->json('會員不存在');
			}
			$vouch_user_id = $tmp_rs['id'];
		}
		$time = $this->time;
		$dateline = $this->get('dateline');
		if($dateline){
			$time = strtotime($dateline);
		}
		if($type && $type == '-'){
			$savelogs = array('wid'=>$wid,'goal_id'=>$uid,'mid'=>0,'val'=>'-'.$val);
			$savelogs['appid'] = $this->app_id;
			$savelogs['dateline'] = $time;
			$savelogs['user_id'] = $vouch_user_id;
			$savelogs['admin_id'] = $this->session->val('admin_id');
			$savelogs['ctrlid'] = 'wealth';
			$savelogs['funcid'] = 'val';
			$savelogs['url'] = 'admin.php...';
			$savelogs['note'] = $note ? $note : P_Lang('管理員操作');
			$savelogs['status'] = 1;
			$data = array('wid'=>$wid,'uid'=>$uid,'lasttime'=>$this->time);
			$user_val = $this->model('wealth')->get_val($uid,$wid);
			if($user_val){
				if($user_val>$val){
					$data['val'] = round(($user_val-$val),2);
				}else{
					$savelogs['val'] = '-'.$user_val;
					$data['val'] = '0';
				}
			}else{
				$savelogs['val'] = '0';
				$data['val'] = '0';
			}
			$this->model('wealth')->save_log($savelogs);
			$this->model('wealth')->save_info($data);
		}else{
			//增加日誌記錄
			$savelogs = array('wid'=>$wid,'goal_id'=>$uid,'mid'=>0,'val'=>$val);
			$savelogs['appid'] = $this->app_id;
			$savelogs['dateline'] = $time;
			$savelogs['user_id'] = $vouch_user_id;
			$savelogs['admin_id'] = $this->session->val('admin_id');
			$savelogs['ctrlid'] = 'wealth';
			$savelogs['funcid'] = 'val';
			$savelogs['url'] = 'admin.php...';
			$savelogs['note'] = $note ? $note : P_Lang('管理員操作');
			$savelogs['status'] = 1;
			$data = array('wid'=>$wid,'uid'=>$uid,'lasttime'=>$this->time);
			$this->model('wealth')->save_log($savelogs);
			//更新統計
			$user_val = $this->model('wealth')->get_val($uid,$wid);
			if($user_val){
				$data['val'] = round(($user_val+$val),2);
			}else{
				$data['val'] = round($val,2);
			}
			$this->model('wealth')->save_info($data);
		}
		$this->json(true);
	}

	/**
	 * 配置財富資訊，要求新增有增加許可權（wealth:add），修改需要有修改許可權（wealth:modify）
	 * @引數 id 財富ID
	**/
	public function set_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			if(!$this->popedom["add"]){
				error(P_Lang('您沒有許可權執行此操作'),'','error');
			}
		}else{
			if(!$this->popedom["modify"]){
				error(P_Lang('您沒有許可權執行此操作'),'','error');
			}
			$rs = $this->model('wealth')->get_one($id);
			$this->assign('rs',$rs);
			$this->assign('id',$id);
		}
		$this->view('wealth_set');
	}

	/**
	 * 儲存財富配置資訊
	 * @引數 id 財富ID，為0或空表示新增新財富
	 * @引數 title 財富名稱，如積分，威望，金幣等
	 * @引數 identifer 財富標識
	 * @引數 unit 計量單位，如點，元，星等
	 * @引數 dnum 財富計量型別，整數，一位小數，及兩位小數
	 * @引數 ifpay 是否支援前臺充值
	 * @引數 pay_ratio 充值兌換比例
	 * @引數 ifcash 是否支援提現
	 * @引數 cash_ratio 提現兌換比例
	 * @引數 ifcheck 是否稽核，請慎用。建議啟用稽核
	 * @引數 taxis 排序，範圍0-255 值越小越往前靠
	**/
	public function save_f()
	{
		$id = $this->get('id','int');
		$array = array();
		if($id){
			if(!$this->popedom['modify']){
				$this->json(P_Lang('您沒有許可權執行此操作'));
			}
		}else{
			if(!$this->popedom['add']){
				$this->json(P_Lang('您沒有許可權執行此操作'));
			}
			$array['site_id'] = $_SESSION['admin_site_id'];
		}
		$array['title'] = $this->get('title');
		if(!$array['title']){
			$this->json(P_Lang('名稱不能為空'));
		}
		$array['identifier'] = $this->get('identifier');
		if(!$array['identifier']){
			$this->json(P_Lang('標識不能為空'));
		}
		$array['identifier'] = $this->format($array['identifier'],'system');
		if(!$array['identifier']){
			$this->json(P_Lang('標識不符合系統要求'));
		}
		$chk = $this->model('wealth')->chk_identifier($array['identifier'],$id);
		if($chk){
			$this->json(P_Lang('標識已被使用'));
		}
		$array['unit'] = $this->get('unit');
		if(!$array['unit']){
			$this->json(P_Lang('計量單位不能為空'));
		}
		$array['dnum'] = $this->get('dnum','int');
		$array['ifpay'] = $this->get('ifpay');
		if($array['ifpay']){
			$array['pay_ratio'] = $this->get('pay_ratio','float');
			if(!$array['pay_ratio']){
				$array['pay_ratio'] = 1;
			}
		}else{
			$array['pay_ratio'] = 0;
		}
		$array['ifcash'] = $this->get('ifcash');
		if($array['ifcash']){
			$array['cash_ratio'] = $this->get('cash_ratio','float');
			if(!$array['cash_ratio']){
				$array['cash_ratio'] = 1;
			}
		}else{
			$array['cash_ratio'] = 0;
		}
		$array['ifcheck'] = $this->get('ifcheck','int');
		$array['taxis'] = $this->get('taxis','int');
		$array['min_val'] = $this->get('min_val','float');
		$this->model('wealth')->save($array,$id);
		$this->json(true);
	}

	/**
	 * 財富狀態
	 * @引數 id 財富ID
	**/
	public function status_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		if(!$this->popedom['status']){
			$this->json(P_Lang('您沒有許可權執行此操作'));
		}
		$rs = $this->model('wealth')->get_one($id);
		$status = $rs['status'] ? 0 : 1;
		$this->model('wealth')->update_status($id,$status);
		$this->json($status,true);
	}

	/**
	 * 財富刪除
	 * @引數 id 財富ID
	**/
	public function delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		if(!$this->popedom['delete']){
			$this->json(P_Lang('您沒有許可權執行此操作'));
		}
		$this->model('wealth')->delete($id);
		$this->json(true);
	}

	/**
	 * 財富規則配置
	 * @引數 id 財富ID
	**/
	public function rule_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定ID'),$this->url('wealth'),'error');
		}
		if(!$this->popedom["setting"]){
			error(P_Lang('您沒有許可權執行此操作'),$this->url('wealth'),'error');
		}
		$rs = $this->model('wealth')->get_one($id);
		$this->assign('rs',$rs);
		$rslist = $this->model('wealth')->rule_all("wid='".$id."'");
		$this->assign('rslist',$rslist);
		$alist = array('register'=>P_Lang('會員註冊'),'login'=>P_Lang('會員登入'),'payment'=>P_Lang('購物付款'));
		$alist['comment'] = P_Lang('評論文章');
		$alist['post'] = P_Lang('釋出文章');
		$alist['content'] = P_Lang('閱讀文章');
		$this->assign('alist',$alist);
		$agentlist = $this->model('wealth')->goal_userlist();
		$this->assign('agentlist',$agentlist);
		$this->view('wealth_rule');
	}

	public function delete_rule_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		if(!$this->popedom['setting']){
			$this->json(P_Lang('您沒有許可權執行此操作'));
		}
		$this->model('wealth')->delete_rule($id);
		$this->json(true);
	}

	/**
	 * 儲存規則
	 * @引數 wid 財富ID，不為空時表示新增
	 * @引數 id 當前規則ID，為空時wid不能為空
	 * @返回 Json字串
	**/
	public function save_rule_f()
	{
		if(!$this->popedom['setting']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$wid = $this->get('wid','int');
		if(!$wid){
			$id = $this->get('id','int');
			if(!$id){
				$this->error(P_Lang('未指定規則ID'));
			}
		}
		$action = $this->get('action');
		if(!$action){
			$this->error(P_Lang('動作未指定'));
		}
		$val = $this->get('val');
		if(!$val){
			$this->error(P_Lang('值為空的規則不需要建立'));
		}
		$goal = $this->get('goal');
		if(!$goal){
			$this->error(P_Lang('未指定目標物件'));
		}
		$taxis = $this->get('taxis','int');
		$data = array('action'=>$action,'val'=>$val,'goal'=>$goal,'taxis'=>$taxis);
		if($wid){
			if($this->model('wealth')->check($action,$goal,$wid)){
				$this->error(P_Lang('執行動作及物件已存在，不能重複建立'));
			}
			$data['wid'] = $wid;
			$this->model('wealth')->save_rule($data);
		}else{
			$old = $this->model('wealth')->rule_one($id);
			if($this->model('wealth')->check($action,$goal,$old['wid'],$id)){
				$this->error(P_Lang('執行動作及物件已存在，不能重複更新'));
			}
			$this->model('wealth')->save_rule($data,$id);
		}
		$this->success();
	}

	public function notcheck_f()
	{
		$pageurl = $this->url('wealth','notcheck');
		$condition = "l.status=0";
		$total = $this->model('wealth')->log_total_notcheck($condition);
		if($total){
			$psize = $this->config['psize'];
			$pageid = $this->get($this->config['pageid'],'int');
			if(!$pageid){
				$pageid = 1;
			}
			$offset = ($pageid-1) * $psize;
			$rslist = $this->model('wealth')->log_list_notcheck($condition,$offset,$psize);
			$string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上一頁').'&next='.P_Lang('下一頁').'&last='.P_Lang('尾頁').'&half=3&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign('pagelist',$pagelist);
			$this->assign('rslist',$rslist);
			$this->assign('offset',$offset);
			$this->assign('psize',$psize);
		}
		$this->view('wealth_notcheck');
	}

	/**
	 * 財富稽核
	 * @引數 id 日誌ID
	 * @引數 $action 動作
	**/
	public function action_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$action = $this->get('action');
		if($action == 'ok'){
			$this->model('wealth')->setok($id);
		}else{
			$this->model('wealth')->log_delete($id);
		}
		$this->json(true);
	}

	public function action_user_f()
	{
		$id = $this->get('wid','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$uid = $this->get('uid','int');
		if(!$uid){
			$this->error(P_Lang('未指定會員ID'));
		}
		$rs = $this->model('wealth')->get_one($id);
		$this->assign('rs',$rs);
		$this->assign('uid',$uid);
		$this->view("wealth_action");
	}
}