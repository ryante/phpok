<?php
/**
 * 資源分類
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年03月21日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class rescate_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function get_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate ORDER BY id ASC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."gd ORDER BY id ASC";
		$gdlist = $this->db->get_all($sql,'id');
		if(!$gdlist){
			$gdlist = array();
		}
		foreach($rslist as $key=>$value){
			$gds = false;
			if($value['gdall']){
				foreach($gdlist as $k=>$v){
					$gds[] = $v['identifier'];
				}
			}else{
				$types = $value['gdtypes'] ? explode(',',$value['gdtypes']) : array();
				foreach($types as $k=>$v){
					if($gdlist[$v]){
						$gds[] = $gdlist[$v]['identifier'];
					}
				}
			}
			$value['gdtypes'] = $gds ? implode('/',$gds) : '';
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	public function get_one($id='')
	{
		if(!$id){
			return $this->get_default();
		}
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function get_default()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate WHERE is_default=1";
		return $this->db->get_one($sql);
	}

	/**
	 * 獲取分類資訊，分類ID內容不存在時讀預設分類
	 * @引數 $id 分類ID，為空讀預設分類
	 * @返回 false 或 array
	**/
	public function cate_info($id='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate WHERE is_default=1";
		if($id && intval($id)>0){
			$sql .= " OR id='".intval($id)."'";
		}
		$sql .= " ORDER BY is_default ASC LIMIT 1";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得附件下的全部分類
	 * @返回 陣列
	**/
	public function cate_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate ORDER BY id ASC";
		return $this->db->get_all($sql);
	}

	/**
	 * 獲取儲存類別樣式
	**/
	public function etypes_all()
	{
		
		$list = $this->lib('file')->ls($this->dir_gateway.'object-storage/');
		if(!$list){
			return false;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			$file = $value.'/config.xml';
			if(!is_file($file)){
				continue;
			}
			$info = $this->lib('xml')->read($file);
			if(!$info){
				continue;
			}
			$rslist[basename($value)] = $info;
		}
		if(!$rslist || count($rslist)<1){
			return false;
		}
		return $rslist;
	}

	public function etypes_one($id)
	{
		//
	}
}