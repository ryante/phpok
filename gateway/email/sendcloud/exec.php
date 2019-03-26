<?php
/**
 * 郵件傳送
 * @package phpok\gateway\email\sendcloud
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年02月27日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
if(!$rs['ext'] || !$rs['ext']['api_user'] || !$rs['ext']['api_key'] || !$rs['ext']['label_id'] || !$rs['ext']['email']){
	if($this->config['debug']){
		phpok_log(print_r($rs,true));
	}
	return false;
}
if(!$extinfo['title'] || !$extinfo['content'] || !$extinfo['email']){
	if($this->config['debug']){
		phpok_log(print_r($extinfo,true));
	}
	return false;
}

$this->lib('sendcloud')->api_user($rs['ext']['api_user']);
$this->lib('sendcloud')->api_key($rs['ext']['api_key']);
$this->lib('sendcloud')->label_id($rs['ext']['label_id']);
$this->lib('sendcloud')->email_from($rs['ext']['email']);
$this->lib('sendcloud')->email_name($rs['ext']['fullname']);
$info = $this->lib('sendcloud')->email($extinfo['title'],$extinfo['content'],$extinfo['email']);
if(!$info){
	if($this->config['debug']){
		phpok_log('傳送失敗');
	}
	return false;
}
if(!$info['result']){
	if($this->config['debug']){
		phpok_log($info['statusCode'].':'.$info['message']);
	}
	return false;
}
return true;