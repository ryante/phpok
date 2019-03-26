<?php
/**
 * 表單選項管理器
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年01月20日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class form_lib
{
	//表單物件
	public $cls;
	public $appid = 'www';
	public $dir_form;

	//建構函式
	public function __construct()
	{
		$this->dir_form = $GLOBALS['app']->dir_phpok.'form/';
		$appid = $GLOBALS['app']->appid;
		$this->appid = $appid == 'admin' ? 'admin' : 'www';
	}

	public function appid($appid='www')
	{
		$this->appid = $appid;
	}

	/**
	 * 獲取物件
	 * @引數 $name 表單名稱
	**/
	public function cls($name)
	{
		$class_name = $name.'_form';
		if($this->$class_name){
			return $this->$class_name;
		}
		if(!file_exists($this->dir_form.$class_name.'.php')){
			return false;
		}
		include_once($this->dir_form.$class_name.'.php');
		$this->$class_name = new $class_name();
		return $this->$class_name;
	}

	private function _obj($rs)
	{
		if(!$rs || !$rs['form_type']){
			return false;
		}
		return $this->cls($rs['form_type']);
	}

	/**
	 * 表單配置資訊
	 * @引數 $id 表單型別名
	**/
	public function config($id)
	{
		$obj = $this->cls($id);
		if(!$obj){
			return false;
		}
		$mlist = get_class_methods($obj);
		if(in_array('phpok_config',$mlist)){
			$obj->phpok_config();
			exit;
		}
		if(in_array('config',$mlist)){
			$obj->config();
			exit;
		}
		exit(P_Lang('檔案異常'));
	}

	/**
	 * 格式化表單資訊
	 * @引數 $rs 要格式化的內容
	**/
	public function format($rs)
	{
		$obj = $this->_obj($rs);
		if(!$obj){
			return $rs;
		}
		$mlist = get_class_methods($obj);
		if(in_array('phpok_format',$mlist)){
			$info = $obj->phpok_format($rs,$this->appid);
			$rs['html'] = $info;
			return $rs;
		}
		if(in_array('format',$mlist)){
			$info = $obj->format($rs);
			$rs['html'] = $info;
			return $rs;
		}
		return $rs;
	}

	/**
	 * 獲取內容資訊
	 * @引數 $rs 陣列，欄位屬性
	 * @返回 
	 * @更新時間 
	**/
	public function get($rs)
	{
		$obj = $this->_obj($rs);
		if(!$obj){
			return false;
		}
		$mlist = get_class_methods($obj);
		if(in_array('phpok_get',$mlist)){
			return $obj->phpok_get($rs,$this->appid);
		}
		if(in_array('get',$mlist)){
			return $obj->get($rs);
		}
		return $GLOBALS['app']->get($rs['identifier'],$rs['format']);
	}

	/**
	 * 輸出內容資訊
	 * @引數 $rs 內容
	 * @引數 $value 值
	**/
	public function show($rs,$value='')
	{
		if(!$rs){
			return $value;
		}
		if($value != ''){
			$rs['content'] = $value;
		}
		$obj = $this->_obj($rs);
		if(!$obj){
			return $value;
		}
		$mlist = get_class_methods($obj);
		if(in_array('phpok_show',$mlist)){
			return $obj->phpok_show($rs,$this->appid);
		}
		if(in_array('show',$mlist)){
			if(!$value) $value = $rs['content'];
			return $obj->show($rs,$value);
		}
		return $value;
	}


	//彈出視窗，用於建立欄位
	function open_form_setting($saveurl)
	{
		if(!$saveurl) return false;
		$GLOBALS['app']->assign('saveUrl',$saveurl);
		//讀取格式化型別
		$field_list = $GLOBALS['app']->model('form')->field_all();
		$form_list = $GLOBALS['app']->model('form')->form_all();
		$format_list = $GLOBALS['app']->model('form')->format_all();
		$GLOBALS['app']->assign('fields',$field_list);
		$GLOBALS['app']->assign('formats',$format_list);
		$GLOBALS['app']->assign('forms',$form_list);
		//建立欄位
		$GLOBALS['app']->view("field_create");
	}

	//格式化值，對應的表單內容
	function info($val,$rs)
	{
		if($val == '' || !$rs || !is_array($rs)) return $val;
		//如果只是普通的文字框
		if($rs['form_type'] == 'text' || $rs['form_type'] == 'password')
		{
			return $val;
		}
		//如果是程式碼編輯器 或是 文字區
		if($rs['form_type'] == 'code_editor' || $rs['form_type'] == 'textarea')
		{
			return $val;
		}
		//如果是編輯器
		if($rs['form_type'] == 'editor')
		{
			return $GLOBALS['app']->lib('ubb')->to_html($val);
		}
		//如果是單選框
		if($rs['form_type'] == 'radio')
		{
			if(!$rs["option_list"]) $rs['option_list'] = 'default:0';
			$opt_list = explode(":",$rs["option_list"]);
			$rslist = opt_rslist($opt_list[0],$opt_list[1],$rs['ext_select']);
			//如果內容為空，則返回空資訊
			if(!$rslist) return false;
			foreach($rslist AS $key=>$value)
			{
				//
			}
		}
		return $val;
	}

	/**
	 * 按需裝載CSS和JS檔案
	 * @引數 $rs 要載入的物件
	**/
	public function cssjs($rs='')
	{
		if($rs && is_array($rs)){
			$obj = $this->_obj($rs);
			if(!$obj){
				return false;
			}
			$mlist = get_class_methods($obj);
			if(in_array('cssjs',$mlist)){
				$obj->cssjs();
			}
			return true;
		}
		$list = $GLOBALS['app']->model('form')->form_all();
		foreach($list as $key=>$value){
			$obj = $this->_obj(array('form_type'=>$key));
			$mlist = get_class_methods($obj);
			if(in_array('cssjs',$mlist)){
				$obj->cssjs();
			}
		}
		return true;
	}
}