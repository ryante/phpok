/**
 * 模組管理
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年10月04日
**/
;(function($){
	$.admin_module = {

		/**
		 * 欄位刪除
		**/
		field_del:function(id,title)
		{
			var tip = p_lang('確定要刪除欄位：{title}？<br/>刪除此欄位將同時刪除相應的內容資訊','<span class="red">'+title+'</span>');
			$.dialog.confirm(tip,function(){
				var url = get_url("module","field_delete") + "&id="+id;
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
		 * 欄位編輯
		**/
		field_edit:function(id)
		{
			var url = get_url("module","field_edit") + "&id="+id;
			$.dialog.open(url,{
				'title':p_lang('編輯欄位 #{id}',id),
				'lock':true,
				'width':'600px',
				'height':'70%',
				'resize':false,
				'drag':false,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe還沒載入完畢呢'));
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('儲存編輯資訊'),
				'cancel':function(){
					return true;
				}
			});
		},

		/**
		 * 欄位快速新增
		**/
		field_add:function(id,fid)
		{
			var url = get_url("module","field_add",'id='+id+'&fid='+fid);
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.phpok.reload();
					return false;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},

		/**
		 * 欄位標準新增
		**/
		field_addok:function(mid)
		{
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			var obj = art.dialog.opener;
			$("#form_save").ajaxSubmit({
				'url':get_url('module','field_addok','mid='+mid),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.alert(p_lang('欄位建立成功'),function(){
							obj.$.dialog.close();
							obj.$.phpok.reload();
						});
						return false;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},

		/**
		 * 新增模組欄位彈出操作
		**/
		field_create:function(id)
		{
			$.dialog.open(get_url("module","field_create","mid="+id),{
				'title':p_lang('新增欄位'),
				'lock':true,
				'width':'650px',
				'height':'70%',
				'resize':false,
				'drag':false,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe還沒載入完畢呢'));
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('提交儲存'),
				'cancel':true
			})
		},

		/**
		 * 儲存建立的模組
		**/
		set_save:function()
		{
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			var obj = art.dialog.opener;
			$("#module_submit_post").ajaxSubmit({
				'url':get_url('module','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						var id = $("#id").val();
						var tip = !id ? p_lang('模組新增成功') : p_lang('模組編輯成功');
						$.dialog.alert(tip,function(){
							obj.$.phpok.reload();
						},'succeed');
						return false;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},

		/**
		 * 模組建立
		**/
		create:function()
		{
			$.dialog.open(get_url('module','set'),{
				'title':p_lang('模組新增'),
				'lock':true,
				'width':'650px',
				'height':'400px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe還沒載入完畢呢'));
						return false;
					};
					iframe.$.admin_module.set_save();
					return false;
				},
				'okVal':p_lang('儲存'),
				'cancelVal':p_lang('取消'),
				'cancel':true
			});
		},
		
		/**
		 * 模組匯入
		**/
		input:function()
		{
			$.dialog.open(get_url('module','import'),{
				'title':p_lang('模組匯入'),
				'lock':true,
				'width':'500px',
				'height':'150px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe還沒載入完畢呢'));
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('匯入模組'),
				'cancelVal':p_lang('取消'),
				'cancel':true
			});
		},

		/**
		 * 模組編輯
		**/
		edit:function(id)
		{
			$.dialog.open(get_url('module','set','id='+id),{
				'title':p_lang('模組修改 #{id}',id),
				'lock':true,
				'width':'650px',
				'height':'400px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe還沒載入完畢呢'));
						return false;
					};
					iframe.$.admin_module.set_save();
					//iframe.save();
					return false;
				},
				'okVal':p_lang('儲存'),
				'cancelVal':p_lang('取消'),
				'cancel':true
			});
		},

		/**
		 * 模組刪除
		**/
		del:function(id,title)
		{
			$.dialog.confirm(p_lang('確定要刪除模組：{title}？<br/>如果模組中有內容，也會相應的被刪除，請慎用','<span style="color:red;font-weight:bold;">'+title+'</span>'),function(){
				var url = get_url("module","delete")+"&id="+id;
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return false;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		},

		/**
		 * 模組匯出
		**/
		export:function(id)
		{
			var url = get_url('module','export','id='+id);
			$.phpok.go(url);
		},

		/**
		 * 自定義模組要顯示的欄位
		**/
		layout:function(id,title)
		{
			$.dialog.open(get_url("module","layout","id="+id),{
				"title":p_lang('模型：{title} 後臺列表佈局','<span class="red">'+title+'</span>'),
				"width":"700px",
				"height":"400px",
				"win_min":false,
				"win_max":false,
				"resize": false,
				"lock": true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe還沒載入完畢呢'));
						return false;
					};
					iframe.$.admin_module.layout_save();
					//iframe.save();
					return false;
				},
				'okVal':p_lang('儲存'),
				'cancelVal':p_lang('取消'),
				'cancel':true
			});
		},

		layout_save:function()
		{
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			var obj = art.dialog.opener;
			$("#module_layout_save").ajaxSubmit({
				'url':get_url('module','layout_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.close();
						$.dialog.tips('配置成功');
						return false;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},

		/**
		 * 模組複製
		**/
		copy:function(id,title)
		{
			$.dialog.prompt(p_lang('請設定新模組的名稱：'),function(val){
				if(!val){
					$.dialog.alert(p_lang('名稱不能為空'));
					return false;
				}
				var url = get_url("module","copy","id="+id+"&title="+$.str.encode(val));
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			},title);
		},

		/**
		 * 模組狀態變更
		**/
		status:function(id)
		{
			$.phpok.json(get_url("module","status","id="+id),function(rs){
				if(rs.status){
					if(!rs.info){
						rs.info = '0';
					}
					var oldvalue = $("#status_"+id).attr("value");
					var old_cls = "status"+oldvalue;
					$("#status_"+id).removeClass(old_cls).addClass("status"+rs.info);
					$("#status_"+id).attr("value",rs.info);
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		/**
		 * 模組排序
		**/
		taxis:function(id,taxis)
		{
			$.dialog.prompt(p_lang('請填寫新的排序：'),function(val){
				if(val != taxis){
					$.phpok.json(get_url('module','taxis','taxis='+val+"&id="+id),function(rs){
						if(rs.status){
							$.phpok.reload();
							return true;
						}
						$.dialog.alert(rs.info);
						return false;
					});
				}
			},taxis);
		}
	}
})(jQuery);