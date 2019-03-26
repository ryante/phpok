/**
 * 附件資料管理
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年12月18日
**/
;(function($){
	$.admin_res = {
		edit_local:function(){
			var url = get_url('res','setting_remote_to_local');
			$.dialog.open(url,{
				'title':p_lang('編輯器附件本地化設定'),
				'width':'600px',
				'height':'500px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.$.admin_res.edit_local_save();
					return false;
				}
			});
		},
		/**
		 * 配置儲存
		**/
		edit_local_save:function()
		{
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			var opener = $.dialog.opener;
			$("#post_save").ajaxSubmit({
				'url':get_url('res','setting_remote_to_local_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.close();
						$.dialog.tips('配置操作成功');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
		},
		/**
		 * 批量更新指定的附件
		**/
		pl_update:function()
		{
			var id = $.input.checkbox_join(".checkbox input[type=checkbox]");
			if(!id || id == "undefined"){
				$.dialog.alert(p_lang('未指定要操作的附件'));
				return true;
			}
			var url = get_url("res","update_pl") + "&id="+$.str.encode(id);
			top.$.win(p_lang('附件批量更新中，請不要關掉這個頁面'),url,{'is_max':true,'win_max':false,'width':600,'height':400});
		},
		/**
		 * 批量刪除指定的附件
		**/
		pl_delete:function()
		{
			var id = $.input.checkbox_join(".checkbox input[type=checkbox]");
			if(!id || id == "undefined"){
				$.dialog.alert(p_lang('未指定要操作的附件'));
				return false;
			}
			$.dialog.confirm(p_lang('確定要刪除選中的附件嗎？刪除後是不可恢復的'),function(){
				var url = get_url("res","delete_pl") + "&id="+$.str.encode(id);
				$.phpok.json(url,function(rs){
					if(rs.status == 'ok'){
						$.dialog.tips(p_lang('批量刪除附件操作成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.content);
					return false;
				})
			});
		},
		/**
		 * 檔案刪除
		**/
		file_delete:function(id)
		{
			$.dialog.confirm(p_lang('確定要刪除此附件嗎？刪除後不能恢復'),function(){
				url = get_url('upload','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(rs.status == 'ok'){
						$.dialog.tips(p_lang('附件刪除成功'),function(){
							$("#thumb_"+id).remove();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.content);
					return false;
				})
			});
		},
		/**
		 * 附件預覽
		**/
		preview_attr:function(id)
		{
			$.dialog.open(get_url('upload','preview','id='+id),{
				'title':p_lang('預覽附件資訊'),
				'width':'700px',
				'height':'400px',
				'lock':false,
				'button': [{
					'name': p_lang('下載原檔案'),
					'callback': function () {
						$.phpok.open(get_url('res','download','id='+id));
						return false;
					},
				}],
				'okVal':p_lang('關閉'),
				'ok':true
			});
		},
		/**
		 * 更新全部附件資訊
		**/
		update_pl_pictures:function()
		{
			$.dialog.prompt(p_lang('請輸入要開始更新的圖片數字ID<br/>預設表示更新全部圖片（會佔用比較多的時間）'),function(val){
				var url = get_url("res","update_pl","id=all");
				if(parseInt(val)>0){
					url +="&start_id="+val;
				}
				top.$.win(p_lang('附件批量更新中'),url,{'is_max':true,'win_max':false,'width':600,'height':400});
			},0).title(p_lang('批量更新圖片規格'));
		},
		/**
		 * 新增附件
		**/
		add_file:function()
		{
			$.dialog.open(get_url('res','add'),{
				'title':p_lang('新增附件資訊'),
				'width':'700px',
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
				'okVal':p_lang('執行附件上傳'),
				'cancelVal':p_lang('取消上傳並關閉'),
				'cancel':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe還沒載入完畢呢'));
						return false;
					};
					iframe.cancel();
					return true;
				}
			});
		},
		/**
		 * 編輯附件操作
		**/
		modify:function(id)
		{
			$.dialog.open(get_url('res','set','id='+id),{
				'title':p_lang('編輯附件資訊'),
				'width':'700px',
				'height':'400px',
				'lock':true,
				'okVal':p_lang('提交'),
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert(p_lang('iframe還沒載入完畢呢'));
						return false;
					};
					iframe.save();
					return false;
				},
				'cancelVal':p_lang('取消修改'),
				'cancel':true
			});
		},
		/**
		 * 移動分類
		**/
		move_cate:function()
		{
			var id = $.input.checkbox_join(".checkbox input[type=checkbox]");
			if(!id || id == "undefined"){
				$.dialog.alert(p_lang('未指定要操作的附件'));
				return false;
			}
			$.dialog({
				'title':p_lang('移動分類，請選擇目標移動分類'),
				'content':document.getElementById('move_cate_html'),
				'lock':true,
				'width':'500px',
				'height':'100px',
				'cancel':function(){},
				'cancelVal':p_lang('取消移動'),
				'okVal':p_lang('執行'),
				'ok':function(){
					var newcate = $("input[name=newcate]:checked").val();
					var url = get_url('res','movecate')+"&tid="+$.str.encode(id)+"&newcate="+newcate;
					$.phpok.json(url,function(){
						$.input.checkbox_none('.checkbox input[type=checkbox]');
						$.dialog.tips(p_lang('分類移動成功'));
						return true;
					});
				}
			});
		},
		zipit:function(id,ext)
		{
			$.dialog.confirm(p_lang('確定初始化當前檔案原圖大小嗎？'),function(){
				var width = $("#resize").val();
				var url = get_url('res','resize','id='+id+'&width='+width);
				if(ext == 'jpg' || ext == 'jpeg'){
					url += "&ptype="+$("#ptype").val();
				}
				var tip = $.dialog.tips(p_lang('正在初始化圖片，請稍候…'));
				$.phpok.json(url,function(data){
					tip.close();
					if(data.status){
						$.dialog.tips('圖片初始化成功');
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				})
			});
		}
		
	}
	$(document).ready(function(){
		layui.use('laydate',function(){
			var laydate = layui.laydate;
			if($("#start_date").length > 0){
				laydate.render({
                    elem: '#start_date',
                });
			}
			if($("#stop_date").length > 0){
                laydate.render({
                    elem: '#stop_date',
                });
			}
		});
		
	});
})(jQuery);
