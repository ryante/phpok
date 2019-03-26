/**
 * 全域性配置頁
 * @作者 qinggan <admin@phpok.com>
 * @版權 2008-2018 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2018年10月25日
**/

function insert_input(val,id,space)
{
	if(!id || id == 'undefined'){
		id = 'rule';
	}
	if(!space || space == 'undefined'){
		space = '';
	}
	var info = $("#"+id).val();
	if(info){
		val = info + space +val;
	}
	$("#"+id).val(val);
}


$(document).ready(function(){
	layui.use(['layer','form','laydate'],function () {
		let form = layui.form;
		form.on('switch(status)', function(data){
			let id = $(this).attr('data');
			if (data.elem.checked) {
				$('#'+id).hide();
			}else{
				$('#'+id).show();
			}
		});
	});
});