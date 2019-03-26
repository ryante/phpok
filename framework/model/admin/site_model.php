<?php
/**
 * 站點資訊管理
 * @package phpok\model\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年09月08日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class site_model extends site_model_base
{
	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 儲存站點資料
	 * @引數 $data 陣列，要儲存的站點資料
	 * @引數 $id 站點ID，留空或為0表示建立新站點
	 * @返回 true/false/站點ID
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		//檢查表字段
		$fields = $this->db->list_fields('site');
		$xmldata = false;
		foreach($data as $key=>$value){
			if(!in_array($key,$fields)){
				if(!$xmldata){
					$xmldata = array();
				}
				$xmldata[$key] = $value;
				unset($data[$key]);
			}
		}
		if($id){
			$this->db->update_array($data,"site",array("id"=>$id));
			if($xmldata){
				$this->lib('xml')->save($xmldata,$this->dir_data.'xml/site_'.$id.'.xml');
			}
			return true;
		}else{
			$insert_id = $this->db->insert_array($data,"site");
			if($insert_id && $xmldata){
				$this->lib('xml')->save($xmldata,$this->dir_data.'xml/site_'.$insert_id.'.xml');
			}
			return $insert_id;
		}
	}

	/**
	 * 更新域名
	 * @引數 $domain 域名
	 * @引數 $id site_domain表的主鍵ID
	 * @返回 true/false
	**/
	public function domain_update($domain,$id)
	{
		if(!$domain || !$id){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."site_domain SET domain='".$domain."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 域名新增
	 * @引數 $domain 域名
	 * @引數 $site_id 站點ID
	 * @返回 true/false
	**/
	public function domain_add($domain,$site_id)
	{
		if(!$domain || !$site_id){
			return false;
		}
		$sql = "INSERT INTO ".$this->db->prefix."site_domain(site_id,domain) VALUES('".$site_id."','".$domain."')";
		return $this->db->insert($sql);
	}

	/**
	 * 刪除域名
	 * @引數 $id site_domain表的主鍵ID
	 * @返回 true/false
	**/
	public function domain_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."site_domain WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 儲存全域性變數擴充套件配置
	 * @引數 $data 陣列，全域性變數資訊
	 * @引數 $id all表中的主鍵ID
	**/
	public function all_save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}		
		if($id){
			return $this->db->update_array($data,"all",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"all");
		}
	}

	/**
	 * 刪除全域性擴充套件
	 * @引數 $id all表中的主鍵ID
	 * @返回 true/false
	**/
	public function ext_delete($id)
	{
		if(!$id) {
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."all WHERE id='".$id."'";
		$this->db->query($sql);
		return $this->_ext_delete('all-'.$id);
	}

	/**
	 * 刪除全域性擴充套件中的擴充套件模組欄位，僅限私有使用，不允許外調
	 * @引數 $module 模組標識
	 * @返回 true/false
	**/
	private function _ext_delete($module='')
	{
		if(!$module){
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."fields WHERE ftype='".$module."'";
		$rslist = $this->db->get_all($sql,"id");
		if($rslist){
			$id_array = array_keys($rslist);
			$ids = implode(",",$id_array);
			$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id IN(".$ids.")";
			$this->db->query($sql);
			$sql = "DELETE FROM ".$this->db->prefix."fields WHERE id IN(".$ids.")";
			$this->db->query($sql);
		}
		return true;
	}

	/**
	 * 刪除站點資訊，此方法請慎用，刪除操作沒有任何判斷及備份操作
	 * @引數 $id 站點ID
	 * @返回 true
	**/
	public function site_delete($id)
	{
		//刪除站點全域性擴充套件欄位
		$sql = "SELECT id FROM ".$this->db->prefix."all WHERE site_id='".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$this->ext_delete($value['id']);
			}
		}
		//刪除專案全域性擴充套件欄位
		$sql = "SELECT id FROM ".$this->db->prefix."project WHERE site_id='".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			$tmpids = array();
			foreach($tmplist as $key=>$value){
				$this->_ext_delete('project-'.$value['id']);
				$tmpids[] = 'p'.$value['id'];
			}
			$tmpids = implode("','",$tmpids);
			$sql = "DELETE FROM ".$this->db->prefix."tag_stat WHERE title_id IN('".$tmpids."')";
			$this->db->query($sql);
		}
		//刪除分類下的擴充套件
		$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE site_id='".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			$tmpids = array();
			foreach($tmplist as $key=>$value){
				$this->_ext_delete('cate-'.$value['id']);
				$tmpids[] = 'c'.$value['id'];
			}
			$tmpids = implode("','",$tmpids);
			$sql = "DELETE FROM ".$this->db->prefix."tag_stat WHERE title_id IN('".$tmpids."')";
			$this->db->query($sql);
		}
		//刪除尺碼屬性
		$sql = "SELECT id FROM ".$this->db->prefix."attr WHERE site_id='".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$sql = "DELETE FROM ".$this->db->prefix."attr_values WHERE aid='".$value['id']."'";
				$this->db->query($sql);
			}
		}
		$sql = "DELETE FROM ".$this->db->prefix."attr WHERE site_id='".$id."'";
		$this->db->query($sql);
		//刪除分類
		$sql = "DELETE FROM ".$this->db->prefix."cate WHERE site_id='".$id."'";
		$this->db->query($sql);
		//刪除Email模板
		$sql = "DELETE FROM ".$this->db->prefix."email WHERE site_id='".$id."'";
		$this->db->query($sql);
		//刪除物流
		$sql = "DELETE FROM ".$this->db->prefix."express WHERE site_id='".$id."'";
		$this->db->query($sql);
		//刪除運費模板
		$sql = "SELECT id FROM ".$this->db->prefix."freight WHERE site_id='".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			$tmpids = array();
			foreach($tmplist as $key=>$value){
				$sql = "SELECT id FROM ".$this->db->prefix."freight_zone WHERE fid='".$value['id']."'";
				$tmp = $this->db->get_all($sql,'id');
				if($tmp){
					$ids = array_keys($tmp);
					$ids = implode(",",$ids);
					$sql = "DELETE FROM ".$this->db->prefix."freight_price WHERE zid IN(".$ids.")";
					$this->db->query($sql);
				}
				$tmpids[] = $value['id'];
			}
			$tmpids = implode(",",$tmpids);
			$sql = "DELETE FROM ".$this->db->prefix."freight_zone WHERE fid IN(".$tmpids.")";
			$this->db->query($sql);
		}
		$sql = "DELETE FROM ".$this->db->prefix."freight WHERE site_id='".$id."'";
		$this->db->query($sql);
		//刪除第三方閘道器
		$sql = "DELETE FROM ".$this->db->prefix."gateway WHERE site_id='".$id."'";
		$this->db->query($sql);
		//刪除支付組
		$sql = "SELECT id FROM ".$this->db->prefix."payment_group WHERE site_id='".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$sql = "DELETE FROM ".$this->db->prefix."payment WHERE gid='".$value['id']."'";
				$this->db->query($sql);
			}
		}
		$sql = "DELETE FROM ".$this->db->prefix."payment_group WHERE site_id='".$id."'";
		$this->db->query($sql);
		//刪除資料呼叫
		$sql = "DELETE FROM ".$this->db->prefix."phpok WHERE site_id='".$id."'";
		$this->db->query($sql);
		//刪除專案資訊
		$sql = "SELECT id,module FROM ".$this->db->prefix."project WHERE site_id='".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			$tmpids = array();
			foreach($tmplist as $key=>$value){
				if($value['module']){
					$m_rs = $this->model('module')->get_one($value['module']);
					if($m_rs['mtype']){
						$sql = "DELETE FROM ".$this->db->prefix.$value['module']." WHERE site_id='".$id."'";
					}else{
						$sql = "DELETE FROM ".$this->db->prefix."list_".$value['module']." WHERE site_id='".$id."'";
					}
					$this->db->query($sql);
				}
				$tmpids[] = $value['id'];
			}
			$tmpids = implode(",",$tmpids);
			//刪除相應的許可權
			$sql = "DELETE FROM ".$this->db->prefix."popedom WHERE pid IN(".$tmpids.")";
			$this->db->query($sql);
		}
		$sql = "DELETE FROM ".$this->db->prefix."project WHERE site_id='".$id."'";
		$this->db->query($sql);
		//刪除回覆
		$sql = "DELETE FROM ".$this->db->prefix."reply WHERE id IN(SELECT id FROM ".$this->db->prefix."list WHERE site_id='".$id."')";
		$this->db->query($sql);
		//刪除站點域名
		$sql = "DELETE FROM ".$this->db->prefix."site_domain WHERE site_id='".$id."'";
		$this->db->query($sql);
		//刪除系統選單
		$sql = "DELETE FROM ".$this->db->prefix."sysmenu WHERE site_id='".$id."'";
		$this->db->query($sql);
		//刪除Tag及Tag_stat資訊
		$sql = "DELETE FROM ".$this->db->prefix."tag_stat WHERE tag_id IN(SELECT id FROM ".$this->db->prefix."tag WHERE site_id=".$id.")";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."tag WHERE site_id='".$id."'";
		$this->db->query($sql);
		//刪除財富操作
		$sql = "SELECT id FROM ".$this->db->prefix."wealth WHERE site_id='".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$sql = "DELETE FROM ".$this->db->prefix."wealth_info WHERE wid='".$value['id']."'";
				$this->db->query($sql);
				$sql = "DELETE FROM ".$this->db->prefix."wealth_log WHERE wid='".$value['id']."'";
				$this->db->query($sql);
				$sql = "DELETE FROM ".$this->db->prefix."wealth_rule WHERE wid='".$value['id']."'";
				$this->db->query($sql);
			}
		}
		$sql = "DELETE FROM ".$this->db->prefix."wealth WHERE site_id='".$id."'";
		$this->db->query($sql);
		//刪除擴充套件分類
		$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id IN(SELECT id FROM ".$this->db->prefix."list WHERE site_id='".$id."')";
		$this->db->query($sql);
		//刪除list表中的資料
		$sql = "DELETE FROM ".$this->db->prefix."list WHERE site_id='".$id."'";
		$this->db->query($sql);
		//刪除站點資訊
		$sql = "DELETE FROM ".$this->db->prefix."site WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 設定預設站點
	 * @引數 $id 要設為預設的站點ID
	 * @返回 true/false
	**/
	public function set_default($id)
	{
		if(!$id){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."site SET is_default=0";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."site SET is_default=1 WHERE id=".intval($id);
		$this->db->query($sql);
		return true;
	}

	/**
	 * 設定是否手機專用域名
	 * @引數 $id site_domain表中的主鍵ID
	 * @引數 $act 啟用
	 * @返回 true/false
	**/
	public function set_mobile($id=0,$act=1)
	{
		$this->clear_mobile_domain();
		if($id && $act){
			$sql = "UPDATE ".$this->db->prefix."site_domain SET is_mobile=1 WHERE site_id=".$this->site_id." AND id='".$id."'";
			return $this->db->query($sql);
		}
		return true;
	}

	/**
	 * 清空手機專用域名
	**/
	public function clear_mobile_domain()
	{
		$sql = "UPDATE ".$this->db->prefix."site_domain SET is_mobile=0 WHERE site_id=".$this->site_id;
		return $this->db->query($sql);
	}

	/**
	 * 訂單價格方案
	 * @引數 $data 陣列，多維
	 * @引數 $id 唯一標識串，用於變更
	**/
	public function price_status_update($data,$id=0)
	{
		if(!$id || !$data){
			return false;
		}
		$rslist = $this->price_status_all();
		$rslist[$id] = $data;
		$file = $this->dir_data.'xml/price_status_'.$this->site_id.'.xml';
		$this->lib('xml')->save($rslist,$file);
		return true;
	}

	public function order_status_one($id)
	{
		$rslist = $this->order_status_all();
		if(!$rslist){
			return false;
		}
		if($rslist[$id]){
			$rs = $rslist[$id];
			$rs['id'] = $id;
			return $rs;
		}
		return false;
	}

	//更新狀態
	public function order_status_update($data,$id=0)
	{
		if(!$id || !$data){
			return false;
		}
		$rslist = $this->order_status_all();
		$rslist[$id] = $data;
		$file = $this->dir_data.'xml/order_status_'.$this->site_id.'.xml';
		$this->lib('xml')->save($rslist,$file);
	}

	public function get_all_site()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."site";
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."site_domain";
		$dlist = $this->db->get_all($sql);
		if($dlist){
			foreach($dlist as $key=>$value){
				$rslist[$value['site_id']]['dlist'][] = $value['domain'];
			}
			foreach($rslist as $key=>$value){
				if($value['dlist']){
					$value['dlist_string'] = implode(" , ",$value['dlist']);
				}
				$rslist[$key] = $value;
			}
		}
		//讀別名
		$file = $this->dir_data.'xml/site_alias.xml';
		if(!file_exists($file)){
			return $rslist;
		}
		$tmplist = $this->lib('xml')->read($file,true);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$tmpid = substr($key,1);
				if($tmpid && $value && $rslist[$tmpid]){
					$rslist[$tmpid]['alias'] = $value;
				}
			}
		}
		return $rslist;
	}

	public function alias_save($title,$id)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."site";
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		$file = $this->dir_data.'xml/site_alias.xml';
		$tmplist = $this->lib('xml')->read($file,true);
		if($tmplist){
			$tmplist['a'.$id] = $title;
		}else{
			$tmplist = array();
			$tmplist['a'.$id] = $title;
		}
		foreach($tmplist as $key=>$value){
			$tmpid = substr($key,1);
			if(!$rslist[$tmpid]){
				unset($tmplist[$key]);
			}
		}
		if(!$tmplist || count($tmplist)<1){
			return false;
		}
		return $this->lib('xml')->save($tmplist,$file);
	}

	public function admin_order_status_all($sort=false)
	{
		$file = $this->dir_data.'xml/admin_order_status_'.$this->site_id.'.xml';
		if(!file_exists($file)){
			$file = $this->dir_data.'xml/admin_order_status.xml';
		}
		if(!file_exists($file)){
			return false;
		}
		$taxis = 100;
		$tmplist = $this->lib('xml')->read($file);
		if($tmplist && $sort){
			$rslist = array();
			foreach($tmplist as $key=>$value){
				$value['identifier'] = $key;
				$rslist[] = $value;
			}
			usort($rslist,array($this,'status_sort'));
			return $rslist;
		}
		return $tmplist;
	}

	/**
	 * 管理員狀態讀取
	 * @引數 $id 讀取標識
	**/
	public function admin_order_status_one($id)
	{
		$rslist = $this->admin_order_status_all();
		if(!$rslist){
			return false;
		}
		if($rslist[$id]){
			$rs = $rslist[$id];
			$rs['id'] = $id;
			return $rs;
		}
		return false;
	}

	/**
	 * 更新管理員內部訂單狀態
	 * @引數 $data 狀態資訊
	 * @引數 $id 要更新的ID
	**/
	public function admin_order_status_update($data,$id=0)
	{
		if(!$id || !$data){
			return false;
		}
		$rslist = $this->admin_order_status_all();
		if(!$rslist){
			$rslist = array();
		}
		$rslist[$id] = $data;
		$file = $this->dir_data.'xml/admin_order_status_'.$this->site_id.'.xml';
		$this->lib('xml')->save($rslist,$file);
		return true;
	}

	public function admin_order_status_delete($id='')
	{
		if(!$id){
			return false;
		}
		$rslist = $this->admin_order_status_all();
		if(!$rslist){
			return false;
		}
		if(!$rslist[$id]){
			return false;
		}
		unset($rslist[$id]);
		$file = $this->dir_data.'xml/admin_order_status_'.$this->site_id.'.xml';
		if(!$rslist && file_exists($file)){
			$this->lib('file')->rm($file);
			return true;
		}
		$this->lib('xml')->save($rslist,$file);		
		return true;
	}
}