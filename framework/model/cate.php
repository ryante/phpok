<?php
/**
 * 欄目管理
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年08月23日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cate_model_base extends phpok_model
{
	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 取得單條分類資訊
	 * @引數 $id 主鍵或是指定的欄位名對應的值
	 * @引數 $field 欄位名，支援id，identifier
	 * @引數 $ext 是否讀取擴充套件資料
	**/
	public function get_one($id,$field="id",$ext=true)
	{
		if(!$id){
			return false;
		}
		$sql = " SELECT * FROM ".$this->db->prefix."cate WHERE `".$field."`='".$id."' ";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($ext){
			$tmplist = $this->model('ext')->ext_all('cate-'.$rs['id'],true);
			if($tmplist){
				$ext_rs = array();
				foreach($tmplist as $key=>$value){
					$ext_rs[$value['identifier']] = content_format($value);
				}
				if($ext_rs){
					$rs = array_merge($rs,$ext_rs);
				}
			}
		}
		return $rs;
	}

	/**
	 * 分類資訊
	 * @引數 $id 分類ID
	 * @引數 $ext 是擴包含擴充套件
	**/
	public function cate_info($id,$ext=false)
	{
		if($ext){
			return $this->get_one($id,'id',true);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得當前分類
	 * @引數 $id 分類ID
	 * @引數 $site_id 站點ID
	**/
	public function cate_one($id,$site_id=0)
	{
		if($site_id){
			$rslist = $this->cate_all($site_id,true);
			if($rslist[$id]){
				return $rslist[$id];
			}
			return false;
		}else{
			$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id='".$id."' AND status=1";
			return $this->db->get_one($sql);
		}
	}

	/**
	 * 取得全部分類資訊
	 * @引數 $site_id 站點ID
	 * @引數 $status 狀態，為1時表示僅讀已稽核資料
	 * @引數 $pid 父級分類ID
	**/
	public function get_all($site_id=0,$status=0,$pid=0)
	{
		$rslist = $this->cate_all($site_id,$status);
		if(!$rslist){
			return false;
		}
		$tmplist = array();
		$this->format_list($tmplist,$rslist,$pid,"0");
		$this->cate_list = $tmplist;
		return $tmplist;
	}

	/**
	 * 取得全部的分類資訊（不格式化）
	 * @引數 $site_id 站點ID
	 * @引數 $status 狀態，為1時表示僅讀已稽核資料
	**/
	public function cate_all($site_id=0,$status=0)
	{
		$sql = " SELECT * FROM ".$this->db->prefix."cate WHERE site_id='".$site_id."'";
		if($status){
			$sql .= " AND status='1' ";
		}
		$sql .= " ORDER BY taxis ASC,id DESC ";
		return $this->db->get_all($sql,'id');
	}

	/**
	 * 格式化分類陣列
	 * @引數 $rslist 儲存目標
	 * @引數 $tmplist 原始資料
	 * @引數 $parent_id 父級ID
	 * @引數 $layer 層級位置
	**/
	public function format_list(&$rslist,$tmplist,$parent_id=0,$layer=0)
	{
		if(!$tmplist && !is_array($tmplist)){
			$tmplist = array();
		}
		foreach($tmplist as $key=>$value){
			if($value["parent_id"] == $parent_id){
				$is_end = true;
				foreach($tmplist as $k=>$v){
					if($v["parent_id"] == $value["id"]){
						$is_end = false;
						break;
					}
				}
				$value["_layer"] = $layer;
				$value["_is_end"] = $is_end;
				$rslist[] = $value;
				//執行子級
				$new_layer = $layer+1;
				$this->format_list($rslist,$tmplist,$value["id"],$new_layer);
			}
		}
	}

	/**
	 * 生成適用於select的下拉選單中的引數
	 * @引數 $list 列表
	**/
	public function cate_option_list($list)
	{
		if(!$list || !is_array($list)){
			return false;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			$value["_space"] = "";
			for($i=0;$i<$value["_layer"];$i++){
				$value["_space"] .= "&nbsp; &nbsp;│";
			}
			if($value["_is_end"] && $value["_layer"]){
				$value["_space"] .= "&nbsp; &nbsp;├";
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	/**
	 * 讀取分類表中的欄位名，用於檢測欄位名防止重複
	 * @引數 $idlist 欄位名儲存目標
	**/
	public function cate_fields(&$idlist)
	{
		$sql = "SHOW FIELDS FROM ".$this->db->prefix."cate";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				$idlist[] = $value["Field"];
			}
		}
	}

	/**
	 * 取得子分類ID資訊
	 * @引數 $id 分類ID，支援多個分類ID，用英文逗號格開
	 * @引數 $status 狀態，為1時表示僅讀已稽核資料
	**/
	public function get_son_id_list($id,$status=0)
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE parent_id IN(".$id.")";
		if($status){
			$sql .= " AND status='1'";
		}
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		$id_list = array_keys($rslist);
		return $id_list;
	}

	/**
	 * 取得子分類資訊
	 * @引數 $list 儲存目標
	 * @引數 $id 父分類ID
	 * @引數 $space 空格補嘗
	**/
	public function get_sublist(&$list,$id,$space="")
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE parent_id='".$id."'";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				$value["space"] = $space ? $space."├ " : "";
				$list[] = $value;
				$newspace = $space ."　　";
				$this->get_sublist($list,$value["id"],$newspace);
			}
		}
	}

	/**
	 * 取得子分類資訊
	 * @引數 $id 父級分類ID，多個分類ID用英文逗號隔開
	 * @引數 $status 1為僅讀稽核過的
	**/
	public function get_sonlist($id=0,$status=0)
	{
		$list = array();
		$sql  = "SELECT id FROM ".$this->db->prefix."cate WHERE parent_id IN(".$id.") ";
		if($status){
			$sql .= " AND status=1 ";
		}
		$sql .= " ORDER BY SUBSTRING_INDEX('".$id."',id,1),taxis ASC";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				$list[] = $value['id'];
			}
		}
		$list = array_unique($list);
		$id = implode(",",$list);
		return $this->catelist_cid($id,true);
	}

	/**
	 * 讀取子分類ID資訊
	 * @引數 $list 儲存目標
	 * @引數 $id 父級分類ID，多個分類ID用英文逗號隔開
	 * @引數 $status 1為僅讀稽核過的
	**/
	public function get_sonlist_id(&$list,$id=0,$status=0)
	{
		if(!$id){
			return false;
		}
		$sql  = "SELECT id FROM ".$this->db->prefix."cate WHERE parent_id IN(".$id.") ";
		if($status){
			$sql .= " AND status=1 ";
		}
		$sql .= " ORDER BY SUBSTRING_INDEX('".$id."',id,1),taxis ASC";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			$idlist = array();
			foreach($rslist as $key=>$value){
				$list[] = $value["id"];
				$idlist[] = $value['id'];
			}
			$idstring = implode(",",$idlist);
			$this->get_sonlist_id($list,$idstring,$status);
		}
	}

	/**
	 * 更新排序
	 * @引數 $id 分類ID
	 * @引數 $taxis 排序值
	**/
	public function update_taxis($id,$taxis=255)
	{
		$sql = "UPDATE ".$this->db->prefix."cate SET taxis='".$taxis."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 刪除分類
	 * @引數 $id 分類ID
	**/
	public function cate_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."cate WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."list SET cate_id=0 WHERE cate_id='".$id."'";
		$this->db->query($sql);
		$sql = "SELECT id FROM ".$this->db->prefix."fields WHERE ftype='cate-".$id."'";
		$rslist = $this->db->get_all($sql,'id');
		if($rslist){
			$idlist = array_keys($rslist);
			$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id IN(".implode(',',$idlist).")";
			$this->db->query($sql);
			//刪除當前分類的擴充套件
			$sql = "DELETE FROM ".$this->db->prefix."fields WHERE id IN(".implode(',',$idlist).")";
			$this->db->query($sql);
		}
		return true;
	}

	/**
	 * 取得根分類資訊
	 * @引數 $site_id 站點ID
	**/
	public function root_catelist($site_id=0)
	{
		if(!$site_id){
			$site_id = $this->site_id;
		}
		if(!$site_id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE parent_id=0 AND site_id='".$site_id."' ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql,"id");
	}

	/**
	 * 通過分類ID獲取分類內容
	 * @引數 $cid 分類ID，多個分類ID用英文逗號隔開
	 * @引數 $ext 是否讀取擴充套件資料
	**/
	public function catelist_cid($cid,$ext=true)
	{
		if(!$cid){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id IN(".$cid.") AND status=1 ORDER BY SUBSTRING_INDEX('".$cid."',id,1) ";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		if(!$ext){
			return $rslist;
		}
		return $this->cate_ext($rslist);
	}

	/**
	 * 前臺呼叫當前分類下的子分類
	 * @引數 $cid 父級分類ID，多個分類ID用英文逗號隔開
	 * @引數 $ext 是否讀取擴充套件資料
	 * @引數 $status 1為僅讀稽核過的
	**/
	public function catelist_sonlist($cid,$ext=true,$status=1)
	{
		if(!$cid){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE parent_id=".intval($cid)." ";
		if($status){
			$sql .= ' AND status=1 ';
		}
		$sql.= " ORDER BY taxis ASC,id DESC ";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		if(!$ext){
			return $rslist;
		}
		return $this->cate_ext($rslist);
	}

	/**
	 * 通過主表資料，讀取擴充套件資料
	 * @引數 $rslist 多級陣列，即主表中的分類資訊，KEY值為主鍵ID
	**/
	public function cate_ext($rslist)
	{
		if(!$rslist){
			return false;
		}
		$idlist = array_keys($rslist);
		$total = count($idlist);
		$clist = array();
		foreach($idlist as $key=>$value){
			$clist[] = "cate-".$value;
		}
		$cateinfo = implode(",",$clist);
		$extlist = $this->model('ext')->get_all($cateinfo,true);
		foreach($rslist as $key=>$value){
			$tmp = $extlist["cate-".$key];
			if($tmp){
				$rslist[$key] = array_merge($tmp,$value);
			}
		}
		return $rslist;
	}

	/**
	 * 遞迴獲取分類資訊
	 * @引數 $array 儲存目標
	 * @引數 $parent_id 父級分類ID
	 * @引數 $rslist 資料來源
	**/
	public function cate_ids(&$array,$parent_id=0,$rslist='')
	{
		if($rslist && is_array($rslist)){
			foreach($rslist as $key=>$value){
				if($value['parent_id'] == $parent_id){
					$array[] = $value['id'];
					$this->cate_ids($array,$value['id'],$rslist);
				}
			}
		}
	}

	/**
	 * 讀取主題下繫結的擴充套件分類資訊
	 * @引數 $id 主題ID
	**/
	public function ext_catelist($id)
	{
		$sql = "SELECT c.* FROM ".$this->db->prefix."list_cate lc JOIN ".$this->db->prefix."cate c ON(lc.cate_id=c.id) ";
		$sql.= "WHERE lc.id='".$id."' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC";
		return $this->db->get_all($sql,'id');
	}

	/**
	 * 讀取分類資訊
	 * @引數 $ids 多個分類ID用英文逗號隔開，或陣列
	 * @引數 $project_identifier 專案標識
	**/
	public function list_ids($ids,$project_identifier='')
	{
		if(!$ids){
			return false;
		}
		$ids = is_array($ids) ? implode(",",$ids) : $ids;
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id IN(".$ids.") AND status=1";
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if($project_identifier){
				$value['url'] = $this->url($project_identifier,$value['identifier']);
			}
			$cate_tmp = $this->model('ext')->ext_all('cate-'.$value['id'],true);
			if($cate_tmp){
				$cate_ext = array();
				foreach($cate_tmp as $k=>$v){
					$cate_ext[$v['identifier']] = $this->lib('form')->show($v);
					if($v['form_type'] == 'url' && $v['content']){
						$v['content'] = unserialize($v['content']);
						$value['url'] = $v['content']['default'];
						if($this->site['url_type'] == 'rewrite' && $v['content']['rewrite']){
							$value['url'] = $v['content']['rewrite'];
						}
					}
				}
				$value = array_merge($cate_ext,$value);
				unset($cate_ext,$cate_tmp);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}
}