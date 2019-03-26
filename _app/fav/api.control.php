<?php
/**
 * 收藏夾相關功能介面
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年06月04日
**/
namespace phpok\app\control\fav;

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class api_control extends \phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 加入收藏
	 * @引數 id 主題ID
	**/
	public function add_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您還未登入，不能執行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定要收藏的主題ID'));
		}
		$chk = $this->model('fav')->chk($id,$this->session->val('user_id'));
		if($chk){
			$this->error(P_Lang('主題已經收藏過，不能重複收藏'));
		}
		$rs = $this->call->phpok('_arc','title_id='.$id);
		if(!$rs){
			$this->error(P_Lang('內容不存在'));
		}
		$data = array('user_id'=>$this->session->val('user_id'));
		$type = ($this->config['fav'] && $this->config['fav']['thumb_id']) ? $this->config['fav']['thumb_id'] : 'thumb';
		if($rs[$type]){
			if(is_array($rs[$type])){
				$data['thumb'] = $rs[$type]['filename'];
			}else{
				$data['thumb'] = $rs[$type];
			}
		}
		$data['title'] = $rs['title'];
		$type = ($this->config['fav'] && $this->config['fav']['note_id']) ? $this->config['fav']['note_id'] : 'content';
		if($rs[$type]){
			$data['note'] = $this->lib('string')->cut($rs[$type],80,'…',false);
		}
		$data['addtime'] = $this->time;
		$data['lid'] = $id;
		$this->model('fav')->save($data);
		$this->success();
	}

	/**
	 * 刪除收藏的主題
	 * @引數 id 收藏表中 qinggan_fav 裡的主鍵ID，注意噢，不是主題ID
	 * @引數 lid 主題ID
	**/
	public function delete_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您還未登入，不能執行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$lid = $this->get('lid','int');
			if(!$lid){
				$this->error(P_Lang('未指定ID'));
			}
			$chk = $this->model('fav')->chk($lid,$this->session->val('user_id'));
			if(!$chk){
				$this->error(P_Lang('沒有找到要刪除的記錄'));
			}
			$id = $chk['id'];
		}
		$rs = $this->model('fav')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('資料不存在'));
		}
		if($rs['user_id'] != $this->session->val('user_id')){
			$this->error(P_Lang('您沒有許可權刪除'));
		}
		$this->model('fav')->delete($id);
		$this->success();
	}

	/**
	 * 檢測主題是否已存在
	 * @引數 $id 主題ID
	**/
	public function check_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您還未登入，不能執行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定主題ID'));
		}
		$rs = $this->model('fav')->chk($id,$_SESSION['user_id']);
		if($rs){
			$this->success($rs['id']);
		}
		$this->success(0);
	}

	/**
	 * 讀取收藏列表
	 * @引數 $pageid 頁碼ID
	 * @引數 $psize 每頁數量
	**/
	public function index_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您還未登入，不能執行此操作'));
		}
		$condition = "f.user_id='".$this->session->val('user_id')."'";
		$total = $this->model('fav')->get_count($condition);
		if(!$total){
			$this->error(P_Lang('您的收藏夾還是空的噢'));
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->get('psize','int');
		if(!$psize){
			$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		}
		$offset = ($pageid-1) * $psize;
		$rslist = $this->model('fav')->get_all($condition,$offset,$psize);
		$data = array('total'=>$total,'pageid'=>$pageid,'psize'=>$psize,'rslist'=>$rslist);
		$this->success($data);
	}

	/**
	 * 執行動作，未新增收藏時進行新增操作，已新增執行取消操作
	 * @引數 $id 主題ID
	**/
	public function act_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您還未登入，不能執行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定主題ID'));
		}
		$chk = $this->model('fav')->chk($id,$this->session->val('user_id'));
		if($chk){
			$this->model('fav')->delete($chk['id']);
			$this->success('delete');
		}
		$rs = $this->call->phpok('_arc','title_id='.$id);
		if(!$rs){
			$this->error(P_Lang('內容不存在'));
		}
		$data = array('user_id'=>$this->session->val('user_id'));
		$type = ($this->config['fav'] && $this->config['fav']['thumb_id']) ? $this->config['fav']['thumb_id'] : 'thumb';
		if($rs[$type]){
			if(is_array($rs[$type])){
				$data['thumb'] = $rs[$type]['filename'];
			}else{
				$data['thumb'] = $rs[$type];
			}
		}
		$data['title'] = $rs['title'];
		$type = ($this->config['fav'] && $this->config['fav']['note_id']) ? $this->config['fav']['note_id'] : 'content';
		if($rs[$type]){
			$data['note'] = $this->lib('string')->cut($rs[$type],80,'…',false);
		}
		$data['addtime'] = $this->time;
		$data['lid'] = $id;
		$this->model('fav')->save($data);
		$this->success('add');
	}
}
