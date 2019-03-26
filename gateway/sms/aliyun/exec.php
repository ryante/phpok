<?php
/**
 * 阿里雲簡訊傳送
 * @package phpok\gateway\sms\aliyun
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2017 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年02月28日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

if(!$rs['ext']){
	if($this->config['debug']){
		phpok_log(P_Lang('阿里簡訊未配置引數'));
	}
	return false;
}
if(!$rs['ext']['signame'] && !$rs['ext']['appkey'] && !$rs['ext']['appsecret'] && !$rs['ext']['server']){
	if(!$this->config['debug']){
		phpok_log(P_Lang('阿里簡訊未配置必填引數'));
	}
	return false;
}
if(!$extinfo['mobile'] || !$extinfo['title']){
	if($this->config['debug']){
		phpok_log(P_Lang('阿里簡訊傳送未指定接收手機號及傳送的模板標籤'));
	}
	return false;
}

$postdata = false;
if($extinfo['content']){
	$tmpcontent = explode("\n",$extinfo['content']);
	$tmp = false;
	foreach($tmpcontent as $key=>$value){
		if(!$value || !trim($value)){
			continue;
		}
		$tmp2 = explode(":",trim($value));
		if($tmp2[0] && $tmp2[1]){
			$tmp[$tmp2[0]] = $tmp2[1];
		}
	}
	if($tmp && is_array($tmp)){
		$postdata = $tmp;
		unset($tmp);
	}
}
$this->lib('aliyun')->end_point($rs['ext']['server']);
$this->lib('aliyun')->regoin_id($rs['ext']['regoin_id']);
$this->lib('aliyun')->access_key($rs['ext']['appkey']);
$this->lib('aliyun')->access_secret($rs['ext']['appsecret']);
$this->lib('aliyun')->signature($rs['ext']['signame']);
$this->lib('aliyun')->template_id($extinfo['title']);
$info = $this->lib('aliyun')->sms($extinfo['mobile'],$postdata);
if(!$info){
	if($this->config['debug']){
		phpok_log(P_Lang('簡訊傳送失敗'));
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