<?php
/**
 * 讀取內容列表，涉及到的主要表有 list及list_數字ID
 * @package phpok\model\list
 * @author qinggan <admin@phpok.com>
 * @copyright 2015-2016 深圳市錕鋙科技有限公司
 * @homepage http://www.phpok.com
 * @version 4.x
 * @license http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @update 2016年06月26日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class list_model_base extends phpok_model
{
	protected $is_biz = false;
	protected $is_user = false;
	protected $multiple_cate = false;
	/**
	 * 建構函式，繼承父Model
	**/
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 是否啟用電商
	 * @引數 $is_biz true 或 false
	**/
	public function is_biz($is_biz='')
	{
		if(isset($is_biz) && is_bool($is_biz)){
			$this->is_biz = $is_biz;
		}
		return $this->is_biz;
	}

	/**
	 * 是否有繫結會員
	 * @引數 $is_user true 或 false
	**/
	public function is_user($is_user='')
	{
		if(isset($is_user) && is_bool($is_user)){
			$this->is_user = $is_user;
		}
		return $this->is_user;
	}

	/**
	 * 是否有多級分類
	 * @引數 $is_user true 或 false
	**/
	public function multiple_cate($multiple_cate='')
	{
		if(isset($multiple_cate) && is_bool($multiple_cate)){
			$this->multiple_cate = $multiple_cate;
		}
		return $this->multiple_cate;
	}

	/**
	 * 獲取擴充套件模組使用的擴充套件欄位
	 * @引數 $mid，模組ID，數值
	 * @引數 $prefix，表別名，預設是ext
	 * @返回 字串，類似：ext.field1,ext.field2
	 * @更新時間 2016年06月26日
	**/
	public function ext_fields($mid,$prefix="ext",$condition='')
	{
		$sql = "SELECT identifier FROM ".$this->db->prefix."fields WHERE ftype='".$mid."'";
		if($condition){
			$sql .= " AND ".$condition;
		}
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		if(!$prefix){
			$prefix = 'ext';
		}
		$list = array();
		foreach($rslist as $key=>$value){
			$list[] = 'ext.'.$value['identifier'];
		}
		return implode(",",$list);
	}

	/**
	 * 獲取主題列表
	 * @引數 $mid，模組ID，數值
	 * @引數 $condition，查詢條件
	 * @引數 $offset，查詢起始位置，預設是0
	 * @引數 $psize，查詢條數，預設是0，表示不限制
	 * @引數 $orderby，排序
	 * @返回 陣列，查詢結果集，擴充套件欄位內容已經格式化
	**/
	public function get_list($mid,$condition="",$offset=0,$psize=0,$orderby="")
	{
		if(!$mid){
			return false;
		}
		$fields_list = $this->db->list_fields('list');
		$field = "l.id,u.user _user";
		foreach($fields_list as $key=>$value){
			if($value == 'id' || !$value){
				continue;
			}
			$field .= ",l.".$value;
		}
		$field_ext = $this->ext_fields($mid,'ext');
		if($field_ext){
			$field .= ",".$field_ext;
		}
		$field.= ",b.price,b.currency_id,b.weight,b.volume,b.unit";
		$sql = "SELECT ".$field." FROM ".$this->db->prefix."list l ";
		$sql.= " LEFT JOIN ".$this->db->prefix."list_".$mid." ext ON(l.id=ext.id AND l.project_id=ext.project_id) ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user u ON(l.user_id=u.id AND u.status=1) ";
		$sql.= " LEFT JOIN ".$this->db->prefix."list_biz b ON(b.id=l.id) ";
		$sql.= " LEFT JOIN ".$this->db->prefix."list_cate lc ON(l.id=lc.id) ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		if(!$orderby){
			$orderby = " l.sort DESC,l.dateline DESC,l.id DESC ";
		}
		$sql .= " ORDER BY ".$orderby." ";
		if($psize && intval($psize)){
			$offset = intval($offset);
			$sql.= " LIMIT ".$offset.",".$psize;
		}
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist) return false;
		$cid_list = array();
		foreach($rslist AS $key=>$value)
		{
			$cid_list[$value["cate_id"]] = $value["cate_id"];
		}
		$m_rs = $this->lib('ext')->module_fields($mid);
		if($m_rs){
			foreach($rslist AS $key=>$value){
				foreach($value AS $k=>$v){
					if($m_rs[$k]){
						$value[$k] = $GLOBALS['app']->lib('ext')->content_format($m_rs[$k],$v);
					}
				}
				$rslist[$key] = $value;
			}
		}
		$cid_string = implode(",",$cid_list);
		if($cid_string){
			$catelist = $GLOBALS['app']->lib('ext')->cate_list($cid_string);
			foreach($rslist as $key=>$value){
				if($value["cate_id"]){
					$value["cate_id"] = $catelist[$value["cate_id"]];
					$rslist[$key] = $value;
				}
			}
		}
		return $rslist;
	}

	/**
	 * 取得總數
	 * @引數 $mid 模組ID
	 * @引數 $condition 查詢條件
	**/
	public function get_total($mid,$condition="")
	{
		if(!$mid){
			return false;
		}
		$sql = " SELECT count(l.id) FROM ".$this->db->prefix."list l ";
		if($condition){
			if(strpos($condition,'ext.') !== false){
				$sql.= " LEFT JOIN ".$this->db->prefix."list_".$mid." ext ";
				$sql.= " ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id) ";
			}
			if(strpos($condition,'u.') !== false){
				$sql.= " LEFT JOIN ".$this->db->prefix."user u ON(l.user_id=u.id) ";
			}
			if(strpos($condition,'b.') !== false){
				$sql.= " LEFT JOIN ".$this->db->prefix."list_biz b ON(b.id=l.id) ";
			}
			if(strpos($condition,'lc.') !== false){
				$sql.= " LEFT JOIN ".$this->db->prefix."list_cate lc ON(l.id=lc.id) ";
			}
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 獲取獨立表資料
	 * @引數 $id 主題ID
	 * @引數 $mid 模組ID
	**/
	public function single_one($id,$mid=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix.$mid." WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}


	/**
	 * 獨立表資料儲存
	 * @引數 $data 要儲存的資料，如果存在 $data[id]，表示更新
	 * @引數 $mid 模組ID
	**/
	public function single_save($data,$mid=0)
	{
		if(!$data || !$mid){
			return false;
		}
		if($data['id']){
			$id = $data['id'];
			unset($data['id']);
			return $this->db->update_array($data,$mid,array('id'=>$id));
		}else{
			return $this->db->insert_array($data,$mid);
		}
	}

	/**
	 * 獨立表列表資料
	 * @引數 $mid 模組ID
	 * @引數 $condition 查詢條件
	 * @引數 $offset 起始位置
	 * @引數 $psize 查詢數量
	 * @引數 $orderby 排序
	**/
	public function single_list($mid,$condition='',$offset=0,$psize=30,$orderby='',$field='*')
	{
		$sql = "SELECT ".$field." FROM ".$this->db->prefix.$mid." ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		if(!$orderby){
			$orderby = 'id DESC';
		}
		$sql .= " ORDER BY ".$orderby." ";
		if($psize && intval($psize)>0){
			$sql .= " LIMIT ".intval($offset).",".intval($psize);
		}
		return $this->db->get_all($sql);
	}

	/**
	 * 查詢獨立表數量
	 * @引數 $mid 模組ID
	 * @引數 $condition 查詢條件
	**/
	public function single_count($mid,$condition='')
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix.$mid." ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}


	/**
	 * 刪除獨立專案下的主題資訊
	 * @引數 $id 主題ID
	 * @引數 $mid 模組ID
	**/
	public function single_delete($id,$mid=0)
	{
		if(!$id || !$mid){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix.$mid." WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得當前一個主題的資訊
	 * @引數 $id 主題ID
	 * @引數 $format 是否格式化
	**/
	public function get_one($id,$format=true)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."list WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."list_biz WHERE id='".$id."'";
		$biz_rs = $this->db->get_one($sql);
		if($biz_rs){
			foreach($biz_rs as $key=>$value){
				$rs[$key] = $value;
			}
			unset($biz_rs);
		}
		if($rs['module_id']){
			$ext_rs = $this->get_ext($rs['module_id'],$id);
			if(!$ext_rs) return $rs;
			if(!$format){
				$rs = array_merge($ext_rs,$rs);
				return $rs;
			}
			$flist = $this->model('module')->fields_all($rs['module_id'],'identifier');
			if(!$flist){
				return $rs;
			}
			foreach($flist as $key=>$value){
				$content = $ext_rs[$value['identifier']];
				$content = $this->lib('ext')->content_format($value,$content);
				$rs[$value['identifier']] = $content;
			}
		}
		return $rs;
	}

	public function call_one($id)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."list l ";
		$sql.= " WHERE l.id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function get_ext($mid,$id)
	{
		if(!$mid || !$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."list_".$mid." WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $rs;
	}

	public function get_ext_list($mid,$id)
	{
		if(!$mid || !$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."list_".$mid." WHERE id IN(".$id.")";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist) return false;
		return $rslist;
	}

	public function save($data,$id=0)
	{
		if(!$data || !is_array($data) || count($data) < 1){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"list",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"list");
		}
	}

	public function update_field($ids,$field,$val=0)
	{
		if(!$field || !$ids){
			return false;
		}
		if(is_array($ids)){
			$ids = implode(",",$ids);
		}
		$sql = "UPDATE ".$this->db->prefix."list SET ".$field."='".$val."' WHERE id IN(".$ids.")";
		return $this->db->query($sql);
	}

	/**
	 * 儲存擴充套件表資訊
	 * @引數 $data 陣列，一維
	 * @引數 $mid 模組ID
	**/
	public function save_ext($data,$mid)
	{
		if(!$data || !is_array($data) || !$mid){
			return false;
		}
		if($data['id']){
			$sql = "SELECT id FROM ".$this->db->prefix."list_".$mid." WHERE id='".$data['id']."'";
			$chk = $this->db->get_one($sql);
			if($chk){
				unset($data['id']);
				$this->db->update_array($data,'list_'.$mid,array('id'=>$chk['id']));
				return true;
			}
		}
		return $this->db->insert_array($data,"list_".$mid,"replace");
	}

	/**
	 * 更新擴充套件表資訊
	 * @引數 $data 陣列，一維陣列 
	 * @引數 $mid 模組ID
	 * @引數 $id 主題ID
	**/
	public function update_ext($data,$mid,$id)
	{
		if(!$data || !is_array($data) || !$mid || !$id){
			return false;
		}
		return $this->db->update_array($data,"list_".$mid,array("id"=>$id));
	}

	/**
	 * 儲存擴充套件分類
	 * @引數 $id 主題ID
	 * @引數 $catelist 要儲存的擴充套件分類ID，支援陣列，字串，整數
	**/
	public function save_ext_cate($id,$catelist)
	{
		if(!$id || !$catelist){
			return false;
		}
		if(is_string($catelist) || is_numeric($catelist)){
			$catelist = explode(",",$catelist);
		}
		$this->list_cate_clear($id);
		$catelist = array_unique($catelist);
		$sql = "INSERT INTO ".$this->db->prefix."list_cate(id,cate_id) VALUES ";
		foreach($catelist as $key=>$value){
			if($key>0){
				$sql .= ",";
			}
			$sql .= "('".$id."','".$value."')";
		}
		$this->db->query($sql);
		return true;
	}

	/**
	 * 刪除主題繫結的分類
	 * @引數 $id 要刪除的主題
	**/
	public function list_cate_clear($id)
	{
		$id = intval($id);
		if(!$id){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id=".$id;
		return $this->db->query($sql);
	}


	/**
	 * 批量刪除
	 * @引數 $condition 查詢條件
	 * @引數 $mid 模組ID
	 * @引數 
	**/
	public function pl_delete($condition='',$mid=0)
	{
		$sql = "SELECT id,module_id FROM ".$this->db->prefix."list WHERE ".$condition;
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		$id_list = array_keys($rslist);
		$ids = implode(",",$id_list);
		//刪除全部回覆
		$sql = "DELETE FROM ".$this->db->prefix."reply WHERE tid IN(".$ids.")";
		$this->db->query($sql);
		//刪除關鍵字記錄
		$sql = "DELETE FROM ".$this->db->prefix."tag_stat WHERE title_id IN(".$ids.")";
		$this->db->query($sql);
		//
		foreach($rslist AS $key=>$value){
			if(!$mid && $value['module_id']){
				$mid = $value['module_id'];
			}
		}
		if($mid){
			$sql = "DELETE FROM ".$this->db->prefix."list_".$mid." WHERE id IN(".$ids.")";
			$this->db->query($sql);
		}
		$sql = "DELETE FROM ".$this->db->prefix."list WHERE id IN(".$ids.")";
		$this->db->query($sql);
		return true;
	}

	
	/**
	 * 檢測主表及擴充套件表中的唯一內容記錄
	 * @引數 $field 欄位標識
	 * @引數 $val 欄位內容
	 * @引數 $pid 專案ID
	 * @引數 $mid 模組ID
	**/
	public function only_record($field,$val,$pid=0,$mid=0)
	{
		if(!$field || !$value){
			return true;
		}
		$chk = $this->main_only_check($field,$val,$pid,$mid);
		if($chk){
			return true;
		}
		$chk = $this->ext_only_check($field,$val,$pid,$mid);
		if($chk){
			return true;
		}
		return false;
	}


	/**
	 * 檢測主表中的唯一性
	 * @引數 $field 欄位標識
	 * @引數 $val 欄位內容
	 * @引數 $pid 專案ID
	 * @引數 $mid 模組ID
	**/
	public function main_only_check($field,$val,$pid=0,$mid=0)
	{
		if(!$field || !$val){
			return true;
		}
		$flist = $this->db->list_fields('list');
		if(!$flist || ($flist && !in_array($field,$flist))){
			return true;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE ".$field."='".$val."'";
		$sql .= " AND site_id='".$this->site_id."'";
		if($pid){
			$sql .= " AND project_id='".$pid."'";
		}
		if($mid){
			$sql .= " AND module_id='".$mid."'";
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 擴充套件表唯一性檢查
	 * @引數 $field 欄位標識
	 * @引數 $val 欄位內容
	 * @引數 $pid 專案ID
	 * @引數 $mid 模組ID
	**/
	public function ext_only_check($field,$val,$pid=0,$mid=0)
	{
		if(!$field || !$val || !$mid){
			return true;
		}
		//檢查表字段
		$flist = $this->db->list_fields('list_'.$mid);
		if(!$flist){
			return false;
		}
		if(!in_array($field,$flist)){
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."list_".$mid." WHERE `".$field."`='".$val."'";
		$sql .= " AND site_id='".$this->site_id."'";
		if($pid){
			$sql .= " AND project_id='".$pid."'";
		}
		return $this->db->get_one($sql);
		
	}

	private function _project_format_orderby($orderby='')
	{
		if(!$orderby){
			$orderby = "l.sort DESC,l.dateline DESC,l.id DESC";
		}
		$tmp = explode(",",$orderby);
		$list = false;
		foreach($tmp as $key=>$value){
			$value = trim($value);
			if(!$value){
				continue;
			}
			$tmp2 = explode(" ",$value);
			$type = end($tmp2);
			if(!$type){
				$type = "ASC";
			}
			$id = $tmp2[0];
			$chk = explode(".",$id);
			$field = $chk[1] ? trim($chk[1]) : $id;
			$list[] = array('id'=>$tmp2[0],'type'=>strtoupper($type),'field'=>$field);
		}
		return $list;
	}
	

	/**
	 * 取得下一個主題ID
	 * @引數 $id 當前主題ID
	 * @返回 數字或false
	 * @更新時間 
	**/
	public function get_next($id)
	{
		if(!$id){
			return false;
		}
		if(is_array($id)){
			$rs = $id;
			$id = $rs['id'];
		}else{
			$rs = $this->call_one($id);
			if(!$rs || !$rs['status'] || !$rs['project_id']){
				return false;
			}
		}
		$project = $this->model('project')->get_one($rs['project_id'],false);
		if(!$project || !$project['status']){
			return false;
		}
		$orderby = $project['orderby'] ? $project['orderby'] : 'l.id DESC';
		$orderby_list = $this->_project_format_orderby($orderby);
		$sql = $this->_np_sql($rs,$project,$orderby_list);
		$sql .= " AND l.id>".$id;
		$orderby = '';
		foreach($orderby_list as $key=>$value){
			if($orderby){
				$orderby .= ",";
			}
			$orderby .= $value['id']." ".($value['type'] == 'DESC' ? 'ASC' : 'DESC');
		}
		$sql .= " ORDER BY ".$orderby." LIMIT 1";
		$tmp = $this->db->get_one($sql);
		if(!$tmp || ($tmp && $tmp['id'] == $id)){
			return false;
		}
		return $tmp['id'];
	}

	/**
	 * 取得上一主題ID
	 * @引數 $id 當前主題ID 或主題內容
	 * @返回 數字或false
	 * @更新時間 2017年02月24日
	**/
	public function get_prev($id)
	{
		if(!$id){
			return false;
		}
		if(is_array($id)){
			$rs = $id;
			$id = $rs['id'];
		}else{
			$rs = $this->call_one($id);
			if(!$rs || !$rs['status'] || !$rs['project_id']){
				return false;
			}
		}
		$project = $this->model('project')->get_one($rs['project_id'],false);
		if(!$project || !$project['status']){
			return false;
		}
		$orderby = $project['orderby'] ? $project['orderby'] : 'l.id DESC';
		$orderby_list = $this->_project_format_orderby($orderby);
		$sql = $this->_np_sql($rs,$project,$orderby_list);
		$sql .= " AND l.id<".$id;
		$sql .= " ORDER BY ".$orderby." LIMIT 1";
		$tmp = $this->db->get_one($sql);
		if(!$tmp || ($tmp && $tmp['id'] == $id)){
			return false;
		}
		return $tmp['id'];
	}

	private function _np_sql($rs,$project,$orderby_list)
	{
		$sql = "SELECT l.id FROM ".$this->db->prefix."list l ";
		$sql.= " LEFT JOIN ".$this->db->prefix."list_".$project['module']." ext ON(l.id=ext.id) ";
		if($rs['cate_id'] && $project['cate_multiple']){
			$sql.= " LEFT JOIN ".$this->db->prefix."list_cate lc ON(l.id=lc.id) ";
		}
		if($project['is_biz'] && $orderby_list){
			$is_biz = false;
			foreach($orderby_list as $key=>$value){
				if(strpos($value['id'],'b.') !== false){
					$is_biz = true;
					break;
				}
			}
			if($is_biz){
				$sql .= " LEFT JOIN ".$this->db->prefix."list_biz b ON(l.id=b.id) ";
			}
		}
		$sql.= " WHERE l.status=1 AND l.hidden=0 ";
		if($rs['cate_id']){
			if($project['cate_multiple']){
				$sql .= " AND (l.cate_id=".$rs['cate_id']." OR lc.cate_id=".$rs['cate_id'].") ";
			}else{
				$sql .= " AND l.cate_id=".$rs['cate_id'];
			}
		}
		if($rs['project_id']){
			$sql .= " AND l.project_id=".$rs['project_id']." ";
		}
		if($rs['module_id']){
			$sql .= " AND l.module_id=".$rs['module_id']." ";
		}
		$sql .= " AND l.site_id=".$rs['site_id']." ";
		return $sql;
	}

	public function attr_list()
	{
		$xmlfile = $this->dir_data."xml/attr.xml";
		if(!file_exists($xmlfile)){
			$array = array("h"=>"頭條","c"=>"推薦","a"=>"特薦");
			return $array;
		}
		return $this->lib('xml')->read($xmlfile);
	}

	function title_list($pid=0)
	{
		if(!$pid) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."list WHERE project_id IN(".$pid.") AND status='1' ORDER BY sort ASC,dateline DESC,id DESC";
		return $this->db->get_all($sql);
	}

	function get_all($condition="",$offset=0,$psize=30,$pri="")
	{
		$sql = "SELECT l.* FROM ".$this->db->prefix."list l ";
		if($condition){
			$sql.= " WHERE ".$condition;
		}
		$sql .= " ORDER BY l.dateline DESC,l.id DESC ";
		if($psize && $psize>0){
			$offset = intval($offset);
			$sql.= " LIMIT ".$offset.",".$psize;
		}
		return $this->db->get_all($sql,$pri);
	}

	function get_all_total($condition="")
	{
		$sql = "SELECT count(l.id) FROM ".$this->db->prefix."list l ";
		if($condition)
		{
			$sql.= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	

	function get_mid($id)
	{
		$sql = "SELECT module_id FROM ".$this->db->prefix."list WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs || !$rs["module_id"])
		{
			return false;
		}
		return $rs["module_id"];
	}

	//
	function simple_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."list WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_one_condition($condition="",$mid=0)
	{
		if(!$condition || !$mid) return false;
		$sql = "SELECT l.*,ext.id _id FROM ".$this->db->prefix."list l ";
		$sql.= "JOIN ".$this->db->prefix."list_".$mid." ext ON(l.id=ext.id) WHERE ".$condition." ORDER BY l.id DESC";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		$ext_rs = $this->get_ext($rs["module_id"],$rs["id"]);
		if($ext_rs) $rs = array_merge($ext_rs,$rs);
		return $rs;
	}

	public function arc_all($project,$condition='',$field='*',$offset=0,$psize=0,$orderby='')
	{
		$sql  = " SELECT ".$field." FROM ".$this->db->prefix."list l ";
		$sql .= " JOIN ".$this->db->prefix."list_".$project['module']." ext ";
		$sql .= " ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id) ";
		if($project['is_biz']){
			$sql .= " LEFT JOIN ".$this->db->prefix."list_biz b ON(b.id=l.id) ";
		}
		if($project['cate'] && $project['cate_multiple']){
			$sql.= " LEFT JOIN ".$this->db->prefix."list_cate lc ON(l.id=lc.id) ";
		}
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		if($orderby){
			$sql .= " ORDER BY ".$orderby." ";
		}
		if($psize){
			$sql .= " LIMIT ".intval($offset).",".$psize;
		}
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		//ulist，會員資訊
		//clist，分類資訊
		//elist，擴充套件主題資訊
		$user_id_list = $idlist = $ulist = $elist = array();
		foreach($rslist as $key=>$value){
			$idlist[] = $value['id'];
			if($project['is_userid'] && $value['user_id']){
				$ulist[] = intval($value['user_id']);
				$user_id_list[$value['id']] = $value['user_id']; 
			}
			$elist[] = 'list-'.$value['id'];
		}
		//讀取會員資訊
		if($project['is_userid']){
			$user_ids = implode(",",array_unique($ulist));
			if($user_ids){
				$condition = "u.id IN(".$user_ids.") AND u.status=1";
				$ulist = $this->model('user')->get_list($condition,0,0);
				if($ulist){
					foreach($user_id_list as $key=>$value){
						if($ulist[$value]){
							$rslist[$key]['user'] = $ulist[$value];
						}
					}
				}
			}
		}
		//讀取主題分類資訊
		if($project['cate']){
			$title_ids = implode(",",array_unique($idlist));
			$sql = "SELECT lc.id,lc.cate_id,c.title,c.identifier FROM ".$this->db->prefix."list_cate lc ";
			$sql.= "LEFT JOIN ".$this->db->prefix."cate c ON(lc.cate_id=c.id) WHERE lc.id IN(".$title_ids.") ";
			$tmplist = $this->db->get_all($sql);
			if(!$tmplist){
				$sql = "SELECT l.id,l.cate_id,c.title,c.identifier FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."cate c ON(l.cate_id=c.id) ";
				$sql.= "WHERE l.id IN(".$title_ids.")";
				$tmplist = $this->db->get_all($sql);
			}
			if($tmplist){
				foreach($tmplist as $key=>$value){
					$tmp = $value;
					$tmp['url'] = $this->url($project['identifier'],$value['identifier']);
					unset($tmp['id']);
					$rslist[$value['id']]['catelist'][$value['cate_id']] = $tmp;
					$cate_id = $rslist[$value['id']]['cate_id'];
					if($cate_id && $cate_id == $value['cate_id']){
						$rslist[$value['id']]['cate'] = $tmp;
					}
				}
			}
		}

		//讀取主題的擴充套件
		$elist = array_unique($elist);
		$tmplist = $this->model('ext')->get_all($elist,true);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$k = explode('-',$key);
				$rslist[$k[1]] = array_merge($value,$rslist[$k[1]]);
			}
		}
		return $rslist;
	}

	public function arc_count($mid,$condition='')
	{
		$sql = "SELECT count(l.id) FROM ".$this->db->prefix."list l ";
		if($condition && strpos($condition,'ext.') !== false){
			$sql .= " JOIN ".$this->db->prefix."list_".$mid." ext ";
			$sql .= " ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id) ";
		}
		if($condition){
			if(strpos($condition,'b.') !== false){
				$sql .= " LEFT JOIN ".$this->db->prefix."list_biz b ON(l.id=b.id) ";
			}
			if(strpos($condition,'lc.') !== false){
				$sql.= " LEFT JOIN ".$this->db->prefix."list_cate lc ON(l.id=lc.id) ";
			}
			$sql .= " WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

	public function delete($id,$mid=0)
	{
		if(!$mid){
			$sql = "SELECT module_id FROM ".$this->db->prefix."list WHERE id='".$id."'";
			$rs = $this->db->get_one($sql);
			$mid = $rs['module_id'];
		}
		//刪除擴充套件主題資訊
		if($mid){
			//刪除附件
			$this->delete_res($id,$mid);
			$sql = "DELETE FROM ".$this->db->prefix."list_".$mid." WHERE id='".$id."'";
			$this->db->query($sql);
		}
		$sql = "DELETE FROM ".$this->db->prefix."list WHERE id='".$id."'";
		$this->db->query($sql);
		//刪除相關的回覆資訊
		$sql = "DELETE FROM ".$this->db->prefix."reply WHERE tid='".$id."'";
		$this->db->query($sql);
		//刪除Tag相關
		$sql = "DELETE FROM ".$this->db->prefix."tag_stat WHERE title_id='".$id."'";
		$this->db->query($sql);
		//刪除擴充套件分類
		$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id='".$id."'";
		$this->db->query($sql);
		//刪除主題自身的擴充套件欄位
		$sql = "SELECT id FROM ".$this->db->prefix."fields WHERE ftype='list-".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id='".$value['id']."'";
				$this->db->query($sql);
			}
			$sql = "DELETE FROM ".$this->db->prefix."fields WHERE ftype='list-".$id."'";
			$this->db->query($sql);
		}
		return true;
	}

	/**
	 * 刪除模組下的附件資訊
	 * @引數 $id 主題ID
	 * @引數 $mid 模組ID，為0時，嘗試從主題中獲取
	**/
	public function delete_res($id,$mid=0)
	{
		if(!$mid){
			$sql = "SELECT module_id FROM ".$this->db->prefix."list WHERE id='".$id."'";
			$rs = $this->db->get_one($sql);
			if(!$rs){
				return false;
			}
			$mid = $rs['module_id'];
		}
		if(!$mid){
			return false;
		}
		$module = $this->model('module')->get_one($mid);
		if(!$module){
			return false;
		}
		$table = $module['mtype'] ? $mid : "list_".$mid;
		$sql = "SELECT * FROM ".$this->db->prefix.$table." WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$flist = $this->model('module')->fields_all($mid);
		if(!$flist){
			return false;
		}
		foreach($flist as $key=>$value){
			if($value['form_type'] != 'upload'){
				continue;
			}
			if(!$rs[$value['identifier']]){
				continue;
			}
			$tmp = explode($rs[$value['identifier']]);
			if($tmp){
				foreach($tmp as $k=>$v){
					if($v && intval($v)){
						$this->model('res')->delete(intval($v));
					}
				}
			}
			$sql = "UPDATE ".$this->db->prefix.$table." SET ".$value['identifier']."='' WHERE id='".$id."'";
			$this->db->query($sql);
		}
		return true;
	}

	public function subtitle_ids($id)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE parent_id='".$id."' AND status=1 ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		return array_keys($rslist);
	}

	public function biz_attrlist($tid,$aid=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."list_attr WHERE tid='".$tid."' ";
		if($aid){
			$sql .= " AND aid='".$aid."'";
		}
		$sql.= " ORDER BY aid ASC,taxis ASC";
		return $this->db->get_all($sql);
	}

	/**
	 * 取得主題的財富基數
	 * @引數 $id 主題ID，陣列或字串或數字
	 * @返回 false/數字
	 * @更新時間 2016年11月28日
	**/
	public function integral($id='')
	{
		$id = $this->title_id_to_string($id);
		if(!$id){
			return false;
		}
		$sql = "SELECT SUM(integral) FROM ".$this->db->prefix."list WHERE status=1 AND id IN(".$id.")";
		return $this->db->count($sql);
	}

	public function integral_list($id='')
	{
		$id = $this->title_id_to_string($id);
		if(!$id){
			return false;
		}
		$sql = "SELECT id,integral FROM ".$this->db->prefix."list WHERE status=1 AND id IN(".$id.") AND integral>0";
		$list = $this->db->get_all($sql);
		if(!$list){
			return false;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			$rslist[$value['id']] = $value['integral'];
		}
		return $rslist;
	}

	/**
	 * 儲存電商資料
	 * @引數 $data 陣列，裡面含有欄位：id,unit,price,is_virtual,currency_id,weight,volume
	**/
	public function biz_save($data)
	{
		return $this->db->insert_array($data,'list_biz','replace');
	}


	private function title_id_to_string($id)
	{
		if(!$id){
			return false;
		}
		if(is_array($id)){
			$id = implode(",",$id);
		}
		$list = explode(",",$id);
		$tmp = false;
		foreach($list as $key=>$value){
			if(!$value || !intval($value)){
				continue;
			}
			if(!$tmp){
				$tmp = array();
			}
			$tmp[] = intval($value);
		}
		if(!$tmp){
			return false;
		}
		return implode(",",$tmp);
	}
}
