/**
 * 表單選項相關JS
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年08月02日
**/
;(function($){
	$.admin_opt = {
		opt_list:function(id)
		{
			$.phpok.go(get_url('opt','list','group_id='+id));
			return true;
		},
		group_edit:function(id,title)
		{
			var url = get_url('opt','group_set','id='+id);
			$.dialog.open(url,{
				'title':'編輯選項組資訊',
				'lock':true,
				'width':'500px',
				'height':'300px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':'修改儲存',
				'cancel':true
			});
		},
		group_add:function()
		{
			var url = get_url('opt','group_set');
			$.dialog.open(url,{
				'title':'新增組資訊',
				'lock':true,
				'width':'400px',
				'height':'300px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':'提交儲存',
				'cancel':true
			});
		},
		group_delete:function(id,title)
		{
			$.dialog.confirm(p_lang('確定要刪除選項組{title}，刪除後相應的資料也會刪除，請慎用',' <span class="red">'+title+'</span> '),function(){
				var url = get_url('opt','group_del','id='+id);
				$.phpok.ajax(url,function(data){
					if(data == 'ok'){
						$.dialog.alert(p_lang('刪除成功'),function(){
							$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(data);
					return false;
				})
			});
		},
		opt_delete:function(id,title)
		{
			$.dialog.confirm(p_lang('確定要刪除{title}嗎？<br>此操作將同時刪除子項內容！且不能恢復，請慎用！',' <span class="red">'+title+'</span> '),function(){
				var url = get_url('opt','del','id='+id);
				$.phpok.ajax(url,function(info){
					if(info == 'ok'){
						$.phpok.reload();
					}else{
						if(!info){
							info = p_lang('刪除失敗');
						}
						$.dialog.alert(info);
						return false;
					}
				})
			});
		},
		opt_export:function(id,pid,sub)
		{
			var url = get_url('opt','export','id='+id);
			if(pid && pid != 'undefined'){
				if(!isNaN(pid) && typeof pid != 'boolean'){
					url += "&pid="+pid;
				}else{
					url += "&sub="+(pid ? 1 : 0);
				}
			}
			if(sub && sub != 'undefined'){
				url += "&sub="+(sub ? 1 : 0);
			}
			$.phpok.go(url);
		},
		opt_import:function(id,pid)
		{
			var url = get_url('opt','import');
			if(id && id != 'undefined'){
				url += '&id='+id;
			}
			if(pid && pid != 'undefined'){
				url += "&pid="+pid;
			}
			$.dialog.open(url,{
				'title':p_lang('資料匯入'),
				'width':'500px',
				'height':'400px',
				'lock':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe還沒載入完畢呢'));
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('資料匯入'),
				'cancel':true
			})
		}
	}
	var config = {
		'title':'資訊',
		'width':'500px',
		'id':'html-edit-content',
		'okVal':'新增',
		'cancelVal':'取消',
		'taxis':10,
		'group_id':0,
		'pid':0,
		'tid':0
	}
	var opt = {
		init:function(opts){
			config = $.extend({},config,opts);
			if(config.total<1){
				config.total = 10;
			}
			return opt;
		},
		edit:function(id,opts){
			if(opts && opts != 'undefined'){
				this.init(opts);
			}
			var content = document.getElementById(config.id);
			var that = this;
			$.dialog({
				'width':config.width,
				'title':'修改',
				'content':content,
				'init':function(){
					var obj = $("#opt_"+id+" td");
					var val = obj.eq(1).text();
					var title = obj.eq(2).text();
					var taxis = obj.eq(3).text();
					$("input[name=taxis]").val(taxis);
					$("input[name=title]").val(title);
					$("input[name=val]").val(val);
				},
				'lock':true,
				'ok':function(){
					var url = get_url('opt','edit','id='+id);
					var title = $("input[name=title]").val();
					if(!title){
						$.dialog.alert('顯示資訊不能為空');
						return false;
					}
					url += "&title="+$.str.encode(title);
					var val = $("input[name=val]").val();
					if(!val){
						$.dialog.alert('值不能為空');
						return false;
					}
					url += "&val="+$.str.encode(val);
					url += "&taxis="+$.str.encode($("input[name=taxis]").val());
					var obj = $.dialog.tips('正在儲存資料，請稍候…');
					$.phpok.ajax(url,function(info){
						obj.close();
						if(info == 'ok'){
							$.phpok.reload();
							return true;
						}else{
							if(info){
								$.dialog.alert(info);
							}
						}
						return false;
					});
					
					
				},
				'okVal':config.okVal,
				'cancelVal':config.cancelVal,
				'cancel':true
			});
		},
		create:function(opts){
			this.init(opts);
			var content = document.getElementById(config.id);
			var that = this;
			$.dialog({
				'width':config.width,
				'title':config.title,
				'content':content,
				'init':function(){
					$("input[name=taxis]").val(config.taxis);
					$("input[name=title]").val('');
					$("input[name=val]").val('');
				},
				'lock':true,
				'ok':function(){
					var url = get_url('opt','add','group_id='+config.group_id+"&pid="+config.pid);
					var title = $("input[name=title]").val();
					if(!title){
						$.dialog.alert('顯示資訊不能為空');
						return false;
					}
					url += "&title="+$.str.encode(title);
					var val = $("input[name=val]").val();
					if(!val){
						$.dialog.alert('值不能為空');
						return false;
					}
					url += "&val="+$.str.encode(val);
					url += "&taxis="+$.str.encode($("input[name=taxis]").val());
					var obj = $.dialog.tips('正在儲存資料，請稍候…');
					$.phpok.ajax(url,function(info){
						obj.close();
						if(info == 'ok'){
							$.phpok.reload();
						}else{
							if(info){
								$.dialog.alert(info);
							}
							return false;
						}
					});
				},
				'okVal':config.okVal,
				'cancelVal':config.cancelVal,
				'cancel':true
			});
		}
	}
	$.extend({
		phpok_opt:function(opts){
			var method = arguments[0];
			if(opt[method]) {
				method = opt[method];
				arguments = Array.prototype.slice.call(arguments, 1);
			} else if( typeof(method) == 'object' || !method ) {
				method = opt.init;
			} else {
				$.error( 'Method ' +  method + ' does not exist on jQuery.phpok_opt' );
				return this;
			}
			return method.apply(this, arguments);
		}
	});
})(jQuery);

