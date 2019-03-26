<?php
/**
 * PHPOK框架入口引挈檔案，請不要改動此檔案
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年07月21日
**/

/**
 * 安全限制
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

/**
 * 強制使用UTF-8編碼
**/
header("Content-type: text/html; charset=utf-8");
header("Cache-control: no-cache,no-store,must-revalidate");
header("Pramga: no-cache"); 
header("Expires: -1");
header("X-Frame-Options: sameorigin");
//setcookie("phpokcom", "test", null, null, null, null, true);

/**
 * 計算執行的時間
 * @引數 $is_end 布林值
 * @返回 引數為true時返回執行的時間，為false定義常量 SYS_TIME_START 為當前時間
**/
function run_time($is_end=false)
{
	if(!$is_end){
		if(defined("SYS_TIME_START")){
			return false;
		}
		define("SYS_TIME_START",microtime(true));
	}else{
		if(!defined("SYS_TIME_START")){
			return false;
		}
		return round((microtime(true) - SYS_TIME_START),5);
	}
}

/**
 * 登記記憶體
 * @引數 $is_end 布林值
 * @返回 引數為true時返回使用的記憶體值，為false定義常量 SYS_MEMORY_START 為當前記憶體值
**/
function run_memory($is_end=false)
{
	if(!$is_end){
		if(defined("SYS_MEMORY_START") || !function_exists("memory_get_usage")){
			return false;
		}
		define("SYS_MEMORY_START",memory_get_usage());
	}else{
		if(!defined("SYS_MEMORY_START")){
			return false;
		}
		$memory = memory_get_usage() - SYS_MEMORY_START;
		//格式化大小
		if($memory <= 1024){
			$memory = "1KB";
		}elseif($memory>1024 && $memory<(1024*1024)){
			$memory = round(($memory/1024),2)."KB";
		}else{
			$memory = round(($memory/(1024*1024)),2)."MB";
		}
		return $memory;
	}
}

run_time();
run_memory();

/**
 * 用於除錯統計時間，無引數，啟用資料庫除錯的結果會在這裡輸出，需要在模板適當位置寫上：{func debug_time} 
**/
function debug_time()
{
	global $app;
	$time = run_time(true);
	$memory = run_memory(true);
	$sql_db_count = $app->db->sql_count();
	$sql_db_time = $app->db->sql_time();
	$cache_count = $app->cache->count();
	$cache_time = $app->cache->time();
	$string = '執行 {total} 秒，記憶體使用 {mem_total}，資料庫執行 {sql_count} 次，';
	$string.= '用時 {sql_time} 秒，快取執行 {cache_count} 次，用時 {cache_time} 秒';
	$array = array('total'=>$time,'mem_total'=>$memory);
	$array['sql_count']= $app->db->sql_count();
	$array['sql_time'] = $app->db->sql_time();
	$array['cache_count'] = $app->cache->count();
	$array['cache_time'] = $app->cache->time();
	$string = P_Lang($string,$array);
	return $string;
}

/**
 * PHPOK4最新框架，一般不直接呼叫此框架
 * @更新時間 2016年06月05日
**/
class _init_phpok
{
	/**
	 * 指定app_id，該id是通過入口的**APP_ID**來獲取，留空使用www
	**/
	public $app_id = "www";

	/**
	 * 控制器及方法
	**/
	public $ctrl = 'index';
	public $func = 'index';

	/**
	 * 定義網站程式根目錄，對應入口的**ROOT**，為空使用./
	**/
	public $dir_root = "./";

	/**
	 * 框架目錄，對應入口的**FRAMEWORK**，為空使用phpok/
	**/
	public $dir_phpok = "phpok/";

	public $dir_data = './_data/';
	public $dir_cache = './_cache/';
	public $dir_config = './_config/';
	public $dir_extension = './_extension/';
	public $dir_plugin = './_plugins/';
	public $dir_app = './_app/';

	/**
	 * 定義引挈，在P4中，將MySQL，Cache，Session設為三個引挈（後續版本可能會改動）
	**/
	public $engine;

	/**
	 * 配置資訊，對應framework/config/目錄下的內容及根目錄的config.php裡的資訊
	**/
	public $config;

	/**
	 * 定義版本，該引數會被常量VERSION改變，如使用了線上升級，會被update.xml裡改變，即
	 * 優先順序是：update.xml > version.php > 自身
	**/
	public $version = "4.0";

	/**
	 * 當前時間，該時間是經常config裡的兩個引數timezone和timetuning調整過的，適用於虛擬主機使用者無法較正伺服器時間用的
	**/
	public $time;

	/**
	 * 當前網址，由系統生成，在模板中直接使用{$sys.url}輸出
	**/
	public $url;
	
	/**
	 * 授權型別，對應license.php裡的常量LICENSE
	**/
	public $license = "LGPL";

	/**
	 * 授權碼，16位或32位的授權碼，要求全部大寫，對應license.php裡的常量LICENSE_CODE
	**/
	public $license_code = "";

	/**
	 * 授權時間，對應license.php裡的常量LICENSE_DATE
	**/
	public $license_date = "";

	/**
	 * 授權者稱呼，企業授權填寫公司名稱，個人授權填寫姓名，對應license.php裡的常量LICENSE_NAME
	**/
	public $license_name = "phpok";

	/**
	 * 授權的域名，注意必須以.開始，僅支援國際域名，二級域名享有國際域名授權，對應license.php裡的常量LICENSE_SITE
	**/
	public $license_site = "phpok.com";

	/**
	 * 顯示開發者資訊，即Powered by資訊，對應license.php裡的常量LICENSE_POWERED
	**/
	public $license_powered = true;

	/**
	 * 是否是手機端，如果使用手機端可能會改寫網址，此項受config配置裡的mobile相關引數影響
	**/
	public $is_mobile = false;

	/**
	 * 定義外掛
	**/
	public $plugin;

	/**
	 * 通過framework/form/裡實現自定義擴充套件動態呼叫CSS樣式，後續版本將拋棄此功能
	**/
	public $csslist;

	/**
	 * 通過framework/form/裡實現自定義擴充套件動態呼叫js檔案，後續版本將拋棄此功能
	**/
	public $jslist;

	/**
	 * 語言包，預設使用gettext方法，系統不支援將使用第三方擴充套件讀取pomo檔案
	**/
	public $lang;

	/**
	 * 語言ID，暫時生成的網址不支援帶語言引數
	**/
	public $langid;

	/**
	 * 語言讀取言式，通過系統檢測，支援gettext和user兩種
	**/
	private $language_status = 'user';

	/**
	 * 閘道器路由介面，對應資料夾gateway裡的PHP執行
	**/
	public $gateway;

	/**
	 * 用於api.php介面接入傳遞token引數，此項功能還不成熟，請慎用
	**/
	public $token;

	/**
	 * 資料傳輸是否使用Ajax
	**/
	public $is_ajax = false;

	private $_libs = array();

	private $_dataParams = array();

	/**
	 * 建構函式，用於初化一些資料
	**/
	public function __construct()
	{
		if(version_compare(PHP_VERSION, '5.3.0', '<') && function_exists('set_magic_quotes_runtime')){
			ini_set("magic_quotes_runtime",0);
		}
		$this->init_constant();
		$this->init_config();
		$this->init_engine();
	}

	/**
	 * 變數引數核心處理
	 * @引數 $id 變數名
	 * @引數 $val 變數值
	 * @引數 $type 變數型別，system 系統變數，config 配置變數，site 站點變數
	**/
	final public function config($id,$val='',$type='system')
	{
		if($id == 'debug' && is_bool($val)){
			if($val){
				if(function_exists('opcache_reset')){
					ini_set('opcache.enable',false);
				}
				ini_set('display_errors','on');
				error_reporting(E_ALL ^ E_NOTICE);
			}else{
				error_reporting(0);
				if(isset($this->config) && isset($this->config['opcache']) && function_exists('opcache_reset')){
					ini_set('opcache.enable',$this->config['opcache']);
				}
			}
			return true;
		}
		if($type == 'system'){
			$this->$id = $val;
		}
		if($type == 'config'){
			$this->config[$id] = $val;
		}
		if($type == 'site'){
			$this->site[$id] = $val;
		}
		return true;
	}

	final public function data($var,$val='')
	{
		if($val == ''){
			if(strpos($var,'.') === false){
				return $this->_dataParams[$var];
			}
			$list = explode(".",$var);
			if(!isset($this->_dataParams[$list[0]]) || !is_array($this->_dataParams[$list[0]])){
				return false;
			}
			$tmp = $this->_dataParams[$list[0]];
			foreach($list as $key=>$value){
				if($key<1){
					continue;
				}
				if(!isset($tmp[$value])){
					$tmp = false;
					break;
				}else{
					$tmp = $tmp[$value];
				}
			}
			return $tmp;
		}
		if(strpos($var,'.') !== true){
			$this->_dataParams[$var] = $val;
			return true;
		}
		$list = explode(".",$var);
		krsort($list);
		$tmp = array();
		$total = count($list);
		$i=0;
		foreach($list as $key=>$value){
			if($i<1){
				$tmp[$value] = $val;
			}else{
				if(($i+1) == $total){
					if(isset($this->_dataParams[$value])){
						$this->_dataParams[$value] = array_merge($this->_dataParams[$value],$tmp);
					}else{
						$this->_dataParams[$value] = $tmp;
					}
				}else{
					$ok = array();
					$ok[$value] = $tmp;
					$tmp = $ok;
				}
			}
			$i++;
		}
		return true;
	}

	final public function undata($var)
	{
		if(!$var){
			return false;
		}
		if(strpos($var,'.') === false){
			unset($this->_dataParams[$var]);
			return true;
		}
		$list = explode(".",$var);
		$total = count($list);
		$list = explode(".",$var);
		krsort($list);
		$i=0;
		foreach($list as $key=>$value){
			if($i<1){
				$tmp = array();
			}else{
				if(($i+1) == $total){
					$this->_dataParams[$value] = $tmp;
				}else{
					$ok = array();
					$ok[$value] = $tmp;
					$tmp = $ok;
				}
			}
			$i++;
		}
		return true;
	}

	/**
	 * 初始化網址要輸出的一些全域性資訊，如網站資訊，初始化後的SEO資訊
	**/
	private function init_assign()
	{
		$url = $this->url;
		$afile = $this->config[$this->app_id.'_file'];
		if(!$afile){
			$afile = 'index.php';
		}
		$url .= $afile;
		if($this->lib('server')->query()){
			$url .= "?".$this->lib('server')->query();
		}
		$this->site["url"] = $url;
		$this->config["url"] = $this->url;
		$this->config['app_id'] = $this->app_id;
		$this->config['time'] = $this->time;
		$this->config['webroot'] = $this->dir_webroot;	
		$this->assign("sys",$this->config);
		$this->phpok_seo($this->site);
		$this->assign("config",$this->site);
		$langid = $this->get("_langid");
		if($this->app_id == 'admin'){
			if(!$langid){
				$langid = (isset($_SESSION['admin_lang_id']) && $_SESSION['admin_lang_id']) ? $_SESSION['admin_lang_id'] : 'default';
			}
			$_SESSION['admin_lang_id'] = $langid;
		}else{
			if(!$langid){
				$langid = isset($this->site['lang']) ? $this->site['lang'] : 'default';
			}
		}
		$this->langid = $langid;
		
		if($multiple_language){
			$this->language($this->langid);
		}
	}

