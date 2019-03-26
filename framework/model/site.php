<?php
/**
 * 網站資訊
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年09月08日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class site_model_base extends phpok_model
{
	private $mobile_domain = false;
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 獲取站點資訊
	 * @引數 $id 站點ID
	 * @返回 陣列
	**/
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."site WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if(file_exists($this->dir_data.'xml/site_'.$id.'.xml')){
			$tmp = $this->lib('xml')->read($this->dir_data.'xml/site_'.$id.'.xml');
			if($tmp){
				$rs = array_merge($tmp,$rs);
			}
		}
		$rs['_domain'] = $this->domain_list($id,'id');
		if($rs['_domain']){
			foreach($rs['_domain'] as $key=>$value){
				if($value['is_mobile']){
					$rs['_mobile'] = $value;
				}
				if($value['id'] == $rs['domain_id']){
					$rs['domain'] = $value['domain'];
				}
			}
		}
		return $rs;
	}

	public function get_one_default()
	{
		$sql = "SELECT id FROM ".$this->db->prefix."site WHERE is_default=1";
		$tmp = $this->db->get_one($sql);
		if(!$tmp){
			return false;
		}
		return $this->get_one($tmp['id']);
	}

	//有快取讀取
	public function get_one_from_domain($domain='')
	{
		$sql = "SELECT site_id FROM ".$this->db->prefix."site_domain WHERE domain='".$domain."'";
		$tmp = $this->db->get_one($sql);
		if(!$tmp){
			return false;
		}
		return $this->get_one($tmp['site_id']);
	}

	public function get_all_site()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."site ";
		return $this->db->get_all($sql);
	}

	public function domain_list($site_id=0,$pri='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."site_domain ";
		if($site_id){
			$sql .= "WHERE site_id='".$site_id."'";
		}
		return $this->db->get_all($sql,$pri);
	}

	public function domain_check($domain)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."site_domain WHERE domain='".$domain."'";
		return $this->db->get_one($sql);
	}

	public function domain_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."site_domain WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function site_domain($siteId,$is_mobile=0)
	{
		$sql = "SELECT domain FROM ".$this->db->prefix."site_domain WHERE site_id='".$siteId."'";
		$sql.= " AND is_mobile='".intval($is_mobile)."'";
		$tmp = $this->db->get_one($sql);
		if(!$tmp){
			return false;
		}
		return $tmp['domain'];
	}

	public function all_one($id)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."all WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function all_ext($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."all_ext WHERE all_id='".$id."'";
		return $this->db->get_all($sql,"fields_id");
	}

	public function all_check($identifier,$site_id=0,$id=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."all WHERE identifier='".$identifier."' AND site_id='".$site_id."'";
		if($id){
			$sql .= " AND id!='".$id."'";
		}
		return $this->db->get_one($sql);
	}

	public function all_list($site_id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."all WHERE site_id='".$site_id."'";
		return $this->db->get_all($sql);
	}

	/**
	 * 訂單狀態設定
	 * @引數 $sort 是否排序
	 * @返回 false 或 陣列
	 * @更新時間 2016年09月28日
	**/
	public function order_status_all($sort=false)
	{
		$site_id = $this->app_id == 'admin' ? $this->session->val('admin_site_id') : $this->site['id'];
		$file = $this->dir_data.'xml/order_status_'.$site_id.'.xml';
		if(!file_exists($file)){
			$file = $this->dir_data.'xml/order_status.xml';
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

	protected function status_sort($a,$b)
	{
		if($a['taxis'] == $b['taxis']){
			return 0;
		}
		return ($a['taxis'] < $b['taxis']) ? -1 : 1;
	}

	/**
	 * 取得訂單價格方案
	 * @引數 $sort 是否執行排序操作
	 * @返回 false 或是 陣列
	 * @更新時間 2016年09月28日
	**/
	public function price_status_all($sort=false)
	{
		$site_id = $this->app_id == 'admin' ? $this->session->val('admin_site_id') : $this->site['id'];
		$file = $this->dir_data.'xml/price_status_'.$site_id.'.xml';
		if(!file_exists($file)){
			$file = $this->dir_data.'xml/price_status.xml';
		}
		if(!file_exists($file)){
			return false;
		}
		$string = 'product,shipping,fee,discount,wealth,payonline';
		if($this->config['order'] && $this->config['order']['price']){
			$string = $this->config['order']['price'];
		}
		$list = explode(",",$string);
		$taxis = 100;
		$tmplist = $this->lib('xml')->read($file);
		foreach($list as $key=>$value){
			if(!$tmplist[$value]){
				$tmplist[$value] = array('title'=>$value,'action'=>'add','taxis'=>$taxis,'status'=>0);
				$taxis++;
			}
		}
		foreach($tmplist as $key=>$value){
			if(!in_array($key,$list)){
				unset($tmplist[$key]);
			}
		}
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
	 * 前臺及API介面獲取的網站資訊
	 * @param mixed $id 網站ID或網站域名
	 * @return array 為空時返回false，不為空返回網站相關資訊 
	 * @date 2016年02月05日
	 */
	public function site_info($id='')
	{
		if(!$id){
			return false;
		}
		$cache_id = $this->cache->id($id);
		$rs = $this->cache->get($cache_id);
		if($rs){
			return $rs;
		}
		$this->db->cache_set($cache_id);
		if(!is_numeric($id)){
			$sql = "SELECT site_id FROM ".$this->db->prefix."site_domain WHERE domain='".$id."'";
			$tmp = $this->db->get_one($sql);
			if(!$tmp){
				$sql = "SELECT id FROM ".$this->db->prefix."site WHERE is_default=1";
				$tmp = $this->db->get_one($sql);
				if(!$tmp){
					return false;
				}
				$id = $tmp['id'];
			}else{
				$id = $tmp['site_id'];
			}
		}
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."site WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if(file_exists($this->dir_data.'xml/site_'.$id.'.xml')){
			$tmp = $this->lib('xml')->read($this->dir_data.'xml/site_'.$id.'.xml');
			if($tmp){
				$rs = array_merge($tmp,$rs);
			}
		}
		$sql = "SELECT * FROM ".$this->db->prefix."site_domain WHERE site_id='".$id."'";
		$dlist = $this->db->get_all($sql);
		if($dlist){
			$rs['_domain'] = $dlist;
			foreach($dlist as $key=>$value){
				if($value['is_mobile']){
					$rs['_mobile'] = $value;
				}
				if($value['id'] == $rs['domain_id']){
					$rs['domain'] = $value['domain'];
				}
			}
		}
		$sql = "SElECT * FROM ".$this->db->prefix."all WHERE site_id='".$id."' AND status='1'";
		$list = $this->db->get_all($sql);
		if(!$list){
			$this->cache->save($cache_id,$rs);
			return $rs;
		}
		$tmp = $tmp2 = array();
		foreach($list AS $key=>$value){
			$tmp[$value["identifier"]] = "all-".$value["id"];
			$tmp2["all-".$value["id"]] = $value["identifier"];
		}
		$tmp = implode("','",$tmp);
		$sql = "SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM ".$this->db->prefix."fields ext ";
		$sql.= "JOIN ".$this->db->prefix."extc extc ON(ext.id=extc.id) ";
		$sql.= "WHERE ext.ftype IN('".$tmp."') ORDER BY ext.taxis ASC,ext.id DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			$this->cache->save($cache_id,$rs);
			return $rs;
		}
		$info = false;
		foreach($rslist AS $key=>$value){
			if(!$tmp2[$value["ftype"]]){
				continue;
			}
			$rs[$tmp2[$value["ftype"]]][$value["identifier"]] = $this->lib('form')->show($value);
		}
		$this->cache->save($cache_id,$rs);
		return $rs;
	}

	/**
	 * 預設模板配置檔案
	**/
	public function tpl_default()
	{
		$rslist = false;
		if(file_exists($this->dir_data.'xml/site_tpl_default.xml')){
			$rslist = $this->lib('xml')->read($this->dir_data.'xml/site_tpl_default.xml');
		}
		return $rslist;
	}

	public function tpl_file($ctrl='',$func='')
	{
		$id = $ctrl.'-'.$func;
		if(file_exists($this->dir_data.'xml/site_tpl_'.$this->site_id.'.xml')){
			$info = $this->lib('xml')->read($this->dir_data.'xml/site_tpl_'.$this->site_id.'.xml');
			if($info[$id]){
				return $info[$id];
			}
		}
		$list = $this->tpl_default();
		if($list && $list[$id] && $list[$id]['default']){
			return $list[$id]['default'];
		}
		return false;
	}

	/**
	 * 讀寫自定義的模板資訊
	 * @引數 $data 要儲存的資料，留空表示讀操作
	**/
	public function tpl_setting($data='')
	{
		if($data && is_array($data)){
			return $this->lib('xml')->save($data,$this->dir_data.'xml/site_tpl_'.$this->site_id.'.xml');
		}
		$rslist = $this->tpl_default();
		if(file_exists($this->dir_data.'xml/site_tpl_'.$this->site_id.'.xml')){
			$extlist = $this->lib('xml')->read($this->dir_data.'xml/site_tpl_'.$this->site_id.'.xml');
			if($extlist && is_array($extlist)){
				foreach($extlist as $key=>$value){
					$rslist[$key]['tpl'] = $value;
				}
			}
		}
		return $rslist;
	}

	/**
	 * 重置模板設定
	**/
	public function tpl_reset()
	{
		if(file_exists($this->dir_data.'xml/site_tpl_'.$this->site_id.'.xml')){
			$this->lib('file')->rm($this->dir_data.'xml/site_tpl_'.$this->site_id.'.xml');
		}
		return true;
	}

	/**
	 * 保留字元
	**/
	public function reserved()
	{
		$reserved = array('js','ajax','inp');
		if($this->config['reserved']){
			$tmp = explode(",",$this->config['reserved']);
			$reserved = array_merge($reserved,$tmp);
		}
		$list = $this->lib('file')->ls($this->dir_phpok.'www');
		foreach($list as $key=>$value){
			if(!$value || is_dir($value) || !file_exists($value) || substr($value,-12) != '_control.php'){
				continue;
			}
			$basename = basename($value);
			$basename = str_replace("_control.php",'',$basename);
			$reserved[] = $basename;
		}
		$install = $this->model('appsys')->installed();
		if($install){
			foreach($install as $key=>$value){
				$reserved[] = $key;
			}
		}
		$reserved = array_unique($reserved);
		return $reserved;
	}

	public function vcode_all()
	{
		$xmlfile = $this->dir_data.'xml/vcode_'.$this->site_id.'.xml';
		if(!file_exists($xmlfile)){
			$xmlfile = $this->dir_data.'xml/vcode.xml';
		}
		return $this->lib('xml')->read($xmlfile,true);
	}

	/**
	 * 是否啟用驗證碼，返回 true 表示啟用，返回 false 表示不啟用
	 * @引數 $id 值專案ID或是system
	 * @引數 $act 動作標識，如為 system，支援 login register，使用專案支援 add，edit，comment
	**/
	public function vcode($id,$act='')
	{
		if(!$this->config['is_vcode']){
			return false;
		}
		if(!function_exists('imagecreate')){
			return false;
		}
		$rslist = $this->vcode_all();
		if(!$rslist){
			return true;
		}
		$identifier = $id == 'system' ? $id : 'p-'.$id;
		if(!$rslist[$identifier]){
			return true;
		}
		if(!$rslist[$identifier]['list']){
			return true;
		}
		if(!isset($rslist[$identifier]['list'][$act])){
			return true;
		}
		return $rslist[$identifier]['list'][$act]['status'];
	}
}