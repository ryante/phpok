<?php
/**
 * 升級 qinggan_user_fields 表到 fields 
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年05月18日
**/

$sql = "SELECT * FROM ".$this->db->prefix."user_fields ORDER BY id ASC";
$rslist = $this->db->get_all($sql);
if($rslist){
	foreach($rslist as $key=>$value){
		//檢查是否已經存在
		$sql = "SELECT id FROM ".$this->db->prefix."fields WHERE identifier='".$value['identifier']."' AND ftype='user'";
		$chk = $this->db->get_one($sql);
		if($chk){
			echo $value['id'].' - '.$value['identifier'].' - user - is exists<br />';
			continue;
		}
		$data = $value;
		unset($data['id'],$data['is_edit']);
		$data['is_front'] = $value['is_edit'];
		$data['ftype'] = 'user';
		$data['ext'] = $value['ext'] ? unserialize($value['ext']) : '';
		if($data['ext']){
			$data['ext'] = serialize($data['ext']);
		}
		$insert_id = $this->db->insert($data,'fields');
		if($insert_id){
			echo $value['id'].' - '.$value['identifier'].' - user - is ok<br />';
		}
	}
}
