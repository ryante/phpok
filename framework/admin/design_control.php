<?php
/**
 * 頁面設計器
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年05月30日
**/

class design_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('design');
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 裝載設計器
	**/
	public function index_f()
	{
		
	}

	
}
