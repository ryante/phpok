/**
 * 商品屬性
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年10月14日
**/
;(function($){
	$.admin_options = {
		add:function()
		{
			var url = get_url('options','save');
			var title = $("#title_0").val();
			if(!title){
				$.dialog.alert(p_lang('名稱不能為空'));
				return false
			}
			url += "&title="+$.str.encode(title);
			var taxis = $("#taxis_0").val();
			if(taxis){
				url += "&taxis="+taxis;
			}
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('新增成功'),function(){
						$.phpok.reload();
					}).lock();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		update:function(id)
		{
			var url = get_url('options','save','id='+id);
			var title = $("#title_"+id).val();
			if(!title){
				$.dialog.alert(p_lang('名稱不能為空'));
				return false
			}
			url += "&title="+$.str.encode(title);
			var taxis = $("#taxis_"+id).val();
			if(taxis){
				url += "&taxis="+taxis;
			}
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('編輯成功'));
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		del:function(id)
		{
			var t = $("#title_"+id).val();
			var tip = p_lang('確定要刪除產品屬性 {title} 嗎？刪除後，產品已使用此屬性相關資訊也會刪除','<span class="red">'+t+'</span>');
			$.dialog.confirm(tip,function(){
				$.phpok.json(get_url('options','delete','id='+id),function(data){
					if(data.status){
						$.dialog.tips(p_lang('刪除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		},
		info_add:function(aid)
		{
			var url = get_url('options','save_values','aid='+aid);
			var title = $("#title_0").val();
			if(!title){
				$.dialog.alert(p_lang('名稱不能為空'));
				return false
			}
			url += "&title="+$.str.encode(title);
			var taxis = $("#taxis_0").val();
			if(taxis){
				url += "&taxis="+taxis;
			}
			var pic = $("#pic_0").val();
			if(pic){
				url += "&pic="+pic;
			}
			var val = $("#val_0").val();
			if(val){
				url += "&val="+val;
			}
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('新增成功'),function(){
						$.phpok.reload();
					}).lock();
					return true;					
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		info_update:function(id)
		{
			var url = get_url('options','save_values','id='+id);
			var title = $("#title_"+id).val();
			if(!title){
				$.dialog.alert(p_lang('名稱不能為空'));
				return false
			}
			url += "&title="+$.str.encode(title);
			var taxis = $("#taxis_"+id).val();
			if(taxis){
				url += "&taxis="+taxis;
			}
			var pic = $("#pic_"+id).val();
			if(pic){
				url += "&pic="+pic;
			}
			var val = $("#val_"+id).val();
			if(val){
				url += "&val="+val;
			}
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('編輯成功'));
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		info_del:function(id)
		{
			var t = $("#title_"+id).val();
			var tip = p_lang('確定要刪除產品屬性 {title} 嗎？刪除後，產品已使用此屬性相關資訊也會刪除','<span class="red">'+t+'</span>');
			$.dialog.confirm(tip,function(){
				$.phpok.json(get_url('options','delete_values','id='+id),function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('刪除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		}
	}
})(jQuery);

