/**
 * 線上升級頁
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2019年1月11日
**/
function zip_update()
{
	var url = get_url('update','zip');
	$.dialog.open(url,{
		'title':p_lang('ZIP包離線升級'),
		'lock':true,
		'width':'500px',
		'height':'150px',
		'ok':function(){
			var iframe = this.iframe.contentWindow;
			if (!iframe.document.body) {
				alert(p_lang('iframe還沒載入完畢呢'));
				return false;
			};
			iframe.save();
			return false;
		},
		'okVal':p_lang('上傳離線包升級'),
		'cancelVal':p_lang('取消'),
		'cancel':function(){return true;}
	});
}
function check_it()
{
	var url = get_url('update','check');
	$.phpok.json(url,function(data){
		if(data.status == 'ok'){
			$.dialog.alert('系統檢測到有更新包，建議您升級');
		}else{
			$.dialog.alert(data.content);
		}
	})
}

$(document).ready(function(){
	$("#project li").each(function(i){
		$(this).click(function(){
			var tips = $(this).attr('tips');
			var url = $(this).attr('href');
			var func = $(this).attr('func');
			if(url){
				if(tips){
					$.dialog.confirm(tips,function(){
						$.phpok.go(url);
					})
				}else{
					$.phpok.go(url);
				}
			}else{
				if(func){
					eval(func);
				}
			}
		});
	});
});