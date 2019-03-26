<?php
/**
 * 管理員資訊管理
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年08月23日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 通過管理員賬號取得管理員資訊
	 * @引數 $username
	**/
	public function get_one_from_name($username)
	{
		if(!$username) return false;
		return $this->get_one($username,"account");
	}

	/**
	 * 取得一條管理員資料
	 * @引數 $id 引數值
	 * @引數 $field 引數名稱，可選：account，id
	**/
	public function get_one($id,$field="id")
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."adm WHERE ".$field."='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 檢測管理員賬號是否存在
	 * @引數 $account 賬號
	 * @引數 $id 不包括指定的ID
	**/
	public function check_account($account,$id=0)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."adm WHERE account='".$account."'";
		if($id){
			$sql .= " AND id !='".$id."'";
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 更新管理員密碼 
	 * @引數 $id 管理員ID
	 * @引數 $password 密碼，必須是已加密過的
	**/
	public function update_password($id,$password)
	{
		if(!$id || !$password){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."adm SET pass='".$password."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得管理員列表
	 * @引數 $condition 查詢條件 
	 * @引數 $offset 起始位置
	 * @引數 $psize 查詢數量
	**/
	public function get_list($condition="",$offset=0,$psize=30)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."adm ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		$sql .= " ORDER BY id DESC ";
		if($psize && intval($psize) > 0){
			$offset = intval($offset);
			$sql .= " LIMIT ".$offset.",".$psize;
		}
		return $this->db->get_all($sql);
	}

	/**
	 * 取得管理員數量
	 * @引數 $condition 查詢條件
	**/
	public function get_total($condition='')
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."adm ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

	/**
	 * 取得管理員許可權
	 * @引數 $id 管理員ID
	**/
	public function get_popedom_list($id)
	{
		$sql = "SELECT pid FROM ".$this->db->prefix."adm_popedom WHERE id='".$id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$list = array();
		foreach($rslist AS $key=>$value){
			$list[] = $value["pid"];
		}
		return $list;
	}

	/**
	 * 刪除管理員
	 * @引數 $id 管理員ID
	**/
	public function delete($id)
	{
		if(!$id){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."adm WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."adm_popedom WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 儲存管理員資訊
	 * @引數 $data 管理員資料，一維陣列
	 * @引數 $id 不為空時表示更新，為空或0時表示新增
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"adm",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"adm");
		}
	}

	/**
	 * 清除非系統管理中許可權
	 * @引數 $id 管理員ID
	**/
	public function clear_popedom($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."adm_popedom WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 儲存許可權
	 * @引數 $data 許可權ID，支援陣列及字串
	 * @引數 $id 管理員ID
	**/
	public function save_popedom($data,$id)
	{
		if(!$id || !$data){
			return false;
		}
		if(!is_array($data)){
			$data = explode(",",$data);
		}
		foreach($data as $key=>$value){
			$tmp = array("id"=>$id,"pid"=>$value);
			$this->db->insert_array($tmp,"adm_popedom","replace");
		}
		return true;
	}

	/**
	 * 更新管理員狀態
	 * @引數 $id 管理員ID
	 * @引數 $status 狀態，0禁用，1使用
	**/
	public function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."adm SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

}