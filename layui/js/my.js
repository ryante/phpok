$(function(){
	$('#more_search_btn').click(function(){
		var expand = $('#more_search_btn i').hasClass('layui-icon-down');
		if (expand) {
			$('#more_search_text').slideUp(300);
			$('#more_search_btn i').removeClass('layui-icon-down');
			$('#more_search_btn i').addClass('layui-icon-up');
		} else {
			$('#more_search_text').slideDown(300)
			$('#more_search_btn i').removeClass('layui-icon-up');
			$('#more_search_btn i').addClass('layui-icon-down');
		}
		
	})

	$('.layui-form-item .choose-item').click(function(event){
		var checked = $(this).children('div').hasClass('layui-form-checked');
		if (checked) {
			$(this).next().children("[type='checkbox']").each(function(){
				$(this).attr('checked', true);
			});
		} else {
			$(this).next().children("[type='checkbox']").each(function(){
				console.log(this)
				$(this).prop("checked",false);
			});
		}
	})
})
