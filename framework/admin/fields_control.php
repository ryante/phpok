<?php
/**
 * 常用欄位管理器
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年05月18日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class fields_control extends phpok_control
{
	private $form_list;
	private $field_list;
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->form_list = $this->model('form')->form_all(true);
		$this->field_list = $this->model('form')->field_all(true);
		$this->format_list = $this->model('form')->format_all(true);
		$this->assign("field_list",$this->field_list);
		$this->assign("form_list",$this->form_list);
		$this->assign("format_list",$this->format_list);
		$this->popedom = appfile_popedom("fields");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 取得全部常用欄位列表
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$rslist = $this->model('fields')->default_all();
		if($rslist){
			foreach($rslist as $key=>$value){
				$value["field_type_name"] = $this->field_list[$value["field_type"]]['title'];
				$value["form_type_name"] = $this->form_list[$value["form_type"]]['title'];
				$rslist[$key] = $value;
			}
		}
		$this->assign("rslist",$rslist);
		$this->view("fields_index");
	}

	/**
	 * 新增欄位
	**/
	public function set_f()
	{
		$id = $this->get("id");
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
			$rs = $this->model('fields')->default_one($id);
			if($rs["ext"]){
				if(is_string($rs['ext'])){
					$ext = unserialize($rs['ext']);
					$rs['ext'] = $ext;
				}
				if($rs['ext'] && is_array($rs['ext'])){
					foreach($rs['ext'] as $key=>$value){
						$rs[$key] = $value;
					}
				}
			}
			$this->assign("rs",$rs);
			$this->assign("id",$id);
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
		}
		$opt_list = $this->model('opt')->group_all();
		$this->assign("opt_list",$opt_list);
		$this->view("fields_set");
	}

	/**
	 * 儲存表單資訊
	**/
	public function save_f()
	{
		$id = $this->get('id','system');
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
			$identifier = $id;
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
			$identifier = $this->get('identifier');
			if(!$identifier){
				$this->error(P_Lang('欄位標識不能為空'));
			}
			$identifier = strtolower($identifier);
			if(!preg_match("/^[a-z][a-z0-9\_]+$/u",$identifier)){
				$this->error(P_Lang('欄位標識不符合系統要求，限字母、數字及下劃線且必須是字母開頭'));
			}
			//檢測標識是否存在
			$chk = $this->model('fields')->default_one($identifier);
			if($chk){
				$this->error(P_Lang('標識已被使用'));
			}
		}
		$title = $this->get("title");
		$note = $this->get("note");
		$field_type = $this->get("field_type");
		$form_type = $this->get("form_type");
		$form_style = $this->get("form_style");
		$format = $this->get("format");
		$content = $this->get("content");
		$ext_form_id = $this->get("ext_form_id");
		$ext = array();
		if($ext_form_id){
			$list = explode(",",$ext_form_id);
			foreach($list as $key=>$value){
				$val = explode(':',$value);
				if($val[1] && $val[1] == "checkbox"){
					$value = $val[0];
					$ext[$value] = $this->get($value,"checkbox");
				}else{
					$value = $val[0];
					$ext[$value] = $this->get($value);
				}
			}
		}
		$array = array();
		$array["title"] = $title;
		$array["identifier"] = $identifier;
		$array["field_type"] = $field_type;
		$array["note"] = $note;
		$array["form_type"] = $form_type;
		$array["form_style"] = $form_style;
		$array["format"] = $format;
		$array["content"] = $content;
		$array["ext"] = $ext;
		$this->model('fields')->default_save($array);
		$this->success();
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定欄位ID'));
		}
		$this->model('fields')->default_delete($id);
		$this->success();
	}

	public function config_f()
	{
		$id = $this->get("id");
		if(!$id){
			exit(P_Lang('未指定ID'));
		}
		$eid = $this->get("eid");
		if($eid){
			$rs = $this->model('fields')->default_one($eid);
			if($rs && $rs['ext'] && is_array($rs['ext'])){
				foreach($rs['ext'] as $key=>$value){
					$rs[$key] = $value;
				}
			}
			$this->assign("rs",$rs);
		}
		$this->lib('form')->config($id);
	}
}