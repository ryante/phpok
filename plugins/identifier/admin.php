<?php
/**
 * 標識串自動生成工具
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年09月18日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_identifier extends phpok_plugin
{
	private $youdao = false;
	private $kunwu = false;
	function __construct()
	{
		parent::plugin();
	}

	private function create_btn()
	{
		$pinfo = $this->_info();
		if($pinfo && $pinfo['param']){
			if($pinfo['param']['youdao_appid'] && $pinfo['param']['youdao_appkey']){
				$this->youdao = true;
			}
			if($pinfo['param']['phpok_appid'] && $pinfo['param']['phpok_appkey']){
				$this->kunwu = true;
			}
		}
		$this->assign("pinfo",$pinfo);
		$this->assign('is_youdao',$this->youdao);
		$this->assign('is_kunwu',$this->kunwu);
		$this->_show('btn.html');
	}

	//分類標識串增加取得翻譯外掛
	public function html_cate_set_body()
	{
		$this->create_btn();
	}

	//彈出視窗的分類增加
	public function html_cate_add_body()
	{
		$this->create_btn();
	}

	//內容標識串
	public function html_list_edit_body()
	{
		$this->create_btn();
	}

	//專案標識串
	public function html_project_set_body()
	{
		$this->create_btn();
	}

	//資料呼叫中心
	public function html_call_set_body()
	{
		$this->create_btn();
	}
}