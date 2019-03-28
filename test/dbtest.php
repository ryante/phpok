<?php
/*************************************************************************
	File Name   : dbtest.php
	Author      : 
	Note        : 
	Created Time: 2019年03月13日 16:45:30
 ************************************************************************/
require_once "config.php";
//$db = new db_mysqli($dbconfig['phpok']);
$db = new db_mysqli($dbconfig['fastadmin']);
//echo iconv('utf-8', 'gbk', $val['title']) . "\n";
$archives = $db->get_all("select id,channel_id,title,createtime,updatetime,append_table from fa_archives where  deletetime is null");
if (empty($archives)) {
    exit("no archives!");
}
$pkProjectInfo = [
    44 => ['project_id' => 6 , 'module_id' => 2],
    45 => ['project_id' => 3 , 'module_id' => 3],
    46 => ['project_id' => 2 , 'module_id' => 5],
    54 => ['project_id' => 5 , 'module_id' => 2],
    55 => ['project_id' => 6 , 'module_id' => 2]
];
foreach ($archives as $val) {
    $table = "fa_{$val['append_table']}";
    $archivesInfo = $db->get_one("select * from {$table} where id = {$val['id']}");
    $listData = [
        'site_id' => 1,
        'project_id' => $pkProjectInfo[$val['channel_id']]['project_id'],
        'module_id' => $pkProjectInfo[$val['channel_id']]['module_id'],
        'title' => $val['title'],
        'dateline' => time(),
        'status' => 1,
    ];
    $listInfoData = $archivesInfo;
    $listInfoData['site_id'] = 1;
    $listInfoData['project_id'] = $pkProjectInfo[$val['channel_id']]['project_id'];
    unset($listInfoData['id']);
    unset($listInfoData['content']);
    if (!empty($archivesInfo['version'])) {
        $version = json_decode($archivesInfo['version'], true);
        $listInfoData['version_name'] = array_shift($version);
        unset($listInfoData['version']);
    }

    $insertId = saveListData($listData, $listInfoData, "dj_list_{$pkProjectInfo[$val['channel_id']]['module_id']}");
    if (empty($insertId)) {
        exit("insert info data error" . json_encode($listInfoData,true));
    }
    echo $insertId . "\n";
    // 子项
    if (!empty($version) && $insertId) {
        $listData['parent_id'] = $insertId;
        $listInfoData['version_name'] = array_shift($version);
        $insertId = saveListData($listData, $listInfoData, "dj_list_{$pkProjectInfo[$val['channel_id']]['module_id']}");
        if (empty($insertId)) {
            exit("insert info data error" . json_encode($listInfoData,true));
        }
    }
}

function getBooks($archiveId, $version = '') {
    global $dbconfig;
    $db = new db_mysqli($dbconfig['fastadmin']);
    $books = $db->get_all("select version,content,image,weigh from fa_book where literature={$archiveId} and version = {$version}");
    if (empty($books)) {
        return false;
    }
    return $books;
}

function saveBookData($lid, $bookData) {
    if (empty($lid) || empty($bookData)) {
        return false;
    }
    global $dbconfig;
    $db = new db_mysqli($dbconfig['localhost']);
    $insertId = $db->insert_array($bookData, "dj_list_6", "insert", false);
    if (empty($insertId)) {
        return false;
    }
    return $insertId;
}

function savePicData($url) {
    if (empty($url)) {
        return false;
    }
    global $dbconfig;
    $db = new db_mysqli($dbconfig['localhost']);
    $insertId = $db->insert_array($bookData, "dj_list_6", "insert", false);
    if (empty($insertId)) {
        return false;
    }
    return $insertId;
}


function saveListData($listData, $listInfoData, $infoTable) {
    if (empty($listData) || empty($listInfoData)) {
        return false;
    }
    global $dbconfig;
    $db = new db_mysqli($dbconfig['localhost']);
    $insertId = $db->insert_array($listData, "dj_list", "insert", false);
    if (empty($insertId)) {
        return false;
    }
    $listInfoData['id'] = $insertId;
    $insertId = $db->insert_array($listInfoData, $infoTable, "insert", false);
    if (empty($insertId)) {
        return false;
    }
    return $insertId;
}
