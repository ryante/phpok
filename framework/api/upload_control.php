<?php
/*****************************************************************************************
	檔案： {phpok}/api/upload_control.php
	備註： 前端附件上傳介面
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2014年7月10日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class upload_control extends phpok_control
{
	private $u_id = 0; //會員ID
	private $u_name = 'guest'; //會員名字，遊客使用guest
	private $is_client = false;//判斷是否客戶端
	function __construct()
	{
		parent::control();
		$token = $this->get('token');
		if($token){
			$this->lib('token')->keyid($this->site['api_code']);
			$info = $this->lib('token')->decode($token);
			if(!$info || !$info['user_id'] || !$info['user_name']){
				$this->json(P_Lang('您還沒有登入，請先登入或註冊'));
			}
			$this->u_id = $info['user_id'];
			$this->u_name = $info['user_name'];
			$this->is_client = true;
		}else{
			if($_SESSION['user_id']){
				$this->u_id = $_SESSION['user_id'];
				$this->u_name = $_SESSION['user_name'];
			}
		}
	}

	//儲存上傳的資料，遊客僅能上傳jpg,png,gif,jpeg附件
	//普通會員能上傳的附件有：jpg,png,gif,jpeg,zip,rar,doc,xls,docx,xlsx,txt,ppt,pptx
	public function save_f()
	{
		if($this->u_id){
			if(!$this->site['upload_user']){
				$this->json(P_Lang('你沒有上傳許可權'));
			}
		}else{
			if(!$this->site['upload_guest']){
				$this->json(P_Lang('遊客沒有上傳許可權'));
			}
		}
		$cateid = $this->get('cateid','int');
		if($cateid){
			$cate_rs = $this->model('rescate')->get_one($cateid);
		}
		if(!$cate_rs){
			$cate_rs = $this->model('rescate')->get_default();
			if(!$cate_rs){
				$this->json(P_Lang('未配置附件儲存方式'));
			}
		}
		$filetypes = $this->u_id ? $cate_rs['filetypes'] : 'jpg,png,gif,rar,zip';
		$this->lib('upload')->set_type($filetypes);
		$this->lib('upload')->set_cate($cate_rs);
		$upload = $this->lib('upload')->upload('upfile');
		if(!$upload || !$upload['status']){
			$this->json(P_Lang('附件上傳失敗'));
		}
		if($upload['status'] != 'ok'){
			$this->json($upload['content']);
		}
		$array = array();
		$array["cate_id"] = $this->lib('upload')->get_cate();
		$array["folder"] = $this->lib('upload')->get_folder();
		$array["name"] = $upload['name'];
		$array["ext"] = $upload["ext"];
		$array["filename"] = $upload['filename'];
		$array["addtime"] = $this->time;
		$array['title'] = $upload['title'];
		$arraylist = array("jpg","gif","png","jpeg");
		if(in_array($upload['ext'],$arraylist)){
			$img_ext = getimagesize($this->dir_root.$upload['filename']);
			$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
			$array["attr"] = serialize($my_ext);
		}
		if(!$this->is_client){
			$array["session_id"] = $this->session->sessid();
		}
		$array['user_id'] = $this->u_id;
		$id = $this->model('res')->save($array);
		if(!$id){
			$this->lib('file')->rm($this->dir_root.$upload['filename']);
			$this->json(P_Lang('圖片儲存失敗'));
		}
		$this->model('res')->gd_update($id);
		$rs = $this->model('res')->get_one($id);
		$this->json($rs,true);
	}
}