/**************************************************************************************************
	檔案： js/webuploader/admin.upload.js
	說明： 後臺附件型別上傳操作類，僅限後臺操作
	版本： 4.0
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	日期： 2015年04月27日 12時36分
***************************************************************************************************/
;(function($){
	$.www_upload = function(options){
		var self = this;
		var defaults = {
			'id':'upload',
			'swf':'js/webuploader/uploader.swf',
			'server':'index.php',
			'pick':'#picker',
			'resize': false,
			'multiple':false,
			'disableGlobalDnd':true,
			'fileVal':'upfile',
			'sendAsBinary':false,
			'duplicate':false,
			'chunked':true,
			'chunkSize':102400,
			'threads':3,
			'auto':false,
			'accept':{'title':'圖片(*.jpg, *.gif, *.png)','extensions':'jpg,png,gif'}
		};
		var opts = $.extend({},defaults,options);
		if(!opts.pick.innerHTML){
			opts.pick.innerHTML = p_lang('選擇本地檔案');
		}
		this.id = "#"+opts.id;
		uploader = WebUploader.create(opts);
		uploader.onBeforeFileQueued = function(file){
			var extlist = (opts.accept.extensions).split(",");
			if($.inArray((file.ext).toLowerCase(),extlist) < 0){
				$.dialog.alert('附件型別不支援 <span class="red">'+file.ext+'</span> 格式');
				return false;
			}
		}
		this.uploader = uploader;
		this.upload_state = 'ready';
		this.option = function(k,val){
			self.uploader.option(k,val);
		}
		uploader.onFileQueued = function( file ) {
            $(self.id+"_progress").append('<div id="phpok-upfile-' + file.id + '" class="phpok-upfile-list">' +
				'<div class="title">' + file.name + ' <span class="status">等待上傳…</span></div>' +
				'<div class="progress"><span>&nbsp;</span></div>' +
				'<div class="cancel" id="phpok-upfile-cancel-'+file.id+'"></div>' +
			'</div>' );
			$("#phpok-upfile-"+file.id+" .cancel").click(function(){
				uploader.removeFile(file,true);
				$("#phpok-upfile-"+file.id).remove();
			});
        }
        uploader.onUploadProgress = function(file,percent){
	        var $li = $('#phpok-upfile-'+file.id),
			$percent = $li.find('.progress span');
			var width = $li.find('.progress').width();
			$percent.css( 'width', parseInt(width * percent, 10) + 'px' );
			$li.find('span.status').html('正在上傳…');
			self.upload_state = 'running';
        }
        uploader.onUploadSuccess = function(file,data){
	        //$("input[type=file]").val('');
			if(!data.status && data._raw){
				var lst = data._raw.split('{"status"');
				var info = lst[0];
				var html = lst[1];
				if(info.indexOf('$HTTP_RAW_POST_DATA') > -1){
					//$.dialog.tips('建議更新您的PHP.INI環境，設定：always_populate_raw_post_data = -1');
					data = $.parseJSON('{"status"'+html);
				}else{
					$.dialog.alert('上傳異常，錯誤提示，系統未配置好上傳環境');
					return false;
				}
			}
			if(data.status != 'ok'){
				$.dialog.alert('上傳異常，錯誤提示：'+data.content);
				return false;
			}
			//執行自定義的方法
			if(opts.success && opts.success != 'undefined'){
				(opts.success)(file,data);
				return true;
			}
			$('#phpok-upfile-'+file.id).find('span.status').html('上傳成功');
			var tmp = $.dialog.data('upload-'+opts.id);
			if(opts.multiple == 'true'){
				var val = $(self.id).val();
				if(val){
					val += ","+data.content.id;
				}else{
					val = data.content.id;
				}
				$(self.id).val(val);
				tmp = tmp ? tmp+","+data.content.id : data.content.id;
			}else{
				if(tmp){
					self.remote_delete(tmp);
				}
				tmp = data.content.id;
				$(self.id).val(data.content.id);
			}
			$.dialog.data('upload-'+opts.id,tmp);
			self.showhtml();
        }
		uploader.on('uploadError',function(file,reason){
			$('#phpok-upfile-'+file.id).find('span.status').html('上傳錯誤：<span style="color:red">'+reason+'</span> ');
		});
		uploader.on('uploadFinished',function(){
			self.upload_state = 'ready';
		});
		//上傳完成，無論失敗與否，3秒後刪除
		uploader.on('uploadComplete',function(file){
			$("#phpok-upfile-"+file.id).fadeOut();
		});
		uploader.on('error',function(handle){
			var tip = '';
			if(handle == 'Q_EXCEED_NUM_LIMIT'){
				tip = '要新增的檔案數量超出系統限制';
			}
			if(handle == 'Q_EXCEED_SIZE_LIMIT'){
				tip = '要新增的檔案總大小超出系統限制';
			}
			if(handle == 'Q_TYPE_DENIED'){
				tip = '檔案型別不符合要求';
			}
			if(handle == 'F_DUPLICATE'){
				tip = '檔案重複';
			}
			if(handle =='F_EXCEED_SIZE'){
				tip = '上傳檔案超過系統限制';
			}
			if(!tip){
				tip = handle;
			}
			$.dialog.alert('<span style="color:red">'+tip+'</span>');
			return false;
		});
		this.showhtml = function(){
			var id = $(this.id).val();
			if(!id){
				$(self.id).val('');
				$(self.id+"_list").html('').fadeOut();
				return false;
			}
			var url = get_url('upload','thumbshow','id='+$.str.encode(id));
			$.phpok.json(url,function(rs){
				if(rs.status != 'ok'){
					$(self.id).val('');
					$(self.id+"_list").html('').fadeOut();
					return true;
				}
				var html = '';
				var index_i = 1;
				for(var i in rs.content){
					html += self.html(rs.content[i],index_i);
					index_i++;
				}
				$(self.id+"_list").html(html).show();
				if(!html){
					$(self.id+"_list").html('').fadeOut();
				}
				return true;
			});
		};
		this.html = function(rs,i){
			var html = '<div class="'+opts.id+'_thumb" name="_elist">';
			html += '<div style="text-align:center;"><img src="'+rs.ico+'" width="100" height="100" /></div>';
			html += '<div class="file-action" style="text-align:center;"><div class="button-group">';
			html += '	<input type="button" value="預覽" class="phpok-btn" onclick="obj_'+opts.id+'.preview(\''+rs.id+'\')" />';
			html += '	<input type="button" value="刪除" class="phpok-btn" onclick="obj_'+opts.id+'.del(\''+rs.id+'\')" /></div>';
			html += '</div></div>';
			html += '</div>';
			return html;
		};
		this.update = function(id){
			$.dialog.open(get_url('upload','editopen','id='+id),{
				'title':'編輯附件資訊',
				'width':'700px',
				'height':'400px',
				'lock':true,
				'okVal':'提交',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'cancelVal':'取消修改',
				'cancel':function(){}
			});
		};
		this.preview = function(id){
			$.dialog.open(get_url('upload','preview','id='+id),{
				'title':'預覽附件資訊',
				'width':'700px',
				'height':'400px',
				'lock':true,
				'okVal':'關閉',
				'ok':function(){}
			});
		};
		this.del = function(id){
			var content = $(self.id).val();
			if(!content || content == "undefined"){
				return true;
			}
			//刪除單個附件
			if(content == id){
				$(self.id).val("");
				$(self.id+"_list").fadeOut(function(){
					$(this).html('');
				});
				//遠端刪除資料
				self.remote_delete(id);
				return true;
			}
			var list = content.split(",");
			var newlist = new Array();
			var new_i = 0;
			for(var i=0;i<list.length;i++){
				if(list[i] != id){
					newlist[new_i] = list[i];
					new_i++;
				}
			}
			content = newlist.join(",");
			$(self.id).val(content);
			//遠端刪除資料
			self.remote_delete(id);
			self.showhtml();
		};
		this.remote_delete = function(id){
			var tmp = $.dialog.data('upload-'+opts.id);
			if(!tmp || tmp == 'undefined'){
				return true;
			}
			var delete_status = false;
			if(tmp != id){
				var list = tmp.split(',');
				var newlist = new Array();
				var new_i = 0;
				for(var i=0;i<list.length;i++){
					if(list[i] != id){
						newlist[new_i] = list[i];
						new_i++;
					}else{
						delete_status = true;
					}
				}
				content = newlist.join(",");
				$.dialog.data('upload-'+opts.id,content);
			} else {
				delete_status = true;
				$.dialog.data('upload-'+opts.id,'');
			}
			if(delete_status){
				var url = get_url('upload','delete','id='+id);
				$.phpok.json(url,function(){
					return true;
				});
			}
		};
		//排序
		this.sort = function(type){
			var t = [];
			$("#"+opts.id+"_list .taxis").each(function(i){
				if(type == 'title'){
					var val = $(this).attr('title');
				}else{
					var val = $(this).val();
				}
				var data = $(this).attr("data");
				t.push({"id":val,"data":data});
			});
			t = t.sort(function(a,b){return parseInt(a['id'])>parseInt(b['id']) ? 1 : -1});
			var list = new Array();
			for(var i in t){
				list[i] = t[i]['data'];
			}
			var val = list.join(",");
			$(this.id).val(val);
			this.showhtml();
		};
		$(this.id+"_submit").click(function(){
			if($(this).hasClass('disabled')){
				$.dialog.alert('正在上傳中，已鎖定');
				return false;
			}
			var f = $(self.id+"_progress .phpok-upfile-list").length;
			if(f<1){
				$.dialog.alert('請選擇要上傳的檔案');
				return false;
			}
			if(self.upload_state == 'ready' || self.upload_state == 'paused'){
				self.uploader.upload();
			}else{
				self.uploader.stop();
			}
		});
	};
})(jQuery);
