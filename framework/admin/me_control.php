<?php
/**
 * 管理員面板資訊
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年03月17日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class me_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 個人資訊設定頁
	**/
	public function setting_f()
	{
		$rs = $this->model('admin')->get_one($this->session->val('admin_id'),'id');
		$this->assign('rs',$rs);
		$this->view('me_setting');
	}

	/**
	 * 修改密碼彈出頁
	**/
	public function pass_f()
	{
		$rs = $this->model('admin')->get_one($this->session->val('admin_id'),'id');
		$this->assign('rs',$rs);
		$this->view('me_password');
	}

	/**
	 * 提交修改密碼
	**/
	public function pass_submit_f()
	{
		$this->config('is_ajax',true);
		$oldpass = $this->get("oldpass");
		if(!$oldpass){
			$this->error(P_Lang('管理員密碼驗證不能為空'));
		}
		$rs = $this->model('admin')->get_one($this->session->val('admin_id'));
		if(!$rs){
			$this->error(P_Lang('管理員資訊不存在'));
		}
		if(!password_check($oldpass,$rs["pass"])){
			$this->error(P_Lang("管理員密碼不正確"));
		}
		$newpass = $this->get("newpass");
		if(!$newpass){
			$this->error(P_Lang('新密碼不能為空'));
		}
		$chkpass = $this->get("chkpass");
		if(!$chkpass){
			$this->error(P_Lang('確認密碼不能為空'));
		}
		if($newpass != $chkpass){
			$this->error(P_Lang("兩次輸入的新密碼不一致"));
		}
		$array = array('pass'=>password_create($newpass));
		$this->model('admin')->save($array,$this->session->val('admin_id'));
		$info = $this->model('admin')->get_one($this->session->val('admin_id'),'id');
		$this->session->assign('admin_rs',$info);
		$this->success();
	}

	/**
	 * 提交修改個人資訊
	**/
	public function submit_f()
	{
		$this->config('is_ajax',true);
		$rs = $this->model('admin')->get_one($this->session->val('admin_id'));
		if(!$rs){
			$this->error(P_Lang('管理員資訊不存在'));
		}
		$array = array();
		$name = $this->get('name');
		if(!$name){
			$name = $rs['account'];
		}
		if($name && $name != $rs['account']){
			$check = $this->model('admin')->check_account($name,$this->session->val('admin_id'));
			if($check){
				$this->error(P_Lang('管理員賬號已經存在，請重新設定'));
			}
			$array['account'] = $name;
		}
		$email = $this->get('email');
		if($email && $email != $rs['email']){
			$array['email'] = $email;
		}
		$array['fullname'] = $this->get('fullname');
		$this->model('admin')->save($array,$this->session->val('admin_id'));
		$info = $this->model('admin')->get_one($this->session->val('admin_id'),'id');
		$this->session->assign('admin_rs',$info);
		$this->success();
	}
}