	/**
	 * 載入語言包
	 * @引數 $langid 字串，留空載入default，中文不需要載入語言包
	 * @更新時間 2016年06月05日
	**/
	public function language($langid='default')
	{
		$multiple_language = isset($this->config['multiple_language']) ? $this->config['multiple_language'] : false;
		if($multiple_language){
			include_once($this->dir_phpok.'language.php');
			$this->language = new phpok_language($langid);
			$this->language->status($multiple_language);
			$this->language->folder($this->dir_root.'langs');
			$this->language->id($this->app_id);
			$this->language->pomo($this->dir_extension);
			unset($multiple_language);
			return true;
		}
		return false;
	}

	/**
	 * 語言包變數格式化，$info將轉化成系統的語言包，同是將$info裡的帶{變數}替換成$var裡傳過來的資訊
	 * @引數 $info 字串，要替變的字串用**{}**包圍，包圍的內容對應$var裡的$key
	 * @引數 $var 陣列，要替換的字元。
	 * @返回 字串，$info為空返回false
	 * @更新時間 2016年06月05日
	**/
	final public function lang_format($info,$var='')
	{
		if(!$this->language){
			$this->language($this->langid);
		}
		if($this->language){
			return $this->language->format($info,$var);
		}
		return $this->_lang_format($info,$var);
	}

	private function _lang_format($info,$var='')
	{
		if($var && is_string($var)){
			$var  = unserialize($var);
		}
		if($var && is_array($var)){
			foreach($var as $key=>$value){
				$info = str_replace(array('{'.$key.'}','['.$key.']'),$value,$info);
			}
		}
		return $info;
	}

	/**
	 * 載入檢視引挈，後臺載入framework/view/下的模板檔案，css，js，images路徑不會修改。前端載入tpl/下的模板檔案
	**/
	public function init_view()
	{
		include_once($this->dir_phpok."phpok_tpl.php");
		$this->model('url')->ctrl_id($this->config['ctrl_id']);
		$this->model('url')->func_id($this->config['func_id']);
		if($this->app_id == "admin"){
			$tpl_rs = array();
			$tpl_rs["id"] = "1";
			$tpl_rs["dir_tpl"] = substr($this->dir_phpok,strlen($this->dir_root))."/view/";
			$tpl_rs["dir_cache"] = $this->dir_data."tpl_admin/";
			$tpl_rs["dir_php"] = $this->dir_root;
			$tpl_rs["dir_root"] = $this->dir_root;
			$tpl_rs["refresh_auto"] = true;
			$tpl_rs["tpl_ext"] = "html";
			//定製語言模板ID
			$tpl_rs['langid'] = 'default';
			if($this->session->val('admin_lang_id')){
				$tpl_rs['langid'] = $this->session->val('admin_lang_id');
			}
			$this->tpl = new phpok_template($tpl_rs);
		}else{
			if($this->app_id == 'www'){
				if(!$this->site["tpl_id"] || ($this->site["tpl_id"] && !is_array($this->site["tpl_id"]))){
					$this->_error("未指定模板檔案");
				}
			}
			$this->model('site')->site_id($this->site['id']);
			$this->model('url')->base_url($this->url);
			$this->model('url')->set_type($this->site['url_type']);
			$this->model('url')->protected_ctrl($this->model('site')->reserved());
			//初始化偽靜態中需要的東西
			if($this->site['url_type'] == 'rewrite'){
				$this->model('url')->site_id($this->site['id']);
				$this->model('rewrite')->site_id($this->site['id']);
				$this->model('url')->rules($this->model('rewrite')->get_all());
				$this->model('url')->page_id($this->config['pageid']);
			}
			$this->tpl = new phpok_template($this->site["tpl_id"]);
			include($this->dir_phpok."phpok_call.php");
			$this->call = new phpok_call();
		}
		include_once($this->dir_phpok."phpok_tpl_helper.php");
		if($this->app_id == 'www' && !$this->site['status']){
			$close = $this->site['content'] ? $this->site['content'] : P_Lang('網站暫停關閉');
			$this->_tip($close,2);
		}
	}

	/**
	 * 手機判斷，使用了第三方擴充套件extension裡的mobile類
	**/
	public function is_mobile()
	{
		if($this->lib('mobile')->is_mobile()){
			return true;
		}
		return false;
	}

	/**
	 * 初始化載入站點資訊，後臺僅載入站點資訊，返回true，前端會執行域名判斷，手機判斷，及模板載入
	**/
	public function init_site()
	{
		$site_id = $this->get("siteId","int");
		$this->url = $this->root_url($site_id);
		if($this->app_id == "admin"){
			if($this->session->val('admin_site_id')){
				$site_rs = $this->model('site')->get_one($_SESSION['admin_site_id']);
			}else{
				$site_rs = $this->model("site")->get_one_default();
			}
			if(!$site_rs){
				$site_rs = array('title'=>'PHPOK.Com');
			}
			$this->site = $site_rs;
			return true;
		}
		$domain = $this->lib('server')->domain($this->config['get_domain_method']);
		if(!$domain){
			$this->_error('無法獲取網站域名資訊，請檢查環境是否支援$_SERVER["SERVER_NAME"]或$_SERVER["HTTP_HOST"]');
		}
		$site_rs = $this->model('site')->site_info(($site_id ? $site_id : $domain));
		if(!$site_rs && $this->app_id == 'www'){
			$this->_error('網站資訊不存在或未啟用');
		}
		if(!$site_rs['is_default']){
			$site_default = $this->model('site')->get_one_default();
			$tmplist = array();
			if($site_default && $site_default['_domain']){
				foreach($site_default['_domain'] as $key=>$value){
					$tmplist[] = $value['domain'];
				}
			}
			if(in_array($domain,$tmplist) && !defined( 'PHPOK_SITE_ID' )){
				define("PHPOK_SITE_ID",$site_rs['id']);
			}
		}
		$url_type = $this->is_https() ? 'https://' : 'http://';
		if($this->app_id == 'www'){
			if($this->config['mobile']['status']){
				$this->is_mobile = $this->config['mobile']['default'];
				if(!$this->is_mobile && $this->config['mobile']['autocheck']){
					$this->is_mobile = $this->is_mobile();
				}
			}
			if($site_rs['_mobile']){
				if($site_rs['_mobile']['domain'] == $domain){
					$this->url = $url_type.$site_rs['_mobile']['domain'].$site_rs['dir'];
					$this->is_mobile = true;
				}else{
					if($this->is_mobile){
						$url = $url_type.$site_rs['_mobile']['domain'].$site_rs['dir'];
						if(substr($url,-1) != '/'){
							$url .= '/';
						}
						$url .= $this->config['www_file'];
						$this->_location($url);
						exit;
					}
				}
			}
			if($site_id && is_numeric($site_id) && $site_rs['domain'] && $site_rs['domain'] != $domain){
				$url = $url_type.$site_rs['domain'].$site_rs['dir'];
				if(substr($url,-1) != '/'){
					$url .= '/';
				}
				$url .= $this->config['www_file'];
				$this->_location($url);
				exit;
			}
		}
		$tplid = $site_rs['tpl_id'];
		if($this->session->val('tpl_id')){
			$tplid = $this->session->val('tpl_id');
		}
		if($this->get('_tpl','int')){
			$tplid = $this->get('_tpl','int');
			$this->session->assign('tpl_id',$tplid);
		}
		$rs = $this->model('tpl')->get_one($tplid);
		if(!$rs){
			$rs = $this->model('tpl')->get_one($site_rs['tpl_id']);
			if(!$rs){
				$this->site = $site_rs;
				return true;
			}
		}
		if($site_rs && $rs){
			$tpl_rs = array('id'=>$rs['id'],'dir_root'=>$this->dir_root);
			$tpl_rs['dir_tplroot'] = 'tpl/';
			$tpl_rs["dir_tpl"] = $rs["folder"] ? "tpl/".$rs["folder"]."/" : "tpl/www/";
			if($this->dir_webroot && $this->dir_webroot != '.' && $this->dir_webroot != './'){
				$tmp = $this->dir_webroot;
				if(substr($tmp,-1) != '/'){
					$tmp .= '/';
				}
				$tpl_rs["dir_tpl"] = $tmp.$tpl_rs["dir_tpl"];
			}
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
			$langid = $site_rs['lang'] ? $site_rs['lang'] : 'default';
			if($this->session->val($this->app_id.'_lang_id')){
				$langid = $this->session->val($this->app_id.'_lang_id');
			}
			if($this->get('_langid')){
				$langid = $this->get('_langid');
				$this->session->assign($this->app_id.'_lang_id',$langid);
			}
			$tpl_rs['langid'] = $langid;
			$site_rs["tpl_id"] = $tpl_rs;
		}
		$this->site = $site_rs;
	}

	/**
	 * 判斷是否啟用https
	**/
	protected function is_https()
	{
		if($this->config['force_https']){
			return true;
		}
		return $this->lib('server')->https();
	}

	/**
	 * 裝載外掛，程式在初始化時就執行外掛載入，一次性載入但未執行，
	 * 如果外掛編寫有問題，會直接無法執行。因此載入外掛時請仔細檢查。
	**/
	public function init_plugin()
	{
		$rslist = $this->model('plugin')->get_all(1);
		if(!$rslist){
			return false;
		}
		$param = array();
		foreach($rslist as $key=>$value){
			if($value['param']){
				$value['param'] = unserialize($value['param']);
			}
			if(file_exists($this->dir_root.'plugins/'.$key.'/'.$this->app_id.'.php')){
				include_once($this->dir_root.'plugins/'.$key.'/'.$this->app_id.'.php');
				$name = $this->app_id."_".$key;
				$cls = new $name();
				$mlist = get_class_methods($cls);
				$this->plugin[$key] = array("method"=>$mlist,"obj"=>$cls,'id'=>$key);
				$param[$key] = $value;
			}
		}
		$this->assign('plugin',$param);
	}

	/**
	 * 動態引態第三方類包，官方提供的類包在framework/libs/下，使用者自行編寫的class放在extension目錄下。
	 * 請注意，extension支援下的類支援config.inc.php配置自動執行
	 * config.inc.php支援的引數有：
	 * 		1. auto，自動執行的方法
	 *		2. include，包含這個類下需要呼叫的其他php檔案，多個檔案用英文逗號隔開，僅支援相對路徑
	 * @引數 $class，類的名稱，第三方對應的是資料夾名稱，要求全部小寫
	**/
	public function lib($class='')
	{
		if(!$class){
			return false;
		}
		if(isset($this->_libs) && $this->_libs && isset($this->_libs[$class]) && $this->_libs[$class]){
			$config = $this->_libs[$class];
		}else{
			$config = array('param'=>'','include'=>'','auto'=>'','classname'=>$class.'_lib');
			if(file_exists($this->dir_root.'extension/'.$class.'/config.inc.php')){
				include($this->dir_root.'extension/'.$class.'/config.inc.php');
				$list = $config['include'] ? explode(",",$config['include']) : array();
				foreach($list as $key=>$value){
					if(substr(strtolower($value),-4) != '.php'){
						$value .= '.php';
					}
					if(file_exists($this->dir_root.'extension/'.$class.'/'.$value)){
						include_once($this->dir_root.'extension/'.$class.'/'.$value);
					}
				}
			}
			$this->_libs[$class] = $config;
		}
		$tmp = isset($config['classname']) ? $config['classname'] : $class.'_lib';
		if(isset($this->$tmp) && is_object($this->$tmp)){
			return $this->$tmp;
		}
		$vfile = array($this->dir_phpok.'libs/'.$class.'.php');
		$vfile[] = $this->dir_phpok.'libs/'.$class.'.phar';
		$vfile[] = $this->dir_root.'extension/'.$class.'.phar';
		$vfile[] = $this->dir_root.'extension/'.$class.'/phpok.php';
		$vfile[] = $this->dir_root.'extension/'.$class.'/index.php';
		$vfile[] = $this->dir_root.'extension/'.$class.'.php';
		$chkstatus = false;
		foreach($vfile as $key=>$value){
			if(file_exists($value)){
				include_once($value);
				$chkstatus = true;
				break;
			}
		}
		if(!$chkstatus){
			$this->error(P_Lang('類檔案{classfile}不存在',array('classfile'=>$class.'.php')));
		}
		$this->$tmp = new $tmp($config['param']);
		if($config['auto']){
			$list = explode(",",$config['auto']);
			foreach($list as $key=>$value){
				$this->$tmp->$value();
			}
		}
		return $this->$tmp;
	}

