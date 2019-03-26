<?php
/**
 * 國信通發簡訊介面
 * @package phpok\gateway\sms\gxt106com
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年11月17日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
$update = $this->get('update');
if($update){
	$mobile = $this->get('mobile');
	if(!$mobile){
		$this->error('未指定手機號');
	}
	if(!$this->lib('common')->tel_check($mobile,'mobile')){
		$this->error('手機號格式不正式');
	}
	$content = $this->get('content');
	if(!$content){
		$this->error('未指定要傳送的內容');
	}
	$url = $rs['ext']['server'] ? $rs['ext']['server'] : "http://www.gxt106.com/sms.aspx";
	$data = array(
		'action'=>'send',
		'userid'=>$rs['ext']['companyid'],
		'account'=>$rs['ext']['account'],
		'password'=>$rs['ext']['password'],
		'mobile'=>$mobile,
		'content'=>$content
	);
	$this->lib('html')->set_post(true);
	$info = $this->lib('html')->get_content($url,http_build_query($data));
	if(!$info || strpos($info,'<returnsms>') === false){
		$this->error('簡訊傳送失敗');
	}
	$info = $this->lib('xml')->read($info,false);
	if(!$info){
		$this->error('簡訊傳送失敗');
	}
	$returnstatus = $info['returnstatus'] ? strtolower($info['returnstatus']) : 'fail';
	if($returnstatus == 'success'){
		$this->success('簡訊傳送成功');
		return true;
	}
	$this->error($info['message']);
	return false;
}
$this->view($this->dir_root.'gateway/'.$rs['type'].'/sendsms.html','abs-file');