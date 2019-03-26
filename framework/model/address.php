<?php
/**
 * 會員地址庫
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年06月05日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class address_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function __destruct()
	{
		parent::__destruct();
	}

	/**
	 * 按條件取得會員地址庫數量
	 * @引數 $condition 查詢條件
	**/
	public function count($condition='')
	{
		$sql = "SELECT count(a.id) FROM ".$this->db->prefix."user_address a LEFT JOIN ".$this->db->prefix."user u ON(a.user_id=u.id)";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 取得會員地址庫
	 * @引數 $condition 查詢條件
	 * @引數 $offset 定位
	 * @引數 $psize 讀取數量
	**/
	public function get_list($condition='',$offset=0,$psize=20)
	{
		$sql = "SELECT a.*,u.user FROM ".$this->db->prefix."user_address a LEFT JOIN ".$this->db->prefix."user u ON(a.user_id=u.id)";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$sql .= " ORDER BY u.id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	/**
	 * 取得單條地址資訊
	 * @引數 $id 地址ID
	**/
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_address WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 儲存更新地址庫資訊
	 * @引數 $data 要儲存的資料，一維陣列
	 * @引數 $id 要更新的地址ID，留空表示新增
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"user_address",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"user_address");
		}
	}

	/**
	 * 刪除地址資訊
	 * @引數 $id 要刪除的地址ID 
	**/
	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."user_address WHERE id='".intval($id)."'";
		return $this->db->query($sql);
	}
}