<?php
/***********************************************************
	Filename: {phpok}/model/data.php
	Note	: 前臺用於呼叫的資料
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月9日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class data_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	private function _res_info($id)
	{
		if(!$id){
			return false;
		}
		return $this->model('res')->get_one(intval($id),true);
	}


	//取得單篇ID
	private function _list_info($id)
	{
		//讀取內容
		$sql  = "SELECT * FROM ".$this->db->prefix."list WHERE ";
		$sql .= is_numeric($id) ? " id='".$id."' " : " identifier='".$id."' ";
		$sql .= " AND site_id='".$this->site['id']."' AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		//取得專案基本資訊
		$rs['project_id'] = $this->_project_info($rs['project_id']);
		if(!$rs['project_id'])
		{
			return false;
		}
		$rs['project_id']['url'] = $this->url($rs['project_id']['identifier']);
		//讀取會員資訊及擴充套件
		if($rs['user_id'])
		{
			$rs['user_id'] = $this->_user_info($rs['user_id']);
		}
		//讀分類及其擴展信息
		if($rs['cate_id'])
		{
			$rs['cate_id'] = $this->_cate_info($rs['cate_id']);
			if($rs['cate_id'])
			{
				$rs['cate_id']['url'] = $this->url($rs['project_id']['identifier'],$rs['cate_id']['identifier']);
			}
		}
		//讀模組資訊及其擴充套件
		if($rs['module_id'])
		{
			$ext = $this->_list_ext_info($rs['id'],$rs['module_id']);
			if($ext)
			{
				$rs = array_merge($ext,$rs);
			}
		}
		return $rs;
	}

	//讀單篇主題的擴展信息
	private function _list_ext_info($id=0,$mid=0)
	{
		if(!$id || !$mid)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."list_".$mid." WHERE id='".$id."' AND site_id='".$this->site['id']."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		//清除四個核心變數
		unset($rs['id'],$rs['site_id'],$rs['project_id'],$rs['cate_id']);
		if($rs && count($rs)>0)
		{
			return $rs;
		}
		return false;
	}

	//專案資訊內容
	private function _project_info($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE id='".$id."' AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		//獲取擴充套件內容
		$extlist = $this->_ext_info('project-'.$id);
		if($extlist)
		{
			$rs = array_merge($extlist,$rs);
		}
		return $rs;
	}

	//讀取分類及期擴展信息，但並不格式化
	private function _cate_info($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id='".$id."' AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		//獲取擴充套件內容
		$extlist = $this->_ext_info('cate-'.$id);
		if($extlist)
		{
			$rs = array_merge($extlist,$rs);
		}
		return $rs;
	}

	//讀取擴充套件內容，未格式化
	private function _ext_info($module)
	{
		$sql = "SELECT e.identifier,c.content FROM ".$this->db->prefix."fields e ";
		$sql.= "LEFT JOIN ".$this->db->prefix."extc c ON(e.id=c.id) WHERE e.ftype='".$module."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$list = array();
		foreach($rslist as $key=>$value){
			$list[$value['identifier']] = $value['content'];
		}
		return $list;
	}
	
	//取得會員內容及未格式化的擴展信息
	private function _user_info($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE id='".$id."' AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		//讀取會員擴充套件
		$sql = "SELECT * FROM ".$this->db->prefix."user_ext WHERE id='".$id."'";
		$ext_rs = $this->db->get_one($sql);
		if($ext_rs)
		{
			$rs = array_merge($ext_rs,$rs);
		}
		return $rs;
	}
	//內容分頁
	function info_page($content,$pageid=0)
	{
		if(!$content) return false;
		if(!$pageid) $pageid = 1;
		$lst = explode('[:page:]',$content);
		$t = $pageid-1;
		if($lst[$t])
		{
			$total = count($lst);
			if($total>1)
			{
				$array = array();
				for($i=0;$i<$total;$i++)
				{
					$array[$i] = $i+1;
				}
			}
			return array('pagelist'=>$array,'content'=>$lst[$t]);
		}
		return $lst[0];
	}

	//取得當前分類資訊
	public function cate($rs)
	{
		if(!$rs['pid'] && !$rs['phpok']) return false;
		if(!$rs['pid'])
		{
			$tmp = $this->_id($rs['phpok'],$this->site['id']);
			if(!$tmp || $tmp['type'] != 'project') return false;
			$rs['pid'] = $tmp['id'];
		}
		if(!$rs['pid']) return false;
		//取得專案資訊
		$project_rs = $this->_project($rs['pid'],false);
		if(!$project_rs['cate']) return false;
		if($rs['cate'])
		{
			$tmp = $this->_id($rs['cate'],$this->site['id']);
			if($tmp['type'] == 'cate') $rs['cateid'] = $tmp['id'];
		}
		if(!$rs['cateid']) $rs['cateid'] = $project_rs['cate'];
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id='".$rs['cateid']."' AND status=1";
		$cate_rs = $this->db->get_one($sql);
		if(!$cate_rs) return false;
		if($rs['cate_ext'])
		{
			$ext = $this->ext_all('cate-'.$cate_rs['id'],$cate_rs);
			if($ext) $cate_rs = array_merge($ext,$cate_rs);
		}
		if(!$cate_rs['url'])
		{
			$cate_rs['url'] = $GLOBALS['app']->url($project_rs['identifier'],$cate_rs['identifier']);
		}
		return $cate_rs;
	}

	//取得分類，不帶專案
	function cate_id($rs)
	{
		$id = $rs['id'] ? $rs['id'] : $rs['cateid'];
		if(!$id && !$rs['phpok']) return false;
		if(!$id)
		{
			$tmp = $this->_id($rs['phpok'],$this->site['id']);
			if(!$tmp || $tmp['type'] != 'cate') $id = $tmp['id'];
		}
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id='".$id."' AND status=1";
		$cate_rs = $this->db->get_one($sql);
		if(!$cate_rs) return false;
		if($rs['cate_ext'])
		{
			$ext = $this->ext_all('cate-'.$cate_rs['id'],$cate_rs);
			if($ext) $cate_rs = array_merge($ext,$cate_rs);
		}
		return $cate_rs;
	}

	//取得當前分類下的子類
	public function subcate($rs)
	{
		if(!$rs['cateid'] && !$rs['phpok']) return false;
		if(!$rs['cateid'])
		{
			$tmp = $this->_id($rs['phpok'],$this->site['id']);
			if(!$tmp || $tmp['type'] != 'cate') return false;
			$rs['cateid'] = $tmp['id'];
		}
		$list = array();
		$cate_all = $GLOBALS['app']->model('cate')->cate_all($rs['site_id']);
		$this->cate_sublist($list,$rs['cateid'],$cate_all,$rs['project']);
		return $list;
	}

	public function _tree(&$list,$catelist,$parent_id=0)
	{
		foreach($catelist as $key=>$value)
		{
			if($value['parent_id'] == $parent_id)
			{
				$list[$value['id']] = $value;
				$this->_tree($list[$value['id']]['sublist'],$catelist,$value['id']);
			}
		}
	}

	//取得專案資訊
	public function project($rs)
	{
		if(!$rs['pid'] && !$rs['phpok']) return false;
		if(!$rs['pid'])
		{
			$tmp = $this->_id($rs['phpok'],$this->site['id']);
			if(!$tmp || $tmp['type'] != 'project') return false;
			$rs['pid'] = $tmp['id'];
		}
		if(!$rs['pid']) return false;
		$rs = $this->_project($rs['pid'],$rs['project_ext']);
		if(!$rs) return false;
		//繫結連結
		if(!$rs['url']) $rs['url'] = $GLOBALS['app']->url($rs['identifier']);
		return $rs;
	}

	//取得父級專案資訊
	public function _project_parent($rs)
	{
		if(!$rs['pid'] && !$rs['phpok']) return false;
		if(!$rs['pid'])
		{
			$tmp = $this->_id($rs['phpok'],$this->site['id']);
			if(!$tmp || $tmp['type'] != 'project') return false;
			$rs['pid'] = $tmp['id'];
		}
		if(!$rs['pid']) return false;
		$project_rs = $this->_project($rs['pid'],false);
		if(!$project_rs || !$project_rs['parent_id']) return false;
		$rs = $this->_project($project_rs['parent_id'],$rs['parent_ext']);
		if(!$rs) return false;
		//繫結連結
		if(!$rs['url']) $rs['url'] = $GLOBALS['app']->url($rs['identifier']);
		return $rs;
	}
	
	//取得子專案資訊
	public function sublist($rs)
	{
		if(!$rs['pid'] && !$rs['phpok']) return false;
		if(!$rs['pid'])
		{
			$tmp = $this->_id($rs['phpok'],$this->site['id']);
			if(!$tmp || $tmp['type'] != 'project') return false;
			$rs['pid'] = $tmp['id'];
		}
		if(!$rs['pid']) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE parent_id=".intval($rs['pid'])." AND status=1 ";
		$sql.= "AND hidden=0 ";
		$sql.= "ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist) return false;
		if($rs['sublist_ext'])
		{
			foreach($rslist AS $key=>$value)
			{
				$ext_rs = $this->ext_all('project-'.$value['id'],$value);
				if($ext_rs) $value = array_merge($ext_rs,$value);
				$rslist[$key] = $value;
			}
		}
		foreach($rslist AS $key=>$value)
		{
			if(!$value['url']) $value['url'] = $GLOBALS['app']->url($value['identifier']);
			$rslist[$key] = $value;
 		}
 		return $rslist;

	}
	//讀取當前分類的子分類
	public function cate_sublist(&$list,$parent_id=0,$rslist='',$identifier='')
	{
		if($rslist)
		{
			foreach($rslist as $key=>$value)
			{
				if($value['parent_id'] == $parent_id)
				{
					if($identifier)
					{
						$value['url'] = $this->url($identifier,$value['identifier']);
					}
					if($value['_url'])
					{
						$value['url'] = $value['_url'];
						unset($value['_url']);
					}
					$list[$value['id']] = $value;
					$this->cate_sublist($list,$value['id'],$rslist,$identifier);
				}
			}
		}
	}

	//取得自定義欄位資訊
	public function fields($rs)
	{
		if(!$rs['pid'] && !$rs['phpok']) return false;
		if(!$rs['pid'])
		{
			$tmp = $this->_id($rs['phpok'],$this->site['id']);
			if(!$tmp || $tmp['type'] != 'project') return false;
			$rs['pid'] = $tmp['id'];
		}
		if(!$rs['pid']) return false;
		$project_rs = $this->_project($rs['pid'],false);
		if(!$project_rs || !$project_rs['module']) return false;
		//自定義欄位
		$array = array();
		$flist = $this->module_field($project_rs['module']);
		//如果存在擴充套件欄位，對擴充套件欄位進行處理，標前識加字首等
		if($flist)
		{
			foreach($flist AS $key=>$value)
			{
				if(!$value['is_front'])
				{
					unset($flist[$key]);
					continue;
				}
				if($rs['prefix'])
				{
					$value["identifier"] = $rs['prefix'].$value['identifier'];
				}
				if($rs['info'][$value['identifier']])
				{
					$value['content'] = $rs['info'][$value['identifier']];
				}
				$flist[$key] = $value;
			}
			//如果包含主題
			if($rs['in_title'])
			{
				$tmp_id = $rs['prefix'].'title';
				$array['title'] = array('id'=>0,"module_id"=>$project_rs['module'],'title'=>($project_rs['alias_title'] ? $project_rs['alias_title'] : '主題'),'identifier'=>$tmp_id,'field_type'=>'varchar','form_type'=>'text','format'=>'safe','taxis'=>1,'width'=>'300','content'=>$rs['info']['title']);
				$array = array_merge($array,$flist);
			}
			else
			{
				$array = $flist;
			}
		}
		//判斷是否格式化
		if($rs['fields_format'])
		{
			foreach($array AS $key=>$value)
			{
				if($value['ext'])
				{
					$ext = is_string($value['ext']) ? unserialize($value['ext']) : $value['ext'];
					unset($value['ext']);
					$value = array_merge($ext,$value);
				}
				$array[$key] = $GLOBALS['app']->lib('form')->format($value);
			}
		}
		return $array;
	}
	
	//取得專案資訊
	public function _project($id,$ext=false)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE id=".intval($id);
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		if($ext)
		{
			$ext = $this->ext_all('project-'.$id,$rs);
			if($ext)
			{
				$rs = array_merge($ext,$rs);
				unset($ext);
			}
		}
		return $rs;
	}

	public function module_field($mid)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE ftype='".$mid."' ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql,'identifier');
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if($value['ext']){
				$value['ext'] = unserialize($value['ext']);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	public function id($identifier,$site_id=0)
	{
		if(!$site_id){
			$site_id = $this->site_id;
		}
		return $this->model('id')->id($identifier,$site_id,true);
	}

	private function _id($identifier,$site_id=0)
	{
		if(!$site_id){
			$site_id = $this->site_id;
		}
		return $this->model('id')->id($identifier,$site_id,true);
	}

	//獲取專案，分類的擴展信息
	public function ext_all($id,$baseinfo='')
	{
		$sql = "SELECT ext.ext,ext.identifier,ext.form_type,c.content FROM ".$this->db->prefix."fields ext ";
		$sql.= "LEFT JOIN ".$this->db->prefix."extc c ON(ext.id=c.id) ";
		$sql.= "WHERE ext.ftype='".$id."'";
		$rslist = $this->db->get_all($sql,'identifier');
		if(!$rslist){
			unset($sql);
			return false;
		}
		$res = '';
		$type = substr($id,0,4) == "cate" ? 'c' : 'p';
		foreach($rslist as $key=>$value){
			//當內容表單為網址時
			if($value['form_type'] == 'url' && $value['content']){
				$value['content'] = unserialize($value['content']);
				$url = $this->site['url_type'] == 'rewrite' ? $value['content']['rewrite'] : $value['content']['default'];
				if(!$url){
					$url = $value['content']['default'];
				}
				$value['content'] = $url;
				//繫結擴充套件自定義url
				if(!$rslist['url']){
					$rslist['url'] = array('form_type'=>'text','content'=>$url);
				}
			}elseif($value['form_type'] == 'upload' && $value['content']){
				$tmp = explode(',',$value['content']);
				foreach($tmp as $k=>$v){
					$v = intval($v);
					if($v){
						$res[] = $v;
					}
				}
			}elseif($value['form_type'] == 'editor' && $value['content']){
				if($value['ext']){
					$value['ext'] = unserialize($value['ext']);
				}
				if($value['ext'] && $value['ext']['inc_tag']){
					$value['content'] = $this->_tag_format($value['content'],$type.$baseinfo['id']);
				}
				$value['content'] = str_replace('[:page:]','',$value['content']);
				$value['content'] = $this->lib('ubb')->to_html($value['content'],false);
			}
			$rslist[$key] = $value;
		}
		//格式化內容資料，併合並附件資料
		$flist = "";
		foreach($rslist AS $key=>$value)
		{
			$flist[$key] = $value;
			$rslist[$key] = $value['content'];
		}
		if($res && is_array($res)) $res = $this->_res_info2($res);
		$rslist = $this->_format($rslist,$flist,$res);
		unset($flist,$res);
		return $rslist;
	}

	//讀取分類下的子分類id
	private function _cate_id(&$array,$parent_id=0,$rslist='')
	{
		if($rslist && is_array($rslist))
		{
			foreach($rslist as $key=>$value)
			{
				if($value['parent_id'] == $parent_id)
				{
					$array[] = $value['id'];
					$this->_cate_id($array,$value['id'],$rslist);
				}
			}
		}
	}

	public function res_info($id)
	{
		return $this->_res_info2($id);
	}

	//讀取附件資訊
	private function _res_info2($id)
	{
		if(!$id){
			return false;
		}
		if(is_string($id)){
			$id = array($id);
		}
		$id = array_unique($id);
		$id = implode(',',$id);
		return $this->model('res')->get_list_from_id($id,true);
	}

	//格式化單列資訊
	private function _format($rs,$flist="",$reslist="",$catelist="",$userlist="",$tlist="")
	{
		if(!$rs || !is_array($rs)) return false;
		if($flist)
		{
			foreach($flist AS $key=>$value)
			{
				$ext = $value['ext'];
				if($ext && is_string($ext))
				{
					$ext = unserialize($ext);
				}
				//格式化附件資訊
				if($value['form_type'] == "upload" && $rs[$value['identifier']] && $reslist && is_array($reslist))
				{
					if($ext['is_multiple'])
					{
						$res = false;
						$tmp = explode(',',$rs[$value['identifier']]);
						foreach($tmp AS $k=>$v)
						{
							$v = intval($v);
							if($v && $reslist[$v]) $res[$v] = $reslist[$v];
						}
						$rs[$value['identifier']] = $res;
					}
					else
					{
						$rs[$value['identifier']] = $reslist[$rs[$value['identifier']]];
					}
				}
			}
			unset($flist);
		}
		//格式化分類資訊
		if($rs['cate_id'] && $catelist && $catelist[$rs['cate_id']]) $rs['cate_id'] = $catelist[$rs['cate_id']];
		//格式化會員資訊
		if($rs['user_id'] && $userlist && $userlist[$rs['user_id']]) $rs['user_id'] = $userlist[$rs['user_id']];
		return $rs;
	}

	//讀取分類基礎資訊
	private function _cate_info2($id)
	{
		if(!$id) return false;
		if(is_string($id)) $id = array($id);
		$id = array_unique($id);
		$id = implode(',',$id);
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id IN(".$id.")";
		return $this->db->get_all($sql,"id");
	}

	//讀取會員基礎資訊
	private function _user_info2($id)
	{
		if(!$id) return false;
		if(is_string($id)) $id = array($id);
		$id = array_unique($id);
		$id = implode(',',$id);
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE id IN(".$id.")";
		return $this->db->get_all($sql,"id");
	}

	//讀取內容基礎資訊
	private function _title_info($id)
	{
		if(!$id) return false;
		if(is_string($id)) $id = array($id);
		$id = array_unique($id);
		$id = implode(',',$id);
		$sql = "SELECT * FROM ".$this->db->prefix."list WHERE id IN(".$id.")";
		return $this->db->get_all($sql,"id");
	}
}