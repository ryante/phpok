<?php
/**
 * 評論資訊維護
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2017年04月28日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class reply_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 取得全部回覆
	 * @引數 $condition 查詢條件
	 * @引數 $offset 起始值
	 * @引數 $psize 每頁查詢數
	**/
	public function get_all($condition="",$offset=0,$psize=30)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."reply ";
		if($condition){
			$sql.= " WHERE ".$condition;
		}
		$sql .= " ORDER BY addtime DESC,id DESC ";
		if($psize && $psize>0){
			$offset = intval($offset);
			$sql.= " LIMIT ".$offset.",".$psize;
		}
		return $this->db->get_all($sql,"id");
	}

	/**
	 * 統計回覆中的已稽核主題資訊，未稽核資訊
	 * @引數 $id 主題ID，多個ID用英文逗號隔開
	**/
	public function total_status($id)
	{
		$list = array();
		$sql = "SELECT tid,count(id) total FROM ".$this->db->prefix."reply WHERE status=1 AND tid IN(".$id.") GROUP BY tid";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist AS $key=>$value){
				$list[$value["tid"]]["checked"] = $value["total"];
				$list[$value["tid"]]["uncheck"] = 0;
			}
		}
		$sql = "SELECT tid,count(id) total FROM ".$this->db->prefix."reply WHERE status=0 AND tid IN(".$id.") GROUP BY tid";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist AS $key=>$value){
				if(!$list[$value["tid"]]){
					$list[$value["tid"]]["checked"] = 0;
				}
				$list[$value["tid"]]["uncheck"] = $value["total"];
			}
		}
		return $list;
	}

	/**
	 * 獲取回覆列表
	 * @引數 $condition 查詢條件
	 * @引數 $offset 開始位置
	 * @引數 $psize 每頁查詢數
	 * @引數 $pri 主鍵
	 * @引數 $orderby 排序
	**/
	public function get_list($condition="",$offset=0,$psize=30,$pri="",$orderby="")
	{
		if(!$orderby){
			$orderby = 'addtime ASC,id DESC';
		}
		$sql = "SELECT * FROM ".$this->db->prefix."reply WHERE ".$condition." ORDER BY ".$orderby;
		if($psize && intval($psize)){
			$offset = intval($offset);
			$sql .= " LIMIT ".$offset.",".$psize;
		}
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		$rslist = $this->_reply($rslist);
		$rslist = $this->_res($rslist,true);
		return $rslist;
	}

	protected function _reply($rslist)
	{
		$ids = array_keys($rslist);
		$ids = implode(",",$ids);
		$sql = "SELECT id,content,addtime,admin_id,parent_id FROM ".$this->db->prefix."reply WHERE parent_id IN(".$ids.") AND admin_id>0 ORDER BY addtime ASC,id ASC";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return $rslist;
		}
		foreach($tmplist as $key=>$value){
			if(!$rslist[$value['parent_id']]['adm_reply']){
				$rslist[$value['parent_id']]['adm_reply'] = array();
			}
			$rslist[$value['parent_id']]['adm_reply'][] = $value;
		}
		return $rslist;
	}

	/**
	 * 評論中的附件
	 * @引數 $rslist 回覆資料，陣列格式
	**/
	protected function _res($rslist,$ext=false)
	{
		$ids = array();
		foreach($rslist as $key=>$value){
			if($value['res']){
				$ids[] = $value['res'];
			}
		}
		if(!$ids){
			return $rslist;
		}
		$ids = implode(",",$ids);
		$list = explode(",",$ids);
		$list = array_unique($list);
		$ids = implode(",",$list);
		$reslist = $this->model('res')->get_list_from_id($ids,$ext);
		if(!$reslist){
			return $rslist;
		}
		foreach($rslist as $key=>$value){
			if(!$value['res']){
				continue;
			}
			$tmp = explode(",",$value['res']);
			$tmplist = array();
			foreach($tmp as $k=>$v){
				if($v && $reslist[$v]){
					$tmplist[$v] = $reslist[$v];
				}
			}
			$value['res'] = $tmplist;
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	/**
	 * 查詢數量
	 * @引數 $condition 條件
	**/
	public function get_total($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."reply";
		if($condition){
			$sql .= " WHERE ".$condition;
		} 
		return $this->db->count($sql);
	}

	/**
	 * 儲存回覆資料
	 * @引數 $data 陣列，要儲存的資料
	 * @引數 $id 回覆ID，不為空時表示更新
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data) || count($data) < 1){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"reply",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"reply");
		}
	}

	/**
	 * 刪除回覆
	 * @引數 $id 回覆ID
	**/
	public function delete($id)
	{
		if(!$id){
			return false;
		}
		$rs = $this->get_one($id);
		if($rs){
			$sql = "DELETE FROM ".$this->db->prefix."reply WHERE id='".$id."' OR parent_id='".$id."'";
			$this->db->query($sql);
			$sql = "SELECT id,addtime FROM ".$this->db->prefix."reply WHERE tid='".$rs['tid']."' ORDER BY id DESC LIMIT 1";
			$tmp = $this->db->get_one($sql);
			$sql = "UPDATE ".$this->db->prefix."list SET replydate=0 WHERE id='".$rs['tid']."'";
			if($tmp){
				$sql = "UPDATE ".$this->db->prefix."list SET replydate='".$tmp['addtime']."' WHERE id='".$rs['tid']."'";
			}
			$this->db->query($sql);
		}
		return true;
	}

	/**
	 * 取得一條回覆資訊
	 * @引數 $id 回覆ID
	**/
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."reply WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 回覆統計
	 * @引數 $ids 主題ID，多個主題用英文逗號隔開，也支援多個主題的陣列
	**/
	public function comment_stat($ids)
	{
		if(!$ids){
			return false;
		}
		if(is_array($ids)){
			$ids = implode(",",$ids);
		}
		$sql = "SELECT count(tid) as total,tid FROM ".$this->db->prefix."reply WHERE tid IN(".$ids.") GROUP BY tid";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$rslist[$value['tid']] = $value['total'];
		}
		return $rslist;
	}

	/**
	 * 取得主題屬性資訊，如繫結的專案ID，如分頁頁碼等
	 * @引數 int $id 主題ID或主題標識
	 */
	public function get_title_info($id)
	{
		$sql = "SELECT l.id,l.project_id,p.psize,p.comment_status FROM ".$this->db->prefix."list l ";
		$sql.= "LEFT JOIN ".$this->db->prefix."project p ON(l.project_id=p.id) WHERE ";
		if(is_numeric($id)){
			$sql.= "l.id='".$id."'";
		}else{
			$sql.= "l.identifier='".$id."' AND l.site_id='".$this->site_id."'";
		}
		$sql.= " AND p.status=1";
		return $this->db->get_one($sql);
	}

	public function adm_reply($id)
	{
		$id = intval($id);
		$sql = "SELECT id,addtime,content,admin_id FROM ".$this->db->prefix."reply WHERE parent_id=".$id." AND admin_id!=0 ORDER BY addtime ASC,id ASC";
		return $this->db->get_all($sql);
	}
}