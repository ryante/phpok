<?php
/**
 * 讀取主題內容
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年08月23日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class content_model_base extends phpok_model
{
	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::model();
	}


	/**
	 * 取得單個主題資訊
	 * @引數 $id 主題ID或標識
	 * @引數 $status 是否已稽核
	**/
	public function get_one($id,$status=true)
	{
		$sql  = "SELECT * FROM ".$this->db->prefix."list WHERE site_id='".$this->site_id."' AND ";
		$sql .= is_numeric($id) ? " id='".$id."' " : " identifier='".$id."' ";
		if($status){
			$sql.= " AND status=1 ";
		}
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."list_biz WHERE id='".$rs['id']."'";
		$biz_rs = $this->db->get_one($sql);
		if($biz_rs){
			foreach($biz_rs as $key=>$value){
				$rs[$key] = $value;
			}
			unset($biz_rs);
		}
		if($rs['module_id']){
			$sql = "SELECT * FROM ".$this->db->prefix."list_".$rs['module_id']." WHERE id='".$rs['id']."'";
			$ext_rs = $this->db->get_one($sql);
			if($ext_rs){
				$rs = array_merge($ext_rs,$rs);
			}
		}
		//讀取屬性
		$sql = "SELECT * FROM ".$this->db->prefix."list_attr WHERE tid='".$rs['id']."' ORDER BY taxis ASC,id DESC";
		$attrlist = $this->db->get_all($sql);
		if($attrlist){
			$vids = array();
			$attrs = array();
			foreach($attrlist as $key=>$value){
				$vids[] = $value['vid'];
				if(!$attrs[$value['aid']]){
					$attrs[$value['aid']] = array('id'=>$value['aid']);
					$attrs[$value['aid']]['rslist'][$value['vid']] = $value;
				}else{
					$attrs[$value['aid']]['rslist'][$value['vid']] = $value;
				}
			}
			unset($attrlist);
			$vids = array_unique($vids);
			$alist = $this->model('options')->get_all('id');
			$vlist = $this->model('options')->values_list("id IN(".implode(",",$vids).")",0,999,'id');
			foreach($attrs as $key=>$value){
				$value['title'] = $alist[$key]['title'];
				foreach($value['rslist'] as $k=>$v){
					$v['title'] = $vlist[$k]['title'];
					$v['val'] = $vlist[$k]['val'];
					$v['pic'] = $vlist[$k]['pic'];
					$value['rslist'][$k] = $v;
				}
				$attrs[$key] = $value;
			}
			$rs['attrlist'] = $attrs;
		}

		$ext = $this->model('ext')->get_all('list-'.$rs['id'],false);
		if($ext){
			$rs = array_merge($rs,$ext);
		}		
		return $rs;
	}

	/**
	 * 通過主題ID獲取對應的模組ID
	 * @引數 $id 主題ID
	**/
	public function get_mid($id)
	{
		$sql = "SELECT module_id FROM ".$this->db->prefix."list WHERE id='".$id."' AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $rs["module_id"];
	}

	/**
	 * 獲取擴充套件欄位並格式化內容
	 * @引數 $mid 模組ID
	 * @引數 $ids 主題，多個主題用英文逗號隔開
	 * @引數 
	**/
	public function ext_list($mid,$ids)
	{
		if(!$mid || !$ids){
			return false;
		}
		$flist = $this->model("module")->fields_all($mid);
		if(!$flist){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."list_".$mid." WHERE id IN(".$ids.")";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			foreach($flist as $k=>$v){
				if($value[$v["identifier"]]){
					$v["content"] = $value[$v["identifier"]];
					$value[$v["identifier"]] = $this->lib('ext')->content_format($v);
				}
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}
}