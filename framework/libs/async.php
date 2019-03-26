<?php
/**
 * 非同步請求PHP，跳過頁面，直接在PHP裡執行，要求伺服器支援fsockopen或pfsockopen或stream_socket_client或curl
 * @package phpok\libs
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年09月06日
**/

if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class async_lib
{
	private $funcname = false;
	private $ip = '127.0.0.1';
	private $url = '';
	
	public function __construct()
	{
		if(function_exists('ignore_user_abort')){
			ignore_user_abort(true);
		}
		set_time_limit(0);
		$this->_load_func();
	}

	public function url($url='')
	{
		if($url){
			$this->url = $url;
		}
		return $this->url;
	}

	public function ip($ip='')
	{
		if($ip){
			$this->ip = $ip;
		}
		return $this->ip;
	}

	public function loadtype($typename='')
	{
		if($typename && in_array($typename,array('fsockopen','pfsockopen','stream','curl'))){
			$this->funcname = $typename;
		}
		return $this->funcname;
	}

	public function start($url='',$ip='')
	{
		if(!$this->funcname){
			return false;
		}
		if($url){
			$this->url = $url;
		}
		if($ip){
			$this->ip = $ip;
		}
		if(!$this->url){
			return false;
		}
		if(!$this->ip){
			$host = parse_url($this->url,PHP_URL_HOST);
			$this->ip = gethostbyname($host);
			if(!$this->ip){
				return false;
			}
		}
		if($this->funcname == 'pfsockopen'){
			$name = '_phpok_fsockopen';
			$rs = $this->$name(true);
		}else{
			$name = '_phpok_'.$this->funcname;
			$rs = $this->$name();
		}
		if($rs['error_code']){
			return $rs;
		}
		return true;
	}

	/**
	 * 通過Curl實現非同步請求，有1秒延遲
	**/
	private function _phpok_curl()
	{
		$data = $this->_url_data();
		$https = false;
		if(strpos($data['host'],'ssl://') !== false){
			$https = true;
			$data['host'] = str_replace("ssl://","",$data['host']);
		}
		$myurl = $https ? 'https://'.$this->ip."/".$data['path'] : 'http://'.$this->ip.'/'.$data['path'];
		$fp = curl_init();
		curl_setopt($fp, CURLOPT_FORBID_REUSE, true); // 處理完後，關閉連線，釋放資源
		curl_setopt($fp,CURLOPT_TIMEOUT,1);
		curl_setopt($fp,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($fp,CURLOPT_ENCODING ,'gzip');//GZIP壓縮
		$header = array();
		$header[] = "Host: ".$data['host'].':'.$data['port'];
		curl_setopt($fp, CURLOPT_URL, $myurl);
		curl_setopt($fp, CURLOPT_HTTPHEADER, $header);
		if($https){
			curl_setopt($fp, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($fp, CURLOPT_SSL_VERIFYHOST, false);
		}
		curl_exec($fp);
		curl_close($fp);
		return array('error_code' => 0);
	}

	/**
	 * 通過stream_socket_client非同步請求PHP
	**/
	private function _phpok_stream()
	{
		$data = $this->_url_data();
		$fp = stream_socket_client('tcp://'.$this->ip.':'.$data['port'],$errno,$errstr,1,STREAM_CLIENT_ASYNC_CONNECT|STREAM_CLIENT_CONNECT);
		if(!$fp){
			return array('error_code' => $errno,'error_msg' => $errstr);
		}
		stream_set_blocking($fp,0);
		$header = "GET ".$data['path']." HTTP/1.1\r\n";
		$header.="Host: ".$data['host']."\r\n";
		$header.="Connection: close\r\n\r\n";//長連線關閉
		fwrite($fp, $header);
		usleep(1000); // 這一句也是關鍵，如果沒有這延時，可能在nginx伺服器上就無法執行成功
		fclose($fp);
		return array('error_code' => 0);
	}

	private function _phpok_fsockopen($status=false)
	{
		$data = $this->_url_data();
		if($status){
			$fp = pfsockopen($this->ip,$data['port'],$error_code,$error_msg,1);
		}else{
			$fp = fsockopen($this->ip,$data['port'],$error_code,$error_msg,1);
		}
		if(!$fp){
			return array('error_code' => $error_code,'error_msg' => $error_msg);
		}
		stream_set_blocking($fp,true);
		stream_set_timeout($fp,1);
		$header = "GET ".$data['path']." HTTP/1.1\r\n";
		$header.="Host: ".$data['host']."\r\n";
		$header.="Connection: close\r\n\r\n";//長連線關閉
		fwrite($fp, $header);
		usleep(1000); // 這一句也是關鍵，如果沒有這延時，可能在nginx伺服器上就無法執行成功
		fclose($fp);
		return array('error_code' => 0);
	}

	/**
	 * 檢查是支援哪種型別的非同步
	**/
	private function _load_func()
	{
		$this->funcname = false;
		if(function_exists('fsockopen')){
			$this->funcname = 'fsockopen';
			return true;
		}
		if(function_exists('pfsockopen')){
			$this->funcname = 'pfsockopen';
			return true;
		}
		if(function_exists('stream_socket_client')){
			$this->funcname = 'stream';
			return true;
		}
		$status = true;
		$list = array('curl_init','curl_setopt','curl_exec','curl_close');
		foreach($list as $key=>$value){
			if(!function_exists($value)){
				$status = false;
			}
		}
		if($status){
			$this->funcname = 'curl';
			return true;
		}
		$this->funcname = false;
		return false;
	}

	/**
	 * 格式化網址
	**/
	private function _url_data()
	{
		$host = parse_url($this->url,PHP_URL_HOST);
		$port = parse_url($this->url,PHP_URL_PORT);
		$port = $port ? $port : 80;
		$scheme = parse_url($this->url,PHP_URL_SCHEME);
		$path = parse_url($this->url,PHP_URL_PATH);
		$query = parse_url($this->url,PHP_URL_QUERY);
		if($query){
			$path .= '?'.$query;
		}
		if($scheme == 'https') {
			$host = 'ssl://'.$host;
			if($port == 80){
				$port = 443;
			}
		}
		return array('host'=>$host,'port'=>$port,'path'=>$path);
	}

}