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
	$url = $rs['ext']['server'] ? $rs['ext']['server'] : "http://api.duanxin.cm/";
	$data = array(
		'action'=>'send',
		'username'=>$rs['ext']['account'],
		'password'=>md5($rs['ext']['password']),
		'phone'=>$mobile,
		'content'=>$content,
		'encode'=>'utf8'
	);
	$url .= "?";
	foreach($data as $key=>$value){
		$url .= $key.'='.rawurlencode($value).'&';
	}
	$info = $this->lib('html')->get_content($url);
	if($info == '100'){
		$this->success('簡訊傳送成功');
		return true;
	}
	$tip = '傳送錯誤';
	if($info == '101'){
		$tip = '驗證失敗';
	}
	if($info == '102'){
		$tip = '簡訊不足';
	}
	if($info == '103'){
		$tip = '操作失敗';
	}
	if($info == '104'){
		$tip = '非法字元';
	}
	if($info == '105'){
		$tip = '內容過多';
	}
	if($info == '106'){
		$tip = '號碼過多';
	}
	if($info == '107'){
		$tip = '頻率過快';
	}
	if($info == '108'){
		$tip = '號碼內容空';
	}
	if($info == '109'){
		$tip = '賬號凍結';
	}
	if($info == '110'){
		$tip = '禁止頻繁單條傳送';
	}
	if($info == '111'){
		$tip = '系統暫定傳送';
	}
	if($info == '112'){
		$tip = '號碼錯誤';
	}
	if($info == '113'){
		$tip = '定時時間格式不對';
	}
	if($info == '114'){
		$tip = '賬號被鎖，10分鐘後登入';
	}
	if($info == '115'){
		$tip = '連線失敗';
	}
	if($info == '116'){
		$tip = '禁止介面傳送';
	}
	if($info == '117'){
		$tip = '繫結IP不正確';
	}
	if($info == '120'){
		$tip = '系統升級';
	}
	$this->error($tip);
	return false;
}
$this->view($this->dir_root.'gateway/'.$rs['type'].'/sendsms.html','abs-file');