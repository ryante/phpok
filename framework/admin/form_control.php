<?php
/**
 * 自定義表單的欄位非同步處理
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年06月13日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class form_control extends phpok_control
{
    const KEYI_PROJECT_ID = 2; //科仪项目 ID
    const LVZHU_PROJECT_ID = 4; //吕祖项目 ID
    const KEYI_GALLERY_LIB_MODULEID = 8; //科仪图库MODULE ID
    const LVZHU_GALLERY_LIB_MODULEID = 10; // 吕祖图库MODULE ID
	function __construct()
	{
		parent::control();
	}

	public function config_f()
	{
		$id = $this->get("id");
		if(!$id){
			exit(P_Lang('未指定ID'));
		}
		$eid = $this->get("eid");
		$etype = $this->get("etype");
		if(!$etype){
			$etype = "ext";
		}
		if($eid && $etype) {
			$this->_getinfo($eid,$etype);
		}
		$this->lib('form')->config($id);
	}

	private function _getinfo($eid=0,$etype='ext')
	{
		if(!$eid){
			return false;
		}
		if($etype == "fields"){
			$rs = $this->model('fields')->default_one($eid);
			if($rs && $rs['ext'] && is_array($rs['ext'])){
				foreach($rs['ext'] as $key=>$value){
					$rs[$key] = $value;
				}
			}
		}elseif($etype == "module"){
			$rs = $this->model('module')->field_one($eid);
		}elseif($etype == "user"){
			$rs = $this->model('user')->field_one($eid);
		}else{
			$rs = $this->model('ext')->get_one($eid);
		}
		if($rs["ext"] && is_string($rs['ext'])){
			$ext = unserialize($rs["ext"]);
			if(!$ext){
				$ext = array();
			}
			foreach($ext as $key=>$value){
				$rs[$key] = $value;
			}
		}
		$this->assign('rs',$rs);
		$this->assign('etype',$etype);
		$this->assign('eid',$eid);
		return $rs;
	}

	/**
	 * 專案欄位資訊
	**/
	public function project_fields_f()
	{
		$this->config('is_ajax',true);
		$pid = $this->get('pid','int');
		if(!$pid){
			$this->error(P_Lang('未指定專案ID'));
		}
		$project = $this->model('project')->get_one($pid);
		if(!$project){
			$this->error(P_Lang('專案資訊不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('專案未繫結模組'));
		}
		$eid = $this->get('eid','int');
		$etype = $this->get('etype');
		if(!$etype){
			$etype = 'ext';
		}
		$rs = array();
		if($eid && $etype){
			$rs = $this->_getinfo($eid,$etype);
		}
		$module = $this->model('module')->get_one($project['module']);
		$flist = array();
		if(!$module['mtype']){
			$tmptitle = $project['alias_title'] ? $project['alias_title'] : P_Lang('主題');
			$flist['title'] = array('title'=>$tmptitle,'status'=>false);
		}
		$elist = $this->model('module')->fields_all($project['module']);
		if($elist){
			foreach($elist as $key=>$value){
				$flist[$value['identifier']] = array('title'=>$value['title'],'status'=>false);
			}
		}
		if(!$flist){
			$this->success();
		}
		$form_show_editing = $rs['form_show_editing'] ? explode(",",$rs['form_show_editing']) : array();
		$data = array('show'=>$flist,'used'=>$flist);
		if($form_show_editing){
			foreach($data['show'] as $key=>$value){
				if(in_array($key,$form_show_editing)){
					$value['status'] = true;
					$data['show'][$key] = $value;
				}
			}
		}
		$form_field_used = $rs['form_field_used'] ? explode(",",$rs['form_field_used']) : array();
		if($form_field_used){
			foreach($data['used'] as $key=>$value){
				if(in_array($key,$form_show_editing)){
					$value['status'] = true;
					$data['used'][$key] = $value;
				}
			}
		}
		$this->success($data);
	}

	/**
	 * 專案主題快速新增及修改
	**/
	public function quickadd_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定欄位ID'));
		}
		$field = $this->model('module')->field_one($id);
		if(!$field){
			$this->error(P_Lang('欄位資訊不存在'));
		}
		$ext = ($field['ext'] && is_string($field['ext'])) ? unserialize($field['ext']) : array();
		if(!$ext || !$ext['form_pid']){
			$this->error(P_Lang('擴充套件專案沒有配置成功'));
		}
		$pid = $ext['form_pid'];
		$identifier = $field['identifier'];
		if(!$identifier){
			$this->error(P_Lang('配置無效，未指定標識'));
		}
		$this->assign('id',$id);
		$this->assign('pid',$pid);
		$this->assign('identifier',$identifier);
		$project = $this->model('project')->get_one($pid,false);
		if(!$project){
			$this->error(P_Lang('專案不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('專案沒有繫結模組'));
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error(P_Lang('模組資訊不存在，請檢查'));
		}
		$this->assign('p_rs',$project);
		$this->assign('m_rs',$module);
		$tid = $this->get('tid','int');
		if($tid){
			if($module['mtype']){
				$rs = $this->model('list')->single_one($tid,$project['module']);
			}else{
				$rs = $this->model('list')->get_one($tid,false);
			}
			$this->assign('rs',$rs);
		}
		$ext_list = $this->model('module')->fields_all($module['id']);
		$extlist = array();
		foreach(($ext_list ? $ext_list : array()) as $key=>$value){
			if($value["ext"] && is_string($value['ext'])){
				$ext = unserialize($value["ext"]);
				$value = array_merge($value,($ext ? $ext : array()));
			}
			if($rs && $rs[$value["identifier"]]){
				$value["content"] = $rs[$value["identifier"]];
			}
			$extlist[] = $this->lib('form')->format($value);
		}
		$this->assign('extlist',$extlist);
		$this->view('form_quickadd');
	}

	/**
	 * 快速新增對應的儲存操作
	**/
	public function quick_save_f()
	{
		$this->config('is_ajax',true);
		$pid = $this->get('pid','int');
		if(!$pid){
			$this->error(P_Lang('未指定專案'));
		}
		$project = $this->model('project')->get_one($pid,false);
		if(!$project){
			$this->error(P_Lang('專案不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('專案沒有繫結模組'));
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error(P_Lang('模組資訊不存在，請檢查'));
		}
		$extlist = $this->model('module')->fields_all($project["module"]);
 		if(!$extlist){
	 		$extlist = array();
 		}
		$id = $this->get('tid','int');
		//獨立執行
		if($module['mtype']){
			if($id){
				$rs = $this->model('list')->single_one($id,$project['module']);
			}
			$array = array();
			$array["project_id"] = $project['id'];
			$array['site_id'] = $project['site_id'];
			foreach($extlist as $key=>$value){
				if($rs[$value['identifier']]){
					$value['content'] = $rs[$value['identifier']];
				}
				$array[$value['identifier']] = $this->lib('form')->get($value);
			}
			if($id){
				$array['id'] = $id;
				$state = $this->model('list')->single_save($array,$project["module"]);
				if(!$state){
					$this->error(P_Lang('更新資料失敗，請檢查'));
				}
			}else{
				$id = $this->model('list')->single_save($array,$project["module"]);
				if(!$id){
					$this->error(P_Lang('儲存資料失敗，請檢查'));
				}
			}
			$this->success($id);
		}
		$title = $this->get('title');
		if(!$title){
			$tmptitle = $project['alias_title'] ? $project['alias_title'] : P_Lang('主題');
			$this->error(P_Lang('{title}不能為空',array('title'=>$tmptitle)));
		}
		$array = array('title'=>$title);
		if(!$id){
			$array["dateline"] = $this->time;
			$array['project_id'] = $project['id'];
			$array["status"] = 1;
			$array["module_id"] = $project["module"];
			$array["site_id"] = $project["site_id"];
			$id = $this->model('list')->save($array);
			if(!$id){
				$this->error(P_Lang('儲存資訊失敗，請聯絡管理員'));
			}
		}else{
			$status = $this->model('list')->save($array,$id);
			if(!$status){
				$this->error(P_Lang('更新資訊失敗'));
			}
			$rs = $this->model('list')->get_one($id);
		}
		//更新擴充套件表資訊
 		$tmplist = array();
 		$tmplist["id"] = $id;
 		$tmplist["site_id"] = $project["site_id"];
 		$tmplist["project_id"] = $project['id'];
		foreach($extlist as $key=>$value){
			if($rs[$value['identifier']]){
				$value['content'] = $rs[$value['identifier']];
			}
			$tmplist[$value["identifier"]] = $this->lib('form')->get($value);
		}
		$this->model('list')->save_ext($tmplist,$project["module"]);
		$this->success($id);
	}

	/**
	 * 過載入擴展信息
	**/
	public function redata_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定欄位ID'));
		}
		$field = $this->model('module')->field_one($id);
		if(!$field){
			$this->error(P_Lang('欄位資訊不存在'));
		}
		$ext = ($field['ext'] && is_string($field['ext'])) ? unserialize($field['ext']) : array();
		if(!$ext || !$ext['form_pid']){
			$this->error(P_Lang('擴充套件專案沒有配置成功'));
		}
		$pid = $ext['form_pid'];
		$identifier = $field['identifier'];
		if(!$identifier){
			$this->error(P_Lang('配置無效，未指定標識'));
		}
		$this->assign('_id',$id);
		$this->assign('_identifier',$identifier);
		$project = $this->model('project')->get_one($pid,false);
		if(!$project){
			$this->error(P_Lang('專案不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('專案沒有繫結模組'));
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error(P_Lang('模組資訊不存在，請檢查'));
		}
		$this->assign('p_rs',$project);
		$this->assign('m_rs',$module);
		$content = $this->get('content');
		$html = $this->lib('form')->cls('extitle')->content_format($field,$content,$project,$module);
		$this->success($html);
	}

	/**
	 * 讀取主題列表
	**/
	public function quicklist_f()
	{
	    $tpl = "form_quicklist";
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定欄位ID'));
		}
		$field = $this->model('module')->field_one($id);
		if(!$field){
			$this->error(P_Lang('欄位資訊不存在'));
		}
		$ext = ($field['ext'] && is_string($field['ext'])) ? unserialize($field['ext']) : array();
		if(!$ext || !$ext['form_pid']){
			$this->error(P_Lang('擴充套件專案沒有配置成功'));
		}
		$pid = $ext['form_pid'];
		$layoutids = $ext['form_show_editing'] ? $ext['form_show_editing'] : array();
		$identifier = $field['identifier'];
		if(!$identifier){
			$this->error(P_Lang('配置無效，未指定標識'));
		}
		$this->assign('id',$id);
		$this->assign('identifier',$identifier);


        // new add 庙宇关联文库文献 2020年6月2日
        if ($id == 109) { // dj_field.id
            $this->relateDoc($id);
            return;
        }
        // new add end

		$project = $this->model('project')->get_one($pid,false);
		if(!$project){
			$this->error(P_Lang('專案不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('專案沒有繫結模組'));
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error(P_Lang('模組資訊不存在，請檢查'));
		}
		$this->assign('p_rs',$project);
		$this->assign('m_rs',$module);
		$mlist = $this->model('module')->fields_all($module['id'],"identifier");
		$pageurl = $this->url('form','quicklist','id='.$id);
		$ext = $this->get('ext');
		if($ext && is_array($ext)){
			foreach($ext as $key=>$value){
				$pageurl .= "&ext[".$key."]=".rawurlencode($value);
			}
		    $pageurl .=
			$this->assign('ext',$ext);
		}
		$psize = $this->config["psize"] ? $this->config["psize"] : "30";
		if($project['psize'] && $project['psize'] > $psize){
			$psize = $project['psize'];
		}
		if(!$this->config["pageid"]){
			$this->config["pageid"] = "pageid";
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		if($module['mtype']){
			$keywords = $this->get('keywords');
			$condition = "project_id='".$project['id']."' AND site_id='".$project['site_id']."' ";
			if($keywords){
				$clist = array();
				foreach($mlist as $key=>$value){
					$clist[] = $value['identifier']." LIKE '%".$keywords."%'";
				}
				$condition .= " AND (".implode(" OR ",$clist).") ";
				$pageurl .= "&keywords=".rawurlencode($keywords);
				$this->assign('keywords',$keywords);
			}
			if($ext && is_array($ext)){
				foreach($ext as $key=>$value){
					if($value != ''){
						$condition .= " AND ".$key."='".$value."'";
					}
				}
			}
			$total = $this->model('list')->single_count($module['id'],$condition);
			if($total>0){
				$rslist = $this->model('list')->single_list($module['id'],$condition,$offset,$psize,$project['orderby']);
			}
			if($mlist){
				$layout = array();
				foreach($mlist as $key=>$value){
					if($value['identifier'] && in_array($value['identifier'],$layoutids)){
						$layout[$value['identifier']] = $value['title'];
					}
				}
				$this->assign('layout',$layout);
			}
		}else{
			$this->model('list')->is_biz(false);
			$this->model('list')->multiple_cate(false);
			$this->model('list')->is_user(false);
			$keywords = $this->get('keywords');
			$condition = "l.project_id='".$project['id']."' AND l.site_id='".$project['site_id']."' ";

			$tmptitle = $project['alias_title'] ? $project['alias_title'] : P_Lang('主題');

			$layout = array();
			if(in_array('title',$layoutids)){
				$layout['title'] = $tmptitle;
			}
			foreach($mlist as $key=>$value){
				if($value['identifier'] && in_array($value['identifier'],$layoutids)){
					$layout[$value['identifier']] = $value['title'];
				}
			}
			$this->assign('layout',$layout);

			// 图库关联ebook  只显示某个库下的文献的EBOOK new do 2020年5月26日
            $this->relateEbook($id, $tpl, $condition, $mlist, $pageurl);
            // new do

			if($keywords){
				$clist = array();
				$clist[] = "l.title LIKE '%".$keywords."%'";
				if (!empty($mlist)) {
                    foreach($mlist as $key=>$value){
                        $clist[] = "ext.".$value['identifier']." LIKE '%".$keywords."%'";
                    }
                }
				$condition .= " AND (".implode(" OR ",$clist).") ";
				$pageurl .= "&keywords=".rawurlencode($keywords);
				$this->assign('keywords',$keywords);
			}
			if($ext && is_array($ext)){
				foreach($ext as $key=>$value){
					if($value != ''){
						$condition .= " AND ext.".$key."='".$value."'";
					}
				}
			}
			$total = $this->model('list')->get_total($module['id'],$condition);
			if($total>0){
				$rslist = $this->model('list')->get_list($module['id'],$condition,$offset,$psize,$project['orderby']);
				if($rslist){
					foreach($rslist as $key=>$value){
						$value['dateline'] = date("Y-m-d H:i",$value['dateline']);
						$rslist[$key] = $value;
					}
				}
			}

		}
		if($total>0){
			$string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上一頁').'&next='.P_Lang('下一頁').'&last='.P_Lang('尾頁').'&half=1';
			$string.= '&add='.P_Lang('數量：').'(total)/(psize)'.P_Lang('，').P_Lang('頁碼：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
			$this->assign("rslist",$rslist);
		}
		$maxcount = $this->get('maxcount','int');
		if(!$maxcount){
			$maxcount = 9999;
		}
		$this->assign('maxcount',$maxcount);
		$this->view($tpl);
	}

	/**
	 * 刪除操作
	**/
	public function quickdelete_f()
	{
		$fid = $this->get('fid','int');
		$id = $this->get('id','int');
		if(!$id || !$fid){
			$this->error(P_Lang('引數不完整，請檢查'));
		}
		$field = $this->model('module')->field_one($fid);
		if(!$field){
			$this->error(P_Lang('欄位資訊不存在'));
		}
		$delete = false;
		if($field['ext'] && is_string($field['ext'])){
			$field['ext'] = unserialize($field['ext']);
		}
		if($field['ext'] && $field['ext']['form_true_delete']){
			$delete = true;
		}
		if(!$delete){
			$this->success();
		}
		$pid = $field['ext']['form_pid'];
		if(!$pid){
			$this->error(P_Lang('欄位沒有配置好'));
		}
		$project = $this->model('project')->get_one($pid,false);
		if(!$project){
			$this->error(P_Lang('專案不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('專案沒有繫結模組'));
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error(P_Lang('模組資訊不存在，請檢查'));
		}
		if($module['mtype']){
			$rs = $this->model('list')->single_one($id,$project['module']);
			if(!$rs){
				$this->error(P_Lang('資料不存在'));
			}
			$this->model('list')->single_delete($id,$project['module']);
			$this->success();
		}
		$rs = $this->model('list')->get_one($id,false);
		if(!$rs){
			$this->error(P_Lang('資料不存在'));
		}
		$this->model('list')->delete($id,$project['module']);
		$this->success();
	}
	
	/**
	 * 預覽
	**/
	public function preview_f()
	{
		$id = $this->get('id','int');
		$pid = $this->get('pid','int');
		if(!$id || !$pid){
			$this->error(P_Lang('引數不完整，請檢查'));
		}
		$project = $this->model('project')->get_one($pid,false);
		if(!$project){
			$this->error(P_Lang('專案不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('專案沒有繫結模組'));
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module){
			$this->error(P_Lang('模組資訊不存在，請檢查'));
		}
		$this->assign('p_rs',$project);
		$this->assign('m_rs',$module);
		if($module['mtype']){
			$rs = $this->model('list')->single_one($id,$project['module']);
		}else{
			$rs = $this->model('list')->get_one($id,false);
		}
		if(!$rs){
			$this->error(P_Lang('資料不存在'));
		}
		$this->assign('rs',$rs);
		$this->assign("id",$rs["id"]);
		$mlist = $this->model('module')->fields_all($project["module"]);
		$this->assign('mlist',$mlist);
		$this->view('form_quickview');
	}

	/*
	 * note: 图库关联EBOOK图片
	 */
	public function relateEbook($fileId, &$tpl , &$condition, &$mlist, &$pageurl) {
	    //parent_project_id 模块在添加扩展模型的字段，里面有个预设值填写上父项目的ID
        $fileArr = [
            104 => self::KEYI_PROJECT_ID,
            111 => self::LVZHU_PROJECT_ID,
        ]; 
        $parentPid = $fileArr[$fileId];
        $docId = $this->get('doc_id');
        if (empty($docId) && empty($parentPid)) {
            return false;
        }
        $tpl = "form_quicklist_ebook";
        $mlist = [];
        $sonProjectList = [];
        if (!empty($parentPid)) {
            $sonProjects = $this->db->get_all("select id,title from dj_project where parent_id={$parentPid} and module!=" . self::KEYI_GALLERY_LIB_MODULEID . " and module !=" . self::LVZHU_GALLERY_LIB_MODULEID);
            if (empty($sonProjects)) {
                return false;
            }
            $projectIds = [];
            foreach ($sonProjects as $val) {
                $sonProjectList[$val['id']] = $val;
                $projectIds[] = $val['id'];
            }
            $projectIdStr = implode(",", $projectIds);
            $docLists = $this->db->get_all("select id,title,project_id from dj_list where project_id in ({$projectIdStr}) order by id desc");
        }
        $this->assign("son_project_lists", $sonProjectList);
        $this->assign("doc_lists", $docLists);
        $this->assign("doc_id", $docId);
        $this->assign("parent_project_id", $parentPid);
        $pageurl .= "&parent_project_id=" . $parentPid;
	    if (!empty($docId)) {
            $condition .= " and ext.lid = {$docId}";
            $pageurl .= "&doc_id=" . $docId;
        } else {
	        $docIds = [];
	        foreach ($docLists as $val) {
	           $docIds[] = $val['id'];
            }
            $docIdStr = implode(",", $docIds);
            $condition .= " and ext.lid in ({$docIdStr})";
        }
        return true;
    }


    /*
     * note: 图库关联文献/碑刻
     */
    public function relateDoc($id) {
        $tpl = "form_quicklist_doc";
        $projectId = $this->get('project_id');
        $keyword = $this->get('keyword');
        $projectLists = $this->model('project')->get_all_project($this->session->val('admin_site_id'));
        $this->assign("project_lists", $projectLists);
        $this->assign("project_id", $projectId);
        $this->assign("keyword", $keyword);
        $pageurl = $this->url('form','quicklist','id='.$id);
        $condition = " l.site_id=1 and l.module_id in (2,3,5)  ";
        if (!empty($projectId)) {
            $pageurl .= "&project_id=" . $projectId;
            $condition .= " and l.project_id = {$projectId}";
        }
        if (!empty($keyword)) {
            $pageurl .= "&keyword=" . $keyword;
            $condition .= " and l.title like  '%{$keyword}%'";
        }
        $total = $this->model('list')->get_all_total($condition);
        $psize = $this->config["psize"] ? $this->config["psize"] : "30";
        $pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		if($total>0){
            $rslist = $this->model('list')->get_all($condition,$offset,$psize);
			$string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上一頁').'&next='.P_Lang('下一頁').'&last='.P_Lang('尾頁').'&half=1';
			$string.= '&add='.P_Lang('數量：').'(total)/(psize)'.P_Lang('，').P_Lang('頁碼：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
			$this->assign("rslist",$rslist);
		}
		$maxcount = $this->get('maxcount','int');
		if(!$maxcount){
			$maxcount = 9999;
		}
		$this->assign('maxcount',$maxcount);
		$this->view($tpl);
    }

}