	/**
	 * 按需載入 Control 類檔案，以實現control裡資料交叉處理
	 * @引數 $name 字串，方法名稱
	 * @引數 $appid 字串，指定APP_ID，不指定使用內建
	**/
	public function control($name,$appid='')
	{
		if($appid && !in_array($appid,array('www','api','admin'))){
			$appid = $this->app_id;
		}
		if(!$appid){
			$appid = $this->app_id;
		}
		$class_name = $appid.'_'.$name.'_control';
		if($this->$class_name && is_object($this->$class_name)){
			return $this->$class_name;
		}
		if(is_file($this->dir_app.$name.'/'.$appid.'.control.php')){
			return $this->_ctrl_phpok5($name,$appid);
		}
		$file = $this->dir_phpok.'/'.$appid.'/'.$name.'_control.php';
		if(!is_file($file)){
			return false;
		}
		include_once($file);
		$class_name2 = $name.'_control';
		$this->$class_name = new $class_name2();
		return $this->$class_name;
	}

	public function ctrl($name,$appid='')
	{
		return $this->control($name,$appid);
	}

	/**
	 * 按需載入Model資訊，所有的檔案均放在framework/model/目錄下。會根據**app_id**自動載入同名但不同入口的檔案
	 * @引數 $name，字串
	 * @返回 例項化後的類，出錯則中止執行報錯
	 * @更新時間 2016年06月05日
	**/
	public function model($name)
	{
		$class_name = $name."_model";
		$class_base = $name."_model_base";
		//擴充套件類存在，讀擴充套件類
		if($this->$class_name && is_object($this->$class_name)){
			return $this->$class_name;
		}
		//擴充套件類不存在，只有基類，則讀基類
		if($this->$class_base && is_object($this->$class_base)){
			return $this->$class_base;
		}
		//檢查是否有 phpok5 使用的類
		$model_file = $this->dir_app.$name.'/model.php';
		if(is_file($model_file)){
			return $this->_model_phpok5($name);
		}
		$basefile = $this->dir_phpok.'model/'.$name.'.php';
		if(!file_exists($basefile)){
			$this->error_404("Model基礎類：".$name." 不存在，請檢查");
		}
		include($basefile);
		$extfile = $this->dir_phpok.'model/'.$this->app_id.'/'.$name.'_model.php';
		if(file_exists($extfile)){
			include($extfile);
			$this->$class_name = new $class_name();
			return $this->$class_name;
		}
		$this->$class_base = new $class_base();
		return $this->$class_base;
	}

	private function _ctrl_phpok5($name,$appid)
	{
		include_once($this->dir_app.$name.'/'.$appid.'.control.php');
		$class_name = $appid.'_'.$name.'_control';
		$tmp = 'phpok\app\control\\'.$name.'\\'.$appid.'_control';
		$this->$class_name = new $tmp();
		return $this->$class_name;
	}

	private function _model_phpok5($name)
	{
		$class_name = $name."_model";
		$class_base = $name."_model_base";
		include($this->dir_app.$name.'/model.php');
		if(is_file($this->dir_app.$name.'/'.$this->app_id.'.model.php')){
			include($this->dir_app.$name.'/'.$this->app_id.'.model.php');
			$tmp = 'phpok\app\model\\'.$name.'\\'.$this->app_id.'_model';
			$this->$class_name = new $tmp();
			return $this->$class_name;
		}
		$tmp = 'phpok\app\model\\'.$name.'\model';
		$this->$class_base = new $tmp();
		return $this->$class_base;
	}

	/**
	 * 執行外掛
	 * @引數 $ap 字串，對應外掛下的方法
	 * @引數 $param 執行方法中涉及到的引數，字串，可根據實際情況傳入
	 * @返回 視外掛執行返回，預設返回true或false
	 * @更新時間 2016年06月05日
	**/
	public function plugin($ap,$param="")
	{
		if(!$ap){
			return false;
		}
		$ap = str_replace("-","_",$ap);//替換節點的中劃線為下劃線
		if(!$this->plugin || count($this->plugin)<1 || !is_array($this->plugin)){
			return false;
		}
		$count = func_num_args();
		if($count>2){
			$tmp = array(0=>$param);
			for($i=2;$i<$count;$i++){
				$val = func_get_arg($i);
				$tmp[($i-1)] = $val;
			}
			foreach($this->plugin as $key=>$value){
				if(in_array($ap,$value['method'])){
					call_user_func_array(array($value['obj'], $ap),$tmp);
				}
			}
			return true;
		}
		foreach($this->plugin as $key=>$value){
			if(in_array($ap,$value['method'])){
				$value['obj']->$ap($param);
			}
		}
		return true;
	}

	final public function node($ap,$param='')
	{
		if(!$ap){
			return false;
		}
		$ap = str_replace("-","_",$ap);//替換節點的中劃線為下劃線
		$applist = $this->model('appsys')->installed();
		if(!$applist){
			return false;
		}
		$count = func_num_args();
		if($count>2){
			$tmp = array(0=>$param);
			for($i=2;$i<$count;$i++){
				$val = func_get_arg($i);
				$tmp[($i-1)] = $val;
			}
			foreach($applist as $key=>$value){
				$obj = $this->ctrl($key);
				if($obj && method_exists($obj,$ap)){
					call_user_func_array(array($obj, $ap),$tmp);
				}
			}
			return true;
		}
		foreach($applist as $key=>$value){
			$obj = $this->ctrl($key);
			if($obj && method_exists($obj,$ap)){
				$obj->$ap($param);
			}
		}
		return true;
	}

	/**
	 * 載入HTML外掛節點
	 * @引數 $name 外掛節點名稱
	**/
	public function plugin_html_ap($name)
	{
		$ap = 'html-'.$this->ctrl.'-'.$this->func.'-'.$name;
		$this->plugin($ap);
		$this->plugin('html-'.$name);
	}

	private function _config_ini_format($array)
	{
		$tmp_array = array("dir_root"=>$this->dir_root,'dir_data'=>$this->dir_data,'dir_cache'=>$this->dir_cache);
		return $tmp_array[$array[1]];
	}

	/**
	 * 裝載資源引挈，預設引挈載入將在config裡配置
	**/
	private function init_engine()
	{
		if(!$this->config["db"] && !$this->config["engine"]){
			$this->_error("資源引挈裝載失敗，請檢查您的資源引挈配置，如資料庫連線配置等");
		}
		if($this->config["db"] && !$this->config["engine"]["db"]){
			$this->config["engine"]["db"] = $this->config["db"];
			$this->config["db"] = "";
		}
		include($this->dir_phpok.'engine/db.php');
		include($this->dir_phpok.'engine/db/'.$this->config['engine']['db']['file'].'.php');
		$var = 'db_'.$this->config['engine']['db']['file'];
		$this->db = new $var($this->config['engine']['db']);
		
		foreach($this->config["engine"] as $key=>$value){
			if($key == 'db'){
				continue;
			}
			foreach($value as $k=>$v){
				$v = preg_replace_callback('/\{(.+)\}/isU',array($this,'_config_ini_format'),$v);
				$value[$k] = $v;
			}
			$basefile = $this->dir_phpok.'engine/'.$key.'.php';
			if(file_exists($basefile)){
				include($basefile);
			}
			$file = $this->dir_phpok."engine/".$key."/".$value["file"].".php";
			if(file_exists($file)){
				include($file);
				$var = $key."_".$value["file"];
				$obj = new $var($value);
			}else{
				$obj = new $key($value);
			}
			if($value['auto_methods']){
				$tmp = explode(",",$value['auto_methods']);
				foreach($tmp as $k=>$v){
					$v = trim($v);
					if(!$v){
						continue;
					}
					$temp = explode(":",$v);
					if(!$temp[0]){
						continue;
					}
					$funclist = get_class_methods($obj);
					if(!$funclist || !in_array($temp[0],$funclist)){
						continue;
					}
					if($temp[1]){
						$var = $temp[1];
						$param = $this->config['engine'][$var] ? $this->config['engine'][$var] : ($this->$var ? $this->$var : $this->lib($var));
						$var = $temp[0];
						$obj->$var($param);
					}else{
						$var = $temp[0];
						$obj->$var();
					}
				}
			}
			$this->$key = $obj;
		}
		$info = $this->lib('debug')->stop('config');
	}

	/**
	 * 讀取網站引數配置
	 * @更新時間 2016年02月05日
	 */
	private function init_config()
	{
		$config = array();
		if(file_exists($this->dir_config.'global.ini.php')){
			$config = parse_ini_file($this->dir_config.'global.ini.php',true);
		}
		//裝載引挈引數
		if(file_exists($this->dir_config.'engine.ini.php')){
			$ext = parse_ini_file($this->dir_config.'engine.ini.php',true);
			if($ext && is_array($ext)){
				$config['engine'] = $ext;
				unset($ext);
			}
		}
		//連線資料庫
		if(file_exists($this->dir_config.'db.ini.php')){
			$ext = parse_ini_file($this->dir_config.'db.ini.php',true);
			if($ext && is_array($ext)){
				$config['engine']['db'] = $ext;
				unset($ext);
			}
		}
		if(file_exists($this->dir_config.$this->app_id.'.ini.php')){
			$ext = parse_ini_file($this->dir_config.$this->app_id.'.ini.php',true);
			if($ext && is_array($ext)){
				$config = array_merge($config,$ext);
				unset($ext);
			}
		}

		//相容舊版本操作，繼續讀取 config.php 檔案
		//將在下一版本更新取消
		if(file_exists($this->dir_root.'config.php')){
			include_once($this->dir_root.'config.php');
		}
		if($config['debug']){
			if(function_exists('opcache_reset')){
				ini_set('opcache.enable',false);
			}
			ini_set('display_errors','on');
			error_reporting(E_ALL ^ E_NOTICE);
		}else{
			error_reporting(0);
			if(isset($config['opcache']) && function_exists('opcache_reset')){
				ini_set('opcache.enable',$config['opcache']);
			}
		}
		if(ini_get('zlib.output_compression')){
			ob_start();
		}else{
			($config["gzip"] && function_exists("ob_gzhandler")) ? ob_start("ob_gzhandler") : ob_start();
		}
		if($config["timezone"] && function_exists("date_default_timezone_set")){
			date_default_timezone_set($config["timezone"]);
		}
		$this->time = time();
		if($config["timetuning"]){
			$this->time = $this->time + $config["timetuning"];
		}
		if(!$config['get_domain_method']){
			$config['get_domain_method'] = 'SERVER_NAME';
		}
		$this->config = $config;
		unset($config);
	}

