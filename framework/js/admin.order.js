/**
 * 後臺訂單管理相關操作
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年06月07日
**/
;(function($){
	$.admin_order = {
		address:function()
		{
			var uid = $("#user_id").val();
			var url = get_url('address','open','tpl=address_order');
			if(uid){
				url = get_url('address','open','tpl=address_order&type=user_id&keywords='+uid);
			}
			$.dialog.open(url,{
				'title':p_lang('選擇收件人地址'),
				'lock':true,
				'width':'800px',
				'height':'600px'
			});
		},
		sn:function()
		{
			var res = 'KF';
			var myDate = new Date();
			res += myDate.getFullYear();
			var month = myDate.getMonth() + 1;
			if(month.length == 1){
				month = '0'+month.toString();
			}
			res += month;
			var date = myDate.getDate();
			if(date.length == 1){
				date = '0'+date.toString();
			}
			res += date;
			var hour = myDate.getHours() + 1;
			if(hour.length == 1){
				hour = '0'+hour.toString();
			}
			res += hour;
			var minutes = myDate.getMinutes();
			if(minutes.length == 1){
				minutes = '0'+minutes.toString();
			}
			res += minutes;
			var seconds = myDate.getSeconds();
			if(seconds.length == 1){
				seconds = '0'+seconds.toString();
			}
			res += seconds;
			var chars = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
			for(var i = 0; i < 3 ; i ++){
				var id = Math.ceil(Math.random()*25);
				res += chars[id];
		    }
		    $("#sn").val(res);
		},
		pass:function()
		{
			var chars = ['0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
			var res = '';
			for(var i = 0; i < 10 ; i ++){
				var id = Math.ceil(Math.random()*35);
				res += chars[id];
		    }
		    $("#passwd").val($.md5(res));
		},
		user:function(type)
		{
			if(!type || type == 'undefined'){
				type = 'email';
			}
			var uid = $("#user_id").val();
			if(!uid){
				$.dialog.alert(p_lang('未繫結會員賬號'));
				return false;
			}
			$.phpok.json(get_url('order','user','id='+uid+"&type="+type),function(rs){
				if(rs.status){
					$("#"+type).val(rs.info);
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},
		ext_delete:function(obj)
		{
			$(obj).parent().parent().parent().remove();
		},
		ext_create:function()
		{
			html  = '<div style="margin:2px 0">';
			html += '<ul class="layout">';
			html += '<li><input type="text" name="extkey[]" class="layui-input" /></li>';
			html += '<li>：</li>';
			html += '<li><input type="text" name="extval[]" class="layui-input default" /></li>';
			html += '<li><input type="button" value=" - " onclick="$.admin_order.ext_delete(this)" class="layui-btn" /></li>';
			html += '</ul></div>';
			$("#ext_html").append(html);
		},
		product_virtual:function(val)
		{
			if(val == 1){
				$("#product_not_virtual").hide();
			}else{
				$("#product_not_virtual").show();
			}
		},
		prolist:function()
		{
			var url = get_url('order','prolist');
			var id = $("#tid").val();
			if(id){
				url += "&id="+id;
			}
			var currency_id = $("#currency_info").attr('data-id');
			if(currency_id){
				url += '&currency_id='+currency_id;
			}

			$.dialog.open(url,{
				'title':p_lang('選擇商品'),
				'width':'70%',
				'height':'70%',
				'lock':true,
				'resize':false,
				'fixed':true
			});
		},
		save:function()
		{
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			$("#ordersave").ajaxSubmit({
				'url':get_url('order','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(!rs.status){
						$.dialog.alert(rs.info);
						return false;
					}
					var tip = p_lang('訂單建立成功');
					if($("#id").length>0){
						tip = p_lang('訂單編輯成功');
					}
					$.dialog.tips(tip,function(){
						$.admin.reload(get_url('order'));
						$.admin.close();
					}).lock();
				}
			});
			return false;
		},
		del:function(id,title)
		{
			var tip = p_lang('確定要刪除訂單 {title} 嗎？<br />刪除後您不能再恢復，請慎用','<span class="red">'+title+'</span>');
			$.dialog.confirm(tip,function(){
				var url = get_url('order','delete','id='+id);
				$.phpok.json(url,function(data){
					if(data.status){
						$.dialog.tips(p_lang('訂單刪除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		},
		show:function(id)
		{
			var url = get_url('order','info','id='+id);
			$.dialog.open(url,{
				'title':p_lang('檢視訂單')+"_#"+id,
				'lock':true,
				'width':'70%',
				'height':'70%',
				'cancel':function(){
					return true;
				},
				'cancelVal':p_lang('關閉')
			})
		},
		payment:function()
		{
			var id = $.checkbox.join();
			if(!id){
				$.dialog.alert(p_lang('請選擇要操作的訂單'));
				return false;
			}
			if(id.indexOf(',') !== -1){
				$.dialog.alert(p_lang('付款操作每次只能操作一個訂單'));
				return false;
			}
			var url = get_url('order','payment','id='+id);
			$.dialog.open(url,{
				'title':p_lang('訂單支付')+'_#<span class="red">'+id+'</span>',
				'lock':true,
				'width':'90%',
				'height':'70%',
				'ok':function(){
					$.phpok.reload();
				},
				'okVal':p_lang('關閉並刷新'),
				'cancel':function(){
					return true;
				},
				'cancelVal':p_lang('關閉')
			})
		},
		express:function()
		{
			var id = $.checkbox.join();
			if(!id){
				$.dialog.alert(p_lang('請選擇要操作的訂單'));
				return false;
			}
			if(id.indexOf(',') !== -1){
				$.dialog.alert(p_lang('物流快遞每次只能一個訂單'));
				return false;
			}
			url = get_url('order','express','id='+id);
			$.dialog.open(url,{
				'title':p_lang('物流快遞，您的訂單編號')+'_#<span class="red">'+id+'</span>',
				'width':'70%',
				'height':'70%',
				'lock':true,
				'cancelVal':p_lang('關閉'),
				'cancel':true
			});
		},
		cancel:function()
		{
			var id = $.checkbox.join();
			if(!id){
				$.dialog.alert(p_lang('請選擇要操作的訂單'));
				return false;
			}
			if(id.indexOf(',') !== -1){
				$.dialog.alert(p_lang('取消操作每次只能一個訂單'));
				return false;
			}
			var sn = $("td[data-id="+id+"]").attr("data-sn");
			var status = $("td[data-id="+id+"]").attr('data-status');
			if(status == 'end'){
				$.dialog.alert(p_lang('訂單已完成，不能執行取消操作'));
				return false;
			}
			if(status == 'stop'){
				$.dialog.alert(p_lang('訂單已結束，不能執行取消操作'));
				return false;
			}
			if(status == 'cancel'){
				$.dialog.alert(p_lang('不能重複執行取消操作'));
				return false;
			}
			var tip = p_lang('確定要取消訂單{sn}嗎？<br/>請填寫理由',' <span class="red">'+sn+'</span> ');
			$.dialog.prompt(tip,function(val){
				if(!val){
					$.dialog.alert(p_lang('取消理由不能為空'));
					return false;
				}
				var url = get_url('order','cancel','id='+id+"&note="+$.str.encode(val));
				$.phpok.json(url,function(data){
					if(data.status){
						$.dialog.tips(p_lang('訂單取消成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			},'');
		},
		stop:function()
		{
			var id = $.checkbox.join();
			if(!id){
				$.dialog.alert(p_lang('請選擇要操作的訂單'));
				return false;
			}
			if(id.indexOf(',') !== -1){
				$.dialog.alert(p_lang('結束操作每次只能一個訂單'));
				return false;
			}
			var sn = $("td[data-id="+id+"]").attr("data-sn");
			var status = $("td[data-id="+id+"]").attr('data-status');
			if(status == 'end'){
				$.dialog.alert(p_lang('訂單已完成，不能執行結束操作'));
				return false;
			}
			if(status == 'cancel'){
				$.dialog.alert(p_lang('訂單已取消，不能執行取消操作'));
				return false;
			}
			if(status == 'stop'){
				$.dialog.alert(p_lang('不能重複執行取消操作'));
				return false;
			}
			var tip = p_lang('確定要結束該訂單嗎？執行後訂單')+'<br/><span class="red">'+sn+'</span>';
			$.dialog.confirm(tip,function(){
				var url = get_url('order','stop','id='+id);
				$.phpok.json(url,function(data){
					if(data.status){
						$.dialog.tips(p_lang('訂單已結束'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		},
		finish:function()
		{
			var id = $.checkbox.join();
			if(!id){
				$.dialog.alert(p_lang('請選擇要操作的訂單'));
				return false;
			}
			if(id.indexOf(',') !== -1){
				$.dialog.alert(p_lang('完成操作每次只能一個訂單'));
				return false;
			}
			var sn = $("td[data-id="+id+"]").attr("data-sn");
			var status = $("td[data-id="+id+"]").attr('data-status');
			if(status == 'end'){
				$.dialog.alert(p_lang('不能重複執行取消操作'));
				return false;
			}
			if(status == 'cancel'){
				$.dialog.alert(p_lang('訂單已取消，不能執行完成操作'));
				return false;
			}
			if(status == 'stop'){
				$.dialog.alert(p_lang('訂單已結束，不能執行完成操作'));
				return false;
			}
			var tip = p_lang('確定該訂單已完成嗎？')+'<br/><span class="red">'+sn+'</span>';
			$.dialog.confirm(tip,function(){
				var url = get_url('order','end','id='+id);
				$.phpok.json(url,function(data){
					if(data.status){
						$.dialog.tips(p_lang('訂單已完成'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		}
	}
})(jQuery);

$(document).ready(function(){
	layui.use('form',function () {
		layui.form.render();
	});
});