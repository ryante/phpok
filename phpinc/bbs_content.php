<?php
/*****************************************************************************************
	檔案： phpinc/bbs_content.php
	備註： 論壇內容
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2015年02月18日 08時47分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
if($rs['user_id']){
	$rs['user'] = $app->model('user')->get_one($rs['user_id']);
}
$pageid = $app->get('pageid','int');
if(!$pageid){
	$pageid = 1;
}
$comment = phpok('_comment','tid='.$rs['id'].'&pageid='.$pageid.'&psize=10');
if($comment['rslist']){
	foreach($comment['rslist'] as $key=>$value){
		$layer = ($key+1) * $pageid;
		if($layer == 1){
			$layer ='沙發';
		}elseif($layer == 2){
			$layer = '板凳';
		}else{
			$layer .= '樓';
		}
		$value['_layer'] = $layer;
		$comment['rslist'][$key] = $value;
		unset($layer);
	}
}