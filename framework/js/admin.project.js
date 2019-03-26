/**
 * 專案管理相關JS
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年10月07日
**/
;(function($){
	$.admin_project = {

		/**
		 * 專案編輯儲存
		**/
		save:function(id)
		{
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			$("#"+id).ajaxSubmit({
				'url':get_url('project','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						var tip = $("#id").val() ? p_lang('專案資訊編輯成功') : p_lang('專案資訊建立成功');
						$.dialog.tips(tip,function(){
							$.admin.reload(get_url('project'));
							$.admin.close();
						}).lock();
						return false;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},

		/**
		 * 模組選擇時執行觸發
		**/
		module_change:function(obj)
		{
			$("#module_set,#module_set2").hide();
			var val = $(obj).val();
			var mtype = $(obj).find('option:selected').attr('data-mtype');
			if(!val || val == '0'){
				return true;
			}
			$("#tmp_orderby_btn,#tmp_orderby_btn2").html('');
			//清除欄位
			//$("#tmp_fields_btn").html('');
			var c = '';
			var f = '';
			if(mtype == 1){
				c += '<input type="button" value="ID" onclick="phpok_admin_orderby(\'orderby2\',\'id\')" class="layui-btn layui-btn-sm" />';
			}
			$.phpok.json(get_url('project','mfields','id='+val),function(rs){
				if(!rs.status){
					$.dialog.alert(rs.info);
					return false;
				}
				if(rs.info){
					var list = rs.info;
					for(var i in list){
						if(list[i].type == 'varchar'){
							if(mtype == 1){
								c += '<input type="button" value="'+list[i].title+'" onclick="phpok_admin_orderby(\'orderby2\',\''+list[i].identifier+'\')" class="layui-btn layui-btn-sm"/>';
							}else{
								c += '<input type="button" value="'+list[i].title+'" onclick="phpok_admin_orderby(\'orderby\',\'ext.'+list[i].identifier+'\')" class="layui-btn layui-btn-sm"/>';
							}
						}
						f += '<input type="button" value="'+list[i].title+'" onclick="$.admin_project.fields_add(\''+list[i].identifier+'\')" class="layui-btn layui-btn-sm"/>';
					}
				}
				if(f && f != ''){
					f += '<input type="button" value="'+p_lang('全部')+'" class="layui-btn layui-btn-sm layui-btn-normal" onclick="$.admin_project.fields_add(\'*\')" />';
					f += '<input type="button" value="'+p_lang('不讀擴充套件')+'" class="layui-btn layui-btn-sm layui-btn-danger" onclick="$.admin_project.fields_add(\'id\')" />';
					$("#tmp_fields_btn").html(f).show();
				}
				if(mtype == 1){
					$("#tmp_orderby_btn2").html(c);
					$("#module_set2").show();
				}else{
					$("#tmp_orderby_btn").html(c);
					$("#module_set").show();
				}
				return true;
			});
		},
		fields_add:function(val)
		{
			if(val == '*' || val == 'id'){
				$("#list_fields").val(val);
				return true;
			}
			var tmp = $("#list_fields").val();
			if(tmp == '*' || tmp == 'id'){
				$("#list_fields").val(val);
				return true;
			}
			var n = tmp;
			if(tmp){
				n += ',';
			}
			n += val;
			$("#list_fields").val(n);
			return true;
		},
		del:function(id)
		{
			var tip = p_lang('確定要刪除此專案嗎？刪除會將相關內容一起刪除 #{id}','<span class="red">'+id);
			$.dialog.confirm(tip,function(){
				var url = get_url('project','delete','id='+id);
				var tips = $.dialog.tips(p_lang('正在執行刪除請求…'));
				$.phpok.json(url,function(data){
					tips.close();
					if(data.status){
						$("#project_"+id).remove();
						$.dialog.tips(p_lang('模組刪除成功'));
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				})
			});
		},
		copy:function()
		{
			var id = $.checkbox.join();
			if(!id){
				$.dialog.alert(p_lang('未選擇要複製的專案'));
				return false;
			}
			var list = id.split(',');
			if(list.length > 1 ){
				$.dialog.alert(p_lang('複製操作只能選擇一個'));
				return false;
			}
			$.dialog.confirm(p_lang('確定要複製此專案 #{id}','<span class="red">'+id+'</id>'),function(){
				var url = get_url('project','copy','id='+id);
				$.phpok.json(url,function(data){
					if(data.status){
						$.dialog.tips(p_lang('專案複製成功'),function(){
							$.phpok.reload();
						})
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		},
		extinfo:function()
		{
			var id = $.checkbox.join();
			if(!id){
				$.dialog.alert(p_lang('未選擇要自定義擴充套件欄位的專案'));
				return false;
			}
			var list = id.split(',');
			if(list.length > 1 ){
				$.dialog.alert(p_lang('自定義擴充套件欄位操作只能選擇一個'));
				return false;
			}
			$.win(p_lang('擴充套件欄位')+"_"+$("#id_"+id).attr("data-title"),get_url('project','content','id='+id));
			return true;
		},
		extinfo_save:function(id)
		{
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			$("#"+id).ajaxSubmit({
				'url':get_url('project','content_save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('資料儲存成功'),function(){
							$.admin.reload(get_url('project'));
							$.admin.close();
						}).lock();
						return false;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		export:function()
		{
			var id = $.checkbox.join();
			if(!id){
				$.dialog.alert(p_lang('未選擇要自定義擴充套件欄位的專案'));
				return false;
			}
			var list = id.split(',');
			if(list.length > 1 ){
				$.dialog.alert(p_lang('自定義擴充套件欄位操作只能選擇一個'));
				return false;
			}
			$.phpok.go(get_url('project','export','id='+id));
			return true;
		},
		import_xml:function()
		{
			var url = get_url('project','import');
			$.dialog.open(url,{
				'title':p_lang('專案匯入'),
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
				'okVal':p_lang('匯入專案'),
				'cancelVal':p_lang('取消'),
				'cancel':true
			});
		},
		set_hidden:function(hidden)
		{
			var id = $.checkbox.join();
			if(!id){
				var tip = hidden == 1 ? p_lang('未選擇要隱藏的專案') : p_lang('未選擇要顯示的專案');
				$.dialog.alert(tip);
				return false;
			}
			var tip = hidden == 1 ? p_lang('指定專案已經設為隱藏') : p_lang('指定專案已經設為顯示');
			var url = get_url('project','hidden','id='+$.str.encode(id)+"&hidden="+hidden);
			$.phpok.json(url,function(data){
				if(data.status){
					$.dialog.tips(tip,function(){
						$.phpok.reload();
					});
					return true;
				}
				$.dialog.alert(data.info);
				return false;
			})
		},
		set_lock:function(status)
		{
			var id = $.checkbox.join();
			if(!id){
				var tip = status == 1 ? p_lang('未選擇要啟用的專案') : p_lang('未選擇要禁用的專案');
				$.dialog.alert(tip);
				return false;
			}
			var tip = status == 1 ? p_lang('指定專案已經設為啟用') : p_lang('指定專案已經設為禁用');
			var url = get_url('project','status','id='+$.str.encode(id)+"&status="+status);
			$.phpok.json(url,function(data){
				if(data.status){
					$.dialog.tips(tip,function(){
						$.phpok.reload();
					});
					return true;
				}
				$.dialog.alert(data.info);
				return false;
			})
		},
		set_status:function(id)
		{
			var url = get_url('project','status','id='+id);
			var old_value = $("#status_"+id).attr("value");
			var new_value = old_value == "1" ? "0" : "1";
			url += "&status="+new_value;
			$.phpok.json(url,function(rs){
				if(rs.status){
					$("#status_"+id).removeClass("status"+old_value).addClass("status"+new_value).attr("value",new_value);
					return true;
				}
				$.dialog.alert(rs.info);
			});
		},
		sort:function(val,id)
		{
			var url = get_url('project','sort','sort['+id+']='+val);
			$.phpok.json(url,function(data){
				if(data.status){
					$("div[name=taxis][data="+id+"]").text(val);
					$.dialog.tips(p_lang('排序編輯成功，您可以手動重新整理看新的排序效果'));
					return true;
				}
				$.dialog.alert(data.info);
				return false;
			})
		},
		ext_help:function()
		{
			top.$.dialog({
				'title':p_lang('擴充套件項幫助說明'),
				'content':document.getElementById('ext_help'),
				'lock':true,
				'width':'700px',
				'height':'500px',
				'padding':'0 10px'
			})
		},
		icolist:function()
		{
			$.dialog.open(get_url('project','icolist'),{
				'title':p_lang('選擇圖示'),
				'lock':true,
				'width':'700px',
				'height':'60%',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':'提交',
				'cancel':true
			})
		}
	};

	$(document).ready(function(){
		if($("#_quick_insert").length>0){
			var module = $("#_quick_insert").attr("data-module");
			var url = get_url('ext','select','type=project&module='+$.str.encode(module));
			$.phpok.ajax(url,function(rs){
				$("#_quick_insert").html(rs);
				layui.form.render();
			});
		}
	});
})(jQuery);

