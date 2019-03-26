<?php
/*****************************************************************************************
	檔案： {phpok}/model/api/admin_model.php
	備註： 管理員相關操作，這裡僅在API介面呼叫時有效
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2014年6月3日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_model extends admin_model_base
{
	function __construct()
	{
		parent::__construct();
	}

	function get_mail($ifsystem=0)
	{
		$sql = "SELECT id,account,email FROM ".$this->db->prefix."adm WHERE email !='' AND status=1";
		if($ifsystem)
		{
			$sql .= ' AND if_system=1 ';
		}
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return false;
		}
		return $rslist;
	}
}