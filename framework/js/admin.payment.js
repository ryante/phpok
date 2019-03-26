/**
 * 支付管理相關操作
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2018年05月09日
**/
;(function($){
	$.admin_payment = {
		group_save:function()
		{
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			$("#post_save").ajaxSubmit({
				'url':get_url('payment','groupsave'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						var tip = $("#id").length > 0 ? p_lang('編輯支付方案成功') : p_lang('新增支付方案成功');
						$.dialog.alert(tip,function(){
							$.phpok.go(get_url('payment'));
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		group_delete:function(id,title)
		{
			var tip = p_lang("確定要刪除支付組【{title}】嗎？<br />刪除後是不能恢復的！","<span class='red'>"+title+"</span>");
			$.dialog.confirm(tip,function(){
				var url = get_url('payment','groupdel','id='+id);
				$.phpok.json(url,function(rs){
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
		},
		add:function(gid)
		{
			var url = get_url('payment','set','gid='+gid);
			$.dialog({
				'title':p_lang('請選擇要支付的型別'),
				'content':document.getElementById("payment_select_info"),
				'ok':function(){
					var code = $("#code").val();
					if(!code){
						alert(p_lang('請選擇要建立的支付引挈'));
						return false;
					}
					url += "&code="+code;
					$.phpok.go(url);
					return true;
				},
				'cancel':true
			});
		},
		del:function(id,title)
		{
			var tip = p_lang("確定要刪除支付方案 {title} 嗎？刪除後是不能恢復的","<span class='red'>"+title+"</span>");
			$.dialog.confirm(tip,function(){
				var url = get_url('payment','delete','id='+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('支付方案刪除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		},
		check:function()
		{
			if(!$("#title").val()){
				$.dialog.alert(p_lang('支付名稱不能為空'));
				return false;
			}
			if(!$("#code").val()){
				$.dialog.alert(p_lang('支付引挈異常，請重新操作'),function(){
					$.phpok.go(url);
				});
				return false;
			}
			return true;
		},
		taxis:function(obj)
		{
			var oldval = $(obj).text();
			var id = $(obj).attr('data');
			var type = $(obj).attr("type");
			$.dialog.prompt(p_lang('請填寫新的排序'),function(val){
				val = parseInt(val);
				if(!val || val<1){
					$.dialog.alert(p_lang('排序僅限數字，不能為空'));
					return false;
				}
				if(val != oldval){
					var url = get_url('payment','taxis','type='+type+"&id="+id+"&taxis="+val);
					$.phpok.json(url,function(rs){
						if(rs.status){
							$.dialog.tips(p_lang('更新排序成功'),function(){
								$.phpok.reload();
							}).lock();
							return true;
						}
						$.dialog.alert(rs.info);
						return false;
					});
					return true;
				}
				$.dialog.tips(p_lang('值一樣，不用更新'));
			},oldval);
		}
	}
})(jQuery);

