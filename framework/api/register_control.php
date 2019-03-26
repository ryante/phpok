<?php
/**
 * 註冊介面API
 * @package phpok\api
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年07月27日
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
	 * 驗證賬戶是否被使用
	 * @引數 user 使用者賬號
	 * @返回 json字串
	**/
	public function check_user_f()
	{
		$user = $this->get("user");
		if(!$user){
			$this->json(P_Lang('賬號不能為空'));
		}
		$safelist = array("'",'"','/','\\',';','&',')','(');
		foreach($safelist as $key=>$value){
			if(strpos($user,$value) !== false){
				$this->json(P_Lang('會員賬號不允許包含字串：').$value);
			}
		}
		$rs = $this->model('user')->chk_name($user);
		if($rs){
			$this->json(P_Lang('會員賬號已存用'));
		}
		$this->json(P_Lang('賬號可以使用'),true);
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
	 * @返回 Json字串
	 * @更新時間 2016年07月30日
	**/
	public function save_f()
	{
		if($this->session->val('user_id')){
			$this->json(P_Lang('您已是本站會員，不能執行這個操作'));
		}
		if($this->model('site')->vcode('system','register')){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->json(P_Lang('驗證碼不能為空'));
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->json(P_Lang('驗證碼填寫不正確'));
			}
			$this->session->unassign('vcode');
		}
		//檢測會員賬號
		$user = $this->get("user");
		if(!$user){
			$this->json(P_Lang('賬號不能為空'));
		}
		$safelist = array("'",'"','/','\\',';','&',')','(');
		foreach($safelist as $key=>$value){
			if(strpos($user,$value) !== false){
				$this->json(P_Lang('會員賬號不允許包含字串：{string}',array('string'=>$value)));
			}
		}
		$chk = $this->model('user')->chk_name($user);
		if($chk){
			$this->json(P_Lang('會員賬號已存用'));
		}
		$newpass = $this->get('newpass');
		if(!$newpass){
			$this->json(P_Lang('密碼不能為空'));
		}
		$chkpass = $this->get('chkpass');
		if(!$chkpass){
			$this->json(P_Lang('確認密碼不能為空'));
		}
		if($newpass != $chkpass){
			$this->json(P_Lang('兩次輸入的密碼不一致'));
		}
		$email = $this->get('email');
		$mobile = $this->get('mobile');
		if($email){
			$chk = $this->lib('common')->email_check($email);
			if(!$chk){
				$this->json(P_Lang('郵箱不合法'));
			}
			$chk = $this->model('user')->user_email($email);
			if($chk){
				$this->json(P_Lang('郵箱已註冊'));
			}
		}
		if($mobile){
			$chk = $this->lib('common')->tel_check($mobile);
			if(!$chk){
				$this->json(P_Lang('手機號不合法'));
			}
			$chk = $this->model('user')->user_mobile($mobile);
			if($chk){
				$this->json(P_Lang('手機號已註冊'));
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
				$this->json(P_Lang('註冊失敗，網站未開放註冊許可權'));
			}
			$group_id = $group_rs["id"];
		}
		if(!$group_id){
			$this->json(P_Lang('註冊失敗，網站未開放註冊許可權'));
		}
		if(!$group_rs["is_default"] && !$group_rs["is_open"]){
			$this->json(P_Lang('註冊失敗，網站未開放註冊許可權'));
		}
		$array["group_id"] = $group_id;
		$array["status"] = $group_rs["register_status"] ? 1 : 0;
		$array["regtime"] = $this->time;
		$uid = $this->model('user')->save($array);
		if(!$uid){
			$this->json(P_Lang('註冊失敗，請聯絡管理員'));
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
		if(!$group_rs["tbl_id"] && !$group_rs['register_status']){
			$this->json(P_Lang('註冊成功，等待管理員驗證'),true);
		}
		$project = $this->model('project')->get_one($group_rs['tbl_id'],false);
		if(!$project['module']){
			$this->json(P_Lang('註冊成功，等待管理員驗證'),true);
		}
		$code = $this->get('_code');
		if(!$code){
			$this->json(P_Lang('註冊成功，等待管理員驗證'),true);
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
			$this->json(P_Lang('註冊成功，已自動登入，請稍候…'),true);
		}
		if($array['status']){
			$rs = $this->model('user')->get_one($uid);
			$this->session->assign('user_id',$rs['id']);
			$this->session->assign('user_gid',$rs['group_id']);
			$this->session->assign('user_name',$rs['user']);
			//註冊稽核通過後贈送積分
			$this->model('wealth')->register($uid,P_Lang('會員註冊'));
			$this->json(P_Lang('註冊成功，已自動登入，請稍候…'),true);
		}
		$this->json(P_Lang('註冊成功，等待管理員驗證'),true);
	}

	/**
	 * 註冊提交成功資訊
	 * @引數 
	 * @引數 
	 * @引數 
	**/
	public function ok_f()
	{
		if($this->session->val('user_id')){
			$this->error(P_Lang('您已是本站會員，不能執行這個操作'));
		}
		if($this->model('site')->vcode('system','register')){
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
		//檢測會員賬號
		$user = $this->get("user");
		if(!$user){
			$this->json(P_Lang('賬號不能為空'));
		}
		$safelist = array("'",'"','/','\\',';','&',')','(');
		foreach($safelist as $key=>$value){
			if(strpos($user,$value) !== false){
				$this->json(P_Lang('會員賬號不允許包含字串：{string}',array('string'=>$value)));
			}
		}
		$chk = $this->model('user')->chk_name($user);
		if($chk){
			$this->json(P_Lang('會員賬號已存用'));
		}
		$newpass = $this->get('newpass');
		if(!$newpass){
			$this->json(P_Lang('密碼不能為空'));
		}
		$chkpass = $this->get('chkpass');
		if(!$chkpass){
			$this->json(P_Lang('確認密碼不能為空'));
		}
		if($newpass != $chkpass){
			$this->json(P_Lang('兩次輸入的密碼不一致'));
		}
		$email = $this->get('email');
		$mobile = $this->get('mobile');
		if($email){
			$chk = $this->lib('common')->email_check($email);
			if(!$chk){
				$this->json(P_Lang('郵箱不合法'));
			}
			$chk = $this->model('user')->user_email($email);
			if($chk){
				$this->json(P_Lang('郵箱已註冊'));
			}
		}
		if($mobile){
			$chk = $this->lib('common')->tel_check($mobile);
			if(!$chk){
				$this->json(P_Lang('手機號不合法'));
			}
			$chk = $this->model('user')->user_mobile($mobile);
			if($chk){
				$this->json(P_Lang('手機號已註冊'));
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
				$this->json(P_Lang('註冊失敗，網站未開放註冊許可權'));
			}
			$group_id = $group_rs["id"];
		}
		if(!$group_id){
			$this->json(P_Lang('註冊失敗，網站未開放註冊許可權'));
		}
		if(!$group_rs["is_default"] && !$group_rs["is_open"]){
			$this->json(P_Lang('註冊失敗，網站未開放註冊許可權'));
		}
		$array["group_id"] = $group_id;
		$array["status"] = $group_rs["register_status"] ? 1 : 0;
		$array["regtime"] = $this->time;
		$uid = $this->model('user')->save($array);
		if(!$uid){
			$this->json(P_Lang('註冊失敗，請聯絡管理員'));
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
		if(!$group_rs["tbl_id"] && !$group_rs['register_status']){
			$this->json(P_Lang('註冊成功，等待管理員驗證'),true);
		}
		$project = $this->model('project')->get_one($group_rs['tbl_id'],false);
		if(!$project['module']){
			$this->json(P_Lang('註冊成功，等待管理員驗證'),true);
		}
		$code = $this->get('_code');
		if(!$code){
			$this->json(P_Lang('註冊成功，等待管理員驗證'),true);
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
			$this->json(P_Lang('註冊成功，已自動登入，請稍候…'),true);
		}
		if($array['status']){
			$rs = $this->model('user')->get_one($uid);
			$this->session->assign('user_id',$rs['id']);
			$this->session->assign('user_gid',$rs['group_id']);
			$this->session->assign('user_name',$rs['user']);
			//註冊稽核通過後贈送積分
			$this->model('wealth')->register($uid,P_Lang('會員註冊'));
			$this->json(P_Lang('註冊成功，已自動登入，請稍候…'),true);
		}
		$this->json(P_Lang('註冊成功，等待管理員驗證'),true);
	}

    /**
     *傳送簡訊驗證碼
    **/
    public function sms_f()
    {
        $smstpl = $this->site['login_type_sms'];
        if(!$smstpl){
	        $this->error(P_Lang('簡訊驗證碼模板未指定'));
        }
        $mobile = $this->get('mobile');
        if(!$mobile){
            $this->error(P_Lang('手機號不能為空'));
        }
        if(!$this->lib('common')->tel_check($mobile,'mobile')){
            $this->error(P_Lang('手機號不符合格式要求'));
        }
        $chk = $this->model('user')->user_mobile($mobile);
        if($chk){
            $this->json(P_Lang('手機號已被使用，請更換其他手機號'));
        }
        $code = $this->session->val('register_code');
        if($code){
            $time = $this->session->val('register_code_time');
            $chktime = $this->time - 60;
            if($time && $time > $chktime){
                $this->error(P_Lang('驗證碼已傳送，請等待一分鐘後再獲取'));
            }
        }
        $this->gateway('type','sms');
        $this->gateway('param','default');
        if(!$this->gateway('check')){
            $this->error(P_Lang('閘道器引數資訊未配置'));
        }
        $code = $this->model('gateway')->code_one($this->gateway['param']['type'],$this->gateway['param']['code']);
        if(!$code){
            $this->error(P_Lang('閘道器配置錯誤，請聯絡工作人員'));
        }
        if($code['code']){
            foreach($code['code'] as $key=>$value){
                if($value['required'] && $value['required'] == 'true' && !$this->gateway['param']['ext'][$key]){
                    $this->error(P_Lang('閘道器配置不完整，請聯絡工作人員'));
                }
            }
        }
        $tpl = $this->model('email')->tpl($smstpl);
        if(!$tpl){
            $this->error(P_Lang('簡訊驗證模板獲取失敗，請檢查'));
        }
        if(!$tpl['content']){
            $this->error(P_Lang('簡訊模板內容為空，請聯絡管理員'));
        }
        $tplcontent = strip_tags($tpl['content']);
        if(!$tplcontent){
            $this->error(P_Lang('簡訊模板內容是空的，請聯絡管理員'));
        }
        $info = $this->lib("vcode")->word();
        $this->assign('code',$info);
        $this->assign('mobile',$mobile);
        $content = $this->fetch($tplcontent,'msg');
        $title = $this->fetch($tpl['title'],'msg');
        $this->session->assign('register_code',$info);
        $this->session->assign('register_code_time',$this->time);
        $this->gateway('exec',array('mobile'=>$mobile,'content'=>$content,'title'=>$title,'identifier'=>$tpl['identifier']));
        $this->success();
    }

	/**
	 * 檢測驗證串是否正確，正確則跳轉到註冊頁
	 * @引數 _chkcode 驗證碼，防止機器人註冊
	 * @引數 _code 驗證串，通過Email得到的驗證串,24小時內有效
	 * @引數 group_id 會員組ID
	 * @返回 Json字串
	 * @更新時間 2016年07月30日
	**/
	public function code_f()
	{
		if($this->session->val('user_id')){
			$this->json(P_Lang('您已是本站會員，不能執行這個操作'));
		}
		if($this->model('site')->vcode('system','register')){
			$code = $this->get('_chkcode');
			if(!$code){
				$this->json(P_Lang('驗證碼不能為空'));
			}
			$code = md5(strtolower($code));
			if($code != $this->session->val('vcode')){
				$this->json(P_Lang('驗證碼填寫不正確'));
			}
			$this->session->unassign('vcode');
		}
		$code = $this->get('_code');
		if(!$code){
			$this->json(P_Lang('邀請碼不能為空'));
		}
		$group_id = $this->get('group_id','int');
		if($group_id){
			$group_rs = $this->model('usergroup')->get_one($group_id);
			if(!$group_rs || !$group_rs['status']){
				$group_id = 0;
			}
		}
		if(!$group_id){
			$group_rs = $this->model('usergroup')->get_default(1);
			if(!$group_rs){
				$this->json(P_Lang('註冊失敗，網站未開放註冊許可權'));
			}
			$group_id = $group_rs['id'];
		}
		if(!$group_rs['register_status'] || $group_rs['register_status'] == '1'){
			$this->json(P_Lang('該組不需要啟用邀請碼功能'));
		}
		if(!$group_rs['tbl_id']){
			$this->json(P_Lang('未分配相應的驗證組功能'));
		}
		$project = $this->model("project")->get_one($group_rs["tbl_id"],false);
		if(!$project['module']){
			$this->json(P_Lang('驗證庫未繫結相應的模組'));
		}
		$chk_rs = $this->model("list")->get_one_condition("l.title='".$code."'",$project['module']);
		if(!$chk_rs){
			$this->json(P_Lang('邀請碼不存在'));
		}
		if($chk_rs && $chk_rs["account"]){
			$this->json(P_Lang('邀請碼已被使用'));
		}
		if(!$chk_rs["status"]){
			$this->json(P_Lang('邀請碼未啟用，您可以聯絡管理員啟用'));
		}
		$url = $this->url('register','','_code='.rawurlencode($code).'&group_id='.$group_id,'www');
		$this->json($url,true);		
	}
}