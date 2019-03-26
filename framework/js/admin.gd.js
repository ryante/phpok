/**
 * GD操作中涉及到的JS
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年10月04日
**/
;(function($){
	$.admin_gd = {

		/**
		 * 設定編輯器使用哪個圖片規格方案
		 * @引數 id 方案ID
		**/
		editor:function(id)
		{
			var url = get_url('gd','editor');
			if(id && id != 'undefined'){
				url += "&id="+id;
			}
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.phpok.reload();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			})
		},

		/**
		 * 設定編輯器使用原圖
		**/
		tofile:function()
		{
			var self = this;
			$.dialog.confirm(p_lang('確定要讓編輯器呼叫原圖嗎？'),function(){
				self.editor(0);
			});
		},

		/**
		 * 刪除配置
		 * @引數 id 要刪除的專案ID
		**/
		del:function(id)
		{
			$.dialog.confirm(p_lang('確定要刪除這個圖片方案嗎？'),function(rs){
				var url = get_url('gd','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				})
			});
		},

		/**
		 * 儲存方案資料
		**/
		save:function()
		{
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			$("#gdsetting").ajaxSubmit({
				'url':get_url('gd','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						var info = $("#id").val() ? p_lang('方案編輯成功') : p_lang('方案新增成功');
						$.dialog.alert(info,function(){
							$.phpok.go(get_url('gd'));
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		}
	}
})(jQuery);