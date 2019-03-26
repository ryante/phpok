<?php
/**
 * 管理員退出
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年05月30日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class logout_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$name = $this->session->val('admin_account');
		$this->session->unassign('admin_id');
		$this->session->unassign('admin_account');
		$this->session->unassign('admin_rs');
		$this->session->unassign('adm_develop');
		$this->success(P_Lang('管理員{admin_name}成功退出',array('admin_name'=>' <span class="red">'.$name.'</span> ')),$this->url('login'));
	}
}