<?php
/**
 * 檔案操作類
 * @package phpok\libs
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年12月08日
**/

class file_lib
{
	public $read_count;
	private $safecode = "<?php die('forbidden'); ?>\n";
	public function __construct()
	{
		$this->read_count = 0;
	}

	/**
	 * 遠端獲取內容，這裡直接呼叫html類來執行
	 * @引數 $url 網址
	 * @引數 $post 要提交的post資料
	**/
	public function remote($url,$post='')
	{
		return $GLOBALS['app']->lib('html')->get_content($url,$post);
	}

	/**
	 * 讀取資料
	 * @引數 $file 要讀取的檔案，支援遠端檔案
	 * @引數 $length 檔案長度，為空表示讀取全部，僅限本地檔案有效
	 * @引數 $filter 是否過濾安全字元，預設為true，不過濾請傳參false，僅限本地檔案有效
	 * @返回 false 或 檔案內容
	**/
	public function cat($file="",$length=0,$filter=true)
	{
		if(!$file){
			return false;
		}
		if(strpos($file,"://") !== false && strpos($file,'file://') === false){
			return $this->remote($file);
		}
		if(!file_exists($file)){
			return false;
		}
		$this->read_count++;
		
		if($length && is_numeric($length)){
			$maxlength = $length;
			if($filter){
				$maxlength = $length + strlen($this->safecode);
			}
			$fp = fopen($file,'rb');
			if(!$fp){
				return false;
			}
			$content = fread($fp,$maxlength);
			fclose($fp);
		}else{
			$content = file_get_contents($file);
		}
		if(!$content){
			return false;
		}
		if($filter || (is_bool($length) && $length)){
			$content = str_replace($this->safecode,'',$content);
		}
		return $content;
	}

	/**
	 * 儲存資料
	 * @引數 $content 要儲存的內容，支援字串，陣列，多維陣列等
	 * @引數 $file 儲存的檔案地址
	 * @引數 $var 僅限$content為陣列，此項不為空時使用
	 * @引數 $type 寫入方式，預設為wb，清零寫入
	 * @返回 true/false
	**/
	public function vi($content='',$file='',$var="",$type="wb")
	{
		if(!$content || !$file){
			return false;
		}
		$this->make($file,"file");
		if(is_array($content) && $var){
			$content = $this->__array($content,$var);
			$safecode = 'if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}';
			$content = "<?php\n".$safecode."\n".$content."\n//-----end";
		}else{
			if(strtolower($type) == 'wb' || strtolower($type) == 'w'){
				$content = $this->safecode.$content;
			}
		}
		$this->_write($content,$file,$type);
		return true;
	}

	/**
	 * 儲存php等原始碼檔案，不會寫入安全保護
	 * @引數 $content 要儲存的內容
	 * @引數 $file 儲存的地址
	 * @引數 $type 寫入模式，wb 表示完全寫入，ab 表示追加寫入
	**/
	public function vim($content,$file,$type="wb")
	{
		$this->make($file,"file");
		return $this->_write($content,$file,$type);
	}

	/**
	 * 儲存資料別名，不改寫任何東西
	 * @引數 $content 要儲存的內容
	 * @引數 $file 儲存的地址
	 * @引數 $type 寫入模式，wb 表示完全寫入，ab 表示追加寫入
	**/
	public function save($content,$file,$type='wb')
	{
		return $this->vim($content,$file,$type);
	}

	/**
	 * 儲存圖片，內容不進行stripslashes處理
	 * @引數 $content 要儲存的內容
	 * @引數 $file 要儲存的檔案
	 * @返回 
	 * @更新時間 
	**/
	public function save_pic($content,$file)
	{
		$this->make($file,"file");
		$handle = $this->_open($file,"wb");
		fwrite($handle,$content);
		unset($content);
		$this->_close($handle);
		return true;
	}

