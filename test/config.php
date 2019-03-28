<?php
/*************************************************************************
	File Name   : config.php
	Author      : 
	Note        : 
	Created Time: 2019年03月13日 16:46:58
 ************************************************************************/
define("PHPOK_SET", 1);
error_reporting(E_ALL ^ E_NOTICE);//顯示除去 E_NOTICE 之外的所有錯誤資訊
require_once dirname(dirname(__FILE__)) . "/framework/engine/db.php";
require_once dirname(dirname(__FILE__)) . "/framework/engine/db/mysqli.php";
global $dbconfig;
$dbconfig = [
    'fastadmin' => [
        'host' => '127.0.0.1',
        'data' => 'fastadmin',
        'user' => 'root',
        'pass' => 'root',
        'port' => '3306',
    ],
    'localhost' => [
        'host' => '127.0.0.1',
        'data' => 'daojiao',
        'user' => 'root',
        'pass' => 'root',
        'port' => '3306',
        'prefix' => '',
    ]

];
