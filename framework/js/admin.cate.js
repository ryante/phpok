/**
 * 分類相關操作
 * @作者 qinggan <admin@phpok.com>
 * @版權 2008-2018 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2018年09月16日
**/

;(function($){
	$.admin_cate = {

		/**
		 * 儲存分類操作
		**/
		save:function()
		{
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			$("#post_save").ajaxSubmit({
				'url':get_url('cate','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						var id = $("#id").val();
						if(id && id > 0){
							var tip = p_lang('分類資訊編輯成功');
						}else{
							var tip = p_lang('分類資訊新增成功');
						}
						$.dialog.tips(tip,function(){
							$.admin.reload(get_url('cate'));
							$.admin.close();
						});
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},

		/**
		 * 刪除分類
		**/
		del:function(id)
		{
			$.dialog.confirm(p_lang('確定要刪除此分類嗎？{id}','<span class="red">#'+id+'</span>'),function(){
	            var url = get_url("cate","delete","id="+id);
	            $.phpok.json(url,function(rs){
		            if(rs.status){
			            $.dialog.tips(p_lang('分類刪除成功'),function(){
				            $.phpok.reload();
			            }).lock();
			            return true;
		            }
		            $.dialog.alert(rs.info);
		            return false;
	            });
	        });
		},

		/**
		 * 新增擴充套件分類
		**/
		ext_add:function(id)
		{
			var val = $("#_tmp_select_add").val();
			if(!val){
				$.dialog.alert(p_lang('請選擇要新增的擴充套件'));
				return false;
			}
			ext_add2(val,id);
		},
		status:function(id)
		{
			var url = get_url('cate','status','id='+id);
			$.phpok.json(url,function(rs){
				if(rs.status){
					if(rs.info == '1'){
						$("#status_"+id).removeClass("status0").addClass("status1");
					}else{
						$("#status_"+id).removeClass("status1").addClass("status0");
					}
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		}
	}
	$(document).ready(function(){
		if($("form.layui-form").length>0){
			layui.use('form',function(){
				layui.form.render();
			})
		}
	});
})(jQuery);