<?php
/**
 * 會員資料增刪查改
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年09月07日
**/

class user_model_base extends phpok_model
{
	public $psize = 20;
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 取得單條會員陣列
	 * @引數 $id 會員ID或其他唯一標識
	 * @引數 $field 指定的標識，當為布林值時表示是否格式化擴充套件資料
	 * @引數 $ext 布林值或1或0，當$field為布林值時這裡表示是否顯示財富
	 * @引數 $wealth 布林值或1或0，表示財富
	**/
	public function get_one($id,$field='id',$ext=true,$wealth=true)
	{
		if(!$id){
			return false;
		}
		if(is_bool($field) || is_numeric($field)){
			$wealth = $ext;
			$ext = $field;
			$field = 'id';
		}
		if(!$field){
			$field = 'id';
		}
		$flist = $this->fields_all();
		$ufields = "u.*";
		$field_type = 'main';
		$condition = "u.".$field."='".$id."'";
		if($flist){
			foreach($flist as $key=>$value){
				$ufields .= ",e.".$value['identifier'];
				if($value['identifier'] == $value){
					$condition = "e.".$field."='".$id."'";
				}
			}
		}
		$sql = " SELECT ".$ufields." FROM ".$this->db->prefix."user u ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user_ext e ON(u.id=e.id) ";
		$sql.= " WHERE ".$condition;
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($wealth){
			$rs['wealth'] = $this->wealth($rs['id']);
		}
		if($ext && $flist){
			foreach($flist AS $key=>$value){
				$rs[$value['identifier']] = $this->lib('form')->show($value,$rs[$value['identifier']]);
			}
		}
		return $rs;
	}

	/**
	 * 取得會員的財富資訊
	 * @引數 $uid 會員ID
	 * @引數 $wid 指定的財富ID，為0或空時返回會員下的所有財富資訊
	 * @引數 $return 返回，僅在$wid大於0時有效，支援兩個引數，一個是value，返回值，一個是array，返回陣列
	**/
	public function wealth($uid,$wid=0,$return='value')
	{
		$wlist = $this->model('wealth')->get_all(1,'id');
		if(!$wlist){
			return false;
		}
		$wealth = array();
		foreach($wlist as $key=>$value){
			$val = number_format(0,$value['dnum']);
			$wealth[$value['identifier']] = array('id'=>$value['id'],'title'=>$value['title'],'val'=>$val,'unit'=>$value['unit']);
		}
		$condition = "uid='".$uid."'";
		$tlist = $this->model('wealth')->vals($condition);
		if($tlist){
			foreach($tlist as $key=>$value){
				$tmp = $wlist[$value['wid']];
				$val = round($value['val'],$tmp['dnum']);
				$wealth[$tmp['identifier']]['val'] = $val;
			}
		}
		if($wid){
			if(is_numeric($wid)){
				$tmp = false;
				foreach($wealth as $key=>$value){
					if($value['id'] == $wid){
						$tmp = $value;
						break;
					}
				}
				if(!$tmp){
					return false;
				}
				if($return == 'array'){
					return $tmp;
				}
				return $tmp['val'];
			}
			//字串
			if(!$wealth[$wid]){
				return false;
			}
			if($return == 'array'){
				return $wealth[$wid];
			}
			return $wealth[$wid]['val'];
		}
		return $wealth;
	}

