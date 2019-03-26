<?php
/**
 * 在PHPOK模板中常用的函式，此函式在action前才載入
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年01月15日
**/

/**
 * 安全限制，防止直接訪問
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

function phpok($id='',$ext="")
{
	if(!$id || !$GLOBALS['app']->call){
		return false;
	}
	$count = func_num_args();
	if($count<=2){
		return $GLOBALS['app']->call->phpok($id,$ext);
	}
	$param = array();
	for($i=1;$i<$count;++$i){
		$tmp = func_get_arg($i);
		if(strpos($tmp,'=') !== true){
			$tmp = str_replace(':','=',$tmp);
		}
		$param[] = $tmp;
	}
	$param = implode("&",$param);
	return $GLOBALS['app']->call->phpok($id,$param);
}

function token($data)
{
	if(!$data){
		return false;
	}
	if(is_string($data)){
		parse_str($data,$data);
	}
	return $GLOBALS['app']->lib('token')->encode($data);
}


/**
 * 模板中呼叫分頁
 * @param string $url 網址
 * @param int $total 總數
 * @param int $num 當前頁
 * @param int psize 每頁顯示數量
 * @param string ... 更多引數，格式是 變數id=變數值 如：home=首頁&prev=上一頁&next=下一頁&last=尾頁&half=5&opt=1&add={total}/{psize}
 * @date 2016年02月05日
 */
function phpok_page($url,$total,$num=0,$psize=20)
{
	if(!$url || !$total){
		return false;
	}
	$count = func_num_args();
	if($count<=4){
		return $GLOBALS['app']->lib('page')->page($url,$total,$num,$psize);
	}
	$param = array();
	for($i=4;$i<$count;++$i){
		$tmp = func_get_arg($i);
		if(strpos($tmp,'=') !== true){
			$tmp = str_replace(':','=',$tmp);
		}
		$param[] = $tmp;
	}
	$param = implode("&",$param);
	parse_str($param,$list);
	if(!$list){
		$list = array();
	}
	foreach($list as $key=>$value){
		if(substr($value,0,1) == ';' && substr($value,-1) == ';'){
			$value = "&".substr($value,1);
		}
		$list[$key] = $value;
	}
	if($list['home']){
		$GLOBALS['app']->lib('page')->home_str($list['home']);
	}
	if($list['prev']){
		$GLOBALS['app']->lib('page')->prev_str($list['prev']);
	}
	if($list['next']){
		$GLOBALS['app']->lib('page')->next_str($list['next']);
	}
	if($list['last']){
		$GLOBALS['app']->lib('page')->last_str($list['last']);
	}
	if($list['half'] != ''){
		$GLOBALS['app']->lib('page')->half($list['half']);
	}
	if($list['opt']){
		$GLOBALS['app']->lib('page')->opt_str($list['opt']);
	}
	if($list['add']){
		$GLOBALS['app']->lib('page')->add_up($list['add']);
	}
	if($list['always']){
		$GLOBALS['app']->lib('page')->always($list['always']);
	}
	if($list['rewrite']){
		$GLOBALS['app']->lib('page')->url_format($list['rewrite']);
	}
	if($list['cut']){
		$GLOBALS['app']->lib('page')->url_cut($list['cut']);
	}
	if($num<1){
		$num = 1;
	}
	return $GLOBALS['app']->lib('page')->page($url,$total,$num,$psize);
}

function phpok_plugin()
{
	$rslist = $GLOBALS['app']->model('plugin')->get_all(1);
	if(!$rslist){
		return false;
	}
	$id = $GLOBALS['app']->app_id;
	$ctrl = $GLOBALS['app']->ctrl;
	$func = $GLOBALS['app']->func;
	
	//裝載外掛
	foreach($rslist AS $key=>$value)
	{
		if(is_file($GLOBALS['app']->dir_root.'plugins/'.$key.'/'.$id.'.php')){
			if($value['param']){
				$value['param'] = unserialize($value['param']);
			}
			include($GLOBALS['app']->dir_root.'plugins/'.$key.'/'.$id.'.php');
			$name = $id.'_'.$key;
			$cls = new $name();
			$func_name = $ctrl.'_'.$func;
			$mlist = get_class_methods($cls);
			if($mlist && in_array($func_name,$mlist)){
				echo $cls->$func_name($value);
			}
		}
	}
}

