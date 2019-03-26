/**
 * 專案管理首頁js
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年07月11日
**/

$(document).ready(function(){
	$("div[name=taxis]").click(function(){
		var oldval = $(this).text();
		var id = $(this).attr('data');
		$.dialog.prompt(p_lang('請填寫新的排序'),function(val){
			if(val != oldval){
				$.admin_project.sort(val,id);
			}
		},oldval);
	});
});