	/**
	 * 根據條件取得會員列表資料
	 * @引數 $condition 查詢條件，主表使用關鍵字 u，擴充套件表使用關鍵字 e
	 * @引數 $offset 起始位置
	 * @引數 $psize 查詢數量
	 * @引數 $pri 繫結的主鍵
	**/
	public function get_list($condition="",$offset=0,$psize=30)
	{
		$flist = $this->fields_all();
		$ufields = "u.*";
		if($flist){
			foreach($flist as $key=>$value){
				$ufields .= ",e.".$value['identifier'];
			}
		}
		$sql = " SELECT ".$ufields." FROM ".$this->db->prefix."user u ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user_ext e ON(u.id=e.id) ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$offset = intval($offset);
		$psize = intval($psize);
		$sql.= " ORDER BY u.regtime DESC,u.id DESC ";
		if($psize){
			$offset = intval($offset);
			$sql .= "LIMIT ".$offset.",".$psize;
		}
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		$idlist = array_keys($rslist);
		//讀取會員積分資訊
		$wlist = $this->model('wealth')->get_all(1,'id');
		if($wlist){
			$condition = "uid IN(".implode(",",$idlist).")";
			$tlist = $this->model('wealth')->vals($condition);
			if($tlist){
				$wealth = array();
				foreach($tlist as $key=>$value){
					$tmp = $wlist[$value['wid']];
					$rslist[$value['uid']]['wealth'][$tmp['identifier']] = $value['val'];
				}
			}
		}
		if(!$flist){
			return $rslist;
		}
		foreach($rslist AS $key=>$value){
			foreach($flist AS $k=>$v){
				$value[$v['identifier']] = $this->lib('form')->show($v,$value[$v['identifier']]);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}


	/**
	 * 取得指定條件下的會員數量
	 * @引數 $condition 查詢條件，主表使用關鍵字 u，擴充套件表使用關鍵字 e
	**/
	public function get_count($condition="")
	{
		$sql = " SELECT count(u.id) FROM ".$this->db->prefix."user u ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user_ext e ON(u.id=e.id) ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 檢測賬號是否衝突
	 * @引數 $name 賬號名稱
	 * @引數 $id 會員ID，表示不包含這個會員ID
	**/
	public function chk_email($email,$id=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE email='".$email."' ";
		if($id){
			$sql.= " AND id!='".$id."' ";
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 檢測賬號是否衝突
	 * @引數 $name 賬號名稱
	 * @引數 $id 會員ID，表示不包含這個會員ID
	**/
	public function chk_name($name,$id=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE user='".$name."' ";
		if($id){
			$sql.= " AND id!='".$id."' ";
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 取得擴充套件欄位的所有擴展信息
	 * @引數 $condition 取得會員擴充套件欄位配置
	 * @引數 $pri_id 主鍵ID
	**/
	public function fields_all($condition="",$pri_id="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE ftype='user' ";
		if($condition){
			$sql .= " AND ".$condition;
		}
		$sql.= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql,$pri_id);
	}

	/**
	 * 取得指定表的欄位
	 * @引數 $tbl 表名
	**/
	public function tbl_fields_list($tbl='user')
	{
		return $this->db->list_fields($tbl);
	}

	/**
	 * 取得某一條擴充套件欄位配置資訊
	 * @引數 $id 主鍵ID
	**/
	public function field_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得指定會員ID下的全部會員主表資訊
	 * @引數 $uid 會員ID，多個ID用英文逗號隔開
	 * @引數 $pri 繫結的主鍵
	 * @引數 
	**/
	public function get_all_from_uid($uid,$pri="")
	{
		if(!$uid){
			return false;
		}
		$tmp = explode(",",$uid);
		foreach($tmp as $key=>$value){
			if(!$value || !trim($value) || !intval($value)){
				unset($tmp[$key]);
			}
		}
		$uid = implode(",",$tmp);
		$condition = "u.id IN(".$uid.")";
		$rslist = $this->get_list($condition,0,0);
		if(!$rslist){
			return false;
		}
		if(!$pri){
			$tmplist = array();
			foreach($rslist as $key=>$value){
				$tmplist[] = $value;
			}
			return $tmplist;
		}
		if($pri && $pri != 'id'){
			$tmplist = array();
			foreach($rslist as $key=>$value){
				$tmpid = $value[$pri];
				$tmplist[$tmpid] = $value;
			}
			return $tmplist;
		}
		return $rslist;
	}

	/**
	 * 取得會員主表字段
	**/
	public function fields()
	{
		return $this->db->list_fields($this->db->prefix."user");
	}

	/**
	 * 通過郵箱取得會員的ID
	 * @引數 $email 指定的郵箱
	 * @引數 $id 不包括會員ID
	**/
	public function uid_from_email($email,$id="")
	{
		if(!$email){
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."user WHERE email='".$email."'";
		if($id){
			$sql.= " AND id !='".$id."'";
		}
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $rs['id'];
	}

	/**
	 * 通過驗證串獲取會員ID，注意，此項及有可能獲得到的會員ID是不準確的，適用於忘記密碼
	 * @引數 $code 驗證串
	**/
	public function uid_from_chkcode($code)
	{
		if(!$code){
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."user WHERE code='".$code."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $rs['id'];
	}

	/**
	 * 儲存會員主要資料
	 * @引數 $data 一維陣列，會員內容
	 * @引數 $id 會員ID，為空或0時表示新增
	**/
	public function save($data,$id=0)
	{
		if($id){
			$status = $this->db->update_array($data,"user",array("id"=>$id));
			if($status){
				return $id;
			}
			return false;
		}else{
			return $this->db->insert_array($data,"user");
		}
	}

	/**
	 * 寫入會員擴充套件資料，適用於新註冊會員
	 * @引數 $data 一維陣列
	**/
	public function save_ext($data)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		$fields = $this->fields_all();
		if($fields){
			foreach($fields as $key=>$value){
				if(!isset($data[$value['identifier']])){
					$data[$value['identifier']] = $value['content'];
				}
			}
		}
		return $this->db->insert_array($data,"user_ext","replace");
	}

	/**
	 * 更新會員擴充套件表資料
	 * @引數 $data 一維陣列，要更新的會員資料內容
	 * @引數 $id 會員ID
	**/
	public function update_ext($data,$id)
	{
		if(!$data || !is_array($data) || !$id){
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."user_ext WHERE id='".$id."'";
		$chk = $this->db->get_one($sql);
		if(!$chk){
			$data['id'] = $id;
			return $this->save_ext($data);
		}
		return $this->db->update_array($data,"user_ext",array("id"=>$id));
	}

	/**
	 * 取得會員的推薦人會員ID
	 * @引數 $uid 當前會員ID
	**/
	public function get_relation($uid)
	{
		$sql = "SELECT introducer FROM ".$this->db->prefix."user_relation WHERE uid='".$uid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $rs['introducer'];
	}

	/**
	 * 儲存會員與推薦人關係
	 * @引數 $uid 會員ID
	 * @引數 $introducer 推薦人ID
	**/
	public function save_relation($uid=0,$introducer=0)
	{
		if(!$uid || !$introducer){
			return false;
		}
		$sql = "REPLACE INTO ".$this->db->prefix."user_relation(uid,introducer,dateline) VALUES('".$uid."','".$introducer."','".$this->time."')";
		return $this->db->query($sql);
	}

	/**
	 * 取得會員推薦列表
	 * @引數 $uid 當前會員ID
	 * @引數 $offset 初始位置
	 * @引數 $psize 查詢數量
	 * @引數 $condition 其他查詢條件
	**/
	public function list_relation($uid,$offset=0,$psize=30,$condition='')
	{
		$sql = "SELECT uid FROM ".$this->db->prefix."user_relation WHERE introducer='".$uid."'";
		if($condition){
			$sql .= " AND ".$condition;
		}
		$sql .= " ORDER BY uid DESC LIMIT ".intval($offset).",".intval($psize);
		$rslist = $this->db->get_all($sql,'uid');
		if(!$rslist){
			return false;
		}
		$ids = array_keys($rslist);
		$condition = "u.id IN(".implode(",",$ids).")";
		return $this->get_list($condition,0,0);
	}

	/**
	 * 取得總數量
	 * @引數 $uid 當前會員ID
	 * @引數 $condition 其他查詢條件
	**/
	public function count_relation($uid,$condition="")
	{
		$sql = "SELECT count(uid) FROM ".$this->db->prefix."user_relation WHERE introducer='".$uid."'";
		if($condition){
			$sql .= " AND ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 取得最大時間和最小時間
	 * @引數 $uid 會員ID
	**/
	public function time_relation($uid)
	{
		$sql = "SELECT max(dateline) max_time,min(dateline) min_time FROM ".$this->db->prefix."user_relation WHERE introducer='".$uid."'";
		return $this->db->get_one($sql);
	}

	public function stat_relation($uid)
	{
		$sql = "SELECT count(uid) as total,FROM_UNIXTIME(dateline,'%Y%m') as month FROM ".$this->db->prefix."user_relation WHERE introducer='".$uid."' ";
		$sql.= "GROUP BY FROM_UNIXTIME(dateline,'%Y%m') ORDER BY dateline ASC";
		return $this->db->get_all($sql);
	}

	/**
	 * 取得會員有驗證串是否一致，一致則自動登入
	 * @引數 $uid 會員ID
	 * @引數 $chk 驗證串
	**/
	public function token_check($uid,$sign)
	{
		if(!$uid || !$sign){
			return false;
		}
		$sql = "SELECT id,group_id,user,pass FROM ".$this->db->prefix."user WHERE id='".$uid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$code = md5($uid.'-'.$rs['user'].'-'.$rs['pass']);
		if(strtolower($code) == strtolower($sign)){
			$this->session->assign('user_id',$uid);
			$this->session->assign('user_name',$rs['user']);
			$this->session->assign('user_gid',$rs['group_id']);
			return true;
		}
		return false;
	}

	/**
	 * 生成驗證串
	 * @引數 $uid 會員ID
	**/
	public function token_create($uid,$keyid='')
	{
		if(!$uid || !$keyid){
			return false;
		}
		$sql = "SELECT id,group_id,user,pass FROM ".$this->db->prefix."user WHERE id='".$uid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$code = md5($uid.'-'.$rs['user'].'-'.$rs['pass']);
		$array = array('id'=>$uid,'code'=>$code);
		$this->lib('token')->keyid($keyid);
		return $this->lib('token')->encode($array);
	}

	/**
	 * 更新會員驗證串
	 * @引數 $code 驗證碼，為空表示清空驗證碼
	 * @引數 $id 會員ID
	**/
	public function update_code($code,$id)
	{
		$sql = "UPDATE ".$this->db->prefix."user SET code='".$code."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 會員地址庫儲存
	 * @引數 $data 要儲存的陣列
	 * @引數 $id 地址ID
	**/
	public function address_save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,'user_address',array("id"=>$id));
		}
		return $this->db->insert_array($data,'user_address');
	}

	/**
	 * 會員下的地址資訊
	 * @引數 $uid 會員ID號
	**/
	public function address_all($uid=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_address WHERE user_id='".$uid."' ORDER BY id DESC LIMIT 999";
		return $this->db->get_all($sql);
	}

	/**
	 * 取得單條地址資訊
	 * @引數 $id 地址ID
	**/
	public function address_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_address WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 設定預設地址
	 * @引數 $id 要指定的預設地址
	**/
	public function address_default($id)
	{
		$chk = $this->address_one($id);
		$user_id = $chk['user_id'];
		$sql = "UPDATE ".$this->db->prefix."user_address SET is_default=0 WHERE user_id='".$user_id."'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."user_address SET is_default=1 WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 刪除一條會員地址庫
	 * @引數 $id 指定的地址ID
	**/
	public function address_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."user_address WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 設定會員狀態
	**/
	public function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."user SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//郵箱登入
	function user_email($email,$uid=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE email='".$email."'";
		if($uid){
			$sql .= " AND id != '".$uid."'";
		}
		return $this->db->get_one($sql);
	}

	public function user_mobile($mobile,$uid=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE mobile='".$mobile."'";
		if($uid){
			$sql .= " AND id != '".$uid."'";
		}
		return $this->db->get_one($sql);
	}
}