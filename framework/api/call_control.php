<?php
/**
 * 資料呼叫新版專用
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @許可 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2018年11月02日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class call_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$data = $this->get('data','html');
		if(!$data){
			$this->error(P_Lang('未指定引數變數'));
		}
		if(substr($data,0,1) == '{'){
			$data = $this->lib('json')->decode(stripslashes($data));
		}else{
			$tmplist = explode(",",$data);
			$data = array();
			foreach($tmplist as $key=>$value){
				$data[$value] = array();
			}
		}
		$call_all = $this->model('call')->all($this->site['id'],'identifier');
		$is_ok = false;
		$rslist = array();
		foreach($data as $key=>$value){
			if($call_all && $call_all[$key] && $call_all[$key]['is_api']){
				$tmpValue = $value;
				$fid = $key;
				if($value['_alias']){
					unset($tmpValue['_alias']);
					$fid = $value['_alias'];
				}
				$rslist[$fid] = phpok($key,$tmpValue);
				$is_ok = true;
			}else{
				$fid = $value['_alias'] ? $value['_alias'] : $key;
				if($call_all && $call_all[$key] && !$call_all[$key]['is_api']){
					$rslist[$fid] = array('status'=>0,'info'=>P_Lang('未啟用遠端呼叫，請檢查'));
				}else{
					$rslist[$fid] = array('status'=>0,'info'=>P_Lang('沒有找到資料呼叫引數，請檢查'));
				}
			}
		}
		if(!$is_ok){
			$this->error(P_Lang('未啟用遠端呼叫或沒有相關呼叫引數，請檢查'));
		}
		$this->success($rslist);
	}
}
