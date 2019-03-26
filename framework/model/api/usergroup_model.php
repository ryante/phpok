<?php
/*****************************************************************************************
	檔案： {phpok}/model/api/usergroup_model.php
	備註： API介面下的會員Model處理
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2014年10月24日 11時11分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class usergroup_model extends usergroup_model_base
{
	function __construct()
	{
		parent::__construct();
	}

	//通過會員取得會員組資訊
	function group_rs($uid=0)
	{
		$gid = $this->group_id($uid);
		if(!$gid)
		{
			return false;
		}
		return $this->one($gid);
	}

	function get_default($status=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_group WHERE is_default=1 AND status=1";
		return $this->db->get_one($sql);
	}

	//讀取單個會員組資訊
	function one($id)
	{
		$rslist = $this->all();
		if(!$rslist)
		{
			return false;
		}
		$rs = false;
		foreach($rslist as $key=>$value)
		{
			if($value['id'] == $id)
			{
				$rs = $value;
				break;
			}
		}
		return $rs;
	}

	function all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_group WHERE status=1";
		return $this->db->get_all($sql);
	}
}
?>