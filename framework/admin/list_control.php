<?php
/**
 * 內容控制器
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年07月09日
 **/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class list_control extends phpok_control
{
    public $popedom;
    public function __construct()
    {
        parent::control();
        $this->lib('form')->cssjs();
    }

    private function popedom_auto($pid)
    {
        //$this->popedom = appfile_popedom("list",$pid);
        $this->popedom = appfile_popedom("list",3);
        $this->assign("popedom",$this->popedom);
        return $this->popedom;
    }

    /**
     * 內容管理首頁
     **/
    public function index_f()
    {
        $site_id = $_SESSION["admin_site_id"];
        $rslist = $this->model('project')->get_all($this->session->val('admin_site_id'),0,"p.status=1 AND p.hidden=0");
        if(!$rslist){
            $rslist = array();
        }
        if(!$this->session->val('admin_rs.if_system')){
            if(!$this->session->val('admin_popedom')){
                $this->error(P_Lang('該管理員未配置許可權，請檢查'));
            }
            $condition = "parent_id>0 AND appfile='list' AND func=''";
            $p_rs = $this->model('sysmenu')->get_one_condition($condition);
            if(!$p_rs){
                $this->error(P_Lang('資料獲取異常，請檢查'));
            }
            $gid = $p_rs["id"];
            $popedom_list = $this->model('popedom')->get_all("gid='".$gid."' AND pid>0",false,false);
            if(!$popedom_list){
                $this->error(P_Lang('未配置站點內容許可權，請檢查'));
            }
            $popedom = array();
            foreach($popedom_list as $key=>$value){
                if(in_array($value["id"],$this->session->val('admin_popedom'))){
                    $popedom[$value["pid"]][$value["identifier"]] = true;
                }
            }
            foreach($rslist as $key=>$value){
                if(!$popedom[$value["id"]] || !$popedom[$value["id"]]["list"]){
                    unset($rslist[$key]);
                    continue;
                }
            }
        }
        $this->assign("rslist",$rslist);
        $this->view("list_index");
    }

    public function action_f()
    {
        $id = $this->get("id");
        if(!$id){
            $this->error(P_Lang('未指定ID'),$this->url("list"));
        }
        $this->popedom_auto($id);
        if(!$this->popedom["list"]){
            $this->error(P_Lang('您沒有許可權執行此操作'));
        }
        $rs = $this->model('project')->get_one($id);
        if(!$rs){
            $this->error(P_Lang('專案資訊不存在'),$this->url("list"));
        }
        $son_list = $this->model('project')->get_all($rs["site_id"],$id,"p.status=1 AND p.hidden=0");
        if($son_list){
            foreach($son_list as $key=>$value){
                $popedom = appfile_popedom("list",$value["id"]);
                if(!$popedom["list"]){
                   // unset($son_list[$key]);
                }
            }
        }
        if($son_list){
            $this->assign("project_list",$son_list);
        }
        if(!$rs['tag']){
            $rs['tag'] = $this->model('tag')->get_tags('p'.$rs['id']);
        }
        $this->assign("rs",$rs);
        $this->assign("id",$id);
        $this->assign("pid",$id);
        $plist = array($rs);
        if($rs["parent_id"]){
            $this->model('project')->get_parentlist($plist,$rs["parent_id"]);
            krsort($plist);
        }
        $this->assign("plist",$plist);
        $cateid = $this->get("cateid");
        if(!$cateid){
            $cateid = $rs["cate"];
        }
        if($cateid && $rs["cate"]){
            $show_parent_catelist = $cateid != $rs["cate"] ? $cateid : false;
            $catelist = $this->model('cate')->get_sonlist($cateid);
            if(!$catelist){
                $cate_rs = $this->model('cate')->get_one($cateid);
                if($cate_rs["parent_id"]){
                    $catelist = $this->model('cate')->get_sonlist($cate_rs["parent_id"]);
                }
            }
            $this->assign("catelist",$catelist);
            $opt_catelist = $this->model('cate')->get_all($rs["site_id"],1,$rs["cate"]);
            $opt_catelist = $this->model('cate')->cate_option_list($opt_catelist);
            if($opt_catelist){
                $cateall = array();
                foreach($opt_catelist as $key=>$value){
                    $cateall[$value['id']] = $value['title'];
                }
                $this->assign('cateall',$cateall);
            }
            $this->assign("opt_catelist",$opt_catelist);
            if($show_parent_catelist){
                $parent_cate_rs = $this->model('cate')->get_one($show_parent_catelist);
                $this->assign('parent_cate_rs',$parent_cate_rs);
            }
            $this->assign("show_parent_catelist",$show_parent_catelist);
        }

        $m_rs = $this->model('module')->get_one($rs['module']);
        $contentTpl = "list_content";
        $setTpl = "list_set2";

        // new add start
        $myModuleId = empty($this->config['my_config']['my_module_id']) ? '' : $this->config['my_config']['my_module_id'];
        if (!empty($myModuleId)) {
            $myModuleArr = explode(",", $myModuleId);
            if (in_array($m_rs['id'], $myModuleArr)) {
                $this->assign('my_module', true);
                $contentTpl = "list_content_new";
            }
        }
        if ($rs['id'] == 4){
            $setTpl = "list_set2_new";
        }
        // new add end

        //設定內容列表
        if($rs["module"]){
            if($m_rs['mtype']){
                $this->standalone_app($rs,$m_rs);
            }
            $this->content_list($rs);
            $this->view($contentTpl);
        }
        $show_edit = true;
        $extlist = $this->model('ext')->ext_all('project-'.$id);
        if($extlist){
            $tmp = false;
            foreach($extlist as $key=>$value){
                if($value["ext"]){
                    $ext = unserialize($value["ext"]);
                    foreach($ext as $k=>$v){
                        $value[$k] = $v;
                    }
                }
                $tmp[] = $this->lib('form')->format($value);
                $this->lib('form')->cssjs($value);
            }
            $this->assign('extlist',$tmp);
        }
        $this->view($setTpl);
    }

    public function set_f()
    {
        $id = $this->get("id");
        if(!$id){
            $this->error(P_Lang('操作有錯誤'),$this->url("list"));
        }
        $this->popedom_auto($id);
        if(!$this->popedom["set"]){
            $this->error(P_Lang('您沒有許可權執行此操作'));
        }
        $rs = $this->model('project')->get_one($id);
        if(!$rs){
            $this->error(P_Lang('專案資訊不存在'),$this->url("list"),"error");
        }
        $this->assign("rs",$rs);
        $this->assign("id",$id);
        $this->assign("pid",$id);
        $extlist = $this->model('ext')->ext_all('project-'.$id);
        if($extlist){
            $tmp = false;
            foreach($extlist as $key=>$value){
                if($value["ext"]){
                    $ext = unserialize($value["ext"]);
                    foreach($ext as $k=>$v){
                        $value[$k] = $v;
                    }
                }
                $tmp[] = $this->lib('form')->format($value);
                $this->lib('form')->cssjs($value);
            }
            $this->assign('extlist',$tmp);
        }
        $tag_config = $this->model('tag')->config();
        $this->assign('tag_config',$tag_config);
        $this->view("list_set");
    }

    /**
     * 儲存專案資訊
     **/
    public function save_f()
    {
        $id = $this->get("id","int");
        if(!$id){
            $this->error(P_Lang('未指定專案ID'));
        }
        $this->popedom_auto($id);
        if(!$this->popedom["set"]){
            $this->error(P_Lang('您沒有許可權執行此操作'));
        }
        $title = $this->get("title");
        if(!$title){
            $this->error(P_Lang('名稱不能為空'));
        }
        $array = array("title"=>$title);
        $array["seo_title"] = $this->get("seo_title");
        $array["seo_keywords"] = $this->get("seo_keywords");
        $array["seo_desc"] = $this->get("seo_desc");
        $array['tag'] = $this->get('tag');
        $this->model('project')->save($array,$id);
        $this->model('tag')->update_tag($array['tag'],'p'.$id);
        ext_save("project-".$id);
        $this->model('temp')->clean("project-".$id,$_SESSION["admin_id"]);
        $this->success();
    }

    private function check_identifier($sign,$id=0,$site_id=0)
    {
        if(!$sign){
            return P_Lang('標識串不能為空');
        }
        $sign = strtolower($sign);
        //字串是否符合條件
        if(!preg_match("/[a-z][a-z0-9\_\-\.]+/",$sign)){
            return P_Lang('標識不符合系統要求，限字母、數字及下劃線（中劃線）且必須是字母開頭');
        }
        if(!$site_id){
            $site_id = $_SESSION["admin_site_id"];
        }
        $check = $this->model('id')->check_id($sign,$site_id,$id);
        if($check){
            return P_Lang('識別符號已被使用');
        }
        return 'ok';
    }

    /**
     * 獨立模組應用
     * @引數 $project 專案資訊，陣列
     * @引數 $module 模組資訊，陣列
     **/
    private function standalone_app($project,$module)
    {
        $pid = $project["id"];
        $mid = $project["module"];
        $layout = $module['layout'] ? explode(",",$module['layout']) : array();
        $this->assign("m_rs",$module);
        $m_list = $this->model('module')->fields_all($mid,"identifier");
        $layout_list = array();

        if($m_list){
            foreach($layout as $key=>$value){
                $layout_list[$value] = $m_list[$value]["title"];
            }
            $search_list = array();
            foreach($m_list as $key=>$value){
                if($value['search'] && ($value['search'] == 1 || $value['search'] == 2)){
                    $search_list[] = $value;
                }
            }
            $this->assign('search_list',$search_list);
        }
        $this->assign("ext_list",$m_list);
        $this->assign("layout",$layout_list);
        unset($layout_list);
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
        $condition = "site_id='".$project['site_id']."' AND project_id='".$project['id']."'";
        $pageurl = $this->url("list","action","id=".$pid);
        $keywords = $this->get('keywords');
        if($keywords){
            $this->assign('keywords',$keywords);
            if($m_list){
                foreach($m_list as $key=>$value){
                    $_condition = $this->model('form')->search($value,$keywords[$value['identifier']],false);
                    if($_condition){
                        $condition .= " AND (".$_condition.") ";
                        $pageurl .= "&keywords[".$value['identifier']."]=".rawurlencode($keywords[$value['identifier']]);
                    }
                }
            }
        }

        $keytype = $this->get('keytype');
        $keywords = $this->get("keywords");
        if($keytype && $keywords && trim($keywords)){
            $condition .= " AND ".$keytype." LIKE '%".$keywords."%'";
            $pageurl .= "&keywords=".rawurlencode($keywords)."&keytype=".rawurlencode($keytype);
            $this->assign("keywords",$keywords);
            $this->assign("keytype",$keytype);
        }
        $total = $this->model('list')->single_count($mid,$condition);
        if($total > 0){
            $rslist = $this->model('list')->single_list($mid,$condition,$offset,$psize,$project['orderby']);
            $string = P_Lang("home=首頁&prev=上壹頁&next=下壹頁&last=尾頁&half=5&add=數量：(total)/(psize)，頁碼：(num)/(total_page)&always=1");
            //$string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上壹頁').'&next='.P_Lang('下壹頁').'&last='.P_Lang('尾頁').'&half=5';
            //$string.= '&add='.P_Lang('數量：').'(total)/(psize)'.P_Lang('，').P_Lang('頁碼：').'(num)/(total_page)&always=1';
            $pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
            $this->assign("pagelist",$pagelist);
            $this->assign("rslist",$rslist);
        }
        $this->view('list_standalone');
    }
    //列表管理
    private function content_list($project_rs)
    {
        if(!$project_rs){
            $this->error(P_Lang('專案資訊不能為空'));
        }
        $pid = $project_rs["id"];
        $mid = $project_rs["module"];
        $site_id = $project_rs["site_id"];
        $orderby = $project_rs["orderby"];
        if(!$pid || !$mid || !$site_id){
            $this->error(P_Lang('資料異常'));
        }
        //讀取電商資料
        $this->model('list')->is_biz(($project_rs['is_biz'] ? true : false));
        //讀取多級分類
        $this->model('list')->multiple_cate(($project_rs['cate_multiple'] ? true : false));
        //繫結會員
        $this->model('list')->is_user(($project_rs['is_userid'] ? true : false));
        //內容佈局維護
        $layout = $m_list = array();
        $m_rs = $this->model('module')->get_one($mid);
        $m_list = $this->model('module')->fields_all($mid,"identifier");

        if($m_rs["layout"]) $layout = explode(",",$m_rs["layout"]);

        $this->assign("m_rs",$m_rs);
        //佈局
        $layout_list = array();
        foreach($layout as $key=>$value){
            if (empty($value)) {
                continue;
            }
            if($value == "hits"){
                $layout_list[$value] = P_Lang('檢視次數');
            }elseif($value == "dateline"){
                $layout_list[$value] = P_Lang('發布時間');
            }elseif($value == 'user_id'){
                $layout_list['user_id'] = P_Lang('會員賬號');
            }else{
                $layout_list[$value] = $m_list[$value]["title"];
            }
        }
        $this->assign("ext_list",$m_list);
        $this->assign("layout",$layout_list);
        unset($layout_list);
        $psize = $this->config["psize"] ? $this->config["psize"] : "30";
        if($project_rs['psize'] && $project_rs['psize'] > $psize){
            $psize = $project_rs['psize'];
        }
        if(!$this->config["pageid"]) $this->config["pageid"] = "pageid";
        $pageid = $this->get($this->config["pageid"],"int");
        if(!$pageid){
            $pageid = 1;
        }
        $offset = ($pageid-1) * $psize;
        $condition = "l.site_id='".$site_id."' AND l.project_id='".$pid."' AND l.parent_id='0' ";
        $pageurl = $this->url("list","action","id=".$pid);
        $keywords = $this->get('keywords');
        if($keywords){
            $this->assign('keywords',$keywords);
        }
        if($keywords && $keywords['cateid'] && $project_rs['cate']){
            $cate_rs = $this->model('cate')->get_one($keywords['cateid']);
            $catelist = array($cate_rs);
            $this->model('cate')->get_sublist($catelist,$keywords['cateid']);
            $cate_id_list = array();
            foreach($catelist as $key=>$value){
                $cate_id_list[] = $value["id"];
            }
            $cate_idstring = implode(",",$cate_id_list);
            if($project_rs['cate_multiple']){
                $condition .= " AND lc.cate_id IN(".$cate_idstring.")";
            }else{
                $condition .= " AND l.cate_id IN(".$cate_idstring.")";
            }

            $pageurl .= "&keywords[cateid]=".$keywords['cateid'];
        }
        if($keywords && $keywords['title']){
            $tmp_condition = array();
            $tmp = str_replace(' ','%',$keywords['title']);
            $tmp_condition[] = "l.title LIKE '%".$tmp."%'";
            if($project_rs['is_seo']){
                $tmp_condition[] = "l.seo_title LIKE '%".$tmp."%'";
                $tmp_condition[] = "l.seo_keywords LIKE '%".$tmp."%'";
                $tmp_condition[] = "l.seo_desc LIKE '%".$tmp."%'";
            }
            if($project['is_identifier']){
                $tmp_condition[] = "l.identifier LIKE '%".$tmp."%'";
            }
            $condition .= " AND (".implode(" OR ",$tmp_condition).") ";
            $pageurl .= "&keywords[title]=".rawurlencode($keywords['title']);
        }
        if($keywords && $keywords['tag'] && $project_rs['is_tag']){
            $keywords['tag'] = str_replace("，",",",$keywords['tag']);
            $tmplist = explode(",",$keywords['tag']);
            $tmp_condition = array();
            foreach($tmplist as $key=>$value){
                $tmp_condition[] = "l.tag LIKE '%".$value."%'";
            }
            $condition .= " AND (".implode(" OR ",$tmp_condition).") ";
            $pageurl .= "&keywords[tag]=".rawurlencode($keywords['tag']);
        }
        if($keywords && $keywords['user'] && $project_rs['is_userid']){
            $keywords['user'] = str_replace("，",",",$keywords['user']);
            $tmplist = explode(",",$keywords['user']);
            $tmp_condition = array();
            foreach($tmplist as $key=>$value){
                $tmp_condition[] = "u.user LIKE '%".$value."%'";
                $tmp_condition[] = "u.email LIKE '%".$value."%'";
                $tmp_condition[] = "u.mobile LIKE '%".$value."%'";
            }
            $condition .= " AND (".implode(" OR ",$tmp_condition).") ";
            $pageurl .= "&keywords[user]=".rawurlencode($keywords['user']);
        }
        if($keywords && $m_list){
            foreach($m_list as $key=>$value){
                $_condition = $this->model('form')->search($value,$keywords[$value['identifier']]);
                if($_condition){
                    $condition .= " AND (".$_condition.") ";
                    $pageurl .= "&keywords[".$value['identifier']."]=".rawurlencode($keywords[$value['identifier']]);
                }
            }
        }
        if($keywords && $keywords['attr']){
            $condition .= " AND FIND_IN_SET('".$keywords['attr']."',l.attr) ";
            $pageurl .= "&keywords[attr]=".rawurlencode($keywords['attr']);
        }
        if($keywords && $keywords['dateline_start']){
            $tmp = strtotime($keywords['dateline_start']);
            if($tmp){
                $condition .= " AND l.dateline>=".$tmp." ";
                $pageurl .= "&keywords[dateline_start]=".rawurlencode($keywords['dateline_start']);
            }
        }
        if($keywords && $keywords['dateline_stop']){
            $tmp = strtotime($keywords['dateline_stop']);
            if($tmp){
                $condition .= " AND l.dateline<=".$tmp." ";
                $pageurl .= "&keywords[dateline_stop]=".rawurlencode($keywords['dateline_stop']);
            }
        }
        if($keywords && $keywords['status']){
            if($keywords['status'] == 1){
                $condition .= ' AND l.status=1 ';
            }else{
                $condition .= ' AND l.status=0 ';
            }
            $pageurl .= "&keywords[status]=".$keywords['status'];
        }

        $orderby_search = $this->get('orderby_search');
        if($keywords && $keywords['orderby_search']){
            switch($keywords['orderby_search']){
                case "hits_hot":
                    $orderby = "l.hits DESC,l.sort ASC,l.id DESC";
                    break;
                case "hits_cold":
                    $orderby = "l.hits ASC,l.sort ASC,l.id DESC";
                    break;
                case "price_high":
                    $orderby = "b.price DESC,l.sort ASC,l.id DESC";
                    break;
                case "price_low":
                    $orderby = "b.price ASC,l.sort ASC,l.id DESC";
                    break;
                case "sort_max":
                    $orderby = "l.sort DESC,l.sort ASC,l.id DESC";
                    break;
                case "sort_min":
                    $orderby = "l.sort ASC,l.sort ASC,l.id DESC";
                    break;
                case "dateline_max":
                    $orderby = "l.dateline DESC,l.sort ASC,l.id DESC";
                    break;
                case "dateline_min":
                    $orderby = "l.dateline ASC,l.sort ASC,l.id DESC";
                    break;
                case "id_max":
                    $orderby = "l.id DESC";
                    break;
                case "id_min":
                    $orderby = "l.id ASC";
                    break;
            }
            $pageurl .= "&keywords[orderby_search]=".$keywords['orderby_search'];
        }
        //取得列表資訊
        $total = $this->model('list')->get_total($mid,$condition);
        if($total > 0){
            $rslist = $this->model('list')->get_list($mid,$condition,$offset,$psize,$orderby);
            $sub_idlist = $rslist ? array_keys($rslist) : array();
            $extcate_ids = $sub_idlist;
            if($project_rs['subtopics']){
                $sub_idstring = implode(",",$sub_idlist);
                $condition = "l.site_id='".$site_id."' AND l.project_id='".$pid."' AND l.parent_id IN(".$sub_idstring.") ";
                $sublist = $this->model('list')->get_list($mid,$condition,0,0,$orderby);
                if($sublist){
                    foreach($sublist as $key=>$value){
                        $rslist[$value["parent_id"]]["sonlist"][$value["id"]] = $value;
                        $extcate_ids[] = $value['id'];
                    }
                }
            }
            $extcate_ids = array_unique($extcate_ids);
            if($project_rs['cate'] && $project_rs['cate_multiple']){
                $clist = $this->model('list')->catelist($extcate_ids);
                $this->assign('clist',$clist);
            }
            if($project_rs['comment_status']){
                $comments = $this->model('reply')->comment_stat($extcate_ids);
                $this->assign('comments',$comments);
            }
            unset($sublist,$sub_idstring,$sub_idlist);
            $string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上壹頁').'&next='.P_Lang('下壹頁').'&last='.P_Lang('尾頁').'&half=5';
            $string.= '&add='.P_Lang('數量：').'(total)/(psize)'.P_Lang('，').P_Lang('頁碼：').'(num)/(total_page)&always=1';
            $pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
            $this->assign("pagelist",$pagelist);
            $this->assign("rslist",$rslist);
        }
        $attrlist = $this->model('list')->attr_list();
        $this->assign("attrlist",$attrlist);
        return true;
    }

    /**
     * 新增或編輯獨立模組下的內容
     * @引數 id 主題ID
     * @引數 pid 專案ID
     **/
    public function edit2_f()
    {
        $pid = $this->get("pid","int");
        if(!$pid){
            $this->error(P_Lang('操作異常，未指定專案'),$this->url("list"));
        }
        $project = $this->model('project')->get_one($pid);
        if(!$project){
            $this->error(P_Lang('專案資訊不存在'));
        }
        $this->assign('p_rs',$project);
        $this->assign('pid',$pid);
        $this->popedom_auto($pid);
        $plist = array($project);
        if($project["parent_id"]){
            $this->model('project')->get_parentlist($plist,$project["parent_id"]);
            krsort($plist);
        }
        $this->assign("plist",$plist);
        $id = $this->get('id','int');
        $popedom_id = $id ? 'modify' : 'add';
        if(!$this->popedom[$popedom_id]){
            $this->error(P_Lang('您沒有許可權執行此操作'));
        }
        $module = $this->model('module')->get_one($project['module']);
        $this->assign('m_rs',$module);
        if($id){
            $rs = $this->model('list')->single_one($id,$project['module']);
            if(!$rs){
                $this->error(P_Lang('主題資訊不存在'));
            }
            $this->assign('rs',$rs);
            $this->assign("id",$rs["id"]);
        }
        $tmplist = $this->model('module')->fields_all($project["module"]);
        if(!$tmplist){
            $tmplist = array();
        }
        $extlist = array();
        foreach($tmplist as $key=>$value){
            if($value["ext"] && is_string($value['ext'])){
                $ext = unserialize($value["ext"]);
                $value = array_merge($value,($ext ? $ext : array()));
            }
            $idlist[] = strtolower($value["identifier"]);
            if($rs && $rs[$value["identifier"]]){
                $value["content"] = $rs[$value["identifier"]];
            }
            $extlist[] = $this->lib('form')->format($value);
        }
        $this->assign("extlist",$extlist);
        $this->view('list_edit2');
    }

    /**
     * 新增或編輯內容，這裡的內容是帶模組的
     **/
    public function edit_f()
    {
        $id = $this->get("id","int");
        $pid = $this->get("pid","int");
        if(!$id && !$pid){
            $this->error(P_Lang('操作異常'),$this->url("list"));
        }
        if($id){
            $rs = $this->model('list')->get_one($id,false);
            $pid = $rs["project_id"];
            $extcate = $this->model('list')->ext_catelist($id);
            if(!$extcate){
                $extcate = array();
            }
        }else{
            $cateid = $this->get("cateid","int");
            $rs = $extcate = array();
            //判斷是否有臨時未儲存的資料
            $autosave = $this->lib('file')->cat($this->dir_data.'cache/autosave_'.$this->session->val('admin_id').'_'.$pid.'.php');
            if($autosave){
                $rs = unserialize($autosave);
                if($rs['dateline']){
                    $rs['dateline'] = strtotime($rs['dateline']);
                }
            }
            if($cateid){
                $rs["cate_id"] = $cateid;
                $extcate = array($cateid);
            }
            if($this->site['biz_main_service']){
                $rs['is_virtual'] = 1;
            }
        }
        if(!$pid){
            $this->error(P_Lang('操作異常'),$this->url("list"));
        }
        $this->popedom_auto($pid);
        $popedom_id = $id ? 'modify' : 'add';
        if(!$this->popedom[$popedom_id]){
            error(P_Lang('您沒有許可權執行此操作'),'','error');
        }
        $p_rs = $this->model('project')->get_one($pid);
        if(!$p_rs){
            error(P_Lang('操作異常'),$this->url("list"),"error");
        }
        $m_rs = $this->model('module')->get_one($p_rs["module"]);
        //讀取擴充套件屬性
        $ext_list = $this->model('module')->fields_all($p_rs["module"]);
        $extlist = array();
        foreach(($ext_list ? $ext_list : array()) as $key=>$value){
            if($value["ext"] && is_string($value['ext'])){
                $ext = unserialize($value["ext"]);
                $value = array_merge($value,($ext ? $ext : array()));
            }
            $idlist[] = strtolower($value["identifier"]);
            if($rs[$value["identifier"]]){
                $value["content"] = $rs[$value["identifier"]];
            }
            $extlist[] = $this->lib('form')->format($value);
        }
        if($id){
            $tmplist = $this->model('ext')->ext_all('list-'.$id,true);
        }else{
            $tmplist = $this->session->val('admin-add-list');
        }
        if($tmplist){
            foreach($tmplist as $key=>$value){
                if($value['ext']){
                    $ext = $value['ext'];
                    if(is_string($value['ext'])){
                        $ext = unserialize($value['ext']);
                    }
                    unset($value['ext']);
                    if($ext){
                        $value = array_merge($value,$ext);
                    }
                }
                if($this->popedom['ext']){
                    $value['is_edit'] = true;
                }
                $extlist[] = $this->lib('form')->format($value);
            }
        }
        $this->assign("extlist",$extlist);
        $this->assign("p_rs",$p_rs);
        $this->assign("m_rs",$m_rs);
        $this->assign("pid",$pid);
        $this->assign('extcate',$extcate);
        $plist = array($p_rs);
        if($p_rs["parent_id"]){
            $this->model('project')->get_parentlist($plist,$p_rs["parent_id"]);
            krsort($plist);
        }
        $this->assign("plist",$plist);
        if($rs["id"]){
            $this->assign("id",$rs["id"]);
        }
        if($p_rs["cate"]){
            $catelist = $this->model('cate')->get_all($p_rs["site_id"],1,$p_rs["cate"]);
            $catelist = $this->model('cate')->cate_option_list($catelist);
            $this->assign("catelist",$catelist);
        }
        if($p_rs['is_biz']){
            $currency_list = $this->model('currency')->get_list();
            if(!$rs['currency_id']){
                $rs['currency_id'] = $p_rs['currency_id'];
            }
            if($currency_list){
                $this->assign('currency_list',$currency_list);
                $currency = array();
                foreach($currency_list as $key=>$value){
                    if($value['id'] == $p_rs['currency_id']){
                        $currency = $value;
                    }
                }
                $this->assign('currency',$currency);
            }
            if($p_rs['freight']){
                $freight = $this->model('freight')->get_one($p_rs['freight']);
                $this->assign('freight',$freight);
            }
            if($p_rs['biz_attr']){
                $biz_attrlist = $this->model('options')->get_all();
                if($biz_attrlist){
                    $this->assign('biz_attrlist',$biz_attrlist);
                }
                //載入現有電商屬性
                if($rs['id']){
                    $tmplist = $this->model('list')->biz_attrlist($rs['id']);
                    if($tmplist){
                        $bizattrs = array();
                        foreach($tmplist as $key=>$value){
                            $bizattrs[] = $value['aid'];
                        }
                        if($bizattrs){
                            $bizattrs = array_unique($bizattrs);
                            $this->assign("_attr",implode(",",$bizattrs));
                        }

                    }
                }
            }
            $unitlist = $this->model('biz')->unitlist();
            $this->assign('unitlist',$unitlist['name']);
        }
        //判斷是否有父主題
        $parent_id = $this->get("parent_id","int");
        if($parent_id){
            $parent_rs = $this->model('list')->get_one($parent_id);
            if(!$rs["cate_id"]){
                $rs["cate_id"] = $parent_rs["cate_id"];
            }
            $this->assign("parent_rs",$parent_rs);
            $this->assign("parent_id",$parent_id);
        }
        $this->assign("rs",$rs);
        $this->model("list");
        if($p_rs['is_attr']){
            $attrlist = $this->model('list')->attr_list();
            if($attrlist){
                $attr = $rs['attr'] ? explode(",",$rs['attr']) : array();
                foreach($attrlist as $key=>$value){
                    $tmp = array('status'=>false,'val'=>$value);

                    if($attr && in_array($key,$attr)){
                        $tmp['status'] = true;
                    }
                    $attrlist[$key] = $tmp;
                }
                $this->assign("attrlist",$attrlist);
            }

        }


        // 擴充套件欄位管理
        if($this->popedom['ext']){
            $ext_module = $id ? 'list-'.$id : 'add-list';
            $this->assign("ext_module",$ext_module);
            $no_include = array('id','title','identifier');
            $used_fields = $this->model('list')->fields_all($p_rs['module'],$id,$this->session->val('admin-add-list'));
            $used_fields = $used_fields ? array_merge($no_include,$used_fields) : $no_include;
            $used_fields = array_unique($used_fields);
            $extfields = $this->model('fields')->fields_list($used_fields);
            $this->assign("extfields",$extfields);
        }

        //獲取標簽選項
        if($p_rs['is_tag']){
            $tag_config = $this->model('tag')->config();
            $this->assign('tag_config',$tag_config);
            if($tag_config['count']){
                $taglist = $this->model('tag')->tag_quick($tag_config['count']);
                $this->assign('taglist',$taglist);
            }
        }

        // new add start
        $myModuleId = empty($this->config['my_config']['my_module_id']) ? '' : $this->config['my_config']['my_module_id'];
        if (!empty($myModuleId)) {
            $myModuleArr = explode(",", $myModuleId);
            if (in_array($m_rs['id'], $myModuleArr)) {
                $this->view("list_edit_new");
            }
        }
        // new add end

        $this->view("list_edit");
    }

    public function ok_f()
    {
        $id = $this->get("id","int");
        $pid = $this->get("pid","int");
        $parent_id = $this->get("parent_id","int");
        if(!$pid && !$id){
            $this->json(P_Lang('操作異常，無法取得專案資訊'));
        }
        $is_add = true;
        if($id){
            $rs = $this->model('list')->get_one($id);
            $this->assign("rs",$rs);
            $pid = $rs["project_id"];
            $parent_id = $rs["parent_id"];
            $is_add = false;
        }
        if(!$pid){
            $this->json(P_Lang('操作異常，無法取得專案資訊'));
        }
        $this->popedom_auto($pid);
        if($id){
            if(!$this->popedom['modify']){
                $this->json(P_Lang('您沒有許可權執行此操作'));
            }
        }else{
            if(!$this->popedom['add']){
                $this->json(P_Lang('您沒有許可權執行此操作'));
            }
        }
        $p_rs = $this->model('project')->get_one($pid);
        if(!$p_rs){
            $this->json(P_Lang('操作異常，無法取得專案資訊'));
        }
        $array = array();
        $title = $this->get("title");
        if(!$title){
            $this->json(P_Lang('內容的主題不能為空'));
        }
        $array["title"] = $title;
        if($p_rs['cate']){
            $cate_id = $this->get("cate_id","int");
            if(!$cate_id){
                $this->json(P_Lang('主分類不能為空'));
            }
            $array["cate_id"] = $cate_id;
        }else{
            $array['cate_id'] = 0;
        }
        //更新標識串
        $array['identifier'] = $this->get("identifier");
        if(!$array['identifier'] && $p_rs['is_identifier'] == 2){
            $this->json(P_Lang('自定義標識不能為空，此項是系統設定必填項'));
        }
        if($array['identifier']){
            $check = $this->check_identifier($array['identifier'],$id,$p_rs["site_id"]);
            if($check != 'ok'){
                $this->json($check);
            }
        }
        if($p_rs['is_attr']){
            $attr = $this->get("attr");
            if($attr && is_array($attr)){
                $attr = implode(",",$attr);
            }
            $array["attr"] = $attr;
        }
        if($p_rs['is_tag']){
            $array["tag"] = $this->get("tag");
            if($array["tag"]){
                $array["tag"] = preg_replace("/(\x20{2,})/"," ",$array["tag"]);
            }
            if(!$array['tag'] && $p_rs['is_tag'] == 2){
                $this->json(P_Lang('Tag標簽不能為空'));
            }
        }else{
            $array['tag'] = '';
        }
        if($p_rs['is_userid']){
            $array['user_id'] = $this->get('user_id','int');
            if(!$array['user_id'] && $p_rs['is_userid'] == 2){
                $this->json(P_Lang('會員賬號不能為空'));
            }
        }
        if($p_rs['is_tpl_content']){
            $array['tpl'] = $this->get('tpl');
            if(!$array['tpl'] && $p_rs['is_tpl_content'] == 2){
                $this->json(P_Lang('自定義內容模板不能為空'));
            }
        }else{
            $array['tpl'] = '';
        }
        $array["parent_id"] = $parent_id;
        $dateline = $this->get("dateline","time");
        if(!$dateline){
            $dateline = $this->time;
        }
        $array["dateline"] = $dateline;

        // new add
        $array["status"] = empty($this->get("status","int")) ? 1 : $this->get("status","int");
        if($this->popedom["status"]){
            $array["status"] = $this->get("status","int");
        }
        $array["hidden"] = $this->get("hidden","int");
        $crontab = 0;
        if($dateline > $this->time && $array['status']){
            $array['hidden'] = 2;
            //加入定時取消操作
            $crontab = $dateline;
        }
        $array["hits"] = $this->get("hits","int");
        $array["sort"] = $this->get("sort","int");
        $array["seo_title"] = $this->get("seo_title");
        $array["seo_keywords"] = $this->get("seo_keywords");
        $array["seo_desc"] = $this->get("seo_desc");
        if(!$array["seo_title"] && $p_rs['is_seo'] == 3){
            $this->json(P_Lang('SEO標題不能為空'));
        }
        if(!$array["seo_keywords"] && $p_rs['is_seo'] == 3){
            $this->json(P_Lang('SEO關鍵字不能為空'));
        }
        if(!$array["seo_desc"] && $p_rs['is_seo'] == 3){
            $this->json(P_Lang('SEO描述不能為空'));
        }

        $array["project_id"] = $p_rs['id'];
        $array["module_id"] = $p_rs["module"];
        $array["site_id"] = $p_rs["site_id"];
        $array['integral'] = $this->get('integral','int');
        $tmpadd = false;
        if(!$id){
            $id = $this->model('list')->save($array);
            $tmpadd = true;
            $this->lib('file')->rm($this->dir_data.'cache/autosave_'.$this->session->val('admin_id').'_'.$p_rs['id'].'.php');
        }else{
            $this->model('list')->save($array,$id);
        }
        if(!$id){
            $this->json(P_Lang('儲存資料失敗，請檢查'));
        }
        $crontab_list = $this->lib('file')->ls($this->dir_data.'crontab');
        if($crontab_list){
            foreach($crontab_list as $key=>$value){
                $tmp = basename($value);
                $tmp = str_replace('.php','',$tmp);
                $tmplist = explode("-",$tmp);
                if(!$tmplist || count($tmplist) != 2 || $tmplist[1] != $id){
                    continue;
                }
                $this->lib('file')->rm($value);
            }
        }
        if($crontab){
            $this->lib('file')->vi($id,$this->dir_data.'crontab/'.$crontab.'-'.$id.'.php');
        }
        //儲存電商資訊
        if($p_rs['is_biz']){
            $biz = array('price'=>$this->get('price','float'),'currency_id'=>$this->get('currency_id','int'));
            $biz['weight'] = $this->get('weight','float');
            $biz['volume'] = $this->get('volume','float');
            $biz['unit'] = $this->get('unit');
            $biz['id'] = $id;
            $biz['is_virtual'] = $this->get('is_virtual','int');
            $this->model('list')->biz_save($biz);
            if($p_rs['biz_attr']){
                //刪除現有屬性
                $attr = $this->get('_biz_attr');
                if($attr){
                    $tmplist = explode(",",$attr);
                    $tmps = array();
                    foreach($tmplist as $key=>$value){
                        $attr_vid_list = $this->get("_attr_".$value);
                        $attr_weight_list = $this->get('_attr_weight_'.$value);
                        $attr_volume_list = $this->get('_attr_volume_'.$value);
                        $attr_price_list = $this->get('_attr_price_'.$value);
                        $attr_taxis_list = $this->get('_attr_taxis_'.$value);
                        if(!$attr_vid_list || !is_array($attr_vid_list)){
                            continue;
                        }

                        foreach($attr_vid_list as $k=>$v){
                            $tmp = array('aid'=>$value,'vid'=>$v);
                            $tmp['price'] = isset($attr_price_list[$v]) ? $attr_price_list[$v] : 0;
                            $tmp['weight'] = isset($attr_weight_list[$v]) ? $attr_weight_list[$v] : 0;
                            $tmp['volume'] = isset($attr_volume_list[$v]) ? $attr_volume_list[$v] : 0;
                            $tmp['taxis'] = isset($attr_taxis_list[$v]) ? $attr_taxis_list[$v] : 0;
                            $tmps[] = $tmp;
                            unset($tmp);
                        }
                    }
                    $this->model('list')->biz_attr_update($tmps,$id);
                    unset($tmps);
                }else{
                    $this->model('list')->biz_attr_delete($id);
                }
            }
        }
        //更新擴充套件分類
        $this->model('list')->list_cate_clear($id);
        if($cate_id){
            $ext_cate = $this->get('ext_cate_id');
            if(!$ext_cate){
                $ext_cate = array($cate_id);
            }else{
                $ext_cate = array_merge($ext_cate,array($cate_id));
                $ext_cate = array_unique($ext_cate);
            }
            $this->model('list')->save_ext_cate($id,$ext_cate);
        }
        //更新Tag標簽
        $this->model('tag')->update_tag($array['tag'],$id);
        if($p_rs["module"]){
            $ext_list = $this->model('module')->fields_all($p_rs["module"]);
            $tmplist = array();
            $tmplist["id"] = $id;
            $tmplist["site_id"] = $p_rs["site_id"];
            $tmplist["project_id"] = $pid;
            $tmplist["cate_id"] = $cate_id;
            if(!$ext_list) $ext_list = array();
            foreach($ext_list as $key=>$value){
                if($rs[$value['identifier']]){
                    $value['content'] = $rs[$value['identifier']];
                }
                $tmplist[$value["identifier"]] = $this->lib('form')->get($value);
            }
            $this->model('list')->save_ext($tmplist,$p_rs["module"]);
        }
        //儲存內容擴充套件欄位
        if($tmpadd){
            ext_save("admin-add-list",true,"list-".$id);
        }else{
            ext_save("list-".$id);
        }
        if($array['status'] && $array['user_id']){
            $this->model('wealth')->add_integral($id,$array['user_id'],'post',P_Lang('管理員編輯主題發布#{id}',array('id'=>$id)));
        }
        $this->plugin('system_admin_title_success',$id,$p_rs);
        $this->json(true);
    }

    public function single_save_f()
    {
        $id = $this->get("id","int");
        $pid = $this->get("pid","int");
        if(!$pid){
            $this->error(P_Lang('未指定專案ID'));
        }
        $this->popedom_auto($pid);
        $popedom_id = $id ? 'modify' : 'add';
        if(!$this->popedom[$popedom_id]){
            $this->error(P_Lang('您沒有許可權執行此操作'));
        }
        $project = $this->model('project')->get_one($pid);
        if(!$project){
            $this->error(P_Lang('操作異常，無法取得專案資訊'));
        }
        $array = array();
        $array["project_id"] = $pid;
        $array['site_id'] = $project['site_id'];
        $extlist = $this->model('module')->fields_all($project["module"]);
        if($extlist){
            foreach($extlist as $key=>$value){
                $array[$value['identifier']] = $this->lib('form')->get($value);
            }
        }
        //儲存資料
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
        $this->plugin('system_admin_title_success',$id,$project);
        $this->success();
    }

    /**
     * 主題刪除
     **/
    public function del_f()
    {
        $id = $this->get("id");
        if(!$id){
            $this->json(P_Lang('沒有指定主題ID'));
        }
        $idlist = explode(",",$id);
        $chk_id = intval($idlist[0]);
        $rs = $this->model('list')->get_one($chk_id);
        $pid = $rs["project_id"];
        $this->popedom_auto($pid);
        if(!$this->popedom['delete']){
            $this->json(P_Lang('您沒有許可權執行此操作'));
        }
        foreach($idlist as $key=>$value){
            $value = intval($value);
            $this->model('list')->delete($value);
        }
        $this->json(P_Lang('主題刪除成功'),true);
    }

    /**
     * 單獨模組主題刪除
     **/
    public function single_delete_f()
    {
        $id = $this->get("id");
        if(!$id){
            $this->error(P_Lang('沒有指定主題ID'));
        }
        $pid = $this->get('pid');
        if(!$pid){
            $this->error(P_Lang('未指定專案'));
        }
        $this->popedom_auto($pid);
        if(!$this->popedom['delete']){
            $this->error(P_Lang('您沒有許可權執行此操作'));
        }
        $project = $this->model('project')->get_one($pid);
        $idlist = explode(",",$id);
        foreach($idlist as $key=>$value){
            $value = intval($value);
            $this->model('list')->single_delete($value,$project['module']);
        }
        $this->success();
    }

    /**
     * 主題審核，通過主題原來的狀態取1或0
     * @引數 id 主題ID
     **/
    public function content_status_f()
    {
        $id = $this->get("id","int");
        if(!$id){
            $this->error(P_Lang('沒有指定ID'));
        }
        $rs = $this->model('list')->get_one($id);
        $this->popedom_auto($rs['project_id']);
        if(!$this->popedom["status"]){
            $this->error("您沒有啟用/禁用許可權");
        }
        $status = $rs["status"] ? 0 : 1;
        $action = $this->model('list')->update_status($id,$status);
        if(!$action){
            $this->error(P_Lang('操作失敗，請檢查SQL語句'));
        }
        if($status && $rs['user_id']){
            $this->model('wealth')->add_integral($id,$rs['user_id'],'post',P_Lang('管理員操作審核主題發布#{id}',array('id'=>$rs['id'])));
        }
        //執行外掛接入點
        $this->plugin("ap-list-status",$id);
        $this->success($status);
    }

    //執行動作
    public function execute_f()
    {
        $ids = $this->get('ids');
        if(!$ids){
            $this->json(P_Lang('未指定ID'));
        }
        $title = $this->get('title');
        $list = explode(',',$ids);
        $tmp1 = $list[0];
        $rs = $this->model('list')->get_one($tmp1);
        $this->popedom_auto($rs['project_id']);
        if(!$this->popedom['status']){
            $this->json(P_Lang('您沒有許可權執行此操作'));
        }
        foreach($list as $key=>$value){
            $value = intval($value);
            if(!$value){
                continue;
            }
            if($title == 'status'){
                $this->model('list')->update_status($value,1);
            }elseif($title == 'unstatus'){
                $this->model('list')->update_status($value,0);
            }elseif($title == 'hidden'){
                $this->model('list')->save(array('hidden'=>1),$value);
            }elseif($title == 'show'){
                $this->model('list')->save(array('hidden'=>0),$value);
            }
        }
        $this->json(P_Lang('操作成功'),true);
    }

    /**
     * 內容排序
     **/
    public function content_sort_f()
    {
        $sort = $this->get('sort');
        if(!$sort || !is_array($sort)){
            $this->error(P_Lang('更新排序失敗'));
        }
        foreach($sort as $key=>$value){
            $this->model('list')->update_sort($key,$value);
        }
        $this->success();
    }

    public function move_cate_f()
    {
        $ids = $this->get("ids");
        $cate_id = $this->get("cate_id");
        $type = $this->get('type');
        if(!$cate_id || !$ids || !$type){
            $this->json(P_Lang('引數傳遞不完整'));
        }
        $list = explode(",",$ids);
        $delete_ok = true;
        foreach($list as $key=>$value){
            $value = intval($value);
            if(!$value){
                continue;
            }
            if($type == 'add'){
                $this->model('list')->list_cate_add($cate_id,$value);
            }
            if($type == 'delete'){
                $act = $this->model('list')->list_cate_delete($cate_id,$value);
                if(!$act){
                    $delete_ok = false;
                }
            }
            if($type == 'move'){
                $mid = $this->model('list')->get_mid($value);
                if($mid){
                    $this->model('list')->update_ext(array("cate_id"=>$cate_id),$mid,$value);
                }
                $this->model('list')->save(array('cate_id'=>$cate_id),$value);
                $this->model('list')->list_cate_add($cate_id,$value);
            }
        }
        if(!$delete_ok){
            $this->json(P_Lang('主分類不允許刪除'));
        }
        $this->json(P_Lang('更新成功'),true);
    }

    //設定屬性
    public function attr_set_f()
    {
        $ids = $this->get("ids");
        $val = $this->get("val");
        $type = $this->get("type");
        if(!$val || !$ids || !$type)
        {
            $this->json(P_Lang('引數傳遞不完整'));
        }
        if($type != "add" && $type != "delete") $type = "add";
        $list = explode(",",$ids);
        foreach($list as $key=>$value)
        {
            $value = intval($value);
            if(!$value) continue;
            $rs = $this->model('list')->simple_one($value);
            if(!$rs) continue;
            $attr = $rs["attr"];
            if($attr)
            {
                $tmp = explode(",",$attr);
                if($type == "add")
                {
                    $tmp[] = $val;
                    $tmp = array_unique($tmp);
                    $attr = implode(",",$tmp);
                }
                else
                {
                    if(in_array($val,$tmp))
                    {
                        foreach($tmp as $k=>$v)
                        {
                            if($v == $val) unset($tmp[$k]);
                        }
                        if($tmp && count($tmp)>0)
                        {
                            $attr = implode(",",$tmp);
                        }
                        else
                        {
                            $attr = "";
                        }
                    }
                }
            }
            else
            {
                $attr = $type == "add" ? $val : "";
            }
            $array = array("attr"=>$attr);
            $this->model('list')->save($array,$value);
        }
        $this->json(P_Lang('更新成功'),true);
    }

    //更多批處理功能
    public function plaction_f()
    {
        $id = $this->get('id');
        if(!$id){
            error(P_Lang('未指定ID'),$this->url('list'),'error');
        }
        $this->popedom_auto($id);
        if(!$this->popedom["list"]){
            error(P_Lang('您沒有許可權執行此操作'),'','error');
        }
        $project_rs = $this->model('project')->get_one($id);
        if(!$project_rs)
        {
            error(P_Lang("專案資訊不存在"),$this->url("list"),"error");
        }
        if(!$project_rs['module'])
        {
            error(P_Lang('未繫結模組，不能使用此功能'),$this->url('list','action','id='.$id),'error');
        }
        $this->assign('page_rs',$project_rs);
        $this->view('list_plaction');
    }

    public function plaction_submit_f()
    {
        $pid = $this->get('pid');
        if(!$pid){
            $this->json(P_Lang('未指定專案ID'));
        }
        $this->popedom_auto($id);
        $project_rs = $this->model('project')->get_one($pid);
        if(!$project_rs){
            $this->json(P_Lang("專案資訊不存在"));
        }
        if(!$project_rs['module']){
            $this->json(P_Lang('未繫結模組，不能使用此功能'));
        }
        $startid = $this->get('startid','int');
        $endid = $this->get('endid','int');
        $plaction = $this->get('plaction');
        if($plaction == 'delete'){
            if(!$this->popedom['delete']){
                $this->json(P_Lang('您沒有許可權執行此操作'));
            }
            $condition = "project_id=".$pid." ";
            if($startid){
                $condition .= "AND id>=".$startid." ";
            }
            if($endid){
                $condition .= "AND id<=".$endid." ";
            }
            $this->model('list')->pl_delete($condition,$project_rs['module']);
            $this->json(P_Lang('批量刪除操作成功'),true);
        }
        $sql = "UPDATE ".$this->db->prefix."list SET ";
        if($plaction == 'status' || $plaction == 'unstatus'){
            if(!$this->popedom['status']){
                $this->json(P_Lang('您沒有許可權執行此操作'));
            }
            $sql .= " status=".($plaction == 'status' ? '1' : '0')." ";
        }elseif($plaction == 'hidden' || $plaction == 'show'){
            if(!$this->popedom['list']){
                $this->json(P_Lang('您沒有許可權執行此操作'));
            }
            $sql .= " hidden=".($plaction == 'hidden' ? '1' : '0')." ";
        }
        $sql.= " WHERE project_id='".$pid."' ";
        if($startid){
            $sql .= "AND id>=".$startid." ";
        }
        if($endid){
            $sql .= "AND id<=".$endid." ";
        }
        $this->db->query($sql);
        $this->json(P_Lang('批處理操作成功'),true);
    }

    /**
     * 讀取產品屬性及可操作內容
     **/
    public function attr_f()
    {
        $tid = $this->get('tid','int');
        $aid = $this->get('aid','int');
        if(!$aid){
            $this->error(P_Lang('未指定屬性ID'));
        }
        $rs = $this->model('options')->get_one($aid);
        if(!$rs){
            $this->error(P_Lang('屬性資訊不存在'));
        }
        $this->assign('rs',$rs);
        $optlist = $this->model('options')->values_list("aid='".$aid."'",0,9999,'id');
        if($tid){
            $rslist = $this->model('list')->biz_attrlist($tid,$aid);
            if($rslist){
                foreach($rslist as $key=>$value){
                    if($optlist && $optlist[$value['vid']]){
                        $value['title'] = $optlist[$value['vid']]['title'];
                        unset($optlist[$value['vid']]);
                        $rslist[$key] = $value;
                    }
                }
            }
            $this->assign('rslist',$rslist);
        }
        if($optlist){
            $this->assign('optlist',$optlist);
        }
        $content = $this->fetch('list_option_info');
        $this->success($content);
    }


    public function comment_f()
    {
        $id = $this->get('id','int');
        if(!$id){
            $this->error(P_Lang('未指定ID'));
        }
        $rs = $this->model('list')->get_one($id);
        if(!$rs){
            $this->error(P_Lang('資料不存在'));
        }
        $this->popedom_auto($rs['project_id']);
        if(!$this->popedom['comment']){
            $this->error(P_Lang('您沒有許可權執行此操作'));
        }
        $this->assign("rs",$rs);
        $pageurl = $this->url("list","comment","id=".$id);
        $condition = "tid='".$id."' AND admin_id=0";
        $pageid = $this->get($this->config["pageid"],"int");
        if(!$pageid){
            $pageid = 1;
        }
        $psize = $this->config["psize"] ? $this->config["psize"] : 30;
        $total = $this->model('reply')->get_total($condition);
        if($total>0){
            $offset = ($pageid-1) * $psize;
            $rslist = $this->model('reply')->get_all($condition,$offset,$psize);
            $this->assign("rslist",$rslist);
            $string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上壹頁').'&next='.P_Lang('下壹頁').'&last='.P_Lang('尾頁').'&half=5';
            $string.= '&add='.P_Lang('數量：').'(total)/(psize)'.P_Lang('，').P_Lang('頁碼：').'(num)/(total_page)&always=1';
            $pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
            $this->assign("pagelist",$pagelist);
        }
        $this->assign("total",$total);
        $this->view("list_comment");
    }

    /**
     * 設定主題的父層關系
     * @引數 id 指定的父層
     * @引數 ids 要繫結的主題，多個主題用英文逗號隔開
     * @返回 JSON資料
     * @更新時間 2016年10月25日
     **/
    public function set_parent_f()
    {
        $id = $this->get('id','int');
        if(!$id){
            $this->error(P_Lang('未指定ID'));
        }
        $ids = $this->get('ids');
        if(!$ids){
            $this->error(P_Lang('沒有要變更的ID'));
        }
        $list = explode(",",$ids);
        $isin = false;
        foreach($list as $key=>$value){
            $value = intval($value);
            if(!$value || $value == $id){
                $isin = true;
                break;
            }
        }
        if($isin){
            $this->error(P_Lang('ID有沖突，要變更的主題ID和內建ID重復了'));
        }
        $rs = $this->model('list')->get_one($id,false);
        if($rs['parent_id']){
            $this->error(P_Lang('父主題不符合要求，父主題不允許存在上級關系'));
        }
        foreach($list as $key=>$value){
            $value = intval($value);
            if($value){
                $tmp = array('parent_id'=>$id);
                $this->model('list')->save($tmp,$value);
            }
        }
        $this->success();
    }

    /**
     * 取消父層主題
     * @引數 ids 要取消的主題，多個主題用英文逗號隔開
     * @返回 JSON
     * @更新時間 2016年10月25日
     **/
    public function unset_parent_f()
    {
        $ids = $this->get('ids');
        if(!$ids){
            $this->error(P_Lang('沒有要變更的ID'));
        }
        $list = explode(",",$ids);
        $isin = false;
        foreach($list as $key=>$value){
            $value = intval($value);
            if(!$value){
                $isin = true;
                break;
            }
        }
        if($isin){
            $this->error(P_Lang('ID有沖突，要變更的主題ID和內建ID重復了'));
        }
        foreach($list as $key=>$value){
            $value = intval($value);
            if($value){
                $tmp = array('parent_id'=>0);
                $this->model('list')->save($tmp,$value);
            }
        }
        $this->success();
    }
}
