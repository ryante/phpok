<?php
/**
 * 執行操作
 * @package phpok\gateway\sms\gxt106com
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年11月17日
**/
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
$url = $rs['ext']['server'] ? $rs['ext']['server'] : "http://www.gxt106.com/sms.aspx";
$data = array(
	'action'=>'send',
	'userid'=>$rs['ext']['companyid'],
	'account'=>$rs['ext']['account'],
	'password'=>$rs['ext']['password'],
	'mobile'=>$extinfo['mobile'],
	'content'=>$extinfo['content']
);
$this->lib('html')->set_post(true);
$info = $this->lib('html')->get_content($url,http_build_query($data));
if(!$info || strpos($info,'<returnsms>') === false){
	return false;
}
$info = $this->lib('xml')->read($info,false);
if(!$info){
	return false;
}
$returnstatus = $info['returnstatus'] ? strtolower($info['returnstatus']) : 'fail';
if($returnstatus == 'success'){
	return true;
}
if($this->config['debug']){
	phpok_log(print_r($info,true));
}
return false;