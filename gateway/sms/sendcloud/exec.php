<?php
/*****************************************************************************************
	檔案： gateway/sms/duanxincm/exec.php
	備註： 執行操作
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2015年10月09日 16時43分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

if(!$rs['ext']){
	if($this->config['debug']){
		phpok_log(P_Lang('SendCloud簡訊未配置引數'));
	}
	return false;
}
if(!$rs['ext']['api_user'] && !$rs['ext']['api_key']){
	if(!$this->config['debug']){
		phpok_log(P_Lang('SendCloud簡訊未配置必填引數'));
	}
	return false;
}
if(!$extinfo['mobile'] || !$extinfo['title']){
	if($this->config['debug']){
		phpok_log(P_Lang('SendCloud簡訊傳送未指定接收手機號及傳送的模板ID'));
	}
	return false;
}
$datalist = false;
if($extinfo['content']){
	$tmpcontent = explode("\n",$extinfo['content']);
	$tmp = false;
	foreach($tmpcontent as $key=>$value){
		if(!$value || !trim($value)){
			continue;
		}
		$tmp2 = explode(":",trim($value));
		if($tmp2[0] != '' && $tmp2[1] != ''){
			$tmp[$tmp2[0]] = $tmp2[1];
		}
	}
	if($tmp){
		$datalist = $tmp;
		unset($tmp);
	}
}
//phpok_log(print_r($rs,true));
//phpok_log(print_r($extinfo,true));
$this->lib('sendcloud')->api_user($rs['ext']['api_user']);
$this->lib('sendcloud')->api_key($rs['ext']['api_key']);
$this->lib('sendcloud')->sms_template_id($extinfo['title']);
$info = $this->lib('sendcloud')->sms($extinfo['mobile'],$datalist);
if(!$info){
	if($this->config['debug']){
		phpok_log('簡訊傳送失敗');
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