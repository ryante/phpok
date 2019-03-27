/**
 * 首頁
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年08月11日
**/
$(document).ready(function(){
	var r_menu = [[{
		'text':p_lang('刷新'),
		'func':function(){
			$.phpok.reload();
		}
	}],[{
		'text':p_lang('清空緩存'),
		'func': function() {top.$.admin_index.clear();}
	},{
		'text':p_lang('修改我的資訊'),
		'func':function(){top.$.admin_index.me();}
	},{
		'text':p_lang('訪問前臺首頁'),
		'func':function(){
			var url = "{$sys.www_file}?siteId={$session.admin_site_id}";
			url = $.phpok.nocache(url);
			window.open(url);
		}
	}],[{
		'text': p_lang('幫助說明'),
		'func': function() {
			top.$("a[layadmin-event=about]").click();
			return true;
		}
	}]];
	$(window).smartMenu(r_menu,{
		'name':'smart',
		'textLimit':8
	});
	window.addEventListener("message",function(e){
		if(e.origin != window.location.origin){
			return false;
		}
		if(e.data == 'badge'){
			$.admin.badge();
			return true;
		}
	}, false);
	//檢測是否新增角標
	window.setTimeout(function(){
		$.admin.badge();
	}, 300);
});
