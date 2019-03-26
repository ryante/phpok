<?php
/**
 * 基於Memcache的SESSION
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2017年12月19日
**/
class session_memcache extends session
{
	private $host = '127.0.0.1';
	private $port = '11211';
	private $prefix = 'sess_';
	public function __construct($config)
	{
		parent::__construct($config);
		$this->config($config);
		if($this->config['prefix']){
			$this->prefix = $this->config['prefix'];
		}
		session_set_save_handler(array($this,"open"),array($this,"close"),array($this,"read"),array($this,"write"),array($this,"destory"),array($this,"gc"));
		$this->start();
	}

	public function open($save_path,$session_name)
	{
		$this->conn = new Memcache;
		$this->conn->connect($this->config['host'],$this->config['port']);
		$info = $this->conn->getExtendedStats();
		$str = $this->config['host'].':'.$this->config['port'];
		if(!$info || !$info[$str]){
			$this->error("連線Memcache伺服器失敗，請檢查");
			return false;
		}
		return true;
	}

	public function close()
	{
		return true;
	}

	public function read($sid="")
	{
		$this->sessid($sid);
		return $this->conn->get($this->prefix.$this->sessid);
	}

	public function write($sid,$data)
	{
		$this->sessid($sid);
		$this->conn->set($this->prefix.$this->sessid,$data,MEMCACHE_COMPRESSED,time() + $this->timeout);
		return true;
	}

	public function destory($sid)
	{
		$this->sessid($sid);
		$this->conn->delete($this->prefix.$this->sessid);
		return true;
	}

	public function gc()
	{
		return true;
	}
}