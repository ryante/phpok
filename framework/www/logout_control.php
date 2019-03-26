<?php
/**
 * 會員退出操作
 * @package phpok\www
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年07月27日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class logout_control extends phpok_control
{
	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 退出
	**/
	public function index_f()
	{
		$nickname = $this->session->val('user_name');
		$this->session->unassign('user_id');
		$this->session->unassign('user_gid');
		$this->session->unassign('user_name');
		$tip = P_Lang('會員{user}成功退出',array('user'=>'<span class="red"> '.$nickname.' </span>'));
		$this->success($tip,$this->url);
	}
}