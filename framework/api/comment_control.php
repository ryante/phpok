<?php
/**
 * 評論資訊
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年08月28日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class comment_control extends phpok_control
{
	private $user_groupid;
	public function __construct()
	{
		parent::control();
		$this->model('popedom')->siteid($this->site['id']);
		$groupid = $this->model('usergroup')->group_id($_SESSION['user_id']);
		if(!$groupid)
		{
			$this->json(P_Lang('無法獲取前端使用者組資訊'));
		}
		$this->user_groupid = $groupid;
	}

	//獲取評論資訊
	public function index_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定主題'));
		}
		$condition = "tid='".$id."' AND parent_id='0' ";
		$condition .= " AND (status=1 OR (status=0 AND (uid=".$_SESSION['user_id']." OR session_id='".session_id()."'))) ";
		$vouch = $this->get('vouch','int');
		if($vouch){
			$condition .= " AND vouch=1 ";
		}
		$total = $this->model('reply')->get_total($condition);
		if(!$total){
			$this->json(P_Lang('暫無評論資訊'));
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->get('psize','int');
		if(!$psize){
			$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		}
		$start = ($pageid-1) * $psize;
		$rslist = $this->model('reply')->get_list($condition,$start,$psize,"","id ASC");
		$idlist = $userlist = array();
		foreach($rslist AS $key=>$value){
			if($value["uid"]){
				$userlist[] = $value["uid"];
			}
			$idlist[] = $value["id"];
		}
		//讀取回復的回覆
		$idstring = implode(",",$idlist);
		$condition  = " parent_id IN(".$idstring.") ";
		$condition .= " AND (status=1 OR (status=0 AND (uid=".$_SESSION['user_id']." OR session_id='".session_id()."'))) ";
		$sublist = $this->model('reply')->get_list($condition,0,0);
		if($sublist){
			$mylist = array();
			foreach($sublist AS $key=>$value){
				if($value["uid"]){
					$userlist[] = $value["uid"];
				}
				$mylist[$value["parent_id"]][] = $value;
			}
		}
		
		//獲取會員資訊
		if($userlist && count($userlist)>0){
			$userlist = array_unique($userlist);
			$user_idstring = implode(",",$userlist);
			$condition = "u.status='1' AND u.id IN(".$user_idstring.")";
			$tmplist = $this->model('user')->get_list($condition,0,0);
			if($tmplist){
				$userlist = array();
				foreach($tmplist AS $key=>$value){
					$userlist[$value["id"]] = $value;
				}
				$tmplist = "";
			}
		}
		//整理回覆列表
		foreach($rslist as $key=>$value){
			if($mylist && $mylist[$value["id"]]){
				foreach($mylist[$value["id"]] AS $k=>$v){
					if($v["uid"] && $userlist){
						$v["uid"] = $userlist[$v["uid"]];
					}
					$mylist[$value["id"]][$k] = $v;
				}
				$value["sonlist"] = $mylist[$value["id"]];
			}
			if($value["uid"] && $userlist){
				$value["uid"] = $userlist[$value["uid"]];
			}
			$rslist[$key] = $value;
		}
		$pageurl = $this->url($id);
		$this->assign("rslist",$rslist);
		$this->assign("pageurl",$pageurl);
		$this->assign("pageid",$start);
		$this->assign("psize",$psize);
		$this->assign("total",$total);
		$html = $this->fetch("api_comment");
		$this->json($html,true,true,false);
	}

	/**
	 * 儲存評論資訊
	 * @引數 vtype 評論型別，僅支援：title 主題，project 專案，cate 分類，order 訂單，留空或是沒有獲取成功則讀取主題
	 * @引數 tid 主題ID，當type為title時此項必填，當為order時， tid指為訂單中的具體某個產品
	 * @引數 _chkcode 驗證碼，僅限評論為主題時有效，其他的評論必須是會員
	 * @引數 parent_id 父級評論ID
	 * @引數 star 評論等級，留空預設為3
	 * @引數 comment 評論內容，會員評論時支援HTML，遊客僅支援文字
	 * @引數 pictures 評論的時候上傳的一些圖片，或附件
	 * @引數 order_id 訂單ID
	**/
	public function save_f()
	{
		$type = $this->get('vtype');
		if(!$type){
			$type = 'title';
		}
		if(!$type || !in_array($type,array('title','project','cate','order'))){
			$this->error(P_Lang('評論型別不對，請檢查'));
		}
		$data = array('vtype'=>$type);
		$uid = $this->session->val('user_id');
		if($uid){
			$data['uid'] = $uid;
		}
		$user_groupid = $this->model('usergroup')->group_id($uid);
		if(!$user_groupid){
			$this->error(P_Lang('無法獲取使用者組資訊，請檢查'));
		}
		$parent_id = $this->get("parent_id","int");
		if($parent_id){
			$data['parent_id'] = $parent_id;
		}
		$data["ip"] = $this->lib('common')->ip();
		$data["addtime"] = $this->time;
		$data["star"] = $this->get('star','int');
		if(!$data['star']){
			$data['star'] = 3;
		}
		$data["session_id"] = $this->session->sessid();
		$_clearVcode = false;
		if($type == 'title'){
			$tid = $this->get('tid','int');
			if(!$tid && !$parent_id){
				$this->error(P_Lang('未指定要評論主題'));
			}
			if(!$tid && $parent_id){
				$comment = $this->model('reply')->get_one($parent_id);
				if(!$comment || !$comment['tid']){
					$this->error(P_Lang('未指定要評論主題'));
				}
				$tid = $comment['tid'];
			}
			$rs = $this->model('list')->call_one($tid);
			if(!$rs){
				$this->error(P_Lang('要評論的主題不存在'));
			}
			$project_rs = $this->model('project')->get_one($rs['project_id'],false);
			if(!$project_rs['comment_status']){
				$this->error(P_Lang('未啟用評論功能'));
			}
			$data['tid'] = $rs['id'];
			$data['title'] = $rs['title'];
			if($this->model('site')->vcode($rs['project_id'],'comment')){
				$code = $this->get('_chkcode');
				if(!$code){
					$this->error(P_Lang('驗證碼不能為空'));
				}
				$code = md5(strtolower($code));
				if($code != $this->session->val('vcode')){
					$this->error(P_Lang('驗證碼填寫不正確'));
				}
				$_clearVcode = true;
			}
			$data["status"] = $this->model('popedom')->val($rs['project_id'],$user_groupid,'reply1');
			$sessid = $this->session->sessid();
			$chk = $this->model('reply')->check_time($tid,$uid,$data["session_id"]);
			if(!$chk){
				$this->error(P_Lang('30秒內同一主題只能回覆一次'));
			}
		}elseif($type == 'order'){
			if(!$uid){
				$this->error(P_Lang('非會員不能對訂單進行評論'));
			}
			$order_id = $this->get('order_id','int');
			if(!$order_id){
				$this->error(P_Lang('未指定訂單ID'));
			}
			$order = $this->model('order')->get_one($order_id);
			if(!$order){
				$this->error(P_Lang('訂單資訊不存在'));
			}
			if($order['user_id'] != $uid){
				$this->error(P_Lang('您沒有許可權對此訂單產品進行評論'));
			}
			$data['order_id'] = $order_id;
			$tid = $this->get('tid','int');
			if($tid){
				$plist = $this->model('order')->product_list($order_id);
				if(!$plist){
					$this->error(P_Lang('訂單中沒有指定的產品'));
				}
				$check = false;
				$rs = array();
				foreach($plist as $key=>$value){
					if($value['tid'] == $tid){
						$check = true;
						$rs = $value;
						break;
					}
				}
				if(!$check){
					$this->error(P_Lang('訂單中沒有此產品'));
				}
				$data['title'] = '#'.$order['sn'].'_'.$rs['title'];
			}else{
				$data['title'] = P_Lang('訂單編號').'#'.$order['sn'];
			}
			$data["status"] = 0;
		}elseif($type == 'project'){
			if(!$uid){
				$this->error(P_Lang('非會員不能對專案進行評論'));
			}
			$tid = $this->get('tid','int');
			if(!$tid && !$parent_id){
				$this->error(P_Lang('未指定哪個專案'));
			}
			if(!$tid && $parent_id){
				$comment = $this->model('reply')->get_one($parent_id);
				if(!$comment || !$comment['tid']){
					$this->error(P_Lang('未指定要評論的專案'));
				}
				$tid = $comment['tid'];
			}
			$project_rs = $this->model('project')->get_one($tid,false);
			if(!$project_rs){
				$this->error(P_Lang('專案不存在'));
			}
			$data['title'] = $project_rs['title'];
			$data["status"] = 0;
		}elseif($type == 'cate'){
			if(!$uid){
				$this->error(P_Lang('非會員不能對分類進行評論'));
			}
			$tid = $this->get('tid','int');
			if(!$tid && !$parent_id){
				$this->error(P_Lang('未指定分類'));
			}
			if(!$tid && $parent_id){
				$comment = $this->model('reply')->get_one($parent_id);
				if(!$comment || !$comment['tid']){
					$this->error(P_Lang('未指定要評論的專案'));
				}
				$tid = $comment['tid'];
			}
			$cate_rs = $this->model('cate')->get_one($tid,'id',false);
			if(!$cate_rs){
				$this->error(P_Lang('分類不存在'));
			}
			$data['title'] = $cate_rs['title'];
			$data["status"] = 0;
		}
		$content = $uid ? $this->get('comment','html') : $this->get('comment');
		if(!$content){
			$this->error(P_Lang('評論內容不能為空'));
		}
		$data['content'] = $content;
		$data['res'] = $this->get('pictures'); //繫結附件，如果使用者有上傳附件，僅支援jpg,gif,png,zip,rar
		$insert_id = $this->model("reply")->save($data);
		if(!$insert_id){
			$this->error(P_Lang('評論儲存失敗，請聯絡管理員'));
		}
		if($_clearVcode){
			$this->session->unassign('vcode');
		}
		if($tid && in_array($type,array('title','order'))){
			$update = array("replydate"=>$this->time);
			$this->model("list")->save($update,$tid);
		}
		//評論送積分
		if($tid && $uid && $data["status"]){
			$this->model('wealth')->add_integral($tid,$uid,'comment',P_Lang('評論：{title}',array('title'=>$rs['title'])));
		}
		//增加通知任務
		if($project_rs && $project_rs['etpl_comment_admin'] || $project_rs['etpl_comment_user']){
			$param = 'id='.$insert_id;
			$this->model('task')->add_once('comment',$param);
		}
		$this->success();
	}
}