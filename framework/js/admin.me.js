/**
 * 管理員資訊修改
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年08月07日
**/

;(function($){
	$.admin_me = {
		pass_submit:function()
		{
			var oldpass = $("#oldpass").val();
			if(!oldpass){
				$.dialog.alert(p_lang('舊密碼不能為空'));
				return false;
			}
			var newpass = $("#newpass").val();
			var chkpass = $("#chkpass").val();
			if(!newpass){
				$.dialog.alert(p_lang('新密碼不能為空'));
				return false;
			}
			if(newpass != chkpass){
				$.dialog.alert(p_lang('兩次輸入的密碼不一致'));
				return false;
			}
			if(oldpass == newpass){
				$.dialog.alert(p_lang('新舊密碼是一樣的，不能執行此操作'));
				return false;
			}
			if(typeof(CKEDITOR) != "undefined"){
				for(var i in CKEDITOR.instances){
					CKEDITOR.instances[i].updateElement();
				}
			}
			$("#post_save").ajaxSubmit({
				'url':get_url('me','pass_submit'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('密碼修改成功，下次登入請使用新密碼'));
						window.setTimeout(function(){
							$.dialog.close();
						}, 1000);
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		},
		setting_submit()
		{
			$("#post_save").ajaxSubmit({
				'url':get_url('me','submit'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('管理員資訊操作成功'));
						window.setTimeout(function(){
							$.dialog.close();
						}, 1000);
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
		}
	}
})(jQuery);