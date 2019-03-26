<?php
/**
 * PHPOK模板引挈，簡單實用
 * @package phpok\framework
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年06月21日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class phpok_template
{
	public $tpl_id = 1;
	public $dir_tpl = "tpl/";
	public $dir_cache = "../_cache/";
	public $dir_php = "./";
	public $dir_root = "./";
	public $dir_tplroot = 'tpl/';
	public $path_change = "";
	public $refresh_auto = true;
	public $refresh = false;
	public $tpl_ext = "html";
	public $html_head = '<?php if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");} ?>';
	public $tpl_value;
	private $cache_config;
	private $web_folder;
	private $is_mobile = false;

	public function __construct($config=array())
	{
		$this->config($config);
	}

	/**
	 * 配置全域性引數
	 * @引數 $config 陣列
	**/
	public function config($config=array())
	{
		if($config["id"]){
			$this->tpl_id = $config["id"];
			if(!$this->cache_config[$config['id']]){
				$this->cache_config[$config['id']] = $config;
			}
		}
		if($config["dir_tpl"]){
			$this->dir_tpl = $config["dir_tpl"];
			$tmp = basename($this->dir_tpl);
			if(substr($tmp,-7) == '_mobile'){
				$tmp = substr($tmp,0,-7);
				$this->is_mobile = true;
			}
			$this->web_folder = $tmp;
		}
		if($config['dir_tplroot']){
			$this->dir_tplroot = $config['dir_tplroot'];
		}
		if($config["dir_cache"]){
			$this->dir_cache = $config["dir_cache"];
		}
		if($config["dir_php"]){
			$this->dir_php = $config["dir_php"];
		}
		if($config["dir_root"]){
			$this->dir_root = $config["dir_root"];
		}
		if($config["path_change"]){
			$this->path_change = $config["path_change"];
		}
		$this->refresh_auto = $config["refresh_auto"] ? true : false;
		$this->refresh = $config["refresh"] ? true : false;
		$this->tpl_ext = $config["tpl_ext"] ? $config["tpl_ext"] : "html";
		if($this->dir_tpl && substr($this->dir_tpl,-1) != "/"){
			$this->dir_tpl .= "/";
		}
		if($this->dir_cache && substr($this->dir_cache,-1) != "/"){
			$this->dir_cache .= "/";
		}
	}

	/**
	 * 附加變數
	 * @引數 $var 變數名，字串
	 * @引數 $val 變數值
	**/
	public function assign($var,$val="")
	{
		if(!$var || (is_array($var) && $val)){
			return false;
		}
		if(is_array($var)){
			foreach($var as $key=>$value){
				$this->tpl_value[$key] = $value;
			}
		}else{
			if(isset($val) && !$val && isset($this->tpl_value[$var])){
				unset($this->tpl_value[$var]);
				return true;
			}
			$this->tpl_value[$var] = $val;
		}
	}

	/**
	 * 讀取變數名內容
	 * @引數 $var 變數名
	**/
	public function val($var)
	{
		return $this->tpl_value[$var];
	}

	/**
	 * 登出變數
	 * @引數 $var 要登出的變數名，留空登出全部變數
	**/
	public function unassign($var='')
	{
		if(!$var){
			unset($this->tpl_value);
			return false;
		}
		if(!is_array($var) && $this->tpl_value[$var]){
			unset($this->tpl_value[$var]);
			return true;
		}
		foreach((array)$var as $key=>$value){
			if($this->tpl_value[$key]){
				unset($this->tpl_value[$key]);
			}
		}
		return true;
	}

	/**
	 * 變更路徑
	 * @引數 $val 要變更的路徑的字串，例如：css,images
	**/
	public function path_change($val="")
	{
		$this->path_change = $val;
	}

	/**
	 * 輸出編譯後的模板資訊
	 * @引數 $tpl是指模板檔案，支援子目錄
	 * @引數 $type 型別，可選值為：file 指相對路徑，不帶字尾，file-ext 指相對路徑，帶字尾，content 或 msg 指純模板內容，abs-file 指帶字尾的絕對路徑
	 * @引數 $path_format 是否格式化
	**/
	public function output($tpl,$type="file",$path_format=true)
	{
		if(!$tpl){
			$this->error(P_Lang('模板資訊為空'));
		}
		if(strpos($tpl,':') !== false && $type == 'file'){
			$tmp = explode(":",$tpl);
			$tpl = $tmp[1];
			if($tmp[0] && $tmp[0] != $this->tpl_id){
				$chk = $this->_read_config($tmp[0]);
				if($chk){
					$this->config($chk);
				}
			}
		}
		$comp_id = $this->comp_id($tpl,$type);
		if(!$comp_id){
			$this->error(P_Lang('沒有指定模板源'));
		}
		$this->compiling($comp_id,$tpl,$type,$path_format);
		$this->assign("session",$_SESSION);
		$this->assign("get",$_GET);
		$this->assign("post",$_POST);
		$this->assign("cookie",$_COOKIE);
		if($this->path_change){
			$tmp_path_list = explode(",",$this->path_change);
			$tmp_path_list = array_unique($tmp_path_list);
			foreach($tmp_path_list as $key=>$value){
				$value = trim($value);
				if($value == ''){
					continue;
				}
				$this->assign("_".$value,$value);
			}
		}
		if(!$this->tpl_value || !is_array($this->tpl_value)){
			$this->tpl_value = array();
		}
		$varlist = (is_array($GLOBALS))?array_merge($GLOBALS,$this->tpl_value):$this->tpl_value;
		extract($varlist);
		include($this->dir_cache.$comp_id);
	}

	/**
	 * 取得內容，不直接輸出，引數output
	 * @引數 $tpl是指模板檔案，支援子目錄
	 * @引數 $type 型別，可選值為：file 指相對路徑，不帶字尾，file-ext 指相對路徑，帶字尾，content 或 msg 指純模板內容，abs-file 指帶字尾的絕對路徑
	 * @引數 $path_format 是否格式化
	**/
	public function fetch($tpl,$type="file",$path_format=true)
	{
		ob_start();
		$this->output($tpl,$type,$path_format);
		$msg = ob_get_contents();
		ob_end_clean();
		return $msg;
	}

	/**
	 * 取得編譯後的檔案ID
	**/
	public function comp_id($tpl,$type="file")
	{
		$string = $this->tpl_id."_";
		if($type == "file" || $type == "file-ext"){
			$tpl = strtolower($tpl);
			$tpl = str_replace("/","_folder_",$tpl);
			$string .= $tpl;
		}elseif($type == "abs-file"){
			$string .= substr(md5($tpl),9,16);
			$string .= "_abs";
		}else{
			$string .= substr(md5($tpl),9,16);
			$string .= "_c";
		}
		$string .= ".php";
		return $string;
	}


	/**
	 * 輸出 HTML 資訊並中止後續執行
	 * @引數 $tpl是指模板檔案，支援子目錄
	 * @引數 $type 型別，可選值為：file 指相對路徑，不帶字尾，file-ext 指相對路徑，帶字尾，content 或 msg 指純模板內容，abs-file 指帶字尾的絕對路徑
	 * @引數 $path_format 是否格式化
	**/
	public function display($tpl,$type="file",$path_format=true)
	{
		$this->output($tpl,$type,$path_format);
		exit;
	}

	/**
	 * 編譯模板內容，通過正則替換自己需要的
	 * @引數 $compiling_id，生成的編譯檔案ID
	 * @引數 $tpl，模板原始檔
	 * @引數 $type，模板型別
	 * @引數 $path_format 是否格式化
	**/
	private function compiling($compiling_id,$tpl,$type="file",$path_format=true)
	{
		$is_refresh = false;
		if(!is_file($this->dir_cache.$compiling_id) || $this->refresh){
			$is_refresh = true;
		}
		if(!in_array($type,array('file','file-ext','abs-file'))){
			$is_refresh = true;
		}
		if(in_array($type,array('file','file-ext','abs-file'))){
			$tplfile = $this->_getfile($tpl,$type);
			if(!$tplfile && $this->refresh_auto){
				$this->error(P_Lang('模板檔案[tplfile]不存在',array('tplfile'=>$tpl)));
			}
			if(!$is_refresh){
				$time = filemtime($this->dir_cache.$compiling_id);
				if($this->refresh_auto && filemtime($tplfile) > $time){
					$is_refresh = true;
					unset($time);
				}
			}
		}
		if(!$is_refresh){
			return true;
		}
		if($tplfile && in_array($type,array('file','file-ext','abs-file'))){
			$html_content = file_get_contents($tplfile);
		}else{
			$html_content = $tpl;
		}
		if(!$html_content){
			$this->error(P_Lang('不支援空內容是模板，請檢查'));
		}
		$php_content = $this->html_to_php($html_content,$path_format);
		$newarray = array('<?php echo $app->plugin_html_ap("phpokhead");?></head>','<?php echo $app->plugin_html_ap("phpokbody");?></body>');
		$php_content = str_replace(array('</head>','</body>'),$newarray,$php_content);
		file_put_contents($this->dir_cache.$compiling_id,$this->html_head.$php_content);
		return true;
	}

	private function _getfile($tpl,$type='file')
	{
		if($type == 'abs-file'){
			return $tpl;
		}
		if(!in_array($type,array('file','file-ext'))){
			return $tpl;
		}
		$file = $tpl;
		if($type == 'file'){
			$file = $tpl.'.'.$this->tpl_ext;
		}
		$tplfile = $this->dir_root.$this->dir_tpl.$file;
		if(is_file($tplfile)){
			return $tplfile;
		}
		if($this->is_mobile){
			$tplfile = $this->dir_root.$this->dir_tplroot.'/'.$this->web_folder.'/'.$file;
			if(is_file($tplfile)){
				return $tplfile;
			}
			if(substr($file,-4) != 'html'){
				$tmplist = explode(".",$file);
				if(count($tmplist) > 2){
					$file = substr($file,0,-(strlen(end($tmplist))+1)).'.html';
				}else{
					$file = $tmplist[0].'.html';
				}
			}
			$tplfile = $this->dir_root.$this->dir_tplroot.'/www_mobile/'.$file;
			if(is_file($tplfile)){
				return $tplfile;
			}
		}
		if(substr($file,-4) != 'html'){
			$tmplist = explode(".",$file);
			if(count($tmplist) > 2){
				$file = substr($file,0,-(strlen(end($tmplist))+1)).'.html';
			}else{
				$file = $tmplist[0].'.html';
			}
		}
		$tplfile = $this->dir_root.$this->dir_tplroot.'/www/'.$file;
		if(is_file($tplfile)){
			return $tplfile;
		}
		return false;
	}

	/**
	 * 前端HTML裡Debug呼叫
	 * @引數 $info 陣列，要除錯的引數
	**/
	public function html_debug($info)
	{
		if(!$info || !is_array($info) || !$info[1] || !trim($info[1])){
			return false;
		}
		$info = '$'.$info[1];
		$info = stripslashes(trim($info));
		$info = $this->str_format($info);
		return '<?php echo "<pre>".print_r('.$info.',true)."</pre>";?>';
	}

	/**
	 * 語言包變數替換
	 * @引數 $info 要替換的語言包字串
	**/
	private function lang_replace($info)
	{
		if(!$info || !is_array($info) || !$info[1] || !trim($info[1])) return '';
		$info = $info[1];
		$info = trim(str_replace(array("'",'"'),'',$info));
		$lst = explode("|",$info);
		$param = false;
		if($lst[1]){
			$tmp = explode(",",$lst[1]);
			foreach($tmp as $key=>$value){
				$tmp2 = explode(":",$value);
				if(!$param){
					$param = array();
				}
				if(substr($tmp2[1],0,1) == '$'){
					$tmp2[1] = '\'.'.$this->str_format($tmp2[1],false,false).'.\'';
				}
				$param[$tmp2[0]] = '<span style="color:red">'.$tmp2[1].'</span>';
			}
		}
		if($param){
			$string = "array(";
			$i=0;
			foreach($param as $key=>$value){
				if($i>0){
					$string .= ",";
				}
				$string .= "'".$key."'=>'".$value."'";
				$i++;
			}
			$string .= ")";
			return '<?php echo P_Lang("'.stripslashes($lst[0]).'",'.$string.');?>';
		}
		return '<?php echo P_Lang("'.stripslashes($info).'");?>';
	}

	/**
	 * 正則替換，html 轉 php
	 * @引數 $content 要轉換的內容
	 * @引數 $path_format 路徑變更
	**/
	private function html_to_php($content,$path_format=true)
	{
		//第一步，整理模板中的路徑問題
		if($this->path_change && $path_format){
			$tmp_path_list = explode(",",$this->path_change);
			$tmp_path_list = array_unique($tmp_path_list);
			foreach($tmp_path_list as $key=>$value){
				$value = trim($value);
				if(!$value){
					continue;
				}
				$content = str_replace($value."/",$this->dir_tpl.$value."/",$content);
			}
		}
		//正則替換內容問題
		$content = preg_replace_callback('/<!--\s+head\s+(.+)\s+-->/isU',array($this,'head_php'),$content);
		$content = preg_replace_callback('/<!--\s+plugin\s*(.*)\s+-->/isU',array($this,'plugin_php'),$content);
		$content = preg_replace('/(\{|<!--)\s*(\/if|end|\/foreach|\/for|\/while|\/loop)\s*(\}|-->)/isU','<?php } ?>',$content);
		$content = preg_replace('/(\{|<!--)\s*(else)\s*(\}|-->)/isU','<?php } else { ?>',$content);
		$content = preg_replace('/<!--\s*php\s*-->/isU','<?php',$content);
		$content = preg_replace('/<!--\s*\/\s*php\s*-->/isU','?>',$content);
		$content = preg_replace_callback('/\{debug\s+\$(.+)\}/isU',array($this,'html_debug'),$content);
		//語言包替換
		$content = preg_replace_callback('/\{lang\s*(.+)\}/isU',array($this,'lang_replace'),$content);
		//內建標籤替換
		$content = preg_replace_callback('/(\{|<!--\s*)(arclist|arc|subcate|catelist|cate|project|sublist|parent|plist|fields|user|userlist)[:]*([\w\$]*)\s+(.+)(\}|\s*-->)/isU',array($this,'data_php'),$content);
		$content = preg_replace_callback('/(\{|<!--\s*)\/(arclist|arc|catelist|cateinfo|subcate|project|sublist)[:]*([a-zA-Z\_0-9\$]*)(\}|\s*-->)/isU',array($this,'undata_php'),$content);
		$content = preg_replace_callback('/(\{|<!--\s*)unset\s*(:|\(|=)\s*([^\)]+)[\)]*(\}|\}|\s*-->)/isU',array($this,'undata_php'),$content);
		//迴圈語法
		$content = preg_replace_callback('/(\{|<!--\s*)\$([a-zA-Z0-9_\$\[\]\'\\\"\.\-]{1,60})\s+AS\s+(.+)(\}|\s*-->)/isU',array($this,'_foreach_php_ex_doller'),$content);
		$content = preg_replace_callback('/(\{|<!--\s*)foreach\s*\(\s*(.+)\s+AS\s+(.+)\s*\)\s*(\}|\s*-->)/isU',array($this,'_foreach_php_in_doller'),$content);
		$content = preg_replace_callback('/(\{|<!--\s*)loop\s+(.+)(\}|\s*-->)/isU',array($this,'_loop_php'),$content);
		$content = preg_replace_callback('/(\{|<!--\s*)(while|for)\s*\(\s*(.+)\s*\)\s*(\}|\s*-->)/isU',array($this,'_for_while_php'),$content);
		$content = preg_replace_callback('/(\{|<!--\s*)(while|for)\s+(.+)(\}|\s*-->)/isU',array($this,'_for_while_php'),$content);
		//條件判斷
		$content = preg_replace_callback('/(\{|<!--\s*)(if|else\s*if)\s*\(\s*(.+)\s*\)\s*(\}|\s*-->)/isU',array($this,'_if_php'),$content);
		$content = preg_replace_callback('/(\{|<!--\s*)(if|else\s*if)\s+(.+)(\}|\s*-->)/isU',array($this,'_if_php'),$content);
		//檔案包含
		$content = preg_replace_callback('/(\{|<!--\s*)include\s+(.+)(\}|\s*-->)/isU',array($this,'_include_php'),$content);
		$content = preg_replace_callback('/(\{|<!--\s*)inc\s*(:|=)\s*(.+)(\}|\s*-->)/isU',array($this,'_inc_php'),$content);
		//單行PHP程式碼
		$content = preg_replace_callback('/(\{|<!--)\s*(run|php)\s*(:|\s+)\s*(.+)\s*(\}|-->)/isU',array($this,'_php_runing'),$content);
		//PHPOK4的網址寫法
		$content = preg_replace_callback('/\{url\s+(.+)\/\}/isU',array($this,'_url_php'),$content);
		$content = preg_replace_callback('/\{ajaxurl\s+(.+)\/\}/isU',array($this,'_ajaxurl_php'),$content);
		//PHPOK4外調函式的寫法
		$content = preg_replace_callback('/\{func\s+(.+)\}/isU',array($this,'_func_php'),$content);
		//基本變數輸出
		$content = preg_replace_callback('/\{\$([a-zA-Z\_].*)\s*\}/isU',array($this,'_echo_php'),$content);
		$content = preg_replace_callback('/\{\s*(:|=|echo\s+)\s*(.+)\}/isU',array($this,'_echo_phpok3'),$content);
		$content = preg_replace('/\{#(.*)#\}/isU','\\1',$content);
		return $content;
	}

	private function _echo_phpok3($info)
	{
		return $this->echo_php($info[2]);
	}

	private function _echo_php($info)
	{
		return $this->echo_php('$'.$info[1]);
	}

	private function _func_php($info)
	{
		return $this->func_php($info[1]);
	}

	private function _ajaxurl_php($info)
	{
		return $this->ajaxurl_php($info[1]);
	}

	private function _url_php($info)
	{
		return $this->url_php($info[1]);
	}

	private function _php_runing($info)
	{
		return $this->php_runing($info[4]);
	}

	private function _inc_php($info)
	{
		return $this->inc_php($info[3]);
	}

	private function _include_php($info)
	{
		return $this->include_php($info[2]);
	}

	private function _if_php($info)
	{
		return $this->if_php($info[2],$info[3]);
	}

	private function _for_while_php($info)
	{
		return $this->for_while_php($info[2],$info[3]);
	}

	private function _loop_php($info)
	{
		return $this->loop_php($info[2]);
	}

	public function url_encode($info)
	{
		return rawurlencode($info[1]);
	}

	/**
	 * 更換頭部資訊
	 * @引數 $string 要格式化的字串
	**/
	public function head_php($string)
	{
		if(!$string || !is_array($string) || !$string[1] || !trim($string[1])){
			return false;
		}
		$string = $string[1];
		$string = stripslashes(trim($string));
		$string = preg_replace_callback("/[\"|']{1}(.+)[\"|']{1}/isU",array($this,'url_encode'),$string);
		$string = preg_replace("/(\x20{2,})/"," ",$string);
		$string = str_replace(" ","&",$string);
		parse_str($string,$list);
		$tmpc = "";
		if($list){
			foreach($list AS $key=>$value){
				$value = $this->str_format($value);
				if($value == 'true' || $value == 'false' || (is_numeric($value) && $value < 65536)){
					$list[$key] = "'".$key."'=>".$value;
				}else{
					$value = substr($value,0,1) == '$' ? $value : '"'.$value.'"';
					$list[$key] = "'".$key."'=>".$value;
				}
			}
			$tmpc = 'array('.implode(",",$list).')';
		}
		$tmpc = stripslashes($tmpc);
		return '<?php echo tpl_head('.$tmpc.');?>';
	}

	/**
	 * PHP 程式碼執行
	 * @引數 $string 要執行的 php 程式碼
	**/
	private function php_runing($string)
	{
		if(!$string || !trim($string)){
			return false;
		}
		$string = trim($string);
		$string = stripslashes($string);
		$string = $this->str_format($string,false,false);
		return '<?php '.$string.';?>';
	}

	/**
	 * 外掛瞄點，暫時不管引數
	 * @引數 $string 外掛變數點
	**/
	private function plugin_php($string)
	{
		if(!$string || !is_array($string) || !$string[1] || !trim($string[1])){
			return false;
		}
		$string = $string[1];
		$string = trim($string);
		$string = str_replace(array("'",'"',"\\","/",' ',"&nbsp;"),'',$string);
		return '<?php echo $app->plugin_html_ap("'.$string.'");?>';
	}

	private function data_php($array)
	{
		$a = $array[2];
		$b = $array[3] ? $array[3] : '';
		$c = $array[4] ? $array[4] : '';
		if(!$a) return '<?php echo "";?>';
		if(!$b) $b = '$list';
		if(substr($b,0,1) != '$') $b = '$'.$b;
		$tmp_c = 'array()';
		if($c)
		{
			$c = preg_replace("/(\x20{2,})/"," ",$c);
			//處理引號裡的空格
			$c = preg_replace("/[\"|']([a-zA-Z\_\-\.,]*)(\s+)([a-zA-Z\_\-\.,]*)[\"|']/isU",'\\1:_:_:-phpok-:_:_:\\3',$c);
			$c = str_replace(" ","&",$c);
			$c = $this->str_format($c);
			parse_str($c,$list);
			if($list)
			{
				$tmp = array();
				foreach($list AS $key=>$value)
				{
					if(!$value) continue;
					$t = substr($value,0,1) == '$' ? $value : '"'.$value.'"';
					$tmp[] = "'".$key."'=>".$t;
				}
				$tmp_c = "array(".implode(",",$tmp).")";
				$tmp_c = str_replace(":_:_:-phpok-:_:_:"," ",$tmp_c);
				$tmp_c = stripslashes($tmp_c);
			}
		}
		$info = '<?php '.$b."=phpok('_".$a."',".$tmp_c.");?>";
		return $info;
	}

	/**
	 * 登出 PHP 資訊
	 * @引數 $b 要登出變數的陣列
	**/
	private function undata_php($b="")
	{
		$b = $b[3];
		if(!$b){
			$b= '$list';
		}
		if(substr($b,0,1) != '$'){
			$b = '$'.$b;
		}
		$b = preg_replace("/(\x20{2,})/"," ",$b);# 去除多餘空格，只保留一個空格
		$b = $this->str_format($b);
		$b = str_replace(" ",",",$b);
		return '<?php unset('.$b.');?>';
	}

	/**
	 * 包含 PHP 檔案
	**/
	private function include_php($string)
	{
		$rs = $this->str_to_list($string);
		if(!$rs){
			return false;
		}
		if(!$rs["tpl"] && !$rs["file"] && !$rs["php"]){
			return false;
		}
		$string = "";
		foreach($rs as $key=>$value){
			if($key != "tpl" && $key != "file" && $key != "_type"){
				if(substr($value,0,1) != '$'){
					$value = '"'.$value.'"';
				}
				$string .= '<?php $this->assign("'.$key.'",'.$value.'); ?>';
			}
		}
		if($rs['file']){
			if(strtolower(substr($rs['file'],-4)) != '.php'){
				$rs['file'] .= '.php';
			}
			if(file_exists($this->dir_php.$rs['file'])){
				
				$string .= '<?php include($this->dir_php."'.$rs['file'].'");?>';
			}
		}
		$type = 'file';
		if($rs['_type'] && in_array($rs['_type'],array('file','file-ext','content','msg','abs-file'))){
			$type = $rs['_type'];
		}
		if($rs["tpl"]){
			$string .= '<?php $this->output("'.$rs["tpl"].'","'.$type.'"); ?>';
		}
		return $string;
	}

	private function inc_php($string)
	{
		if(!$string){
			return false;
		}
		$string = 'tpl="'.$string.'"';
		return $this->include_php($string);
	}

	/**
	 * 處理網址
	**/
	private function url_php($string)
	{
		if(!$string || !trim($string)){
			return false;
		}
		$string = trim($string);
		$string = preg_replace("/(\x20{2,})/"," ",$string);# 去除多餘空格，只保留一個空格
		$string = str_replace(" ","&",$string);
		parse_str($string,$list);
		if(!$list || count($list)<1){
			return false;
		}
		$array = array();
		foreach($list as $key=>$value){
			$value = $this->str_format($value);
			if(substr($value,0,1) != '$'){
				$value = "'".$value."'";
			}
			$array[] = "'".$key."'=>".$value;
		}
		return '<?php echo phpok_url(array('.implode(",",$array).'));?>';
	}

	private function ajaxurl_php($string)
	{
		if(!$string || !trim($string)){
			return false;
		}
		global $app;
		$string = trim($string);
		$string = preg_replace("/(\x20{2,})/"," ",$string);# 去除多餘空格，只保留一個空格
		parse_str($string,$list);
		if(!$list || count($list)<1){
			return false;
		}
		$url = $app->url.$app->config['www_file']."?";
		$url.= $app->config['ctrl_id']."=ajax";
		foreach($list AS $key=>$value){
			$value = $this->str_format($value);
			if(substr($value,0,1) == '$'){
				$url .= "&".$key.'=<?php echo rawurlencode('.$value.');?>';
			}else{
				$url .= "&".$key.'='.rawurlencode($value);
			}
		}
		return $url;
	}

	/**
	 * 格式化函式引數
	 * @引數 $string
	 * @返回 帶有PHP標識的字串
	**/
	private function func_php($string)
	{
		if(!$string){
			return false;
		}
		$string = stripslashes(trim($string));
		$string = $this->str_format($string);
		$string = preg_replace_callback("/[\"|']{1}(.+)[\"|']{1}/isU",array($this,'url_encode'),$string);
		$string = preg_replace("/(\x20{2,})/"," ",$string);# 去除多餘空格，只保留一個空格
		$list = explode(" ",$string);
		$func = $list[0];
		if(!$func || !function_exists($func)){
			return false;
		}
		$string = '<?php echo '.$func.'(';
		$newlist = array();
		foreach($list AS $key=>$value){
			if($key<1){
				continue;
			}
			if($value == ''){
				continue;
			}
			$value = $this->str_format($value);
			if($value == ''){
				continue;
			}
			if($value == '0'){
				$newlist[] = 0;
			}else{
				if(substr($value,0,1) != '$'){
					$newlist[] = "'".rawurldecode($value)."'";
				}else{
					$newlist[] = rawurldecode($value);
				}
			}
		}
		$newstring = implode(",",$newlist);
		$string .= $newstring.');?>';
		return $string;
	}

	/**
	 * PHP 輸出
	 * @引數 $string 要輸出的程式碼
	**/
	private function echo_php($string)
	{
		if(!$string){
			return false;
		}
		$string = trim($string);
		$string = stripslashes($string);
		$string = $this->str_format($string,false,false);
		return '<?php echo '.$string.';?>';
	}

	/**
	 * While/For迴圈
	 * @引數 $left 引數 for 或 while
	 * @引數 $string 要迴圈的資料
	**/
	private function for_while_php($left,$string)
	{
		if(!$string || !$left){
			return false;
		}
		$string = trim($string);
		$string = stripslashes($string);
		$string = $this->str_format($string,false);
		$php = '<?php '.$left.'('.$string.'){ ?>';
		return $php;
	}

	private function _foreach_php_ex_doller($array)
	{
		return $this->foreach_php('$'.$array[2],$array[3]);
	}

	private function _foreach_php_in_doller($array)
	{
		return $this->foreach_php($array[2],$array[3]);
	}

	/**
	 * Foreach 簡單迴圈
	 * @引數 $from 資料來源
	 * @引數 $value 要格式化的資料
	**/
	private function foreach_php($from,$value)
	{
		if(!$from || !$value){
			return false;
		}
		$list = explode("=>",$value);
		if($list[1]){
			$key = $list[0];
			$value = $list[1];
		}
		$string = 'from="'.$from.'" value="'.$value.'"';
		if($key){
			$string .= ' key="'.$key.'"';
		}
		return $this->loop_php($string);
	}

	/**
	 * IF 條件操作
	**/
	private function if_php($left_string,$string)
	{
		if(!$string || !$left_string){
			return false;
		}
		$string = trim($string);
		$string = stripslashes($string);
		# 通過正則替換文字中的.為[]
		$string = $this->str_format($string,false,false);
		if(strtolower(substr($left_string,0,4)) == "else"){
			$left_string = '}'.$left_string;
		}
		$php = '<?php '.$left_string.'('.$string.'){ ?>';
		return $php;
	}

	private function get_loop_id($from)
	{
		$from = substr($from,1);
		$from = str_replace(array("['",'["',"']",'"]','$','-'),"_",$from);
		$from = str_replace(array("[","]"),"_",$from);
		if(substr($from,-1) != "_"){
			$from .= "_";
		}
		return $from."id";
	}

	/**
	 * 迴圈資料
	**/
	private function loop_php($string)
	{
		$rs = $this->str_to_list($string,"key,value,from");
		if(!$rs){
			return false;
		}
		if(!$rs || !is_array($rs) || count($rs)<1 || !$rs["from"]){
			return false;
		}
		if(!$rs["id"]){
			$rs["id"] = $this->get_loop_id($rs["from"]);
		}
		$id = $rs["id"];
		if(in_array(substr($id,0,1),array("0","1","2","3","4","5","6","7","8","9"))){
			$id = "phpok_".$id;
		}
		if(substr($id,0,1) == '$'){
			$id = substr($id,1);
		}
		$php  = '<?php $'.$id.'["num"] = 0;';
		$php .= $rs["from"].'=is_array('.$rs["from"].') ? '.$rs["from"].' : array();';
		$php .= '$'.$id.' = array();';
		$php .= '$'.$id.'["total"] = count('.$rs["from"].');';
		if(!$rs["index"]){
			$rs["index"] = 0;
		}
		$index_id = $rs["index"] - 1;
		$php .= '$'.$id.'["index"] = '.$index_id.';';
		if(!$rs["value"]){
			$rs["value"] = '$value';
		}
		$php .= 'foreach('.$rs["from"].' as ';
		if($rs["key"] || $rs["item"]){
			if(!$rs["key"]){
				$rs["key"] = $rs["item"];
			}
			$php .= $rs["key"]."=>";
		}
		$php .= $rs["value"].'){ ';
		$php .= '$'.$id.'["num"]++;';
		$php .= '$'.$id.'["index"]++;';
		$php .= ' ?>';
		return $php;
	}

	/**
	 * 格式化文字，去除首尾引號，將.陣列變成[]模式
	 * @引數 $string，要格式化的文字
	 * @引數 $auto_dollar，前面是否主動新增 $ 符號，預設為否
	 * @引數 $del_mark，是否刪除引號
	**/
	private function str_format($string,$auto_dollar=false,$del_mark=true)
	{
		if($string == ''){
			return false;
		}
		if($string == '0'){
			return '0';
		}
		$string = stripslashes(trim($string));
		if($del_mark){
			if(substr($string,0,1) == '"' || substr($string,0,1) == "'"){
				$string = substr($string,1);
			}
			if(substr($string,-1) == '"' || substr($string,-1) == "'"){
				$string = substr($string,0,-1);
			}
		}
		$string = $this->points_to_array($string);
		if($auto_dollar && substr($string,0,1) != '$'){
			$string = '$'.$string;
		}
		return $string;
	}

	private function points_sort($a,$b)
	{
		$al = strlen($a);
		$bl = strlen($b);
		if ($al == $bl) {
			return 0;
		}
		return ($al < $bl) ? +1 : -1;
	}

	/**
	 * 將字串中的點變成陣列，最多支援5級
	 * @引數 $string 字串
	 * @返回 字串（有帶中括號）
	**/
	private function points_to_array($string)
	{
		if(!$string){
			return false;
		}
		preg_match_all('/\$([\w\_\-\.]+?)/iU',$string,$matches);
		if(!$matches || !$matches[0]){
			return $string;
		}
		$matches[0] = array_unique($matches[0]);
		usort($matches[0],array($this,'points_sort'));
		foreach($matches[0] as $key=>$value){
			$list = explode('.',$value);
			$tmp = '';
			foreach($list as $k=>$v){
				if($k < 1){
					$tmp = $v;
				}else{
					$tmp .= $v ? '['.$v.']' : '.';
				}
			}
			$string = str_replace($value,$tmp,$string);
		}
		$string = preg_replace('/\[([0-9][a-z\_\-]+)\]/iU',"['\\1']",$string);
		$string = preg_replace('/\[(0[0-9]+)\]/iU',"['\\1']",$string);
		$string = preg_replace('/\[([a-z\_\x7f-\xff].*)\]/iU',"['\\1']",$string);
		return $string;
	}

	/**
	 * 字串格式化為陣列
	 * @引數 $string 要格式化的字串
	 * @引數 $need_dollar 
	**/
	private function str_to_list($string,$need_dollar="")
	{
		if(!$string || !trim($string)){
			return false;
		}
		$string = stripslashes(trim($string));
		$string = $this->str_format($string);
		$string = preg_replace_callback("/[\"|']{1}(.+)[\"|']{1}/isU",array($this,'url_encode'),$string);
		$string = preg_replace("/(\x20{2,})/"," ",$string);# 去除多餘空格，只保留一個空格
		$list = explode(" ",$string); # 格式化為陣列
		$rs = array();
		if($need_dollar && !is_array($need_dollar)){
			$need_dollar = explode(",",$need_dollar);
		}else{
			if(!$need_dollar){
				$need_dollar = array();
			}
		}
		foreach($list as $key=>$value){
			$value = trim($value);
			if($value){
				$str = explode("=",$value);
				$str_key = strtolower($str[0]);
				$str_value = $str[1];
				if($str_key && $str_value){
					$str_value = rawurldecode($str_value);
					$str_value = in_array($str_key,$need_dollar) ? $this->str_format($str_value,true) : $this->str_format($str_value,false);
					$rs[$str_key] = $str_value;
				}
			}
		}
		return $rs;
	}

	/**
	 * 模板引挈中的報錯
	**/
	public function error($msg,$title='')
	{
		if(!$msg){
			$msg = "異常請檢查";
		}
		if(!$title){
			$title = '模板錯誤';
		}
		$html = '<!DOCTYPE html>'."\n";
		$html.= '<html>'."\n";
		$html.= '<head>'."\n";
		$html.= '	<meta charset="utf-8" />'."\n";
		$html.= '	<title>'.$title.'</title>'."\n";
		$html.= '</head>'."\n";
		$html.= '<body style="padding:10px;font-size:14px;">'."\n";
		$html.= $msg."\n";
		$html.= '</body>'."\n";
		$html.= '</html>';
		exit($html);
	}

	/**
	 * 取得當前模板框架的字尾
	**/
	public function ext()
	{
		return $this->tpl_ext;
	}

	public function get_tpl($tplname,$default="default")
	{
		$tplfile = $this->dir_tpl.$tplname.".".$this->tpl_ext;
		if(file_exists($tplfile)){
			return $tplname;
		}
		return $default;
	}

	private function _read_config($id)
	{
		if($this->cache_config[$id]){
			return $this->cache_config[$id];
		}
		global $app;
		$rs = $app->model('tpl')->tpl_info($id);
		if(!$rs){
			return false;
		}
		$this->cache_config[$id] = $rs;
		return $rs;
	}

	/**
	 * 檢測檔案是否存在
	 * @引數 $tplname 模板名
	 * @引數 $isext 是否包含字尾
	 * @引數 $ifabs 是否絕對路徑
	**/
	public function check_exists($tplname,$isext=false,$ifabs=false)
	{
		$tplfile = $tplname;
		if(strpos($tplname,':') !== false){
			$tmp = explode(":",$tplname);
			$tplfile = $tmp[1];
			$chk = $this->_read_config($tmp[0]);
			if($tmp[0] != $this->tpl_id && $chk){
				if(!$isext){
					$tplfile .= ".".$chk['tpl_ext'];
				}
				if(!$ifabs){
					$tplfile = $this->dir_root.$chk['dir_tpl'].$tplfile;
				}
				if(file_exists($tplfile)){
					return true;
				}
				return false;
			}
		}
		if(!$isext){
			$tplfile .= ".".$this->tpl_ext;
		}
		if(!$ifabs){
			$tplfile = $this->dir_root.$this->dir_tpl.$tplfile;
		}
		if(file_exists($tplfile)){
			return true;
		}
		return false;
	}

	/**
	 * 檢測模板檔案是否存在，自動檢測帶字尾，不帶字尾，相對路徑，絕對路徑等
	 * @引數 $tplfile 模板名
	**/
	public function check($tplfile)
	{
		if(!$tplfile){
			return false;
		}
		if(strpos($tplfile,':') !== false){
			$tmp = explode(":",$tplname);
			$tplfile = $tmp[1];
			$chk = $this->_read_config($tmp[0]);
			if($chk && $tmp[0] != $this->tpl_id){
				$list = array(0=>$this->dir_root.$chk['dir_tpl'].$tplfile.'.'.$chk['tpl_ext']);
				$list[1] = $this->dir_root.$chk['dir_tpl'].$tplfile;
				$list[2] = $this->dir_root.$tplfile.'.'.$this->tpl_ext;
				$list[3] = $this->dir_root.$tplfile;
				$list[4] = $tplfile.'.'.$this->tpl_ext;
				$list[5] = $tplfile;
				$ok = false;
				foreach($list as $key=>$value){
					if(file_exists($value)){
						$ok = true;
						break;
					}
				}
				return $ok;
			}
		}
		$list = array(0=>$this->dir_root.$this->dir_tpl.$tplfile.".".$this->tpl_ext);
		$list[1] = $this->dir_root.$this->dir_tpl.$tplfile;
		$list[2] = $this->dir_root.$tplfile.'.'.$this->tpl_ext;
		$list[3] = $this->dir_root.$tplfile;
		$list[4] = $tplfile.'.'.$this->tpl_ext;
		$list[5] = $tplfile;
		$ok = false;
		foreach($list as $key=>$value){
			if(file_exists($value)){
				$ok = true;
				break;
			}
		}
		return $ok;
	}
}
