<?php
/**
 * 外掛中心
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年10月07日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class plugin_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 讀取已安裝的全部外掛
	 * @引數 $status 為1進，表示只讀取正在使用的外掛
	**/
	public function get_all($status=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."plugins ";
		if($status){
			$sql .= "WHERE status=1 ";
		}
		$sql .= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql,'id');
	}

	/**
	 * 取得全部的外掛列表
	**/
	public function dir_list()
	{
		$folder = $this->dir_root."plugins/";
		//讀取列表
		$handle = opendir($folder);
		$list = array();
		while(false !== ($file = readdir($handle))){
			if(substr($file,0,1) != "." && is_dir($folder.$file)){
				$list[] = $file;
			}
		}
		closedir($handle);
		return $list;
	}

	/**
	 * 讀取外掛基本資料
	 * @引數 $id 外掛標識
	**/
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."plugins WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 讀取外掛配置檔案XML資料，僅在安裝時有效
	 * @引數 $id 外掛標識
	**/
	public function get_xml($id)
	{
		$folder = $this->dir_root."plugins/".$id."/";
		if(!is_dir($folder)){
			return false;
		}
		$rs = array();
		if(file_exists($folder."config.xml")){
			$rs = $this->lib('xml')->read($folder.'config.xml');
		}
		$rs["id"] = $id;
		$rs["path"] = $folder;
		return $rs;
	}

	/**
	 * 儲存安裝的資料
	 * @引數 $data 外掛資料
	**/
	public function install_save($data)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		$this->db->insert_array($data,'plugins','replace');
		$sql = "SELECT id FROM ".$this->db->prefix."plugins WHERE id='".$data['id']."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $rs['id'];
	}

	/**
	 * 更新外掛擴充套件資料
	 * @引數 $id 外掛標識
	 * @引數 $info 外掛擴充套件資料
	**/
	public function update_param($id,$info='')
	{
		if($info && is_array($info)){
			$info = serialize($info);
		}
		$sql = "UPDATE ".$this->db->prefix."plugins SET param='".$info."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 更新外掛資訊
	 * @引數 $data 外掛基本資料
	 * @引數 $id 外掛標識
	**/
	public function update_plugin($data,$id)
	{
		if(!$data || !$id || !is_array($data)){
			return false;
		}
		$this->db->update_array($data,'plugins',array('id'=>$id));
	}

	/**
	 * 刪除外掛
	 * @引數 $id 外掛標識
	**/
	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."plugins WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 更新外掛狀態
	 * @引數 $id 外掛標識
	 * @引數 $status 狀態，1使用，0未使用
	**/
	public function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."plugins SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}