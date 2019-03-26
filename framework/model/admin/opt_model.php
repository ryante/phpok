<?php
/**
 * 選項組管理
 * @package phpok\model\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年08月03日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class opt_model extends opt_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 選項組刪除
	 * @引數 $id 組ID
	**/
	public function group_del($id)
	{
		if(!$id){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."opt_group WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."opt WHERE group_id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 儲存選項組
	 * @引數 $data 陣列
	 * @引數 $id 組ID，為0或空時，表示新增
	**/
	public function group_save($data,$id=0)
	{
		if(!$id){
			return $this->db->insert_array($data,"opt_group");
		}else{
			return $this->db->update_array($data,"opt_group",array("id"=>$id));
		}
	}

	/**
	 * 儲存選項內容
	 * @引數 $data 陣列
	 * @引數 $id 選項ID
	**/
	public function opt_save($data,$id=0)
	{
		if(!$id){
			return $this->db->insert_array($data,"opt");
		}else{
			return $this->db->update_array($data,"opt",array("id"=>$id));
		}
	}

	/**
	 * 刪除選項
	 * @引數 $id 選項ID
	**/
	public function opt_del($id)
	{
		if(!$id) return false;
		$sql = "DELETE FROM ".$this->db->prefix."opt WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}