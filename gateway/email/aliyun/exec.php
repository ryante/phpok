<?php
/**
 * 阿里雲郵件傳送
 * @package phpok\gateway\email\aliyun
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2017 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年02月28日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
if(!$rs['ext'] || !$rs['ext']['appkey'] || !$rs['ext']['appsecret'] || !$rs['ext']['server'] || !$rs['ext']['signame'] || !$rs['ext']['email'] || !$rs['ext']['nickname']){
	if($this->config['debug']){
		phpok_log('阿里雲配置引數不完整');
	}
	return false;
}
if(!$extinfo['title'] || !$extinfo['content'] || !$extinfo['email']){
	if($this->config['debug']){
		phpok_log('傳送的內容不完整');
	}
	return false;
}

$this->lib('aliyun')->regoin_id($rs['ext']['server']);
$this->lib('aliyun')->access_key($rs['ext']['appkey']);
$this->lib('aliyun')->access_secret($rs['ext']['appsecret']);
$this->lib('aliyun')->signature($rs['ext']['signame']);
$this->lib('aliyun')->dm_account($rs['ext']['email']);
$this->lib('aliyun')->dm_name($rs['ext']['nickname']);
$info = $this->lib('aliyun')->email($extinfo['title'],$extinfo['content'],$extinfo['email']);
if(!$info){
	if($this->config['debug']){
		phpok_log(P_Lang('郵件傳送失敗'));
	}
	return false;
}
if(!$info['status']){
	$error = $info['errid'] ? $info['errid'].":".$info['error'] : $info['error'];
	if($this->config['debug']){
		phpok_log($error);
	}
	return false;
}
return true;