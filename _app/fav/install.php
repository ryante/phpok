<?php
/**
 * 收藏夾安裝
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年07月01日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

$sql = "CREATE TABLE IF NOT EXISTS `".$this->db->prefix."fav` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',`user_id` int(10) unsigned NOT NULL COMMENT '會員ID',`thumb` varchar(255) NOT NULL COMMENT '縮圖',`title` varchar(255) NOT NULL COMMENT '標題',`note` varchar(255) NOT NULL COMMENT '摘要',`addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新增時間',`lid` int(11) NOT NULL COMMENT '關聯主題',PRIMARY KEY (`id`),KEY `user_id` (`user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='會員收藏夾' AUTO_INCREMENT=1";

$this->db->query($sql);

//增加導航選單
$menu = array('parent_id'=>5,'title'=>P_Lang('收藏夾管理'),'status'=>1);
$menu['appfile'] = 'fav';
$menu['taxis'] = 255;
$menu['site_id'] = 0;
$menu['icon'] = 'newtab';
$insert_id = $this->model('sysmenu')->save($menu);
if($insert_id){
	$tmparray = array('gid'=>$insert_id,'title'=>'檢視','identifier'=>'list','taxis'=>10);
	$this->model('popedom')->save($tmparray);
	$tmparray = array('gid'=>$insert_id,'title'=>'刪除','identifier'=>'delete','taxis'=>10);
	$this->model('popedom')->save($tmparray);
}