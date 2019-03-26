<?php
/**
 * 驗證碼處理
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @許可 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2018年10月27日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class vcode_model_base extends phpok_model
{
	private $type = 'sms';
	private $error_info = '';
	public function __construct()
	{
		parent::model();
	}

	public function error_info($info='')
	{
		if($info && $info !=''){
			$this->error_info = $info;
		}
		return $this->error_info;
	}

	public function error_reset()
	{
		$this->error_info = '';
		return true;
	}

	/**
	 * 設定驗證碼的型別
	**/
	public function type($val='')
	{
		if($val && $val != ''){
			$this->type = $val;
		}
		return $this->type;
	}

	/**
	 * 建立驗證碼
	 * @引數 $type 驗證碼的型別
	 * @引數 $length 驗證碼的長度
	 * @引數 
	**/
	public function create($type='',$length=4)
	{
		$this->error_reset();
		if($type && $type != '' && !is_numeric($type)){
			$this->type($type);
		}
		if($type && is_numeric($type) && intval($type)){
			$length = $type;
		}
		$data = $this->session->val('verification_code');
		if($data && is_array($data) && $data['type'] == $this->type){
			if( ($data['time'] + 60) > $this->time){
				$this->error_info(P_Lang('禁止頻繁傳送驗證碼，請於一分鐘後請求'));
				return false;
			}
		}
		$data = array('time'=>$this->time,'count'=>0,'type'=>$this->type);
		$type = $this->type == 'sms' ? 'number' : 'all';
		$data['code'] = $this->code($length,$type);
		$this->session->assign('verification_code',$data);
		return $data;
	}

	/**
	 * 檢驗驗證碼
	 * @引數 $code 驗證碼值
	 * @返回 布林值 true 或 false 
	**/
	public function check($code='')
	{
		$this->error_reset();
		if(!$code){
			$this->error_info(P_Lang('驗證碼不能為空'));
			return false;
		}
		$data = $this->session->val('verification_code');
		if(!$data || !is_array($data)){
			$this->error_info(P_Lang('伺服器沒有找到匹配資料'));
			return false;
		}
		if($data['code'] != $code){
			$this->error_info(P_Lang('驗證碼不匹配'));
			//更新驗證碼錯誤次數
			$data['count'] = $data['count'] + 1;
			$this->session->assign('verification_code',$data);
			return false;
		}
		if($data['count'] >= 5){
			$this->error_info(P_Lang('驗證碼錯誤次數超過5次，請重新獲取驗證碼'));
			return false;
		}
		$longtime = $this->type == 'sms' ? 300 : 1800;
		if(($data['time'] + $longtime) < $this->time){
			$this->error_info(P_Lang('驗證碼已過期，請重新獲取驗證碼'));
			return false;
		}
		return true;
	}

	/**
	 * 銷燬驗證碼
	**/
	public function delete()
	{
		$this->error_reset();
		$this->session->unassign('verification_code');
		return true;
	}

	/**
	 * 驗證碼格式
	 * @引數 $length 長度
	 * @引數 $type 型別，支援all字母+數字，number存數字
	 * @返回 字串 
	**/
	private function code($length=6,$type="all")
	{
		$a = 'ABCDEFGHJKLMNPQRSTUVWXY3456789';
		if($type == 'number'){
			$a = '0123456789';
		}
		$maxlength = strlen($a)-1;
		$rand_str = '';
		for($i=0;$i<$length;++$i){
			$rand_str .= $a[rand(0,$maxlength)];
		}
		return $rand_str;
	}
}
