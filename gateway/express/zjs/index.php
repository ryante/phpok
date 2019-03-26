<?php
/*****************************************************************************************
	檔案： express/zjs/index.php
	備註： 獲取資料
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2015年09月07日 15時45分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
if(!$express || !$rs || !$rs['code']){
	return false;
}
$ext = ($express['ext'] && is_string($express['ext'])) ? unserialize($express['ext']) : array();
$rdm1 ="0000" ;
$rdm2 ="0000" ;
$clientFlag = $ext['logisticProviderID'];
$xml ="<BatchQueryRequest><logisticProviderID>".trim($ext['logisticProviderID'])."</logisticProviderID>";
$xml.= "<orders><order><mailNo>".trim($rs['code'])."</mailNo></order></orders></BatchQueryRequest>";
$strSeed = $ext['keyseed'];//客戶金鑰
$strConst = $ext['fixed_string'];//常量值
$str = $rdm1.$clientFlag.$xml.$strSeed.$strConst.$rdm2;
$strVerifyData=$rdm1.substr(md5($str),7,21).$rdm2;//生成金鑰
$postdata='clientFlag='.$clientFlag.'&xml='.($xml).'&verifyData='.$strVerifyData;

$ch = curl_init(); //建立一個curl
// 2. 設定選項，包括URL
curl_setopt($ch, CURLOPT_URL, "http://edi.zjs.com.cn/svst/tracking.asmx/Get");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_REFERER, "http://www.zjs.com.cn/"); //構造來路
// 3. 執行並獲取HTML檔案內容
$output = curl_exec($ch);
if(!$output){
	return array('content'=>P_Lang('遠端獲取資料失敗'));
}
$curl_info = curl_getinfo($ch);
if($curl_info['http_code'] != '200'){
	return array('content'=>P_Lang('遠端獲取資料失敗'));
}
curl_close($ch);
$output=str_replace(array('&lt;', '&gt;'), array('<','>'),$output);
$xmlinfo = $this->lib('xml')->read(trim($output),false);
if(!$xmlinfo){
	$output=substr($output,79);
	$output=str_replace('</string>','',$output);
	return array('content'=>$output);
}
if($xmlinfo['BatchQueryResponse']){
	$xmlinfo = $xmlinfo['BatchQueryResponse'];
}
$logisticProviderID=$xmlinfo['logisticProviderID'];//客戶標識
$orders=$xmlinfo['orders'];
$order=$orders['order'];
$steps=$order['steps'];
$step=$steps['step'];
$mailNo=$order['mailNo'];//運單號
$orderStatus=$order['orderStatus'];//當前訂單狀態，訂單狀態值：GOT 物流公司已經取件、SIGNED 訂單已經簽收、FAILED 訂單簽收失敗
$statusTime=$order['statusTime'];//當前狀態時間
$tmplist = array();
if($step && is_array($step)){
	if($step['acceptTime']){
		$tmplist[0] = array('time'=>$step['acceptTime'],'content'=>$step['acceptAddress']);
	}
	foreach($step as $key=>$val){
		if($key != 'acceptTime' && $key != 'acceptAddress' && is_array($val)){
			$tmp = array('time'=>$val['acceptTime'],'content'=>$val['acceptAddress']);
			$tmplist[] = $tmp;
		}
	}
}
$is_end = false;
if($orderStatus=="SIGNED"){
	$is_end = true;
	$last = array('title'=>$statusTime,'content'=>'訂單已經簽收，簽收人是'.$order['signinPer']);
	$tmplist[] = $last;
}elseif($orderStatus == 'GOT'){
	$tmp = array('time'=>$statusTime,'content'=>'物流公司已取件');
	$tmplist[] = $tmp;
}elseif($orderStatus == 'FAILED'){
	$tmp = array('time'=>$statusTime,'content'=>'訂單簽收失敗，原因說明：'.$order['error']);
	$tmplist[] = $tmp;
}else{
	$tmp = array('time'=>date("Y-m-d H:i:s",$this->time),'content'=>'查無結果！請檢查運單號是否正確，若正確，原因可能為：物流公司還沒有錄入資訊，請4小時後再查詢');
	$tmplist[] = $tmp;
}
return array('is_end'=>$is_end,'content'=>$tmplist,'status'=>true);
