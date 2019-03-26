/**
 * 後臺管理收藏夾的JS
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年06月04日
**/
;(function($){
	$.admin_fav = {
		del:function(id)
		{
			$.dialog.confirm(p_lang('確認要刪除該收藏主題（ID：{id}）嗎？',id),function(){
				$.phpok.json(get_url('fav','delete','id='+id),function(data){
					if(data.status){
						$("tr[data-id="+id+"]").remove();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		}
	}
	$(document).ready(function(){
		layui.use(['laydate','form'],function () {
	        layui.laydate.render({elem:'#startdate'});
	        layui.laydate.render({elem:'#stopdate'});
	    });
	});
})(jQuery);