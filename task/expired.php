<?php
/*****************************************************************************************
	檔案： task/expired.php
	備註： 自動刪除過期
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2015年11月17日 09時38分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
$this->cache->expired();
return true;
?>