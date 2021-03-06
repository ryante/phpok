<?php
/**
 * 資料庫管理相關
 * @package phpok\model\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年10月04日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class sql_model extends sql_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	//讀取全部表資訊
	public function tbl_all()
	{
		//$this->db->cache_clear();
		$sql = "SHOW TABLE STATUS FROM ".$this->db->database();
		return $this->db->get_all($sql);
	}

	//優化資料表
	public function optimize($table)
	{
		$sql = "OPTIMIZE TABLE ".$table;
		return $this->db->query($sql);
	}

	public function repair($table)
	{
		$sql = "REPAIR TABLE ".$table;
		return $this->db->query($sql);
	}

	public function sql_prefix()
	{
		return $this->db->prefix;
	}

	public function show_create_table($table)
	{
		$sql = "SHOW CREATE TABLE ".$table;
		$this->db->set("type","num");
		$rs = $this->db->get_one($sql);
		$rs = $rs[1];
		$this->db->set("type","charet");
		return $rs;
	}

	public function getsql($tbl,$offset=0,$psize="all")
	{
		$sql = "SELECT * FROM ".$tbl;
		if($psize != "all")
		{
			$sql .= " LIMIT ".$offset.",".$psize;
		}
		return $this->db->get_all($sql);
	}

	public function table_count($tbl)
	{
		$sql = "SHOW TABLE STATUS FROM ".$this->db->database()." WHERE Name='".$tbl."'";
		$rs = $this->db->get_one($sql);
		return $rs['Rows'];
	}

	//轉化
	public function escape($string)
	{
		return $this->db->escape_string($string);
	}

	//執行SQL
	public function query($sql)
	{
		return $this->db->query($sql);
	}

	//檢測管理員是否存在，不存在或存在異常就更新
	public function update_adm($data,$id=0)
	{
		if($id){
			$sql = "UPDATE ".$this->db->prefix."adm SET account='".$data['account']."',pass='".$data['pass']."' WHERE id='".$id."'";
			return $this->db->query($sql);
		}else{
			$sql = "INSERT INTO ".$this->db->prefix."adm(account,pass,email,status,if_system) VALUES('".$data['account']."','".$data['pass']."','".$data['status']."','".$data['if_system']."')";
			return $this->db->insert($sql);
		}
	}

	//更新SESSION
	public function update_session($sid,$data)
	{
		$sql = "TRUNCATE TABLE `".$this->db->prefix."session`";
		$this->db->query($sql);
		$sql = "INSERT INTO ".$this->db->prefix."session(id,data,lasttime) VALUES('".$sid."','".$data."','".$this->time."')";
		$this->db->query($sql);
		return true;
	}

	public function table_delete($tbl)
	{
		$sql = "DROP TABLE IF EXISTS ".$tbl;
		return $this->db->query($sql);
	}
}