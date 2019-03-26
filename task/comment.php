<?php
/**
 * 評論時通知管理員或會員的郵件或簡訊計劃任務
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年03月10日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

if(!$param['id']){
	return false;
}
$id = $param['id'];
$rs = $this->model('reply')->get_one($id);
if(!$rs){
	return false;
}
$this->assign('rs',$rs);

//會員資訊
if($rs['uid']){
	$user_rs = $this->model('user')->get_one($rs['uid']);
	$this->assign('user_rs',$user_rs);
}

//主題資訊
if($rs['tid']){
	$title_rs = $this->model('content')->get_one($rs['tid'],false);
	$this->assign('title_rs',$title_rs);
}

//訂單資訊
if($rs['order_id']){
	$order = $this->model('order')->get_one($rs['order_id']);
	$this->assign('order',$order);
}


if($title_rs){
	$project = $this->model('project')->get_one($title_rs['project_id'],false);
	if(!$project){
		echo '99999';
		return false;
	}
	if($project['etpl_comment_admin']){
		$this->gateway('type','email');
		$this->gateway('param','default');
		$tpl = $this->model('email')->tpl($project['etpl_comment_admin']);
		$condition = "email!='' AND if_system=1 AND status=1";
		$admin = $this->model('admin')->get_list($condition,0,1);
		if($tpl && $admin){
			$admin = current($admin);
			$this->assign('admin',$admin);
			$email = $admin['email'];
			$fullname = $admin['fullname'] ? $admin['fullname'] : $admin['account'];
			$this->assign('fullname',$fullname);
			$title = $this->fetch($tpl['title'],'msg');
			$content = $this->fetch($tpl['content'],'msg');
			if($title && $email && $content){
				$array = array('email'=>$email,'fullname'=>$fullname,'title'=>$title,'content'=>$content);
				$this->gateway('exec',$array);
			}
		}
	}
	if($project['etpl_comment_user'] && $user_rs && $user_rs['email']){
		$this->gateway('type','email');
		$this->gateway('param','default');
		$tpl = $this->model('email')->tpl($project['etpl_comment_user']);
		if($tpl){
			$fullname = $usre_rs['fullname'] ? $usre_rs['fullname'] : $usre_rs['user'];
			$title = $this->fetch($tpl['title'],'msg');
			$content = $this->fetch($tpl['content'],'msg');
			if($title && $email && $content){
				$array = array('email'=>$email,'fullname'=>$fullname,'title'=>$title,'content'=>$content);
				$this->gateway('exec',$array);
			}
		}
	}
}
