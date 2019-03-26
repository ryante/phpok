/**
 * 運費
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年11月27日
**/
;(function($){
	$.admin_freight = {
		update:function(id)
		{
			var url = get_url('freight','save','id='+id);
			var title = $("#title_"+id).val();
			if(!title){
				$.dialog.alert(p_lang('名稱不能為空'));
				return false
			}
			url += "&title="+$.str.encode(title);
			var type = $("#type_"+id).val();
			if(type){
				url += "&type="+type;
			}
			var currency_id = $("#currency_"+id).val();
			if(currency_id){
				url += "&currency_id="+currency_id;
			}
			var taxis = $("#taxis_"+id).val();
			if(taxis){
				url += "&taxis="+taxis;
			}
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('運費模板修改成功')).lock();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		add:function()
		{
			var url = get_url('freight','save');
			var title = $("#title_0").val();
			if(!title){
				$.dialog.alert('名稱不能為空');
				return false
			}
			url += "&title="+$.str.encode(title);
			var type = $("#type_0").val();
			if(type){
				url += "&type="+type;
			}
			var currency_id = $("#currency_0").val();
			if(currency_id){
				url += "&currency_id="+currency_id;
			}
			var taxis = $("#taxis_0").val();
			if(taxis){
				url += "&taxis="+taxis;
			}
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('運費模板修改成功'),function(){
						$.phpok.reload();
					}).lock();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		del:function(id)
		{
			var t = $("#title_"+id).val();
			var tip = p_lang('確定要刪除該區域：{title} 嗎？刪除後，已使用此模板相關資訊也會刪除','<span class="red">'+t+'</span>');
			$.dialog.confirm(tip,function(){
				$.phpok.json(get_url('freight','delete','id='+id),function(rs){
					if(rs.status){
						$.dialog.alert(p_lang('刪除成功'),function(){
							$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		},
		price:function(id)
		{
			$.dialog.open(get_url('freight','price','fid='+id),{
				'title':p_lang('運費價格')+" #"+id,
				'width':'90%',
				'height':'80%',
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
				'okVal':p_lang('儲存運費資訊'),
				'cancel':true,
				'cancelVal':p_lang('取消')
			});
		},
		zone_add:function(fid)
		{
			var url = get_url('freight','zone_setting','fid='+fid);
			$.dialog.open(url,{
				'title':p_lang('新增新區域'),
				'lock':true,
				'width':'700px',
				'height':'500px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('儲存新增'),
				'cancel':true,
				'cancelVal':p_lang('取消')
			});
		},
		zone_edit:function(id)
		{
			var url = get_url('freight','zone_setting','id='+id);
			$.dialog.open(url,{
				'title':p_lang('編輯區域')+"_#"+id,
				'lock':true,
				'width':'700px',
				'height':'500px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('儲存修改'),
				'cancel':true,
				'cancelVal':p_lang('取消')
			});
		},
		zone_del:function(id)
		{
			$.dialog.confirm(p_lang('確定要刪除這塊區域配置嗎'),function(){
				$.phpok.json(get_url('freight','zone_delete','id='+id),function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('刪除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return true;
				});
			});
		},
		zone_taxis:function(id,val)
		{
			var url = get_url('freight','zone_sort','id='+id+"&val="+val);
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('排序變更成功'),function(){
						$.phpok.reload();
					}).lock();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			})
		}
	}
})(jQuery);