// 根據圖片儲存的ID，獲得圖片資訊
function phpok_image_rs($img_id)
{
	if(!$img_id) return false;
	return $GLOBALS['app']->model('res')->get_one($img_id);
}

//顯示評論資訊
//回覆資料呼叫
function phpok_reply($id,$psize=10,$orderby="ASC",$vouch=false)
{
	$condition = "tid='".$id."' AND parent_id='0' ";
	$sessid = $GLOBALS['app']->session->sessid();
	$uid = $_SESSION['user_id'] ? $_SESSION['user_id'] : 0;
	$condition .= " AND (status=1 OR (status=0 AND (uid=".$uid." OR session_id='".$sessid."'))) ";
	if($vouch){
		$condition .= " AND vouch=1 ";
	}
	$total = $GLOBALS['app']->model('reply')->get_total($condition);
	if(!$total) return false;
	//判斷當前頁
	$pageid = $GLOBALS['app']->get($GLOBALS['app']->config['pageid'],'int');
	if(!$pageid) $pageid = 1;
	$order = strtoupper($orderby) == 'ASC' ? 'id ASC' : 'id DESC';
	$offset = ($pageid-1) * $psize;
	$rslist = $GLOBALS['app']->model('reply')->get_list($condition,$offset,$psize,"",$order);
	//讀子主題
	$idlist = $userlist = array();
	foreach($rslist as $key=>$value){
		if($value["uid"]) $userlist[] = $value["uid"];
		$idlist[] = $value["id"];
	}
	
	//獲取會員資訊
	if($userlist && count($userlist)>0){
		$userlist = array_unique($userlist);
		$user_idstring = implode(",",$userlist);
		$condition = "u.status='1' AND u.id IN(".$user_idstring.")";
		$tmplist = $GLOBALS['app']->model('user')->get_list($condition,0,0);
		if($tmplist){
			$userlist = array();
			foreach($tmplist as $key=>$value){
				$userlist[$value["id"]] = $value;
			}
			$tmplist = "";
		}
	}
	//整理回覆列表
	foreach($rslist as $key=>$value)
	{
		if($value["uid"] && $userlist){
			$value["uid"] = $userlist[$value["uid"]];
		}
		$rslist[$key] = $value;
	}
	//返回結果集
	$array = array('list'=>$rslist,'psize'=>$psize,'pageid'=>$pageid,'total'=>$total);
	return $array;
}

function phpok_ip()
{
	return $GLOBALS['app']->lib('common')->ip();
}

//網址，下劃線分割字元法
function phpok_url($rs)
{
	if(!$rs){
		return false;
	}
	if(is_string($rs)){
		parse_str($rs,$rs);
	}
	$ctrl = $func = $id = $appid = "";
	if($rs["ctrl"]){
		$ctrl = $rs["ctrl"];
	}
	if($rs["func"]){
		$func = $rs["func"];
	}
	if($rs["id"]){
		$id = $rs["id"];
	}
	if($rs['appid']){
		$appid = $rs['appid'];
	}
	if(!$ctrl && !$id){
		return false;
	}
	if(!$ctrl){
		$ctrl = $id;
		$id = "";
	}
	$tmp = array();
	foreach($rs as $key=>$value){
		if(in_array($key,array("ctrl","id","func",'appid'))){
			continue;
		}
		if($key == '_nocache' && $value && $value != 'false' && $value != '0'){
			$tmp[] = '_noCache=0.'.$GLOBALS['app']->time.rand(1000,9999);
		}else{
			$tmp[] = $key."=".rawurlencode($value);
		}
	}
	if($ctrl && $id && $id != $ctrl){
		$tmp[] = "id=".rawurlencode($rs["id"]);
	}
	$string = ($tmp && count($tmp)>0) ? implode("&",$tmp) : "";
	return $GLOBALS['app']->url($ctrl,$func,$string,$appid);
}

