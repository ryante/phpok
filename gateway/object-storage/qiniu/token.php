<?php
/**
 * 七牛獲取Token請求
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2019年1月18日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

$r = array('status'=>false);
if(!$extinfo){
	$r['info'] = P_Lang('引數不完整，請檢查');
	return $r;
}

//非管理員，檢測是否有上傳許可權
if(!$this->session->val('admin_id')){
	if(!$this->session->val('user_id') && !$this->site['upload_guest']){
		$r['info'] = P_Lang('你沒有上傳許可權');
		return $r;
	}
	if($this->session->val('user_id') && !$this->site['upload_user']){
		$r['info'] = P_Lang('你沒有上傳許可權');
		return $r;
	}
}
$this->lib('qiniu')->ak($extinfo['appkey']);
$this->lib('qiniu')->sk($extinfo['appsecret']);
$this->lib('qiniu')->bucket($extinfo['bucket']);
$this->lib('qiniu')->url($this->url.$this->config['api_file']);
$info = $this->lib('qiniu')->token();
$r['info'] = $info;
$r['status'] = true;
return $r;