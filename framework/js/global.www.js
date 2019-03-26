/***********************************************************
	Filename: {phpok}js/global.www.js
	Note	: 前臺公共JS載入
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年9月12日
***********************************************************/
//前臺通用彈出視窗
function phpok_open(id,title)
{
	if(id == "login" || id == "register")
	{
		var url = get_url("ajax","exit","filename="+id);
	}
	else
	{
		var url = id;
	}
	if(!title || title == "undefined") title = '彈出窗';
	$.dialog.open(url,{
		 "title":title
		,"resize":true
		,"lock":true
		,"id":"phpok_open_frame"
		,"fixed":true
		,"drag":false
	});
}

//前臺常用JS函式封裝
;(function($){
//定義驗證碼
$.fn.phpok_vcode = function(ext){
	var url = api_url('vcode');
	if(ext && ext != 'undefined')
	{
		url += "&id="+ext;
	}
	$(this).attr('src',$.phpok.nocache(url));
}
$.phpok_www = {
	comment:function(id,tid,callback){
		if(!tid || tid == 'undefined')
		{
			$.dialog.alert(lang.commentNotId);
			return false;
		}
		//直接通過JS判斷是否惡意灌水
		var spam = $("#"+id+"_spam").val();
		if(!spam)
		{
			$.dialog.alert(lang.commentSpamEmpty);
			return false;
		}
		var content = $("#"+id).val();
		if(!content)
		{
			$.dialog.alert(lang.commentEmpty);
			return false;
		}
		var url = api_url('comment','save','id='+tid);
		url += "&content="+$.str.encode(content);
		url += "&_spam="+spam;
		//提交評論
		var rs = json_ajax(url);
		if(rs.status == 'ok')
		{
			if(callback && callback != 'undefined')
			{
				eval("callback()");
			}
			else
			{
				$.dialog.alert(lang.commentSuccess,function(){
					$.phpok.reload();
				},'succeed');
			}
		}
		else
		{
			$.dialog.alert(rs.content,'','error');
			return false;
		}
	}
};
})(jQuery);