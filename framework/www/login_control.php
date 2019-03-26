<?php
/**
 * 會員登入操作，基於WEB模式
 * @package phpok\www
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
	public function __construct()
	{
		parent::control();
		$backurl = $this->get('_back');
		if(!$backurl){
			$backurl = $this->config['url'];
		}
		if($this->session->val('user_id')){
			$this->success(P_Lang('您已是本站會員，不需要再次登入'),$backurl);
		}
	}

	/**
	 * 登入頁
	 * @引數 _back 返回上一級的頁面連結地址
	 * @返回 
	 * @更新時間 
	**/
	public function index_f()
	{
		$backurl = $this->get('_back');
		if(!$backurl){
			$backurl = $this->config['url'];
		}
		if(!$this->site['login_status']){
			$tips = $this->site["login_close"] ? $this->site["login_close"] : P_Lang('網站關閉');
			$this->error($tips,$backurl);
		}
		$check_sms = $this->model('gateway')->get_default('sms');
		if($this->site['login_type'] && $this->site['login_type'] == 'sms' && $check_sms){
			$this->_location($this->url('login','sms'));
		}
		$check_email = $this->model('gateway')->get_default('email');
		if($this->site['login_type'] && $this->site['login_type'] == 'email' && $check_email){
			$this->_location($this->url('login','sms'));
		}
		$tplfile = $this->model('site')->tpl_file('login','index');
		if(!$tplfile){
			$tplfile = 'login';
		}
		$this->assign("_back",$backurl);
		$this->assign('is_vcode',$this->model('site')->vcode('system','login'));
		$this->assign('login_email',$check_email);
		$this->assign('login_sms',$check_sms);
		$this->view($tplfile);
	}

	/**
	 * 簡訊驗證登入
	**/
	public function sms_f()
	{
		$backurl = $this->get('_back');
		if(!$backurl){
			$backurl = $this->config['url'];
		}
		if(!$this->site['login_status']){
			$tips = $this->site["login_close"] ? $this->site["login_close"] : P_Lang('網站關閉');
			$this->error($tips,$backurl,10);
		}
		$chk = $this->model('gateway')->get_default('sms');
		if(!$chk){
			$this->error(P_Lang('沒有安裝預設簡訊傳送引挈，請先安裝並設定一個預設'),$backurl);
		}
		if(!$this->site['login_type_sms']){
			$this->error(P_Lang('未設定簡訊模板'),$backurl);
		}
		$this->assign('is_vcode',$this->model('site')->vcode('system','login'));
		$tplfile = $this->model('site')->tpl_file('login','sms');
		if(!$tplfile){
			$tplfile = 'login_sms';
		}
		$check_email = $this->model('gateway')->get_default('email');
		$this->assign('login_email',$check_email);
		$this->view($tplfile);
	}

	/**
	 * 郵件驗證碼登入
	**/
	public function email_f()
	{
		$backurl = $this->get('_back');
		if(!$backurl){
			$backurl = $this->config['url'];
		}
		if(!$this->site['login_status']){
			$tips = $this->site["login_close"] ? $this->site["login_close"] : P_Lang('網站關閉');
			$this->error($tips,$backurl,10);
		}
		$chk = $this->model('gateway')->get_default('email');
		if(!$chk){
			$this->error(P_Lang('沒有安裝預設郵件傳送引挈，請先安裝並設定一個預設'),$backurl);
		}
		if(!$this->site['login_type_email']){
			$this->error(P_Lang('未設定郵件模板'),$backurl);
		}
		$this->assign('is_vcode',$this->model('site')->vcode('system','login'));
		$tplfile = $this->model('site')->tpl_file('login','email');
		if(!$tplfile){
			$tplfile = 'login_email';
		}
		$check_sms = $this->model('gateway')->get_default('sms');
		$this->assign('login_sms',$check_sms);
		$this->view($tplfile);
	}
	

	/**
	 * 基於WEB的登入模式，有返回有跳轉，適用於需要嵌入第三方HTML程式碼使用
	 * @引數 _back 返回之前登入後的頁面
	 * @引數 _chkcode 驗證碼，根據實際情況判斷是否啟用此項
	 * @引數 user 會員賬號/郵箱/手機號
	 * @引數 pass 密碼
	**/
	public function ok_f()
	{
		$_back = $this->get("_back");
		if(!$_back){
			$_back = $this->config['url'];
			$error_url = $this->url('login');
		}else{
			$error_url = $this->url('login','','_back='.rawurlencode($_back));
		}
		if($this->session->val('user_id')){
			$this->success(P_Lang('您已是本站會員，不需要再次登入'),$_back);
		}
		if($this->model('site')->vcode('system','login')){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->error(P_Lang('驗證碼不能為空'),$error_url);
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->error(P_Lang('驗證碼填寫不正確'),$error_url);
			}
			$this->session->unassign('vocode');
		}
		//獲取登入資訊
		$user = $this->get("user");
		if(!$user){
			$this->error(P_Lang('賬號不能為空'),$error_url);
		}
		$pass = $this->get("pass");
		if(!$pass){
			$this->error(P_Lang('會員密碼不能為空'),$error_url);
		}
		//多種登入方式
		$user_rs = $this->model('user')->get_one($user,'user');
		if(!$user_rs){
			$user_rs = $this->model('user')->get_one($user,'email');
			if(!$user_rs){
				$user_rs = $this->model('user')->get_one($user,'mobile');
				if(!$user_rs){
					$this->error(P_Lang('會員資訊不存在'),$error_url);
				}
			}
		}
		if(!$user_rs['status']){
			$this->error(P_Lang('會員稽核中，暫時不能登入'),$error_url);
		}
		if($user_rs['status'] == '2'){
			$this->error(P_Lang('會員被管理員鎖定，請聯絡管理員解鎖'),$error_url);
		}
		if(!password_check($pass,$user_rs["pass"])){
			$this->error(P_Lang('登入密碼不正確'),$error_url);
		}
		$this->session->assign('user_id',$user_rs['id']);
		$this->session->assign('user_gid',$user_rs['group_id']);
		$this->session->assign('user_name',$user_rs['user']);
		//接入財富
		$this->model('wealth')->login($user_rs['id'],P_Lang('會員登入'));
		$this->success(P_Lang('會員登入成功'),$_back);
	}

	/**
	 * 彈出視窗登入頁
	**/
	public function open_f()
	{
		if($this->session->val('user_id')){
			$this->error(P_Lang('您已是本站會員，不需要再次登入'));
		}
		if(!$this->site['login_status']){
			$tips = $this->site["login_close"] ? $this->site["login_close"] : P_Lang('網站關閉');
			$this->error($tips);
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'login_open';
		}
		$this->assign('is_vcode',$this->model('site')->vcode('system','login'));
		$email = $this->get('email');
		if($email){
			$this->assign('email',$email);
		}
		$mobile = $this->get('mobile');
		if($mobile){
			$this->assign('mobile',$mobile);
		}
		$user = $this->get('user');
		if($user){
			$this->assign('user',$user);
		}
		$accout = $user ? $user : ($mobile ? $mobile : $email);
		$this->assign('accout',$accout);
		$this->view($tplfile);
	}

	/**
	 * 取回密碼
	**/
	public function getpass_f()
	{
		$server = $this->model('gateway')->get_default('email');
		$sms_server = $this->model('gateway')->get_default('sms');
		if(!$server && !$sms_server){
			$this->error(P_Lang('未配置好郵件/簡訊通知功能，請聯絡管理員'),$this->url);
		}
		if($server){
			$this->assign('check_email',true);
		}
		if($sms_server){
			$this->assign('check_sms',true);
		}
		$type_id = $this->get('type_id');
		if(!$type_id || !in_array($type_id,array('email','sms'))){
			$type_id = $server ? 'email' : 'sms';
		}
		$this->assign('type_id',$type_id);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'login_getpass';
		}
		$this->assign('is_vcode',$this->model('site')->vcode('system','getpass'));
		$this->view($tplfile);
	}
}