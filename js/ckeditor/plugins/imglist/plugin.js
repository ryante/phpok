(function(){
	var a= { 
        exec:function(editor){
	        editor.config.phpok_Dialog_OBJ.open(editor.config.phpok_Images_URL,{
		        'title':'圖片庫',
				'lock':true,
				'width':'80%',
				'height':'70%',
				'ok':function(){
					var iframe = this.iframe.contentWindow;
					if (!iframe.document.body) {
						alert('iframe還沒載入完畢呢');
						return false;
					};
					iframe.save(editor);
					return false;
				},
				'okVal':'提交',
				'cancel':true
	        });
        }
    };
	CKEDITOR.plugins.add('imglist', {
		allowedContent: 'img[alt,!src,width,height,data-width,data-height]{border-style,border-width,float,height,margin,margin-bottom,margin-left,margin-right,margin-top,width}',
		init: function(editor) {
			editor.addCommand('imglist', a);
			editor.ui.addButton('imglist', {
				label: "圖片庫",
				icon: this.path+'images/imgs.png',
				command: 'imglist'
			});
		}
	});
})();
