<?php
/**
 * 通過PDO連線MSSQL資料庫
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年02月25日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class db_pdo_mssql extends db
{
	private $type = PDO::FETCH_ASSOC;

	public function __construct($config=array())
	{
		parent::__construct($config);
		$this->kec("[","]");
	}

	public function type($type='')
	{
		if($type && $type == 'num'){
			$this->type = PDO::FETCH_NUM;
		}else{
			$this->type = PDO::FETCH_ASSOC;
		}
		return $this->type;
	}

	/**
	 * 資料庫連結
	**/
	public function connect()
	{
		$this->_time();
		$dsn = 'sqlsrv:Server='.$this->host.','.$this->port.';Database=test';
		try{
			$this->conn = new PDO($dsn,$this->user,$this->pass);
		} catch(PDOException $e){
			$this->error('資料庫連線失敗，錯誤資訊：'.$e->getMessage());
		}
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->_time();
		return true;
	}

	/**
	 * 檢測連線
	**/
	private function check_connect()
	{
		if(!$this->conn || !is_object($this->conn)){
			$this->connect();
		}
		if(!$this->conn || !is_object($this->conn)){
			$this->error('資料庫連線失敗');
		}
		return true;
	}

	/**
	 * 結束連線
	**/
	public function __destruct()
	{
		if($this->conn && is_object($this->conn)){
			$this->conn = null;
		}
	}

	//定義基本的變數資訊
	public function set($name,$value)
	{
		if($name == "rs_type" || $name == 'type'){
			$value = strtolower($value) == "num" ? PDO::FETCH_NUM : PDO::FETCH_ASSOC;
			$this->type = $value;
		}else{
			$this->$name = $value;
		}
	}

	public function query($sql,$loadcache=true)
	{
		if($loadcache){
			$this->cache_sql($sql);
		}
		$this->check_connect();
		$this->_time();
		$this->query = $this->conn->query($sql);
		if($loadcache){
			$this->cache_update($sql);
		}
		$tmptime = $this->_time();
		$this->_count();
		$this->debug($sql,$tmptime);
		return $this->query;
	}

	/**
	 * 獲取列表資料
	 * @引數 $sql 要查詢的SQL
	 * @引數 $primary 繫結主鍵
	**/
	public function get_all($sql,$primary='')
	{
		if($sql){
			$false = $this->cache_false($primary.'-'.$sql);
			if($false){
				return false;
			}
			if($this->cache_get($primary.'-'.$sql)){
				return $this->cache_get($primary.'-'.$sql);
			}
			$this->query($sql);
		}
		
		if(!$this->query || !is_object($this->query)){
			return false;
		}
		$this->_time();
		$rs = false;
		while($rows = $this->query->fetch($this->type)){
			if($primary){
				$rs[$rows[$primary]] = $rows;
			}else{
				$rs[] = $rows;
			}
		}
		$this->query->closeCursor();
		$this->_time();
		if(!$rs || count($rs)<1){
			$this->cache_false_save($primary.'-'.$sql);
			return false;
		}
		if($this->cache_need($primary.'-'.$sql)){
			$this->cache_save($primary.'-'.$sql,$rs);
		}
		$this->cache_first($primary.'-'.$sql);
		return $rs;
	}

	/**
	 * 獲取一條資料
	 * @引數 $sql 要執行的SQL
	**/
	public function get_one($sql="")
	{
		if($sql){
			$false = $this->cache_false($sql);
			if($false){
				return false;
			}
			if($this->cache_get($sql)){
				return $this->cache_get($sql);
			}
			$this->query($sql);
		}
		if(!$this->query || !is_object($this->query)){
			return false;
		}
		$this->_time();
		$rs = $this->query->fetch($this->type);
		$this->query->closeCursor();
		$this->_time();
		if(!$rs){
			$this->cache_false_save($sql);
			return false;
		}
		//檢測是否需要快取
		if($this->cache_need($sql)){
			$this->cache_save($sql,$rs);
		}
		$this->cache_first($sql);
		return $rs;
	}

	/**
	 * 返回最後插入的ID
	**/
	public function insert_id()
	{
		$this->check_connect();
		return $this->conn->lastInsertId();
	}


	/**
	 * 返回行數
	 * @引數 $sql 要執行的SQL語句
	 * @引數 $is_count 是否計算數量，僅限 sql 中使用 count() 時有效
	**/
	public function count($sql="",$is_count=true)
	{
		if($sql && is_string($sql) && $is_count){
			$this->set('type','num');
			$rs = $this->get_one($sql);
			$this->set('type','assoc');
			return $rs[0];
		}else{
			if($sql && is_string($sql)){
				$this->query($sql);
			}
			if($this->query){
				return $this->query->rowCount();
			}
		}
		return false;
	}

	/**
	 * 返回被篩選出來的欄位數目
	 * @引數 $sql 要執行的SQL語句
	**/
	public function num_fields($sql="")
	{
		if($sql){
			$this->query($sql);
		}
		if($this->query){
			return $this->query->columnCount();
		}
		return false;
	}

	/**
	 * 顯示錶欄位，僅限欄位名，沒有欄位屬性
	 * @引數 $table 表名
	 * @引數 $prefix 是否檢查資料表字首
	 * @返回 無值或表字段陣列
	**/
	public function list_fields($table,$prefix=true)
	{
		if($prefix && substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$rs = $this->get_all("SHOW COLUMNS FROM ".$table);
		if(!$rs){
			return false;
		}
		foreach($rs as $key=>$value){
			$rslist[] = $value["Field"];
		}
		return $rslist;
	}

	/**
	 * 取得明細的欄位管理
	 * @引數 $table 表名
	 * @引數 $check_prefix 是否檢查資料表字首
	**/
	public function list_fields_more($table,$check_prefix=true)
	{
		if($check_prefix && substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$rs = $this->get_all("SHOW COLUMNS FROM ".$table);
		if(!$rs){
			return false;
		}
		foreach($rs as $key=>$value){
			$tmp = array();
			foreach($value as $k=>$v){
				$tmp[strtolower($k)] = $v;
			}
			$rslist[$value["Field"]] = $tmp;
		}
		return $rslist;
	}

	/**
	 * 顯示資料庫表
	**/
	public function list_tables()
	{
		$list = $this->get_all("SHOW TABLES");
		if(!$list){
			return false;
		}
		$rslist = array();
		$id = 'Tables_in_'.$this->database;
		foreach($list as $key=>$value){
			$rslist[] = $value[$id];
		}
		return $rslist;
	}

	/**
	 * 顯示錶名
	 * @引數 $table_list 陣列，整個資料庫中的表
	 * @引數 $i 順序ID
	**/
	public function table_name($table_list,$i)
	{
		return $table_list[$i];
	}

	/**
	 * 字元轉義
	 * @引數 $char 要轉義的字元
	**/
	public function escape_string($char)
	{
		if(!$char){
			return false;
		}
		return addslashes($char);
	}

	/**
	 * 取得MySQL版本號
	 * @引數 $type 支援server和client兩種型別
	**/
	public function version($type='server')
	{
		if($type == 'server'){
			return $this->conn->getAttribute(PDO::ATTR_SERVER_VERSION);
		}else{
			return $this->conn->getAttribute(PDO::ATTR_CLIENT_VERSION);
		}
	}

	/**
	 * 建立主表操作
	 * @引數 $tblname 表名稱
	 * @引數 $pri_id 主鍵ID
	 * @引數 $note 表摘要
	 * @引數 $engine 引挈，預設是 InnoDB
	**/
	public function create_table_main($tblname,$pri_id='',$note='',$engine='')
	{
		if(!$engine){
			$engine = 'InnoDB';
		}
		if(!$pri_id){
			$pri_id = 'id';
		}
		$sql  = "CREATE TABLE IF NOT EXISTS `".$tblname."`(`".$pri_id."` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',";
		$sql .= "PRIMARY KEY (`".$pri_id."`) ) ";
		$sql .= "ENGINE=".$engine." DEFAULT CHARACTER SET utf8 COMMENT='".$note."' AUTO_INCREMENT=1;";
		return $this->query($sql);
	}

	/**
	 * 增加或修改表字段
	 * @引數 $tblname 表名稱，帶字首
	 * @引數 $data 要更新的表資訊，包括欄位有：id 表ID，type型別，length長度，unsigned是否無符號，notnull是否非空，default預設值，comment備註
	 * @引數 $old 舊錶欄位ID，如果檢查不能，表示新增
	**/
	public function update_table_fields($tblname,$data,$old='')
	{
		if(!$tblname || !$data || !is_array($data)){
			return false;
		}
		$check = $this->list_fields_more($tblname,false);
		if(!$check){
			return false;
		}
		if(!$oldid){
			$old = $data['id'];
		}
		if(!$data['type']){
			$data['type'] = 'varchar';
		}
		$sql = "ALTER TABLE `".$tblname."` ";
		if($check[$old]){
			$sql .= "CHANGE `".$old."` `".$data['id']."` ";
		}else{
			$sql .= "ADD `".$data['id']."` ";
		}
		$sql .= strtoupper($data['type']);
		if($data['type'] == 'varchar'){
			$sql .= "(255)";
		}else{
			if($data['length']){
				$sql.= "(".$data['length'].")";
			}

		}
		$sql .= " ";
		if($data['unsigned']){
			$sql .= "UNSIGNED ";
		}
		if($data['notnull']){
			$sql .= "NOT NULL ";
			if($data['default'] != ''){
				$sql .= "DEFAULT '".$data['default']."' ";
			}else{
				if($data['type'] == 'varchar'){
					$sql .= "DEFAULT '' ";
				}
			}
		}else{
			$sql .= "NULL ";
		}
		if($data['comment']){
			$sql .= "COMMENT '".$data['comment']."' ";
		}
		return $this->query($sql);
	}

	/**
	 * 建立更新索引
	 * @引數 $tblname 表名
	 * @引數 $indexname 索引名，也可以是欄位名
	 * @引數 $fields 欄位名，支援欄位陣列，留空使用索引名
	 * @引數 $old 刪除舊索引
	**/
	public function update_table_index($tblname,$indexname,$fields='',$old='')
	{
		$sql = "ALTER TABLE ".$tblname." ";
		if($old){
			$sql .= "DROP INDEX `".$old."`,";
		}
		if(!$fields){
			$fields = $indexname;
		}
		if(is_array($fields)){
			$fields = implode("`,`",$fields);
		}
		$sql .= "ADD INDEX `".$indexname."`(`".$fields."`)";
		return $this->query($sql);
	}

	/**
	 * 刪除表字段
	 * @引數 $tblname 表名稱
	 * @引數 $id 要刪除的欄位
	**/
	public function delete_table_fields($tblname,$id)
	{
		$sql = "ALTER TABLE ".$tblname." DROP `".$id."`";
		return $this->query($sql);
	}

	/**
	 * 刪除表操作
	 * @引數 $table 表名稱，要求帶字首
	 * @引數 $check_prefix 是否加字首
	**/
	public function delete_table($table,$check_prefix=true)
	{
		if($check_prefix && substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$sql = "DROP TABLE IF EXISTS `".$table."`";
		return $this->query($sql);
	}
}