/**
 * @license Copyright (c) 2003-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	config.toolbar_Basic = [
    	['Source','Preview','RemoveFormat','Image','-','Bold', 'Italic', 'Underline','Strike','-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink', '-', 'About']
    ];

    //
	config.removeDialogTabs = 'link:advanced';

	//讀言包
	config.language = 'zh-cn';
	
	//背景顏色
	//config.uiColor = '#fff';
	
	//高度
	config.height = 300;
	
	//工具欄（基礎'Basic'、全能'Full'、自定義）plugins/toolbar/plugin.js
    config.toolbar = 'Full';
    
    //工具欄是否可以被收縮
	config.toolbarCanCollapse = false;
	
	config.image_previewText = 'PHPOK企業站系統（下述稱“系統”或“本系統”）是一套使用PHP語言及MySQL資料庫編寫的企業網站建設系統，基於LGPL協議開源授權！';
	
	config.resize_enabled = false;
	config.resize_maxHeight = 3000;
	config.toolbarStartupExpanded = true;
	
	//當提交包含有此編輯器的表單時，是否自動更新元素內的資料
	config.autoUpdateElement =true;
	
	// 設定是使用絕對目錄還是相對目錄，為空為相對目錄
	config.baseHref = '';

	// 編輯器的z-index值
	config.baseFloatZIndex = 900;

	//設定快捷鍵
    config.keystrokes = [
    	[CKEDITOR.ALT + 121 /*F10*/ , 'toolbarFocus'], //獲取焦點
    	[CKEDITOR.ALT + 122 /*F11*/ , 'elementsPathFocus'], //元素焦點

    	[CKEDITOR.SHIFT + 121 /*F10*/ , 'contextMenu'], //文字選單

    	[CKEDITOR.CTRL + 90 /*Z*/ , 'undo'], //撤銷
    	[CKEDITOR.CTRL + 89 /*Y*/ , 'redo'], //重做
    	[CKEDITOR.CTRL + CKEDITOR.SHIFT + 90 /*Z*/ , 'redo'], //

    	[CKEDITOR.CTRL + 76 /*L*/ , 'link'], //連結

    	[CKEDITOR.CTRL + 66 /*B*/ , 'bold'], //粗體
    	[CKEDITOR.CTRL + 73 /*I*/ , 'italic'], //斜體
    	[CKEDITOR.CTRL + 85 /*U*/ , 'underline'], //下劃線

    	[CKEDITOR.ALT + 109 /*-*/ , 'toolbarCollapse']
    ];

    //設定快捷鍵 可能與瀏覽器快捷鍵衝突plugins/keystrokes/plugin.js.
    config.blockedKeystrokes = [
    	CKEDITOR.CTRL + 66 /*B*/ ,
    	CKEDITOR.CTRL + 73 /*I*/ ,
    	CKEDITOR.CTRL + 85 /*U*/
    ];

    //設定編輯內元素的背景色的取值
    config.colorButton_backStyle = {
    	element: 'span',
    	styles: {
    		'background-color': '#(color)'
    	}
    };

    //設定前景色的取值 
    config.colorButton_colors = '000,800000,8B4513,2F4F4F,008080,000080,4B0082,696969,B22222,A52A2A,DAA520,006400,40E0D0,0000CD,800080,808080,F00,FF8C00,FFD700,008000,0FF,00F,EE82EE,A9A9A9,FFA07A,FFA500,FFFF00,00FF00,AFEEEE,ADD8E6,DDA0DD,D3D3D3,FFF0F5,FAEBD7,FFFFE0,F0FFF0,F0FFFF,F0F8FF,E6E6FA,FFF';

    //是否在選擇顏色時顯示“其它顏色”選項plugins/colorbutton/plugin.js
    config.colorButton_enableMore =false;

    //區塊的前景色預設值設定 plugins/colorbutton/plugin.js
    config.colorButton_foreStyle = {
        element : 'span',
       styles : { 'color' : '#(color)' }
    };

	//所需要新增的CSS檔案 在此新增 可使用相對路徑和網站的絕對路徑
    config.contentsCss = './js/ckeditor/contents.css';

	//文字方向
    config.contentsLangDirection ='ltr'; //從左到右

    //是否拒絕本地拼寫檢查和提示 預設為拒絕 目前僅firefox和safari支援plugins/wysiwygarea/plugin.js.
    config.disableNativeSpellChecker =true;

    //進行表格編輯功能 如：新增行或列 目前僅firefox支援plugins/wysiwygarea/plugin.js
    config.disableNativeTableHandles =true; //預設為不開啟

    //是否開啟 圖片和表格 的改變大小的功能config.disableObjectResizing = true;
    config.disableObjectResizing= false //預設為開啟

    //是否對編輯區域進行渲染plugins/editingblock/plugin.js
    config.editingBlock = true;

    //編輯器中回車產生的標籤
    config.enterMode =CKEDITOR.ENTER_P; //可選：CKEDITOR.ENTER_BR或CKEDITOR.ENTER_DIV

    //是否轉換一些難以顯示的字元為相應的HTML字元plugins/entities/plugin.js
    config.entities_greek = true;

    //是否轉換一些拉丁字元為HTMLplugins/entities/plugin.js
    config.entities_latin = true;

    //是否轉換一些特殊字元為ASCII字元 如"This is Chinese:漢語."轉換為"This is Chinese: 漢語."plugins/entities/plugin.js
    config.entities_processNumerical =false;

