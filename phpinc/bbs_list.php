<?php
/*****************************************************************************************
	檔案： php/bbs_list.php
	備註： 格式化論壇主題
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2014年10月4日
*****************************************************************************************/
/**
 * 格式化論壇主題
 * @package phpok\phpinc
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年06月21日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
//常用專案引數
//多少小時內發的貼子或回覆，顯示為最新主題
$hour = 6;
if($rslist){
	foreach($rslist as $key=>$value){
		$icon = $value['toplevel'] ? 'am-icon-angle-double-up'.$value['toplevel'] : 'am-icon-angle-right';
		$time = $value['replydate'] ? $value['replydate'] : $value['dateline'];
		if(($time + $hour * 3600) > $sys['time']){
			$icon = 'am-icon-angle-double-right am-primary';
		}
		$value['_icon'] = $icon;
		$value['_user'] = $value['user'] ? $value['user'] : array('user'=>'佚名');
		$value['_author'] = $value['_user']['user'];
		$value['_author_url'] = $value['user_id'] ? $app->url('user','info','id='.$value['user_id']) : '';
		$value['_lastdate'] = $value['replydate'] ? time_format($value['replydate']) : time_format($value['dateline']);
		$rslist[$key] = $value;
	}
}