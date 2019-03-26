<?php
/*****************************************************************************************
	檔案： {phpok}/model/gateway.php
	備註： 第三方閘道器接入管理工具
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2015年09月29日 23時58分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class gateway_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 獲取閘道器資訊
	 * @引數 $id 閘道器ID
	 * @引數 $type 閘道器型別，當為 true 或 false 時，表示 chkstatus
	 * @引數 $chkstatus 是否驗證必填資訊專案
	**/
	public function get_one($id,$type='',$chkstatus=false)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."gateway WHERE id='".$id."'";
		if($type && is_string($type)){
			$sql .= " AND type='".$type."'";
		}
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['ext']){
			$rs['ext'] = unserialize($rs['ext']);
		}
		if(isset($type) && is_bool($type)){
			$chkstatus = $type;
		}
		if($chkstatus){
			$param = $this->code_one($rs['type'],$rs['code']);
			if(!$param['code']){
				return false;
			}
			$chk_status = true;
			foreach($param['code'] as $key=>$value){
				if($value['required'] && $value['required'] != 'false' && $value['required'] != '0'){
					$tmpid = $rs['ext'][$key];
					if($tmpid == ''){
						$chk_status = false;
						break;
					}
				}
			}
			if(!$chk_status){
				return false;
			}
		}
		return $rs;
	}

	public function get_default($type)
	{
		if(!$type){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."gateway WHERE type='".$type."' AND site_id='".$this->site_id."' AND status=1";
		$sql.= " AND is_default=1 ORDER BY id DESC";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['ext']){
			$rs['ext'] = unserialize($rs['ext']);
		}
		$param = $this->code_one($rs['type'],$rs['code']);
		if(!$param['code']){
			return false;
		}
		$chk_status = true;
		foreach($param['code'] as $key=>$value){
			if($value['required'] && $value['required'] != 'false' && $value['required'] != '0'){
				$tmpid = $rs['ext'][$key];
				if($tmpid == ''){
					$chk_status = false;
					break;
				}
			}
		}
		if(!$chk_status){
			return false;
		}
		return $rs;
	}

	public function code_one($type,$id)
	{
		if(!$type || !$id){
			return false;
		}
		$rs = array('id'=>$id,'dir'=>$this->dir_root.'gateway/'.$type.'/'.$id);
		$xmlfile = $this->dir_root.'gateway/'.$type.'/'.$id.'/config.xml';
		if(file_exists($xmlfile)){
			$tmp = $this->lib('xml')->read($xmlfile);
		}else{
			$tmp = array('title'=>$id,'code'=>'');
		}
		$rs['code'] = $tmp['code'];
		$rs['title'] = $tmp['title'];
		$rs['note'] = $tmp['note'];
		if($tmp['manage']){
			$rs['manage'] = $tmp['manage'];
		}
		return $rs;
	}

	public function all($type)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."gateway WHERE type='".$type."' ORDER BY is_default DESC,taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}


	public function action($server,$data)
	{
		$rs = $server;
		$extinfo = $data;
		$file = $this->dir_root.'gateway/'.$rs['type'].'/'.$rs['code'].'/exec.php';
		if(!file_exists($file)){
			return false;
		}
		return include $file;
	}

	//儲存臨時資料
	public function save_temp($info,$gid,$uid=0)
	{
		$file = 'gateway_'.$gid.'_'.$uid.'_'.$this->site_id.'.php';
		return $this->lib('file')->vi($info,$this->dir_cache.$file);
	}

	//讀取臨時資料
	public function read_temp($gid,$uid=0)
	{
		$file = 'gateway_'.$gid.'_'.$uid.'_'.$this->site_id.'.php';
		if(!file_exists($this->dir_cache.$file)){
			return false;
		}
		$info = $this->lib('file')->cat($this->dir_cache.$file);
		if(!$info || !trim($info)){
			return false;
		}
		return trim($info);
	}

	public function delete_temp($gid,$uid=0)
	{
		$file = 'gateway_'.$gid.'_'.$uid.'_'.$this->site_id.'.php';
		$this->lib('file')->rm($this->dir_cache.$file);
		return true;
	}
}