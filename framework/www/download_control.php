<?php
/**
 * 附件下載管理
 * @package phpok\www
 * @author qinggan <admin@phpok.com>
 * @copyright 2015-2016 深圳市錕鋙科技有限公司
 * @homepage http://www.phpok.com
 * @version 4.x
 * @license http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @update 2016年07月18日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class download_control extends phpok_control
{
	/**
	 * 建構函式
	**/
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 下載觸發頁
	 * @引數 file，要下載的檔案，該引數要求相對檔案路徑
	 * @引數 id，要下載的檔案ID，id和file各選一個，只要有一個有值即可
	 * @引數 back，當附件不存在或報錯時，返回頁地址
	**/
	public function index_f()
	{
		$file = $this->get('file');
		$id = $this->get('id','int');
		$back = $this->get('back');
		if(!$back){
			$back = $_SERVER['HTTP_REFERER'];
		}
		if(!$back){
			$back = $this->config['url'];
		}
		if(!$id && !$file){
			$this->error(P_Lang('未指定附件ID或附件檔案'),$back);
		}
		if($file){
			$rs = $this->model('res')->get_one_filename($file,false);
		}else{
			$rs = $this->model('res')->get_one($id);
		}
		if(!$rs){
			$this->error(P_Lang('附件不存在'),$back);
		}
		if(!$rs['filename']){
			$this->error(P_Lang('附件不存在'),$back);
		}
		//更新下載次數
		$download = $rs['download'] + 1;
		$this->model('res')->save(array('download'=>$download),$rs['id']);
		$this->lib('file')->download($rs['filename'],$rs['title']);
		exit;
	}
}
?>