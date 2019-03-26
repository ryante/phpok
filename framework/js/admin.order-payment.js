/**
 * 支付頁
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年10月06日
**/
;(function($){
	$.admin_order_payment = {
		select:function(val)
		{
			if(val == 'other'){
				$("input[name=title]").parent().show();
			}else{
				$("input[name=title]").parent().hide();
			}
		},
		add:function()
		{
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			$("#postsave").ajaxSubmit({
				'url':get_url('order','payment_save','id={$rs.id}'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('付款新增成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		del:function(id,order_id)
		{
			var url = get_url('order','payment_delete','id='+id+"&order_id="+order_id);
			$.dialog.confirm(p_lang('確定要刪除這條支付嗎？'),function(){
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('付款資訊刪除成功'),function(){
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
$(document).ready(function(){
	layui.use(['form','laydate'],function(){
		var form = layui.form;
		form.on('select(payment)',function(data){
			$.admin_order_payment.select(data.value);
		});
		layui.laydate.render({
            elem: '#dateline'
        });
	});
});