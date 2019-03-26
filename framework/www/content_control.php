<?php
/**
 * 內容資訊
 * @package phpok\framework\www
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2017年11月17日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class content_control extends phpok_control
{
	private $user_groupid;
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

	/**
	 * 內容資訊，可傳遞引數：主題ID，分類識別符號及專案識別符號
	 */
	public function index_f()
	{
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定ID'),"","error");
		}
		$rs = $this->model('content')->get_one($id,true);
		if(!$rs){
			$this->error_404();
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
		$tplfile = array();
		if($project['parent_id']){
			$parent_rs = $this->call->phpok("_project",array('pid'=>$project['parent_id']));
			if(!$parent_rs || !$parent_rs['status']){
				$this->error(P_Lang('父級專案未啟用'));
			}
			$this->assign("parent_rs",$parent_rs);
			if($parent_rs['tpl_content']){
				$tplfile[8] = $parent_rs['tpl_content'];
			}
			$this->phpok_seo($parent_rs);
		}
		$rs['tag'] = $this->model('tag')->tag_list($rs['id'],'list');
		$rs = $this->content_format($rs);
		$taglist = array('tag'=>$rs['tag'],'list'=>array('title'=>$rs['title']));
		//如果未繫結網址
		if(!$rs['url']){
			$url_id = $rs['identifier'] ? $rs['identifier'] : $rs['id'];
			$tmpext = '&project='.$project['identifier'];
			if($project['cate'] && $rs['cate_id']){
				$tmpext.= '&cateid='.$rs['cate_id'];
			}
			$rs['url'] = $this->url($url_id,'',$tmpext,'www');
		}
		//讀取分類樹
		$rs['_catelist'] = $this->model('cate')->ext_catelist($rs['id']);
		if($rs['_catelist']){
			foreach($rs['_catelist'] as $k=>$v){
				$rs['_catelist'][$k]['url'] = $this->url($project['identifier'],$v['identifier'],'','www');
			}
		}
		$this->assign('page_rs',$project);
		$this->phpok_seo($project);
		
		if($rs['tpl']){
			$tplfile[0] = $rs['tpl'];
		}
		if($project['tpl_content']){
			$tplfile[7] = $project['tpl_content'];
		}
		if($rs['cate_id'] && $project['cate']){
			$cate_root_rs = $this->call->phpok('_cate',array('pid'=>$project['id'],'cateid'=>$project['cate']));
			if(!$cate_root_rs || !$cate_root_rs['status']){
				$this->error(P_Lang('根分類資訊不存在或未啟用'),$this->url,5);
			}
			$this->assign('cate_root_rs',$cate_root_rs);
			$this->phpok_seo($cate_root_rs);
			if($cate_root_rs['tpl_content']){
				$tplfile[6] = $cate_root_rs['tpl_content'];
			}
			//分類資訊
			$cate_rs = $this->call->phpok('_cate',array("pid"=>$project['id'],'cateid'=>$rs['cate_id']));
			if(!$cate_rs || !$cate_rs['status']){
				$this->error(P_Lang('分類資訊不存在或未啟用'),$this->url,5);
			}
			if($cate_rs['parent_id']){
				$cate_parent_rs = $this->call->phpok('_cate',array('pid'=>$project['id'],'cateid'=>$cate_rs['parent_id']));
				if(!$cate_parent_rs || !$cate_root_rs['status']){
					$this->error(P_Lang('父級分類資訊不存在或未啟用'),$this->url,5);
				}
				$this->assign('cate_parent_rs',$cate_parent_rs);
				$this->phpok_seo($cate_parent_rs);
				if($cate_parent_rs['tpl_content']){
					$tplfile[5] = $cate_parent_rs['tpl_content'];
				}
			}
			$this->assign("cate_rs",$cate_rs);
			$this->phpok_seo($cate_rs);
			if($cate_rs['tpl_content']){
				$tplfile[4] = $cate_rs['tpl_content'];
			}
		}
		$tplfile[9] = $project['identifier'].'_content';
		ksort($tplfile);
		$tpl = '';
		foreach($tplfile as $key=>$value){
			if($this->tpl->check_exists($value)){
				$tpl = $value;
				break;
			}
		}
		if(!$tpl){
			$this->error(P_Lang('未配置相應的模板'));
		}
		$this->model('list')->add_hits($rs["id"]);
		$rs['hits'] = $this->model('list')->get_hits($rs['id']);
		$this->phpok_seo($rs);
		$this->assign("rs",$rs);
		//判斷這個主題是否支援評論及評論驗證碼
		if($project['comment_status']){
			$vcode = $this->model('site')->vcode($project['id'],'comment');
			$this->assign('is_vcode',$vcode);
		}
		//是否增加積分
		if($this->session->val('user_id')){
			$this->model('wealth')->add_integral($rs['id'],$this->session->val('user_id'),'content',P_Lang('閱讀#{id}',array('id'=>$rs['id'])));
		}
		$this->view($tpl);
	}

	private function content_format($rs)
	{
		$flist = $this->model('module')->fields_all($rs['module_id']);
		if(!$flist){
			return $rs;
		}
		$page = $this->config['pageid'] ? $this->config['pageid'] : 'pageid';
		$pageid = $this->get($page,'int');
		if(!$pageid){
			$pageid = 1;
		}
		$this->assign('pageid',$pageid);
		foreach($flist as $key=>$value){
			if($value['form_type'] == 'editor'){
				$value['pageid'] = $pageid;
			}
			$rs[$value['identifier']] = $this->lib('form')->show($value,$rs[$value['identifier']]);
			if($value['form_type'] == 'url' && $rs[$value['identifier']] && $value['identifier'] != 'url' && !$rs['url']){
				$rs['url'] = $rs[$value['identifier']];
			}
			if($value['form_type'] == 'editor'){
				if(is_array($rs[$value['identifier']])){
					$rs[$value['identifier'].'_pagelist'] = $rs[$value['identifier']]['pagelist'];
					$rs[$value['identifier']] = $rs[$value['identifier']]['content'];
				}
				if($value['ext'] && $rs['tag']){
					$ext = unserialize($value['ext']);
					if($ext['inc_tag']){
						$taglist['list'][$value['identifier']] = $rs[$value['identifier']];
						$rs[$value['identifier']] = $this->model('tag')->tag_format($rs['tag'],$rs[$value['identifier']]);
					}
				}
			}
		}
		return $rs;
	}
}