	/**
	 * 刪除操作，請一定要小心，在程式中最好嚴格一些，不然有可能將整個目錄刪掉
	 * @引數 $del 要刪除的檔案或資料夾
	 * @引數 $type 僅支援file和folder，為file時僅刪除$del檔案，如果$del為資料夾，表示刪除其下面的檔案。為folder時，表示刪除$del這個檔案，如果為資料夾，表示刪除此資料夾及子項
	 * @返回 true/false
	**/
	public function rm($del,$type="file")
	{
		if(!file_exists($del)){
			return false;
		}
		if(is_file($del)){
			unlink($del);
			return true;
		}
		$array = $this->_dir_list($del);
		if(!$array){
			if($type == 'folder'){
				rmdir($del);
			}
			return true;
		}
		foreach($array as $key=>$value){
			if(file_exists($value)){
				if(is_dir($value)){
					$this->rm($value,$type);
				}else{
					unlink($value);
				}
			}
		}
		if($type == "folder"){
			rmdir($del);
		}
		return true;
	}

	/**
	 * 建立檔案或目錄
	 * @引數 $file 檔案或目錄
	 * @引數 $type 預設是dir，表示建立目錄
	 * @返回 true
	**/
	public function make($file,$type="dir")
	{
		$newfile = $file;
		$msg = "";
		if(defined("ROOT")){
			$root_strlen = strlen(ROOT);
			if(substr($file,0,$root_strlen) == ROOT){
				$newfile = substr($file,$root_strlen);
			}
			$msg = ROOT;//從根目錄記算起是否有檔案寫入
		}
		$array = explode("/",$newfile);
		$count = count($array);
		if($type == "dir"){
			for($i=0;$i<$count;$i++){
				$msg .= $array[$i];
				if(!file_exists($msg) && ($array[$i])){
					mkdir($msg,0777);
				}
				$msg .= "/";
			}
		}else{
			for($i=0;$i<($count-1);$i++){
				$msg .= $array[$i];
				if(!file_exists($msg) && ($array[$i])){
					mkdir($msg,0777);
				}
				$msg .= "/";
			}
			if(!file_exists($file)){
				@touch($file);//建立檔案
			}
		}
		return true;
	}

	/**
	 * 複製操作
	 * @引數 $old 舊檔案（夾）
	 * @引數 $new 新檔案（夾）
	 * @引數 $recover 是否覆蓋
	 * @返回 false/true
	**/
	public function cp($old,$new,$recover=true)
	{
		if(!file_exists($old)){
			return false;
		}
		if(is_file($old)){
			//如果目標是資料夾
			if(substr($new,-1) == '/'){
				$this->make($new,'dir');
				$basename = basename($old);
				if(file_exists($new.$basename) && !$recover){
					return false;
				}
				copy($old,$new.$basename);
				return true;
			}
			if(file_exists($new) && !$recover){
				return false;
			}
			copy($old,$new);
			return true;
		}
		$basename = basename($old);
		$this->make($new.$basename,'dir');
		$dlist = $this->ls($old);
		if($dlist && count($dlist)>0){
			foreach($dlist as $key=>$value){
				$this->cp($value,$new.$basename.'/',$recover);
			}
		}
		return true;
	}

	#[]
	/**
	 * 檔案移動操作
	 * @引數 $old 舊檔案（夾）
	 * @引數 $new 新檔案（夾）
	 * @引數 $recover 是否覆蓋
	 * @返回 false/true
	**/
	public function mv($old,$new,$recover=true)
	{
		if(!file_exists($old)){
			return false;
		}
		if(substr($new,-1) == "/"){
			$this->make($new,"dir");
		}else{
			$this->make($new,"file");
		}
		if(file_exists($new)){
			if($recover){
				unlink($new);
			}else{
				return false;
			}
		}else{
			$new = $new.basename($old);
		}
		rename($old,$new);
		return true;
	}

	/**
	 * 獲取資料夾列表
	 * @引數 $folder 獲取指定資料夾下的列表（僅一層深度）
	 * @返回 陣列
	**/
	public function ls($folder)
	{
		$this->read_count++;
		$list = $this->_dir_list($folder);
		if(is_array($list)){
			sort($list,SORT_STRING);
		}
		return $list;
	}

	/**
	 * 獲取資料夾及子資料夾等多層檔案列表（無限級，長度受系統限制）
	 * @引數 $folder 資料夾
	 * @引數 $list 引用變數
	**/
	public function deep_ls($folder,&$list)
	{
		$this->read_count++;
		$tmplist = $this->_dir_list($folder);
		foreach($tmplist AS $key=>$value){
			if(is_dir($value)){
				$this->deep_ls($value,$list);
			}else{
				$list[] = $value;
			}
		}
	}

