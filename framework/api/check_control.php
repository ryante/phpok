<?php
/**
 * 各種常用驗證介面
 * @package phpok\api
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年09月11日
**/

if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class check_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 郵箱驗證
	 * @引數 val 要驗證的郵箱
	 * @返回 Json資料，status為1時表示驗證通過
	**/
	public function email_f()
	{
		$val = $this->get('val');
		if(!$val){
			$this->error(P_Lang('郵箱不能為空'));
		}
		if(!$this->lib('common')->email_check($val)){
			$this->error(P_Lang('郵箱不合法'));
		}
		$this->success();
	}

	/**
	 * 電話驗證，支援400，座機及手機號驗證，僅驗證合法性，不驗證是否已存在
	 * @引數 val 要驗證的電話
	 * @返回 Json資料，status為1時表示驗證通過
	**/
	public function tel_f()
	{
		$val = $this->get('val');
		if(!$val){
			$this->error(P_Lang('電話不能為空'));
		}
		if(!$this->lib('common')->tel_check($val)){
			$this->error(P_Lang('電話不合法'));
		}
		$this->success();
	}

	/**
	 * 手機驗證，僅驗證合法性，不驗證是否已存在
	 * @引數 val 要驗證的手機
	 * @返回 Json資料，status為1時表示驗證通過
	**/
	public function mobile_f()
	{
		$val = $this->get('val');
		if(!$val){
			$this->error(P_Lang('手機號不能為空'));
		}
		if(!$this->lib('common')->tel_check($val,'mobile')){
			$this->error(P_Lang('手機號不合法'));
		}
		$this->success();
	}

	/**
	 * 身份證驗證，僅驗證合法性，不驗證是否已存在
	 * @引數 val 要驗證的身份證
	 * @返回 Json資料，status為1時表示驗證通過
	**/
	public function idcard_f()
	{
		$val = $this->get('val');
		if(!$val){
			$this->error(P_Lang('身份證號不能為空'));
		}
		if(!$this->lib('common')->idcard_check($val)){
			$this->error(P_Lang('身份證號不合法'));
		}
		$this->success();
	}	
}
