/**
 * 下拉操作
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年08月04日
**/
;(function($){
	$.phpok_form_select = {

		/**
		 * 下拉選單變化後執行的JS
		 * @引數 groupid 選項組ID
		 * @引數 identifier 變數標識
		 * @引數 val 選中的值
		**/
		change:function(groupid,identifier,val)
		{
			var url = api_url('opt','index','group_id='+groupid+"&identifier="+identifier+"&val="+$.str.encode(val));
			$.phpok.ajax(url,function(data){
				$("#"+identifier+"_html").html(data);
			})
		}
	}
})(jQuery);