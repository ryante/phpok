<?php
/*************************************************************************
	File Name   : config.php
	Author      : 
	Note        : 
	Created Time: 2019年03月13日 16:46:58
 ************************************************************************/
define("PHPOK_SET", 1);
error_reporting(E_ALL ^ E_NOTICE);//显示除去 E_NOTICE 之外的所有错误信息
require_once dirname(dirname(__FILE__)) . "/framework/engine/db.php";
require_once dirname(dirname(__FILE__)) . "/framework/engine/db/mysqli.php";
global $dbconfig;
$dbconfig = [
    'phpok' => [
        'host' => '192.168.2.205',
        'data' => 'daojiao',
        'user' => 'ryante',
        'pass' => 'abc123',
        'port' => '3306',
    ]
];
