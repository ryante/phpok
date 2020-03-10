/**
 * Tag標籤的增刪查改操作
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 GNU Lesser General Public License (LGPL)
 * @日期 2017年04月20日
**/
;(function($){
	$.phpok_tag = {
		add:function()
		{
			var url = get_url('tag','set');
			$.dialog.open(url,{
				'title':p_lang('新增標籤'),
				'width':'560px',
				'height':'360px',
				'lock':true,
				'okVal':p_lang('提交儲存'),
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'cancel':true
			});
		},
		edit:function(id)
		{
			var url = get_url('tag','set','id='+id);
			$.dialog.open(url,{
				'title':p_lang('修改標籤'),
				'width':'560px',
				'height':'360px',
				'lock':true,
				'okVal':p_lang('提交儲存'),
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'cancel':true
			});
		},
		del:function(id,title)
		{
			$.dialog.confirm(p_lang('確定要刪除標籤 {title} 嗎？刪除後相關標籤資料也會刪除','<span class="red">'+title+'</span>'),function(){
				var url = get_url('tag','delete','id='+id);
				$.phpok.json(url,function(data){
					if(data.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		},
		config:function()
		{
			var url = get_url('tag','config');
			$.dialog.open(url,{
				'title':p_lang('配置標籤引數'),
				'width':'500px',
				'height':'300px',
				'lock':true,
				'okVal':p_lang('提交儲存'),
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'cancel':true
			});
		},
		selected:function(val,cut_identifier)
		{
			var opener = $.dialog.opener;
			var old = opener.$("input[name=tag]").val();
			if(!old){
				opener.$("input[name=tag]").val(val);
				return true;
			}
			if(!cut_identifier || cut_identifier == 'undefined'){
				cut_identifier = ',';
			}
			var lst = old.split(cut_identifier);
			var total = lst.length;
			if(total>=20){
				$.dialog.alert(p_lang('超出系統限制，請刪除一些不常用的標籤'));
				return false;
			}
			var status = true;
			for(var i in lst){
				if(lst[i] && $.trim(lst[i]) == val){
					status = false;
				}
			}
			if(!status){
				$.dialog.alert(p_lang('標籤已經存在，不支援重複新增'));
				return false;
			}
			opener.$("input[name=tag]").val(old+""+cut_identifier+""+val);
			return true;
		}
	}
})(jQuery);