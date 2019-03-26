<?php
/**
 * 刪除檔案操作
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2019年1月20日
**/

$filename = $this->get('filename');
$r = array('status'=>false);
if(!$filename){
	$r['info'] = P_Lang('沒有傳引數');
	return $r;
}
$this->lib('qiniu')->ak($extinfo['appkey']);
$this->lib('qiniu')->sk($extinfo['appsecret']);
$this->lib('qiniu')->bucket($extinfo['bucket']);
$this->lib('qiniu')->delete_file($filename);
$r['status'] = true;
return $r;