if(!function_exists('www_url')){
	function www_url($ctrl='index',$func='index',$ext='',$baseurl=true)
	{
		return $GLOBALS['app']->url($ctrl,$func,$ext,'www',$baseurl);
	}
}

if(!function_exists('www_plugin_url')){
	function www_plugin_url($id='index',$exec='index',$ext='',$baseurl=true)
	{
		$string = "id=".$id;
		if($exec && $exec != 'index'){
			$string .= "&exec=".$exec;
		}
		if($ext){
			$string .= "&".$ext;
		}
		return $GLOBALS['app']->url('plugin','exec',$string,'www',$baseurl);
	}
}

if(!function_exists('api_url')){
	function api_url($ctrl='index',$func='index',$ext='',$baseurl=true)
	{
		return $GLOBALS['app']->url($ctrl,$func,$ext,'api',$baseurl);
	}
}

if(!function_exists('api_plugin_url')){
	function api_plugin_url($id='index',$exec='index',$ext='',$baseurl=true)
	{
		$string = "id=".$id;
		if($exec && $exec != 'index'){
			$string .= "&exec=".$exec;
		}
		if($ext){
			$string .= "&".$ext;
		}
		return $GLOBALS['app']->url('plugin','exec',$string,'api',$baseurl);
	}
}

if(!function_exists('admin_url')){
	function admin_url($ctrl,$func="",$ext="",$baseurl=false)
	{
		return $GLOBALS['app']->url($ctrl,$func,$ext,'admin',$baseurl);
	}
}

if(!function_exists('admin_plugin_url')){
	function admin_plugin_url($id='index',$exec='index',$ext='',$baseurl=false)
	{
		$string = "id=".$id;
		if($exec && $exec != 'index'){
			$string .= "&exec=".$exec;
		}
		if($ext){
			$string .= "&".$ext;
		}
		return $GLOBALS['app']->url('plugin','exec',$string,'admin',$baseurl);
	}
}

//讀取會員擁有釋出的許可權資訊
function usercp_project()
{
	if(!$_SESSION['user_id'] || !$_SESSION['user_gid']){
		return false;
	}
	$group_rs = $GLOBALS['app']->model('usergroup')->get_one($_SESSION['user_gid']);
	$popedom = $group_rs['popedom'] ? unserialize($group_rs['popedom']) : array();
	$site_id = $GLOBALS['app']->site['id'];
	if(!$popedom || ($popedom && !$popedom[$site_id])){
		return false;
	}
	$popedom = explode(",",$popedom[$site_id]);
	$plist = false;
	foreach($popedom as $key=>$value){
		if(substr($value,0,5) == 'post:'){
			if(!$plist){
				$plist = array();
			}
			$plist[] = str_replace('post:','',trim($value));
		}
	}
	if(!$plist){
		return false;
	}
	$pids = implode(",",$plist);
	return $GLOBALS['app']->model('project')->plist($pids,true);
}

//讀取會員資訊，如果有ID，則讀取該ID陣列資訊
function usercp_info($field="")
{
	if(!$_SESSION['user_id'] || !$_SESSION['user_rs']['group_id']) return false;
	$group_rs = $GLOBALS['app']->model('usergroup')->get_one($_SESSION['user_rs']['group_id']);
	if(!$group_rs || !$group_rs['status']) return false;
	$condition = '';
	if($group_rs['fields'])
	{
		$lst = explode(',',$group_rs['fields']);
		$string = implode("','",$lst);
		$condition = "identifier IN('".$string."') ";
	}
	$flist = $GLOBALS['app']->model('user')->fields_all($condition,'identifier');
	if(!$flist) return false;
	$rslist = array(0=>array('val'=>$group_rs['title'],'title'=>$GLOBALS['app']->lang[$GLOBALS['app']->app_id]['user_group_name']));
	foreach($flist AS $key=>$value)
	{
		$tmp = array('title'=>$value['title'],'val'=>$_SESSION['user_rs'][$key]);
		$rslist[] = $tmp;
	}
	return $rslist;
}

