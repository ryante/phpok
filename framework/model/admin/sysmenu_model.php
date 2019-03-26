<?php
/**
 * 系統選單管理器
 * @package phpok\model\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年07月31日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class sysmenu_model extends sysmenu_model_base
{
	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 刪除核心選單，同時刪除相應的許可權配置
	 * @引數 $id 選單ID
	 * @返回 true
	**/
	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."sysmenu WHERE id='".$id."' AND parent_id !=0";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."popedom WHERE gid='".$id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 儲存核心選單資料
	 * @引數 $data 一維陣列，要儲存的資料
	 * @引數 $id 當ID為0或空時，表示新增，反之表示更新
	**/
	public function save($data,$id=0)
	{
		if(!$id){
			return $this->db->insert_array($data,"sysmenu");
		}else{
			return $this->db->update_array($data,"sysmenu",array("id"=>$id));
		}
	}

	/**
	 * 更新核心選單的狀態
	 * @引數 $id 主鍵ID
	 * @引數 $status 要變更的值
	**/
	public function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."sysmenu SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 更新核心選單的排序
	 * @引數 $id 主鍵ID
	 * @引數 $taxis 排序的值
	 * @更新時間 
	**/
	public function update_taxis($id,$taxis=255)
	{
		$taxis = intval($taxis);
		if($taxis > 255){
			$taxis = 255;
		}
		$sql = "UPDATE ".$this->db->prefix."sysmenu SET taxis='".$taxis."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}