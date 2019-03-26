<?php
/**
 * 功能應用管理工具
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @許可 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2018年06月05日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class appsys_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('appsys');
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 管理整個平臺功能應用器
	**/
	public function index_f()
	{
		if(!$this->popedom['list']){
			$this->error(P_Lang('您沒有檢視許可權'));
		}
		$rslist = $this->model('appsys')->get_all();
		$this->assign('rslist',$rslist);
		$this->view('appsys_index');
	}

	public function setting_f()
	{
		if(!$this->popedom['setting']){
			$this->error(P_Lang('您沒有配置環境許可權'));
		}
		$rs = $this->model('appsys')->server();
		if($rs && is_array($rs)){
			$this->assign('rs',$rs);
		}
		$this->view('appsys_setting');
	}

	public function setting_save_f()
	{
		if(!$this->popedom['setting']){
			$this->error(P_Lang('您沒有配置環境許可權'));
		}
		$data = array();
		$data['server'] = $this->get('server');
		$data['ip'] = $this->get('ip');
		$data['folder'] = $this->get('folder');
		$data['https'] = $this->get('https','int');
		$this->model('appsys')->server($data);
		$this->success();
	}

	public function remote_f()
	{
		if(!$this->popedom['remote']){
			$this->error(P_Lang('您沒有更新遠端資料許可權'));
		}
		$server = $this->model('appsys')->server();
		if(!$server || !$server['server']){
			$this->error(P_Lang('未配置好遠端伺服器環境，請更新配置環境'));
		}
		$url  = $server['https'] ? 'https://' : 'http://';
		$url .= $server['server'];
		if(substr($url,-1) == '/'){
			$url = substr($url,0,-1);
		}
		$folder = !$server['folder'] ? '/' : (substr($server['folder'],0,1) != '/' ? '/'.$server['folder'] : $server['folder']);
		$url .= $folder;
		$this->lib('curl')->timeout(30);
		if($server['ip']){
			$this->lib('curl')->host_ip($server['ip']);
		}
		$data = $this->lib('curl')->get_json($url);
		if(!$data){
			$this->error(P_Lang('遠端更新資料失敗'));
		}
		if(!$data['status']){
			$tip = $data['info'] ? $data['info'] : ($data['error'] ? $data['error'] : P_Lang('獲取資料失敗'));
			$this->error($tip);
		}
		$this->lib('xml')->save($data['info'],$this->dir_data.'xml/appall.xml');
		$this->success();
	}

	public function import_f()
	{
		$array = array("identifier"=>'zipfile',"form_type"=>'upload');
		$array['upload_type'] = 'update';
		$this->lib('form')->cssjs($array);
		$upload = $this->lib('form')->format($array);
		$this->assign('upload_html',$upload);
		$this->view('appsys_upload');
	}

	/**
	 * 解壓應用
	**/
	public function unzip_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$filename = $this->get('filename');
			if(!$filename){
				$this->error(P_Lang('附件不存在'));
			}
		}else{
			$rs = $this->model('res')->get_one($id);
			if(!$rs){
				$this->error(P_Lang('附件不存在'));
			}
			$filename = $rs['filename'];
		}
		$tmp = strtolower(substr($filename,-4));
		if($tmp != '.zip'){
			$this->error(P_Lang('非ZIP檔案不支援線上解壓'));
		}
		if(!file_exists($this->dir_root.$filename)){
			$this->error(P_Lang('檔案不存在'));
		}
		$info = $this->lib('phpzip')->zip_info($this->dir_root.$filename);
		$info = current($info);
		if(!$info['filename']){
			$this->error(P_Lang('應用有異常'));
		}
		$info = explode('/',$info['filename']);
		if(!$info[0]){
			$this->error(P_Lang('應用有異常'));
		}
		if(file_exists($this->dir_app.$info[0])){
			$this->error(P_Lang('應用已存在，不允許重複解壓'));
		}
		$this->lib('phpzip')->unzip($this->dir_root.$filename,$this->dir_app);
		$config = $this->model('appsys')->get_one($info[0]);
		$config['installed'] = false;
		$this->lib('xml')->save($config,$this->dir_app.$info[0].'/config.xml');
		$this->success();
	}

	public function backup_list_f()
	{
		$rslist = $this->model('appsys')->backup_all(false);
		if($rslist){
			$this->assign('rslist',$rslist);
		}
		$this->view('appsys_backuplist');
	}

	/**
	 * 備份應用到 zip 目錄
	**/
	public function backup_f()
	{
		$id = $this->get('id','system');
		if(!$id){
			$this->error(P_Lang('未指定專案'),$this->url('appsys'));
		}
		if(!file_exists($this->dir_app.$id)){
			$this->error(P_Lang('應用不存在'),$this->url('appsys'));
		}
		$rs = $this->model('appsys')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('未找到配置資訊'),$this->url('appsys'));
		}
		$zipfile = $this->dir_data.'zip/'.$id.'-'.date("Ymd",$this->time).'.zip';
		if(is_file($zipfile)){
			$this->lib('file')->rm($zipfile);
		}
		$this->lib('phpzip')->set_root($this->dir_app);
		$this->lib('phpzip')->zip($this->dir_app.$id,$zipfile);
		$this->success();
	}

	/**
	 * 刪除備份檔案
	**/
	public function backup_delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您沒有刪除應用的許可權'),$this->url('appsys'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定要刪除的備份檔案'));
		}
		if(is_file($this->dir_data.'zip/'.$id)){
			$this->lib('file')->rm($this->dir_data.'zip/'.$id);
		}
		$this->success();
	}

	/**
	 * 解除安裝應用，不刪除操作
	**/
	public function uninstall_f()
	{
		if(!$this->popedom['uninstall']){
			$this->error(P_Lang('您沒有解除安裝應用的許可權'),$this->url('appsys'));
		}
		$id = $this->get('id','system');
		if(!$id){
			$this->error(P_Lang('未指定專案'),$this->url('appsys'));
		}
		if(!file_exists($this->dir_app.$id)){
			$this->error(P_Lang('應用不存在'),$this->url('appsys'));
		}
		$rs = $this->model('appsys')->get_one($id);
		if($rs && $rs['uninstall'] && is_file($this->dir_app.$id.'/'.$rs['uninstall'])){
			include_once($this->dir_app.$id.'/'.$rs['uninstall']);
		}
		$this->model('appsys')->uninstall($id);
		$this->success(P_Lang('應用解除安裝成功'),$this->url('appsys'));
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您沒有刪除應用的許可權'),$this->url('appsys'));
		}
		$id = $this->get('id','system');
		if(!$id){
			$this->error(P_Lang('未指定專案'),$this->url('appsys'));
		}
		if(!file_exists($this->dir_app.$id)){
			$this->error(P_Lang('應用不存在'),$this->url('appsys'));
		}
		$rs = $this->model('appsys')->get_one($id);
		if(isset($rs['installed']) && $rs['installed']){
			$this->error(P_Lang('未解除安裝的應用不能刪除'));
		}
		$baklist = $this->model('appsys')->backup_all(true);
		if(!$baklist[$id]){
			$this->error(P_Lang('沒有找到備份檔案，不能刪除'));
		}
		$this->lib('file')->rm($this->dir_app.$id);
		$this->success();
	}

	/**
	 * 匯出應用
	 * @引數 $id 應用ID
	**/
	public function export_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定備份應用ID'),$this->url('appsys'));
		}
		$rs = $this->model('appsys')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('未找到配置資訊'),$this->url('appsys'));
		}
		$zipfile = $this->dir_cache.$id.'-'.$this->time.'-'.rand(100,999).'.zip';
		if(is_file($zipfile)){
			$this->lib('file')->rm($zipfile);
		}
		$this->lib('phpzip')->set_root($this->dir_app);
		$this->lib('phpzip')->zip($this->dir_app.$id,$zipfile);
		$this->lib('file')->download($zipfile,$rs['title'].'.zip');
	}

	/**
	 * 執行安裝
	**/
	public function install_f()
	{
		if(!$this->popedom['install']){
			$this->error(P_Lang('您沒有安裝應用的許可權'),$this->url('appsys'));
		}
		$id = $this->get('id','system');
		if(!$id){
			$this->error(P_Lang('未指定專案'),$this->url('appsys'));
		}
		if(is_dir($this->dir_app.$id)){
			//檢查Config檔案
			if(is_file($this->dir_app.$id.'/config.xml')){
				$info = $this->lib('xml')->read($this->dir_app.$id.'/config.xml',true);
				if($info && $info['install'] && is_file($this->dir_app.$id.'/'.$info['install'])){
					include_once($this->dir_app.$id.'/'.$info['install']);
				}
			}
			$this->model('appsys')->install($id);
			$this->success(P_Lang('安裝成功'),$this->url('appsys'));
		}
		if(!is_file($this->dir_data.'zip/'.$id.'.zip')){
			$server = $this->model('appsys')->server();
			if(!$server || !$server['server']){
				$this->error(P_Lang('未配置好遠端伺服器環境，請更新配置環境'),$this->url('appsys'));
			}
			$url  = $server['https'] ? 'https://' : 'http://';
			$url .= $server['server'];
			if(substr($url,-1) == '/'){
				$url = substr($url,0,-1);
			}
			$folder = !$server['folder'] ? '/' : (substr($server['folder'],0,1) != '/' ? '/'.$server['folder'] : $server['folder']);
			$url .= $folder;
			$this->lib('curl')->timeout(30);
			if($server['ip']){
				$this->lib('curl')->host_ip($server['ip']);
			}
			$info = $this->lib('curl')->get_json($url.'?id='.$id);
			if(!$info['status']){
				$info = $info['info'] ? $info['info'] : ($info['error'] ? $info['error'] : '安裝失敗');
				$this->error(P_Lang($info),$this->url('appsys'));
			}
			$content = base64_decode($info['info']);
			$this->lib('file')->save_pic($content,$this->dir_data.'zip/'.$id.'.zip');
		}
		if(!is_file($this->dir_data.'zip/'.$id.'.zip')){
			$this->error(P_Lang('專案不存在'));
		}
		//解壓到目標檔案
		$ziplist = $this->lib('phpzip')->zip_info($this->dir_data.'zip/'.$id.'.zip');
		if(!$ziplist){
			$this->error(P_Lang('壓縮包資料有錯誤'),$this->url('appsys'));
		}
		if(count($ziplist)==1){
			if(!file_exists($this->dir_app.$id)){
				$this->lib('file')->make($this->dir_app.$id);
			}
			$this->lib('phpzip')->unzip($this->dir_data.'zip/'.$id.'.zip',$this->dir_app.$id);
		}else{
			$this->lib('phpzip')->unzip($this->dir_data.'zip/'.$id.'.zip',$this->dir_app);
		}
		//檢查Config檔案
		if(is_file($this->dir_app.$id.'/config.xml')){
			$info = $this->lib('xml')->read($this->dir_app.$id.'/config.xml',true);
			if($info && $info['install'] && is_file($this->dir_app.$id.'/'.$info['install'])){
				include_once($this->dir_app.$id.'/'.$info['install']);
			}
		}
		$this->model('appsys')->install($id);
		$this->success(P_Lang('安裝成功'),$this->url('appsys'));
	}

	public function add_f()
	{
		if(!$this->popedom['setting']){
			$this->error(P_Lang('您沒有建立應用許可權'));
		}
		$this->view('appsys_add');
	}

	public function filelist_f()
	{
		if(!$this->popedom['filelist']){
			$this->error(P_Lang('您沒有模板應用檔案列表許可權'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('appsys')->get_one($id);
		$this->assign('rs',$rs);
		$this->assign('id',$id);
		if(!is_dir($this->dir_app.$id)){
			$this->error(P_Lang('目錄不存在'));
		}
		$folder = $this->get("folder");
		if(!$folder){
			$folder = "/";
		}
		$tmplist = explode("/",$folder);
		$leadlist = array();
		$leadurl = $this->url("appsys","filelist","id=".$id);
		if(substr($folder,-1) != "/"){
			$folder .= "/";
		}
		if($folder && $folder != '/'){
			$this->assign("folder",$folder);
		}
		//繫結目錄
		$tpl_dir = $this->dir_app.$id."/".$folder;
		$tpl_list = $this->lib('file')->ls($tpl_dir);
		if(!$tpl_list){
			$tpl_list = array();
		}
		$myurl = $this->url("appsys","filelist","id=".$id);
		$rslist = $dirlist = array();
		$rs_i = $dir_i = 0;
		$edit_array = array("html","php","js","css","asp","jsp","tpl","dwt","aspx","htm","txt","xml");
		$pic_array = array("gif","png","jpeg","jpg");
		$this->assign("edit_array",$edit_array);
		$this->assign("pic_array",$pic_array);
		foreach($tpl_list as $key=>$value){
			$bname = basename($value);
			$type = is_dir($value) ? "dir" : "file";
			if(is_dir($value)){
				$url = $this->url("appsys","filelist","id=".$id."&folder=".rawurlencode($folder.$bname."/"));
				$dirlist[] = array("filename"=>$value,"title"=>$bname,"data"=>"","type"=>"dir","url"=>$url);
				$dir_i++;
			}else{
				$date = date("Y-m-d H:i:s",filemtime($value));
				$tmp = explode(".",$bname);
				$tmp_total = count($tmp);
				$type = "unknown";
				if($tmp_total > 1){
					$tmp_ext = strtolower($tmp[($tmp_total-1)]);
					$typefile = $this->dir_root."images/filetype/".$tmp_ext.".gif";
					$type = file_exists($typefile) ? $tmp_ext : "unknown";
				}
				$tmp = array("filename"=>$value,"title"=>$bname,"date"=>$date,"type"=>$type);
				$rslist[] = $tmp;
				$rs_i++;
			}
		}
		if($dir_i> 0){
			$this->assign("dirlist",$dirlist);
		}
		if($rs_i > 0){
			$this->assign("rslist",$rslist);
		}
		$this->view('appsys_filelist');
	}

	public function file_edit_f()
	{
		if(!$this->popedom['fedit']){
			$this->error(P_Lang('您沒有模板應用檔案列表許可權'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('appsys')->get_one($id);
		if(!is_dir($this->dir_app.$id)){
			$this->error(P_Lang('目錄不存在'));
		}
		$folder = $this->get("folder");
		if(!$folder){
			$folder = "/";
		}
		$title = $this->get("title");
		if(!$title){
			$this->error(P_Lang('未指定檔名'));
		}
		$file = $this->dir_app.$id."/".$folder.$title;
		if(!file_exists($file)){
			$this->error(P_Lang('檔案不存在'));
		}
		$is_edit = true;
		if(!is_writable($file)){
			$tips = P_Lang('檔案無法寫法，不支援線上編輯');
			$this->assign('tips',$tips);
			$is_edit = false;
		}
		$this->assign('is_edit',$is_edit);
		$content = $this->lib('file')->cat($file);
		$content = str_replace(array("&lt;",'&gt;'),array("&amp;lt;","&amp;gt;"),$content);
		$content = str_replace(array('<','>'),array('&lt;','&gt;'),$content);
		$this->assign("content",$content);
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$this->assign("folder",$folder);
		$this->assign("title",$title);
		//載入編輯器
		$cdnUrl = phpok_cdn();
		$this->addcss($cdnUrl.'codemirror/5.42.2/lib/codemirror.css');
		$this->addjs($cdnUrl.'codemirror/5.42.2/lib/codemirror.js');
		$this->addjs($cdnUrl.'codemirror/5.42.2/mode/css/css.js');
		$this->addjs($cdnUrl.'codemirror/5.42.2/mode/javascript/javascript.js');
		$this->addjs($cdnUrl.'codemirror/5.42.2/mode/htmlmixed/htmlmixed.js');
		$this->addjs($cdnUrl.'codemirror/5.42.2/mode/php/php.js');
		$this->addjs($cdnUrl.'codemirror/5.42.2/mode/xml/xml.js');
		$this->view("appsys_file_edit");
	}

	public function file_edit_save_f()
	{
		if(!$this->popedom["fedit"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('appsys')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('專案資訊不存在'));
		}
		$folder = $this->get("folder");
		if(!$folder){
			$folder = "/";
		}
		$title = $this->get("title");
		$file = $this->dir_app."/".$id.$folder.$title;
		if(!file_exists($file)){
			$this->error(P_Lang('檔案不存在'));
		}
		if(!is_writable($file)){
			$this->error(P_Lang('檔案無法寫法，不支援線上編輯'));
		}
		$content = $this->get("content","html_js");
		$this->lib('file')->vim($content,$file);
		$this->success();
	}

	public function create_f()
	{
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('應用名稱不能為空'));
		}
		$identifier = $this->get('identifier','system');
		if(!$identifier){
			$this->error(P_Lang('標識為空或標識不符合規定'));
		}
		$elist = $this->_get_applist();
		if($elist && in_array($identifier,$elist)){
			$this->error(P_Lang('標識已存在'));
		}
		$is_admin = $this->get('is_admin','checkbox');
		$is_api = $this->get('is_api','checkbox');
		$is_www = $this->get('is_www','checkbox');
		if(!$is_admin && !$is_api && !$is_www){
			$this->error(P_Lang('至少選擇一個執行範圍'));
		}
		$install = $this->get('install');
		$uninstall = $this->get('uninstall');
		$note = $this->get('note');
		$author = $this->get('author');
		$this->lib('file')->make($this->dir_app.$identifier,'dir');
		if(!file_exists($this->dir_app.$identifier)){
			$this->error(P_Lang('目錄不存在'));
		}
		//建立模板目錄
		$this->lib('file')->make($this->dir_app.$identifier.'/tpl','dir');
		//寫入檔案
		$data = array('title'=>$title);
		$data['status'] = array('admin'=>$is_admin,'www'=>$is_www,'api'=>$is_api);
		if($install){
			if(substr($install,-4) != '.php'){
				$install .= ".php";
			}
			$data['install'] = $install;
		}
		if($uninstall){
			if(substr($uninstall,-4) != '.php'){
				$uninstall .= ".php";
			}
			$data['uninstall'] = $uninstall;
		}
		$data['installed'] = false;
		$this->lib('xml')->save($data,$this->dir_app.$identifier.'/config.xml');
		if(!is_file($this->dir_app.$identifier.'/config.xml')){
			$this->error(P_Lang('配置檔案寫入失敗'));
		}
		//安裝檔案
		if($install){
			$content  = $this->_php_head();
			$content .= $this->_php_notes(P_Lang('安裝檔案'),$note,$author);
			$content .= $this->_php_safe();
			$content .= $this->_php_install($title,$identifier);
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/'.$install);
			$content = '-- 安裝資料庫檔案，直接在這裡寫SQL';
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/install.sql');
		}
		//解除安裝檔案
		if($uninstall){
			$content  = $this->_php_head();
			$content .= $this->_php_notes(P_Lang('解除安裝檔案'),$note,$author);
			$content .= $this->_php_safe();
			$content .= $this->_php_uninstall($identifier);
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/'.$uninstall);
			$content = '-- 解除安裝資料庫檔案，直接在這裡寫SQL';
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/uninstall.sql');
		}

		//建立控制器
		if($is_admin){
			$content  = $this->_php_head();
			$content .= $this->_php_notes(P_Lang('後臺管理'),$note,$author);
			$content .= $this->_php_namespace($identifier,'control');
			$content .= $this->_php_safe();
			$content .= $this->_php_control('admin',$identifier);
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/admin.control.php');
			$content = "<!-- include tpl=head_lay nopadding=true -->\n//\n<!-- include tpl=foot_lay is_open=true -->";
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/tpl/admin_index.html');
			//建立JS
			$content  = $this->_php_notes(P_Lang('後面頁面指令碼'),$note,$author);
			$content .= $this->_js_config('admin',$identifier);
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/admin.js');
		}
		if($is_www){
			$content  = $this->_php_head();
			$content .= $this->_php_notes(P_Lang('網站前臺'),$note,$author);
			$content .= $this->_php_namespace($identifier,'control');
			$content .= $this->_php_safe();
			$content .= $this->_php_control('www',$identifier);
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/www.control.php');
			$content = "<!-- include tpl=head -->\n//\n<!-- include tpl=foot -->";
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/tpl/www_index.html');
			//建立JS
			$content  = $this->_php_notes(P_Lang('前臺頁面指令碼'),$note,$author);
			$content .= $this->_js_config('www',$identifier);
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/www.js');
		}
		if($is_api){
			$content  = $this->_php_head();
			$content .= $this->_php_notes(P_Lang('介面應用'),$note,$author);
			$content .= $this->_php_namespace($identifier,'control');
			$content .= $this->_php_safe();
			$content .= $this->_php_control('api',$identifier);
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/api.control.php');
		}
		//公共Model
		$content  = $this->_php_head();
		$content .= $this->_php_notes(P_Lang('模型內容資訊'),$note,$author);
		$content .= $this->_php_namespace($identifier,'model');
		$content .= $this->_php_safe();
		$content .= $this->_php_model_base();
		$this->lib('file')->vim($content,$this->dir_app.$identifier.'/model.php');
		//建立公共頁global.func.php
		$content  = $this->_php_head();
		$content .= $this->_php_notes(P_Lang('公共方法'),$note,$author);
		$content .= $this->_php_safe();
		$this->lib('file')->vim($content,$this->dir_app.$identifier.'/global.func.php');
		$this->success();
	}

	private function _php_notes($title,$note='',$author='')
	{
		if(!$author){
			$author = 'phpok.com <admin@phpok.com>';
		}
		if($note){
			$title .= '_'.$note;
		}
		$info  = '/**'."\n";
		$info .= ' * '.$title."\n";
		$info .= ' * @作者 '.$author."\n";
		$info .= ' * @版權 深圳市錕鋙科技有限公司'."\n";
		$info .= ' * @主頁 http://www.phpok.com'."\n";
		$info .= ' * @版本 5.x'."\n";
		$info .= ' * @許可 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License'."\n";
		$info .= ' * @時間 '.date("Y年m月d日 H時i分",$this->time)."\n";
		$info .= '**/'."\n";
		return $info;
	}

	private function _php_namespace($identifier,$type='control')
	{
		$info = 'namespace phpok\\\app\\\\'.$type.'\\\\'.$identifier.';'."\n";
		return $info;
	}

	private function _php_safe()
	{
		$info  = '/**'."\n";
		$info .= ' * 安全限制，防止直接訪問'."\n";
		$info .= '**/'."\n";
		$info .= 'if(!defined("PHPOK_SET")){'."\n";
		$info .= '	exit("<h1>Access Denied</h1>");'."\n";
		$info .= '}'."\n";
		return $info;
	}

	private function _php_install($title,$identifier)
	{
		$info  = '//phpok_loadsql($this->db,$this->dir_app.\''.$identifier.'/install.sql\',true);'."\n";
		$info .= '//增加導航選單'."\n";
		$info .= '//$menu = array(\'parent_id\'=>5,\'title\'=>\''.$title.'\',\'status\'=>1);'."\n";
		$info .= '//$menu[\'appfile\'] = \''.$identifier.'\';'."\n";
		$info .= '//$menu[\'taxis\'] = 255;'."\n";
		$info .= '//$menu[\'site_id\'] = 0;'."\n";
		$info .= '//$menu[\'icon\'] = \'newtab\';'."\n";
		$info .= '//$insert_id = $this->model(\'sysmenu\')->save($menu);'."\n";
		$info .= '//if($insert_id){'."\n";
		$info .= '//	$tmparray = array(\'gid\'=>$insert_id,\'title\'=>\'檢視\',\'identifier\'=>\'list\',\'taxis\'=>10);'."\n";
		$info .= '//	$this->model(\'popedom\')->save($tmparray);'."\n";
		$info .= '//	$tmparray = array(\'gid\'=>$insert_id,\'title\'=>\'刪除\',\'identifier\'=>\'delete\',\'taxis\'=>10);'."\n";
		$info .= '//	$this->model(\'popedom\')->save($tmparray);'."\n";
		$info .= '//}'."\n";
		return $info;
	}

	private function _php_uninstall($identifier)
	{
		$info  = '//phpok_loadsql($this->db,$this->dir_app.\''.$identifier.'/uninstall.sql\',true);'."\n";
		$info .= '//$sql = "SELECT * FROM ".$this->db->prefix."sysmenu WHERE appfile=\''.$identifier.'\'";'."\n";
		$info .= '//$rs = $this->db->get_one($sql);'."\n";
		$info .= '//if($rs){'."\n";
		$info .= '//	$sql = "DELETE FROM ".$this->db->prefix."popedom WHERE gid=\'".$rs[\'id\']."\'";'."\n";
		$info .= '//	$this->db->query($sql);'."\n";
		$info .= '//	$sql = "DELETE FROM ".$this->db->prefix."sysmenu WHERE id=\'".$rs[\'id\']."\'";'."\n";
		$info .= '//	$this->db->query($sql);'."\n";
		$info .= '//}'."\n";
		return $info;
	}

	private function _php_control($type='admin',$identifier='')
	{
		$info  = 'class '.$type.'_control extends \\\phpok_control'."\n";
		$info .= '{'."\n";
		if($type == 'admin'){
			$info .= '	private $popedom;'."\n";
		}
		$info .= '	public function __construct()'."\n";
		$info .= '	{'."\n";
		$info .= '		parent::control();'."\n";
		if($type == 'admin'){
			$info .= '		$this->popedom = appfile_popedom(\''.$identifier.'\');'."\n";
			$info .= '		$this->assign("popedom",$this->popedom);'."\n";
		}
		$info .= '	}'."\n\n";
		$info .= '	public function index_f()'."\n";
		$info .= '	{'."\n";
		if($type == 'api'){
			$info .= '		//$info = "";'."\n";
			$info .= '		//$this->error($info);'."\n";
			$info .= '		$this->success();'."\n";
		}else{
			$info .= '		$this->display(\''.$type.'_index\');'."\n";
		}
		$info .= '	}'."\n";
		$info .= '}'."\n";
		return $info;
	}

	private function _php_model_base()
	{
		$info  = 'class model extends \\\phpok_model'."\n";
		$info .= '{'."\n";
		$info .= '	public function __construct()'."\n";
		$info .= '	{'."\n";
		$info .= '		parent::model();'."\n";
		$info .= '	}'."\n\n";
		$info .= '}'."\n";
		return $info;
	}

	private function _js_config($type='admin',$identifier='')
	{
		$lft = $type == 'admin' ? 'admin' : 'phpok_app';
		$info  = ';(function($){'."\n";
		$info .= '	$.'.$lft.'_'.$identifier.' = {'."\n\t\t//\n";
		$info .= '	}'."\n";
		$info .= '})(jQuery);'."\n";
		return $info;
	}

	private function _php_head()
	{
		$info = '<?php'."\n";
		return $info;
	}


	private function _get_applist()
	{
		$list = array();
		$this->_system_applist('admin',$list);
		$this->_system_applist('www',$list);
		$this->_system_applist('api',$list);
		$tmplist = $this->lib('file')->ls($this->dir_app);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				if(is_dir($value)){
					$list[] = basename($value);
				}
			}
		}
		if($list){
			$list = array_unique($list);
			return $list;
		}
		return false;
	}

	private function _system_applist($folder='admin',$list)
	{
		$tmplist = $this->lib('file')->ls($this->dir_phpok.$folder);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$tmp = basename($value);
				if(strpos($tmp,'_control.php') === false){
					unset($list[$key]);
					continue;
				}
				$tmp = str_replace("_control.php","",$tmp);
				$list[] = $tmp;
			}
		}
	}
}