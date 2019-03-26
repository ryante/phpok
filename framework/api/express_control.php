<?php
/**
 * 物流通用資料對接
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年01月28日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class express_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$id = $this->get('id','int');
		$rs = $this->model('order')->express_one($id);
		if(!$rs){
			$this->json(P_Lang('資料不存在'));
		}
		if(!$rs['order_id'] || $rs['is_end'] || !$rs['express_id']){
			$this->json(true);
		}
		//讀express資訊
		$express = $this->model('express')->get_one($rs['express_id']);
		if(!$express){
			$this->json(true);
		}
		$rate = $express['rate'] ? $express['rate'] : 6;
		if($rs['last_query_time']){
			$time = strtotime(date("Y-m-d H:i",$rs['last_query_time']));
			$time += $rate * 3600;
			//如果未超出系統限制，不查詢直接返回查詢結果
			if($time >= $this->time){
				$this->json(true);
			}
		}
		$file = $this->dir_root.'gateway/express/'.$express['code'].'/index.php';
		if(!file_exists($file)){
			$this->json(true);
		}
		$info = include $file;
		if(!$info || !is_array($info)){
			$this->json(true);
		}
		if(!$info['status']){
			if(!$info['content']){
				$info['content'] = P_Lang('快遞資訊獲取失敗');
			}
			$this->json($info['content']);
		}
		//更新操作時間
		$this->model('order')->update_last_query_time($id);
		//刪除舊的獲取查詢結果的資料
		$this->model('order')->log_delete($rs['order_id'],$rs['id'],$express['title']);
		//儲存新的
		if($info['content']){
			foreach($info['content'] as $key=>$value){
				$data = array('order_id'=>$rs['order_id'],'order_express_id'=>$rs['id']);
				$data['addtime'] = strtotime($value['time']);
				$data['who'] = $express['title'];
				$data['note'] = $value['content'];
				$this->model('order')->log_save($data);
			}
		}
		if($info['is_end']){
			$this->model('order')->update_end($id);
		}
		$this->json('refresh',true);
	}

	/**
	 * 新版遠端獲取物流快遞資訊
	**/
	public function remote_f()
	{
		$id = $this->get('id','int');
		$rs = $this->model('order')->express_one($id);
		if(!$rs){
			$this->error(P_Lang('資料不存在'));
		}
		if(!$rs['order_id'] || $rs['is_end'] || !$rs['express_id']){
			$this->success();
		}
		$express = $this->model('express')->get_one($rs['express_id']);
		if(!$express){
			$this->success();
		}
		$rate = $express['rate'] ? $express['rate'] : 6;
		if($rs['last_query_time']){
			$time = strtotime(date("Y-m-d H:i",$rs['last_query_time']));
			$time += $rate * 3600;
			if($time >= $this->time){
				$this->success();
			}
		}
		$file = $this->dir_root.'gateway/express/'.$express['code'].'/index.php';
		if(!file_exists($file)){
			$this->success();
		}
		$info = include $file;
		if(!$info || !is_array($info)){
			$this->success();
		}
		if(!$info['status']){
			if(!$info['content']){
				$info['content'] = P_Lang('快遞資訊獲取失敗');
			}
			$this->error($info['content']);
		}
		$this->model('order')->update_last_query_time($id);
		$this->model('order')->log_delete($rs['order_id'],$rs['id'],$express['title']);
		if($info['content']){
			foreach($info['content'] as $key=>$value){
				$data = array('order_id'=>$rs['order_id'],'order_express_id'=>$rs['id']);
				$data['addtime'] = strtotime($value['time']);
				$data['who'] = $express['title'];
				$data['note'] = $value['content'];
				$this->model('order')->log_save($data);
			}
		}
		if($info['is_end']){
			$this->model('order')->update_end($id);
		}
		$this->success('refresh');
	}
}