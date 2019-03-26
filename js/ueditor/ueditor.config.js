/**
 * ueditor完整配置項
 * 可以在這裡配置整個編輯器的特性
 */
/**************************提示********************************
 * 所有被註釋的配置項均為UEditor預設值。
 * 修改預設配置請首先確保已經完全明確該引數的真實用途。
 * 主要有兩種修改方案，一種是取消此處註釋，然後修改成對應引數；另一種是在例項化編輯器時傳入對應引數。
 * 當升級編輯器時，可直接使用舊版配置檔案替換新版配置檔案,不用擔心舊版配置檔案中因缺少新功能所需的引數而導致指令碼報錯。
 **************************提示********************************/

(function () {

    /**
     * 編輯器資原始檔根路徑。它所表示的含義是：以編輯器例項化頁面為當前路徑，指向編輯器資原始檔（即dialog等資料夾）的路徑。
     * 鑑於很多同學在使用編輯器的時候出現的種種路徑問題，此處強烈建議大家使用"相對於網站根目錄的相對路徑"進行配置。
     * "相對於網站根目錄的相對路徑"也就是以斜槓開頭的形如"/myProject/ueditor/"這樣的路徑。
     * 如果站點中有多個不在同一層級的頁面需要例項化編輯器，且引用了同一UEditor的時候，此處的URL可能不適用於每個頁面的編輯器。
     * 因此，UEditor提供了針對不同頁面的編輯器可單獨配置的根路徑，具體來說，在需要例項化編輯器的頁面最頂部寫上如下程式碼即可。當然，需要令此處的URL等於對應的配置。
     * window.UEDITOR_HOME_URL = "/xxxx/xxxx/";
     */
    var URL = window.UEDITOR_HOME_URL || getUEBasePath();

    /**
     * 配置項主體。注意，此處所有涉及到路徑的配置別遺漏URL變數。
     */
    window.UEDITOR_CONFIG = {

        //為編輯器例項新增一個路徑，這個不能被註釋
        UEDITOR_HOME_URL: URL

        // 伺服器統一請求介面路徑
        , serverUrl: URL + "../../api.php?c=ueditor"

        //工具欄上的所有的功能按鈕和下拉框，可以在new編輯器的例項時選擇自己需要的從新定義
        , toolbars: [[
            'fullscreen', 'source', '|','insertpbefore','insertpafter', '|', 'undo', 'redo', '|',
            'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
            'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
            'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
            'directionalityltr', 'directionalityrtl', 'indent', '|',
            'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
            'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
            'insertimage', 'insertvideo','simpleupload'
            , 'attachment', 'map', 'insertframe', 'insertcode', 'pagebreak', 'background', '|',
            'horizontal', 'date', 'time', 'spechars', '|',
            'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', '|',
            'print', 'preview', 'drafts', 'help'
        ]]
        //當滑鼠放在工具欄上時顯示的tooltip提示,留空支援自動多語言配置，否則以配置值為準
        //,labelMap:{
        //    'insertpbefore':'', 'undo':''
        ///}

        //語言配置項,預設是zh-cn。有需要的話也可以使用如下這樣的方式來自動多語言切換，當然，前提條件是lang資料夾下存在對應的語言檔案：
        //lang值也可以通過自動獲取 (navigator.language||navigator.browserLanguage ||navigator.userLanguage).toLowerCase()
        //,lang:"zh-cn"
        //,langPath:URL +"lang/"

        //主題配置項,預設是default。有需要的話也可以使用如下這樣的方式來自動多主題切換，當然，前提條件是themes資料夾下存在對應的主題檔案：
        //現有如下面板:default
        //,theme:'default'
        //,themePath:URL +"themes/"

        //,zIndex : 900     //編輯器層級的基數,預設是900

        //針對getAllHtml方法，會在對應的head標籤中增加該編碼設定。
        //,charset:"utf-8"

        //若例項化編輯器的頁面手動修改的domain，此處需要設定為true
        //,customDomain:false

        //常用配置專案
        //,isShow : true    //預設顯示編輯器

        //,textarea:'editorValue' // 提交表單時，伺服器獲取編輯器提交內容的所用的引數，多例項時可以給容器name屬性，會將name給定的值最為每個例項的鍵值，不用每次例項化的時候都設定這個值

        //,initialContent:'歡迎使用ueditor!'    //初始化編輯器的內容,也可以通過textarea/script給值，看官網例子

        //,autoClearinitialContent:true //是否自動清除編輯器初始內容，注意：如果focus屬性設定為true,這個也為真，那麼編輯器一上來就會觸發導致初始化的內容看不到了

        //,focus:false //初始化時，是否讓編輯器獲得焦點true或false

        //如果自定義，最好給p標籤如下的行高，要不輸入中文時，會有跳動感
        //,initialStyle:'p{line-height:1em}'//編輯器層級的基數,可以用來改變字型等

        //,iframeCssUrl: URL + '/themes/iframe.css' //給編輯區域的iframe引入一個css檔案

        //indentValue
        //首行縮排距離,預設是2em
        //,indentValue:'2em'

        //,initialFrameWidth:1000  //初始化編輯器寬度,預設1000
        //,initialFrameHeight:320  //初始化編輯器高度,預設320

        //,readonly : false //編輯器初始化結束後,編輯區域是否是隻讀的，預設是false

        //,autoClearEmptyNode : true //getContent時，是否刪除空的inlineElement節點（包括巢狀的情況）

        //,fullscreen : false //是否開啟初始化時即全屏，預設關閉

        //,imagePopup:true      //圖片操作的浮層開關，預設開啟

        //,autoSyncData:true //自動同步編輯器要提交的資料

        //貼上只保留標籤，去除標籤所有屬性
        //,retainOnlyLabelPasted: false

        //,pasteplain:false  //是否預設為純文字貼上。false為不使用純文字貼上，true為使用純文字貼上
        //純文字貼上模式下的過濾規則
        //'filterTxtRules' : function(){
        //    function transP(node){
        //        node.tagName = 'p';
        //        node.setStyle();
        //    }
        //    return {
        //        //直接刪除及其位元組點內容
        //        '-' : 'script style object iframe embed input select',
        //        'p': {$:{}},
        //        'br':{$:{}},
        //        'div':{'$':{}},
        //        'li':{'$':{}},
        //        'caption':transP,
        //        'th':transP,
        //        'tr':transP,
        //        'h1':transP,'h2':transP,'h3':transP,'h4':transP,'h5':transP,'h6':transP,
        //        'td':function(node){
        //            //沒有內容的td直接刪掉
        //            var txt = !!node.innerText();
        //            if(txt){
        //                node.parentNode.insertAfter(UE.uNode.createText(' &nbsp; &nbsp;'),node);
        //            }
        //            node.parentNode.removeChild(node,node.innerText())
        //        }
        //    }
        //}()

        //,allHtmlEnabled:false //提交到後臺的資料是否包含整個html字串

        //insertorderedlist
        //有序列表的下拉配置,值留空時支援多語言自動識別，若配置值，則以此值為準
        //,'insertorderedlist':{
        //     //系統自帶
        //     'decimal' : '' ,         //'1,2,3...'
        //     'lower-alpha' : '' ,    // 'a,b,c...'
        //     'lower-roman' : '' ,    //'i,ii,iii...'
        //     'upper-alpha' : '' , lang   //'A,B,C'
        //     'upper-roman' : ''      //'I,II,III...'
        //}

        //insertunorderedlist
        //無序列表的下拉配置，值留空時支援多語言自動識別，若配置值，則以此值為準
        //,insertunorderedlist : { //自定的樣式
        //    'dash' :'— 破折號', //-破折號
        //    'dot':' 。 小圓圈', //系統自帶
        //    'circle' : '',  // '○ 小圓圈'
        //    'disc' : '',    // '● 小圓點'
        //    'square' : ''   //'■ 小方塊'
        //}
        //,listDefaultPaddingLeft : '30'//預設的左邊縮排的基數倍
        //,listiconpath : 'http://bs.baidu.com/listicon/'//自定義標號的路徑
        //,maxListLevel : 3 //限制可以tab的級數, 設定-1為不限制

        //,autoTransWordToList:false  //禁止word中貼上進來的列表自動變成列表標籤

        //fontfamily
        //字型設定 label留空支援多語言自動切換，若配置，則以配置值為準
        ,'fontfamily':[
			{ label:'微軟雅黑',name:'microsoftyahei',val:'微軟雅黑,Microsoft YaHei'},
			{ label:'宋體',name:'songti',val:'宋體,SimSun'},
			{ label:'楷體',name:'kaiti',val:'楷體,楷體_GB2312, SimKai'},
			{ label:'黑體',name:'heiti',val:'黑體, SimHei'},
			{ label:'隸書',name:'lishu',val:'隸書, SimLi'},
			{ label:'Arial',name:'arial',val:'arial, helvetica,sans-serif'},
			{ label:'Arial Black',name:'arialBlack',val:'arial black,avant garde'},
			{ label:'Comic Sans Ms',name:'comicSansMs',val:'comic sans ms'},
			{ label:'Impact',name:'impact',val:'impact,chicago'},
			{ label:'Times New Roman',name:'timesNewRoman',val:'times new roman'}
		]

        //fontsize
        //字號
        // ,'fontsize':[10, 11, 12, 14, 16, 18, 20, 24, 36, 40]

        //paragraph
        //段落格式 值留空時支援多語言自動識別，若配置，則以配置值為準
        //,'paragraph':{'p':'', 'h1':'', 'h2':'', 'h3':'', 'h4':'', 'h5':'', 'h6':''}

        //rowspacingtop
        //段間距 值和顯示的名字相同
        //,'rowspacingtop':['5', '10', '15', '20', '25']

        //rowspacingBottom
        //段間距 值和顯示的名字相同
        //,'rowspacingbottom':['5', '10', '15', '20', '25']

        //lineheight
        //行內間距 值和顯示的名字相同
        //,'lineheight':['1', '1.5','1.75','2', '3', '4', '5']

        //customstyle
        //自定義樣式，不支援國際化，此處配置值即可最後顯示值
        //block的元素是依據設定段落的邏輯設定的，inline的元素依據BIU的邏輯設定
        //儘量使用一些常用的標籤
        //引數說明
        //tag 使用的標籤名字
        //label 顯示的名字也是用來標識不同型別的識別符號，注意這個值每個要不同，
        //style 新增的樣式
        //每一個物件就是一個自定義的樣式
        //,'customstyle':[
        //    {tag:'h1', name:'tc', label:'', style:'border-bottom:#ccc 2px solid;padding:0 4px 0 0;text-align:center;margin:0 0 20px 0;'},
        //    {tag:'h1', name:'tl',label:'', style:'border-bottom:#ccc 2px solid;padding:0 4px 0 0;margin:0 0 10px 0;'},
        //    {tag:'span',name:'im', label:'', style:'font-style:italic;font-weight:bold'},
        //    {tag:'span',name:'hi', label:'', style:'font-style:italic;font-weight:bold;color:rgb(51, 153, 204)'}
        //]


        //elementPathEnabled
        //是否啟用元素路徑，預設是顯示
        //,elementPathEnabled : true

        //wordCount
        //,wordCount:true          //是否開啟字數統計
        //,maximumWords:10000       //允許的最大字元數
        //字數統計提示，{#count}代表當前字數，{#leave}代表還可以輸入多少字元數,留空支援多語言自動切換，否則按此配置顯示
        //,wordCountMsg:''   //當前已輸入 {#count} 個字元，您還可以輸入{#leave} 個字元
        //超出字數限制提示  留空支援多語言自動切換，否則按此配置顯示
        //,wordOverFlowMsg:''    //<span style="color:red;">你輸入的字元個數已經超出最大允許值，伺服器可能會拒絕儲存！</span>

        //tab
        //點選tab鍵時移動的距離,tabSize倍數，tabNode什麼字元做為單位
        //,tabSize:4
        //,tabNode:'&nbsp;'

        //removeFormat
        //清除格式時可以刪除的標籤和屬性
        //removeForamtTags標籤
        //,removeFormatTags:'b,big,code,del,dfn,em,font,i,ins,kbd,q,samp,small,span,strike,strong,sub,sup,tt,u,var'
        //removeFormatAttributes屬性
        //,removeFormatAttributes:'class,style,lang,width,height,align,hspace,valign'

        //undo
        //可以最多回退的次數,預設20
        //,maxUndoCount:20
        //當輸入的字元數超過該值時，儲存一次現場
        //,maxInputCount:1

        //autoHeightEnabled
        // 是否自動長高,預設true
        //,autoHeightEnabled:true

        //scaleEnabled
        //是否可以拉伸長高,預設true(當開啟時，自動長高失效)
        //,scaleEnabled:false
        //,minFrameWidth:800    //編輯器拖動時最小寬度,預設800
        //,minFrameHeight:220  //編輯器拖動時最小高度,預設220

        //autoFloatEnabled
        //是否保持toolbar的位置不動,預設true
        //,autoFloatEnabled:true
        //浮動時工具欄距離瀏覽器頂部的高度，用於某些具有固定頭部的頁面
        //,topOffset:30
        //編輯器底部距離工具欄高度(如果引數大於等於編輯器高度，則設定無效)
        //,toolbarTopOffset:400

        //設定遠端圖片是否抓取到本地儲存
        //,catchRemoteImageEnable: true //設定是否抓取遠端圖片

        //pageBreakTag
        //分頁識別符號,預設是_ueditor_page_break_tag_
        //,pageBreakTag:'_ueditor_page_break_tag_'

        //tableDragable
        //表格是否可以拖拽
        //,tableDragable: true



        //編輯器初始化完成後是否進入原始碼模式，預設為否。
        //,sourceEditorFirst:false

        //iframeUrlMap
        //dialog內容的路徑 ～會被替換成URL,垓屬性一旦開啟，將覆蓋所有的dialog的預設路徑
        //,iframeUrlMap:{
        //    'anchor':'~/dialogs/anchor/anchor.html',
        //}

        //allowLinkProtocol 允許的連結地址，有這些字首的連結地址不會自動新增http
        //, allowLinkProtocols: ['http:', 'https:', '#', '/', 'ftp:', 'mailto:', 'tel:', 'git:', 'svn:']

        //預設過濾規則相關配置專案
        //,disabledTableInTable:true  //禁止表格巢狀
        //,allowDivTransToP:true      //允許進入編輯器的div標籤自動變成p標籤
        //,rgb2Hex:true               //預設產出的資料中的color自動從rgb格式變成16進位制格式

		// xss 過濾是否開啟,inserthtml等操作
		,xssFilterRules: true
		//input xss過濾
		,inputXssFilter: true
		//output xss過濾
		,outputXssFilter: true
		// xss過濾白名單 名單來源: https://raw.githubusercontent.com/leizongmin/js-xss/master/lib/default.js
		,whitList: {
			a:      ['target', 'href', 'title', 'class', 'style','name','id'],
			abbr:   ['title', 'class', 'style'],
			address: ['class', 'style'],
			area:   ['shape', 'coords', 'href', 'alt'],
			article: [],
			aside:  [],
			audio:  ['autoplay', 'controls', 'loop', 'preload', 'src', 'class', 'style'],
			b:      ['class', 'style'],
			bdi:    ['dir'],
			bdo:    ['dir'],
			big:    [],
			blockquote: ['cite', 'class', 'style'],
			br:     [],
			caption: ['class', 'style'],
			center: [],
			cite:   [],
			code:   ['class', 'style'],
			col:    ['align', 'valign', 'span', 'width', 'class', 'style'],
			colgroup: ['align', 'valign', 'span', 'width', 'class', 'style'],
			dd:     ['class', 'style'],
			del:    ['datetime'],
			details: ['open'],
			div:    ['class', 'style'],
			dl:     ['class', 'style'],
			dt:     ['class', 'style'],
			em:     ['class', 'style'],
			embed:	['class','type','style','src','width','height','wmode','play','loop','menu','allowscriptaccess','allowfullscreen','base','pluginspage'],
			font:   ['color', 'size', 'face'],
			footer: [],
			h1:     ['class', 'style'],
			h2:     ['class', 'style'],
			h3:     ['class', 'style'],
			h4:     ['class', 'style'],
			h5:     ['class', 'style'],
			h6:     ['class', 'style'],
			header: [],
			hr:     [],
			i:      ['class', 'style'],
			iframe: ['width','height','src','_src','class','loadingclass','alt','title','style','frameborder'],
			img:    ['src', 'alt', 'title', 'width', 'height', 'id', '_src', 'loadingclass', 'class','_url','style'],
			ins:    ['datetime'],
			li:     ['class', 'style'],
			mark:   [],
			nav:    [],
			ol:     ['class', 'style'],
			p:      ['class', 'style'],
			pre:    ['class', 'style'],
			s:      [],
			section:[],
			small:  [],
			span:   ['class', 'style'],
			sub:    ['class', 'style'],
			sup:    ['class', 'style'],
			strong: ['class', 'style'],
			table:  ['width', 'border', 'align', 'valign', 'class', 'style'],
			tbody:  ['align', 'valign', 'class', 'style'],
			td:     ['width', 'rowspan', 'colspan', 'align', 'valign', 'class', 'style'],
			tfoot:  ['align', 'valign', 'class', 'style'],
			th:     ['width', 'rowspan', 'colspan', 'align', 'valign', 'class', 'style'],
			thead:  ['align', 'valign', 'class', 'style'],
			tr:     ['rowspan', 'align', 'valign', 'class', 'style'],
			tt:     [],
			u:      [],
			ul:     ['class', 'style'],
			video:  ['autoplay', 'controls', 'loop', 'preload', 'src', 'height', 'width', 'class', 'style']
		}
    };

    function getUEBasePath(docUrl, confUrl) {

        return getBasePath(docUrl || self.document.URL || self.location.href, confUrl || getConfigFilePath());

    }

    function getConfigFilePath() {

        var configPath = document.getElementsByTagName('script');

        return configPath[ configPath.length - 1 ].src;

    }

    function getBasePath(docUrl, confUrl) {

        var basePath = confUrl;


        if (/^(\/|\\\\)/.test(confUrl)) {

            basePath = /^.+?\w(\/|\\\\)/.exec(docUrl)[0] + confUrl.replace(/^(\/|\\\\)/, '');

        } else if (!/^[a-z]+:/i.test(confUrl)) {

            docUrl = docUrl.split("#")[0].split("?")[0].replace(/[^\\\/]+$/, '');

            basePath = docUrl + "" + confUrl;

        }

        return optimizationPath(basePath);

    }

    function optimizationPath(path) {

        var protocol = /^[a-z]+:\/\//.exec(path)[ 0 ],
            tmp = null,
            res = [];

        path = path.replace(protocol, "").split("?")[0].split("#")[0];

        path = path.replace(/\\/g, '/').split(/\//);

        path[ path.length - 1 ] = "";

        while (path.length) {

            if (( tmp = path.shift() ) === "..") {
                res.pop();
            } else if (tmp !== ".") {
                res.push(tmp);
            }

        }

        return protocol + res.join("/");

    }

    window.UE = {
        getUEBasePath: getUEBasePath
    };

})();
