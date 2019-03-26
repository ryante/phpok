<?php
/***********************************************************
	Filename: {phpok}/api/index_control.php
	Note	: API介面預設接入
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年10月30日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class index_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		if(!$this->site['api_code']){
			$this->error(P_Lang("系統未啟用介面功能"));
		}
		$data = array('ctrl_id'=>$this->config['ctrl_id']);
		$data['func_id'] = $this->config['func_id'];
		$data['site_id'] = $this->site['id'];
		$data['session_name'] = $this->session->sid();
		$data['session_val'] = $this->session->sessid();
		$wxAppConfig = $this->get('wxAppConfig');
		$clear_url = $this->config['url'].'wxapp/';
		if($wxAppConfig && is_file($this->dir_data.'wxappconfig.php')){
			include_once($this->dir_data.'wxappconfig.php');
			if($wxconfig && $wxconfig['rslist']){
				foreach($wxconfig['rslist'] as $key=>$value){
					if($value['thumb']){
						$value['thumb'] = str_replace($clear_url,'',$value['thumb']);
					}
					if($value['thumb_selected']){
						$value['thumb_selected'] = str_replace($clear_url,'',$value['thumb_selected']);
					}
					$wxconfig['rslist'][$key] = $value;
				}
			}
			$data['wxconfig'] = $wxconfig;
		}
		$this->success($data);
	}

	public function site_f()
	{
		$this->config('is_ajax',true);
		if(!$this->site['api_code']){
			$this->error(P_Lang("系統未啟用介面功能"));
		}
		unset($this->site['api_code']);
		$id = $this->get('id');
		if(!$id){
			$id = 'title';
		}
		$data = array();
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			if($this->site[$value]){
				$data[$value] = $this->site[$value];
			}
		}
		$this->success($data);
	}

	public function token_f()
	{
		$this->config('is_ajax',true);
		if(!$this->site['api_code']){
			$this->error(P_Lang("系統未配置介面功能"));
		}
		$id = $this->get('id','system');
		if(!$id){
			$this->error(P_Lang('未指定資料呼叫標識'));
		}
		$this->model('call')->site_id($this->site['id']);
		$rs = $this->model('call')->get_one($id,'identifier');
		if(!$rs || !$rs['status']){
			$this->error(P_Lang('標識不存在或未啟用'));
		}
		if(!$rs['is_api']){
			$this->error(P_Lang('未啟用遠端接入'));
		}
		if($rs['type_id'] == 'sql' && !$this->config['api_remote_sql']){
			$this->error(P_Lang('系統未開放遠端呼叫SQL操作，需要在配置檔案啟用api_remote_sql值設為true'));
		}
		$param = array();
		$pid = $this->get('pid','int');
		if($pid){
			$param['pid'] = $pid;
		}else{
			$project = $this->get('project','system');
			if($project){
				$tmp = $this->model('project')->simple_project_from_identifier($project,$this->site['id']);
				if($tmp && $tmp['id']){
					$param['pid'] = $tmp['id'];
				}
			}
		}
		//判斷是否有引數分類
		$cateid = $this->get('cateid','int');
		if($cateid){
			$param['cateid'] = $cateid;
		}else{
			$cate = $this->get('cate','system');
			if($cate){
				$cate_rs = $this->model('cate')->get_one($cate,'identifier',false);
				if($cate_rs && $cate_rs['status']){
					$param['cateid'] = $cate_rs['id'];
				}
			}
		}
		//判斷是否有指定sqlinfo
		$sqlinfo = $this->get('sql');
		if($sqlinfo){
			$sqlinfo = str_replace(array('&#39;','&quot;','&apos;','&#34;'),array("'",'"',"'",'"'),$sqlinfo);
			$param['sqlinfo'] = $sqlinfo;
		}
		//判斷是否要指定會員ID
		$uid = $this->get('uid','int');
		if($uid){
			$param['user_id'] = $uid;
		}else{
			$user = $this->get('user');
			if($user){
				$user_rs = $this->model('user')->get_one($user,'user',false,false);
				if($user_rs && $user_rs['status'] == 1){
					$param['user_id'] = $user_rs['id'];
				}
			}
		}
		$ext = $this->get('ext');
		if($ext && is_array($ext)){
			foreach($ext as $key=>$value){
				if($key == 'sqlext' && $value){
					$value = str_replace(array('&#39;','&quot;','&apos;','&#34;'),array("'",'"',"'",'"'),$value);
				}
				$param[$key] = $value;
			}
		}
		$this->lib('token')->keyid($this->site['api_code']);
		$array = array('id'=>$id,'param'=>$param);
		$token = $this->lib('token')->encode($array);
		$this->success($token);
	}

	public function phpok_f()
	{
		if(!$this->site['api_code']){
			$this->json(P_Lang("系統未啟用介面功能"));
		}
		$token = $this->get("token");
		if(!$token){
			$this->json(P_Lang("介面資料異常"));
		}
		$this->lib('token')->keyid($this->site['api_code']);
		$info = $this->lib('token')->decode($token);
		if(!$info){
			$this->json(P_Lang('資訊為空'));
		}
		$id = $info['id'];
		if(!$id){
			$this->json(P_Lang('未指定資料呼叫中心ID'));
		}
		$param = $info['param'];
		if($param){
			if(is_string($param)){
				$pm = array();
				parse_str($param,$pm);
				$param = $pm;
				unset($pm);
			}
		}else{
			$param = array();
		}
		$ext = $this->get('ext');
		if($ext && is_array($ext)){
			foreach($ext as $key=>$value){
				if(!$value){
					continue;
				}
				if($key == 'sqlext' && $value){
					$value = str_replace(array('&#39;','&quot;','&apos;','&#34;'),array("'",'"',"'",'"'),$value);
				}
				$param[$key] = $value;
			}
		}
		$list = $this->call->phpok($id,$param);
		if(!$list){
			$this->json(P_Lang("沒有獲取到資料"));
		}
		$tpl = $this->get("tpl");
		if($tpl && $this->tpl->check_exists($tpl)){
			$this->assign("rslist",$list);
			$info = $this->fetch($tpl);
			$this->json($info,true);
		}
		$this->json($list,true);
	}

	public function qrcode_f()
	{
		$data = $this->get('data');
		if(!$data){
			$this->error(P_Lang('未指定生成的二維碼資料'));
		}
		$this->lib('qrcode')->png($data);
	}
}