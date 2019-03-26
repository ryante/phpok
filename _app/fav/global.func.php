<?php
/**
 * 公共函式
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年06月04日
**/


/**
 * 返回主題被收藏的次數
 * @引數 $title_id 主題ID
 * @內容頁模板程式碼 {func fav_count $rs.id} 
**/
function fav_count($title_id=0)
{
	return $GLOBALS['app']->model('fav')->title_fav_count($title_id);
}

/**
 * 檢測主題是否已被收藏
 * @引數 $title_id 主題ID
 * @引數 $user_id 會員ID，留空直接通過 session 獲取
 * @內容示例 {if fav_check($rs.id,$session.user_id)}
**/
function fav_check($title_id=0,$user_id=0)
{
	if(!$user_id){
		$user_id = $GLOBALS['app']->session->val('user_id');
	}
	return $GLOBALS['app']->model('fav')->chk($title_id,$user_id);
}