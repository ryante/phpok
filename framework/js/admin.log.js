/**
 * 後臺日誌涉及到的操作
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年05月07日
**/
;(function($){
	$.admin_log = {
		search:function(name,val)
		{
			if(name == 'start_time'){
				$("input[name=start_time]").val(val);
				$("input[type=submit][class=submit2]").click();
			}
		},
		del:function(id)
		{
			$.dialog.confirm(p_lang('確定要刪除這條日誌嗎？'),function(){
				var url = get_url('log','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info,true,'error');
				})
			})
		},
		delete30:function()
		{
			$.dialog.confirm(p_lang('確定要刪除30天之前日誌嗎？'),function(){
				var url = get_url('log','delete','date=30');
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info,true,'error');
				})
			})
		},
		delete_selected:function()
		{
			var ids = $.checkbox.join();
			if(!ids){
				$.dialog.alert(p_lang('未選擇要刪除的日誌'));
				return false;
			}
			$.dialog.confirm(p_lang('確定要刪除選中的日誌嗎？'),function(){
				var url = get_url('log','delete','ids='+$.str.encode(ids));
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info,true,'error');
				})
			})
		}
	}
})(jQuery);