/**
 * 編輯訂單
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年09月29日
**/
;(function($){
	$.admin_order_set = {
		add:function()
		{
			var url = get_url('order','product_set');
			if($("#id").val()){
				url += "&order_id="+$("#id").val();
			}else{
				var currency_id = $("#currency_id").val();
				if(!currency_id){
					$.dialog.alert(p_lang('請選擇貨幣型別'));
					return false;
				}
				url += "&currency_id="+currency_id;
			}
			$.dialog.open(url,{
				'title':p_lang('產品新增'),
				'lock':true,
				'width':'760px',
				'height':'500px',
				'cancel':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				}
			});
		},
		edit:function(id)
		{
			var url = get_url('order','product_set','id='+id);
			if($("#id").val()){
				url += "&order_id="+$("#id").val();
			}else{
				var currency_id = $("#currency_id").val();
				if(!currency_id){
					$.dialog.alert(p_lang('請選擇貨幣型別'));
					return false;
				}
				url += "&currency_id="+currency_id;
			}
			$.dialog.open(url,{
				'title':p_lang('編輯產品'),
				'lock':true,
				'width':'760px',
				'height':'500px',
				'cancel':true,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				}
			});
		},
		del:function(id)
		{
			var self = this;
			$.dialog.confirm(p_lang('確定要刪除該產品嗎？'),function(){
				var url = get_url('order','product_delete','id='+id);
				$.phpok.json(url,function(data){
					if(data.status){
						$.dialog.tips(p_lang('刪除操作成功，請稍候…'));
						self.product_reload();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				})
			});
		},
		get_price:function()
		{
			var self = this;
			var url = get_url('order','product_price');
			if($("#id").val()){
				url += "&id="+$("#id").val();
			}else{
				var currency_id = $("#currency_id").val();
				url += "&currency_id="+currency_id;
			}
			var act = $.dialog.tips(p_lang('正在計算價格，請稍候…'),10).lock();
			$.phpok.json(url,function(rs){
				act.close();
				if(rs.status){
					$("#ext_price_product").val(rs.info);
					self.total_price();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			})
		},
		total_price:function()
		{
			var total = 0;
			$("input[sign=ext_price]").each(function(i){
				var val = $(this).val();
				val = parseFloat(val);
				if(isNaN(val)){
					val = 0;
				}
				if($(this).attr("action") == 'add'){
					total += val;
				}else{
					total -= val;
				}
			});
			total = total.toFixed(2);
			$('#price').val(total.toString());
		},
		product_reload:function()
		{
			var self = this;
			var url = get_url('order','productlist');
			var id = $("#id").val();
			if(id){
				url += "&id="+id;
			}else{
				var currency_id = $("#currency_id").val();
				if(currency_id){
					url += "&currency_id="+currency_id;
				}
			}
			var tip = $.dialog.tips("正在載入產品資訊，請稍候…",30).lock();
			$.phpok.json(url,function(data){
				tip.close();
				if(data.status){
					$("#product_info").html(data.info);
					layui.use('form',function () {
						layui.form.render();
					});
					self.get_price();
					return true;
				}
				$.dialog.alert(data.info);
				return false;
			})
		}
	}
})(jQuery);
$(document).ready(function(){
	if(!$("#id").val()){
		$.admin_order.sn();
		$.admin_order.pass();
	}
	if(!$("#passwd").val()){
		$.admin_order.pass();
	}
	$.admin_order_set.product_reload();
});