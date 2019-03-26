<?php
namespace Qiniu\Processing;

use Qiniu;

/**
 * 主要涉及圖片連結拼接
 *
 * @link http://developer.qiniu.com/code/v6/api/kodo-api/image/imageview2.html
 */
final class ImageUrlBuilder
{
    /**
     * mode合法範圍值
     *
     * @var array
     */
    protected $modeArr = array(0, 1, 2, 3, 4, 5);

    /**
     * format合法值
     *
     * @var array
     */
    protected $formatArr = array('psd', 'jpeg', 'png', 'gif', 'webp', 'tiff', 'bmp');

    /**
     * 水印圖片位置合法值
     *
     * @var array
     */
    protected $gravityArr = array('NorthWest', 'North', 'NorthEast',
        'West', 'Center', 'East', 'SouthWest', 'South', 'SouthEast');

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
    public function thumbnail(
        $url,
        $mode,
        $width,
        $height,
        $format = null,
        $interlace = null,
        $quality = null,
        $ignoreError = 1
    ) {

        // url合法效驗
        if (!$this->isUrl($url)) {
            return $url;
        }

        // 引數合法性效驗
        if (!in_array(intval($mode), $this->modeArr, true)) {
            return $url;
        }

        if (!$width || !$height) {
            return $url;
        }

        $thumbStr = 'imageView2/' . $mode . '/w/' . $width . '/h/' . $height . '/';

        // 拼接輸出格式
        if (!is_null($format)
            && in_array($format, $this->formatArr)
        ) {
            $thumbStr .= 'format/' . $format . '/';
        }

        // 拼接漸進顯示
        if (!is_null($interlace)
            && in_array(intval($interlace), array(0, 1), true)
        ) {
            $thumbStr .= 'interlace/' . $interlace . '/';
        }

        // 拼接圖片質量
        if (!is_null($quality)
            && intval($quality) >= 0
            && intval($quality) <= 100
        ) {
            $thumbStr .= 'q/' . $quality . '/';
        }

        $thumbStr .= 'ignore-error/' . $ignoreError . '/';

        // 如果有query_string用|線分割實現多引數
        return $url . ($this->hasQuery($url) ? '|' : '?') . $thumbStr;
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
    public function waterImg(
        $url,
        $image,
        $dissolve = 100,
        $gravity = 'SouthEast',
        $dx = null,
        $dy = null,
        $watermarkScale = null
    ) {
        // url合法效驗
        if (!$this->isUrl($url)) {
            return $url;
        }

        $waterStr = 'watermark/1/image/' . \Qiniu\base64_urlSafeEncode($image) . '/';

        // 拼接水印透明度
        if (is_numeric($dissolve)
            && $dissolve <= 100
        ) {
            $waterStr .= 'dissolve/' . $dissolve . '/';
        }

        // 拼接水印位置
        if (in_array($gravity, $this->gravityArr, true)) {
            $waterStr .= 'gravity/' . $gravity . '/';
        }

        // 拼接橫軸邊距
        if (!is_null($dx)
            && is_numeric($dx)
        ) {
            $waterStr .= 'dx/' . $dx . '/';
        }

        // 拼接縱軸邊距
        if (!is_null($dy)
            && is_numeric($dy)
        ) {
            $waterStr .= 'dy/' . $dy . '/';
        }

        // 拼接自適應原圖的短邊比例
        if (!is_null($watermarkScale)
            && is_numeric($watermarkScale)
            && $watermarkScale > 0
            && $watermarkScale < 1
        ) {
            $waterStr .= 'ws/' . $watermarkScale . '/';
        }

        // 如果有query_string用|線分割實現多引數
        return $url . ($this->hasQuery($url) ? '|' : '?') . $waterStr;
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
    public function waterText(
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
        // url合法效驗
        if (!$this->isUrl($url)) {
            return $url;
        }

        $waterStr = 'watermark/2/text/'
            . \Qiniu\base64_urlSafeEncode($text) . '/font/'
            . \Qiniu\base64_urlSafeEncode($font) . '/';

        // 拼接文字大小
        if (is_int($fontSize)) {
            $waterStr .= 'fontsize/' . $fontSize . '/';
        }

        // 拼接文字顏色
        if (!is_null($fontColor)
            && $fontColor
        ) {
            $waterStr .= 'fill/' . \Qiniu\base64_urlSafeEncode($fontColor) . '/';
        }

        // 拼接水印透明度
        if (is_numeric($dissolve)
            && $dissolve <= 100
        ) {
            $waterStr .= 'dissolve/' . $dissolve . '/';
        }

        // 拼接水印位置
        if (in_array($gravity, $this->gravityArr, true)) {
            $waterStr .= 'gravity/' . $gravity . '/';
        }

        // 拼接橫軸邊距
        if (!is_null($dx)
            && is_numeric($dx)
        ) {
            $waterStr .= 'dx/' . $dx . '/';
        }

        // 拼接縱軸邊距
        if (!is_null($dy)
            && is_numeric($dy)
        ) {
            $waterStr .= 'dy/' . $dy . '/';
        }

        // 如果有query_string用|線分割實現多引數
        return $url . ($this->hasQuery($url) ? '|' : '?') . $waterStr;
    }

    /**
     * 效驗url合法性
     *
     * @param  string $url url連結
     * @return string
     * @author Sherlock Ren <sherlock_ren@icloud.com>
     */
    protected function isUrl($url)
    {
        $urlArr = parse_url($url);

        return $urlArr['scheme']
        && in_array($urlArr['scheme'], array('http', 'https'))
        && $urlArr['host']
        && $urlArr['path'];
    }

    /**
     * 檢測是否有query
     *
     * @param  string $url url連結
     * @return string
     * @author Sherlock Ren <sherlock_ren@icloud.com>
     */
    protected function hasQuery($url)
    {
        $urlArr = parse_url($url);

        return !empty($urlArr['query']);
    }
}
