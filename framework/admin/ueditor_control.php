<?php
/***********************************************************
	Filename: {phpok}/admin/ueditor_control.php
	Note	: Ueditor 編輯器中涉及到上傳的操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2014年7月7日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ueditor_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	private function load_config()
	{
		$config = $this->lib('file')->cat($this->dir_data.'config.json');
		$config = preg_replace("/\/\*[\s\S]+?\*\//","",$config);
		$config = $this->lib('json')->decode($config);
		$config['imageCompressEnable'] = false;
		$cate_rs = $this->model('rescate')->get_one();
		if(!$cate_rs){
			$cate_rs = array('id'=>0,'root'=>'res/','folder'=>'/');
		}
		if($cate_rs['root'] == '/' || !$cate_rs['root']){
			$cate_rs["root"] = "res/";
		}
		$folder = $cate_rs["root"];
		if($cate_rs["folder"] && $cate_rs["folder"] != "/"){
			$folder .= date($cate_rs["folder"],$this->time);
		}
		if(!file_exists($this->dir_root.$folder)){
			$this->lib('file')->make($this->dir_root.$folder);
		}
		if(!file_exists($this->dir_root.$folder)){
			$folder = $cate_rs['root'];
		}
		if(!file_exists($this->dir_root.$folder)){
			$folder = 'res/';
		}
		if(substr($folder,-1) != "/"){
			$folder .= "/";
		}
		if(substr($folder,0,1) == "/"){
			$folder = substr($folder,1);
		}
		if($folder){
			$folder = str_replace("//","/",$folder);
		}
		$rooturl = $this->root_url();
		$config['imagePathFormat'] = $folder;
		$config['imageManagerUrlPrefix'] = $rooturl;
		$domain = $this->lib('server')->domain($this->config['get_domain_method']);
		$tmp = array('localhost','127.0.0.1','img.baidu.com');
		if($domain){
			$tmp[] = $domain;
		}
		$tmp_xml = $this->model('res')->remote_config();
		$domainlist = '*';
		if($tmp_xml && $tmp_xml['domain1']){
			$tmp = explode("\n",$tmp_xml['domain1']);
			if($domain){
				$tmp[] = $domain;
			}
		}
		if($tmp_xml && $tmp_xml['domain2']){
			$tmplist = explode("\n",$tmp_xml['domain2']);
			$dlist = array();
			$is_all = false;
			foreach($tmplist as $key=>$value){
				if($value && trim($value) =='*'){
					$is_all = true;
					break;
				}
				if($value && trim($value) && trim($value) != '*'){
					$dlist[] = trim($value);
				}
			}
			if(!$is_all && $dlist){
				$domainlist = $dlist;
			}
		}
		$config['catcherLocalDomain'] = array_unique($tmp);
		$config['catcherPathFormat'] = $folder;
		$config['videoPathFormat'] = $folder;
		$config['filePathFormat'] = $folder;
		$config['fileManagerUrlPrefix'] = $rooturl;
		$config['cateid'] = $cate_rs['id'];
		$config['phpok_get_local_domains'] = $domainlist;
		foreach($config as $key=>$value){
			if(substr($key,0,5) == 'scraw'){
				unset($config[$key]);
			}
		}
		return $config;
	}

	//停止執行
	private function _stop($info,$data='')
	{
		if(!$data){
			$data = array();
		}
		$data['state'] = ($info && !is_bool($info)) ? $info : 'SUCCESS';
		exit($this->lib('json')->encode($data));
	}

	public function index_f()
	{
		$action = $this->get('action');
		if(!$action){
			$this->_stop(P_Lang('未指定請求方式'));
		}
		$action_array = array('config','uploadimage','uploadvideo','uploadfile','listimage','listfile','listvideo','catchimage');
		if(!in_array($action,$action_array)){
			$this->_stop(P_Lang('請求引數不正確'));
		}
		$action_name = 'u_'.$action;
		$this->$action_name();
	}

	//圖片本地化
	private function u_catchimage()
	{
		$config = $this->load_config();
		$folder = $config['catcherPathFormat'];
		$imgUrls = $this->get($config['catcherFieldName']);
		$domains = $config['phpok_get_local_domains'] ? $config['phpok_get_local_domains'] : '*';
		if(!$imgUrls){
			$this->_stop(P_Lang('沒有圖片資訊'));
		}
		set_time_limit(0);
		$tmpNames = array();
		$arraylist = array("jpg","gif","png","jpeg");
		$rslist = array();
		$oldlist = array();
		foreach($imgUrls as $key=>$imgUrl){
			$imgUrl = str_replace( "&amp;" , "&" , $imgUrl);
			if(strtolower(substr($imgUrl,0,10)) == 'data:image'){
				$tmp = explode(",",$imgUrl);
				$content = base64_decode(substr($imgUrl,strlen($tmp[0])));
				$tmp_title = $this->time."_".$key;
				$new_filename = $tmp_title;
				$ext = 'png';
			}else{
				if(strpos($imgUrl,"http")!==0){
					array_push($rslist,array('state'=>'附件獲取失敗'));
					continue;
				}
				if($domains && is_array($domains)){
					$tmp_host = parse_url($imgUrl,PHP_URL_HOST);
					if(!in_array($tmp_host,$domains)){
						array_push($rslist,array('state'=>'附件獲取失敗'));
						continue;
					}
				}
				$content = $this->lib('html')->get_content($imgUrl);
				$tmp_title = basename($imgUrl);
				$new_filename = substr(md5($imgUrl),9,16)."_".rand(0,99)."_".$key;
				$ext = strtolower(substr($imgUrl,-3));
				if(!$ext || !in_array($ext,$arraylist)){
					$ext = "png";
				}
			}
            if(!$content){
	            array_push($rslist,array('state'=>P_Lang('附件獲取失敗')));
                continue;
            }
            $save_folder = $this->dir_root.$folder;
			$newfile = $save_folder.$new_filename.".".$ext;
			$this->lib('file')->save_pic($content,$newfile);
			if(!is_file($newfile)){
				array_push($rslist,array('state'=>P_Lang('附件寫入失敗')));
				continue;
			}
			//遷移附件到資料庫中
			$array = array();
			$array["cate_id"] = $config['cateid'];
			$array["folder"] = $folder;
			$array["name"] = $new_filename.'.'.$ext;
			$array["ext"] = $ext;
			$array["filename"] = $folder.$new_filename.".".$ext;
			$array["addtime"] = $this->time;
			if($tmp_title){
				$this->lib('string')->to_utf8($tmp_title);
			}
			$array["title"] = $tmp_title ? str_replace(".".$ext,"",$tmp_title) : str_replace(".".$ext,"",$new_filename);
			if(in_array($ext,$arraylist)){
				$img_ext = getimagesize($newfile);
				$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
				$array["attr"] = serialize($my_ext);
			}
			$array["admin_id"] = $_SESSION['admin_id'];
			$id = $this->model('res')->save($array);
			if(!$id){
				$this->lib('file')->rm($this->dir_root.$array['filename']);
				array_push($rslist,array('state'=>P_Lang('附件儲存失敗')));
                continue;
			}
			$this->model('res')->gd_update($id);
			$oldlist[$id] = $imgUrl;
			array_push( $rslist , array('id'=>$id) );
		}
		$idlist = array();
		foreach($rslist as $key=>$value){
			if($value['id']){
				$idlist[] = $value['id'];
			}
		}
		if(!$idlist || count($idlist)<1){
			$this->_stop(P_Lang('沒有可用的附件'));
		}
		$condition = "res.id IN(".implode(",",$idlist).")";
		$gd_rs = $this->model('gd')->get_editor_default();
		$piclist = $this->model('res')->edit_pic_list($condition,0,999,$gd_rs);
		if(!$piclist){
			$this->_stop(P_Lang('沒有可用的附件'));
		}
		$plist = array();
		foreach($piclist as $key=>$value){
			$plist[$value['id']] = $value;
		}
		foreach($rslist as $key=>$value){
			if($value['id'] && $plist[$value['id']]){
				$tmp = array();
				$tmp['title'] = $plist[$value['id']]['title'];
				$tmp['original'] = $plist[$value['id']]['title'];
				$tmp['state'] = 'SUCCESS';
				$tmp['source'] = $oldlist[$value['id']];
				$tmp['url'] = $plist[$value['id']]['filename'];
				$rslist[$key] = $tmp;
			}
		}
		$this->_stop(true,array('list'=>$rslist));
	}

	//讀取視訊列表
	private function u_listvideo()
	{
		$config = $this->load_config();
		$offset = $this->get('start','int');
		$psize = $this->get('size','int');
		$type = $config['videoManagerAllowFiles'];
		$type = implode("|",$type);
		$type = str_replace(".","",$type);
		$condition = "res.ext IN('".str_replace('|',"','",$type)."')";
		$rslist = $this->model('res')->edit_pic_list($condition,$offset,$psize,false);
		if(!$rslist){
			$this->_stop(P_Lang('視訊內容為空'));
		}
		$piclist = array();
		foreach($rslist as $key=>$value){
			$tmp = array('url'=>$value['filename'],'ico'=>$value['ico'],'mtime'=>$value['addtime'],'title'=>$value['title']);
			$piclist[] = $tmp;
		}
		$data = array('list'=>$piclist,'start'=>$offset,'size'=>$psize);
		$this->_stop(true,$data);
	}

	//檔案管理工具
	private function u_listfile()
	{
		$offset = $this->get('start','int');
		$psize = $this->get('size','int');
		$rslist = $this->model('res')->edit_pic_list('',$offset,$psize,false);
		if(!$rslist)
		{
			$this->_stop(P_Lang('附件內容為空'));
		}
		$piclist = array();
		foreach($rslist as $key=>$value)
		{
			$tmp = array('id'=>$value['id'],'url'=>$value['filename'],'ico'=>$value['ico'],'mtime'=>$value['addtime'],'original'=>$value['title']);
			$piclist[] = $tmp;
		}
		$data = array('list'=>$piclist,'start'=>$offset,'size'=>$psize);
		$this->_stop(true,$data);
	}
	//圖片管理工具
	private function u_listimage()
	{
		$offset = $this->get('start','int');
		$psize = $this->get('size','int');
		$condition = "res.ext IN ('gif','jpg','png','jpeg') ";
		$gd_rs = $this->model('gd')->get_editor_default();
		$keywords = $this->get('keywords');
		if($keywords){
			$condition .= " AND (res.filename LIKE '%".$keywords."%' OR res.title LIKE '%".$keywords."%') ";
		}
		$rslist = $this->model('res')->edit_pic_list($condition,$offset,$psize);
		if(!$rslist){
			$this->_stop(P_Lang('圖片資料內容為空'));
		}
		$piclist = array();
		foreach($rslist as $key=>$value){
			$tmp = array('url'=>$value['filename'],'ico'=>$value['ico'],'mtime'=>$value['addtime'],'title'=>$value['title'],'original'=>$value['title']);
			if($gd_rs && $value['gd'][$gd_rs['identifier']]){
				$tmp['url'] = $value['gd'][$gd_rs['identifier']];
			}
			$piclist[] = $tmp;
		}
		$data = array('list'=>$piclist,'start'=>$offset,'size'=>$psize);
		$this->_stop(true,$data);
	}

	//附件上傳
	private function u_uploadfile()
	{
		$config = $this->load_config();
		$folder = $config['filePathFormat'];
		$input_name = $config['fileFieldName'];
		$rs = $this->upload_base($input_name,$folder,$config['cateid']);
		if(!$rs || $rs['status'] != 'ok'){
			$this->_stop(P_Lang('檔案上傳失敗'));
		}
		$data = array('id'=>$rs['id'],'title'=>$rs['title'],'url'=>$rs['filename'],'original'=>$rs['title']);
		$this->_stop(true,$data);
	}
	//視訊上傳
	private function u_uploadvideo()
	{
		$config = $this->load_config();
		$folder = $config['videoPathFormat'];
		$input_name = $config['videoFieldName'];
		$rs = $this->upload_base($input_name,$folder,$config['cateid']);
		if(!$rs || $rs['status'] != 'ok'){
			$this->_stop(P_Lang('視訊上傳失敗'));
		}
		$data = array('title'=>$rs['title'],'url'=>$rs['filename'],'original'=>$rs['title']);
		$this->_stop(true,$data);
	}

	//圖片上傳
	private function u_uploadimage()
	{
		$config = $this->load_config();
		$rs = $this->upload_base($config['imageFieldName'],$config['imagePathFormat'],$config['cateid']);
		if(!$rs || $rs['status'] != 'ok')
		{
			$this->_stop(P_Lang('上傳失敗：').$rs['content']);
		}
		$gd_rs = $this->model('gd')->get_editor_default();
		if($gd_rs)
		{
			$ext_rs = $this->model('res')->get_gd_pic($rs['id']);

            // new edit
            //$filename = ($ext_rs && $ext_rs[$gd_rs['identifier']]) ? $ext_rs[$gd_rs['identifier']]['filename'] : $rs['filename'];
            $filename = ($ext_rs && $ext_rs[$gd_rs['identifier']]) ? $ext_rs[$gd_rs['identifier']] : $rs['filename'];
		}
		else
		{
			$filename = $rs['filename'];
		}
		$data = array('title'=>$rs['title'],'url'=>$filename,'original'=>$rs['title']);
		$this->_stop(true,$data);
	}

	//讀取配置資訊
	private function u_config()
	{
		$config = $this->load_config();
		$this->_stop(true,$config);
	}

	//寫入主題列表
	public function info_f()
	{
		$pageurl = $this->url("ueditor","info");
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = 14;
		$offset = ($pageid - 1) * $psize;
		//讀取所有專案
		$projectlist = $this->model('project')->get_all_project($_SESSION['admin_site_id']);
		$this->assign("projectlist",$projectlist);
		//讀取全部列表
		$condition = "l.site_id=".$_SESSION['admin_site_id'];
		$project_id = $this->get('project_id','int');
		if($project_id)
		{
			$p_rs = $this->model('project')->get_one($project_id);
			if($p_rs)
			{
				$condition .= " AND l.project_id=".$project_id;
				$pageurl .= "&project_id=".$project_id;
				$cate_id = $this->get('cate_id','int');
				if($cate_id && $p_rs['cate'])
				{
					$cate_rs = $this->model('cate')->get_one($cate_id);
					$catelist = array($cate_rs);
					$this->model('cate')->get_sublist($catelist,$cate_id);
					$cate_id_list = array();
					foreach($catelist AS $key=>$value)
					{
						$cate_id_list[] = $value["id"];
					}
					$cate_idstring = implode(",",$cate_id_list);
					$condition .= " AND l.cate_id IN(".$cate_idstring.")";
					$pageurl .= "&cate_id=".$cate_id;
					$this->assign("cate_id",$cate_id);
				}
				$this->assign("project_id",$project_id);
			}
		}
		$keywords = $this->get("keywords");
		if($keywords)
		{
			$condition .= " AND (l.title LIKE '%".$keywords."%' OR l.tag LIKE '%".$keywords."%' OR l.seo_keywords LIKE '%".$keywords."%' OR l.seo_desc LIKE '%".$keywords."%' OR l.seo_title LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$total = $this->model('list')->get_all_total($condition);
		if($total>0)
		{
			$rslist = $this->model('list')->get_all($condition,$offset,$psize);
			$this->assign("rslist",$rslist);
			$this->assign("total",$total);
			if($total>$psize)
			{
				$string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上一頁').'&next='.P_Lang('下一頁').'&last='.P_Lang('尾頁').'&half=3';
				$string.= '&add='.P_Lang('數量：').'(total)/(psize)'.P_Lang('，').P_Lang('頁碼：').'(num)/(total_page)&always=1';
				$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
				$this->assign("pagelist",$pagelist);
			}
		}
		$this->view("edit_title");
	}
	
	//基礎上傳
	private function upload_base($input_name='upfile',$folder='res/',$cateid=0)
	{
		//上傳型別
		$typelist = $this->model('res')->type_list();
		if($typelist){
			$ext = array();
			foreach($typelist as $key=>$value){
				$ext[] = $value['ext'];
			}
			$ext = implode(",",$ext);
			$this->lib('upload')->set_type($ext);
		}
		$rs = $this->lib('upload')->upload($input_name);
		if($rs["status"] != "ok"){
			return $rs;
		}
		//儲存目錄
		$basename = basename($rs["filename"]);
		$save_folder = $this->dir_root.$folder;
		if($folder.$basename != $rs["filename"]){
			$this->lib('file')->mv($rs["filename"],$save_folder.$basename);
		}
		if(!file_exists($save_folder.$basename)){
			$this->lib('file')->rm($rs["filename"]);
			$rs = array();
			$rs["status"] = "error";
			$rs["error"] = P_Lang('附件遷移失敗');
			return $rs;
		}
		$rs['title'] = $this->lib('string')->to_utf8($rs['title']);
		$array = array();
		$array["cate_id"] = $cateid;
		$array["folder"] = $folder;
		$array["name"] = $basename;
		$array["ext"] = $rs["ext"];
		$array["filename"] = $folder.$basename;
		$array["addtime"] = $this->time;
		$array["title"] = str_replace(".".$rs["ext"],"",$rs["title"]);
		$arraylist = array("jpg","gif","png","jpeg");
		if(in_array($rs["ext"],$arraylist)){
			$img_ext = getimagesize($save_folder.$basename);
			$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
			$array["attr"] = serialize($my_ext);
		}
		$array["admin_id"] = $this->session->val('admin_id');
		//儲存圖片資訊
		$id = $this->model('res')->save($array);
		if(!$id){
			$this->lib('file')->rm($save_folder.$basename);
			$rs = array();
			$rs["status"] = "error";
			$rs["error"] = P_Lang('儲存失敗');
			return $rs;
		}
		$this->model('res')->gd_update($id);
		$rs = $this->model('res')->get_one($id);
		$rs["status"] = "ok";
		return $rs;
	}
}