	/**
	 * 網址生成，在模板中通過{url ctrl=控制器 func=方法 id=標識 …/}生成網址
	 * @引數 $ctrl 字串或數字，系統保留字串（$config[reserved]）為系統，非保留字元自動移成識別符號或ID
	 * @引數 $func 字串，當ctrl為標識或ID是，該引數對應cate裡的標識
	 * @引數 $ext 字串，擴充套件引數，格式為：變數名=變數值，多個擴充套件引數用&符號連線，示例：pageid=1&param=1
	 * @引數 $appid 字串，留空自動呼叫當前頁面系統使用的app_id，支援的字串有：api，www，admin三個
	 * @引數 $baseurl 布林值，為true時網址會帶上{$sys.url}，即http://****，為false時，僅返回相對網址
	 * @返回 字串，網址連結
	 * @更新時間 2016年06月05日
	**/
	final public function url($ctrl="",$func="",$ext="",$appid='',$baseurl=false)
	{
		if(!$appid){
			$appid = $this->app_id;
		}
		$this->model('url')->app_file($this->config[$appid.'_file']);
		$this->model('url')->set_type($this->site['url_type']);
		$this->model('url')->url_appid($appid);
		if(is_bool($func)){
			$baseurl = $func;
			$func = '';
		}
		if($baseurl){
			$this->model('url')->base_url($this->url);
		}
		return $this->model('url')->url($ctrl,$func,$ext);
	}

	/**
	 * 自動生成網址，系統自帶
	**/
	final public function root_url($siteId=0)
	{
		$http_type = $this->is_https() ? 'https://' : 'http://';
		$port = $this->lib('server')->port();
		if($siteId){
			$myurl = $this->model('site')->site_domain($siteId,$this->is_mobile);
		}
		if(!$myurl){
			$myurl = $this->lib('server')->domain($this->config['get_domain_method']);
		}
		if(!$myurl){
			$this->_error('無法獲取網站域名資訊，請檢查環境是否支援$_SERVER["SERVER_NAME"]或$_SERVER["HTTP_HOST"]');
		}
		if($port != "80" && $port != "443"){
			$myurl .= ":".$port;
		}
		$docu = $this->lib('server')->me();
		if($this->lib('server')->path_info()){
			$docu = substr($docu,0,-(strlen($this->lib('server')->path_info())));
		}
		$array = explode("/",$docu);
		$count = count($array);
		if($count>1){
			foreach($array as $key=>$value){
				$value = trim($value);
				if($value && ($key+1) < $count){
					$myurl .= "/".$value;
				}
			}
			unset($array,$count);
		}
		$myurl .= "/";
		$myurl = str_replace("//","/",$myurl);
		return $http_type.$myurl;
	}
	
	/**
	 * 配置網站全域性常量
	 */
	private function init_constant()
	{
		//配置程式根目錄
		if(!defined("ROOT")){
			define("ROOT",str_replace("\\","/",dirname(__FILE__))."/../");
		}
		$this->dir_root = ROOT;
		if(substr($this->dir_root,-1) != "/"){
			$this->dir_root .= "/";
		}
		//配置訪問根目錄
		if(!defined("WEBROOT")){
			define("WEBROOT",'/');
		}
		$this->dir_webroot = WEBROOT;
		if($this->dir_webroot == '.'){
			$this->dir_webroot = '';
		}
		if($this->dir_webroot && substr($this->dir_webroot,-1) != "/"){
			$this->dir_webroot .= "/";
		}
		//配置框架根目錄
		if(!defined("FRAMEWORK")){
			define("FRAMEWORK",$this->dir_root."framework/");
		}
		$this->dir_phpok = FRAMEWORK;
		if(substr($this->dir_phpok,-1) != "/"){
			$this->dir_phpok .= "/";
		}
		$list = array('cache','config','data','extension','plugin','gateway');
		$extlist = array();
		foreach($list as $key=>$value){
			$tmp = strtoupper($value);
			if(!defined($tmp)){
				define($tmp,$this->dir_root.'_'.$value.'/');
			}
			$name = 'dir_'.$value;
			$this->$name = constant($tmp);
			if(substr($this->$name,-1) != "/"){
				$this->$name .= "/";
			}
		}
		if(!defined('OKAPP')){
			define('OKAPP',ROOT.'_app/');
		}
		$this->dir_app = OKAPP;
		//定義APP_ID
		if(!defined("APP_ID")){
			define("APP_ID","www");
		}
		$this->app_id = APP_ID;
		//判斷載入的版本及授權方式
		if(file_exists($this->dir_root."version.php")){
			include($this->dir_root."version.php");
			$this->version = defined("VERSION") ? VERSION : "4.5.0";
		}
		if(file_exists($this->dir_root."license.php")){
			include($this->dir_root."license.php");
			$license_array = array("LGPL","PBIZ","CBIZ");
			$this->license = (defined("LICENSE") && in_array(LICENSE,$license_array)) ? LICENSE : "LGPL";
			if(defined("LICENSE_DATE")){
				$this->license_date = LICENSE_DATE;
			}
			if(defined("LICENSE_SITE")){
				$this->license_site = LICENSE_SITE;
			}
			if(defined("LICENSE_CODE")){
				$this->license_code = LICENSE_CODE;
			}
			if(defined("LICENSE_NAME")){
				$this->license_name = LICENSE_NAME;
			}
			if(defined("LICENSE_POWERED")){
				$this->license_powered = LICENSE_POWERED;
			}
		}
		$this->is_ajax = $this->lib('server')->ajax();
	}

	/**
	 * 通過post或get取得資料，自動判斷是否轉義，未轉義將自動轉義，轉義後執行格式化操作
	 * @引數 $id 字串，要取得的資料ID，對應網頁中的input裡的name資訊
	 * @引數 $type 字串，格式化方式，預設是safe，支援：safe，html，html_js，float，int，checkbox，time，text，system等多種格式化方式
	 * @引數 $ext 數值或布林值，為1或true時，在type為html時，等同於html_js，當type為func時，則ext為直接執行的函式
	 * @返回 格式化後的資料
	**/
	final public function get($id,$type="safe",$ext="")
	{
		$val = isset($_POST[$id]) ? $_POST[$id] : (isset($_GET[$id]) ? $_GET[$id] : (isset($_COOKIE[$id]) ? $_COOKIE[$id] : ''));
		if($val == ''){
			if($type == 'int' || $type == 'intval' || $type == 'float' || $type == 'floatval'){
				return 0;
			}else{
				return '';
			}
		}
		//判斷內容是否有轉義，所有未轉義的資料都直接轉義
		$addslashes = false;
		if(function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()){
			$addslashes = true;
		}
		if(!$addslashes){
			$val = $this->_addslashes($val);
		}
		return $this->format($val,$type,$ext);
	}

	/**
	 * 格式化內容
	 * @引數 $msg，要格式化的內容，該內容已經轉義了
	 * @引數 $type，型別，支援：safe，text，html，html_js，func，int，float，system
	 * @引數 $ext，擴充套件，當type為html時，ext存在表示支援js，不存在表示不支援js，當type為func屬性時，表示ext直接執行函式
	**/
	final public function format($msg,$type="safe",$ext="")
	{
		if($msg == ""){
			return '';
		}
		if(is_array($msg)){
			foreach($msg as $key=>$value){
				if(!is_numeric($key)){
					$key2 = $this->format($key);
					if($key2 == '' || in_array($key2,array('#','&','%'))){
						unset($msg[$key]);
						continue;
					}
				}
				$msg[$key] = $this->format($value,$type,$ext);
			}
			if($msg && count($msg)>0){
				return $msg;
			}
			return false;
		}
		if($type == 'html_js' || ($type == 'html' && $ext)){
			$msg = stripslashes($msg);
			if($this->app_id != 'admin'){
				$msg = $this->lib('string')->xss_clean($msg);
			}
			$msg = $this->lib('string')->clear_url($msg,$this->url);
			return addslashes($msg);
		}
		$msg = stripslashes($msg);
		//格式化處理內容
		switch ($type){
			case 'safe':
				$msg = str_replace(array("\\","'",'"',"<",">"),array("&#92;","&#39;","&quot;","&lt;","&gt;"),$msg);
			break;
			case 'safe_text':
				$msg = strip_tags($msg);
				$msg = str_replace(array("\\","'",'"',"<",">"),'',$msg);
			break;
			case 'system':
				$msg = !preg_match("/^[a-zA-Z][a-z0-9A-Z\_\-]+$/u",$msg) ? false : $msg;
			break;
			case 'id':
				$msg = !preg_match("/^[a-zA-Z][a-z0-9A-Z\_\-]+$/u",$msg) ? false : $msg;
			break;
			case 'checkbox':
				$msg = strtolower($msg) == 'on' ? 1 : $this->format($msg,'safe');
			break;
			case 'int':
				$msg = intval($msg);
			break;
			case 'intval':
				$msg = intval($msg);
			break;
			case 'float':
				$msg = floatval($msg);
			break;
			case 'floatval':
				$msg = floatval($msg);
			break;
			case 'time':
				$msg = strtotime($msg);
			break;
			case 'html':
				$msg = $this->lib('string')->safe_html($msg,$this->url);
			break;
			case 'func':
				$msg = function_exists($ext) ? $ext($msg) : false;
			break;
			case 'text':
				$msg = strip_tags($msg);
			break;
			default:
				$msg = str_replace(array("\\","'",'"',"<",">"),array("&#92;","&#39;","&quot;","&lt;","&gt;"),$msg);
			break;
		}
		if($msg){
			$msg = addslashes($msg);
		}
		return $msg;
	}

	/**
	 * 安全的HTML資訊，用於過濾iframe,script,link及html中涉及到的一些觸發資訊
	**/
	public function safe_html($info)
	{
		return $this->lib('string')->safe_html($info);
	}

	/**
	 * 轉義資料
	**/
	private function _addslashes($val)
	{
		if(is_array($val)){
			foreach($val as $key=>$value){
				$val[$key] = $this->_addslashes($value);
			}
		}else{
			$val = addslashes($val);
		}
		return $val;
	}

	/**
	 * 分配資訊給模板，使用模板中可呼叫
	 * @引數 $var 模板中要使用的變數名
	 * @引數 $val 要分配的資訊
	**/
	final public function assign($var,$val)
	{
		$this->tpl->assign($var,$val);
	}

	/**
	 * 登出分配給模板中的變數資訊
	 * @引數 $var 要登出的變數
	**/
	final public function unassign($var)
	{
		$this->tpl->unassign($var);
	}

	/**
	 * 檢視輸出，這是針對 phpok5 版寫的，實現不同的路徑的模板檔案識別，不適合外掛
	 * @引數 $file 相對檔案
	**/
	final public function display($file)
	{
		$tplfile = $this->dir_app.$this->ctrl.'/tpl/'.$file.'.html';
		if(file_exists($tplfile)){
			$this->view($tplfile,'abs-file');
		}
		$this->view($file);
	}

