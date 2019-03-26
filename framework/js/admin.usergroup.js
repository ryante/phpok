/**
 * 會員組
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年12月23日
**/
;(function($){
	$.admin_usergroup = {
		del:function(id)
		{
			if(!id || id == 'undefined'){
				$.dialog.alert(p_lang('操作非法'));
				return false;
			}
			$.dialog.confirm(p_lang('確定要刪除此會員組嗎？刪除後是不能恢復的'),function(){
				var url = get_url("usergroup","delete","id="+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.tips(p_lang('會員組刪除成功'),function(){
							$.phpok.reload();
						}).lock();
						return true;
					}
					$.dialog.alert(rs.info);
					return false;
				})
			});
		},
		modify:function(id)
		{
			$.win(p_lang('編輯會員組')+"_#"+id,get_url("usergroup","set","id="+id));
		},
		add:function()
		{
			$.win(p_lang('新增會員組'),get_url("usergroup","set"));
		},
		set_default:function(id,title)
		{
			var tip = p_lang('確定要將組{title}設定為會員預設組嗎?<br />設定成功後，新註冊會員將自定使用此組功能',' <span class="red">'+title+'</span> ');
			$.dialog.confirm(tip,function(){
		        var url = get_url("usergroup","default","id="+id);
		        $.phpok.json(url,function(rs){
			        if(rs.status){
				        $.dialog.tips(p_lang('預設組設定成功'),function(){
					        $.phpok.reload();
				        }).lock();
				        return true;
			        }
			        $.dialog.alert(rs.info);
			        return false;
		        });
		    });
		},
		guest:function(id,title)
		{
			var tip = p_lang("確定要將組{title}設定為遊客組嗎?<br />設定成功後，來訪者將呼叫此組許可權資訊"," <span class='red'>"+title+"</span> ");
			$.dialog.confirm(tip,function(){
				var url = get_url("usergroup","guest","id="+id);
				$.phpok.json(url,function(rs){
					if(rs.status){
				        $.dialog.tips(p_lang('遊客組設定成功'),function(){
					        $.phpok.reload();
				        }).lock();
				        return true;
			        }
			        $.dialog.alert(rs.info);
			        return false;
				});
			});
		},
		status:function(id)
		{
			var val = $("#status_"+id).val();
		    if(val == 1){
			    var tip = p_lang("確定要禁用此會員組資訊嗎?<br />禁用後，該組會員不能登入，請慎用");
		        $.dialog.confirm(tip,function(){
		            var url = get_url("usergroup","status","id="+id+"&status=0");
		            $.phpok.json(url,function(rs){
			            if(rs.status){
				            $.dialog.tips(p_lang('會員組已禁用'),function(){
								$.phpok.reload();
							}).lock();
							return true;
			            }
			            $.dialog.alert(rs.info);
			            return false;
		            });
		        });
		        return true;
		    }
		    var url = get_url("usergroup","status","id="+id+"&status=1");
		    $.phpok.json(url,function(rs){
			    if(rs.status){
		            $.dialog.tips(p_lang('會員組啟用成功'),function(){
						$.phpok.reload();
					}).lock();
					return true;
	            }
	            $.dialog.alert(rs.info);
	            return false;
		    });
		},
		setok:function()
		{
			$("#post_save").ajaxSubmit({
				'url':get_url('usergroup','setok'),
				'type':'post',
				'dataType':'json',
				'success':function(rs){
					if(rs.status){
						$.dialog.alert(rs.info,function(){
							$.admin.reload(get_url('usergroup'));
							$.admin.close(get_url('usergroup'));
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

