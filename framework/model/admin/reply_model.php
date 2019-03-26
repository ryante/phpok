<?php
/**
 * 管理評論
 * @package phpok\model\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年07月29日
**/

if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class reply_model extends reply_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 獲取指定主題下的評論數統計，返回統計陣列，為空返回false
	 * @param mixed $ids 主題ID，字串或陣列
	 * @date 2016年02月14日
	 */
	public function comment_stat($ids)
	{
		if(!$ids){
			return false;
		}
		if(is_array($ids)){
			$ids = implode(",",$ids);
		}
		$sql = "SELECT count(tid) as total,tid FROM ".$this->db->prefix."reply WHERE tid IN(".$ids.") GROUP BY tid";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$rslist[$value['tid']] = array('total'=>$value['total'],'uncheck'=>0);
		}
		$sql = "SELECT count(tid) as total,tid FROM ".$this->db->prefix."reply WHERE tid IN(".$ids.") AND status=0 GROUP BY tid";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return $rslist;
		}
		foreach($tmplist as $key=>$value){
			$rslist[$value['tid']]['uncheck'] = $value['total'];
		}
		return $rslist;
	}

	/**
	 * 取得回覆統計
	 * @引數 $condition 查詢條件
	 * @引數 $offset 起始值
	 * @引數 $psize 每頁查詢數
	**/
	public function get_all($condition="",$offset=0,$psize=30)
	{
		$rslist = parent::get_all($condition,$offset,$psize);
		if(!$rslist){
			return false;
		}
		$rslist = $this->_res($rslist);
		$rslist = $this->_reply($rslist);
		return $rslist;
	}

	/**
	 * 評論型別
	**/
	public function types()
	{
		$list = array('title'=>P_Lang('主題'),'cate'=>P_Lang('分類'),'project'=>P_Lang('分類'),'order'=>P_Lang('訂單'));
		return $list;
	}

}