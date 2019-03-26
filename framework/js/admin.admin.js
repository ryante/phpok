/**
 * 管理員的增刪查改
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年09月02日
**/
;(function($){
	$.admin_admin = {
		set:function(id)
		{
			if(id && id != 'undefined'){
				var url = get_url('admin','set','id='+id);
				var title = p_lang('編輯管理員') + " #"+id;
			}else{
				var url = get_url('admin','set');
				var title = p_lang('新增管理員');
			}
			top.$.win(title,url);
		},
		status:function(id)
		{
			var url = get_url("admin","status","id="+id);
			$.phpok.json(url,function(rs){
				if(rs.status){
					if(!rs.info){
						rs.info = '0';
					}
					if(rs.info == '1'){
	                	$("#status_"+id).val(p_lang('啟用')).removeClass('layui-btn-danger');
					}else{
	                	$("#status_"+id).val(p_lang('停用')).addClass('layui-btn-danger');
					}
					return true;
				}
				if(!rs.info){
					rs.info = p_lang('設定管理員狀態錯誤');
				}
				layer.alert(rs.info);
				return true;
			});
		},
		del:function(id,title)
		{
			var tip = p_lang('確定要刪除管理員 {title} 嗎？',"<span class='red'>"+title+"</span>");
			layer.confirm(tip,function(index){
				var url = get_url("admin","delete","id="+id);
				$.phpok.json(url,function(data){
					if(data.status){
						layer.msg(p_lang('管理員刪除成功'));
						$("#admin_tr_"+id).remove();
						layer.close(index);
						return true;
					}
					layer.alert(data.info);
					return false;
				})
			});
		},
		if_system:function(val)
		{
			if(val && val == 1){
	            $("#sysmenu_html").hide();
	        }else{
	            $("#sysmenu_html").show();
	        }
		},
		save:function()
	    {
		    if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
		    $(".layui-form").ajaxSubmit({
		    	'url':get_url('admin','save'),
		    	'type':'post',
		    	'dataType':'json',
		    	'success':function(rs){
		    		if(rs.status){
			    		var id = $("#id").val();
			    		var tipinfo = (id && id != 'undefined') ? p_lang('編輯成功') : p_lang('管理員新增成功');
			    		$.admin.reload(get_url('admin'));
			    		layer.msg(tipinfo,{time:1000},function(){
				    		top.layui.admin.events.closeThisTabs();
			    		});
		    			return false;
		    		}
		    		$.dialog.alert(rs.info);
		    		return false;
		    	}
		    });
		    return false;
	    }
	}
	$(document).ready(function(){
		if($("form.layui-form").length>0){
			layui.use('form',function(){
				layui.form.render();
			});
		}
	});
})(jQuery);