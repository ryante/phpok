/**
 * 地址庫中涉及到的 JS
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年06月04日
**/

;(function($){
	$.admin_address = {
		order:function(id)
		{
			var opener = $.dialog.opener;
			var url = get_url('address','one','id='+id);
			$.phpok.json(url,function(rs){
				if(rs.status){
					var data = rs.info;
					opener.$("#s-fullname").val(data.fullname);
					opener.$("#s-country").val(data.country);
					opener.$("#s-province").val(data.province);
					opener.$("#s-city").val(data.city);
					opener.$("#s-county").val(data.county);
					opener.$("#s-address").val(data.address);
					opener.$("#s-zipcode").val(data.zipcode);
					opener.$("#s-email").val(data.email);
					opener.$("#s-mobile").val(data.mobile);
					opener.$("#s-tel").val(data.tel);
					$.dialog.close();
				}else{
					$.dialog.alert(rs.info);
				}
			})
		},
		edit:function(id)
		{
			$.dialog.open(get_url('address','set','id='+id),{
				'title':p_lang('編輯地址')+" #"+id,
				'width':'650px',
				'height':'550px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.$.admin_address.save();
					return false;
				},
				'resize':false,
				'okVal':p_lang('儲存地址'),
				'cancel':true
			});
		},
		//新增地址
		add:function()
		{
			$.dialog.open(get_url('address','set'),{
				'title':p_lang('新增地址'),
				'width':'650px',
				'height':'550px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.$.admin_address.save();
					return false;
				},
				'resize':false,
				'okVal':p_lang('儲存地址'),
				'cancel':true
			});
		},
		del:function(id)
		{
			var tip = p_lang('確定要ID為 <span class="red">{id}</span> 的資料嗎？<br>刪除後地址庫資訊是不能恢復的',"#"+id);
			layer.confirm(tip,function(index){
				var url = get_url('address','delete','id='+id);
				$.phpok.json(url,function(data){
					if(data.status){
						layer.msg(p_lang('地址刪除成功'));
						$("#address_"+id).remove();
						layer.close(index);
						return true;
					}
					layer.alert(data.info);
					return true;
				});
			});
		},
		save:function()
		{
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			var opener = $.dialog.opener;
			$("#post_save").ajaxSubmit({
				'url':get_url('address','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						var id = $("#id").val();
						var tip = id ? p_lang('地址資訊編輯成功') : p_lang('地址資訊新增成功');
						$.dialog.tips(tip,function(){
							opener.$.phpok.reload();
						}).lock();
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

