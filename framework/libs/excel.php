<?php
/**
 * Excel類，呼叫擴充套件PHPExcel
 * @package phpok\libs
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年08月09日
**/

if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class excel_lib
{
	private $app;

	/**
	 * 建構函式
	**/
	public function __construct()
	{
		$this->app = init_app();
		if(!file_exists($this->app->dir_root.'extension/phpexcel/PHPExcel.php')){
			if(!file_exists($this->app->dir_root.'extension/phpexcel/phpexcel.zip')){
				exit("Not Found PHPExcel Classes");
			}
			//執行解壓
			$this->app->lib('phpzip')->unzip($this->app->dir_root.'extension/phpexcel/phpexcel.zip',$this->app->dir_root.'extension/phpexcel/');
			sleep(1);
		}
	}

	/**
	 * 建立一個Excel檔案
	 * @引數 
	 * @返回 
	 * @更新時間 
	**/
	public function add($title='',$saveto='')
	{
		if(!$title){
			$title = $this->app->time;
		}
		if(!$saveto){
			$saveto = $this->app->dir_cache.$this->app->time.'.xls';
		}
	}
}