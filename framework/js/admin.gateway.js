/**
 * 用於後臺的閘道器路由涉及到的JS
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2016年11月17日
**/
;(function($){
	$.admin_gateway = {
		add:function(id)
		{
			var url = get_url('gateway','getlist','id='+id);
			$.phpok.json(url,function(rs){
				if(!rs.status || (rs.status && rs.status == 'error')){
					var tip = rs.info ? rs.info : rs.content;
					$.dialog.alert(tip);
					return false;
				}
				var content = '<select id="code">';
				for(var	i in rs.content){
					content	+= '<option	value="'+rs.content[i].id+'" data-title="'+rs.content[i].title+'">'+rs.content[i].title;
					if(rs.content[i].note){
						content	+= ' / '+rs.content[i].note+'';
					}
					content	+= '</option>';
				}
				content += '</select>';
				var obj = $.dialog({
					'title': p_lang('閘道器選擇器'),
					'lock':true,
					'content':content,
					'cancel':true,
					'okVal':p_lang('提交'),
					'ok':function(){
						var	code = $("#code").val();
						var text = $("#code").find('option:selected').attr('data-title');
						var	url	= get_url('gateway','set','type='+id+"&code="+code);
						$.win(p_lang('閘道器路由')+"_"+text,url);
						obj.close();
						return true;
					}
				});
				return true;
			});
		},
		set_default:function(id)
		{
			var url = get_url('gateway','default','id='+id);
			var rs = $.phpok.json(url,function(rs){
				if(!rs.status || (rs.status && rs.status == 'error')){
					var tip = rs.info ? rs.info : rs.content;
					$.dialog.alert(tip);
					return false;
				}
				$.phpok.reload();
				return true;
			});
		},
		taxis:function(val,id)
		{
			$.phpok.json(get_url('gateway','sort','sort['+id+']='+val),function(rs){
				if(!rs.status || (rs.status && rs.status == 'error')){
					var tip = rs.info ? rs.info : rs.content;
					$.dialog.alert(tip);
					return false;
				}
				$.phpok.reload();
				return true;
			});
		},
		save:function()
		{
			var title = $("#title").val();
			if(!title){
				$.dialog.alert('名稱不能為空');
				return false;
			}
			$("#post_save").ajaxSubmit({
				'url':get_url('gateway','save'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						var id = $("#id").val();
						var tip = (id && id != 'undefined') ? p_lang('編輯閘道器資訊成功') : p_lang('新增閘道器資訊成功');
						$.dialog.alert(tip,function(){
							$.admin.reload(get_url('gateway'));
							$.admin.close(get_url('gateway'));
						},'succeed');
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				}
			});
			return false;
		}
	}
})(jQuery);

function update_taxis(val,id)
{

}
function update_status(id,val)
{
	if(val == 1){
		$.dialog.confirm('確定要關閉這個閘道器嗎？',function(){
			var url = get_url('gateway','status','id='+id+"&status=0");
			var rs = $.phpok.json(url);
			if(rs && rs.status == 'ok'){
				$.phpok.reload();
			}else{
				$.dialog.alert(rs.content);
				return false;
			}
		});
	}else{
		var url = get_url('gateway','status','id='+id+"&status=1");
		var rs = $.phpok.json(url);
		if(rs && rs.status == 'ok'){
			$.phpok.reload();
		}else{
			$.dialog.alert(rs.content);
			return false;
		}
	}
}

function delete_it(id,title)
{
	$.dialog.confirm('確定要刪除閘道器：<span class="red">'+title+"</span> 嗎？刪除後是不能恢復的",function(){
		var url = get_url('gateway','delete','id='+id);
		var rs = $.phpok.json(url);
		if(rs.status == 'ok'){
			$.phpok.reload();
		}else{
			$.dialog.alert(rs.content);
			return false;
		}
	});
}

function gateway_extmanage(id,manageid,type)
{
	var url = get_url('gateway','extmanage','id='+id+"&manageid="+manageid);
	if(type == 'ajax'){
		var rs = $.phpok.json(url);
		if(rs.status){
			$.dialog.alert(rs.info,function(){
				return true
			},'succeed');
		}else{
			$.dialog.alert(rs.info);
		}
	}else{
		$.dialog.open(url,{
			'title':'閘道器路由管理 #'+id,
			'lock':true,
			'width':'680px',
			'height':'500px'
		});
	}
}

$(document).ready(function(){
	$("div[name=taxis]").click(function(){
		var oldval = $(this).text();
		var id = $(this).attr('data');
		$.dialog.prompt(p_lang('請填寫新的排序'),function(val){
			if(val != oldval){
				$.admin_gateway.taxis(val,id);
			}
		},oldval);
	});
});