//預設的字型名 plugins/font/plugin.js
    config.font_defaultLabel = 'Arial';

    //字型編輯時的字符集 可以新增常用的中文字元：宋體、楷體、黑體等plugins/font/plugin.js
    config.font_names = 'Arial;Times NewRoman;Verdana';

    //文字的預設式樣 plugins/font/plugin.js
    config.font_style = {
        element   : 'span',
        styles  : { 'font-family' : '#(family)' },
        overrides : [ { element :'font', attributes : { 'face' : null } } ]
    };

    //字型預設大小 plugins/font/plugin.js
    config.fontSize_defaultLabel = '14px';

    //字型編輯時可選的字型大小 plugins/font/plugin.js
    config.fontSize_sizes='10/10px;11/11px;12/12px;14/14px;16/16px;18/18px;20/20px;22/22px;24/24px;26/26px;28/28px;36/36px;48/48px;72/72px';

    //設定字型大小時 使用的式樣 plugins/font/plugin.js
    config.fontSize_style = {
        element   : 'span',
       styles   : { 'font-size' : '#(size)' },
        overrides : [ {element : 'font', attributes : { 'size' : null } } ]
    };

    //是否強制複製來的內容去除格式plugins/pastetext/plugin.js
    config.forcePasteAsPlainText =false//不去除

   //是否強制用“&”來代替“&amp;”plugins/htmldataprocessor/plugin.js
    config.forceSimpleAmpersand = false;

    //對address標籤進行格式化 plugins/format/plugin.js
    config.format_address = { element : 'address', attributes : { class :'styledAddress' } };

    //對DIV標籤自動進行格式化 plugins/format/plugin.js
    config.format_div = { element : 'div', attributes : { class :'normalDiv' } };

    //對H1標籤自動進行格式化 plugins/format/plugin.js
    config.format_h1 = { element : 'h1', attributes : { class :'contentTitle1' } };

    //對H2標籤自動進行格式化 plugins/format/plugin.js
    config.format_h2 = { element : 'h2', attributes : { class :'contentTitle2' } };

    //對H3標籤自動進行格式化 plugins/format/plugin.js
    config.format_h1 = { element : 'h3', attributes : { class :'contentTitle3' } };

    //對H4標籤自動進行格式化 plugins/format/plugin.js
    config.format_h1 = { element : 'h4', attributes : { class :'contentTitle4' } };

    //對H5標籤自動進行格式化 plugins/format/plugin.js
    config.format_h1 = { element : 'h5', attributes : { class :'contentTitle5' } };

    //對H6標籤自動進行格式化 plugins/format/plugin.js
    config.format_h1 = { element : 'h6', attributes : { class :'contentTitle6' } };

    //對P標籤自動進行格式化 plugins/format/plugin.js
    config.format_p = { element : 'p', attributes : { class : 'normalPara' }};

    //對PRE標籤自動進行格式化 plugins/format/plugin.js
    config.format_pre = { element : 'pre', attributes : { class : 'code'} };

    //用分號分隔的標籤名字 在工具欄上顯示plugins/format/plugin.js
    config.format_tags ='p;h1;h2;h3;h4;h5;h6;pre;address;div';

    //是否使用完整的html編輯模式如使用，其原始碼將包含：<html><body></body></html>等標籤
    config.fullPage = false;

    //是否忽略段落中的空字元 若不忽略 則字元將以表示plugins/wysiwygarea/plugin.js
    config.ignoreEmptyParagraph = true;

    //在清除圖片屬性框中的連結屬性時 是否同時清除兩邊的<a>標籤plugins/image/plugin.js
    config.image_removeLinkByEmptyURL = true;

    //一組用逗號分隔的標籤名稱，顯示在左下角的層次巢狀中plugins/menu/plugin.js.
    config.menu_groups='clipboard,form,tablecell,tablecellproperties,tablerow,tablecolumn,table,anchor,link,image,flash,checkbox,radio,textfield,hiddenfield,imagebutton,button,select,textarea';

    //顯示子選單時的延遲，單位：ms plugins/menu/plugin.js
    config.menu_subMenuDelay = 400;

    //當執行“新建”命令時，編輯器中的內容plugins/newpage/plugin.js
    config.newpage_html = '';

    //當從word裡複製文字進來時，是否進行文字的格式化去除plugins/pastefromword/plugin.js
    config.pasteFromWordIgnoreFontFace = true; //預設為忽略格式

    //是否使用<h1><h2>等標籤修飾或者代替從word文件中貼上過來的內容plugins/pastefromword/plugin.js
    config.pasteFromWordKeepsStructure = false;

    //從word中貼上內容時是否移除格式plugins/pastefromword/plugin.js
    config.pasteFromWordRemoveStyle =false;

    //當輸入：shift+Enter時插入的標籤
    config.shiftEnterMode = CKEDITOR.ENTER_P; //可選：CKEDITOR.ENTER_BR或CKEDITOR.ENTER_DIV
    
	//頁面載入時，編輯框是否立即獲得焦點plugins/editingblock/plugin.js plugins/editingblock/plugin.js.
    config.startupFocus = false;

    //載入時，以何種方式編輯 原始碼和所見即所得 "source"和"wysiwyg"plugins/editingblock/plugin.js.
    config.startupMode ='wysiwyg';

    //載入時，是否顯示框體的邊框plugins/showblocks/plugin.js
    config.startupOutlineBlocks = false;

	//撤銷的記錄步數
    config.undoStackSize =20;

    config.smiley_columns = 12;

    config.smiley_descriptions = [
    	'01.png','02.png','03.png','04.png','05.png','06.png','07.png','08.png','09.png','10.png','11.png','12.png','13.png','14.png','15.png','16.png','17.png','18.png','19.png','20.png','21.png','22.png','23.png','24.png','25.png','26.png','27.png','28.png','29.png','30.png','31.png','32.png','33.png','34.png','35.png','36.png','37.png','38.png','39.png','40.png','41.png','42.png','43.png','44.png','45.png','46.png','47.png','48.png','49.png','50.png','51.png','52.png','53.png','54.png','55.png','56.png','57.png','58.png','59.png','60.png','61.png','62.png','63.png','64.png'
    ];
    config.smiley_path = 'images/emotion/';
    config.smiley_images = [
        '01.png','02.png','03.png','04.png','05.png','06.png','07.png','08.png','09.png','10.png','11.png','12.png','13.png','14.png','15.png','16.png','17.png','18.png','19.png','20.png','21.png','22.png','23.png','24.png','25.png','26.png','27.png','28.png','29.png','30.png','31.png','32.png','33.png','34.png','35.png','36.png','37.png','38.png','39.png','40.png','41.png','42.png','43.png','44.png','45.png','46.png','47.png','48.png','49.png','50.png','51.png','52.png','53.png','54.png','55.png','56.png','57.png','58.png','59.png','60.png','61.png','62.png','63.png','64.png'
    ];

    config.extraAllowedContent = 'img[alt,!src,width,height,data-width,data-height]{border-style,border-width,float,height,margin,margin-bottom,margin-left,margin-right,margin-top,width}'
};
