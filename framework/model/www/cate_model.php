<?php
/**
 * 前端分類讀取
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年02月04日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cate_model extends cate_model_base
{
	private $tmpdata = array();
	public function __construct()
	{
		parent::__construct();
	}

	//讀取當前分類資訊
	public function get_one($id,$field="id",$ext=true)
	{
		$cate_all = $this->cate_all($this->site_id);
		if(!$cate_all){
			return false;
		}
		$rs = false;
		foreach($cate_all as $key=>$value){
			if($value[$field] == $id){
				$rs = $value;
				break;
			}
		}
		return $rs;
	}

	//前端讀取分類，帶格式化
	public function get_all($site_id=0,$status=0,$pid=0)
	{
		$cate_all = $this->cate_all($siteid);
		$tmplist = array();
		$this->_format($tmplist,$cate_all,$pid);
	}

	//格式化分類陣列
	private function _format(&$rslist,$tmplist,$parent_id=0,$layer=0)
	{
		foreach($tmplist AS $key=>$value)
		{
			if($value["parent_id"] == $parent_id)
			{
				$is_end = true;
				foreach($tmplist AS $k=>$v)
				{
					if($v["parent_id"] == $value["id"])
					{
						$is_end = false;
						break;
					}
				}
				$value["_layer"] = $layer;
				$value["_is_end"] = $is_end;
				$rslist[] = $value;
				//執行子級
				$new_layer = $layer+1;
				$this->_format($rslist,$tmplist,$value["id"],$new_layer);
			}
		}
	}

	//前端中涉及到的快取
	public function cate_all($site_id=0,$status=0)
	{
		$siteid = intval($site_id);
		if(!$siteid){
			$siteid = $this->site_id;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE site_id='".$siteid."' AND status=1 ORDER BY taxis ASC,id DESC";
		$cache_id = $this->cache->id('www'.$sql);
		if($this->tmpdata && $this->tmpdata[$cache_id]){
			return $this->tmpdata[$cache_id];
		}
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		$extlist = $GLOBALS['app']->model('ext')->cate();
		if($extlist){
			foreach($rslist as $key=>$value){
				$tmpid = 'cate-'.$value['id'];
				if($extlist[$tmpid]){
					$value = array_merge($value,$extlist[$tmpid]);
				}
				$rslist[$key] = $value;
			}
		}
		$this->tmpdata[$cache_id] = $rslist;
		return $rslist;
	}

	//讀取子類
	public function sublist(&$catelist,$parent_id=0,$cate_all=0)
	{
		if(!$cate_all)
		{
			$cate_all = $this->cate_all();
		}
		if(!$cate_all || !is_array($cate_all))
		{
			return false;
		}
		foreach($cate_all as $key=>$value)
		{
			if($value['parent_id'] == $parent_id)
			{
				$catelist[] = $value;
				$this->sublist($catelist,$value['id'],$cate_all);
			}
		}
	}

	//生成適用於select的下拉選單中的引數
	public function cate_option_list($list)
	{
		if(!$list || !is_array($list)) return false;
		$rslist = array();
		foreach($list AS $key=>$value)
		{
			$value["_space"] = "";
			for($i=0;$i<$value["_layer"];$i++)
			{
				$value["_space"] .= "&nbsp; &nbsp;│";
			}
			if($value["_is_end"] && $value["_layer"])
			{
				$value["_space"] .= "&nbsp; &nbsp;├";
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	public function get_root_id($id)
	{
		$rs = $this->get_one($id);
		if(!$rs)
		{
			return false;
		}
		if(!$rs['parent_id'])
		{
			return $rs['id'];
		}
		else
		{
			return $this->get_root_id($rs['parent_id']);
		}
	}
	
}

?>