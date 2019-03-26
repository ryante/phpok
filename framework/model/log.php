<?php
/**
 * 日誌相關
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @許可 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年05月05日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class log_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 儲存日誌
	 * @引數 $note 日誌說明
	 * @引數 $mask 是否手動標記，為true時表示手動標誌
	**/
	public function save($note='',$mask=false)
	{
		if(!$note){
			$tmpfile = $this->app_id.'/'.$this->ctrl.'_control.php';
			$note = P_Lang('執行檔案{ctrl}方法：{func}',array('ctrl'=>$tmpfile,'func'=>$this->func.'_f'));
		}
		if(is_string($note) && strpos($note,'<') !== false){
			$note = htmlentities($note);
			$note = phpok_cut($note,255,'…');
		}
		//傳過來的日誌說明為陣列或對像都是手動標誌
		if(is_array($note) || is_object($note)){
			$note = '<pre>'.print_r($note,true).'</pre>';
			$mask = true;
		}
		$note = addslashes(trim($note));
		$ip = $this->lib('common')->ip();
		$data = array('note'=>$note,'dateline'=>$this->time,'app_id'=>$this->app_id,'admin_id'=>0,'user_id'=>0,'ip'=>$ip);
		if($this->app_id == 'admin'){
			if($this->session->val('admin_id')){
				$data['admin_id'] = $this->session->val('admin_id');
			}
		}else{
			if($this->session->val('user_id')){
				$data['user_id'] = $this->session->val('user_id');
			}
		}
		$data['ctrl'] = $this->ctrl;
		$data['func'] = $this->func;
		$data['mask'] = $mask ? 1 : 0;
		$url = $this->lib('server')->https() ? 'https://' : 'http://';
		$url.= $this->lib('server')->domain($this->config['get_domain_method']);
		$port = $this->lib('server')->port();
		if($port != 80 && $port != 443){
			$url .= ':'.$port;
		}
		$url .= $this->lib('server')->uri();
		$referer = $this->lib('server')->referer();
		$data['url'] = $this->format($url);
		$data['referer'] = $this->format($referer);
		$data['session_id'] = $this->session->sessid();
		
		//1分鐘內同樣的錯誤不再重複寫入
		$time = $this->time - 60;
		$sql = "SELECT id FROM ".$this->db->prefix."log WHERE note='".$note."' AND app_id='".$this->app_id."' AND ctrl='".$data['ctrl']."'";
		$sql.= " AND func='".$data['func']."' AND dateline>=".$time." LIMIT 1";
		$chk = $this->db->get_one($sql);
		if($chk){
			return false;
		}

		//登入頁防止刷庫，僅允許10秒寫入一條資料
		if($data['ctrl'] == 'login'){
			$time = $this->time - 10;
			$sql = "SELECT id FROM ".$this->db->prefix."log WHERE app_id='".$this->app_id."' AND ctrl='login' AND dateline>=".$time." LIMIT 1";
			$chk = $this->db->get_one($sql);
			if($chk){
				return false;
			}
		}
		$this->db->insert_array($data,'log');
	}

	/**
	 * 取得日誌列表
	 * @引數 $condition 查詢條件
	 * @引數 $offset 開始位置，首位從0計起
	 * @引數 $psize 每次讀取數量
	**/
	public function get_list($condition='',$offset=0,$psize=30)
	{
		$sql  = "SELECT l.*,a.account,u.user FROM ".$this->db->prefix."log l ";
		$sql .= "LEFT JOIN ".$this->db->prefix."adm a ON(l.admin_id=a.id) ";
		$sql .= "LEFT JOIN ".$this->db->prefix."user u ON(l.user_id=u.id) ";
		if($condition){
			$sql.= "WHERE ".$condition." ";
		}
		$sql.= "ORDER BY l.dateline DESC,l.id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	/**
	 * 取得日誌數量
	 * @引數 $condition 查詢條件
	**/
	public function get_count($condition='')
	{
		$sql  = "SELECT count(l.id) FROM ".$this->db->prefix."log l ";
		$sql .= "LEFT JOIN ".$this->db->prefix."adm a ON(l.admin_id=a.id) ";
		$sql .= "LEFT JOIN ".$this->db->prefix."user u ON(l.user_id=u.id) ";
		if($condition){
			$sql.= "WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

}
