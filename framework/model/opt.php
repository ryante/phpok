<?php
/**
 * 選項組資訊
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年08月03日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class opt_model_base extends phpok_model
{
	private $_cache;
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 取得全部的選項組
	**/
	function group_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."opt_group ORDER BY id DESC";
		return $this->db->get_all($sql);
	}

	/**
	 * 取得某個組資訊
	 * @引數 $id 組ID
	**/
	public function group_one($id)
	{
		$cache_id = "group_one_".$id;
		if(isset($this->_cache[$cache_id])){
			return $this->_cache[$cache_id];
		}
		$sql = "SELECT * FROM ".$this->db->prefix."opt_group WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$this->_cache[$cache_id] = $rs;
		return $rs;
	}

	/**
	 * 取得值列表
	 * @引數 $condition 查詢條件
	 * @引數 $offset 開始位置
	 * @引數 $psize 內容數
	**/
	public function opt_list($condition="",$offset=0,$psize=20)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE 1=1 ";
		if($condition){
			$sql .= " AND ".$condition;
		}
		$sql .= " ORDER BY taxis ASC";
		$sql .= " LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	/**
	 * 選項列表
	 * @引數 $condition 查詢條件
	**/
	public function opt_all($condition="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."opt ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$sql .= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	/**
	 * 格式化多維陣列
	 * @引數 $groupid 組ID
	 * @引數 $pid 父級ID
	**/
	public function opt_format($groupid=0,$pid=0)
	{
		if(!$groupid){
			return false;
		}
		$condition = "group_id=".intval($groupid);
		$rslist = $this->opt_all($condition);
		if(!$rslist){
			return false;
		}
		$list = array();
		$this->_opt_format($list,$rslist,$pid);
		return $list;
	}

	private function _opt_format(&$list,$rslist,$pid=0)
	{
		foreach($rslist as $key=>$value){
			if($value['parent_id'] == $pid){
				$tmp = $value;
				$tmp['sublist'] = array();
				$this->_opt_format($tmp['sublist'],$rslist,$value['id']);
				$list[] = $tmp;
			}
		}
	}

	/**
	 * 取得數量總數
	 * @引數 $condition 查詢條件
	**/
	public function opt_count($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."opt ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 取得選項內容
	 * @引數 $id 選項ID
	**/
	public function opt_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得單條選項內容
	 * @引數 $condition 查詢條件
	**/
	public function opt_one_condition($condition)
	{
		if(!$condition){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE ".$condition;
		return $this->db->get_one($sql);
	}

	/**
	 * 選項內容
	 * @引數 $gid 組ID
	 * @引數 $val 值
	**/
	public function opt_val($gid,$val)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE val='".$val."' AND group_id='".$gid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$array = array('val'=>$val,'title'=>($rs['title'] ? $rs['title'] : $val));
		return $array;
	}

	/**
	 * 檢測值是否重複
	 * @引數 $gid 組ID
	 * @引數 $val 值
	 * @引數 $pid 當前父級ID
	 * @引數 $id 當前ID
	**/
	public function chk_val($gid,$val,$pid=0,$id=0)
	{
		$cache_id = "chk_val_".$gid.'_'.$val."_".$pid."_".$id;
		if(isset($this->_cache[$cache_id])){
			return $this->_cache[$cache_id];
		}
		$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE val='".$val."' AND group_id='".$gid."'";
		$sql.= " AND parent_id='".$pid."'";
		if($id){
			$sql .= " AND id !='".$id."'";
		}
		$rs = $this->db->get_one($sql);
		$this->_cache[$cache_id] = $rs;
		if(!$rs){
			return false;
		}
		return $rs;
	}

	/**
	 * 取得父子關係的陣列
	 * @引數 $list 引用組
	 * @引數 $pid 父級ID
	**/
	public function opt_parent(&$list,$pid=0)
	{
		if($pid){
			$rs = $this->opt_one($pid);
			$list[] = $rs;
			if($rs["parent_id"]){
				$this->opt_parent($list,$rs["parent_id"]);
			}
		}
	}

	/**
	 * 取得子項列表
	 * @引數 $list 引用組
	 * @引數 $id 父級ID
	**/
	public function opt_son(&$list,$id=0)
	{
		$condition = "parent_id='".$id."'";
		$tmplist = $this->opt_all($condition);
		if($tmplist){
			foreach($tmplist AS $key=>$value){
				$list[] = $value;
				$this->opt_son($list,$value["id"]);
			}
		}
	}
}