//讀取我上傳的最新圖片
function usercp_new_reslist($offset=0,$psize=10)
{
	if(!$_SESSION['user_id']) return false;
	$condition = "ext IN('jpg','gif','png','jpeg') AND user_id='".$_SESSION['user_id']."'";
	//$condition = "ext IN('jpg','gif','png','jpeg')";
	return $GLOBALS['app']->model('res')->get_list($condition,$offset,$psize);
}


function time_format($timestamp)
{
	$current_time = $GLOBALS['app']->time;
    $since = abs($current_time-$timestamp);
    if(floor($since/3600))
	{
        if(date('Y-m-d',$timestamp) == date('Y-m-d',$current_time))
		{
            $output = '今天 ';
            $output.= date('H:i',$timestamp);
        }
		else
		{
            if(date('Y',$timestamp) == date('Y',$current_time))
			{
                $output = date('m月d日 H:i',$timestamp);
            }
			else
			{
                $output = date('Y年m月d日',$timestamp);
            }
        }
    }
	else
	{
        if(($output=floor($since/60)))
		{
            $output = $output.'分鐘前';
        }
		else
		{
			$output = '1分鐘內';
		}
    }
    return $output;
}

//前臺取得地址表單
function phpok_address($format=false)
{
	$shipping = $GLOBALS['app']->site['biz_shipping'];
	$billing = $GLOBALS['app']->site['biz_billing'];
	if(!$shipping && !$billing) return false;
	$rs = array();
	if($shipping)
	{
		$flist = $GLOBALS['app']->call->phpok('_fields',array("pid"=>$shipping,'fields_format'=>$format,'prefix'=>"s_"));
		$rs['shipping'] = $flist;
	}
	if($billing)
	{
		$flist = $GLOBALS['app']->call->phpok('_fields',array("pid"=>$billing,'fields_format'=>$format,'prefix'=>'b_'));
		$rs['billing'] = $flist;
	}
	return $rs;
}

//判斷屬性是否存在
function in_attr($str="",$info="")
{
	if(!$str || !$info) return false;
	$info = explode(",",$info);
	$str = explode(",",$str);
	$rs = array_intersect($str,$info);
	if($rs && count($rs)>0) return true;
	return false;
}

//讀取會員資訊
function phpok_user($id)
{
	return $GLOBALS['app']->model('user')->get_one($id);
}

//讀取文字內容，並格式化文字內容
function phpok_txt($file,$pageid=0,$type='txt')
{
	if(!$file || !is_file($GLOBALS['app']->dir_root.$file))
	{
		return false;
	}
	$content = file_get_contents($GLOBALS['app']->dir_root.$file);
	if(!$content) return false;
	$rs = $GLOBALS['app']->model('data')->info_page($content,$pageid);
	if(!$rs) return false;
	if(is_string($rs))
	{
		$rs = $type == 'txt' ? nl2br($rs) : $rs;
		return array('content'=>$rs);
	}
	else
	{
		$rs['content'] = $type == 'txt' ? nl2br($rs['content']) : $rs;
		return $rs;
	}
}

/**
 * 生成 Token 或解析 Token，一個頁面僅允許一個 Token，請注意使用
 * @引數 data 要加密的資料，為空表示解密
**/
function phpok_token($data='')
{
	if(!$GLOBALS['app']->site['api_code']){
		return false;
	}
	$GLOBALS['app']->lib('token')->keyid($GLOBALS['app']->site['api_code']);
	if($data){
		$info = $GLOBALS['app']->lib('token')->encode($data);
		$GLOBALS['app']->assign('token',$info);
		return $info;
	}
	$getid = $GLOBALS['app']->config['token_id'] ? $GLOBALS['app']->config['token_id'] : 'token';
	if(!$getid){
		$getid = 'token';
	}
	$token = $GLOBALS['app']->get($getid);
	if(!$token){
		return false;
	}
	return $GLOBALS['app']->lib('token')->decode($token);	
}
