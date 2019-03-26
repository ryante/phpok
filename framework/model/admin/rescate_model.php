<?php
/*****************************************************************************************
	檔案： {phpok}/model/admin/rescate_model.php
	備註： 附件分類管理
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2015年04月25日 13時20分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class rescate_model extends rescate_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if(!$id){
			$id = $this->db->insert_array($data,'res_cate');
			if(!$id){
				return false;
			}
		}else{
			$action = $this->db->update_array($data,'res_cate',array("id"=>$id));
			if(!$action){
				return false;
			}
		}
		if($data['is_default']){
			$sql = "UPDATE ".$this->db->prefix."res_cate SET is_default=0 WHERE id !='".$id."'";
			$this->db->query($sql);
		}
		return $id;
	}

	public function delete($id)
	{
		$default_rs = $this->get_default();
		$newid = $default_rs ? $default_rs['id'] : 0;
		$sql = "UPDATE ".$this->db->prefix."res SET cate_id='".$newid."' WHERE cate_id='".$id."'";
		$this->db->query($sql);
		$sql = 	"DELETE FROM ".$this->db->prefix."res_cate WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}
}

?>