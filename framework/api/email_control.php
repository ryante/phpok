<?php
/**
 * 郵件相關操作
 * @package phpok\api
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年07月30日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class email_control extends phpok_control
{
	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 郵件傳送
	 * @引數 email 僅限管理員登入後可直接通過email來傳送郵件
	 * @引數 token 限前臺使用，用於普通會員使用PHPOK伺服器來傳送郵件
	 * @引數 title 郵件標題
	 * @引數 content 郵件內容，支援HTML
	 * @引數 fullname 收件人姓名，留空使用Email中@的前半部分做名稱
	**/
	public function index_f()
	{
		if($this->session->val('admin_id')){
			$email = $this->get('email');
			if(!$email){
				$this->json(P_Lang('Email不能為空'));
			}
		}else{
			$token = $this->get('token');
			if(!$token){
				$this->json(P_Lang('Email獲取異常，未指定Token資訊'));
			}
			$info = $this->lib('token')->decode($token);
			if(!$info || !$info['email']){
				$this->json(P_Lang('異常，內容不能為空'));
			}
			$email = $info['email'];
			if(!$email){
				$this->json(P_Lang('Token中沒有Email，請檢查'));
			}
		}
		$title = $this->get('title');
		$content = $this->get('content','html');
		if(!$content){
			$this->json(P_Lang('郵件內容不能為空'));
		}
		if(!$title){
			$title = phpok_cut($content,50,'…');
		}
		$email_server = $this->model('gateway')->get_default('email');
		if(!$email_server){
			$this->json(P_Lang('SMTP未配置好'));
		}
		$list = explode(',',$email);
		//如果僅只有一個Email時
		if(count($list) == 1){
			if(!$this->lib('common')->email_check($email)){
				$this->json(P_Lang('Email郵箱不符合要求'));
			}
			$fullname = $this->get('fullname');
			if(!$fullname){
				$fullname = str_replace(strstr($value,'@'),'',$email);
			}
			$info = $this->lib('email')->send_mail($email,$title,$content,$fullname);
			if(!$info){
				$this->json($this->lib('email')->error());
			}
			$this->json(true);
		}
		foreach($list as $key=>$value){
			$value = trim($value);
			if(!$value){
				continue;
			}
			if(!$this->lib('common')->email_check($value)){
				continue;
			}
			$value_name = str_replace(strstr($value,'@'),'',$value);
			$info = $this->lib('email')->send_mail($value,$title,$content,$value_name);
			if(!$info){
				$this->json($this->lib('email')->error());
			}
		}
		$this->json(true);		
	}

	/**
	 * 傳送註冊碼到指定的郵箱
	 * @引數 group_id 會員組ID
	 * @引數 email 要接收註冊碼的郵箱
	**/
	public function register_f()
	{
		if($this->session->val('user_id')){
			$this->json(P_Lang('您已經是會員，不能執行這個操作'));
		}
		$email_server = $this->model('gateway')->get_default('email');
		if(!$email_server){
			$this->json(P_Lang('SMTP未配置好'));
		}
		$group_id = $this->get('group_id','int');
		if($group_id){
			$group_rs = $this->model("usergroup")->get_one($group_id);
			if(!$group_rs || !$group_rs['status']){
				$group_id = 0;
			}
		}
		if(!$group_id){
			$group_rs = $this->model('usergroup')->get_default();
			if(!$group_rs || !$group_rs["status"]){
				$this->json(P_Lang('會員組不存在或未啟用'));
			}
			$group_id = $group_rs["id"];
		}
		if(!$group_id){
			$this->json(P_Lang('會員組ID不存在'));
		}
		$gid = $group_id;
		if(!$group_rs['register_status'] || $group_rs['register_status'] == '1'){
			$this->json(P_Lang('會員組不支援郵箱註冊認證'));
		}
		if(!$group_rs['tbl_id']){
			$this->json(P_Lang('未繫結相應的註冊專案'));
		}
		$p_rs = $this->model('project')->get_one($group_rs['tbl_id'],false);
		if(!$p_rs['module']){
			$this->json(P_Lang('未繫結相應的模組'));
		}
		$email_rs = $this->model('email')->get_identifier('register_code');
		if(!$email_rs){
			 $this->json(P_Lang('通知郵箱模板不存在'));
		}
		$email = $this->get('email');
		if(!$email){
			$this->json(P_Lang('郵箱不存在'));
		}
		if(!$this->lib('common')->email_check($email)){
			$this->json(P_Lang('郵箱驗證不通過'));
		}
		$uid = $this->model('user')->uid_from_email($email);
		if($uid){
			$this->json(P_Lang('郵箱已被使用'));
		}
		$title = $this->lib('common')->str_rand(10).$this->time;
		$array = array('site_id'=>$this->site['id'],'module_id'=>$p_rs['module'],'project_id'=>$p_rs['id']);
		$array['title'] = $title;
		$array['dateline'] = $this->time;
		$array['status'] = 1;
		$insert_id = $this->model('list')->save($array);
		if(!$insert_id){
			$this->json(P_Lang('資料儲存失敗，請聯絡管理員'));
		}
		$ext = array('id'=>$insert_id,'site_id'=>$p_rs['site_id'],'project_id'=>$p_rs['id']);
		$ext['account'] = '';
		$this->model('list')->save_ext($ext,$p_rs['module']);
		$ext = '_code='.rawurlencode($title).'&group_id='.$group_id.'&email='.rawurlencode($email);
		$link = $this->url('register','',$ext,'www');
		$this->assign('link',$link);
		$this->assign('email',$email);
		$title = $this->fetch($email_rs["title"],"content");
		$content = $this->fetch($email_rs["content"],"content");
		$info = $this->lib('email')->send_mail($email,$title,$content);
		if(!$info){
			$this->json($this->lib('email')->error());
		}
		$this->json(true);
	}
}