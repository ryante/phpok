<?php
/*************************************************************************
	File Name   : dbtest.php
	Author      : 
	Note        : 
	Created Time: 2019年03月13日 16:45:30
 ************************************************************************/
require_once "config.php";
$db = new db_mysqli($dbconfig['phpok']);
$row = $db->get_one("select * from dj_adm");
print_r($row);die;
