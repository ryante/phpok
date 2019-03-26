/**
 * 收藏夾相關JS動作
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年06月04日
**/
;(function($){
	$.phpok_app_fav = {
		act:function(id,obj)
		{
			var url = api_url('fav','act','id='+id);
			$.phpok.json(url,function(data){
				if(data.status){
					if(data.info == 'add'){
						$(obj).val(p_lang('加入收藏成功'));
						window.setTimeout(function(){
							$(obj).val('已收藏')
						}, 1000);
					}
					if(data.info == 'delete'){
						$(obj).val(p_lang('取消收藏成功'));
						window.setTimeout(function(){
							$(obj).val('加入收藏')
						}, 1000);
					}
					return true;
				}
				$.dialog.alert(data.info);
				return false;				
			});
		},
		del:function(id)
		{
			$.dialog.confirm(p_lang('確定要刪除這條收藏記錄嗎？'),function(){
				var url = api_url('fav','delete','id='+id);
				$.phpok.json(url,function(data){
					if(data.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			})
		}
	}
})(jQuery);