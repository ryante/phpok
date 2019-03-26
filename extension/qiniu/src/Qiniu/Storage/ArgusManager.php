<?php
namespace Qiniu\Storage;

use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Zone;
use Qiniu\Http\Client;
use Qiniu\Http\Error;

/**
 * 主要涉及了鑑黃介面的實現，具體的介面規格可以參考
 *
 * @link https://developer.qiniu.com/dora/manual/3674/kodo-product-introduction
 */
final class ArgusManager
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
     * 視訊鑑黃
     *
     * @param $body     body資訊
     * @param $vid      videoID
     *
     * @return mixed      成功返回NULL，失敗返回物件Qiniu\Http\Error
     * @link  https://developer.qiniu.com/dora/manual/4258/video-pulp
     */
    public function pulpVideo($body, $vid)
    {
        $path = '/v1/video/' . $vid;
        
        return $this->arPost($path, $body);
    }

    private function getArHost()
    {
        $scheme = "http://";
        if ($this->config->useHTTPS == true) {
            $scheme = "https://";
        }
        return $scheme . Config::ARGUS_HOST;
    }

    private function arPost($path, $body = null)
    {
        $url = $this->getArHost() . $path;
        return $this->post($url, $body);
    }

    private function post($url, $body)
    {
        $headers = $this->auth->authorizationV2($url, 'POST', $body, 'application/json');
        $headers['Content-Type']='application/json';
        $ret = Client::post($url, $body, $headers);
        if (!$ret->ok()) {
            print($ret->statusCode);
            return array(null, new Error($url, $ret));
        }
        $r = ($ret->body === null) ? array() : $ret->json();
        return array($r, null);
    }
}
