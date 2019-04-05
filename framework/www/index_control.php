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
		$libs = $this->getLibs();
        $this->assign('libs', $libs);
	    $moduleFields = $this->getModuleFields();
        $this->assign('module_fields', $moduleFields);
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
//            $times = 600;
            $times = 0;
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
        $rows = $this->db->get_all("select id,parent_id,module,title,pic from dj_project where id in (2,3,4) order by taxis desc,id desc");
        if (empty($rows)) {
            return false;
        }
        $data = [];
        foreach ($rows as $val) {
            $data[$val['id']] = $val;
        }
        $this->saveCache($cacheKey, json_encode($data, true));
        return $data;
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
            if ($val['is_front'] == 1 ) {
                $data[$val['ftype']]['is_front'][$val['identifier']] = $val['title'];
            }
            if ($val['search'] == 2 ) {
                $data[$val['ftype']]['search'][$val['identifier']] = $val['title'];
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
        foreach ($data as $key => $val) {
            if (!empty($val['start_cover_pic'])) {
                $picInfo = $this->model('res')->get_one($val['start_cover_pic'],true);
                if (!empty($picInfo)) {
                    $pic = ['filename' => $picInfo['filename'], 'gd' => $picInfo['gd']];
                    $data[$key]['start_cover_pic'] = $pic;
                }
            }
            if (!empty($val['end_cover_pic'])) {
                $picInfo = $this->model('res')->get_one($val['end_cover_pic'],true);
                if (!empty($picInfo)) {
                    $pic = ['filename' => $picInfo['filename'], 'gd' => $picInfo['gd']];
                    $data[$key]['end_cover_pic'] = $pic;
                }
            }
            if (!empty($val['pdf_file'])) {
                $fileInfo = $this->model('res')->get_one($val['pdf_file'],true);
                if (!empty($fileInfo)) {
                    $pdf = ['filename' => $fileInfo['filename'], 'title' => $fileInfo['title']];
                    $data[$key]['pdf_file'] = $pdf;
                }
            }
            $result[$val['id']] = $data[$key];
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
                   $v = str_replace($keyWord, "<span class='layui-badge layui-bg-green'>{$keyWord}</span>", $v);
                   $result[$val['id']] = $val;
                }
            }
        }
        return $result;
    }

    // 通过文库标签、文献标签、书籍标签搜索文献
    public function searchDocByTag($libTag = "", $docTag = "", $bookTag = "") {
	    if (empty($libTag) && empty($docTag)) {
	        return false;
        }
        $data = [];
        $idsArr = [];
        $allDocs = $this->getAllDocs();
        if (!empty($libTag)) {
	        $libTags = $this->getLibTags();
	        if (empty($libTags[$libTag])) {
	            return false;
            }
            foreach ($allDocs as $val) {
                if (in_array($val['project_id'], $libTags[$libTag])) {
                    $data[] = $val;
                }
            }
            return $data;
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
        foreach ($idsArr as $val) {
            $data[] = $allDocs[$val];
        }
        return $data;
    }


    // 通过project_id搜索文献
    public function searchDocByPid($pid) {
	   if (empty($pid)) {
	       return false;
       }
       $data = [];
       $sonPid = $this->db->get_all("select id from dj_project where parent_id = {$pid}");
	   if (!empty($sonPid)) {
	      foreach ($sonPid as $val) {
	          $pidArr[] = $val['id'];
          }
       } else {
	       $pidArr[] = $pid;
       }
       $docs = $this->getAllDocs();
	   if (empty($docs)) {
           return false;
       }
       foreach ($docs as $val) {
	       if (in_array($val['project_id'], $pidArr)) {
	           $data[] = $val;
           }
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
        $rows = $this->db->get_all("select a.title,a.dateline,a.sort,a.tag,b.* from dj_list a inner join dj_list_6 b on a.id=b.id where {$where} order by a.sort desc,a.id desc");
        if (empty($rows)) {
            return false;
        }
        foreach ($rows as  &$val) {
            if (!empty($val['image'])) {
                $picInfo = $this->model('res')->get_one($val['image'],true);
                if (!empty($picInfo)) {
                    $pic = ['filename' => $picInfo['filename'], 'gd' => $picInfo['gd']];
                    $val['image'] = $pic;
                }
            }
        }
        $this->saveCache($cacheKey, json_encode($rows, true));
        return $rows;
    }


	public function index_f()
	{
        $this->assign('seo_title','首页');
		$this->view("index");
	}

	public function docs_f() {
	    $pid = $this->get('pid');
        $libTags = $this->get('lib_tags');
        $docTags = $this->get('doc_tags');
        $keywords = $this->get('keywords');
        $searchFields = $this->get('fields');
        if (empty($pid) && empty($libTags) && empty($docTags) && empty($keywords)) {
            $docs = $this->getAllDocs();
        }
        if (!empty($pid)) {
            $prs = $this->db->get_one("select title from dj_project where id={$pid}");
            $docs = $this->searchDocByPid($pid);
            $this->assign('pid', $pid);
            $this->assign('nav_title', $prs['title']);
        }
        if (!empty($libTags)) {
            $docs = $this->searchDocByTag($libTags);
            $this->assign('lib_tags', $docTags);
            $this->assign('nav_title', "文库标签:  {$libTags}");
        }
        if (!empty($docTags)) {
            $docs = $this->searchDocByTag("", $docTags);
            $this->assign('doc_tags', $docTags);
            $this->assign('nav_title', "文献标签:  {$docTags}");
        }
        if (!empty($keywords)) {
            $docs = $this->searchDocsByKw($keywords, $searchFields);
            $this->assign('keywords', $keywords);
            $this->assign('search_fields', $searchFields);
            $this->assign('nav_title', "关键字:  {$keywords}");
        }
        $libTags = $this->getLibTags();
        $docTags = $this->getDocTags();
        $bookTags = $this->getBookTags();
        $this->assign('lib_tags', $libTags);
        $this->assign('doc_tags', $docTags);
        $this->assign('book_tags', $bookTags);
        $this->assign('docs', $docs);
        $this->view('img_list');
    }

    public function book_f() {
	    $id = $this->get('doc_id');
	    if (empty($id)) {
            $this->error(P_Lang('未指定文献id'));
        }
        $docs = $this->getAllDocs();
	    if (empty($docs[$id])) {
            $this->error(P_Lang('找不到相关数据'));
        }
        $bookInfo = $docs[$id];
	    $projectInfo = $this->db->get_one("select * from dj_project where id={$bookInfo['project_id']}");
	    if (!empty($projectInfo['parent_id'])) {
            $projectInfo = $this->db->get_one("select * from dj_project where id={$projectInfo['parent_id']}");
        }
	    $this->assign("prs_info", $projectInfo);
        $this->assign('nav_title', $bookInfo['title']);
        $this->assign('pid', $projectInfo['id']);
        $this->assign('rs', $bookInfo);
        $this->view('book');
    }

    public function read_f() {
	    $id = $this->get('doc_id');
        $type = $this->get('type');
	    if (empty($id)) {
            $this->error(P_Lang('未指定文献id'));
        }
        $docs = $this->getAllDocs();
	    if (empty($docs[$id])) {
            $this->error(P_Lang('找不到相关数据'));
        }
        $bookInfo = $docs[$id];
	    $bookLists = $this->getDocBooks($id);
	    krsort($bookLists);
        $this->assign('rslist', $bookLists);
        $this->assign('rs', $bookInfo);
        $type = empty($type) ? 1 : $type;
        $this->assign('type', $type);
        $this->view('wowbook');
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