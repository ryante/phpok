/**
 * 物流快遞
 * @作者 qinggan <admin@phpok.com>
 * @版權 2008-2018 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2018年10月07日
**/
;(function($){
	$.admin_order_express = {
		save:function()
		{
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			$("#postsave").ajaxSubmit({
				'url':get_url('order','express_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('物流資訊新增成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		del:function(id)
		{
			var tip = p_lang('確定要刪除這條物流資訊嗎？刪除後相應記錄會被刪除');
			$.dialog.confirm(tip,function(){
				var url = get_url('order','express_delete','id='+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('刪除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				})
			});
		},
		remote:function(id)
		{
			var url = api_url('express','remote','id='+id);
			var tip = $.dialog.tips('正在獲取資料，請稍候…',100);
			$.phpok.json(url,function(rs){
				tip.close();
				if(rs.status){
					$.phpok.reload();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		}
	}
})(jQuery);