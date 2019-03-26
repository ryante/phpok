<?php
/* *
 * 類名：AlipaySubmit
 * 功能：支付寶各介面請求提交類
 * 詳細：構造支付寶各介面表單HTML文字，獲取遠端HTTP資料
 * 版本：3.3
 * 日期：2012-07-23
 * 說明：
 * 以下程式碼只是為了方便商戶測試而提供的樣例程式碼，商戶可以根據自己網站的需要，按照技術文件編寫,並非一定要使用該程式碼。
 * 該程式碼僅供學習和研究支付寶介面使用，只是提供一個參考。
 */
require_once("alipay_core.function.php");
require_once("alipay_md5.function.php");

class AlipaySubmit {

	var $alipay_config;
	/**
	 *支付寶閘道器地址（新）
	 */
	var $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';

	function __construct($alipay_config){
		$this->alipay_config = $alipay_config;
	}
    function AlipaySubmit($alipay_config) {
    	$this->__construct($alipay_config);
    }
	
	/**
	 * 生成簽名結果
	 * @param $para_sort 已排序要簽名的陣列
	 * return 簽名結果字串
	 */
	function buildRequestMysign($para_sort) {
		//把陣列所有元素，按照“引數=引數值”的模式用“&”字元拼接成字串
		$prestr = createLinkstring($para_sort);
		
		$mysign = "";
		switch (strtoupper(trim($this->alipay_config['sign_type']))) {
			case "MD5" :
				$mysign = md5Sign($prestr, $this->alipay_config['key']);
				break;
			default :
				$mysign = "";
		}
		
		return $mysign;
	}

	/**
     * 生成要請求給支付寶的引數陣列
     * @param $para_temp 請求前的引數陣列
     * @return 要請求的引數陣列
     */
	function buildRequestPara($para_temp) {
		//除去待簽名引數陣列中的空值和簽名引數
		$para_filter = paraFilter($para_temp);

		//對待簽名引數陣列排序
		$para_sort = argSort($para_filter);

		//生成簽名結果
		$mysign = $this->buildRequestMysign($para_sort);
		
		//簽名結果與簽名方式加入請求提交引數組中
		$para_sort['sign'] = $mysign;
		$para_sort['sign_type'] = strtoupper(trim($this->alipay_config['sign_type']));
		
		return $para_sort;
	}

	/**
     * 生成要請求給支付寶的引數陣列
     * @param $para_temp 請求前的引數陣列
     * @return 要請求的引數陣列字串
     */
	function buildRequestParaToString($para_temp) {
		//待請求引數陣列
		$para = $this->buildRequestPara($para_temp);
		
		//把引數組中所有元素，按照“引數=引數值”的模式用“&”字元拼接成字串，並對字串做urlencode編碼
		$request_data = createLinkstringUrlencode($para);
		
		return $request_data;
	}
	
    /**
     * 建立請求，以表單HTML形式構造（預設）
     * @param $para_temp 請求引數陣列
     * @param $method 提交方式。兩個值可選：post、get
     * @param $button_name 確認按鈕顯示文字
     * @return 提交表單HTML文字
     */
	function buildRequestForm($para_temp, $method, $button_name) {
		//待請求引數陣列
		$para = $this->buildRequestPara($para_temp);
		
		$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->alipay_gateway_new."_input_charset=".trim(strtolower($this->alipay_config['input_charset']))."' method='".$method."'>";
		foreach($para as $key=>$val){
			$sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
		}

		//submit按鈕控制元件請不要含有name屬性
        $sHtml = $sHtml."<input type='submit' value='".$button_name."'></form>";
		
		//$sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
		
		return $sHtml;
	}
	
	/**
     * 建立請求，以模擬遠端HTTP的POST請求方式構造並獲取支付寶的處理結果
     * @param $para_temp 請求引數陣列
     * @return 支付寶處理結果
     */
	function buildRequestHttp($para_temp) {
		$sResult = '';
		
		//待請求引數陣列字串
		$request_data = $this->buildRequestPara($para_temp);

		//遠端獲取資料
		$sResult = getHttpResponsePOST($this->alipay_gateway_new, $this->alipay_config['cacert'],$request_data,trim(strtolower($this->alipay_config['input_charset'])));

		return $sResult;
	}
	
	/**
     * 建立請求，以模擬遠端HTTP的POST請求方式構造並獲取支付寶的處理結果，帶檔案上傳功能
     * @param $para_temp 請求引數陣列
     * @param $file_para_name 檔案型別的引數名
     * @param $file_name 檔案完整絕對路徑
     * @return 支付寶返回處理結果
     */
	function buildRequestHttpInFile($para_temp, $file_para_name, $file_name) {
		
		//待請求引數陣列
		$para = $this->buildRequestPara($para_temp);
		$para[$file_para_name] = "@".$file_name;
		
		//遠端獲取資料
		$sResult = getHttpResponsePOST($this->alipay_gateway_new, $this->alipay_config['cacert'],$para,trim(strtolower($this->alipay_config['input_charset'])));

		return $sResult;
	}
	
	/**
     * 用於防釣魚，呼叫介面query_timestamp來獲取時間戳的處理函式
	 * 注意：該功能PHP5環境及以上支援，因此必須伺服器、本地電腦中裝有支援DOMDocument、SSL的PHP配置環境。建議本地除錯時使用PHP開發軟體
     * return 時間戳字串
	 */
	function query_timestamp() {
		$url = $this->alipay_gateway_new."service=query_timestamp&partner=".trim(strtolower($this->alipay_config['partner']))."&_input_charset=".trim(strtolower($this->alipay_config['input_charset']));
		$encrypt_key = "";		

		$doc = new DOMDocument();
		$doc->load($url);
		$itemEncrypt_key = $doc->getElementsByTagName( "encrypt_key" );
		$encrypt_key = $itemEncrypt_key->item(0)->nodeValue;
		
		return $encrypt_key;
	}
}
?>