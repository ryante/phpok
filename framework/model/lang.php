<?php
/**
 * 語言包管理
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2017年12月04日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class lang_model_base extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	public function get_list()
	{
		$langlist = array("cn"=>"簡體中文");
		if(is_file($this->dir_data."xml/langs.xml")){
			$langlist = $this->lib('xml')->read($this->dir_data.'xml/langs.xml');
		}
		return $langlist;
	}
}