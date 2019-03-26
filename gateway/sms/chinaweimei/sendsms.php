<?php
/**
 * 維美髮簡訊介面
 * @package phpok\gateway\sms\chinaweimei
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
	$url = $rs['ext']['server'] ? $rs['ext']['server'] : "http://cs.chinaweimei.com/wmsms/VerifySms.aspx";
	$data = array(
		'action'=>'send',
		'user'=>$rs['ext']['account'],
		'pass'=>$rs['ext']['password'],
		'tel'=>$mobile,
		'content'=>$content
	);
	$url .= "?";
	foreach($data as $key=>$value){
		$url .= $key.'='.rawurlencode($value).'&';
	}
	$info = $this->lib('html')->get_content($url);
	if(!$info || strpos($info,'{') === false){
		$this->error('簡訊傳送失敗');
	}
	$info = $this->lib('json')->decode($info);
	if($info['result_code'] == 'ok'){
		$this->success('簡訊傳送成功');
		return true;
	}
	$this->error($info['result_msg']);
	return false;
}
$this->view($this->dir_root.'gateway/'.$rs['type'].'/sendsms.html','abs-file');