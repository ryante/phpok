<?php
/**
 * 郵件內容管理器
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年04月22日
**/

class email_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function get_one($id)
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."email WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function get_list($condition="",$offset=0,$psize=20)
	{
		$sql = " SELECT * FROM ".$this->db->prefix."email ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		$sql.= " ORDER BY id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	public function simple_list($siteid=0)
	{
		$condition = $siteid ? "site_id IN(0,".$siteid.")" : "site_id=0";
		$sql = "SELECT id,identifier,title,note FROM ".$this->db->prefix."email WHERE ".$condition;
		return $this->db->get_all($sql);
	}

	//取得總數量
	public function get_count($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."email ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 儲存郵件內容資訊
	 * @引數 $data 陣列，要寫入的資料
	 * @引數 $id 大於0時表示更新
	**/
	public function save($data,$id=0)
	{
		if($id){
			$this->db->update_array($data,"email",array("id"=>$id));
			return true;
		}else{
			$insert_id = $this->db->insert_array($data,"email");
			return $insert_id;
		}
	}

	/**
	 * 刪除郵件內容
	 * @引數 $id 要刪除的郵件ID，多個ID用英文逗號隔開
	**/
	public function del($id=0)
	{
		if(!$id){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."email WHERE id IN(".$id.")";
		return $this->db->query($sql);
	}

	/**
	 * 檢測標識是否存在
	 * @引數 $identifier 標識
	 * @引數 $site_id 站點ID
	 * @引數 $id 不檢查指定的ID
	**/
	public function get_identifier($identifier,$site_id=0,$id=0)
	{
		if(!$site_id){
			$site_id = $this->site_id;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."email WHERE identifier='".$identifier."' AND site_id='".$site_id."'";
		if($id){
			$sql .= " AND id !='".$id."'";
		}
		$sql .= " ORDER BY id DESC LIMIT 1";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得模板內容
	 * @引數 $code 標識ID
	 * @引數 $site_id 站點ID
	**/
	public function tpl($code,$site_id=0)
	{
		return $this->get_identifier($code,$site_id);
	}
}