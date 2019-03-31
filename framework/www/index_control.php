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
		$this->myModule = [
		    2 => '經書',
            3 => '碑刻集',
            5 => '科儀文獻',
        ];
		$this->assign('my_module', $this->myModule);
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
        $rows = $this->db->get_all("select id,parent_id,title,pic from dj_project where id in (2,3,4) order by taxis desc,id desc");
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

    // 拉取所有文献
    public function getAllDocs() {
        $cacheKey = "all_doc_lists";
        $data = $this->getCache($cacheKey);
        if (!empty($data)) {
            return json_decode($data, true);
        }
	    $result = [];
	    $rows[2] = $this->db->get_all("select a.project_id,a.module_id,a.title,a.dateline,a.tag,a.sort,b.* from dj_list a inner join dj_list_2 b on a.id=b.id where a.status=1 ");
        $rows[3] = $this->db->get_all("select a.project_id,a.module_id,a.title,a.dateline,a.tag,a.sort,b.* from dj_list a inner join dj_list_3 b on a.id=b.id where a.status=1 ");
        $rows[5] = $this->db->get_all("select a.project_id,a.module_id,a.title,a.dateline,a.tag,a.sort,b.* from dj_list a inner join dj_list_5 b on a.id=b.id where a.status=1 ");
        $rows[2] = empty($rows[2]) ? [] : $rows[2];
        $rows[3] = empty($rows[3]) ? [] : $rows[3];
        $rows[5] = empty($rows[5]) ? [] : $rows[5];
        $data = array_merge($rows[2], $rows[3], $rows[5]);
        if (empty($data)) {
            return false;
        }
        $sortKey = array_column($data, "id", "id");
        array_multisort($sortKey, SORT_DESC, $data);
        foreach ($data as $val) {
            $result[$val['id']] = $val;
        }
        $this->saveCache($cacheKey, json_encode($result, true));
        return $result;
    }

    // 通过关键字搜索文献
    public function searchDocsByKw($keyWord = '', $searchField = [] )
    {
        $result = [];
        $docs = $this->getAllDocs();
        if (empty($docs)) {
            return false;
        }
        if (empty($keyWord)) {
            return $docs;
        }

        // 搜索要带上标题
        if (empty($searchField)) {
            $searchField = [
                2 => ['title'],
                3 => ['title'],
                5 => ['title']
            ];
        } else {
            foreach ($searchField as $key => $val) {
                if (!empty($val)){
                    $searchField[$key][] = "title";
                }
            }
        }
        foreach ($docs as $val) {
            if (empty($searchField[$val['module_id']])) {
                continue;
            }
            foreach ($val as $k => &$v) {
                if (empty($v)) {
                    continue;
                }
                if (!in_array($k, $searchField[$val['module_id']])) {
                   continue;
                }
                if (stripos($v, $keyWord) !== false) {
                   $v = str_replace($keyWord, "<span class='search-kw'>{$keyWord}</span>", $v);
                   $result[$val['id']] = $val;
                }
            }
        }
        return $result;
    }

    // 通过文库标签、文献标签、书籍标签搜索文献
    public function searchDocByTag($libTag = "", $docTag = "", $bookTag = "") {
	    if (!empty($libTag) && empty($docTag)) {
	        return false;
        }
        $data = [];
        $idsArr = [];
        if (!empty($libTag)) {
	        $libTags = $this->getLibTags();
	        if (empty($libTags[$libTag])) {
	            return false;
            }
	        $projectId = implode(',', $libTags[$libTag]);
	        $ids = $this->db->get_all("select id from dj_list where project_id in ({$projectId}) and status=1");
	        if (empty($ids)) {
	            return false;
            }
            foreach ($ids as $val) {
	           $idsArr[] = $val;
            }
        }
        if (!empty($docTag)) {
	        $docTags =  $this->getDocTags();
	        $idsArr = $docTags[$docTag];
        }
        if (!empty($bookTag)) {
            $bookTags = $this->getBookTags();
            if (empty($bookTags[$bookTag])) {
                return false;
            }
            $bookId = implode(",", $bookTags[$bookTag]);
            $ids = $this->db->get_all("select lid id from dj_list_6 where id in ({$bookId})");
            if (empty($ids)) {
                return false;
            }
            foreach ($ids as $val) {
                $idsArr[] = $val;
            }
        }
        if (empty($idsArr)) {
            return false;
        }
        $allDocs = $this->getAllDocs();
        foreach ($idsArr as $val) {
            $data[] = $allDocs[$val];
        }
        return $data;
    }


    // 获取文献书籍
    public function getDocBooks($lid) {
	    if (empty($lid)) {
	        return false;
        }
        $cacheKey = "doc_{$lid}_book_lists";
        $data = $this->getCache($cacheKey);
        if (!empty($data)) {
            return json_decode($data, true);
        }
        $where = " b.lid = {$lid} and a.status=1";
        $row = $this->db->get_one("select a.title,a.dateline,a.sort,a.tag,b.* from dj_list a inner join dj_list_6 b on a.id=b.id where {$where} order by a.sort desc,a.id desc");
        if (empty($row)) {
            return false;
        }
        $this->saveCache($cacheKey, json_encode($row, true));
        return $row;
    }


	public function index_f()
	{
	    $libs = $this->getLibs();
	    $moduleFields = $this->getModuleFields();
	    $this->assign('libs', $libs);
        $this->assign('module_fields', $moduleFields);
        $this->assign('seo_title','首页');
		$this->view("index");
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