<?php
/**
 * 莫名簡訊，獲取剩餘簡訊數
 * @package phpok\gateway\sms\duanxincm
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年11月17日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
$url = $rs['ext']['server'] ? $rs['ext']['server'] : "http://api.duanxin.cm/";
$data = array(
	'action'=>'getBalance',
	'username'=>$rs['ext']['account'],
	'password'=>md5($rs['ext']['password'])
);
$url .= "?";
foreach($data as $key=>$value){
	$url .= $key.'='.rawurlencode($value).'&';
}
$info = $this->lib('html')->get_content($url);
if(!$info){
	return false;
}
$list = explode("||",$info);
if($list[0] == '100'){
	$count = intval($list[1]);
	if($count<1){
		return '請充值，當前賬戶沒有剩餘簡訊數量';
	}
	if($count<30){
		return '您當前可用簡訊數量僅餘<span style="color:red">'.$count.'</span>條，請及時充值';
	}
	return '您當前還可以使用<span style="color:red">'.$count.'</span>條簡訊';
}
$this->error('驗證失敗');
return false;