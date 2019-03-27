/**
 * 偽靜態頁操作
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年07月03日
**/
;(function($){
	$.admin_rewrite = {
		taxis:function(val,id)
		{
			url = get_url('rewrite','taxis','id='+id+"&sort="+val);
			$.phpok.json(url,function(rs){
				if(rs.status){
					layer.msg(p_lang('排序變更成功，請手動刷新'));
					return true;
				}
				layer.alert(rs.info);
				return false;
			});
		},
		add:function()
		{
			var url = get_url('rewrite','set');
			$.phpok.go(url);
		},
		edit:function(id)
		{
			var url = get_url('rewrite','set','id='+id);
			$.phpok.go(url);
		},
		del:function(id,title)
		{
			layer.confirm(p_lang('確定要刪除這條規則嗎？{title}',"<span class='red'>"+title+"</span>"),function(){
				var url = get_url('rewrite','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						layer.msg(p_lang('規則刪除成功'));
						$("#edit_"+id).remove();
						return true;
					}
					layer.alert(rs.info);
					return false;
				});
			});
		},
		copy:function(id)
		{
			var url = get_url('rewrite','copy','id='+id);
			var rs = $.phpok.json(url,function(rs){
				if(rs.status){
					layer.msg(p_lang('複製偽靜態頁連結成功'));
					setTimeout(function () {
						$.phpok.reload();
                    },500);
					return true;
				}
				layer.alert(rs.info);
				return false;
			});
		}
	}
})(jQuery);

$(document).ready(function(){
	$("div[name=taxis]").click(function(){
		var oldval = $(this).text();
		var id = $(this).attr('data');
		layer.prompt(p_lang('請填寫新的排序'),function(val){
			if(val != oldval){
				$.admin_rewrite.taxis(val,id);
			}
		},oldval);
	});
});