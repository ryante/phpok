/**
 * 專案編輯時有效處理
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2018年03月10日
**/
function cate_add(title)
{
	var url = get_url('cate',"add");
	$.dialog.open(url,{
		"title":title,
		"width":"700px",
		"height":"300px",
		"lock":true,
		"win_max":false,
		"win_min":false,
		'move':false
	});
}

function set_biz()
{
	var status = $("#is_biz").is(':checked');
	if(status){
		$("#use_biz_setting").show();
	}else{
		$("#use_biz_setting").hide();
	}
}

function set_post_status()
{
	var status = $("#post_status").is(':checked');
	if(status){
		$("#email_set_post_status").show();
		$("li[name=f_post]").show();
	}else{
		$("#email_set_post_status").hide();
		$("li[name=f_post]").find('input').attr("checked",false);
		$("li[name=f_post]").hide();
	}
}

function set_comment_status()
{
	var status = $("#comment_status").is(':checked');
	if(status){
		$("#email_set_comment_status").show();
		$("li[name=f_reply]").show();
	}else{
		$("#email_set_comment_status").hide();
		$("li[name=f_reply]").find('input').attr("checked",false);
		$("li[name=f_reply]").hide();
	}
}

function refresh_catelist()
{
	$.phpok.json(get_url("project","rootcate"),function(rs){
		if(rs.status == "ok"){
			var info = '<option value="0">'+p_lang('不關聯分類')+'</option>';
			var lst = rs.content;
			for(var i in lst){
				info += '<option value="'+lst[i]['id']+'">'+lst[i]['title']+'</option>';
			}
			$("#cate").html(info);
		}
	});
}

function update_show_select(val)
{
	if(val && val != 'undefined' && val != '0'){
		$("#cate_multiple_set").show();
	}else{
		$("#cate_multiple_set").hide();
	}
}

$(document).ready(function(){
	$.admin_project.module_change($("#module")[0]);
	layui.use('form',function(){
		var form = layui.form;
		form.render();
		form.on('checkbox', function(data){
			if(data.elem.checked){
				$(data.elem).attr("checked",true);
			}else{
				$(data.elem).removeAttr("checked");
			}
			if(data.elem.id == 'post_status'){
				set_post_status();
			}
			if(data.elem.id == 'comment_status'){
				set_comment_status();
			}
			if(data.elem.id == 'is_biz'){
				set_biz();
			}
		});
	})
});