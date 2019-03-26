<?php
/**
 * 外掛中心
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年06月26日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class plugin_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("plugin");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 取得外掛列表
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$rslist = $this->model('plugin')->get_all();
		if($rslist){
			foreach($rslist as $key=>$value){
				$extconfig = false;
				if(is_file($this->dir_plugin.$value['id'].'/setting.php')){
					$extconfig = true;
				}
				$value['extconfig'] = $extconfig;
				$rslist[$key] = $value;
			}
		}
		$this->assign("rslist",$rslist);
		$dlist = $this->model('plugin')->dir_list();
		if($dlist){
			$not_install = array();
			foreach($dlist as $key=>$value){
				if(!$rslist[$value] || !$rslist){
					$not_install[$value] = $this->model('plugin')->get_xml($value);
				}
			}
			$this->assign('not_install',$not_install);
		}
		$this->view("plugin_index");
	}

	/**
	 * 配置件外掛資訊
	**/
	public function config_f()
	{
		if(!$this->popedom["config"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定ID'),$this->url('plugin'));
		}
		$this->assign("id",$id);
		$rs = $this->model('plugin')->get_one($id);
		if($rs['param']){
			$rs['param'] = unserialize($rs['param']);
		}
		$this->assign("rs",$rs);
		if(file_exists($this->dir_root.'plugins/'.$id.'/setting.php')){
			include_once($this->dir_root.'plugins/'.$id.'/setting.php');
			$name = 'setting_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if($methods && in_array('index',$methods)){
				$plugin_html = $cls->index();
				$this->assign('plugin_html',$plugin_html);
			}
		}
		$this->view("plugin_config");
	}

	/**
	 * 擴充套件引數配置
	**/
	public function extconfig_f()
	{
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->assign("id",$id);
		$rs = $this->model('plugin')->get_one($id);
		if($rs['param']){
			$rs['param'] = unserialize($rs['param']);
		}
		$this->assign("rs",$rs);
		$plugin_html = '';
		if(file_exists($this->dir_root.'plugins/'.$id.'/setting.php')){
			include_once($this->dir_root.'plugins/'.$id.'/setting.php');
			$name = 'setting_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if($methods && in_array('index',$methods)){
				$plugin_html = $cls->index();
				$this->assign('plugin_html',$plugin_html);
			}
		}
		if(!$plugin_html){
			$this->error(P_Lang('沒有可配置的擴充套件引數'));
		}
		$this->view('plugin_extconfig');
	}

	public function extconfig_save_f()
	{
		if(!$this->popedom["config"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('資料記錄不存在'));
		}
		if(file_exists($this->dir_plugin.$id.'/setting.php')){
			include_once($this->dir_plugin.$id.'/setting.php');
			$name = 'setting_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if($methods && in_array('save',$methods)){
				$cls->save();
			}
		}
		$this->success();
	}

	/**
	 * 儲存配置的外掛資訊
	**/
	public function save_f()
	{
		if(!$this->popedom["config"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定ID'),$this->url('plugin'));
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('資料記錄不存在'),$this->url('plugin'));
		}
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('外掛名稱不能為空'),$this->url('plugin','config','id='.$id));
		}
		$note = $this->get('note');
		$taxis = $this->get("taxis",'int');
		$author = $this->get('author');
		$version = $this->get('version');
		$array = array('title'=>$title,'note'=>$note,'taxis'=>$taxis,'author'=>$author,'version'=>$version);
		$this->model('plugin')->update_plugin($array,$id);
		if(file_exists($this->dir_root.'plugins/'.$id.'/setting.php')){
			include_once($this->dir_root.'plugins/'.$id.'/setting.php');
			$name = 'setting_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if($methods && in_array('save',$methods)){
				$cls->save();
			}
		}
		$this->success(P_Lang('{title}設定成功',array('title'=>' <span class="red">'.$rs['title'].'</span> ')),$this->url("plugin"));
	}

	/**
	 * 外掛排序
	**/
	public function taxis_f()
	{
		if(!$this->popedom["config"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('外掛ID不能為空'));
		}
		$taxis = $this->get('taxis','int');
		$array = array('taxis'=>$taxis);
		$this->model('plugin')->update_plugin($array,$id);
		$this->success();
	}

	/**
	 * 解壓外掛
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
			$this->error(P_Lang('外掛有異常'));
		}
		$info = explode('/',$info['filename']);
		if(!$info[0]){
			$this->error(P_Lang('外掛有異常'));
		}
		if(file_exists($this->dir_root.'plugins/'.$info[0])){
			$this->error(P_Lang('外掛已存在，不允許重複解壓'));
		}
		$this->lib('phpzip')->unzip($this->dir_root.$filename,$this->dir_root.'plugins/');
		$this->success();
	}

	/**
	 * 安裝外掛
	**/
	public function install_f()
	{
		if(!$this->popedom["install"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定ID'),$this->url('plugin'));
		}
		$this->assign("id",$id);
		$rs = $this->model('plugin')->get_xml($id);
		$rs['taxis'] = $this->model('plugin')->get_next_taxis();
		$this->assign("rs",$rs);
		if(file_exists($rs['path'].'install.php')){
			include_once($rs['path'].'install.php');
			$name = 'install_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if($methods && in_array("index",$methods)){
				$info = $cls->index();
				$this->assign("plugin_html",$info);
			}
		}
		$this->view("plugin_install");
	}

	/**
	 * 儲存安裝外掛中的資訊
	**/
	public function install_save_f()
	{
		if(!$this->popedom["install"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定ID'),$this->url('plugin'));
		}
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('外掛名稱不能為空'),$this->url('plugin','config','id='.$id));
		}
		$note = $this->get("note");
		$taxis = $this->get('taxis','int');
		$author = $this->get('author');
		$version = $this->get('version');
		$array = array('id'=>$id,'title'=>$title,'note'=>$note,'status'=>0,'author'=>$author,'taxis'=>$taxis,'version'=>$version);
		$id = $this->model('plugin')->install_save($array);
		if(!$id){
			$this->error(P_Lang('外掛安裝失敗'),$this->url('plugin','install','id='.$id));
		}
		$xmlrs = $this->model('plugin')->get_xml($id);
		if(file_exists($xmlrs['path'].'install.php')){
			include_once($xmlrs['path'].'install.php');
			$name = 'install_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if($methods && in_array('save',$methods)){
				$cls->save();
			}
		}
		$this->success(P_Lang('{title}安裝成功',array('title'=>' <span class="red">'.$title.'</span> ')),$this->url("plugin"));
	}

	/**
	 * 解除安裝外掛
	**/
	public function uninstall_f()
	{
		if(!$this->popedom["install"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('資料記錄不存在'));
		}
		if(file_exists($this->dir_root.'plugins/'.$id.'/uninstall.php')){
			include_once($this->dir_root.'plugins/'.$id.'/uninstall.php');
			$name = 'uninstall_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if(in_array("index",$methods)){
				$cls->index();
			}
		}
		$this->model('plugin')->delete($id);
		$this->success();
	}

	/**
	 * 狀態執行
	**/
	public function status_f()
	{
		if(!$this->popedom["install"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('資料記錄不存在'));
		}
		$status = $rs["status"] ? 0 : 1;
		$this->model('plugin')->update_status($id,$status);
		//執行外掛執行
		if(file_exists($this->dir_root.'plugins/'.$id.'/setting.php')){
			include_once($this->dir_root.'plugins/'.$id.'/setting.php');
			$name = 'setting_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if($methods && in_array('status',$methods)){
				$cls->status();
			}
		}
		$this->success($status);
	}

	/**
	 * 執行自定義函式
	**/
	public function exec_f()
	{
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('資料記錄不存在'));
		}
		if($rs['param']) $rs['param'] = unserialize($rs['param']);
		if(!file_exists($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php')){
			$this->error(P_Lang('外掛檔案{appid}不存在',array('appid'=>' <span class="red">'.$this->app_id.'.php</span> ')));
		}
		include_once($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php');
		$name = $this->app_id.'_'.$id;
		$cls = new $name();
		$methods = get_class_methods($cls);
		$exec = $this->get("exec");
		if(!$exec) $exec = 'index';
		if(!$methods || !in_array($exec,$methods)){
			$this->error(P_Lang('方法{method}不存在',array('method'=>' <span class=red>'.$exec.'</span> ')));
		}
		$cls->$exec($rs);
	}

	/**
	 * 執行自定義函式，即exec的別名
	**/
	public function ajax_f()
	{
		$this->exec_f();
	}

	public function upload_f()
	{
		$array = array("identifier"=>'zipfile',"form_type"=>'upload');
		$array['upload_type'] = 'update';
		$this->lib('form')->cssjs($array);
		$upload = $this->lib('form')->format($array);
		$this->assign('upload_html',$upload);
		$this->view('plugin_upload');
	}

	/**
	 * 匯出外掛
	**/
	public function zip_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('外掛標識不存在'),$this->url('plugin'));
		}
		if(!file_exists($this->dir_root.'plugins/'.$id)){
			$this->error(P_Lang('外掛不存在'),$this->url('plugin'));
		}
		$zipfile = $this->dir_cache.$id.'.zip';
		$this->lib('phpzip')->set_root($this->dir_root.'plugins/');
		$this->lib('phpzip')->zip($this->dir_root.'plugins/'.$id,$zipfile);
		ob_end_clean();
		header("Date: ".gmdate("D, d M Y H:i:s", $this->time)." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s", $this->time)." GMT");
		header("Content-Encoding: none");
		header("Content-Disposition: attachment; filename=".rawurlencode($id.".zip"));
		header("Content-Length: ".filesize($zipfile));
		header("Accept-Ranges: bytes");
		readfile($zipfile);
		flush();
		ob_flush();
	}

	public function icon_f()
	{
		if(!$this->popedom["config"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定外掛ID'));
		}
		$glist = array('menu'=>P_Lang('快捷欄'),'all'=>P_Lang('全域性區'),'content'=>P_Lang('內容區'));
		$this->assign('glist',$glist);
		$vid = $this->get('vid');
		if($vid){
			$rs = $this->model('plugin')->icon_one($id,$vid);
			$this->assign('rs',$rs);
			$this->assign('vid',$vid);
			$iconlist = $this->_get_iconlist($rs['type']);
			$this->assign('iconlist',$iconlist);
		}else{
			//取得下一個排序
			$taxis = $this->model('plugin')->icon_taxis_next($id);
			$this->assign('taxis',$taxis);
		}
		$this->assign('id',$id);
		$elist = $this->model('plugin')->methods($id);
		$this->assign('elist',$elist);
		$this->view("plugin_icon");
	}

	/**
	 * 儲存快捷方式操作
	**/
	public function icon_save_f()
	{
		if(!$this->popedom["config"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定外掛ID'));
		}
		$data = array();
		$vid = $this->get('vid');
		if($vid){
			$data['id'] = $vid;
		}
		$data['title'] = $this->get('title');
		if(!$data['title']){
			$this->error(P_Lang('名稱不能為空'));
		}
		$data['efunc'] = $this->get('efunc');
		if(!$data['efunc']){
			$this->error(P_Lang('未指定要執行的方法'));
		}
		$data['taxis'] = $this->get('taxis','int');
		$data['type'] = $this->get('type');
		if(!$data['type']){
			$this->error(P_Lang('未指定要顯示的位置'));
		}
		$icon = $this->get('icon');
		if(!$icon){
			if($data['type'] == 'menu'){
				$icon = 'newtab';
			}else{
				$icon = 'plugin.png';
			}
		}
		$data['icon'] = $icon;
		//檢測是否已存在記錄
		$record = false;
		$rslist = $this->model('plugin')->iconlist($id);
		if($rslist){
			foreach($rslist as $key=>$value){
				if($value['efunc'] == $data['efunc'] && $data['type'] == $value['type'] && $value['id'] != $data['id']){
					$record = true;
					break;
				}
			}
			if($record){
				$this->error(P_Lang('資料已存在記錄，請重新設定一個'));
			}
		}
		$this->model('plugin')->icon_save($data,$id);
		$this->success();
	}

	/**
	 * 取得圖示列表
	**/
	public function iconlist_f()
	{
		if(!$this->popedom["config"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$type = $this->get('type');
		$iconlist = $this->_get_iconlist($type);
		$this->success($iconlist);
	}

	/**
	 * 外掛管理項
	**/
	public function setting_f()
	{
		if(!$this->popedom["config"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定外掛ID'));
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('沒有相關外掛資訊'),$this->url('plugin'));
		}
		$this->assign('id',$id);
		$this->assign('rs',$rs);
		$glist = array('menu'=>P_Lang('快捷欄'),'all'=>P_Lang('全域性區'),'content'=>P_Lang('內容區'));
		$this->assign('glist',$glist);
		$elist = $this->model('plugin')->methods($id);
		$rslist = $this->model('plugin')->iconlist($id);
		if($rslist){
			foreach($rslist as $key=>$value){
				$value['position'] = $glist[$value['type']];
				$value['efunc_title'] = $elist[$value['efunc']]['title'];
				$rslist[$key] = $value;
			}
			$this->assign('rslist',$rslist);
		}
		$this->view("plugin_setting");
	}

	public function icon_delete_f()
	{
		if(!$this->popedom["config"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定外掛ID'));
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('沒有相關外掛資訊'));
		}
		$vid = $this->get('vid');
		if(!$vid){
			$this->error(P_Lang('未指定外掛快捷鍵ID'));
		}
		$this->model('plugin')->icon_delete($id,$vid);
		$this->success();
	}

	/**
	 * 建立外掛
	**/
	public function create_f()
	{
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('外掛名稱不能為空'));
		}
		$id = $this->get('id','system');
		if($id){
			if(strpos($id,'_') !== false){
				$this->error(P_Lang('外掛標識不支援下劃線'));
			}
			$id = strtolower($id);
		}else{
			$id = md5($title.'-phpok.com-'.uniqid(rand(), true));
		}
		//檢測外掛資料夾是否存在
		if(file_exists($this->dir_root.'plugins/'.$id)){
			$this->error(P_Lang('外掛標識已被使用，請重新設定'));
		}		
		$note = $this->get('note');
		$author = $this->get('author');
		if(!$author){
			$author = 'phpok.com';
		}
		if(!$note){
			$note = P_Lang('自定義外掛');
		}
		//建立XML檔案
		$content = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$content.= '<root>'."\n\t";
		$content.= '<title>'.$title.'</title>'."\n\t";
		$content.= '<desc>'.$note.'</desc>'."\n\t";
		$content.= '<author>'.$author.'</author>'."\n\t";
		$content.= '<version>1.0</version>'."\n";
		$content.= '</root>';
		$this->lib('file')->vim($content,$this->dir_root.'plugins/'.$id.'/config.xml');
		$this->lib('file')->vim('',$this->dir_root.'plugins/'.$id.'/template/setting.html');
		$array = array('www','api','admin','install','uninstall','setting');
		foreach($array as $key=>$value){
			$content = '<?php'."\n".$this->php_note_title($id,$value,$title,$author)."\n".$this->php_demo($id,$value);
			$this->lib('file')->vim($content,$this->dir_root.'plugins/'.$id.'/'.$value.'.php');
		}
		$this->success();
	}

	private function _get_iconlist($type)
	{
		if($type && $type == 'menu'){
			$css = $this->lib("file")->cat($this->dir_root.'css/icomoon.css');
			preg_match_all("/\.icon-([a-z\-0-9]*):before\s*(\{|,)/isU",$css,$iconlist);
			$iconlist = $iconlist[1];
			sort($iconlist);
		}else{
			$iconlist = $this->lib('file')->ls('images/ico/');
			if($iconlist){
				foreach($iconlist as $key=>$value){
					$iconlist[$key] = basename($value);
				}
			}
		}
		return $iconlist;
	}

	private function php_note_title($id,$fileid,$title='',$author='')
	{
		$note = '';
		switch($fileid) {
			case "admin":
				$note = P_Lang('後臺應用');
				break;
			case 'www':
			    $note = P_Lang('前臺應用');
				break;
			case 'api':
			    $note = P_Lang('介面應用');
				break;
			case 'install':
			    $note = P_Lang('外掛安裝');
				break;
			case 'uninstall':
			    $note = P_Lang('外掛解除安裝');
				break;
			case 'setting':
			    $note = P_Lang('外掛配置');
				break;
			default:
				$note = P_Lang('未知');
		}
		$string = "/**\n";
		$string.= " * ".$title.($note ? '<'.$note.'>': '')."\n";
		$string.= " * @package phpok\\\plugins\n";
		if($author){
			$string.= " * @作者 ".$author."\n";
		}
		$string.= " * @版本 ".$this->version."\n";
		$string.= " * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License\n";
		$string.= " * @時間 ".date("Y年m月d日 H時i分",$this->time)."\n";
		$string.= "**/";
		return $string;
	}

	/**
	 * 
	 * @引數 
	 * @返回 
	 * @更新時間 
	**/

	private function php_demo($id,$fileid)
	{
		$string = 'class '.$fileid.'_'.$id.' extends phpok_plugin'."\n";
		$string.= '{'."\n\t";
		$string.= 'public $me;'."\n\t";
		$string.= 'public function __construct()'."\n\t";
		$string.= '{'."\n\t\t";
		$string.= 'parent::plugin();'."\n\t\t";
		$string.= '$this->me = $this->_info();'."\n\t";
		$string.= '}'."\n\t";
		$string.= "\n\t";
		//初始化全域性應用
		if($fileid == 'www' || $fileid == 'admin' || $fileid == 'api'){
			$string .= '/**'."\n\t";
			$string .= ' * 全域性執行外掛，在執行當前方法執行前，調整引數，如果不使用，請刪除這個方法'."\n\t";
			$string .= '**/'."\n\t";
			$string .= 'public function phpok_before()'."\n\t";
			$string .= '{'."\n\t\t";
			$string .= '//PHP程式碼;'."\n\t";
			$string .= '}'."\n\t";
			$string .= "\n\t";
			$string .= '/**'."\n\t";
			$string .= ' * 全域性執行外掛，在執行當前方法執行後，資料未輸出前，如果不使用，請刪除這個方法'."\n\t";
			$string .= '**/'."\n\t";
			$string .= 'public function phpok_after()'."\n\t";
			$string .= '{'."\n\t\t";
			$string .= '//PHP程式碼;'."\n\t";
			$string .= '}'."\n\t";
			$string .= "\n\t";
			if($fileid == 'www' || $fileid == 'admin'){
				$string .= '/**'."\n\t";
				$string .= ' * 系統內建在</head>節點前輸出HTML內容，如果不使用，請刪除這個方法'."\n\t";
				$string .= '**/'."\n\t";
				$string .= 'public function html_phpokhead()'."\n\t";
				$string .= '{'."\n\t\t";
				$string .= '//$this->_show("phpokhead.html");'."\n\t";
				$string .= '}'."\n\t";
				$string.= "\n\t";
				$string .= '/**'."\n\t";
				$string .= ' * 系統內建在</body>節點前輸出HTML內容，如果不使用，請刪除這個方法'."\n\t";
				$string .= '**/'."\n\t";
				$string .= 'public function html_phpokbody()'."\n\t";
				$string .= '{'."\n\t\t";
				$string .= '//$this->_show("phpokbody.html");'."\n\t";
				$string .= '}'."\n\t";
				$string.= "\n\t";
			}
		}
		if($fileid == 'admin'){
			$string .= '/**'."\n\t";
			$string .= ' * 更新或新增儲存完主題後觸發動作，如果不使用，請刪除這個方法'."\n\t";
			$string .= ' * @引數 $id 主題ID'."\n\t";
			$string .= ' * @引數 $project 專案資訊，陣列'."\n\t";
			$string .= ' * @返回 true '."\n\t";
			$string .= '**/'."\n\t";
			$string .= 'public function system_admin_title_success($id,$project)'."\n\t";
			$string .= '{'."\n\t\t";
			$string .= '//PHP程式碼;'."\n\t";
			$string .= '}'."\n\t";
			$string.= "\n\t";
		}
		if($fileid == 'www'){
			$string .= '/**'."\n\t";
			$string .= ' * 針對不同專案，配置不同的主題查詢條件，如果不使用，請刪除這個方法'."\n\t";
			$string .= ' * @引數 $project 專案資訊，陣列'."\n\t";
			$string .= ' * @引數 $module 模組資訊，陣列'."\n\t";
			$string .= ' * @返回 $dt陣列或false '."\n\t";
			$string .= '**/'."\n\t";
			$string .= 'public function system_www_arclist($project,$module)'."\n\t";
			$string .= '{'."\n\t\t";
			$string .= '//$dt = array();'."\n\t\t";
			$string .= '//$dt["fields"] = "id,thumb";'."\n\t\t";
			$string .= '//$this->assign("dt",$dt);'."\n\t";
			$string .= '}'."\n\t";
			$string.= "\n\t";
		}
		if($fileid == 'install'){
			$string .= '/**'."\n\t";
			$string .= ' * 外掛安裝時，增加的擴充套件表單輸出項，如果不使用，請刪除這個方法'."\n\t";
			$string .= '**/'."\n\t";
			$string .= 'public function index()'."\n\t";
			$string.= '{'."\n\t\t";
			$string.= '//return $this->_tpl(\'setting.html\');'."\n\t";
			$string.= '}'."\n\t";
			$string.= "\n\t";
			$string .= '/**'."\n\t";
			$string .= ' * 外掛安裝時，儲存擴充套件引數，如果不使用，請刪除這個方法'."\n\t";
			$string .= '**/'."\n\t";
			$string .= 'public function save()'."\n\t";
			$string.= '{'."\n\t\t";
			$string.= '$id = $this->_id();'."\n\t\t";
			$string.= '$ext = array();'."\n\t\t";
			$string.= '//$ext[\'擴充套件引數欄位名\'] = $this->get(\'表單欄位名\');'."\n\t\t";
			$string.= '$this->_save($ext,$id);'."\n\t";
			$string.= '}'."\n\t";
			$string.= "\n\t";
		}
		if($fileid == 'uninstall'){
			$string .= '/**'."\n\t";
			$string .= ' * 外掛解除安裝時，執行的方法，如刪除表，或去除其他一些選項，如果不使用，請刪除這個方法'."\n\t";
			$string .= '**/'."\n\t";
			$string .= 'public function index()'."\n\t";
			$string.= '{'."\n\t\t";
			$string.= '//執行一些自定義的動作'."\n\t";
			$string.= '}'."\n\t";
			$string.= "\n\t";
		}
		if($fileid == 'setting'){
			$string .= '/**'."\n\t";
			$string .= ' * 外掛配置引數時，增加的擴充套件表單輸出項，如果不使用，請刪除這個方法'."\n\t";
			$string .= '**/'."\n\t";
			$string .= 'public function index()'."\n\t";
			$string.= '{'."\n\t\t";
			$string.= '//return $this->_tpl(\'setting.html\');'."\n\t";
			$string.= '}'."\n\t";
			$string.= "\n\t";
			$string .= '/**'."\n\t";
			$string .= ' * 外掛配置引數時，儲存擴充套件引數，如果不使用，請刪除這個方法'."\n\t";
			$string .= '**/'."\n\t";
			$string .= 'public function save()'."\n\t";
			$string.= '{'."\n\t\t";
			$string.= '//$id = $this->_id();'."\n\t\t";
			$string.= '//$ext = array();'."\n\t\t";
			$string.= '//$ext[\'擴充套件引數欄位名\'] = $this->get(\'表單欄位名\');'."\n\t\t";
			$string.= '//$this->_save($ext,$id);'."\n\t";
			$string.= '}'."\n\t";
			$string.= "\n\t";
			$string .= '/**'."\n\t";
			$string .= ' * 外掛執行稽核動作時，執行的操作，如果不使用，請刪除這個方法'."\n\t";
			$string .= '**/'."\n\t";
			$string .= 'public function status()'."\n\t";
			$string.= '{'."\n\t\t";
			$string.= '//執行一些自定義的動作'."\n\t";
			$string.= '}'."\n\t";
			$string.= "\n\t";
		}
		$string.= "\n";
		$string.= '}';
		return $string;
	}
}