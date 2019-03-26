<?php
/**
 * 通知類模板，包括簡訊通知及郵件通知
 * @package phpok\admin\control
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年02月24日
**/

class email_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("email");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 通知模板列表
	 * @引數 pageid 分頁ID
	 * @引數 
	 * @返回 
	 * @更新時間 
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您沒有許可權執行此操作'));
		}
		$condition = "site_id IN(".$this->session->val('admin_site_id').",0)";
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config["psize"] ? $this->config["psize"] : 20;
		$offset = ($pageid-1) * $psize;
		$total = $this->model('email')->get_count($condition);//讀取模組總數
		if($total){
			$rslist = $this->model('email')->get_list($condition,$offset,$psize);
			$this->assign("rslist",$rslist);
			$pageurl = $this->url("email");
			$string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上一頁').'&next='.P_Lang('下一頁').'&last='.P_Lang('尾頁').'&half=5';
			$string.= '&add='.P_Lang('數量：').'(total)/(psize)'.P_Lang('，').P_Lang('頁碼：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			if($pagelist){
				$this->assign("pagelist",$pagelist);
			}
		}		
		$this->view("email_list");
	}

	/**
	 * 新增可配置通知模板的內容
	**/
	public function set_f()
	{
		$id = $this->get("id","int");
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您沒有許可權執行此操作'),$this->url('email'));
			}
			$rs = $this->model('email')->get_one($id);
			$type = substr($rs['identifier'],0,4) == 'sms_' ? 'sms' : 'email';
			$this->assign("rs",$rs);
			$this->assign("id",$id);
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您沒有許可權執行此操作'),$this->url('email'));
			}
			$type = $this->get('type');
			if(!$type){
				$type = 'email';
			}
			$rs = array("content"=>'');
		}
		$this->assign('type',$type);
		if($type == 'sms'){
			$edit_content = form_edit('content',$rs['content'],'textarea','height=300&width=500');
		}else{
			$edit_content = form_edit('content',$rs['content'],'editor','height=300&btn_image=1&is_code=1');
		}
		$this->assign('edit_content',$edit_content);
		$this->view("email_set");
	}

	public function setok_f()
	{
		$array = array();
		$id = $this->get("id","int");
		if(!$id){
			$array["site_id"] = $this->session->val('admin_site_id');
			$tip = P_Lang('通知內容新增成功，請稍候…');
		}else{
			$tip = P_Lang('通知內容編輯成功，請稍候…');
		}
		$array["title"] = $this->get("title");
		$array["identifier"] = $this->get("identifier");
		if(substr($array['identifier'],0,4) == 'sms_'){
			$array['content'] = $this->get('content','text');
		}else{
			$array["content"] = $this->get("content","html",false);
		}
		if(!$array["title"] || !$array["identifier"]){
			$this->error(P_Lang('資訊填寫不完整'),$this->url("email","set","id=".$id));
		}
		$array['note'] = $this->get('note');
		$this->model('email')->save($array,$id);
		$this->success($tip,$this->url("email"));
	}

	/**
	 * 刪除通知模板
	 * @引數 id 模板內容ID
	 * @返回 JSON資料
	 * @更新時間 2017年02月25日
	**/
	public function del_f()
	{
		if(!$this->popedom['delete']){
			$this->json(P_Lang('您沒有許可權執行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$this->model('email')->del($id);
		$this->json(true);
	}

	/**
	 * 驗證標識是否符合要求
	 * @引數 id 通知模板ID
	 * @引數 identifier 標識
	 * @引數 type 型別, sms 表示簡訊,其他表示郵件
	 * @返回 JSON資料
	 * @更新時間 2017年02月25日
	**/
	public function check_f()
	{
		$id = $this->get("id","int");
		$identifier = $this->get("identifier");
		if(!$identifier){
			$this->json(P_Lang('未指定標識串'));
		}
		$type = $this->get('type');
		if($type == 'sms' && substr($identifier,0,4) != 'sms_'){
			$this->json(P_Lang('簡訊必須是以sms_開頭'));
		}
		$rs = $this->model('email')->get_identifier($identifier,$id);
		if($rs){
			$this->json(P_Lang('識別符號已被使用'));
		}
		$this->json(true);
	}
}