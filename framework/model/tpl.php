<?php
/**
 * 模板相關操作
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年06月30日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tpl_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 獲取模板資訊
	 * @引數 $id 模板ID，數字
	**/
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."tpl WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得全部風格列表，不限站點
	**/
	public function get_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."tpl ORDER BY id DESC";
		return $this->db->get_all($sql);
	}

	/**
	 * 儲存或新增風格資訊
	 * @引數 $data 陣列，儲存模板引數資訊
	 * @引數 $id 主鍵ID，留空或為0表示新增新模板記錄
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"tpl",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"tpl");
		}
	}

	/**
	 * 刪除模板記錄
	 * @引數 $id 主鍵ID
	**/
	public function delete($id)
	{
		if(!$id){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."tpl WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function tpl_info($id)
	{
		$rs = $this->get_one($id);
		if(!$rs){
			return false;
		}
		$tpl_rs = array('id'=>$rs['id'],'dir_root'=>$this->dir_root);
		$tpl_rs["dir_tpl"] = $rs["folder"] ? "tpl/".$rs["folder"]."/" : "tpl/www/";
		$tpl_rs["dir_cache"] = $this->dir_data."tpl_www/";
		$tpl_rs["dir_php"] = $rs['phpfolder'] ? $this->dir_root.$rs['phpfolder'].'/' : $this->dir_root.'phpinc/';
		if($rs["folder_change"]){
			$tpl_rs["path_change"] = $rs["folder_change"];
		}
		$tpl_rs["refresh_auto"] = $rs["refresh_auto"] ? true : false;
		$tpl_rs["refresh"] = $rs["refresh"] ? true : false;
		$tpl_rs["tpl_ext"] = $rs["ext"] ? $rs["ext"] : "html";
		if($this->is_mobile){
			$tpl_rs["id"] = $rs["id"]."_mobile";
			$tplfolder = $rs["folder"] ? $rs["folder"]."_mobile" : "www_mobile";
			if(!file_exists($this->dir_root."tpl/".$tplfolder)){
				$tplfolder = $rs["folder"] ? $rs["folder"] : "www";
			}
			$tpl_rs["dir_tpl"] = "tpl/".$tplfolder;
		}
		$tpl_rs['langid'] = $this->session->val($this->app_id.'_lang_id');
		return $tpl_rs;
	}

	public function all_files()
	{
		$list = $this->get_all();
		if(!$list){
			return false;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			$filelist = $this->files($value);
			if($filelist){
				$rslist = array_merge($rslist,$filelist);
			}
		}
		return $rslist;
	}

	/**
	 * 讀取檔案列表，過濾掉資料夾和block_開頭的檔案
	 * @引數 $rs 陣列或數字，模板基礎資料
	**/
	public function files($rs=0)
	{
		if($rs && is_numeric($rs)){
			$rs = $this->get_one($rs);
		}
		if(!$rs){
			return false;
		}
		if(!$rs['ext']){
			$rs['ext'] = 'html';
		}
		$ext_length = strlen($rs["ext"]);
		$list = $this->lib('file')->ls($this->dir_root.'tpl/'.$rs['folder'].'/');
		if(!$list){
			return false;
		}
		$tmplist = false;
		if(file_exists($this->dir_data.'xml/tpl_'.$rs['id'].'.xml')){
			$tmplist = $this->lib('xml')->read($this->dir_data.'xml/tpl_'.$rs['id'].'.xml');
		}
		$rslist = false;
		foreach($list as $key=>$value){
			$bname = $this->lib('string')->to_utf8(basename($value));
			if(is_dir($value) || substr($bname,-$ext_length) != $rs["ext"] || substr($bname,0,6) == 'block_'){
				continue;
			}
			$tplid = substr($bname,0,-($ext_length+1));
			$tmpid = $tplid;
			if(is_numeric(substr($tplid,0,1))){
				$tmpid = 'ok'.$tplid;
			}
			$title = $rs['title'].':'.$bname;
			if($tmplist && $tmplist[$tmpid]){
				$title = $rs['title'].':'.$tmplist[$tmpid].':'.$bname;
			}
			$rslist[$tmpid.'-'.$rs['id']] = array("title"=>$title,"id"=>$rs['id'].':'.$tplid);
		}
		return $rslist;
	}
}