/**
 * 表單通用JS，涉及到自定義表單中所有的JS檔案，請注意，此檔案需要載入在 jQuery 之後，且不建議直接讀取
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年03月22日
**/

//非同步載入js
function load_js(url)
{
	if(!url || url == "undefined") return false;
	var lst = url.split(",");
	var lst_count = lst.length;
	var elist = new Array();
	var tm = 0;
	$("script").each(function(t){
		var src = $(this).attr("src");
		if(src && src != 'undefined'){
			elist[tm] = src;
			tm++; 
		}
	});
	var html = '';
	for(var i=0;i<lst_count;i++)
	{
		if($.inArray(lst[i],elist) < 0)
		{
			html += '<script type="text/javascript" src="'+lst[i]+'"></script>';
		}
	}
	$("head").append(html);
}

// 同步載入Ajax，返回字串
function get_ajax(turl)
{
	return $.phpok.ajax(turl);
}

// 同步載入Ajax，返回JSON陣列
function json_ajax(turl)
{
	return $.phpok.json(turl);
}

/**
 * JS語法中涉及到的語言包替換
 * @引數 str 要替換的語言包，支援使用{}包起來的變數
 * @引數 info 支援字串，對數資料，要替換的變數，為空表示沒有變數資訊
 * @返回 替換後的資料
 * @更新時間 
**/
function p_lang(str,info)
{
	if(!str || str == 'undefined'){
		return false;
	}
	if(lang && lang[str]){
		if(!info || info == 'undefined' || typeof info == 'boolean'){
			return lang[str];
		}
		str = lang[str];
		if(typeof info == 'string' || typeof info == 'number'){
			return str.replace(/\{\w+\}/,info);
		}
		for(var i in info){
			str = str.replace('{'+i+'}',info[i]);
		}
		return str;
	}
	if(!info || info == 'undefined' || typeof info == 'boolean'){
		return str;
	}
	if(typeof info == 'string' || typeof info == 'number'){
		return str.replace(/\{\w+\}/,info);
	}
	for(var i in info){
		str = str.replace('{'+i+'}',info[i]);
	}
	return str;
}

// 非同步載入Ajax，執行函式
function ajax_async(turl,func,type)
{
	if(!turl || turl == "undefined")
	{
		return false;
	}
	if(!func || func == "undefined")
	{
		return false;
	}
	if(!type || type == "undefined")
	{
		type = "json";
	}
	if(type != "html" && type != "json" && type != "text" && type != "xml")
	{
		type = "json";
	}
	turl = $.phpok.nocache(turl);
	$.ajax({
		'url': turl,
		'cache': false,
		'async': true,
		'dataType': type,
		'success': function(rs){
			(func)(rs);
		}
	});
}

// 跳轉頁面
function direct(url)
{
	if(!url || url == "undefined") url = window.location.href;
	$.phpok.go(url);
}

//自動刷新
function auto_refresh(rs)
{
	$.phpok.reload();
}

function autosave_callback(rs)
{
	return true;
}

/* 計算字元數長度，中文等同於三個字元，英文為一個字元 */
function strlen(str)
{
	var len = str.length;
	var reLen = 0;
	for (var i = 0; i < len; i++)
	{
		if (str.charCodeAt(i) < 27 || str.charCodeAt(i) > 126)
		{
			reLen += 3;
		} else {
			reLen++;
		}
	}
	if(reLen > 1024 && reLen < (1024 * 1024))
	{
		var reLen = (parseFloat(reLen / 1024).toFixed(3)).toString() + "KB";
	}
	else if(reLen > (1024 * 1024) && reLen < (1024 * 1024 * 1024))
	{
		var reLen = (parseFloat(reLen / (1024 * 1024)).toFixed(3)).toString() + "MB";
	}
	if(!reLen) reLen = "0";
	return reLen;
}


//友情提示
function tips(content,time,id)
{
	if(!time || time == "undefined") time = 1.5;
	if(!id || id == "undefind")
	{
		$.dialog.tips(content,time);
	}
	else
	{
		return $.dialog({
			id: 'Tips',
			title: false,
			cancel: false,
			fixed: true,
			lock: false,
			focus: id,
			resize: false
		}).content(content).time(time || 1.5);
	}
}

/* 計算陣列或對像中的個數 */
function count(id)
{
	var t = typeof id;
	if(t == 'string'){
		return id.length;
	}
	if(t == 'object'){
		var n = 0;
		for(var i in id){
			n++;
		}
		return n;
	}
	return false;
}
