<?php
/**
 * 會員註冊
 * @package phpok\www
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年07月25日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class register_control extends phpok_control
{
	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 註冊頁面，包含註冊驗證頁，使用到模板：register_check_專案ID
	 * @引數 _back 返回上一頁
	 * @引數 _code 驗證碼
	 * @引數 email 郵箱
	**/
	public function index_f()
	{
		$_back = $this->get("_back");
		if(!$_back){
			$_back = $this->config['url'];
		}
		if($this->session->val('user_id')){
			$this->error(P_Lang('您已登入，不用註冊'),$_back);
		}
		$this->assign('_back',$_back);
		if(!$this->site['register_status']){
			$tips = $this->site["register_close"] ? $this->site["register_close"] : P_Lang('系統暫停會員註冊，請聯絡站點管理員');
			$this->error($tips,$_back);
		}
		//取得開放的會員組資訊
		$grouplist = $this->model("usergroup")->opened_grouplist();
		if(!$grouplist){
			$this->error(P_Lang('未找到有效的會員組資訊'),$_back,10);
		}
		$this->assign("grouplist",$grouplist);
		$gid = $this->get("group_id","int");
		if($gid){
			$group_rs = $this->model("usergroup")->get_one($gid);
			if(!$group_rs || !$group_rs["status"]){
				$gid = 0;
			}
		}
		if(!$gid){
			if(count($grouplist) == 1){
				$group_rs = current($grouplist);
				$gid = $group_rs['id'];
			}else{
				foreach($grouplist AS $key=>$value){
					if($value["is_default"]){
						$gid = $value["id"];
						$group_rs = $value;
					}
				}
			}
		}
		//判斷是否使用驗證碼註冊
		$this->assign("group_id",$gid);
		$this->assign("group_rs",$group_rs);
		if($group_rs["register_status"] && $group_rs["register_status"] != "1"){
			if(!$group_rs['tbl_id']){
				$this->error(P_Lang('未繫結驗證專案'),$_back);
			}
			$p_rs = $this->model("project")->get_one($group_rs["tbl_id"],false);
			if(!$p_rs['module']){
				$this->error(P_Lang('繫結的專案中沒有關聯模組'),$_back);
			}
			$code = $this->get('_code');
			if(!$code){
				$tplfile = 'register_check_'.$group_rs['register_status'];
				if(!$this->tpl->check($tplfile)){
					$tplfile = 'register_chkcode';
					if(!$this->tpl->check($tplfile)){
						$this->error(P_Lang('繫結驗證串的模板不存，請檢查'));
					}
				}
				$this->view($tplfile);
				exit;
			}
			$chk_rs = $this->model("list")->get_one_condition("l.title='".$code."'",$p_rs['module']);
			if(!$chk_rs){
				$this->error(P_Lang("驗證碼不正確，請檢查"),$this->url("register"));
			}
			if($chk_rs && $chk_rs["account"]){
				$this->error(P_Lang("驗證碼已使用過，請填寫新的驗證碼"),$this->url("register"));
			}
			if(!$chk_rs["status"]){
				$this->error(P_Lang("驗證碼未啟用"),$this->url("register"));
			}
			if(($chk_rs['dateline'] + 86400) < $this->time){
				error(P_Lang('驗證碼已過期'),$this->url('register'));
			}
			$email = $this->get('email');
			if($email){
				$this->assign('account',$email);
				$this->assign('email',$email);
			}
			$this->assign("code",$code);
		}
		//取得當前組的擴充套件欄位
		$ext_list = $this->model("user")->fields_all("is_front=1");
		$extlist = false;
		if(!$ext_list){
			$ext_list = array();
		}
		foreach($ext_list AS $key=>$value){
			if($value["ext"]){
				$ext = unserialize($value["ext"]);
				foreach($ext AS $k=>$v){
					$value[$k] = $v;
				}
			}
			$idlist[] = strtolower($value["identifier"]);
			if($rs[$value["identifier"]]){
				$value["content"] = $rs[$value["identifier"]];
			}
			if(!$group_rs['fields'] || ($group_rs['fields'] && in_array($value['identifier'],explode(",",$group_rs['fields'])))){
				$extlist[] = $this->lib('form')->format($value);
			}
		}
		$this->assign("extlist",$extlist);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'register';
		}
		$this->assign('is_vcode',$this->model('site')->vcode('system','register'));
		$this->view($tplfile);
	}

	/**
	 * 儲存註冊資訊
	 * @引數 _chkcode 驗證碼
	 * @引數 user 賬號
	 * @引數 newpass 密碼
	 * @引數 chkpass 確認密碼
	 * @引數 email 郵箱
	 * @引數 mobile 手機號
	 * @引數 group_id 使用者組ID
	 * @引數 _code 註冊推廣碼
	 * @更新時間 2016年08月01日
	**/
	public function save_f()
	{
		if($this->session->val('user_id')){
			$this->error(P_Lang('您已是本站會員，不能執行這個操作'),$this->url);
		}
		$errurl = $this->url('register');
		if($this->model('site')->vcode('system','register')){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->error(P_Lang('驗證碼不能為空'),$errurl);
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->error(P_Lang('驗證碼填寫不正確'),$errurl);
			}
			$this->session->unassign('vcode');
		}
		//檢測會員賬號
		$user = $this->get("user");
		if(!$user){
			$this->error(P_Lang('賬號不能為空'),$errurl);
		}
		$safelist = array("'",'"','/','\\',';','&',')','(');
		foreach($safelist as $key=>$value){
			if(strpos($user,$value) !== false){
				$this->error(P_Lang('會員賬號不允許包含字串：{string}',array('string'=>$value)),$errurl);
			}
		}
		$chk = $this->model('user')->chk_name($user);
		if($chk){
			$this->error(P_Lang('會員賬號已存用'),$errurl);
		}
		$newpass = $this->get('newpass');
		if(!$newpass){
			$this->error(P_Lang('密碼不能為空'),$errurl);
		}
		$chkpass = $this->get('chkpass');
		if(!$chkpass){
			$this->error(P_Lang('確認密碼不能為空'),$errurl);
		}
		if($newpass != $chkpass){
			$this->error(P_Lang('兩次輸入的密碼不一致'),$errurl);
		}
		$email = $this->get('email');
		$mobile = $this->get('mobile');
		if($email){
			$chk = $this->lib('common')->email_check($email);
			if(!$chk){
				$this->error(P_Lang('郵箱不合法'),$errurl);
			}
			$chk = $this->model('user')->user_email($email);
			if($chk){
				$this->error(P_Lang('郵箱已註冊'),$errurl);
			}
		}
		if($mobile){
			$chk = $this->lib('common')->tel_check($mobile);
			if(!$chk){
				$this->error(P_Lang('手機號不合法'),$errurl);
			}
			$chk = $this->model('user')->user_mobile($mobile);
			if($chk){
				$this->error(P_Lang('手機號已註冊'),$errurl);
			}
		}
		
		$array = array();
		$array["user"] = $user;
		$array["pass"] = password_create($newpass);
		$array['email'] = $email;
		$array['mobile'] = $mobile;
		$group_id = $this->get("group_id","int");
		if($group_id){
			$group_rs = $this->model("usergroup")->get_one($group_id);
			if(!$group_rs || !$group_rs['status']){
				$group_id = 0;
			}
		}
		if(!$group_id){
			$group_rs = $this->model('usergroup')->get_default();
			if(!$group_rs || !$group_rs["status"]){
				$this->error(P_Lang('註冊失敗，網站未開放註冊許可權'),$errurl);
			}
			$group_id = $group_rs["id"];
		}
		if(!$group_id){
			$this->error(P_Lang('註冊失敗，網站未開放註冊許可權'),$errurl);
		}
		if(!$group_rs["is_default"] && !$group_rs["is_open"]){
			$this->error(P_Lang('註冊失敗，網站未開放註冊許可權'),$errurl);
		}
		$array["group_id"] = $group_id;
		$array["status"] = $group_rs["register_status"] ? 1 : 0;
		$array["regtime"] = $this->time;
		$uid = $this->model('user')->save($array);
		if(!$uid){
			$this->error(P_Lang('註冊失敗，請聯絡管理員'),$errurl);
		}
		if($uid){
			if($this->session->val('introducer')){
				$this->model('user')->save_relation($uid,$this->session->val('introducer'));
			}
		}
		$extlist = $this->model('user')->fields_all();
		$ext = array();
		$ext["id"] = $uid;
		if($extlist){
			foreach($extlist AS $key=>$value){
				$ext[$value["identifier"]] = ext_value($value);
			}
		}
		$this->model('user')->save_ext($ext);
		if($array['status']){
			$rs = $this->model('user')->get_one($uid);
			$this->session->assign('user_id',$rs['id']);
			$this->session->assign('user_gid',$rs['group_id']);
			$this->session->assign('user_name',$rs['user']);
			//註冊稽核通過後贈送積分
			$this->model('wealth')->register($uid,P_Lang('會員註冊'));
			$this->success(P_Lang('註冊成功，已自動登入，請稍候…'),$this->url);
		}
		if(!$group_rs["tbl_id"] && !$group_rs['register_status']){
			$this->success(P_Lang('註冊成功，等待管理員驗證'),$this->url);
		}
		$project = $this->model('project')->get_one($group_rs['tbl_id'],false);
		if(!$project['module']){
			$this->success(P_Lang('註冊成功，等待管理員驗證'),$this->url);
		}
		$code = $this->get('_code');
		if(!$code){
			$this->success(P_Lang('註冊成功，等待管理員驗證'),$this->url);
		}
		$info = $this->model('list')->get_one_condition("l.title='".$code."'",$project['module']);
		if($info){
			$ext = array('site_id'=>$info['site_id'],'project_id'=>$info['project_id']);
			$ext['account'] = $user;
			$this->model('list')->update_ext($ext,$project['module'],$info['id']);
			$this->model('user')->set_status($uid,1);
			$rs = $this->model('user')->get_one($uid);
			$this->session->assign('user_id',$rs['id']);
			$this->session->assign('user_gid',$rs['group_id']);
			$this->session->assign('user_name',$rs['user']);
			//註冊稽核通過後贈送積分
			$this->model('wealth')->register($uid,P_Lang('會員註冊'));
			$this->success(P_Lang('註冊成功，已自動登入，請稍候…'),$this->url);
		}
		$this->success(P_Lang('註冊成功，等待管理員驗證'),$this->url);
	}
}