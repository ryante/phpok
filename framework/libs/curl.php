<?php
/**
 * 官網封裝的curl
 * @package phpok\libs
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年07月12日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class curl_lib
{
	private $is_gzip = true; // 是否啟用 GZIP 壓縮傳輸
	private $is_post = false; // 是否啟用 POST，預設為 GET
	private $is_proxy = false; // 是否啟用代理
	private $is_ssl = false; // 是否驗證 SSL
	private $proxy_service = ''; // 代理伺服器
	private $proxy_port = ''; // 代理埠
	private $proxy_user = ''; // 代理賬號
	private $proxy_pass = ''; // 代理密碼
	private $proxy_type = 'http'; // 代理方式
	private $post_data = array(); // POST 表單資料，陣列
	private $http_body = ''; // 返回的內容資訊
	private $http_header = ''; // 返回的頭部資訊
	private $http_code = 200; // 返回的狀態
	private $host_ip = 0;
	private $headers = array(); // 傳送的頭部資訊
	private $timeout = 30; // 超時時間，單位秒
	private $connect_timeout = 10; // 連線超時時間，單位秒
	private $ssl_ca_info = ''; // SSL 的 CA 證書
	private $ssl_cert_pem = ''; // SSL 的 PEM 證書
	private $ssl_cert_type = 'PEM';
	private $ssl_pass = ''; // SSL 的證書密碼
	private $ssl_key = ''; // SSL 私鑰
	private $ssl_key_pass = ''; // SSL 私鑰密碼
	private $ssl_key_type = 'PEM'; // SSL 私鑰型別
	private $user = ''; //網址對應的使用者
	private $pass = ''; //訪問網址對應的密碼
	private $referer = ''; // 自定義來源
	private $user_agent = ''; // 自定義
	private $cookie = array(); // COOKIE 資訊，陣列
	private $cookie_file = '';
	private $error = false;

	/**
	 * 超時設定
	**/
	public function timeout($val='')
	{
		if($val && is_numeric($val)){
			$this->timeout = $val;
		}
		return $this->timeout;
	}

	public function set_cookie($key,$value='')
	{
		$this->cookie[$key] = $value;
	}

	public function cookie($key='',$value='')
	{
		if(!$key){
			return $this->cookie;
		}
		if($value == ''){
			if(isset($this->cookie[$key])){
				return $this->cookie[$key];
			}
			return false;
		}
		$this->cookie[$key] = $value;
		return $this->cookie[$key];
	}

	/**
	 * Cookie 儲存到一個檔案
	 * @引數 $file 為字串且目錄可寫時，為指定檔案，為布林值時表示刪除cookie_file
	**/
	public function cookie_file($file='')
	{
		if($file && is_writable(dirname($file)) && !is_bool($file) && !is_numeric($file)){
			$this->cookie_file = $file;
		}
		if(is_bool($file) || is_numeric($file)){
			$this->cookie_file = '';
		}
		return $this->cookie_file;
	}

	/**
	 * 清除 Cookie File 檔案
	**/
	public function cookie_file_clear()
	{
		$this->cookie_file = '';
	}

	/**
	 * 連線響應超時設定
	**/
	public function connect_timeout($val='')
	{
		if($val && is_numeric($val)){
			$this->connect_timeout = $val;
		}
		return $this->connect_timeout;
	}

	/**
	 * 設定是否啟用 GZIP 壓縮
	 * @引數 $state 布林值 true 或 false 或 1 或 0
	**/
	public function is_gzip($state = '')
	{
		if(is_bool($state) || is_numeric($state)){
			$this->is_gzip = $state;
		}
		return $this->is_gzip;
	}

	/**
	 * 設定是否啟用 POST 傳送資訊
	 * @引數 $state 布林值 true 或 false 或 1 或 0
	**/
	public function is_post($state = '')
	{
		if(is_bool($state) || is_numeric($state)){
			$this->is_post = $state;
		}
		return $this->is_post;
	}

	/**
	 * 設定是否啟用 POST 傳送資訊
	 * @引數 $state 布林值 true 或 false 或 1 或 0
	**/
	public function is_proxy($state = '')
	{
		if(is_bool($state) || is_numeric($state)){
			$this->is_proxy = $state;
		}
		return $this->is_proxy;
	}


	/**
	 * 設定是否支援 is_ssl 驗證
	 * @引數 $state 布林值 true 或 false 或 1 或 0
	**/
	public function is_ssl($state = '')
	{
		if(is_bool($state) || is_numeric($state)){
			$this->is_ssl = $state;
		}
		return $this->is_ssl;
	}

	/**
	 * CA 證書
	**/
	public function ssl_ca_info($val='')
	{
		if($val && file_exists($val)){
			$this->ssl_ca_info = $val;
		}
		return $this->ssl_ca_info;
	}

	/**
	 * SSL 的 PEM 證書
	**/
	public function ssl_cert_pem($val='')
	{
		if($val && file_exists($val)){
			$this->ssl_cert_pem = $val;
		}
		return $this->ssl_cert_pem;
	}

	/**
	 * SSL 的 PEM 證書型別
	**/
	public function ssl_cert_type($val='')
	{
		if($val && in_array(strtoupper($val,array('PEM','DER','ENG')))){
			$this->ssl_cert_type = $val;
		}
		return $this->ssl_cert_type;
	}

	/**
	 * 證書密碼
	**/
	public function ssl_pass($val='')
	{
		if($val != ''){
			$this->ssl_pass = $val;
		}
		return $this->ssl_pass;
	}

	/**
	 * SSL 的 私鑰資訊
	**/
	public function ssl_key($val='')
	{
		if($val && file_exists($val)){
			$this->ssl_key = $val;
		}
		return $this->ssl_key;
	}

	/**
	 * SSL 的 私鑰密碼
	**/
	public function ssl_key_pass($val='')
	{
		if($val){
			$this->ssl_key_pass = $val;
		}
		return $this->ssl_key_pass;
	}

	/**
	 * SSL 的 私鑰型別
	**/
	public function ssl_key_type($val='')
	{
		if($val && in_array(strtoupper($val),array('PEM','DER','ENG'))){
			$this->ssl_key_type = $val;
		}
		return $this->ssl_key_type;
	}

	/**
	 * HTTP 驗證使用者
	**/
	public function user($val='')
	{
		if($val != ''){
			$this->user = $val;
		}
		return $this->user;
	}

	public function pass($val='')
	{
		if($val != ''){
			$this->pass = $val;
		}
		return $this->pass;
	}

	public function referer($url='')
	{
		if($url){
			$this->referer = $url;
		}
		return $this->referer;
	}

	public function set_referer($url='')
	{
		return $this->referer($url);
	}

	public function user_agent($val='')
	{
		if($val){
			$this->user_agent = $val;
		}
		return $this->user_agent;
	}

	public function post_data($id='',$value='')
	{
		if($id){
			if($value != ''){
				$this->post_data[$id] = $value;
			}else{
				$this->post_data = $id;
			}
		}
		return $this->post_data;
	}

	/**
	 * 設定代理引數
	 * @引數 $service 代理伺服器
	 * @引數 $port 代理埠
	 * @引數 $user 代理使用者
	 * @引數 $pass 代理密碼
	 * @引數 $type 代理模式，預設為 http，支援 http，socks4 socks5 socks4a socks5_hostname
	**/
	public function set_proxy($service='',$port='',$user='',$pass='',$type='')
	{
		if($service){
			$this->proxy_service = $service;
		}
		if($port){
			$this->proxy_port = $port;
		}
		if($user){
			$this->proxy_user = $user;
		}
		if($pass){
			$this->proxy_pass = $pass;
		}
		$this->proxy_set_type($type);
		return true;
	}

	/**
	 * 取得或設定代理引數
	 * @引數 $id 僅支援 service，port，user，pass 四個引數
	 * @引數 $value 值
	**/
	public function proxy($id,$value='')
	{
		$tmp = array('service','port','user','pass','type');
		if(!in_array($id,$tmp)){
			return false;
		}
		$tmp_id = 'proxy_'.$id;
		if($value != '' && $id != 'type'){
			$this->$tmp_id = $value;
		}
		if($value != '' && $id == 'type'){
			return $this->proxy_set_type($value);
		}
		return $this->$tmp_id;
	}

	public function proxy_set_type($type='http')
	{
		if(!$type){
			$type = 'http';
		}
		$type = strtolower($type);
		$tmp = array('http'=> CURLPROXY_HTTP,'socks4'=>CURLPROXY_SOCKS4,'socks5'=>CURLPROXY_SOCKS5,'socks4a'=>CURLPROXY_SOCKS4A,'socks5_hostname'=>CURLPROXY_SOCKS5_HOSTNAME);
		if(!in_array($type,array_keys($tmp))){
			$type = 'http';
		}
		$this->proxy_type = $tmp[$type];
	}

	/**
	 * 設定HTTP響應頭部引數
	 * @引數 $key 變數名
	 * @引數 $value 變數值
	**/
	public function set_header($key,$value)
	{
		$this->headers[$key] = $value;
	}

	/**
	 * set_header 的別名
	**/
	public function set_head($key,$value)
	{
		return $this->set_header($key,$value);
	}

	public function http_code()
	{
		return $this->http_code;
	}

	/**
	 * 設定IP，當主機無法獲取gethostbyname
	 * @引數 $ip IP地址
	 * @返回 當前的IP或是您指定的IP
	**/
	public function host_ip($ip='')
	{
		if($ip){
			$this->host_ip = $ip;
		}
		return $this->host_ip;
	}

	public function exec($url='')
	{
		if(!$url){
			return false;
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
		curl_setopt($curl, CURLOPT_HEADER,true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		if($this->is_post && $this->post_data){
			curl_setopt($curl,CURLOPT_POST,true);
			if(is_array($this->post_data)){
				$post = http_build_query($this->post_data);
				curl_setopt($curl,CURLOPT_POSTFIELDS,$post);
				$this->set_header('Content-length',strlen($post));
			}else{
				curl_setopt($curl,CURLOPT_POSTFIELDS,$this->post_data);
				$this->set_header('Content-length',strlen($this->post_data));
			}
		}else{
			curl_setopt($curl, CURLOPT_HTTPGET,true);
		}
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,$this->connect_timeout);//等待時間，超時退出
		if($this->is_gzip){
			curl_setopt($curl,CURLOPT_ENCODING ,'gzip');//GZIP壓縮
		}
		if($this->referer){
			curl_setopt($curl, CURLOPT_REFERER,$this->referer);
		}
		if($this->cookie && is_array($this->cookie)){
			curl_setopt($curl,CURLOPT_COOKIE,implode("; ",$this->cookie));
		}
		if($this->cookie_file){
			if(file_exists($this->cookie_file)){
				curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie_file); 
			}
			curl_setopt($curl, CURLOPT_COOKIEJAR,$this->cookie_file); 
		}
		if($this->is_proxy && $this->proxy_service){
			curl_setopt($curl,CURLOPT_HTTPPROXYTUNNEL,true);
			curl_setopt($curl,CURLOPT_PROXY,$this->proxy_service);
			curl_setopt($curl,CURLOPT_PROXYPORT,$this->proxy_port);
			if($this->proxy_user || $this->proxy_pass){
				curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
				curl_setopt($curl,CURLOPT_PROXYUSERPWD,base64_encode($this->proxy_user.":".$this->proxy_pass));
			}
			curl_setopt($curl, CURLOPT_PROXYTYPE, $this->proxy_type);
		}
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
		if($this->user && $this->pass){
			curl_setopt($curl, CURLOPT_USERPWD, $this->user.":".$this->pass);
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		}

		//繫結IP後執行
		if($this->host_ip){
			$info = parse_url($url);
			$string = $info['scheme'].'://'.$info['host'];
			$url = $info['scheme'].'://'.$this->host_ip.substr($url,strlen($string));
			$port = $info['port'] ? $info['port'] : ($info['scheme'] == 'https' ? '443' : '80');
			$this->set_header('Host',$info['host'].':'.$port);
		}
		
		if($this->headers && is_array($this->headers)){
			$headers = array();
			foreach($this->headers as $key=>$value){
				$headers[] = $key.": ".$value;
			}
			if($headers && count($headers)>0){
				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			}
		}
		curl_setopt($curl, CURLOPT_URL, $url);
		if($this->is_ssl){
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
		}else{
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		}
		if($this->ssl_ca_info){
			curl_setopt($curl, CURLOPT_CAINFO, $this->ssl_ca_info); 
		}
		if($this->ssl_cert_pem){
			curl_setopt($curl,CURLOPT_SSLCERTTYPE,$this->ssl_cert_type);
			curl_setopt($curl, CURLOPT_SSLCERT, $this->ssl_cert_pem); 
			if($this->ssl_pass){
				curl_setopt($curl, CURLOPT_SSLCERTPASSWD, $this->ssl_pass); 
			}
		}
		if($this->ssl_key){
			curl_setopt($curl,CURLOPT_SSLKEYTYPE,$this->ssl_key_type);
			curl_setopt($curl,CURLOPT_SSLKEY,$this->ssl_key);
			if($this->ssl_key_pass){
				curl_setopt($curl,CURLOPT_SSLKEYPASSWD,$this->ssl_key_pass);
			}
		}
		$content = curl_exec($curl);
		if (curl_errno($curl) != 0){
			return false;
		}
		$separator = '/\r\n\r\n|\n\n|\r\r/';
		list($this->http_header, $this->http_body) = preg_split($separator, $content, 2);
		if($this->http_body){
			$this->http_body = $this->_bom($this->http_body);
		}
		$this->http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		if($this->http_code == 301 || $this->http_code == 302){
			$matches = array();
			preg_match('/Location:(.*?)\n/', $this->http_header, $matches);
			$url = @parse_url(trim(array_pop($matches)));
			if (!$url){
				return true;
			}
			$new_url = $url['scheme'] . '://' . $url['host'] . $url['path']
			. (isset($url['query']) ? '?' . $url['query'] : '');
			$new_url = stripslashes($new_url);
			return $this->exec($new_url);
		}
		if($this->http_code != '200'){
			return false;
		}
		return true;
	}

	public function get_content($url='')
	{
		return $this->get_body($url);
	}

	public function get_body($url='')
	{
		if($url){
			$this->exec($url);
		}
		return $this->http_body;
	}

	public function get_header($url='')
	{
		if($url && is_string($url)){
			$this->exec($url);
		}
		if($url && is_bool($url)){
			$info = trim($this->http_header);
			$info = str_replace("\r","",$info);
			$list = explode("\n",$info);
			$rslist = array();
			foreach($list as $key=>$value){
				if(!$value || !trim($value)){
					continue;
				}
				$value = trim($value);
				$tmp = strstr($value,':');
				if($tmp && $tmp != $value){
					$tmpid = str_replace($tmp,'',$value);
					$rslist[trim($tmpid)] = trim(substr($tmp,1));
				}
			}
			return $rslist;
		}
		return $this->http_header;
	}

	public function get_json($url='')
	{
		if(!$this->is_post && !$this->post_data){
			$this->set_header('Content-Type','application/json; charset=utf-8');
		}
		if($url){
			$this->exec($url);
		}
		if($this->http_code != 200){
			$info = $this->error('錯誤，HTTP 返回程式碼'.$this->http_code,'json');
			return json_decode($info,true);
		}
		if(!$this->http_body){
			$info = $this->error('內容為空','json');
			return json_decode($info,true);
		}
		if(substr($this->http_body,0,1) != '{'){
			$info = $this->error('非 JSON 資料','json');
			return json_decode($info,true);
		}
		return json_decode($this->http_body,true);
	}

	private function _bom($info)
	{
		$info = trim($info);
		$a1 = substr($info, 0, 1);
		$a2 = substr($info, 1, 1);
		$a3 = substr($info, 2, 1);
		if(ord($a1) == 239 && ord($a2) == 187 && ord($a3) == 191){
			return substr($info,3);
		}
		return $info;
	}

	public function get_xml($url='')
	{
		if(!$this->is_post && !$this->post_data){
			$this->set_header('Content-Type','text/xml; charset=utf-8');
		}
		if($url){
			$this->exec($url);
		}
		if($this->http_code != 200){
			return $this->error('錯誤，HTTP 返回程式碼'.$this->http_code,'xml');
		}
		if(!$this->http_body){
			return $this->error('內容為空','xml');
		}
		return $this->http_body;
	}

	public function error($error='',$type='json')
	{
		$this->error = $error;
		if($type == 'xml'){
			$xml = '<'.'?xml version="1.0" encoding="utf-8"?'.'>'."\n";
			$xml.= '<root><status>0</status><error><![CDATA['.$error.']]></error></root>';
			return $xml;
		}
		$array = array('status'=>false,'error'=>$error);
		return json_encode($array);
	}

	public function ok($type='json')
	{
		$this->error = false;
		if($type == 'xml'){
			$xml = '<'.'?xml version="1.0" encoding="utf-8"?'.'>'."\n";
			$xml.= '<root><status>1</status></root>';
			return $xml;
		}
		$array = array('status'=>true);
		return json_encode($array);
	}
}
