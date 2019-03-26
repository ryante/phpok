<?php
/**
 * 阿里雲市場簡訊傳送
 * @package phpok\gateway\sms\ali
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年02月28日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

function ali_gateway_create_sign($xlist,$blist,$appsecret='')
{
	$string = "GET\n";
	$string.= "*/*\n\n";
	$string.= 'application/text; charset=UTF-8'."\n\n";
	ksort($xlist);
	foreach($xlist as $key=>$value){
		$string.= $key.":".$value."\n";
	}
	$string.= "/singleSendSms?";
	ksort($blist);
	$query = '';
	foreach($blist as $key=>$value){
		$query.= $key.'='.$value."&";
	}
	$query = substr($query,0,-1);
	$string.= $query;
	return base64_encode(hash_hmac('sha256', $string, $appsecret, true));
}

if(!$rs['ext']){
	if($this->config['debug']){
		phpok_log(P_Lang('阿里簡訊未配置引數'));
	}
	return false;
}
if(!$rs['ext']['appcode'] && !$rs['ext']['appkey'] && !$rs['ext']['appsecret']){
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

$paramString = '{}';
if($extinfo['content']){
	$tmpcontent = explode("\n",$extinfo['content']);
	$tmp = false;
	foreach($tmpcontent as $key=>$value){
		if(!$value || !trim($value)){
			continue;
		}
		$tmp2 = explode(":",trim($value));
		if(!$tmp2[0] && $tmp2[1]){
			$tmp[$tmp2[0]] = $tmp2[1];
		}
	}
	if($tmp && is_array($tmp)){
		$paramString = $this->lib('json')->encode($tmp);
	}
}
$url = $rs['ext']['server'] ? $rs['ext']['server'] : "http://sms.market.alicloudapi.com/singleSendSms";
$data = array(
	'ParamString'=>$paramString,
	'RecNum'=>$extinfo['mobile'],
	'TemplateCode'=>$extinfo['title'],
	'SignName'=>$rs['ext']['signame']
);
$url .= "?";
foreach($data as $key=>$value){
	$url .= $key.'='.rawurlencode($value).'&';
}
if($rs['ext']['sendtype'] == 'appcode'){
	$this->lib('html')->set_header('Authorization','APPCODE '.$rs['ext']['appcode']);
}else{
	$xlist = array('X-Ca-Key'=>$rs['ext']['appkey']);
	$xlist['X-Ca-Nonce'] = md5($this->time);
	$xlist['X-Ca-Stage'] = 'RELEASE';
	$sign = ali_gateway_create_sign($xlist,$data,$rs['ext']['appsecret']);
	$xlist['X-Ca-Signature'] = $sign;
	$xlist['X-Ca-Signature-Headers'] = "X-Ca-Key,X-Ca-Nonce,X-Ca-Stage";
	foreach($xlist as $key=>$value){
		$this->lib('html')->set_header($key,$value);
	}
	$this->lib('html')->set_header('Accept','*/*');
	$this->lib('html')->set_header('Content-Type','application/text; charset=UTF-8');
}
$info = $this->lib('html')->get_content($url);
if(!$info){
	if($this->config['debug']){
		phpok_log('簡訊傳送失敗');
	}
	return false;
}
$info = $this->lib('json')->decode($info);
if(!$info['success']){
	if($this->config['debug']){
		phpok_log('簡訊傳送失敗：'.$info['message']);
	}
	return false;
}
return true;