<?php
/***********************************************************
	Filename: {phpok}/model/search.php
	Note	: 搜尋涉及到的查詢，這裡僅用於全站搜尋簡單查詢
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月21日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class search_model_base extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	//取得查詢結果數量
	function get_total($condition="")
	{
		$sql = "SELECT count(l.id) FROM ".$this->db->prefix."list l WHERE l.status=1 AND l.hidden=0 ";
		if($condition)
		{
			$sql.= " AND ".$condition;
		}
		return $this->db->count($sql);
	}

	//查詢ID數量
	function id_list($condition="",$offset=0,$psize=30)
	{
		$sql .= "SELECT l.id FROM ".$this->db->prefix."list l WHERE l.status=1 AND l.hidden=0 ";
		if($condition){
			$sql.= " AND ".$condition;
		}
		$sql.= " ORDER BY l.sort DESC,l.dateline DESC,l.id DESC LIMIT ".intval($offset).",".intval($psize);
		return $this->db->get_all($sql);
	}

	//
}