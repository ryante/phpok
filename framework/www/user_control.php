<?php
/**
 * 會員詳細頁，開放瀏覽
 * @package phpok\www
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年07月01日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class user_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function index_f()
	{
		$uid = $this->get("uid");
		if(!$uid){
			$this->error(P_Lang('未指定會員資訊'));
		}
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('遊客無法檢視會員資訊'));
		}
		$user_rs = $this->model('user')->get_one($uid);
		$this->assign("user_rs",$user_rs);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'user_info';
		}
		$this->view($tplfile);
	}
}