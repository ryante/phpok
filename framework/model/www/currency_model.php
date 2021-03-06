<?php
/*****************************************************************************************
	檔案： {phpok}/model/www/currency_model.php
	備註： 貨幣型別
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2015年11月17日 01時28分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class currency_model extends currency_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_list($pri='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."currency ORDER BY taxis ASC,id DESC";
		$cache_id = $this->cache->id($sql);
		$rslist = $this->cache->get($cache_id);
		if($rslist){
			return $rslist;
		}
		$this->db->cache_set($cache_id);
		$rslist = $this->db->get_all($sql,$pri);
		if(!$rslist){
			return false;
		}
		$this->cache->save($cache_id,$rslist);
		return $rslist;
	}
}

?>