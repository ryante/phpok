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

    // 获取标签列表
    public function getAllTags() {
        $cacheKey = "all_tags_list";
        $data = $this->getCache($cacheKey);
        if (!empty($data)) {
            return json_decode($data, true);
        }
        $tagGroups = [];
        $groups = $this->db->get_all("select id,name,show_nums from dj_tag_group where `show` = 1 order by sort desc, id desc");
        if (empty($groups)) {
            return false;
        }
        foreach($groups as $val) {
            $tagGroups[$val['id']] = $val;
        }
        $tags = $this->db->get_all("select id,title,tag_group_id from dj_tag where tag_group_id != 0 order by id desc");
        foreach ($tags as $key => $val) {
            $items = $this->db->get_one("select count(`title_id`) total from dj_tag_stat where tag_id='{$val['id']}'");
            $val['items'] = $items['total'];
            $tagGroups[$val['tag_group_id']]['tags'][] = $val;
        }
       
        $this->saveCache($cacheKey, json_encode($tagGroups, true));
        return $tagGroups;
    }

    // 获取模型搜索、显示字段
    public function getModuleFields() {
        $cacheKey = "module_field_list";
        $data = $this->getCache($cacheKey);
        if (!empty($data)) {
            return json_decode($data, true);
        }
        $data = [];
        $rows = $this->db->get_all("select ftype,title,identifier,is_front,search from dj_fields where ftype in ({$this->myModuleId}) order by taxis asc");
        if (empty($rows)) {
            return false;
        }
        foreach ($rows as $val) {
            $data[$val['ftype']]['all'][$val['identifier']] = $val['title'];
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
        $allProject = $this->db->get_all("select id from dj_project");
        foreach ($allProject as $project) {
            $projectId[] = $project['id'];
        }
        $projectStr = implode(",", $projectId);
	    $rows[2] = $this->db->get_all("select a.project_id,a.module_id,a.title,a.dateline,a.tag,a.sort,b.* from dj_list a inner join dj_list_2 b on a.id=b.id where a.status=1 and a.project_id in ({$projectStr}) order by a.sort asc ");
        $rows[3] = $this->db->get_all("select a.project_id,a.module_id,a.title,a.dateline,a.tag,a.sort,b.* from dj_list a inner join dj_list_3 b on a.id=b.id where a.status=1 and a.project_id in ({$projectStr}) order by a.sort asc");
        $rows[5] = $this->db->get_all("select a.project_id,a.module_id,a.title,a.dateline,a.tag,a.sort,b.* from dj_list a inner join dj_list_5 b on a.id=b.id where a.status=1 and a.project_id in ({$projectStr}) order by a.sort asc");
        $rows[2] = empty($rows[2]) ? [] : $rows[2];
        $rows[3] = empty($rows[3]) ? [] : $rows[3];
        $rows[5] = empty($rows[5]) ? [] : $rows[5];
        $data = array_merge($rows[2], $rows[3], $rows[5]);
        if (empty($data)) {
            return false;
        }
        //$sortKey = array_column($data, "id", "id");
        //array_multisort($sortKey, SORT_DESC, $data);
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
	    if (!empty($val['summary'])) {
                $fileInfo = $this->model('res')->get_one($val['summary'],true);
                if (!empty($fileInfo)) {
                    $pdf = ['filename' => $fileInfo['filename'], 'title' => $fileInfo['title']];
                    $data[$key]['summary'] = $pdf;
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

    // 通过标签搜索文献及文献内文
    public function searchDocsByTag($tagId) {
	    if (empty($tagId)) {
	        return false;
        }
        $cacheKey = "search_tagid_{$tagId}";
        $data = $this->getCache($cacheKey);
        if (!empty($data)) {
            return json_decode($data, true);
        }
        $docIds = $this->db->get_all("select title_id from dj_tag_stat where tag_id = '{$tagId}'");
        if (empty($docIds)) {
            return false;
        }
        $bookIds = [];
        $data = [];
        $allDocs = $this->getAllDocs();
        foreach($docIds as $val) {
            /*
             * 又改变主意不显示内文了
            $docIdInfo = $this->db->get_one("select lid from dj_list_6  where id='{$val['title_id']}'");
            if (!empty($docIdInfo) && !empty($allDocs[$docIdInfo['lid']])) {
                if (empty($data[$docIdInfo['lid']])) {
                    $data[$docIdInfo['lid']] = $allDocs[$docIdInfo['lid']]; 
                }
                $tmpBookLists = $this->getDocBooks($docIdInfo['lid']);
                if (empty($tmpBookLists) || empty($tmpBookLists[$val['title_id']]['nohtml_content'])) {
                    continue;
                }
                $data[$docIdInfo['lid']]['book_list'] = $tmpBookLists;
                continue;
            }
             */
            if (!empty($allDocs[$val['title_id']]) && empty($data[$val['title_id']])) {
                $data[$val['title_id']] = $allDocs[$val['title_id']];
            }
        }
        $this->saveCache($cacheKey, json_encode($data, true));
        return $data;
    }


    // 通过project_id搜索文献
    public function searchDocsByPid($pid) {
	   if (empty($pid)) {
	       return false;
       }
       $data = [];
       $sonPid = $this->db->get_all("select id from dj_project where parent_id = '{$pid}'");
	   if (!empty($sonPid)) {
	      foreach ($sonPid as $val) {
	          $pidArr[] = $val['id'];
          }
          $pidArr[] = $pid;
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
        $where = " b.lid = '{$lid}' and a.status=1";
        $rows = $this->db->get_all("select a.title,a.project_id,a.dateline,a.sort,a.tag,b.* from dj_list a inner join dj_list_6 b on a.id=b.id where {$where} order by a.sort desc,a.id desc");
        if (empty($rows)) {
            return false;
        }
        $page = 1;
        $data = [];
        foreach ($rows as  $val) {
            if (!empty($val['image'])) {
                $picInfo = $this->model('res')->get_one($val['image'],true);
                if (!empty($picInfo)) {
                    $pic = ['filename' => $picInfo['filename'], 'gd' => $picInfo['gd']];
                    $val['image'] = $pic;
                }
            }
            if (!empty($val['content_pdf'])) {
                $fileInfo = $this->model('res')->get_one($val['content_pdf'],true);
                if (!empty($fileInfo)) {
                    $pdf = ['filename' => $fileInfo['filename'], 'title' => $fileInfo['title']];
                    $val['content_pdf'] = $pdf;
                }
            }
            if (!empty($val['img_pdf'])) {
                $fileInfo = $this->model('res')->get_one($val['img_pdf'],true);
                if (!empty($fileInfo)) {
                    $pdf = ['filename' => $fileInfo['filename'], 'title' => $fileInfo['title']];
                    $val['img_pdf'] = $pdf;
                }
            }
            $val['page'] = 2 * $page; 
            $page++;
            $data[$val['id']] = $val;
        }
        $this->saveCache($cacheKey, json_encode($data, true));
        return $data;
    }

    // 内文搜索
    public function searchBookContent($keyWord, $docId = '') {
        $where = "1=1";
        $docs = $this->getAllDocs();
        if (!empty($docId)) {
            $thisDocsInfo = $docs[$docId];
            if (empty($thisDocsInfo)) {
                return false;
            }
            $where .= " and a.project_id = '{$thisDocsInfo['project_id']}' ";
        }
        if (!empty($keyWord)) {
            $where .= " and nohtml_content like '%{$keyWord}%'";
        }
        $rows = $this->db->get_all("select b.id,b.lid,b.nohtml_content from dj_list a inner join dj_list_6 b on a.id=b.lid where {$where} order by a.sort desc, a.id desc");
        if (!empty($rows)) {
            foreach ($rows as $key => $val) {
                $val['nohtml_content'] = $this->formatBookContent($val['nohtml_content'], $keyWord, 38);
                $tmpBookLists = $this->getDocBooks($val['lid']);
                $val['page'] = $tmpBookLists[$val['id']]['page'];
                if (!empty($docs[$val['lid']])) {
                    if (empty($bookData[$val['lid']]['id'])) {
                        $bookData[$val['lid']] = $docs[$val['lid']];
                    }
                    $bookData[$val['lid']]['book_list'][] = $val;
                }
            }
        }
        return $bookData;
    }

    // 首页
	public function index_f()
	{
        $this->assign('seo_title','首页');
		$this->view("index");
	}

    // 文献列表
	public function docs_f() {
	    $pid = $this->get('pid');
        $tagId = $this->get('tag_id');
        $keywords = $this->get('keywords');
        $searchFields = $this->get('fields');
        if (empty($pid) && empty($tagId) && empty($keywords)) {
            $docs = $this->getAllDocs();
        }
        $tags = $this->getAllTags();
        if (!empty($pid)) {
            $prs = $this->db->get_one("select title from dj_project where id='{$pid}'");
            $docs = $this->searchDocsByPid($pid);
            $this->assign('pid', $pid);
            $this->assign('nav_title', $prs['title']);
        }
        if (!empty($tagId)) {
            $docs = $this->searchDocsByTag($tagId);
            $tagInfo = $this->db->get_one("select title from dj_tag where id='{$tagId}'");
            $this->assign('tag_id', $tagId);
            $this->assign('nav_title', "文献标签：{$tagInfo['title']}");
        }
        if (!empty($keywords)) {
            $docs = $this->searchDocsByKw($keywords, $searchFields);
            $bookData = $this->searchBookContent($keywords);
            foreach ($docs as $key => $val) {
                if (!empty($bookData[$key])) {
                    $docs[$key] = $bookData[$key];
                    unset($bookData[$key]);
                }
            }
            $docs = array_merge($docs, $bookData);
            $this->assign('keywords', $keywords);
            $this->assign('search_fields', $searchFields);
            $this->assign('nav_title', "关键字：{$keywords}");
        }
        $this->assign('tags', $tags);
        $this->assign('docs', $docs);
        $this->assign('docs_total', count($docs));
        $this->view('img_list');
    }

    public function book_f() {
        $keyWord = $this->get('keyword');
        $searchRange = $this->get('search_range');
	    $id = $this->get('doc_id');
	    $page = $this->get('page', 'int');
	    if (empty($id)) {
            $this->error(P_Lang('未指定文献id'));
        }
        $docs = $this->getAllDocs();
	    if (empty($docs[$id])) {
            $this->error(P_Lang('找不到相关数据'));
        }
        $bookInfo = $docs[$id];
	$projectInfo = $this->db->get_one("select * from dj_project where id='{$bookInfo['project_id']}'");
	if (!empty($projectInfo['parent_id'])) {
            $projectInfo = $this->db->get_one("select * from dj_project where id='{$projectInfo['parent_id']}'");
        }
	$sonBook = $this->db->get_all("select id,title from dj_list where parent_id={$id}");
	$this->assign("prs_info", $projectInfo);
        $this->assign('nav_title', $bookInfo['title']);
        $this->assign('pid', $projectInfo['id']);
        $this->assign('rs', $bookInfo);
        $this->assign('page', $page);
        $this->assign('keyword', $keyWord);
        $this->assign('search_range', $searchRange);
        $this->assign('son_book', $sonBook);
        // 区分碑刻的显示
        if ($bookInfo['module_id'] == 3) {
            $bookLists = $this->getDocBooks($id);
            $this->assign('rslist', current($bookLists));
            $this->view('book_beike');
        } else {
            $this->view('book');
        }
    }

    public function read_f() {
	    $id = $this->get('doc_id');
        $type = $this->get('type');
	    $page = $this->get('page', 'int');
        $keyWord = $this->get('keyword');
	    if (empty($id)) {
            $this->error(P_Lang('未指定文献id'));
        }
        $docs = $this->getAllDocs();
	    if (empty($docs[$id])) {
            $this->error(P_Lang('找不到相关数据'));
        }
        $bookInfo = $docs[$id];
	    $bookLists = $this->getDocBooks($id);
        $this->assign('total', count($bookLists));
        $this->assign('rslist', $bookLists);
        $this->assign('rs', $bookInfo);
        $this->assign('keyword', $keyWord);
        $this->assign('page', $page);
        $type = empty($type) ? 1 : $type;
        $this->assign('type', $type);
        $this->view('wowbook');
    }

    public function book_search_f() {
        $keyWord = $this->get('keyword');
        $searchRange = $this->get('search_range');
        $docId = $this->get('doc_id');
        $docs = $this->getAllDocs();
        $books = [];
        $bookData = [];
        if ($searchRange == 2) {
            if (empty($docs[$docId])) {
                $this->error(P_Lang('找不到相关数据'));
            }
            $docId = $this->get('doc_id');
            $bookLists = $this->getDocBooks($docId);
            if (!empty($bookLists)) {
                foreach ($bookLists as $key => $val) {
                    if (!empty($keyWord)) {
                        if (stripos($val['nohtml_content'], $keyWord) !== false) {
                            $val['nohtml_content'] = $this->formatBookContent($val['nohtml_content'], $keyWord, 38);
                            $books[] = $val;
                        }
                    } else {
                       $books[] = $val;
                    }
                }
                $bookData[$docId] = $docs[$docId];
                $bookData[$docId]['book_list'] = $books;
            }
        } else {
            $bookData = $this->searchBookContent($keyWord, $docId);
        }
        $this->assign('doc_id', $docId);
        $this->assign('books', $bookData);
        $this->assign('keyword', $keyWord);
        $this->assign('search_range', $searchRange);
        $this->view('book_search');
    }


    // 格式化内文
    public function formatBookContent($str, $kw, $subLen) {
        if (empty($str) || empty($kw)) {
            return;
        }   
        $str = str_replace([" ","　","\t","\n","\r"], '', $str);
        $strLen = mb_strlen($str);
        $kwLen = mb_strlen($kw);
        $pos = mb_strpos($str, $kw, 0, 'utf-8');
        if ($strLen <= $subLen) {
            return $str;
        }   
        $halfOffset = intval(($subLen - $kwLen) / 2); 
        if ($pos < $halfOffset) {
            // 左边偏移不够
            $str = mb_substr($str, 0, $subLen) . "...";
        } elseif (($pos + $halfOffset) > $strLen ) { 
            // 右边偏移不够
            $str = "..." . mb_substr($str, $subLen - 2 * $subLen);
        } else {
            $leftSubStr = mb_substr($str, $pos - $halfOffset, $halfOffset);
            $rightSubStr = mb_substr($str, $pos, $halfOffset + $kwLen);
            $str = "..." . $leftSubStr . $rightSubStr . "...";
        }   
        $str = str_replace($kw,"<span class='layui-badge layui-bg-green'>{$kw}</span>", $str);
        return $str;
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
