<?php
/*****************************************************************************************
	檔案： {phpok}/api/task_control.php
	備註： 計劃任務通知
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2015年09月20日 10時05分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class task_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	//計劃任務，每次只執行一條
	public function index_f()
	{
		//解除鎖定
		$this->model('task')->unlock();
		//指定未來時間釋出，自動寫入到計劃任務上來
		$this->crontab_title();
		//獲取計劃任務
		$rslist = $this->model('task')->get_all();
		if(!$rslist){
			$this->json(true);
		}
		foreach($rslist as $key=>$value){
			$this->exec_action($value);
		}
		$this->json(true);
	}

	private function exec_action($rs)
	{
		//鎖定計劃任務執行
		$_id = $rs['id'];
		$_only_once = $rs['only_once'];
		$this->model('task')->lock($_id);
		//年份處理
		$rs['year'] = $rs['year'] == '*' ? date("Y",$this->time) : $rs['year'];
		$rs['month'] = $rs['month'] == '*' ? date("m",$this->time) : $rs['month'];
		$rs['day'] = $rs['day'] == '*' ? date("d",$this->time) : $rs['day'];
		$rs['hour'] = $rs['hour'] == '*' ? date("H",$this->time) : $rs['hour'];
		$rs['minute'] = $rs['minute'] == '*' ? date("i",$this->time) : $rs['minute'];
		$rs['second'] = $rs['second'] == '*' ? date('s',$this->time) : $rs['second'];
		$time = $rs['year'].'-'.$rs['month'].'-'.$rs['day'].' '.$rs['hour'].':'.$rs['minute'].':'.$rs['second'];
		$time = strtotime($time) - 5;
		//五分鐘內只執行一次
		if($rs['exec_time'] && ($rs['exec_time'] + 300)>$this->time){
			$this->model('task')->unlock($_id);
			return true;
		}
		//只執行一天內的計劃任務，超過一天的不再執行
		$if_delete = false;
		if($time <= $this->time && (($time+24*3600)>$this->time || $rs['only_once'])){
			$this->model('task')->exec_start($_id);
			$file = $this->dir_root.'task/'.$rs['action'].'.php';
			if(file_exists($file)){
				if($rs['param']){
					parse_str($rs['param'],$param);
				}
				$status = include $file;
			}
			$this->model('task')->exec_stop($_id);
			if($_only_once){
				$this->model('task')->delete($_id);
				$if_delete = true;
			}
		}
		if(!$if_delete){
			$this->model('task')->unlock($_id);
		}
		return true;
	}

	/**
	 * 檢測 _data/crontab/資料夾下有沒有相應的檔案
	**/
	private function crontab_title()
	{
		//檢測鎖定快取檔案
		if(!is_file($this->dir_cache.'ttime.php')){
			$time = $this->lib('file')->cat($this->dir_cache.'ttime.php');
		}else{
			$time = $this->time - 400;
		}
		if(($time + 300)>$this->time){
			return false;
		}
		$list = $this->lib('file')->ls($this->dir_data.'crontab');
		if(!$list){
			return false;
		}
		$idlist = array();
		foreach($list as $key=>$value){
			$basename = basename($value);
			$basename = str_replace(".php",'',$basename);
			$tmp = explode("-",$basename);
			if(!$tmp || count($tmp) != 2 || !is_numeric($tmp[0])){
				$this->lib('file')->rm($value);
				continue;
			}
			if($tmp[0] > $this->time){
				continue;
			}
			$idlist[] = intval($tmp[1]);
			$this->lib('file')->rm($value);
		}
		if(!$idlist || count($idlist)<1){
			return false;
		}
		$ids = implode(",",$idlist);
		$this->model('list')->update_field($ids,"hidden",'0');
		$this->lib('file')->vi($this->time,$this->dir_cache.'ttime.php');
		return true;
	}
}