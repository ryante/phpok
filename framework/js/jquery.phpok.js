/**
 * PHPOK程式中常用到的JS，封裝在此
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 GNU Lesser General Public License (LGPL)
 * @日期 2017年04月18日
**/

;(function($){
	$.phpok = {

		/**
		 * 重新整理當前頁面，使用方法：$.phpok.refresh();
		**/
		refresh: function()
		{
			window.location.reload(true);
		},

		/**
		 * 重新整理頁面別名，使用方法：$.phpok.reload();
		**/
		reload:function()
		{
			this.refresh();
		},

		/**
		 * 跳轉到目標網址
		 * @引數 url 要跳轉到的網址
		 * @引數 nocache 是否禁止快取，設定且為true時，程式會在網址後面補增_noCache引數
		**/
		go: function(url,nocache)
		{
			if(!url){
				return false;
			}
			if(nocache || nocache == 'undefined'){
				url = this.nocache(url);
			}
			window.location.href = url;
		},

		/**
		 * 彈出視窗
		 * @引數 url 要彈出視窗的網址
		 * @引數 nocache 是否禁止快取，設定且為true時，程式會在網址後面補增_noCache引數
		**/
		open:function(url,nocache)
		{
			if(!url){
				return false;
			}
			if(nocache || nocache == 'undefined'){
				url = this.nocache(url);
			}
			window.open(url);
		},

		/**
		 * 讀取Ajax的內容，讀出來的內容為html
		 * @引數 url 目標網址
		 * @引數 obj 執行方法，為空或未設定，則返回HTML程式碼，此時為同步請求
		**/
		ajax:function(url,obj,postData)
		{
			if(!url){
				return false;
			}
			var cls = {'url':url,'cache':false,'dataType':'html'};
			if(postData && postData != 'undefined'){
				cls.data = postData;
				cls.type = 'post';
			}
			cls.beforeSend = function(request){
				request.setRequestHeader("request_type","ajax");
				request.setRequestHeader("phpok_ajax",1);
				if(session_name && session_name != 'undefined'){
					request.setRequestHeader(session_name,$.cookie.get(session_name));
				}
			};
			if(!obj || obj == 'undefined'){
				cls.async = false;
				return $.ajax(cls).responseText;
			}
			cls.success = function(rs){(obj)(rs)};
			$.ajax(cls);
		},

		/**
		 * 讀取 Ajax 內容，返回JSON資料
		 * @引數 url 目標網址
		 * @引數 obj 執行方法，為空或未設定，則返回JSON物件，此時為同步請求
		**/
		json:function(url,obj,postData)
		{
			if(!url){
				return false;
			}
			var self = this;
			var cls = {'url':url,'cache':false,'dataType':'json'};
			if(postData && postData != 'undefined'){
				cls.data = postData;
				cls.type = 'post';
			}
			cls.beforeSend = function(request){
				request.setRequestHeader("request_type","ajax");
				request.setRequestHeader("phpok_ajax",1);
				if(!postData || postData == 'undefined'){
					request.setRequestHeader("content-type","application/json");
				}
				if(session_name && session_name != 'undefined'){
					request.setRequestHeader(session_name,$.cookie.get(session_name));
				}
			};
			if(!obj || obj == 'undefined'){
				cls.async = false;
				var info = $.ajax(cls).responseText;
				return self.json_decode(info);
			}
			if(typeof obj == 'boolean'){
				cls.success = function(rs){
					return true;
				}
			}else{
				cls.success = function(rs){
					(obj)(rs);
				};
			}
			$.ajax(cls);
		},

		/**
		 * 格式化網址，增加_noCache尾巴，以保證不從快取中讀取資料
		 * @引數 url 要格式化的網址
		**/
		nocache: function(url)
		{
			url = url.replace(/&amp;/g,'&');
			if(url.indexOf('_noCache') != -1){
				url = url.replace(/\_noCache=[0-9\.]+/,'_noCache='+Math.random());
			}else{
				url += url.indexOf('?') != -1 ? '&' : '?';
				url += '_noCache='+Math.random();
			}
			return url;
		},


		json_encode:function(obj)
		{
			if(!obj || obj == 'undefined'){
				return false;
			}
			return JSON.stringify(obj);
		},


		json_decode:function(str)
		{
			if(!str || str == 'undefined'){
				return false;
			}
			return $.parseJSON(str);
		},

		/**
		 * 生成隨機數
		 * @引數 len 長度，留空使用長度10
		 * @引數 type 型別，支援 letter,num,fixed,all，其中 fixed 表示字母數字混合，all 表示字母，數字，及特殊符號，letter 表示字母，num 表示數字
		**/
		rand:function(len,type)
		{
			len = len || 10;
			if(!type || type == 'undefined'){
				type = 'letter';
			}
			var types = {'letter':'abcdefhijkmnprstwxyz','num':'0123456789','fixed':'abcdefhijkmnprstwxyz0123456789','all':'abcdefhijkmnprstwxyz0123456789-,.*!@#$%=~'}
			if(type != 'letter' && type != 'num' && type != 'all' && type != 'fixed'){
				type = 'letter';
			}
			var string = types[type];
			var length = string.length;
			var val = '';
			for (i = 0; i < len; i++) {
				val += string.charAt(Math.floor(Math.random() * length));
			}
			return val;
		},
		/**
		 * 向頂層傳送訊息
		 * @引數 info 要傳送的文字訊息，注意，僅限文字
		**/
		message:function(info,url)
		{
			try{
				if(url && url != 'undefined'){

					$("iframe").each(function(i){
						var src = $(this).attr('src');
						if(typeof url == 'boolean'){
							var obj = $(this)[0].contentWindow;
							obj.postMessage(info,window.location.origin);
						}else{
							if(url.indexOf(src) != -1){
								var obj = $(this)[0].contentWindow;
								obj.postMessage(info,url)
							}
						}
					});
				}else{
					window.top.postMessage(info,top.window.location.origin);
				}
			} catch (error) {
				console.log(error);
				return false;
			}
		},
		data:function(id,val)
		{
			if(val && val != 'undefined'){
				localStorage.setItem(id,val);
				return true;
			}
			var info = localStorage.getItem(id);
			if(!info || info == 'undefined'){
				return false;
			}
			return info;
		},
		undata:function(id)
		{
			localStorage.removeItem(id);
		}
	};

	/**
	 * JSON字串與物件轉換操作
	**/
	$.json = {

		/**
		 * 字串轉物件
		 * @引數 str 要轉化的字串
		**/
		decode:function(str)
		{
			if(!str || str == 'undefined'){
				return false;
			}
			return JSON.parse(str);
		},

		/**
		 * 物件轉成字串
		 * @引數 obj 要轉化的物件
		**/
		encode:function(obj)
		{
			if(!obj || obj == 'undefined'){
				return false;
			}
			return JSON.stringify(obj);
		}
	};

	$.checkbox = {
		_obj:function(id)
		{
			if(id && id != 'undefined' && typeof id == 'string'){
				if(id.match(/^[a-zA-Z0-9\-\_]{1,}$/)){
					if($("#"+id).is('input')){
						return $("#"+id);
					}
					return $("#"+id+" input[type=checkbox]");
				}
				if($(id).is('input')){
					return $(id);
				}
				return $(id+" input[type=checkbox]");
			}
			return $("input[type=checkbox]");
		},

		/**
		 * 全選
		 * @引數 id 要操作的ID
		**/
		all:function(id)
		{
			var obj = this._obj(id);
			obj.prop('checked',true);
            window.setTimeout("layui.form.render('checkbox')",100);
			return true;
		},

		/**
		 * 返先
		 * @引數 id 要操作的ID
		**/
		none:function(id)
		{
			var obj = this._obj(id);
			obj.removeAttr('checked');
            window.setTimeout("layui.form.render('checkbox')",100);
			return true;
		},

		/**
		 * 更多選擇，預設只選5個（count預設值為5） $.checkbox.more(id,5);
		 * @引數 id 要操作的ID
		 * @引數 count 每次次最多選幾個
		**/
		more: function(id,count){
			var obj = this._obj(id);
			var num = 0;
			if(!count || count == 'undefined' || parseInt(count)<5){
				count = 5;
			}
			obj.each(function(){
				if(!$(this).is(":checked") && num<count){
					$(this).prop("checked",true);
					num++;
				}
			});
            window.setTimeout("layui.form.render('checkbox')",100)
			return true;
		},

		/**
		 * 反選，呼叫方法：$.checkbox.anti(id);
		 * @引數 id 要操作的ID
		**/
		anti:function(id)
		{
			var t = this._obj(id);
			t.each(function(i){
				if($(this).is(":checked")){
					$(this).removeAttr('checked');
				}else{
					$(this).prop('checked',true);
				}
				window.setTimeout("layui.form.render('checkbox')",100)
			});
		},

		/**
		 * 合併複選框值資訊
		 * @引數 id 要操作的ID
		 * @引數 type 要支援合關的字元
		 * @引數 str 要連線的字元，為空或未設定使用英文逗號隔開
		**/
		join:function(id,type,str)
		{
			var cv = this._obj(id);
			var idarray = new Array();
			var m = 0;
			cv.each(function(){
				if(type == "all"){
					idarray[m] = $(this).val();
					m++;
				}else if(type == "unchecked" && !$(this).is(':checked')){
					idarray[m] = $(this).val();
					m++;
				}else{
					if($(this).is(':checked')){
						idarray[m] = $(this).val();
						m++;
					}
				}
			});
			var linkid = (str && str != 'undefined') ? str : ',';
			var tid = idarray.join(linkid);
			return tid;
		}
	}

	/**
	 * 字串相關操作
	**/
	$.str = {

		/**
		 * 字串合併，用英文逗號隔開
		 * @引數 str1 要合併的字串1
		 * @引數 str2 要合併的字串2
		**/
		join: function(str1,str2){
			var string = '';
			if(!str1 || str1 == 'undefined'){
				if(!str2 || str2 == 'undefined'){
					return false;
				}
				string = str2;
			}
			if(str1 && str1 != 'undefined'){
				if(!str2 || str2 == 'undefined'){
					string = str1;
				}else{
					string = str1 + "," + str2;
				}
			}
			if(string == ''){
				return false;
			}
			var array = string.split(",");
			array = $.unique(array);
			string = array.join(",");
			return string ? string : false;
		},

		/**
		 * 字串識別符號檢測
		 * @引數 str 要檢測的字串
		 * @返回 true 或 false
		**/
		identifier: function(str){
			//驗證標識串，PHPOK系統中，大量使用標識串，將此檢測合併進來
			var chk = /^[A-Za-z]+[a-zA-Z0-9_\-]*$/;
			return chk.test(str);
		},

		/**
		 * 網址常規編碼
		 * @引數 str 要編碼的字串
		**/
		encode: function(str){
			return encodeURIComponent(str);
		}
	};

	/**
	 * 由PHPOK編寫的基於jQuery的Cookie操作
	 * 讀取cookie資訊 $.cookie.get("變數名");
	 * 設定cookie資訊
	 * 刪除Cookie資訊 $.cookie.del("變數名");
	**/
	$.cookie = {

		/**
		 * 取得 Cookie 資訊 $.cookie.get('變數名')
		 * @引數 name 要獲取的 cookie 變數中的標識
		**/
		get: function(name)
		{
			var cookieValue = "";
			var search = name + "=";
			if(document.cookie.length > 0){
				var offset = document.cookie.indexOf(search);
				if (offset != -1){
					offset += search.length;
					var end = document.cookie.indexOf(";", offset);
					if (end == -1){
						end = document.cookie.length;
					}
					cookieValue = unescape(document.cookie.substring(offset, end));
					end = null;
				}
				search = offset = null;
			}
			return cookieValue;
		},

		/**
		 * 設定 Cookie 資訊 $.cookie.set("變數名","值","過期時間");
		 * @引數 cookieName 變數名
		 * @引數 cookieValue 變數內容
		 * @引數 DayValue 過期時間，預設是1天，單位是天
		 * @返回
		 * @更新時間
		**/
		set: function(cookieName,cookieValue,DayValue)
		{
			var expire = "";
			var day_value=1;
			if(DayValue!=null){
				day_value=DayValue;
			}
			expire = new Date((new Date()).getTime() + day_value * 86400000);
			expire = "; expires=" + expire.toGMTString();
			document.cookie = cookieName + "=" + escape(cookieValue) +";path=/"+ expire;
			cookieName = cookieValue = DayValue = day_value = expire = null;
		},

		/**
		 * 刪除 Cookie 操作
		 * @引數 cookieName 變數名
		**/
		del: function(cookieName){
			var expire = "";
			expire = new Date((new Date()).getTime() - 1 );
			expire = "; expires=" + expire.toGMTString();
			document.cookie = cookieName + "=" + escape("") +";path=/"+ expire;
			cookieName = expire = null;
		}
	};

	$.extend({
		identifier:function(id)
		{
			return $.str.identifier(id);
		}
	});

})(jQuery);

function identifier(str)
{
	return $.str.identifier(str);
}


/**
 * 舊版 Input 操作類
**/
;(function($){

	$.input = {

		checkbox_all: function(id)
		{
			return $.checkbox.all(id);
		},

		//全不選，呼叫方法：$.input.checkbox_none(id);
		checkbox_none: function(id)
		{
			return $.checkbox.none(id);
		},

		//每次選5個（total預設值為5） $.input.checkbox_not_all(id,5);
		checkbox_not_all: function(id,total)
		{
			return $.checkbox.more(id,total);
		},

		//反選，呼叫方法：$.input.checkbox_anti(id);
		checkbox_anti: function(id)
		{
			return $.checkbox.anti(id);
		},

		//合併複選框值資訊，以英文逗號隔開
		checkbox_join: function(id,type)
		{
			return $.checkbox.join(id,type);
		}

	};

})(jQuery);