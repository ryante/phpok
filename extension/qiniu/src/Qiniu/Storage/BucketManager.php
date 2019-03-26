<?php
namespace Qiniu\Storage;

use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Zone;
use Qiniu\Http\Client;
use Qiniu\Http\Error;

/**
 * 主要涉及了空間資源管理及批量操作介面的實現，具體的介面規格可以參考
 *
 * @link https://developer.qiniu.com/kodo/api/1274/rs
 */
final class BucketManager
{
    private $auth;
    private $config;

    public function __construct(Auth $auth, Config $config = null)
    {
        $this->auth = $auth;
        if ($config == null) {
            $this->config = new Config();
        } else {
            $this->config = $config;
        }
    }

    /**
     * 獲取指定賬號下所有的空間名。
     *
     * @return string[] 包含所有空間名
     */
    public function buckets($shared = true)
    {
        $includeShared = "false";
        if ($shared === true) {
            $includeShared = "true";
        }
        return $this->rsGet('/buckets?shared=' . $includeShared);
    }

    /**
     * 獲取指定空間繫結的所有的域名
     *
     * @return string[] 包含所有空間域名
     */
    public function domains($bucket)
    {
        return $this->apiGet('/v6/domain/list?tbl=' . $bucket);
    }

    /**
     * 獲取空間繫結的域名列表
     * @return string[] 包含空間繫結的所有域名
     */

    /**
     * 列取空間的檔案列表
     *
     * @param $bucket     空間名
     * @param $prefix     列舉字首
     * @param $marker     列舉識別符號
     * @param $limit      單次列舉個數限制
     * @param $delimiter  指定目錄分隔符
     *
     * @return array    包含檔案資訊的陣列，類似：[
     *                                              {
     *                                                 "hash" => "<Hash string>",
     *                                                  "key" => "<Key string>",
     *                                                  "fsize" => "<file size>",
     *                                                  "putTime" => "<file modify time>"
     *                                              },
     *                                              ...
     *                                            ]
     * @link  http://developer.qiniu.com/docs/v6/api/reference/rs/list.html
     */
    public function listFiles($bucket, $prefix = null, $marker = null, $limit = 1000, $delimiter = null)
    {
        $query = array('bucket' => $bucket);
        \Qiniu\setWithoutEmpty($query, 'prefix', $prefix);
        \Qiniu\setWithoutEmpty($query, 'marker', $marker);
        \Qiniu\setWithoutEmpty($query, 'limit', $limit);
        \Qiniu\setWithoutEmpty($query, 'delimiter', $delimiter);
        $url = $this->getRsfHost() . '/list?' . http_build_query($query);
        return $this->get($url);
    }

    /**
     * 獲取資源的元資訊，但不返回檔案內容
     *
     * @param $bucket     待獲取資訊資源所在的空間
     * @param $key        待獲取資源的檔名
     *
     * @return array    包含檔案資訊的陣列，類似：
     *                                              [
     *                                                  "hash" => "<Hash string>",
     *                                                  "key" => "<Key string>",
     *                                                  "fsize" => <file size>,
     *                                                  "putTime" => "<file modify time>"
     *                                                  "fileType" => <file type>
     *                                              ]
     *
     * @link  http://developer.qiniu.com/docs/v6/api/reference/rs/stat.html
     */
    public function stat($bucket, $key)
    {
        $path = '/stat/' . \Qiniu\entry($bucket, $key);
        return $this->rsGet($path);
    }

    /**
     * 刪除指定資源
     *
     * @param $bucket     待刪除資源所在的空間
     * @param $key        待刪除資源的檔名
     *
     * @return mixed      成功返回NULL，失敗返回物件Qiniu\Http\Error
     * @link  http://developer.qiniu.com/docs/v6/api/reference/rs/delete.html
     */
    public function delete($bucket, $key)
    {
        $path = '/delete/' . \Qiniu\entry($bucket, $key);
        list(, $error) = $this->rsPost($path);
        return $error;
    }


