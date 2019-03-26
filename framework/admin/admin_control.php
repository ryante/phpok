<?php
/**
 * 管理員及其組管理組
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年07月21日
**/


if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_control extends phpok_control
{
	private $popedom;

	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("admin");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 管理員列表，普通管理員要有檢視許可權（admin:list）
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config["psize"];
		if(!$psize){
			$psize = 30;
		}
		$offset = ($pageid - 1) * $psize;
		$condition = "1=1";
		$keywords = $this->get("keywords");
		$pageurl = $this->url("admin");
		if($keywords){
			$condition .= " AND account LIKE '%".$keywords."%' ";
			$pageurl .= '&keywords='.rawurlencode($keywords);
		}
		$rslist = $this->model('admin')->get_list($condition,$offset,$psize);
		$total = $this->model('admin')->get_total($condition);
		if($total > $psize){
			$string = P_Lang("home=首頁&prev=上一頁&next=下一頁&last=尾頁&half=5&add=數量：(total)/(psize)，頁碼：(num)/(total_page)&always=1");
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->assign("rslist",$rslist);
		$this->view("admin_list");
	}

	/**
	 * 新增或修改管理員資訊，不能修改自己的資訊，編輯管理員要有編輯許可權（admin:modify），新增管理員要有新增許可權（admin:add）
	 * @引數 id 管理員ID，為0或空表示新增管理員
	**/
	public function set_f()
	{
		$id = $this->get("id","int");
		$plist = array();
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
			if($id == $this->session->val('admin_id')){
				$this->error(P_Lang('您不能操作自己的資訊'),$this->url("admin"));
			}
			$this->assign("id",$id);
			$rs = $this->model('admin')->get_one($id);
			if($rs["if_system"] && !$this->session->val('admin_rs.if_system')){
				$this->error(P_Lang("非系統管理員不能執行此項"),$this->url("admin"));
			}
			$this->assign("rs",$rs);
			if(!$rs["if_system"]){
				$plist = $this->model('admin')->get_popedom_list($id);
			}
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
		}
		$this->assign("plist",$plist);
		//讀取全部功能
		$syslist = $this->model('sysmenu')->get_all(0,1);
		$this->assign("syslist",$syslist);
		//讀取全部功能的許可權資訊
		$glist = $this->model('popedom')->get_all("pid=0",true,false);
		$clist = $this->model('popedom')->get_all("pid>0",true,true);
		$c_rs = $this->model('sysmenu')->get_one_condition("appfile='list' AND parent_id>0");
		$this->assign("c_rs",$c_rs);
		$sitelist = $this->model('site')->get_all_site();
		if($sitelist){
			foreach($sitelist AS $key=>$value){
				$all_project = $this->model('project')->get_all_project($value["id"]);
				if($all_project){
					foreach($all_project AS $k=>$v){
						if($clist[$v["id"]]){
							$all_project[$k]["_popedom"] = $clist[$v["id"]];
						}
					}
				}
				$value["sonlist"] = $all_project;
				$sitelist[$key] = $value;
			}
			$this->assign("sitelist",$sitelist);
		}
		$this->assign("glist",$glist);
		$this->view("admin_set");
	}

	/**
	 * 檢查是否有系統管理員
	 * @引數 id 管理員ID，即要跳過檢查的管理員
	 * @返回 Json字串
	**/
	public function check_if_system_f()
	{
		$id = $this->get("id","int");
		$exit = $this->check_system($id);
		if($exit == "ok"){
			$this->json("ok",true);
		}else{
			$this->json($exit);
		}
	}

	/**
	 * 檢查是否有系統管理員
	 * @引數 $id 管理員ID，即要跳過檢查的管理員
	 * @返回 字串，存在系統管理員返回為ok 不存在就返回錯誤資訊
	**/
	private function check_system($id=0)
	{
		$condition = "if_system=1 AND status=1";
		$rslist = $this->model('admin')->get_list($condition,0,100);
		if(!$rslist){
			return P_Lang('沒有系統管理員');
		}
		$if_system = false;
		foreach($rslist AS $key=>$value){
			if($value["id"] != $id){
				$if_system = true;
			}
		}
		if(!$if_system){
			return P_Lang('沒有系統管理員');
		}
		return "ok";
	}

	/**
	 * 刪除管理員，普通管理員要有刪除許可權（admin:delete），普通管理員不能刪除系統管理員
	 * @引數 id 要刪除的管理員，不能刪除自己，普通管理員
	 * @返回 
	 * @更新時間 
	**/
	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		if($id == $this->session->val('admin_id')){
			$this->error(P_Lang('您不能刪除自己'));
		}
		$rs = $this->model('admin')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('管理員資訊不存在'));
		}
		
		if($rs['if_system'] && !$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('非系統管理員不能刪除系統管理員'));
		}
		$exit = $this->check_system($id);
		if($exit != "ok"){
			$this->error($exit);
		}
		$this->model('admin')->delete($id);
		$this->success();
	}

	/**
	 * 檢測賬號是否存在
	 * @引數 id 管理員ID，不為0且不為空時，表示要檢查的管理員賬號跳過id值
	 * @引數 account 管理員賬號
	 * @返回 Json字串
	**/
	public function check_account_f()
	{
		$id = $this->get("id","int");
		$account = $this->get("account");
		$str = $this->check_account($account,$id);
		if($str == "ok"){
			$this->json("ok",true);
		}
		$this->json($str);
	}

	/**
	 * 檢測賬號是否存在，僅限內部使用
	 * @引數 $account 管理員賬號
	 * @引數 $id 管理員ID，不為0且不為空時，表示要檢查的管理員賬號跳過id值
	 * @返回 字串，檢測通過返回ok，不通過返回錯誤資訊
	**/
	private function check_account($account,$id=0)
	{
		if(!$account){
			return P_Lang('賬號不能為空');
		}
		$rs = $this->model('admin')->check_account($account,$id);
		if($rs){
			return P_Lang('賬號已經存在');
		}
		return "ok";
	}

	/**
	 * 儲存管理員資訊，無法自己修改自己資訊
	 * @引數 id 為0或空值時表示新增管理員，不為0表示編輯管理員資訊，包括分配許可權
	 * @引數 account 管理員賬號，不能為空
	 * @引數 pass 管理員密碼，id有值時pass可以為空
	 * @引數 email 管理員郵箱，系統管理員此郵箱為接收通知使用
	 * @引數 status 管理員狀態
	 * @引數 popedom 普通管理員許可權（系統管理員沒有此引數傳遞）
	**/
	public function save_f()
	{
		$id = $this->get("id","int");
		if($id && $id == $this->session->val('admin_id')){
			$this->error(P_Lang('您不能操作自己的資訊'));
		}
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
		}
		$account = $this->get("account");
		if(!$account){
			$this->error(P_Lang('賬號不能為空'));
		}
		$rs = $this->model('admin')->check_account($account,$id);
		if($rs){
			$this->error(P_Lang('賬號已經存在'.$account));
		}
		$array = array();
		$array["account"] = $account;
		$pass = $this->get("pass");
		if(!$pass && !$id){
			$this->error(P_Lang('密碼不能為空'));
		}
		if($pass){
			if(strlen($pass) < 5){
				$this->error(P_Lang('密碼長度不能少於4位'));
			}
			$array["pass"] = password_create($pass);
		}
		$array['email'] = $this->get("email");
		if($this->popedom["status"]){
			$array["status"] = $this->get("status","int");
		}
		$if_system = $this->get("if_system","int");
		if(!$this->session->val('admin_rs.if_system')){
			$if_system = 0;
		}
		$array["if_system"] = $if_system;
		if($id){
			$st = $this->model('admin')->save($array,$id);
			if(!$st){
				$this->error(P_Lang('管理員資訊更新失敗，請檢查'));
			}
		}else{
			$id = $this->model('admin')->save($array);
			if(!$id){
				$this->error(P_Lang('管理員資訊新增失敗，請檢查'));
			}
		}
		$this->model('admin')->clear_popedom($id);
		if(!$if_system){
			$popedom = $this->get("popedom");
			if($popedom){
				$popedom = array_unique($popedom);
				$this->model('admin')->save_popedom($popedom,$id);
			}
		}
		$this->success();
	}

	/**
	 * 更新管理員狀態，不能更新自己的許可權
	 * @引數 id 管理員ID
	**/
	public function status_f()
	{
		if(!$this->popedom['status']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		if($id == $this->session->val('admin_id')){
			$this->error(P_Lang('您不能操作自己的資訊'));
		}
		$rs = $this->model('admin')->get_one($id);
		$status = $rs["status"] ? 0 : 1;
		$action = $this->model('admin')->update_status($id,$status);
		if(!$action){
			$this->error(P_Lang('更新狀態失敗'));
		}
		$this->success($status);
	}
}