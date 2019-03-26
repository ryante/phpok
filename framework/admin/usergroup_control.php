<?php
/**
 * 會員組管理
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年12月23日
**/

class usergroup_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("usergroup");
		$this->assign("popedom",$this->popedom);
		$this->lib('form')->cssjs();
	}

	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$rslist = $this->model('usergroup')->get_all();
		$this->assign("rslist",$rslist);
		$this->view("usergroup_list");
	}

	public function set_f()
	{
		$id = $this->get("id","int");
		$popedom_users = array();
		$read_popedom_list = $reply_popedom_list = array();
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
			$rs = $this->model('usergroup')->get_one($id);
			if($rs['popedom']){
				$rs['popedom'] = unserialize($rs['popedom']);
				if($rs['popedom'][$_SESSION['admin_site_id']]){
					$popedom_users = explode(",",$rs['popedom'][$_SESSION['admin_site_id']]);
				}
			}
			$this->assign("rs",$rs);
			$this->assign('id',$id);
			$ext_module = "usergroup-".$id;
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
			$ext_module = "add-usergroup";
		}
		$this->assign("popedom_users",$popedom_users);
		
		//專案列表
		$rslist = $this->model('project')->get_all_project($_SESSION["admin_site_id"]);
		$this->assign("project_list",$rslist);
		//取得模組中帶有account欄位
		$reglist = false;
		if($rslist){
			foreach($rslist as $key=>$value){
				if(!$value['module']) continue;
				$tmplist = $this->model('module')->f_all("identifier='account' AND ftype=".$value['module']);
				if(!$tmplist) continue;
				$reglist[] = $value;
			}
			$this->assign('reglist',$reglist);
		}
		
		//判斷是否啟用
		//自定義擴充套件欄位
		$this->assign("ext_module",$ext_module);
		$forbid_list = $this->model('ext')->fields("user_group");
		$forbid = array_unique(array_merge(array("id","identifier"),$forbid_list));
		$extlist = get_phpok_ext($ext_module,implode(",",$forbid));
		$this->assign("extlist",$extlist);

		//會員欄位列表
		$all_fields_list = $this->model('user')->fields_all();
		if($all_fields_list){
			$this->assign("all_fields_list",$all_fields_list);
			$fields_list = "";
			if($rs["fields"]){
				$fields_list = explode(",",$rs["fields"]);
			}
			$this->assign("fields_list",$fields_list);
		}
		
		$this->view("usergroup_set");
	}

	//儲存資訊
	public function setok_f()
	{
		$array = array();
		$id = $this->get("id","int");
		$title = $this->get("title");
		$error_url = $this->url("usergroup","set");
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
			$error_url .= "&id=".$id;
			$rs = $this->model('usergroup')->get_one($id);
			$old_popedom = $rs['popedom'] ? unserialize($rs['popedom']) : array();
			$sitelist = $this->model('site')->get_all_site();
			if($sitelist){
				foreach($sitelist as $key=>$value){
					$array['popedom'][$value['id']] = $old_popedom[$value['id']] ? $old_popedom[$value['id']] : '';
				}
			}
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
		}
		if(!$title){
			$this->error(P_Lang('組名稱不允許為空'),$error_url);
		}
		$array["title"] = $title;
		$popedom = $this->get('popedom','checkbox');
		if($popedom){
			$array['popedom'][$this->session->val('admin_site_id')] = implode(",",$popedom);
		}
		$array['popedom'] = serialize($array['popedom']);
		$array["is_open"] = $this->get("is_open","int");
		$array["taxis"] = $this->get("taxis","int");
		$array["register_status"] = $this->get("register_status");
		$array["tbl_id"] = $this->get("tbl_id","int");
		$fields_list = $this->get("fields_list");
		if($fields_list && is_array($fields_list)){
			if(in_array("all",$fields_list)){
				$array["fields"] = "";
			}else{
				$array["fields"] = implode(",",$fields_list);
			}
		}
		if($id){
			$this->model('usergroup')->save($array,$id);
			//儲存擴充套件欄位
			ext_save("usergroup-".$id);
			$this->model('temp')->clean("usergroup-".$id,$this->session->val('admin_id'));
			$tip = P_Lang('會員組編輯成功');
		}else{
			$id = $this->model('usergroup')->save($array);
			if($id){
				ext_save("add-usergroup-ext-id",true,"usergroup-".$id);
				$this->model('temp')->clean("add-usergroup-ext-id",$this->session->val('admin_id'));
			}
			$tip = P_Lang('會員組新增成功');
		}
		$this->success($tip,$this->url('usergroup'));
	}

	public function delete_f()
	{
		if(!$this->popedom["delete"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('usergroup')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('資料記錄不存在'));
		}
		if($rs["is_default"]){
			$this->error(P_Lang('預設會員組不能刪除'));
		}
		if($rs["is_guest"]){
			$this->error(P_Lang('預設遊客組不能刪除'));
		}
		$this->model('usergroup')->del($id);
		$this->success();
	}


	public function default_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('usergroup')->set_default($id);
		$this->success();
	}

	public function guest_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('usergroup')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('資料記錄不存在'));
		}
		if($rs["is_default"]){
			$this->error(P_Lang('預設會員組不能設為遊客組'));
		}
		$this->model('usergroup')->set_guest($id);
		$this->success();
	}

	public function status_f()
	{
		if(!$this->popedom['status']){
			exit(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			exit(P_Lang('未指定ID'));
		}
		$rs = $this->model('usergroup')->get_one($id);
		if(!$rs)
		{
			exit(P_Lang('會員組資訊不存在'));
		}
		$status = $this->get("status","int");
		$this->model('usergroup')->set_status($id,$status);
		exit("ok");
	}
}
?>