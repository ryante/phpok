/**
 * 內容管理
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2018年01月16日
**/
var autosave_handle;

;(function($){
	$.admin_list_edit = {

		/**
		 * 自動儲存
		**/
		autosave:function()
		{
			window.clearTimeout(autosave_handle);
			$("#_listedit").ajaxSubmit({
				'url':get_url('auto','list'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips('資料已臨時儲存').position('50%','1px');
						// 每隔 5 分鐘自動儲存一次資料
						autosave_handle = window.setTimeout(function(){
							$.admin_list_edit.autosave();
						}, 300000);
					}
				}
			});
			return false;
		},

		/**
		 * 儲存資料
		**/
		save:function()
		{
			var loading_action;
			var id = $("#id").val();
			var pcate = $("#_root_cate").val();
			var pcate_multiple = $("#_root_cate_multiple").val();
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			$("#_listedit").ajaxSubmit({
				'url':get_url('list','ok'),
				'type':'post',
				'dataType':'json',
				'beforeSubmit':function(){
					loading_action = $.dialog.tips('<img src="images/loading.gif" border="0" align="absmiddle" /> '+p_lang('正在儲存資料，請稍候…')).time(30).lock();
				},
				'success':function(rs){
					if(loading_action){
						loading_action.close();
					}
					if(rs.status == 'ok'){
						var url = get_url('list','action','id='+$("#pid").val());
						if(pcate>0){
							var cateid = $("#cate_id").val();
							url += "&keywords[cateid]="+cateid;
						}
						if(id){
							$.dialog.alert(p_lang('內容資訊修改成功'),function(){
								$.phpok.message('pendding');
								$.admin.reload(url);
								$.admin.close(url);
							},'succeed');
							return true;
						}
						$.dialog.through({
							'icon':'succeed',
							'content':p_lang('內容新增操作成功，請選擇繼續新增或返回列表'),
							'ok':function(){
								$.phpok.message('pendding');
								$.admin.reload(url);
								$.phpok.reload();
							},
							'okVal':p_lang('繼續新增'),
							'cancel':function(){
								$.phpok.message('pendding');
								$.admin.reload(url);
								$.admin.close(url);
							},
							'cancelVal':p_lang('關閉視窗'),
							'lock':true
						});
						return true;

					}
					$.dialog.alert(rs.content);
					return true;
				}
			});
			return false;
		},

		/**
		 * 新增屬性
		**/
		attr_create:function()
		{
			var self = this;
			$.dialog.prompt(p_lang('請新增屬性名稱，注意，新增前請先檢查之前的屬性是否存在'),function(name){
				if(!name){
					$.dialog.alert(p_lang('名稱不能為空'));
				}
				var url = get_url('options','save','title='+$.str.encode(name));
				$.phpok.json(url,function(data){
					if(data.status == 'ok'){
						self.attrlist_load();
						self.attr_add(data.content);
						return true;
					}
					$.dialog.alert(data.content);
					return false;
				})
			});
		},

		/**
		 * 選擇新增屬性
		**/
		attr_add:function(val)
		{
			if(!val){
				return false;
			}
			var old = $("#_biz_attr").val();
			if(old){
				if(old == val){
					$.dialog.alert(p_lang('屬性已經使用，不能重複'));
					return false;
				}
				var list = old.split(",");
				var is_used = false;
				for(var i in list){
					if(list[i] == val){
						is_used = true;
						break;
					}
				}
				if(is_used){
					$.dialog.alert(p_lang('屬性已經使用，不能重複'));
					return false;
				}
				var ncontent = old+","+val;
				//寫入新值
				$("#_biz_attr").val(ncontent);
				//建立HTML
				var html = '<li id="_biz_attr_'+val+'"><li>';
				$("#biz_attr_options").append(html);
				//非同步載入HTML
			}else{
				$("#_biz_attr").val(val);
				var html = '<li id="_biz_attr_'+val+'"><li>';
				$("#biz_attr_options").html(html);
			}
			this.attr_info_product(val);
		},

		/**
		 * 刪除屬性
		 * @引數 id
		**/
		attr_remove:function(val)
		{
			if(!val){
				return false;
			}
			var old = $("#_biz_attr").val();
			if(!old || old == 'undefined' || old == '0'){
				return false;
			}
			if(old == val){
				$("#_biz_attr").val('');
				$("#biz_attr_options").html('');
				return false;
			}
			var list = old.split(",");
			var nlist = new Array();
			var m = 0;
			for(var i in list){
				if(list[i] != val){
					nlist[m] = list[i];
					m++;
				}
			}
			var ncontent = nlist.join(",");
			$("#_biz_attr").val(ncontent);
			//刪除HTML
			var html = '<li id="_biz_attr_'+val+'"><li>';
			$("#_biz_attr_"+val).remove();
		},

		/**
		 * 非同步載入屬性
		**/
		attr_load:function()
		{
			var bizinfo = $("#_biz_attr").val();
			if(bizinfo && bizinfo != 'undefined' && bizinfo != '0'){
				var list = bizinfo.split(",");
				var html = '';
				for(var i in list){
					html += '<li id="_biz_attr_'+list[i]+'"><li>';
				}
				$("#biz_attr_options").html(html);
				for(var i in list){
					this.attr_info_product(list[i]);
				}
			}
		},

		/**
		 * 讀取屬性及內容資訊
		 * @引數 id 屬性ID
		**/
		attr_info_product:function(id)
		{
			//執行屬性新增
			var url = get_url('list','attr','aid='+id);
			var tid = $("#id").val();
			if(tid){
				url += "&tid="+tid;
			}
			$.phpok.json(url,function(data){
				if(data.status){
					$("#_biz_attr_"+id).html(data.info);
					layui.form.render();
					return true;
				}
				$.dialog.alert(data.info);
				return false;
			});
		},

		/**
		 * 選擇全部屬性
		**/
		attrlist_load:function()
		{
			var url = get_url('options','all');
			$.phpok.json(url,function(data){
				if(data.status != 'ok'){
					var html = '<option value="">'+data.content+'</option>';
					$("#biz_attr_id").html(html);
					return false;
				}
				var html = '<option value="">'+p_lang('請選擇一個屬性…')+'</option>';
				for(var i in data.content){
					html += '<option value="'+data.content[i].id+'">'+data.content[i].title+'</option>';
				}
				$("#biz_attr_id").html(html);
				return true;
			});
			return false;
		},

		attr_option_delete:function(id,val)
		{
			var name = $("#attr_"+id+"_"+val).attr("data-name");
			$("#attr_"+id+"_opt").append('<option value="'+val+'">'+name+'</option>');
			$("#attr_"+id+"_"+val).remove();
		},

		/**
		 * 屬性快速新增
		 * @引數 id 屬性ID
		 * @引數 val 要寫入的值
		**/
		attr_option_quickadd:function(id,val)
		{
			if(!id || !val || val == 'undefined' || val == '0' || val == ''){
				return false;
			}
			var text = $("#attr_"+id+"_opt").find("option:selected").text();
			$("#attr_"+id+"_opt option[value="+val+"]").remove();
			this.attr_option_html(id,val,text);
		},

		/**
		 * 輸出HTML
		 * @引數 id 屬性ID
		 * @引數 val 引數ID
		 * @引數 text 顯示名稱
		**/
		attr_option_html:function(id,val,text)
		{
			var count = $("tr[name=attr_"+id+"]").length;
			var taxis = count > 0 ? parseInt(count+1) * 5 : 5;
			var html = '<tr name="attr_'+id+'" id="attr_'+id+'_'+val+'" data-name="'+text+'">';
			html += '<td class="center"><input type="hidden" name="_attr_'+id+'[]" value="'+val+'" />'+text+'</td>';
			html += '<td class="center"><input type="text" name="_attr_weight_'+id+'['+val+']" value="0" class="layui-input" /></td>';
			html += '<td class="center"><input type="text" name="_attr_volume_'+id+'['+val+']" value="0" class="layui-input" /></td>';
			html += '<td class="center"><input type="text" name="_attr_price_'+id+'['+val+']" value="" class="layui-input" /></td>';
			html += '<td class="center"><input type="text" name="_attr_taxis_'+id+'['+val+']" value="'+taxis+'" class="layui-input" /></td>'
			html += '<td class="center"><input type="button" value="'+p_lang('刪除')+'" onclick="$.admin_list_edit.attr_option_delete(\''+id+'\',\''+val+'\')" class="layui-btn layui-btn-sm" /></td>';
			html += '</tr>';
			if($("tr[name=attr_"+id+"]").length > 0){
				$("tr[name=attr_"+id+"]").last().after(html);
			}else{
				$("tr[name=attr_"+id+"_thead]").after(html);
			}
			return true;
		},

		/**
		 * 手動新增值資訊
		 * @引數 id 屬性ID
		**/
		attr_option_add:function(id)
		{
			var self = this;
			$.dialog.prompt(p_lang('請建立一個新值'),function(name){
				if(!name){
					$.dialog.alert(p_lang('新值不能為空'));
					return false;
				}
				var url = get_url('options','save_values','aid='+id+'&title='+$.str.encode(name));
				$.phpok.json(url,function(data){
					if(data.status == 'ok'){
						self.attr_option_html(id,data.content,name);
						return true;
					}
					$.dialog.alert(data.content);
					return false;
				})
			});
		}
	}

})(jQuery);
$(document).keypress(function(e){
	//按鈕CTRL+回車鍵執行儲存
	if(e.ctrlKey && e.which == 13 || e.which == 10) {
		$('.phpok_submit_click').click();
	}

});
$(document).ready(function(){


	//僅在新增主題時執行自動儲存操作
	/*var id = $("#id").val();
	if(!id || id == '0' || id == 'undefined'){
		autosave_handle = window.setTimeout(function(){
			$.admin_list_edit.autosave();
		}, 60000);
	}*/


	//載入產品屬性
	if($("#_biz_attr").length > 0){
		$.admin_list_edit.attr_load();
	}


});