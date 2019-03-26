<?php
/**
 * GD方案管理
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年10月04日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class gd_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('gd');
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 方案列表
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$rslist = $this->model('gd')->get_all();
		$this->assign("rslist",$rslist);
		$this->view("gd_index");
	}

	/**
	 * 編輯頁面
	**/
	public function set_f()
	{
		$id = $this->get("id","int");
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您沒有許可權執行此操作'),$this->url('gd'));
			}
			$rs = $this->model('gd')->get_one($id);
			if($rs["mark_picture"] && !file_exists($rs["mark_picture"])){
				$rs["mark_picture"] = "";
			}
			$this->assign("id",$id);
			$this->assign("rs",$rs);
		} else {
			if(!$this->popedom["add"]){
				error(P_Lang('您沒有許可權執行此操作'),$this->url('gd'),'error');
			}
		}
		$this->view("gd_set");
	}

	/**
	 * 儲存資料
	**/
	public function save_f()
	{
		$id = $this->get("id","int");
		$array = array();
		if(!$id){
			if(!$this->popedom['add']){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
			$identifier = $this->get("identifier");
			if(!$identifier){
				$this->error(P_Lang('標識不能為空'));
			}
			$identifier = strtolower($identifier);
			if(!preg_match("/[a-z][a-z0-9\_\-]+/",$identifier)){
				$this->error(P_Lang('標識不符合系統要求，限字母、數字及下劃線且必須是字母開頭'));
			}
			$chk = $this->model('gd')->get_one($identifier,'identifier');
			if($chk){
				$this->error(P_Lang('標識已經存在'));
			}
			$array["identifier"] = $identifier;
		}else{
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
		}
		$array["width"] = $this->get("width","int");
		$array["height"] = $this->get("height","int");
		$array["mark_picture"] = $this->get("mark_picture");
		$array["mark_position"] = $this->get("mark_position");
		$array["cut_type"] = $this->get("cut_type","int");
		$array["bgcolor"] = $this->get("bgcolor");
		$array["trans"] = $this->get("trans","int");
		$array["quality"] = $this->get("quality","int");
		$this->model('gd')->save($array,$id);
		$this->success();
	}

	/**
	 * 刪除圖片方案
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
		$this->model('gd')->delete($id);
		$this->success();
	}

	/**
	 * 設定編輯器使用的圖片規格
	**/
	public function editor_f()
	{
		if(!$this->popedom['modify']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id','int');
		$this->model('gd')->update_editor($id);
		$this->success();
	}
}