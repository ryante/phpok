/**
 * 管理員登入頁
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年08月07日
**/

//驗證碼
function login_code(appid)
{
	var src_url = api_url("vcode","","id="+appid);
	$("#src_code").attr("src",$.phpok.nocache(src_url));
}

//驗證並登入
function admlogin()
{
	$("#adminlogin").ajaxSubmit({
		'url':get_url('login','ok'),
		'type':'post',
		'dataType':'json',
		'success':function(rs){
			if(rs.status){
				$.phpok.go(get_url('index'));
				return true;
			}
			$.dialog.alert(rs.info,function(){
				$("#code_id").val('');
				login_code('admin');
			},'error');
			return false;
		}
	});
	return false;
}

function update_lang(val)
{
	var url = get_url('login','','_langid='+val);
	$.phpok.go(url);
}


function cLanguage() {
	var l = $("#c-language");
	var s = l.find(".c-select");
	var o = l.find(".c-option");
	var aA = s.find("a");
	s.click(function(event){
		event.stopPropagation()
		if (l.hasClass("open")) {
			l.removeClass("open");
			o.slideUp();
		}else{
			l.addClass("open");
			o.slideDown();
		}
	});

	/*aA.click(function(event){
		event.stopPropagation()
		l.removeClass("open");
		o.slideUp();
	});*/
	$(window).click(function(){
		l.removeClass("open");
		o.slideUp();
	})
}

$(document).ready(function(){
	if (self.location != top.location){
		top.location = self.location;
	}
	$('.c-banner').slick({
		dots: false,
		slidesToShow: 1,
		slidesToScroll: 1,
		fade:true,
		speed:1500,
		autoplay: true,
		arrows: false,
		autoplaySpeed: 3000
	});
	cLanguage();
});