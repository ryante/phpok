<?php
/**
 * 會員中心資料儲存
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年10月27日
**/

if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class usercp_control extends phpok_control
{
	private $u_id; //會員ID
	public function __construct()
	{
		parent::control();
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您還沒有登入，請先登入或註冊'));
		}
		$this->u_id = $this->session->val('user_id');
	}

	/**
	 * 儲存個人資料
	**/
	public function info_f()
	{
		$group_rs = $this->model('usergroup')->group_rs($this->u_id);
		if(!$group_rs){
			$this->error(P_Lang('會員組不存在'));
		}
		$condition = 'is_front=1';
		if($group_rs['fields']){
			$tmp = explode(",",$group_rs['fields']);
			$condition .= " AND identifier IN('".(implode("','",$tmp))."')";
		}
		$ext_list = $this->model('user')->fields_all($condition,"id");
		if($ext_list){
			$ext = array();
			foreach($ext_list as $key=>$value){
				$ext[$value['identifier']] = $this->lib('form')->get($value);
			}
			if($ext && count($ext)>0){
				$this->model('user')->update_ext($ext,$this->u_id);
			}
		}
		$this->success();
	}

	/**
	 * 更新會員頭像
	**/
	public function avatar_f()
	{
		$type = $this->get('type');
		if($type == 'base64'){
			$data = $this->get('data');
			if(!$data){
				$this->error(P_Lang('圖片內容不能為空'));
			}
			if(strpos($data,',') === false){
				$this->error(P_Lang('附片格式不正確'));
			}
			$tmp = explode(",",$data);
			$tmpinfo = substr($data,strlen($tmp[0]));
			$content = base64_decode($tmpinfo);
			if($content == $tmpinfo){
				$this->error(P_Lang('不是合法的圖片檔案'));
			}
			$info = explode(";",$tmp[0]);
			$ext = 'png';
			if($info[0]){
				$tmp = explode("/",$info[0]);
				if($tmp[1]){
					$ext = $tmp[1];
				}
			}
			if(!in_array($ext,array('jpg','png','gif','jpeg'))){
				$this->error(P_Lang('上傳的檔案格式不附合系統要求'));
			}
			if($ext == 'jpeg'){
				$ext = 'jpg';
			}
			$save_pic = 'res/user/'.$this->u_id.'.'.$ext;
			$this->lib('file')->rm($this->dir_root.$save_pic);
			$this->lib('file')->save_pic($content,$this->dir_root.$save_pic);
			//生成正方式
			$this->lib('gd')->thumb($this->dir_root.$save_pic,$this->u_id,100,100);
			$this->lib('file')->mv('res/user/_'.$this->u_id.'.'.$ext,$save_pic);
			$this->model('user')->update_avatar($save_pic,$this->u_id);
			$this->success();
		}
		$data = $this->get('data');
		if(!$data){
			$this->json(P_Lang('頭像圖片地址不能為空'));
		}
		$pInfo = pathinfo($data);
		$fileType = strtolower($pInfo['extension']);
		if(!$fileType || !in_array($fileType,array('jpg','gif','png','jpeg'))){
			$this->json(P_Lang('頭像圖片僅支援jpg,gif,png,jpeg'));
		}
		if(!file_exists($this->dir_root.$data)){
			$this->json(P_Lang('頭像檔案不存在'));
		}
		$this->model('user')->update_avatar($data,$this->u_id);
		$this->json(true);
	}

	/**
	 * 更新會員密碼功能
	**/
	public function passwd_f()
	{
		$user = $this->model('user')->get_one($this->u_id);
		if($user['pass']){
			$oldpass = $this->get("oldpass");
			if(!$oldpass){
				$this->error(P_Lang('舊密碼不能為空'));
			}
			if(!password_check($oldpass,$user["pass"])){
				$this->error(P_Lang('舊密碼輸入錯誤'));
			}
		}
		$newpass = $this->get("newpass");
		$chkpass = $this->get("chkpass");
		if(!$newpass || !$chkpass){
			$this->error(P_Lang('新密碼不能為空'));
		}
		if(strlen($newpass) < 6){
			$this->error(P_Lang('密碼不符合要求，密碼長度不能小於6位'));
		}
		if(strlen($newpass) > 20){
			$this->error(P_Lang('密碼不符合要求，密碼長度不能超過20位'));
		}
		if($newpass != $chkpass){
			$this->error(P_Lang('新舊密碼不一致'));
		}
		if($oldpass && $oldpass == $newpass){
			$this->error(P_Lang('新舊密碼不能一樣'));
		}
		$password = password_create($newpass);
		$this->model('user')->update_password($password,$this->u_id);
		$this->model('user')->update_session($this->u_id);
		$this->success();
	}

	/**
	 * 更新會員手機
	**/
	public function mobile_f()
	{
		$pass = $this->get('pass');
		if(!$pass){
			$this->error(P_Lang('密碼不能為空'));
		}
		$newmobile = $this->get("mobile");
		if(!$newmobile){
			$this->error(P_Lang('新手機號碼不能為空'));
		}
		$user = $this->model('user')->get_one($this->u_id);
		if(!password_check($pass,$user['pass'])){
			$this->error(P_Lang('密碼填寫錯誤'));
		}
		if($user['mobile'] == $newmobile){
			$this->error(P_Lang('新舊手機號碼不能一樣'));
		}
		$uid = $this->model('user')->uid_from_mobile($newmobile,$this->u_id);
		if($uid){
			$this->error(P_Lang('手機號碼已被使用'));
		}
		$server = $this->model('gateway')->get_default('sms');
		if($server){
			$chkcode = $this->get('chkcode');
			if(!$chkcode){
				$this->error(P_Lang('驗證碼不能為空'));
			}
			$check = $this->model('vcode')->check($chkcode);
			if(!$check){
				$this->error($this->model('vcode')->error_info());
			}
			$this->model('vcode')->delete();
		}
		$this->model('user')->update_mobile($newmobile,$this->u_id);
		$this->success();
	}

	/**
	 * 更新會員郵箱
	**/
	public function email_f()
	{
		$pass = $this->get('pass');
		if(!$pass){
			$this->error(P_Lang('密碼不能為空'));
		}
		$email = $this->get("email");
		if(!$email){
			$this->error(P_Lang('新郵箱不能為空'));
		}
		//判斷郵箱是否合法
		$chk = $this->lib('common')->email_check($email);
		if(!$chk){
			$this->error(P_Lang('郵箱格式不正確，請重新填寫'));
		}
		$user = $this->model('user')->get_one($this->u_id);
		if($user['email'] == $email){
			$this->error(P_Lang('新舊郵箱不能一樣'));
		}
		$chk = $this->model('user')->uid_from_email($email,$this->u_id);
		if($chk){
			$this->error(P_Lang('郵箱已被使用，請更換其他郵箱'));
		}
		$server = $this->model('gateway')->get_default('email');
		if($server){
			$chkcode = $this->get('chkcode');
			if(!$chkcode){
				$this->error(P_Lang('驗證碼不能為空'));
			}
			$check = $this->model('vcode')->check($chkcode);
			if(!$check){
				$this->error($this->model('vcode')->error_info());
			}
			$this->model('vcode')->delete();
		}
		$this->model('user')->save(array('email'=>$email),$this->u_id);
		$this->model('user')->update_session($this->u_id);
		$this->success();
	}


	//更新發票資訊
	public function invoice_f()
	{
		$invoice_type = $this->get("invoice_type");
		if(!$invoice_type)
		{
			$this->json(P_Lang('發票型別不能為空'));
		}
		$this->model('user')->update_invoice_type($invoice_type,$this->u_id);
		$invoice_title = $this->get("invoice_title");
		if(!$invoice_title)
		{
			$this->json(P_Lang('發票抬頭不能為空'));
		}
		$this->model('user')->update_invoice_title($invoice_title,$this->u_id);
		$this->model('user')->update_session($this->u_id);
		$this->json(true);
	}

	/**
	 * 獲取會員的收貨地址資訊
	**/
	public function address_f()
	{
		$rslist = $this->model('user')->address_all($this->session->val('user_id'));
		if(!$rslist){
			$this->error(P_Lang('會員暫無收貨地址資訊'));
		}
		$total = count($rslist);
		$default = $first = array();
		foreach($rslist as $key=>$value){
			if($key<1){
				$first = $value;
			}
			if($value['is_default']){
				$default = $value;
			}
		}
		if(!$default){
			$default = $first;
		}
		$array = array('total'=>$total,'rs'=>$default,'rslist'=>$rslist);
		$this->success($array);
	}

	public function address_default_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('user')->address_one($id);
		if($rs['user_id'] != $this->u_id){
			$this->json(P_Lang('您沒有許可權操作此地址資訊'));
		}
		$this->model('user')->address_default($id);
		$this->json(true);
	}

	public function address_setting_f()
	{
		$id = $this->get('id','int');
		$array = array();
		if($id){
			$chk = $this->model('user')->address_one($id);
			if(!$chk || $chk['user_id'] != $this->u_id){
				$this->json(P_Lang('您沒有許可權執行此操作'));
			}
		}else{
			$array['user_id'] = $this->u_id;
		}
		$country = $this->get('country');
		if(!$country){
			$country = '中國';
		}
		$array['country'] = $country;
		$array['province'] = $this->get('pca_p');
		$array['city'] = $this->get('pca_c');
		$array['county'] = $this->get('pca_a');
		$array['fullname'] = $this->get('fullname');
		if(!$array['fullname']){
			$this->json(P_Lang('收件人姓名不能為空'));
		}
		$array['address'] = $this->get('address');
		$array['mobile'] = $this->get('mobile');
		$array['tel'] = $this->get('tel');
		if(!$array['mobile'] && !$array['tel']){
			$this->json(P_Lang('手機或固定電話必須有填寫一項'));
		}
		if($array['mobile']){
			if(!$this->lib('common')->tel_check($array['mobile'],'mobile')){
				$this->json(P_Lang('手機號格式不對，請填寫11位數字'));
			}
		}
		if($array['tel']){
			if(!$this->lib('common')->tel_check($array['tel'],'tel')){
				$this->json(P_Lang('電話格式不對'));
			}
		}
		$array['email'] = $this->get('email');
		if($array['email']){
			if(!$this->lib('common')->email_check($array['email'])){
				$this->json(P_Lang('郵箱格式不對'));
			}
		}
		$this->model('user')->address_save($array,$id);
		$this->json(true);
	}

	/**
	 * PHPOK5版會員收貨地址儲存
	**/
	public function address_save_f()
	{
		$id = $this->get('id','int');
		$array = array();
		if($id){
			$chk = $this->model('user')->address_one($id);
			if(!$chk || $chk['user_id'] != $this->u_id){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
		}else{
			$array['user_id'] = $this->u_id;
		}
		$country = $this->get('country');
		if(!$country){
			$country = '中國';
		}
		$array['country'] = $country;
		$array['province'] = $this->get('pca_p');
		$array['city'] = $this->get('pca_c');
		$array['county'] = $this->get('pca_a');
		$array['fullname'] = $this->get('fullname');
		if(!$array['fullname']){
			$this->json(P_Lang('收件人姓名不能為空'));
		}
		$array['address'] = $this->get('address');
		$array['mobile'] = $this->get('mobile');
		$array['tel'] = $this->get('tel');
		if(!$array['mobile'] && !$array['tel']){
			$this->error(P_Lang('手機或固定電話必須有填寫一項'));
		}
		if($array['mobile']){
			if(!$this->lib('common')->tel_check($array['mobile'],'mobile')){
				$this->error(P_Lang('手機號格式不對，請填寫11位數字'));
			}
		}
		if($array['tel']){
			if(!$this->lib('common')->tel_check($array['tel'],'tel')){
				$this->error(P_Lang('電話格式不對'));
			}
		}
		$array['email'] = $this->get('email');
		if($array['email']){
			if(!$this->lib('common')->email_check($array['email'])){
				$this->error(P_Lang('郵箱格式不對'));
			}
		}
		if($id){
			$this->model('user')->address_save($array,$id);
		}else{
			$id = $this->model('user')->address_save($array);
			if(!$id){
				$this->error(P_Lang('地址新增失敗'));
			}
		}
		$is_default = $this->get('is_default','checkbox');
		if($is_default){
			$this->model('user')->address_default($id);
		}
		$this->success($id);
	}

	public function address_delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('user')->address_one($id);
		if($rs['user_id'] != $this->u_id){
			$this->json(P_Lang('您沒有許可權操作此地址資訊'));
		}
		$this->model('user')->address_delete($id);
		$this->json(true);
	}

	public function invoice_setting_f()
	{
		$id = $this->get('id','int');
		$type = $this->get('type');
		$title = $this->get('title');
		if(!$title){
			$title = P_Lang('個人發票');
		}
		$content = $this->get('content');
		if(!$content){
			$content = P_Lang('明細');
		}
		$note = $this->get('note');
		$array = array('user_id'=>$this->u_id,'type'=>$type,'title'=>$title,'content'=>$content,'note'=>$note);
		$this->model('user')->invoice_save($array,$id);
		$this->json(true);
	}

	public function invoice_default_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('user')->invoice_one($id);
		if($rs['user_id'] != $this->u_id){
			$this->json(P_Lang('您沒有許可權操作此資訊'));
		}
		$this->model('user')->invoice_default($id);
		$this->json(true);
	}

	public function invoice_delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('user')->invoice_one($id);
		if($rs['user_id'] != $this->u_id){
			$this->json(P_Lang('您沒有許可權操作此地址資訊'));
		}
		$this->model('user')->invoice_delete($id);
		$this->json(true);
	}

	/**
	 * 變更個人資訊，通過fields獲取要變更的擴充套件引數資訊，僅用於儲存會員擴充套件表裡字元型別
	 * @引數 fields 要更新的變數
	**/
	public function save_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非會員不能執行此操作'));
		}
		$fields = $this->get('fields');
		if(!$fields){
			$this->error(P_Lang('未指定要修改的欄位'));
		}
		$list = explode(",",$fields);
		$flist = $this->model('user')->fields_all("is_front=1");
		if(!$flist){
			$this->error(P_Lang('沒有可編輯的欄位'));
		}
		$idlist = array();
		foreach($flist as $key=>$value){
			$idlist[] = $value['identifier'];
		}
		$array = array();
		foreach($list as $key=>$value){
			if(!in_array($value,$idlist)){
				continue;
			}
			$val = $this->get($value);
			$array[$value] = $val;
		}
		if($array && count($array)>0){
			$this->model("user")->update_ext($array,$this->session->val('user_id'));
			$this->success();
		}
		$this->error(P_Lang('沒有接收到引數及值'));
	}
}