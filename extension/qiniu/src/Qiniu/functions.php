<?php

namespace Qiniu;

use Qiniu\Config;

if (!defined('QINIU_FUNCTIONS_VERSION')) {
    define('QINIU_FUNCTIONS_VERSION', Config::SDK_VER);

    /**
     * 計算檔案的crc32檢驗碼:
     *
     * @param $file string  待計算校驗碼的檔案路徑
     *
     * @return string 檔案內容的crc32校驗碼
     */
    function crc32_file($file)
    {
        $hash = hash_file('crc32b', $file);
        $array = unpack('N', pack('H*', $hash));
        return sprintf('%u', $array[1]);
    }

    /**
     * 計算輸入流的crc32檢驗碼
     *
     * @param $data 待計算校驗碼的字串
     *
     * @return string 輸入字串的crc32校驗碼
     */
    function crc32_data($data)
    {
        $hash = hash('crc32b', $data);
        $array = unpack('N', pack('H*', $hash));
        return sprintf('%u', $array[1]);
    }

    /**
     * 對提供的資料進行urlsafe的base64編碼。
     *
     * @param string $data 待編碼的資料，一般為字串
     *
     * @return string 編碼後的字串
     * @link http://developer.qiniu.com/docs/v6/api/overview/appendix.html#urlsafe-base64
     */
    function base64_urlSafeEncode($data)
    {
        $find = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, base64_encode($data));
    }

    /**
     * 對提供的urlsafe的base64編碼的資料進行解碼
     *
     * @param string $str 待解碼的資料，一般為字串
     *
     * @return string 解碼後的字串
     */
    function base64_urlSafeDecode($str)
    {
        $find = array('-', '_');
        $replace = array('+', '/');
        return base64_decode(str_replace($find, $replace, $str));
    }

    /**
     * Wrapper for JSON decode that implements error detection with helpful
     * error messages.
     *
     * @param string $json JSON data to parse
     * @param bool $assoc When true, returned objects will be converted
     *                        into associative arrays.
     * @param int $depth User specified recursion depth.
     *
     * @return mixed
     * @throws \InvalidArgumentException if the JSON cannot be parsed.
     * @link http://www.php.net/manual/en/function.json-decode.php
     */
    function json_decode($json, $assoc = false, $depth = 512)
    {
        static $jsonErrors = array(
            JSON_ERROR_DEPTH => 'JSON_ERROR_DEPTH - Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'JSON_ERROR_STATE_MISMATCH - Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR => 'JSON_ERROR_CTRL_CHAR - Unexpected control character found',
            JSON_ERROR_SYNTAX => 'JSON_ERROR_SYNTAX - Syntax error, malformed JSON',
            JSON_ERROR_UTF8 => 'JSON_ERROR_UTF8 - Malformed UTF-8 characters, possibly incorrectly encoded'
        );

        if (empty($json)) {
            return null;
        }
        $data = \json_decode($json, $assoc, $depth);

        if (JSON_ERROR_NONE !== json_last_error()) {
            $last = json_last_error();
            throw new \InvalidArgumentException(
                'Unable to parse JSON data: '
                . (isset($jsonErrors[$last])
                    ? $jsonErrors[$last]
                    : 'Unknown error')
            );
        }

        return $data;
    }

    /**
     * 計算七牛API中的資料格式
     *
     * @param $bucket 待操作的空間名
     * @param $key 待操作的檔名
     *
     * @return string  符合七牛API規格的資料格式
     * @link http://developer.qiniu.com/docs/v6/api/reference/data-formats.html
     */
    function entry($bucket, $key)
    {
        $en = $bucket;
        if (!empty($key)) {
            $en = $bucket . ':' . $key;
        }
        return base64_urlSafeEncode($en);
    }

    /**
     * array 輔助方法，無值時不set
     *
     * @param $array 待操作array
     * @param $key key
     * @param $value value 為null時 不設定
     *
     * @return array 原來的array，便於連續操作
     */
    function setWithoutEmpty(&$array, $key, $value)
    {
        if (!empty($value)) {
            $array[$key] = $value;
        }
        return $array;
    }

    /**
     * 縮圖連結拼接
     *
     * @param  string $url 圖片連結
     * @param  int $mode 縮略模式
     * @param  int $width 寬度
     * @param  int $height 長度
     * @param  string $format 輸出型別
     * @param  int $quality 圖片質量
     * @param  int $interlace 是否支援漸進顯示
     * @param  int $ignoreError 忽略結果
     * @return string
     * @link http://developer.qiniu.com/code/v6/api/kodo-api/image/imageview2.html
     * @author Sherlock Ren <sherlock_ren@icloud.com>
     */
    function thumbnail(
        $url,
        $mode,
        $width,
        $height,
        $format = null,
        $quality = null,
        $interlace = null,
        $ignoreError = 1
    ) {

        static $imageUrlBuilder = null;
        if (is_null($imageUrlBuilder)) {
            $imageUrlBuilder = new \Qiniu\Processing\ImageUrlBuilder;
        }

        return call_user_func_array(array($imageUrlBuilder, 'thumbnail'), func_get_args());
    }

    /**
     * 圖片水印
     *
     * @param  string $url 圖片連結
     * @param  string $image 水印圖片連結
     * @param  numeric $dissolve 透明度
     * @param  string $gravity 水印位置
     * @param  numeric $dx 橫軸邊距
     * @param  numeric $dy 縱軸邊距
     * @param  numeric $watermarkScale 自適應原圖的短邊比例
     * @link   http://developer.qiniu.com/code/v6/api/kodo-api/image/watermark.html
     * @return string
     * @author Sherlock Ren <sherlock_ren@icloud.com>
     */
    function waterImg(
        $url,
        $image,
        $dissolve = 100,
        $gravity = 'SouthEast',
        $dx = null,
        $dy = null,
        $watermarkScale = null
    ) {

        static $imageUrlBuilder = null;
        if (is_null($imageUrlBuilder)) {
            $imageUrlBuilder = new \Qiniu\Processing\ImageUrlBuilder;
        }

        return call_user_func_array(array($imageUrlBuilder, 'waterImg'), func_get_args());
    }

    /**
     * 文字水印
     *
     * @param  string $url 圖片連結
     * @param  string $text 文字
     * @param  string $font 文字字型
     * @param  string $fontSize 文字字號
     * @param  string $fontColor 文字顏色
     * @param  numeric $dissolve 透明度
     * @param  string $gravity 水印位置
     * @param  numeric $dx 橫軸邊距
     * @param  numeric $dy 縱軸邊距
     * @link   http://developer.qiniu.com/code/v6/api/kodo-api/image/watermark.html#text-watermark
     * @return string
     * @author Sherlock Ren <sherlock_ren@icloud.com>
     */
    function waterText(
        $url,
        $text,
        $font = '黑體',
        $fontSize = 0,
        $fontColor = null,
        $dissolve = 100,
        $gravity = 'SouthEast',
        $dx = null,
        $dy = null
    ) {

        static $imageUrlBuilder = null;
        if (is_null($imageUrlBuilder)) {
            $imageUrlBuilder = new \Qiniu\Processing\ImageUrlBuilder;
        }

        return call_user_func_array(array($imageUrlBuilder, 'waterText'), func_get_args());
    }

    /**
     *  從uptoken解析accessKey和bucket
     *
     * @param $upToken
     * @return array(ak,bucket,err=null)
     */
    function explodeUpToken($upToken)
    {
        $items = explode(':', $upToken);
        if (count($items) != 3) {
            return array(null, null, "invalid uptoken");
        }
        $accessKey = $items[0];
        $putPolicy = json_decode(base64_urlSafeDecode($items[2]));
        $scope = $putPolicy->scope;
        $scopeItems = explode(':', $scope);
        $bucket = $scopeItems[0];
        return array($accessKey, $bucket, null);
    }
}
