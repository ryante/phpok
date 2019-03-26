<?php
/**
 * 檢查是否購買了，才啟用評論
 * @package phpok
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年06月15日
**/
$is_comment = false;
if($rs && $session['user_id'] && $page_rs['comment_status']){
	//檢測訂單中是否有此人購買的產品
	$sql  = "SELECT p.id FROM ".$app->db->prefix."order_product p LEFT JOIN ".$app->db->prefix."order o ON(p.order_id=o.id) WHERE p.tid='".$rs['id']."' ";
	$sql .= "AND o.user_id='".$session['user_id']."' AND o.status IN('end','shipping','received','stop') LIMIT 1";
	$chk = $app->db->get_one($sql);
	if($chk && $chk['id']){
		$is_comment = true;
	}
}