	/**
	 * 輸出HTML資訊
	 * @引數 $file 字串，指定的模板檔案，支援不帶字尾的模板名稱，也支援完整的模板名稱，也支援HTML內容，具體受引數$type影響
	 * @引數 $type 字串，支援 file：不帶字尾的模板名，file-ext：帶字尾的模板名，
	 *                         content：直接是內容，msg：等同於content，abs-file：完整路徑的模板檔案
	 * @引數 $path_format 布林值 是否格式化路徑資訊，慎用，模板裡有大量巢狀，可能會混亂（未深度測試）
	 * @返回 無，直接輸出HTML資訊到裝置上
	**/
	final public function view($file,$type="file",$path_format=true)
	{
		$this->plugin('phpok-after');
		$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after');
		
		//是否啟用非同步通知
		if($this->config['async']['status'] && $this->config['async']['interval_times']){
			$check = false;
			if(!file_exists($this->dir_cache.'async_interval_times.php')){
				$check = true;
			}
			if(!$check){
				$time = file_get_contents($this->dir_cache.'async_interval_times.php');
				if(($time + $this->config['async_interval_times'] * 60) < $this->time){
					$check = true;
				}
			}
			if($check){
				$taskurl = api_url('task','index',$this->session->sid()."=".$this->session->sessid(),true);
				$this->lib('async')->start($taskurl);
				file_put_contents($this->dir_cache.'async_interval_times.php',$this->time);
			}
		}

		header("Content-type: text/html; charset=utf-8");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: Mon, 26 Jul 1997 05:00:00  GMT");
		header("Cache-control: no-cache,no-store,must-revalidate,max-age=3");
		header("Pramga: no-cache");
		$this->tpl->display($file,$type,$path_format);
	}

	/**
	 * 取得HTML資訊，不輸出到裝置上，方便二次更改
	 * @引數 $file 字串，指定的模板檔案，支援不帶字尾的模板名稱，也支援完整的模板名稱，也支援HTML內容，具體受引數$type影響
	 * @引數 $type 字串，支援 file：不帶字尾的模板名，file-ext：帶字尾的模板名，
	 *                         content：直接是內容，msg：等同於content，abs-file：完整路徑的模板檔案
	 * @引數 $path_format 布林值 是否格式化路徑資訊，慎用，模板裡有大量巢狀，可能會混亂（未深度測試）
	 * @返回 字串
	**/
	final public function fetch($file,$type="file",$path_format=true)
	{
		$this->plugin('phpok-after');
		$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after');
		return $this->tpl->fetch($file,$type,$path_format);
	}

	/**
	 * 取得系統URL
	**/
	final public function get_url()
	{
		return $this->url;
	}

	/**
	 * 異常丟擲，該錯誤主要用於未載入模板時使用，出現這個錯誤，表示程式無法正常執行，直接中止
	 * @引數 $content 字串，在裝置上要列印的錯誤資訊
	**/
	final public function _error($content="")
	{
		if(!$content) $content = "異常請檢查";
		$html = '<!DOCTYPE html>'."\n";
		$html.= '<html>'."\n";
		$html.= '<head>'."\n";
		$html.= '	<meta charset="utf-8" />'."\n";
		$html.= '	<title>友情提示</title>'."\n";
		$html.= '</head>'."\n";
		$html.= '<body style="padding:10px;font-size:14px;">'."\n";
		$html.= $content."\n";
		$html.= '</body>'."\n";
		$html.= '</html>';
		exit($html);
	}

	private function _userToken()
	{
		$me = false;
		if($this->session->val('user_id')){
			$me = $this->model('user')->get_one($this->session->val('user_id'));
			if($me){
				$this->data('me',$me);
				$this->assign('me',$me);
			}
		}
		if(!$me && !$this->site['api_code']){
			return false;
		}
		if($me && $this->site['api_code']){
			$token = $this->model('user')->token_create($me['id'],$this->site['api_code']);
			$this->data('meToken',$token);
			return $me;
		}
		$tokenId = $this->config['token_id'] ? $this->config['token_id'] : 'userToken';
		$token = $this->get($tokenId,'html');
		if(!$token){
			return false;
		}
		$this->lib('token')->keyid($this->site['api_code']);
		$info = $this->lib('token')->decode($token);
		if(!$info || !is_array($info)){
			return false;
		}
		if(!$info['id'] || !$info['code']){
			return false;
		}
		$chkstatus = $this->model('user')->token_check($info['id'],$info['code']);
		if(!$chkstatus){
			return false;
		}
		$me = $this->model('user')->get_one($this->session->val('user_id'));
		if(!$me){
			return false;
		}
		$newToken = $this->lib('token')->encode(array('id'=>$info['id'],'code'=>$info['code']));
		$this->data('meToken',$newToken);
		$this->data('me',$me);
		$this->assign('me',$me);
		return $me;
	}

	/**
	 * 執行應用，三個入口（前端，介面，後臺）都是從這裡執行，進行初始化處理
	 * token 及 user_id 在 phpok5.0 中將剝離，不會放在核心引挈裡
	**/
	final public function action()
	{
		$this->init_assign();
		$this->init_plugin();
		if($this->app_id == 'admin'){
			$this->action_admin();
			exit;
		}
		$this->_userToken();
		if($this->app_id == 'api'){
			$this->action_api();
			exit;
		}
		$this->action_www();
		exit;
	}

	/**
	 * 介面入口處理
	**/
	private function action_api()
	{
		$ctrl = $this->get($this->config["ctrl_id"],"system");
		if(!$ctrl){
			$ctrl = 'index';
		}
		$func = $this->get($this->config["func_id"],"system");
		if(!$func){
			$func = 'index';
		}
		$this->_action($ctrl,$func);
	}

	private function _route()
	{
		$data = array();
		$uri = $this->lib('server')->uri();
		$docu = $this->lib('server')->me();
		if($this->lib('server')->path_info()){
			$docu = substr($docu,0,-(strlen($this->lib('server')->path_info())));
		}
		$array = explode("/",$docu);
		$docu = '/';
		$count = count($array);
		if($count>1){
			foreach($array as $key=>$value){
				$value = trim($value);
				if($value && ($key+1) < $count){
					$docu .= $value.'/';
				}
			}
		}
		if($docu != '/' && substr($uri,0,strlen($docu)) == $docu){
			$uri = substr($uri,(strlen($docu)-1));
		}
		$script_name = $this->lib('server')->phpfile();
		if('/'.$script_name == substr($uri,0,(strlen($script_name)+1))){
			$uri = substr($uri,(strlen($script_name)+1));
		}
		$data['script'] = $script_name;
		$query_string = $this->lib('server')->query();
		if($query_string){
			$uri = str_replace('?'.$query_string,'',$uri);
			$data['query'] = $query_string;
			$get = parse_str($query_string);
			$this->data('get',$get);
		}
		if($uri != '/' && strlen($uri)>2){
			if(substr($uri,0,1) == '/'){
				$uri = substr($uri,1);
			}
			if(substr($uri,-1) == '/'){
				$uri = substr($uri,0,-1);
			}
		}
		$data['url'] = $uri;
		$data['folder'] = $docu;
		$this->data('uri',$data);
		$this->model('rewrite')->uri_format($uri);
	}

	/**
	 * 前臺入口處理
	**/
	private function action_www()
	{
		$this->model('site')->site_id($this->site['id']);
		$this->_route();
		$id = $this->get('id');
		$ctrl = $this->get($this->config["ctrl_id"],"system");
		$func = '';
		if($id && !$ctrl && $id != 'index'){
			$ctrl = $id;
			$reserved = $this->model('site')->reserved();
			if(!in_array($id,$reserved)){
				$ctrl = is_numeric($id) ? 'content' : $this->model('id')->get_ctrl($id,$this->site['id']);
				if(!$ctrl){
					$this->error_404();
				}
			}
			if($ctrl == 'post'){
				$cate = $this->get('cate','system');
				if($cate == 'add' || $cate == 'edit'){
					$func = $cate;
					unset($_GET['cate']);
				}
			}
		}
		if(!$ctrl){
			$ctrl = 'index';
		}
		if(!$func){
			$func = $this->get($this->config["func_id"],"system");
		}
		if(!$func){
			$func = 'index';
		}
		//針對亂七八糟的網址，或是路徑進行清理
		if($ctrl == 'index' && $func == 'index'){
			$uri = $this->data('uri.url');
			$query = $this->data('uri.query');
			if($query){
				$params = $this->config['get_params'] ? explode(",",$this->config['get_params']) : array('uid','phpfile','siteId','_langid');
				parse_str($query,$tmp);
				foreach(($tmp ? $tmp : array())  as $key=>$value){
					if(in_array($key,$params)){
						unset($tmp[$key]);
					}
				}
				if($tmp && is_array($tmp) && count($tmp)>0){
					$this->error_404(P_Lang('您的請求資訊不正確，請檢查（無效引數）'));
				}
			}
			$docu = $this->data('uri.folder');
			$script_name = $this->data('uri.script');
			$exit = false;
			if(is_file($this->dir_root.$uri)){
				$exit = true;
			}
			$uri = str_replace(array('index.html','index.htm',$this->config['www_file'],'index'),'',$uri);
			$basename = basename($docu);
			$folder = $docu;
			if($basename){
				$folder = substr($docu,0,-(strlen($basename)));
				if($uri && substr($uri,-(strlen($basename))) == $basename){
					$uri = substr($uri,0,-(strlen($basename)));
				}
			}
			if($uri && $uri != '/' && $folder && $uri != $folder && !$exit){
				$this->error_404(P_Lang('您的請求資訊不正確，請檢查（無效路由）'));
			}
		}
		$this->_action($ctrl,$func);
	}

	/**
	 * 後臺入口處理
	**/
	private function action_admin()
	{
		$ctrl = $this->get($this->config["ctrl_id"],"system");
		$func = $this->get($this->config["func_id"],"system");
		if(!$ctrl){
			$ctrl = "index";
		}
		if(!$func){
			$func = "index";
		}
		if($ctrl != 'login' && !$this->config['develop']){
			$referer = $this->lib('server')->referer();
			if(!$referer && !$this->session->val('admin_id')){
				$ctrl='login';
				$func = 'index';
				$this->_location($this->url('login'));
			}
			if($referer){
				$chk = parse_url($this->url);
				$info = parse_url($referer);
				if($info['host'] != $chk['host']){
					$ctrl = 'login';
					$func = 'index';
					$this->session->destroy();
					$this->_location($this->url('login'));
				}
			}
		}
		$this->lib('form')->appid('admin');
		$this->_action($ctrl,$func);
	}

	/**
	 * 網頁跳轉，此跳轉基於PHP執行
	 * @引數 $url 字串，要跳轉的網址
	**/
	public function _location($url)
	{
		ob_end_clean();
		ob_start();
		header("Content-type: text/html; charset=utf-8");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
		header("Last-Modified: Mon, 26 Jul 1997 05:00:00  GMT"); 
		header("Cache-control: no-cache,no-store,must-revalidate,max-age=0"); 
		header("Pramga: no-cache");
		header("Location:".$url);
		ob_end_flush();
		exit;
	}

