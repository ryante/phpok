/**
 * 貨幣管理
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年11月25日
**/

;(function($){
	$.admin_currency = {
		status:function(id)
		{
			$.phpok.json(get_url("currency","status","id="+id),function(rs){
				if(rs.status){
					if(!rs.info){
						rs.info = '0';
					}
					var oldvalue = $("#status_"+id).attr("value");
					var old_cls = "status"+oldvalue;
					$("#status_"+id).removeClass(old_cls).addClass("status"+rs.info);
					$("#status_"+id).attr("value",rs.info);
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		}
	}
})(jQuery);

function set_sort()
{
	var ids = $.input.checkbox_join();
	if(!ids)
	{
		$.dialog.alert("未指定要排序的ID");
		return false;
	}
	var url = get_url("currency","sort");
	var list = ids.split(",");
	for(var i in list)
	{
		var val = $("#taxis_"+list[i]).val();
		url += "&sort["+list[i]+"]="+val;
	}
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		$.phpok.reload();
	}
	else
	{
		$.dialog.alert(rs.content);
		return false;
	}
}

function check_save()
{
	var title = $("#title").val();
	if(!title)
	{
		$.dialog.alert("貨幣名稱不能為空");
		return false;
	}
	var code =$("#code").val();
	if(!code)
	{
		$.dialog.alert("貨幣標識不能為空");
		return false;
	}
	if(code.length != '3')
	{
		$.dialog.alert("標識只支援三位數");
		return false;
	}
	return true;
}

function currency_del(id,title)
{
	$.dialog.confirm("確定要刪除貨幣：<span class='red'>"+title+"</span>，刪除操作可能會給現有產品資訊貨幣計算帶來錯，請慎用！",function(){
		var url = get_url('currency','delete','id='+id);
		var rs = json_ajax(url);
		if(rs.status == 'ok')
		{
			$.dialog.alert("貨幣：<span class='red'>"+title+"</span> 刪除成功",function(){
				$.phpok.reload();
			});
		}
		else
		{
			if(!rs.content) rs.content = '刪除失敗';
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

function update_taxis(val,id)
{
	var url = get_url("currency","sort","sort["+id+"]="+val);
	$.phpok.json(url,function(rs){
		if(rs.status == 'ok'){
			$.phpok.reload();
		}else{
			$.dialog.alert(rs.content);
			return false;
		}
	});
}
$(document).ready(function(){
	$("div[name=taxis]").click(function(){
		var oldval = $(this).text();
		var id = $(this).attr('data');
		$.dialog.prompt(p_lang('請填寫新的排序'),function(val){
			if(val != oldval){
				update_taxis(val,id);
			}
		},oldval);
	});
});