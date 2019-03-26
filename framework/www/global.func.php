<?php
/**
 * 前臺公共函式
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年07月05日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

if(!function_exists("phpok_next")){
	function phpok_next($rs)
	{
		if(!$rs){
			return false;
		}
		$next_id = $GLOBALS['app']->model('list')->get_next($rs);
		if(!$next_id){
			return false;
		}
		return phpok('_arc',"title_id=".$next_id);
	}
}

if(!function_exists("phpok_prev")){
	function phpok_prev($rs)
	{
		if(!$rs){
			return false;
		}
		$prev_id = $GLOBALS['app']->model('list')->get_prev($rs);
		if(!$prev_id){
			return false;
		}
		return phpok('_arc',"title_id=".$prev_id);
	}
}