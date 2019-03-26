<?php
/*****************************************************************************************
	檔案： phpinc/all.php
	備註： 呼叫全部主題資訊，指定屬性
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2015年03月24日 16時32分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
function phpok_all($attr='',$psize=10)
{
	$sql = "SELECT * FROM ".$GLOBALS['app']->db->prefix."list WHERE status=1 AND hidden=0 ";
	if($attr){
		$sql .= " AND attr LIKE '%".$attr."%' ";
	}
	$sql .= " ORDER BY sort ASC,id DESC LIMIT ".intval($psize);
	$tmplist = $GLOBALS['app']->db->get_all($sql);
	if(!$tmplist){
		return false;
	}
	$rslist = array();
	foreach($tmplist as $key=>$value){
		$value['url'] = $value['identifier'] ? $GLOBALS['app']->url($value['identifier']) : $GLOBALS['app']->url($value['id']);
		$rslist[$value['id']] = $value;
	}
	return $rslist;
}
?>