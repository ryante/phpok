<?php
/***********************************************************
	Filename: {phpok}models/id.php
	Note	: ID管理工具
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-11-27 13:23
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class id_model_base extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	public function get_ctrl($identifier,$site_id=0)
	{
		$site_id = $site_id ? '0,'.$site_id : '0';
		$sql = "SELECT id FROM ".$this->db->prefix."project WHERE identifier='".$identifier."' AND site_id IN(".$site_id.")";
		$rs = $this->db->get_one($sql);
		if($rs){
			return 'project';
		}
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE identifier='".$identifier."' AND site_id IN(".$site_id.")";
		$rs = $this->db->get_one($sql);
		if($rs){
			return 'content';
		}
		return false;
	}

	//檢測標識ID是否被使用了
	//identifier：字串
	//site_id，站點ID，整數
	function check_id($identifier,$site_id=0,$id=0)
	{
		$site_id = $site_id ? '0,'.$site_id : '0';
		//在專案中檢測
		$sql = "SELECT id FROM ".$this->db->prefix."project WHERE LOWER(identifier)='".strtolower($identifier)."' ";
		$sql.= "AND site_id IN(".$site_id.") ";
		if($id){
			$sql .= " AND id !=".intval($id);
		}
		$check_rs = $this->db->get_one($sql);
		if($check_rs){
			return true;
		}
		//在分類中檢測
		$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE LOWER(identifier)='".strtolower($identifier)."' AND site_id IN(".$site_id.")";
		if($id) $sql .= " AND id !=".intval($id);
		$check_rs = $this->db->get_one($sql);
		if($check_rs){
			return true;
		}
		//在內容裡檢測
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE LOWER(identifier)='".strtolower($identifier)."' AND site_id IN(".$site_id.")";
		if($id) $sql .= " AND id !=".intval($id);
		$check_rs = $this->db->get_one($sql);
		if($check_rs){
			return true;
		}
		return false;
	}

	function project_id($identifier,$site_id=0)
	{
		$site_id = $site_id ? '0,'.$site_id : '0';
		$sql = "SELECT id FROM ".$this->db->prefix."project WHERE LOWER(identifier)='".strtolower($identifier)."' ";
		$sql.= "AND site_id IN(".$site_id.") ";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $rs['id'];
	}

	/**
	 * 獲取ID屬性資訊，優先順序：專案>分類>主題
	 * @引數 $identifier 標識串
	 * @引數 $site_id 站點ID
	 * @引數 $status 為true時表示只檢索狀態為1的資料
	**/
	public function id($identifier,$site_id=0,$status=false)
	{
		$plist = $this->id_project($site_id,$status);
		if($plist && $plist[$identifier]){
			return $plist[$identifier];
		}
		$clist = $this->id_cate($site_id,$status);
		if($clist && $clist[$identifier]){
			return $clist[$identifier];
		}
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE site_id='".$site_id."' AND identifier='".$identifier."'";
		if($status){
			$sql .= " AND status=1 ";
		}
		$chk = $this->db->get_one($sql);
		if($chk && $chk['id']){
			return array('id'=>$chk['id'],'type'=>'content');
		}
		return false;
	}

	public function id_project($site_id=0,$status=0)
	{
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."project WHERE site_id='".$site_id."'";
		if($status){
			$sql .= " AND status=1 ";
		}
		$cache_id  = $this->cache->id($sql);
		$tmplist = $this->cache->get($cache_id);
		if(!$tmplist){
			$tmplist = $this->db->get_all($sql);
			if($tmplist){
				$this->cache->save($cache_id,$tmplist);
			}
		}
		if(!$tmplist){
			return false;
		}
		$plist = array();
		foreach($tmplist as $key=>$value){
			$plist[$value['identifier']] = array('id'=>$value['id'],'type'=>'project');
		}
		return $plist;
	}

	public function id_cate($site_id,$status=0)
	{
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."cate WHERE site_id='".$site_id."'";
		if($status){
			$sql .= " AND status=1 ";
		}
		$cache_id  = $this->cache->id($sql);
		$tmplist = $this->cache->get($cache_id);
		if(!$tmplist){
			$tmplist = $this->db->get_all($sql);
			if($tmplist){
				$this->cache->save($cache_id,$tmplist);
			}
		}
		if(!$tmplist){
			return false;
		}
		$clist = array();
		foreach($tmplist as $key=>$value){
			$clist[$value['identifier']] = array('id'=>$value['id'],'type'=>'cate');
		}
		return $clist;
	}

	//
	public function id_all($site_id=0,$status=0)
	{
		$cache_id = $this->cache->id('model','id','id_all',$site_id,$status);
		$rslist = $this->cache->get($cache_id);
		if($rslist){
			return $rslist;
		}
		$this->db->cache_set($cache_id);
		$rslist = array();
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."project WHERE site_id='".$site_id."'";
		if($status){
			$sql.= " AND status=1 ";
		}
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$rslist[$value['identifier']] = array('id'=>$value['id'],'type'=>'project');
			}
			unset($tmplist);
		}
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."cate WHERE site_id='".$site_id."'";
		if($status){
			$sql.= " AND status=1";
		}
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$rslist[$value['identifier']] = array('id'=>$value['id'],'type'=>'cate');
			}
		}
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."list WHERE site_id='".$site_id."' AND identifier!=''";
		if($status){
			$sql.= " AND status=1";
		}
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$rslist[$value['identifier']] = array('id'=>$value['id'],'type'=>'content');
			}
		}
		if($rslist && count($rslist)>0){
			$this->cache->save($cache_id,$rslist);
			return $rslist;
		}
		return false;
	}
}
?>