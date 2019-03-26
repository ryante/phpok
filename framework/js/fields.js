/***********************************************************
	Filename: phpok/js/fields.js
	Note	: 欄位管理中涉及的JS
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-11-26 17:07
***********************************************************/
// 驗證標識串
function check_identifier(is_alert)
{
	var c = $("#identifier").val();
	if(!c)
	{
		$("#identifier_note").addClass("error").html("標識串不能為空！");
		if(is_alert) alert("標識串不能為空！");
		return false;
	}
	//驗證標識串是否符合要求
	if(!$.str.identifier(c))
	{
		alert("標識串不符合系統要求，要求僅支援：字母、數字或下劃線且首字必須為字母");
		return false;
	}
	//通過服務端驗證資料是否存在
	var url = get_url("ajax","exit","filename=field_identifier") + "&identifier="+c;
	var rs = json_ajax(url);
	if(rs.status != "ok")
	{
		$("#identifier_note").addClass("error").html(rs.content);
		if(is_alert) alert(rs.content);
		return false;
	}
	$("#identifier_note").removeClass("error").html("");
	return true;
}

// 驗證標題
function check_title(is_alert)
{
	var c = $("#title").val();
	if(!c)
	{
		$("#title_note").addClass("error").html("名稱不能為空！");
		if(is_alert) alert("名稱不能為空！");
		return false;
	}
	$("#title_note").removeClass("error").html("");
	return true;
}

/* 樣式屬性操作 */
function set_style(act)
{
	var value = $("#form_style").val();
	//去除空格資訊
	if(value && value != "undefined")
	{
		value = value.replace(/ /g,"");
		value = value.toLowerCase();
	}
	if(act == "bold")
	{
		var regexp = new RegExp("font-weight:bold;");
		if(regexp.test(value))
		{
			value = value.replace("font-weight:bold;","");
			$("#css_edit_bold").removeClass("btn_selected").addClass("btn");
		}
		else
		{
			value += "font-weight:bold;";
			$("#css_edit_bold").removeClass("btn").addClass("btn_selected");
		}
		$("#form_style").val(value);
	}
	else if(act == "width")
	{
		var regexp = new RegExp("width:");
		if(regexp.test(value))
		{
			value = value.replace(/width:\w+;/,"");
			$("#css_edit_width").removeClass("btn_selected").addClass("btn");
			$("#form_style").val(value);
		}
		else
		{
			apprise('設定寬度', {'input':true,'textOk':'提交','textCancel':'取消'},function(r){
				if(r && r != false)
				{
					var t_value = r.replace("px","");
					value += "width:"+t_value+"px;";
					$("#form_style").val(value);
					$("#css_edit_width").removeClass("btn").addClass("btn_selected");
				}
			});
		}
	}
	else if(act == "height")
	{
		var regexp = new RegExp("height:");
		if(regexp.test(value))
		{
			value = value.replace(/height:\w+;/,"");
			$("#css_edit_height").removeClass("btn_selected").addClass("btn");
			$("#form_style").val(value);
		}
		else
		{
			apprise('設定高度', {'input':true,'textOk':'提交','textCancel':'取消'},function(r){
				if(r && r != false)
				{
					var t_value = r.replace("px","");
					value += "height:"+t_value+"px;";
					$("#form_style").val(value);
					$("#css_edit_height").removeClass("btn").addClass("btn_selected");
				}
			});
		}
	}
}

