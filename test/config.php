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
        'host' => '47.92.198.155',
        'data' => 'daojiao',
        'user' => 'szg',
        'pass' => 'szg123!?',
        'port' => '3306',
    ],
'localhost' => [
        'host' => '127.0.0.1',
        'data' => 'daojiao',
        'user' => 'root',
        'pass' => 'abc1234!?',
        'port' => '3306',
    ]

];
