<?php
/* *
 * 類名：AlipayNotify
 * 功能：支付寶通知處理類
 * 詳細：處理支付寶各介面通知返回
 * 版本：3.2
 * 日期：2011-03-25
 * 說明：
 * 以下程式碼只是為了方便商戶測試而提供的樣例程式碼，商戶可以根據自己網站的需要，按照技術文件編寫,並非一定要使用該程式碼。
 * 該程式碼僅供學習和研究支付寶介面使用，只是提供一個參考

 *************************注意*************************
 * 除錯通知返回時，可檢視或改寫log日誌的寫入TXT裡的資料，來檢查通知返回是否正常
 */

require_once("alipay_core.function.php");
require_once("alipay_md5.function.php");

class AlipayNotify {
    /**
     * HTTPS形式訊息驗證地址
     */
	var $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
	/**
     * HTTP形式訊息驗證地址
     */
	var $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';
	var $alipay_config;

	function __construct($alipay_config){
		$this->alipay_config = $alipay_config;
	}

    /**
     * 驗證訊息是否是支付寶發出的合法訊息
     * @return 驗證結果
     */
	function verify($data){
		//當資料為空時，返回否
		if(!$data || !is_array($data)) return false;
		$isSign = $this->getSignVeryfy($data, $data["sign"]);
		$responseTxt = 'true';
		if (! empty($data["notify_id"])) {$responseTxt = $this->getResponse($data["notify_id"]);}
		if (preg_match("/true$/i",$responseTxt) && $isSign) {
			return true;
		} else {
			return false;
		}
	}
	
    /**
     * 獲取返回時的簽名驗證結果
     * @param $para_temp 通知返回來的引數陣列
     * @param $sign 返回的簽名結果
     * @return 簽名驗證結果
     */
	function getSignVeryfy($para_temp, $sign) {
		//除去待簽名引數陣列中的空值和簽名引數
		$para_filter = paraFilter($para_temp);
		
		//對待簽名引數陣列排序
		$para_sort = argSort($para_filter);
		
		//把陣列所有元素，按照“引數=引數值”的模式用“&”字元拼接成字串
		$prestr = createLinkstring($para_sort);
		$isSgin = false;
		switch (strtoupper(trim($this->alipay_config['sign_type']))) {
			case "MD5" :
				$isSgin = md5Verify($prestr, $sign, $this->alipay_config['key']);
				break;
			default :
				$isSgin = false;
		}
		
		return $isSgin;
	}

    /**
     * 獲取遠端伺服器ATN結果,驗證返回URL
     * @param $notify_id 通知校驗ID
     * @return 伺服器ATN結果
     * 驗證結果集：
     * invalid命令引數不對 出現這個錯誤，請檢測返回處理中partner和key是否為空 
     * true 返回正確資訊
     * false 請檢查防火牆或者是伺服器阻止埠問題以及驗證時間是否超過一分鐘
     */
	function getResponse($notify_id) {
		$transport = strtolower(trim($this->alipay_config['transport']));
		$partner = trim($this->alipay_config['partner']);
		$veryfy_url = '';
		if($transport == 'https') {
			$veryfy_url = $this->https_verify_url;
		}
		else {
			$veryfy_url = $this->http_verify_url;
		}
		$veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
		$responseTxt = getHttpResponseGET($veryfy_url, $this->alipay_config['cacert']);

		return $responseTxt;
	}
}
?>
