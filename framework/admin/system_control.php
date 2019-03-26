<?php
/**
 * 核心配置
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年06月03日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class system_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("system");
		$this->assign("popedom",$this->popedom);
	}

	//核心配置列表頁，這裡顯示全部，不分頁
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$rslist = $this->model('sysmenu')->get_all($this->session->val('admin_site_id'));
		$this->assign("rslist",$rslist);
		$desktop_show_type = $this->model('sysmenu')->desktop_setting('show_desktop');
		$this->view("sysmenu_index");
	}

	/**
	 * 新增子專案
	**/
	public function set_f()
	{
		$id = $this->get("id","int");
		$pid = $this->get("pid","int");
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
			$rs = $this->model('sysmenu')->get_one($id);
			$this->assign("id",$id);
			$this->assign("rs",$rs);
			$pid = $rs["parent_id"];
			$popedom_list = $this->model('popedom')->get_list($id);
			$this->assign("popedom_list",$popedom_list);
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
		}
		if($pid){
			$parent_list = $this->model('sysmenu')->get_list(0,0);
			$this->assign("parent_list",$parent_list);
			$this->assign("pid",$pid);
			//讀取目錄
			$dirlist = array();
			$list = $this->lib('file')->ls($this->dir_app);
			if($list){
				foreach($list as $key=>$value){
					$tmp = basename($value);
					if(file_exists($value.'/'.$this->app_id.'.control.php')){
						$dirlist[] = array('id'=>$tmp,'title'=>'Control: '.$tmp);
					}
				}
			}
			$list = $this->lib('file')->ls($this->dir_phpok."admin");
			foreach($list as $key=>$value){
				$tmp = str_replace("_control.php","",strtolower(basename($value)));
				if(strpos($tmp,".func.php") === false){
					$dirlist[] = array("id"=>$tmp,"title"=>basename($value));
				}
			}
			$this->assign("dirlist",$dirlist);
			$css = $this->lib("file")->cat($this->dir_root.'css/icomoon.css');
			preg_match_all("/\.icon-([a-z\-0-9]*):before\s*(\{|,)/isU",$css,$iconlist);
			$iconlist = $iconlist[1];
			sort($iconlist);
			$this->assign('iconlist',$iconlist);
		}
		$this->view("sysmenu_set");
	}

	public function icon_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$css = $this->lib("file")->cat($this->dir_root.'css/icomoon.css');
		preg_match_all("/\.icon-([a-z\-0-9]*):before\s*(\{|,)/isU",$css,$iconlist);
		$iconlist = $iconlist[1];
		sort($iconlist);
		$this->assign('iconlist',$iconlist);
		$rs = $this->model('sysmenu')->get_one($id);
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$this->view("sysmenu_icon");
	}

	public function icon_save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$icon = $this->get('icon');
		$array = array('icon'=>$icon);
		$this->model('sysmenu')->save($array,$id);
		$this->json(true);
	}

	# 儲存專案
	// 沒試
	public function save_f()
	{
		$id = $this->get("id","int");
		$popedom_id = $id ? 'modify' : 'add';
		if(!$this->popedom[$popedom_id]){
			error(P_Lang('您沒有許可權執行此操作'),'','error');
		}
		$error_url = $this->url("system","set");
		if($id) $error_url .= "&id=".$id;
		$title = $this->get("title");
		if(!$title){
			error(P_Lang('名稱不能為空'),$error_url,"error");
		}
		$array = array();
		$array["title"] = $title;
		$array["taxis"] = $this->get("taxis","int");
		$array["appfile"] = $this->get("appfile");
		$array['icon'] = $this->get('icon');
		$array['func'] = $this->get('func');
		$array['ext'] = $this->get('ext');
		if(!$id){
			$parent_id = $this->get("parent_id","int");
			if(!$parent_id){
				error(P_Lang('未指定上一級專案'),$error_url);
			}
			if(!$array["appfile"]){
				error(P_Lang('未指定控制層'),$error_url,'error');
			}
			$array["parent_id"] = $parent_id;
			$array["site_id"] = $_SESSION["admin_site_id"];
			$id = $this->model('sysmenu')->save($array);
			if(!$id){
				error(P_Lang('專案新增失敗'),$error_url,"ok");
			}
		}else{
			$rs = $this->model('sysmenu')->get_one($id);
			if(!$rs){
				error(P_Lang('獲取資料失敗，請檢查'),$error_url,"error");
			}
			if($rs["parent_id"]){
				$parent_id = $this->get("parent_id","int");
				if(!$parent_id){
					$parent_id = $rs["parent_id"];
				}
			}else{
				$parent_id = 0;
			}
			$array["parent_id"] = $parent_id;
			$this->model('sysmenu')->save($array,$id);
		}
		$rs = $this->model('sysmenu')->get_one($id);
		$popedom_list = $this->model('popedom')->get_list($id);
		if($popedom_list){
			foreach($popedom_list AS $key=>$value){
				$tmp_array = array();
				$tmp_title = $this->get("popedom_title_".$value["id"]);
				$tmp_identifier = $this->get("popedom_identifier_".$value["id"]);
				$tmp_taxis = $this->get("popedom_taxis_".$value["id"]);
				if($value["title"] != $tmp_title && $tmp_title) $tmp_array["title"] = $tmp_title;
				if($value["identifier"] != $tmp_identifier && $tmp_identifier) $tmp_array["identifier"] = $tmp_identifier;
				if($value["taxis"] != $tmp_taxis && $tmp_taxis) $tmp_array["taxis"] = $tmp_taxis;
				if(count($tmp_array)>0){
					if($rs["appfile"] == "list"){
						$this->model('popedom')->update_popedom_list($tmp_array,$id,$value["identifier"]);
					}else{
						$this->model('popedom')->save($tmp_array,$value["id"]);
					}
				}
			}
		}
		//新增新屬性
		$popedom_identifier_add = $this->get("popedom_identifier_add");
		$popedom_taxis_add = $this->get("popedom_taxis_add");
		$popedom_title_add = $this->get("popedom_title_add");
		if($popedom_identifier_add && count($popedom_identifier_add)>0 && is_array($popedom_identifier_add) && $_SESSION["admin_rs"]["if_system"]){
			foreach($popedom_identifier_add AS $key=>$value){
				if(!$value || !trim($value)) continue;
				$title = $popedom_title_add[$key];
				if(!$title || !trim($title)) continue;
				$taxis = $popedom_taxis_add[$key] ? intval($popedom_taxis_add[$key]) : 255;
				//檢測這個欄位是否被使用過了
				$check_rs = $this->model('popedom')->is_exists($value,$id);
				if(!$check_rs){
					$tmp = array("title"=>$title,"identifier"=>$value,"taxis"=>$taxis,"gid"=>$id,"pid"=>0);
					$this->model('popedom')->save($tmp);
				}
			}
		}
		error(P_Lang('專案新增/更新成功'),$this->url("system"),"ok");
	}

	//更新狀態
	function status_f()
	{
		if(!$this->popedom["status"]) $this->json(P_Lang('您沒有許可權執行此操作'));
		$id = $this->get("id","int");
		if(!$id){
			$this->json(P_Lang('沒有指定ID'));
		}
		$rs = $this->model('sysmenu')->get_one($id);
		if($rs["if_system"]){
			$this->json(P_Lang('系統欄目不支援執行此操作'));
		}
		$status = $rs["status"] ? 0 : 1;
		$action = $this->model('sysmenu')->update_status($id,$status);
		if(!$action){
			$this->json(P_Lang('操作失敗，請檢查SQL語句'));
		}else{
			$this->json($status,true);
		}
	}

	//批量更新排序
	function taxis_f()
	{
		$taxis = $this->lib('trans')->safe("taxis");
		if(!$taxis || !is_array($taxis)){
			$this->json(P_Lang('沒有指定要更新的排序'));
		}
		foreach($taxis AS $key=>$value){
			$this->model('sysmenu')->update_taxis($key,$value);
		}
		$this->json(P_Lang('資料排序更新成功'),true);
	}

	//刪除許可權配置
	function delete_popedom_f()
	{
		$id = $this->get("id","int");
		if(!$id)
		{
			$this->json(P_Lang('未指定ID'));
		}
		//判斷是否是系統管理
		if(!$_SESSION["admin_rs"]["if_system"])
		{
			$this->json(P_Lang('您不是開發管理員，不能執行此操作'));
		}
		$this->model('popedom')->delete($id);
		$this->json(P_Lang('刪除成功'),true);
	}

	function delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		if(!$this->popedom['delete']){
			$this->json(P_Lang('您沒有許可權執行此操作'));
		}
		$rs = $this->model('sysmenu')->get_one($id);
		if(!$rs){
			$this->json(P_Lang('資料記錄不存在'));
		}
		if(!$rs['parent_id']){
			$this->json(P_Lang('根導航不允許刪除'));
		}
		if($rs['if_system']){
			$this->json(P_Lang('核心導航操作不允許刪除'));
		}
		$this->model('sysmenu')->delete($id);
		$this->json(P_Lang('刪除成功'),true);
	}
}
?>