<?php
/**
 * 驗證碼介面
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年11月22日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class vcode_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 圖形驗證碼
	**/
	public function index_f()
	{
		$info = $this->lib("vcode")->word();
		$code = md5(strtolower($info));
		$this->session->assign('vcode',$code);
		$this->lib("vcode")->create();
	}

	/**
	 * 簡訊驗證碼
	 * @引數 mobile 手機號，目前僅限中國大陸手機號有效
	 * @引數 tplid 驗證碼模板ID，未設定使用後臺設定的驗證碼模板ID
	 * @引數 gateid 簡訊閘道器ID，未設定使用預設的閘道器
	**/
	public function sms_f()
	{
		$mobile = $this->get('mobile');
		if(!$mobile){
			$this->error(P_Lang('手機號不能為空'));
		}
		if(!$this->lib('common')->tel_check($mobile,'mobile')){
			$this->error(P_Lang('手機號不符合格式要求'));
		}
		$tplid = $this->get('tplid','int');
		if(!$tplid){
			$tplid = $this->site['login_type_sms'];
			if(!$tplid){
				$this->error(P_Lang('未配置簡訊驗證碼模板'));
			}
		}
		$gateid = $this->get('gateid','int');
		if($gateid){
			$rs = $this->model('gateway')->get_one($gateid,'sms',true);
		}
		if(!$rs){
			$rs = $this->model('gateway')->get_default('sms');
		}
		if(!$rs){
			$this->error(P_Lang('沒有安裝簡訊傳送引挈，請先安裝並設定預設'),$backurl);
		}
		$data = $this->model('vcode')->create('sms',4);
		if(!$data){
			$this->error($this->model('vcode')->error_info());
		}
		$this->gateway('type','sms');
		$this->gateway('param',$rs['id']);
		if(!$this->gateway('check')){
			$this->error(P_Lang('閘道器引數資訊未配置'));
		}
		$code = $this->model('gateway')->code_one($this->gateway['param']['type'],$this->gateway['param']['code']);
		if(!$code){
			$this->error(P_Lang('閘道器配置錯誤，請聯絡工作人員'));
		}
		if($code['code']){
			$error = false;
			foreach($code['code'] as $key=>$value){
				if($value['required'] && $value['required'] == 'true' && $this->gateway['param']['ext'][$key] == ''){
					$error = true;
					break;
				}
			}
			if($error){
				$this->error(P_Lang('閘道器配置不完整，請聯絡工作人員'));
			}
		}
		$tpl = $this->model('email')->tpl($tplid);
		if(!$tpl){
			$this->error(P_Lang('簡訊模板不存在'));
		}
		$this->assign('code',$data['code']);
		$this->assign('mobile',$mobile);
		$content = $tpl['content'] ? $this->fetch($tpl['content'],'msg') : '';
		if($content){
			$content = strip_tags($content);
		}
		$title = $tpl['title'] ? $this->fetch($tpl['title'],'msg') : '';
		$this->gateway('exec',array('mobile'=>$mobile,'content'=>$content,'title'=>$title,'identifier'=>$tpl['identifier']));
		$this->success();
	}

	/**
	 * 郵件驗證碼
	 * @引數 email 郵箱
	 * @引數 tplid 驗證碼模板ID，未設定使用後臺設定的驗證碼模板ID
	 * @引數 gateyid 閘道器ID，未設定使用預設的閘道器
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
		$tplid = $this->get('tplid','int');
		if(!$tplid){
			$tplid = $this->site['login_type_email'];
		}
		$gateid = $this->get('gateid','int');
		if($gateid){
			$rs = $this->model('gateway')->get_one($gateid,'email',true);
		}
		if(!$rs){
			$rs = $this->model('gateway')->get_default('email');
		}
		if(!$rs){
			$this->error(P_Lang('沒有安裝郵件傳送引挈，請先安裝並設定預設'),$backurl);
		}
		$this->gateway('type','email');
		$this->gateway('param',$rs['id']);
		if(!$this->gateway('check')){
			$this->error(P_Lang('閘道器引數資訊未配置'));
		}
		$code = $this->model('gateway')->code_one($this->gateway['param']['type'],$this->gateway['param']['code']);
		if(!$code){
			$this->error(P_Lang('閘道器配置錯誤，請聯絡工作人員'));
		}
		if($code['code']){
			$error = false;
			foreach($code['code'] as $key=>$value){
				if($value['required'] && $value['required'] == 'true' && $this->gateway['param']['ext'][$key] == ''){
					$error = true;
					break;
				}
			}
			if($error){
				$this->error(P_Lang('閘道器配置不完整，請聯絡工作人員'));
			}
		}
		$data = $this->model('vcode')->create('email',6);
		if(!$data){
			$this->error($this->model('vcode')->error_info());
		}
		$tpltitle = P_Lang('獲取驗證碼');
		$tplcontent = P_Lang('您的驗證碼是：').'{$code}';
		if($tplid){
			$tpl = $this->model('email')->tpl($tplid);
			if($tpl && $tpl['content'] && strip_tags($tpl['content'])){
				$tplcontent = $tpl['content'];
			}
			if($tpl && $tpl['title']){
				$tpltitle = $tpl['title'];
			}
		}
		$this->assign('code',$data['code']);
		$this->assign('email',$email);
		$title = $this->fetch($tpltitle,'msg');
		$content = $this->fetch($tplcontent,'msg');
		$this->gateway('exec',array('email'=>$email,'content'=>$content,'title'=>$title));
		$this->success();
	}
}