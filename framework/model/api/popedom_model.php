<?php
/*****************************************************************************************
	檔案： {phpok}/model/api/popedom_model.php
	備註： API介面讀取許可權
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2014年10月24日 11時09分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class popedom_model extends popedom_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	//設定站點ID
	public function siteid($siteid)
	{
		$this->site_id = $siteid;
	}

	//取得許可權返回值1或0
	public function val($pid,$groupid,$type='post1')
	{
		$popedom = $this->_popedom_list($groupid);
		if(!$popedom)
		{
			return false;
		}
		if(in_array($type.':'.$pid,$popedom))
		{
			return '1';
		}
		return '0';
	}
}