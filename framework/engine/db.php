<?php
/**
 * DB基類
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2017年12月04日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class db
{
	protected $database;
	protected $conn;
	protected $query;
	protected $preg_sql = '/^(UPDATE|DELETE|REPLACE|INSERT)/isU';
	protected $escapes = '';
	protected $special_replace;
	protected $debug = false;
	protected $cache = false;
	protected $cache_false = array();
	protected $cache_data = array();
	protected $cache_first = array();
	protected $tbl_list = array();
	protected $host = '127.0.0.1';
	protected $user = 'root';
	protected $pass = '';
	protected $port = 3306;
	protected $socket = '';
	//保留字轉義符，用於轉義資料庫保留字的轉義
	protected $kec_left = '`';
	protected $kec_right = '`';

	//使用遠端連結
	protected $client_url = '';
	
	private $time = 0;
	private $count = 0;
	private $time_tmp = 0;
	private $_sqlist = array();
	private $_slowlist = array();
	private $slow_status = false;
	private $slow_time = 1;
	public $prefix = 'qinggan_';
	public $error_type = 'exit';
	
	public function __construct($config=array())
	{
		$this->config($config);
	}

	public function config($config)
	{
		$this->database($config['data']);
		$this->prefix = $config['prefix'] ? $config['prefix'] : 'qinggan_';
		$this->debug = $config['debug'] ? true : false;
		$this->host = $config['host'] ? $config['host'] : '127.0.0.1';
		$this->user = $config['user'] ? $config['user'] : 'root';
		$this->pass = $config['pass'] ? $config['pass'] : '';
		$this->port = $config['port'] ? $config['port'] : 3306;
		$this->socket = $config['socket'] ? $config['socket'] : '';
		$this->cache = $config['cache'] ? $config['cache'] : false;
		$this->slow_status = $config['slow'] ? $config['slow'] : false;
		$this->slow_time = $config['slow_time'] ? floatval($config['slow_time']) : 1;
		if($config['client_url']){
			$this->client_url = $config['client_url'];
		}
	}

	//寫入除錯日誌
	public function __destruct()
	{
		if($this->_slowlist){
			$file = DATA.'log/'.date("Ymd").'_slow.log.php';
			if(!is_file($file)){
				$content = "<?php die('forbidden'); ?>\n";
				file_put_contents($file,$content);
			}
			$info = '';
			foreach($this->_slowlist as $key=>$value){
				$info .= $value['dateline'];
				$info .= '|=phpok=|'.$value['time'];
				$info .= '|=phpok=|'.$value['sql']."\n";
			}
			file_put_contents($file,$info,FILE_APPEND);
		}
		if($this->_sqlist){
			$file = DATA.'log/'.date("Ymd").'_debug.log.php';
			if(!is_file($file)){
				$content = "<?php die('forbidden'); ?>\n";
				file_put_contents($file,$content);
			}
			$info = '';
			foreach($this->_sqlist as $key=>$value){
				$info .= $value['count'];
				$info .= '|=phpok=|'.$value['time'];
				$info .= '|=phpok=|'.$value['sql']."\n";
			}
			file_put_contents($file,$info,FILE_APPEND);
		}
	}

	/**
	 * 設定保留字的轉義符
	 * @引數 $left 左側轉義符
	 * @引數 $right 右側轉義符，留空使用與左側一樣的轉義符
	**/
	public function kec($left='',$right='')
	{
		if($left){
			$this->kec_left = $left;
			$this->kec_right = $right ? $right : $left;
		}
		return true;
	}

	/**
	 * 資料庫伺服器
	 * @引數 $host 指定資料庫伺服器
	**/
	public function host($host='')
	{
		if($host){
			$this->host = $host;
		}
		return $this->host;
	}

	/**
	 * 資料庫賬號
	 * @引數 $user 賬號名稱
	**/
	public function user($user='')
	{
		if($user){
			$this->user = $user;
		}
		return $this->user;
	}

	/**
	 * 資料庫密碼
	 * @引數 $pass 密碼
	**/
	public function pass($pass='')
	{
		if($pass){
			$this->pass = $pass;
		}
		return $this->pass;
	}

	/**
	 * 資料庫埠
	 * @引數 $port 埠，必須是數字
	**/
	public function port($port='')
	{
		if($port && is_numeric($port)){
			$this->port = $port;
		}
		return $this->port;
	}

	/**
	 * Socket 套接字，使應用程式能夠讀寫與收發通訊協定（protocol）與資料的程式
	 * @引數 $socket 指定 socket 檔案
	**/
	public function socket($socket='')
	{
		if($socket){
			$this->socket = $socket;
		}
		return $this->socket;
	}

	/**
	 * 指定資料庫
	 * @引數 $database 資料庫名稱
	**/
	public function database($database='')
	{
		if($database){
			$this->database = $database;
		}
		return $this->database;
	}

	/**
	 * 判斷是否有連線到資料庫
	**/
	public function status()
	{
		if(!$this->conn){
			return false;
		}
		return true;
	}

	//收集表名稱
	public function cache_index($id='')
	{
		if(!$id){
			return false;
		}
		$info = $this->tbl_list[$id];
		unset($this->tbl_list[$id]);
		return $info;
	}

	//重置表名稱收集
	public function cache_set($id)
	{
		if(!$id){
			return false;
		}
		$this->tbl_list[$id] = array();
	}

	public function sql_time()
	{
		return $this->time;
	}

	public function sql_count()
	{
		return $this->count;
	}

	/**
	 * 報錯型別
	 * @引數 $type 僅支援 exit 和 json 兩個字串
	**/
	public function error_type($type='')
	{
		if($type){
			$this->error_type = $type;
		}
		return $this->error_type;
	}

	/**
	 * 自定義錯誤
	 * @引數 $error 錯誤資訊
	 * @引數 $errid 錯誤ID
	**/
	public function error($error='',$errid='')
	{
		if($this->debug){
			$info = "SQL執行錯誤【ID：{'{$errid}'}，錯誤資訊是：{'{$error}'}】";
		}else{
			$info = 'SQL執行錯誤，請檢查';
		}
		if($this->error_type == 'json'){
			$array = array('status'=>'error','content'=>$info);
			exit(json_encode($array,JSON_UNESCAPED_UNICODE));
		}else{
			echo $info;
			exit;
		}
	}

	/**
	 * 除錯，2018年11月24日後不再支援即時輸出，改為寫日誌
	 * @引數 $sql SQL語句
	 * @引數 $time 當前SQL執行時間
	**/
	public function debug($sql='',$time=0)
	{
		if(isset($sql) && is_bool($sql)){
			$this->debug = $sql;
			return $this->debug;
		}
		if($this->slow_status){
			$this->_slow($sql,$time);
		}
		if(!$this->debug){
			return true;
		}
		if($sql && trim($sql)){
			$sql = trim($sql);
			$sqlid = 'phpok'.md5($sql);
			if($this->_sqlist && $this->_sqlist[$sqlid]){
				$this->_sqlist[$sqlid]['count']++;
				$this->_sqlist[$sqlid]['time'] = round(($this->_sqlist[$sqlid]['time']+$time),5);
			}else{
				$this->_sqlist[$sqlid] = array('sql'=>$sql,'count'=>1,'time'=>$time);
			}
			return true;
		}
	}

	private function _slow($sql,$time)
	{
		if($time < $this->slow_time){
			return true;
		}
		$sql = trim($sql);
		$this->_slowlist[] = array('sql'=>$sql,'dateline'=>time(),'time'=>$time);
		return true;
	}

	public function slowlist()
	{
		if(!$this->_slowlist){
			return false;
		}
		return $this->_slowlist;
	}

	public function conn()
	{
		return $this->conn;
	}

	//快取執行計時器
	protected function _time()
	{
		$time = microtime(true);
		if($this->time_tmp){
			$tmptime = round(($time - $this->time_tmp),5);
			$this->time = round(($this->time + $tmptime),5);
			$this->time_tmp = 0;
			return $tmptime;
		}else{
			$this->time_tmp = $time;
		}
	}

	//計數器
	protected function _count($val=1)
	{
		$this->count += $val;
	}


	//通過正則獲取表
	protected function cache_sql($sql)
	{
		preg_match_all('/(FROM|JOIN|UPDATE|INTO)\s+([a-zA-Z0-9\_\.\-]+)(\s|\()+/isU',$sql,$list);
		$tbl = $list[2] ? $list[2] : false;
		if(!$tbl){
			return true;
		}
		foreach($this->tbl_list as $key=>$value){
			if(!$value){
				$value = $tbl;
			}else{
				foreach($tbl as $k=>$v){
					$value[] = $v;
				}
			}
			$value = array_unique($value);
			$this->tbl_list[$key] = $value;
		}
	}

	protected function cache_update($sql)
	{
		if(preg_match($this->preg_sql,$sql)){
			preg_match_all('/(FROM|JOIN|UPDATE|INTO)\s+([a-zA-Z0-9\_\.\-]+)(\s|\()+/isU',$sql,$list);
			$tbl = $list[2] ? $list[2] : false;
			if($tbl && $GLOBALS['app']){
				foreach($tbl as $key=>$value){
					$GLOBALS['app']->cache->delete_index($value);
				}
			}
		}
	}

	protected function cache_false_save($sql)
	{
		if(!$this->cache){
			return true;
		}
		$id = 'sql'.md5($sql);
		$this->cache_false[$id] = true;
		return true;
	}

	protected function cache_false($sql)
	{
		if(!$this->cache){
			return false;
		}
		$id = 'sql'.md5($sql);
		if(isset($this->cache_false[$id])){
			return true;
		}
		return false;
	}

	/**
	 * 檢測是否需要即時快取
	 * @引數 $sql 要檢測的SQL語句
	**/
	protected function cache_need($sql)
	{
		if(!$this->cache){
			return false;
		}
		$id = 'sql'.md5($sql);
		if($this->cache_first[$id]){
			return true;
		}
		return false;
	}

	protected function cache_first($sql)
	{
		if(!$this->cache){
			return true;
		}
		$id = 'sql'.md5($sql);
		$this->cache_first[$id] = true;
		return true;
	}

	/**
	 * 需要即時快取的資料
	 * @引數 $sql SQL語句
	 * @引數 $data 返回的結果集
	**/
	public function cache_save($sql,$data)
	{
		if(!$this->cache){
			return true;
		}
		$id = "sql".md5($sql);
		$this->cache_data[$id] = $data;
		return true;
	}

	public function cache_get($sql)
	{
		if(!$this->cache){
			return false;
		}
		$id = "sql".md5($sql);
		if(isset($this->cache_data[$id])){
			return $this->cache_data[$id];
		}
		return false;
	}

	/**
	 * 格式化陣列成SQL
	 * @引數 $data 陣列
	 * @引數 $table 表名
	 * @引數 $type 型別，僅支援 insert，replace 三種
	**/
	protected function _insert_array($data,$table,$type='insert')
	{
		$sql = (strtolower($type) == 'insert' ? "INSERT" : "REPLACE")." INTO ".$table." ";
		$sql_fields = array();
		$sql_val = array();
		foreach($data as $key=>$value){
			$sql_fields[] = $this->kec_left.$key.$this->kec_right;
			$sql_val[] = "'".$value."'";
		}
		$sql.= "(".(implode(",",$sql_fields)).") VALUES(".(implode(",",$sql_val)).")";
		return $sql;
	}

	/**
	 * 寫入操作
	 * @引數 $sql 要插入的SQL或陣列
	 * @引數 $tbl 資料表名稱
	 * @引數 $type 插放入方式，僅限 $sql 為陣列時有效，當為布林值時表示是否字首，此時type預設為 insert
	 * @引數 $prefix 是否檢查字首
	**/
	public function insert($sql,$tbl='',$type='insert',$prefix=true)
	{
		if(is_array($sql) && $tbl){
			if(is_bool($type)){
				$prefix = $type;
				$type = 'insert';
			}
			return $this->insert_array($sql,$tbl,$type,$prefix);
		}
		$this->query($sql);
		return $this->insert_id();
	}

	/**
	 * 陣列寫入操作
	 * @引數 $data 陣列
	 * @引數 $tbl 表名
	 * @引數 $type 寫入方式
	 * @引數 $prefix 是否檢查字首
	**/
	public function insert_array($data,$tbl,$type="insert",$prefix=true)
	{
		if(!$tbl || !$data || !is_array($data)){
			return false;
		}
		if($prefix && substr($tbl,0,strlen($this->prefix)) != $this->prefix){
			$tbl = $this->prefix.$tbl;
		}
		$sql = $this->_insert_array($data,$tbl,$type);
		return $this->insert($sql);
	}

	/**
	 * 刪除表資料操作
	 * @引數 $table 表名
	 * @引數 $condition 查詢條件
	 * @引數 $prefix 是否檢查字首
	**/
	public function delete($table,$condition='',$prefix=true)
	{
		if(!$condition || !$table){
			return false;
		}
		if(is_array($condition)){
			$sql_fields = array();
			foreach($condition as $key=>$value){
				$sql_fields[] = $this->kec_left.$key.$this->kec_right."='".$value."' ";
			}
			$condition = implode(" AND ",$sql_fields);
			if(!$condition){
				return false;
			}
		}
		if($prefix && substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$sql = "DELETE FROM ".$table." WHERE ".$condition;
		return $this->query($sql);
	}

	/**
	 * 更新操作
	 * @引數 $sql SQL語句或陣列
	 * @引數 $tbl 表格
	 * @引數 $condition 更新條件
	 * @引數 $prefix 是否檢查字首
	**/
	public function update($sql,$tbl='',$condition='',$prefix=true)
	{
		if(is_array($sql) && $tbl && $condition){
			return $this->update_array($sql,$tbl,$condition,$prefix);
		}
		return $this->query($sql);
	}

	/**
	 * 更新資料
	 * @引數 $data SQL語句或陣列
	 * @引數 $tbl 表格
	 * @引數 $condition 更新條件
	 * @引數 $prefix 是否檢查字首
	**/
	public function update_array($data,$table,$condition,$prefix=true)
	{
		if(!$data || !$table || !$condition || !is_array($data) || !is_array($condition)){
			return false;
		}
		if($prefix && substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$sql = "UPDATE ".$table." SET ";
		$sql_fields = array();
		foreach($data as $key=>$value){
			$sql_fields[] = $this->kec_left.$key.$this->kec_right."='".$value."'";
		}
		$sql.= implode(",",$sql_fields);
		$sql_fields = array();
		foreach($condition as $key=>$value){
			$sql_fields[] = $this->kec_left.$key.$this->kec_right."='".$value."' ";
		}
		$sql .= " WHERE ".implode(" AND ",$sql_fields);
		return $this->query($sql);
	}
}