	/**
	 * 呼叫控制器及方法執行
	 * @引數 $ctrl 控制器名稱，根據不同的入口呼叫不同的控制器
	 * @引數 $func 要執行的方法
	**/
	private function _action($ctrl='index',$func='index')
	{
		//如果App_id非指定的三種，強制初始化
		if(!in_array($this->app_id,array('api','www','admin'))){
			$this->app_id = 'www';
		}
		$reserved = array('login','js','ajax','inp','register');
		if($this->app_id == 'admin'){
			if(!$this->session->val('admin_id') && !in_array($ctrl,$reserved)){
				$ctrl = 'login';
				$go_url = $this->url($ctrl);
				$this->_location($go_url);
			}
		}
		if($this->app_id == 'www'){
			$is_login = isset($this->config['is_login']) ? $this->config['is_login'] : false;
			if(isset($this->config[$this->app_id]['is_login'])){
				$is_login = $this->config[$this->app_id]['is_login'];
			}
			if($is_login && !$this->session->val('user_id') && !in_array($ctrl,$reserved)){
				$ctrl = 'login';
				$go_url = $this->url($ctrl);
				$this->_location($go_url);
			}
		}
		
		if(file_exists($this->dir_phpok.$this->app_id."/global.func.php")){
			include($this->dir_phpok.$this->app_id."/global.func.php");
		}
		//前臺後臺都支援自定義載入的 global.func.php
		if(file_exists($this->dir_plugin."global.func.php")){
			include($this->dir_plugin."global.func.php");
		}
		if(file_exists($this->dir_extension."global.func.php")){
			include($this->dir_extension."global.func.php");
		}
		if(file_exists($this->dir_root."gateway/global.func.php")){
			include($this->dir_root."gateway/global.func.php");
		}
		//允許使用者自定義載入 global.func.php 檔案
		//前臺及介面支援二個地方載入，分別是：data，phpinc
		if($this->app_id != 'admin'){
			if(file_exists($this->dir_data."global.func.php")){
				include($this->dir_data."global.func.php");
			}
			if(file_exists($this->dir_root."phpinc/global.func.php")){
				include($this->dir_root."phpinc/global.func.php");
			}
		}

		//--- 增加 phpok5 寫法
		if(file_exists($this->dir_app.'global.func.php')){
			include($this->dir_app."global.func.php");
		}
		if(file_exists($this->dir_app.$this->app_id.'.func.php')){
			include($this->dir_app.$this->app_id.".func.php");
		}
		$apps = $this->model('appsys')->installed();
		$protected_ctrl = array();
		if($apps){
			foreach($apps as $key=>$value){
				$protected_ctrl[] = $key;
				if(is_file($this->dir_app.$key.'/global.func.php')){
					include_once($this->dir_app.$key.'/global.func.php');
				}
				if(is_file($this->dir_app.$key.'/'.$this->app_id.'.func.php')){
					include_once($this->dir_app.$key.'/'.$this->app_id.'.func.php');
				}
			}
		}
		$this->model('url')->protected_ctrl($protected_ctrl);

		//自動執行的函式
		if($this->config[$this->app_id]["autoload_func"]){
			$list = explode(",",$this->config[$this->app_id]["autoload_func"]);
			foreach($list as $key=>$value){
				if(function_exists($value)){
					$value();
				}
			}
			unset($list);
		}
		
		$appfile = $this->dir_app.$ctrl.'/'.$this->app_id.'.control.php';
		if($appfile && file_exists($appfile)){
			$this->_action_phpok5($appfile,$ctrl,$func);
		}
		$this->_action_phpok4($ctrl,$func);
	}

	private function _action_phpok4($ctrl,$func)
	{
		$dir_root = $this->dir_phpok.$this->app_id.'/';
		if($ctrl == 'js' || $ctrl == 'ajax' || $ctrl == "inp"){
			$dir_root = $this->dir_phpok;
		}
		//載入應用檔案
		if(!file_exists($dir_root.$ctrl.'_control.php')){
			$this->error_404('應用檔案：'.$ctrl.'_control.php 不存在，請檢查');
		}
		include($dir_root.$ctrl.'_control.php');

		$app_name = $ctrl."_control";
		$this->ctrl = $ctrl;
		$this->func = $func;
		$cls = new $app_name();
		$func_name = $func."_f";
		if(!in_array($func_name,get_class_methods($cls))){
			$this->_error("控制器 ".$ctrl." 不存在方法 ".$func_name);
		}
		$this->config['ctrl'] = $ctrl;
		$this->config['func'] = $func;
		$this->config['time'] = $this->time;
		$this->config['webroot'] = $this->dir_webroot;
		$this->assign('sys',$this->config);
		$this->plugin('phpok-before');
		$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-before');
		if($this->app_id == 'www' && !$this->site['status'] && !$this->session->val('admin_id')){
			$this->error($this->site["content"]);
		}
		$cls->$func_name();
		exit;
	}

	private function _action_phpok5($appfile,$ctrl,$func)
	{
		include($appfile);
		$this->ctrl = $ctrl;
		$this->func = $func;
		$name = 'phpok\app\control\\'.$ctrl.'\\'.$this->app_id.'_control';
		$cls = new $name();
		$func_name = $func."_f";
		if(!in_array($func_name,get_class_methods($cls))){
			$this->_error("控制器 ".$ctrl." 不存在方法 ".$func_name);
		}
		$this->config['ctrl'] = $ctrl;
		$this->config['func'] = $func;
		$this->config['time'] = $this->time;
		$this->config['webroot'] = $this->dir_webroot;
		$this->assign('sys',$this->config);
		$this->plugin('phpok-before');
		$this->plugin('ap-'.$ctrl.'-'.$func.'-before');
		if($this->app_id == 'www' && !$this->site['status'] && !$this->session->val('admin_id')){
			$this->error($this->site["content"]);
		}
		$cls->$func_name();
		exit;
	}

	/**
	 * JSON資料輸出，要注意的是在輸出時會觸發外掛，故該方法在外掛使用要小心，防止出現死迴圈
	 * @引數 $content 要輸出的內容，支援字串，陣列及布林值，為布林值是true直接輸出 status=>ok，為false時輸出 status=>error
	 * @引數 $status 布林值，為true時輸出status=>ok，false輸出status=>error，並附帶相應的內容content=>$content
	 * @引數 $exit 布林值，為false時，不中止執行，會繼續執行下面的PHP檔案，一般不需要用到
	 * @返回 格式化後json資料
	 * @更新時間 2016年06月05日
	**/
	final public function json($content,$status=false,$exit=true)
	{
		if($content && !is_bool($content) && is_string($content) && strlen($content) < 61440 && $exit && $this->config['debug']){
			$this->model('log')->save($content);
		}
		if($exit){
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
			header("Last-Modified: Mon, 26 Jul 1997 05:00:00  GMT"); 
			header("Cache-control: no-cache,no-store,must-revalidate,max-age=0"); 
			header("Pramga: no-cache"); 
		}
		if(!$content && is_bool($content)){
			$rs = array('status'=>'error');
			exit($this->lib('json')->encode($rs));
		}
		//當content內容為true 且為布林型別，直接返回正確通知結果
		if($content && is_bool($content)){
			$rs = array('status'=>'ok');
			$this->plugin('phpok-after');
			$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after');
			exit($this->lib('json')->encode($rs));
		}
		$status_info = $status ? 'ok' : 'error';
		if($status_info == 'ok'){
			$this->plugin('phpok-after');
			$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after');
		}
		$rs = array('status'=>$status_info);
		if($content != '') $rs['content'] = $content;
		$info = $this->lib('json')->encode($rs);
		unset($rs);
		if($exit){
			exit($info);
		}
		return $info;
	}


	/**
	 * JSONP資料返回操作
	 * @引數 $content，混合型，為字串或陣列時，表示內容。為true或false時，status裡的內容表示網址
	 * @引數 $status，狀態，如果為字串時，表示網址
	 * @引數 $url，網址，如果為true或false時表示狀態
	 * @返回 字串
	 * @更新時間 2016年06月11日
	**/
	final public function jsonp($content,$status=false,$url='')
	{
		$callback = $this->get($this->config['jsonp']['getid']);
		if(!$callback){
			$callback = $this->config['jsonp']['default'];
			if(!$callback){
				$callback = 'callback';
			}
		}
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
		header("Last-Modified: Mon, 26 Jul 1997 05:00:00  GMT"); 
		header("Cache-control: no-cache,no-store,must-revalidate,max-age=0"); 
		header("Pramga: no-cache");
		if(!$content && is_bool($content)){
			$rs = array('status'=>0);
			if($status && is_string($status)){
				$rs['url'] = $status;
			}
			exit($callback.'('.$this->lib('json')->encode($rs).')');
		}
		if($content && is_bool($content)){
			$rs = array('status'=>1);
			if($status && is_string($status)){
				$rs['url'] = $status;
			}
			$this->plugin('phpok-after');
			$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after');
			exit($callback.'('.$this->lib('json')->encode($rs).')');
		}
		if($status){
			$rs = array('info'=>$content);
			if(is_bool($status)){
				$rs['status'] = 1;
				if($url){
					$rs['url'] = $url;
				}
				$this->plugin('phpok-after');
				$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after');
			}else{
				$rs = array('info'=>$content,'url'=>$status);
				if($url && is_bool($url)){
					$rs['status'] = 1;
					$this->plugin('phpok-after');
					$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after');
				}
			}
			exit($callback.'('.$this->lib('json')->encode($rs).')');
		}
		$rs = array('status'=>0);
		$rs['info'] = $content;
		if($url && is_string($url)){
			$rs['url'] = $url;
		}
		exit($callback.'('.$this->lib('json')->encode($rs).')');
	}

	/**
	 * 404錯誤，基於頁面
	**/
	final public function error_404($ajax=false)
	{
		$this->plugin("error-404");
		header("HTTP/1.0 404 Not Found");
		header('Status: 404 Not Found');
		if(true === $ajax || $this->is_ajax){
			header('Content-Type:application/json; charset=utf-8');
			exit($this->lib('json')->encode(array('status'=>false,'info'=>P_Lang('404錯誤'))));
		}
		if($ajax && is_string($ajax)){
			$this->tpl->assign('info',$ajax);
		}
		if($this->tpl->check_exists('404')){
			$this->tpl->display('404');
			exit;
		}
		echo '<h1>404錯誤</h1>';
		if($ajax && is_string($ajax)){
			echo "<p>".$ajax.'</p>';
		}else{
			echo '<p>您要訪問的頁面不存在</p>';
		}
		exit;
	}

	/**
	 * 友情錯誤提示，支援Ajax
	 * @引數 $info 錯誤資訊
	 * @引數 $url 跳轉網址
	 * @引數 $ajax 是否為Ajax方式 當數字時指定跳轉時間
	 * @更新時間 2016年01月22日
	**/
	public function error($info='',$url='',$ajax=false)
	{
		if($url && $ajax === false && !$this->is_ajax){
			$ajax = 2;
		}
		if($info && is_string($info) && $this->config['debug']){
			$this->model('log')->save($info);
		}
		$this->_tip($info,0,$url,$ajax);
	}

	/**
	 * 友情成功提示，支援Ajax
	 * @引數 $info 錯誤資訊
	 * @引數 $url 跳轉網址
	 * @引數 $ajax 是否為Ajax方式 當數字時指定跳轉時間
	 * @更新時間 2016年01月22日
	 */
	public function success($info='',$url='',$ajax=false)
	{
		if($url && $ajax === false && !$this->is_ajax){
			$ajax = 2;
		}
		if($info && is_string($info) && $this->config['debug']){
			$this->model('log')->save($info);
		}
		$this->_tip($info,1,$url,$ajax);
	}

