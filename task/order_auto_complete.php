<?php
/**
 * 訂單自動確認
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2019年1月11日
**/
$sql = "SELECT id,user_id FROM ".$this->db->prefix."order WHERE status IN('shipping','paid') AND (addtime+confirm_time)<".$this->time;
$rslist = $this->db->get_all($sql);
if($rslist){
	foreach($rslist as $key=>$value){
		//更新訂單狀態
		$this->model('order')->update_order_status($value['id'],'received');
		//更新訂單日誌
		$log = array('order_id'=>$value['id'],'addtime'=>$this->time);
		$log['user_id'] = $value['user_id'];
		$log['who'] = 'system';
		$log['note'] = P_Lang('系統簽收');
		$this->model('order')->log_save($log);
	}
}