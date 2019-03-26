<?php
/**
 * 自動儲存資料
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年06月14日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class auto_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	# 儲存表單
	public function index_f()
	{
		$type = $this->get("__type");
		if(!$type) $type = "list";
		$str = $_POST ? serialize($_POST) : "";
		if(!$str){
			$this->json(P_Lang('沒有自動儲存的表單資料'),true);
		}
		$rs = $this->model('temp')->chk($type,$_SESSION["admin_id"]);
		if($rs){
			$id = $rs["id"];
			unset($rs["id"]);
			$rs["content"] = $str;
		}else{
			$rs["content"] = $str;
			$rs["tbl"] = $type;
			$rs["admin_id"] = $_SESSION["admin_id"];
		}
		$this->model('temp')->save($rs,$id);
		$this->json(P_Lang('資料儲存成功'),true);
	}

	public function read_f()
	{
		$type = $this->get("__type");
		if(!$type) $type = "list";
		$rs = $this->model('temp')->chk($type,$_SESSION["admin_id"]);
		if($rs){
			$content = unserialize($rs["content"]);
			$this->json($content,true);
		}else{
			$this->json("沒有資料");
		}
	}


	/**
	 * 自動儲存新增的資料
	**/
	public function list_f()
	{
		$pid = $this->get('pid');
		$uid = $this->session->val('admin_id');
		$filename = $this->dir_cache.'autosave_'.$uid.'_'.$pid.'.php';
		$this->lib('file')->rm($filename);
		$data = isset($_POST) ? serialize($_POST) : '';
		if($data){
			$this->lib('file')->vi($data,$filename);
		}
		$this->success();
	}

}