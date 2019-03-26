<?php
/***********************************************************
	Filename: {phpok}/model/ext.php
	Note	: 擴充套件欄位管理器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-03-05 16:56
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ext_model_base extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	# 檢查欄位是否有被使用
	function check_identifier($identifier,$module)
	{
		if(!$identifier || !$module) return false;
		$sql = "SELECT id FROM ".$this->db->prefix."fields WHERE identifier='".$identifier."' AND ftype='".$module."'";
		return ($this->db->get_one($sql) ? true : false);
	}

	/**
	 * 讀取模組下的欄位內容
	 * @引數 $module 模組名稱
	 * @引數 $show_content 是否讀取內容，預設true
	**/
	public function ext_all($module,$show_content=true)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE ftype='".$module."' ORDER BY taxis ASC,id DESC";
		if($show_content){
			$sql = "SELECT e.*,c.content content_val FROM ".$this->db->prefix."fields e ";
			$sql.= "LEFT JOIN ".$this->db->prefix."extc c ON(e.id=c.id) ";
			$sql.= "WHERE e.ftype='".$module."' ";
			$sql.= "ORDER BY e.taxis asc,id DESC";
		}
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		if($show_content){
			foreach($rslist AS $key=>$value){
				if($value['content_val']){
					$value["content"] = $value['content_val'];
				}
				unset($value['content_val']);
				$rslist[$key] = $value;
			}
		}
		return $rslist;
	}

	

	# 取得資料庫下的欄位
	# tbl 指定資料表名，多個資料表用英文逗號隔開
	# prefix 表名是否帶有字首，預設不帶
	function fields($tbl,$prefix=false)
	{
		if(!$tbl) return false;
		$list = explode(",",$tbl);
		$idlist = array();
		foreach($list AS $key=>$value)
		{
			$table = $prefix ? $value : $this->db->prefix.$value;
			$extlist = $this->db->list_fields($table);
			if($extlist)
			{
				$idlist = array_merge($idlist,$extlist);
			}
		}
		foreach($idlist AS $key=>$value)
		{
			$idlist[$key] = strtolower($value);
		}
		return array_unique($idlist);
	}

	# 取得單個欄位的配置
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}


	//取得所有擴充套件選項資訊
	function get_all($id,$mult = false)
	{
		$sql = "SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM ".$this->db->prefix."fields ext ";
		$sql.= "JOIN ".$this->db->prefix."extc extc ON(ext.id=extc.id) ";
		if($mult){
			if(is_array($id)){
				$id = implode(",",$id);
			}
			$id = str_replace(",","','",$id);
			$sql .= " WHERE ext.ftype IN('".$id."')";
		}else{
			$sql .= " WHERE ext.ftype='".$id."'";
		}
		$sql .= ' ORDER BY ext.taxis ASC,ext.id DESC';
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$rs = array();
		foreach($rslist as $key=>$value){
			if($mult){
				$rs[$value["ftype"]][$value["identifier"]] = $this->lib('form')->show($value);
			}else{
				$rs[$value["identifier"]] = $this->lib('form')->show($value);
			}
		}
		return $rs;
	}

	public function get_all_like($id)
	{
		$sql = "SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM ".$this->db->prefix."fields ext ";
		$sql.= "JOIN ".$this->db->prefix."extc extc ON(ext.id=extc.id) ";
		$sql.= "WHERE ext.ftype LIKE '".$id."%' ORDER BY ext.taxis ASC,ext.id DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist) return false;
		$list = false;
		foreach($rslist AS $key=>$value)
		{
			$list[$value["ftype"]][$value["identifier"]] = content_format($value);
		}
		return $list;
	}
}