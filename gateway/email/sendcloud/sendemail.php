<?php
/**
 * SMTP傳送郵件
 * @package phpok\gateway\email\smtp
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年11月17日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
$update = $this->get('update','int');
if($update){
	$title = $this->get('title');
	if(!$title){
		$this->error('郵件主題不能為空');
		return false;
	}
	$email = $this->get('email');
	if(!$email){
		$this->error('目標Email不能為空');
		return false;
	}
	if(!$this->lib('common')->email_check($email)){
		$this->error('Email格式不正確');
		return false;
	}
	$content = $this->get('content','html');
	if(!$content){
		$this->error('郵件內容不能為空');
		return false;
	}
	$this->lib('sendcloud')->api_user($rs['ext']['api_user']);
	$this->lib('sendcloud')->api_key($rs['ext']['api_key']);
	$this->lib('sendcloud')->label_id($rs['ext']['label_id']);
	$this->lib('sendcloud')->email_from($rs['ext']['email']);
	$this->lib('sendcloud')->email_name($rs['ext']['fullname']);
	$info = $this->lib('sendcloud')->email($title,$content,$email);
	if(!$info){
		$this->error('傳送失敗');
		return false;
	}
	if(!$info['result']){
		$this->error($info['statusCode'].':'.$info['message']);
		return false;
	}
	$this->success();
	return true;
}
$this->view($this->dir_root.'gateway/'.$rs['type'].'/sendemail.html','abs-file');