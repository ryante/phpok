<?php
/***********************************************************
	備註：評論列表讀取
	版本：5.0.0
	官網：www.phpok.com
	作者：qinggan <qinggan@188.com>
	更新：2016年02月07日
***********************************************************/ 
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class comment_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$this->model('popedom')->siteid($this->site['id']);
		$groupid = $this->model('usergroup')->group_id($this->session->val('user_id'));
		if(!$groupid){
			$this->error(P_Lang('無法獲取前端使用者組資訊'));
		}
		$this->user_groupid = $groupid;
	}

	public function index_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定主題ID'));
		}
		$rs = $this->model('content')->get_one($id,true);
		if(!$rs){
			$this->error(P_Lang('內容不存在'),$this->url,5);
		}
		if(!$rs['project_id']){
			$this->error(P_Lang('未繫結專案'),$this->url,5);
		}
		if(!$rs['module_id']){
			$this->error(P_Lang('未繫結相應的模組'));
		}
		$project = $this->call->phpok('_project',array('pid'=>$rs['project_id']));
		if(!$project || !$project['status']){
			$this->error(P_Lang('專案不存在或未啟用'));
		}
		if(!$this->model('popedom')->check($project['id'],$this->user_groupid,'read')){
			$this->error(P_Lang('您沒有閱讀此文章許可權'));
		}
		$this->assign('page_rs',$project);
		$url_id = $rs['identifier'] ? $rs['identifier'] : $rs['id'];
		$tmpext = '&project='.$project['identifier'];
		if($project['cate'] && $rs['cate_id']){
			$tmpext.= '&cateid='.$rs['cate_id'];
		}
		$rs['url'] = $this->url($url_id,'',$tmpext,'www');
		$this->assign("rs",$rs);
		$psize = $project['psize'] ? $project['psize'] : $this->config['psize'];
		if(!$psize){
			$psize = 30;
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$comment = phpok('_comment','tid='.$rs['id'],'pageid='.$pageid,'psize='.$psize);
		if($comment){
			$total = $comment['total'];
			$this->assign('total',$total);
			$this->assign('psize',$psize);
			$this->assign('pageid',$pageid);
			$this->assign('pageurl',$this->url('comment','','id='.$rs['id']));
			$this->assign('rslist',$comment['rslist']);
			$this->assign('avatar',$comment['avatar']);
			$this->assign('nickname',$comment['user']);
		}
		$this->model('site')->site_id($this->site['id']);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'comment';
		}
		$this->assign('is_vcode',$this->model('site')->vcode($project['id'],'comment'));
		$this->view($tplfile);
	}
}