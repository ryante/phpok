<?php
/* *
 * 支付寶介面公用函式
 * 詳細：該類是請求、通知返回兩個檔案所呼叫的公用函式核心處理檔案
 * 版本：3.3
 * 日期：2012-07-19
 * 說明：
 * 以下程式碼只是為了方便商戶測試而提供的樣例程式碼，商戶可以根據自己網站的需要，按照技術文件編寫,並非一定要使用該程式碼。
 * 該程式碼僅供學習和研究支付寶介面使用，只是提供一個參考。
 */

/**
 * 把陣列所有元素，按照“引數=引數值”的模式用“&”字元拼接成字串
 * @param $para 需要拼接的陣列
 * return 拼接完成以後的字串
 */
function createLinkstring($para) {
	$arg  = "";
	foreach($para as $key=>$val){
		$arg.=$key."=".$val."&";
	}
	//去掉最後一個&字元
	$arg = substr($arg,0,-1);
	
	//如果存在轉義字元，那麼去掉轉義
	if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
	
	return $arg;
}
/**
 * 把陣列所有元素，按照“引數=引數值”的模式用“&”字元拼接成字串，並對字串做urlencode編碼
 * @param $para 需要拼接的陣列
 * return 拼接完成以後的字串
 */
function createLinkstringUrlencode($para) {
	$arg  = "";
	while (list ($key, $val) = each ($para)) {
		$arg.=$key."=".urlencode($val)."&";
	}
	//去掉最後一個&字元
	$arg = substr($arg,0,-1);
	
	//如果存在轉義字元，那麼去掉轉義
	if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
	
	return $arg;
}
/**
 * 除去陣列中的空值和簽名引數
 * @param $para 簽名引數組
 * return 去掉空值與簽名引數後的新簽名引數組
 */
function paraFilter($para) {
	$para_filter = array();
	foreach($para as $key=>$val){
		if($key == "sign" || $key == "sign_type" || $val == ""){
			continue;
		}
		$para_filter[$key] = $para[$key];
	}
	return $para_filter;
}
/**
 * 對陣列排序
 * @param $para 排序前的陣列
 * return 排序後的陣列
 */
function argSort($para) {
	ksort($para);
	reset($para);
	return $para;
}
/**
 * 寫日誌，方便測試（看網站需求，也可以改成把記錄存入資料庫）
 * 注意：伺服器需要開通fopen配置
 * @param $word 要寫入日誌裡的文字內容 預設值：空值
 */
function logResult($word='') {
	$fp = fopen("log.txt","a");
	flock($fp, LOCK_EX) ;
	fwrite($fp,"執行日期：".strftime("%Y%m%d%H%M%S",time())."\n".$word."\n");
	flock($fp, LOCK_UN);
	fclose($fp);
}

/**
 * 遠端獲取資料，POST模式
 * 注意：
 * 1.使用Crul需要修改伺服器中php.ini檔案的設定，找到php_curl.dll去掉前面的";"就行了
 * 2.資料夾中cacert.pem是SSL證書請保證其路徑有效，目前預設路徑是：getcwd().'\\cacert.pem'
 * @param $url 指定URL完整路徑地址
 * @param $cacert_url 指定當前工作目錄絕對路徑
 * @param $para 請求的資料
 * @param $input_charset 編碼格式。預設值：空值
 * return 遠端輸出的資料
 */
function getHttpResponsePOST($url, $cacert_url, $para, $input_charset = '') {

	if (trim($input_charset) != '') {
		$url = $url."_input_charset=".$input_charset;
	}
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL證書認證
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//嚴格認證
	curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//證書地址
	curl_setopt($curl, CURLOPT_HEADER, 0 ); // 過濾HTTP頭
	curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 顯示輸出結果
	curl_setopt($curl,CURLOPT_POST,true); // post傳輸資料
	curl_setopt($curl,CURLOPT_POSTFIELDS,$para);// post傳輸資料
	$responseText = curl_exec($curl);
	//var_dump( curl_error($curl) );//如果執行curl過程中出現異常，可開啟此開關，以便檢視異常內容
	curl_close($curl);
	
	return $responseText;
}

/**
 * 遠端獲取資料，GET模式
 * 注意：
 * 1.使用Crul需要修改伺服器中php.ini檔案的設定，找到php_curl.dll去掉前面的";"就行了
 * 2.資料夾中cacert.pem是SSL證書請保證其路徑有效，目前預設路徑是：getcwd().'\\cacert.pem'
 * @param $url 指定URL完整路徑地址
 * @param $cacert_url 指定當前工作目錄絕對路徑
 * return 遠端輸出的資料
 */
function getHttpResponseGET($url,$cacert_url) {
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, 0 ); // 過濾HTTP頭
	curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 顯示輸出結果
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL證書認證
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//嚴格認證
	curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//證書地址
	$responseText = curl_exec($curl);
	//var_dump( curl_error($curl) );//如果執行curl過程中出現異常，可開啟此開關，以便檢視異常內容
	curl_close($curl);
	
	return $responseText;
}

/**
 * 實現多種字元編碼方式
 * @param $input 需要編碼的字串
 * @param $_output_charset 輸出的編碼格式
 * @param $_input_charset 輸入的編碼格式
 * return 編碼後的字串
 */
function charsetEncode($input,$_output_charset ,$_input_charset) {
	$output = "";
	if(!isset($_output_charset) )$_output_charset  = $_input_charset;
	if($_input_charset == $_output_charset || $input ==null ) {
		$output = $input;
	} elseif (function_exists("mb_convert_encoding")) {
		$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
	} elseif(function_exists("iconv")) {
		$output = iconv($_input_charset,$_output_charset,$input);
	} else die("sorry, you have no libs support for charset change.");
	return $output;
}
/**
 * 實現多種字元解碼方式
 * @param $input 需要解碼的字串
 * @param $_output_charset 輸出的解碼格式
 * @param $_input_charset 輸入的解碼格式
 * return 解碼後的字串
 */
function charsetDecode($input,$_input_charset ,$_output_charset) {
	$output = "";
	if(!isset($_input_charset) )$_input_charset  = $_input_charset ;
	if($_input_charset == $_output_charset || $input ==null ) {
		$output = $input;
	} elseif (function_exists("mb_convert_encoding")) {
		$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
	} elseif(function_exists("iconv")) {
		$output = iconv($_input_charset,$_output_charset,$input);
	} else die("sorry, you have no libs support for charset changes.");
	return $output;
}
?>