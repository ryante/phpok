<?php
/**
 * 阿里發簡訊介面
 * @package phpok\gateway\sms\ali
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年01月21日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

$update = $this->get('update');
if($update == 2){
	$tplcode = $this->get('tplcode','int');
	if(!$tplcode){
		$this->error('未指定模板標識');
	}
	$code = $this->model('email')->get_one($tplcode);
	if(!$code){
		$this->error('模板標籤不存在');
	}
	$content = $code['content'];
	if(!$content){
		$this->success('變數:內容');
	}
	$content = strip_tags($content);
	$content = str_replace("\r\n","\n",$content);
	$tmp = explode("\n",$content);
	$content = '';
	foreach($tmp as $key=>$value){
		if(!$value || !trim($value)){
			continue;
		}
		$value = trim($value);
		$tmp2 = explode(":",$value);
		if(!$tmp2[0] || !$tmp2[1]){
			continue;
		}
		if($content){
			$content .= "\n";
		}
		$content .= $tmp2[0].":";
	}
	$this->success($content);
}
if($update == 1){
	$mobile = $this->get('mobile');
	if(!$mobile){
		$this->error('未指定手機號');
	}
	if(!$this->lib('common')->tel_check($mobile,'mobile')){
		$this->error('手機號格式不正式');
	}
	$tplcode = $this->get('tplcode','int');
	if(!$tplcode){
		$this->error('未指定模板標籤');
	}
	$content = $this->get('content');
	if(!$content){
		$this->error('未設定動態引數變數');
	}
	$code = $this->model('email')->get_one($tplcode);
	if(!$code){
		$this->error('模板ID不存在');
	}
	$tmp = explode("\n",$content);
	$codelist = array();
	foreach($tmp as $key=>$value){
		if(!$value || !trim($value)){
			continue;
		}
		$value = trim($value);
		$t = explode(":",$value);
		if($t[0] && $t[1]){
			$codelist[$t[0]] = $t[1];
		}
	}
	$this->lib('sendcloud')->api_user($rs['ext']['api_user']);
	$this->lib('sendcloud')->api_key($rs['ext']['api_key']);
	$this->lib('sendcloud')->sms_template_id($code['title']);
	$info = $this->lib('sendcloud')->sms($mobile,$codelist);
	if(!$info){
		$this->error('簡訊傳送失敗');
	}
	if(!$info['result']){
		$this->error($info['statusCode'].':'.$info['message']);
		return false;
	}
	$this->success();
	return true;
}
//讀取簡訊模板
$smslist = $this->model('email')->get_list("identifier LIKE 'sms_%'",0,999);
$this->assign('smslist',$smslist);
$this->view($this->dir_root.'gateway/'.$rs['type'].'/sendcloud/sendsms.html','abs-file');