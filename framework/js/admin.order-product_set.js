/**
 * 訂單產品儲存操作
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年10月03日
**/
function save()
{
	if(typeof(CKEDITOR) != "undefined"){
		for(var i in CKEDITOR.instances){
			CKEDITOR.instances[i].updateElement();
		}
	}
	var opener = $.dialog.opener;
	$("#post_save").ajaxSubmit({
		'url':get_url('order','product_save'),
		'type':'post',
		'dataType':'json',
		'success':function(rs){
			if(rs.status){
				$.dialog.close();
				$.dialog.tips(p_lang('產品資訊操作成功'),function(){
					opener.$.admin_order_set.product_reload();
				}).lock();
				return true;
			}
			$.dialog.alert(rs.info);
			return false;
		}
	});
}

function load_product(id)
{
	var url = get_url('order','product','id='+id);
	var currency_id = $("#currency_info").attr("data-id");
	if(currency_id){
		url += '&currency_id='+currency_id;
	}
	
	$.phpok.json(url,function(rs){
		if(!rs.status){
			$.dialog.alert(rs.info);
			return false;
		}
		$("input[name=title]").val(rs.info.title);
		$("input[name=price]").val(rs.info.price);
		$("input[name=qty]").val(1);
		$("input[name=unit]").val(rs.info.unit);
		$("input[name=weight]").val(rs.info.weight);
		$("input[name=volume]").val(rs.info.volume);
		$("input[name=thumb]").val(rs.info.thumb);
		if(rs.info.is_virtual){
			$("input[name=is_virtual][value=1]").click();
		}else{
			$("input[name=is_virtual][value=0]").click();
		}
		return true;
	});
}