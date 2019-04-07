$(function(){
	$('#more_search_btn').click(function(){
		var expand = $('#more_search_btn i').hasClass('layui-icon-right');
		if (expand) {
			$('#more_search_text').slideDown(300)
			$('#more_search_btn i').removeClass('layui-icon-right');
			$('#more_search_btn i').addClass('layui-icon-down');
		} else {
			$('#more_search_text').slideUp(300);
			$('#more_search_btn i').removeClass('layui-icon-down');
			$('#more_search_btn i').addClass('layui-icon-right');
		}
		
	})

    $('.layui-form-item .choose-item').click(function(event){
        var checked = $(this).children('div').hasClass('layui-form-checked');
        if (checked) {
            $(this).next().children("[type='checkbox']").each(function(){
                $(this).attr('checked', true);
                $(this).next().addClass('layui-form-checked');
            });
        } else {
            $(this).next().children("[type='checkbox']").each(function(){
                $(this).removeAttr('checked');
                $(this).next().removeClass('layui-form-checked');
            });
        }
    })

    $('#list_view_1').click(function () {
        $('#lib-list2').css("display","none");
        $('#lib-list1').css("display","block");
    })

    $('#list_view_2').click(function () {
        $('#lib-list1').css("display","none");
        $('#lib-list2').css("display","block");
    })



    $('#book_search').click(function () {
        var searchRange = $('#search_range').val();
        var searchTitle = $('#search_title').val();
        var docId = $('#doc_id').val();
        var src = $('.layui-show iframe').attr('src') + '&keyword=' + searchTitle;
        if (searchRange == 1) {
            $('.layui-show iframe').attr('src', src);
        } else {
            window.location.href = "index.php?f=book_search&doc_id=" + docId + "&search_range=" + searchRange + "&keyword=" + searchTitle;
        }
    })


})

