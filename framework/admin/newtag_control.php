<?php
/**
 * Tag標籤管理工具
 * @package phpok
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年04月20日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class newtag_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("tag");
		$this->assign("popedom",$this->popedom);
		$this->model('tag')->site_id($_SESSION['admin_site_id']);
	}

	/**
	 * 標籤管理列表
	 * @引數 keywords 搜尋關鍵字
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$pageurl = $this->url('tag');
		$keywords = $this->get('keywords');
		$condition = "1=1";
		if($keywords){
			$condition .= " AND title LIKE '%".$keywords."%' ";
			$pageurl .= "&title=".rawurlencode($keywords);
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid - 1) * $psize;
		$total = $this->model('tag')->get_total($condition);
		if($total>0){
			$rslist = $this->model('tag')->get_list($condition,$offset,$psize);
			$string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上一頁').'&next='.P_Lang('下一頁').'&last='.P_Lang('尾頁').'&half=5';
			$string.= '&add='.P_Lang('數量：').'(total)/(psize)'.P_Lang('，').P_Lang('頁碼：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("rslist",$rslist);
			$this->assign('pagelist',$pagelist);
		}
		$this->view('tag_index');
	}

	/**
	 * 新增或編輯標籤
	**/
	public function set_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
			$rs = $this->model('tag')->get_one($id);
			$this->assign('rs',$rs);
			$this->assign('id',$id);
		}else{
			if(!$this->popedom['add']){
				$this->error(P_Lang('您沒有許可權執行此操作'));
			}
		}
		$this->view("tag_set");
	}

	/**
	 * 儲存標籤資料
	**/
	public function save_f()
	{
		$id = $this->get('id','int');
		$popedom = $id ? 'modify' : 'add';
		if(!$this->popedom[$popedom]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('關鍵字名稱不能為空'));
		}
		$chk = $this->model('tag')->chk_title($title,$id);
		if($chk){
			$this->error(P_Lang('關鍵字已存在，請檢查'));
		}
		$data = array('title'=>$title,'url'=>$this->get('url'),'target'=>$this->get('target','int'));
		$data['site_id'] = $this->session->val('admin_site_id');
		$data['alt'] = $this->get('alt');
		$data['is_global'] = $this->get('is_global','int');
		$data['replace_count'] = $this->get('replace_count','int');
		if($id){
			$this->model('tag')->save($data,$id);
			$this->success();
		}
		$insert_id = $this->model('tag')->save($data);
		if(!$insert_id){
			$this->error(P_Lang('新增失敗，請檢查'));
		}
		$this->success();
	}

	/**
	 * 刪除標籤
	 * @引數 id 要刪除的標籤對應的ID
	**/
	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('tag')->delete($id);
		$this->success();
	}

	/**
	 * 配置Tag標籤管理引數
	**/
	public function config_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$rs = $this->model('tag')->config();
		if($rs){
			$this->assign('rs',$rs);
		}
		$this->view("tag_config");
	}

	/**
	 * 儲存配置資訊
	**/
	public function config_save_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$data = array();
		$data['separator'] = $this->get('separator');
		if(!$data['separator']){
			$data['separator'] = '|';
		}
		$data['count'] = $this->get('count','int');
		$this->model('tag')->config($data);
		$this->success();
	}

	/**
	 * 彈出窗選擇
	**/
	public function open_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$rs = $this->model('tag')->config();
		if($rs){
			$this->assign('rs',$rs);
		}
		$pageurl = $this->url('tag','open');
		$keywords = $this->get('keywords');
		$condition = "1=1";
		if($keywords){
			$condition .= " AND title LIKE '%".$keywords."%' ";
			$pageurl .= "&title=".rawurlencode($keywords);
		}
		$psize = 80;
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid - 1) * $psize;
		$total = $this->model('tag')->get_total($condition);
		if($total>0){
			$rslist = $this->model('tag')->get_list($condition,$offset,$psize);
			$string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上一頁').'&next='.P_Lang('下一頁').'&last='.P_Lang('尾頁').'&half=5';
			$string.= '&add='.P_Lang('數量：').'(total)/(psize)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("rslist",$rslist);
			$this->assign('pagelist',$pagelist);
		}
		$this->view('tag_open');
	}
}