<?php
/**
 * 七牛雲端儲存操作
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
$this->addjs($this->dir_webroot.'js/qiniu/qiniu.min.js');
$this->addjs($this->dir_webroot.'js/qiniu/upload.func.js');
$this->lib('qiniu')->ak($extinfo['appkey']);
$this->lib('qiniu')->sk($extinfo['appsecret']);
$this->lib('qiniu')->bucket($extinfo['bucket']);
$info = $this->lib('qiniu')->token();
$this->assign('_token',$info);
$this->assign('qiniu_server_url',$extinfo['server_url']);
$this->assign('qiniu_view_url',$extinfo['view_url']);
$f = $this->tpl->val('_rs');
if($f && $f['cate_id']){
	$cate_rs = $this->model('rescate')->get_one($f['cate_id']);
	$folder = $cate_rs['root'];
	if($cate_rs['folder']){
		$folder .= date($cate_rs['folder'],$this->time);
	}
	$this->assign('folder',$folder);
}
$this->assign('gateway_rs',$rs);

$tplfile = $this->dir_gateway.'object-storage/qiniu/btn.html';
return $this->fetch($tplfile,'abs-file');