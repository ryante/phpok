<?php
/**
 * 欄位管理器
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @許可 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2018年05月18日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class fields_model extends fields_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 讀取 xml/fields/ 下的一條 XML 欄位配置資訊
	 * @引數 $id 標識
	**/
	public function default_one($id)
	{
		$filename = $this->dir_data.'xml/fields/'.$id.'.xml';
		if(!file_exists($filename)){
			return false;
		}
		return $this->lib('xml')->read($filename);
	}

	/**
	 * 讀取 xml/fields/ 下的全部 XML 檔案資訊
	**/
	public function default_all()
	{
		$flist = $this->lib('file')->ls($this->dir_data.'xml/fields/');
		if(!$flist){
			return false;
		}
		$rslist = array();
		foreach($flist as $key=>$value){
			$rs = $this->lib('xml')->read($value);
			$rslist[$rs['identifier']] = $rs;
		}
		ksort($rslist);
		return $rslist;
	}

	/**
	 * 刪除常用欄位
	 * @引數 $id 欄位標識
	**/
	public function default_delete($id)
	{
		$filename = $this->dir_data.'xml/fields/'.$id.'.xml';
		if(file_exists($filename)){
			$this->lib('file')->rm($filename);
		}
		return true;
	}

	/**
	 * 儲存常用欄位
	 * @引數 $data 要儲存的資料資訊
	**/
	public function default_save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($data['ext'] && is_string($data['ext'])){
			$data['ext'] = unserialize($data['ext']);
		}
		$filename = $this->dir_data.'xml/fields/'.$data['identifier'].'.xml';
		$this->lib('xml')->save($data,$filename);
		return true;
	}
}
