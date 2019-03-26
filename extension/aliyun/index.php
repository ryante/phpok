<?php
/**
 * 阿里雲SDK資訊，請配合外掛或是閘道器路由使用
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2017年12月23日
**/


/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

include_once dirname(__FILE__).'/aliyun-php-sdk-core/Config.php';

/**
 * 郵件推送
**/
use Dm\Request\V20151123 as Dm;

/**
 * 視訊上傳
**/
use vod\Request\V20170321 as vod;

/**
 * 短訊息服務
**/
use Aliyun\DySDKLite as sms;

class aliyun_lib
{
	/**
	 * Access Key ID 金鑰ID
	**/
	private $access_key = '';

	/**
	 * Access Key Secret 金鑰加密引數
	**/
	private $access_secret = '';

	/**
	 * 伺服器節點ID，預設使用 cn-hangzhou
	**/
	private $regoin_id = 'cn-hangzhou';

	/**
	 * 簽名
	**/
	private $signature = '錕鋙科技';

	/**
	 * 模板ID，一般適用於簡訊傳送使用
	**/
	private $template_id = 0;

	/**
	 * 伺服器節點地址
	**/
	private $end_point = ''; //節點

	/**
	 * 郵件傳送賬號
	**/
	private $dm_account = '';

	/**
	 * 發件人暱稱
	**/
	private $dm_name = '錕鋙科技';

	private $client;

	/**
	 * 建構函式
	**/
	public function __construct()
	{
		//
	}

	/**
	 * Access Key ID 金鑰ID
	 * @引數 $val 要設定的值
	**/
	public function access_key($val='')
	{
		if($val){
			$this->access_key = $val;
		}
		return $this->access_key;
	}

	/**
	 * Access Key Secret 金鑰加密引數
	 * @引數 $val 要設定的值
	**/
	public function access_secret($val='')
	{
		if($val){
			$this->access_secret = $val;
		}
		return $this->access_secret;
	}

	/**
	 * 伺服器節點ID，預設使用 cn-hangzhou
	 * @引數 $val 要設定的值
	**/
	public function regoin_id($val='')
	{
		if($val){
			$this->regoin_id = $val;
		}
		return $this->regoin_id;
	}

	/**
	 * 簽名
	 * @引數 $val 要設定的值
	**/
	public function signature($val='')
	{
		if($val){
			$this->signature = $val;
		}
		return $this->signature;
	}

	/**
	 * 模板ID
	 * @引數 $val 要設定的值
	**/
	public function template_id($val='')
	{
		if($val){
			$this->template_id = $val;
		}
		return $this->template_id;
	}

	/**
	 * 伺服器節點地址
	 * @引數 $val 要設定的值
	**/
	public function end_point($val='')
	{
		if($val){
			$this->end_point = $val;
		}
		return $this->end_point;
	}

	/**
	 * 郵件賬號
	 * @引數 $val 要設定的值
	**/
	public function dm_account($val='')
	{
		if($val){
			$this->dm_account = $val;
		}
		return $this->dm_account;
	}

	/**
	 * 發件人稱呼
	 * @引數 $val 要設定的值
	**/
	public function dm_name($val='')
	{
		if($val){
			$this->dm_name = $val;
		}
		return $this->dm_name;
	}

