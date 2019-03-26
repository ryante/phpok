<?php
/**
 * 閘道器路由介面引數執行資訊
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2019年1月18日
**/
class gateway_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$this->config('is_ajax',true);
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定閘道器路由ID'));
		}
		$rs = $this->model('gateway')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('閘道器路由資訊不存在'));
		}
		if(!$rs['status']){
			$this->error(P_Lang('閘道器路由未啟用'));
		}
		$file = $this->get('file');
		if(!$file){
			$file = 'exec';
		}
		$exec_file = $this->dir_gateway.$rs['type'].'/'.$rs['code'].'/'.$file.'.php';
		if(!is_file($exec_file)){
			$this->error(P_Lang('要執行的檔案{file}不存在',array('file'=>$file)));
		}
		$this->gateway('type',$rs['type']);
		$this->gateway('param',$rs);
		$this->gateway('extinfo',$rs['ext']);
		$this->gateway($file.'.php','json');
	}

	/**
	 * 內部呼叫執行閘道器操作
	 * @引數 $id 閘道器ID
	 * @引數 $file 預設執行 exec.php 檔案（傳過來的引數不帶 .php）
	**/
	public function exec_file($id,$file='exec',$post=array())
	{
		if(!$id){
			return false;
		}
		$rs = $this->model('gateway')->get_one($id);
		if(!$rs){
			return false;
		}
		if(!$rs['status']){
			return false;
		}
		$exec_file = $this->dir_gateway.$rs['type'].'/'.$rs['code'].'/'.$file.'.php';
		if(!is_file($exec_file)){
			return false;
		}
		$this->gateway('type',$rs['type']);
		$this->gateway('param',$rs);
		$this->gateway('extinfo',$rs['ext']);
		if($post && is_array($post)){
			foreach($post as $key=>$value){
				$_POST[$key] = $value;
			}
		}
		return $this->gateway($file.'.php');
	}
}
