<?php
/**
 * 外掛獲取JSON內容資料
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年11月26日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

class plugin_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 外掛前臺方法
	**/
	public function index_f()
	{
		$id = $this->get('id','system');
		$exec = $this->get('exec','system');
		$this->action($id,$exec);
	}

	/**
	 * 外掛方法別名
	**/
	public function exec_f()
	{
		$this->index_f();
	}

	/**
	 * 外掛方法別名
	**/
	public function ajax_f()
	{
		$this->index_f();
	}

	public function action($id,$exec="index",$params=array())
	{
		//強制使用Json資料
		$this->config('is_ajax',true,'system');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		if(!$exec){
			$exec = 'index';
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs || !$rs['status']){
			$this->error(P_Lang('外掛不存在或未啟用'));
		}
		if(!file_exists($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php')){
			$this->error(P_Lang('外掛應用{appid}.php不存在',array('appid'=>$this->app_id)));
		}
		//將傳過來的引數變數變成Post模式，以方便內部執行
		if($params && is_array($params)){
			foreach($params as $key=>$value){
				$_POST[$key] = $value;
			}
		}
		include_once($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php');
		$name = $this->app_id.'_'.$id;
		$cls = new $name();
		$mlist = get_class_methods($cls);
		if(!$mlist || !in_array($exec,$mlist)){
			$this->error(P_Lang('外掛方法{method}不存在',array('method'=>$exec)));
		}
		$cls->$exec($params);
	}
}