	/**
	 * 郵件傳送
	 * @引數 $title 郵件主題
	 * @引數 $content 郵件內容
	 * @引數 $mailto 目標郵箱
	**/
	public function email($title='',$content='',$mailto='')
	{
		if(!$title || !$content || !$mailto){
			return $this->error(P_Lang('引數傳遞不完整'));
		}
		if(!$this->access_key){
			return $this->error(P_Lang('未指定Access Key'));
		}
		if(!$this->access_secret){
			return $this->error(P_Lang('未指定Access Secret'));
		}
		if(!$this->signature){
			return $this->error(P_Lang('未配置標籤'));
		}
		if(!$this->dm_account){
			return $this->error(P_Lang('未配置發信地址'));
		}
		if(!$this->dm_name){
			return $this->error(P_Lang('未配置發信人暱稱'));
		}
		$iClientProfile = DefaultProfile::getProfile($this->regoin_id, $this->access_key,$this->access_secret);
		if($this->end_point && $this->regoin_id != 'cn-hangzhou'){
			$iClientProfile::addEndpoint($this->regoin_id,$this->regoin_id,"Dm",$this->end_point);
		}
		$client = new DefaultAcsClient($iClientProfile);
		$request = new Dm\SingleSendMailRequest();
		if($this->regoin_id != 'cn-hangzhou'){
			$request->setVersion("2017-06-22");
		}
		$request->setAccountName($this->dm_account);
		$request->setFromAlias($this->dm_name);
		$request->setAddressType(1);
		$request->setTagName($this->signature);
		$request->setReplyToAddress("true");
		$request->setToAddress($mailto);
		$request->setSubject($title);
		$request->setHtmlBody(stripslashes($content));
		try {
			$response = $client->getAcsResponse($request);
			return $this->success();
		}
		catch (ClientException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
		catch (ServerException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
	}

	/**
	 * 簡訊傳送
	 * @引數 $mobile 目標手機號
	 * @引數 $data 變數引數，僅限陣列
	**/
	public function sms($mobile,$data='')
	{
		if(!$mobile){
			return $this->error(P_Lang('未指定手機號'));
		}
		if(!$this->template_id){
			return $this->error(P_Lang('未指定模板ID'));
		}
		if(!$this->access_key){
			return $this->error(P_Lang('未指定Access Key'));
		}
		if(!$this->access_secret){
			return $this->error(P_Lang('未指定Access Secret'));
		}
		if(!$this->signature){
			return $this->error(P_Lang('未配置簽名'));
		}

		if(!$this->end_point){
			return $this->error(P_Lang('未配置EndPoint'));
		}

		$params = array("PhoneNumbers"=>$mobile,'SignName'=>$this->signature,'TemplateCode'=>$this->template_id);
		if($data){
			$data = json_encode($data, JSON_UNESCAPED_UNICODE);
			$params['TemplateParam'] = $data;
		}
		$params['RegionId'] = $this->regoin_id;
		$params['Action'] = 'SendSms';
		$params['Version'] = '2017-05-25';
		$helper = new sms\SignatureHelper();
		$content = $helper->request($this->access_key,$this->access_secret,$this->end_point,$params);
		if(!$content){
			return $this->error('簡訊傳送失敗');
		}
		$content = (array) $content;
		if($content['Code'] == 'OK'){
			return $this->success($content);
		}
		return $this->error($content['Message'],$content['Code']);
	}

	public function client()
	{
		if(!$this->regoin_id){
			return $this->error(P_Lang('未設定 Regoin ID'));
		}
		if(!$this->access_key){
			return $this->error(P_Lang('未指定Access Key ID'));
		}
		if(!$this->access_secret){
			return $this->error(P_Lang('未指定Access Key Secret'));
		}
		if(!$this->signature){
			return $this->error(P_Lang('未配置標籤'));
		}
		$iClientProfile = DefaultProfile::getProfile($this->regoin_id, $this->access_key,$this->access_secret);
		$this->client = new DefaultAcsClient($iClientProfile);
		return $this->client;
	}

	/**
	 * 上傳視訊檔案
	 * @引數 $filename 檔名
	 * @引數 $title 標題
	 * @引數 $thumb 縮圖
	 * @引數 $note 摘要
	 * @引數 $tag 標籤
	**/
	public function create_upload_video($filename,$title='',$thumb='',$note='',$tag='')
	{
		if(!$filename){
			return false;
		}
		if(!$title){
			$tmp = explode(".",$filename);
			$title = $tmp[0];
			if(!$title){
				$title = $filename;
			}
		}
		$request = new vod\CreateUploadVideoRequest();
		$request->setAcceptFormat('JSON');
		$request->setRegionId($this->regoin_id);
		$request->setTitle($title);
		//視訊原始檔名稱(必須包含副檔名)
		$request->setFileName($filename);
		//視訊原始檔位元組數
		$request->setFileSize(0);
		if($note){
			$request->setDescription($note);
		}
		if($thumb){
			$request->setCoverURL($thumb);
		}
		//$request->setIP("127.0.0.1");
		if($tag){
			$request->setTags($tag);
		}
		$request->setCateId(0);
		try {
			$response = $this->client->getAcsResponse($request);
			return $this->success($response);
		}
		catch (ClientException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
		catch (ServerException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
	}

	/**
	 * 視訊重新整理
	 * @引數 $videoid 視訊ID
	**/
	public function refresh_upload_video($videoid)
	{
		if(!$videoid){
			return false;
		}
		$request = new vod\RefreshUploadVideoRequest();
		$request->setAcceptFormat('JSON');
		$request->setRegionId($this->regoin_id);
		$request->setVideoId($videoid);
		try {
			$response = $this->client->getAcsResponse($request);
			return $this->success($response);
		}
		catch (ClientException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
		catch (ServerException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
	}

	/**
	 * 取得視訊資訊
	 * @引數 $videoid 視訊ID
	**/
	public function video_info($videoid)
	{
		if(!$videoid){
			return false;
		}
		$request = new vod\GetVideoInfoRequest();
		$request->setAcceptFormat('JSON');
		$request->setRegionId($this->regoin_id);
		$request->setVideoId($videoid);
		try {
			$response = $this->client->getAcsResponse($request);
			return $this->success($response);
		}
		catch (ClientException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
		catch (ServerException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
	}

	/**
	 * 視訊列表
	 * @引數 $pageid 頁碼
	 * @引數 $psize 每次查詢數量
	 * @引數 $starttime 開始時間
	 * @引數 $endtime 結束時間
	**/
	public function video_list($pageid=1,$psize=30,$starttime='',$endtime='')
	{
		$request = new vod\GetVideoListRequest();
		$request->setPageNo($pageid);
		$request->setPageSize($psize);
		if($starttime){
			if(substr($starttime,-1) != 'Z'){
				$starttime .= 'Z';
			}
			$request->setStartTime($starttime);
		}
		if($endtime){
			if(substr($endtime,-1) != 'Z'){
				$endtime .= 'Z';
			}
			$request->setEndTime($endtime);
		}
		try {
			$response = $this->client->getAcsResponse($request);
			return $this->success($response);
		}
		catch (ClientException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
		catch (ServerException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
	}

	public function play_auth($videoid)
	{
		if(!$videoid){
			return false;
		}
		$request = new vod\GetVideoPlayAuthRequest();
		$request->setAcceptFormat('JSON');
		$request->setRegionId($this->regoin_id);
		$request->setVideoId($videoid);
		try {
			$response = $this->client->getAcsResponse($request);
			return $this->success($response);
		}
		catch (ClientException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
		catch (ServerException  $e) {
			return $this->error($e->getErrorMessage(),$e->getErrorCode());
		}
	}

	/**
	 * 錯誤返回
	 * @引數 $error 錯誤內容
	 * @引數 $errid 錯誤ID
	 * @返回 陣列
	**/
	private function error($error='',$errid=0)
	{
		if(!$error){
			$error = '異常';
		}
		$array = array('status'=>false,'error'=>$error);
		if($errid){
			$array['errid'] = $errid;
		}
		return $array;
	}

	/**
	 * 成功時返回的結果
	 * @引數 $info 返回的內容，支援字串，陣列，及空
	**/
	private function success($info='')
	{
		$array = array('status'=>true);
		if($info != ''){
			$array['info'] = $info;
		}
		return $array;
	}
}
