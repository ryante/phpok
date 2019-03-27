/**
 * 應用管理器
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年06月07日
**/
;(function($){
	$.admin_appsys = {
		setting:function()
		{
			$.dialog.open(get_url('appsys','setting'),{
				'title':p_lang('APP應用配置'),
				'lock':true,
				'width':'500px',
				'height':'240px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.$.admin_appsys.setting_save();
					return false;
				},
				'cancel':true
			});
		},
		setting_save:function()
		{
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			var opener = $.dialog.opener;
			var url = get_url('appsys','setting_save');
			$("#post_save").ajaxSubmit({
				'url':url,
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						opener.$.dialog.tips(p_lang('配置資訊成功'));
						$.dialog.close();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return true;
		},
		remote:function()
		{
			var tip = $.dialog.tips(p_lang('正在更新遠端資料，請稍候…'),30)
			$.phpok.json(get_url('appsys','remote'),function(data){
				tip.close();
				if(data.status){
					$.dialog.alert('遠端資料更新完成',function(){
						$.phpok.reload();
					},'succeed');
					return true;
				}
				$.dialog.alert(data.info);
				return false;
			});
		},
		install:function(id)
		{
			var title = $("#"+id+"_title").text();
			$.dialog.confirm(p_lang('確定要安裝應用 {title} 嗎？請耐心等待安裝安全','<b class="red">'+title+'</b>'),function(){
				var url = get_url('appsys','install','id='+id);
				var tip = $.dialog.tips('正在安裝應用，請稍候…',100);
				$.phpok.json(url,function(data){
					tip.close();
					if(data.status){
						var info = data.info ? data.info : p_lang('應用發裝成功，涉及到選單項請全域性刷新');
						$.dialog.alert(info,function(){
							$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
				return true;
			});
		},
		uninstall:function(id)
		{
			var title = $("#"+id+"_title").text();
			$.dialog.confirm(p_lang('確定要解除安裝應用 {title} 嗎？<br>解除安裝過程不會考慮應用與應用之間的關聯，解除安裝前請仔細確認','<b class="red">'+title+'</b>'),function(){
				var url = get_url('appsys','uninstall','id='+id);
				var tip = $.dialog.tips('正在解除安裝應用，請稍候…',100);
				$.phpok.json(url,function(data){
					tip.close();
					if(data.status){
						var info = data.info ? data.info : p_lang('應用解除安裝成功');
						$.dialog.alert(info,function(){
							$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
				return true;
			});
		},
		delete_apps:function(id)
		{
			var title = $("#"+id+"_title").text();
			$.dialog.confirm(p_lang('確定要刪除應用 {title} 嗎？<br>刪除後不可恢復，請確認已備份過相應的檔案','<b class="red">'+title+'</b>'),function(){
				var url = get_url('appsys','delete','id='+id);
				var tip = $.dialog.tips('正在刪除應用，請稍候…',100).lock();
				$.phpok.json(url,function(data){
					tip.close();
					if(data.status){
						var info = data.info ? data.info : p_lang('應用刪除成功');
						$.dialog.alert(info,function(){
							$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
				return true;
			});
		},
		add:function()
		{
			var url = get_url('appsys','add');
			$.dialog.open(url,{
				'title':p_lang('建立新應用'),
				'lock':true,
				'width':'700px',
				'height':'483px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.$.admin_appsys.create();
					return false;
				},
				'okVal':p_lang('提交儲存'),
				'cancel':true,
				'cancelVal':p_lang('取消關閉')
			});
		},
		create:function()
		{
			var opener = $.dialog.opener;
			$("#post_save").ajaxSubmit({
				'url':get_url('appsys','create'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.alert(p_lang('應用建立成功，請開發人員進行開發操作'),function(){
							opener.$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		export_zip:function(id)
		{
			$.phpok.go(get_url('appsys','export','id='+id));
		},
		backup_zip:function(id)
		{
			var url = get_url('appsys','backup','id='+id);
			var obj = $.dialog.tips(p_lang('正在備份中…'),100).lock();
			$.phpok.json(url,function(rs){
				obj.close();
				if(rs.status){
					$.dialog.tips(p_lang('備份成功')).lock();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			})
		},
		import_zip:function()
		{
			var url = get_url('appsys','import');
			$.dialog.open(url,{
				'title':p_lang('匯入應用'),
				'width':'500px',
				'height':'150px',
				'lock':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('開始上傳'),
				'cancel':true
			})
		},
		backup_delete:function(id)
		{
			var tip = p_lang('確定要刪除備份檔案{file}嗎？刪除後是不能恢復的','<span class="red">'+id+'</span>');
			$.dialog.confirm(tip,function(){
				var url = get_url('appsys','backup_delete','id='+$.str.encode(id));
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.alert(p_lang('備份檔案刪除成功'),function(){
							$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				})
			})
		},
		filelist:function(id,title)
		{
			$.win(p_lang('檔案列表')+"_"+title,get_url('appsys','filelist','id='+id));
		},
		file_edit:function(id,folder,title)
		{
			$.win(p_lang('編輯')+"_"+title,get_url('appsys','file_edit','id='+id+"&folder="+$.str.encode(folder)+"&title="+$.str.encode(title)));
		}
	}
})(jQuery);