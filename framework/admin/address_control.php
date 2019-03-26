<?php
/**
 * 會員地址庫
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @許可 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年06月03日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class address_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('address');
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 彈窗檢視會員的地址庫資訊
	 * @引數 type 查詢型別
	 * @引數 keywords 關鍵字
	**/
	public function open_f()
	{
		if(!$this->popedom['list']){
			$this->error(P_Lang('您沒有此許可權操作'));
		}
		$tpl = $this->get('tpl');
		if(!$tpl){
			$pageurl = $this->url('address','open');
			$tpl = 'address_open';
		}else{
			$pageurl = $this->url('address','open','tpl='.$tpl);
		}
		
		$keywords = $this->get('keywords');
		if($keywords && !is_array($keywords)){
			$type = $this->get('type');
			$keywords = array($type=>$keywords);
		}
		$status = $this->_index($pageurl,$keywords);
		if(!$status){
			$this->tip(P_Lang('該會員還沒有設定地址資訊'));
		}
		$this->view($tpl);
	}

	/**
	 * 會員地址庫
	 * @引數 type 查詢型別
	 * @引數 keywords 關鍵字
	**/
	public function index_f()
	{
		if(!$this->popedom['list']){
			$this->error(P_Lang('您沒有此許可權操作'));
		}
		$pageurl = $this->url('address');
		$type = $this->get('type');
		$keywords = $this->get('keywords');
		if(!$keywords && !is_array($keywords)){
			$type = $this->get('type');
			if($type){
				$keywords = array($type=>$keywords);
			}
		}
		$this->_index($pageurl,$keywords);
		$this->view("address_list");
	}

	public function one_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('address')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('地址資訊不存在'));
		}
		$this->success($rs);
	}

	private function _index($pageurl='',$keywords='')
	{
		$condition = "1=1";
		if($keywords && is_array($keywords)){
			$this->assign('keywords',$keywords);
			if($keywords['user']){
				$tmplist = array("u.user='".$keywords['user']."'");
				$tmplist[] = "u.email='".$keywords['user']."'";
				$tmplist[] = "u.mobile='".$keywords['user']."'";
				$condition .= " AND (".implode(" OR ",$tmplist).")";
				$pageurl .= "&keywords[user]=".rawurlencode($keywords['user']);
			}
			if($keywords['user_id']){
				$condition .= " AND a.user_id='".$keywords['user_id']."'";
				$pageurl .= "&keywords[user_id]=".rawurlencode($keywords['user_id']);
			}
			if($keywords['address']){
				$tmplist = array("a.country LIKE '%".$keywords['address']."%'");
				$tmplist[] = "a.province LIKE '%".$keywords['address']."%'";
				$tmplist[] = "a.city LIKE '%".$keywords['address']."%'";
				$tmplist[] = "a.county LIKE '%".$keywords['address']."%'";
				$tmplist[] = "a.address LIKE '%".$keywords['address']."%'";
				$condition .= " AND (".implode(" OR ",$tmplist).")";
				$pageurl .= "&keywords[address]=".rawurlencode($keywords['address']);
			}
			if($keywords['contact']){
				$tmplist = array("a.email LIKE '%".$keywords['contact']."%'");
				$tmplist[] = "a.tel LIKE '%".$keywords['contact']."%'";
				$tmplist[] = "a.mobile LIKE '%".$keywords['contact']."%'";
				$condition .= " AND (".implode(" OR ",$tmplist).")";
				$pageurl .= "&keywords[contact]=".rawurlencode($keywords['contact']);
			}
			if($keywords['fullname']){
				$condition .= " AND a.fullname='".$keywords['user_id']."'";
				$pageurl .= "&keywords[user_id]=".rawurlencode($keywords['user_id']);
			}
		}
		$this->assign('pageurl',$pageurl);
		$total = $this->model('address')->count($condition);
		if(!$total){
			return false;
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$offset = ($pageid-1) * $psize;
		$rslist = $this->model('address')->get_list($condition,$offset,$psize);
		$this->assign('rslist',$rslist);
		$this->assign('total',$total);
		$this->assign('pageid',$pageid);
		$this->assign('psize',$psize);
		$this->assign('offset',$offset);
		$string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上一頁').'&next='.P_Lang('下一頁').'&last='.P_Lang('尾頁').'&half=5';
		$string.= '&add='.P_Lang('數量：').'(total)/(psize)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign("pagelist",$pagelist);
		return true;
	}

	public function set_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您沒有修改地址庫許可權'));
			}
			$rs = $this->model('user')->address_one($id);
			$this->assign('rs',$rs);
			$this->assign('id',$id);
		}else{
			if(!$this->popedom['add']){
				$this->error(P_Lang('您沒有新增地址庫許可權'));
			}
		}
		$this->view("address_set");
	}

	public function save_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您沒有修改地址庫許可權'));
			}
		}else{
			if(!$this->popedom['add']){
				$this->error(P_Lang('您沒有新增地址庫許可權'));
			}
		}
		$array = array();
		$array['user_id'] = $this->get('user_id','int');
		if(!$array['user_id']){
			$this->error(P_Lang('未繫結會員'));
		}
		$array['fullname'] = $this->get('fullname');
		if(!$array['fullname']){
			$this->error(P_Lang('收件人姓名不能為空'));
		}
		$array['country'] = $this->get('country');
		$array['province'] = $this->get('province');
		$array['city'] = $this->get('city');
		$array['county'] = $this->get('county');
		$array['address'] = $this->get('address');
		if(!$array['country']){
			$this->error(P_Lang('國家不能為空'));
		}
		if(!$array['province']){
			$this->error(P_Lang('省份名稱不能為空'));
		}
		if(!$array['address']){
			$this->error(P_Lang('地址資訊不能為空'));
		}
		$array['zipcode'] = $this->get('zipcode');
		$array['mobile'] = $this->get('mobile');
		$array['tel'] = $this->get('tel');
		if(!$array['mobile'] && !$array['tel']){
			$this->error(P_Lang('手機號或電話，必須至少填寫一個'));
		}
		$array['email'] = $this->get('email');
		$this->model('user')->address_save($array,$id);
		$tip = $id ? P_Lang('地址資訊編輯成功') : P_Lang('地址資訊新增成功');
		$this->success($tip);
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您沒有刪除地址庫許可權'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定要刪除的ID'));
		}
		$this->model('address')->delete($id);
		$this->success();
	}
}
