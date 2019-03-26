/***********************************************************
	Filename: {phpok}/js/list.js
	Note	: 內容管理裡的JS
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-02-21 11:13
***********************************************************/
function project_check()
{
	var id = $("#id").val();
	var title = $("#title").val();
	if(title) title = $.trim(title);
	if(!title)
	{
		$.dialog.alert("名稱不能為空");
		return false;
	}
	return true;
}



function content_del(id)
{
	$.dialog.confirm("確定要刪除主題ID：<span class='red'>"+id+"</span> 的資訊嗎？<br />刪除後是不能恢復的？",function(){
		var url = get_url("list","del") +"&id="+id;
		var rs = json_ajax(url);
		if(rs.status == "ok")
		{
			$.dialog.alert("主題刪除成功",function(){
				window.location.reload();
			});
		}
		else
		{
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

function tab_id(id)
{
	$("#float_tab li").each(function(i){
		if(i == id)
		{
			$(this).removeClass("tab_out").addClass("tab_over");
			$("#content_"+id).show();
		}
		else
		{
			$(this).removeClass("tab_over").addClass("tab_out");
			$("#content_"+i).hide();
		}
	});
}

// 顯示高階屬性配置
function show_advanced()
{
	if($("#advanced").is(":hidden"))
	{
		$("#advanced").show();
	}
	else
	{
		$("#advanced").hide();
	}
}

function project_delete(id)
{
	var title = $("#txt_"+id).html();
	var url = $("#delurl_"+id).attr("href");
	if(!url)
	{
		$.dialog.alert("配置有錯誤，請檢查");
		return false;
	}
	$.dialog.confirm("確定要刪除 <span class='red'>"+title+"</span> 嗎？刪除後其內容將會一起被清除掉",function(){
		var rs = json_ajax(url);
		if(rs.status == "ok")
		{
			$.dialog.alert("刪除成功",function(){
				window.location.reload();
			});
		}
		else
		{
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

function project_config(id)
{
	var url = $("#config_"+id).attr("href");
	if(!url)
	{
		$.dialog.alert("配置有錯誤，請檢查");
		return false;
	}
	direct(url);
}

function project_content(id)
{
	var url = $("#content_"+id).attr("href");
	if(!url)
	{
		$.dialog.alert("配置有錯誤，請檢查");
		return false;
	}
	direct(url);
}

//批量稽核
function set_status(id)
{
	var url = get_url("list","content_status") + '&id='+id;
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		if(!rs.content) rs.content = '0';
		var oldvalue = $("#status_"+id).attr("value");
		var old_cls = "status"+oldvalue;
		$("#status_"+id).removeClass(old_cls).addClass("status"+rs.content).attr("value",rs.content);
	}
	else
	{
		$.dialog.alert(rs.content);
		return false;
	}
}

//批量排序
function set_sort()
{
	var ids = $.input.checkbox_join();
	if(!ids)
	{
		$.dialog.alert("未指定要排序的ID");
		return false;
	}
	var url = get_url("list","content_sort");
	var list = ids.split(",");
	for(var i in list)
	{
		var val = $("#sort_"+list[i]).val();
		url += "&sort["+list[i]+"]="+val;
	}
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		window.location.reload();
	}
	else
	{
		$.dialog.alert(rs.content);
		return false;
	}
}

//批量刪除
function set_delete()
{
	var ids = $.input.checkbox_join();
	if(!ids)
	{
		$.dialog.alert("未指定要刪除的主題");
		return false;
	}
	$.dialog.confirm("確定要刪除選定的主題嗎？<br />刪除後是不能恢復的？",function(){
		var url = get_url("list","del") +"&id="+$.str.encode(ids);
		var rs = json_ajax(url);
		if(rs.status == "ok")
		{
			$.dialog.alert("主題刪除成功",function(){
				window.location.reload();
			});
		}
		else
		{
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

function show_order()
{
	if($("#page_sort").is(":hidden"))
	{
		$("#page_sort").show();
	}
	else
	{
		$("#page_sort").hide();
	}
}

function page_sort()
{
	var ids = $.input.checkbox_join();
	if(!ids)
	{
		$.dialog.alert("未指定要排序的ID");
		return false;
	}
	var url = get_url("list","sort");
	var list = ids.split(",");
	for(var i in list)
	{
		var val = $("#taxis_"+list[i]).val();
		url += "&sort["+list[i]+"]="+val;
	}
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		$.dialog.alert("排序更新成功",function(){
			window.location.reload();
		});
	}
	else
	{
		$.dialog.alert(rs.content);
		return false;
	}
}

function plus_price()
{
	var m = 1;
	$("#ext_price tr").each(function(i){
		if(i > 1)
		{
			m++;
		}
	});
	var html = '<tr id="ext_price_'+m+'"><td><input type="text" name="price_title[]" value="" /></td><td><input type="text" name="qty[]" value="" class="short" /></td><td><input type="text" name="price[]" value="" /></td><td><input type="button" value="-" onclick="minus_price('+m+')" class="btn" /></td></tr>';
	var t = m - 1;
	$("#ext_price_"+t).after(html);
}

function minus_price(id)
{
	$("#ext_price_"+id).remove();
}



function update_select()
{
	var val = $("#list_action_val").val();
	if(val.substr(0,5) == 'attr:'){
		$("#attr_set_li").show();
	}else{
		$("#attr_set_li").hide();
	}
	if(val.substr(0,5) == 'cate:'){
		$("#cate_set_li").show();
	}else{
		$("#cate_set_li").hide();
	}
}

function set_admin_id(id)
{
	var url = get_url('workflow','title','id='+id);
	$.dialog.open(url,{
		'title':p_lang('指派管理員維護'),
		'lock':true,
		'width':'500px',
		'height':'300px',
		'ok':function(){
			var iframe = this.iframe.contentWindow;
			if (!iframe.document.body) {
				alert(p_lang('iframe還沒載入完畢呢'));
				return false;
			};
			return iframe.save();
		},
		'cancel':function(){
			return true;
		}
	});
}

function set_parent()
{
	var ids = $.input.checkbox_join();
	if(!ids){
		$.dialog.alert(p_lang('未指定要操作的主題'));
		return false;
	}
	$.dialog.prompt(p_lang('請輸入繫結的主題ID號，要繫結的主題不能是選中的ID'),function(val){
		if(!val){
			$.dialog.alert('內容不能為空');
			return false;
		}
		var lst = ids.split(',');
		var isin = false;
		for(var i in lst){
			if(lst[i] == val){
				isin = true;
			}
		}
		if(isin){
			$.dialog.alert(p_lang('輸入的主題重複了'));
			return false;
		}
		var url = get_url('list','set_parent','id='+val+"&ids="+$.str.encode(ids));
		var rs = $.phpok.json(url);
		if(rs.status){
			$.phpok.reload();
		}else{
			$.dialog.alert(rs.info);
			return false;
		}
	},'');
}

function unset_parent()
{
	var ids = $.input.checkbox_join();
	if(!ids){
		$.dialog.alert(p_lang('未指定要操作的主題'));
		return false;
	}
	$.dialog.confirm(p_lang('確定要移除父子級關係嗎？'),function(){
		var url = get_url('list','unset_parent','ids='+$.str.encode(ids));
		var rs = $.phpok.json(url);
		if(rs.status){
			$.phpok.reload();
		}else{
			$.dialog.alert(rs.info);
			return false;
		}
	})
}


function list_action_exec()
{
	var ids = $.input.checkbox_join();
	if(!ids){
		$.dialog.alert(p_lang('未指定要操作的主題'));
		return false;
	}
	var val = $("#list_action_val").val();
	if(!val || val == ''){
		$.dialog.alert(p_lang('未指定要操作的動作'),'','error');
		return false;
	}
	if(val == 'appoint'){
		set_admin_id(ids);
		return false;
	}
	if(val == 'delete'){
		set_delete();
		return false;
	}
	if(val == 'sort'){
		set_sort();
		return false;
	}
	if(val == 'set_parent'){
		set_parent();
		return false;
	}
	if(val == 'unset_parent'){
		unset_parent();
		return false;
	}
	//執行批量稽核通過
	if(val == 'status' || val == 'unstatus' || val == 'show' || val == 'hidden'){
		var url = get_url('list','execute','ids='+$.str.encode(ids)+"&title="+val);
	}else{
		var tmp = val.split(':');
		if(tmp[1] && tmp[0] == 'attr'){
			var type = $("#attr_set_val").val();
			url = get_url('list','attr_set','ids='+$.str.encode(ids)+'&val='+tmp[1]+'&type='+type);
		}else{
			var type = $("#cate_set_val").val();
			var url = get_url('list',"move_cate")+"&ids="+$.str.encode(ids)+"&cate_id="+tmp[1]+"&type="+type;
		}
	}
	$.dialog.tips('正在執行操作，請稍候…');
	var rs = $.phpok.json(url);
	if(rs.status == 'ok'){
		$.phpok.reload();
	}else{
		$.dialog.alert(rs.content);
		return false;
	}
}
