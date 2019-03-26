<?php
/**
 * 電商相關操作
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年01月16日
**/

class biz_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function unitlist()
	{
		$xmlfile = $this->dir_data."xml/unit.xml";
		if(!file_exists($xmlfile)){
			return false;
		}
		return $this->lib('xml')->read($xmlfile);
	}
}
