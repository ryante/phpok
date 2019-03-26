<?php
/**
 * 顯示會員組
 * @package phpok\phpinc
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @許可 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年06月21日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
if($session['user_gid']){
	$usergroup = $app->model('usergroup')->get_one($session['user_gid']);
}