<?php
/**
 * CSS樣式集合器，用於合併多個CSS
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年01月20日
**/

error_reporting(E_ALL ^ E_NOTICE);
function_exists("ob_gzhandler") ? ob_start("ob_gzhandler") : ob_start();
header("Content-type: text/css; charset=utf-8");
define("ROOT",str_replace("\\","/",dirname(__FILE__))."/");
$type = isset($_GET['type']) ? $_GET['type'] : 'default';
if(!$type || ($type && !in_array($type,array('default','admin','open')))){
	$type = 'default';
}

//後臺首頁涉及到的樣式檔案
$file = array();

//後臺首頁
if($type == 'admin'){
	$file[] = 'artdialog.css';
	$file[] = 'icomoon.css';
	$file[] = 'smartmenu.css';
	$file[] = 'admin.css';
}

//後臺彈視窗
if($type == 'open'){
	$file[] = 'icomoon.css';
	$file[] = 'open.css';
	$file[] = 'artdialog.css';
	$file[] = 'form.css';
	$file[] = 'smartmenu.css';
	//使用 selectpage 下拉選單
	$file[] = 'selectpage.css';
}

//後臺桌面視窗
if($type == 'default'){
	$file[] = 'admin-index.css';
	$file[] = 'window.css';
	$file[] = 'artdialog.css';
	$file[] = 'icomoon.css';
}

$file = array_unique($file);
foreach($file as $key=>$value){
	$value = basename($value);
	if(is_file(ROOT.$value)){
		echo file_get_contents(ROOT.$value);
		echo "\n";
	}
}
exit;