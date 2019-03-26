<?php
/**
 * 後臺訂單相關資料庫操作
 * @package phpok\model\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年10月04日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class order_model extends order_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 後臺訂單刪除操作
	 * @引數 $id 訂單ID號
	 * @返回 false 或 true
	 * @更新時間 
	**/
	public function delete($id)
	{
		$id = intval($id);
		if(!$id){
			return false;
		}
		//刪除訂單主表
		$sql = "DELETE FROM ".$this->db->prefix."order WHERE id=".$id;
		$this->db->query($sql);
		//刪除訂單地址資訊
		$sql = "DELETE FROM ".$this->db->prefix."order_address WHERE order_id=".$id;
		$this->db->query($sql);
		//刪除訂單物流資訊
		$sql = "DELETE FROM ".$this->db->prefix."order_express WHERE order_id=".$id;
		$this->db->query($sql);
		//刪除訂單發票資訊
		$sql = "DELETE FROM ".$this->db->prefix."order_invoice WHERE order_id=".$id;
		$this->db->query($sql);
		//刪除訂單日誌
		$sql = "DELETE FROM ".$this->db->prefix."order_log WHERE order_id=".$id;
		$this->db->query($sql);
		//刪除付款資訊
		$sql = "DELETE FROM ".$this->db->prefix."order_payment WHERE order_id=".$id;
		$this->db->query($sql);
		//刪除訂單產品資訊
		$sql = "DELETE FROM ".$this->db->prefix."order_product WHERE order_id=".$id;
		$this->db->query($sql);
		return true;
	}

	//儲存訂單各種狀態下的價格
	public function save_order_price($data)
	{
		return $this->db->insert_array($data,'order_price');
	}

	public function delete_order_price($order_id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."order_price WHERE order_id='".$order_id."'";
		return $this->db->query($sql);
	}

	public function get_list($condition='',$offset=0,$psize=30)
	{
		$sql = " SELECT o.*,u.user FROM ".$this->db->prefix."order o ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user u ON(o.user_id=u.id) ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		$sql .= " ORDER BY o.id DESC LIMIT ".$offset.",".$psize;
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		$ids = implode(",",array_keys($rslist));
		$sql = "SELECT id,order_id,title FROM ".$this->db->prefix."order_payment WHERE order_id IN(".$ids.")";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			$payments = array();
			foreach($tmplist as $key=>$value){
				$payments[$value['order_id']][] = $value['title'];
			}
			foreach($rslist as $key=>$value){
				$value['pay_title'] = '';
				if($payments[$value['id']]){
					if(count($payments[$value['id']])>2){
						$value['pay_title'] = '<span style="color:darkblue">'.P_Lang('多次付款').'</span>';
					}else{
						$value['pay_title'] = implode("/",$payments[$value['id']]);
					}
				}
				$rslist[$key] = $value;
			}
			unset($tmplist);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."order_payment WHERE order_id IN(".$ids.") AND dateline>0";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			$paid_list = array();
			foreach($tmplist as $key=>$value){
				$currency_id = (isset($value['currency_id']) && $value['currency_id']) ? $value['currency_id'] : $rslist[$value['order_id']]['currency_id'];
				if(!isset($paid_list[$value['order_id']])){
					$paid_list[$value['order_id']] = 0;
				}
				$tmp = price_format_val($value['price'],$currency_id,$rslist[$value['order_id']]['currency_id']);
				$paid_list[$value['order_id']] += floatval($tmp);
			}
			foreach($rslist as $key=>$value){
				$value['paid'] = $paid_list[$value['id']] ? $paid_list[$value['id']] : 0;
				$value['unpaid'] = round(($value['price'] - $value['paid']),4);
				$rslist[$key] = $value;
			}
		}
		return $rslist;
	}

	public function get_count($condition="")
	{
		$sql = "SELECT count(o.id) FROM ".$this->db->prefix."order o ";
		//$sql.= "LEFT JOIN ".$this->db->prefix."order_payment p ON(o.id=p.order_id) ";
		$sql.= "LEFT JOIN ".$this->db->prefix."user u ON(o.user_id=u.id) ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	public function express_all($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_express WHERE order_id='".$id."' AND express_id!=0 ";
		$sql.= "ORDER BY addtime ASC";
		return $this->db->get_all($sql);
	}

	public function express_save($data)
	{
		return $this->db->insert_array($data,'order_express');
	}

	public function express_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_express WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function express_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."order_express WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."order_log WHERE order_express_id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 更新訂單狀態，僅限後臺管理員有效
	 * @引數 $id 訂單ID
	 * @引數 $status 訂單狀態
	 * @引數 $note 訂單狀態
	 * @返回 true
	 * @更新時間 2016年10月04日
	**/
	public function update_order_status($id,$status='',$note='')
	{
		$sql = "UPDATE ".$this->db->prefix."order SET status='".$status."',status_title='".$note."' WHERE id='".$id."'";
		$this->db->query($sql);
		if(in_array($status,array('end','stop','cancel'))){
			$sql = "UPDATE ".$this->db->prefix."order SET endtime='".$this->time."' WHERE id='".$id."'";
			$this->db->query($sql);
		}
		$param = 'id='.$id."&status=".$status;
		$this->model('task')->add_once('order',$param);
		$rs = $this->get_one($id);
		if(!$note){
			$statuslist = $this->status_list();
			$note = $statuslist[$status];
		}
		$log = P_Lang('訂單（{sn}）狀態變更為：{status}',array('sn'=>$rs['sn'],'status'=>$note));
		$who = P_Lang('管理員：{admin}',array('admin'=>$this->session->val('admin_account')));
		$log = array('order_id'=>$id,'addtime'=>$this->time,'who'=>$who,'note'=>$log);
		$this->log_save($log);
		return true;
	}

	/**
	 * 整理訂單裡的產品，僅保留有效產品
	 * @引數 $id 訂單ID
	 * @引數 $order_product_ids 訂單裡的產品ID，多個ID用英文逗號隔開
	 * @返回 true
	**/
	public function order_product_clearup($id,$order_product_ids='')
	{
		if(!$id || !$order_product_ids){
			return false;
		}
		if(is_array($order_product_ids)){
			$order_product_ids = implode(",",$order_product_ids);
		}
		$sql = "DELETE FROM ".$this->db->prefix."order_product WHERE order_id='".$id."' AND id NOT IN(".$order_product_ids.")";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 檢測訂單是否需要物流，數量大於0表示需要，小於0或空或false為不需要
	 * @引數 $id 訂單ID號
	 * @返回 數值或false
	**/
	public function check_need_express($id)
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."order_product WHERE order_id='".$id."' AND is_virtual=0";
		return $this->db->count($sql);
	}
}