<?php
/**
 * 公共操作，不限前臺，後臺
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年08月12日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ajax_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 返回預設結果集資訊
	 * @引數 file 檔名，不包含目錄，僅限字母，數字
	**/
	public function index_f()
	{
		$filename = $this->get("file");
		if(!$filename){
			$this->error(P_Lang('目標檔案不能為空'));
		}
		$ajax_file = $this->dir_phpok."ajax/".$this->app_id."_".$filename.".php";
		if(!file_exists($ajax_file)){
			$ajax_file = $this->dir_root."ajax/".$this->app_id."_".$filename.".php";
			if(!file_exists($ajax_file)){
				$this->error(P_Lang("檔案 {file} 不存在",$filename));
			}
		}
		include $ajax_file;
	}
}