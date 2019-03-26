<?php
/***********************************************************
	Filename: {phpok}/model/payment.php
	Note	: 付款管理器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月23日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class payment_model_base extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE id=".intval($id);
		return $this->db->get_one($sql);
	}

	function get_code($code)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE code='".$code."'";
		return $this->db->get_one($sql);
	}

	//更新狀態
	function status($id=0,$status=0,$is_id=false)
	{
		$sql = "UPDATE ".$this->db->prefix."payment SET status='".$status."' WHERE ";
		$sql.= $is_id ? " id='".$id."'" : " code='".$id."'";
		return $this->db->query($sql);
	}

	//更新手機端狀態
	function wap($id=0,$wap=0,$is_id=false)
	{
		$sql = "UPDATE ".$this->db->prefix."payment SET wap='".$wap."' WHERE ";
		$sql.= $is_id ? " id='".$id."'" : " code='".$id."'";
		return $this->db->query($sql);
	}

	//刪除支付介面
	function delete_code($code)
	{
		if(!$code) return false;
		$sql = "DELETE FROM ".$this->db->prefix."payment WHERE code='".$code."'";
		return $this->db->query($sql);
	}

	public function log_check($sn,$type='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment_log WHERE sn='".$sn."'";
		if($type){
			$sql .= " AND type='".$type."'";
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 檢查訂單是否有未支付日誌
	 * @引數 $sn 訂單標識
	 * @引數 $type 型別
	**/
	public function log_check_notstatus($sn,$type='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment_log WHERE sn='".$sn."' AND status=0";
		if($type){
			$sql .= " AND type='".$type."'";
		}
		return $this->db->get_one($sql);
	}

	public function log_update($data,$id=0)
	{
		if(!$id || !$data || !is_array($data)){
			return false;
		}
		return $this->db->update_array($data,'payment_log',array('id'=>$id));
	}

	public function log_create($data)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if(is_numeric($data['currency_id'])){
			if(!$data['currency_rate'] || $data['currency_rate'] < 0.00000001){
				$currency = $this->model('currency')->get_one($data['currency_id']);
				$data['currency_rate'] = $currency['val'];
			}
		}
		return $this->db->insert_array($data,'payment_log');
	}

	public function log_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment_log WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function log_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."payment_log WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 刪除未支付完成的支付請求
	 * @引數 $sn 訂單編號
	 * @引數 $type 訂單型別
	**/
	public function log_delete_notstatus($sn,$type='')
	{
		if(!$sn){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."payment_log WHERE sn='".$sn."' AND status=0";
		if($type){
			$sql .= " AND type='".$type."'";
		}
		return $this->db->query($sql);
	}
}