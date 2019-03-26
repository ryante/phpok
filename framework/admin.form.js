/**
 * 後臺自定義表單中涉及到的JS觸發
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2018年01月18日
**/
;(function($){
	$._configForm = {
		text:function(id,val)
		{
			if(id == 'form_btn'){
				if(val == '' || val == 'undefined'){
					$("#ext_quick_words_html").show();
					$("#ext_color_html").hide();
					return true;
				}
				if(val == 'color'){
					$("#ext_quick_words_html").hide();
					$("#ext_color_html").show();
					return true;
				}
				$("#ext_quick_words_html").hide();
				$("#ext_color_html").hide();
				return true;
			}
			if(id == 'eqt'){
				$("#ext_quick_type").val(val);
			}
		},

		extitle:function(id,val,eid,etype)
		{
			if(id == 'form_pid'){
				if(!val || val == 'undefined' || val == '0'){
					$("#fields_show_html,#fields_used_html,#true_delete_html").hide();
					return true;
				}
				var url = get_url('form','project_fields','pid='+val);
				if(eid && eid != "undefined"){
					url += "&eid="+eid;
				}
				if(etype && etype != "undefined"){
					url += "&etype="+etype;
				}
				$.phpok.json(url,function(data){
					if(data.status){
						if(!data.info){
							$("#fields_show_html,#fields_used_html,#true_delete_html").hide();
							return true;
						}
						var slist = data.info.show;
						var html = '<ul class="layout">';
						for(var i in slist){
							html += '<li><label><input type="checkbox" name="form_show_editing[]" value="'+i+'"';
							if(slist[i].status){
								html += ' checked';
							}
							html += ' />'+slist[i].title+'</label></li>'
						}
						html += "</ul>";
						$("#fields_show").html(html);
						$("#fields_show_html,#true_delete_html").show();
						//使用資料
						var elist = data.info.used;
						var html = '<ul class="layout">';
						for(var i in elist){
							html += '<li><label><input type="checkbox" name="form_field_used[]" value="'+i+'"';
							if(elist[i].status){
								html += ' checked';
							}
							html += ' />'+elist[i].title+'</label></li>'
						}
						html += "</ul>";
						$("#fields_used").html(html);
						$("#fields_used_html,#true_delete_html").show();
						return true;
					}
					$("#fields_show_html,#fields_used_html,#true_delete_html").hide();
					$.dialog.alert(data.info);
					return false;
				});
				return true;
			}
			if(id == 'form_is_single'){
				if(val == 1){
					$("#form_maxcount_li").hide();
					$("#form_maxcount").val(1);
				}else{
					$("#form_maxcount_li").show();
					$("#form_maxcount").val(20);
				}
				return true;
			}
		},

		/**
		 * 表單選擇器，對錶單內容進行格式化操作
		 * @引數 val 選擇的表單型別
		 * @引數 id 要寫入的HTML欄位
		 * @引數 eid 已存在值
		 * @引數 etype 值的來源
		**/
		option:function(val,id,eid,etype)
		{
			if(!val || val == "undefined"){
				$("#"+id).html("").hide();
				return false;
			}
			var url = get_url("form","config","id="+$.str.encode(val));
			if(eid && eid != "undefined"){
				url += "&eid="+eid;
			}
			if(etype && etype != "undefined"){
				url += "&etype="+etype;
			}
			$.phpok.ajax(url,function(rs){
				if(rs && rs != 'exit'){
					$("#"+id).html(rs).show();
				}
			});
		}
	}
})(jQuery);