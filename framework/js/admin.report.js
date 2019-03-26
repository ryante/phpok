/**
 * 統計報表涉及到的JS操作
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年10月17日
**/
;(function($){
	$.admin_report = {
		select_project:function(val)
		{
			if(!val || val == 'undefined'){
				$("div[data-id=line-x],div[data-id=line-y],div[data-id=line-z]").hide();
				return true;
			}
			if(val && val != 'undefined'){
				var url = get_url('report','ajax_type','type='+val);
				$.phpok.json(url,function(data){
					if(!data.status){
						$.dialog.alert(data.info);
						return false;
					}
					if(!data.info){
						$("div[data-id=line-x],div[data-id=line-y]").addClass('hide');
						return true;
					}
					if(data.info.x){
						var x = data.info.x;
						var xhtml = '<option value="">'+p_lang('請選擇…')+'</option>';
						for(var i in x){
							xhtml += '<option value="'+i+'">'+x[i]+'</option>';
						}
						$("div[data-id=line-x] select").html(xhtml);
						$("div[data-id=line-x]").removeClass('hide');
					}
					if(data.info.y){
						var y = data.info.y;
						var yhtml = '<ul class="layout">';
						//var yhtml = '<select name="x"><option value="">'+p_lang('請選擇統計專案…')+'</option>';
						for(var i in y){
							yhtml += '<li><label><input type="checkbox" name="y[]" lay-ignore value="'+i+'"/> '+y[i]+'</label></li>';
						}
						yhtml += '</ul>';
						$("div[data-id=line-y]").html(yhtml);
						$("div[data-id=line-y]").removeClass('hide');
					}
					if(data.info.z){
						$("div[data-id=line-z]").removeClass('hide');
					}else{
						$("div[data-id=line-z]").addClass('hide');
					}
				});

				layui.form.render()
			}
		}
	}
	$(document).ready(function(){
		layui.use(['laydate','form'],function () {
	        layui.laydate.render({elem:'#startdate'});
	        layui.laydate.render({elem:'#stopdate'});
	        layui.form.on('select(type)',function (data) {
	            $.admin_report.select_project(data.value);
	            window.setTimeout("layui.form.render()",200);
	        })
	    });
	});
})(jQuery);


