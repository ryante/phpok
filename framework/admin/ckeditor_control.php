<?php
/**
 * CKeditor 上傳元件
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @許可 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2018年10月12日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class ckeditor_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		//
	}

	public function upload_f()
	{
		//
	}

	/**
	 * 獲取伺服器上所有圖片
	**/
	public function images_f()
	{
		$pageurl = $this->url('ckeditor','images');
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'];
		$offset = ($pageid - 1) * $psize;
		$condition = "res.ext IN ('gif','jpg','png','jpeg') ";
		$gd_rs = $this->model('gd')->get_editor_default();
		$keywords = $this->get('keywords');
		if($keywords){
			$condition .= " AND (res.filename LIKE '%".$keywords."%' OR res.title LIKE '%".$keywords."%') ";
		}
		$total = $this->model('res')->edit_pic_total($condition,$gd_rs);
		if($total){
			$rslist = $this->model('res')->edit_pic_list($condition,$offset,$psize,$gd_rs);
			if($rslist){
				$piclist = array();
				foreach($rslist as $key=>$value){
					$tmp = array('url'=>$value['filename'],'ico'=>$value['ico'],'mtime'=>$value['addtime'],'title'=>$value['title'],'id'=>$value['id']);
					if($value['attr']){
						$attr = is_string($value['attr']) ? unserialize($value['attr']) : $value['attr'];
						$tmp['width'] = $attr['width'];
						$tmp['height'] = $attr['height'];
					}
					$piclist[] = $tmp;
				}
				$this->assign('rslist',$piclist);
			}
			$string = 'home='.P_Lang('首頁').'&prev='.P_Lang('上一頁').'&next='.P_Lang('下一頁').'&last='.P_Lang('尾頁').'&half=3';
			$string.= '&add='.P_Lang('數量：').'(total)/(psize)'.P_Lang('，').P_Lang('頁碼：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
			$this->assign("pageurl",$pageurl);
			$this->lib('form')->cssjs(array('form_type'=>'upload'));
			$this->addjs('js/webuploader/admin.upload.js');
		}
		$this->view($this->dir_phpok.'open/ckeditor_images.html','abs-file');
	}
}
