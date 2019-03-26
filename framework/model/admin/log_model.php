<?php
/**
 * 後臺操作涉及到的日誌，如日誌刪除
 * @package phpok\model\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @許可 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年05月07日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class log_model extends log_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 刪除日誌操作
	 * @引數 $condition 刪除條件
	**/
	public function delete($condition='')
	{
		$sql = "DELETE FROM ".$this->db->prefix."log ";
		if($condition){
			$sql.= "WHERE ".$condition." ";
		}
		return $this->db->query($sql);
	}
}