	/**
	 * 取得資料夾下的列表
	 * @引數 $file 檔案（夾）
	 * @引數 $type 僅支援folder或file，為file，直接返回$file本身
	 * @返回 $file或陣列
	**/
	private function _dir_list($file,$type="folder")
	{
		if(substr($file,-1) == "/"){
			$file = substr($file,0,-1);
		}
		if(!file_exists($file)){
			return false;
		}
		if($type == "file" || is_file($file)){
			return $file;
		}else{
			$handle = opendir($file);
			$array = array();
			while(false !== ($myfile = readdir($handle))){
				if($myfile != "." && $myfile != ".." && $myfile != ".svn") $array[] = $file."/".$myfile;
			}
			closedir($handle);
			return $array;
		}
	}

	/**
	 * 陣列轉成字串
	 * @引數 $array 要轉的陣列的
	 * @引數 $var 傳遞的變數
	 * @引數 $content 內容
	 * @返回 
	 * @更新時間 
	**/
	private function __array($array,$var,$content="")
	{
		foreach($array AS $key=>$value){
			if(is_array($value)){
				$content .= $this->__array($value,"".$var."[\"".$key."\"]");
			}else{
				$old_str = array('"',"<?php","?>","\r");
				$new_str = array("'","&lt;?php","?&gt;","");
				$value = str_replace($old_str,$new_str,$value);
				$content .= "\$".$var."[\"".$key."\"] = \"".$value."\";\n";
			}
		}
		return $content;
	}

	/**
	 * 開啟檔案
	 * @引數 $file 開啟的檔案
	 * @引數 $type 開啟型別，預設是wb
	**/
	private function _open($file,$type="wb")
	{
		$handle = fopen($file,$type);
		$this->read_count++;
		return $handle;
	}

	/**
	 * 寫入資訊
	 * @引數 $content 內容
	 * @引數 $file 要寫入的檔案
	 * @引數 $type 開啟方式
	 * @返回 true
	**/
	private function _write($content,$file,$type="wb")
	{
		if($content){
			$content = stripslashes($content);
		}
		$handle = $this->_open($file,$type);
		fwrite($handle,$content);
		unset($content);
		$this->_close($handle);
		return true;
	}

	/**
	 * 關閉控制程式碼
	 * @引數 $handle 控制程式碼
	**/
	private function _close($handle)
	{
		return fclose($handle);
	}

	/**
	 * 附件下載
	 * @引數 $file 要下載的檔案地址
	 * @引數 $title 下載後的檔名
	**/
	public function download($file,$title='')
	{
		if(!$file){
			return false;
		}
		if(!file_exists($file)){
			return false;
		}
		$ext = pathinfo($file,PATHINFO_EXTENSION);
		$filesize = filesize($file);
		if(!$title){
			$title = basename($file);
		}else{
			$title = str_replace('.'.$ext,'',$title);
			$title.= '.'.$ext;
		}
		ob_end_clean();
		set_time_limit(0);
		header("Content-type: applicatoin/octet-stream");
		header("Date: ".gmdate("D, d M Y H:i:s",time())." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s",time())." GMT");
		header("Content-Encoding: none");
		header("Content-Disposition: attachment; filename=".rawurlencode($title)."; filename*=utf-8''".rawurlencode($title));
		header("Accept-Ranges: bytes");
		$range = 0;
		$size2 = $filesize -1;
		if (isset ($_SERVER['HTTP_RANGE'])) {
		    list ($a, $range) = explode("=", $_SERVER['HTTP_RANGE']);
		    $new_length = $size2 - $range;
		    header("HTTP/1.1 206 Partial Content");
		    header("Content-Length: ".$new_length); //輸入總長
		    header("Content-Range: bytes ".$range."-".$size2."/".$filesize);
		} else {
		    header("Content-Range: bytes 0-".$size2."/".$filesize); //Content-Range: bytes 0-4988927/4988928
		    header("Content-Length: ".$filesize);
		}
		$handle = fopen($file, "rb");
		fseek($handle, $range);
		set_time_limit(0);
		while (!feof($handle)) {
			print (fread($handle, 1024 * 8));
			flush();
			ob_flush();
		}
		fclose($handle);
	}
}