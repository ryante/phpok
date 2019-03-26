<?php
/**
 * 購物車相關全域性操作
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年08月17日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cart_model_base extends phpok_model
{
	/**
	 * 購物車ID，僅限內部使用
	**/
	private $_id;

	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 獲取購物車ID，如果不存在，系統會嘗試建立
	 * @引數 $sessid 使用者Session ID
	 * @引數 $uid 會員ID，為0表示遊客下單
	 * @返回 數字ID
	**/
	public function cart_id($sessid='',$uid=0)
	{
		if(!$sessid){
			$sessid = $this->session->sessid();
		}
		if(!$uid && $this->session->val('user_id')){
			$uid = $this->session->val('user_id');
		}
		$sql = "SELECT id FROM ".$this->db->prefix."cart WHERE session_id='".$sessid."'";
		if($uid){
			$sql .= " OR user_id='".$uid."'";
		}
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			$array = array('session_id'=>$sessid,'user_id'=>$uid,'addtime'=>$this->time);
			$this->_id = $this->db->insert_array($array,'cart');
			return $this->_id;
		}
		if($tmplist && count($tmplist) == 1){
			$rs = current($tmplist);
			$array = array('session_id'=>$sessid,'user_id'=>$uid,'addtime'=>$this->time);
			$this->db->update_array($array,'cart',array('id'=>$rs['id']));
			$this->_id = $rs['id'];
			return $this->_id;
		}
		//合併購物車
		$array = array('session_id'=>$sessid,'user_id'=>$uid,'addtime'=>$this->time);
		$this->_id = $this->db->insert_array($array,'cart');
		$idlist = array();
		foreach($tmplist as $key=>$value){
			$sql = "UPDATE ".$this->db->prefix."cart_product SET cart_id='".$this->_id."' WHERE cart_id='".$value['id']."'";
			$this->db->query($sql);
			$idlist[] = $value['id'];
		}
		$sql = "DELETE FROM ".$this->db->prefix."cart WHERE id IN(".implode(",",$idlist).")";
		$this->db->query($sql);
		return $this->_id;
	}

	/**
	 * 取得購物車資訊
	 * @引數 $cart_id 購物車ID，留空使用系統的$this->_id
	 * @引數 $condition fixed 條件查詢，當為陣列時表示多個ID，當為數字時表示單個ID
	 * @返回 false 或購物車裡的產品資訊
	 * @更新時間 2016年08月19日
	**/
	public function get_all($cart_id='',$condition='')
	{
		if(!$cart_id){
			$cart_id = $this->_id;
			if(!$cart_id){
				return false;
			}
		}
		if($condition && is_numeric($condition)){
			$condition = "id='".$condition."'";
		}
		if($condition && is_array($condition)){
			if($condition){
				$condition = "id IN(".implode(",",$condition).")";
			}
		}
		$sql = "SELECT * FROM ".$this->db->prefix."cart_product WHERE cart_id='".$cart_id."'";
		if($condition && is_string($condition)){
			$sql .= " AND ".$condition;
		}
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if($value['ext']){
				$value['_attrlist'] = $this->product_ext_to_array($value['ext'],$value['tid']);
				$rslist[$key] = $value;
			}
		}
		return $rslist;
	}

	/**
	 * 取得購物車裡的產品詳細資訊
	 * @引數 $id 購物車產品（表cart_product）裡的id
	 * @返回 陣列
	 * @更新時間 2016年09月01日
	**/
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."cart_product WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 更新購物車裡的產品數量
	 * @引數 $id 購物車產品（表cart_product）裡的id
	 * @引數 $qty 數量，不能小於1
	 * @返回 true 或 false
	**/
	public function update($id,$qty=1)
	{
		$id = intval($id);
		if(!$id){
			return false;
		}
		$qty = intval($qty);
		if($qty < 1){
			$qty = 1;
		}
		$sql = "UPDATE ".$this->db->prefix."cart_product SET qty='".$qty."' WHERE id='".$id."'";
		$this->db->query($sql);
		$rs = $this->get_one($id);
		if($rs){
			$this->update_cart_time($rs['cart_id']);
		}
		return true;
	}

	/**
	 * 新增產品資料
	 * @引數 $data 陣列
	 * @返回 false 或插入的id
	**/
	public function add($data)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		$this->update_cart_time($data['cart_id']);
		$data['ext'] = $this->product_ext_to_string($data['ext']);
		return $this->db->insert_array($data,'cart_product');
	}

	/**
	 * 更新購物車操作時間
	 * @引數 $cart_id 購物車ID
	**/
	public function update_cart_time($cart_id)
	{
		$sql = "UPDATE ".$this->db->prefix."cart SET addtime='".$this->time."' WHERE id='".$cart_id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得購物車下的產品數量
	 * @引數 $cart_id 購物車ID
	**/
	public function total($cart_id)
	{
		$sql = "SELECT SUM(qty) FROM ".$this->db->prefix."cart_product WHERE cart_id='".$cart_id."'";
		return $this->db->count($sql);
	}

	/**
	 * 刪除購物車資訊
	 * @引數 $cart_id 購物車ID
	 * @引數 $ids 要刪除的產品ID，陣列
	**/
	public function delete($cart_id,$ids='')
	{
		$condition = '';
		if($ids && is_numeric($ids)){
			$condition = "id='".$ids."'";
		}
		if($ids && is_array($ids)){
			$condition = "id IN(".implode(",",$ids).")";
		}
		if($condition){
			$sql = "DELETE FROM ".$this->db->prefix."cart_product WHERE cart_id='".$cart_id."' AND ".$condition;
			$this->db->query($sql);
			return true;
		}
		$sql = "DELETE FROM ".$this->db->prefix."cart WHERE id='".$cart_id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."cart_product WHERE cart_id='".$cart_id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 刪除產品
	 * @引數 $id 主鍵ID，這裡說明下，不是產品ID
	**/
	public function delete_product($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."cart_product WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 清空購物車下的產品資料
	 * @引數 $cart_id 購物車ID
	**/
	public function clear_cart($cart_id='')
	{
		if(!$cart_id){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."cart_product WHERE cart_id='".$cart_id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 清空超過24小時的購物車
	**/
	public function clear_expire_cart()
	{
		$oldtime = $this->time - 24 * 60 *60;
		$sql = "DELETE FROM ".$this->db->prefix."cart WHERE addtime<".$oldtime;
		$this->db->query($sql);
		$sql = "SELECT id FROM ".$this->db->prefix."cart LIMIT 1";
		$tmp = $this->db->get_one($sql);
		if($tmp){
			$sql = "DELETE FROM ".$this->db->prefix."cart_product WHERE cart_id NOT IN(SELECT id FROM ".$this->db->prefix."cart)";
			$this->db->query($sql);
			return true;
		}
		$sql = "TRUNCATE ".$this->db->prefix."cart_product";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 計算運費
	 * @引數 $data 陣列，裡面包含：number數量，weight重量，volume體積
	 * @引數 $province 省份
	 * @引數 $city 城市
	 * @返回 false 或 實際運費
	 * @更新時間 2016年09月11日
	**/
	public function freight_price($data,$province='',$city='')
	{
		if(!$data || !$province || !$city){
			return false;
		}
		if(!$this->site['biz_freight']){
			return false;
		}
		$freight = $this->model('freight')->get_one($this->site['biz_freight']);
		if(!$freight){
			return false;
		}
		$param_val = 'fixed';
		$data['fixed'] = 'fixed';
		if($data[$freight['type']]){
			$param_val = $data[$freight['type']];
		}
		if(!$param_val){
			return false;
		}
		$zone_id = $this->model('freight')->zone_id($freight['id'],$province,$city);
		if(!$zone_id){
			return false;
		}
		$val = $this->model('freight')->price_one($zone_id,$param_val);
		if($val){
			if(strpos($val,'N') !== false){
				$val = str_replace("N",$param_val,$val);
				eval("\$val = $val;");
			}
			return $val;
		}
		return false;
	}

	/**
	 * 購物車產品屬性擴充套件陣列資料格式化為字串，如果值都是數字，則用英文逗號隔開，非數字則用serialize序列化
	 * @引數 $data 要格式化的內容，必須是陣列
	**/
	public function product_ext_to_string($data='')
	{
		if(!$data || !is_array($data)){
			return false;
		}
		$is_num = true;
		foreach($data as $key=>$value){
			if(!is_numeric($key) || !is_numeric($value)){
				$is_num = false;
				break;
			}
		}
		if($is_num){
			return implode(",",$data);
		}
		return serialize($data);
	}

	/**
	 * 購物車產品中的擴充套件屬性資料轉化為陣列，多數字組合則讀取產品屬性表，反之則用unserialize反序列化
	 * @引數 $data 要格式化的資料，字串
	 * @引數 $tid 產品ID（僅限資料為數字及英文逗號組成）
	**/
	public function product_ext_to_array($data='',$tid=0)
	{
		if(!$data || ($data && is_array($data))){
			return false;
		}
		if(strpos($data,':{') !== false){
			$list = unserialize($data);
			if(!$list){
				return false;
			}
			$tmparray = array();
			foreach($list as $key=>$value){
				$tmp = array('title'=>$key,'val'=>$value,'content'=>$value);
				$tmparray[] = $tmp;
			}
			return $tmparray;
		}
		if(!$tid){
			return explode(",",$data);
		}
		$sql = "SELECT a.title,v.title content,v.val FROM ".$this->db->prefix."list_attr l ";
		$sql.= "LEFT JOIN ".$this->db->prefix."attr a ON(l.aid=a.id AND a.site_id=".$this->site_id.") ";
		$sql.= "LEFT JOIN ".$this->db->prefix."attr_values v ON(l.vid=v.id AND l.aid=v.aid) ";
		$sql.= "WHERE l.tid='".$tid."' AND l.id IN(".$data.") ";
		return $this->db->get_all($sql);
	}

	/**
	 * 產品屬性引數比較，如果相同返回 true，不同返回 false
	 * @引數 $data 屬性1
	 * @引數 $check 屬性2
	**/
	public function product_ext_compare($data,$check)
	{
		if(!$data && !$check){
			return true;
		}
		if(($data && !$check) ||(!$data && $check)){
			return false;
		}
		if(is_string($data)){
			$data = $this->product_ext_to_array($data);
		}
		if(is_string($check)){
			$check = $this->product_ext_to_array($check);
		}
		$status = false;
		$diff1 = array_diff($data,$check);
		$diff2 = array_diff($check,$data);
		if($diff1 || $diff2){
			return false;
		}
		return true;
	}
}