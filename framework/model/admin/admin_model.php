<?php
/**
 * 管理員操作
 * @package phpok\model\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年05月07日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_model extends admin_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	//所有非系統管理員
	public function all_manager()
	{
		$sql = "SELECT id,account,fullname FROM ".$this->db->prefix."adm WHERE status=1 AND if_system=0";
		return $this->db->get_all($sql);
	}

	/**
	 * SESSION鎖定檢測
	**/
	public function session_lock_check()
	{
		$info = 'lock-session-'.$this->session->sessid();
		return $this->lock_checking($info);
	}

	/**
	 * SESSION鎖定，僅限登入頁有效
	**/
	public function session_lock()
	{
		$info = 'lock-session-'.$this->session->sessid();
		$this->lib('file')->vi($this->time,$this->dir_cache.$info.'.php');
		return true;
	}

	/**
	 * IP鎖定檢測
	**/
	public function ip_lock_check()
	{
		$info = 'lock-ip-'.$this->lib('common')->ip();
		return $this->lock_checking($info);
	}

	/**
	 * IP鎖定，僅限登入頁有效
	**/
	public function ip_lock()
	{
		$info = 'lock-ip-'.$this->lib('common')->ip();
		$this->lib('file')->vi($this->time,$this->dir_cache.$info.'.php');
		return true;
	}

	/**
	 * 管理員賬號登入限制
	 * @引數 $name 管理員賬號
	**/
	public function account_lock_check($name)
	{
		$info = 'lock-admin-'.$name;
		return $this->lock_checking($info);
	}

	/**
	 * 管理員賬號鎖定
	 * @引數 $name 管理員賬號
	**/
	public function account_lock($name)
	{
		$info = 'lock-admin-'.$name;
		$this->lib('file')->vi($this->time,$this->dir_cache.$info.'.php');
		return true;
	}

	public function lock_delete($user)
	{
		$this->lib('file')->rm($this->dir_cache.'lock-admin-'.$user.'.php');
		$this->lib('file')->rm($this->dir_cache.'lock-ip-'.$this->lib('common')->ip().'.php');
		$this->lib('file')->rm($this->dir_cache.'lock-session-'.$this->session->sessid().'.php');
		$this->lib('file')->rm($this->dir_cache.'lock-'.$this->session->sessid().'-admin.php');
		return true;
	}

	private function lock_checking($info='')
	{
		if(!$info){
			return false;
		}
		$locktime = intval($this->config['lock_time'] ? $this->config['lock_time'] : 2) * 3600;
		if(!$locktime){
			$locktime = 7200;
		}
		$time = $this->time - $locktime;
		if(!file_exists($this->dir_cache.$info.'.php')){
			return false;
		}
		$dateline = $this->lib('file')->cat($this->dir_cache.$info.'.php');
		$unlock_time = $dateline + $locktime;
		return array('dateline'=>$dateline,'unlock_time'=>$unlock_time);
	}
}