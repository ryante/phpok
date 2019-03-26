<?php
/**
 * SESSION預設引挈
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2017年12月19日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class session_default extends session
{
	public $path_dir = '';
	public function __construct($config)
	{
		parent::__construct($config);
		$this->config($config);
		if($this->path_dir){
			$this->save_path($this->path_dir);
		}
		$this->start();
	}

	public function config($config)
	{
		parent::config($config);
		$this->path_dir = $config['path'] ? $config['path'] : '';
	}
}