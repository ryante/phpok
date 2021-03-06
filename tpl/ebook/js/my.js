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
        $('#lib-list3').css("display","none");
        $('#lib-list2').css("display","none");
        $('#lib-list1').css("display","block");
    })

    $('#list_view_2').click(function () {
        $('#lib-list1').css("display","none");
        $('#lib-list2').css("display","block");
        $('#lib-list3').css("display","none");
    })

    $('#list_view_3').click(function () {
        $('#lib-list1').css("display","none");
        $('#lib-list2').css("display","none");
        $('#lib-list3').css("display","block");
    })

    $("#lib-list3 .layui-table tr").click(function(){
        window.location.href = "home.php?f=book&doc_id=" + $(this).data('id');
    })


    $('#book_search').click(function () {
        var searchRange = $('#search_range').val();
        var searchTitle = $('#search_title').val();
        var docId = $('#doc_id').val();
        var src = $('.layui-show iframe').attr('src') + '&keyword=' + searchTitle;
        if (searchRange == 1) {
            $('.layui-show iframe').attr('src', src);
        } else {
            window.location.href = "home.php?f=book_search&doc_id=" + docId + "&search_range=" + searchRange + "&keyword=" + searchTitle;
        }
    })
	
	$('.tag-list a').hover(function(event){
		if ($(this).parent().hasClass('no-border')) {
			return;
		} 
		if ($(this).data('tip') == '' || $(this).data('tip') == 0) {
			return;
		}
		e = event.target;
		subtips = layer.tips($(this).data('tip'), e, {tips:[1,'#393D49']});
	}, function(){
		layer.close(subtips);	
	});



})



// 文献列表
var listTabPages = new Array();
listTabPages[1] = 1;
listTabPages[2] = 1;
listTabPages[3] = 1;
var maxPage = $('#total_docs').val();
$(function(){
    $('.list1-page-1').show();
    $('.list2-page-1').show();
    $('.list3-page-1').show();
    $('.view-more').click(function () {
        tab = $(this).data('tab');
        listTabPages[tab]++;
        $('.list'+tab+'-page-'+listTabPages[tab]).show();
        if (listTabPages[tab] >=  maxPage) {
            $(this).parent().remove();
        }
    })
})

//搜索项
var addClickTimes = 0;
layui.use('form', function(){
    var form = layui.form;//只有执行了这一步，部分表单元素才会自动修饰成功
    // 初始化 
    module = $('#search-module-list').val();
    fieldHtml = $('#search-field-tpl-'+module).html();
    $('.search-field-list').html(fieldHtml);

    if (searchField != "") {
        $('.search-field-list').each(function(key, val){
            field = searchField[key];
            if (field.length == 0) {
                return;
            }
            $('.search-field-list:eq(' + key + ') option[value="' + field + '"]').attr("selected", "selected");
        });
    }

    form.render('select', 'condition-search');
    // end
    // 新增搜索项
    $('#add-search-item').click(function () {
        if (addClickTimes >= 10) {
            layer.msg('已超過系統限制');
            return;
        }
        appendHtml = $('#search-item-tpl').html().trim();
        module = $('#search-module-list').val();
        fieldHtml = $('#search-field-tpl-'+module).html();
        appendHtml = appendHtml.replace('search-field-tpl', fieldHtml);
        $('#append-search-item').append(appendHtml);
        addClickTimes++;
        form.render('select', 'condition-search');
    });
    // 移除搜索项
    $('#remove-search-item').click(function(){
       $('#append-search-item').html('');
       addClickTimes = 0;
        form.render('select', 'condition-search');
    })
    //文库选项选择
    form.on('select(search-module-list)', function (data) {
        $('.search-field-list').each(function(){
            html = $('#search-field-tpl-'+data.value).html();
            $('.search-field-list').html(html);
            form.render('select', 'condition-search');
        })
    });
});

function tagLink(id) {
	window.location.href="home.php?f=docs&tag_id=" + id;
}

function fullScreen() {
    var elem = document.body;
    if (elem.webkitRequestFullScreen) {
        elem.webkitRequestFullScreen();
    } else if (elem.mozRequestFullScreen) {
        elem.mozRequestFullScreen();
    } else if (elem.requestFullScreen) {
        elem.requestFullscreen();
    } else {
        notice.notice_show("浏览器不支持全屏API或已被禁用", null, null, null, true, true);
    }
}

function exitFullScreen() {
    var elem = document;
    if (elem.webkitCancelFullScreen) {
        elem.webkitCancelFullScreen();
    } else if (elem.mozCancelFullScreen) {
        elem.mozCancelFullScreen();
    } else if (elem.cancelFullScreen) {
        elem.cancelFullScreen();
    } else if (elem.exitFullscreen) {
        elem.exitFullscreen();
    } else {
        notice.notice_show("浏览器不支持全屏API或已被禁用", null, null, null, true, true);
    }
}

