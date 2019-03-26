<?php
/**
 * 會員登入，基於API請求
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年07月25日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class login_control extends phpok_control
{
	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 會員登入介面
	 * @引數 _chkcode 驗證碼
	 * @引數 user 會員賬號/郵箱/手機號
	 * @引數 pass 會員密碼
	**/
	public function save_f()
	{
		if($this->session->val('user_id')){
			$this->error(P_Lang('您已是本站會員，不需要再次登入'));
		}
		if($this->model('site')->vcode('system','login')){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->error(P_Lang('驗證碼不能為空'));
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->error(P_Lang('驗證碼填寫不正確'));
			}
			$this->session->unassign('vcode');
		}
		$user = $this->get("user");
		if(!$user){
			$this->error(P_Lang('賬號/郵箱/手機號不能為空'));
		}
		$pass = $this->get("pass");
		if(!$pass){
			$this->error(P_Lang('密碼不能為空'));
		}
		if($this->lib('common')->email_check($user)){
			$user_rs = $this->model('user')->get_one($user,'email');
		}
		if(!$user_rs && $this->lib('common')->tel_check($user,'mobile')){
			$user_rs = $this->model('user')->get_one($user,'mobile');
		}
		if(!$user_rs){
			$user_rs = $this->model('user')->get_one($user,'user');
		}
		if(!$user_rs){
			$this->error(P_Lang('會員資訊不存在'));
		}
		if(!$user_rs['status']){
			$this->error(P_Lang('會員稽核中，暫時不能登入'));
		}
		if($user_rs['status'] == '2'){
			$this->error(P_Lang('會員被管理員鎖定，請聯絡管理員解鎖'));
		}
		if(!password_check($pass,$user_rs["pass"])){
			$this->error(P_Lang('登入密碼不正確'));
		}
		$this->model('user')->update_session($user_rs['id']);
		$this->model('wealth')->login($user_rs['id'],P_Lang('會員登入'));
		$this->success($this->session->sessid());
	}

	/**
	 * 會員登入別名
	**/
	public function index_f()
	{
		$this->save_f();
	}

	/**
	 * 通過取回密碼進行修改
	 * @引數 mobile 手機號（與郵箱必須有一個）
	 * @引數 email 郵箱（與手機號中必須有一個）
	 * @引數 _chkcode 手機驗證碼或郵箱驗證碼
	 * @引數 newpass 新密碼
	 * @引數 chkpass 確認密碼
	**/
	public function repass_f()
	{
		$type_id = $this->get('type_id');
		if(!$type_id || !in_array($type_id,array('email','sms'))){
			$this->error(P_Lang('僅支援郵件或簡訊重設密碼'));
		}
		if($type_id == 'email'){
			$email = $this->get('email');
			if(!$email){
				$this->error(P_Lang('Email不能為空'));
			}
			if(!$this->lib('common')->email_check($email)){
				$this->error(P_Lang('Email地址不符合要求'));
			}
			
			$this->model('vcode')->type('email');
			$user_rs = $this->model('user')->get_one($email,'email',false,false);
		}else{
			$mobile = $this->get('mobile');
			if(!$mobile){
				$this->error(P_Lang('手機號不能為空'));
			}
			if(!$this->lib('common')->tel_check($mobile,'mobile')){
				$this->error(P_Lang('手機號不符合格式要求'));
			}
			$this->model('vcode')->type('sms');
			$user_rs = $this->model('user')->get_one($mobile,'mobile',false,false);
		}
		if(!$user_rs){
			$this->error(P_Lang('使用者資訊不存在'));
		}
		if(!$user_rs['status']){
			$this->error(P_Lang('會員賬號稽核中，暫時不能使用取回密碼功能'));
		}
		if($user_rs['status'] == '2'){
			$this->error(P_Lang('會員賬號被管理員鎖定，不能使用取回密碼功能，請聯絡管理員'));
		}
		$code = $this->get('_chkcode');
		if(!$code){
			$this->error(P_Lang('驗證碼不能為空'));
		}
		$data = $this->model('vcode')->check($code);
		if(!$data){
			$this->error($this->model('vcode')->error_info());
		}
		$newpass = $this->get('newpass');
		if(!$newpass){
			$this->error(P_Lang('密碼不能為空'));
		}
		$chkpass = $this->get('chkpass');
		if(!$chkpass){
			$this->error(P_Lang('確認密碼不能為空'));
		}
		if($newpass != $chkpass){
			$this->error(P_Lang('兩次輸入的密碼不一致'));
		}
		$pass = password_create($newpass);
		$this->model('user')->update_password($pass,$user_rs['id']);
		$this->success();
	}

	/**
	 * 登入狀態判斷
	**/
	public function status_f()
	{
		if($this->session->val('user_id')){
			$array = array('user_id'=>$this->session->val('user_id'));
			$array['user_name'] = $this->session->val('user_name');
			$array['user_gid'] = $this->session->val('user_gid');
			$this->success($array);
		}
		$this->error(P_Lang('會員未登入'));
	}

	/**
	 * 簡訊驗證碼登入，此項登入不需要圖形再輸入圖形驗證碼，驗證碼有效期時間是10分鐘
	 * @引數 type 執行方式，當為getcode表示取得驗證碼，其他為登入驗證
	 * @引數 mobile 手機號
	 * @引數 _chkcode 驗證碼（type不為空時有效）
	 * @返回 JSON陣列
	**/
	public function sms_f()
	{
		$mobile = $this->get('mobile');
		if(!$mobile){
			$this->error(P_Lang('手機號不能為空'));
		}
		if(!$this->lib('common')->tel_check($mobile,'mobile')){
			$this->error(P_Lang('手機號不正確'));
		}
		$rs = $this->model('user')->get_one($mobile,'mobile',false,false);
		if(!$rs){
			$this->error(P_Lang('手機號不存在'));
		}
		$code = $this->get('_chkcode');
		if(!$code){
			$this->error(P_Lang('驗證碼不能為空'));
		}
		$this->model('vcode')->type('sms');
		$data = $this->model('vcode')->check($code);
		if(!$data){
			$this->error($this->model('vcode')->error_info());
		}
		$this->model('user')->update_session($rs['id']);
		$this->model('wealth')->login($rs['id'],P_Lang('會員登入'));
		$this->success($this->session->sessid());
	}

	/**
	 * 郵件驗證碼登入模式
	 * @引數 type 執行方式，當為getcode表示取得驗證碼，其他為登入驗證
	 * @引數 email 郵箱
	 * @引數 _chkcode 驗證碼
	 * @返回 JSON資料
	**/
	public function email_f()
	{
		$email = $this->get('email');
		if(!$email){
			$this->error(P_Lang('Email不能為空'));
		}
		if(!$this->lib('common')->email_check($email)){
			$this->error(P_Lang('Email地址不符合要求'));
		}
		$rs = $this->model('user')->get_one($email,'email',false,false);
		if(!$rs){
			$this->error(P_Lang('Email地址不存在'));
		}
		$code = $this->get('_chkcode');
		if(!$code){
			$this->error(P_Lang('驗證碼不能為空'));
		}
		$this->model('vcode')->type('email');
		$data = $this->model('vcode')->check($code);
		if(!$data){
			$this->error($this->model('vcode')->error_info());
		}
		$this->model('user')->update_session($rs['id']);
		$this->model('wealth')->login($rs['id'],P_Lang('會員登入'));
		$this->success();
	}
}