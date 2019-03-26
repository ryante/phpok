<?php
namespace Qiniu\Processing;

use Qiniu\Config;
use Qiniu\Http\Client;
use Qiniu\Http\Error;
use Qiniu\Processing\Operation;

/**
 * 持久化處理類,該類用於主動觸發非同步持久化操作.
 *
 * @link http://developer.qiniu.com/docs/v6/api/reference/fop/pfop/pfop.html
 */
final class PersistentFop
{
    /**
     * @var 賬號管理金鑰對，Auth物件
     */
    private $auth;

    /*
     * @var 配置物件，Config 物件
     * */
    private $config;


    public function __construct($auth, $config = null)
    {
        $this->auth = $auth;
        if ($config == null) {
            $this->config = new Config();
        } else {
            $this->config = $config;
        }
    }

    /**
     * 對資原始檔進行非同步持久化處理
     * @param $bucket     資源所在空間
     * @param $key        待處理的原始檔
     * @param $fops       string|array  待處理的pfop操作，多個pfop操作以array的形式傳入。
     *                    eg. avthumb/mp3/ab/192k, vframe/jpg/offset/7/w/480/h/360
     * @param $pipeline   資源處理佇列
     * @param $notify_url 處理結果通知地址
     * @param $force      是否強制執行一次新的指令
     *
     *
     * @return array 返回持久化處理的persistentId, 和返回的錯誤。
     *
     * @link http://developer.qiniu.com/docs/v6/api/reference/fop/
     */
    public function execute($bucket, $key, $fops, $pipeline = null, $notify_url = null, $force = false)
    {
        if (is_array($fops)) {
            $fops = implode(';', $fops);
        }
        $params = array('bucket' => $bucket, 'key' => $key, 'fops' => $fops);
        \Qiniu\setWithoutEmpty($params, 'pipeline', $pipeline);
        \Qiniu\setWithoutEmpty($params, 'notifyURL', $notify_url);
        if ($force) {
            $params['force'] = 1;
        }
        $data = http_build_query($params);
        $scheme = "http://";
        if ($this->config->useHTTPS === true) {
            $scheme = "https://";
        }
        $url = $scheme . Config::API_HOST . '/pfop/';
        $headers = $this->auth->authorization($url, $data, 'application/x-www-form-urlencoded');
        $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        $response = Client::post($url, $data, $headers);
        if (!$response->ok()) {
            return array(null, new Error($url, $response));
        }
        $r = $response->json();
        $id = $r['persistentId'];
        return array($id, null);
    }

    public function status($id)
    {
        $scheme = "http://";

        if ($this->config->useHTTPS === true) {
            $scheme = "https://";
        }
        $url = $scheme . Config::API_HOST . "/status/get/prefop?id=$id";
        $response = Client::get($url);
        if (!$response->ok()) {
            return array(null, new Error($url, $response));
        }
        return array($response->json(), null);
    }
}
