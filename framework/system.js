/**
 * JS初始化庫
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年12月01日
**/

/**
 * 常規基礎變數，也是系統用到的變數
 */
var basefile = "{$basefile}";
var ctrl_id = "{$sys.ctrl_id}";
var func_id = "{$sys.func_id}";
var webroot = "{$sys.url}";
var apifile = "{$sys.api_file}";
var wwwfile = "{$sys.www_file}";
var adminfile = "{$sys.admin_file}";
var phpok_site_id = "{$phpok_site_id}";
var site_id = "{$site_id}";
var session_name = '{func session_name}';
var session_id = '{func session_id}';

//是否啟用電調
var biz_status = '{$config.biz_status}';


/**
 * 常規基礎變數結束
 */


/**
 * 載入語言包
 */
var lang = new Array();

<!-- loop from=$langs key=$key value=$value id=$tmpid -->
lang["{$key}"] = "{$value}";
<!-- /loop -->

/**
 * 結束語言包
 */

/**
 * 開始載入Jquery，注意，系統會嘗式智慧檢測載入的jquery版本
 */

{$jquery}

/**
 * 結束載入Jquery
 */

;(function($){
	$.phpokurl = {
		base:function(ctrl,func,ext,file)
		{
			var url = webroot + "" +file;
			var is_wen = true;
			if(ctrl && ctrl != 'index'){
				url += "?"+ctrl_id+"="+ctrl;
				is_wen = false;
			}
			if(func && func != 'index'){
				if(is_wen){
					url += "?";
					is_wen = false;
				}else{
					url += "&";
				}
				url += func_id+"="+func;
			}
			if(ext){
				url += is_wen ? ("?"+ext) : ("&"+ext);
			}
			if(phpok_site_id && site_id && phpok_site_id != site_id){
				url += is_wen ? ("?siteId="+phpok_site_id) : ("&siteId="+phpok_site_id);
			}
			return url;
		},
		plugin:function(id,efunc,ext,file)
		{
			var url = webroot+""+file+"?"+ctrl_id+"=plugin&"+func_id+"=exec";
			if(id){
				url += "&id="+id;
			}
			if(efunc){
				url += "&exec="+efunc;
			}
			if(ext){
				url += "&"+ext;
			}
			if(phpok_site_id && site_id && phpok_site_id != site_id){
				url += "&siteId="+phpok_site_id;
			}
			return url;
		}
	}
})(jQuery);

function get_url(ctrl,func,ext)
{
	return $.phpokurl.base(ctrl,func,ext,basefile);
}

function get_plugin_url(id,efunc,ext)
{
	return $.phpokurl.plugin(id,efunc,ext,basefile);
}

function admin_url(ctrl,func,ext)
{
	return $.phpokurl.base(ctrl,func,ext,adminfile);
}

function admin_plugin_url(id,efunc,ext)
{
	return $.phpokurl.plugin(id,efunc,ext,adminfile);
}

function www_url(ctrl,func,ext)
{
	return $.phpokurl.base(ctrl,func,ext,wwwfile);
}

function www_plugin_url(id,efunc,ext)
{
	return $.phpokurl.plugin(id,efunc,ext,wwwfile);
}

function api_url(ctrl,func,ext)
{
	return $.phpokurl.base(ctrl,func,ext,apifile);
}

function api_plugin_url(id,efunc,ext)
{
	return $.phpokurl.plugin(id,efunc,ext,apifile);
}
