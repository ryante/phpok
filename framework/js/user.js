/***********************************************************
	Filename: {phpok}/js/user.js
	Note	: 會員管理中涉及到的JS
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年5月2日
***********************************************************/
//檢查新增操作
function check_add()
{
	var url = get_url("user","chk");
	var id = $("#id").val();
	if(id && id != "undefined"){
		url += "&id="+id;
	}
	var user = $("#user").val();
	if(!user || user == "undefined"){
		$.dialog.alert("會員賬號不能為空");
		return false;
	}
	url += "&user="+$.str.encode(user);
	var mobile = $("#mobile").val();
	if(mobile){
		url += "&mobile="+$.str.encode(mobile);
	}
	var email = $("#email").val();
	if(email){
		url += "&email="+$.str.encode(email);
	}
	var rs = $.phpok.json(url);
	if(rs.status != "ok"){
		$.dialog.alert(rs.content);
		return false;
	}
	return true;
}

function del(id)
{
	if(!id){
		$.dialog.alert("操作非法");
		return false;
	}
	$.dialog.confirm(p_lang('確定要刪除會員號ID為 {id} 的會員資訊嗎？<br>刪除後資料將被清空且不能恢復','<span class="red">#'+id+'</span>'),function(){
		var url = get_url('user','ajax_del','id='+id);
		$.phpok.ajax(url,function(data){
			if(data == 'ok'){
				$.phpok.reload();
			}else{
				if(!data){
					data = p_lang('刪除會員操作異常');
					$.dialog.alert(data);
				}
			}
		});
		return true;
	});
}

//更改許可權狀態
function set_status(id)
{
	if(!id)
	{
		alert("操作非法");
		return false;
	}
	var t = $("#status_"+id).attr("value");
	if(t == 2)
	{
		$.dialog.alert("此會員已被鎖定，請點編輯後進行解除鎖定");
		return false;
	}
	var url = get_url("user","ajax_status") + "&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		var n_t = t == 1 ? 0 : 1;
		$("#status_"+id).removeClass("status"+t).addClass("status"+n_t);
		$("#status_"+id).attr("value",n_t);
		return true;
	}
	else
	{
		if(!msg) msg = "error: 操作非法";
		alert(msg);
		return false;
	}
}
function action_wealth_select(val)
{
	if(val == '1'){
		$("#a_html").html('增加');
		$("#a_type").val("+");
	}else{
		$("#a_html").html('減少');
		$("#a_type").val("-");
	}
}
function action_wealth(title,wid,uid,unit)
{
	var url = get_url('wealth','action_user','wid='+wid+"&uid="+uid);
	$.dialog.open(url,{
		'title':p_lang('會員{title}操作',{'title':title}),
		'lock':true,
		'width':'500px',
		'height':'200px',
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
}


function show_wealth_log(title,wid,uid)
{
	var url = get_url('wealth','log','wid='+wid+"&uid="+uid);
	$.dialog.open(url,{
		'title':title+p_lang('日誌'),
		'lock':true,
		'width':'500px',
		'height':'400px',
		'ok':function(){
			return true;
		},
		'okVal':'關閉'
	});
}

