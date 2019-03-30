<?php
/***********************************************************
	Filename: {phpok}/www/index_control.php
	Note	: 網站首頁及APP的封面頁
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2015年06月06日 09時09分
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class index_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$this->myModuleId = "2,3,5";
	}

    /**
     * @param $key 缓存KEY
     * @param string $times 缓存时间
     * @return bool|string
     */
	public function getCache($key, $times = '') {
        $file = CACHE . $key . ".txt";
        $editTime = filemtime($file);
        $nowTime = time();
        if (empty($times)) {
            $times = 600;
        }
        if (!file_exists($file) || ($nowTime - $editTime) > $times) {
            return false;
        }
        return file_get_contents($file);
    }

    /**
     * @param $key 缓存KEY
     * @param $data 缓存时间
     * @return mixed
     */
	public function saveCache($key, $data) {
	    if (empty($key)) {
	        return $data;
        }
	    $file = CACHE . $key . ".txt";
	    @file_put_contents($file, $data);
    }


	// 获取文库
	public function getLibs(){
	    $cacheKey = "libs_list";
        $data = $this->getCache($cacheKey);
        if (!empty($data)) {
            return json_decode($data, true);
        }
        $rows = $this->db->get_all("select id,parent_id,title,pic from dj_project where module in ({$this->myModuleId}) and parent_id=0");
        if (empty($rows)) {
            return false;
        }
        $this->saveCache($cacheKey, json_encode($rows, true));
        return $rows;
    }

    //获取文库标签
    public function getLibTags() {
        $cacheKey = "lib_tags_list";
        $data = $this->getCache($cacheKey);
        if (!empty($data)) {
            return json_decode($data, true);
        }
        $data = [];
        $rows = $this->db->get_all("select id,tag from dj_project where module in ({$this->myModuleId}) and tag!=''");
        if (empty($rows)) {
            return false;
        }
        foreach ($rows as $val) {
            if (empty($val['tag'])) {
                continue;
            }
            $tmpTags = explode(",", $val['tag']);
            if (!empty($tmpTags)) {
                foreach ($tmpTags as $v) {
                    $data[$v][] = $val['id'];
                }
            }
        }
        $this->saveCache($cacheKey, json_encode($data, true));
        return $data;
    }

    //获取文献标签
    public function getDocTags() {
        $cacheKey = "doc_tags_list";
        $data = $this->getCache($cacheKey);
        if (!empty($data)) {
            return json_decode($data, true);
        }
        $data = [];
        $rows = $this->db->get_all("select id,tag from dj_list where module_id in ({$this->myModuleId}) and tag != ''");
        if (empty($rows)) {
            return false;
        }
        foreach ($rows as $val) {
            if (empty($val['tag'])) {
                continue;
            }
            $tmpTags = explode(",", $val['tag']);
            if (!empty($tmpTags)) {
                foreach ($tmpTags as $v) {
                    $data[$v][] = $val['id'];
                }
            }
        }
        $this->saveCache($cacheKey, json_encode($data, true));
        return $data;
    }

    //获取ebook标签
    public function getBookTags() {
        $cacheKey = "book_tags_list";
        $data = $this->getCache($cacheKey);
        if (!empty($data)) {
            return json_decode($data, true);
        }
        $data = [];
        $rows = $this->db->get_all("select id,tag from dj_list where module_id=6 and tag != ''");
        if (empty($rows)) {
            return false;
        }
        foreach ($rows as $val) {
            if (empty($val['tag'])) {
                continue;
            }
            $tmpTags = explode(",", $val['tag']);
            if (!empty($tmpTags)) {
                foreach ($tmpTags as $v) {
                    $data[$v][] = $val['id'];
                }
            }
        }
        $this->saveCache($cacheKey, json_encode($data, true));
        return $data;
    }

    // 获取模型搜索、显示字段
    public function getModuleFields() {
        $cacheKey = "module_field_list";
        $data = $this->getCache($cacheKey);
        if (!empty($data)) {
            return json_decode($data, true);
        }
        $data = [];
        $rows = $this->db->get_all("select ftype,title,identifier,is_front,search from dj_fields where ftype in ({$this->myModuleId}) order by taxis desc");
        if (empty($rows)) {
            return false;
        }
        foreach ($rows as $val) {
            $tmp = ['identifier' => $val['identifier'], 'title' => $val['title']];
            if ($val['is_front'] == 1 ) {
                $data[$val['ftype']]['is_front'][] = $tmp;
            }
            if ($val['search'] == 2 ) {
                $data[$val['ftype']]['search'][] = $tmp;
            }
        }
        $this->saveCache($cacheKey, json_encode($data, true));
        return $data;
    }


	public function index_f()
	{
	    $libs = $this->getModuleFields();
	    print_r($libs);die;
	    $this->saveCache('libs_list', json_encode($libs, true));die;
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'index';
		}
		//檢測是否有指定
		$tmp = $this->model('id')->id('index',$this->site['id'],true);
		if($tmp){
			$pid = $tmp['id'];
			$page_rs = $this->call->phpok('_project',array('pid'=>$pid));
			$this->phpok_seo($page_rs);
			$this->assign("page_rs",$page_rs);
			if($page_rs["tpl_index"] && $this->tpl->check_exists($page_rs["tpl_index"])){
				$tplfile = $page_rs["tpl_index"];
			}
			unset($page_rs);
		}
		$this->view($tplfile);
	}

	public function tips_f()
	{
		$info = $this->get('info');
		$backurl = $this->get('back');
		if(!$info){
			$info = P_Lang('友情提示');
		}
		if(!$backurl){
			$backurl = $this->url;
		}
		$this->assign('url',$backurl);
		$this->assign('tips',$info);
		$this->view('tips');
	}

	/**
	 * 推薦人
	 * @引數 uid 推薦人ID
	**/
	public function link_f()
	{
		$uid = $this->get('uid','int');
		if(!$uid){
			$this->_location($this->config['www_file']);
		}
		$rs = $this->model('user')->get_one($uid,'id',false,false);
		if(!$rs){
			$this->_location($this->config['www_file']);
		}
		if($this->session->val('user_id')){
			$this->_location($this->config['www_file']);
		}
		$this->session->assign('introducer',$uid);
		$this->_location($this->url('register'));
	}

	public function phpinc_f()
	{
		$phpfile = $this->get('phpfile','system');
		if(!$phpfile){
			$this->error(P_Lang('未指定合法的 PHP 檔案'));
		}
		$phpfile .= ".php";
		if(!file_exists($this->dir_root.'phpinc/'.$phpfile)){
			$this->error(P_Lang('PHP 檔案不存在'));
		}
		global $app;
		include($this->dir_root.'phpinc/'.$phpfile);
	}
}