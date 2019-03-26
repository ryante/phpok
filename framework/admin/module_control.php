<?php
/***********************************************************
	Filename: {phpok}/admin/module_control.php
	Note	: 模組管理器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-11-29 20:21
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class module_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->form_list = $this->model('form')->form_all(true);
		$this->field_list = $this->model('form')->field_all(true);
		$this->format_list = $this->model('form')->format_all(true);
		$this->assign('form_list',$this->form_list);
		$this->assign("field_list",$this->field_list);
		$this->assign("format_list",$this->format_list);

		$this->popedom = appfile_popedom("module");
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom["list"]){
			error(P_Lang('您沒有許可權執行此操作'),'','error');
		}
		$rslist = $this->model('module')->get_all();
		$this->assign("rslist",$rslist);
		$this->view("module_index");
	}

	public function set_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id','int');
		if($id){
			$this->assign("id",$id);
			$rs = $this->model('module')->get_one($id);
			$layout = !$rs['mtype'] ? array("hits","dateline") : array();
			if($rs["layout"]){
				$layout = explode(",",$rs["layout"]);
			}
			$this->assign("layout",$layout);
			$used_list = $this->model('module')->fields_all($id,"identifier");
			if($used_list){
				foreach($used_list as $key=>$value){
					$value["field_type_name"] = $this->field_list[$value["field_type"]]['title'];
					$value["form_type_name"] = $this->form_list[$value["form_type"]]['title'];
					$used_list[$key] = $value;
				}
			}
			$this->assign("used_list",$used_list);
		}else{
			$taxis = $this->model('module')->module_next_taxis();
			$rs = array('taxis'=>$taxis);
		}
		$this->assign("rs",$rs);
		$this->view("module_set");
	}

	public function layout_f()
	{
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定模組ID'));
		}
		$rs = $this->model('module')->get_one($id);
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$layout = !$rs['mtype'] ? array("hits","dateline") : array();
		if($rs["layout"]){
			$layout = explode(",",$rs["layout"]);
		}
		$this->assign("layout",$layout);
		$used_list = $this->model('module')->fields_all($id,"identifier");
		if($used_list){
			foreach($used_list AS $key=>$value){
				$value["field_type_name"] = $this->field_list[$value["field_type"]]['title'];
				$value["form_type_name"] = $this->form_list[$value["form_type"]]['title'];
				$used_list[$key] = $value;
			}
		}
		$this->assign("used_list",$used_list);
		$this->view("module_layout");
	}

	public function layout_save_f()
	{
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定模組ID'));
		}
		$layout = $this->get("layout");
		if($layout && is_array($layout)){
			$layout = implode(",",$layout);
		}else{
			$layout = '';
		}
		$array = array("layout"=>$layout);
		$this->model('module')->save($array,$id);
		$this->success();
	}

	/**
	 * 模組複製
	 * @引數 id 要複製的模組ID
	 * @引數 title 新的模組名稱
	**/
	public function copy_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定模組ID'));
		}
		$rs = $this->model('module')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('模組資訊不存在'));
		}
		$title = $this->get("title");
		if(!$title){
			$title = $rs["title"].P_Lang('(複製)');
		}
		$rs["title"] = $title;
		unset($rs["id"]);
		$new_id = $this->model('module')->save($rs);
		if(!$new_id){
			$this->error(P_Lang('模組複製失敗，請檢查'));
		}
		$list = $this->model('module')->fields_all($id);
		if($list){
			foreach($list AS $key=>$value){
				unset($value["id"]);
				$value["ftype"] = $new_id;
				if($value["ext"]){
					$value["ext"] = stripslashes($value["ext"]);
				}
				$this->model('module')->fields_save($value);
			}
		}
		$this->model('module')->create_tbl($new_id);
		$tbl_exists = $this->model('module')->chk_tbl_exists($new_id,$rs['mtype']);
		if(!$tbl_exists){
			$this->error(P_Lang('模組建立表失敗，請檢查'));
		}
		$rslist = $this->model('module')->fields_all($new_id);
		if($rslist){
			foreach($rslist as $key=>$value){
				$this->model('module')->create_fields($value['id']);
			}
		}
		$this->success();
	}

	/**
	 * 儲存或更新模型
	**/
	public function save_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id","int");
		$title = $this->get("title");
		if(!$title){
			$this->error(P_Lang('模組名稱不能為空'));
		}
		$note = $this->get("note");
		$taxis = $this->get("taxis","int");
		$array = array("title"=>$title,"note"=>$note,"taxis"=>$taxis);
		if($id){
			$layout = $this->get("layout");
			if($layout && is_array($layout)){
				$array['layout'] = implode(",",$layout);
			}else{
				$array['layout'] = '';
			}
			$this->model('module')->save($array,$id);
		}else{
			$array["layout"] = "hits,dateline,sort";
			$array['mtype'] = $this->get('mtype','int');
			$id = $this->model('module')->save($array);
		}
		if(!$id){
			$this->error(P_Lang('資料儲存失敗，請檢查'));
		}
		$rs = $this->model('module')->get_one($id);
		$tbl_exists = $this->model('module')->chk_tbl_exists($id,$rs['mtype']);
		if(!$tbl_exists){
			$this->model('module')->create_tbl($id);
		}
		$this->success();
	}

	/**
	 * 欄位管理器
	**/
	public function fields_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定模型'),$this->url("module"));
		}
		$rs = $this->model('module')->get_one($id);
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$condition = "area LIKE '%module%'";
		$fields_list = $this->model('fields')->default_all();
		if($fields_list){
			foreach($fields_list as $key=>$value){
				$value["field_type_name"] = $this->field_list[$value["field_type"]]['title'];
				$value["form_type_name"] = $this->form_list[$value["form_type"]]['title'];
				$fields_list[$key] = $value;
			}
		}
		$used_list = $this->model('module')->fields_all($id,"identifier");
		if($used_list){
			foreach($used_list as $key=>$value){
				$value["field_type_name"] = $this->field_list[$value["field_type"]]['title'];
				$value["form_type_name"] = $this->form_list[$value["form_type"]]['title'];
				$value['format_type_name'] = $this->format_list[$value['format']]['title'];
				$used_list[$key] = $value;
			}
		}
		$this->assign("used_list",$used_list);
		if($fields_list && $used_list){
			$newlist = array();
			foreach($fields_list AS $key=>$value){
				if(!$used_list[$key]){
					$newlist[$key] = $value;
				}
			}
			$this->assign("fields_list",$newlist);
		}else{
			$this->assign("fields_list",$fields_list);
		}
		$this->view("module_fields");
	}

	public function field_add_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定模型ID'));
		}
		$fid = $this->get("fid");
		if(!$fid){
			$this->error(P_Lang('未指定要新增的欄位ID'));
		}
		$rs = $this->model('module')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('模型不存在'));
		}
		//取得fid的內容資訊
		$f_rs = $this->model('fields')->default_one($fid);
		if(!$f_rs){
			$this->error(P_Lang('欄位不存在'));
		}
		$title = $this->get("title");
		if(!$title){
			$title = $f_rs["title"];
		}
		$note = $this->get("note");
		if(!$note){
			$note = $f_rs["note"];
		}
		$taxis = $this->model('module')->fields_next_taxis($id);
		$tmp_array = array("module_id"=>$id);
		$tmp_array["title"] = $title;
		$tmp_array["note"] = $note;
		$tmp_array["identifier"] = $f_rs["identifier"];
		$tmp_array["field_type"] = $f_rs["field_type"];
		$tmp_array["form_type"] = $f_rs["form_type"];
		$tmp_array["form_style"] = $f_rs["form_style"];
		$tmp_array["format"] = $f_rs["format"];
		$tmp_array["content"] = $f_rs["content"];
		$tmp_array["taxis"] = $taxis;
		$tmp_array["ext"] = "";
		if($f_rs["ext"]){
			$tmp_array["ext"] = serialize($f_rs['ext']);
		}
		$this->model('module')->fields_save($tmp_array);
		//更新擴充套件表資訊
		$tbl_exists = $this->model('module')->chk_tbl_exists($id,$rs['mtype']);
		if(!$tbl_exists){
			$this->model('module')->create_tbl($id);
			$tbl_exists2 = $this->model('module')->chk_tbl_exists($id,$rs['mtype']);
			if(!$tbl_exists2){
				$this->error(P_Lang('模組建立表失敗，請檢查'));
			}
		}
		$list = $this->model('module')->fields_all($id);
		if($list){
			foreach($list as $key=>$value){
				$this->model('module')->create_fields($value['id']);
			}
		}
		$this->success();
	}

	/**
	 * 建立擴充套件欄位
	**/
	public function field_create_f()
	{
		$mid = $this->get('mid','int');
		if(!$mid){
			$this->error(P_Lang('未指定模組ID'));
		}
		$m_rs = $this->model('module')->get_one($mid);
		$this->assign('m_rs',$m_rs);
		$this->assign('mid',$mid);
		$taxis = $this->model('module')->fields_next_taxis($mid);
		$this->assign('rs',array('taxis'=>$taxis));
		$this->view('module_field_create');
	}
	
	/**
	 * 刪除欄位
	**/
	public function field_delete_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定要刪除的欄位'));
		}
		$this->model('module')->field_delete($id);
		$this->success();
	}

	/**
	 * 刪除模組
	**/
	public function delete_f()
	{
		if(!$this->popedom['set']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定要刪除的模組'));
		}
		$rs = $this->model('module')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('模組不存在'));
		}
		if($rs["status"] == '1'){
			$this->error(P_Lang('模組使用中，請先停用模組資訊'));
		}
		$this->model("module")->delete($id);
		$this->success();
	}

	/**
	 * 更新模組狀態
	**/
	public function status_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('沒有指定ID'));
		}
		$rs = $this->model('module')->get_one($id);
		$status = $rs["status"];
		$status++;
		if($status>2){
			$status = '0';
		}
		$action = $this->model('module')->update_status($id,$status);
		if(!$action){
			$this->error(P_Lang('操作失敗，請檢查SQL語句'));
		}
		$this->success($status);
	}

	/**
	 * 更新模組排序
	 * @引數 $id，數值，要排序的模組ID
	 * @引數 $taxis，排序值
	 * @返回 JSON
	 * @更新時間 2016年07月12日
	**/
	public function taxis_f()
	{
		$taxis = $this->get('taxis','int');
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('module')->update_taxis($id,$taxis);
		$this->success();
	}

	public function field_edit_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('module')->field_one($id);
		$this->assign("rs",$rs);
		$m_rs = $this->model('module')->get_one($rs['module_id']);
		$this->assign('m_rs',$m_rs);
		$this->assign("id",$id);
		$this->view("module_field_set");
	}

	/**
	 * 儲存更新的欄位排序
	 * @引數 $id，要排序的欄位ID
	 * @引數 $taxis，排序值
	 * @返回 JSON資料
	 * @更新時間 2016年07月12日
	**/
	public function field_taxis_f()
	{
		$taxis = $this->get('taxis','int');
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定要編輯的ID'));
		}
		$array = array('taxis'=>$taxis);
		$this->model('module')->fields_save($array,$id);
		$this->success();
	}

	public function field_edit_save_f()
	{
		if(!$this->popedom['set']){
			$this->json(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('module')->field_one($id);
		$module_id = $rs['module_id'];
		$title = $this->get("title");
		if(!$title){
			$this->json(P_Lang('欄位名稱不能為空'));
		}
		$ext_form_id = $this->get("ext_form_id");
		$ext = array();
		if($ext_form_id){
			$list = explode(",",$ext_form_id);
			foreach($list AS $key=>$value){
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
		$array["note"] = $this->get("note");
		$array["form_type"] = $this->get("form_type");
		$array["form_style"] = $this->get("form_style","html");
		$array["format"] = $this->get("format");
		$array["content"] = $this->get("content");
		$array["taxis"] = $this->get("taxis","int");
		$array['is_front'] = $this->get('is_front','int');
		$array["ext"] = ($ext && count($ext)>0) ? serialize($ext) : "";
		$array['search'] = $this->get('search','int');
		$array['search_separator'] = $this->get('search_separator');
		$this->model('module')->fields_save($array,$id);
		$this->model('module')->update_fields($id);
		$this->json(true);
	}

	public function field_addok_f()
	{
		if(!$this->popedom['set']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$mid = $this->get('mid','int');
		if(!$mid){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('module')->get_one($mid);
		if(!$rs){
			$this->error(P_Lang('模組資訊不存在'));
		}
		$array = array('module_id'=>$mid);
		$array['title'] = $this->get("title");
		if(!$array['title']){
			$this->error(P_Lang('名稱不能為空'));
		}
		$array['note'] = $this->get("note");
		$identifier = $this->get('identifier');
		if(!$identifier){
			$this->error(P_Lang('標識串不能為空'));
		}
		$identifier = strtolower($identifier);
		if(!preg_match("/^[a-z][a-z0-9\_]+$/u",$identifier)){
			$this->error(P_Lang('欄位標識不符合系統要求，限字母、數字及下劃線且必須是字母開頭'));
		}
		if($identifier == 'phpok'){
			$this->error(P_Lang('phpok是系統禁用字元，請不要使用'));
		}
		if(!$rs['mtype']){
			$flist = $this->model('fields')->tbl_fields('list');
			if($flist && in_array($identifier,$flist)){
				$this->json(P_Lang('字元已經存在'));
			}
		}
		$tblname = $rs['mtype'] ? $mid : 'list_'.$mid;
		$flist = $this->model('fields')->tbl_fields($tblname);
		if($flist && in_array($identifier,$flist)){
			$this->error(P_Lang('字元在擴充套件表中已使用'));
		}
		$array['identifier'] = $identifier;
		$array['field_type'] = $this->get("field_type");
		$array['form_type'] = $this->get("form_type");
		$array['form_style'] = $this->get("form_style");
		$array['format'] = $this->get("format");
		$array['content'] = $this->get("content");
		$array['taxis'] = $this->get("taxis","int");
		$array['is_front'] = $this->get('is_front','int');
		$array['search'] = $this->get('search','int');
		$array['search_separator'] = $this->get('search_separator');
		$ext_form_id = $this->get("ext_form_id");
		$ext = array();
		if($ext_form_id){
			$list = explode(",",$ext_form_id);
			foreach($list AS $key=>$value){
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
		$array['ext'] = ($ext && count($ext)>0) ? serialize($ext) : "";
		$this->model('module')->fields_save($array);
		$tbl_exists = $this->model('module')->chk_tbl_exists($mid,$rs['mtype']);
		if(!$tbl_exists){
			$this->model('module')->create_tbl($mid);
			$tbl_exists2 = $this->model('module')->chk_tbl_exists($mid,$rs['mtype']);
			if(!$tbl_exists2){
				$this->error(P_Lang('模組：[title]建立表失敗',array('title'=>$rs['title'])));
			}
		}
		$list = $this->model('module')->fields_all($mid);
		if($list){
			foreach($list as $key=>$value){
				if($flist && in_array($value['identifier'],$flist)){
					continue;
				}
				$this->model('module')->create_fields($value['id']);
			}
		}
		$this->success();
	}

	/**
	 * 匯出模組欄位，此項僅用於匯出XML配置檔案，如果模組中繫結主題或其他一些選項，在這裡不會被體現，需要您手動再繫結
	 * @引數 $id，要匯出的模組欄位ID
	**/
	public function export_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'),$this->url('module'));
		}
		$rs = $this->model('module')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('模組資料不存在'),$this->url('module'));
		}
		unset($rs['id']);
		$rslist = $this->model('module')->fields_all($id,'identifier');
		if($rslist){
			$tmplist = array();
			foreach($rslist as $key=>$value){
				unset($value['id'],$value['module_id']);
				if($value['ext']){
					$value['ext'] = unserialize($value['ext']);
				}
				$tmplist[$key] = $value;
			}
			$rs['_fields'] = $tmplist;
		}
		//將資料寫成XML
		$tmpfile = $this->dir_cache.'module.xml';
		$this->lib('xml')->save($rs,$tmpfile);
		$this->lib('phpzip')->set_root($this->dir_cache);
		$zipfile = $this->dir_cache.$this->time.'.zip';
		$this->lib('phpzip')->zip($tmpfile,$zipfile);
		$this->lib('file')->rm($tmpfile);
		//下載zipfile
		$this->lib('file')->download($zipfile,$rs['title']);
	}

	/**
	 * 模組匯入
	 * @變數 zipfile 指定的ZIP檔案地址
	**/
	public function import_f()
	{
		$zipfile = $this->get('zipfile');
		if(!$zipfile){
			$this->lib('form')->cssjs(array('form_type'=>'upload'));
			$this->addjs('js/webuploader/admin.upload.js');
			$this->view('module_import');
		}
		if(strpos($zipfile,'..') !== false){
			$this->error(P_Lang('不支援帶..上級路徑'));
		}
		if(!file_exists($this->dir_root.$zipfile)){
			$this->error(P_Lang('ZIP檔案不存在'));
		}
		$this->lib('phpzip')->unzip($this->dir_root.$zipfile,$this->dir_cache);
		if(!file_exists($this->dir_cache.'module.xml')){
			$this->error(P_Lang('匯入模組失敗，請檢查解壓縮是否成功'));
		}
		$rs = $info = $this->lib('xml')->read($this->dir_cache.'module.xml',true);
		if(!$rs){
			$this->error(P_Lang('XML內容解析異常'));
		}
		$tmp = $rs;
		if(isset($tmp['_fields'])){
			unset($tmp['_fields']);
		}
		
		$insert_id = $this->model('module')->save($tmp);
		if(!$insert_id){
			$this->error(P_Lang('模組匯入失敗，儲存模組基本資訊錯誤'));
		}
		$this->model('module')->create_tbl($insert_id);
		$tbl_exists = $this->model('module')->chk_tbl_exists($insert_id,$tmp['mtype']);
		if(!$tbl_exists){
			$this->model('module')->delete($insert_id);
			$this->error(P_Lang('建立模組表失敗'));
		}
		if(isset($rs['_fields']) && $rs['_fields']){
			foreach($rs['_fields'] as $key=>$value){
				if($value['ext'] && is_array($value['ext'])){
					$value['ext'] = serialize($value['ext']);
				}
				$value['module_id'] = $insert_id;
				$this->model('module')->fields_save($value);
				$this->model('module')->create_fields($value['id']);
			}
		}
		$this->lib('file')->rm($this->dir_cache.'module.xml');
		$this->lib('file')->rm($this->dir_root.$zipfile);
		$this->success();
	}
}