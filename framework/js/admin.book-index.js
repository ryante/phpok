/**
 * 內容首頁
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年08月11日
**/
;(function($){
	$(document).ready(function(){
		$("#project li").mouseover(function(){
			$(this).addClass("hover");
		}).mouseout(function(){
			$(this).removeClass("hover");
		}).click(function(){
			var url = $(this).attr("href");
			var txt = $(this).find('.txt').text();
			if(url){
				$.win(txt,url);
				return true;
			}
			$.dialog.alert(p_lang('未指定動作'));
			return false;
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
		$.admin.badge();
	});
})(jQuery);