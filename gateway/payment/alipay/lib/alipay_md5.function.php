<?php
/* *
 * MD5
 * 詳細：MD5加密
 * 版本：3.3
 * 日期：2012-07-19
 * 說明：
 * 以下程式碼只是為了方便商戶測試而提供的樣例程式碼，商戶可以根據自己網站的需要，按照技術文件編寫,並非一定要使用該程式碼。
 * 該程式碼僅供學習和研究支付寶介面使用，只是提供一個參考。
 */

/**
 * 簽名字串
 * @param $prestr 需要簽名的字串
 * @param $key 私鑰
 * return 簽名結果
 */
function md5Sign($prestr, $key) {
	$prestr = $prestr . $key;
	return md5($prestr);
}

/**
 * 驗證簽名
 * @param $prestr 需要簽名的字串
 * @param $sign 簽名結果
 * @param $key 私鑰
 * return 簽名結果
 */
function md5Verify($prestr, $sign, $key) {
	$prestr = $prestr . $key;
	$mysgin = md5($prestr);

	if($mysgin == $sign) {
		return true;
	}
	else {
		return false;
	}
}
?>