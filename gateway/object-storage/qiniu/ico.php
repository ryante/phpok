<?php
/**
 * 獲取圖片的後臺縮圖
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2019年1月20日
**/
$filename = $this->get('filename');
if(!$filename){
	return false;
}
$filext = $this->get('filext');
if(!$filext){
	$filext = 'unknown';
}
$ico = 'images/filetype-large/'.$filext.'.jpg';
$extlist = array('png','gif','jpeg','jpg');
//裁剪縮圖
if(in_array($filext,$extlist)){
	$ico = $filename.'?imageView2/1/w/200';
}else{
	if(!is_file($this->dir_root.$ico)){
		$ico = 'images/filetype-large/unknown.jpg';
	}
}
return $ico;