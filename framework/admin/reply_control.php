<?php
/**
 * 回覆內容管理
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年07月31日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class reply_control extends phpok_control
{
	var $popedom;
	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("reply");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 取得網站全部評論
	 * @引數 status 狀態，1為已稽核，2為未稽核，0或空為全部
	 * @引數 keywords 關鍵字，要檢索的關鍵字
	 * @引數 pageid 分頁ID
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$pageurl = $this->url("reply");
		$status = $this->get("status","int");
		$condition = "admin_id=0 ";
		if($status){
			$n_status = $status == 1 ? "1" : "0";
			$condition .= "AND status=".$n_status." ";
			$pageurl .= "&status=".$status; 
			$this->assign("status",$status);
		}
		//關鍵字
		$keywords = $this->get("keywords");
		if($keywords){
			$condition .= "AND (title LIKE '%".$keywords."%' OR content LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
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
			$string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上一頁').'&next='.P_Lang('下一頁').'&last='.P_Lang('尾頁').'&half=5';
			$string.= '&add='.P_Lang('數量：').'(total)/(psize)'.P_Lang('，').P_Lang('頁碼：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->assign("total",$total);
		$this->view("reply_index");
	}

	/**
	 * 讀取某個主題下的全部評論
	 * @引數 tid 主題ID
	 * @引數 status 狀態，1已稽核，2未稽核，0或空表示全部
	 * @引數 keywords 關鍵字，檢索評論關鍵字
	 * @引數 pageid 分頁ID
	**/
	public function list_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$tid = $this->get("tid","int");
		if(!$tid){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('list')->get_one($tid);
		$this->assign("rs",$rs);
		$pageurl = $this->url("reply","list","tid=".$tid);
		$status = $this->get('status',"int");
		$condition = "tid='".$tid."' AND parent_id=0";
		if($status){
			$n_status = $status == 1 ? "1" : "0";
			$condition .= " AND status='".$n_status."'";
			$pageurl .= "&status=".$status; 
			$this->assign("status",$status);
		}
		$keywords = $this->get("keywords");
		if($keywords){
			$condition .= " AND (content LIKE '%".$keywords."%' OR adm_content LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = $this->config["psize"] ? $this->config["psize"] : 30;
		$total = $this->model('reply')->get_total($condition);
		if(!$total){
			$this->error(P_Lang('沒有評論內容'));
		}
		$offset = ($pageid-1) * $psize;
		$rslist = $this->model('reply')->get_list($condition,$offset,$psize,"id");
		if(!$rslist){
			$this->error(P_Lang('沒有找到評論內容'));
		}
		$uidlist = array();
		foreach($rslist AS $key=>$value){
			if($value["uid"]){
				$uidlist[] = $value["uid"];
			}
		}
		$idlist = array_keys($rslist);
		$condition = "tid='".$tid."' AND parent_id IN(".implode(",",$idlist).")";
		$sublist = $this->model('reply')->get_list($condition,0,0);
		if($sublist){
			foreach($sublist AS $key=>$value){
				if($value["uid"]){
					$uidlist[] = $value["uid"];
				}
				$rslist[$value["parent_id"]]["sublist"][$value["id"]] = $value;
			}
		}
		if($uidlist && count($uidlist)>0){
			$uidlist = array_unique($uidlist);
			$ulist = $this->model('user')->get_all_from_uid(implode(",",$uidlist),'id');
			if(!$ulist){
				$ulist = array();
			}
			foreach($rslist AS $key=>$value){
				if($value["uid"]){
					$value["uid"] = $ulist[$value["uid"]];
				}
				if($value["sublist"]){
					foreach($value["sublist"] AS $k=>$v){
						if($v){
							$v["uid"] = $ulist[$v["uid"]];
						}
						$value["sublist"][$k] = $v;
					}
				}
				$rslist[$key] = $value;
			}
		}
		$this->assign("rslist",$rslist);
		if($total>$psize){
			$string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上一頁').'&next='.P_Lang('下一頁').'&last='.P_Lang('尾頁').'&half=5';
			$string.= '&add='.P_Lang('數量').' (total)/(psize) '.P_Lang('頁碼').' (num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->assign("total",$total);
		$this->view('list_comment');
	}

	/**
	 * 變更評論的狀態
	 * @引數 status 要變更的狀態
	 * @引數 id 評論ID
	**/
	public function status_f()
	{
		if(!$this->popedom['status']){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('reply')->get_one($id);
		$status = $rs['status'] ? 0 : 1;
		$array = array("status"=>$status);
		$this->model('reply')->save($array,$id);
		if($status && $rs['tid'] && $rs['uid'] && ($rs['vtype'] == 'title' || $rs['vtype'] == 'order')){
			$this->model('wealth')->add_integral($rs['tid'],$rs['uid'],'comment',P_Lang('管理員稽核評論#{id}',array('id'=>$id)));
		}
		$this->success($status);
	}

	/**
	 * 刪除回覆
	 * @引數 
	 * @返回 
	 * @更新時間 
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
		$this->model('reply')->delete($id);
		$this->success();
	}

	/**
	 * 編輯評論內容
	 * @引數 id 評論ID
	**/
	public function edit_f()
	{
		if(!$this->popedom["modify"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('reply')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('資料記錄不存在'));
		}
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		if($rs['tid'] && in_array($rs['vtype'],array('title','order'))){
			$title_rs = $this->model('list')->get_one($rs["tid"]);
			$this->assign("title_rs",$title_rs);
		}
		$edit_content = form_edit('content',$rs['content'],'editor','width=680&height=180');
		$this->assign('edit_content',$edit_content);
		$this->assign('res_content',form_edit('pictures',$rs['res'],'upload','is_multiple=1'));
		$this->view("reply_content");
	}

	/**
	 * 儲存編輯的評論
	 * @引數 id 評論ID
	 * @引數 star 星數，最多5，最少為0
	 * @引數 content 評論內容
	 * @引數 status 狀態
	**/
	public function edit_save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$array = array();
		$array["star"] = $this->get("star","int");
		$array["content"] = $this->get("content",'html');
		$array["status"] = $this->get("status","int");
		$array['res'] = $this->get('pictures');
		$this->model('reply')->save($array,$id);
		$rs = $this->model('reply')->get_one($id);
		if($array["status"] && $rs['tid'] && $rs['uid']){
			$this->model('wealth')->add_integral($rs['tid'],$rs['uid'],'comment',P_Lang('管理員編輯評論#{id}',array('id'=>$rs['id'])));
		}
		$this->success();
	}

	/**
	 * 管理員回覆評論
	 * @引數 id 評論ID
	**/
	public function adm_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('reply')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('資料記錄不存在'));
		}
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$rslist = $this->model('reply')->adm_reply($id);
		$this->assign('rslist',$rslist);
		if($rs['tid'] && in_array($rs['vtype'],array('title','order'))){
			$title_rs = $this->model('list')->get_one($rs["tid"]);
			$this->assign("title_rs",$title_rs);
		}
		$edit_content = form_edit('content','','editor','width=680&height=300');
		$this->assign('edit_content',$edit_content);
		$this->view("reply_adm");
	}

	public function adm_save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$array = array();
		$array["content"] = $this->get("content","html");
		$array["addtime"] = $this->time;
		$array['admin_id'] = $this->session->val('admin_id');
		$array['parent_id'] = $id;
		$array['status'] = 1;
		$this->model('reply')->save($array);
		$this->success();
	}
}