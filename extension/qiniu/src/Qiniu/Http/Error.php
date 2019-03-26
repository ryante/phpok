<?php
namespace Qiniu\Http;

/**
 * 七牛業務請求邏輯錯誤封裝類，主要用來解析API請求返回如下的內容：
 * <pre>
 *     {"error" : "detailed error message"}
 * </pre>
 */
final class Error
{
    private $url;
    private $response;

    public function __construct($url, $response)
    {
        $this->url = $url;
        $this->response = $response;
    }

    public function code()
    {
        return $this->response->statusCode;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function message()
    {
        return $this->response->error;
    }
}
