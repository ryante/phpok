<?php
/**
 * 專案管理
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年07月19日
**/


if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class newproject_control extends phpok_control
{
	/**
	 * 許可權
	**/
	private $popedom;

	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("project");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 專案列表，展示全部專案，包括啟用，未啟用，隱藏的，普通管理員要求有檢視許可權（project:list）
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$rslist = $this->model('project')->get_all_project($this->session->val('admin_site_id'));
		$this->assign("rslist",$rslist);
		$this->view("project_index_new");
	}

	/**
	 * 新增或編輯專案基礎配置資訊，普通管理員要有配置許可權（project:set）
	 * @引數 id 專案ID，數值，ID為空表示新增專案，不為0表示編輯這個ID下的專案
	**/
	public function set_f()
	{
		if(!$this->popedom["set"]){
			error(P_Lang('您沒有許可權執行此操作'),'','error');
		}
		$id = $this->get("id","int");
		$idstring = "";
		if($id){
			$this->assign("id",$id);
			$rs = $this->model('project')->get_one($id);
			if(!$rs['ico']){
				$rs['ico'] = 'images/ico/default.png';
			}
			$this->assign("rs",$rs);
			$ext_module = "project-".$id;
		}else{
			$rs = array();
			$ext_module = "add-project";
			$parent_id = $this->get("pid");
			$rs["parent_id"] = $parent_id;
			$rs['taxis'] = $this->model('project')->project_next_sort($parent_id);
			$rs['ico'] = 'images/ico/default.png';
			$this->assign("rs",$rs);
		}

        $tuku_module = $this->db->get_one("select * from dj_module where title='圖庫'");
		$this->assign("tuku_module",$tuku_module);
		//$parent_list = $this->model('project')->get_all($this->session->val('admin_site_id'),0);
		$parent_list = $this->model('project')->get_all_project($this->session->val('admin_site_id'));
		$this->assign("parent_list",$parent_list);
		$this->assign("ext_module",$ext_module);
		$forbid = array("id","identifier");
		$forbid_list = $this->model('ext')->fields("project");
		$forbid = array_merge($forbid,$forbid_list);
		$forbid = array_unique($forbid);
		$this->assign("ext_idstring",implode(",",$forbid));
		$module_list = $this->model('module')->get_all();
		$this->assign("module_list",$module_list);
		$catelist = $this->model('cate')->root_catelist($this->session->val('admin_site_id'));
		$this->assign("catelist",$catelist);
		$currency_list = $this->model('currency')->get_list();
		$this->assign('currency_list',$currency_list);
		$emailtpl = $this->model('email')->simple_list($this->session->val('admin_site_id'));
		if($emailtpl){
			foreach($emailtpl as $key=>$value){
				if(substr($value['identifier'],0,4) == 'sms_'){
					unset($emailtpl[$key]);
				}
			}
			$this->assign("emailtpl",$emailtpl);
		}
		$c_rs = $this->model('sysmenu')->get_one_condition("appfile='list' AND parent_id>0");
		$gid = $c_rs["id"];
		unset($c_rs);
		$popedom_list = $this->model('popedom')->get_all("pid=0 AND gid='".$gid."'",false,false);
		$this->assign("popedom_list",$popedom_list);
		if($id){
			$popedom_list2 = $this->model('popedom')->get_all("pid='".$id."' AND gid='".$gid."'",false,false);
			if($popedom_list2){
				$m_plist = array();
				foreach($popedom_list2 AS $key=>$value){
					$m_plist[] = $value["identifier"];
				}
				$this->assign("popedom_list2",$m_plist);
			}
		}
		$note_content = form_edit('admin_note',$rs['admin_note'],"editor","btn[image]=1&height=180");
		$this->assign('note_content',$note_content);
		
		$grouplist = $this->model('usergroup')->get_all("status=1");
		if($grouplist){
			foreach($grouplist as $key=>$value){
				$tmp_popedom = array('read'=>false,'post'=>false,'reply'=>false,'post1'=>false,'reply1'=>false);
				$tmp = $value['popedom'] ? unserialize($value['popedom']) : false;
				if($tmp && $tmp[$this->session->val('admin_site_id')]){
					$tmp = $tmp[$this->session->val('admin_site_id')];
					$tmp = explode(",",$tmp);
					foreach($tmp_popedom as $k=>$v){
						if($id && in_array($k.':'.$id,$tmp)){
							$tmp_popedom[$k] = true;
						}else{
							if(!$id && $k == 'read'){
								$tmp_popedom[$k] = true;
							}
						}
					}
				}
				$value['popedom'] = $tmp_popedom;
				$grouplist[$key] = $value;
			}
		}
		$this->assign('grouplist',$grouplist);
		$freight = $this->model('freight')->get_all();
		$this->assign('freight',$freight);

		$tag_config = $this->model('tag')->config();
		$this->assign('tag_config',$tag_config);
		$this->view("project_set_new");
	}

	/**
	 * 專案屬性擴充套件，即擴充套件專案自身欄位配置，要操作此項要求普通管理員有配置許可權（project:set）
	 * @引數 id，專案ID，不能為空或0
	**/
	public function content_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'),$this->url("project"));
		}
		$this->assign("id",$id);
		$rs = $this->model('project')->get_one($id);
		$this->assign("rs",$rs);
		$ext_module = "project-".$id;
		$this->assign("ext_module",$ext_module);
		$extlist = $this->model('ext')->ext_all($ext_module);
		if($extlist){
			$tmp = false;
			foreach($extlist AS $key=>$value){
				if($value["ext"]){
					$ext = unserialize($value["ext"]);
					foreach($ext AS $k=>$v){
						$value[$k] = $v;
					}
				}
				$tmp[] = $this->lib('form')->format($value);
				$this->lib('form')->cssjs($value);
			}
			$this->assign('extlist',$tmp);
		}
		$this->view("project_content");
	}

	/**
	 * 取得模組的擴充套件欄位
	 * @引數 id 模組ID
	 * @返回 Json資料
	 * @更新時間 2016年07月21日
	**/
	public function mfields_f()
	{
		if(!$this->popedom['set']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rslist = $this->model('module')->fields_all($id);
		if(!$rslist){
			$this->success();
		}
		$list = array();
		foreach($rslist AS $key=>$value){
			$type = "text";
			if($value["field_type"] != "longtext" && $value["field_type"] != "longblob" && $value["field_type"] != "text"){
				$type = "varchar";
			}
			$list[] = array("id"=>$value["id"],"identifier"=>$value["identifier"],"title"=>$value["title"],'type'=>$type);
		}
		$this->success($list);
	}

	/**
	 * 儲存專案資訊
	 * @引數 id 專案ID，為0或空時表示新增
	 * @引數 title 專案名稱
	 * @引數 module 模組ID，為0表示不繫結模組
	 * @引數 cate 分類ID，為0表示不繫結分類，此項僅限module不為0時有效
	 * @引數 cate_multiple 是否支援多分類，僅限繫結分類後才有效
	 * @引數 tpl_index 自定義封面模板
	 * @引數 tpl_list 自定義列表模板
	 * @引數 tpl_content 自定義內容模板
	 * @引數 taxis 專案排序，值範圍是0-255，越小越往前靠
	 * @引數 parent_id 父級專案ID，為0表示當前為父級專案
	 * @引數 nick_title 專案別名，此項主要是給管理員使用，前臺無效
	 * @引數 alias_title 主題別名
	 * @引數 alias_note 主題備註
	 * @引數 psize 每個專案顯示多少主題，此項影響前臺佈局，僅在module不為0時有效
	 * @引數 ico 專案圖示，僅限後臺使用
	 * @引數 orderby 專案主題排序，僅在module不為0時有效
	 * @引數 lock 是否鎖定，對應資料表的 status，選中表示鎖定，未選中表示開放
	 * @引數 hidden 是否隱藏，對應資料表的hidden，選中表示隱藏，未選中表示顯示
	 * @引數 seo_title 專案SEO標題，此項為空將會呼叫全域性的SEO標題
	 * @引數 seo_keywords 專案SEO關鍵字，此項為空將會呼叫全域性的SEO關鍵字
	 * @引數 seo_desc 專案SEO描述，此項為空將會呼叫全域性的SEO描述
	 * @引數 subtopics 是否啟用子主題，即該主題存在簡單的父子關係，主要常用於導航
	 * @引數 is_search 是否支援搜尋，禁用後前臺將無法搜尋該專案下的主題資訊，僅限module不為0時有效
	 * @引數 is_tag 是否啟用自定義標籤，啟用於允許使用者針對主題設定標籤
	 * @引數 is_biz 是否啟用電商，啟用後需要配置相應的貨幣，運費等功能
	 * @引數 currency_id 貨幣ID
	 * @引數 admin_note 管理員備註，僅限後臺使用
	 * @引數 post_status 是否啟用釋出功能，啟用後您需要配置相應的釋出模板及釋出許可權
	 * @引數 comment_status 是否啟用評論，啟用後需要配置前臺許可權
	 * @引數 post_tpl 釋出模板，未定義將使用 標識_post 來替代，如果找不到，將會報錯
	 * @引數 etpl_admin 釋出通知管理員的郵件模板
	 * @引數 etpl_user 釋出通知會員的郵件模板
	 * @引數 etpl_comment_admin 評論通知管理員
	 * @引數 etpl_comment_user 評論通知會員
	 * @引數 is_attr 是否啟用主題屬性，主題屬性配置在 _data/xml/attr.xml 裡
	 * @引數 is_userid 主題是否繫結會員
	 * @引數 is_tpl_content 是否允許主題單獨繫結模板
	 * @引數 is_seo 是否啟用主題自定義SEO，未啟用將使用 分類SEO > 專案SEO > 全域性SEO
	 * @引數 is_identifier 是否啟用自定義標識
	 * @引數 tag 專案標籤，這裡設定後，在新增主題如果啟用標籤而未配置標籤，將會償試從這裡獲取
	 * @引數 biz_attr 是否啟用電商產品屬性功能，啟用後，電商商品支援自定義屬性以實現價格浮動
	 * @引數 freight 運費模板，為0表示不使用運費
	 * @引數 _popedom 管理員許可權
	 * @引數 read,post,reply,post1,reply1 前臺許可權，分別表示：檢視，釋出，評論，釋出免稽核，評論免稽核
	**/
	public function save_f()
	{
		if(!$this->popedom['set']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id','int');
		$title = $this->get("title");
		$identifier = $this->get("identifier");
		$module = $this->get("module","int");
		$cate = $this->get("cate","int");
		if($cate){
			$cate_multiple = $this->get('cate_multiple','int');
		}else{
			$cate_multiple = 0;
		}
		$tpl_index = $this->get("tpl_index");
		$tpl_list = $this->get("tpl_list");
		$tpl_content = $this->get("tpl_content");
		$taxis = $this->get("taxis","int");
		if(!$title){
			$this->error(P_Lang('名稱不能為空'));
		}
		$check_rs = $this->check_identifier($identifier,$id,$this->session->val('admin_site_id'));
		if($check_rs != "ok"){
			$this->error($check_rs);
		}
		$array = array();
		if(!$id){
			$array["site_id"] = $this->session->val('admin_site_id');
		}
		if($module){
			$m_rs = $this->model('module')->get_one($module);
			if($m_rs['mtype']){
				$array["orderby"] = $this->get("orderby2");
				$array["psize"] = $this->get("psize2","int");
			}else{
				$array["orderby"] = $this->get("orderby");
				$array["psize"] = $this->get("psize","int");
				$array["alias_title"] = $this->get("alias_title");
				$array["alias_note"] = $this->get("alias_note");
			}
		}
        if (isset($_POST["parent_id"])) {
            $array["parent_id"] = $this->get("parent_id","int");
        }
        if (isset($_POST["module"])) {
            $array["module"] = $module;
        }
		$array["cate"] = $cate;
		$array['cate_multiple'] = $cate_multiple;
		$array["title"] = $title;
		$array["nick_title"] = $this->get("nick_title");
		$array["taxis"] = $taxis;
		$array["tpl_index"] = $tpl_index;
		$array["tpl_list"] = $tpl_list;
		$array["tpl_content"] = $tpl_content;
		$array["ico"] = $this->get("ico");
        $array["pic"] = $this->get("pic");
		$array["status"] = $this->get("lock","checkbox") ? 0 : 1;
		$array["hidden"] = $this->get("hidden","checkbox");
		$array["identifier"] = $identifier;
		$array["seo_title"] = $this->get("seo_title");
		$array["seo_keywords"] = $this->get("seo_keywords");
		$array["seo_desc"] = $this->get("seo_desc");
		$array["subtopics"] = $this->get("subtopics",'checkbox');
		$array["is_search"] = $this->get("is_search",'checkbox');
		$array["is_tag"] = $this->get("is_tag",'int');
		$array["is_biz"] = $this->get("is_biz",'checkbox');
		$array["currency_id"] = $this->get("currency_id",'int');
		$array["admin_note"] = $this->get("admin_note","html");
		$array['post_status'] = $this->get('post_status','checkbox');
		$array['comment_status'] = $this->get('comment_status','checkbox');
		$array['post_tpl'] = $this->get('post_tpl');
		$array['etpl_admin'] = $this->get('etpl_admin');
		$array['etpl_user'] = $this->get('etpl_user');
		$array['etpl_comment_admin'] = $this->get('etpl_comment_admin');
		$array['etpl_comment_user'] = $this->get('etpl_comment_user');
		$array['is_attr'] = $this->get('is_attr','checkbox');
		$array['is_userid'] = $this->get('is_userid','int');
		$array['is_tpl_content'] = $this->get('is_tpl_content','int');
		$array['is_seo'] = $this->get('is_seo','int');
		$array['is_identifier'] = $this->get('is_identifier','int');
		$array['tag'] = $this->get('tag');
		$array['biz_attr'] = $this->get('biz_attr');
		$array['freight'] = $this->get('freight');
		$array['list_fields'] = $this->get('list_fields');
		$ok_url = $this->url("newproject");
		$c_rs = $this->model('sysmenu')->get_one_condition("appfile='list' AND parent_id>0");
		$gid = $c_rs["id"];
		unset($c_rs);
		if($id){
			$action = $this->model('project')->save($array,$id);
			if(!$action){
				$this->error(P_Lang('編輯失敗'));
			}
			$rs = $this->model('project')->get_one($id);
			$popedom = $this->get("_popedom","int");
			if($popedom && is_array($popedom)){
				$str = implode(",",$popedom);
				$tlist = array();
				$newlist = $this->model('popedom')->get_all("id IN(".$str.")",false,false);
				if($newlist){
					foreach($newlist AS $key=>$value){
						$tmp_condition = "pid='".$id."' AND gid='".$gid."' AND identifier='".$value["identifier"]."'";
						$tmp = $this->model('popedom')->get_one_condition($tmp_condition);
						if(!$tmp){
							$tmp_value = $value;
							unset($tmp_value["id"]);
							$tmp_value["pid"] = $id;
							$this->model('popedom')->save($tmp_value);
						}
						$tlist[] = $value["identifier"];
					}
					$alist = $this->model('popedom')->get_all("gid='".$gid."' AND pid='".$id."'",false,false);
					if($alist){
						foreach($alist AS $key=>$value){
							if(!in_array($value["identifier"],$tlist)){
								$this->model('popedom')->delete($value["id"]);
							}
						}
					}
				}
			}
		}else{
			$id = $this->model('project')->save($array);
			if(!$id){
				$this->error(P_Lang('新增失敗'));
			}
			$popedom = $this->get("_popedom","int");
			if($popedom && is_array($popedom)){
				$str = implode(",",$popedom);
				$newlist = $this->model('popedom')->get_all("id IN(".$str.")",false,false);
				if($newlist){
					foreach($newlist AS $key=>$value){
						$tmp_condition = "pid='".$id."' AND gid='".$gid."' AND identifier='".$value["identifier"]."'";
						$tmp = $this->model('popedom')->get_one_condition($tmp_condition);
						if(!$tmp){
							$tmp_value = $value;
							unset($tmp_value["id"]);
							$tmp_value["pid"] = $id;
							$this->model('popedom')->save($tmp_value);
						}
					}
				}
			}
		}
		$this->_save_user_group($id);
		$this->_save_tag($id);
		$this->success();
	}

	/**
	 * 更新專案Tag標籤
	 * @引數 $id，專案ID
	 * @返回 true
	**/
	private function _save_tag($id)
	{
		$rs = $this->model('project')->get_one($id,false);
		$this->model('tag')->update_tag($rs['tag'],'p'.$id);
		return true;
	}

	/**
	 * 更新前臺會員及遊客許可權，更新每個專案對應的前臺會員或遊客的許可權
	 * @引數 $id，專案ID
	 * @返回 true或false
	**/
	private function _save_user_group($id)
	{
		$grouplist = $this->model('usergroup')->get_all("status=1");
		if(!$grouplist){
			return false;
		}
		$tmp_popedom = array('read','post','reply','post1','reply1');
		foreach($grouplist as $key=>$value){
			$tmp = false;
			$plist = $value['popedom'] ? unserialize($value['popedom']) : false;
			if($plist && $plist[$this->session->val('admin_site_id')]){
				$tmp = $plist[$this->session->val('admin_site_id')];
				$tmp = explode(",",$tmp);
			}
			foreach($tmp_popedom as $k=>$v){
				$checked = $this->get("p_".$v."_".$value['id'],'checkbox');
				if($checked){
					$tmp[] = $v.":".$id;
				}else{
					foreach((array)$tmp as $kk=>$vv){
						if($vv == $v.":".$id){
							unset($tmp[$kk]);
						}
					}
				}
			}
			if($tmp){
				$tmp = array_unique($tmp);
				$tmp = implode(",",$tmp);
				$plist[$this->session->val('admin_site_id')] = $tmp;
			}else{
				$plist[$this->session->val('admin_site_id')] = array();
			}
			$this->model('usergroup')->save(array('popedom'=>serialize($plist)),$value['id']);
		}
		return true;
	}

	/**
	 * 專案擴充套件欄位儲存
	 * @引數 id，專案ID，此項不能為空
	 * @引數 title，專案名稱
	**/
	public function content_save_f()
	{
		if(!$this->popedom['set']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$title = $this->get("title");
		if(!$title){
			$this->error(P_Lang('名稱不能為空'));
		}
		$array = array("title"=>$title);
		$this->model('project')->save($array,$id);
		ext_save("project-".$id);
		$this->success();
	}

	/**
	 * 檢測標識串是否被使用了
	 * @引數 $sign 檢測的標識
	 * @引數 $id 忽略的專案ID，用於編輯時跳過自身
	 * @引數 $site_id 站點ID
	 * @返回 ok是表示檢測通過，其他字元表示檢測不通過
	**/
	private function check_identifier($sign,$id=0,$site_id=0)
	{
		if(!$sign){
			return P_Lang('標識串不能為空');
		}
		$sign = strtolower($sign);
		//字串是否符合條件
		if(!preg_match("/[a-z][a-z0-9\_\-\.]+/",$sign)){
			return P_Lang("標識不符合系統要求，限字母、數字及下劃線（中劃線）且必須是字母開頭");
		}
		if(!$site_id){
			$site_id = $this->session->val('admin_site_id');
		}
		$rs = $this->model('id')->check_id($sign,$site_id,$id);
		if($rs){
			return P_Lang('識別符號已被使用');
		}
		return 'ok';
	}

	/**
	 * 刪除專案操作，要求普通管理員有配置許可權（project:set）
	 * @引數 id 專案ID
	 * @返回 json字串
	**/
	public function delete_f()
	{
		if(!$this->popedom['set']){
			$this->json(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		//判斷是否有子專案
		$list = $this->model('project')->get_son($id);
		if($list){
			$this->json(P_Lang('已存在子專案，請移除子專案'));
		}
		$rs = $this->model('project')->get_one($id,false);
		if(!$rs){
			$this->json(P_Lang('專案資訊不存在'));
		}
		$this->model('project')->delete_project($id);
		$this->model('tag')->stat_delete('p'.$id,"title_id");
		$this->json(true);
	}

	/**
	 * 更新專案狀態，要求普通管理員有配置許可權（project:set）
	 * @引數 id 專案ID
	 * @返回 json字串
	**/
	public function status_f()
	{
		if(!$this->popedom['set']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$list = explode(",",$id);
		$status = $this->get("status","int");
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			$this->model('project')->status($value,$status);
		}
		$this->success();
	}

	/**
	 * 專案排序
	 * @引數 id 專案ID
	 * @返回 json字串
	**/
	public function sort_f()
	{
		$sort = $this->get('sort');
		if(!$sort || !is_array($sort)){
			$this->json(P_Lang('更新排序失敗'));
		}
		foreach($sort AS $key=>$value){
			$key = intval($key);
			$value = intval($value);
			$this->model('project')->update_taxis($key,$value);
		}
		$this->json(true);
	}

	/**
	 * 取得全部分類下的根分類
	**/
	public function rootcate_f()
	{
		$catelist = $this->model('cate')->root_catelist($this->session->val('admin_site_id'));
		$this->json($catelist,true);
	}

	/**
	 * 專案複製操作
	 * @引數 id 要複製的專案ID
	**/
	public function copy_f()
	{
		if(!$this->popedom['set']){
			$this->json(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->json(P_Lang('未指定專案ID'));
		}
		$rs = $this->model('project')->get_one($id,false);
		if(!$rs){
			$this->json(P_Lang('專案不存在'));
		}
		//自定義標識串
		$identifier = $rs['identifier'].$_SESSION['admin_id'].$this->time;
		$array = array();
		$array['site_id'] = $_SESSION["admin_site_id"];
		$array["parent_id"] = $rs['parent_id'];
		$array["module"] = $rs['module'];
		$array["cate"] = $cate;
		$array["title"] = $rs['title'];
		$array["nick_title"] = $rs['nick_title'];
		$array["alias_title"] = $rs['alias_title'];
		$array["alias_note"] = $rs['alias_note'];
		$array["psize"] = $rs['psize'];
		$array["taxis"] = $rs['taxis'];
		if($rs['module']){
			$array["tpl_index"] = $rs['tpl_index'];
			$array["tpl_list"] = $rs['tpl_list'] ? $rs['tpl_list'] : $rs['identifier'].'_list';
			$array["tpl_content"] = $rs['tpl_content'] ? $rs['tpl_content'] : $rs['identifier'].'_content';
		}else{
			$array["tpl_index"] = $rs['tpl_index'];
			$array["tpl_list"] = $rs['tpl_list'];
			$array["tpl_content"] = $rs['tpl_content'];
			if(!$array['tpl_list'] && !$array['tpl_content'] && !$array['tpl_index']){
				$array['tpl_index'] = $rs['identifier'].'_page';
			}
		}
		$array["ico"] = $rs['ico'];
		$array["orderby"] = $rs['orderby'];
		$array["status"] = $rs['status'];
		$array["hidden"] = $rs['hidden'];
		$array["identifier"] = $identifier;
		$array["subtopics"] = $rs['subtopics'];
		$array["is_search"] = $rs['is_search'];
		$array["is_tag"] = $rs['is_tag'];
		$array["is_biz"] = $rs['is_biz'];
		$array["currency_id"] = $rs['currency_id'];
		$array['post_status'] = $rs['post_status'];
		$array['comment_status'] = $rs['comment_status'];
		$array['post_tpl'] = $rs['post_tpl'];
		$array['etpl_admin'] = $rs['etpl_admin'];
		$array['etpl_user'] = $rs['etpl_user'];
		$array['etpl_comment_admin'] = $rs['etpl_comment_admin'];
		$array['etpl_comment_user'] = $rs['etpl_comment_user'];
		$array['is_attr'] = $rs['is_attr'];
		$c_rs = $this->model('sysmenu')->get_one_condition("appfile='list' AND parent_id>0");
		$gid = $c_rs["id"];
		$nid = $this->model('project')->save($array);
		if(!$nid){
			$this->json(P_Lang('複製專案失敗'));
		}
		//配置後臺許可權
		$popedom_list = $this->model('popedom')->get_all("pid=0 AND gid='".$gid."'",false,false);
		if($popedom_list){
			foreach($popedom_list as $key=>$value){
				$tmp_array = array('gid'=>$gid,'pid'=>$nid,'title'=>$value['title'],'identifier'=>$value['identifier']);
				$tmp_array['taxis'] = $value['taxis'];
				$this->model('popedom')->save($tmp_array);
			}
		}
		//儲存前臺許可權
		$grouplist = $this->model('usergroup')->get_all("status=1");
		if($grouplist){
			$tmp_popedom = array('read','post','reply','post1','reply1');
			foreach($grouplist as $key=>$value){
				$tmp = array();
				$plist = $value['popedom'] ? unserialize($value['popedom']) : false;
				if($plist && $plist[$this->session->val('admin_site_id')]){
					$tmp = $plist[$this->session->val('admin_site_id')];
					$tmp = explode(",",$tmp);
				}
				foreach($tmp_popedom as $k=>$v){
					$tmp[] = $v.":".$nid;
				}
				$tmp = array_unique($tmp);
				$tmp = implode(",",$tmp);
				$plist[$this->session->val('admin_site_id')] = $tmp;
				$this->model('usergroup')->save(array('popedom'=>serialize($plist)),$value['id']);
			}
		}
		$this->json(true);
	}

	/**
	 * 專案匯出
	 * @引數 id，專案ID
	**/
	public function export_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定專案ID'),$this->url('project'),'error');
		}
		$rs = $this->model('project')->get_one($id,false);
		if(!$rs){
			$this->error(P_Lang('專案不存在'),$this->url('project'),'error');
		}
		unset($rs['id'],$rs['parent_id'],$rs['site_id']);
		foreach($rs as $key=>$value){
			if($value == ''){
				unset($rs[$key]);
			}
		}
		if($rs['module']){
			$module = $this->model('module')->get_one($rs['module']);
			unset($module['id']);
			$module_list = $this->model('module')->fields_all($rs['module'],'identifier');
			if($module_list){
				$tmplist = array();
				foreach($module_list as $key=>$value){
					unset($value['id'],$value['module_id']);
					if($value['ext']){
						$value['ext'] = unserialize($value['ext']);
					}
					$tmplist[$key] = $value;
				}
				$module['_fields'] = $tmplist;
			}
			$rs['_module'] = $module;
			unset($rs['module']);
		}
		//擴充套件欄位
		$extlist = $this->model('ext')->ext_all('project-'.$id,false);
		if($extlist){
			$tmplist = array();
			foreach($extlist as $key=>$value){
				unset($value['id'],$value['module']);
				if($value['ext']){
					$value['ext'] = unserialize($value['ext']);
				}
				$tmplist[$value['identifier']] = $value;
			}
			$rs['_ext'] = $tmplist;
		}
		$tmpfile = $this->dir_cache.'project.xml';
		$this->lib('xml')->save($rs,$tmpfile);
		$this->lib('phpzip')->set_root($this->dir_cache);
		$zipfile = $this->dir_cache.$this->time.'.zip';
		$this->lib('phpzip')->zip($tmpfile,$zipfile);
		$this->lib('file')->rm($tmpfile);
		//下載zipfile
		$this->lib('file')->download($zipfile,$rs['title']);
	}

	/**
	 * 專案匯入
	 * @變數 zipfile 指定的ZIP檔案地址
	**/
	public function import_f()
	{
		$zipfile = $this->get('zipfile');
		if(!$zipfile){
			$this->lib('form')->cssjs(array('form_type'=>'upload'));
			$this->addjs('js/webuploader/admin.upload.js');
			$this->view('project_import');
		}
		if(strpos($zipfile,'..') !== false){
			$this->error(P_Lang('不支援帶..上級路徑'));
		}
		if(!file_exists($this->dir_root.$zipfile)){
			$this->error(P_Lang('ZIP檔案不存在'));
		}
		$this->lib('phpzip')->unzip($this->dir_root.$zipfile,$this->dir_cache);
		if(!file_exists($this->dir_cache.'project.xml')){
			$this->error(P_Lang('匯入專案失敗，請檢查解壓縮是否成功'));
		}
		$rs = $info = $this->lib('xml')->read($this->dir_cache.'project.xml',true);
		if(!$rs){
			$this->error(P_Lang('XML內容解析異常'));
		}
		$tmp = $rs;
		if(isset($tmp['_module'])){
			unset($tmp['_module']);
		}
		if(isset($tmp['_ext'])){
			unset($tmp['_ext']);
		}
		$tmp['site_id'] = $this->session->val('admin_site_id');
		$tmp['identifier'] = 'i'.$this->time;
		
		$insert_id = $this->model('project')->save($tmp);
		if(!$insert_id){
			$this->error(P_Lang('專案匯入失敗，儲存專案基本資訊錯誤'));
		}
		
		if($rs['_ext']){
			foreach($rs['_ext'] as $key=>$value){
				if($value['ext']){
					$value['ext'] = serialize($value['ext']);
				}
				$value['ftype'] = 'project-'.$insert_id;
				$this->model('ext')->save($value);
			}
		}
		if($rs['_module']){
			$tmp2 = $rs['_module'];
			if(isset($tmp2['_fields'])){
				unset($tmp2['_fields']);
			}
			$mid = $this->model('module')->save($tmp2);
			if(!$mid){
				$this->model('project')->delete_project($insert_id);
				$this->error(P_Lang('專案匯入失敗：模組建立失敗'));
			}
			$this->model('module')->create_tbl($mid);
			$tbl_exists = $this->model('module')->chk_tbl_exists($mid,$tmp2['mtype']);
			if(!$tbl_exists){
				$this->model('module')->delete($mid);
				$this->model('project')->delete_project($insert_id);
				$this->error(P_Lang('建立模組表失敗'));
			}
			if(isset($rs['_module']['_fields']) && $rs['_module']['_fields']){
				foreach($rs['_module']['_fields'] as $key=>$value){
					if($value['ext'] && is_array($value['ext'])){
						$value['ext'] = serialize($value['ext']);
					}
					$value['module_id'] = $mid;
					$this->model('module')->fields_save($value);
					$this->model('module')->create_fields($value['id']);
				}
			}
			//更新專案和模組之間的關係
			$array = array('module'=>$mid);
			$this->model('project')->update($array,$insert_id);
		}
		$this->lib('file')->rm($this->dir_cache.'project.xml');
		$this->lib('file')->rm($this->dir_cache.$zipfile);
		$this->success();
	}

	public function hidden_f()
	{
		if(!$this->popedom['set']){
			$this->error(P_Lang('您沒有許可權執行狀態操作'));
		}
		$id = $this->get('id');
		$hidden = $this->get('hidden','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		if($hidden>1){
			$hidden = 1;
		}
		if($hidden < 0){
			$hidden = 0;
		}
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			if(!$value || !trim($value) || !intval($value)){
				continue;
			}
			$this->model('project')->set_hidden(intval($value),$hidden);
		}
		$this->success();
	}

	public function icolist_f()
	{
		$icolist = $this->lib('file')->ls('images/ico/');
		if(!file_exists($this->dir_root.'res/ico/')){
			$this->lib('file')->make($this->dir_root.'res/ico/');
		}
		$tmplist = $this->lib('file')->ls('res/ico/');
		if($tmplist){
			$icolist = array_merge($icolist,$tmplist);
		}
		$this->assign('icolist',$icolist);
		$this->lib('form')->cssjs(array('form_type'=>'upload'));
		$this->addjs('js/webuploader/admin.upload.js');
		$this->view('project_icolist');
	}
}
