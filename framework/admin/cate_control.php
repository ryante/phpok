<?php
/**
 * 欄目管理
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年09月16日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cate_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("cate");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 欄目列表
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$rslist = $this->model('cate')->get_all($this->session->val('admin_site_id'));
		$this->assign("rslist",$rslist);
		$this->view("cate_index");
	}

	/**
	 * 新增或編輯欄目資訊，支援自定義欄位
	**/
	public function set_f()
	{
		$parent_id = $this->get("parent_id","int");
		$id = $this->get("id","int");
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您沒有許可權執行此操作'),$this->url('cate'));
			}
			$rs = $this->model('cate')->get_one($id);
			$this->assign("id",$id);
			$this->assign("rs",$rs);
			$parent_id = $rs["parent_id"];
			$this->assign("parent_id",$parent_id);
			$ext_module = "cate-".$id;
			$extlist = $this->model('ext')->ext_all($ext_module);
			if(!$rs['parent_id']){
				$ext2 = $this->lib('xml')->read($this->dir_data.'xml/cate_extfields_'.$id.'.xml');
				if($ext2['fid']){
					$this->assign('ext2',explode(",",$ext2['fid']));
				}
			}
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您沒有許可權執行此操作'),$this->url('cate'));
			}
			$this->assign("parent_id",$parent_id);
			$ext_module = "add-cate";
			$extlist = $this->session->val('admin-add-cate');
			$taxis = $this->model('cate')->cate_next_taxis($parent_id);
			$this->assign('rs',array('taxis'=>$taxis));
			if($parent_id){
				$root_id = $parent_id;
				$this->model('cate')->get_root_id($root_id,$parent_id);
				$ext2 = $this->lib('xml')->read($this->dir_data.'xml/cate_extfields_'.$root_id.'.xml');
				if($ext2['fid']){
					$tmplist = explode(",",$ext2['fid']);
					foreach($tmplist as $key=>$value){
						$tmp = $this->model('fields')->default_one($value);
						if($tmp){
							unset($tmp['id']);
							$this->session->assign('admin-add-cate.'.$tmp['identifier'],$tmp);
						}
					}
					$extlist = $this->session->val('admin-add-cate');
				}
			}
		}
		$used_fields = array();
		if($extlist){
			$tmp = false;
			foreach($extlist as $key=>$value){
				if($value["ext"]){
					$ext = is_string($value['ext']) ? unserialize($value["ext"]) : $value['ext'];
					if(!$ext){
						$ext = array();
					}
					foreach($ext as $k=>$v){
						$value[$k] = $v;
					}
				}
				$tmp[] = $this->lib('form')->format($value);
				$this->lib('form')->cssjs($value);
				$used_fields[] = $value['identifier'];
			}
			$this->assign('extlist',$tmp);
			//已使用的擴充套件欄位
			$this->assign('used_fields',$used_fields);
		}
		$this->assign("ext_module",$ext_module);
		$parentlist = $this->model('cate')->get_all($this->session->val('admin_site_id'));
		$parentlist = $this->model('cate')->cate_option_list($parentlist);
		$this->assign("parentlist",$parentlist);
		$extfields = $this->model('fields')->default_all();
		$this->assign("extfields",$extfields);

		$tag_config = $this->model('tag')->config();
		$this->assign('tag_config',$tag_config);
		$this->view("cate_set");
	}

	/**
	 * 新增根分類
	**/
	public function add_f()
	{
		if(!$this->popedom["add"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$this->view("cate_open_add");
	}

	/**
	 * 分類狀態設定
	**/
	public function status_f()
	{
		if(!$this->popedom['status']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('cate')->cate_info($id,false);
		$content = $rs['status'] ? 0 : 1;
		$this->model('cate')->save(array('status'=>$content),$id);
		$this->success($content);
	}

	/**
	 * 彈窗分類資訊儲存
	**/
	public function open_save_f()
	{
		if(!$this->popedom['add']){
			$this->json(P_Lang('您沒有許可權執行此操作'));
		}
		$title = $this->get("title");
		$identifier = $this->get("identifier");
		if(!$title || !$identifier) $this->json("資訊不完整");
		$identifier2 = strtolower($identifier);
		//字串是否符合條件
		if(!preg_match("/[a-z][a-z0-9\_\-]+/",$identifier2)){
			$this->json(P_Lang('標識不符合系統要求，限字母、數字及下劃線（中劃線）且必須是字母開頭'));
		}
		$check = $this->model('id')->check_id($identifier2,$this->session->val('admin_site_id'));
		if($check){
			$this->json(P_Lang('標識已被使用'));
		}
		$array = array();
		$array["site_id"] = $this->session->val('admin_site_id');
		$array["parent_id"] = 0;
		$array["title"] = $title;
		$array["taxis"] = $this->model('cate')->cate_next_taxis(0);
		$array["psize"] = "";
		$array["tpl_list"] = "";
		$array["tpl_content"] = "";
		$array["status"] = 1;
		$array["identifier"] = $identifier;
		$id = $this->model('cate')->save($array);
		if(!$id){
			$this->json(P_Lang('分類新增失敗，請檢查！'));
		}
		$this->json(P_Lang('分類新增成功'),true);
	}

	/**
	 * 儲存分類資訊
	**/
	public function save_f()
	{
		$id = $this->get("id","int");
		if((!$id && !$this->popedom['add']) || ($id && !$this->popedom['modify'])){
			$this->error(P_Lang('您沒有許可權執行此操作'),$this->url('cate'));
		}
		$title = $this->get("title");
		$identifier = $this->get("identifier");
		$error_url = $this->url("cate","set");
		if($id) $error_url .= "&id=".$id;
		if(!$identifier){
			$this->error(P_Lang('標識不能為空'),$error_url);
		}
		$identifier2 = strtolower($identifier);
		if(!preg_match("/[a-z][a-z0-9\_\-]+/",$identifier2)){
			$this->error(P_Lang('標識不符合系統要求，限字母、數字及下劃線（中劃線）且必須是字母開頭'),$error_url);
		}
		$check = $this->model('id')->check_id($identifier2,$this->session->val('admin_site_id'),$id);
		if($check){
			$this->error(P_Lang('標識已被使用'),$error_url);
		}
		$parent_id = $this->get('parent_id','int');
		$array = array('title'=>$title,'identifier'=>$identifier);
		$array['parent_id'] = $parent_id;
		$array['status'] = $this->get('status','int');
		$array['tpl_list'] = $this->get('tpl_list');
		$array['tpl_content'] = $this->get('tpl_content');
		$array['psize'] = $this->get('psize','int');
		$array['taxis'] = $this->get('taxis','int');
		$array['seo_title'] = $this->get('seo_title');
		$array['seo_keywords'] = $this->get('seo_keywords');
		$array['seo_desc'] = $this->get('seo_desc');
		$array['tag'] = $this->get('tag');
		if(!$id){
			$array["site_id"] = $this->session->val('admin_site_id');
			$id = $this->model('cate')->save($array);
			if(!$id){
				$this->error(P_Lang('分類新增失敗，請檢查'),$error_url);
			}
			ext_save("admin-add-cate",true,"cate-".$id);
		}else{
			$rs = $this->model('cate')->get_one($id);
			if($parent_id == $id){
				$old_rs = $this->model('cate')->get_one($id);
				$parent_id = $old_rs["id"];
			}
			$son_cate_list = array();
			$this->son_cate_list($son_cate_list,$id);
			if(in_array($parent_id,$son_cate_list)){
				error(P_Lang('不允許將分類遷移至此分類下的子分類'),$error_url,"error");
			}
			$array["parent_id"] = $parent_id;
			$update = $this->model('cate')->save($array,$id);
			if(!$update){
				error(P_Lang('分類更新失敗'),$error_url);
			}
			ext_save("cate-".$id);
		}
		$this->_save_tag($id);
		if(!$parent_id && $id){
			$extfields = $this->get('_extfields');
			if($extfields){
				$extfields = implode(",",$extfields);
				$this->lib('xml')->save(array('fid'=>$extfields),$this->dir_data.'xml/cate_extfields_'.$id.'.xml');
			}else{
				$this->lib('file')->rm($this->dir_data.'xml/cate_extfields_'.$id.'.xml');
			}
		}
		$this->success(P_Lang('分類資訊配置成功'),$this->url("cate"));
	}

	private function _save_tag($id)
	{
		$rs = $this->model('cate')->cate_info($id,false);
		$this->model('tag')->update_tag($rs['tag'],'c'.$id);
		return true;
	}

	function son_cate_list(&$son_cate_list,$id)
	{
		$list = $this->model('cate')->get_son_id_list($id);
		if($list){
			foreach($list AS $key=>$value){
				$son_cate_list[] = $value;
			}
			$this->son_cate_list($son_cate_list,implode(",",$list));
		}
	}

	/**
	 * 刪除分類操作
	**/
	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$idlist = $this->model('cate')->get_son_id_list($id);
		if($idlist){
			$this->error(P_Lang('存在子欄目，不能直接刪除，請先刪除相應的子欄目'));
		}
		$check_rs = $this->model('project')->chk_cate($id);
		if($check_rs){
			$this->error(P_Lang('分類使用中，請先刪除'));
		}
		$this->model('cate')->cate_delete($id);
		$this->model('tag')->stat_delete('c'.$id,"title_id");
		$this->success();
	}

	/**
	 * 刪除擴充套件欄位
	**/
	public function ext_delete_f()
	{
		if(!$this->popedom['ext']){
			$this->json(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->json(P_Lang('未指定要刪除的ID'));
		}
		$cate_id = $this->get("cate_id","int");
		if($cate_id){
			$action = $this->model('cate')->cate_ext_delete($cate_id,$id);
			$this->json(P_Lang('擴充套件欄位刪除成功'),true);
		}
		$idstring = $this->session->val('cate_ext_id');
		if($idstring){
			$list = explode(",",$idstring);
			$tmp = array();
			foreach($list AS $key=>$value){
				if($value && $value != $id){
					$tmp[] = $value;
				}
			}
			$new_idstring = implode(",",$tmp);
			$this->session->assign('cate_ext_id',$new_idstring);
		}
		$this->json(P_Lang('擴充套件欄位刪除成功'),true);
	}

	/**
	 * 驗證分類是否可用
	**/
	public function check_f()
	{
		$id = $this->get("id","int");
		$sign = $this->get("sign");
		if(!$sign){
			$this->json(P_Lang('標識串不能為空'));
		}
		$sign = strtolower($sign);
		if(!preg_match("/[a-z][a-z0-9\_\-]+/",$sign)){
			$this->json(P_Lang('標識不符合系統要求，限字母、數字及下劃線（中劃線）且必須是字母開頭'));
		}
		$check = $this->model('id')->check_id($sign,$this->session->val('admin_site_id'),$id);
		if($check){
			$this->json(P_Lang('標識已被使用'));
		}
		$this->json("標識正常，可以使用",true);
	}

	/**
	 * 分類自定義排序
	**/
	public function taxis_f()
	{
		$taxis = $this->lib('trans')->safe("taxis");
		if(!$taxis || !is_array($taxis)){
			$this->json(P_Lang('沒有指定要更新的排序'));
		}
		foreach($taxis as $key=>$value){
			$this->model('cate')->update_taxis($key,$value);
		}
		$this->json(P_Lang('資料排序更新成功'),true);
	}
	
	/**
	 * 單個分類自定義排序
	**/
	public function ajax_taxis_f()
	{
		$id = $this->get('id','int');
		$taxis = $this->get('taxis','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$this->model('cate')->update_taxis($id,$taxis);
		$this->json(true);
	}
}