<?php
/*****************************************************************************************
	檔案： {phpok}/model/tag.php
	備註： Tag標籤在後臺的呼叫
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2014年5月25日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tag_model extends tag_model_base
{
	private $popedom;
	function __construct()
	{
		parent::__construct();
	}

	public function get_one($id,$field='id',$site_id=0)
	{
		if($site_id){
			$this->site_id($site_id);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."tag WHERE ".$field."='".$id."' AND site_id='".$this->site_id."'";
		return $this->db->get_one($sql);
	}

	public function get_list($condition="",$offset=0,$psize=30,$site_id=0)
	{
		if($site_id)
		{
			$this->site_id($site_id);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."tag WHERE site_id='".$this->site_id."' ";
		if($condition)
		{
			$sql .= " AND ".$condition;
		}
		$sql.= " ORDER BY is_global DESC,id DESC LIMIT ".$offset.",".$psize;
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist)
		{
			return false;
		}
		$ids = array_keys($rslist);
		$sql = "SELECT count(title_id) as count,tag_id FROM ".$this->db->prefix."tag_stat WHERE tag_id IN(".implode(",",$ids).") GROUP BY tag_id";
		$count_list = $this->db->get_all($sql,'tag_id');
		if($count_list)
		{
			foreach($rslist as $key=>$value)
			{
				$rslist[$key]['count'] = $count_list[$key]['count'] ? $count_list[$key]['count'] : 0;
			}
		}
		return $rslist;
	}

	public function get_total($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."tag WHERE site_id='".$this->site_id."' ";
		if($condition)
		{
			$sql .= " AND ".$condition;
		}
		return $this->db->count($sql);
	}

	public function chk_title($title,$id=0)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."tag WHERE title='".$title."' AND site_id='".$this->site_id."'";
		if($id)
		{
			$sql .= " AND id!='".$id."'";
		}
		return $this->db->get_one($sql);
	}

	public function save($data,$id=0)
	{
		if($id)
		{
			return $this->db->update_array($data,'tag',array('id'=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"tag");
		}
	}

	public function delete($id)
	{
		//刪除記錄
		$this->stat_delete($id,'tag_id');
		$sql = "DELETE FROM ".$this->db->prefix."tag WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function stat_delete($id,$field='tag_id')
	{
		$sql = "DELETE FROM ".$this->db->prefix."tag_stat WHERE ".$field."='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 批量更新標籤及標籤統計
	 * @引數 $data 標籤資料，可以是陣列也可以是字串
	 * @引數 $list_id 主題ID（專案ID，p字首）（分類ID，c字首）
	**/
	public function update_tag($data='',$list_id=0)
	{
		if(!$list_id){
			return false;
		}

		//沒有要更新的tag標籤時，刪除原有記錄
		if(!$data){
			return $this->stat_delete($list_id,'title_id');
		}
		$data = $this->string_to_array($data);
		
		$site_id = $this->_tag_site_id($list_id);
		$this->stat_delete($list_id,'title_id');
		foreach($data as $key=>$value){
			if(!$value || !trim($value)){
				continue;
			}
			$value = trim($value);
			$chk_rs = $this->chk_title($value);
			if($chk_rs){
				$id = $chk_rs['id'];
			}else{
				$array = array('site_id'=>$site_id,'title'=>$value,'url'=>'','target'=>'0');
				$id = $this->save($array);
			}
			if($id){
				$this->stat_save($id,$list_id);
			}
		}
		return true;
	}

	/**
	 * 通過標籤中的記錄，獲取相應的站點ID
	 * @引數 $id 標籤ID
	**/
	private function _tag_site_id($id)
	{
		if(substr($id,0,1) == 'p'){
			$sql = "SELECT site_id FROM ".$this->db->prefix."project WHERE id='".substr($id,1)."'";
		}
		if(substr($id,0,1) == 'c'){
			$sql = "SELECT site_id FROM ".$this->db->prefix."cate WHERE id='".substr($id,1)."'";
		}
		if(is_numeric($id)){
			$sql = "SELECT site_id FROM ".$this->db->prefix."list WHERE id='".$id."'";
		}
		$rs = $this->db->get_one($sql);
		if(!$rs || !$rs['site_id']){
			return $this->site_id;
		}
		return $rs['site_id'];
	}

	/**
	 * 字串轉陣列
	 * @引數 $string 要轉化的字串
	 * @返回 陣列
	**/
	private function string_to_array($string)
	{
		if(!$string || !trim($string)){
			return false;
		}
		if(is_array($string)){
			return $string;
		}
		$config = $this->config();
		$separator = $config['separator'] ? $config['separator'] : ',';
		return explode($separator,$string);
	}
}