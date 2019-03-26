<?php
/*****************************************************************************************
	檔案： {phpok}/model/admin/project_model.php
	備註： 專案管理
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2015年02月05日 23時22分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class project_model extends project_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	//儲存核心選單
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		if(!$id)
		{
			return $this->db->insert_array($data,"project");
		}
		else
		{
			$this->db->update_array($data,"project",array("id"=>$id));
			return true;
		}
	}

	public function insert($data)
	{
		if(!$data || !is_array($data)) return false;
		return $this->db->insert_array($data,"project");
	}

	public function update($data,$id)
	{
		if(!$data || !is_array($data) || !$id) return false;
		return $this->db->update_array($data,"project",array("id"=>$id));
	}

	//設定狀態
	public function status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."project SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 設定隱藏或顯示
	 * @引數 $id 專案ID
	 * @引數 $hidden 1表示隱藏，0表示顯示
	**/
	public function set_hidden($id,$hidden=0)
	{
		$sql = "UPDATE ".$this->db->prefix."project SET hidden='".$hidden."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//更新排序
	public function update_taxis($id,$taxis="0")
	{
		$sql = "UPDATE ".$this->db->prefix."project SET taxis='".$taxis."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//刪除專案操作
	public function delete_project($id)
	{
		if(!$id || !intval($id)){
			return false;
		}
		$id = intval($id);
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE id=".$id;
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['module']){
			$table_list = $this->db->list_tables();
			$module = $this->model('module')->get_one($rs['module']);
			if($module['mtype']){
				$tmp = $this->db->prefix.$rs['module'];
				if(in_array($tmp,$table_list)){
					$sql = "DELETE FROM ".$tmp." WHERE project_id='".$id."'";
					$this->db->query($sql);
				}
			}else{
				$tmp = $this->db->prefix."list_".$rs['module'];
				if(in_array($tmp,$table_list)){
					$sql = "DELETE FROM ".$tmp." WHERE project_id=".$id;
					$this->db->query($sql);
				}
				$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id IN(SELECT id FROM ".$this->db->prefix."list WHERE project_id='".$id."')";
				$this->db->query($sql);
				$sql = "DELETE FROM ".$this->db->prefix."list_biz WHERE id IN(SELECT id FROM ".$this->db->prefix."list WHERE project_id='".$id."')";
				$this->db->query($sql);
				$sql = "DELETE FROM ".$this->db->prefix."list_attr WHERE tid IN(SELECT id FROM ".$this->db->prefix."list WHERE project_id='".$id."')";
				$this->db->query($sql);
				$sql = "DELETE FROM ".$this->db->prefix."list WHERE project_id=".$id;
				$this->db->query($sql);
			}
		}

		//刪除專案擴充套件
		$sql = "SELECT id FROM ".$this->db->prefix."fields WHERE ftype='project-".$id."'";
		$extlist = $this->db->get_all($sql);
		if($extlist){
			foreach($extlist as $key=>$value){
				$this->db->query("DELETE FROM ".$this->db->prefix."extc WHERE id='".$value['id']."'");
			}
			$this->db->query("DELETE FROM ".$this->db->prefix."fields WHERE ftype='project-".$id."'");
		}
		$sql = "DELETE FROM ".$this->db->prefix."project WHERE id='".$id."'";
		$this->db->query($sql);
		//刪除後臺許可權配置
		$sql = "SELECT id FROM ".$this->db->prefix."popedom WHERE pid='".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$this->db->query("DELETE FROM ".$this->db->prefix."adm_popedom WHERE pid='".$value['id']."'");
			}
			$this->db->query("DELETE FROM ".$this->db->prefix."popedom WHERE pid='".$id."'");
		}
		return true;
	}

	public function project_next_sort($pid=0)
	{
		$sql = "SELECT max(taxis) as taxis FROM ".$this->db->prefix."project WHERE site_id='".$this->site_id."'";
		if($pid){
			$sql .= " AND parent_id='".$pid."'";
		}else{
			$sql .= " AND parent_id=0";
		}
		$rs = $this->db->get_one($sql);
		return $this->return_next_taxis($rs);
	}
	
}