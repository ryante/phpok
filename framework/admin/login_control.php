<?php
/**
 * 管理員登入
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年05月05日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class login_control extends phpok_control
{
	function __construct()
	{
		parent::control();
		$this->model("admin");
		$this->model("site");
	}

	/**
	 * 登入頁面
	**/
	public function index_f()
	{
		if($this->session->val('admin_id')){
			$this->error(P_Lang('您已成功登入'),$this->url('index'));
		}
		$vcode = ($this->config["is_vcode"] && function_exists("imagecreate")) ? true : false;
		$this->assign("vcode",$vcode);
		$multiple_language = isset($this->config['multiple_language']) ? $this->config['multiple_language'] : false;
		if($multiple_language){
			$langlist = $this->model('lang')->get_list();
			$this->assign('langlist',$langlist);
			$this->assign('langid',$this->session->val('admin_lang_id'));
		}
		$this->assign('multiple_language',$multiple_language);
		$logo = $this->site['adm_logo180'] ? $this->site['adm_logo180'] : 'images/login.svg';
		$this->assign('logo',$logo);
		$this->view('login');
	}

	private function lock_action($user='',$error_add=false)
	{
		$lockfile = $this->dir_cache.'lock-'.$this->session->sessid().'-admin.php';
		if(!file_exists($lockfile)){
			$lock_count = 0;
			$lock_time = $this->time;
		}else{
			$info = $this->lib('file')->cat($lockfile);
			if($info){
				$tmp = explode(",",$info);
				$lock_time = $tmp[0];
				$lock_count = $tmp[1] ? intval($tmp[1]) : 0;
			}else{
				$lock_count = 0;
				$lock_time = $this->time;
			}
		}
		$lock_time_config = intval($this->config['lock_time'] ? $this->config['lock_time'] : 2) * 3600;
		if(!$lock_time_config){
			$lock_time_config = 7200;
		}
		if(($this->time - $lock_time_config) > $lock_time){
			$lock_count = 0;
			$lock_time = $this->time;
		}
		//管理員
		$max_lock_count = $this->config['lock_error_count'] ? intval($this->config['lock_error_count']) : 5;
		if(!$max_lock_count){
			$max_lock_count = 5;
		}
		if($lock_count>=$max_lock_count){
			if($user && !is_bool($user)){
				$check = $this->model('admin')->account_lock_check($user);
				if(!$check){
					$this->model('admin')->account_lock($user);
				}else{
					$time = date("Y-m-d H:i:s",$check['unlock_time']);
					$this->error(P_Lang('管理員賬戶系統鎖定，解鎖時間是 {time}',array('time'=>$time)));
				}
			}else{
				//針對IP資料進行較驗
				$ip_lock = $this->model('admin')->ip_lock_check();
				if(!$ip_lock){
					$this->model('admin')->ip_lock();
				}else{
					$time = date("Y-m-d H:i:s",$ip_lock['unlock_time']);
					$this->error(P_Lang('管理員賬戶系統鎖定，解鎖時間是 {time}',array('time'=>$time)));
				}
				//針對SESSION資料進行較驗
				$session_lock = $this->model('admin')->session_lock_check();
				if(!$session_lock){
					$this->model('admin')->session_lock();
				}else{
					$time = date("Y-m-d H:i:s",$session_lock['unlock_time']);
					$this->error(P_Lang('管理員賬戶系統鎖定，解鎖時間是 {time}',array('time'=>$time)));
				}
			}
			$this->error(P_Lang('登入錯誤次數超過{count}次了，系統鎖定兩小時',array('count'=>$max_lock_count)));
		}
		if($error_add || ($user && is_bool($user))){
			$lock_count++;
		}
		$this->lib('file')->vi($lock_time.','.$lock_count,$lockfile);
	}

	public function ok_f()
	{
		if($this->session->val('admin_id')){
			$this->error(P_Lang('您已成功登入，無需再次驗證'));
		}
		$user = $this->get('user');
		if(!$user){
			$this->lock_action(true);
			$this->error(P_Lang('管理員賬號不能為空'));
		}
		$this->lock_action($user,false);
		
		//檢查 2 小時內該賬戶是否有系統鎖定
		$check = $this->model('admin')->account_lock_check($user);
		if($check){
			$time = date("Y-m-d H:i:s",$check['unlock_time']);
			$this->error(P_Lang('管理員賬戶系統鎖定，解鎖時間是 {time}',array('time'=>$time)));
		}
		
		$pass = $this->get('pass');
		if(!$pass){
			$this->lock_action($user,true);
			$this->error(P_Lang('密碼不能為空'));
		}
		//驗證碼檢測
		if($this->config['is_vcode'] && function_exists('imagecreate')){
			$code = $this->get("_code");
			if(!$code){
				$this->error(P_Lang('驗證碼不能為空'));
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->lock_action($user,true);
				$this->error(P_Lang('驗證碼填寫不正確'));
			}
			$this->session->unassign('vcode_admin');
		}
		
		$rs = $this->model('admin')->get_one_from_name($user);
		if(!$rs){
			$this->lock_action($user,true);
			$this->error(P_Lang('管理員資訊不存在'));
		}
		if(!password_check($pass,$rs["pass"])){
			$this->lock_action($user,true);
			$this->error(P_Lang('管理員密碼輸入不正確'));
		}
		if(!$rs["status"]){
			$this->lock_action($user,true);
			$this->error(P_Lang("管理員賬號已被鎖定，請聯絡超管！"));
		}
		//獲取管理員的許可權
		$this->session->assign('admin_site_id',$this->site['id']);
		if(!$rs["if_system"]){
			$popedom_list = $this->model('admin')->get_popedom_list($rs["id"]);
			if(!$popedom_list){
				$this->error(P_Lang('你的管理許可權未設定好，請聯絡超級管理員進行設定'));
			}
			$this->session->assign('admin_popedom',$popedom_list);
			$site_id = $this->model('popedom')->get_site_id($popedom_list);
			if(!$site_id){
				$this->error(P_Lang('你的管理許可權未設定好，請聯絡超級管理員進行設定'));
			}
			$this->session->assign('admin_site_id',$site_id);
		}
		$this->session->assign('admin_id',$rs['id']);
		$this->session->assign('admin_account',$rs['account']);
		$this->session->assign('admin_rs',$rs);
		if($this->config['develop']){
			$this->session->assign('adm_develop',true);
		}
		//刪除鎖定
		$this->model('admin')->lock_delete($rs['account']);
		//
		$this->success(P_Lang('管理員登入成功'));
	}
}