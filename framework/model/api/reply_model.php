<?php
/*****************************************************************************************
	檔案： {phpok}/model/api/reply_model.php
	備註： 評論相關
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2015年08月14日 20時44分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class reply_model extends reply_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	function check_time($tid,$uid='',$sessid='')
	{
		if(!$uid && !$sessid){
			return false;
		}
		$sql = "SELECT addtime FROM ".$this->db->prefix."reply WHERE tid='".$tid."'";
		if($uid){
			$sql .= " AND uid='".$uid."'";
		}else{
			$sessid  .= " AND session_id='".$sessid."'";
		}
		$sql .= " ORDER BY addtime DESC LIMIT 1";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return true;
		}
		if(($rs['addtime'] + 30) > $this->time){
			return false;
		}
		return true;
	}
}

?>