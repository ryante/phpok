/**
 * 會員自定義欄位管理器
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 GNU Lesser General Public License (LGPL)
 * @日期 2017年03月31日
**/

function user_field_edit(id)
{
	var url = get_url("user","field_edit") + "&id="+id;
	$.dialog.open(url,{
		"title" : "編輯欄位屬性",
		"width" : "700px",
		"height" : "95%",
		"resize" : false,
		"lock" : true,
		'close'	: function(){
			direct(window.location.href);
		}
	});
}

//刪除欄位
function user_field_del(id,title)
{
	$.dialog.confirm(p_lang('確定要刪除欄位 {title} 嗎？<br>刪除後相應的欄位內容也會被刪除，不能恢復','<span class="red">'+title+'</span>'),function(){
		var url = get_url("user","field_delete") + "&id="+id;
		$.phpok.json(url,function(rs){
			if(rs.status){
				$.phpok.reload();
			}else{
				$.dialog.alert(rs.info);
			}
		})
	});
}

function user_field_quickadd(id)
{
	var url = get_url('user','fields_save','id='+id);
	$.phpok.json(url,function(rs){
		if(rs.status){
			$.phpok.reload();
		}else{
			$.dialog.alert(rs.info);
			return false;
		}
	})
}