	/**
	 * 提示資訊
	 * @引數 $info 錯誤資訊
	 * @引數 $url 跳轉網址
	 * @引數 $ajax 是否為Ajax方式 當數字時指定跳轉時間
	 * @更新時間 2016年01月22日
	**/
	public function tip($info='',$url='',$ajax=false)
	{
		if($url && $ajax === false && !$this->is_ajax){
			$ajax = 2;
		}
		if($info && is_string($info) && $this->config['debug']){
			$this->model('log')->save($info);
		}
		$this->_tip($info,2,$url,$ajax);
	}

	/**
	 * 友好提示
	 * @引數 $info 錯誤資訊
     * @引數 $status 狀態，1或true為成功，0或false為失敗，2為提示
	 * @引數 $url 跳轉網址
	 * @引數 $ajax 是否為Ajax方式 當數字時指定跳轉時間
	 * @更新時間 2016年01月22日
	**/
	protected function _tip($info='',$status=0,$url='',$ajax=false)
	{
		if(true === $ajax || $this->is_ajax){
			$data = is_array($ajax) ? $ajax : array();
			$data['info'] = $info;
			$data['status'] = $status;
			if($url){
				$data['url'] = $url;
			}
			header('Content-Type:application/json; charset=utf-8');
            exit($this->lib('json')->encode($data));
        }
        if($ajax && (is_int($ajax) || is_float($ajax))){
	        $this->assign('time',$ajax);
        }
        if($url){
	        if(defined('PHPOK_SITE_ID')){
		        if(strpos($url,'?') === false){
					$url .= "?siteId=".PHPOK_SITE_ID;
				}else{
					$url .= "&siteId=".PHPOK_SITE_ID;
				}
	        }
	        $this->assign('url',$url);
        }
        $this->assign('title',($status ? P_Lang('操作成功') : P_Lang('操作失敗')));
        $this->assign('type',($status ? 'success' : 'error'));
        if($status == 2){
	        $this->assign('type','notice');
        }
        $this->assign('status',$status);
        $this->assign('tips',$info);
        $this->assign('info',$info);
        $this->assign('content',$info);
        if($this->get("close_win")){
	        $this->assign('url','javascript:window.close();void(0)');
        }
        $fileid = $status ? 'success' : 'error';
        $tplfile = $this->tpl->check($fileid) ? $fileid : ($this->tpl->check('tips') ? 'tips' : '');
        header("Content-type: text/html; charset=utf-8");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
		header("Last-Modified: Mon, 26 Jul 1997 05:00:00  GMT"); 
		header("Cache-control: no-cache,no-store,must-revalidate,max-age=3"); 
		header("Pramga: no-cache"); 
        if(!$tplfile){
	        $chk = array($this->dir_root.'tpl/'.$fileid.'.html',$this->dir_root.'tpl/tips.html');
	        foreach($chk as $key=>$value){
		        if($this->tpl->check($value,true,true)){
			        $tplfile = $value;
		        }
	        }
	        $this->tpl->display($tplfile,'abs-file',false);
        }
		$this->tpl->display($tplfile);
	}

	/**
	 * 針對PHPOK4前臺執行SEO優化
	 * @引數 $rs 陣列，要替換的資料，需要包含：keywords或kw或keyword表示SEO裡的關鍵字，
	 *                                       description或desc表示優化描述，title表示優化標題
	**/
	final public function phpok_seo($rs)
	{
		if(!$rs || !is_array($rs)) return false;
		$seo = $this->site['seo'] ? $this->site["seo"] : array();
		foreach($rs as $key=>$value){
			if(substr($key,0,3) == "seo" && $value && is_string($value)){
				$subkey = substr($key,4);
				if($subkey == "kw" || $subkey == "keywords" || $subkey == "keyword"){
					$seo["keywords"] = $value;
				}elseif($subkey == "desc" || $subkey == "description"){
					$seo["description"] = $value;
				}elseif($subkey == "title"){
					$seo["title"] = $value;
				}else{
					$seo[$subkey] = $value;
				}
			}
		}
		$this->site['seo'] = $seo;
		$this->assign("seo",$seo);
		return $seo;
	}

	/**
	 * 增加js庫，在HTML模板裡可以直接使用 phpok_head_js，將生成符合標準的js檔案連結
	**/
	public function addjs($url='')
	{
		$this->jslist[] = $url;
	}

	/**
	 * 增加css檔案連結，在HTML裡可以直接使用 phpok_head_css，將生成符合標準的CSS檔案連結
	**/
	public function addcss($url='')
	{
		$this->csslist[] = $url;
	}

	/**
	 * 第三方閘道器執行
	 * @引數 $action 要執行的閘道器，param表示讀取閘道器資訊，extinfo表示變更閘道器擴充套件資訊extinfo，exec表示閘道器路由檔案的執行
	 * @引數 $param action為param時表示閘道器ID，default表示讀預設閘道器，action為extinfo時，param表示內容，
	 *              action為exec時表示輸出方式，為空返回，支援json，action為check時表示檢測閘道器是否存在
	**/
	final public function gateway($action,$param='')
	{
		if($action == 'type'){
			$this->gateway['type'] = $param;
			return true;
		}
		if($action == 'param'){
			if($param == 'default'){
				$info = $this->model('gateway')->get_default($this->gateway['type']);
			}elseif(is_numeric($param)){
				$info = $this->model('gateway')->get_one($param);
			}else{
				$info = $param;
			}
			if($info){
				$this->gateway['param'] = $info;
			}
			return true;
		}
		if($action == 'extinfo'){
			$this->gateway['extinfo'] = $param;
		}
		if($action == 'exec' || substr($action,-4) == '.php'){
			if(!$this->gateway['param']){
				return false;
			}
			$file = $action == 'exec' ? 'exec.php' : $action;
			$rs = $this->gateway['param'];
			$extinfo = $this->gateway['extinfo'];
			$exec_file = $this->dir_gateway.''.$this->gateway['param']['type'].'/'.$this->gateway['param']['code'].'/'.$file;
			$info = false;
			if(file_exists($exec_file)){
				$info = include $exec_file;
			}
			if($param == 'json'){
				if(!$info){
					$this->error();
				}
				exit($this->lib('json')->encode($info));
			}else{
				return $info;
			}
		}
		if($action == 'check'){
			return $this->gateway['param'] ? true : false;
		}
		if(!$this->gateway['param']){
			return false;
		}
		return true;
	}

}

/**
 * 核心魔術方法，此項可實現類，方法的自動載入，PHPOK裡的Control，Model及Plugin都繼承了這個類
**/
class _init_auto
{
	public function __construct()
	{
		//
	}

	/**
	 * 魔術方法之方法過載
	 * @引數 $method $GLOBALS['app']下的方法，如果存在，直接呼叫，不存在，通過分析動態載入lib或是model
	 * @引數 $param 傳遞過來的變數
	**/
	public function __call($method,$param)
	{
		if($method && method_exists($GLOBALS['app'],$method)){
			return call_user_func_array(array($GLOBALS['app'],$method),$param);
		}else{
			$lst = explode("_",$method);
			if($lst[1] == 'model'){
				$GLOBALS['app']->model($lst[0]);
				call_user_func_array(array($GLOBALS['app'],$method),$param);
			}elseif($lst[1] == 'lib'){
				$GLOBALS['app']->lib($lst[0]);
				return call_user_func_array(array($GLOBALS['app'],$method),$param);
			}
		}
	}

	/**
	 * 屬性過載，讀取不可訪問屬性的值時，嘗試通過這裡過載
	 * @引數 $id $GLOBALS['app']下的屬性
	**/
	public function __get($id)
	{
		$lst = explode("_",$id);
		if($lst[1] == "model"){
			return $GLOBALS['app']->model($lst[0]);
		}elseif($lst[1] == "lib"){
			return $GLOBALS['app']->lib($lst[0]);
		}
		return $GLOBALS['app']->$id;
	}

	/**
	 * 屬性過載，當對不可訪問屬性呼叫
	 * @引數 $id $GLOBALS['app']下的屬性
	**/
	public function __isset($id)
	{
		return $this->__get($id);
	}
}

/**
 * 初始化第三方類，如果第三方類繼承該類，則可以直接使用一些變數，而無需再定位及初化，
 * 繼承該類後可以直接使用下類屬性：<br />
 *     1. $this->dir_root，程式根目錄<br />
 *     2. $this->dir_phpok，程式框架目錄<br />
 *     3. $this->dir_data，程式資料儲存目錄<br />
 *     4. $this->dir_cache，快取目錄<br />
 *     5. $this->dir_extension，第三方擴充套件類根目錄
**/
class _init_lib
{
	protected $dir_root;
	protected $dir_phpok;
	protected $dir_data;
	protected $dir_cache;
	protected $dir_extension;
	public function __construct()
	{
		$this->dir_root = $GLOBALS['app']->dir_root;
		$this->dir_phpok = $GLOBALS['app']->dir_phpok;
		$this->dir_data = $GLOBALS['app']->dir_data;
		$this->dir_cache = $GLOBALS['app']->dir_cache;
		$this->dir_extension = $GLOBALS['app']->dir_extension;
	}

	protected function dir_root($dir='')
	{
		if($dir){
			$this->dir_root = $dir;
		}
		return $this->dir_root;
	}

	protected function dir_phpok($dir='')
	{
		if($dir){
			$this->dir_phpok = $dir;
		}
		return $this->dir_phpok;
	}

	protected function dir_data($dir='')
	{
		if($dir){
			$this->dir_data = $dir;
		}
		return $this->dir_data;
	}

	protected function dir_cache($dir='')
	{
		if($dir){
			$this->dir_cache = $dir;
		}
		return $this->dir_cache;
	}

	protected function dir_extension($dir='')
	{
		if($dir){
			$this->dir_extension = $dir;
		}
		return $this->dir_extension;
	}
}

/**
 * PHPOK控制器，裡面大部分函式將通過Global功能呼叫核心引挈
**/
class phpok_control extends _init_auto
{
	public function control($id='',$app_id='')
	{
		if(!$id){
			parent::__construct();
			return true;
		}
		return $GLOBALS['app']->control($id,$app_id);
	}
}

/**
 * Model根類，繼承了_into_auto類，支援直接呼叫核心引挈裡的資訊
**/
class phpok_model extends _init_auto
{
	/**
	 * 站點ID，所有的Model類都可以直接用這個
	**/
	public $site_id = 0;

	/**
	 * 緩衝區，用於即時快取資訊，同一條SQL多次請求時直接從緩衝區獲取，注意需要手動更新資料
	**/
	protected $_buffer = array();

	/**
	 * 動態載入Model
	 * @引數 $id 為空用於繼承父建構函式，不為空時動態載入其他model類，即實現了多個model的互相呼叫
	**/
	public function model($id='')
	{
		if(!$id){
			parent::__construct();
			if($this->app_id == 'admin' && $this->session->val('admin_site_id')){
				$this->site_id = $this->session->val('admin_site_id');
			}
			if($this->app_id != 'admin' && $this->site['id']){
				$this->site_id = $this->site['id'];
			}
		}else{
			return $GLOBALS['app']->model($id);
		}
	}

