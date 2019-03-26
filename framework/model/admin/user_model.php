<?php
/**
 * 會員增刪改查
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年01月20日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class user_model extends user_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 刪除會員操作
	 * @引數 $id 會員ID
	**/
	public function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."user WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."user_ext WHERE id='".$id."'";
		$this->db->query($sql);
		//刪除相應的積分
		$sql = "DELETE FROM ".$this->db->prefix."wealth_info WHERE uid='".$id."'";
		$this->db->query($sql);
		//刪除積分日誌
		$sql = "DELETE FROM ".$this->db->prefix."wealth_log WHERE goal_id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function identifier_chk($identifier='')
	{
		if(!$identifier){
			return false;
		}
		$fields = $this->db->list_fields('user');
		$fields[] = 'wealth';
		$fields[] = 'introducer';
		$fields[] = 'title';
		$sql = "SELECT identifier FROM ".$this->db->prefix."fields WHERE ftype='user'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$fields[] = $value['identifier'];
			}
		}
		if(in_array($identifier,$fields)){
			return false;
		}
		return true;
	}

	/**
	 * 建立會員欄位
	**/
	public function create_fields($rs)
	{
		if(!$rs || !is_array($rs)){
			return false;
		}
		$idlist = $this->tbl_fields_list($this->db->prefix."user_ext");
		if($idlist && in_array($rs["identifier"],$idlist)){
			return true;
		}
		$tlist = array("varchar","int","float","date","datetime","text","longtext","blob","longblob");
		if(!in_array($rs["field_type"],$tlist)){
			return false;
		}
		$sql = "ALTER TABLE ".$this->db->prefix."user_ext ADD `".$rs["identifier"]."`";
		if($rs["field_type"] == "int"){
			$sql.= " INT ";
			$rs["content"] = intval($rs["content"]);
			$sql.= " NOT NULL DEFAULT '".$rs["content"]."' ";
		}elseif($rs["field_type"] == "float"){
			$sql.= " FLOAT ";
			$rs["content"] = intval($rs["content"]);
			$sql.= " NOT NULL DEFAULT '".$rs["content"]."' ";
		}elseif($rs["field_type"] == "date"){
			$sql.= " DATE NULL ";
		}elseif($rs["field_type"] == "datetime"){
			$sql.= " DATETIME NULL ";
		}elseif($rs["field_type"] == "longtext" || $rs["field_type"] == "text"){
			$sql.= " LONGTEXT NOT NULL ";
		}elseif($rs["field_type"] == "longblob" || $rs["field_type"] == "blob"){
			$sql.= " LONGBLOB NOT NULL ";
		}else{
			$sql.= " VARCHAR( 255 ) ";
			$sql.= " NOT NULL DEFAULT '".$rs["content"]."' ";
		}
		$sql.= " COMMENT  '".$rs["title"]."' ";
		return $this->db->query($sql);
	}

	public function field_delete($id)
	{
		if(!$id){
			return false;
		}
		$rs = $this->field_one($id);
		$field = $rs["identifier"];
		$sql = "ALTER TABLE ".$this->db->prefix."user_ext DROP `".$field."`";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."fields WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//後臺顯示
	public function get_one($id,$field='id',$ext=true,$wealth=true)
	{
		if(!$id){
			return false;
		}
		$sql = " SELECT u.*,e.* FROM ".$this->db->prefix."user u ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user_ext e ON(u.id=e.id) ";
		$sql.= " WHERE u.".$field."='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $rs;
	}

	/**
	 * 會員自定義欄位排序
	**/
	public function user_next_taxis()
	{
		$sql = "SELECT max(taxis) as taxis FROM ".$this->db->prefix."fields WHERE ftype='user' AND taxis<255";
		$rs = $this->db->get_one($sql);
		return $this->return_next_taxis($rs);
	}

	/**
	 * 儲存擴充套件欄位資料
	 * @引數 $data 一維陣列
	 * @引數 $id 主鍵ID，留空或為0表示寫入新的
	**/
	public function fields_save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		$data['ftype'] = 'user';
		if(isset($data['is_edit'])){
			$data['is_front'] = $data['is_edit'];
			unset($data['is_edit']);
		}
		if($id){
			return $this->db->update_array($data,"fields",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"fields");
		}
	}

	/**
	 * 簡單通過會員ID獲取會員的ID及賬號
	 * @引數 $ids 會員ID，支援資料及字串
	 * @引數 $field 欄位，要查詢的欄位
	**/
	public function simple_user_list($ids,$field='user')
	{
		if(!$ids){
			return false;
		}
		if($ids && is_array($ids)){
			$ids = implode(",",$ids);
		}
		$sql = "SELECT id,".$field." FROM ".$this->db->prefix."user WHERE id IN(".$ids.")";
		$tmplist = $this->db->get_all($sql,'id');
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$rslist[$value['id']] = $value[$field];
		}
		return $rslist;
	}
}