<?php
/**
 * 購物車介面請求相關
 * @package phpok\api
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年08月19日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cart_control extends phpok_control
{
	/**
	 * 購物車ID，該ID將貫穿整個購物過程
	**/
	private $cart_id = 0;

	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::control();
		$this->cart_id = $this->model('cart')->cart_id($this->session->sessid(),$this->session->val('user_id'));
	}

	/**
	 * 加入購物車
	 * @引數 id 產品ID
	 * @引數 title 產品名稱（當產品ID不存在時）
	 * @引數 qty 產品數量，僅支援數字
	 * @引數 ext 產品性屬，僅有id時有效，只支援數字
	 * @引數 price 產品價格，僅當id為空時有效
	 * @引數 thumb 產品縮圖，僅當id為空時有效
	 * @引數 _clear 設為1表示立即訂購
	 * @返回 JSON資料
	 * @更新時間 2018年04月22日
	**/
	public function add_f()
	{
		if(!$this->cart_id){
			$this->error(P_Lang('購物車編號異常'));
		}
		$clear = $this->get('_clear');
		$id = $this->get('id','int');
		$qty = $this->get('qty','int');
		if(!$qty){
			$qty = 1;
		}
		$array = $id ? $this->product_from_tid($id) : $this->product_from_title();
		$rslist = $this->model('cart')->get_all($this->cart_id);
		$updateid = $total = 0;
		if(!$rslist){
			$rslist = array();
		}
		foreach($rslist as $key=>$value){
			if($id){
				if($value['tid'] == $id && $this->model('cart')->product_ext_compare($array['ext'],$value['ext'])){
					$updateid = $value['id'];
					$total = $value['qty'];
				}
			}else{
				if($value['title'] == $array['title'] && $array['price'] == $value['price'] && $this->model('cart')->product_ext_compare($array['ext'],$value['ext'])){
					$updateid = $value['id'];
					$total = $value['qty'];
				}
			}
		}
		if($updateid){
			$this->model('cart')->update($updateid,($total+$qty));
			$insert_id = $updateid;
		}else{
			$array['qty'] = $qty;
			$insert_id = $this->model('cart')->add($array);
		}
		$this->success($insert_id);
	}

	private function product_from_title()
	{
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('產品名稱不能為空'));
		}
		$array = array('cart_id'=>$this->cart_id);
		$array['title'] = $title;
		$array['price'] = $this->get('price','float');
		$array['tid'] = 0;
		$array['weight'] = $this->get('weight','float');
		$array['volume'] = $this->get('volume','float');
		$ext = $this->get('ext');
		if($ext && is_array($ext)){
			sort($ext);
		}
		$array['ext'] = $ext;
		$array['thumb'] = $this->get('thumb');
		$array['is_virtual'] = $this->get('is_virtual','int');
		return $array;
	}

	/**
	 * 產品資料從id傳過來的值生成
	 * @引數 $id 產品ID
	 * @引數 $qty 數量
	**/
	private function product_from_tid($id,$qty=0)
	{
		$array = array('tid'=>$id,'cart_id'=>$this->cart_id);
		$rs = $this->call->phpok('_arc',array('site'=>$this->site['id'],'title_id'=>$id));		
		if(!$rs){
			$this->error(P_Lang('產品資訊不存在或未啟用'));
		}
		$thumb_id = $this->config['cart']['thumb_id'] ? $this->config['cart']['thumb_id'] : 'thumb';
		if($rs[$thumb_id]){
			if(is_string($rs[$thumb_id])){
				$array['thumb'] = $rs[$thumb_id];
			}
			if(is_array($rs[$thumb_id])){
				$array['thumb'] = $rs[$thumb_id]['filename'];
			}
		}
		$array['title'] = $rs['title'];
		$array['is_virtual'] = $rs['is_virtual'];
		$array['unit'] = $rs['unit'];
		$ext = $this->get('ext');
		$int = false;
		if($ext && (is_string($ext) || is_int($ext))){
			$int = true;
			$ext = explode(",",$ext);
			foreach($ext as $key=>$value){
				$value = intval($value);
				if(!$value){
					unset($ext[$key]);
					continue;
				}
				$ext[$key] = $value;
			}
		}
		if($ext && is_array($ext)){
			sort($ext);
		}
		$price = price_format_val($rs['price'],$rs['currency_id'],$this->site['currency_id']);
		$weight = $rs['weight'];
		$volume = $rs['volume'];
		if($ext && $rs['attrlist'] && $int){
			foreach($rs['attrlist'] as $key=>$value){
				foreach($value['rslist'] as $k=>$v){
					if(in_array($v['id'],$ext)){
						$price = floatval($price) + price_format_val($v['price'],$rs['currency_id'],$this->site['currency_id']);
						$weight = floatval($weight) + floatval($v['weight']);
						$volume = floatval($volume) + floatval($v['volume']);
					}
				}
			}
		}
		$array['ext'] = $ext;
		$array['price'] = $price;
		$array['weight'] = $weight;
		$array['volume'] = $volume;
		return $array;
	}

	/**
	 * 獲取購物車裡的產品總數
	**/
	public function total_f()
	{
		$total = $this->model('cart')->total($this->cart_id);
		$this->success($total);
	}

	public function clear_f()
	{
		$this->model('cart')->clear_cart($this->cart_id);
		$this->success();
	}

	/**
	 * 更新購物車裡的產品數量
	 * @引數 id 購物車裡的產品ID，即表（cart_product）的主鍵id，不是產品ID
	 * @引數 qty 更新購物車數量，不能小於1
	 * @返回 
	 * @更新時間 
	**/
	public function qty_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定產品ID'));
		}
		$rs = $this->model('cart')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('產品不存在'));
		}
		if($rs['cart_id'] != $this->cart_id){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$qty = $this->get('qty');
		if(!$qty){
			$qty = $rs['qty'];
		}
		$this->model('cart')->update($id,$qty);
		$rs["qty"] = $qty;
		$this->success($rs);
	}

	/**
	 * 刪除購物車產品資料
	 * @引數 id 購物車裡的產品ID，即表（cart_product）的主鍵id，不是產品ID
	 * @返回 JSON資料
	**/
	public function delete_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定產品ID'));
		}
		if(is_string($id)){
			$list = explode(",",$id);
			unset($id);
			$id = array();
			foreach($list as $key=>$value){
				if($value && intval($value)){
					$id[] = intval($value);
				}
			}
		}
		if($id && is_array($id)){
			foreach($id as $key=>$value){
				if(!$value || !intval($value)){
					unset($id[$key]);
				}
			}
		}
		if(!$id || count($id)<1){
			$this->error(P_Lang('沒有有效的產品ID'));
		}
		foreach($id as $key=>$value){
			$rs = $this->model('cart')->get_one($value);
			if(!$rs){
				$this->error(P_Lang('產品不存在'));
			}
			if($rs['cart_id'] != $this->cart_id){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
			$this->model('cart')->delete_product($value);
		}
		$this->success();
	}

	/**
	 * 格式化價格，帶貨幣符號
	 * @引數 price 單個價格或多個價格
	 * @返回 返回字串（單個價格）或陣列（多個價格）
	 * @更新時間 2016年09月01日
	**/
	public function price_format_f()
	{
		$price = $this->get('price','float');
		if(is_array($price)){
			$list = array();
			foreach($price as $key=>$value){
				$list[$key] = price_format($value,$this->site['currency_id']);
			}
			$this->success($list);
		}
		$this->success(price_format($price,$this->site['currency_id']));
	}

	/**
	 * 格式化價格，無貨幣符號
	 * @引數 price 單個價格或多個價格
	 * @返回 返回字串（單個價格）或陣列（多個價格）
	 * @更新時間 2016年09月01日
	**/
	public function price_format_val_f()
	{
		$price = $this->get('price','float');
		if(is_array($price)){
			$list = array();
			foreach($price as $key=>$value){
				$list[$key] = price_format_val($value,$this->site['currency_id']);
			}
			$this->success($list);
		}
		$this->success(price_format_val($price,$this->site['currency_id']));
	}

	public function price_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定要計算的產品ID'));
		}
		if(is_string($id)){
			$list = explode(",",$id);
			unset($id);
			$id = array();
			foreach($list as $key=>$value){
				if($value && intval($value)){
					$id[] = intval($value);
				}
			}
		}
		if($id && is_array($id)){
			foreach($id as $key=>$value){
				if(!$value || !intval($value)){
					unset($id[$key]);
				}
			}
		}
		if(!$id || count($id)<1){
			$this->error(P_Lang('沒有有效的產品ID'));
		}
		$price = 0;
		$rslist = $this->model('cart')->get_all($this->cart_id);
		if(!$rslist){
			$this->error(P_Lang('購物車資訊為空'));
		}
		foreach($rslist as $key=>$value){
			if(in_array($value['id'],$id)){
				$price += price_format_val($value['price'] * $value['qty'],$this->site['currency_id']);
			}
		}
		$show = price_format($price,$this->site['currency_id']);
		$val = price_format_val($price,$this->site['currency_id']);
		$this->success(array('val'=>$val,'price'=>$show));
	}

	public function pricelist_f()
	{
		$ids = $this->get('ids');
		if(!$ids){
			$this->error(P_Lang('未指定要計算的產品'));
		}
		$idlist = explode(",",$ids);
		unset($ids);
		$ids = array();
		foreach($idlist as $key=>$value){
			if(!$value || !intval($value)){
				continue;
			}
			$ids[] = intval($value);
		}
		unset($idlist);
		if(!$ids || count($ids)<1){
			$this->error(P_Lang('購物車裡沒有產品資訊'));
		}
		$rslist = $this->model('cart')->get_all($this->cart_id,$ids);
		if(!$rslist){
			$this->error(P_Lang('未找到產品資訊'));
		}
		$address = array();
		if($this->session->val('user_id')){
			$address_id = $this->get('address_id','int');
			if($address_id){
				$address = $this->model('address')->get_one($address_id);
			}
		}else{
			$province = $this->get('province');
			$city = $this->get('city');
			if($province && $city){
				$address = array('province'=>$province,'city'=>$city);
			}
		}
		$total = 0;
		$shipping = 0;
		foreach($rslist as $key=>$value){
			$total += floatval($value['price']) * intval($value['qty']);
			//判斷是否有運費
			if($address && $address['province'] && $address['city'] && !$value['is_virtual'] && ($value['weight'] || $value['volume'])){
				$tmp = array('number'=>$value['qty']);
				$tmp['weight'] = floatval($value['weight']) * intval($value['qty']);
				$tmp['volume'] = floatval($value['volume']) * intval($value['qty']);
				$tmp_price = $this->model('cart')->freight_price($tmp,$address['province'],$address['city']);
				if($tmp_price){
					$shipping += floatval($tmp_price);
					$total += floatval($tmp_price);
				}
			}
		}
		$list = array('all'=>price_format($total,$this->site['currency_id']));
		$list['shipping'] = price_format($shipping,$this->site['currency_id']);
		$this->success($list);
	}
}