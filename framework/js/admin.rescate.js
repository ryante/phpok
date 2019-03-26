/**
 * 附件分類管理器
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年12月30日
**/
;(function($){
	$.admin_rescate = {
		del:function(id,title)
		{
			var tip = p_lang('確定要刪除這個附件分類嗎？{title}','<span class="red">'+title+'</span>');
			$.dialog.confirm(tip,function(){
	            var url = get_url('rescate','delete','id='+id);
	            $.phpok.json(url,function(rs){
	                if(rs.status == 'ok'){
	                    $.dialog.alert('刪除成功',function(){
	                        $.phpok.reload();
	                    },'succeed');
	                }else{
	                    $.dialog.alert(rs.content);
	                    return false;
	                }
	            });
	        });
		},
		etypes_info:function(id)
		{
			if(!id || id == 'undefined'){
				id = $("#")
			}
		}
	}
})(jQuery);