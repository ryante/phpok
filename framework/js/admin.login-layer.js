/**
 * 管理員登入頁
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年08月05日
**/

;(function($){
	$.admin_login = {
		code:function(imgid)
		{
			var url = api_url('vcode');
			$("#"+imgid).attr("src",$.phpok.nocache(url));
		},
		language:function(val)
		{
			var url = get_url('login','','_langid='+val);
			$.phpok.go(url);
		},
		ok:function()
		{
			var self = this;
			$("#post_save").ajaxSubmit({
				'url':get_url('login','ok'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						layer.msg(p_lang('登入成功'), {
		  					icon: 1,
		  					time: 1000
		  				}, function() {
		  					$.phpok.go(get_url('index'));
		  				});
						return true;
					}
					layer.msg(rs.info,{
						icon:2,time:1000
					},function(){
						$("input[name=user],input[name=pass],input[name=_code]").val('');
						self.code('src_code');
					});
					return false;
				}
			});
			return false;
		}
	}
})(jQuery);


$(document).ready(function(){
	if (self.location != top.location){
		top.location = self.location;
	}
	if($('input[name=_code]').length > 0){
		$.admin_login.code('src_code');
		$("#src_code").click(function(){
			$.admin_login.code('src_code');
		})
	}

	layui.config({
	  	base: webroot+'static/admin/' //靜態資源所在路徑
	}).extend({
	  	index: 'lib/index' //主入口模組
	}).use(['index', 'user', 'form'], function() {
		setter = layui.setter,
		admin = layui.admin,
		form = layui.form,
		router = layui.router(),
		search = router.search;
	  	form.render();
	});

});