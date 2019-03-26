<?php
/**
 * 表單釋出/修改頁
 * @package phpok\www
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年08月28日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class post_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$this->model('popedom')->site_id($this->site['id']);
		$groupid = $this->model('usergroup')->group_id($this->session->val('user_id'));
		if(!$groupid){
			$this->error(P_Lang('無法獲取前端使用者組資訊'));
		}
		$this->user_groupid = $groupid;
	}

	/**
	 * 內容釋出頁
	**/
	public function index_f()
	{
		$id = $this->get("id");
		$pid = $this->get('pid','int');
		if(!$id && !$pid){
			$this->error(P_Lang('未指定專案'));
		}
		$project_rs = $this->call->phpok('_project',array("phpok"=>$id,'pid'=>$pid));
		if(!$project_rs || !$project_rs['module']){
			$this->error(P_Lang("專案不符合要求"));
		}
		$err_url = $project_rs['url'];
		if(!$project_rs['post_status']){
			$this->error(P_Lang('專案未啟用釋出功能，聯絡管理員啟用此功能'),$err_url,10);
		}
		$project_rs['url'] = $this->url('post',$project_rs['identifier']);
		$this->assign("page_rs",$project_rs);
		$group_rs = $this->model('usergroup')->get_one($this->user_groupid);
		if(!$this->model('popedom')->check($project_rs['id'],$this->user_groupid,'post')){
			$this->error(P_Lang('您的級別（{grouptitle}）沒有釋出許可權，請聯絡我們的客服',array('grouptitle'=>$group_rs['title'])),$err_url,10);
		}
		//繫結分類資訊
		if($project_rs['cate']){
			$catelist = array();
			$cate_all = $this->model("cate")->cate_all($project_rs['site_id']);
			$this->model("cate")->sublist($catelist,$project_rs['cate'],$cate_all);
			$this->assign("catelist",$catelist);
		}
		$cateid = $this->get("cateid","int");
		if($cateid){
			$cate_rs = $this->call->phpok('_cate',array('pid'=>$project_rs['id'],'cateid'=>$cateid,'cate_ext'=>true));
			$this->assign("cate_rs",$cate_rs);
		}else{
			$cate = $this->get('cate');
			if($cate){
				$cate_rs = $this->call->phpok('_cate',array('pid'=>$project_rs['id'],'cate'=>$cate,'cate_ext'=>true));
				$this->assign("cate_rs",$cate_rs);
			}
		}
		
		//擴充套件欄位
		$ext_list = $this->model('module')->fields_all($project_rs["module"],"identifier");
		$extlist = array();
		foreach(($ext_list ? $ext_list : array()) AS $key=>$value){
			if(!$value['is_front']){
				continue;
			}
			if($value["ext"]){
				$ext = unserialize($value["ext"]);
				foreach($ext AS $k=>$v){
					$value[$k] = $v;
				}
			}
			$extlist[] = $this->lib('form')->format($value);
		}
		$this->assign("extlist",$extlist);
		$tpl = $project_rs['post_tpl'] ? $project_rs['post_tpl'] : $project_rs['identifier'].'_post';
		if(!$this->tpl->check_exists($tpl)){
			$tpl = 'post_add';
			if(!$this->tpl->check_exists($tpl)){
				error(P_Lang('未配置釋出模板，聯絡管理員進行配置'));
			}
		}

		$_back = $this->get("_back");
		if(!$_back){
			$_back = $this->lib('server')->referer();
		}
		if(!$_back){
			$_back = $this->url($project_rs['identifier'],$cate_rs['identifier']);
		}
		$this->assign('_back',$_back);
		//判斷是否加驗證碼
		$this->assign('is_vcode',$this->model('site')->vcode($project_rs['id'],'add'));
		$this->view($tpl);
	}

	/**
	 * 編輯主題資訊
	**/
	public function edit_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非會員不能操作此資訊'),$this->url,10);
		}
		$_back = $this->get("_back");
		if(!$_back){
			$_back = $this->lib('server')->referer();
			if(!$_back){
				$_back = $this->url;
			}
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'),$_back);
		}
		$this->assign('id',$id);
		$rs = $this->model('content')->get_one($id,0);
		if(!$rs){
			$this->error(P_Lang('內容資訊不存在'),$_back);
		}
		if($rs['user_id'] != $this->session->val('user_id')){
			$this->error(P_Lang('您沒有修改此內容許可權'),$_back);
		}
		//獲取專案資訊
		$project_rs = $this->call->phpok('_project','pid='.$rs['project_id']);
		if(!$project_rs || !$project_rs['module']){
			$this->error(P_Lang('專案不符合要求'),$_back);
		}
		$project_rs['url'] = $this->url('usercp','list','id='.$project_rs['identifier']);
		$this->assign("page_rs",$project_rs);

		//繫結分類資訊
		if($project_rs['cate']){
			$catelist = array();
			$cate_all = $this->model("cate")->cate_all($project_rs['site_id']);
			$this->model("cate")->sublist($catelist,$project_rs['cate'],$cate_all);
			$this->assign("catelist",$catelist);
		}
		if($rs['cate_id']){
			$cate_rs = $this->model("cate")->get_one($rs['cate_id'],"id",$project_rs['site_id']);
			$this->assign("cate_rs",$cate_rs);
		}
	
		//擴充套件欄位
		$ext_list = $this->model('module')->fields_all($project_rs["module"],"identifier");
		$extlist = array();
		foreach(($ext_list ? $ext_list : array()) AS $key=>$value){
			if(!$value['is_front']){
				continue;
			}
			if($value["ext"]){
				$ext = unserialize($value["ext"]);
				foreach($ext AS $k=>$v){
					$value[$k] = $v;
				}
			}
			$value['content'] = $rs[$value['identifier']];
			$extlist[] = $this->lib('form')->format($value);
		}
		$this->assign("extlist",$extlist);
		$this->assign('rs',$rs);
		$tpl = $project_rs['post_tpl'] ? $project_rs['post_tpl'].'_edit' : $project_rs['identifier'].'_post_edit';
		if(!$this->tpl->check_exists($tpl)){
			$tpl = 'post_edit';
			if(!$this->tpl->check_exists($tpl)){
				$this->error(P_Lang('缺少編輯模板'));
			}
		}
		$this->assign('is_vcode',$this->model('site')->vcode($project_rs['id'],'edit'));
		$this->view($tpl);
	}
}