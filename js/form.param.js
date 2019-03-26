/**
 * 規格引數涉及到的JS操作
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年07月19日
**/
;(function($){
	$.phpok_form_param = {

		/**
		 * 增加一行
		 * @引數 identifier 表單欄位標識
		 * @引數 width 文字框寬度
		**/
		add_line:function(identifier,width)
		{
			var td_count = ($("#"+identifier+"_tbl tr:eq(0) th")).length - 1;
			var html = '<tr>';
			html += '<td><input type="button" value="'+p_lang('刪除')+'" onclick="$.phpok_form_param.delete_line(this)" class="layui-btn layui-btn-sm layui-btn-danger" /></td>';
			for(var i=0;i<td_count;i++){
				html += '<td><input type="text" name="'+identifier+'_body[]"';
				html += ' class="layui-input"';
				html += ' style="width:99%" /></td>';
			}
			html += '</tr>';
			$("#"+identifier+"_tbl").append(html);
		},
		delete_line:function(obj)
		{
			$(obj).parent().parent().remove();
			return true;
		},
		delete_one:function(identifier,obj)
		{
			var idx = $('th').index($(obj).parent());
			$("#"+identifier+"_tbl tr").each(function(){
				$(this).find('td:eq('+idx+')').remove();
			});
			$(obj).parent().remove();
		},
		add_ele_mul:function(identifier,width)
		{
			if(!width || width == 'undefined'){
				width = '120';
			}
			var val = $("#ele_"+identifier).val();
			var th = '<th>';
			th += '<input type="text" name="'+identifier+'_title[]" class="layui-input short" style="width:'+width+'px;float:left;" value="'+val+'" />';
			th += '<div style="position: absolute;top:5px;right:5px;" title="'+p_lang('刪除')+'" onclick="$.phpok_form_param.delete_one(\''+identifier+'\',this)"><i class="layui-icon layui-icon-close-fill"></i></div>'
			th += '</th>';
			$("#"+identifier+"_tbl tr:eq(0)").append(th);
			//定製列
			$("#"+identifier+"_tbl tr").each(function(i){
				if(i>0){
					var td = '<td><input type="text" name="'+identifier+'_body[]" class="layui-input short" style="width:'+width+'px;" value="" /></td>';
					$(this).append(td);
				}
			});
			$("#ele_"+identifier).val('');
		},
		add_ele_single:function(identifier,width)
		{
			var val = $("#ele_"+identifier).val();
			var html = '<div style="margin-bottom:10px;"><ul class="layout">';
			html += '<li><input type="text" name="'+identifier+'_title[]" class="layui-input" value="'+val+'"/></li>';
			html += '<li><input type="text" name="'+identifier+'_body[]" class="layui-input" /></li>';
			html += '<li style="margin-top:3px;"><input type="button" value="'+p_lang('刪除')+'" class="layui-btn layui-btn-sm layui-btn-danger" onclick="$.phpok_form_param.delete_line_single(this)" /></li>';
			html += '</ul><div class="clear"></div></div>';
			$("#list_"+identifier).append(html);
			$("#ele_"+identifier).val('');
		},
		delete_line_single:function(obj)
		{
			$(obj).parent().parent().parent().remove();
			return true;
		}
	}
})(jQuery);