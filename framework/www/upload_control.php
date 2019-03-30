<?php
/**
 * 附件上傳相關操作
 * @package phpok\www
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年12月08日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class upload_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 附件上傳
	 * @引數 cateid 分類ID
	 * @引數 upfile 上傳的附件
	 * @返回 JSON資料
	**/
	public function save_f()
	{
		$this->popedom();
		$cateid = $this->get('cateid','int');
		$rs = $this->upload_base('upfile',$cateid);
		if(!$rs || $rs['status'] != 'ok'){
			$this->json($rs['error']);
		}
		unset($rs['status']);
		$rs['uploadtime'] = date("Y-m-d H:i:s",$rs['addtime']);
		$this->json($rs,true);
	}

	/**
	 * 設定許可權，防止非法人員上傳
	**/
	private function popedom()
	{
		if(!$this->site['upload_guest'] && !$this->session->val('user_id')){
			$this->json(P_Lang('系統已禁止遊客上傳，請聯絡管理員'));
		}
		if(!$this->site['upload_user'] && $this->session->val('user_id')){
			$this->json(P_Lang('系統已禁止會員上傳，請聯絡管理員'));
		}
		return true;
	}


	/**
	 * 基礎上傳
	 * @引數 $input_name 上傳表單名
	 * @引數 $cateid 要儲存的目錄
	 * @返回 陣列，含status狀態，ok表示上傳成功，其他為失敗
	**/
	private function upload_base($input_name='upfile',$cateid=0)
	{
		$rs = $this->lib('upload')->getfile($input_name,$cateid);
		if($rs["status"] != "ok"){
			return $rs;
		}
		$array = array();
		$array["cate_id"] = $rs['cate']['id'];
		$array["folder"] = $rs['folder'];
		$array["name"] = basename($rs['filename']);
		$array["ext"] = $rs['ext'];
		$array["filename"] = $rs['filename'];
		$array["addtime"] = $this->time;
		$array["title"] = $rs['title'];
		$array['session_id'] = $this->session->sessid();
		$array['user_id'] = $this->session->val('user_id');
		$arraylist = array("jpg","gif","png","jpeg");
		if(in_array($rs["ext"],$arraylist)){
			$img_ext = getimagesize($this->dir_root.$rs['filename']);
			$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
			$array["attr"] = serialize($my_ext);
		}
		$id = $this->model('res')->save($array);
		if(!$id){
			$this->lib('file')->rm($this->dir_root.$rs['filename']);
			return array('status'=>'error','error'=>P_Lang('圖片儲存失敗'));
		}
		$this->model('res')->gd_update($id);
		$rs = $this->model('res')->get_one($id);
		$rs["status"] = "ok";
		return $rs;
	}

	/**
	 * 附件上傳替換
	 * @引數 oldid 舊附件ID
	 * @引數 
	 * @引數 
	 * @引數 
	 * @返回 
	 * @更新時間 
	**/
	public function replace_f()
	{
		$this->popedom();
		$id = $this->get("oldid",'int');
		if(!$id){
			$this->json(P_Lang('沒有指定要替換的附件'));
		}
		$old_rs = $this->model('res')->get_one($id);
		if(!$old_rs){
			$this->json(P_Lang('資源不存在'));
		}
		$rs = $this->lib('upload')->upload('upfile');
		if($rs["status"] != "ok")
		{
			$this->json(P_Lang('附件上傳失敗'));
		}
		$arraylist = array("jpg","gif","png","jpeg");
		$my_ext = array();
		if(in_array($rs["ext"],$arraylist))
		{
			$img_ext = getimagesize($rs["filename"]);
			$my_ext["width"] = $img_ext[0];
			$my_ext["height"] = $img_ext[1];
		}
		//替換資源
		$this->lib('file')->mv($rs["filename"],$old_rs["filename"]);
		$tmp = array("addtime"=>$this->time);
		$tmp["attr"] = serialize($my_ext);
		$this->model('res')->save($tmp,$id);
		//更新附件擴展信息
		$this->model('res')->gd_update($id);
		$rs = $this->model('res')->get_one($id);
		$this->json($rs,true);
	}

	public function thumbshow_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$list = explode(",",$id);
		$newlist = array();
		foreach($list AS $key=>$value){
			$value = intval($value);
			if($value){
				$newlist[] = $value;
			}
		}
		$id = implode(",",$newlist);
		if(!$id){
			$this->json(P_Lang('請傳遞正確的附件ID'));
		}
		$rslist = $this->model("res")->get_list_from_id($id);
		if($rslist){
			$reslist = array();
			foreach($newlist as $key=>$value){
				if($rslist[$value]){
					$reslist[] = $rslist[$value];
				}
			}
			$this->json($reslist,true);
		}
		$this->json(P_Lang('附件資訊獲取失敗，可能已經刪除'));
	}

	public function editopen_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定ID'));
		}
		$rs = $this->model('res')->get_one($id);
		if(!$rs){
			error(P_Lang('資料不存在'));
		}
		$this->popedom_action($rs['session_id'],$rs['user_id']);
		$note = form_edit('note',$rs['note'],'editor','width=650&height=250&etype=simple');
		$this->assign('rs',$rs);
		$this->assign('note',$note);
		$this->view($this->dir_phpok."open/res_editopen.html",'abs-file',false);
	}

	public function editopen_save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('res')->get_one($id);
		if(!$rs){
			$this->json(P_Lang('資料不存在'));
		}
		$this->popedom_action($rs['session_id'],$rs['user_id']);
		$title = $this->get('title');
		if(!$title){
			$this->json(P_Lang('附件標題不能為空'));
		}
		$note = $this->get('note','html');
		$this->model('res')->save(array('title'=>$title,'note'=>$note),$id);
		$this->json(true);
	}

	public function preview_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定ID'));
		}
		$rs = $this->model('res')->get_one($id);
		if(!$rs){
			error(P_Lang('資料不存在'));
		}
		if($_SESSION['user_id']){
			if($_SESSION['user_id'] != $rs['user_id'] && $rs['session_id'] != $this->session->sessid()){
				error(P_Lang('您沒有許可權檢視此附件資訊'));
			}
		}else{
			if(!$rs['session_id']){
				error(P_Lang('您沒有許可權檢視此附件資訊'));
			}
			if($rs['session_id'] != $this->session->sessid()){
				error(P_Lang('您沒有許可權檢視此附件資訊'));
			}
		}
		$arraylist = array('jpg','png','gif','jpeg');
		if($rs['ext'] && in_array($rs['ext'],$arraylist)){
			$this->assign('ispic',true);
		}
		$this->assign('rs',$rs);
		$this->view($this->dir_phpok."open/res_openview.html",'abs-file',false);
	}

	public function delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('res')->get_one($id);
		if(!$rs){
			$this->json(P_Lang('附件資訊不存在'));
		}
		$this->popedom_action($rs['session_id'],$rs['user_id']);
		$this->model('res')->delete($id);
		$this->json(true);
	}

	private function popedom_action($sessid='',$uid=0)
	{
		if($_SESSION['user_id']){
			if($uid && $uid == $_SESSION['user_id']){
				return true;
			}
			if($sessid && $sessid == $this->session->sessid()){
				return true;
			}
		}else{
			if($sessid && $sessid == $this->session->sessid()){
				return true;
			}
		}
		$info = P_Lang('沒有許可權操作');
		$this->json($info);
	}
}
?>