    /**
     * 給資源進行重新命名，本質為move操作。
     *
     * @param $bucket     待操作資源所在空間
     * @param $oldname    待操作資原始檔名
     * @param $newname    目標資原始檔名
     *
     * @return mixed      成功返回NULL，失敗返回物件Qiniu\Http\Error
     */
    public function rename($bucket, $oldname, $newname)
    {
        return $this->move($bucket, $oldname, $bucket, $newname);
    }

    /**
     * 給資源進行重新命名，本質為move操作。
     *
     * @param $from_bucket     待操作資源所在空間
     * @param $from_key        待操作資原始檔名
     * @param $to_bucket       目標資源空間名
     * @param $to_key          目標資原始檔名
     *
     * @return mixed      成功返回NULL，失敗返回物件Qiniu\Http\Error
     * @link  http://developer.qiniu.com/docs/v6/api/reference/rs/copy.html
     */
    public function copy($from_bucket, $from_key, $to_bucket, $to_key, $force = false)
    {
        $from = \Qiniu\entry($from_bucket, $from_key);
        $to = \Qiniu\entry($to_bucket, $to_key);
        $path = '/copy/' . $from . '/' . $to;
        if ($force === true) {
            $path .= '/force/true';
        }
        list(, $error) = $this->rsPost($path);
        return $error;
    }

    /**
     * 將資源從一個空間到另一個空間
     *
     * @param $from_bucket     待操作資源所在空間
     * @param $from_key        待操作資原始檔名
     * @param $to_bucket       目標資源空間名
     * @param $to_key          目標資原始檔名
     *
     * @return mixed      成功返回NULL，失敗返回物件Qiniu\Http\Error
     * @link  http://developer.qiniu.com/docs/v6/api/reference/rs/move.html
     */
    public function move($from_bucket, $from_key, $to_bucket, $to_key, $force = false)
    {
        $from = \Qiniu\entry($from_bucket, $from_key);
        $to = \Qiniu\entry($to_bucket, $to_key);
        $path = '/move/' . $from . '/' . $to;
        if ($force) {
            $path .= '/force/true';
        }
        list(, $error) = $this->rsPost($path);
        return $error;
    }

    /**
     * 主動修改指定資源的檔案型別
     *
     * @param $bucket     待操作資源所在空間
     * @param $key        待操作資原始檔名
     * @param $mime       待操作檔案目標mimeType
     *
     * @return mixed      成功返回NULL，失敗返回物件Qiniu\Http\Error
     * @link  http://developer.qiniu.com/docs/v6/api/reference/rs/chgm.html
     */
    public function changeMime($bucket, $key, $mime)
    {
        $resource = \Qiniu\entry($bucket, $key);
        $encode_mime = \Qiniu\base64_urlSafeEncode($mime);
        $path = '/chgm/' . $resource . '/mime/' . $encode_mime;
        list(, $error) = $this->rsPost($path);
        return $error;
    }


    /**
     * 修改指定資源的儲存型別
     *
     * @param $bucket     待操作資源所在空間
     * @param $key        待操作資原始檔名
     * @param $fileType       待操作檔案目標檔案型別
     *
     * @return mixed      成功返回NULL，失敗返回物件Qiniu\Http\Error
     * @link  https://developer.qiniu.com/kodo/api/3710/modify-the-file-type
     */
    public function changeType($bucket, $key, $fileType)
    {
        $resource = \Qiniu\entry($bucket, $key);
        $path = '/chtype/' . $resource . '/type/' . $fileType;
        list(, $error) = $this->rsPost($path);
        return $error;
    }

