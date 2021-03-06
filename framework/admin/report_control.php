<?php
/**
 * 報表統計，包括會員，訂單，自定義模組的資料統計
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @許可 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年10月17日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class report_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('report');
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 報表統計
	 * @引數 type 統計型別，user 為會員，order 為訂單，數字為要統計的專案
	 * @引數 fields 要統計的欄位，多個欄位用英文逗號隔開
	 * @引數 group 分組執行
	 * @引數 times 統計的時間範圍，陣列，包括開始時間和結束時間
	 * @引數 
	**/
	public function index_f()
	{
		$list = array('user'=>P_Lang('會員'),'order'=>P_Lang('訂單'),'title'=>P_Lang('主題數'));
		$wealth_list = $this->model('wealth')->get_all();
		if($wealth_list){
			$list['wealth'] = P_Lang('財富');
		}
		$project_list = $this->model('project')->project_all($this->session->val('admin_site_id'),'id',$condition);
		if($project_list){
			foreach($project_list as $key=>$value){
				$list[$value['id']] = $value['title'];
			}
		}
		$this->assign('plist',$list);
		$condition = 'module>0';
		$type = $this->get('type');
		if($type && $list[$type]){
			$this->assign('lead_title',$list[$type]);
			$this->assign('type',$type);
		}
		$x = $this->get('x');
		$y = $this->get('y');
		$startdate = $this->get('startdate');
		$stopdate = $this->get('stopdate');
		$this->assign('x',$x);
		$this->assign('y',$y);
		$this->assign('startdate',$startdate);
		$this->assign('stopdate',$stopdate);
		if($type == 'user'){
			$xy = $this->_user_type();
			$rslist = $this->model('report')->user_data($x,$startdate,$stopdate);
		}
		if($type == 'order'){
			$xy = $this->_order_type();
			$rslist = $this->model('report')->order_data($x,$startdate,$stopdate);		
		}
		if($type == 'title'){
			$xy = $this->_title_type();
			$rslist = $this->model('report')->title_data($x,$startdate,$stopdate);		
		}
		if($type == 'wealth'){
			$xy = $this->_wealth_type();
			$rslist = $this->model('report')->wealth_data($x,$startdate,$stopdate);		
		}
		if($type && is_numeric($type)){
			$xy = $this->_list_type($type);
			$data_mode = $this->get('data_mode');
			$rslist = $this->model('report')->list_data($type,$x,$y,$data_mode,$startdate,$stopdate);		
		}
		if($rslist && $x){
			$rslist = $this->_format_rslist_x($x,$rslist);
			$this->assign('rslist',$rslist);
		}
		
		if($y && $xy['y']){
			$y_title = array();
			foreach($xy['y'] as $key=>$value){
				if($key && in_array($key,$y)){
					$y_title[$key] = $value;
				}
			}
			$this->assign('y_title',$y_title);
		}
		if($x && $xy['x'] && $xy['x'][$x]){
			$this->assign('x_title',$xy['x'][$x]);
		}
		$chart = $this->get('chart');
		$this->assign('chart',$chart);
		$this->addjs('js/laydate/laydate.js');
		$this->addjs('js/echarts.min.js');
		$this->view('report_index');
	}

	public function ajax_type_f()
	{
		$type = $this->get('type');
		if($type == 'user'){
			$info = $this->_user_type();
			$this->success($info);
		}
		if($type == 'order'){
			$info = $this->_order_type();
			$this->success($info);
		}
		if($type == 'title'){
			$info = $this->_title_type();
			$this->success($info);
		}
		if($type == 'wealth'){
			$info = $this->_wealth_type();
			if(!$info){
				$this->error(P_Lang('未設定相應的財富資訊'));
			}
			$this->success($info);
		}
		if($type && is_numeric($type)){
			$info = $this->_list_type($type);
			$this->success($info);
		}
		$this->success();
	}

	private function _wealth_type()
	{
		$ylist = array();
		$wlist = $this->model('wealth')->get_all();
		if(!$wlist){
			return false;
		}
		if($wlist){
			foreach($wlist as $key=>$value){
				$ylist[$value['identifier']] = $value['title'];
			}
		}
		$xlist = array('date'=>P_Lang('日期'),'week'=>P_Lang('周'),'month'=>P_Lang('月份'),'year'=>P_Lang('年度'),'user'=>P_Lang('會員'));
		$this->assign('xlist',$xlist);
		$this->assign('ylist',$ylist);
		return array('x'=>$xlist,'y'=>$ylist);
	}

	private function _list_type($pid)
	{
		$ylist = array('count'=>P_Lang('數量'));
		$project = $this->model('project')->get_one($pid,false);
		if(!$project || !$project['module']){
			return false;
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			return false;
		}
		$xlist = array();
		if(!$module['mtype']){
			$ylist['hits'] = P_Lang('點選');
			$xlist = array('date'=>P_Lang('日期'),'week'=>P_Lang('周'),'month'=>P_Lang('月份'),'year'=>P_Lang('年度'));
		}
		$zlist = false;
		$flist = $this->model('module')->fields_all($project['module']);
		if($flist){
			$forbid = array('longtext','longblob','text','blob','tinytext','tinyblob','mediumtext','mediumblob');
			foreach($flist as $key=>$value){
				if(in_array($value['field_type'],$forbid)){
					continue;
				}
				$ylist['ext_'.$value['identifier']] = $value['title'];
				$xlist['ext_'.$value['identifier']] = $value['title'];
				$zlist = true;
			}
		}
		if(!$xlist || count($xlist)<1){
			return false;
		}
		$this->assign('xlist',$xlist);
		$this->assign('ylist',$ylist);
		$this->assign('zlist',$zlist);
		return array('x'=>$xlist,'y'=>$ylist,'z'=>$zlist);
	}

	private function _title_type()
	{
		$ylist = array('count'=>P_Lang('數量'),'hits'=>P_Lang('點選'),'reply'=>P_Lang('回覆'));
		$xlist = array('date'=>P_Lang('日期'),'week'=>P_Lang('周'),'month'=>P_Lang('月份'),'year'=>P_Lang('年度'));
		$this->assign('xlist',$xlist);
		$this->assign('ylist',$ylist);
		return array('x'=>$xlist,'y'=>$ylist);
	}

	private function _order_type()
	{
		$ylist = array('count'=>P_Lang('訂單數量'),'price'=>P_Lang('訂單價格'),'user'=>P_Lang('會員數'));
		$xlist = array('date'=>P_Lang('日期'),'week'=>P_Lang('周'),'month'=>P_Lang('月份'),'year'=>P_Lang('年度'),'order'=>P_Lang('訂單狀態'));
		$xlist['user'] = P_Lang('會員');
		$this->assign('xlist',$xlist);
		$this->assign('ylist',$ylist);
		return array('x'=>$xlist,'y'=>$ylist);
	}

	private function _user_type()
	{
		$ylist = array('count'=>P_Lang('註冊數量'));
		$flist = $this->model('user')->fields_all('field_type NOT IN("longtext","longblob","text")');
		if($flist){
			foreach($flist as $key=>$value){
				$ylist[$value['identifier']] = $value['title'];
			}
		}
		$xlist = array('date'=>P_Lang('日期'),'week'=>P_Lang('周'),'month'=>P_Lang('月份'),'year'=>P_Lang('年度'),'group_id'=>P_Lang('會員組'));
		$this->assign('xlist',$xlist);
		$this->assign('ylist',$ylist);
		return array('x'=>$xlist,'y'=>$ylist);
	}

	/**
	 * 格式化X座標裡的資料
	 * @引數 $x X的資料
	 * @引數 $rslist 資料
	**/
	private function _format_rslist_x($x='date',$rslist)
	{
		if(!$x || !$rslist){
			return false;
		}
		if($x == 'group_id'){
			$grouplist = $this->model('usergroup')->get_all('is_guest=0','id');
			foreach($rslist as $key=>$value){
				$value['x'] = $grouplist[$value['x']]['title'];
				$rslist[$key] = $value;
			}
		}
		if($x == 'order'){
			$olist = $this->model('site')->order_status_all();
			foreach($rslist as $key=>$value){
				$value['x'] = $olist[$value['x']]['title'];
				$rslist[$key] = $value;
			}
		}
		if($x == 'user'){
			$ids = array();
			foreach($rslist as $key=>$value){
				$ids[] = $value['x'];
			}
			$tmplist = $this->model('user')->simple_user_list($ids,'user');
			if($tmplist){
				foreach($rslist as $key=>$value){
					if($tmplist[$value['x']]){
						$value['x'] = $tmplist[$value['x']];
						$rslist[$key] = $value;
					}
				}
			}
		}
		if($x == 'week'){
			foreach($rslist as $key=>$value){
				$tmp = str_replace('-',P_Lang('年第'),$value['x']).P_Lang('周');
				$value['x'] = $tmp;
				$rslist[$key] = $value;
			}
		}
		if($x == 'month'){
			foreach($rslist as $key=>$value){
				$tmp = str_replace('-',P_Lang('年'),$value['x']).P_Lang('月');
				$value['x'] = $tmp;
				$rslist[$key] = $value;
			}
		}
		if($x == 'year'){
			foreach($rslist as $key=>$value){
				$tmp = $value['x'].P_Lang('年');
				$value['x'] = $tmp;
				$rslist[$key] = $value;
			}
		}
		return $rslist;
	}
}
