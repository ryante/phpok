/**
 * 後臺首頁涉及到的樣式
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年08月13日
**/
;(function($){
	$.admin_index = {
		site:function()
		{
			$.dialog.open(get_url('site','add'),{
				'title': p_lang('新增站點')
				,'lock': true
				,'width': '450px'
				,'height': '150px'
				,'resize': false
				,'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				}
				,'okVal':p_lang('新增新站點')
				,'cancel':true
			});
		},
		me:function()
		{
			$.dialog.open(get_url("me","setting"),{
				"title":p_lang('修改管理員資訊'),
				"width":600,
				"height":260,
				"lock":true,
				'move':false,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.$.admin_me.setting_submit();
					return false;
				},
				'okVal':p_lang('提交儲存'),
				'cancel':true
			});
		},
		pass:function()
		{
			
			$.dialog.open(get_url("me","pass"),{
				"title":p_lang('管理員密碼修改'),
				"width":500,
				"height":240,
				"lock":true,
				'move':false,
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.$.admin_me.pass_submit();
					return false;
				},
				'okVal':p_lang('提交儲存'),
				'cancel':true
			});
		},
		logout:function()
		{
			$.dialog.confirm(p_lang('您確定要退出嗎？'),function(){
				$.phpok.go(get_url("logout"));
			});
		},
		clear:function()
		{
			var obj = $.dialog.tips(p_lang('請稍候，正在執行'),100)
			$.phpok.json(get_url("index","clear"),function(data){
				obj.close();
				if(data.status){
					layer.msg(p_lang('快取清空完成'));
					return true;
				}
				$.dialog.alert(rs.info);
				return true;
			});
		},
		lang:function(val)
		{
			$.phpok.go(get_url("index",'','_langid='+val));
		},
		pendding:function()
		{
			$.phpok.json(get_url('index','pendding'),function(rs){
				$("span.layui-badge").remove();
				$.cookie.del('badge');
				if(rs.status && rs.info){
					var list = rs.info;
					var html = '<span class="layui-badge">{total}</span>';
					var total = 0;
					var pid_info = '';
					for(var key in list){
						if(key == 'update_action'){
							$.admin_index.update();
						}else{
							if(list[key]['id'] == 'user' || list[key]['id'] == 'reply' || list[key]['id'] == 'update'){
								$("li[data-name="+list[key]['id']+"] a,dd[data-name="+list[key]['id']+"] a").append(html.replace('{total}',list[key]['total']));
							}else{
								if(pid_info){
									pid_info += ",";
								}
								pid_info += list[key]['id']+":"+list[key]['total'];
								total = parseInt(total) + parseInt(list[key]['total']);
								$("dd[pid="+list[key]['id']+"] a").append(html.replace('{total}',list[key]['total']));
							}
						}
					}
					if(pid_info != ''){
						$.cookie.set('badge',pid_info);
					}
					
					if(total>0){
						$("li[data-name=list] a").eq(0).append(html.replace('{total}',total));
					}
				}
				$.phpok.message('badge',true);
			});
		},
		update:function()
		{
			$.phpok.json(get_url('update','check'),function(data){
				if(data.status == 'ok'){
					$.dialog.notice({
						title: '友情提示',
						width: 220,// 必須指定一個畫素寬度值或者百分比，否則瀏覽器視窗改變可能導致artDialog收縮
						content: '您的程式有新的更新，為了保證系統安全，建議您及時更新程式',
						icon: 'face-smile',
						time: 10
					});
				}
			});
		},
		develop:function(val)
		{
			if(val == 1){
				$.dialog.tips(p_lang('正在切換到開發模式，請稍候…'));
				$.phpok.json(get_url('index','develop','val=1'),function(data){
					if(data.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			}else{
				$.dialog.tips(p_lang('正在切換到應用模式，請稍候…'));
				$.phpok.json(get_url('index','develop','val=0'),function(data){
					if(data.status){
						$.phpok.reload();
						return true;
					}
					$.dialog.alert(data.info);
					return false;
				});
			}
		}
	}
})(jQuery);


$(document).ready(function(){
	//監聽事件
	document.addEventListener("keydown", function (e) {
	    if(e.keyCode==116) {
	        e.preventDefault();
	        $('a[layadmin-event=refresh]').click();
	        //要做的其他事情
	    }
	}, false);
	window.addEventListener("message",function(e){
		if(e.origin != window.location.origin){
			return false;
		}
		if(e.data == 'close'){
			$('.aui_close').click();
			return true;
		}
		if(e.data == 'pendding'){
			$.admin_index.pendding();
		}
	}, false);
	$.admin_index.pendding();
	
	//自定義右鍵
	var r_menu = [[{
		'text':p_lang('重新整理網頁'),
		'func':function(){
			$.phpok.reload();
		}
	},{
		'text': p_lang('清空快取'),
		'func': function() {
			$.admin_index.clear();
		}    
	},{
		'text':p_lang('修改我的資訊'),
		'func':function(){
			$.admin_index.me();
		}
	}],[{
		'text':p_lang('關於PHPOK'),
		'func':function(){
			$("a[layadmin-event=about]").click();
		}
	}]];
	$(window).smartMenu(r_menu,{'textLimit':8});
});