function load_style()
{
	var value = $("#form_style").val();
	if(value && value != "undefined")
	{
		//判斷是否有高度屬性
		var regexp = new RegExp("height:");
		if(regexp.test(value))
		{
			$("#css_edit_height").removeClass("btn").addClass("btn_selected");
		}
		else
		{
			$("#css_edit_height").removeClass("btn_selected").addClass("btn");
		}
		//判斷是否有寬度屬性
		var regexp = new RegExp("width:");
		if(regexp.test(value))
		{
			$("#css_edit_width").removeClass("btn").addClass("btn_selected");
		}
		else
		{
			$("#css_edit_width").removeClass("btn_selected").addClass("btn");
		}
		//判斷是否有加粗
		var regexp = new RegExp("font-weight:");
		if(regexp.test(value))
		{
			$("#css_edit_bold").removeClass("btn").addClass("btn_selected");
		}
		else
		{
			$("#css_edit_bold").removeClass("btn_selected").addClass("btn");
		}
	}
	else
	{
		$("#css_edit_bold").removeClass("btn_selected").addClass("btn");
		$("#css_edit_width").removeClass("btn_selected").addClass("btn");
		$("#css_edit_height").removeClass("btn_selected").addClass("btn");
	}
}

function show_form_opt(val)
{
	$("#form_opt_html").hide();
	$("#form_btn_html").hide();
	$("#form_edit_html").hide();
	//要顯示的值
	var list = new Array("radio","checkbox","select","select_multiple","related_multiple","related_single");
	var is_list = false;
	for(var i=0;i<list.length;i++)
	{
		if(list[i] == val)
		{
			is_list = true;
		}
	}
	if(is_list)
	{
		$("#form_opt_html").show();
	}
	if(val == "text")
	{
		$("#form_btn_html").show();
	}
	if(val == "html_editor")
	{
		$("#form_edit_html").show();
	}
}

// 檢查新增的欄位屬性
function field_add_check(tbl_prefix,id)
{
	//判斷名稱是否為空
	var chk_title = check_title(true);
	if(!chk_title) return false;

	if(!id || id == "undefined")
	{
		// 檢測標識串
		var chk_identifier = check_identifier(true);
		if(!chk_identifier) return false;
	}

	//檢測儲存型別
	var field_type = $("#field_type").val();
	var tbl = $("#field_tbl").val();
	if(field_type == "longtext" && (tbl == "ext" || tbl == "blob"))
	{
		alert("目標資料表儲存與設定的欄位型別不一致！超長文字請儲存到："+tbl_prefix+"list_content 或 自定義建立 表中！");
		return false;
	}
	else if(field_type == "longblob" && (tbl == "ext" || tbl == "content"))
	{
		alert("目標資料表儲存與設定的欄位型別不一致！二進位制資訊請儲存到："+tbl_prefix+"list_blob 或 自定義建立 表中！");
		return false;
	}
	else if(field_type != "longblob" && tbl == "blob")
	{
		alert("選擇有錯誤，二進位制資訊不能儲存到表："+tbl_prefix+"list_blob 中！");
		return false;
	}

	return true;
}

//更新排序
function update_taxis()
{
	var url = get_url("fields","taxis");
	var id_string = $.input.checkbox_join();
	if(!id_string || id_string == "undefined")
	{
		alert("沒有指定要更新的排序ID！");
		return false;
	}
	//取得排序值資訊
	var id_list = id_string.split(",");
	var id_leng = id_list.length;
	for(var i=0;i<id_leng;i++)
	{
		var taxis = $("#taxis_"+id_list[i]).val();
		if(!taxis) taxis = "255";
		url += "&taxis["+id_list[i]+"]="+$.str.encode(taxis);
	}
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		alert("排序更新完成！");
		direct(window.location.href);
	}
	else
	{
		alert(rs.content);
		return false;
	}
}

function field_del(id,title)
{
	var qc = confirm("確定要刪除欄位："+title+" ？ 刪除後，已投入使用的欄位不受此影響！");
	if(qc == "0")
	{
		return false;
	}
	var url = get_url("fields","delete");
	url += "&id="+id;
	var rs = json_ajax(url);
	if(rs.status == "ok")
	{
		direct(window.location.href);
	}
	else
	{
		alert(rs.content);
		return false;
	}
}

function fields_goto(val)
{
	var url = get_url("fields");
	if(val && val != 'undefined')
	{
		url += "&type="+val;
	}
	direct(url);
}
