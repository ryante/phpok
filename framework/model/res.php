<?php
/**
 * 附件管理基礎類
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2018年01月17日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class res_model_base extends phpok_model
{
	private $gdlist = array();
	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::model();
		$this->gdlist = $this->model('gd')->get_all('id');
	}

	/**
	 * 取得資源資訊
	 * @引數 $id 附件ID
	 * @引數 $is_ext 是否讀取擴充套件
	 * @返回 陣列
	**/
	public function get_one($id,$is_ext=false)
	{
		if(!$id){
			return false;
		}
		$sql  = "SELECT res.*,cate.etype FROM ".$this->db->prefix."res res ";
		$sql .= "LEFT JOIN ".$this->db->prefix."res_cate cate ON(res.cate_id=cate.etype) ";
		$sql .= "WHERE res.id='".$id."' ";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs["attr"]){
			$attr = unserialize($rs["attr"]);
			$rs["attr"] = $attr;
		}
		//判斷附件方案
		$list = array("jpg","gif","png","jpeg");
		if(!$rs['ico'] && in_array($rs['ext'],$list)){
			$rs['ico'] = $this->get_ico($rs['filename']);
		}
		if(!in_array($rs["ext"],$list) || !$is_ext || !$this->gdlist || count($this->gdlist)<1){
			return $rs;
		}
		if($this->is_local($rs['filename'])){
			foreach($this->gdlist as $key=>$value){
				$rs['gd'][$value['identifier']] = $this->local_url($rs['filename'],$value);
			}
			return $rs;
		}
		if(!$rs['cate_id'] || !$rs['etype']){
			return $rs;
		}
		$tmp = $this->control('gateway','api')->exec_file($rs['etype'],'gd',array('filename'=>$rs['filename']));
		if($tmp){
			$rs['gd'] = $tmp;
		}
		return $rs;
	}

	/**
	 * 檢測檔案是否本地址
	 * @引數 $file 檔名，對應資料表 qinggan_res 下的 filename
	 * @返回 true 或 false
	**/
	public function is_local($file)
	{
		$file = trim(strtolower($file));
		if(strpos($file,'?') !== false){
			return false;
		}
		$tmp = substr($file,0,7);
		if($tmp == 'http://' || $tmp == 'https:/'){
			return false;
		}
		return true;
	}

	/**
	 * 取得擴充套件GD圖片
	 * @引數 $id，附件ID，多個ID用英文逗號隔開
	 * @引數 $is_list，這裡人工設定是否多個附件ID，設為true時將寫成多維陣列
	 * @返回 多維陣列或一維陣列（受$is_list控制）
	**/
	public function get_gd_pic($id,$is_list=false)
	{
		if(!$this->gdlist){
			return false;
		}
		$id = $this->ids_safe($id);
		if(!$id){
			return false;
		}
		$sql  = "SELECT res.id,res.cate_id,res.filename,cate.etype FROM ".$this->db->prefix."res res ";
		$sql .= "LEFT JOIN ".$this->db->prefix."res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(".$id.")";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$list = array();
		foreach($rslist as $key=>$value){
			if($this->is_local($value['filename'])){
				foreach($this->gdlist as $k=>$v){
					$list[$value['id']][$v['identifier']] = $this->local_url($value['filename'],$v);
				}
			}else{
				if($value['etype']){
					$tmp = $this->control('gateway','api')->exec_file($value['etype'],'gd',array('filename'=>$value['filename']));
					if($tmp){
						$list[$value['id']] = $tmp;
					}
				}
			}
		}
		if($is_list){
			return $list;
		}
		reset($list);
		return current($list);
	}

	/**
	 * 取得單個擴充套件圖片的GD
	 * @引數 $res_id 附件ID
	 * @引數 $gd_id GD庫ID
	 * @返回 陣列
	**/
	public function get_pic($res_id,$gd_id)
	{
		if(!$res_id && !$gd_id){
			return false;
		}
		if(!$this->gdlist || !$this->gdlist[$gd_id]){
			return false;
		}
		$rs = $this->get_one($res_id,false);
		if(!$rs || !$rs['cate_id']){
			return false;
		}
		$gdinfo = $this->gdlist[$gd_id];
		if($this->is_local($rs['filename'])){
			$filename = $this->local_url($rs['filename'],$gdinfo);
			return array('res_id'=>$res_id,'gd_id'=>$gd_id,'filename'=>$filename,'filetime'=>$rs['addtime']);
		}
		$cate = $this->model('rescate')->get_one($rs['cate_id']);
		if(!$cate['etype']){
			return false;
		}
		$tmp = $this->control('gateway','api')->exec_file($cate['etype'],'gd',array('filename'=>$rs['filename']));
		if(!$tmp){
			return false;
		}
		$gdinfo = $this->gdlist[$gd_id];
		$filename = $tmp[$gdinfo['identifier']];
		return array('res_id'=>$res_id,'gd_id'=>$gd_id,'filename'=>$filename,'filetime'=>$rs['addtime']);
	}

	/**
	 * 通過名字查詢附件，僅查一條
	 * @引數 $name 附件名稱
	 * @返回 陣列
	**/
	public function get_name($name)
	{
		if(!$name){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."res WHERE name='".$name."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$list = array("jpg","gif","png","jpeg");
		if(!$rs['ico'] && in_array($rs['ext'],$list)){
			$rs['ico'] = $this->get_ico($rs['filename']);
		}
		return $rs;
	}

	/**
	 * 取得資源列表
	 * @引數 $condition 條件
	 * @引數 $offset 查詢初始位置
	 * @引數 $psize 查詢數量
	 * @引數 $is_ext 是否包含擴充套件
	 * @返回 多維陣列
	**/
	public function get_list($condition="",$offset=0,$psize=20,$is_ext=false)
	{
		$extlist = array("jpg","gif","png","jpeg");
		$sql = "SELECT * FROM ".$this->db->prefix."res ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$sql .= " ORDER BY addtime DESC,id DESC LIMIT ".$offset.",".$psize;
		if(!$is_ext){
			$rslist = $this->db->get_all($sql);
			if(!$rslist){
				return false;
			}
			foreach($rslist as $key=>$value){
				$value["attr"] = $value["attr"] ? unserialize($value["attr"]) : "";
				if(!$value['ico'] && in_array($value['ext'],$extlist)){
					$value['ico'] = $this->get_ico($value['filename']);
				}
				$rslist[$key] = $value;
			}
			return $rslist;
		}
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		$id = implode(",",array_keys($rslist));
		$extlist = $this->get_gd_pic($id,true);
		$tmplist = array();
		foreach($rslist as $key=>$value){
			$value["gd"] = $extlist[$value["id"]];
			$value["attr"] = $value["attr"] ? unserialize($value["attr"]) : "";
			if(!$value['ico'] && in_array($value['ext'],$extlist)){
				$value['ico'] = $this->get_ico($value['filename']);
			}
			$tmplist[] = $value;
		}
		return $tmplist;
	}

	/**
	 * 取得指定ID下的附件，基於ID排序
	 * @引數 $id 附件ID，多個ID用英文逗號隔開
	 * @引數 $is_ext 是否讀取擴充套件表資料
	 * @返回 
	 * @更新時間 
	**/
	public function get_list_from_id($id,$is_ext=false)
	{
		$id = $this->ids_safe($id);
		if(!$id){
			return false;
		}
		$extlist = array("jpg","gif","png","jpeg");
		$sql = "SELECT * FROM ".$this->db->prefix."res WHERE id IN(".$id.") ORDER BY SUBSTRING_INDEX('".$id."',id,1)";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		if(!$is_ext){
			foreach($rslist as $key=>$value){
				$value["attr"] = $value["attr"] ? unserialize($value["attr"]) : "";
				if(!$value['ico'] && in_array($value['ext'],$extlist)){
					$value['ico'] = $this->get_ico($value['filename']);
				}
				$rslist[$key] = $value;
			}
			return $rslist;
		}
		$id = implode(",",array_keys($rslist));
		$extlist = $this->get_gd_pic($id,true);
		foreach($rslist as $key=>$value){
			$value["gd"] = $extlist[$value["id"]];
			$value["attr"] = $value["attr"] ? unserialize($value["attr"]) : "";
			if(!$value['ico'] && in_array($value['ext'],$extlist)){
				$value['ico'] = $this->get_ico($value['filename']);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	/**
	 * 刪除GD庫對應的附件資訊
	 * @引數 $id GD庫記錄的ID
	 * @引數 $root_dir 根目錄ID
	 * @返回 
	 * @更新時間 
	**/
	public function delete_gd_id($id,$root_dir="")
	{
		return true;
	}

	/**
	 * 取得資源數量
	 * @引數 $condition 條件，單表查詢
	 * @返回 數字
	**/
	public function get_count($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."res ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 取得附件資訊
	 * @引數 $filename 附件檔名
	 * @引數 $is_ext 是否讀取擴展信息
	 * @返回 false / 陣列
	**/
	public function get_one_filename($filename,$is_ext=true)
	{
		if(!$filename){
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."res WHERE filename='".$filename."' ORDER BY id DESC LIMIT 1";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $this->get_one($rs["id"],$is_ext);
	}

	/**
	 * 刪除資源
	 * @引數 $id 附件ID
	**/
	public function delete($id)
	{
		$rs = $this->get_one($id);
		if(!$rs){
			return false;
		}
		if($this->is_local($rs['filename'])){
			$this->lib('file')->rm($this->dir_root.$rs['filename']);
		}
		if($rs["ico"] && $this->is_local($rs['ico']) && substr($rs["ico"],0,7) != "images/"){
			$this->lib('file')->rm($this->dir_root.$rs['ico']);
		}
		//刪除遠端附件
		if($rs['etype']){
			$this->control('gateway','api')->exec_file($rs['etype'],'delete',array('filename'=>$rs['name']));			
		}
		$sql = "DELETE FROM ".$this->db->prefix."res WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 儲存資料
	 * @引數 $data 陣列
	 * @引數 $id 附件ID，為空表示新增，反之為修改
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($data['attr'] && is_array($data['attr'])){
			$data['attr'] = serialize($data['attr']);
		}
		if($id){
			return $this->db->update_array($data,"res",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"res");
		}
	}


	/**
	 * 取得資源分類
	 * @引數 $id 分類ID，為空讀預設分類
	 * @返回 false或分類陣列資訊
	**/
	public function cate_one($id=0)
	{
		if($id){
			$sql = "SELECT * FROM ".$this->db->prefix."res_cate WHERE id='".$id."'";
			return $this->db->get_one($sql);
		}else{
			return $this->cate_default();
		}
	}

	/**
	 * 根據分類標題取得分類資訊，如果標題一樣，僅讀取第一條資料
	 * @引數 $title
	 * @返回 
	 * @更新時間 
	**/
	public function cate_one_from_title($title='')
	{
		if(!$title){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate WHERE title='".$title."' ORDER BY id DESC";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得全部分類
	**/
	public function cate_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate ORDER BY id ASC ";
		return $this->db->get_all($sql);
	}

	/**
	 * 取得預設分類ID
	**/
	function cate_default()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate WHERE is_default='1'";
		return $this->db->get_one($sql);
	}

	/**
	 * 設定預設分類
	 * @引數 $id 要設定的預設分類
	**/
	public function cate_default_set($id=0)
	{
		if(!$id){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."res_cate SET is_default='0' WHERE is_default='1'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."res_cate SET is_default='1' WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 分類儲存操作
	 * @引數 $data 陣列，附件分類資訊
	 * @引數 $id 分類ID，為空或為0表示新增，不為空表示編輯
	**/
	public function cate_save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"res_cate",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"res_cate");
		}
	}

	/**
	 * 刪除圖片附件分類
	 * @引數 $id 要刪除的附件分類ID
	 * @引數 $default_id 要變更的分類ID
	**/
	public function cate_delete($id,$default_id=0)
	{
		$sql = "DELETE FROM ".$this->db->prefix."res_cate WHERE id='".$id."'";
		$this->db->query($sql);
		$cate_rs = $this->cate_one($default_id);
		if(!$cate_rs){
			$cate_rs = $this->cate_default();
		}
		if($cate_rs){
			$sql = "UPDATE ".$this->db->prefix."res SET cate_id='".$cate_rs['id']."' WHERE cate_id='".$id."'";
			$this->db->query($sql);
		}
		return true;
	}

	/**
	 * 更新附件名稱
	 * @引數 $title 附件名稱
	 * @引數 $id 附件ID
	**/
	public function update_title($title,$id)
	{
		$sql = "UPDATE ".$this->db->prefix."res SET title='".$title."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 更新附件備註
	 * @引數 $note 附件備註
	 * @引數 $id 附件ID
	**/
	public function update_note($note='',$id=0)
	{
		if(!$id){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."res SET note='".$note."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得所有的附件型別
	**/
	public function type_list()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate ORDER BY is_default DESC,id ASC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			$array = array("picture"=>array("name"=>"圖片","swfupload"=>"*.jpg;*.png;*.gif;*.jpeg","ext"=>"jpg,png,gif,jpeg","gd"=>1));
			return $array;
		}
		$array = array();
		foreach($rslist as $key=>$value){
			$types = $value['filetypes'] ? explode(",",$value['filetypes']) : array('jpg','gif','png');
			$swflist = array();
			foreach($types as $k=>$v){
				$swflist[] = '*.'.$v;
			}
			$value['swfupload'] = implode(";",$swflist);
			$value['name'] = $value['typeinfo'] ? $value['typeinfo'] : $value['title'];
			$array[$value['id']] = array('name'=>$value['name'],'swfupload'=>$value['swfupload'],'ext'=>implode(",",$types),'gd'=>1);
		}
		return $array;
	}

	/**
	 * 批量刪除附件
	 * @引數 $id 附件ID，陣列或整數或多個字串
	**/
	public function pl_delete($id=0)
	{
		if(!$id){
			return false;
		}
		if(!is_array($id)){
			$id = explode(',',$id);
		}
		$list = array();
		foreach($id as $key=>$value){
			if(!$value || !trim($value) || !intval($value)){
				continue;
			}
			$list[] = intval($value);
		}
		$id = implode(",",$list);
		if(!$id){
			return false;
		}
		$sql = "SELECT res.*,cate.etype FROM ".$this->db->prefix."res res ";
		$sql.= "LEFT JOIN ".$this->db->prefix."res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(".$id.")";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				if($value['filename'] && strpos($value['filename'],'://') === false && file_exists($this->dir_root.$value['filename'])){
					$this->lib('file')->rm($this->dir_root.$value['filename']);
				}
				if($value['ico'] && substr($value["ico"],0,7) != "images/" && $this->is_local($value['ico']) && file_exists($this->dir_root.$value["ico"])){
					$this->lib('file')->rm($this->dir_root.$value['ico']);
				}
				//批量刪除雲端資料
				if($value['etype'] && !$this->is_local($value['filename'])){
					$this->control('gateway','api')->exec_file($value['etype'],'delete',array('filename'=>$value['name']));
				}
			}
		}
		$sql = "DELETE FROM ".$this->db->prefix."res WHERE id IN(".$id.")";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 取得編輯器中的圖片列表
	 * @引數 $condition 查詢條件，擴充套件表別名是e，主表別名是res
	 * @引數 $offset 起始值，預設是0
	 * @引數 $psize 數量，預設是30條
	 * @引數 $gd 是否讀擴充套件表裡的資料
	 * @返回 false 或 多維陣列
	**/
	public function edit_pic_list($condition="",$offset=0,$psize=30,$gd=false)
	{
		$extlist = array("jpg","gif","png","jpeg");
		$sql = "SELECT res.* FROM ".$this->db->prefix."res res ";
		if($condition){
			$sql.= " WHERE ".$condition." ";
		}
		$sql.= " ORDER BY res.id DESC LIMIT ".$offset.",".$psize;
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$ids = array();
		foreach($rslist as $key=>$value){
			if(!$value['ico'] && in_array($value['ext'],$extlist) && $this->is_local($value['filename'])){
				$value['ico'] = $this->get_ico($value['filename']);
			}
			if($this->is_local($value['filename'])){
				foreach($this->gdlist as $k=>$v){
					$value['gd'][$v['identifier']] = $this->local_url($value['filename'],$v);
				}
			}else{
				if($value['etype']){
					$tmp = $this->control('gateway','api')->exec_file($value['etype'],'gd',array('filename'=>$value['filename']));
					if($tmp){
						$value['gd'] = $tmp;
					}
				}
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	/**
	 * 統計可在編輯器呼叫的圖片總數
	 * @引數 $condition 查詢條件，擴充套件表別名是e，主表別名是res
	 * @引數 $gd 是否讀擴充套件表裡的資料
	 * @返回 數字
	**/
	public function edit_pic_total($condition="",$gd=false)
	{
		
		$sql = "SELECT id FROM ".$this->db->prefix."res ";
		if($condition){
			$sql.= " WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

	/**
	 * 讀取附件資訊
	 * @引數 $string 要讀取的附件ID，多個ID用英文逗號隔開，或陣列
	 * @引數 $ext 讀取擴充套件欄位內容
	**/
	public function reslist($string,$ext=true)
	{
		if(!$string){
			return false;
		}
		if(is_array($string)){
			$string = array_unique($string);
			$string = implode(",",$string);
		}
		$list = explode(",",$string);
		$array = array();
		foreach($list as $key=>$value){
			if(!$value || !trim($value) || !intval($value) || in_array($value,$array)){
				continue;
			}
			$array[] = intval($value);
		}
		$string = implode(",",$array);
		$sql = "SELECT res.*,cate.etype FROM ".$this->db->prefix."res res ";
		$sql.= "LEFT JOIN ".$this->db->prefix."res_cate cate ON(res.cate_id=cate.id) ";
		$sql.= "WHERE res.id IN(".$string.") ORDER BY res.id ASC";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		if(!$ext){
			return $rslist;
		}
		foreach($rslist as $key=>$value){
			if($this->is_local($value['filename'])){
				foreach($this->gdlist as $k=>$v){
					$rslist[$key]['gd'][$v['identifier']] = $this->local_url($value['filename'],$v);
				}
			}else{
				if($value['etype']){
					$tmp = $this->control('gateway','api')->exec_file($value['etype'],'gd',array('filename'=>$value['filename']));
					if($tmp){
						$rslist[$key]['gd'] = $tmp;
					}
				}
			}
		}
		return $rslist;
	}

	/**
	 * 更新附件GD
	 * @引數 $id 附件ID
	 * @返回 false / true
	**/
	public function gd_update($id)
	{
		if(!$id){
			return false;
		}
		$rs = $this->get_one($id);
		if(!$rs){
			return false;
		}
		if($rs['cate_id']){
			$cate_rs = $this->model('rescate')->get_one($rs['cate_id']);
		}
		if(!$cate_rs){
			$cate_rs = $this->model('rescate')->get_default();
			if(!$cate_rs){
				return false;
			}
			$sql = "UPDATE ".$this->db->prefix."res SET cate_id='".$cate_rs['id']."' WHERE id='".$id."'";
			$this->db->query($sql);
		}
		$arraylist = array("jpg","gif","png","jpeg");
		if(!in_array($rs['ext'],$arraylist)){
			$ico = "images/filetype-large/".$rs["ext"].".jpg";
			if(!is_file($this->dir_root.$ico)){
				$ico = "images/filetype-large/unknown.jpg";
			}
			$sql = "UPDATE ".$this->db->prefix."res SET ico='".$ico."' WHERE id='".$id."'";
			$this->db->query($sql);
			return true;
		}
		if($this->is_local($rs['filename'])){
			$ico = $this->get_ico($rs['filename']);
			$sql = "UPDATE ".$this->db->prefix."res SET ico='".$ico."' WHERE id='".$id."'";
			$this->db->query($sql);
			return true;
		}
		$ico = $this->control('gateway','api')->exec_file($rs['etype'],'ico',array('filename'=>$rs['filename'],'filext'=>$rs['ext']));
		if(!$ico){
			$ico = "images/filetype-large/".$rs["ext"].".jpg";
			if(!file_exists($ico)){
				$ico = "images/filetype-large/unknown.jpg";
			}
		}
		$sql = "UPDATE ".$this->db->prefix."res SET ico='".$ico."' WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function get_ico($filename,$width=200,$height=200,$cutype=1,$qty=80)
	{
		if(!$this->is_local($filename)){
			return false;
		}
		$tmp = array('url'=>$filename);
		$tmp['width'] = $width;
		$tmp['height'] = $height;
		$tmp['cut_type'] = $cutype;
		$tmp['quality'] =$qty;
		$tmp['bgcolor'] = 'FFFFFF';
		return 'img.php?token='.$this->lib('common')->urlsafe_b64encode(serialize($tmp));
	}

	/**
	 * 更新附件到新的分類
	 * @引數 $id 要更新的附件ID，多個ID用英文逗號隔開
	 * @引數 $newcate 新的分類ID
	 * @返回 
	 * @更新時間 
	**/
	public function update_cate($id='',$newcate=0)
	{
		$id = $this->ids_safe($id);
		if(!$id){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."res SET cate_id='".$newcate."' WHERE id IN(".$id.")";
		return $this->db->query($sql);
	}

	/**
	 * ID的安全過濾
	 * @引數 $id，支援陣列，字串，數字
	 * @返回 false 或 數字+英文逗號的字串
	**/
	private function ids_safe($id)
	{
		if(!$id){
			return false;
		}
		if(is_array($id)){
			$id = implode(",",$id);
		}
		$list = explode(',',$id);
		foreach($list as $key=>$value){
			if(!$value || !trim($value) || !intval($value)){
				unset($list[$key]);
				continue;
			}
			$list[$key] = intval($value);
		}
		return implode(",",$list);
	}

	/**
	 * 讀寫附件遠端配置
	 * @引數 $data 不為空且為陣列時，表示儲存資訊
	**/
	public function remote_config($data='')
	{
		$file = $this->dir_data.'xml/remote_config_'.$this->site_id.'.xml';
		if($data && is_array($data)){
			$this->lib('xml')->save($data,$file);
			return true;
		}
		$file = $this->dir_data.'xml/remote_config_'.$this->site_id.'.xml';
		if(!file_exists($file)){
			$file = $this->dir_data.'xml/remote_config.xml';
		}
		if(!file_exists($file)){
			return false;
		}
		return $this->lib('xml')->read($file);
	}

	private function local_url($file,$gdinfo)
	{
		$gdinfo['url'] = $file;
		$token = serialize($gdinfo);
		$url = 'img.php?token='.$this->lib('common')->urlsafe_b64encode($token);
		return $url;
	}
}