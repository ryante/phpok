<?php
/**
 * 多語言操作檔案
 * @package phpok\framework
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2017年11月27日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class phpok_language
{
	private $_status = true;
	private $_method = 'user';
	private $_id = 'www';
	private $_folder = '';
	private $_lang_id = 'zh_CN';
	private $pomo;
	private $gettext_status = false;

	/**
	 * 初始化
	**/
	public function __construct($langid)
	{
		$this->_method = function_exists('gettext') ? 'gettext' : 'user';
		$this->lang_id($langid);
	}


	public function pomo($folder='')
	{
		if(!$this->_status || $this->_method == 'gettext'){
			return false;
		}
		if(!file_exists($folder.'/pomo/mo.php')){
			return false;
		}
		include_once($folder.'pomo/mo.php');
	}

	/**
	 * 定義繫結的ID/域名
	 * @引數 $id 為空使用 www
	 * @引數 
	 * @引數 
	**/
	public function id($id='')
	{
		if($id && is_string($id)){
			$this->_id = $id;
		}
		return $this->_id;
	}

	/**
	 * 開啟語言包
	**/
	public function open()
	{
		return $this->_status(true);
	}

	/**
	 * 關閉語言包
	**/
	public function close()
	{
		return $this->_status(false);
	}

	/**
	 * 關閉語言包
	**/
	public function stop()
	{
		return $this->_status(false);
	}

	/**
	 * 獲取是否使用語言包
	**/
	public function status()
	{
		return $this->_status;
	}

	/**
	 * 語言包目錄
	 * @引數 $folder 目錄地址，必須存在才有效
	**/
	public function folder($folder='')
	{
		if($folder && is_string($folder) && file_exists($folder)){
			$this->_folder = $folder;
		}
		return $this->_folder;
	}

	/**
	 * 設定語言ID
	 * @引數 $langid 預設是 default，支援其他語言
	**/
	public function lang_id($langid='default')
	{
		if(!$this->_status){
			return true;
		}
		if($langid){
			if($langid == 'default' || $langid == 'cn'){
				$langid = 'zh_CN';
			}
			$this->_lang_id = $langid;
		}
		return $this->_lang_id;
	}

	public function format($info,$var='')
	{
		if(!$info){
			return false;
		}
		if($this->_status && $this->_method == 'user'){
			if(!$this->pomo){
				$this->_init();
			}
			if(!$this->pomo){
				return $this->_format($info,$var);
			}
			$info = $this->pomo->translate($info);
			return $this->_format($info,$var);
		}
		if($this->_status && $this->_method == 'gettext'){
			if(!$this->gettext_status){
				$this->_init();
			}
			if(!$this->gettext_status){
				return $this->_format($info,$var);
			}
			$info2 = gettext($info);
			if(!$info2){
				return $this->_format($info,$var);
			}
			return $this->_format($info2,$var);
		}
		return $this->_format($info,$var);		
	}

	private function _format($info,$var='')
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
	
	private function _status($status='')
	{
		if(is_bool($status)){
			$this->_status = $status;
		}
		if(is_numeric($status)){
			$this->_status = $status ? true : false;
		}
		return $this->_status;
	}



	private function _init()
	{
		if(!$this->_id || !$this->_folder){
			$this->_status = false;
			return true;
		}
		if($this->_method == 'gettext'){
			$this->gettext_status = true;
			putenv('LANG='.$this->_lang_id);
			setlocale(LC_ALL,$this->_lang_id);
			bindtextdomain($this->_id,$this->_folder);
			textdomain($this->_id);
			return true;
		}
		$mofile = $this->_folder.'/'.$this->_lang_id.'/LC_MESSAGES/'.$this->_id.'.mo';
		if(!file_exists($mofile) || !is_readable($mofile)){
			$this->_status = false;
			return false;
		}
		//pomo檔案讀取
		$lang = new \NOOP_Translations;
		$mo = new \MO();
		if (!$mo->import_from_file($mofile)){
			return false;
		}
		$mo->merge_with($lang);
		$this->pomo = &$mo;
		return true;
	}
}