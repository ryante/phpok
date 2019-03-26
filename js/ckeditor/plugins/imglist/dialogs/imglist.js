/**
 * 相簿JS
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html 開源授權協議：GNU Lesser General Public License
 * @時間 2018年10月12日
**/
CKEDITOR.dialog.add('imglist', function(editor){
    var escape = function(value){
        return value;
    };
    return {
        title: '附件',
        resizable: CKEDITOR.DIALOG_RESIZE_BOTH,
        minWidth: 300,
        minHeight: 80,
        contents: [{
            id: 'cb',
            name: 'cb',
            label: 'cb',
            title: 'cb',
            elements: [{
                type: 'text',
                label: '請輸入日期控制元件名稱',
                id: 'lang',
                required: true,
            },{
                type:'html',
                html:'<span>說明：日曆控制元件選擇的日期、時間將回填到該輸入框中。</span>'
            }]
        }],
        onOk: function(){
            lang = this.getValueOf('cb', 'lang');
            editor.insertHtml("<p>" + lang + "</p>");
        },
        onLoad: function(){
        }
    };
});