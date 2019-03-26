<?php
/**
 * 資料庫備份及恢復操作
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年12月02日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class sql_control extends phpok_control
{
	private $popedom;

	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("sql");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 資料庫列表
	**/
	public function index_f()
	{
		if(!$this->popedom["list"] && !$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		//讀取全部資料庫表
		$rslist = $this->model('sql')->tbl_all();
		$total_size = 0;
		$strlen = strlen($this->db->prefix);
		if($rslist){
			foreach($rslist as $key=>$value){
				$length = $value['Avg_row_length'] + $value['Data_length'] + $value['Index_length'] + $value['Data_free'];
				$value['length'] = $this->lib('common')->num_format($length);
				$value['free'] = $value['Data_free'] ? $this->lib('common')->num_format($value['Data_free']) : 0;
				$total_size += $length;
				$value['delete'] = substr($value['Name'],0,$strlen) == $this->db->prefix ? false : true;
				$rslist[$key] = $value;
			}
		}
		$this->assign("rslist",$rslist);
		$this->view("sql_index");
	}

	/**
	 * 資料表優化
	 * @引數 id 要優化的資料表，不能為空
	**/
	public function optimize_f()
	{
		if(!$this->popedom['optimize']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未選定要操作的資料表'));
		}
		$idlist = explode(",",$id);
		foreach($idlist as $key=>$value){
			if(!preg_match("/^[a-z0-9A-Z\_\-]+$/u",$value)){
				continue;
			}
			$this->model('sql')->optimize($value);
		}
		$this->success();
	}

	/**
	 * 資料表修復
	 * @引數 id 要修復的資料表，不能為空
	**/
	public function repair_f()
	{
		if(!$this->popedom['repair']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未選定要操作的資料表'));
		}
		$idlist = explode(",",$id);
		foreach($idlist as $key=>$value){
			if(!preg_match("/^[a-z0-9A-Z\_\-]+$/u",$value)){
				continue;
			}
			$this->model('sql')->repair($value);
		}
		$this->success();
	}

	/**
	 * 備份資料表操作
	 * @引數 id 要備份的表，為空或all時表示備份全部
	 * @引數 backfilename 備份的檔名，為空表示剛開始備份，系統自動生成一個備份檔名，並同時將資料庫裡的表結構備份好
	 * @引數 startid 整數型 開始ID，為空表示從0開始，表示備份表的ID順序
	 * @引數 dataid 備份到的目標ID，也是從0開始（為空即為0）
	 * @引數 pageid 頁碼ID，每次備份100條資料，當資料表中的資料超過100條時，pageid就起到作用了
	**/
	public function backup_f()
	{
		if(!$this->popedom['create']){
			$this->error(P_Lang('您沒有許可權執行此操作'),$this->url('sql'));
		}
		$id = $this->get('id');
		if(!$id || $id == "all"){
			$tbl_list = $this->model('sql')->tbl_all();
			$idlist = array();
			foreach($tbl_list AS $key=>$value){
				$idlist[] = $value["Name"];
			}
			$url = $this->url('sql','backup','id=all');
		}else{
			$url = $this->url("sql","backup","id=".rawurlencode($id));
			$idlist = explode(",",$id);
		}
		$backfilename = $this->get('backfilename');
		$sql_prefix = $this->model('sql')->sql_prefix();
		if(!$backfilename){
			$backfilename = "sql".$this->time;
			$url .= "&backfilename=".$backfilename;
			//更新資料表結構
			$html = "";
			foreach($idlist as $key=>$value){
				if(!preg_match("/^[a-z0-9A-Z\_\-]+$/u",$value)){
					continue;
				}
				$html .= "DROP TABLE IF EXISTS ".$value.";\n";
				$html .= $this->model('sql')->show_create_table($value);
				$html .= ";\n\n";
				if($value == $sql_prefix.'adm'){
					$rslist = $this->model('sql')->getsql($sql_prefix."adm",0,"all");
					if($rslist){
						foreach($rslist AS $k=>$v){
							$html .= "INSERT INTO ".$sql_prefix."adm VALUES('".implode("','",$v)."');\n";
						}
					}
				}
			}
			$this->lib('file')->vi($html,$this->dir_data.$backfilename.".php");//儲存資料
			$this->lib('file')->vi("-- PHPOK4 Full 資料備份\n\n",$this->dir_data.$backfilename."_tmpdata.php");
			$this->success(P_Lang('表結構備份成功，正在執行下一步'),$url);
		}
		$url .= "&backfilename=".$backfilename;
		$startid = $this->get("startid","int");
		$dataid = $this->get("dataid",'int');
		if(($startid + 1)> count($idlist) && file_exists($this->dir_data.$backfilename.'_tmpdata.php')){
			$newfile = $this->dir_data.$backfilename.'_'.$dataid.'.php';
			$this->lib('file')->mv($this->dir_data.$backfilename.'_tmpdata.php',$newfile);
			$this->success(P_Lang('資料備份成功'),$this->url('sql','backlist'));
		}
		$pageid = $this->get("pageid",'int');
		$table = $idlist[$startid];//指定表
		//判斷如果是管理員表，則跳到下一步
		if($table == $sql_prefix."adm" || $table == $sql_prefix."session"){
			$url .= "&startid=".($startid+1)."&pageid=".$pageid."&dataid=".$dataid;
			$this->success(P_Lang('資料表{table}已備份完成！正在進行下一步操作，請稍候！',array('table'=>' <span class="red">'.$table.'</span> ')),$url);
		}
		$psize = 100;
		$total = $this->model('sql')->table_count($table);
		if($total<1){
			$url .= "&startid=".($startid+1)."&pageid=".$pageid."&dataid=".$dataid;
			$this->success(P_Lang('資料表{table}已備份完成！正在進行下一步操作，請稍候！',array('table'=>' <span class="red">'.$table.'</span> ')),$url);
		}
		if($psize >= $total){
			$rslist = $this->model('sql')->getsql($table,0,'all');
			if(!$rslist){
				$rslist = array();
			}
			$msg = "\n-- 表：".$table."，備份時間：".date("Y-m-d H:i:s",$this->time)."\n";
			$msg.= "INSERT INTO ".$table." VALUES";
			$i=0;
			foreach($rslist as $key=>$value){
				$tmp_value = array();
				foreach($value AS $k=>$v){
					$v = $this->model('sql')->escape($v);
					$tmp_value[$k] = $v;
				}
				if($i){
					$msg .= ",\n";
				}
				$msg .= "('".implode("','",$tmp_value)."')";
				$i++;
			}
			$msg .= ";\n";
			$new_startid = $startid + 1;
			$pageid = 0;
		}else{
			$msg = '';
			$pageid = $this->get('pageid','int');
			if($pageid<1){
				$pageid = 1;
			}
			if($pageid<2){
				$msg .= "\n-- 表：".$table."，備份時間：".date("Y-m-d H:i:s",$this->time)."\n";
			}
			$offset = ($pageid-1) * $psize;
			if($offset < $total){
				$rslist = $this->model('sql')->getsql($table,$offset,$psize);
				if($rslist){
					$msg.= "INSERT INTO ".$table." VALUES";
					$i=0;
					foreach($rslist AS $key=>$value){
						$tmp_value = array();
						foreach($value AS $k=>$v){
							$v = $this->model('sql')->escape($v);
							$tmp_value[$k] = $v;
						}
						if($i){
							$msg .= ",\n";
						}
						$msg .= "('".implode("','",$tmp_value)."')";
						$i++;
					}
					$msg .= ";\n";
					$new_startid = $startid;
					$pageid = $pageid + 1;
				}else{
					$new_startid = $startid + 1;
					$pageid = 0;
				}
			}else{
				$new_startid = $startid + 1;
				$pageid = 0;
			}
		}
		$url .= "&startid=".$new_startid."&pageid=".$pageid;
		$fsize = 0;
		if(!file_exists($this->dir_data.$backfilename.'_tmpdata.php')){
			$tmpinfo = "\n-- Create time:".date("Y-m-d H:i:s",$this->time)."\n";
			$this->lib('file')->vi($tmpinfo,$this->dir_data.$backfilename.'_tmpdata.php','file');
		}
		$this->lib('file')->vi(addslashes($msg),$this->dir_data.$backfilename.'_tmpdata.php','','ab');
		$fsize = filesize($this->dir_data.$backfilename.'_tmpdata.php');
		$update_dataid = false;
		if($fsize >= 2097152 || !$idlist[$new_startid]){
			$update_dataid = true;
			$newfile = $this->dir_data.$backfilename.'_'.intval($dataid).'.php';
			$this->lib('file')->mv($this->dir_data.$backfilename.'_tmpdata.php',$newfile);
		}
		if($update_dataid){
			$url .= "&dataid=".(intval($dataid)+1);
		}
		if(!$idlist[$new_startid]){
			$this->success(P_Lang('資料備份成功'),$this->url('sql','backlist'));
		}
		$tmparray = array('pageid'=>' <span class="red">'.($dataid+1).'</span> ','table'=>' <span class="red">'.$idlist[$startid].'</span> ');
		$this->success(P_Lang('正在備份資料，當前第{pageid}個檔案，正在備{table}相關資料',$tmparray),$url);
	}

	/**
	 * 備份列表，檢視當前系統備份的資料表資料
	**/
	public function backlist_f()
	{
		if(!$this->popedom['list'] && !$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您沒有許可權執行此操作'),$this->url('sql'));
		}
		$filelist = $this->lib('file')->ls($this->dir_data);
		if(!$filelist){
			$this->error(P_Lang('空資料，請檢查目錄：_data/'),$this->url("sql"));
		}
		$tmplist = array();
		$i=0;
		foreach($filelist AS $key=>$value){
			$bv = basename($value);
			if(substr($bv,0,3) == "sql" && strlen($bv) == 17 && substr($bv,-4) == '.php'){
				$tmplist[$i] = array('filename'=>$bv,'time'=>date("Y-m-d H:i:s",substr($bv,3,10)),'size'=>filesize($value),'id'=>substr($bv,3,10));
				$i++;
			}
			if(!file_exists($value) || substr($bv,0,3) != 'sql' || strpos($bv,'_') === false || substr($bv,-4) != '.php'){
				unset($filelist[$key]);
			}
		}
		if(!$tmplist){
			$this->error(P_Lang('沒有相備份資料'),$this->url('sql'));
		}
		foreach($tmplist as $key=>$value){
			foreach($filelist as $k=>$v){
				$tmp = basename($v);
				if(substr($tmp,0,13) == 'sql'.$value['id']){
					$value['size'] += filesize($v);
				}
			}
			$tmplist[$key] = $value;
		}
		foreach($tmplist as $key=>$value){
			$value['size_str'] = $this->lib('common')->num_format($value['size']);
			$tmplist[$key] = $value;
		}
		$this->assign("rslist",$tmplist);
		$this->view("sql_list");
	}

	/**
	 * 刪除備份資料
	 * @引數 id 指定要刪除的備份資料ID
	**/
	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您沒有許可權執行此操作'),$this->url('sql'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('沒有指定備份檔案'),$this->url('sql','backlist'));
		}
		$filelist = $this->lib('file')->ls($this->dir_data);
		if(!$filelist){
			$this->error(P_Lang('空資料，請檢查目錄：_data/'),$this->url("sql"));
		}
		$idlen = strlen($id);
		foreach($filelist AS $key=>$value){
			$bv = basename($value);
			if(substr($bv,0,13) == 'sql'.$id){
				$this->lib('file')->rm($value);
			}
		}
		$this->success(P_Lang('備份檔案刪除成功'),$this->url('sql','backlist'));
	}

	/**
	 * 恢復資料備份
	 * @引數 id 要恢復的資料ID
	**/
	public function recover_f()
	{
		if(!$this->popedom['recover'] && !$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您沒有許可權執行此操作'),$this->url('sql'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('沒有指定備份檔案'),$this->url('sql','backlist'));
		}
		$backfile = $this->dir_data.'sql'.$id.'.php';
		if(!file_exists($backfile)){
			$this->error(P_Lang('備份檔案不存在'),$this->url('sql','backlist'));
		}
		$session_string = '';
		if($this->config['engine']['session']['file'] == 'sql'){
			$session_string = session_encode();
			$session_id = $this->session->sessid();
		}
		$session = $_SESSION;
		$msg = $this->lib('file')->cat($backfile);
		$this->format_sql($msg);
		//判斷管理員是否存在
		$admin_rs = $this->model('admin')->get_one($session['admin_id'],'id');
		if(!$admin_rs || $admin_rs['account'] != $session['admin_account']){
			//寫入當前登入的管理員資訊
			if($admin_rs){
				$this->model('sql')->update_adm($session['admin_rs'],$session['admin_id']);
			}else{
				$insert_id = $this->model('sql')->update_adm($session['admin_rs']);
				$session['admin_id'] = $insert_id;
			}
		}
		if($session_string && $session_id){
			$this->model('sql')->update_session($session_id,$session_string);
		}
		//更新相應的SESSION資訊，防止被退出
		$_SESSION = $session;
		$this->success(P_Lang('表結構資料修復成功，正在修復內容資料，請稍候！'),$this->url('sql','recover_data','id='.$id."&startid=0"));
	}

	/**
	 * 恢復備份檔案中的其他資料
	 * @引數 id 要恢復的資料ID
	 * @引數 startid 開始ID，從0記起
	**/
	public function recover_data_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您沒有許可權執行此操作'),$this->url('sql'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('沒有指定備份檔案'),$this->url('sql','backlist'));
		}
		$startid = $this->get('startid','int');
		$backfile = $this->dir_data.'sql'.$id.'_'.$startid.'.php';
		if(!file_exists($backfile)){
			$this->success(P_Lang('資料恢復完成'),$this->url('sql','backlist'));
		}
		$msg = $this->lib('file')->cat($backfile);
		$this->format_sql($msg);
		$new_startid = $startid + 1;
		$newfile = $this->dir_data.'sql'.$id.'_'.$new_startid.'.php';
		if(!file_exists($newfile)){
			$this->success(P_Lang('資料恢復完成'),$this->url('sql','backlist'));
		}
		$tmparray = array('pageid'=>' <span class="red">'.($startid+1).'</span>');
		$this->success(P_Lang("正在恢復資料，正在恢復第{pageid}個檔案，請稍候…",$tmparray),$this->url('sql','recover_data','id='.$id.'&startid='.$new_startid));
	}

	/**
	 * 格式化SQL語句
	 * @引數 $sql 要格式化的資料
	**/
	private function format_sql($sql)
	{
		$sql = str_replace("\r","\n",$sql);
		$list = explode(";\n",trim($sql));
		$update_admin = false;
		foreach($list as $key=>$value){
			if(!$value || !trim($value)){
				continue;
			}
			$vlist = explode("\n",trim($value));
			$tmpsql = '';
			foreach($vlist as $k=>$v){
				if(!$v || !trim($v)){
					continue;
				}
				$v = trim($v);
				if(substr($v,0,1) != '#' && substr($v,0,2) != '--'){
					$tmpsql .= $v;
				}
			}
			if($tmpsql){
				if(strpos($tmpsql,'INSERT INTO '.$this->db->prefix.'adm') !== false){
					$sql = "TRUNCATE TABLE `".$this->db->prefix."adm`";
					$this->db->query($sql);
					$update_admin = true;
				}
				$this->model('sql')->query($tmpsql);
			}
		}
		if($update_admin){
			$admin_rs = $this->model('admin')->get_one($this->session->val('admin_id'),'id');
			if(!$admin_rs || $admin_rs['account'] != $this->session->val('admin_account')){
				if($admin_rs){
					$this->model('sql')->update_adm($this->session->val('admin_rs'),$this->session->val('admin_id'));
				}else{
					$insert_id = $this->model('sql')->update_adm($this->session->val('admin_rs'));
					$this->session->assign('admin_id',$insert_id);
				}
			}
		}
		return true;
	}

	public function show_f()
	{
		$tbl = $this->get('table');
		if(!$tbl){
			$this->error(P_Lang('未指定表名'));
		}
		$rslist = $this->model('sql')->table_info($tbl);
		$this->assign('rslist',$rslist);
		$this->view('sql_show');
	}

	public function table_delete_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$tbl = $this->get('tbl');
		if(!$tbl){
			$this->error(P_Lang('未指定表名'));
		}
		$length = strlen($this->db->prefix);
		if(substr($tbl,0,$length) == $this->db->prefix){
			$this->error(P_Lang('官網字首的系統表不支援刪除'));
		}
		$this->model('sql')->table_delete($tbl);
		$this->success();
	}
}