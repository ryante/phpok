<?php
/**
 * 第三方控制元件，檢測是否手機瀏覽器
 * @package phpok\libs
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年06月20日
**/

if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class mobile_lib extends _init_lib
{
	private $obj;
	public function __construct()
	{
		parent::__construct();
		$file = $this->dir_extension().'mobile/Mobile_Detect.php';
		if(file_exists($file)){
			include_once($file);
		}
		$this->obj = new Mobile_Detect();
	}

	public function is_mobile()
	{
		return $this->obj->isMobile();
	}

	public function is_ios()
	{
		if(!$this->obj->is('iOS')){
			return false;
		}
		if($this->obj->version('iPad','float')>=4.3 || $this->obj->version('iPhone','float') >= 4.3 || $this->obj->version('iPod','float') >= 4.3){
			return true;
		}
		return false;
	}
}