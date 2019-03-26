<?php
/**
 * 升級 ext 表到 fields 
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年05月18日
**/

$rslist = $this->db->list_tables();
if($rslist){
	foreach($rslist as $key=>$value){
		$sql = "ALTER TABLE ".$value." ENGINE=MYISAM";
		$this->db->query($sql);
		echo "UPDATE table Engine: MYISAM OK<br />";
	}
}
