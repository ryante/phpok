/**
 * 公共頁面JS執行，需要加工 artdialog.css
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2018年03月17日
**/
function top_search()
{
	var title = $("#top-keywords").val();
	if(!title){
		$.dialog.alert('請輸入要搜尋的關鍵字');
		return false;
	}
	return true;
}

// 退出
function logout(t)
{
	var q = confirm("您好，【"+t+"】，確定要退出嗎？");
	if(q == '0')
	{
		return false;
	}
	$.phpok.go(get_url('logout'));
}



;(function($){

	/**
	 * 會員相關操作
	**/
	$.user = {
		login: function(title){
			if(!title || title == 'undefined'){
				title = p_lang('會員登入');
			}
			var email = $("#email").val();
			var mobile = $("#mobile").val();
			var url = get_url('login','open');
			if(email){
				url += "&email="+$.str.encode(email);
			}
			if(mobile){
				url += "&mobile="+$.str.encode(mobile);
			}
			$.dialog.open(url,{
				'title':title,
				'lock':true,
				'width':'300px',
				'height':'180px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':p_lang('會員登入'),
				'cancel':true
			});
		},
		register:function()
		{
			//
		},
		logout: function(title){
			$.dialog.confirm('您好，<span class="red">'+title+'</span>，您確定要退出嗎？',function(){
				$.phpok.go(get_url('logout'));
			});
		}
	};

	/**
	 * 評論相關操作
	**/
	$.comment = {
		post:function()
		{
			$("#comment-post").ajaxSubmit({
				'url':api_url('comment','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.alert('感謝您提交的評論',function(){
							$.phpok.reload();
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		}
	};

	/**
	 * 地址薄增刪改管理
	**/
	$.address = {
		add:function()
		{
			var url = get_url('usercp','address_setting');
			$.dialog.open(url,{
				'title':p_lang('新增新地址'),
				'lock':true,
				'width':'500px',
				'height':'500px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':'提交儲存',
				'cancel':true
			})
		},
		
		edit:function(id)
		{
			var url = get_url('usercp','address_setting','id='+id);
			$.dialog.open(url,{
				'title':p_lang('編輯地址 {id}',"#"+id),
				'lock':true,
				'width':'500px',
				'height':'500px',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save();
					return false;
				},
				'okVal':'儲存資料',
				'cancel':true
			});
		},
		
		del:function(id)
		{
			$.dialog.confirm(p_lang('確定要刪除這個地址嗎？地址ID {id}',"#"+id),function(){
				var url = api_url('usercp','address_delete','id='+id);
				$.phpok.json(url,function(){
					$.phpok.reload();
				})
			});
		},
		set_default:function(id)
		{
			$.dialog.confirm(p_lang('確定要設定這個地址為預設地址嗎？地址ID {id}',"#"+id),function(){
				var url = api_url('usercp','address_default','id='+id);
				$.phpok.json(url,function(){
					$.phpok.reload();
				})
			});
		}
	}
})(jQuery);


$(document).ready(function(){
    //返回頂部
    if ($("meta[name=toTop]").attr("content") == "true") {
    	$("<div id='toTop' class='toTop'></div>").appendTo('body');
    	$("#toTop").css({
    		width: '50px',
    		height: '50px',
    		bottom: '10px',
    		right: '15px',
    		position: 'fixed',
    		cursor: 'pointer',
    		zIndex: '999999'
    	});
    	if ($(this).scrollTop() == 0) {
    		$("#toTop").hide();
    	}
    	$(window).scroll(function(event) {
    		if ($(this).scrollTop() == 0) {
    			$("#toTop").hide();
    		}
    		if ($(this).scrollTop() != 0) {
    			$("#toTop").show();
    		}
    	});
    	$("#toTop").click(function(event) {
    		$("html,body").animate({
    			scrollTop: "0px"
    		}, 666)
    	});
    }


	if($("#comment-post").length > 0){
	    //提交評論
	    $("#comment-post").submit(function(){
			$.comment.post();
			return false;
		});
		$(document).keypress(function(e){
			if(e.ctrlKey && e.which == 13 || e.which == 10) {
				$.comment.post();
				return false;
			}
		});
	}

	$(".floatbar .weixin").hover(function(){
		var src = $(this).find(".wxpic").attr("data-filename");
		var html = '<img src="'+src+'" border="0" />';
		$(this).find('.wxpic').html(html).show();
	},function(){
		$(this).find('.wxpic').hide();
	});

	//非同步定時通知
	window.setTimeout(function(){
		$.phpok.json(api_url('task'),true);
	}, 800);

	if(biz_status && biz_status != 'undefined' && biz_status == '1'){
		$.cart.total();
	}
});