    /**
     * 修改檔案的儲存狀態，即禁用狀態和啟用狀態間的的互相轉換
     *
     * @param $bucket     待操作資源所在空間
     * @param $key        待操作資原始檔名
     * @param $status       待操作檔案目標檔案型別
     *
     * @return mixed      成功返回NULL，失敗返回物件Qiniu\Http\Error
     * @link  https://developer.qiniu.com/kodo/api/4173/modify-the-file-status
     */
    public function changeStatus($bucket, $key, $status)
    {
        $resource = \Qiniu\entry($bucket, $key);
        $path = '/chstatus/' . $resource . '/status/' . $status;
        list(, $error) = $this->rsPost($path);
        return $error;
    }

    /**
     * 從指定URL抓取資源，並將該資源儲存到指定空間中
     *
     * @param $url        指定的URL
     * @param $bucket     目標資源空間
     * @param $key        目標資原始檔名
     *
     * @return array    包含已拉取的檔案資訊。
     *                         成功時：  [
     *                                          [
     *                                              "hash" => "<Hash string>",
     *                                              "key" => "<Key string>"
     *                                          ],
     *                                          null
     *                                  ]
     *
     *                         失敗時：  [
     *                                          null,
     *                                         Qiniu/Http/Error
     *                                  ]
     * @link  http://developer.qiniu.com/docs/v6/api/reference/rs/fetch.html
     */
    public function fetch($url, $bucket, $key = null)
    {

        $resource = \Qiniu\base64_urlSafeEncode($url);
        $to = \Qiniu\entry($bucket, $key);
        $path = '/fetch/' . $resource . '/to/' . $to;

        $ak = $this->auth->getAccessKey();
        $ioHost = $this->config->getIovipHost($ak, $bucket);

        $url = $ioHost . $path;
        return $this->post($url, null);
    }

    /**
     * 從映象源站抓取資源到空間中，如果空間中已經存在，則覆蓋該資源
     *
     * @param $bucket     待獲取資源所在的空間
     * @param $key        代獲取資原始檔名
     *
     * @return mixed      成功返回NULL，失敗返回物件Qiniu\Http\Error
     * @link  http://developer.qiniu.com/docs/v6/api/reference/rs/prefetch.html
     */
    public function prefetch($bucket, $key)
    {
        $resource = \Qiniu\entry($bucket, $key);
        $path = '/prefetch/' . $resource;

        $ak = $this->auth->getAccessKey();
        $ioHost = $this->config->getIovipHost($ak, $bucket);

        $url = $ioHost . $path;
        list(, $error) = $this->post($url, null);
        return $error;
    }

    /**
     * 在單次請求中進行多個資源管理操作
     *
     * @param $operations     資源管理運算元組
     *
     * @return array 每個資源的處理情況，結果類似：
     *              [
     *                   { "code" => <HttpCode int>, "data" => <Data> },
     *                   { "code" => <HttpCode int> },
     *                   { "code" => <HttpCode int> },
     *                   { "code" => <HttpCode int> },
     *                   { "code" => <HttpCode int>, "data" => { "error": "<ErrorMessage string>" } },
     *                   ...
     *               ]
     * @link http://developer.qiniu.com/docs/v6/api/reference/rs/batch.html
     */
    public function batch($operations)
    {
        $params = 'op=' . implode('&op=', $operations);
        return $this->rsPost('/batch', $params);
    }

    /**
     * 設定檔案的生命週期
     *
     * @param $bucket 設定檔案生命週期檔案所在的空間
     * @param $key    設定檔案生命週期檔案的檔名
     * @param $days   設定該檔案多少天后刪除，當$days設定為0時表示取消該檔案的生命週期
     *
     * @return Mixed
     * @link https://developer.qiniu.com/kodo/api/update-file-lifecycle
     */
    public function deleteAfterDays($bucket, $key, $days)
    {
        $entry = \Qiniu\entry($bucket, $key);
        $path = "/deleteAfterDays/$entry/$days";
        list(, $error) = $this->rsPost($path);
        return $error;
    }

    private function getRsfHost()
    {
        $scheme = "http://";
        if ($this->config->useHTTPS == true) {
            $scheme = "https://";
        }
        return $scheme . Config::RSF_HOST;
    }

