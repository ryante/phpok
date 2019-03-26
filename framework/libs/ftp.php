<?php
/***********************************************************
	Filename: {phpok}/libs/ftp.php
	Note	: FTP基本操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-02-18 17:46
***********************************************************/
class ftp_lib
{

	var $hostname	= ''; # FTP伺服器
	var $username	= ''; # FTP賬號
	var $password	= ''; # FTP密碼
	var $port 		= 21; # 連線埠
	var $passive 	= true; # 是否使用被動模式
	var $conn_id 	= false; # 連線ID
	var $root_dir   = "/"; # 伺服器根目錄
	var $timeout    = 60; # FTP連線超時時間

	# 建構函式，此引數在PHPOK中極少使用或是不使用
	function __construct() {}

	# 設定FTP伺服器資訊
	function hostname($hostname="")
	{
		if(!$hostname) return false;
		$hostname = preg_replace('|.+?://|','',$hostname);
		$this->hostname = $hostname;
	}

	# 設定登入賬號
	function username($username="Anonymous")
	{
		$this->username = $username;
	}

	# 設定登入密碼
	function password($password="")
	{
		$this->password = $password;
	}

	# 設定FTP的埠
	function port($port=21)
	{
		$this->port = 21;
	}

	# 設定根目錄
	function root_dir($root_dir="/")
	{
		$this->root_dir = $root_dir;
	}

	function connect($config = array())
	{
		$this->init($config);
		if(!$this->hostname)
		{
			return false;
		}
		# 連線FTP資訊
		$this->conn_id = @ftp_connect($this->hostname,$this->port,$this->timeout);
		if(!$this->conn_id) return false;
		# FTP登入
		$login_status = $this->login();
		if(!$login_status) return false;
		# 啟用被動模式
		if($this->passive) ftp_pasv($this->conn_id, true);
		return true;

		# 改變目錄
		$dir_status = $this->change_dir($this->root_dir);
	}

	# 改變目錄
	function change_dir($path='')
	{
		if(!$path || !$this->is_conn()) return false;
		$status = @ftp_chdir($this->conn_id,$path);
		if(!$status) return false;
		return true;
	}

	# 建立目錄
	function make_dir($path,$chmod=null)
	{
		if(!$path || !$this->is_conn()) return false;
		$status = @ftp_mkdir($this->conn_id,$path);
		if(!$status) return false;
		if(!is_null($chmod))
		{
			$this->chmod($path,(int)$chmod);
		}
		return true;
	}

	# 更改目錄許可權
	function chmod($path,$chmod)
	{
		if(!$this->is_conn || !$path || !$chmod) return false;
		return @ftp_chmod($this->conn_id, $chmod, $path);
	}

	# 上傳
	function upload($local,$remote,$mode="auto",$chmod=null)
	{
		if(!$this->is_conn() || !$local || !file_exists($local) || !$remote) return false;
		if($mode == "auto")
		{
			$ext = $this->get_ext($local);
			$mode = $this->set_type($ext);
		}
		$mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;
		if(substr($this->root_dir,-1) != "/") $this->root_dir .= "/";
		$remote = $this->root_dir . $remote;

		$status = @ftp_put($this->conn_id, $remote, $local, $mode);
		if(!$status) return false;
		if( ! is_null($chmod))
		{
			$this->chmod($remote,(int)$chmod);
		}
		return true;
	}

	# 下載
	function download($remote,$local,$mode="auto")
	{
		if(!$this->is_conn() || !$local || !file_exists($local) || !$remote) return false;
		if($mode == 'auto')
		{
			$ext = $this->get_ext($remote);
			$mode = $this->set_type($ext);
		}

		$mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;
		return @ftp_get($this->conn_id, $local, $remote, $mode);
	}

	# 重新命名
	function rename($old,$new)
	{
		if(!$old || !$new || !$this->is_conn()) return false;
		return @ftp_rename($this->conn_id, $oldname, $newname);
	}

	# 移動
	function move($old,$new)
	{
		return $this->rename($old,$new);
	}

	# 刪除檔案
	function delete($file)
	{
		if(!$file || !$this->is_conn()) return false;
		return @ftp_delete($this->conn_id, $file);
	}

	# 刪除資料夾
	function delete_dir($path)
	{
		if(!$path || !$this->is_conn()) return false;
		$path = preg_replace("/(.+?)\/*$/", "\\1/", $path);
		//獲取目錄檔案列表
		$filelist = $this->filelist($path);
		if($filelist && is_array($filelist) && count($filelist) > 0)
		{
			foreach($filelist AS $item)
			{
				if(!$this->delete($item))
				{
					$this->delete_dir($item);
				}
			}
		}
		return @ftp_rmdir($this->conn_id, $path);
	}

	# 取得資料夾列表
	function filelist($path = '.')
	{
		if(!$path || ! $this->is_conn()) return false;
		return ftp_nlist($this->conn_id, $path);
	}

	# 關閉FTP連線
	function close()
	{
		if(!$this->is_conn()) return false;
		return @ftp_close($this->conn_id);
	}

	# FTP引數初始化
	function init($config = array())
	{
		foreach($config as $key => $val)
		{
			if(isset($this->$key))
			{
				$this->$key = $val;
			}
		}

		//特殊字元過濾
		if($this->hostname)
		{
			$this->hostname = preg_replace('|.+?://|','',$this->hostname);
		}
	}

	# FTP登入
	function login()
	{
		return @ftp_login($this->conn_id, $this->username, $this->password);
	}

	# 判斷是否連線
	function is_conn()
	{
		if( ! is_resource($this->conn_id))
		{
			return false;
		}
		return true;
	}

	# 取得檔案字尾名
	function get_ext($filename)
	{
		if(FALSE === strpos($filename, '.'))
		{
			return 'txt';
		}
		$extarr = explode('.', $filename);
		return strtolower(end($extarr));
	}

	# 從字尾擴充套件定義FTP傳輸模式  ascii 或 binary
	function set_type($ext)
	{
		$text_type = array ('txt','text','php','phps','php4','js','css','htm','html','phtml','shtml','log','xml');
		return (in_array($ext, $text_type)) ? 'ascii' : 'binary';
	}
}
?>