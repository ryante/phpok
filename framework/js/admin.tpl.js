/**
 * 風格管理
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年06月29日
**/
;(function($){
	$.admin_tpl = {
		rename:function(id,folder,title,notice)
		{
			layer.prompt(notice,function(val){
				if(!val || val == undefined){
					val = title;
				}
				if(val == title){
					layer.alert("新舊名稱一樣");
					return false;
				}
				var url = get_url("tpl","rename","id="+id+"&folder="+$.str.encode(folder)+"&old="+$.str.encode(title)+"&title="+$.str.encode(val));
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					layer.alert(rs.info);
					return false;
				})
			},title);
		},
		
		del:function(id,folder,title)
		{
			layer.confirm(p_lang('確定要刪除檔案（夾）{title}嗎？<br>刪除後是不能恢復的！','<span class="red">'+title+'</span> '),function(){
				if(!title){
					layer.alert("操作異常！");
					return false;
				}
				var url = get_url("tpl","delfile","id="+id+"&folder="+$.str.encode(folder)+"&title="+$.str.encode(title));
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					layer.alert(rs.info);
					return false;
				})
			});
		},
		
		download:function(id,folder,title)
		{
			var url_ext = "id="+id+"&folder="+$.str.encode(folder)+"&title="+$.str.encode(title);
			var url = get_url("tpl","download",url_ext);
			$.phpok.go(url);
		},
		
		folder_rename:function(id,folder,title)
		{
			var notice = p_lang('將資料夾{title}改名為：（僅支援字母、數字、下劃線）',' <span class="red">'+title+'</span> ');
			this.rename(id,folder,title,notice);
		},
		
		file_rename:function(id,folder,title)
		{
			var notice = p_lang('將檔案{title}改名為：<br><span class="red">僅支援字母、數字、下劃線和點，注意副檔名必須填寫</span>',' <span class="red">'+title+'</span> ');
			this.rename(id,folder,title,notice);
		},
		
		add_folder:function(id,folder)
		{
			layer.prompt(p_lang('請填寫要建立的資料夾名稱，<span class="red">僅支援數字，字母及下劃線</span>：'),function(val){
				if(!val || val == "undefined"){
					layer.alert("資料夾名稱不能為空");
					return false;
				}
				var url_ext = "id="+id+"&folder="+$.str.encode(folder)+"&type=folder&title="+$.str.encode(val);
				var url = get_url("tpl","create",url_ext);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					layer.alert(rs.info);
					return false;
				});
			});
		},
		
		add_file:function(id,folder,ext)
		{
			if(!ext || ext == 'undefined'){
				ext = 'html';
			}
			var tip = p_lang('填寫要建立的檔名，<span class="red">僅持數字，字母，下劃線及點</span>：');
			layer.prompt(tip,function(val){
				if(!val || val == "undefined"){
					layer.alert("檔名稱不能為空");
					return false;
				}
				var extlen = -(ext.length + 1);
				var val_t = val.substr(extlen);
				if(val_t != '.'+ext){
					val += '.'+ext;
				}
				var url_ext = "id="+id+"&folder="+$.str.encode(folder)+"&type=file&title="+$.str.encode(val);
				var url = get_url("tpl","create",url_ext);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.phpok.reload();
						return true;
					}
					layer.alert(rs.info);
					return false;
				});
			});
		},
		
		view:function(url)
		{
			var html = '<img src="'+url+'" border="0" />';
			layer.through({
				title: p_lang('預覽圖片'),
				lock: true,
				content:html,
				width: '400px',
				height: '300px',
				resize: true
			});
		},
		
		edit:function(id,folder,title)
		{
			var url = get_url('tpl','edit','id='+id+"&folder="+$.str.encode(folder)+"&title="+$.str.encode(title));
			$.win(p_lang('編輯')+"_"+title,url);
			
			/*var url_ext = "id="+id+"&folder="+$.str.encode(folder)+"&title="+$.str.encode(title);
			var title = p_lang('編輯檔案：{title} 線上編輯請確保檔案有寫入許可權','<span class="red">'+title+'</span>');
			$.dialog.open(get_url("tpl","edit",url_ext),{
				'width':'1000px',
				'height':'700px',
				'lock':true,
				'title':title,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('儲存程式碼'),
				'cancel':true,
				'cancelVal':p_lang('取消並關閉視窗')
			});*/
		},
		
		open_select:function(id,val)
		{
			var url = get_url('tpl','open','tpl_id='+val+"&id="+id);
			$.phpok.go(url);
		},
		
		phpok_input:function(val,id)
		{
			var obj = $.dialog.opener;
			obj.$("#"+id).val(val);
			$.dialog.close();
		},
		
		tpl_delete:function(id,title)
		{
			var tip = p_lang('確定要刪除{title}嗎？<br>刪除後請手動刪除相應檔案目錄',' <span class="red b">'+title+'</span> ');
			layer.confirm(tip,function(){
				var url = get_url("tpl","delete","id="+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('風格刪除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					layer.alert(rs.content);
					return false;
				});
			});
		},
		tpl_set:function(id)
		{
			$.win(p_lang('風格編輯 #'+id),get_url('tpl','set','id='+id));
		},
		
		tpl_filelist:function(id)
		{
			let url = get_url('tpl','list','id='+id);
			$.win(p_lang('檔案管理 #'+id),get_url('tpl','list','id='+id));
		},
		
		set_folder:function(val)
		{
			var str = $("#folder_change").val();
			if(!str || str == "undefined"){
				$("#folder_change").val(val);
				return true;
			}
			if(str == val){
				$("#folder_change").val("");
				return true;
			}
			var list = str.split(",");
			if($.inArray(val,list) > 0){
				var nlist = new Array();
				var m = 0;
				for(var i in list){
					if(list[i] != val){
						nlist[m] = list[i];
						m++;
					}
				}
				str = nlist.join(",");
				$("#folder_change").val(str);
				return true;
			}
			str += ","+val;
			$("#folder_change").val(str);
			return true;
		},
		save:function()
		{
			var title = $("#title").val();
			if(!title){
				$.dialog.alert(p_lang('名稱不能為空'));
				return false;
			}
			var folder = $("#folder").val();
			if(!folder){
				$.dialog.alert(p_lang('資料夾不能為空'));
				return false;
			}
			var ext = $("#ext").val();
			if(!ext){
				$.dialog.alert(p_lang('字尾不允許為空'));
				return false;
			}
			$("#post_save").ajaxSubmit({
				'url':get_url('tpl','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.alert('操作成功',function(){
							$.admin.reload(get_url('tpl'));
							$.admin.close(get_url('tpl'));
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