    private function getRsHost()
    {
        $scheme = "http://";
        if ($this->config->useHTTPS == true) {
            $scheme = "https://";
        }
        return $scheme . Config::RS_HOST;
    }

    private function getApiHost()
    {
        $scheme = "http://";
        if ($this->config->useHTTPS == true) {
            $scheme = "https://";
        }
        return $scheme . Config::API_HOST;
    }

    private function rsPost($path, $body = null)
    {
        $url = $this->getRsHost() . $path;
        return $this->post($url, $body);
    }

    private function apiGet($path)
    {
        $url = $this->getApiHost() . $path;
        return $this->get($url);
    }

    private function rsGet($path)
    {
        $url = $this->getRsHost() . $path;
        return $this->get($url);
    }

    private function get($url)
    {
        $headers = $this->auth->authorization($url);
        $ret = Client::get($url, $headers);
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        return array($ret->json(), null);
    }

    private function post($url, $body)
    {
        $headers = $this->auth->authorization($url, $body, 'application/x-www-form-urlencoded');
        $ret = Client::post($url, $body, $headers);
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        $r = ($ret->body === null) ? array() : $ret->json();
        return array($r, null);
    }

    public static function buildBatchCopy($source_bucket, $key_pairs, $target_bucket, $force)
    {
        return self::twoKeyBatch('/copy', $source_bucket, $key_pairs, $target_bucket, $force);
    }


    public static function buildBatchRename($bucket, $key_pairs, $force)
    {
        return self::buildBatchMove($bucket, $key_pairs, $bucket, $force);
    }


    public static function buildBatchMove($source_bucket, $key_pairs, $target_bucket, $force)
    {
        return self::twoKeyBatch('/move', $source_bucket, $key_pairs, $target_bucket, $force);
    }


    public static function buildBatchDelete($bucket, $keys)
    {
        return self::oneKeyBatch('/delete', $bucket, $keys);
    }


    public static function buildBatchStat($bucket, $keys)
    {
        return self::oneKeyBatch('/stat', $bucket, $keys);
    }

    public static function buildBatchDeleteAfterDays($bucket, $key_day_pairs)
    {
        $data = array();
        foreach ($key_day_pairs as $key => $day) {
            array_push($data, '/deleteAfterDays/' . \Qiniu\entry($bucket, $key) . '/' . $day);
        }
        return $data;
    }

    public static function buildBatchChangeMime($bucket, $key_mime_pairs)
    {
        $data = array();
        foreach ($key_mime_pairs as $key => $mime) {
            array_push($data, '/chgm/' . \Qiniu\entry($bucket, $key) . '/mime/' . base64_encode($mime));
        }
        return $data;
    }

    public static function buildBatchChangeType($bucket, $key_type_pairs)
    {
        $data = array();
        foreach ($key_type_pairs as $key => $type) {
            array_push($data, '/chtype/' . \Qiniu\entry($bucket, $key) . '/type/' . $type);
        }
        return $data;
    }

    private static function oneKeyBatch($operation, $bucket, $keys)
    {
        $data = array();
        foreach ($keys as $key) {
            array_push($data, $operation . '/' . \Qiniu\entry($bucket, $key));
        }
        return $data;
    }

    private static function twoKeyBatch($operation, $source_bucket, $key_pairs, $target_bucket, $force)
    {
        if ($target_bucket === null) {
            $target_bucket = $source_bucket;
        }
        $data = array();
        $forceOp = "false";
        if ($force) {
            $forceOp = "true";
        }
        foreach ($key_pairs as $from_key => $to_key) {
            $from = \Qiniu\entry($source_bucket, $from_key);
            $to = \Qiniu\entry($target_bucket, $to_key);
            array_push($data, $operation . '/' . $from . '/' . $to . "/force/" . $forceOp);
        }
        return $data;
    }
}
