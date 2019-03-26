<?php
/**
 * 收藏夾
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年06月04日
**/
namespace phpok\app\model\fav;

class model extends \phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 獲取數量
	 * @引數 $condition 查詢條件
	**/
	public function get_count($condition='')
	{
		$sql = "SELECT count(f.id) FROM ".$this->db->prefix."fav f ";
		$sql.= "LEFT JOIN ".$this->db->prefix."user u ON(f.user_id=u.id) ";
		if($condition){
			$sql.= "WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

	/**
	 * 獲取列表
	 * @引數 $condition 查詢條件
	 * @引數 $offset 開始標識
	 * @引數 $psize 每次查詢數
	**/
	public function get_all($condition='',$offset=0,$psize=30)
	{
		$sql = "SELECT f.*,u.user FROM ".$this->db->prefix."fav f ";
		$sql.= "LEFT JOIN ".$this->db->prefix."user u ON(f.user_id=u.id) ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		$sql .= " ORDER BY f.addtime DESC,f.id DESC ";
		if($psize && $psize>0){
			$sql .= " LIMIT ".intval($offset).",".$psize;
		}
		return $this->db->get_all($sql);
	}

	/**
	 * 刪除收藏夾標記
	 * @引數 $id 收藏夾ID
	**/
	public function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."fav WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得主題被收藏數
	 * @引數 $id 主題ID
	**/
	public function title_fav_count($id)
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."fav WHERE lid='".$id."'";
		return $this->db->count($sql);
	}

	/**
	 * 檢查主題是否已被會員收藏
	 * @引數 $id 主題ID
	 * @引數 $uid 會員ID
	 * @引數 $field 欄位，預設使用 lid
	**/
	public function chk($id,$uid=0,$field='lid')
	{
		$sql = "SELECT id FROM ".$this->db->prefix."fav WHERE user_id='".$uid."' AND ".$field."='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 儲存收藏
	 * @引數 $data 一維陣列
	 * @引數 $id 有ID時表示更新，無ID時表示新增
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,'fav',array('id'=>$id));
		}
		return $this->db->insert_array($data,'fav');
	}

	/**
	 * 刪除收藏操作
	 * @引數 $id 收藏夾主表 qinggan_nav 的主鍵ID
	**/
	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."fav WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得一條收藏資訊
	 * @引數 $id 收藏夾主表 qinggan_nav 的主鍵ID
	**/
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."fav WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}
}