<?php
/**
 * 貨幣管理
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年11月25日
**/

class currency_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("currency");
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom['list']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$rslist = $this->model('currency')->get_list();
		$this->assign("rslist",$rslist);
		$this->view("currency_list");
	}

	public function set_f()
	{
		$id = $this->get('id','int');
		$popedom_id = $id ? 'modify' : 'add';
		if(!$this->popedom[$popedom_id]){
			$this->error(P_Lang('您沒有許可權執行此操作'),$this->url('currency'),10);
		}
		if($id){
			$rs = $this->model('currency')->get_one($id);
			$this->assign("rs",$rs);
			$this->assign("id",$id);
		}
		$this->view("currency_set");
	}

	public function setok_f()
	{
		$id = $this->get('id','int');
		$popedom_id = $id ? 'modify' : 'add';
		if(!$this->popedom[$popedom_id]){
			$this->error(P_Lang('您沒有許可權執行此操作'),$this->url('currency'),10);
		}
		$array = array();
		$array["code"] = $this->get('code');
		$array["code_num"] = $this->get('code_num');
		$array["val"] = $this->get("val","float");
		$array["title"] = $this->get("title");
		$array["symbol_left"] = $this->get("symbol_left");
		$array["symbol_right"] = $this->get("symbol_right");
		$array["taxis"] = $this->get("taxis","int");
		$array["status"] = $this->get("status","int");
		$array["hidden"] = $this->get("hidden","int");
		$error_url = $this->url('currency','set');
		if($id) $error_url = $this->url('currency','set','id='.$id);
		if(!$array["title"]){
			$this->error(P_Lang('名稱不允許為空'),$error_url);
		}
		if(!$array["code"]){
			$this->error(P_Lang('編碼不允許為空'),$error_url);
		}
		$this->model('currency')->save($array,$id);
		$this->success(P_Lang('貨幣設定操作成功'),$this->url("currency"));
	}

	public function delete_f()
	{
		$id = $this->get("id",'int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		if(!$this->popedom['delete']){
			$this->json(P_Lang('您沒有許可權執行此操作'));
		}
		$this->model('currency')->del($id);
		$this->json("ok",true);
	}

	public function sort_f()
	{
		$sort = $this->get('sort');
		if(!$sort || !is_array($sort)){
			$this->json(P_Lang('更新排序失敗'));
		}
		foreach($sort AS $key=>$value){
			$key = intval($key);
			$value = intval($value);
			$this->model('currency')->update_sort($key,$value);
		}
		json_exit(P_Lang('更新排序成功'),true);
	}

	public function status_f()
	{
		if(!$this->popedom['status']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定要操作的ID'));
		}
		$rs = $this->model('currency')->get_one($id);
		$status = $rs['status'] ? '0' : '1';
		$this->model('currency')->update_status($id,$status);
		$this->success($status);
	}
}