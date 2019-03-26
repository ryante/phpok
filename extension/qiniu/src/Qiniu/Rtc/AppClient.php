<?php
namespace Qiniu\Rtc;

use Qiniu\Http\Client;
use Qiniu\Http\Error;
use Qiniu\Config;
use Qiniu\Auth;

class AppClient
{
    private $auth;
    private $baseURL;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;

        $this->baseURL = sprintf("%s/%s/apps", Config::RTCAPI_HOST, Config::RTCAPI_VERSION);
    }

    /*
     * hub: 直播空間名
     * title: app 的名稱  注意，Title 不是唯一標識，重複 create 動作將生成多個 app
     * maxUsers：人數限制
     * NoAutoKickUser: bool 型別，可選，禁止自動踢人（搶流）。預設為 false ，
       即同一個身份的 client (app/room/user) ，新的連麥請求可以成功，舊連線被關閉。
     */
    public function createApp($hub, $title, $maxUsers = null, $noAutoKickUser = null)
    {
        $params['hub'] = $hub;
        $params['title'] = $title;
        if (!empty($maxUsers)) {
            $params['maxUsers'] = $maxUsers;
        }
        if (!empty($noAutoKickUser)) {
            $params['noAutoKickUser'] = $noAutoKickUser;
        }
        $body = json_encode($params);
        $ret = $this->post($this->baseURL, $body);
        return $ret;
    }

    /*
     * appId: app 的唯一標識，建立的時候由系統生成。
     * Title: app 的名稱， 可選。
     * Hub: 繫結的直播 hub，可選，用於合流後 rtmp 推流。
     * MaxUsers: int 型別，可選，連麥房間支援的最大線上人數。
     * NoAutoKickUser: bool 型別，可選，禁止自動踢人。
     * MergePublishRtmp: 連麥合流轉推 RTMP 的配置，可選擇。其詳細配置包括如下
            Enable: 布林型別，用於開啟和關閉所有房間的合流功能。
            AudioOnly: 布林型別，可選，指定是否只合成音訊。
            Height, Width: int64，可選，指定合流輸出的高和寬，預設為 640 x 480。
            OutputFps: int64，可選，指定合流輸出的幀率，預設為 25 fps 。
            OutputKbps: int64，可選，指定合流輸出的位元速率，預設為 1000 。
            URL: 合流後轉推旁路直播的地址，可選，支援魔法變數配置按照連麥房間號生成不同的推流地址。如果是轉推到七牛直播雲，不建議使用該配置。
            StreamTitle: 轉推七牛直播雲的流名，可選，支援魔法變數配置按照連麥房間號生成不同的流名。例如，配置 Hub 為 qn-zhibo ，配置 StreamTitle 為 $(roomName) ，則房間 meeting-001 的合流將會被轉推到 rtmp://pili-publish.qn-zhibo.***.com/qn-zhibo/meeting-001地址。詳細配置細則，請諮詢七牛技術支援。
     */
    public function updateApp($appId, $hub, $title, $maxUsers = null, $mergePublishRtmp = null, $noAutoKickUser = null)
    {
        $url = $this->baseURL . '/' . $appId;
        $params['hub'] = $hub;
        $params['title'] = $title;
        if (!empty($maxUsers)) {
            $params['maxUsers'] = $maxUsers;
        }
        if (!empty($noAutoKickUser)) {
            $params['noAutoKickUser'] = $noAutoKickUser;
        }
        if (!empty($mergePublishRtmp)) {
            $params['mergePublishRtmp'] = $mergePublishRtmp;
        }
        $body = json_encode($params);
        $ret = $this->post($url, $body);
        return $ret;
    }

    /*
     * appId: app 的唯一標識，建立的時候由系統生成。
     */
    public function getApp($appId)
    {
        $url = $this->baseURL . '/' . $appId;
        $ret  = $this->get($url);
        return $ret;
    }

    /*
     * appId: app 的唯一標識，建立的時候由系統生成
     */
    public function deleteApp($appId)
    {
        $url = $this->baseURL . '/' . $appId;
        list(, $err)  = $this->delete($url);
        return $err;
    }

    /*
     * 獲取房間的人數
     * appId: app 的唯一標識，建立的時候由系統生成。
     * roomName: 操作所查詢的連麥房間。
     */
    public function listUser($appId, $roomName)
    {
        $url = sprintf("%s/%s/rooms/%s/users", $this->baseURL, $appId, $roomName);
        $ret  = $this->get($url);
        return $ret;
    }

   /*
    * 踢出玩家
    * appId: app 的唯一標識，建立的時候由系統生成。
    * roomName: 連麥房間
    * userId: 請求加入房間的使用者ID
    */
    public function kickUser($appId, $roomName, $userId)
    {
        $url = sprintf("%s/%s/rooms/%s/users/%s", $this->baseURL, $appId, $roomName, $userId);
        list(, $err)  = $this->delete($url);
        return $err;
    }

    /*
     * 獲取房間的人數
     * appId: app 的唯一標識，建立的時候由系統生成。
     * prefix: 所查詢房間名的字首索引，可以為空。
     * offset: int 型別，分頁查詢的位移標記。
     * limit: int 型別，此次查詢的最大長度。
     * GET /v3/apps/<AppID>/rooms?prefix=<RoomNamePrefix>&offset=<Offset>&limit=<Limit>
     */
    public function listActiveRooms($appId, $prefix = null, $offset = null, $limit = null)
    {
        if (isset($prefix)) {
            $query['prefix'] = $prefix;
        }
        if (isset($offset)) {
            $query['offset'] = $offset;
        }
        if (isset($limit)) {
            $query['limit'] = $limit;
        }
        if (isset($query) && !empty($query)) {
            $query = '?' . http_build_query($query);
            $url = sprintf("%s/%s/rooms%s", $this->baseURL, $appId, $query);
        } else {
            $url = sprintf("%s/%s/rooms", $this->baseURL, $appId);
        }
        $ret  = $this->get($url);
        return $ret;
    }

    /*
     * appId: app 的唯一標識，建立的時候由系統生成。
     * roomName: 房間名稱，需滿足規格 ^[a-zA-Z0-9_-]{3,64}$
     * userId: 請求加入房間的使用者 ID，需滿足規格 ^[a-zA-Z0-9_-]{3,50}$
     * expireAt: int64 型別，鑑權的有效時間，傳入以秒為單位的64位Unix
       絕對時間，token 將在該時間後失效。
     * permission: 該使用者的房間管理許可權，"admin" 或 "user"，預設為 "user" 。
       當權限角色為 "admin" 時，擁有將其他使用者移除出房間等特權.
     */
    public function appToken($appId, $roomName, $userId, $expireAt, $permission)
    {
        $params['appId'] = $appId;
        $params['userId'] = $userId;
        $params['roomName'] = $roomName;
        $params['permission'] = $permission;
        $params['expireAt'] = $expireAt;
        $appAccessString = json_encode($params);
        return $this->auth->signWithData($appAccessString);
    }

    private function get($url, $cType = null)
    {
        $rtcToken = $this->auth->authorizationV2($url, "GET", null, $cType);
        $rtcToken['Content-Type'] = $cType;
        $ret = Client::get($url, $rtcToken);
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        return array($ret->json(), null);
    }

    private function delete($url, $contentType = 'application/json')
    {
        $rtcToken = $this->auth->authorizationV2($url, "DELETE", null, $contentType);
        $rtcToken['Content-Type'] = $contentType;
        $ret = Client::delete($url, $rtcToken);
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        return array($ret->json(), null);
    }

    private function post($url, $body, $contentType = 'application/json')
    {
        $rtcToken = $this->auth->authorizationV2($url, "POST", $body, $contentType);
        $rtcToken['Content-Type'] = $contentType;
        $ret = Client::post($url, $body, $rtcToken);
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        $r = ($ret->body === null) ? array() : $ret->json();
        return array($r, null);
    }
}
