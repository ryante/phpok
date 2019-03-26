<?php
/**
 * 模組管理
 * @package phpok\model\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年10月04日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class module_model extends module_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function module_next_taxis()
	{
		$sql = "SELECT max(taxis) as taxis FROM ".$this->db->prefix."module WHERE taxis<255";
		$rs = $this->db->get_one($sql);
		return $this->return_next_taxis($rs);
	}

	public function fields_next_taxis($mid)
	{
		$sql = "SELECT max(taxis) as taxis FROM ".$this->db->prefix."fields WHERE ftype='".$mid."' AND taxis<255";
		$rs = $this->db->get_one($sql);
		return $this->return_next_taxis($rs);
	}

	/**
	 * 建立模組系統表
	 * @引數 $id 模組ID
	**/
	public function create_tbl($id)
	{
		$rs = $this->get_one($id);
		if(!$rs){
			return false;
		}
		$tblname = $rs['mtype'] ? $this->db->prefix.$id : $this->db->prefix."list_".$id;
		$pri_id = 'id';
		$note = $rs['title'];
		$this->db->create_table_main($tblname,$pri_id,$note);
		//建立 site_id 欄位
		$data = array('id'=>'site_id','type'=>'MEDIUMINT','unsigned'=>true,'notnull'=>true,'default'=>'0');
		$data['comment'] = '網站ID';
		$this->db->update_table_fields($tblname,$data);
		$data = array('id'=>'project_id','type'=>'MEDIUMINT','unsigned'=>true,'notnull'=>true,'default'=>'0');
		$data['comment'] = '專案ID';
		$this->db->update_table_fields($tblname,$data);
		if($rs['mtype']){
			//建立 site_id 對應的索引
			$this->db->update_table_index($tblname,'site_id_index',array('site_id','project_id'));
		}else{
			$data = array('id'=>'cate_id','type'=>'MEDIUMINT','unsigned'=>true,'notnull'=>true,'default'=>'0');
			$data['comment'] = '主分類ID';
			$this->db->update_table_fields($tblname,$data);
			//建立 site_id 對應的索引
			$this->db->update_table_index($tblname,'site_id_index',array('site_id','project_id','cate_id'));
		}
		return true;
	}

	/**
	 * 更新欄位
	 * @引數 $id module_fields 表中的欄位ID
	**/
	public function update_fields($id)
	{
		return $this->_fields_action($id);
	}

	/**
	 * 建立欄位
	 * @引數 $id module_fields 表中的欄位ID
	 * @引數 $rs module_fields 陣列
	**/
	public function create_fields($id,$rs='')
	{
		return $this->_fields_action($id);
	}

	private function _fields_action($id)
	{
		if(!$id){
			return false;
		}
		$rs = $this->field_one($id);
		if(!$rs){
			return false;
		}
		$table = $this->get_one($rs['ftype']);
		if(!$this->chk_tbl_exists($table['id'],$table['mtype'])){
			return false;
		}
		$tblname = $table['mtype'] ? $this->db->prefix.$table['id'] : $this->db->prefix."list_".$table['id'];
		$data = array('id'=>$rs['identifier'],'type'=>$rs['field_type'],'unsigned'=>false);
		$data['notnull'] = true;
		if($rs['field_type'] == 'date' || $rs['field_type'] == 'datetime'){
			$data['notnull'] = false;
		}
		if($rs['content'] != ''){
			$data['default'] = (string) $rs['content'];
		}
		if(!$rs['content'] && ($rs['field_type'] == 'int' || $rs['field_type'] == 'float')){
			$date['default'] = '0';
		}
		$data['comment'] = $rs['title'];
		if($rs['type'] == 'varchar'){
			$data['length'] = 255;
		}
		return $this->db->update_table_fields($tblname,$data);
	}

	/**
	 * 刪除欄位
	 * @引數 $id 要刪除的欄位ID，數值
	**/
	public function field_delete($id)
	{
		if(!$id){
			return false;
		}
		$rs = $this->field_one($id);
		$table = $this->get_one($rs['ftype']);
		$tblname = $table['mtype'] ? $this->db->prefix.$table['id'] : $this->db->prefix."list_".$table['id'];
		$idlist = $this->db->list_fields($tblname,false);
		if($idlist && in_array($rs['identifier'],$idlist)){
			$this->db->delete_table_fields($tblname,$rs['identifier']);
		}
		$sql = "DELETE FROM ".$this->db->prefix."fields WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 刪除模組操作
	 * @引數 $id 模組ID
	**/
	public function delete($id)
	{
		if(!$id){
			return false;
		}
		$rs = $this->get_one($id);
		
		$tblname = $rs['mtype'] ? $this->db->prefix.$id : $this->db->prefix."list_".$id;
		$this->db->delete_table($tblname,false);
		if(!$rs['mtype']){
			$sql = "SELECT id FROM ".$this->db->prefix."list WHERE module_id='".$id."'";
			$rslist = $this->db->get_all($sql);
			if($rslist){
				foreach($rslist as $key=>$value){
					//刪除主題繫結的分類
					$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id='".$value['id']."'";
					$this->db->query($sql);
					//刪除主題電商相關
					$sql = "DELETE FROM ".$this->db->prefix."list_biz WHERE id='".$value['id']."'";
					$this->db->query($sql);
					//刪除主題相關屬性
					$sql = "DELETE FROM ".$this->db->prefix."list_attr WHERE tid='".$value['id']."'";
					$this->db->query($sql);
				}
				$sql = "DELETE FROM ".$this->db->prefix."list WHERE module_id='".$id."'";
				$this->db->query($sql);
			}
			//更新專案資訊
			$sql = "UPDATE ".$this->db->prefix."project SET module='0' WHERE module='".$id."'";
			$this->db->query($sql);
		}
		//刪除擴充套件欄位
		$sql = "DELETE FROM ".$this->db->prefix."fields WHERE ftype='".$id."'";
		$this->db->query($sql);
		//刪除記錄
		$sql = "DELETE FROM ".$this->db->prefix."module WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."module SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 更新排序
	 * @引數 $id 模組ID
	 * @引數 $taxis 排序值
	**/
	public function update_taxis($id,$taxis=255)
	{
		$sql = "UPDATE ".$this->db->prefix."module SET taxis='".$taxis."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 儲存模組下的欄位表
	 * @引數 $data 陣列
	 * @引數 $id 大於0表示更新，小於等於0或為空表示新增
	**/
	public function fields_save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($data['module_id'] && !isset($data['ftype'])){
			$data['ftype'] = $data['module_id'];
		}
		if(isset($data['module_id'])){
			unset($data['module_id']);
		}
		if($id){
			return $this->db->update_array($data,"fields",array("id"=>$id));
		}
		return $this->db->insert_array($data,"fields");
	}

	/**
	 * 儲存模組表
	 * @引數 $data 模組資訊，陣列
	 * @引數 $id 大於0表示更新，小於等於0或為空表示新增
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"module",array("id"=>$id));
		}
		return $this->db->insert_array($data,"module");
	}
}