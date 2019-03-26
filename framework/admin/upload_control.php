<?php
/**
 * 附件上傳操作
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年07月18日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class upload_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 附件上傳，上傳的表單ID固定用upfile
	 * @引數 cateid 分類ID，數值
	**/
	public function save_f()
	{
		$cateid = $this->get('cateid','int');
		$rs = $this->upload_base('upfile',$cateid);
		if(!$rs || $rs['status'] != 'ok'){
			$tip = $rs['error'] ? $rs['error'] : $rs['content'];
			$this->json($tip);
		}
		unset($rs['status']);
		$rs['uploadtime'] = date("Y-m-d H:i:s",$rs['addtime']); 
		$this->json($rs,true);
	}

	/**
	 * 接收ZIP包上傳，主要用於更新及資料匯入，上傳的表單ID固定用upfile
	**/
	public function zip_f()
	{
		$rs = $this->lib('upload')->zipfile('upfile');
		if($rs['status'] != 'ok'){
			$this->json($rs['error']);
		}
		$this->json($rs['filename'],true);
	}

	/**
	 * 上傳圖片
	**/
	public function img_f()
	{
		$folder = $this->get('folder');
		if(!$folder){
			$this->json(P_Lang('未指附件路徑'));
		}
		if(substr($folder,0,1) == '/'){
			$this->json(P_Lang('目標路徑不能以/開頭'));
		}
		if(!is_dir($this->dir_root.$folder)){
			$this->json(P_Lang('目標資料夾不存在'));
		}
		$rs = $this->lib('upload')->imgfile('upfile',$folder);
		if($rs['status'] != 'ok'){
			$this->json($rs['error']);
		}
		$this->json($rs['filename'],true);
	}


	/**
	 * 基礎上傳
	 * @引數 $input_name，表單ID，預設是upfile
	 * @引數 $cateid，附件儲存到哪個分類下
	**/
	public function upload_base($input_name='upfile',$cateid=0)
	{
		$rs = $this->lib('upload')->getfile($input_name,$cateid);
		if($rs['status'] != 'ok'){
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
		$array['admin_id'] = $_SESSION['admin_id'];
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
	 * 附件替換式上傳，新檔案表單ID固定用upfile
	 * @引數 oldid，舊附件ID
	**/
	public function replace_f()
	{
		$id = $this->get("oldid",'int');
		if(!$id){
			$this->json(P_Lang('沒有指定要替換的附件'));
		}
		$old_rs = $this->model('res')->get_one($id);
		if(!$old_rs){
			$this->json(P_Lang('資源不存在'));
		}
		$rs = $this->lib('upload')->upload('upfile');
		if($rs["status"] != "ok"){
			$this->json(P_Lang('附件上傳失敗'));
		}
		$arraylist = array("jpg","gif","png","jpeg");
		$my_ext = array();
		if(in_array($rs["ext"],$arraylist)){
			$img_ext = getimagesize($rs["filename"]);
			$my_ext["width"] = $img_ext[0];
			$my_ext["height"] = $img_ext[1];
		}
		$this->lib('file')->mv($rs["filename"],$old_rs["filename"]);
		$tmp = array("addtime"=>$this->time);
		$tmp["attr"] = serialize($my_ext);
		$this->model('res')->save($tmp,$id);
		$this->model('res')->gd_update($id);
		$rs = $this->model('res')->get_one($id);
		$this->json($rs,true);
	}

	/**
	 * 縮圖列表
	 * @引數 id 多個附件ID用英文逗號隔開
	 * @返回 Json字串
	**/
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
			//排序
			$reslist = array();
			foreach($newlist as $key=>$value){
				if($rslist[$value]){
					$reslist[] = $rslist[$value];
				}
			}
			$this->json($reslist,true);
		}
		$this->json(P_Lang('附件資訊獲取失敗，可能已經刪除，請檢查'));
	}

	/**
	 * 彈出視窗編輯附件資訊
	 * @引數 id 附件ID
	**/
	public function editopen_f()
	{
		return $this->control('res')->set_f();
	}

	/**
	 * 儲存附件資訊
	 * @引數 id 附件ID
	 * @引數 title 附件名稱
	 * @引數 note 附件備註，支援HTML程式碼
	 * @返回 Json字串
	**/
	public function editopen_save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$title = $this->get('title');
		if(!$title){
			$this->json(P_Lang('附件標題不能為空'));
		}
		$note = $this->get('note','html');
		$this->model('res')->save(array('title'=>$title,'note'=>$note),$id);
		$this->json(true);
	}

	/**
	 * 附件預覽
	 * @引數 id 附件ID
	**/
	public function preview_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定ID'));
		}
		$rs = $this->model('res')->get_one($id);
		$arraylist = array('jpg','png','gif','jpeg');
		if($rs['ext'] && in_array($rs['ext'],$arraylist)){
			$this->assign('ispic',true);
		}
		$this->assign('rs',$rs);
		$this->view('res_openview');
	}

	/**
	 * 附件刪除
	 * @引數 id 附件ID
	**/
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
		if(!$this->session->val('admin_rs.if_system') && $rs['admin_id'] != $this->session->val('admin_id')){
			$this->json(P_Lang('非系統管理員不能刪除其他管理員上傳的附件'));
		}
		$this->model('res')->delete($id);
		$this->json(true);
	}
}
?>