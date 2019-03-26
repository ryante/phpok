<?php
/*****************************************************************************************
	檔案： {phpok}/model/popedom.php
	備註： 後臺管理員許可權類
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2015年02月03日 11時25分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class popedom_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	protected function _popedom_list($groupid)
	{
		$sql = "SELECT popedom FROM ".$this->db->prefix."user_group WHERE id='".$groupid."' AND status=1";
		$cache_id = $this->cache->id($sql);
		$rs = $this->cache->get($cache_id);
		if($rs){
			return explode(",",$rs[$this->site_id]);
		}
		$this->db->cache_set($cache_id);
		$rs = $this->db->get_one($sql);
		if(!$rs || !$rs['popedom']){
			return false;
		}
		$popedom = unserialize($rs['popedom']);
		if(!$popedom[$this->site_id]){
			return false;
		}
		$rs = explode(",",$popedom[$this->site_id]);
		$this->cache->save($cache_id,$popedom);
		return $rs;
	}

	//判斷是否有閱讀許可權
	//pid，為專案ID
	//groupid，為會員組ID
	public function check($pid,$groupid=0,$type='read')
	{
		$popedom = $this->_popedom_list($groupid);
		if(!$popedom){
			return false;
		}
		if(in_array($type.':'.$pid,$popedom)){
			return true;
		}
		return false;
	}
}

?>