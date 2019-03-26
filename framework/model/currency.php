<?php
/**
 * 貨幣管理器
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年10月10日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class currency_model_base extends phpok_model
{
	private $_cache;
	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 取得國際貨幣列表
	 * @引數 $pri 多維陣列中的key定位，留空使用數字，從0起
	 * @返回 多維陣列或false
	**/
	public function get_list($pri='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."currency ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql,$pri);
	}

	/**
	 * 取得指定貨幣的基本資訊
	 * @引數 $id 主鍵ID
	 * @引數 $field_id 表字段名，在貨幣裡常指id、code、title等
	 * @返回 陣列或false
	**/
	public function get_one($id,$field_id='id')
	{
		if($field_id == 'id'){
			if($this->_cache && $this->_cache[$id]){
				return $this->_cache[$id];
			}
			$this->_cache = $this->get_list('id');
			if($this->_cache && $this->_cache[$id]){
				return $this->_cache[$id];
			}
		}
		$sql = "SELECT * FROM ".$this->db->prefix."currency WHERE ".$field_id."='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 儲存資訊，新增或更新
	 * @引數 $data 陣列
	 * @引數 $id 主鍵ID
	 * @返回 true/false/或自增ID
	**/
	public function save($data,$id="")
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"currency",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"currency","replace");
		}
	}

	/**
	 * 更新貨幣狀態
	 * @引數 $id 貨幣ID
	 * @引數 $status 為0表示禁用，1表示啟用
	 * @返回 true/false
	**/
	public function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."currency SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}
	
	/**
	 * 更新排序
	 * @引數 $id 貨幣ID
	 * @引數 $taxis 排序值，最大255，最小0
	 * @返回 true/false
	**/
	public function update_sort($id,$taxis=255)
	{
		$sql = "UPDATE ".$this->db->prefix."currency SET taxis='".$taxis."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 刪除貨幣操作，請慎用，執行此刪除後，貨幣訂單等計算會有問題
	 * @引數 $id 貨幣ID
	 * @返回 true/false
	**/
	public function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."currency WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得貨幣匯率
	 * @引數 $id 貨幣ID
	 * @引數 $field_id 表字段名，在貨幣裡常指id、code、title等
	 * @返回 匯率資訊或空
	**/
	public function rate($id,$field_id='id')
	{
		$sql = "SELECT val FROM ".$this->db->prefix."currency WHERE ".$field_id."='".$id."'";
		$tmp = $this->db->get_one($sql);
		if(!$tmp){
			return false;
		}
		return $tmp['val'];
	}
}