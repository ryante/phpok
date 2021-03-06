<?php
/**
 * 財富管理，後臺Model類
 * @package phpok\model\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2015年07月17日 00時49分
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class wealth_model extends wealth_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function chk_identifier($identifier,$id=0)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."wealth WHERE site_id='".$this->site_id."' AND identifier='".$identifier."'";
		if($id){
			$sql .= " AND id!='".$id."'";
		}
		return $this->db->get_one($sql);
	}

	public function save($data,$id=0)
	{
		if($id){
			return $this->db->update_array($data,'wealth',array('id'=>$id));
		}else{
			return $this->db->insert_array($data,'wealth');
		}
	}

	public function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."wealth SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."wealth_info WHERE wid='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."wealth_log WHERE wid='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."wealth_rule WHERE wid='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."wealth WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function delete_rule($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."wealth_rule WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 儲存規則
	 * @引數 $data 要儲存的陣列
	 * @引數 $id ID不為0時表示更新
	 * @返回 true或false或新插入的ID
	 * @更新時間 2016年07月25日
	**/
	public function save_rule($data,$id=0)
	{
		if($id){
			return $this->db->update_array($data,'wealth_rule',array('id'=>$id));
		}else{
			return $this->db->insert_array($data,'wealth_rule');
		}
	}

	/**
	 * 檢查這規則是否使用，防止重複衝突
	 * @引數 $action 執行動作
	 * @引數 $goal 目標物件
	 * @引數 $wid 財富ID
	 * @返回 有資料返回true，無資料返回false
	**/
	public function check($action,$goal,$wid,$id=0)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."wealth_rule WHERE action='".$action."' AND goal='".$goal."'";
		$sql.= " AND wid='".$wid."' ";
		if($id){
			$sql .= " AND id != '".$id."'";
		}
		$chk = $this->db->get_one($sql);
		if($chk && $chk['id']){
			return true;
		}else{
			return false;
		}
	}

	public function info_total($condition='')
	{
		$sql = "SELECT count(w.uid) FROM ".$this->db->prefix."wealth_info w ";
		$sql.= "JOIN ".$this->db->prefix."user u ON(w.uid=u.id) ";
		if($condition){
			$sql .= "WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

	public function info_list($condition='',$offset=0,$psize=30)
	{
		$sql = "SELECT w.*,u.user FROM ".$this->db->prefix."wealth_info w ";
		$sql.= "JOIN ".$this->db->prefix."user u ON(w.uid=u.id) ";
		if($condition){
			$sql .= "WHERE ".$condition." ";
		}
		$sql.= "ORDER BY w.uid DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	public function log_total($condition='')
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."wealth_log";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	public function log_list($condition='',$offset=0,$psize=30)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."wealth_log ";
		if($condition){
			$sql .= "WHERE ".$condition." ";
		}
		$sql.= "ORDER BY dateline DESC,id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	public function log_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."wealth_log WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function log_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."wealth_log WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function log_total_notcheck($condition='')
	{
		$sql = "SELECT count(l.id) FROM ".$this->db->prefix."wealth_log l";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	public function log_list_notcheck($condition='',$offset=0,$psize=30)
	{
		$sql = " SELECT l.*,w.title w_title,w.unit w_unit,u.user FROM ".$this->db->prefix."wealth_log l ";
		$sql.= " LEFT JOIN ".$this->db->prefix."wealth w ON(l.wid=w.id) ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user u ON(l.goal_id=u.id) ";
		if($condition){
			$sql .= "WHERE ".$condition." ";
		}
		$sql.= "ORDER BY l.dateline DESC,l.id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	public function setok($id)
	{
		$rs = $this->log_one($id);
		if($rs['status']){
			return true;
		}
		$val = $this->get_val($rs['goal_id'],$rs['wid']);
		$val2 = $val + $rs['val'];
		if($val2 < 0){
			$val2 = 0;
		}
		$sql = "UPDATE ".$this->db->prefix."wealth_info SET val='".$val2."',lasttime='".$this->time."' ";
		$sql.= "WHERE uid='".$rs['goal_id']."' AND wid='".$rs['wid']."'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."wealth_log SET status=1 WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}
}

?>