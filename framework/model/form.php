<?php
/**
 * 表單選擇器
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年01月20日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class form_model_base extends phpok_model
{
	private $info = array();
	private $space_array = array(' ','	','　',',','，');
	public function __construct()
	{
		parent::model();
		$this->info = $this->lib('xml')->read($this->dir_phpok.'system.xml');
	}

	/**
	 * 表單型別
	 * @引數 $note 是否顯示備註，預設為 false
	**/
	public function form_all($note = false)
	{
		if($this->info['form']){
			if($note){
				return $this->info['form'];
			}
			$list = array();
			foreach($this->info['form'] as $key=>$value){
				$list[$key] = is_array($value) ? $value['title'] : $value;
			}
			return $list;
		}
		return false;
	}

	/**
	 * 格式化方式
	 * @引數 $note 是否顯示備註，預設為 false
	**/
	public function format_all($note = false)
	{
		if($this->info['format']){
			if($note){
				return $this->info['format'];
			}
			$list = array();
			foreach($this->info['format'] as $key=>$value){
				$list[$key] = is_array($value) ? $value['title'] : $value;
			}
			return $list;
		}
		return false;
	}

	/**
	 * 欄位型別
	 * @引數 $note 是否顯示備註，預設為 false
	**/
	public function field_all($note = false)
	{
		if($this->info['field']){
			if($note){
				return $this->info['field'];
			}
			$list = array();
			foreach($this->info['field'] as $key=>$value){
				$list[$key] = is_array($value) ? $value['title'] : $value;
			}
			return $list;
		}
		return false;
	}

	//讀取表單下的子專案資訊
	public function project_sublist($pid)
	{
		$sql = "SELECT id as val,title FROM ".$this->db->prefix."project WHERE parent_id=".intval($pid)." AND status=1 ";
		$sql.= "ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	/**
	 * 通過搜尋擴充套件欄位的關鍵字，找回儲存的val值
	 * @引數 $fid qinggan_fields 表中的主鍵ID 或是 陣列
	 * @引數 $keywords 要搜尋的值
	 * @引數 $ext 擴充套件表字首
	**/
	public function search($fid,$keywords,$ext='ext')
	{
		if(!$fid || !$keywords){
			return false;
		}
		if(is_array($fid)){
			$rs = $fid;
			$fid = $r['id'];
		}else{
			$rs = $this->model('fields')->one($fid);
		}
		if(!$rs){
			return false;
		}
		//不支援搜尋
		if(!$rs['search']){
			return false;
		}
		if($rs['ext'] && is_string($rs['ext'])){
			$rs['ext'] = unserialize($rs['ext']);
		}
		if($ext){
			if(substr($ext,-1) != '.'){
				$ext .= '.';
			}
		}
		//區間檢索
		if($rs['search'] == 3){
			if(!$rs['search_separator']){
				return false;
			}
			$tmp_format = array('int','float','time');
			if(!in_array($rs['format'],$tmp_format)){
				return false;
			}
			if(strpos($keywords,$rs['search_separator']) === false){
				return false;
			}
			$tmp = explode($rs['search_separator'],$keywords);
			if($rs['format'] == 'time'){
				$min = strtotime($tmp[0]);
				$max = strtotime($tmp[1]);
			}elseif($rs['format'] == 'int'){
				$min = intval($tmp[0]);
				$max = intval($tmp[1]);
			}else{
				$min = floatval($tmp[0]);
				$max = floatval($tmp[1]);
			}
			if($min>$max){
				$min2 = $max;
				$max = $min;
				$min = $min2;
				unset($min2);
			}
			$condition = $ext.$rs['identifier'].">=".$min." AND ".$ext.$rs['identifier']."<=".$max;
			return $condition;
		}
		//文字搜尋
		$txtlist = array('text','password','textarea','editor','code_editor','param','pca','url');
		if($rs['form_type'] && in_array($rs['form_type'],$txtlist)){
			$keywords = str_replace($this->space_array(),'|',$keywords);
			$tmplist = explode("|",$keywords);
			$tmp_condition = array();
			foreach($tmplist as $key=>$value){
				if(!$value || !trim($value)){
					continue;
				}
				$value = trim($value);
				if($rs['search'] == 1){
					$tmp_condition[] = $ext.$rs['identifier']."='".$value."'";
				}else{
					$tmp_condition[] = $ext.$rs['identifier']." LIKE '%".$value."%'";
				}
			}
			$condition = implode(" OR ",$tmp_condition);
			return $condition;
		}
		$keywords = str_replace($this->space_array(),'|',$keywords);
		$tmplist = explode("|",$keywords);
		$tmp_condition = array();
		//會員資訊檢索
		if($rs['form_type'] == 'user'){
			foreach($tmplist as $key=>$value){
				if(!$value || !trim($value)){
					continue;
				}
				$value = trim($value);
				if($rs['search'] == 1){
					$tmp_condition[] = "user='".$value."'";
					$tmp_condition[] = "email='".$value."'";
					$tmp_condition[] = "mobile='".$value."'";
				}else{
					$tmp_condition[] = "user LIKE '%".$value."%'";
					$tmp_condition[] = "email LIKE '%".$value."%'";
					$tmp_condition[] = "mobile LIKE '%".$value."%'";
				}
			}
			$sql = "SELECT id FROM ".$this->db->prefix."user WHERE ".implode(" OR ",$tmp_condition);
			$condition = $ext.$rs['identifier']." IN(".$sql.")";
			return $condition;
		}
		//上傳附件檢索
		if($rs['form_type'] == 'upload'){
			foreach($tmplist as $key=>$value){
				if(!$value || !trim($value)){
					continue;
				}
				$value = trim($value);
				if($rs['search'] == 1){
					$tmp_condition[] = "name='".$value."'";
					$tmp_condition[] = "title='".$value."'";
					$tmp_condition[] = "ext='".$value."'";
				}else{
					$tmp_condition[] = "name LIKE '%".$value."%'";
					$tmp_condition[] = "title LIKE '%".$value."%'";
					$tmp_condition[] = "ext LIKE '%".$value."%'";
				}
			}
			$sql = "SELECT id FROM ".$this->db->prefix."res WHERE ".implode(" OR ",$tmp_condition);
			$condition = $ext.$rs['identifier']." IN(".$sql.")";
			return $condition;
		}
		//擴充套件主題檢索
		if($rs['form_type'] == 'title'){
			foreach($tmplist as $key=>$value){
				if(!$value || !trim($value)){
					continue;
				}
				$value = trim($value);
				if($rs['search'] == 1){
					$tmp_condition[] = "title='".$value."'";
				}else{
					$tmp_condition[] = "title LIKE '%".$value."%'";
				}
			}
			$sql = "SELECT id FROM ".$this->db->prefix."list WHERE ".implode(" OR ",$tmp_condition);
			$condition = $ext.$rs['identifier']." IN(".$sql.")";
			return $condition;
		}
		//擴充套件模型檢索
		if($rs['form_type'] == 'extitle'){
			if(!$rs['ext'] || !$rs['ext']['form_pid']){
				return false;
			}
			$pid = $rs['ext']['form_pid'];
			$project = $this->model('project')->get_one($pid,false);
			if(!$project || !$project['module']){
				return false;
			}
			$mid = $project['module'];
			$module = $this->model('module')->get_one($mid);
			if(!$module){
				return false;
			}
			$condition = '';
			if(!$module['mtype']){
				foreach($tmplist as $key=>$value){
					if(!$value || !trim($value)){
						continue;
					}
					$value = trim($value);
					if($rs['search'] == 1){
						$tmp_condition[] = "title='".$value."'";
					}else{
						$tmp_condition[] = "title LIKE '%".$value."%'";
					}
				}
				$sql = "SELECT GROUP_CONCAT(id SEPARATOR '|') FROM ".$this->db->prefix."list WHERE ".implode(" OR ",$tmp_condition);
				$condition = "REPLACE(".$ext.$rs['identifier'].",',','|') REGEXP (".$sql.")";
			}
			$flist = $this->model('module')->fields_all($mid,'identifier');
			if($flist){
				$tbl = $module['mtype'] ? $this->db->prefix.$mid : $this->db->prefix."list_".$mid;
				foreach($flist as $key=>$value){
					if(!$value['search'] || $value['search'] == 3){
						continue;
					}
					$tmp_condition = array();
					foreach($tmplist as $k=>$v){
						if(!$v || !trim($v)){
							continue;
						}
						$v = trim($v);
						if($value['search'] == 1){
							$tmp_condition[] = $v['identifier']."='".$v."'";
						}else{
							$tmp_condition[] = $v['identifier']." LIKE '%".$v."%'";
						}
					}
					$sql = "SELECT GROUP_CONCAT(id SEPARATOR '|') FROM ".$tbl." WHERE ".implode(" OR ",$tmp_condition);
					if($condition){
						$condition .= " OR ";
						$condition .= "REPLACE(".$ext.$rs['identifier'].",',','|') REGEXP (".$sql.")";
					}else{
						$condition = "REPLACE(".$ext.$rs['identifier'].",',','|') REGEXP (".$sql.")";
					}
				}
			}
			if($condition){
				return $condition;
			}
			return false;
		}
		//單選框 下拉 核取方塊 檢索
		$txtlist = array('radio','checkbox','select');
		if(in_array($rs['form_type'],$txtlist)){
			$jz_search = false;
			if($rs['form_type'] == 'radio' && $rs['search'] == 1){
				$jz_search = true;
			}
			if($rs['form_type'] == 'select' && $rs['search'] == 1 && !$rs['ext']['is_multiple']){
				$jz_search = true;
			}
			if(!$rs['ext'] || !$rs['ext']['option_list']){
				foreach($tmplist as $key=>$value){
					if(!$value || !trim($value)){
						continue;
					}
					$value = trim($value);
					if($jz_search){
						$tmp_condition[] = $ext.$rs['identifier']."='".$value."'";
					}else{
						$tmp_condition[] = $ext.$rs['identifier']." LIKE '%".$value."%'";
					}
				}
				$condition = " ".implode(" OR ",$tmp_condition);
				return $condition;
			}
			$tmp = $rs['ext']['option_list'];
			if(!$tmp[1]){
				return false;
			}
			if($tmp[0] == 'opt'){
				foreach($tmplist as $key=>$value){
					if(!$value || !trim($value)){
						continue;
					}
					$value = trim($value);
					if($jz_search){
						$tmp_condition[] = "title='".$value."'";
						$tmp_condition[] = "val='".$value."'";
					}else{
						$tmp_condition[] = "title LIKE '%".$value."%'";
						$tmp_condition[] = "val LIKE '%".$value."%'";
					}
				}
				$sql = "SELECT val FROM ".$this->db->prefix."opt WHERE group_id='".$tmp[1]."' AND (".implode(" OR ",$tmp_condition).")";
				$condition = $ext.$rs['identifier']." IN(".$sql.")";
				if($rs['form_type'] == 'checkbox' || ($rs['form_type'] == 'select' && $rs['is_multiple'])){
					$condition = $ext.$rs['identifier']." LIKE CONCAT('%',".($sql).",'%')";
				}
				return $condition;
			}
			if($tmp[0] == 'project' || $tmp[0] == 'cate'){
				foreach($tmplist as $key=>$value){
					if(!$value || !trim($value)){
						continue;
					}
					$value = trim($value);
					if($jz_search){
						$tmp_condition[] = "title='".$value."'";
					}else{
						$tmp_condition[] = "title LIKE '%".$value."%'";
					}
				}
				$tbl = $tmp[0] == 'project' ? $this->db->prefix."project" : $this->db->prefix."cate";
				$sql = "SELECT id FROM ".$tbl." WHERE parent_id='".$tmp[1]."' AND (".implode(" OR ",$tmp_condition).")";
				$condition = $ext.$rs['identifier']." IN(".$sql.")";
				if($rs['form_type'] == 'checkbox' || ($rs['form_type'] == 'select' && $rs['is_multiple'])){
					$condition = $ext.$rs['identifier']." LIKE CONCAT('%',".($sql).",'%')";
				}
				return $condition;
			}
			if($tmp[0] == 'title'){
				foreach($tmplist as $key=>$value){
					if(!$value || !trim($value)){
						continue;
					}
					$value = trim($value);
					if($rs['search'] == 1){
						$tmp_condition[] = "title='".$value."'";
					}else{
						$tmp_condition[] = "title LIKE '%".$value."%'";
					}
				}
				$sql = "SELECT id FROM ".$this->db->prefix."list WHERE project_id='".$tmp[1]."' AND (".implode(" OR ",$tmp_condition).")";
				$condition = $ext.$rs['identifier']." IN(".$sql.")";
				if($rs['form_type'] == 'checkbox' || ($rs['form_type'] == 'select' && $rs['is_multiple'])){
					$condition = $ext.$rs['identifier']." LIKE CONCAT('%',".($sql).",'%')";
				}
				return $condition;
			}
			return false;
			
		}
	}
}