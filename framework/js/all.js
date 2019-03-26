/***********************************************************
	Filename: {phpok}/js/all.js
	Note	: 全域性模組引數設定
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-19 20:20
***********************************************************/

//新增或修改擴充套件組ID
function g_ext_check()
{
	var id = $("#id").val();
	var title = $("#title").val();
	if(!title){
		layer.alert(p_lang('名稱不能為空'));
		return false;
	}
	var identifier = $("#identifier").val();
	if(!identifier){
		layer.alert(p_lang('標識串不能為空'));
		return false;
	}
	var chk = $.str.identifier(identifier);
	if(!chk){
		layer.alert(p_lang('標識串不符合條件要求'));
		return false;
	}
	//檢測是否被使用了
	identifier = identifier.toLowerCase();
	if(identifier == "config" || identifier == "phpok"){
		layer.alert(p_lang('標識串不符合條件要求'));
		return false;
	}
	var url = get_url("all","all_check")+"&identifier="+$.str.encode(identifier);
	if(id){
		url +="&id="+id;
	}
	var rs = $.phpok.json(url);
	if(rs.status != "ok"){
		layer.alert(rs.content);
		return false;
	}
	var ico = $("input[name=ico]").val();
	if(!ico){
		layer.alert(p_lang('請選擇一個圖示'));
		return false;
	}
	return true;
}

//新增欄位
function all_add_ext(id,t)
{
	var url = get_url("all","ext_add") + "&id="+id;
	var all_id = $("#id").val();
	if(!all_id){
		layer.alert(p_lang('新增異常，未指定ID'));
		return false;
	}
	url += '&all_id='+all_id;
	var rs = $.phpok.json(url);
	if(rs.status == "ok"){
		autosave("ext_post","all",auto_refresh);
	}else{
		layer.alert(rs.content);
		return false;
	}
}

//刪除擴充套件欄位
function all_ext_delete(id,title)
{
	var cate_id = $("#id").val();
	url = get_url("all","ext_delete");
	if(!cate_id){
		layer.alert(p_lang('未指定全域性ID'));
		return false;
	}
	url += "&all_id="+cate_id;
	url += "&id="+id;
	layer.confirm(p_lang('確定要刪除這個擴充套件欄位嗎？'),function(){
		var rs = $.phpok.json(url);
		if(rs.status == 'ok'){
			autosave("ext_post","all",auto_refresh);
		}else{
			layer.alert(rs.content);
			return false;
		}
	});
}

function ext_g_delete(id)
{
	layer.confirm(p_lang('確定要刪除此組資訊嗎？刪除後相關資料都會一起被刪除'),function(){
		$.phpok.go(get_url('all','ext_gdelete','id='+id));
	});
}


function email_setting(val)
{
	if(val == 1){
		$("#email_setting").show();
	}else{
		$("#email_setting").hide();
	}
}

function set_url_type(val)
{
	if(!val || val == 'undefined'){
		val = 'default';
	}
	$("#url_type_default,#url_type_rewrite,#url_type_html").hide();
	$("#url_type_"+val).show();
}