	/**
	 * 定義站點ID，用於實現同一個程式裡有多個站點
	 * @引數 $site_id，站點ID
	**/
	public function site_id($site_id=0)
	{
		$this->site_id = $site_id;
	}

	/**
	 * 動態獲取下一個排序
	 * @引數 $rs 陣列或數字，為數字時返回該值+10後的數字，為陣列時，嘗試獲取taxis或sort對應的數值，並返回+10後的數字，為空時返回10
	 * @返回 數字，下一個排序
	**/
	protected function return_next_taxis($rs='')
	{
		if($rs){
			if(is_array($rs)){
				$taxis = $rs['taxis'] ? $rs['taxis'] : $rs['sort'];
			}else{
				$taxis = $rs;
			}
			$taxis = intval($taxis);
			return intval($taxis+5);
		}else{
			return 5;
		}
	}

	/**
	 * 獲取或儲存緩衝區資訊
	 * @引數 $sql 緩衝區標識
	 * @引數 $data 要儲存的快取資訊
	**/
	protected function _buffer($sql,$data='')
	{
		$id = "sql".md5($sql);
		if(isset($data) && $data != ''){
			$this->_buffer[$id] = $data;
			return true;
		}
		if(isset($this->_buffer[$id])){
			return $this->_buffer[$id];
		}
		return false;
	}
}

/**
 * 初始化外掛類，即在外掛中，也可以使用$this->model或是$this->lib等方法來獲取相應的核心資訊
**/
class phpok_plugin extends _init_auto
{
	public function plugin()
	{
		parent::__construct();
	}

	/**
	 * 返回外掛的ID
	**/
	final public function _id()
	{
		$name = get_class($this);
		$lst = explode("_",$name);
		unset($lst[0]);
		return implode("_",$lst);
	}

	/**
	 * 返回外掛資訊
	 * @引數 $id 外掛ID，為空時嘗試讀取當前外掛ID
	 * @返回 陣列 id外掛ID，title名稱，author作者，version版本，note說明，param外掛擴充套件儲存的資料，這個是一個陣列，path外掛路徑
	 * @更新時間 
	**/
	final public function _info($id='')
	{
		if(!$id){
			$id = $this->_id();
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs){
			$rs = array('id'=>$id);
		}
		if($rs['param']){
			$rs['param'] = unserialize($rs['param']);
		}
		$rs['path'] = $this->dir_root.'plugins/'.$id.'/';
		return $rs;
	}

	/**
	 * 儲存外掛擴充套件資料，注意，這裡僅儲存外掛的擴充套件資料
	 * @引數 $ext 陣列，要儲存的陣列
	 * @引數 $id 字串，指定的外掛ID，為空嘗試獲取當前外掛ID
	**/
	final public function _save($ext,$id='')
	{
		if(!$id){
			$id = $this->_id();
		}
		if(!$id){
			return false;
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs){
			return false;
		}
		$info = ($ext && is_array($ext)) ? serialize($ext) : '';
		return $this->model('plugin')->update_param($id,$info);
	}

	/**
	 * 返回外掛輸出的HTML資料，請注意，這裡並沒有輸出，只是返回
	 * @引數 $name 模板名稱，帶字尾的模板名稱，相對路徑，系統會依次檢查，具體請看：<b>private function _tplfile()</b>
	 * @引數 $id 字串，指定的外掛ID，為空嘗試獲取當前外掛ID
	 * @返回 正確時返回模板內容，錯誤時返回false 
	**/
	final public function _tpl($name,$id='')
	{
		$file = $this->_tplfile($name,$id);
		if(!$file){
			return false;
		}
		return $this->tpl->fetch($file,'abs-file');
	}

	/**
	 * 輸出的HTML資料到裝置上，請注意，這裡是輸出，不是返回，同時也要注意，這裡沒有中止
	 * @引數 $name 模板名稱，帶字尾的模板名稱，相對路徑，系統會依次檢查，具體請看：<b>private function _tplfile()</b>
	 * @引數 $id 字串，指定的外掛ID，為空嘗試獲取當前外掛ID
	 * @返回 正確時輸出HTML，錯誤時跳過沒有任何輸出
	**/
	final public function _show($name,$id='')
	{
		$info = $this->_tpl($name,$id);
		if($info){
			echo $info;
		}
	}

	/**
	 * 輸出的HTML資料到裝置上並中斷後續操作，請注意，這裡是輸出，有中斷
	 * @引數 $name 模板名稱，帶字尾的模板名稱，相對路徑，系統會依次檢查，具體請看：<b>private function _tplfile()</b>
	 * @引數 $id 字串，指定的外掛ID，為空嘗試獲取當前外掛ID
	 * @返回 正確時輸出HTML，錯誤時跳過沒有任何輸出
	**/
	final public function _view($name,$id='')
	{
		$file = $this->_tplfile($name,$id);
		if($file){
			$this->tpl->display($file,'abs-file');
			exit;
		}
	}

	/**
	 * 按順序讀取挑出最近的一個模板
	 * @引數 $name 模板名稱，帶字尾的模板名稱，相對路徑，系統會依次檢查這些檔案是否存在，只要有一個符合要求即可<br />
	 * 1. 當前模板目錄/plugins/外掛ID/template/$name<br />
	 * 2. 當前模板目錄/plugins/外掛ID/$name<br />
	 * 3. 當前模板目錄/外掛ID/$name<br />
	 * 4. 當前模板目錄/plugins_外掛ID_$name<br />
	 * 5. 當前模板目錄/外掛ID_$name<br />
	 * 6. 程式根目錄/plugins/外掛ID/template/$name<br />
	 * 7. 程式根目錄/plugins/外掛ID/$name
	 * @引數 $id 字串，指定的外掛ID，為空嘗試獲取當前外掛ID
	 * @返回 正確時輸出HTML，錯誤時跳過沒有任何輸出
	**/
	private function _tplfile($name,$id='')
	{
		if(!$id){
			$id = $this->_id();
		}
		$list = array();
		$list[0] = $this->dir_root.$this->tpl->dir_tpl.'plugins/'.$id.'/template/'.$name;
		$list[1] = $this->dir_root.$this->tpl->dir_tpl.'plugins/'.$id.'/'.$name;
		$list[2] = $this->dir_root.$this->tpl->dir_tpl.$id.'/'.$name;
		$list[3] = $this->dir_root.$this->tpl->dir_tpl.'plugins_'.$id.'_'.$name;
		$list[4] = $this->dir_root.$this->tpl->dir_tpl.$id.'_'.$name;
		$list[5] = $this->dir_root.'plugins/'.$id.'/template/'.$name;
		$list[6] = $this->dir_root.'plugins/'.$id.'/tpl/'.$name;
		$list[7] = $this->dir_root.'plugins/'.$id.'/'.$name;
		$file = false;
		foreach($list as $key=>$value){
			if(file_exists($value)){
				$file = $value;
				break;
			}
		}
		return $file;
	}

	/**
	 * 舊版本寫法，與之對應新的寫法是：$this->_id()
	**/
	protected function plugin_id()
	{
		return $this->_id();
	}

	/**
	 * 舊版本寫法，與之對應新的寫法是：$this->_info()
	**/
	protected function plugin_info($id='')
	{
		return $this->_info();
	}

	/**
	 * 舊版本寫法，與之對應新的寫法是：$this->_save()
	**/
	protected function plugin_save($ext,$id="")
	{
		return $this->_save($ext,$id);
	}

	/**
	 * 舊版本寫法，與之對應新的寫法是：$this->_tpl()
	**/
	protected function plugin_tpl($name,$id='')
	{
		return $this->_tpl($name,$id);
	}

	/**
	 * 舊版本寫法，與之對應新的寫法是：$this->_show()
	**/
	protected function show_tpl($name,$id='')
	{
		$this->_show($name,$id);
	}

	/**
	 * 舊版本寫法，與之對應新的寫法是：$this->_view()
	**/
	protected function echo_tpl($name,$id='')
	{
		$this->_view($name,$id);
	}
}

/**
 * 安全登出全域性變數
**/
unset($_ENV, $_SERVER['MIBDIRS'],$_SERVER['MYSQL_HOME'],$_SERVER['OPENSSL_CONF'],$_SERVER['PHP_PEAR_SYSCONF_DIR'],$_SERVER['PHPRC'],$_SERVER['SystemRoot'],$_SERVER['COMSPEC'],$_SERVER['PATHEXT'], $_SERVER['WINDIR'],$_SERVER['PATH']);

$app = new _init_phpok();
include_once($app->dir_phpok."phpok_helper.php");
$app->init_site();
$app->init_view();

/**
 * 引用全域性 app
**/
function init_app(){
	return $GLOBALS['app'];
}

/**
 * 核心函式，phpok_head_js，用於載入自定義擴充套件中涉及到的js
**/
function phpok_head_js()
{
	$debug = $GLOBALS['app']->config['debug'];
	$jslist = $GLOBALS['app']->jslist;
	if(!$jslist || !is_array($jslist)){
		return false;
	}
	$jslist = array_unique($jslist);
	$html = "";
	foreach($jslist as $key=>$value){
		if($debug){
			$value .= strpos($value,'?') !== false ? '&_noCache='.time() : '?_noCache='.time();
		}
		$html .= '<script type="text/javascript" src="'.$value.'" charset="utf-8"></script>'."\n";
	}
	return $html;
}

/**
 * 核心函式，phpok_head_css，用於載入自定義擴充套件中涉及到的css
**/
function phpok_head_css()
{
	$debug = $GLOBALS['app']->config['debug'];
	$csslist = $GLOBALS['app']->csslist;
	if(!$csslist || !is_array($csslist)){
		return false;
	}
	$csslist = array_unique($csslist);
	$html = "";
	foreach($csslist as $key=>$value){
		if($debug){
			$value .= strpos($value,'?') !== false ? '&_noCache='.time() : '?_noCache='.time();
		}
		$html .= '<link rel="stylesheet" type="text/css" href="'.$value.'" charset="utf-8" />'."\n";
	}
	return $html;
}

/**
 * 語言包變數格式化，$info將轉化成系統的語言包，同是將$info裡的帶{變數}替換成$var裡傳過來的資訊
 * @引數 $info 字串，要替變的字串用**{}**包圍，包圍的內容對應$var裡的$key
 * @引數 $replace 陣列，要替換的字元。
 * @返回 字串，$info為空返回false
 * @更新時間 2016年06月05日
**/
function P_Lang($info,$replace='')
{
	$status = isset($GLOBALS['app']->config['multiple_language']) ? $GLOBALS['app']->config['multiple_language'] : false;
	if($status){
		return $GLOBALS['app']->lang_format($info,$replace);
	}
	if($replace && is_string($replace)){
		$replace  = unserialize($replace);
	}
	if($replace && is_array($replace)){
		foreach($replace as $key=>$value){
			$info = str_replace(array('{'.$key.'}','['.$key.']'),$value,$info);
		}
	}
	return $info;
}

/**
 * 核心函式，動態加CSS
**/
function phpok_add_css($file='')
{
	$GLOBALS['app']->addcss($file);
}

/**
 * 核心函式，動態加js
**/
function phpok_add_js($file='')
{
	$GLOBALS['app']->addjs($file);
}

/**
 * 執行動作
**/
$app->action();