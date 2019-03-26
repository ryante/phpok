/**
 * 後臺會員涉及到的地址
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年05月27日
**/
;(function($){
	$.admin_user = {
		address:function(id)
		{
			var url = get_url('address','open','type=user_id&keywords='+id);
			$.dialog.open(url,{
				'title':p_lang('會員地址'),
				'width':'800px',
				'height':'500px',
				'lock':true
			})
		},
		show_setting:function()
		{
			var url = get_url('user','show_setting');
			$.dialog.open(url,{
				'title':p_lang('會員欄位顯示設定'),
				'width':'600px',
				'height':'400px',
				'lock':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				},'okVal':p_lang('提交'),'cancel':true,'cancelVal':p_lang('取消')
			})
		},

		/**
		 * 會員欄位快速新增
		**/
		field_quick_add:function(id)
		{
			var url = get_url('user','fields_save','id='+id);
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
		 * 會員欄位刪除
		**/
		field_delete:function(id,title)
		{
			$.dialog.confirm(p_lang('確定要刪除欄位 {title} 嗎？<br>刪除後相應的欄位內容也會被刪除，不能恢復','<span class="red">'+title+'</span>'),function(){
				$.phpok.json( get_url("user","field_delete","id="+id),function(rs){
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
		 * 會員欄位編輯
		**/
		field_edit:function(id)
		{
			$.dialog.open(get_url("user","field_edit","id="+id),{
				"title" : p_lang('編輯欄位屬性'),
				"width" : "700px",
				"height" : "80%",
				"resize" : false,
				"lock" : true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.$.admin_user.field_save();
					return false;
				},
				'okVal':p_lang('儲存'),
				'cancel':true
			});
		},

		/**
		 * 會員欄位新增
		**/
		field_add:function()
		{
			$.dialog.open(get_url("user","field_edit"),{
				"title" : p_lang('新增會員欄位'),
				"width" : "700px",
				"height" : "80%",
				"resize" : false,
				"lock" : true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.$.admin_user.field_save();
					return false;
				},
				'okVal':p_lang('儲存'),
				'cancel':true
			});
		},

		/**
		 * 儲存擴充套件欄位資訊
		**/
		field_save:function()
		{
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			var opener = $.dialog.opener;
			var obj = $.dialog.tips(p_lang('正在儲存資料…'),100);
			$("#post_save").ajaxSubmit({
				'url':get_url('user','field_edit_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						obj.content(p_lang('資料儲存成功'));
						opener.$.phpok.reload();
						return true;
					}
					obj.close();
					$.dialog.alert(rs.info);
					return false;
				}
			});
		},

		save:function()
		{
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			$("#post_save").ajaxSubmit({
				'url':get_url('user','setok'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.alert(rs.info,function(){
							$.admin.reload(get_url('user'));
							$.admin.close();
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