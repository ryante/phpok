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
if(!$rs['ext'] || !$rs['ext']['password'] || !$rs['ext']['account']){
	if($this->config['debug']){
		phpok_log(print_r($rs,true));
	}
	return false;
}
if(!$extinfo['mobile'] || !$extinfo['content']){
	if($this->config['debug']){
		phpok_log(print_r($extinfo,true));
	}
	return false;
}
$url = $rs['ext']['server'] ? $rs['ext']['server'] : "http://api.duanxin.cm/";
$data = array(
	'action'=>'send',
	'username'=>$rs['ext']['account'],
	'password'=>strtolower(md5($rs['ext']['password'])),
	'phone'=>$extinfo['mobile'],
	'content'=>$extinfo['content'],
	'encode'=>'utf8'
);
$url .= "?";
foreach($data as $key=>$value){
	$url .= $key.'='.rawurlencode($value).'&';
}
$info = $this->lib('html')->get_content($url);
if($info != '100'){
	if($this->config['debug']){
		phpok_log($info);
	}
	return false;
}
return true;