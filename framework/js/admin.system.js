/**
 * 核心模組
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年06月28日
**/
;(function($){
	$.admin_system = {
		set_status:function(id,obj)
		{
			var url = get_url("system","status","id="+id);
			$.phpok.json(url,function(rs){
				if(!rs.content){
					rs.content = '0';
				}
				var oldvalue = $(obj).val();
				if(oldvalue == '禁用'){
					$(obj).val('啟用');
				}else{
					$(obj).val('禁用');
				}
			});
		},
		delete_sysmenu:function(id,title)
		{
			$.dialog.confirm(p_lang('確定要刪除導航{title}嗎？刪除後是不能恢復的！',' <span class="red">'+title+'</span> '),function(){
				var url = get_url('system','delete','id='+id);
				var rs = $.phpok.json(url,function(rs){
					if(rs.status != 'ok'){
						$.dialog.alert(rs.content);
						return false;
					}
					$.phpok.reload();
				});
			});
		},
		update_taxis:function(val,id)
		{
			var url = get_url('system','taxis','taxis['+id+']='+val);
			$.phpok.json(url,function(rs){
				if(rs.status == 'ok'){
					$("div[data="+id+"]").html(val);
				}else{
					$.dialog.alert(rs.content);
					return false;
				}
			})
		},
		set_icon:function(id)
		{
			var url = get_url('system','icon','id='+id);
			$.dialog.open(url,{
				'title':p_lang('設定圖示'),
				'width':'70%',
				'height':'70%',
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
				'okVal':p_lang('提交儲存'),
				'cancel':true
			});
		},
		icon_hs:function(id)
		{
			var icon = $("#icon_status_"+id).attr('data-icon');
			var url = get_url('system','icon_save','id='+id);
			if(!icon){
				var url = get_url('system','icon_save','id='+id+"&icon=newtab");
			}
			$.phpok.json(url,function(rs){
				if(rs.status == 'ok'){
					if(!icon){
						$("#icon_status_"+id).val(p_lang('顯示')).attr("data-icon",'newtab');
						$("#icon_"+id).removeClass().addClass("hand").addClass('icon-newtab').show();
						return true;
					}
					$("#icon_status_"+id).val(p_lang('隱藏')).attr("data-icon",'');
					$("#icon_"+id).removeClass().hide();
					return true;
				}
				$.dialog.alert(rs.content);
				return true;
			});
		}
	}
	$(document).ready(function(){
		$("div[name=taxis]").click(function(){
			var oldval = $(this).text();
			var id = $(this).attr('data');
			$.dialog.prompt(p_lang('請填寫新的排序：'),function(val){
				if(val != oldval){
					$.admin_system.update_taxis(val,id);
				}
			},oldval);
		});
	});
})(jQuery);
