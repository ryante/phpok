<?php

namespace Qiniu\Cdn;

use Qiniu\Auth;
use Qiniu\Http\Error;
use Qiniu\Http\Client;

final class CdnManager
{

    private $auth;
    private $server;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
        $this->server = 'http://fusion.qiniuapi.com';
    }

    /**
     * @param array $urls 待重新整理的檔案連結陣列
     * @return array
     */
    public function refreshUrls(array $urls)
    {
        return $this->refreshUrlsAndDirs($urls, array());
    }

    /**
     * @param array $dirs 待重新整理的檔案連結陣列
     * @return array
     * 目前客戶預設沒有目錄重新整理許可權，重新整理會有400038報錯，參考：https://developer.qiniu.com/fusion/api/1229/cache-refresh
     * 需要重新整理目錄請工單聯絡技術支援 https://support.qiniu.com/tickets/category
     */
    public function refreshDirs(array $dirs)
    {
        return $this->refreshUrlsAndDirs(array(), $dirs);
    }

    /**
     * @param array $urls 待重新整理的檔案連結陣列
     * @param array $dirs 待重新整理的目錄連結陣列
     *
     * @return array 重新整理的請求回覆和錯誤，參考 examples/cdn_manager.php 程式碼
     * @link http://developer.qiniu.com/article/fusion/api/refresh.html
     *
     * 目前客戶預設沒有目錄重新整理許可權，重新整理會有400038報錯，參考：https://developer.qiniu.com/fusion/api/1229/cache-refresh
     * 需要重新整理目錄請工單聯絡技術支援 https://support.qiniu.com/tickets/category
     */
    public function refreshUrlsAndDirs(array $urls, array  $dirs)
    {
        $req = array();
        if (!empty($urls)) {
            $req['urls'] = $urls;
        }
        if (!empty($dirs)) {
            $req['dirs'] = $dirs;
        }

        $url = $this->server . '/v2/tune/refresh';
        $body = json_encode($req);
        return $this->post($url, $body);
    }

    /**
     * @param array $urls 待預取的檔案連結陣列
     *
     * @return array 預取的請求回覆和錯誤，參考 examples/cdn_manager.php 程式碼
     *
     * @link http://developer.qiniu.com/article/fusion/api/refresh.html
     */
    public function prefetchUrls(array $urls)
    {
        $req = array(
            'urls' => $urls,
        );

        $url = $this->server . '/v2/tune/prefetch';
        $body = json_encode($req);
        return $this->post($url, $body);
    }

    /**
     * @param array $domains 待獲取頻寬資料的域名陣列
     * @param string $startDate 開始的日期，格式類似 2017-01-01
     * @param string $endDate 結束的日期，格式類似 2017-01-01
     * @param string $granularity 獲取資料的時間間隔，可以是 5min, hour 或者 day
     *
     * @return array 頻寬資料和錯誤資訊，參考 examples/cdn_manager.php 程式碼
     *
     * @link http://developer.qiniu.com/article/fusion/api/traffic-bandwidth.html
     */
    public function getBandwidthData(array $domains, $startDate, $endDate, $granularity)
    {
        $req = array();
        $req['domains'] = implode(';', $domains);
        $req['startDate'] = $startDate;
        $req['endDate'] = $endDate;
        $req['granularity'] = $granularity;

        $url = $this->server . '/v2/tune/bandwidth';
        $body = json_encode($req);
        return $this->post($url, $body);
    }

    /**
     * @param array $domains 待獲取流量資料的域名陣列
     * @param string $startDate 開始的日期，格式類似 2017-01-01
     * @param string $endDate 結束的日期，格式類似 2017-01-01
     * @param string $granularity 獲取資料的時間間隔，可以是 5min, hour 或者 day
     *
     * @return array 流量資料和錯誤資訊，參考 examples/cdn_manager.php 程式碼
     *
     * @link http://developer.qiniu.com/article/fusion/api/traffic-bandwidth.html
     */
    public function getFluxData(array $domains, $startDate, $endDate, $granularity)
    {
        $req = array();
        $req['domains'] = implode(';', $domains);
        $req['startDate'] = $startDate;
        $req['endDate'] = $endDate;
        $req['granularity'] = $granularity;

        $url = $this->server . '/v2/tune/flux';
        $body = json_encode($req);
        return $this->post($url, $body);
    }

    /**
     * @param array $domains 待獲取日誌下載連結的域名陣列
     * @param string $logDate 獲取指定日期的日誌下載連結，格式類似 2017-01-01
     *
     * @return array 日誌下載連結資料和錯誤資訊，參考 examples/cdn_manager.php 程式碼
     *
     * @link http://developer.qiniu.com/article/fusion/api/log.html
     */
    public function getCdnLogList(array $domains, $logDate)
    {
        $req = array();
        $req['domains'] = implode(';', $domains);
        $req['day'] = $logDate;

        $url = $this->server . '/v2/tune/log/list';
        $body = json_encode($req);
        return $this->post($url, $body);
    }

    private function post($url, $body)
    {
        $headers = $this->auth->authorization($url, $body, 'application/json');
        $headers['Content-Type'] = 'application/json';
        $ret = Client::post($url, $body, $headers);
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        $r = ($ret->body === null) ? array() : $ret->json();
        return array($r, null);
    }

    /**
     * 構建時間戳防盜鏈鑑權的訪問外鏈
     *
     * @param string $rawUrl 需要簽名的資源url
     * @param string $encryptKey 時間戳防盜鏈金鑰
     * @param string $durationInSeconds 連結的有效期（以秒為單位）
     *
     * @return string 帶鑑權資訊的資源外鏈，參考 examples/cdn_timestamp_antileech.php 程式碼
     */
    public static function createTimestampAntiLeechUrl($rawUrl, $encryptKey, $durationInSeconds)
    {

        $parsedUrl = parse_url($rawUrl);

        $deadline = time() + $durationInSeconds;
        $expireHex = dechex($deadline);
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $path = implode('/', array_map('rawurlencode', explode('/', $path)));

        $strToSign = $encryptKey . $path . $expireHex;
        $signStr = md5($strToSign);

        if (isset($parsedUrl['query'])) {
            $signedUrl = $rawUrl . '&sign=' . $signStr . '&t=' . $expireHex;
        } else {
            $signedUrl = $rawUrl . '?sign=' . $signStr . '&t=' . $expireHex;
        }

        return $signedUrl;
    }
}
