<?php
namespace Qiniu\Storage;

use Qiniu\Config;
use Qiniu\Http\Client;
use Qiniu\Http\Error;

/**
 * 斷點續上傳類, 該類主要實現了斷點續上傳中的分塊上傳,
 * 以及相應地建立塊和建立檔案過程.
 *
 * @link http://developer.qiniu.com/docs/v6/api/reference/up/mkblk.html
 * @link http://developer.qiniu.com/docs/v6/api/reference/up/mkfile.html
 */
final class ResumeUploader
{
    private $upToken;
    private $key;
    private $inputStream;
    private $size;
    private $params;
    private $mime;
    private $contexts;
    private $host;
    private $currentUrl;
    private $config;

    /**
     * 上傳二進位制流到七牛
     *
     * @param $upToken    上傳憑證
     * @param $key        上傳檔名
     * @param $inputStream 上傳二進位制流
     * @param $size       上傳流的大小
     * @param $params     自定義變數
     * @param $mime       上傳資料的mimeType
     *
     * @link http://developer.qiniu.com/docs/v6/api/overview/up/response/vars.html#xvar
     */
    public function __construct(
        $upToken,
        $key,
        $inputStream,
        $size,
        $params,
        $mime,
        $config
    ) {

        $this->upToken = $upToken;
        $this->key = $key;
        $this->inputStream = $inputStream;
        $this->size = $size;
        $this->params = $params;
        $this->mime = $mime;
        $this->contexts = array();
        $this->config = $config;

        list($accessKey, $bucket, $err) = \Qiniu\explodeUpToken($upToken);
        if ($err != null) {
            return array(null, $err);
        }

        $upHost = $config->getUpHost($accessKey, $bucket);
        if ($err != null) {
            throw new \Exception($err->message(), 1);
        }
        $this->host = $upHost;
    }

    /**
     * 上傳操作
     */
    public function upload($fname)
    {
        $uploaded = 0;
        while ($uploaded < $this->size) {
            $blockSize = $this->blockSize($uploaded);
            $data = fread($this->inputStream, $blockSize);
            if ($data === false) {
                throw new \Exception("file read failed", 1);
            }
            $crc = \Qiniu\crc32_data($data);
            $response = $this->makeBlock($data, $blockSize);
            $ret = null;
            if ($response->ok() && $response->json() != null) {
                $ret = $response->json();
            }
            if ($response->statusCode < 0) {
                list($accessKey, $bucket, $err) = \Qiniu\explodeUpToken($this->upToken);
                if ($err != null) {
                    return array(null, $err);
                }

                $upHostBackup = $this->config->getUpBackupHost($accessKey, $bucket);
                $this->host = $upHostBackup;
            }
            if ($response->needRetry() || !isset($ret['crc32']) || $crc != $ret['crc32']) {
                $response = $this->makeBlock($data, $blockSize);
                $ret = $response->json();
            }

            if (!$response->ok() || !isset($ret['crc32']) || $crc != $ret['crc32']) {
                return array(null, new Error($this->currentUrl, $response));
            }
            array_push($this->contexts, $ret['ctx']);
            $uploaded += $blockSize;
        }
        return $this->makeFile($fname);
    }

    /**
     * 建立塊
     */
    private function makeBlock($block, $blockSize)
    {
        $url = $this->host . '/mkblk/' . $blockSize;
        return $this->post($url, $block);
    }

    private function fileUrl($fname)
    {
        $url = $this->host . '/mkfile/' . $this->size;
        $url .= '/mimeType/' . \Qiniu\base64_urlSafeEncode($this->mime);
        if ($this->key != null) {
            $url .= '/key/' . \Qiniu\base64_urlSafeEncode($this->key);
        }
        $url .= '/fname/' . \Qiniu\base64_urlSafeEncode($fname);
        if (!empty($this->params)) {
            foreach ($this->params as $key => $value) {
                $val = \Qiniu\base64_urlSafeEncode($value);
                $url .= "/$key/$val";
            }
        }
        return $url;
    }

    /**
     * 建立檔案
     */
    private function makeFile($fname)
    {
        $url = $this->fileUrl($fname);
        $body = implode(',', $this->contexts);
        $response = $this->post($url, $body);
        if ($response->needRetry()) {
            $response = $this->post($url, $body);
        }
        if (!$response->ok()) {
            return array(null, new Error($this->currentUrl, $response));
        }
        return array($response->json(), null);
    }

    private function post($url, $data)
    {
        $this->currentUrl = $url;
        $headers = array('Authorization' => 'UpToken ' . $this->upToken);
        return Client::post($url, $data, $headers);
    }

    private function blockSize($uploaded)
    {
        if ($this->size < $uploaded + Config::BLOCK_SIZE) {
            return $this->size - $uploaded;
        }
        return Config::BLOCK_SIZE;
    }
}
