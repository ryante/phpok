<?php
/**
 * 簡訊驗證碼介面
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @許可 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年12月04日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class sms_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$this->config('is_ajax',true);
	}

	/**
	 * 獲取驗證碼
	**/
	public function index_f()
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
				$this->error(P_Lang('未指定簡訊模板ID'));
			}
		}
		$code = $this->session->val('sms_code');
		if($code && strpos($code,'-') !== false){
			$tmp = explode('-',$code);
			$time = $tmp[1];
			$code = $tmp[0];
			$chktime = $this->time - 60;
			if($time && $time > $chktime){
				$this->error(P_Lang('驗證碼已傳送，請等待一分鐘後再獲取'));
			}
		}
		$gateway = $this->get('gateway','int');
		if(!$gateway){
			$gateway = 'default';
		}
		$this->gateway('type','sms');
		$this->gateway('param',$gateway);
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
		$tpl = $this->model('email')->tpl($tplid);
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
		$this->session->assign('sms_code',$info.'-'.$this->time);
		$this->gateway('exec',array('mobile'=>$mobile,'content'=>$content,'title'=>$title,'code'=>$info));
		$this->success();
	}

	/**
	 * 驗證碼驗證
	**/
	public function check_f()
	{
		$code = $this->get('code');
		$check = $this->smscheck($code);
		if($check['status']){
			$this->success();
		}
		$this->error($check['info']);
	}

	/**
	 * 用於被呼叫的驗證碼
	 * @引數 $code 驗證碼
	**/
	public function smscheck($code='')
	{
		$data = array('status'=>false,'info'=>'');
		if(!$code){
			$data['info'] = P_Lang('驗證碼不能為空');
			return $data;
		}
		$chk_code = $this->session->val('sms_code');
		if(!$chk_code){
			$data['info'] = P_Lang('驗證碼沒有記錄，請重新獲取');
			return $data;
		}
		if(strpos($chk_code,'-') !== true){
			$data['info'] = P_Lang('驗證碼記錄有誤，請重新獲取');
			return $data;
		}
		$tmp = explode('-',$chk_code);
		$time = $tmp[1];
		if(!$time){
			$data['info'] = P_Lang('驗證碼記錄有誤，請重新獲取');
			return $data;
		}
		$chk_code = $tmp[0];
		if(!$chk_code){
			$data['info'] = P_Lang('驗證碼記錄有誤，請重新獲取');
			return $data;
		}
		$chktime = $this->time - 300;
		if($time < $chktime){
			$data['info'] = P_Lang('驗證碼已過期，請重新獲取');
			return $data;
		}
		if($chk_code != $code){
			$data['info'] = P_Lang('驗證碼填寫不正確，請修改或重新獲取');
			return $data;
		}
		//驗證碼檢測通過，清除 session 記錄
		$this->session->unassign('sms_code');
		return array('status'=>true);
	}
}
