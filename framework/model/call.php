<?php
/**
 * 資料呼叫中心涉及到的SQL操作
 * @package phpok
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年08月23日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class call_model_base extends phpok_model
{
	public $psize = 20;
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 取得資料呼叫型別
	**/
	public function types()
	{
		$xmlfile = $this->dir_data.'xml/calltype_'.$this->site_id.'.xml';
		if(!file_exists($xmlfile)){
			$xmlfile = $this->dir_data.'xml/calltype.xml';
		}
		return $this->lib('xml')->read($xmlfile);
	}

	/**
	 * 頁碼
	**/
	public function psize($psize='')
	{
		if($psize && is_numeric($psize)){
			$this->psize = $psize;
		}
		return $this->psize;
	}
	
	/**
	 * 通過ID取得資料（此操作用於後臺）
	 * @引數 $id 主鍵ID
	 * @引數 $identifier 標識，預設是id，也可以取identifier
	**/
	public function get_one($id,$identifier='id')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE ".$identifier."='".$id."'";
		if($identifier != 'id'){
			$sql .= " AND site_id='".$this->site_id."'";
		}
		return $this->db->get_one($sql);
	}


	/**
	 * 取得列表
	 * @引數 $condition 查詢條件
	 * @引數 $offset 初始位置
	 * @引數 $psize 查詢數量
	**/
	public function get_list($condition="",$offset=0,$psize=30)
	{
		$sql = "SELECT call.* FROM ".$this->db->prefix."phpok call WHERE call.site_id='".$this->site_id."' ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		$sql.= " ORDER BY call.id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	/**
	 * 取得站點下的全部資料
	 * @引數 $site_id 站點ID
	 * @引數 $status 為1或true時表示僅查已稽核的資料
	 * @引數 $pri 主鍵，留空使用identifier
	**/
	public function get_all($site_id=0,$status=0,$pri='identifier')
	{
		if(!$site_id){
			$site_id = $this->site_id;
		}
		if(!$site_id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE site_id='".$site_id."'";
		if($status){
			$sql.= " AND status=1";
		}
		return $this->db->get_all($sql,$pri);
	}

	/**
	 * 查詢數量
	 * @引數 $condition 查詢條件
	**/
	public function get_count($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."phpok WHERE site_id='".$this->site_id."' ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		return $this->db->count($sql);
	}

	/**
	 * 檢測標識串是否存在
	 * @引數 $identifier 標識
	**/
	public function chk_identifier($identifier)
	{
		return $this->get_one_sign($identifier);
	}

	/**
	 * 通過標識串取得呼叫的配置資料
	 * @引數 $identifier 標識
	**/
	public function get_one_sign($identifier)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE identifier='".$identifier."' AND site_id='".$this->site_id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 檢測標識串是否存在
	 * @引數 $identifier 標識
	**/
	public function chksign($identifier)
	{
		return $this->get_one_sign($identifier);
	}

	/**
	 * 獲取一條資料，僅獲取已通過稽核的資料，並對擴充套件資料進行合併
	 * @引數 $identifier 標識
	 * @引數 $site_id 站點ID
	**/
	public function one($identifier,$site_id=0)
	{
		if(!$identifier){
			return false;
		}
		if(!$site_id){
			$site_id = $this->site_id;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE identifier='".$identifier."' AND site_id='".$site_id."' AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['ext']){
			$ext = unserialize($rs['ext']);
			$rs = array_merge($rs,$ext);
		}
		return $rs;
	}

	/**
	 * 取得站點下的全部資料，並對資料進行格式化
	 * @引數 $site_id 站點ID
	 * @引數 $status 為1或true時表示僅查已稽核的資料
	**/
	public function all($site_id=0,$pri='')
	{
		if($site_id && !is_numeric($site_id)){
			$pri = $site_id;
			$site_id = $this->site_id;
		}
		if(!$site_id){
			$site_id = $this->site_id;
		}
		$rslist = $this->get_all($site_id,true,$pri);
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if($value['ext']){
				$ext = unserialize($value['ext']);
				unset($value['ext']);
				$value = array_merge($value,$ext);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}
}