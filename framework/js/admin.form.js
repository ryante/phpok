/**
 * 表單頁面涉及到的一些資訊
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2018年01月21日
**/
;(function($){
	$.admin_form = {
		view:function(id,pid){
			var url = get_url('form','preview','id='+id+"&pid="+pid);
			$.dialog.open(url,{
				'title':p_lang('預覽'),
				'lock':true,
				'width':'750px',
				'height':'650px',
				'ok':true
			});
		}
	}
})(jQuery);