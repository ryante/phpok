/**
 * 系統選單操作
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年11月15日
**/

//新增一行
function add_trtd()
{
	var count = $("#popedom tr").length;
	var n_id = "tbl_"+(count+1).toString();
	var html = '<tr id="'+n_id+'">';
	html += '<td align="center"><input type="text" name="popedom_title_add[]" class="layui-input" /></td>';
	html += '<td align="center"><input type="text" name="popedom_identifier_add[]" class="layui-input" /></td>';
	html += '<td align="center"><input type="text" name="popedom_taxis_add[]" class="layui-input" /></td>';
	html += '<td align="center"><input type="button" value="刪除" class="layui-btn layui-btn-xs layui-btn-danger"  onclick="del_trtd(\''+n_id+'\')" /></td>';
	html += '</tr>';
	$("#popedom").append(html);
}

function del_trtd(id)
{
	$("#"+id).remove();
}

function popedom_del(id)
{
	//刪除許可權
	var url = get_url("system","delete_popedom")+"&id="+id;
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		$("#popedom_"+id).remove();
		return true;
	}
	else
	{
		if(!rs.content) rs.content = "刪除失敗";
		$.dialog.alert(rs.content);
		return false;
	}
}

$(document).ready(function() {
	$(".dropdown dt").click(function() {
		$(".dropdown dd ul").toggle();
	});
				
	$(".dropdown dd ul li").click(function() {
		var text = $(this).html();
		$(".dropdown dt span").html(text);
		$(".dropdown dd ul").hide();
		$("#icon").val($(this).find("span.value").html());
	});
	$(document).bind('click', function(e) {
		var $clicked = $(e.target);
		if (! $clicked.parents().hasClass("dropdown"))
			$(".dropdown dd ul").hide();
	});
});