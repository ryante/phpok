<?php
/**
 * 欄位增刪查改
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2018年05月18日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class fields_model_base extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	
	/**
	 * 讀取 qigngan_fields 表下的一條欄位配置資訊，返回的 ext 資訊已經自動轉成陣列
	 * @引數 $id 主鍵ID
	**/
	public function one($id)
	{
		if(!$id || !intval($id)){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE id=".intval($id);
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
	 * 讀取模組下的所有擴充套件欄位資訊，返回的 ext 資訊已自動轉成陣列模式
	 * @引數 $ftype 模組ID 或 模組型別
	 * @引數 $primary 自定義 key 鍵，預設為空，支援 id 和 identifier
	**/
	public function flist($ftype,$primary='')
	{
		if(!$ftype){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE ftype='".$ftype."' ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql,$primary);
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if($value['ext']){
				$value['ext'] = unserialize($value['ext']);
				$rslist[$key] = $value;
			}
		}
		return $rslist;
	}

	/**
	 * 取得指定頁面下的欄位
	**/
	public function fields_list($words="",$offset=0,$psize=40,$type="")
	{
		if(!$words){
			$words = "id,identifier";
		}
		if(is_string($words)){
			$words = explode(",",$words);
		}
		$rslist = $this->_all();
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if(in_array($key,$words)){
				unset($rslist[$key]);
			}
		}
		if(!$rslist){
			return false;
		}
		return $rslist;
	}

	private function _all()
	{
		$flist = $this->lib('file')->ls($this->dir_data.'xml/fields/');
		if(!$flist){
			return false;
		}
		$rslist = array();
		foreach($flist as $key=>$value){
			$rs = $this->lib('xml')->read($value);
			$rslist[$rs['identifier']] = $rs;
		}
		ksort($rslist);
		return $rslist;
	}

	function fields_count($words,$type="")
	{
		if(!$words) $words = "id,identifier";
		$sql = "SELECT count(id) FROM ".$this->db->prefix."fields ";
		$list = explode(",",$words);
		$list = array_unique($list);
		$words = implode("','",$list);
		$sql .= " WHERE identifier NOT IN ('".$words."') ";
		if($type)
		{
			$sql .= " AND area LIKE '%".$type."%'";
		}
		return $this->db->count($sql);
	}

	function get_list($id)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE id IN(".$id.") ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	//判斷欄位是否被使用了
	function is_has_sign($identifier,$id=0)
	{
		if(!$identifier){
			return true;
		}
		$sql = "SELECT identifier FROM ".$this->db->prefix."fields WHERE identifier='".$identifier."' ";
		if($id){
			$sql .= " AND id !='".$id."' ";
		}
		$rs = $this->db->get_one($sql);
		if($rs){
			return true;
		}
		$idlist = array("title","phpok","identifier","app");
		$idlist = $this->_rslist("list",$idlist);
		if($idlist){
			$idlist = array_unique($idlist);
			if(in_array($identifier,$idlist)){
				return true;
			}
		}
		return false;
	}

	public function tbl_fields($tbl)
	{
		return $this->_rslist($tbl);
	}

	private function _rslist($tbl,$idlist=array())
	{
		$sql = "SHOW FIELDS FROM ".$this->db->prefix.$tbl;
		$rslist = $this->db->get_all($sql);
		if($rslist){
			$idlist = array();
			foreach($rslist AS $key=>$value){
				$idlist[] = $value["Field"];
			}
			return $idlist;
		}else{
			return false;
		}
	}


	//取得資料表字段設定的欄位型別
	function type_all()
	{
		$array = array(
			"varchar"=>"字串",
			"int"=>"整型",
			"float"=>"浮點型",
			"date"=>"日期",
			"datetime"=>"日期時間",
			"longtext"=>"長文字",
			"longblob"=>"二進位制資訊"
		);
		return $array;
	}

	public function list_fields()
	{
		return $this->db->list_fields('list');
	}	
}