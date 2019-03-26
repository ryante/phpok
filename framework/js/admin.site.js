/**
 * 站點相關操作
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年04月13日
**/
;(function($){
	$.phpok_site = {
		del:function(id,title)
		{
			var tip = "確定要刪除網站 {title} 嗎？<br>刪除後網站相關資訊都將刪除且不能恢復，請慎用";
			$.dialog.confirm(p_lang(tip,'<span class="red i">'+title+'</span>'),function(){
				//刪除網站操作
				var url = get_url("site","delete",'id='+id);
				var tip_obj = $.dialog.tips("正在刪除站點資訊…",100);
				$.phpok.json(url,function(data){
					$.dialog.close(tip_obj);
					if(data.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		},
		set_default:function(id,title)
		{
			var tip = "確定要設定網站 {title} 為預設網站嗎?";
			$.dialog.confirm(p_lang(tip,"<span class='red i'>"+title+"</span>"),function(){
				$.phpok.json(get_url("site",'default','id='+id),function(data){
					if(data.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		},
		alias:function(id,old)
		{
			if(!old || old == 'undefined'){
				old = '';
			}
			$.dialog.prompt(p_lang('請輸入站點別名'),function(val){
				if(!val){
					$.dialog.alert(p_lang('別名不能為空'));
					return false;
				}
				var url = get_url('site','alias','id='+id+'&alias='+$.str.encode(val));
				$.phpok.json(url,function(data){
					if(data.status){
						$.dialog.alert(p_lang('別名設定成功'),function(){
							$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			},old);
		},
		add:function()
		{
			$.dialog.open(get_url('site','add'),{
				'title': p_lang('新增站點')
				,'lock': true
				,'width': '450px'
				,'height': '150px'
				,'resize': false
				,'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				}
				,'okVal':p_lang('新增新站點')
				,'cancel':true
			});
		}
	}
	$.admin_site = {
		order_edit:function(id)
		{
	        $.dialog.open(get_url('site', 'order_status_set', 'id=' + id), {
	            'title': p_lang('編輯'),
	            'lock': true,
	            'width': '550px',
	            'height': '500px',
	            'ok': function () {
	                var iframe = this.iframe.contentWindow;
	                if (!iframe.document.body) {
	                    alert('iframe還沒載入完畢呢');
	                    return false;
	                }
	                iframe.$.admin_site.order_save();
	                return false;
	            },
	            'okVal': p_lang('提交修改'),
	            'cancel': true,
	            'cancelVal':p_lang('取消關閉')
	        })
		},
		order_save:function()
		{
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			var obj = $.dialog.opener;
			$("#postsave").ajaxSubmit({
				'url':get_url("site",'order_status_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.alert('編輯成功',function(){
							obj.$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		adm_add_it:function()
		{
			var url = get_url('site', 'admin_status_set');
	        $.dialog.open(url, {
	            'title': p_lang('新增狀態'),
	            'lock': true,
	            'width': '450px',
	            'height': '300px',
	            'ok': function () {
	                var iframe = this.iframe.contentWindow;
	                if (!iframe.document.body) {
	                    alert('iframe還沒載入完畢呢');
	                    return false;
	                }
	                iframe.$.admin_site.adm_order_save();
	                return false;
	            },
	            'okVal': p_lang('提交儲存'),
	            'cancel': true
	        })
		},
		adm_edit_it:function(id)
		{
			var url = get_url('site', 'admin_status_set', "id=" + id);
	        $.dialog.open(url, {
	            'title': p_lang('編輯狀態') + " #" + id,
	            'lock': true,
	            'width': '450px',
	            'height': '300px',
	            'ok': function () {
	                var iframe = this.iframe.contentWindow;
	                if (!iframe.document.body) {
	                    alert('iframe還沒載入完畢呢');
	                    return false;
	                }
	                iframe.$.admin_site.adm_order_save();
	                return false;
	            },
	            'okVal': p_lang('提交儲存'),
	            'cancel': true
	        });
		},
		adm_order_save:function()
		{
			var obj = $.dialog.opener;
			$("#postsave").ajaxSubmit({
				'url':get_url("site",'admin_order_status_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.alert('資料儲存成功',function(){
							obj.$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		delete_it:function(id,obj)
		{
			$.dialog.confirm(p_lang('確定要刪除該訂單狀態嗎？注意，相應的訂單狀態不會刪除'),function(){
				var url = get_url('site','admin_order_status_delete','id='+id);
				$.phpok.json(url,function(data){
					if(data.status){
						$(obj).parent().parent().remove();
						$.dialog.tips(p_lang('訂單狀態刪除成功'));
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		},
		edit_price:function(id)
		{
			var url = get_url('site','edit_price','id='+id);
			$.dialog.open(url,{
				'title': p_lang('編輯狀態') + " #" + id,
				'lock': true,
				'width':'550px',
				'height':'300px',
				'ok': function () {
	                var iframe = this.iframe.contentWindow;
	                if (!iframe.document.body) {
	                    alert('iframe還沒載入完畢呢');
	                    return false;
	                }
	                iframe.$.admin_site.edit_price_save();
	                return false;
	            },
	            'okVal': p_lang('提交儲存'),
	            'cancel': true
			})
		},
		edit_price_save:function()
		{
			var obj = $.dialog.opener;
			$("#postsave").ajaxSubmit({
				'url':get_url("site",'price_status_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.alert('資料儲存成功',function(){
							obj.$.phpok.reload();
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
	$(document).ready(function(){
		$.getScript('js/clipboard.min.js',function(){
			var clipboard = new Clipboard('.site-url-copy');
			clipboard.on('success', function(e) {
				$.dialog.alert(p_lang('網址複製成功'));
				e.clearSelection();
			});

			clipboard.on('error', function(e) {
				$.dialog.alert(p_lang('網址複製失敗'));
			});
		});
	});
})(jQuery);

