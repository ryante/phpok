<?php
/**
 * PHPOK企業站系統，使用PHP語言及MySQL資料庫編寫的企業網站建設系統，基於LGPL協議開源授權
 * @package phpok
 * @author phpok.com
 * @copyright 2015-2016 深圳市錕鋙科技有限公司
 * @version 4.x
 * @license http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
**/

/**
 * 定義常量，所有PHP檔案僅允許從這裡入口
**/
define("PHPOK_SET",true);

/**
 * 定義**APP_ID**，不同**APP_ID**呼叫不同的檔案
**/
define("APP_ID","admin");

/**
 * 定義應用的根目錄，如果程式出程，請將ROOT改為：define("ROOT","./");
**/
define("ROOT",str_replace("\\","/",dirname(__FILE__))."/");

/**
 * 網頁訪問根目錄
**/
define('WEBROOT','.');

/**
 * 定義框架目錄
**/
define("FRAMEWORK",ROOT."framework/");

/**
 * 定義資料檔案目錄
**/
define('DATA',ROOT.'_data/');

/**
 * 定義配置檔案目錄
**/
define('CONFIG',ROOT.'_config/');

/**
 * 定義快取目錄
**/
define('CACHE',ROOT.'_cache/');

/**
 * 定義 APP 目錄，該目錄用於系統應用程式讀取，僅限官方擴充套件開發應用
**/
define('OKAPP',ROOT.'_app/');


/**
 * 定義擴充套件庫目錄
**/
define('EXTENSION',ROOT.'extension/');

/**
 * 定義外掛目錄
**/
define('PLUGIN',ROOT.'plugins/');

/**
 * 定義閘道器路由目錄
**/
define('GATEWAY',ROOT.'gateway/');


/**
 * 引入初始化檔案
**/
require(FRAMEWORK.'init.php');
