/*!
 * UEditor
 * version: ueditor
 * build: Thu Jun 16 2016 12:33:50 GMT+0800 (CST)
 */

(function(){

// editor.js
UEDITOR_CONFIG = window.UEDITOR_CONFIG || {};

var baidu = window.baidu || {};

window.baidu = baidu;

window.UE = baidu.editor =  window.UE || {};

UE.plugins = {};

UE.commands = {};

UE.instants = {};

UE.I18N = {};

UE._customizeUI = {};

UE.version = "1.4.3";

var dom = UE.dom = {};

// core/browser.js
/**
 * 瀏覽器判斷模組
 * @file
 * @module UE.browser
 * @since 1.2.6.1
 */

/**
 * 提供瀏覽器檢測的模組
 * @unfile
 * @module UE.browser
 */
var browser = UE.browser = function(){
    var agent = navigator.userAgent.toLowerCase(),
        opera = window.opera,
        browser = {
        /**
         * @property {boolean} ie 檢測當前瀏覽器是否為IE
         * @example
         * ```javascript
         * if ( UE.browser.ie ) {
         *     console.log( '當前瀏覽器是IE' );
         * }
         * ```
         */
        ie		:  /(msie\s|trident.*rv:)([\w.]+)/.test(agent),

        /**
         * @property {boolean} opera 檢測當前瀏覽器是否為Opera
         * @example
         * ```javascript
         * if ( UE.browser.opera ) {
         *     console.log( '當前瀏覽器是Opera' );
         * }
         * ```
         */
        opera	: ( !!opera && opera.version ),

        /**
         * @property {boolean} webkit 檢測當前瀏覽器是否是webkit核心的瀏覽器
         * @example
         * ```javascript
         * if ( UE.browser.webkit ) {
         *     console.log( '當前瀏覽器是webkit核心瀏覽器' );
         * }
         * ```
         */
        webkit	: ( agent.indexOf( ' applewebkit/' ) > -1 ),

        /**
         * @property {boolean} mac 檢測當前瀏覽器是否是執行在mac平臺下
         * @example
         * ```javascript
         * if ( UE.browser.mac ) {
         *     console.log( '當前瀏覽器執行在mac平臺下' );
         * }
         * ```
         */
        mac	: ( agent.indexOf( 'macintosh' ) > -1 ),

        /**
         * @property {boolean} quirks 檢測當前瀏覽器是否處於“怪異模式”下
         * @example
         * ```javascript
         * if ( UE.browser.quirks ) {
         *     console.log( '當前瀏覽器執行處於“怪異模式”' );
         * }
         * ```
         */
        quirks : ( document.compatMode == 'BackCompat' )
    };

    /**
    * @property {boolean} gecko 檢測當前瀏覽器核心是否是gecko核心
    * @example
    * ```javascript
    * if ( UE.browser.gecko ) {
    *     console.log( '當前瀏覽器核心是gecko核心' );
    * }
    * ```
    */
    browser.gecko =( navigator.product == 'Gecko' && !browser.webkit && !browser.opera && !browser.ie);

    var version = 0;

    // Internet Explorer 6.0+
    if ( browser.ie ){

        var v1 =  agent.match(/(?:msie\s([\w.]+))/);
        var v2 = agent.match(/(?:trident.*rv:([\w.]+))/);
        if(v1 && v2 && v1[1] && v2[1]){
            version = Math.max(v1[1]*1,v2[1]*1);
        }else if(v1 && v1[1]){
            version = v1[1]*1;
        }else if(v2 && v2[1]){
            version = v2[1]*1;
        }else{
            version = 0;
        }

        browser.ie11Compat = document.documentMode == 11;
        /**
         * @property { boolean } ie9Compat 檢測瀏覽器模式是否為 IE9 相容模式
         * @warning 如果瀏覽器不是IE， 則該值為undefined
         * @example
         * ```javascript
         * if ( UE.browser.ie9Compat ) {
         *     console.log( '當前瀏覽器執行在IE9相容模式下' );
         * }
         * ```
         */
        browser.ie9Compat = document.documentMode == 9;

        /**
         * @property { boolean } ie8 檢測瀏覽器是否是IE8瀏覽器
         * @warning 如果瀏覽器不是IE， 則該值為undefined
         * @example
         * ```javascript
         * if ( UE.browser.ie8 ) {
         *     console.log( '當前瀏覽器是IE8瀏覽器' );
         * }
         * ```
         */
        browser.ie8 = !!document.documentMode;

        /**
         * @property { boolean } ie8Compat 檢測瀏覽器模式是否為 IE8 相容模式
         * @warning 如果瀏覽器不是IE， 則該值為undefined
         * @example
         * ```javascript
         * if ( UE.browser.ie8Compat ) {
         *     console.log( '當前瀏覽器執行在IE8相容模式下' );
         * }
         * ```
         */
        browser.ie8Compat = document.documentMode == 8;

        /**
         * @property { boolean } ie7Compat 檢測瀏覽器模式是否為 IE7 相容模式
         * @warning 如果瀏覽器不是IE， 則該值為undefined
         * @example
         * ```javascript
         * if ( UE.browser.ie7Compat ) {
         *     console.log( '當前瀏覽器執行在IE7相容模式下' );
         * }
         * ```
         */
        browser.ie7Compat = ( ( version == 7 && !document.documentMode )
                || document.documentMode == 7 );

        /**
         * @property { boolean } ie6Compat 檢測瀏覽器模式是否為 IE6 模式 或者怪異模式
         * @warning 如果瀏覽器不是IE， 則該值為undefined
         * @example
         * ```javascript
         * if ( UE.browser.ie6Compat ) {
         *     console.log( '當前瀏覽器執行在IE6模式或者怪異模式下' );
         * }
         * ```
         */
        browser.ie6Compat = ( version < 7 || browser.quirks );

        browser.ie9above = version > 8;

        browser.ie9below = version < 9;

        browser.ie11above = version > 10;

        browser.ie11below = version < 11;

    }

    // Gecko.
    if ( browser.gecko ){
        var geckoRelease = agent.match( /rv:([\d\.]+)/ );
        if ( geckoRelease )
        {
            geckoRelease = geckoRelease[1].split( '.' );
            version = geckoRelease[0] * 10000 + ( geckoRelease[1] || 0 ) * 100 + ( geckoRelease[2] || 0 ) * 1;
        }
    }

    /**
     * @property { Number } chrome 檢測當前瀏覽器是否為Chrome, 如果是，則返回Chrome的大版本號
     * @warning 如果瀏覽器不是chrome， 則該值為undefined
     * @example
     * ```javascript
     * if ( UE.browser.chrome ) {
     *     console.log( '當前瀏覽器是Chrome' );
     * }
     * ```
     */
    if (/chrome\/(\d+\.\d)/i.test(agent)) {
        browser.chrome = + RegExp['\x241'];
    }

    /**
     * @property { Number } safari 檢測當前瀏覽器是否為Safari, 如果是，則返回Safari的大版本號
     * @warning 如果瀏覽器不是safari， 則該值為undefined
     * @example
     * ```javascript
     * if ( UE.browser.safari ) {
     *     console.log( '當前瀏覽器是Safari' );
     * }
     * ```
     */
    if(/(\d+\.\d)?(?:\.\d)?\s+safari\/?(\d+\.\d+)?/i.test(agent) && !/chrome/i.test(agent)){
    	browser.safari = + (RegExp['\x241'] || RegExp['\x242']);
    }


    // Opera 9.50+
    if ( browser.opera )
        version = parseFloat( opera.version() );

    // WebKit 522+ (Safari 3+)
    if ( browser.webkit )
        version = parseFloat( agent.match( / applewebkit\/(\d+)/ )[1] );

    /**
     * @property { Number } version 檢測當前瀏覽器版本號
     * @remind
     * <ul>
     *     <li>IE系列返回值為5,6,7,8,9,10等</li>
     *     <li>gecko系列會返回10900，158900等</li>
     *     <li>webkit系列會返回其build號 (如 522等)</li>
     * </ul>
     * @example
     * ```javascript
     * console.log( '當前瀏覽器版本號是： ' + UE.browser.version );
     * ```
     */
    browser.version = version;

    /**
     * @property { boolean } isCompatible 檢測當前瀏覽器是否能夠與UEditor良好相容
     * @example
     * ```javascript
     * if ( UE.browser.isCompatible ) {
     *     console.log( '瀏覽器與UEditor能夠良好相容' );
     * }
     * ```
     */
    browser.isCompatible =
        !browser.mobile && (
        ( browser.ie && version >= 6 ) ||
        ( browser.gecko && version >= 10801 ) ||
        ( browser.opera && version >= 9.5 ) ||
        ( browser.air && version >= 1 ) ||
        ( browser.webkit && version >= 522 ) ||
        false );
    return browser;
}();
//快捷方式
var ie = browser.ie,
    webkit = browser.webkit,
    gecko = browser.gecko,
    opera = browser.opera;

// core/utils.js
/**
 * 工具函式包
 * @file
 * @module UE.utils
 * @since 1.2.6.1
 */

/**
 * UEditor封裝使用的靜態工具函式
 * @module UE.utils
 * @unfile
 */

var utils = UE.utils = {

    /**
     * 用給定的迭代器遍歷物件
     * @method each
     * @param { Object } obj 需要遍歷的物件
     * @param { Function } iterator 迭代器， 該方法接受兩個引數， 第一個引數是當前所處理的value， 第二個引數是當前遍歷物件的key
     * @example
     * ```javascript
     * var demoObj = {
     *     key1: 1,
     *     key2: 2
     * };
     *
     * //output: key1: 1, key2: 2
     * UE.utils.each( demoObj, funciton ( value, key ) {
     *
     *     console.log( key + ":" + value );
     *
     * } );
     * ```
     */

    /**
     * 用給定的迭代器遍歷陣列或類陣列物件
     * @method each
     * @param { Array } array 需要遍歷的陣列或者類陣列
     * @param { Function } iterator 迭代器， 該方法接受兩個引數， 第一個引數是當前所處理的value， 第二個引數是當前遍歷物件的key
     * @example
     * ```javascript
     * var divs = document.getElmentByTagNames( "div" );
     *
     * //output: 0: DIV, 1: DIV ...
     * UE.utils.each( divs, funciton ( value, key ) {
     *
     *     console.log( key + ":" + value.tagName );
     *
     * } );
     * ```
     */
    each : function(obj, iterator, context) {
        if (obj == null) return;
        if (obj.length === +obj.length) {
            for (var i = 0, l = obj.length; i < l; i++) {
                if(iterator.call(context, obj[i], i, obj) === false)
                    return false;
            }
        } else {
            for (var key in obj) {
                if (obj.hasOwnProperty(key)) {
                    if(iterator.call(context, obj[key], key, obj) === false)
                        return false;
                }
            }
        }
    },

    /**
     * 以給定物件作為原型建立一個新物件
     * @method makeInstance
     * @param { Object } protoObject 該物件將作為新建立物件的原型
     * @return { Object } 新的物件， 該物件的原型是給定的protoObject物件
     * @example
     * ```javascript
     *
     * var protoObject = { sayHello: function () { console.log('Hello UEditor!'); } };
     *
     * var newObject = UE.utils.makeInstance( protoObject );
     * //output: Hello UEditor!
     * newObject.sayHello();
     * ```
     */
    makeInstance:function (obj) {
        var noop = new Function();
        noop.prototype = obj;
        obj = new noop;
        noop.prototype = null;
        return obj;
    },

    /**
     * 將source物件中的屬性擴充套件到target物件上
     * @method extend
     * @remind 該方法將強制把source物件上的屬性複製到target物件上
     * @see UE.utils.extend(Object,Object,Boolean)
     * @param { Object } target 目標物件， 新的屬性將附加到該物件上
     * @param { Object } source 源物件， 該物件的屬性會被附加到target物件上
     * @return { Object } 返回target物件
     * @example
     * ```javascript
     *
     * var target = { name: 'target', sex: 1 },
     *      source = { name: 'source', age: 17 };
     *
     * UE.utils.extend( target, source );
     *
     * //output: { name: 'source', sex: 1, age: 17 }
     * console.log( target );
     *
     * ```
     */

    /**
     * 將source物件中的屬性擴充套件到target物件上， 根據指定的isKeepTarget值決定是否保留目標物件中與
     * 源物件屬性名相同的屬性值。
     * @method extend
     * @param { Object } target 目標物件， 新的屬性將附加到該物件上
     * @param { Object } source 源物件， 該物件的屬性會被附加到target物件上
     * @param { Boolean } isKeepTarget 是否保留目標物件中與源物件中屬性名相同的屬性
     * @return { Object } 返回target物件
     * @example
     * ```javascript
     *
     * var target = { name: 'target', sex: 1 },
     *      source = { name: 'source', age: 17 };
     *
     * UE.utils.extend( target, source, true );
     *
     * //output: { name: 'target', sex: 1, age: 17 }
     * console.log( target );
     *
     * ```
     */
    extend:function (t, s, b) {
        if (s) {
            for (var k in s) {
                if (!b || !t.hasOwnProperty(k)) {
                    t[k] = s[k];
                }
            }
        }
        return t;
    },

    /**
     * 將給定的多個物件的屬性複製到目標物件target上
     * @method extend2
     * @remind 該方法將強制把源物件上的屬性複製到target物件上
     * @remind 該方法支援兩個及以上的引數， 從第二個引數開始， 其屬性都會被複制到第一個引數上。 如果遇到同名的屬性，
     *          將會覆蓋掉之前的值。
     * @param { Object } target 目標物件， 新的屬性將附加到該物件上
     * @param { Object... } source 源物件， 支援多個物件， 該物件的屬性會被附加到target物件上
     * @return { Object } 返回target物件
     * @example
     * ```javascript
     *
     * var target = {},
     *     source1 = { name: 'source', age: 17 },
     *     source2 = { title: 'dev' };
     *
     * UE.utils.extend2( target, source1, source2 );
     *
     * //output: { name: 'source', age: 17, title: 'dev' }
     * console.log( target );
     *
     * ```
     */
    extend2:function (t) {
        var a = arguments;
        for (var i = 1; i < a.length; i++) {
            var x = a[i];
            for (var k in x) {
                if (!t.hasOwnProperty(k)) {
                    t[k] = x[k];
                }
            }
        }
        return t;
    },

    /**
     * 模擬繼承機制， 使得subClass繼承自superClass
     * @method inherits
     * @param { Object } subClass 子類物件
     * @param { Object } superClass 超類物件
     * @warning 該方法只能讓subClass繼承超類的原型， subClass物件自身的屬性和方法不會被繼承
     * @return { Object } 繼承superClass後的子類物件
     * @example
     * ```javascript
     * function SuperClass(){
     *     this.name = "小李";
     * }
     *
     * SuperClass.prototype = {
     *     hello:function(str){
     *         console.log(this.name + str);
     *     }
     * }
     *
     * function SubClass(){
     *     this.name = "小張";
     * }
     *
     * UE.utils.inherits(SubClass,SuperClass);
     *
     * var sub = new SubClass();
     * //output: '小張早上好!
     * sub.hello("早上好!");
     * ```
     */
    inherits:function (subClass, superClass) {
        var oldP = subClass.prototype,
            newP = utils.makeInstance(superClass.prototype);
        utils.extend(newP, oldP, true);
        subClass.prototype = newP;
        return (newP.constructor = subClass);
    },

    /**
     * 用指定的context物件作為函式fn的上下文
     * @method bind
     * @param { Function } fn 需要繫結上下文的函式物件
     * @param { Object } content 函式fn新的上下文物件
     * @return { Function } 一個新的函式， 該函式作為原始函式fn的代理， 將完成fn的上下文調換工作。
     * @example
     * ```javascript
     *
     * var name = 'window',
     *     newTest = null;
     *
     * function test () {
     *     console.log( this.name );
     * }
     *
     * newTest = UE.utils.bind( test, { name: 'object' } );
     *
     * //output: object
     * newTest();
     *
     * //output: window
     * test();
     *
     * ```
     */
    bind:function (fn, context) {
        return function () {
            return fn.apply(context, arguments);
        };
    },

    /**
     * 建立延遲指定時間後執行的函式fn
     * @method defer
     * @param { Function } fn 需要延遲執行的函式物件
     * @param { int } delay 延遲的時間， 單位是毫秒
     * @warning 該方法的時間控制是不精確的，僅僅只能保證函式的執行是在給定的時間之後，
     *           而不能保證剛好到達延遲時間時執行。
     * @return { Function } 目標函式fn的代理函式， 只有執行該函式才能起到延時效果
     * @example
     * ```javascript
     * var start = 0;
     *
     * function test(){
     *     console.log( new Date() - start );
     * }
     *
     * var testDefer = UE.utils.defer( test, 1000 );
     * //
     * start = new Date();
     * //output: (大約在1000毫秒之後輸出) 1000
     * testDefer();
     * ```
     */

    /**
     * 建立延遲指定時間後執行的函式fn, 如果在延遲時間內再次執行該方法， 將會根據指定的exclusion的值，
     * 決定是否取消前一次函式的執行， 如果exclusion的值為true， 則取消執行，反之，將繼續執行前一個方法。
     * @method defer
     * @param { Function } fn 需要延遲執行的函式物件
     * @param { int } delay 延遲的時間， 單位是毫秒
     * @param { Boolean } exclusion 如果在延遲時間內再次執行該函式，該值將決定是否取消執行前一次函式的執行，
     *                     值為true表示取消執行， 反之則將在執行前一次函式之後才執行本次函式呼叫。
     * @warning 該方法的時間控制是不精確的，僅僅只能保證函式的執行是在給定的時間之後，
     *           而不能保證剛好到達延遲時間時執行。
     * @return { Function } 目標函式fn的代理函式， 只有執行該函式才能起到延時效果
     * @example
     * ```javascript
     *
     * function test(){
     *     console.log(1);
     * }
     *
     * var testDefer = UE.utils.defer( test, 1000, true );
     *
     * //output: (兩次呼叫僅有一次輸出) 1
     * testDefer();
     * testDefer();
     * ```
     */
    defer:function (fn, delay, exclusion) {
        var timerID;
        return function () {
            if (exclusion) {
                clearTimeout(timerID);
            }
            timerID = setTimeout(fn, delay);
        };
    },

    /**
     * 獲取元素item在陣列array中首次出現的位置, 如果未找到item， 則返回-1
     * @method indexOf
     * @remind 該方法的匹配過程使用的是恆等“===”
     * @param { Array } array 需要查詢的陣列物件
     * @param { * } item 需要在目標陣列中查詢的值
     * @return { int } 返回item在目標陣列array中首次出現的位置， 如果在陣列中未找到item， 則返回-1
     * @example
     * ```javascript
     * var item = 1,
     *     arr = [ 3, 4, 6, 8, 1, 1, 2 ];
     *
     * //output: 4
     * console.log( UE.utils.indexOf( arr, item ) );
     * ```
     */

    /**
     * 獲取元素item陣列array中首次出現的位置, 如果未找到item， 則返回-1。通過start的值可以指定搜尋的起始位置。
     * @method indexOf
     * @remind 該方法的匹配過程使用的是恆等“===”
     * @param { Array } array 需要查詢的陣列物件
     * @param { * } item 需要在目標陣列中查詢的值
     * @param { int } start 搜尋的起始位置
     * @return { int } 返回item在目標陣列array中的start位置之後首次出現的位置， 如果在陣列中未找到item， 則返回-1
     * @example
     * ```javascript
     * var item = 1,
     *     arr = [ 3, 4, 6, 8, 1, 2, 8, 3, 2, 1, 1, 4 ];
     *
     * //output: 9
     * console.log( UE.utils.indexOf( arr, item, 5 ) );
     * ```
     */
    indexOf:function (array, item, start) {
        var index = -1;
        start = this.isNumber(start) ? start : 0;
        this.each(array, function (v, i) {
            if (i >= start && v === item) {
                index = i;
                return false;
            }
        });
        return index;
    },

    /**
     * 移除陣列array中所有的元素item
     * @method removeItem
     * @param { Array } array 要移除元素的目標陣列
     * @param { * } item 將要被移除的元素
     * @remind 該方法的匹配過程使用的是恆等“===”
     * @example
     * ```javascript
     * var arr = [ 4, 5, 7, 1, 3, 4, 6 ];
     *
     * UE.utils.removeItem( arr, 4 );
     * //output: [ 5, 7, 1, 3, 6 ]
     * console.log( arr );
     *
     * ```
     */
    removeItem:function (array, item) {
        for (var i = 0, l = array.length; i < l; i++) {
            if (array[i] === item) {
                array.splice(i, 1);
                i--;
            }
        }
    },

    /**
     * 刪除字串str的首尾空格
     * @method trim
     * @param { String } str 需要刪除首尾空格的字串
     * @return { String } 刪除了首尾的空格後的字串
     * @example
     * ```javascript
     *
     * var str = " UEdtior ";
     *
     * //output: 9
     * console.log( str.length );
     *
     * //output: 7
     * console.log( UE.utils.trim( " UEdtior " ).length );
     *
     * //output: 9
     * console.log( str.length );
     *
     *  ```
     */
    trim:function (str) {
        return str.replace(/(^[ \t\n\r]+)|([ \t\n\r]+$)/g, '');
    },

    /**
     * 將字串str以','分隔成陣列後，將該陣列轉換成雜湊物件， 其生成的hash物件的key為陣列中的元素， value為1
     * @method listToMap
     * @warning 該方法在生成的hash物件中，會為每一個key同時生成一個另一個全大寫的key。
     * @param { String } str 該字串將被以','分割為陣列， 然後進行轉化
     * @return { Object } 轉化之後的hash物件
     * @example
     * ```javascript
     *
     * //output: Object {UEdtior: 1, UEDTIOR: 1, Hello: 1, HELLO: 1}
     * console.log( UE.utils.listToMap( 'UEdtior,Hello' ) );
     *
     * ```
     */

    /**
     * 將字串陣列轉換成雜湊物件， 其生成的hash物件的key為陣列中的元素， value為1
     * @method listToMap
     * @warning 該方法在生成的hash物件中，會為每一個key同時生成一個另一個全大寫的key。
     * @param { Array } arr 字串陣列
     * @return { Object } 轉化之後的hash物件
     * @example
     * ```javascript
     *
     * //output: Object {UEdtior: 1, UEDTIOR: 1, Hello: 1, HELLO: 1}
     * console.log( UE.utils.listToMap( [ 'UEdtior', 'Hello' ] ) );
     *
     * ```
     */
    listToMap:function (list) {
        if (!list)return {};
        list = utils.isArray(list) ? list : list.split(',');
        for (var i = 0, ci, obj = {}; ci = list[i++];) {
            obj[ci.toUpperCase()] = obj[ci] = 1;
        }
        return obj;
    },

    /**
     * 將str中的html符號轉義,將轉義“'，&，<，"，>”五個字元
     * @method unhtml
     * @param { String } str 需要轉義的字串
     * @return { String } 轉義後的字串
     * @example
     * ```javascript
     * var html = '<body>&</body>';
     *
     * //output: &lt;body&gt;&amp;&lt;/body&gt;
     * console.log( UE.utils.unhtml( html ) );
     *
     * ```
     */
    unhtml:function (str, reg) {
        return str ? str.replace(reg || /[&<">'](?:(amp|lt|quot|gt|#39|nbsp|#\d+);)?/g, function (a, b) {
            if (b) {
                return a;
            } else {
                return {
                    '<':'&lt;',
                    '&':'&amp;',
                    '"':'&quot;',
                    '>':'&gt;',
                    "'":'&#39;'
                }[a]
            }

        }) : '';
    },
    /**
     * 將url中的html字元轉義， 僅轉義  ', ", <, > 四個字元
     * @param  { String } str 需要轉義的字串
     * @param  { RegExp } reg 自定義的正則
     * @return { String }     轉義後的字串
     */
    unhtmlForUrl:function (str, reg) {
        return str ? str.replace(reg || /[<">']/g, function (a) {
            return {
                '<':'&lt;',
                '&':'&amp;',
                '"':'&quot;',
                '>':'&gt;',
                "'":'&#39;'
            }[a]

        }) : '';
    },

    /**
     * 將str中的轉義字元還原成html字元
     * @see UE.utils.unhtml(String);
     * @method html
     * @param { String } str 需要逆轉義的字串
     * @return { String } 逆轉義後的字串
     * @example
     * ```javascript
     *
     * var str = '&lt;body&gt;&amp;&lt;/body&gt;';
     *
     * //output: <body>&</body>
     * console.log( UE.utils.html( str ) );
     *
     * ```
     */
    html:function (str) {
        return str ? str.replace(/&((g|l|quo)t|amp|#39|nbsp);/g, function (m) {
            return {
                '&lt;':'<',
                '&amp;':'&',
                '&quot;':'"',
                '&gt;':'>',
                '&#39;':"'",
                '&nbsp;':' '
            }[m]
        }) : '';
    },

    /**
     * 將css樣式轉換為駝峰的形式
     * @method cssStyleToDomStyle
     * @param { String } cssName 需要轉換的css樣式名
     * @return { String } 轉換成駝峰形式後的css樣式名
     * @example
     * ```javascript
     *
     * var str = 'border-top';
     *
     * //output: borderTop
     * console.log( UE.utils.cssStyleToDomStyle( str ) );
     *
     * ```
     */
    cssStyleToDomStyle:function () {
        var test = document.createElement('div').style,
            cache = {
                'float':test.cssFloat != undefined ? 'cssFloat' : test.styleFloat != undefined ? 'styleFloat' : 'float'
            };

        return function (cssName) {
            return cache[cssName] || (cache[cssName] = cssName.toLowerCase().replace(/-./g, function (match) {
                return match.charAt(1).toUpperCase();
            }));
        };
    }(),

    /**
     * 動態載入檔案到doc中
     * @method loadFile
     * @param { DomDocument } document 需要載入資原始檔的文件物件
     * @param { Object } options 載入資原始檔的屬性集合， 取值請參考程式碼示例
     * @example
     * ```javascript
     *
     * UE.utils.loadFile( document, {
     *     src:"test.js",
     *     tag:"script",
     *     type:"text/javascript",
     *     defer:"defer"
     * } );
     *
     * ```
     */

    /**
     * 動態載入檔案到doc中，載入成功後執行的回撥函式fn
     * @method loadFile
     * @param { DomDocument } document 需要載入資原始檔的文件物件
     * @param { Object } options 載入資原始檔的屬性集合， 該集合支援的值是script標籤和style標籤支援的所有屬性。
     * @param { Function } fn 資原始檔載入成功之後執行的回撥
     * @warning 對於在同一個文件中多次載入同一URL的檔案， 該方法會在第一次載入之後快取該請求，
     *           在此之後的所有同一URL的請求， 將會直接觸發回撥。
     * @example
     * ```javascript
     *
     * UE.utils.loadFile( document, {
     *     src:"test.js",
     *     tag:"script",
     *     type:"text/javascript",
     *     defer:"defer"
     * }, function () {
     *     console.log('載入成功');
     * } );
     *
     * ```
     */
    loadFile:function () {
        var tmpList = [];

        function getItem(doc, obj) {
            try {
                for (var i = 0, ci; ci = tmpList[i++];) {
                    if (ci.doc === doc && ci.url == (obj.src || obj.href)) {
                        return ci;
                    }
                }
            } catch (e) {
                return null;
            }

        }

        return function (doc, obj, fn) {
            var item = getItem(doc, obj);
            if (item) {
                if (item.ready) {
                    fn && fn();
                } else {
                    item.funs.push(fn)
                }
                return;
            }
            tmpList.push({
                doc:doc,
                url:obj.src || obj.href,
                funs:[fn]
            });
            if (!doc.body) {
                var html = [];
                for (var p in obj) {
                    if (p == 'tag')continue;
                    html.push(p + '="' + obj[p] + '"')
                }
                doc.write('<' + obj.tag + ' ' + html.join(' ') + ' ></' + obj.tag + '>');
                return;
            }
            if (obj.id && doc.getElementById(obj.id)) {
                return;
            }
            var element = doc.createElement(obj.tag);
            delete obj.tag;
            for (var p in obj) {
                element.setAttribute(p, obj[p]);
            }
            element.onload = element.onreadystatechange = function () {
                if (!this.readyState || /loaded|complete/.test(this.readyState)) {
                    item = getItem(doc, obj);
                    if (item.funs.length > 0) {
                        item.ready = 1;
                        for (var fi; fi = item.funs.pop();) {
                            fi();
                        }
                    }
                    element.onload = element.onreadystatechange = null;
                }
            };
            element.onerror = function () {
                throw Error('The load ' + (obj.href || obj.src) + ' fails,check the url settings of file ueditor.config.js ')
            };
            doc.getElementsByTagName("head")[0].appendChild(element);
        }
    }(),

    /**
     * 判斷obj物件是否為空
     * @method isEmptyObject
     * @param { * } obj 需要判斷的物件
     * @remind 如果判斷的物件是NULL， 將直接返回true， 如果是陣列且為空， 返回true， 如果是字串， 且字串為空，
     *          返回true， 如果是普通物件， 且該物件沒有任何例項屬性， 返回true
     * @return { Boolean } 物件是否為空
     * @example
     * ```javascript
     *
     * //output: true
     * console.log( UE.utils.isEmptyObject( {} ) );
     *
     * //output: true
     * console.log( UE.utils.isEmptyObject( [] ) );
     *
     * //output: true
     * console.log( UE.utils.isEmptyObject( "" ) );
     *
     * //output: false
     * console.log( UE.utils.isEmptyObject( { key: 1 } ) );
     *
     * //output: false
     * console.log( UE.utils.isEmptyObject( [1] ) );
     *
     * //output: false
     * console.log( UE.utils.isEmptyObject( "1" ) );
     *
     * ```
     */
    isEmptyObject:function (obj) {
        if (obj == null) return true;
        if (this.isArray(obj) || this.isString(obj)) return obj.length === 0;
        for (var key in obj) if (obj.hasOwnProperty(key)) return false;
        return true;
    },

    /**
     * 把rgb格式的顏色值轉換成16進位制格式
     * @method fixColor
     * @param { String } rgb格式的顏色值
     * @param { String }
     * @example
     * rgb(255,255,255)  => "#ffffff"
     */
    fixColor:function (name, value) {
        if (/color/i.test(name) && /rgba?/.test(value)) {
            var array = value.split(",");
            if (array.length > 3)
                return "";
            value = "#";
            for (var i = 0, color; color = array[i++];) {
                color = parseInt(color.replace(/[^\d]/gi, ''), 10).toString(16);
                value += color.length == 1 ? "0" + color : color;
            }
            value = value.toUpperCase();
        }
        return  value;
    },
    /**
     * 只針對border,padding,margin做了處理，因為效能問題
     * @public
     * @function
     * @param {String}    val style字串
     */
    optCss:function (val) {
        var padding, margin, border;
        val = val.replace(/(padding|margin|border)\-([^:]+):([^;]+);?/gi, function (str, key, name, val) {
            if (val.split(' ').length == 1) {
                switch (key) {
                    case 'padding':
                        !padding && (padding = {});
                        padding[name] = val;
                        return '';
                    case 'margin':
                        !margin && (margin = {});
                        margin[name] = val;
                        return '';
                    case 'border':
                        return val == 'initial' ? '' : str;
                }
            }
            return str;
        });

        function opt(obj, name) {
            if (!obj) {
                return '';
            }
            var t = obj.top , b = obj.bottom, l = obj.left, r = obj.right, val = '';
            if (!t || !l || !b || !r) {
                for (var p in obj) {
                    val += ';' + name + '-' + p + ':' + obj[p] + ';';
                }
            } else {
                val += ';' + name + ':' +
                    (t == b && b == l && l == r ? t :
                        t == b && l == r ? (t + ' ' + l) :
                            l == r ? (t + ' ' + l + ' ' + b) : (t + ' ' + r + ' ' + b + ' ' + l)) + ';'
            }
            return val;
        }

        val += opt(padding, 'padding') + opt(margin, 'margin');
        return val.replace(/^[ \n\r\t;]*|[ \n\r\t]*$/, '').replace(/;([ \n\r\t]+)|\1;/g, ';')
            .replace(/(&((l|g)t|quot|#39))?;{2,}/g, function (a, b) {
                return b ? b + ";;" : ';'
            });
    },

    /**
     * 克隆物件
     * @method clone
     * @param { Object } source 源物件
     * @return { Object } source的一個副本
     */

    /**
     * 深度克隆物件，將source的屬性克隆到target物件， 會覆蓋target重名的屬性。
     * @method clone
     * @param { Object } source 源物件
     * @param { Object } target 目標物件
     * @return { Object } 附加了source物件所有屬性的target物件
     */
    clone:function (source, target) {
        var tmp;
        target = target || {};
        for (var i in source) {
            if (source.hasOwnProperty(i)) {
                tmp = source[i];
                if (typeof tmp == 'object') {
                    target[i] = utils.isArray(tmp) ? [] : {};
                    utils.clone(source[i], target[i])
                } else {
                    target[i] = tmp;
                }
            }
        }
        return target;
    },

    /**
     * 把cm／pt為單位的值轉換為px為單位的值
     * @method transUnitToPx
     * @param { String } 待轉換的帶單位的字串
     * @return { String } 轉換為px為計量單位的值的字串
     * @example
     * ```javascript
     *
     * //output: 500px
     * console.log( UE.utils.transUnitToPx( '20cm' ) );
     *
     * //output: 27px
     * console.log( UE.utils.transUnitToPx( '20pt' ) );
     *
     * ```
     */
    transUnitToPx:function (val) {
        if (!/(pt|cm)/.test(val)) {
            return val
        }
        var unit;
        val.replace(/([\d.]+)(\w+)/, function (str, v, u) {
            val = v;
            unit = u;
        });
        switch (unit) {
            case 'cm':
                val = parseFloat(val) * 25;
                break;
            case 'pt':
                val = Math.round(parseFloat(val) * 96 / 72);
        }
        return val + (val ? 'px' : '');
    },

    /**
     * 在dom樹ready之後執行給定的回撥函式
     * @method domReady
     * @remind 如果在執行該方法的時候， dom樹已經ready， 那麼回撥函式將立刻執行
     * @param { Function } fn dom樹ready之後的回撥函式
     * @example
     * ```javascript
     *
     * UE.utils.domReady( function () {
     *
     *     console.log('123');
     *
     * } );
     *
     * ```
     */
    domReady:function () {

        var fnArr = [];

        function doReady(doc) {
            //確保onready只執行一次
            doc.isReady = true;
            for (var ci; ci = fnArr.pop(); ci()) {
            }
        }

        return function (onready, win) {
            win = win || window;
            var doc = win.document;
            onready && fnArr.push(onready);
            if (doc.readyState === "complete") {
                doReady(doc);
            } else {
                doc.isReady && doReady(doc);
                if (browser.ie && browser.version != 11) {
                    (function () {
                        if (doc.isReady) return;
                        try {
                            doc.documentElement.doScroll("left");
                        } catch (error) {
                            setTimeout(arguments.callee, 0);
                            return;
                        }
                        doReady(doc);
                    })();
                    win.attachEvent('onload', function () {
                        doReady(doc)
                    });
                } else {
                    doc.addEventListener("DOMContentLoaded", function () {
                        doc.removeEventListener("DOMContentLoaded", arguments.callee, false);
                        doReady(doc);
                    }, false);
                    win.addEventListener('load', function () {
                        doReady(doc)
                    }, false);
                }
            }

        }
    }(),

    /**
     * 動態新增css樣式
     * @method cssRule
     * @param { String } 節點名稱
     * @grammar UE.utils.cssRule('新增的樣式的節點名稱',['樣式'，'放到哪個document上'])
     * @grammar UE.utils.cssRule('body','body{background:#ccc}') => null  //給body新增背景顏色
     * @grammar UE.utils.cssRule('body') =>樣式的字串  //取得key值為body的樣式的內容,如果沒有找到key值先關的樣式將返回空，例如剛才那個背景顏色，將返回 body{background:#ccc}
     * @grammar UE.utils.cssRule('body',document) => 返回指定key的樣式，並且指定是哪個document
     * @grammar UE.utils.cssRule('body','') =>null //清空給定的key值的背景顏色
     */
    cssRule:browser.ie && browser.version != 11 ? function (key, style, doc) {
        var indexList, index;
        if(style === undefined || style && style.nodeType && style.nodeType == 9){
            //獲取樣式
            doc = style && style.nodeType && style.nodeType == 9 ? style : (doc || document);
            indexList = doc.indexList || (doc.indexList = {});
            index = indexList[key];
            if(index !==  undefined){
                return doc.styleSheets[index].cssText
            }
            return undefined;
        }
        doc = doc || document;
        indexList = doc.indexList || (doc.indexList = {});
        index = indexList[key];
        //清除樣式
        if(style === ''){
            if(index!== undefined){
                doc.styleSheets[index].cssText = '';
                delete indexList[key];
                return true
            }
            return false;
        }

        //新增樣式
        if(index!== undefined){
            sheetStyle =  doc.styleSheets[index];
        }else{
            sheetStyle = doc.createStyleSheet('', index = doc.styleSheets.length);
            indexList[key] = index;
        }
        sheetStyle.cssText = style;
    }: function (key, style, doc) {
        var head, node;
        if(style === undefined || style && style.nodeType && style.nodeType == 9){
            //獲取樣式
            doc = style && style.nodeType && style.nodeType == 9 ? style : (doc || document);
            node = doc.getElementById(key);
            return node ? node.innerHTML : undefined;
        }
        doc = doc || document;
        node = doc.getElementById(key);

        //清除樣式
        if(style === ''){
            if(node){
                node.parentNode.removeChild(node);
                return true
            }
            return false;
        }

        //新增樣式
        if(node){
            node.innerHTML = style;
        }else{
            node = doc.createElement('style');
            node.id = key;
            node.innerHTML = style;
            doc.getElementsByTagName('head')[0].appendChild(node);
        }
    },
    sort:function(array,compareFn){
        compareFn = compareFn || function(item1, item2){ return item1.localeCompare(item2);};
        for(var i= 0,len = array.length; i<len; i++){
            for(var j = i,length = array.length; j<length; j++){
                if(compareFn(array[i], array[j]) > 0){
                    var t = array[i];
                    array[i] = array[j];
                    array[j] = t;
                }
            }
        }
        return array;
    },
    serializeParam:function (json) {
        var strArr = [];
        for (var i in json) {
            //忽略預設的幾個引數
            if(i=="method" || i=="timeout" || i=="async") continue;
            //傳遞過來的物件和函式不在提交之列
            if (!((typeof json[i]).toLowerCase() == "function" || (typeof json[i]).toLowerCase() == "object")) {
                strArr.push( encodeURIComponent(i) + "="+encodeURIComponent(json[i]) );
            } else if (utils.isArray(json[i])) {
                //支援傳陣列內容
                for(var j = 0; j < json[i].length; j++) {
                    strArr.push( encodeURIComponent(i) + "[]="+encodeURIComponent(json[i][j]) );
                }
            }
        }
        return strArr.join("&");
    },
    formatUrl:function (url) {
        var u = url.replace(/&&/g, '&');
        u = u.replace(/\?&/g, '?');
        u = u.replace(/&$/g, '');
        u = u.replace(/&#/g, '#');
        u = u.replace(/&+/g, '&');
        return u;
    },
    isCrossDomainUrl:function (url) {
        var a = document.createElement('a');
        a.href = url;
        if (browser.ie) {
            a.href = a.href;
        }
        return !(a.protocol == location.protocol && a.hostname == location.hostname &&
        (a.port == location.port || (a.port == '80' && location.port == '') || (a.port == '' && location.port == '80')));
    },
    clearEmptyAttrs : function(obj){
        for(var p in obj){
            if(obj[p] === ''){
                delete obj[p]
            }
        }
        return obj;
    },
    str2json : function(s){

        if (!utils.isString(s)) return null;
        if (window.JSON) {
            return JSON.parse(s);
        } else {
            return (new Function("return " + utils.trim(s || '')))();
        }

    },
    json2str : (function(){

        if (window.JSON) {

            return JSON.stringify;

        } else {

            var escapeMap = {
                "\b": '\\b',
                "\t": '\\t',
                "\n": '\\n',
                "\f": '\\f',
                "\r": '\\r',
                '"' : '\\"',
                "\\": '\\\\'
            };

            function encodeString(source) {
                if (/["\\\x00-\x1f]/.test(source)) {
                    source = source.replace(
                        /["\\\x00-\x1f]/g,
                        function (match) {
                            var c = escapeMap[match];
                            if (c) {
                                return c;
                            }
                            c = match.charCodeAt();
                            return "\\u00"
                            + Math.floor(c / 16).toString(16)
                            + (c % 16).toString(16);
                        });
                }
                return '"' + source + '"';
            }

            function encodeArray(source) {
                var result = ["["],
                    l = source.length,
                    preComma, i, item;

                for (i = 0; i < l; i++) {
                    item = source[i];

                    switch (typeof item) {
                        case "undefined":
                        case "function":
                        case "unknown":
                            break;
                        default:
                            if(preComma) {
                                result.push(',');
                            }
                            result.push(utils.json2str(item));
                            preComma = 1;
                    }
                }
                result.push("]");
                return result.join("");
            }

            function pad(source) {
                return source < 10 ? '0' + source : source;
            }

            function encodeDate(source){
                return '"' + source.getFullYear() + "-"
                + pad(source.getMonth() + 1) + "-"
                + pad(source.getDate()) + "T"
                + pad(source.getHours()) + ":"
                + pad(source.getMinutes()) + ":"
                + pad(source.getSeconds()) + '"';
            }

            return function (value) {
                switch (typeof value) {
                    case 'undefined':
                        return 'undefined';

                    case 'number':
                        return isFinite(value) ? String(value) : "null";

                    case 'string':
                        return encodeString(value);

                    case 'boolean':
                        return String(value);

                    default:
                        if (value === null) {
                            return 'null';
                        } else if (utils.isArray(value)) {
                            return encodeArray(value);
                        } else if (utils.isDate(value)) {
                            return encodeDate(value);
                        } else {
                            var result = ['{'],
                                encode = utils.json2str,
                                preComma,
                                item;

                            for (var key in value) {
                                if (Object.prototype.hasOwnProperty.call(value, key)) {
                                    item = value[key];
                                    switch (typeof item) {
                                        case 'undefined':
                                        case 'unknown':
                                        case 'function':
                                            break;
                                        default:
                                            if (preComma) {
                                                result.push(',');
                                            }
                                            preComma = 1;
                                            result.push(encode(key) + ':' + encode(item));
                                    }
                                }
                            }
                            result.push('}');
                            return result.join('');
                        }
                }
            };
        }

    })()

};
/**
 * 判斷給定的物件是否是字串
 * @method isString
 * @param { * } object 需要判斷的物件
 * @return { Boolean } 給定的物件是否是字串
 */

/**
 * 判斷給定的物件是否是陣列
 * @method isArray
 * @param { * } object 需要判斷的物件
 * @return { Boolean } 給定的物件是否是陣列
 */

/**
 * 判斷給定的物件是否是一個Function
 * @method isFunction
 * @param { * } object 需要判斷的物件
 * @return { Boolean } 給定的物件是否是Function
 */

/**
 * 判斷給定的物件是否是Number
 * @method isNumber
 * @param { * } object 需要判斷的物件
 * @return { Boolean } 給定的物件是否是Number
 */

/**
 * 判斷給定的物件是否是一個正則表示式
 * @method isRegExp
 * @param { * } object 需要判斷的物件
 * @return { Boolean } 給定的物件是否是正則表示式
 */

/**
 * 判斷給定的物件是否是一個普通物件
 * @method isObject
 * @param { * } object 需要判斷的物件
 * @return { Boolean } 給定的物件是否是普通物件
 */
utils.each(['String', 'Function', 'Array', 'Number', 'RegExp', 'Object', 'Date'], function (v) {
    UE.utils['is' + v] = function (obj) {
        return Object.prototype.toString.apply(obj) == '[object ' + v + ']';
    }
});


// core/EventBase.js
/**
 * UE採用的事件基類
 * @file
 * @module UE
 * @class EventBase
 * @since 1.2.6.1
 */

/**
 * UEditor公用空間，UEditor所有的功能都掛載在該空間下
 * @unfile
 * @module UE
 */

/**
 * UE採用的事件基類，繼承此類的對應類將獲取addListener,removeListener,fireEvent方法。
 * 在UE中，Editor以及所有ui例項都繼承了該類，故可以在對應的ui物件以及editor物件上使用上述方法。
 * @unfile
 * @module UE
 * @class EventBase
 */

/**
 * 通過此構造器，子類可以繼承EventBase獲取事件監聽的方法
 * @constructor
 * @example
 * ```javascript
 * UE.EventBase.call(editor);
 * ```
 */
var EventBase = UE.EventBase = function () {};

EventBase.prototype = {

    /**
     * 註冊事件監聽器
     * @method addListener
     * @param { String } types 監聽的事件名稱，同時監聽多個事件使用空格分隔
     * @param { Function } fn 監聽的事件被觸發時，會執行該回調函式
     * @waining 事件被觸發時，監聽的函式假如返回的值恆等於true，回撥函式的佇列中後面的函式將不執行
     * @example
     * ```javascript
     * editor.addListener('selectionchange',function(){
     *      console.log("選區已經變化！");
     * })
     * editor.addListener('beforegetcontent aftergetcontent',function(type){
     *         if(type == 'beforegetcontent'){
     *             //do something
     *         }else{
     *             //do something
     *         }
     *         console.log(this.getContent) // this是註冊的事件的編輯器例項
     * })
     * ```
     * @see UE.EventBase:fireEvent(String)
     */
    addListener:function (types, listener) {
        types = utils.trim(types).split(/\s+/);
        for (var i = 0, ti; ti = types[i++];) {
            getListener(this, ti, true).push(listener);
        }
    },

    on : function(types, listener){
      return this.addListener(types,listener);
    },
    off : function(types, listener){
        return this.removeListener(types, listener)
    },
    trigger:function(){
        return this.fireEvent.apply(this,arguments);
    },
    /**
     * 移除事件監聽器
     * @method removeListener
     * @param { String } types 移除的事件名稱，同時移除多個事件使用空格分隔
     * @param { Function } fn 移除監聽事件的函式引用
     * @example
     * ```javascript
     * //changeCallback為方法體
     * editor.removeListener("selectionchange",changeCallback);
     * ```
     */
    removeListener:function (types, listener) {
        types = utils.trim(types).split(/\s+/);
        for (var i = 0, ti; ti = types[i++];) {
            utils.removeItem(getListener(this, ti) || [], listener);
        }
    },

    /**
     * 觸發事件
     * @method fireEvent
     * @param { String } types 觸發的事件名稱，同時觸發多個事件使用空格分隔
     * @remind 該方法會觸發addListener
     * @return { * } 返回觸發事件的佇列中，最後執行的回撥函式的返回值
     * @example
     * ```javascript
     * editor.fireEvent("selectionchange");
     * ```
     */

    /**
     * 觸發事件
     * @method fireEvent
     * @param { String } types 觸發的事件名稱，同時觸發多個事件使用空格分隔
     * @param { *... } options 可選引數，可以傳入一個或多個引數，會傳給事件觸發的回撥函式
     * @return { * } 返回觸發事件的佇列中，最後執行的回撥函式的返回值
     * @example
     * ```javascript
     *
     * editor.addListener( "selectionchange", function ( type, arg1, arg2 ) {
     *
     *     console.log( arg1 + " " + arg2 );
     *
     * } );
     *
     * //觸發selectionchange事件， 會執行上面的事件監聽器
     * //output: Hello World
     * editor.fireEvent("selectionchange", "Hello", "World");
     * ```
     */
    fireEvent:function () {
        var types = arguments[0];
        types = utils.trim(types).split(' ');
        for (var i = 0, ti; ti = types[i++];) {
            var listeners = getListener(this, ti),
                r, t, k;
            if (listeners) {
                k = listeners.length;
                while (k--) {
                    if(!listeners[k])continue;
                    t = listeners[k].apply(this, arguments);
                    if(t === true){
                        return t;
                    }
                    if (t !== undefined) {
                        r = t;
                    }
                }
            }
            if (t = this['on' + ti.toLowerCase()]) {
                r = t.apply(this, arguments);
            }
        }
        return r;
    }
};
/**
 * 獲得物件所擁有監聽型別的所有監聽器
 * @unfile
 * @module UE
 * @since 1.2.6.1
 * @method getListener
 * @public
 * @param { Object } obj  查詢監聽器的物件
 * @param { String } type 事件型別
 * @param { Boolean } force  為true且當前所有type型別的偵聽器不存在時，建立一個空監聽器陣列
 * @return { Array } 監聽器陣列
 */
function getListener(obj, type, force) {
    var allListeners;
    type = type.toLowerCase();
    return ( ( allListeners = ( obj.__allListeners || force && ( obj.__allListeners = {} ) ) )
        && ( allListeners[type] || force && ( allListeners[type] = [] ) ) );
}



// core/dtd.js
///import editor.js
///import core/dom/dom.js
///import core/utils.js
/**
 * dtd html語義化的體現類
 * @constructor
 * @namespace dtd
 */
var dtd = dom.dtd = (function() {
    function _( s ) {
        for (var k in s) {
            s[k.toUpperCase()] = s[k];
        }
        return s;
    }
    var X = utils.extend2;
    var A = _({isindex:1,fieldset:1}),
        B = _({input:1,button:1,select:1,textarea:1,label:1}),
        C = X( _({a:1}), B ),
        D = X( {iframe:1}, C ),
        E = _({hr:1,ul:1,menu:1,div:1,blockquote:1,noscript:1,table:1,center:1,address:1,dir:1,pre:1,h5:1,dl:1,h4:1,noframes:1,h6:1,ol:1,h1:1,h3:1,h2:1}),
        F = _({ins:1,del:1,script:1,style:1}),
        G = X( _({b:1,acronym:1,bdo:1,'var':1,'#':1,abbr:1,code:1,br:1,i:1,cite:1,kbd:1,u:1,strike:1,s:1,tt:1,strong:1,q:1,samp:1,em:1,dfn:1,span:1}), F ),
        H = X( _({sub:1,img:1,embed:1,object:1,sup:1,basefont:1,map:1,applet:1,font:1,big:1,small:1}), G ),
        I = X( _({p:1}), H ),
        J = X( _({iframe:1}), H, B ),
        K = _({img:1,embed:1,noscript:1,br:1,kbd:1,center:1,button:1,basefont:1,h5:1,h4:1,samp:1,h6:1,ol:1,h1:1,h3:1,h2:1,form:1,font:1,'#':1,select:1,menu:1,ins:1,abbr:1,label:1,code:1,table:1,script:1,cite:1,input:1,iframe:1,strong:1,textarea:1,noframes:1,big:1,small:1,span:1,hr:1,sub:1,bdo:1,'var':1,div:1,object:1,sup:1,strike:1,dir:1,map:1,dl:1,applet:1,del:1,isindex:1,fieldset:1,ul:1,b:1,acronym:1,a:1,blockquote:1,i:1,u:1,s:1,tt:1,address:1,q:1,pre:1,p:1,em:1,dfn:1}),

        L = X( _({a:0}), J ),//a不能被切開，所以把他
        M = _({tr:1}),
        N = _({'#':1}),
        O = X( _({param:1}), K ),
        P = X( _({form:1}), A, D, E, I ),
        Q = _({li:1,ol:1,ul:1}),
        R = _({style:1,script:1}),
        S = _({base:1,link:1,meta:1,title:1}),
        T = X( S, R ),
        U = _({head:1,body:1}),
        V = _({html:1});

    var block = _({address:1,blockquote:1,center:1,dir:1,div:1,dl:1,fieldset:1,form:1,h1:1,h2:1,h3:1,h4:1,h5:1,h6:1,hr:1,isindex:1,menu:1,noframes:1,ol:1,p:1,pre:1,table:1,ul:1}),

        empty =  _({area:1,base:1,basefont:1,br:1,col:1,command:1,dialog:1,embed:1,hr:1,img:1,input:1,isindex:1,keygen:1,link:1,meta:1,param:1,source:1,track:1,wbr:1});

    return  _({

        // $ 表示自定的屬性

        // body外的元素列表.
        $nonBodyContent: X( V, U, S ),

        //塊結構元素列表
        $block : block,

        //內聯元素列表
        $inline : L,

        $inlineWithA : X(_({a:1}),L),

        $body : X( _({script:1,style:1}), block ),

        $cdata : _({script:1,style:1}),

        //自閉和元素
        $empty : empty,

        //不是自閉合，但不能讓range選中裡邊
        $nonChild : _({iframe:1,textarea:1}),
        //列表元素列表
        $listItem : _({dd:1,dt:1,li:1}),

        //列表根元素列表
        $list: _({ul:1,ol:1,dl:1}),

        //不能認為是空的元素
        $isNotEmpty : _({table:1,ul:1,ol:1,dl:1,iframe:1,area:1,base:1,col:1,hr:1,img:1,embed:1,input:1,link:1,meta:1,param:1,h1:1,h2:1,h3:1,h4:1,h5:1,h6:1}),

        //如果沒有子節點就可以刪除的元素列表，像span,a
        $removeEmpty : _({a:1,abbr:1,acronym:1,address:1,b:1,bdo:1,big:1,cite:1,code:1,del:1,dfn:1,em:1,font:1,i:1,ins:1,label:1,kbd:1,q:1,s:1,samp:1,small:1,span:1,strike:1,strong:1,sub:1,sup:1,tt:1,u:1,'var':1}),

        $removeEmptyBlock : _({'p':1,'div':1}),

        //在table元素裡的元素列表
        $tableContent : _({caption:1,col:1,colgroup:1,tbody:1,td:1,tfoot:1,th:1,thead:1,tr:1,table:1}),
        //不轉換的標籤
        $notTransContent : _({pre:1,script:1,style:1,textarea:1}),
        html: U,
        head: T,
        style: N,
        script: N,
        body: P,
        base: {},
        link: {},
        meta: {},
        title: N,
        col : {},
        tr : _({td:1,th:1}),
        img : {},
        embed: {},
        colgroup : _({thead:1,col:1,tbody:1,tr:1,tfoot:1}),
        noscript : P,
        td : P,
        br : {},
        th : P,
        center : P,
        kbd : L,
        button : X( I, E ),
        basefont : {},
        h5 : L,
        h4 : L,
        samp : L,
        h6 : L,
        ol : Q,
        h1 : L,
        h3 : L,
        option : N,
        h2 : L,
        form : X( A, D, E, I ),
        select : _({optgroup:1,option:1}),
        font : L,
        ins : L,
        menu : Q,
        abbr : L,
        label : L,
        table : _({thead:1,col:1,tbody:1,tr:1,colgroup:1,caption:1,tfoot:1}),
        code : L,
        tfoot : M,
        cite : L,
        li : P,
        input : {},
        iframe : P,
        strong : L,
        textarea : N,
        noframes : P,
        big : L,
        small : L,
        //trace:
        span :_({'#':1,br:1,b:1,strong:1,u:1,i:1,em:1,sub:1,sup:1,strike:1,span:1}),
        hr : L,
        dt : L,
        sub : L,
        optgroup : _({option:1}),
        param : {},
        bdo : L,
        'var' : L,
        div : P,
        object : O,
        sup : L,
        dd : P,
        strike : L,
        area : {},
        dir : Q,
        map : X( _({area:1,form:1,p:1}), A, F, E ),
        applet : O,
        dl : _({dt:1,dd:1}),
        del : L,
        isindex : {},
        fieldset : X( _({legend:1}), K ),
        thead : M,
        ul : Q,
        acronym : L,
        b : L,
        a : X( _({a:1}), J ),
        blockquote :X(_({td:1,tr:1,tbody:1,li:1}),P),
        caption : L,
        i : L,
        u : L,
        tbody : M,
        s : L,
        address : X( D, I ),
        tt : L,
        legend : L,
        q : L,
        pre : X( G, C ),
        p : X(_({'a':1}),L),
        em :L,
        dfn : L
    });
})();


// core/domUtils.js
/**
 * Dom操作工具包
 * @file
 * @module UE.dom.domUtils
 * @since 1.2.6.1
 */

/**
 * Dom操作工具包
 * @unfile
 * @module UE.dom.domUtils
 */
function getDomNode(node, start, ltr, startFromChild, fn, guard) {
    var tmpNode = startFromChild && node[start],
        parent;
    !tmpNode && (tmpNode = node[ltr]);
    while (!tmpNode && (parent = (parent || node).parentNode)) {
        if (parent.tagName == 'BODY' || guard && !guard(parent)) {
            return null;
        }
        tmpNode = parent[ltr];
    }
    if (tmpNode && fn && !fn(tmpNode)) {
        return  getDomNode(tmpNode, start, ltr, false, fn);
    }
    return tmpNode;
}
var attrFix = ie && browser.version < 9 ? {
        tabindex:"tabIndex",
        readonly:"readOnly",
        "for":"htmlFor",
        "class":"className",
        maxlength:"maxLength",
        cellspacing:"cellSpacing",
        cellpadding:"cellPadding",
        rowspan:"rowSpan",
        colspan:"colSpan",
        usemap:"useMap",
        frameborder:"frameBorder"
    } : {
        tabindex:"tabIndex",
        readonly:"readOnly"
    },
    styleBlock = utils.listToMap([
        '-webkit-box', '-moz-box', 'block' ,
        'list-item' , 'table' , 'table-row-group' ,
        'table-header-group', 'table-footer-group' ,
        'table-row' , 'table-column-group' , 'table-column' ,
        'table-cell' , 'table-caption'
    ]);
var domUtils = dom.domUtils = {
    //節點常量
    NODE_ELEMENT:1,
    NODE_DOCUMENT:9,
    NODE_TEXT:3,
    NODE_COMMENT:8,
    NODE_DOCUMENT_FRAGMENT:11,

    //位置關係
    POSITION_IDENTICAL:0,
    POSITION_DISCONNECTED:1,
    POSITION_FOLLOWING:2,
    POSITION_PRECEDING:4,
    POSITION_IS_CONTAINED:8,
    POSITION_CONTAINS:16,
    //ie6使用其他的會有一段空白出現
    fillChar:ie && browser.version == '6' ? '\ufeff' : '\u200B',
    //-------------------------Node部分--------------------------------
    keys:{
        /*Backspace*/ 8:1, /*Delete*/ 46:1,
        /*Shift*/ 16:1, /*Ctrl*/ 17:1, /*Alt*/ 18:1,
        37:1, 38:1, 39:1, 40:1,
        13:1 /*enter*/
    },
    /**
     * 獲取節點A相對於節點B的位置關係
     * @method getPosition
     * @param { Node } nodeA 需要查詢位置關係的節點A
     * @param { Node } nodeB 需要查詢位置關係的節點B
     * @return { Number } 節點A與節點B的關係
     * @example
     * ```javascript
     * //output: 20
     * var position = UE.dom.domUtils.getPosition( document.documentElement, document.body );
     *
     * switch ( position ) {
     *
     *      //0
     *      case UE.dom.domUtils.POSITION_IDENTICAL:
     *          console.log('元素相同');
     *          break;
     *      //1
     *      case UE.dom.domUtils.POSITION_DISCONNECTED:
     *          console.log('兩個節點在不同的文件中');
     *          break;
     *      //2
     *      case UE.dom.domUtils.POSITION_FOLLOWING:
     *          console.log('節點A在節點B之後');
     *          break;
     *      //4
     *      case UE.dom.domUtils.POSITION_PRECEDING;
     *          console.log('節點A在節點B之前');
     *          break;
     *      //8
     *      case UE.dom.domUtils.POSITION_IS_CONTAINED:
     *          console.log('節點A被節點B包含');
     *          break;
     *      case 10:
     *          console.log('節點A被節點B包含且節點A在節點B之後');
     *          break;
     *      //16
     *      case UE.dom.domUtils.POSITION_CONTAINS:
     *          console.log('節點A包含節點B');
     *          break;
     *      case 20:
     *          console.log('節點A包含節點B且節點A在節點B之前');
     *          break;
     *
     * }
     * ```
     */
    getPosition:function (nodeA, nodeB) {
        // 如果兩個節點是同一個節點
        if (nodeA === nodeB) {
            // domUtils.POSITION_IDENTICAL
            return 0;
        }
        var node,
            parentsA = [nodeA],
            parentsB = [nodeB];
        node = nodeA;
        while (node = node.parentNode) {
            // 如果nodeB是nodeA的祖先節點
            if (node === nodeB) {
                // domUtils.POSITION_IS_CONTAINED + domUtils.POSITION_FOLLOWING
                return 10;
            }
            parentsA.push(node);
        }
        node = nodeB;
        while (node = node.parentNode) {
            // 如果nodeA是nodeB的祖先節點
            if (node === nodeA) {
                // domUtils.POSITION_CONTAINS + domUtils.POSITION_PRECEDING
                return 20;
            }
            parentsB.push(node);
        }
        parentsA.reverse();
        parentsB.reverse();
        if (parentsA[0] !== parentsB[0]) {
            // domUtils.POSITION_DISCONNECTED
            return 1;
        }
        var i = -1;
        while (i++, parentsA[i] === parentsB[i]) {
        }
        nodeA = parentsA[i];
        nodeB = parentsB[i];
        while (nodeA = nodeA.nextSibling) {
            if (nodeA === nodeB) {
                // domUtils.POSITION_PRECEDING
                return 4
            }
        }
        // domUtils.POSITION_FOLLOWING
        return  2;
    },

    /**
     * 檢測節點node在父節點中的索引位置
     * @method getNodeIndex
     * @param { Node } node 需要檢測的節點物件
     * @return { Number } 該節點在父節點中的位置
     * @see UE.dom.domUtils.getNodeIndex(Node,Boolean)
     */

    /**
     * 檢測節點node在父節點中的索引位置， 根據給定的mergeTextNode引數決定是否要合併多個連續的文字節點為一個節點
     * @method getNodeIndex
     * @param { Node } node 需要檢測的節點物件
     * @param { Boolean } mergeTextNode 是否合併多個連續的文字節點為一個節點
     * @return { Number } 該節點在父節點中的位置
     * @example
     * ```javascript
     *
     *      var node = document.createElement("div");
     *
     *      node.appendChild( document.createTextNode( "hello" ) );
     *      node.appendChild( document.createTextNode( "world" ) );
     *      node.appendChild( node = document.createElement( "div" ) );
     *
     *      //output: 2
     *      console.log( UE.dom.domUtils.getNodeIndex( node ) );
     *
     *      //output: 1
     *      console.log( UE.dom.domUtils.getNodeIndex( node, true ) );
     *
     * ```
     */
    getNodeIndex:function (node, ignoreTextNode) {
        var preNode = node,
            i = 0;
        while (preNode = preNode.previousSibling) {
            if (ignoreTextNode && preNode.nodeType == 3) {
                if(preNode.nodeType != preNode.nextSibling.nodeType ){
                    i++;
                }
                continue;
            }
            i++;
        }
        return i;
    },

    /**
     * 檢測節點node是否在給定的document物件上
     * @method inDoc
     * @param { Node } node 需要檢測的節點物件
     * @param { DomDocument } doc 需要檢測的document物件
     * @return { Boolean } 該節點node是否在給定的document的dom樹上
     * @example
     * ```javascript
     *
     * var node = document.createElement("div");
     *
     * //output: false
     * console.log( UE.do.domUtils.inDoc( node, document ) );
     *
     * document.body.appendChild( node );
     *
     * //output: true
     * console.log( UE.do.domUtils.inDoc( node, document ) );
     *
     * ```
     */
    inDoc:function (node, doc) {
        return domUtils.getPosition(node, doc) == 10;
    },
    /**
     * 根據給定的過濾規則filterFn， 查詢符合該過濾規則的node節點的第一個祖先節點，
     * 查詢的起點是給定node節點的父節點。
     * @method findParent
     * @param { Node } node 需要查詢的節點
     * @param { Function } filterFn 自定義的過濾方法。
     * @warning 查詢的終點是到body節點為止
     * @remind 自定義的過濾方法filterFn接受一個Node物件作為引數， 該物件代表當前執行檢測的祖先節點。 如果該
     *          節點滿足過濾條件， 則要求返回true， 這時將直接返回該節點作為findParent()的結果， 否則， 請返回false。
     * @return { Node | Null } 如果找到符合過濾條件的節點， 就返回該節點， 否則返回NULL
     * @example
     * ```javascript
     * var filterNode = UE.dom.domUtils.findParent( document.body.firstChild, function ( node ) {
     *
     *     //由於查詢的終點是body節點， 所以永遠也不會匹配當前過濾器的條件， 即這裡永遠會返回false
     *     return node.tagName === "HTML";
     *
     * } );
     *
     * //output: true
     * console.log( filterNode === null );
     * ```
     */

    /**
     * 根據給定的過濾規則filterFn， 查詢符合該過濾規則的node節點的第一個祖先節點，
     * 如果includeSelf的值為true，則查詢的起點是給定的節點node， 否則， 起點是node的父節點
     * @method findParent
     * @param { Node } node 需要查詢的節點
     * @param { Function } filterFn 自定義的過濾方法。
     * @param { Boolean } includeSelf 查詢過程是否包含自身
     * @warning 查詢的終點是到body節點為止
     * @remind 自定義的過濾方法filterFn接受一個Node物件作為引數， 該物件代表當前執行檢測的祖先節點。 如果該
     *          節點滿足過濾條件， 則要求返回true， 這時將直接返回該節點作為findParent()的結果， 否則， 請返回false。
     * @remind 如果includeSelf為true， 則過濾器第一次執行時的引數會是節點本身。
     *          反之， 過濾器第一次執行時的引數將是該節點的父節點。
     * @return { Node | Null } 如果找到符合過濾條件的節點， 就返回該節點， 否則返回NULL
     * @example
     * ```html
     * <body>
     *
     *      <div id="test">
     *      </div>
     *
     *      <script type="text/javascript">
     *
     *          //output: DIV, BODY
     *          var filterNode = UE.dom.domUtils.findParent( document.getElementById( "test" ), function ( node ) {
     *
     *              console.log( node.tagName );
     *              return false;
     *
     *          }, true );
     *
     *      </script>
     * </body>
     * ```
     */
    findParent:function (node, filterFn, includeSelf) {
        if (node && !domUtils.isBody(node)) {
            node = includeSelf ? node : node.parentNode;
            while (node) {
                if (!filterFn || filterFn(node) || domUtils.isBody(node)) {
                    return filterFn && !filterFn(node) && domUtils.isBody(node) ? null : node;
                }
                node = node.parentNode;
            }
        }
        return null;
    },
    /**
     * 查詢node的節點名為tagName的第一個祖先節點， 查詢的起點是node節點的父節點。
     * @method findParentByTagName
     * @param { Node } node 需要查詢的節點物件
     * @param { Array } tagNames 需要查詢的父節點的名稱陣列
     * @warning 查詢的終點是到body節點為止
     * @return { Node | NULL } 如果找到符合條件的節點， 則返回該節點， 否則返回NULL
     * @example
     * ```javascript
     * var node = UE.dom.domUtils.findParentByTagName( document.getElementsByTagName("div")[0], [ "BODY" ] );
     * //output: BODY
     * console.log( node.tagName );
     * ```
     */

    /**
     * 查詢node的節點名為tagName的祖先節點， 如果includeSelf的值為true，則查詢的起點是給定的節點node，
     * 否則， 起點是node的父節點。
     * @method findParentByTagName
     * @param { Node } node 需要查詢的節點物件
     * @param { Array } tagNames 需要查詢的父節點的名稱陣列
     * @param { Boolean } includeSelf 查詢過程是否包含node節點自身
     * @warning 查詢的終點是到body節點為止
     * @return { Node | NULL } 如果找到符合條件的節點， 則返回該節點， 否則返回NULL
     * @example
     * ```javascript
     * var queryTarget = document.getElementsByTagName("div")[0];
     * var node = UE.dom.domUtils.findParentByTagName( queryTarget, [ "DIV" ], true );
     * //output: true
     * console.log( queryTarget === node );
     * ```
     */
    findParentByTagName:function (node, tagNames, includeSelf, excludeFn) {
        tagNames = utils.listToMap(utils.isArray(tagNames) ? tagNames : [tagNames]);
        return domUtils.findParent(node, function (node) {
            return tagNames[node.tagName] && !(excludeFn && excludeFn(node));
        }, includeSelf);
    },
    /**
     * 查詢節點node的祖先節點集合， 查詢的起點是給定節點的父節點，結果集中不包含給定的節點。
     * @method findParents
     * @param { Node } node 需要查詢的節點物件
     * @return { Array } 給定節點的祖先節點陣列
     * @grammar UE.dom.domUtils.findParents(node)  => Array  //返回一個祖先節點陣列集合，不包含自身
     * @grammar UE.dom.domUtils.findParents(node,includeSelf)  => Array  //返回一個祖先節點陣列集合，includeSelf指定是否包含自身
     * @grammar UE.dom.domUtils.findParents(node,includeSelf,filterFn)  => Array  //返回一個祖先節點陣列集合，filterFn指定過濾條件，返回true的node將被選取
     * @grammar UE.dom.domUtils.findParents(node,includeSelf,filterFn,closerFirst)  => Array  //返回一個祖先節點陣列集合，closerFirst為true的話，node的直接父親節點是陣列的第0個
     */

    /**
     * 查詢節點node的祖先節點集合， 如果includeSelf的值為true，
     * 則返回的結果集中允許出現當前給定的節點， 否則， 該節點不會出現在其結果集中。
     * @method findParents
     * @param { Node } node 需要查詢的節點物件
     * @param { Boolean } includeSelf 查詢的結果中是否允許包含當前查詢的節點物件
     * @return { Array } 給定節點的祖先節點陣列
     */
    findParents:function (node, includeSelf, filterFn, closerFirst) {
        var parents = includeSelf && ( filterFn && filterFn(node) || !filterFn ) ? [node] : [];
        while (node = domUtils.findParent(node, filterFn)) {
            parents.push(node);
        }
        return closerFirst ? parents : parents.reverse();
    },

    /**
     * 在節點node後面插入新節點newNode
     * @method insertAfter
     * @param { Node } node 目標節點
     * @param { Node } newNode 新插入的節點， 該節點將置於目標節點之後
     * @return { Node } 新插入的節點
     */
    insertAfter:function (node, newNode) {
        return node.nextSibling ? node.parentNode.insertBefore(newNode, node.nextSibling):
            node.parentNode.appendChild(newNode);
    },

    /**
     * 刪除節點node及其下屬的所有節點
     * @method remove
     * @param { Node } node 需要刪除的節點物件
     * @return { Node } 返回剛刪除的節點物件
     * @example
     * ```html
     * <div id="test">
     *     <div id="child">你好</div>
     * </div>
     * <script>
     *     UE.dom.domUtils.remove( document.body, false );
     *     //output: false
     *     console.log( document.getElementById( "child" ) !== null );
     * </script>
     * ```
     */

    /**
     * 刪除節點node，並根據keepChildren的值決定是否保留子節點
     * @method remove
     * @param { Node } node 需要刪除的節點物件
     * @param { Boolean } keepChildren 是否需要保留子節點
     * @return { Node } 返回剛刪除的節點物件
     * @example
     * ```html
     * <div id="test">
     *     <div id="child">你好</div>
     * </div>
     * <script>
     *     UE.dom.domUtils.remove( document.body, true );
     *     //output: true
     *     console.log( document.getElementById( "child" ) !== null );
     * </script>
     * ```
     */
    remove:function (node, keepChildren) {
        var parent = node.parentNode,
            child;
        if (parent) {
            if (keepChildren && node.hasChildNodes()) {
                while (child = node.firstChild) {
                    parent.insertBefore(child, node);
                }
            }
            parent.removeChild(node);
        }
        return node;
    },

    /**
     * 取得node節點的下一個兄弟節點， 如果該節點其後沒有兄弟節點， 則遞迴查詢其父節點之後的第一個兄弟節點，
     * 直到找到滿足條件的節點或者遞迴到BODY節點之後才會結束。
     * @method getNextDomNode
     * @param { Node } node 需要獲取其後的兄弟節點的節點物件
     * @return { Node | NULL } 如果找滿足條件的節點， 則返回該節點， 否則返回NULL
     * @example
     * ```html
     *     <body>
     *      <div id="test">
     *          <span></span>
     *      </div>
     *      <i>xxx</i>
     * </body>
     * <script>
     *
     *     //output: i節點
     *     console.log( UE.dom.domUtils.getNextDomNode( document.getElementById( "test" ) ) );
     *
     * </script>
     * ```
     * @example
     * ```html
     * <body>
     *      <div>
     *          <span></span>
     *          <i id="test">xxx</i>
     *      </div>
     *      <b>xxx</b>
     * </body>
     * <script>
     *
     *     //由於id為test的i節點之後沒有兄弟節點， 則查詢其父節點（div）後面的兄弟節點
     *     //output: b節點
     *     console.log( UE.dom.domUtils.getNextDomNode( document.getElementById( "test" ) ) );
     *
     * </script>
     * ```
     */

    /**
     * 取得node節點的下一個兄弟節點， 如果startFromChild的值為ture，則先獲取其子節點，
     * 如果有子節點則直接返回第一個子節點；如果沒有子節點或者startFromChild的值為false，
     * 則執行<a href="#UE.dom.domUtils.getNextDomNode(Node)">getNextDomNode(Node node)</a>的查詢過程。
     * @method getNextDomNode
     * @param { Node } node 需要獲取其後的兄弟節點的節點物件
     * @param { Boolean } startFromChild 查詢過程是否從其子節點開始
     * @return { Node | NULL } 如果找滿足條件的節點， 則返回該節點， 否則返回NULL
     * @see UE.dom.domUtils.getNextDomNode(Node)
     */
    getNextDomNode:function (node, startFromChild, filterFn, guard) {
        return getDomNode(node, 'firstChild', 'nextSibling', startFromChild, filterFn, guard);
    },
    getPreDomNode:function (node, startFromChild, filterFn, guard) {
        return getDomNode(node, 'lastChild', 'previousSibling', startFromChild, filterFn, guard);
    },
    /**
     * 檢測節點node是否屬是UEditor定義的bookmark節點
     * @method isBookmarkNode
     * @private
     * @param { Node } node 需要檢測的節點物件
     * @return { Boolean } 是否是bookmark節點
     * @example
     * ```html
     * <span id="_baidu_bookmark_1"></span>
     * <script>
     *      var bookmarkNode = document.getElementById("_baidu_bookmark_1");
     *      //output: true
     *      console.log( UE.dom.domUtils.isBookmarkNode( bookmarkNode ) );
     * </script>
     * ```
     */
    isBookmarkNode:function (node) {
        return node.nodeType == 1 && node.id && /^_baidu_bookmark_/i.test(node.id);
    },
    /**
     * 獲取節點node所屬的window物件
     * @method  getWindow
     * @param { Node } node 節點物件
     * @return { Window } 當前節點所屬的window物件
     * @example
     * ```javascript
     * //output: true
     * console.log( UE.dom.domUtils.getWindow( document.body ) === window );
     * ```
     */
    getWindow:function (node) {
        var doc = node.ownerDocument || node;
        return doc.defaultView || doc.parentWindow;
    },
    /**
     * 獲取離nodeA與nodeB最近的公共的祖先節點
     * @method  getCommonAncestor
     * @param { Node } nodeA 第一個節點
     * @param { Node } nodeB 第二個節點
     * @remind 如果給定的兩個節點是同一個節點， 將直接返回該節點。
     * @return { Node | NULL } 如果未找到公共節點， 返回NULL， 否則返回最近的公共祖先節點。
     * @example
     * ```javascript
     * var commonAncestor = UE.dom.domUtils.getCommonAncestor( document.body, document.body.firstChild );
     * //output: true
     * console.log( commonAncestor.tagName.toLowerCase() === 'body' );
     * ```
     */
    getCommonAncestor:function (nodeA, nodeB) {
        if (nodeA === nodeB)
            return nodeA;
        var parentsA = [nodeA] , parentsB = [nodeB], parent = nodeA, i = -1;
        while (parent = parent.parentNode) {
            if (parent === nodeB) {
                return parent;
            }
            parentsA.push(parent);
        }
        parent = nodeB;
        while (parent = parent.parentNode) {
            if (parent === nodeA)
                return parent;
            parentsB.push(parent);
        }
        parentsA.reverse();
        parentsB.reverse();
        while (i++, parentsA[i] === parentsB[i]) {
        }
        return i == 0 ? null : parentsA[i - 1];

    },
    /**
     * 清除node節點左右連續為空的兄弟inline節點
     * @method clearEmptySibling
     * @param { Node } node 執行的節點物件， 如果該節點的左右連續的兄弟節點是空的inline節點，
     * 則這些兄弟節點將被刪除
     * @grammar UE.dom.domUtils.clearEmptySibling(node,ignoreNext)  //ignoreNext指定是否忽略右邊空節點
     * @grammar UE.dom.domUtils.clearEmptySibling(node,ignoreNext,ignorePre)  //ignorePre指定是否忽略左邊空節點
     * @example
     * ```html
     * <body>
     *     <div></div>
     *     <span id="test"></span>
     *     <i></i>
     *     <b></b>
     *     <em>xxx</em>
     *     <span></span>
     * </body>
     * <script>
     *
     *      UE.dom.domUtils.clearEmptySibling( document.getElementById( "test" ) );
     *
     *      //output: <div></div><span id="test"></span><em>xxx</em><span></span>
     *      console.log( document.body.innerHTML );
     *
     * </script>
     * ```
     */

    /**
     * 清除node節點左右連續為空的兄弟inline節點， 如果ignoreNext的值為true，
     * 則忽略對右邊兄弟節點的操作。
     * @method clearEmptySibling
     * @param { Node } node 執行的節點物件， 如果該節點的左右連續的兄弟節點是空的inline節點，
     * @param { Boolean } ignoreNext 是否忽略忽略對右邊的兄弟節點的操作
     * 則這些兄弟節點將被刪除
     * @see UE.dom.domUtils.clearEmptySibling(Node)
     */

    /**
     * 清除node節點左右連續為空的兄弟inline節點， 如果ignoreNext的值為true，
     * 則忽略對右邊兄弟節點的操作， 如果ignorePre的值為true，則忽略對左邊兄弟節點的操作。
     * @method clearEmptySibling
     * @param { Node } node 執行的節點物件， 如果該節點的左右連續的兄弟節點是空的inline節點，
     * @param { Boolean } ignoreNext 是否忽略忽略對右邊的兄弟節點的操作
     * @param { Boolean } ignorePre 是否忽略忽略對左邊的兄弟節點的操作
     * 則這些兄弟節點將被刪除
     * @see UE.dom.domUtils.clearEmptySibling(Node)
     */
    clearEmptySibling:function (node, ignoreNext, ignorePre) {
        function clear(next, dir) {
            var tmpNode;
            while (next && !domUtils.isBookmarkNode(next) && (domUtils.isEmptyInlineElement(next)
                //這裡不能把空格算進來會吧空格幹掉，出現文字間的空格丟掉了
                || !new RegExp('[^\t\n\r' + domUtils.fillChar + ']').test(next.nodeValue) )) {
                tmpNode = next[dir];
                domUtils.remove(next);
                next = tmpNode;
            }
        }
        !ignoreNext && clear(node.nextSibling, 'nextSibling');
        !ignorePre && clear(node.previousSibling, 'previousSibling');
    },
    /**
     * 將一個文字節點textNode拆分成兩個文字節點，offset指定拆分位置
     * @method split
     * @param { Node } textNode 需要拆分的文字節點物件
     * @param { int } offset 需要拆分的位置， 位置計算從0開始
     * @return { Node } 拆分後形成的新節點
     * @example
     * ```html
     * <div id="test">abcdef</div>
     * <script>
     *      var newNode = UE.dom.domUtils.split( document.getElementById( "test" ).firstChild, 3 );
     *      //output: def
     *      console.log( newNode.nodeValue );
     * </script>
     * ```
     */
    split:function (node, offset) {
        var doc = node.ownerDocument;
        if (browser.ie && offset == node.nodeValue.length) {
            var next = doc.createTextNode('');
            return domUtils.insertAfter(node, next);
        }
        var retval = node.splitText(offset);
        //ie8下splitText不會跟新childNodes,我們手動觸發他的更新
        if (browser.ie8) {
            var tmpNode = doc.createTextNode('');
            domUtils.insertAfter(retval, tmpNode);
            domUtils.remove(tmpNode);
        }
        return retval;
    },

    /**
     * 檢測文字節點textNode是否為空節點（包括空格、換行、佔位符等字元）
     * @method  isWhitespace
     * @param { Node } node 需要檢測的節點物件
     * @return { Boolean } 檢測的節點是否為空
     * @example
     * ```html
     * <div id="test">
     *
     * </div>
     * <script>
     *      //output: true
     *      console.log( UE.dom.domUtils.isWhitespace( document.getElementById("test").firstChild ) );
     * </script>
     * ```
     */
    isWhitespace:function (node) {
        return !new RegExp('[^ \t\n\r' + domUtils.fillChar + ']').test(node.nodeValue);
    },
    /**
     * 獲取元素element相對於viewport的位置座標
     * @method getXY
     * @param { Node } element 需要計算位置的節點物件
     * @return { Object } 返回形如{x:left,y:top}的一個key-value對映物件， 其中鍵x代表水平偏移距離，
     *                          y代表垂直偏移距離。
     *
     * @example
     * ```javascript
     * var location = UE.dom.domUtils.getXY( document.getElementById("test") );
     * //output: test的座標為: 12, 24
     * console.log( 'test的座標為： ', location.x, ',', location.y );
     * ```
     */
    getXY:function (element) {
        var x = 0, y = 0;
        while (element.offsetParent) {
            y += element.offsetTop;
            x += element.offsetLeft;
            element = element.offsetParent;
        }
        return { 'x':x, 'y':y};
    },
    /**
     * 為元素element繫結原生DOM事件，type為事件型別，handler為處理函式
     * @method on
     * @param { Node } element 需要繫結事件的節點物件
     * @param { String } type 繫結的事件型別
     * @param { Function } handler 事件處理器
     * @example
     * ```javascript
     * UE.dom.domUtils.on(document.body,"click",function(e){
     *     //e為事件物件，this為被點選元素對戲那個
     * });
     * ```
     */

    /**
     * 為元素element繫結原生DOM事件，type為事件型別，handler為處理函式
     * @method on
     * @param { Node } element 需要繫結事件的節點物件
     * @param { Array } type 繫結的事件型別陣列
     * @param { Function } handler 事件處理器
     * @example
     * ```javascript
     * UE.dom.domUtils.on(document.body,["click","mousedown"],function(evt){
     *     //evt為事件物件，this為被點選元素物件
     * });
     * ```
     */
    on:function (element, type, handler) {

        var types = utils.isArray(type) ? type : utils.trim(type).split(/\s+/),
            k = types.length;
        if (k) while (k--) {
            type = types[k];
            if (element.addEventListener) {
                element.addEventListener(type, handler, false);
            } else {
                if (!handler._d) {
                    handler._d = {
                        els : []
                    };
                }
                var key = type + handler.toString(),index = utils.indexOf(handler._d.els,element);
                if (!handler._d[key] || index == -1) {
                    if(index == -1){
                        handler._d.els.push(element);
                    }
                    if(!handler._d[key]){
                        handler._d[key] = function (evt) {
                            return handler.call(evt.srcElement, evt || window.event);
                        };
                    }


                    element.attachEvent('on' + type, handler._d[key]);
                }
            }
        }
        element = null;
    },
    /**
     * 解除DOM事件繫結
     * @method un
     * @param { Node } element 需要解除事件繫結的節點物件
     * @param { String } type 需要接觸繫結的事件型別
     * @param { Function } handler 對應的事件處理器
     * @example
     * ```javascript
     * UE.dom.domUtils.un(document.body,"click",function(evt){
     *     //evt為事件物件，this為被點選元素物件
     * });
     * ```
     */

    /**
     * 解除DOM事件繫結
     * @method un
     * @param { Node } element 需要解除事件繫結的節點物件
     * @param { Array } type 需要接觸繫結的事件型別陣列
     * @param { Function } handler 對應的事件處理器
     * @example
     * ```javascript
     * UE.dom.domUtils.un(document.body, ["click","mousedown"],function(evt){
     *     //evt為事件物件，this為被點選元素物件
     * });
     * ```
     */
    un:function (element, type, handler) {
        var types = utils.isArray(type) ? type : utils.trim(type).split(/\s+/),
            k = types.length;
        if (k) while (k--) {
            type = types[k];
            if (element.removeEventListener) {
                element.removeEventListener(type, handler, false);
            } else {
                var key = type + handler.toString();
                try{
                    element.detachEvent('on' + type, handler._d ? handler._d[key] : handler);
                }catch(e){}
                if (handler._d && handler._d[key]) {
                    var index = utils.indexOf(handler._d.els,element);
                    if(index!=-1){
                        handler._d.els.splice(index,1);
                    }
                    handler._d.els.length == 0 && delete handler._d[key];
                }
            }
        }
    },

    /**
     * 比較節點nodeA與節點nodeB是否具有相同的標籤名、屬性名以及屬性值
     * @method  isSameElement
     * @param { Node } nodeA 需要比較的節點
     * @param { Node } nodeB 需要比較的節點
     * @return { Boolean } 兩個節點是否具有相同的標籤名、屬性名以及屬性值
     * @example
     * ```html
     * <span style="font-size:12px">ssss</span>
     * <span style="font-size:12px">bbbbb</span>
     * <span style="font-size:13px">ssss</span>
     * <span style="font-size:14px">bbbbb</span>
     *
     * <script>
     *
     *     var nodes = document.getElementsByTagName( "span" );
     *
     *     //output: true
     *     console.log( UE.dom.domUtils.isSameElement( nodes[0], nodes[1] ) );
     *
     *     //output: false
     *     console.log( UE.dom.domUtils.isSameElement( nodes[2], nodes[3] ) );
     *
     * </script>
     * ```
     */
    isSameElement:function (nodeA, nodeB) {
        if (nodeA.tagName != nodeB.tagName) {
            return false;
        }
        var thisAttrs = nodeA.attributes,
            otherAttrs = nodeB.attributes;
        if (!ie && thisAttrs.length != otherAttrs.length) {
            return false;
        }
        var attrA, attrB, al = 0, bl = 0;
        for (var i = 0; attrA = thisAttrs[i++];) {
            if (attrA.nodeName == 'style') {
                if (attrA.specified) {
                    al++;
                }
                if (domUtils.isSameStyle(nodeA, nodeB)) {
                    continue;
                } else {
                    return false;
                }
            }
            if (ie) {
                if (attrA.specified) {
                    al++;
                    attrB = otherAttrs.getNamedItem(attrA.nodeName);
                } else {
                    continue;
                }
            } else {
                attrB = nodeB.attributes[attrA.nodeName];
            }
            if (!attrB.specified || attrA.nodeValue != attrB.nodeValue) {
                return false;
            }
        }
        // 有可能attrB的屬性包含了attrA的屬性之外還有自己的屬性
        if (ie) {
            for (i = 0; attrB = otherAttrs[i++];) {
                if (attrB.specified) {
                    bl++;
                }
            }
            if (al != bl) {
                return false;
            }
        }
        return true;
    },

    /**
     * 判斷節點nodeA與節點nodeB的元素的style屬性是否一致
     * @method isSameStyle
     * @param { Node } nodeA 需要比較的節點
     * @param { Node } nodeB 需要比較的節點
     * @return { Boolean } 兩個節點是否具有相同的style屬性值
     * @example
     * ```html
     * <span style="font-size:12px">ssss</span>
     * <span style="font-size:12px">bbbbb</span>
     * <span style="font-size:13px">ssss</span>
     * <span style="font-size:14px">bbbbb</span>
     *
     * <script>
     *
     *     var nodes = document.getElementsByTagName( "span" );
     *
     *     //output: true
     *     console.log( UE.dom.domUtils.isSameStyle( nodes[0], nodes[1] ) );
     *
     *     //output: false
     *     console.log( UE.dom.domUtils.isSameStyle( nodes[2], nodes[3] ) );
     *
     * </script>
     * ```
     */
    isSameStyle:function (nodeA, nodeB) {
        var styleA = nodeA.style.cssText.replace(/( ?; ?)/g, ';').replace(/( ?: ?)/g, ':'),
            styleB = nodeB.style.cssText.replace(/( ?; ?)/g, ';').replace(/( ?: ?)/g, ':');
        if (browser.opera) {
            styleA = nodeA.style;
            styleB = nodeB.style;
            if (styleA.length != styleB.length)
                return false;
            for (var p in styleA) {
                if (/^(\d+|csstext)$/i.test(p)) {
                    continue;
                }
                if (styleA[p] != styleB[p]) {
                    return false;
                }
            }
            return true;
        }
        if (!styleA || !styleB) {
            return styleA == styleB;
        }
        styleA = styleA.split(';');
        styleB = styleB.split(';');
        if (styleA.length != styleB.length) {
            return false;
        }
        for (var i = 0, ci; ci = styleA[i++];) {
            if (utils.indexOf(styleB, ci) == -1) {
                return false;
            }
        }
        return true;
    },
    /**
     * 檢查節點node是否為block元素
     * @method isBlockElm
     * @param { Node } node 需要檢測的節點物件
     * @return { Boolean } 是否是block元素節點
     * @warning 該方法的判斷規則如下： 如果該元素原本是block元素， 則不論該元素當前的css樣式是什麼都會返回true；
     *          否則，檢測該元素的css樣式， 如果該元素當前是block元素， 則返回true。 其餘情況下都返回false。
     * @example
     * ```html
     * <span id="test1" style="display: block"></span>
     * <span id="test2"></span>
     * <div id="test3" style="display: inline"></div>
     *
     * <script>
     *
     *     //output: true
     *     console.log( UE.dom.domUtils.isBlockElm( document.getElementById("test1") ) );
     *
     *     //output: false
     *     console.log( UE.dom.domUtils.isBlockElm( document.getElementById("test2") ) );
     *
     *     //output: true
     *     console.log( UE.dom.domUtils.isBlockElm( document.getElementById("test3") ) );
     *
     * </script>
     * ```
     */
    isBlockElm:function (node) {
        return node.nodeType == 1 && (dtd.$block[node.tagName] || styleBlock[domUtils.getComputedStyle(node, 'display')]) && !dtd.$nonChild[node.tagName];
    },
    /**
     * 檢測node節點是否為body節點
     * @method isBody
     * @param { Element } node 需要檢測的dom元素
     * @return { Boolean } 給定的元素是否是body元素
     * @example
     * ```javascript
     * //output: true
     * console.log( UE.dom.domUtils.isBody( document.body ) );
     * ```
     */
    isBody:function (node) {
        return  node && node.nodeType == 1 && node.tagName.toLowerCase() == 'body';
    },
    /**
     * 以node節點為分界，將該節點的指定祖先節點parent拆分成兩個獨立的節點，
     * 拆分形成的兩個節點之間是node節點
     * @method breakParent
     * @param { Node } node 作為分界的節點物件
     * @param { Node } parent 該節點必須是node節點的祖先節點， 且是block節點。
     * @return { Node } 給定的node分界節點
     * @example
     * ```javascript
     *
     *      var node = document.createElement("span"),
     *          wrapNode = document.createElement( "div" ),
     *          parent = document.createElement("p");
     *
     *      parent.appendChild( node );
     *      wrapNode.appendChild( parent );
     *
     *      //拆分前
     *      //output: <p><span></span></p>
     *      console.log( wrapNode.innerHTML );
     *
     *
     *      UE.dom.domUtils.breakParent( node, parent );
     *      //拆分後
     *      //output: <p></p><span></span><p></p>
     *      console.log( wrapNode.innerHTML );
     *
     * ```
     */
    breakParent:function (node, parent) {
        var tmpNode,
            parentClone = node,
            clone = node,
            leftNodes,
            rightNodes;
        do {
            parentClone = parentClone.parentNode;
            if (leftNodes) {
                tmpNode = parentClone.cloneNode(false);
                tmpNode.appendChild(leftNodes);
                leftNodes = tmpNode;
                tmpNode = parentClone.cloneNode(false);
                tmpNode.appendChild(rightNodes);
                rightNodes = tmpNode;
            } else {
                leftNodes = parentClone.cloneNode(false);
                rightNodes = leftNodes.cloneNode(false);
            }
            while (tmpNode = clone.previousSibling) {
                leftNodes.insertBefore(tmpNode, leftNodes.firstChild);
            }
            while (tmpNode = clone.nextSibling) {
                rightNodes.appendChild(tmpNode);
            }
            clone = parentClone;
        } while (parent !== parentClone);
        tmpNode = parent.parentNode;
        tmpNode.insertBefore(leftNodes, parent);
        tmpNode.insertBefore(rightNodes, parent);
        tmpNode.insertBefore(node, rightNodes);
        domUtils.remove(parent);
        return node;
    },
    /**
     * 檢查節點node是否是空inline節點
     * @method  isEmptyInlineElement
     * @param { Node } node 需要檢測的節點物件
     * @return { Number }  如果給定的節點是空的inline節點， 則返回1, 否則返回0。
     * @example
     * ```html
     * <b><i></i></b> => 1
     * <b><i></i><u></u></b> => 1
     * <b></b> => 1
     * <b>xx<i></i></b> => 0
     * ```
     */
    isEmptyInlineElement:function (node) {
        if (node.nodeType != 1 || !dtd.$removeEmpty[ node.tagName ]) {
            return 0;
        }
        node = node.firstChild;
        while (node) {
            //如果是建立的bookmark就跳過
            if (domUtils.isBookmarkNode(node)) {
                return 0;
            }
            if (node.nodeType == 1 && !domUtils.isEmptyInlineElement(node) ||
                node.nodeType == 3 && !domUtils.isWhitespace(node)
                ) {
                return 0;
            }
            node = node.nextSibling;
        }
        return 1;

    },

    /**
     * 刪除node節點下首尾兩端的空白文字子節點
     * @method trimWhiteTextNode
     * @param { Element } node 需要執行刪除操作的元素物件
     * @example
     * ```javascript
     *      var node = document.createElement("div");
     *
     *      node.appendChild( document.createTextNode( "" ) );
     *
     *      node.appendChild( document.createElement("div") );
     *
     *      node.appendChild( document.createTextNode( "" ) );
     *
     *      //3
     *      console.log( node.childNodes.length );
     *
     *      UE.dom.domUtils.trimWhiteTextNode( node );
     *
     *      //1
     *      console.log( node.childNodes.length );
     * ```
     */
    trimWhiteTextNode:function (node) {
        function remove(dir) {
            var child;
            while ((child = node[dir]) && child.nodeType == 3 && domUtils.isWhitespace(child)) {
                node.removeChild(child);
            }
        }
        remove('firstChild');
        remove('lastChild');
    },

    /**
     * 合併node節點下相同的子節點
     * @name mergeChild
     * @desc
     * UE.dom.domUtils.mergeChild(node,tagName) //tagName要合併的子節點的標籤
     * @example
     * <p><span style="font-size:12px;">xx<span style="font-size:12px;">aa</span>xx</span></p>
     * ==> UE.dom.domUtils.mergeChild(node,'span')
     * <p><span style="font-size:12px;">xxaaxx</span></p>
     */
    mergeChild:function (node, tagName, attrs) {
        var list = domUtils.getElementsByTagName(node, node.tagName.toLowerCase());
        for (var i = 0, ci; ci = list[i++];) {
            if (!ci.parentNode || domUtils.isBookmarkNode(ci)) {
                continue;
            }
            //span單獨處理
            if (ci.tagName.toLowerCase() == 'span') {
                if (node === ci.parentNode) {
                    domUtils.trimWhiteTextNode(node);
                    if (node.childNodes.length == 1) {
                        node.style.cssText = ci.style.cssText + ";" + node.style.cssText;
                        domUtils.remove(ci, true);
                        continue;
                    }
                }
                ci.style.cssText = node.style.cssText + ';' + ci.style.cssText;
                if (attrs) {
                    var style = attrs.style;
                    if (style) {
                        style = style.split(';');
                        for (var j = 0, s; s = style[j++];) {
                            ci.style[utils.cssStyleToDomStyle(s.split(':')[0])] = s.split(':')[1];
                        }
                    }
                }
                if (domUtils.isSameStyle(ci, node)) {
                    domUtils.remove(ci, true);
                }
                continue;
            }
            if (domUtils.isSameElement(node, ci)) {
                domUtils.remove(ci, true);
            }
        }
    },

    /**
     * 原生方法getElementsByTagName的封裝
     * @method getElementsByTagName
     * @param { Node } node 目標節點物件
     * @param { String } tagName 需要查詢的節點的tagName， 多個tagName以空格分割
     * @return { Array } 符合條件的節點集合
     */
    getElementsByTagName:function (node, name,filter) {
        if(filter && utils.isString(filter)){
           var className = filter;
           filter =  function(node){return domUtils.hasClass(node,className)}
        }
        name = utils.trim(name).replace(/[ ]{2,}/g,' ').split(' ');
        var arr = [];
        for(var n = 0,ni;ni=name[n++];){
            var list = node.getElementsByTagName(ni);
            for (var i = 0, ci; ci = list[i++];) {
                if(!filter || filter(ci))
                    arr.push(ci);
            }
        }

        return arr;
    },
    /**
     * 將節點node提取到父節點上
     * @method mergeToParent
     * @param { Element } node 需要提取的元素物件
     * @example
     * ```html
     * <div id="parent">
     *     <div id="sub">
     *         <span id="child"></span>
     *     </div>
     * </div>
     *
     * <script>
     *
     *     var child = document.getElementById( "child" );
     *
     *     //output: sub
     *     console.log( child.parentNode.id );
     *
     *     UE.dom.domUtils.mergeToParent( child );
     *
     *     //output: parent
     *     console.log( child.parentNode.id );
     *
     * </script>
     * ```
     */
    mergeToParent:function (node) {
        var parent = node.parentNode;
        while (parent && dtd.$removeEmpty[parent.tagName]) {
            if (parent.tagName == node.tagName || parent.tagName == 'A') {//針對a標籤單獨處理
                domUtils.trimWhiteTextNode(parent);
                //span需要特殊處理  不處理這樣的情況 <span stlye="color:#fff">xxx<span style="color:#ccc">xxx</span>xxx</span>
                if (parent.tagName == 'SPAN' && !domUtils.isSameStyle(parent, node)
                    || (parent.tagName == 'A' && node.tagName == 'SPAN')) {
                    if (parent.childNodes.length > 1 || parent !== node.parentNode) {
                        node.style.cssText = parent.style.cssText + ";" + node.style.cssText;
                        parent = parent.parentNode;
                        continue;
                    } else {
                        parent.style.cssText += ";" + node.style.cssText;
                        //trace:952 a標籤要保持下劃線
                        if (parent.tagName == 'A') {
                            parent.style.textDecoration = 'underline';
                        }
                    }
                }
                if (parent.tagName != 'A') {
                    parent === node.parentNode && domUtils.remove(node, true);
                    break;
                }
            }
            parent = parent.parentNode;
        }
    },
    /**
     * 合併節點node的左右兄弟節點
     * @method mergeSibling
     * @param { Element } node 需要合併的目標節點
     * @example
     * ```html
     * <b>xxxx</b><b id="test">ooo</b><b>xxxx</b>
     *
     * <script>
     *     var demoNode = document.getElementById("test");
     *     UE.dom.domUtils.mergeSibling( demoNode );
     *     //output: xxxxoooxxxx
     *     console.log( demoNode.innerHTML );
     * </script>
     * ```
     */

    /**
     * 合併節點node的左右兄弟節點， 可以根據給定的條件選擇是否忽略合併左節點。
     * @method mergeSibling
     * @param { Element } node 需要合併的目標節點
     * @param { Boolean } ignorePre 是否忽略合併左節點
     * @example
     * ```html
     * <b>xxxx</b><b id="test">ooo</b><b>xxxx</b>
     *
     * <script>
     *     var demoNode = document.getElementById("test");
     *     UE.dom.domUtils.mergeSibling( demoNode, true );
     *     //output: oooxxxx
     *     console.log( demoNode.innerHTML );
     * </script>
     * ```
     */

    /**
     * 合併節點node的左右兄弟節點，可以根據給定的條件選擇是否忽略合併左右節點。
     * @method mergeSibling
     * @param { Element } node 需要合併的目標節點
     * @param { Boolean } ignorePre 是否忽略合併左節點
     * @param { Boolean } ignoreNext 是否忽略合併右節點
     * @remind 如果同時忽略左右節點， 則該操作什麼也不會做
     * @example
     * ```html
     * <b>xxxx</b><b id="test">ooo</b><b>xxxx</b>
     *
     * <script>
     *     var demoNode = document.getElementById("test");
     *     UE.dom.domUtils.mergeSibling( demoNode, false, true );
     *     //output: xxxxooo
     *     console.log( demoNode.innerHTML );
     * </script>
     * ```
     */
    mergeSibling:function (node, ignorePre, ignoreNext) {
        function merge(rtl, start, node) {
            var next;
            if ((next = node[rtl]) && !domUtils.isBookmarkNode(next) && next.nodeType == 1 && domUtils.isSameElement(node, next)) {
                while (next.firstChild) {
                    if (start == 'firstChild') {
                        node.insertBefore(next.lastChild, node.firstChild);
                    } else {
                        node.appendChild(next.firstChild);
                    }
                }
                domUtils.remove(next);
            }
        }
        !ignorePre && merge('previousSibling', 'firstChild', node);
        !ignoreNext && merge('nextSibling', 'lastChild', node);
    },

    /**
     * 設定節點node及其子節點不會被選中
     * @method unSelectable
     * @param { Element } node 需要執行操作的dom元素
     * @remind 執行該操作後的節點， 將不能被滑鼠選中
     * @example
     * ```javascript
     * UE.dom.domUtils.unSelectable( document.body );
     * ```
     */
    unSelectable:ie && browser.ie9below || browser.opera ? function (node) {
        //for ie9
        node.onselectstart = function () {
            return false;
        };
        node.onclick = node.onkeyup = node.onkeydown = function () {
            return false;
        };
        node.unselectable = 'on';
        node.setAttribute("unselectable", "on");
        for (var i = 0, ci; ci = node.all[i++];) {
            switch (ci.tagName.toLowerCase()) {
                case 'iframe' :
                case 'textarea' :
                case 'input' :
                case 'select' :
                    break;
                default :
                    ci.unselectable = 'on';
                    node.setAttribute("unselectable", "on");
            }
        }
    } : function (node) {
        node.style.MozUserSelect =
            node.style.webkitUserSelect =
                node.style.msUserSelect =
                    node.style.KhtmlUserSelect = 'none';
    },
    /**
     * 刪除節點node上的指定屬性名稱的屬性
     * @method  removeAttributes
     * @param { Node } node 需要刪除屬性的節點物件
     * @param { String } attrNames 可以是空格隔開的多個屬性名稱，該操作將會依次刪除相應的屬性
     * @example
     * ```html
     * <div id="wrap">
     *      <span style="font-size:14px;" id="test" name="followMe">xxxxx</span>
     * </div>
     *
     * <script>
     *
     *     UE.dom.domUtils.removeAttributes( document.getElementById( "test" ), "id name" );
     *
     *     //output: <span style="font-size:14px;">xxxxx</span>
     *     console.log( document.getElementById("wrap").innerHTML );
     *
     * </script>
     * ```
     */

    /**
     * 刪除節點node上的指定屬性名稱的屬性
     * @method  removeAttributes
     * @param { Node } node 需要刪除屬性的節點物件
     * @param { Array } attrNames 需要刪除的屬性名陣列
     * @example
     * ```html
     * <div id="wrap">
     *      <span style="font-size:14px;" id="test" name="followMe">xxxxx</span>
     * </div>
     *
     * <script>
     *
     *     UE.dom.domUtils.removeAttributes( document.getElementById( "test" ), ["id", "name"] );
     *
     *     //output: <span style="font-size:14px;">xxxxx</span>
     *     console.log( document.getElementById("wrap").innerHTML );
     *
     * </script>
     * ```
     */
    removeAttributes:function (node, attrNames) {
        attrNames = utils.isArray(attrNames) ? attrNames : utils.trim(attrNames).replace(/[ ]{2,}/g,' ').split(' ');
        for (var i = 0, ci; ci = attrNames[i++];) {
            ci = attrFix[ci] || ci;
            switch (ci) {
                case 'className':
                    node[ci] = '';
                    break;
                case 'style':
                    node.style.cssText = '';
                    var val = node.getAttributeNode('style');
                    !browser.ie && val && node.removeAttributeNode(val);
            }
            node.removeAttribute(ci);
        }
    },
    /**
     * 在doc下建立一個標籤名為tag，屬性為attrs的元素
     * @method createElement
     * @param { DomDocument } doc 新建立的元素屬於該document節點建立
     * @param { String } tagName 需要建立的元素的標籤名
     * @param { Object } attrs 新建立的元素的屬性key-value集合
     * @return { Element } 新建立的元素物件
     * @example
     * ```javascript
     * var ele = UE.dom.domUtils.createElement( document, 'div', {
     *     id: 'test'
     * } );
     *
     * //output: DIV
     * console.log( ele.tagName );
     *
     * //output: test
     * console.log( ele.id );
     *
     * ```
     */
    createElement:function (doc, tag, attrs) {
        return domUtils.setAttributes(doc.createElement(tag), attrs)
    },
    /**
     * 為節點node新增屬性attrs，attrs為屬性鍵值對
     * @method setAttributes
     * @param { Element } node 需要設定屬性的元素物件
     * @param { Object } attrs 需要設定的屬性名-值對
     * @return { Element } 設定屬性的元素物件
     * @example
     * ```html
     * <span id="test"></span>
     *
     * <script>
     *
     *     var testNode = UE.dom.domUtils.setAttributes( document.getElementById( "test" ), {
     *         id: 'demo'
     *     } );
     *
     *     //output: demo
     *     console.log( testNode.id );
     *
     * </script>
     *
     */
    setAttributes:function (node, attrs) {
        for (var attr in attrs) {
            if(attrs.hasOwnProperty(attr)){
                var value = attrs[attr];
                switch (attr) {
                    case 'class':
                        //ie下要這樣賦值，setAttribute不起作用
                        node.className = value;
                        break;
                    case 'style' :
                        node.style.cssText = node.style.cssText + ";" + value;
                        break;
                    case 'innerHTML':
                        node[attr] = value;
                        break;
                    case 'value':
                        node.value = value;
                        break;
                    default:
                        node.setAttribute(attrFix[attr] || attr, value);
                }
            }
        }
        return node;
    },

    /**
     * 獲取元素element經過計算後的樣式值
     * @method getComputedStyle
     * @param { Element } element 需要獲取樣式的元素物件
     * @param { String } styleName 需要獲取的樣式名
     * @return { String } 獲取到的樣式值
     * @example
     * ```html
     * <style type="text/css">
     *      #test {
     *          font-size: 15px;
     *      }
     * </style>
     *
     * <span id="test"></span>
     *
     * <script>
     *     //output: 15px
     *     console.log( UE.dom.domUtils.getComputedStyle( document.getElementById( "test" ), 'font-size' ) );
     * </script>
     * ```
     */
    getComputedStyle:function (element, styleName) {
        //一下的屬性單獨處理
        var pros = 'width height top left';

        if(pros.indexOf(styleName) > -1){
            return element['offset' + styleName.replace(/^\w/,function(s){return s.toUpperCase()})] + 'px';
        }
        //忽略文字節點
        if (element.nodeType == 3) {
            element = element.parentNode;
        }
        //ie下font-size若body下定義了font-size，則從currentStyle裡會取到這個font-size. 取不到實際值，故此修改.
        if (browser.ie && browser.version < 9 && styleName == 'font-size' && !element.style.fontSize &&
            !dtd.$empty[element.tagName] && !dtd.$nonChild[element.tagName]) {
            var span = element.ownerDocument.createElement('span');
            span.style.cssText = 'padding:0;border:0;font-family:simsun;';
            span.innerHTML = '.';
            element.appendChild(span);
            var result = span.offsetHeight;
            element.removeChild(span);
            span = null;
            return result + 'px';
        }
        try {
            var value = domUtils.getStyle(element, styleName) ||
                (window.getComputedStyle ? domUtils.getWindow(element).getComputedStyle(element, '').getPropertyValue(styleName) :
                    ( element.currentStyle || element.style )[utils.cssStyleToDomStyle(styleName)]);

        } catch (e) {
            return "";
        }
        return utils.transUnitToPx(utils.fixColor(styleName, value));
    },
    /**
     * 刪除元素element指定的className
     * @method removeClasses
     * @param { Element } ele 需要刪除class的元素節點
     * @param { String } classNames 需要刪除的className， 多個className之間以空格分開
     * @example
     * ```html
     * <span id="test" class="test1 test2 test3">xxx</span>
     *
     * <script>
     *
     *     var testNode = document.getElementById( "test" );
     *     UE.dom.domUtils.removeClasses( testNode, "test1 test2" );
     *
     *     //output: test3
     *     console.log( testNode.className );
     *
     * </script>
     * ```
     */

    /**
     * 刪除元素element指定的className
     * @method removeClasses
     * @param { Element } ele 需要刪除class的元素節點
     * @param { Array } classNames 需要刪除的className陣列
     * @example
     * ```html
     * <span id="test" class="test1 test2 test3">xxx</span>
     *
     * <script>
     *
     *     var testNode = document.getElementById( "test" );
     *     UE.dom.domUtils.removeClasses( testNode, ["test1", "test2"] );
     *
     *     //output: test3
     *     console.log( testNode.className );
     *
     * </script>
     * ```
     */
    removeClasses:function (elm, classNames) {
        classNames = utils.isArray(classNames) ? classNames :
            utils.trim(classNames).replace(/[ ]{2,}/g,' ').split(' ');
        for(var i = 0,ci,cls = elm.className;ci=classNames[i++];){
            cls = cls.replace(new RegExp('\\b' + ci + '\\b'),'')
        }
        cls = utils.trim(cls).replace(/[ ]{2,}/g,' ');
        if(cls){
            elm.className = cls;
        }else{
            domUtils.removeAttributes(elm,['class']);
        }
    },
    /**
     * 給元素element新增className
     * @method addClass
     * @param { Node } ele 需要增加className的元素
     * @param { String } classNames 需要新增的className， 多個className之間以空格分割
     * @remind 相同的類名不會被重複新增
     * @example
     * ```html
     * <span id="test" class="cls1 cls2"></span>
     *
     * <script>
     *     var testNode = document.getElementById("test");
     *
     *     UE.dom.domUtils.addClass( testNode, "cls2 cls3 cls4" );
     *
     *     //output: cl1 cls2 cls3 cls4
     *     console.log( testNode.className );
     *
     * <script>
     * ```
     */

    /**
     * 給元素element新增className
     * @method addClass
     * @param { Node } ele 需要增加className的元素
     * @param { Array } classNames 需要新增的className的陣列
     * @remind 相同的類名不會被重複新增
     * @example
     * ```html
     * <span id="test" class="cls1 cls2"></span>
     *
     * <script>
     *     var testNode = document.getElementById("test");
     *
     *     UE.dom.domUtils.addClass( testNode, ["cls2", "cls3", "cls4"] );
     *
     *     //output: cl1 cls2 cls3 cls4
     *     console.log( testNode.className );
     *
     * <script>
     * ```
     */
    addClass:function (elm, classNames) {
        if(!elm)return;
        classNames = utils.trim(classNames).replace(/[ ]{2,}/g,' ').split(' ');
        for(var i = 0,ci,cls = elm.className;ci=classNames[i++];){
            if(!new RegExp('\\b' + ci + '\\b').test(cls)){
                cls += ' ' + ci;
            }
        }
        elm.className = utils.trim(cls);
    },
    /**
     * 判斷元素element是否包含給定的樣式類名className
     * @method hasClass
     * @param { Node } ele 需要檢測的元素
     * @param { String } classNames 需要檢測的className， 多個className之間用空格分割
     * @return { Boolean } 元素是否包含所有給定的className
     * @example
     * ```html
     * <span id="test1" class="cls1 cls2"></span>
     *
     * <script>
     *     var test1 = document.getElementById("test1");
     *
     *     //output: false
     *     console.log( UE.dom.domUtils.hasClass( test1, "cls2 cls1 cls3" ) );
     *
     *     //output: true
     *     console.log( UE.dom.domUtils.hasClass( test1, "cls2 cls1" ) );
     * </script>
     * ```
     */

    /**
     * 判斷元素element是否包含給定的樣式類名className
     * @method hasClass
     * @param { Node } ele 需要檢測的元素
     * @param { Array } classNames 需要檢測的className陣列
     * @return { Boolean } 元素是否包含所有給定的className
     * @example
     * ```html
     * <span id="test1" class="cls1 cls2"></span>
     *
     * <script>
     *     var test1 = document.getElementById("test1");
     *
     *     //output: false
     *     console.log( UE.dom.domUtils.hasClass( test1, [ "cls2", "cls1", "cls3" ] ) );
     *
     *     //output: true
     *     console.log( UE.dom.domUtils.hasClass( test1, [ "cls2", "cls1" ]) );
     * </script>
     * ```
     */
    hasClass:function (element, className) {
        if(utils.isRegExp(className)){
            return className.test(element.className)
        }
        className = utils.trim(className).replace(/[ ]{2,}/g,' ').split(' ');
        for(var i = 0,ci,cls = element.className;ci=className[i++];){
            if(!new RegExp('\\b' + ci + '\\b','i').test(cls)){
                return false;
            }
        }
        return i - 1 == className.length;
    },

    /**
     * 阻止事件預設行為
     * @method preventDefault
     * @param { Event } evt 需要阻止預設行為的事件物件
     * @example
     * ```javascript
     * UE.dom.domUtils.preventDefault( evt );
     * ```
     */
    preventDefault:function (evt) {
        evt.preventDefault ? evt.preventDefault() : (evt.returnValue = false);
    },
    /**
     * 刪除元素element指定的樣式
     * @method removeStyle
     * @param { Element } element 需要刪除樣式的元素
     * @param { String } styleName 需要刪除的樣式名
     * @example
     * ```html
     * <span id="test" style="color: red; background: blue;"></span>
     *
     * <script>
     *
     *     var testNode = document.getElementById("test");
     *
     *     UE.dom.domUtils.removeStyle( testNode, 'color' );
     *
     *     //output: background: blue;
     *     console.log( testNode.style.cssText );
     *
     * </script>
     * ```
     */
    removeStyle:function (element, name) {
        if(browser.ie ){
            //針對color先單獨處理一下
            if(name == 'color'){
                name = '(^|;)' + name;
            }
            element.style.cssText = element.style.cssText.replace(new RegExp(name + '[^:]*:[^;]+;?','ig'),'')
        }else{
            if (element.style.removeProperty) {
                element.style.removeProperty (name);
            }else {
                element.style.removeAttribute (utils.cssStyleToDomStyle(name));
            }
        }


        if (!element.style.cssText) {
            domUtils.removeAttributes(element, ['style']);
        }
    },
    /**
     * 獲取元素element的style屬性的指定值
     * @method getStyle
     * @param { Element } element 需要獲取屬性值的元素
     * @param { String } styleName 需要獲取的style的名稱
     * @warning 該方法僅獲取元素style屬性中所標明的值
     * @return { String } 該元素包含指定的style屬性值
     * @example
     * ```html
     * <div id="test" style="color: red;"></div>
     *
     * <script>
     *
     *      var testNode = document.getElementById( "test" );
     *
     *      //output: red
     *      console.log( UE.dom.domUtils.getStyle( testNode, "color" ) );
     *
     *      //output: ""
     *      console.log( UE.dom.domUtils.getStyle( testNode, "background" ) );
     *
     * </script>
     * ```
     */
    getStyle:function (element, name) {
        var value = element.style[ utils.cssStyleToDomStyle(name) ];
        return utils.fixColor(name, value);
    },
    /**
     * 為元素element設定樣式屬性值
     * @method setStyle
     * @param { Element } element 需要設定樣式的元素
     * @param { String } styleName 樣式名
     * @param { String } styleValue 樣式值
     * @example
     * ```html
     * <div id="test"></div>
     *
     * <script>
     *
     *      var testNode = document.getElementById( "test" );
     *
     *      //output: ""
     *      console.log( testNode.style.color );
     *
     *      UE.dom.domUtils.setStyle( testNode, 'color', 'red' );
     *      //output: "red"
     *      console.log( testNode.style.color );
     *
     * </script>
     * ```
     */
    setStyle:function (element, name, value) {
        element.style[utils.cssStyleToDomStyle(name)] = value;
        if(!utils.trim(element.style.cssText)){
            this.removeAttributes(element,'style')
        }
    },
    /**
     * 為元素element設定多個樣式屬性值
     * @method setStyles
     * @param { Element } element 需要設定樣式的元素
     * @param { Object } styles 樣式名值對
     * @example
     * ```html
     * <div id="test"></div>
     *
     * <script>
     *
     *      var testNode = document.getElementById( "test" );
     *
     *      //output: ""
     *      console.log( testNode.style.color );
     *
     *      UE.dom.domUtils.setStyles( testNode, {
     *          'color': 'red'
     *      } );
     *      //output: "red"
     *      console.log( testNode.style.color );
     *
     * </script>
     * ```
     */
    setStyles:function (element, styles) {
        for (var name in styles) {
            if (styles.hasOwnProperty(name)) {
                domUtils.setStyle(element, name, styles[name]);
            }
        }
    },
    /**
     * 刪除_moz_dirty屬性
     * @private
     * @method removeDirtyAttr
     */
    removeDirtyAttr:function (node) {
        for (var i = 0, ci, nodes = node.getElementsByTagName('*'); ci = nodes[i++];) {
            ci.removeAttribute('_moz_dirty');
        }
        node.removeAttribute('_moz_dirty');
    },
    /**
     * 獲取子節點的數量
     * @method getChildCount
     * @param { Element } node 需要檢測的元素
     * @return { Number } 給定的node元素的子節點數量
     * @example
     * ```html
     * <div id="test">
     *      <span></span>
     * </div>
     *
     * <script>
     *
     *     //output: 3
     *     console.log( UE.dom.domUtils.getChildCount( document.getElementById("test") ) );
     *
     * </script>
     * ```
     */

    /**
     * 根據給定的過濾規則， 獲取符合條件的子節點的數量
     * @method getChildCount
     * @param { Element } node 需要檢測的元素
     * @param { Function } fn 過濾器， 要求對符合條件的子節點返回true， 反之則要求返回false
     * @return { Number } 符合過濾條件的node元素的子節點數量
     * @example
     * ```html
     * <div id="test">
     *      <span></span>
     * </div>
     *
     * <script>
     *
     *     //output: 1
     *     console.log( UE.dom.domUtils.getChildCount( document.getElementById("test"), function ( node ) {
     *
     *         return node.nodeType === 1;
     *
     *     } ) );
     *
     * </script>
     * ```
     */
    getChildCount:function (node, fn) {
        var count = 0, first = node.firstChild;
        fn = fn || function () {
            return 1;
        };
        while (first) {
            if (fn(first)) {
                count++;
            }
            first = first.nextSibling;
        }
        return count;
    },

    /**
     * 判斷給定節點是否為空節點
     * @method isEmptyNode
     * @param { Node } node 需要檢測的節點物件
     * @return { Boolean } 節點是否為空
     * @example
     * ```javascript
     * UE.dom.domUtils.isEmptyNode( document.body );
     * ```
     */
    isEmptyNode:function (node) {
        return !node.firstChild || domUtils.getChildCount(node, function (node) {
            return  !domUtils.isBr(node) && !domUtils.isBookmarkNode(node) && !domUtils.isWhitespace(node)
        }) == 0
    },
    clearSelectedArr:function (nodes) {
        var node;
        while (node = nodes.pop()) {
            domUtils.removeAttributes(node, ['class']);
        }
    },
    /**
     * 將顯示區域滾動到指定節點的位置
     * @method scrollToView
     * @param    {Node}   node    節點
     * @param    {window}   win      window物件
     * @param    {Number}    offsetTop    距離上方的偏移量
     */
    scrollToView:function (node, win, offsetTop) {
        var getViewPaneSize = function () {
                var doc = win.document,
                    mode = doc.compatMode == 'CSS1Compat';
                return {
                    width:( mode ? doc.documentElement.clientWidth : doc.body.clientWidth ) || 0,
                    height:( mode ? doc.documentElement.clientHeight : doc.body.clientHeight ) || 0
                };
            },
            getScrollPosition = function (win) {
                if ('pageXOffset' in win) {
                    return {
                        x:win.pageXOffset || 0,
                        y:win.pageYOffset || 0
                    };
                }
                else {
                    var doc = win.document;
                    return {
                        x:doc.documentElement.scrollLeft || doc.body.scrollLeft || 0,
                        y:doc.documentElement.scrollTop || doc.body.scrollTop || 0
                    };
                }
            };
        var winHeight = getViewPaneSize().height, offset = winHeight * -1 + offsetTop;
        offset += (node.offsetHeight || 0);
        var elementPosition = domUtils.getXY(node);
        offset += elementPosition.y;
        var currentScroll = getScrollPosition(win).y;
        // offset += 50;
        if (offset > currentScroll || offset < currentScroll - winHeight) {
            win.scrollTo(0, offset + (offset < 0 ? -20 : 20));
        }
    },
    /**
     * 判斷給定節點是否為br
     * @method isBr
     * @param { Node } node 需要判斷的節點物件
     * @return { Boolean } 給定的節點是否是br節點
     */
    isBr:function (node) {
        return node.nodeType == 1 && node.tagName == 'BR';
    },
    /**
     * 判斷給定的節點是否是一個“填充”節點
     * @private
     * @method isFillChar
     * @param { Node } node 需要判斷的節點
     * @param { Boolean } isInStart 是否從節點內容的開始位置匹配
     * @returns { Boolean } 節點是否是填充節點
     */
    isFillChar:function (node,isInStart) {
        if(node.nodeType != 3)
            return false;
        var text = node.nodeValue;
        if(isInStart){
            return new RegExp('^' + domUtils.fillChar).test(text)
        }
        return !text.replace(new RegExp(domUtils.fillChar,'g'), '').length
    },
    isStartInblock:function (range) {
        var tmpRange = range.cloneRange(),
            flag = 0,
            start = tmpRange.startContainer,
            tmp;
        if(start.nodeType == 1 && start.childNodes[tmpRange.startOffset]){
            start = start.childNodes[tmpRange.startOffset];
            var pre = start.previousSibling;
            while(pre && domUtils.isFillChar(pre)){
                start = pre;
                pre = pre.previousSibling;
            }
        }
        if(this.isFillChar(start,true) && tmpRange.startOffset == 1){
            tmpRange.setStartBefore(start);
            start = tmpRange.startContainer;
        }

        while (start && domUtils.isFillChar(start)) {
            tmp = start;
            start = start.previousSibling
        }
        if (tmp) {
            tmpRange.setStartBefore(tmp);
            start = tmpRange.startContainer;
        }
        if (start.nodeType == 1 && domUtils.isEmptyNode(start) && tmpRange.startOffset == 1) {
            tmpRange.setStart(start, 0).collapse(true);
        }
        while (!tmpRange.startOffset) {
            start = tmpRange.startContainer;
            if (domUtils.isBlockElm(start) || domUtils.isBody(start)) {
                flag = 1;
                break;
            }
            var pre = tmpRange.startContainer.previousSibling,
                tmpNode;
            if (!pre) {
                tmpRange.setStartBefore(tmpRange.startContainer);
            } else {
                while (pre && domUtils.isFillChar(pre)) {
                    tmpNode = pre;
                    pre = pre.previousSibling;
                }
                if (tmpNode) {
                    tmpRange.setStartBefore(tmpNode);
                } else {
                    tmpRange.setStartBefore(tmpRange.startContainer);
                }
            }
        }
        return flag && !domUtils.isBody(tmpRange.startContainer) ? 1 : 0;
    },

    /**
     * 判斷給定的元素是否是一個空元素
     * @method isEmptyBlock
     * @param { Element } node 需要判斷的元素
     * @return { Boolean } 是否是空元素
     * @example
     * ```html
     * <div id="test"></div>
     *
     * <script>
     *     //output: true
     *     console.log( UE.dom.domUtils.isEmptyBlock( document.getElementById("test") ) );
     * </script>
     * ```
     */

    /**
     * 根據指定的判斷規則判斷給定的元素是否是一個空元素
     * @method isEmptyBlock
     * @param { Element } node 需要判斷的元素
     * @param { RegExp } reg 對內容執行判斷的正則表示式物件
     * @return { Boolean } 是否是空元素
     */
    isEmptyBlock:function (node,reg) {
        if(node.nodeType != 1)
            return 0;
        reg = reg || new RegExp('[ \xa0\t\r\n' + domUtils.fillChar + ']', 'g');

        if (node[browser.ie ? 'innerText' : 'textContent'].replace(reg, '').length > 0) {
            return 0;
        }
        for (var n in dtd.$isNotEmpty) {
            if (node.getElementsByTagName(n).length) {
                return 0;
            }
        }
        return 1;
    },

    /**
     * 移動元素使得該元素的位置移動指定的偏移量的距離
     * @method setViewportOffset
     * @param { Element } element 需要設定偏移量的元素
     * @param { Object } offset 偏移量， 形如{ left: 100, top: 50 }的一個鍵值對， 表示該元素將在
     *                                  現有的位置上向水平方向偏移offset.left的距離， 在豎直方向上偏移
     *                                  offset.top的距離
     * @example
     * ```html
     * <div id="test" style="top: 100px; left: 50px; position: absolute;"></div>
     *
     * <script>
     *
     *     var testNode = document.getElementById("test");
     *
     *     UE.dom.domUtils.setViewportOffset( testNode, {
     *         left: 200,
     *         top: 50
     *     } );
     *
     *     //output: top: 300px; left: 100px; position: absolute;
     *     console.log( testNode.style.cssText );
     *
     * </script>
     * ```
     */
    setViewportOffset:function (element, offset) {
        var left = parseInt(element.style.left) | 0;
        var top = parseInt(element.style.top) | 0;
        var rect = element.getBoundingClientRect();
        var offsetLeft = offset.left - rect.left;
        var offsetTop = offset.top - rect.top;
        if (offsetLeft) {
            element.style.left = left + offsetLeft + 'px';
        }
        if (offsetTop) {
            element.style.top = top + offsetTop + 'px';
        }
    },

    /**
     * 用“填充字元”填充節點
     * @method fillNode
     * @private
     * @param { DomDocument } doc 填充的節點所在的docment物件
     * @param { Node } node 需要填充的節點物件
     * @example
     * ```html
     * <div id="test"></div>
     *
     * <script>
     *     var testNode = document.getElementById("test");
     *
     *     //output: 0
     *     console.log( testNode.childNodes.length );
     *
     *     UE.dom.domUtils.fillNode( document, testNode );
     *
     *     //output: 1
     *     console.log( testNode.childNodes.length );
     *
     * </script>
     * ```
     */
    fillNode:function (doc, node) {
        var tmpNode = browser.ie ? doc.createTextNode(domUtils.fillChar) : doc.createElement('br');
        node.innerHTML = '';
        node.appendChild(tmpNode);
    },

    /**
     * 把節點src的所有子節點追加到另一個節點tag上去
     * @method moveChild
     * @param { Node } src 源節點， 該節點下的所有子節點將被移除
     * @param { Node } tag 目標節點， 從源節點移除的子節點將被追加到該節點下
     * @example
     * ```html
     * <div id="test1">
     *      <span></span>
     * </div>
     * <div id="test2">
     *     <div></div>
     * </div>
     *
     * <script>
     *
     *     var test1 = document.getElementById("test1"),
     *         test2 = document.getElementById("test2");
     *
     *     UE.dom.domUtils.moveChild( test1, test2 );
     *
     *     //output: ""（空字串）
     *     console.log( test1.innerHTML );
     *
     *     //output: "<div></div><span></span>"
     *     console.log( test2.innerHTML );
     *
     * </script>
     * ```
     */

    /**
     * 把節點src的所有子節點移動到另一個節點tag上去, 可以通過dir引數控制附加的行為是“追加”還是“插入頂部”
     * @method moveChild
     * @param { Node } src 源節點， 該節點下的所有子節點將被移除
     * @param { Node } tag 目標節點， 從源節點移除的子節點將被附加到該節點下
     * @param { Boolean } dir 附加方式， 如果為true， 則附加進去的節點將被放到目標節點的頂部， 反之，則放到末尾
     * @example
     * ```html
     * <div id="test1">
     *      <span></span>
     * </div>
     * <div id="test2">
     *     <div></div>
     * </div>
     *
     * <script>
     *
     *     var test1 = document.getElementById("test1"),
     *         test2 = document.getElementById("test2");
     *
     *     UE.dom.domUtils.moveChild( test1, test2, true );
     *
     *     //output: ""（空字串）
     *     console.log( test1.innerHTML );
     *
     *     //output: "<span></span><div></div>"
     *     console.log( test2.innerHTML );
     *
     * </script>
     * ```
     */
    moveChild:function (src, tag, dir) {
        while (src.firstChild) {
            if (dir && tag.firstChild) {
                tag.insertBefore(src.lastChild, tag.firstChild);
            } else {
                tag.appendChild(src.firstChild);
            }
        }
    },

    /**
     * 判斷節點的標籤上是否不存在任何屬性
     * @method hasNoAttributes
     * @private
     * @param { Node } node 需要檢測的節點物件
     * @return { Boolean } 節點是否不包含任何屬性
     * @example
     * ```html
     * <div id="test"><span>xxxx</span></div>
     *
     * <script>
     *
     *     //output: false
     *     console.log( UE.dom.domUtils.hasNoAttributes( document.getElementById("test") ) );
     *
     *     //output: true
     *     console.log( UE.dom.domUtils.hasNoAttributes( document.getElementById("test").firstChild ) );
     *
     * </script>
     * ```
     */
    hasNoAttributes:function (node) {
        return browser.ie ? /^<\w+\s*?>/.test(node.outerHTML) : node.attributes.length == 0;
    },

    /**
     * 檢測節點是否是UEditor所使用的輔助節點
     * @method isCustomeNode
     * @private
     * @param { Node } node 需要檢測的節點
     * @remind 輔助節點是指編輯器要完成工作臨時新增的節點， 在輸出的時候將會從編輯器內移除， 不會影響最終的結果。
     * @return { Boolean } 給定的節點是否是一個輔助節點
     */
    isCustomeNode:function (node) {
        return node.nodeType == 1 && node.getAttribute('_ue_custom_node_');
    },

    /**
     * 檢測節點的標籤是否是給定的標籤
     * @method isTagNode
     * @param { Node } node 需要檢測的節點物件
     * @param { String } tagName 標籤
     * @return { Boolean } 節點的標籤是否是給定的標籤
     * @example
     * ```html
     * <div id="test"></div>
     *
     * <script>
     *
     *     //output: true
     *     console.log( UE.dom.domUtils.isTagNode( document.getElementById("test"), "div" ) );
     *
     * </script>
     * ```
     */
    isTagNode:function (node, tagNames) {
        return node.nodeType == 1 && new RegExp('\\b' + node.tagName + '\\b','i').test(tagNames)
    },

    /**
     * 給定一個節點陣列，在通過指定的過濾器過濾後， 獲取其中滿足過濾條件的第一個節點
     * @method filterNodeList
     * @param { Array } nodeList 需要過濾的節點陣列
     * @param { Function } fn 過濾器， 對符合條件的節點， 執行結果返回true， 反之則返回false
     * @return { Node | NULL } 如果找到符合過濾條件的節點， 則返回該節點， 否則返回NULL
     * @example
     * ```javascript
     * var divNodes = document.getElementsByTagName("div");
     * divNodes = [].slice.call( divNodes, 0 );
     *
     * //output: null
     * console.log( UE.dom.domUtils.filterNodeList( divNodes, function ( node ) {
     *     return node.tagName.toLowerCase() !== 'div';
     * } ) );
     * ```
     */

    /**
     * 給定一個節點陣列nodeList和一組標籤名tagNames， 獲取其中能夠匹配標籤名的節點集合中的第一個節點
     * @method filterNodeList
     * @param { Array } nodeList 需要過濾的節點陣列
     * @param { String } tagNames 需要匹配的標籤名， 多個標籤名之間用空格分割
     * @return { Node | NULL } 如果找到標籤名匹配的節點， 則返回該節點， 否則返回NULL
     * @example
     * ```javascript
     * var divNodes = document.getElementsByTagName("div");
     * divNodes = [].slice.call( divNodes, 0 );
     *
     * //output: null
     * console.log( UE.dom.domUtils.filterNodeList( divNodes, 'a span' ) );
     * ```
     */

    /**
     * 給定一個節點陣列，在通過指定的過濾器過濾後， 如果引數forAll為true， 則會返回所有滿足過濾
     * 條件的節點集合， 否則， 返回滿足條件的節點集合中的第一個節點
     * @method filterNodeList
     * @param { Array } nodeList 需要過濾的節點陣列
     * @param { Function } fn 過濾器， 對符合條件的節點， 執行結果返回true， 反之則返回false
     * @param { Boolean } forAll 是否返回整個節點陣列, 如果該引數為false， 則返回節點集合中的第一個節點
     * @return { Array | Node | NULL } 如果找到符合過濾條件的節點， 則根據引數forAll的值決定返回滿足
     *                                      過濾條件的節點陣列或第一個節點， 否則返回NULL
     * @example
     * ```javascript
     * var divNodes = document.getElementsByTagName("div");
     * divNodes = [].slice.call( divNodes, 0 );
     *
     * //output: 3（假定有3個div）
     * console.log( divNodes.length );
     *
     * var nodes = UE.dom.domUtils.filterNodeList( divNodes, function ( node ) {
     *     return node.tagName.toLowerCase() === 'div';
     * }, true );
     *
     * //output: 3
     * console.log( nodes.length );
     *
     * var node = UE.dom.domUtils.filterNodeList( divNodes, function ( node ) {
     *     return node.tagName.toLowerCase() === 'div';
     * }, false );
     *
     * //output: div
     * console.log( node.nodeName );
     * ```
     */
    filterNodeList : function(nodelist,filter,forAll){
        var results = [];
        if(!utils .isFunction(filter)){
            var str = filter;
            filter = function(n){
                return utils.indexOf(utils.isArray(str) ? str:str.split(' '), n.tagName.toLowerCase()) != -1
            };
        }
        utils.each(nodelist,function(n){
            filter(n) && results.push(n)
        });
        return results.length  == 0 ? null : results.length == 1 || !forAll ? results[0] : results
    },

    /**
     * 查詢給定的range選區是否在給定的node節點內，且在該節點的最末尾
     * @method isInNodeEndBoundary
     * @param { UE.dom.Range } rng 需要判斷的range物件， 該物件的startContainer不能為NULL
     * @param node 需要檢測的節點物件
     * @return { Number } 如果給定的選取range物件是在node內部的最末端， 則返回1, 否則返回0
     */
    isInNodeEndBoundary : function (rng,node){
        var start = rng.startContainer;
        if(start.nodeType == 3 && rng.startOffset != start.nodeValue.length){
            return 0;
        }
        if(start.nodeType == 1 && rng.startOffset != start.childNodes.length){
            return 0;
        }
        while(start !== node){
            if(start.nextSibling){
                return 0
            };
            start = start.parentNode;
        }
        return 1;
    },
    isBoundaryNode : function (node,dir){
        var tmp;
        while(!domUtils.isBody(node)){
            tmp = node;
            node = node.parentNode;
            if(tmp !== node[dir]){
                return false;
            }
        }
        return true;
    },
    fillHtml :  browser.ie11below ? '&nbsp;' : '<br/>'
};
var fillCharReg = new RegExp(domUtils.fillChar, 'g');

// core/Range.js
/**
 * Range封裝
 * @file
 * @module UE.dom
 * @class Range
 * @since 1.2.6.1
 */

/**
 * dom操作封裝
 * @unfile
 * @module UE.dom
 */

/**
 * Range實現類，本類是UEditor底層核心類，封裝不同瀏覽器之間的Range操作。
 * @unfile
 * @module UE.dom
 * @class Range
 */


(function () {
    var guid = 0,
        fillChar = domUtils.fillChar,
        fillData;

    /**
     * 更新range的collapse狀態
     * @param  {Range}   range    range物件
     */
    function updateCollapse(range) {
        range.collapsed =
            range.startContainer && range.endContainer &&
                range.startContainer === range.endContainer &&
                range.startOffset == range.endOffset;
    }

    function selectOneNode(rng){
        return !rng.collapsed && rng.startContainer.nodeType == 1 && rng.startContainer === rng.endContainer && rng.endOffset - rng.startOffset == 1
    }
    function setEndPoint(toStart, node, offset, range) {
        //如果node是自閉合標籤要處理
        if (node.nodeType == 1 && (dtd.$empty[node.tagName] || dtd.$nonChild[node.tagName])) {
            offset = domUtils.getNodeIndex(node) + (toStart ? 0 : 1);
            node = node.parentNode;
        }
        if (toStart) {
            range.startContainer = node;
            range.startOffset = offset;
            if (!range.endContainer) {
                range.collapse(true);
            }
        } else {
            range.endContainer = node;
            range.endOffset = offset;
            if (!range.startContainer) {
                range.collapse(false);
            }
        }
        updateCollapse(range);
        return range;
    }

    function execContentsAction(range, action) {
        //調整邊界
        //range.includeBookmark();
        var start = range.startContainer,
            end = range.endContainer,
            startOffset = range.startOffset,
            endOffset = range.endOffset,
            doc = range.document,
            frag = doc.createDocumentFragment(),
            tmpStart, tmpEnd;
        if (start.nodeType == 1) {
            start = start.childNodes[startOffset] || (tmpStart = start.appendChild(doc.createTextNode('')));
        }
        if (end.nodeType == 1) {
            end = end.childNodes[endOffset] || (tmpEnd = end.appendChild(doc.createTextNode('')));
        }
        if (start === end && start.nodeType == 3) {
            frag.appendChild(doc.createTextNode(start.substringData(startOffset, endOffset - startOffset)));
            //is not clone
            if (action) {
                start.deleteData(startOffset, endOffset - startOffset);
                range.collapse(true);
            }
            return frag;
        }
        var current, currentLevel, clone = frag,
            startParents = domUtils.findParents(start, true), endParents = domUtils.findParents(end, true);
        for (var i = 0; startParents[i] == endParents[i];) {
            i++;
        }
        for (var j = i, si; si = startParents[j]; j++) {
            current = si.nextSibling;
            if (si == start) {
                if (!tmpStart) {
                    if (range.startContainer.nodeType == 3) {
                        clone.appendChild(doc.createTextNode(start.nodeValue.slice(startOffset)));
                        //is not clone
                        if (action) {
                            start.deleteData(startOffset, start.nodeValue.length - startOffset);
                        }
                    } else {
                        clone.appendChild(!action ? start.cloneNode(true) : start);
                    }
                }
            } else {
                currentLevel = si.cloneNode(false);
                clone.appendChild(currentLevel);
            }
            while (current) {
                if (current === end || current === endParents[j]) {
                    break;
                }
                si = current.nextSibling;
                clone.appendChild(!action ? current.cloneNode(true) : current);
                current = si;
            }
            clone = currentLevel;
        }
        clone = frag;
        if (!startParents[i]) {
            clone.appendChild(startParents[i - 1].cloneNode(false));
            clone = clone.firstChild;
        }
        for (var j = i, ei; ei = endParents[j]; j++) {
            current = ei.previousSibling;
            if (ei == end) {
                if (!tmpEnd && range.endContainer.nodeType == 3) {
                    clone.appendChild(doc.createTextNode(end.substringData(0, endOffset)));
                    //is not clone
                    if (action) {
                        end.deleteData(0, endOffset);
                    }
                }
            } else {
                currentLevel = ei.cloneNode(false);
                clone.appendChild(currentLevel);
            }
            //如果兩端同級，右邊第一次已經被開始做了
            if (j != i || !startParents[i]) {
                while (current) {
                    if (current === start) {
                        break;
                    }
                    ei = current.previousSibling;
                    clone.insertBefore(!action ? current.cloneNode(true) : current, clone.firstChild);
                    current = ei;
                }
            }
            clone = currentLevel;
        }
        if (action) {
            range.setStartBefore(!endParents[i] ? endParents[i - 1] : !startParents[i] ? startParents[i - 1] : endParents[i]).collapse(true);
        }
        tmpStart && domUtils.remove(tmpStart);
        tmpEnd && domUtils.remove(tmpEnd);
        return frag;
    }

    /**
     * 建立一個跟document繫結的空的Range例項
     * @constructor
     * @param { Document } document 新建的選區所屬的文件物件
     */

    /**
     * @property { Node } startContainer 當前Range的開始邊界的容器節點, 可以是一個元素節點或者是文字節點
     */

    /**
     * @property { Node } startOffset 當前Range的開始邊界容器節點的偏移量, 如果是元素節點，
     *                              該值就是childNodes中的第幾個節點， 如果是文字節點就是文字內容的第幾個字元
     */

    /**
     * @property { Node } endContainer 當前Range的結束邊界的容器節點, 可以是一個元素節點或者是文字節點
     */

    /**
     * @property { Node } endOffset 當前Range的結束邊界容器節點的偏移量, 如果是元素節點，
     *                              該值就是childNodes中的第幾個節點， 如果是文字節點就是文字內容的第幾個字元
     */

    /**
     * @property { Boolean } collapsed 當前Range是否閉合
     * @default true
     * @remind Range是閉合的時候， startContainer === endContainer && startOffset === endOffset
     */

    /**
     * @property { Document } document 當前Range所屬的Document物件
     * @remind 不同range的的document屬性可以是不同的
     */
    var Range = dom.Range = function (document) {
        var me = this;
        me.startContainer =
            me.startOffset =
                me.endContainer =
                    me.endOffset = null;
        me.document = document;
        me.collapsed = true;
    };

    /**
     * 刪除fillData
     * @param doc
     * @param excludeNode
     */
    function removeFillData(doc, excludeNode) {
        try {
            if (fillData && domUtils.inDoc(fillData, doc)) {
                if (!fillData.nodeValue.replace(fillCharReg, '').length) {
                    var tmpNode = fillData.parentNode;
                    domUtils.remove(fillData);
                    while (tmpNode && domUtils.isEmptyInlineElement(tmpNode) &&
                        //safari的contains有bug
                        (browser.safari ? !(domUtils.getPosition(tmpNode,excludeNode) & domUtils.POSITION_CONTAINS) : !tmpNode.contains(excludeNode))
                        ) {
                        fillData = tmpNode.parentNode;
                        domUtils.remove(tmpNode);
                        tmpNode = fillData;
                    }
                } else {
                    fillData.nodeValue = fillData.nodeValue.replace(fillCharReg, '');
                }
            }
        } catch (e) {
        }
    }

    /**
     * @param node
     * @param dir
     */
    function mergeSibling(node, dir) {
        var tmpNode;
        node = node[dir];
        while (node && domUtils.isFillChar(node)) {
            tmpNode = node[dir];
            domUtils.remove(node);
            node = tmpNode;
        }
    }

    Range.prototype = {

        /**
         * 克隆選區的內容到一個DocumentFragment裡
         * @method cloneContents
         * @return { DocumentFragment | NULL } 如果選區是閉合的將返回null， 否則， 返回包含所clone內容的DocumentFragment元素
         * @example
         * ```html
         * <body>
         *      <!-- 中括號表示選區 -->
         *      <b>x<i>x[x</i>xx]x</b>
         *
         *      <script>
         *          //range是已選中的選區
         *          var fragment = range.cloneContents(),
         *              node = document.createElement("div");
         *
         *          node.appendChild( fragment );
         *
         *          //output: <i>x</i>xx
         *          console.log( node.innerHTML );
         *
         *      </script>
         * </body>
         * ```
         */
        cloneContents:function () {
            return this.collapsed ? null : execContentsAction(this, 0);
        },

        /**
         * 刪除當前選區範圍中的所有內容
         * @method deleteContents
         * @remind 執行完該操作後， 當前Range物件變成了閉合狀態
         * @return { UE.dom.Range } 當前操作的Range物件
         * @example
         * ```html
         * <body>
         *      <!-- 中括號表示選區 -->
         *      <b>x<i>x[x</i>xx]x</b>
         *
         *      <script>
         *          //range是已選中的選區
         *          range.deleteContents();
         *
         *          //豎線表示閉合後的選區位置
         *          //output: <b>x<i>x</i>|x</b>
         *          console.log( document.body.innerHTML );
         *
         *          //此時， range的各項屬性為
         *          //output: B
         *          console.log( range.startContainer.tagName );
         *          //output: 2
         *          console.log( range.startOffset );
         *          //output: B
         *          console.log( range.endContainer.tagName );
         *          //output: 2
         *          console.log( range.endOffset );
         *          //output: true
         *          console.log( range.collapsed );
         *
         *      </script>
         * </body>
         * ```
         */
        deleteContents:function () {
            var txt;
            if (!this.collapsed) {
                execContentsAction(this, 1);
            }
            if (browser.webkit) {
                txt = this.startContainer;
                if (txt.nodeType == 3 && !txt.nodeValue.length) {
                    this.setStartBefore(txt).collapse(true);
                    domUtils.remove(txt);
                }
            }
            return this;
        },

        /**
         * 將當前選區的內容提取到一個DocumentFragment裡
         * @method extractContents
         * @remind 執行該操作後， 選區將變成閉合狀態
         * @warning 執行該操作後， 原來選區所選中的內容將從dom樹上剝離出來
         * @return { DocumentFragment } 返回包含所提取內容的DocumentFragment物件
         * @example
         * ```html
         * <body>
         *      <!-- 中括號表示選區 -->
         *      <b>x<i>x[x</i>xx]x</b>
         *
         *      <script>
         *          //range是已選中的選區
         *          var fragment = range.extractContents(),
         *              node = document.createElement( "div" );
         *
         *          node.appendChild( fragment );
         *
         *          //豎線表示閉合後的選區位置
         *
         *          //output: <b>x<i>x</i>|x</b>
         *          console.log( document.body.innerHTML );
         *          //output: <i>x</i>xx
         *          console.log( node.innerHTML );
         *
         *          //此時， range的各項屬性為
         *          //output: B
         *          console.log( range.startContainer.tagName );
         *          //output: 2
         *          console.log( range.startOffset );
         *          //output: B
         *          console.log( range.endContainer.tagName );
         *          //output: 2
         *          console.log( range.endOffset );
         *          //output: true
         *          console.log( range.collapsed );
         *
         *      </script>
         * </body>
         */
        extractContents:function () {
            return this.collapsed ? null : execContentsAction(this, 2);
        },

        /**
         * 設定Range的開始容器節點和偏移量
         * @method  setStart
         * @remind 如果給定的節點是元素節點，那麼offset指的是其子元素中索引為offset的元素，
         *          如果是文字節點，那麼offset指的是其文字內容的第offset個字元
         * @remind 如果提供的容器節點是一個不能包含子元素的節點， 則該選區的開始容器將被設定
         *          為該節點的父節點， 此時， 其距離開始容器的偏移量也變成了該節點在其父節點
         *          中的索引
         * @param { Node } node 將被設為當前選區開始邊界容器的節點物件
         * @param { int } offset 選區的開始位置偏移量
         * @return { UE.dom.Range } 當前range物件
         * @example
         * ```html
         * <!-- 選區 -->
         * <b>xxx<i>x<span>xx</span>xx<em>xx</em>xxx</i>[xxx]</b>
         *
         * <script>
         *
         *     //執行操作
         *     range.setStart( document.getElementsByTagName("i")[0], 1 );
         *
         *     //此時， 選區變成了
         *     //<b>xxx<i>x[<span>xx</span>xx<em>xx</em>xxx</i>xxx]</b>
         *
         * </script>
         * ```
         * @example
         * ```html
         * <!-- 選區 -->
         * <b>xxx<img>[xx]x</b>
         *
         * <script>
         *
         *     //執行操作
         *     range.setStart( document.getElementsByTagName("img")[0], 3 );
         *
         *     //此時， 選區變成了
         *     //<b>xxx[<img>xx]x</b>
         *
         * </script>
         * ```
         */
        setStart:function (node, offset) {
            return setEndPoint(true, node, offset, this);
        },

        /**
         * 設定Range的結束容器和偏移量
         * @method  setEnd
         * @param { Node } node 作為當前選區結束邊界容器的節點物件
         * @param { int } offset 結束邊界的偏移量
         * @see UE.dom.Range:setStart(Node,int)
         * @return { UE.dom.Range } 當前range物件
         */
        setEnd:function (node, offset) {
            return setEndPoint(false, node, offset, this);
        },

        /**
         * 將Range開始位置設定到node節點之後
         * @method  setStartAfter
         * @remind 該操作將會把給定節點的父節點作為range的開始容器， 且偏移量是該節點在其父節點中的位置索引+1
         * @param { Node } node 選區的開始邊界將緊接著該節點之後
         * @return { UE.dom.Range } 當前range物件
         * @example
         * ```html
         * <!-- 選區示例 -->
         * <b>xx<i>xxx</i><span>xx[x</span>xxx]</b>
         *
         * <script>
         *
         *     //執行操作
         *     range.setStartAfter( document.getElementsByTagName("i")[0] );
         *
         *     //結果選區
         *     //<b>xx<i>xxx</i>[<span>xxx</span>xxx]</b>
         *
         * </script>
         * ```
         */
        setStartAfter:function (node) {
            return this.setStart(node.parentNode, domUtils.getNodeIndex(node) + 1);
        },

        /**
         * 將Range開始位置設定到node節點之前
         * @method  setStartBefore
         * @remind 該操作將會把給定節點的父節點作為range的開始容器， 且偏移量是該節點在其父節點中的位置索引
         * @param { Node } node 新的選區開始位置在該節點之前
         * @see UE.dom.Range:setStartAfter(Node)
         * @return { UE.dom.Range } 當前range物件
         */
        setStartBefore:function (node) {
            return this.setStart(node.parentNode, domUtils.getNodeIndex(node));
        },

        /**
         * 將Range結束位置設定到node節點之後
         * @method  setEndAfter
         * @remind 該操作將會把給定節點的父節點作為range的結束容器， 且偏移量是該節點在其父節點中的位置索引+1
         * @param { Node } node 目標節點
         * @see UE.dom.Range:setStartAfter(Node)
         * @return { UE.dom.Range } 當前range物件
         * @example
         * ```html
         * <!-- 選區示例 -->
         * <b>[xx<i>xxx</i><span>xx]x</span>xxx</b>
         *
         * <script>
         *
         *     //執行操作
         *     range.setStartAfter( document.getElementsByTagName("span")[0] );
         *
         *     //結果選區
         *     //<b>[xx<i>xxx</i><span>xxx</span>]xxx</b>
         *
         * </script>
         * ```
         */
        setEndAfter:function (node) {
            return this.setEnd(node.parentNode, domUtils.getNodeIndex(node) + 1);
        },

        /**
         * 將Range結束位置設定到node節點之前
         * @method  setEndBefore
         * @remind 該操作將會把給定節點的父節點作為range的結束容器， 且偏移量是該節點在其父節點中的位置索引
         * @param { Node } node 目標節點
         * @see UE.dom.Range:setEndAfter(Node)
         * @return { UE.dom.Range } 當前range物件
         */
        setEndBefore:function (node) {
            return this.setEnd(node.parentNode, domUtils.getNodeIndex(node));
        },

        /**
         * 設定Range的開始位置到node節點內的第一個子節點之前
         * @method  setStartAtFirst
         * @remind 選區的開始容器將變成給定的節點， 且偏移量為0
         * @remind 如果給定的節點是元素節點， 則該節點必須是允許包含子節點的元素。
         * @param { Node } node 目標節點
         * @see UE.dom.Range:setStartBefore(Node)
         * @return { UE.dom.Range } 當前range物件
         * @example
         * ```html
         * <!-- 選區示例 -->
         * <b>xx<i>xxx</i><span>[xx]x</span>xxx</b>
         *
         * <script>
         *
         *     //執行操作
         *     range.setStartAtFirst( document.getElementsByTagName("i")[0] );
         *
         *     //結果選區
         *     //<b>xx<i>[xxx</i><span>xx]x</span>xxx</b>
         *
         * </script>
         * ```
         */
        setStartAtFirst:function (node) {
            return this.setStart(node, 0);
        },

        /**
         * 設定Range的開始位置到node節點內的最後一個節點之後
         * @method setStartAtLast
         * @remind 選區的開始容器將變成給定的節點， 且偏移量為該節點的子節點數
         * @remind 如果給定的節點是元素節點， 則該節點必須是允許包含子節點的元素。
         * @param { Node } node 目標節點
         * @see UE.dom.Range:setStartAtFirst(Node)
         * @return { UE.dom.Range } 當前range物件
         */
        setStartAtLast:function (node) {
            return this.setStart(node, node.nodeType == 3 ? node.nodeValue.length : node.childNodes.length);
        },

        /**
         * 設定Range的結束位置到node節點內的第一個節點之前
         * @method  setEndAtFirst
         * @param { Node } node 目標節點
         * @remind 選區的結束容器將變成給定的節點， 且偏移量為0
         * @remind node必須是一個元素節點， 且必須是允許包含子節點的元素。
         * @see UE.dom.Range:setStartAtFirst(Node)
         * @return { UE.dom.Range } 當前range物件
         */
        setEndAtFirst:function (node) {
            return this.setEnd(node, 0);
        },

        /**
         * 設定Range的結束位置到node節點內的最後一個節點之後
         * @method  setEndAtLast
         * @param { Node } node 目標節點
         * @remind 選區的結束容器將變成給定的節點， 且偏移量為該節點的子節點數量
         * @remind node必須是一個元素節點， 且必須是允許包含子節點的元素。
         * @see UE.dom.Range:setStartAtFirst(Node)
         * @return { UE.dom.Range } 當前range物件
         */
        setEndAtLast:function (node) {
            return this.setEnd(node, node.nodeType == 3 ? node.nodeValue.length : node.childNodes.length);
        },

        /**
         * 選中給定節點
         * @method  selectNode
         * @remind 此時， 選區的開始容器和結束容器都是該節點的父節點， 其startOffset是該節點在父節點中的位置索引，
         *          而endOffset為startOffset+1
         * @param { Node } node 需要選中的節點
         * @return { UE.dom.Range } 當前range物件，此時的range僅包含當前給定的節點物件
         * @example
         * ```html
         * <!-- 選區示例 -->
         * <b>xx<i>xxx</i><span>[xx]x</span>xxx</b>
         *
         * <script>
         *
         *     //執行操作
         *     range.selectNode( document.getElementsByTagName("i")[0] );
         *
         *     //結果選區
         *     //<b>xx[<i>xxx</i>]<span>xxx</span>xxx</b>
         *
         * </script>
         * ```
         */
        selectNode:function (node) {
            return this.setStartBefore(node).setEndAfter(node);
        },

        /**
         * 選中給定節點內部的所有節點
         * @method  selectNodeContents
         * @remind 此時， 選區的開始容器和結束容器都是該節點， 其startOffset為0，
         *          而endOffset是該節點的子節點數。
         * @param { Node } node 目標節點， 當前range將包含該節點內的所有節點
         * @return { UE.dom.Range } 當前range物件， 此時range僅包含給定節點的所有子節點
         * @example
         * ```html
         * <!-- 選區示例 -->
         * <b>xx<i>xxx</i><span>[xx]x</span>xxx</b>
         *
         * <script>
         *
         *     //執行操作
         *     range.selectNode( document.getElementsByTagName("b")[0] );
         *
         *     //結果選區
         *     //<b>[xx<i>xxx</i><span>xxx</span>xxx]</b>
         *
         * </script>
         * ```
         */
        selectNodeContents:function (node) {
            return this.setStart(node, 0).setEndAtLast(node);
        },

        /**
         * clone當前Range物件
         * @method  cloneRange
         * @remind 返回的range是一個全新的range物件， 其內部所有屬性與當前被clone的range相同。
         * @return { UE.dom.Range } 當前range物件的一個副本
         */
        cloneRange:function () {
            var me = this;
            return new Range(me.document).setStart(me.startContainer, me.startOffset).setEnd(me.endContainer, me.endOffset);

        },

        /**
         * 向當前選區的結束處閉合選區
         * @method  collapse
         * @return { UE.dom.Range } 當前range物件
         * @example
         * ```html
         * <!-- 選區示例 -->
         * <b>xx<i>xxx</i><span>[xx]x</span>xxx</b>
         *
         * <script>
         *
         *     //執行操作
         *     range.collapse();
         *
         *     //結果選區
         *     //“|”表示選區已閉合
         *     //<b>xx<i>xxx</i><span>xx|x</span>xxx</b>
         *
         * </script>
         * ```
         */

        /**
         * 閉合當前選區，根據給定的toStart引數項決定是向當前選區開始處閉合還是向結束處閉合，
         * 如果toStart的值為true，則向開始位置閉合， 反之，向結束位置閉合。
         * @method  collapse
         * @param { Boolean } toStart 是否向選區開始處閉合
         * @return { UE.dom.Range } 當前range物件，此時range物件處於閉合狀態
         * @see UE.dom.Range:collapse()
         * @example
         * ```html
         * <!-- 選區示例 -->
         * <b>xx<i>xxx</i><span>[xx]x</span>xxx</b>
         *
         * <script>
         *
         *     //執行操作
         *     range.collapse( true );
         *
         *     //結果選區
         *     //“|”表示選區已閉合
         *     //<b>xx<i>xxx</i><span>|xxx</span>xxx</b>
         *
         * </script>
         * ```
         */
        collapse:function (toStart) {
            var me = this;
            if (toStart) {
                me.endContainer = me.startContainer;
                me.endOffset = me.startOffset;
            } else {
                me.startContainer = me.endContainer;
                me.startOffset = me.endOffset;
            }
            me.collapsed = true;
            return me;
        },

        /**
         * 調整range的開始位置和結束位置，使其"收縮"到最小的位置
         * @method  shrinkBoundary
         * @return { UE.dom.Range } 當前range物件
         * @example
         * ```html
         * <span>xx<b>xx[</b>xxxxx]</span> => <span>xx<b>xx</b>[xxxxx]</span>
         * ```
         *
         * @example
         * ```html
         * <!-- 選區示例 -->
         * <b>x[xx</b><i>]xxx</i>
         *
         * <script>
         *
         *     //執行收縮
         *     range.shrinkBoundary();
         *
         *     //結果選區
         *     //<b>x[xx]</b><i>xxx</i>
         * </script>
         * ```
         *
         * @example
         * ```html
         * [<b><i>xxxx</i>xxxxxxx</b>] => <b><i>[xxxx</i>xxxxxxx]</b>
         * ```
         */

        /**
         * 調整range的開始位置和結束位置，使其"收縮"到最小的位置，
         * 如果ignoreEnd的值為true，則忽略對結束位置的調整
         * @method  shrinkBoundary
         * @param { Boolean } ignoreEnd 是否忽略對結束位置的調整
         * @return { UE.dom.Range } 當前range物件
         * @see UE.dom.domUtils.Range:shrinkBoundary()
         */
        shrinkBoundary:function (ignoreEnd) {
            var me = this, child,
                collapsed = me.collapsed;
            function check(node){
                return node.nodeType == 1 && !domUtils.isBookmarkNode(node) && !dtd.$empty[node.tagName] && !dtd.$nonChild[node.tagName]
            }
            while (me.startContainer.nodeType == 1 //是element
                && (child = me.startContainer.childNodes[me.startOffset]) //子節點也是element
                && check(child)) {
                me.setStart(child, 0);
            }
            if (collapsed) {
                return me.collapse(true);
            }
            if (!ignoreEnd) {
                while (me.endContainer.nodeType == 1//是element
                    && me.endOffset > 0 //如果是空元素就退出 endOffset=0那麼endOffst-1為負值，childNodes[endOffset]報錯
                    && (child = me.endContainer.childNodes[me.endOffset - 1]) //子節點也是element
                    && check(child)) {
                    me.setEnd(child, child.childNodes.length);
                }
            }
            return me;
        },

        /**
         * 獲取離當前選區內包含的所有節點最近的公共祖先節點，
         * @method  getCommonAncestor
         * @remind 返回的公共祖先節點一定不是range自身的容器節點， 但有可能是一個文字節點
         * @return { Node } 當前range物件內所有節點的公共祖先節點
         * @example
         * ```html
         * //選區示例
         * <span>xxx<b>x[x<em>xx]x</em>xxx</b>xx</span>
         * <script>
         *
         *     var node = range.getCommonAncestor();
         *
         *     //公共祖先節點是： b節點
         *     //輸出： B
         *     console.log(node.tagName);
         *
         * </script>
         * ```
         */

        /**
         * 獲取當前選區所包含的所有節點的公共祖先節點， 可以根據給定的引數 includeSelf 決定獲取到
         * 的公共祖先節點是否可以是當前選區的startContainer或endContainer節點， 如果 includeSelf
         * 的取值為true， 則返回的節點可以是自身的容器節點， 否則， 則不能是容器節點
         * @method  getCommonAncestor
         * @param { Boolean } includeSelf 是否允許獲取到的公共祖先節點是當前range物件的容器節點
         * @return { Node } 當前range物件內所有節點的公共祖先節點
         * @see UE.dom.Range:getCommonAncestor()
         * @example
         * ```html
         * <body>
         *
         *     <!-- 選區示例 -->
         *     <b>xxx<i>xxxx<span>xx[x</span>xx]x</i>xxxxxxx</b>
         *
         *     <script>
         *
         *         var node = range.getCommonAncestor( false );
         *
         *         //這裡的公共祖先節點是B而不是I， 是因為引數限制了獲取到的節點不能是容器節點
         *         //output: B
         *         console.log( node.tagName );
         *
         *     </script>
         *
         * </body>
         * ```
         */

        /**
         * 獲取當前選區所包含的所有節點的公共祖先節點， 可以根據給定的引數 includeSelf 決定獲取到
         * 的公共祖先節點是否可以是當前選區的startContainer或endContainer節點， 如果 includeSelf
         * 的取值為true， 則返回的節點可以是自身的容器節點， 否則， 則不能是容器節點； 同時可以根據
         * ignoreTextNode 引數的取值決定是否忽略型別為文字節點的祖先節點。
         * @method  getCommonAncestor
         * @param { Boolean } includeSelf 是否允許獲取到的公共祖先節點是當前range物件的容器節點
         * @param { Boolean } ignoreTextNode 獲取祖先節點的過程中是否忽略型別為文字節點的祖先節點
         * @return { Node } 當前range物件內所有節點的公共祖先節點
         * @see UE.dom.Range:getCommonAncestor()
         * @see UE.dom.Range:getCommonAncestor(Boolean)
         * @example
         * ```html
         * <body>
         *
         *     <!-- 選區示例 -->
         *     <b>xxx<i>xxxx<span>x[x]x</span>xxx</i>xxxxxxx</b>
         *
         *     <script>
         *
         *         var node = range.getCommonAncestor( true, false );
         *
         *         //output: SPAN
         *         console.log( node.tagName );
         *
         *     </script>
         *
         * </body>
         * ```
         */
        getCommonAncestor:function (includeSelf, ignoreTextNode) {
            var me = this,
                start = me.startContainer,
                end = me.endContainer;
            if (start === end) {
                if (includeSelf && selectOneNode(this)) {
                    start = start.childNodes[me.startOffset];
                    if(start.nodeType == 1)
                        return start;
                }
                //只有在上來就相等的情況下才會出現是文字的情況
                return ignoreTextNode && start.nodeType == 3 ? start.parentNode : start;
            }
            return domUtils.getCommonAncestor(start, end);
        },

        /**
         * 調整當前Range的開始和結束邊界容器，如果是容器節點是文字節點,就調整到包含該文字節點的父節點上
         * @method trimBoundary
         * @remind 該操作有可能會引起文字節點被切開
         * @return { UE.dom.Range } 當前range物件
         * @example
         * ```html
         *
         * //選區示例
         * <b>xxx<i>[xxxxx]</i>xxx</b>
         *
         * <script>
         *     //未調整前， 選區的開始容器和結束都是文字節點
         *     //執行調整
         *     range.trimBoundary();
         *
         *     //調整之後， 容器節點變成了i節點
         *     //<b>xxx[<i>xxxxx</i>]xxx</b>
         * </script>
         * ```
         */

        /**
         * 調整當前Range的開始和結束邊界容器，如果是容器節點是文字節點,就調整到包含該文字節點的父節點上，
         * 可以根據 ignoreEnd 引數的值決定是否調整對結束邊界的調整
         * @method trimBoundary
         * @param { Boolean } ignoreEnd 是否忽略對結束邊界的調整
         * @return { UE.dom.Range } 當前range物件
         * @example
         * ```html
         *
         * //選區示例
         * <b>xxx<i>[xxxxx]</i>xxx</b>
         *
         * <script>
         *     //未調整前， 選區的開始容器和結束都是文字節點
         *     //執行調整
         *     range.trimBoundary( true );
         *
         *     //調整之後， 開始容器節點變成了i節點
         *     //但是， 結束容器沒有發生變化
         *     //<b>xxx[<i>xxxxx]</i>xxx</b>
         * </script>
         * ```
         */
        trimBoundary:function (ignoreEnd) {
            this.txtToElmBoundary();
            var start = this.startContainer,
                offset = this.startOffset,
                collapsed = this.collapsed,
                end = this.endContainer;
            if (start.nodeType == 3) {
                if (offset == 0) {
                    this.setStartBefore(start);
                } else {
                    if (offset >= start.nodeValue.length) {
                        this.setStartAfter(start);
                    } else {
                        var textNode = domUtils.split(start, offset);
                        //跟新結束邊界
                        if (start === end) {
                            this.setEnd(textNode, this.endOffset - offset);
                        } else if (start.parentNode === end) {
                            this.endOffset += 1;
                        }
                        this.setStartBefore(textNode);
                    }
                }
                if (collapsed) {
                    return this.collapse(true);
                }
            }
            if (!ignoreEnd) {
                offset = this.endOffset;
                end = this.endContainer;
                if (end.nodeType == 3) {
                    if (offset == 0) {
                        this.setEndBefore(end);
                    } else {
                        offset < end.nodeValue.length && domUtils.split(end, offset);
                        this.setEndAfter(end);
                    }
                }
            }
            return this;
        },

        /**
         * 如果選區在文字的邊界上，就擴充套件選區到文字的父節點上, 如果當前選區是閉合的， 則什麼也不做
         * @method txtToElmBoundary
         * @remind 該操作不會修改dom節點
         * @return { UE.dom.Range } 當前range物件
         */

        /**
         * 如果選區在文字的邊界上，就擴充套件選區到文字的父節點上, 如果當前選區是閉合的， 則根據引數項
         * ignoreCollapsed 的值決定是否執行該調整
         * @method txtToElmBoundary
         * @param { Boolean } ignoreCollapsed 是否忽略選區的閉合狀態， 如果該引數取值為true， 則
         *                      不論選區是否閉合， 都會執行該操作， 反之， 則不會對閉合的選區執行該操作
         * @return { UE.dom.Range } 當前range物件
         */
        txtToElmBoundary:function (ignoreCollapsed) {
            function adjust(r, c) {
                var container = r[c + 'Container'],
                    offset = r[c + 'Offset'];
                if (container.nodeType == 3) {
                    if (!offset) {
                        r['set' + c.replace(/(\w)/, function (a) {
                            return a.toUpperCase();
                        }) + 'Before'](container);
                    } else if (offset >= container.nodeValue.length) {
                        r['set' + c.replace(/(\w)/, function (a) {
                            return a.toUpperCase();
                        }) + 'After' ](container);
                    }
                }
            }

            if (ignoreCollapsed || !this.collapsed) {
                adjust(this, 'start');
                adjust(this, 'end');
            }
            return this;
        },

        /**
         * 在當前選區的開始位置前插入節點，新插入的節點會被該range包含
         * @method  insertNode
         * @param { Node } node 需要插入的節點
         * @remind 插入的節點可以是一個DocumentFragment依次插入多個節點
         * @return { UE.dom.Range } 當前range物件
         */
        insertNode:function (node) {
            var first = node, length = 1;
            if (node.nodeType == 11) {
                first = node.firstChild;
                length = node.childNodes.length;
            }
            this.trimBoundary(true);
            var start = this.startContainer,
                offset = this.startOffset;
            var nextNode = start.childNodes[ offset ];
            if (nextNode) {
                start.insertBefore(node, nextNode);
            } else {
                start.appendChild(node);
            }
            if (first.parentNode === this.endContainer) {
                this.endOffset = this.endOffset + length;
            }
            return this.setStartBefore(first);
        },

        /**
         * 閉合選區到當前選區的開始位置， 並且定位游標到閉合後的位置
         * @method  setCursor
         * @return { UE.dom.Range } 當前range物件
         * @see UE.dom.Range:collapse()
         */

        /**
         * 閉合選區，可以根據引數toEnd的值控制選區是向前閉合還是向後閉合， 並且定位游標到閉合後的位置。
         * @method  setCursor
         * @param { Boolean } toEnd 是否向後閉合， 如果為true， 則閉合選區時， 將向結束容器方向閉合，
         *                      反之，則向開始容器方向閉合
         * @return { UE.dom.Range } 當前range物件
         * @see UE.dom.Range:collapse(Boolean)
         */
        setCursor:function (toEnd, noFillData) {
            return this.collapse(!toEnd).select(noFillData);
        },

        /**
         * 建立當前range的一個書籤，記錄下當前range的位置，方便當dom樹改變時，還能找回原來的選區位置
         * @method createBookmark
         * @param { Boolean } serialize 控制返回的標記位置是對當前位置的引用還是ID，如果該值為true，則
         *                              返回標記位置的ID， 反之則返回標記位置節點的引用
         * @return { Object } 返回一個書籤記錄鍵值對， 其包含的key有： start => 開始標記的ID或者引用，
         *                          end => 結束標記的ID或引用， id => 當前標記的型別， 如果為true，則表示
         *                          返回的記錄的型別為ID， 反之則為引用
         */
        createBookmark:function (serialize, same) {
            var endNode,
                startNode = this.document.createElement('span');
            startNode.style.cssText = 'display:none;line-height:0px;';
            startNode.appendChild(this.document.createTextNode('\u200D'));
            startNode.id = '_baidu_bookmark_start_' + (same ? '' : guid++);

            if (!this.collapsed) {
                endNode = startNode.cloneNode(true);
                endNode.id = '_baidu_bookmark_end_' + (same ? '' : guid++);
            }
            this.insertNode(startNode);
            if (endNode) {
                this.collapse().insertNode(endNode).setEndBefore(endNode);
            }
            this.setStartAfter(startNode);
            return {
                start:serialize ? startNode.id : startNode,
                end:endNode ? serialize ? endNode.id : endNode : null,
                id:serialize
            }
        },

        /**
         *  調整當前range的邊界到書籤位置，並刪除該書籤物件所標記的位置內的節點
         *  @method  moveToBookmark
         *  @param { BookMark } bookmark createBookmark所建立的標籤物件
         *  @return { UE.dom.Range } 當前range物件
         *  @see UE.dom.Range:createBookmark(Boolean)
         */
        moveToBookmark:function (bookmark) {
            var start = bookmark.id ? this.document.getElementById(bookmark.start) : bookmark.start,
                end = bookmark.end && bookmark.id ? this.document.getElementById(bookmark.end) : bookmark.end;
            this.setStartBefore(start);
            domUtils.remove(start);
            if (end) {
                this.setEndBefore(end);
                domUtils.remove(end);
            } else {
                this.collapse(true);
            }
            return this;
        },

        /**
         * 調整range的邊界，使其"放大"到最近的父節點
         * @method  enlarge
         * @remind 會引起選區的變化
         * @return { UE.dom.Range } 當前range物件
         */

        /**
         * 調整range的邊界，使其"放大"到最近的父節點，根據引數 toBlock 的取值， 可以
         * 要求擴大之後的父節點是block節點
         * @method  enlarge
         * @param { Boolean } toBlock 是否要求擴大之後的父節點必須是block節點
         * @return { UE.dom.Range } 當前range物件
         */
        enlarge:function (toBlock, stopFn) {
            var isBody = domUtils.isBody,
                pre, node, tmp = this.document.createTextNode('');
            if (toBlock) {
                node = this.startContainer;
                if (node.nodeType == 1) {
                    if (node.childNodes[this.startOffset]) {
                        pre = node = node.childNodes[this.startOffset]
                    } else {
                        node.appendChild(tmp);
                        pre = node = tmp;
                    }
                } else {
                    pre = node;
                }
                while (1) {
                    if (domUtils.isBlockElm(node)) {
                        node = pre;
                        while ((pre = node.previousSibling) && !domUtils.isBlockElm(pre)) {
                            node = pre;
                        }
                        this.setStartBefore(node);
                        break;
                    }
                    pre = node;
                    node = node.parentNode;
                }
                node = this.endContainer;
                if (node.nodeType == 1) {
                    if (pre = node.childNodes[this.endOffset]) {
                        node.insertBefore(tmp, pre);
                    } else {
                        node.appendChild(tmp);
                    }
                    pre = node = tmp;
                } else {
                    pre = node;
                }
                while (1) {
                    if (domUtils.isBlockElm(node)) {
                        node = pre;
                        while ((pre = node.nextSibling) && !domUtils.isBlockElm(pre)) {
                            node = pre;
                        }
                        this.setEndAfter(node);
                        break;
                    }
                    pre = node;
                    node = node.parentNode;
                }
                if (tmp.parentNode === this.endContainer) {
                    this.endOffset--;
                }
                domUtils.remove(tmp);
            }

            // 擴充套件邊界到最大
            if (!this.collapsed) {
                while (this.startOffset == 0) {
                    if (stopFn && stopFn(this.startContainer)) {
                        break;
                    }
                    if (isBody(this.startContainer)) {
                        break;
                    }
                    this.setStartBefore(this.startContainer);
                }
                while (this.endOffset == (this.endContainer.nodeType == 1 ? this.endContainer.childNodes.length : this.endContainer.nodeValue.length)) {
                    if (stopFn && stopFn(this.endContainer)) {
                        break;
                    }
                    if (isBody(this.endContainer)) {
                        break;
                    }
                    this.setEndAfter(this.endContainer);
                }
            }
            return this;
        },
        enlargeToBlockElm:function(ignoreEnd){
            while(!domUtils.isBlockElm(this.startContainer)){
                this.setStartBefore(this.startContainer);
            }
            if(!ignoreEnd){
                while(!domUtils.isBlockElm(this.endContainer)){
                    this.setEndAfter(this.endContainer);
                }
            }
            return this;
        },
        /**
         * 調整Range的邊界，使其"縮小"到最合適的位置
         * @method adjustmentBoundary
         * @return { UE.dom.Range } 當前range物件
         * @see UE.dom.Range:shrinkBoundary()
         */
        adjustmentBoundary:function () {
            if (!this.collapsed) {
                while (!domUtils.isBody(this.startContainer) &&
                    this.startOffset == this.startContainer[this.startContainer.nodeType == 3 ? 'nodeValue' : 'childNodes'].length &&
                    this.startContainer[this.startContainer.nodeType == 3 ? 'nodeValue' : 'childNodes'].length
                    ) {

                    this.setStartAfter(this.startContainer);
                }
                while (!domUtils.isBody(this.endContainer) && !this.endOffset &&
                    this.endContainer[this.endContainer.nodeType == 3 ? 'nodeValue' : 'childNodes'].length
                    ) {
                    this.setEndBefore(this.endContainer);
                }
            }
            return this;
        },

        /**
         * 給range選區中的內容新增給定的inline標籤
         * @method applyInlineStyle
         * @param { String } tagName 需要新增的標籤名
         * @example
         * ```html
         * <p>xxxx[xxxx]x</p>  ==>  range.applyInlineStyle("strong")  ==>  <p>xxxx[<strong>xxxx</strong>]x</p>
         * ```
         */

        /**
         * 給range選區中的內容新增給定的inline標籤， 並且為標籤附加上一些初始化屬性。
         * @method applyInlineStyle
         * @param { String } tagName 需要新增的標籤名
         * @param { Object } attrs 跟隨新新增的標籤的屬性
         * @return { UE.dom.Range } 當前選區
         * @example
         * ```html
         * <p>xxxx[xxxx]x</p>
         *
         * ==>
         *
         * <!-- 執行操作 -->
         * range.applyInlineStyle("strong",{"style":"font-size:12px"})
         *
         * ==>
         *
         * <p>xxxx[<strong style="font-size:12px">xxxx</strong>]x</p>
         * ```
         */
        applyInlineStyle:function (tagName, attrs, list) {
            if (this.collapsed)return this;
            this.trimBoundary().enlarge(false,
                function (node) {
                    return node.nodeType == 1 && domUtils.isBlockElm(node)
                }).adjustmentBoundary();
            var bookmark = this.createBookmark(),
                end = bookmark.end,
                filterFn = function (node) {
                    return node.nodeType == 1 ? node.tagName.toLowerCase() != 'br' : !domUtils.isWhitespace(node);
                },
                current = domUtils.getNextDomNode(bookmark.start, false, filterFn),
                node,
                pre,
                range = this.cloneRange();
            while (current && (domUtils.getPosition(current, end) & domUtils.POSITION_PRECEDING)) {
                if (current.nodeType == 3 || dtd[tagName][current.tagName]) {
                    range.setStartBefore(current);
                    node = current;
                    while (node && (node.nodeType == 3 || dtd[tagName][node.tagName]) && node !== end) {
                        pre = node;
                        node = domUtils.getNextDomNode(node, node.nodeType == 1, null, function (parent) {
                            return dtd[tagName][parent.tagName];
                        });
                    }
                    var frag = range.setEndAfter(pre).extractContents(), elm;
                    if (list && list.length > 0) {
                        var level, top;
                        top = level = list[0].cloneNode(false);
                        for (var i = 1, ci; ci = list[i++];) {
                            level.appendChild(ci.cloneNode(false));
                            level = level.firstChild;
                        }
                        elm = level;
                    } else {
                        elm = range.document.createElement(tagName);
                    }
                    if (attrs) {
                        domUtils.setAttributes(elm, attrs);
                    }
                    elm.appendChild(frag);
                    range.insertNode(list ? top : elm);
                    //處理下滑線在a上的情況
                    var aNode;
                    if (tagName == 'span' && attrs.style && /text\-decoration/.test(attrs.style) && (aNode = domUtils.findParentByTagName(elm, 'a', true))) {
                        domUtils.setAttributes(aNode, attrs);
                        domUtils.remove(elm, true);
                        elm = aNode;
                    } else {
                        domUtils.mergeSibling(elm);
                        domUtils.clearEmptySibling(elm);
                    }
                    //去除子節點相同的
                    domUtils.mergeChild(elm, attrs);
                    current = domUtils.getNextDomNode(elm, false, filterFn);
                    domUtils.mergeToParent(elm);
                    if (node === end) {
                        break;
                    }
                } else {
                    current = domUtils.getNextDomNode(current, true, filterFn);
                }
            }
            return this.moveToBookmark(bookmark);
        },

        /**
         * 移除當前選區內指定的inline標籤，但保留其中的內容
         * @method removeInlineStyle
         * @param { String } tagName 需要移除的標籤名
         * @return { UE.dom.Range } 當前的range物件
         * @example
         * ```html
         * xx[x<span>xxx<em>yyy</em>zz]z</span>  => range.removeInlineStyle(["em"])  => xx[x<span>xxxyyyzz]z</span>
         * ```
         */

        /**
         * 移除當前選區內指定的一組inline標籤，但保留其中的內容
         * @method removeInlineStyle
         * @param { Array } tagNameArr 需要移除的標籤名的陣列
         * @return { UE.dom.Range } 當前的range物件
         * @see UE.dom.Range:removeInlineStyle(String)
         */
        removeInlineStyle:function (tagNames) {
            if (this.collapsed)return this;
            tagNames = utils.isArray(tagNames) ? tagNames : [tagNames];
            this.shrinkBoundary().adjustmentBoundary();
            var start = this.startContainer, end = this.endContainer;
            while (1) {
                if (start.nodeType == 1) {
                    if (utils.indexOf(tagNames, start.tagName.toLowerCase()) > -1) {
                        break;
                    }
                    if (start.tagName.toLowerCase() == 'body') {
                        start = null;
                        break;
                    }
                }
                start = start.parentNode;
            }
            while (1) {
                if (end.nodeType == 1) {
                    if (utils.indexOf(tagNames, end.tagName.toLowerCase()) > -1) {
                        break;
                    }
                    if (end.tagName.toLowerCase() == 'body') {
                        end = null;
                        break;
                    }
                }
                end = end.parentNode;
            }
            var bookmark = this.createBookmark(),
                frag,
                tmpRange;
            if (start) {
                tmpRange = this.cloneRange().setEndBefore(bookmark.start).setStartBefore(start);
                frag = tmpRange.extractContents();
                tmpRange.insertNode(frag);
                domUtils.clearEmptySibling(start, true);
                start.parentNode.insertBefore(bookmark.start, start);
            }
            if (end) {
                tmpRange = this.cloneRange().setStartAfter(bookmark.end).setEndAfter(end);
                frag = tmpRange.extractContents();
                tmpRange.insertNode(frag);
                domUtils.clearEmptySibling(end, false, true);
                end.parentNode.insertBefore(bookmark.end, end.nextSibling);
            }
            var current = domUtils.getNextDomNode(bookmark.start, false, function (node) {
                return node.nodeType == 1;
            }), next;
            while (current && current !== bookmark.end) {
                next = domUtils.getNextDomNode(current, true, function (node) {
                    return node.nodeType == 1;
                });
                if (utils.indexOf(tagNames, current.tagName.toLowerCase()) > -1) {
                    domUtils.remove(current, true);
                }
                current = next;
            }
            return this.moveToBookmark(bookmark);
        },

        /**
         * 獲取當前選中的自閉合的節點
         * @method  getClosedNode
         * @return { Node | NULL } 如果當前選中的是自閉合節點， 則返回該節點， 否則返回NULL
         */
        getClosedNode:function () {
            var node;
            if (!this.collapsed) {
                var range = this.cloneRange().adjustmentBoundary().shrinkBoundary();
                if (selectOneNode(range)) {
                    var child = range.startContainer.childNodes[range.startOffset];
                    if (child && child.nodeType == 1 && (dtd.$empty[child.tagName] || dtd.$nonChild[child.tagName])) {
                        node = child;
                    }
                }
            }
            return node;
        },

        /**
         * 在頁面上高亮range所表示的選區
         * @method select
         * @return { UE.dom.Range } 返回當前Range物件
         */
            //這裡不區分ie9以上，trace:3824
        select:browser.ie ? function (noFillData, textRange) {
            var nativeRange;
            if (!this.collapsed)
                this.shrinkBoundary();
            var node = this.getClosedNode();
            if (node && !textRange) {
                try {
                    nativeRange = this.document.body.createControlRange();
                    nativeRange.addElement(node);
                    nativeRange.select();
                } catch (e) {}
                return this;
            }
            var bookmark = this.createBookmark(),
                start = bookmark.start,
                end;
            nativeRange = this.document.body.createTextRange();
            nativeRange.moveToElementText(start);
            nativeRange.moveStart('character', 1);
            if (!this.collapsed) {
                var nativeRangeEnd = this.document.body.createTextRange();
                end = bookmark.end;
                nativeRangeEnd.moveToElementText(end);
                nativeRange.setEndPoint('EndToEnd', nativeRangeEnd);
            } else {
                if (!noFillData && this.startContainer.nodeType != 3) {
                    //使用<span>|x<span>固定住游標
                    var tmpText = this.document.createTextNode(fillChar),
                        tmp = this.document.createElement('span');
                    tmp.appendChild(this.document.createTextNode(fillChar));
                    start.parentNode.insertBefore(tmp, start);
                    start.parentNode.insertBefore(tmpText, start);
                    //當點b,i,u時，不能清除i上邊的b
                    removeFillData(this.document, tmpText);
                    fillData = tmpText;
                    mergeSibling(tmp, 'previousSibling');
                    mergeSibling(start, 'nextSibling');
                    nativeRange.moveStart('character', -1);
                    nativeRange.collapse(true);
                }
            }
            this.moveToBookmark(bookmark);
            tmp && domUtils.remove(tmp);
            //IE在隱藏狀態下不支援range操作，catch一下
            try {
                nativeRange.select();
            } catch (e) {
            }
            return this;
        } : function (notInsertFillData) {
            function checkOffset(rng){

                function check(node,offset,dir){
                    if(node.nodeType == 3 && node.nodeValue.length < offset){
                        rng[dir + 'Offset'] = node.nodeValue.length
                    }
                }
                check(rng.startContainer,rng.startOffset,'start');
                check(rng.endContainer,rng.endOffset,'end');
            }
            var win = domUtils.getWindow(this.document),
                sel = win.getSelection(),
                txtNode;
            //FF下關閉自動長高時滾動條在關閉dialog時會跳
            //ff下如果不body.focus將不能定位閉合游標到編輯器內
            browser.gecko ? this.document.body.focus() : win.focus();
            if (sel) {
                sel.removeAllRanges();
                // trace:870 chrome/safari後邊是br對於閉合得range不能定位 所以去掉了判斷
                // this.startContainer.nodeType != 3 &&! ((child = this.startContainer.childNodes[this.startOffset]) && child.nodeType == 1 && child.tagName == 'BR'
                if (this.collapsed && !notInsertFillData) {
//                    //opear如果沒有節點接著，原生的不能夠定位,不能在body的第一級插入空白節點
//                    if (notInsertFillData && browser.opera && !domUtils.isBody(this.startContainer) && this.startContainer.nodeType == 1) {
//                        var tmp = this.document.createTextNode('');
//                        this.insertNode(tmp).setStart(tmp, 0).collapse(true);
//                    }
//
                    //處理游標落在文字節點的情況
                    //處理以下的情況
                    //<b>|xxxx</b>
                    //<b>xxxx</b>|xxxx
                    //xxxx<b>|</b>
                    var start = this.startContainer,child = start;
                    if(start.nodeType == 1){
                        child = start.childNodes[this.startOffset];

                    }
                    if( !(start.nodeType == 3 && this.startOffset)  &&
                        (child ?
                            (!child.previousSibling || child.previousSibling.nodeType != 3)
                            :
                            (!start.lastChild || start.lastChild.nodeType != 3)
                        )
                    ){
                        txtNode = this.document.createTextNode(fillChar);
                        //跟著前邊走
                        this.insertNode(txtNode);
                        removeFillData(this.document, txtNode);
                        mergeSibling(txtNode, 'previousSibling');
                        mergeSibling(txtNode, 'nextSibling');
                        fillData = txtNode;
                        this.setStart(txtNode, browser.webkit ? 1 : 0).collapse(true);
                    }
                }
                var nativeRange = this.document.createRange();
                if(this.collapsed && browser.opera && this.startContainer.nodeType == 1){
                    var child = this.startContainer.childNodes[this.startOffset];
                    if(!child){
                        //往前靠攏
                        child = this.startContainer.lastChild;
                        if( child && domUtils.isBr(child)){
                            this.setStartBefore(child).collapse(true);
                        }
                    }else{
                        //向後靠攏
                        while(child && domUtils.isBlockElm(child)){
                            if(child.nodeType == 1 && child.childNodes[0]){
                                child = child.childNodes[0]
                            }else{
                                break;
                            }
                        }
                        child && this.setStartBefore(child).collapse(true)
                    }

                }
                //是createAddress最後一位算的不準，現在這裡進行微調
                checkOffset(this);
                nativeRange.setStart(this.startContainer, this.startOffset);
                nativeRange.setEnd(this.endContainer, this.endOffset);
                sel.addRange(nativeRange);
            }
            return this;
        },

        /**
         * 滾動到當前range開始的位置
         * @method scrollToView
         * @param { Window } win 當前range物件所屬的window物件
         * @return { UE.dom.Range } 當前Range物件
         */

        /**
         * 滾動到距離當前range開始位置 offset 的位置處
         * @method scrollToView
         * @param { Window } win 當前range物件所屬的window物件
         * @param { Number } offset 距離range開始位置處的偏移量， 如果為正數， 則向下偏移， 反之， 則向上偏移
         * @return { UE.dom.Range } 當前Range物件
         */
        scrollToView:function (win, offset) {
            win = win ? window : domUtils.getWindow(this.document);
            var me = this,
                span = me.document.createElement('span');
            //trace:717
            span.innerHTML = '&nbsp;';
            me.cloneRange().insertNode(span);
            domUtils.scrollToView(span, win, offset);
            domUtils.remove(span);
            return me;
        },

        /**
         * 判斷當前選區內容是否佔位符
         * @private
         * @method inFillChar
         * @return { Boolean } 如果是佔位符返回true，否則返回false
         */
        inFillChar : function(){
            var start = this.startContainer;
            if(this.collapsed && start.nodeType == 3
                && start.nodeValue.replace(new RegExp('^' + domUtils.fillChar),'').length + 1 == start.nodeValue.length
                ){
                return true;
            }
            return false;
        },

        /**
         * 儲存
         * @method createAddress
         * @private
         * @return { Boolean } 返回開始和結束的位置
         * @example
         * ```html
         * <body>
         *     <p>
         *         aaaa
         *         <em>
         *             <!-- 選區開始 -->
         *             bbbb
         *             <!-- 選區結束 -->
         *         </em>
         *     </p>
         *
         *     <script>
         *         //output: {startAddress:[0,1,0,0],endAddress:[0,1,0,4]}
         *         console.log( range.createAddress() );
         *     </script>
         * </body>
         * ```
         */
        createAddress : function(ignoreEnd,ignoreTxt){
            var addr = {},me = this;

            function getAddress(isStart){
                var node = isStart ? me.startContainer : me.endContainer;
                var parents = domUtils.findParents(node,true,function(node){return !domUtils.isBody(node)}),
                    addrs = [];
                for(var i = 0,ci;ci = parents[i++];){
                    addrs.push(domUtils.getNodeIndex(ci,ignoreTxt));
                }
                var firstIndex = 0;

                if(ignoreTxt){
                    if(node.nodeType == 3){
                        var tmpNode = node.previousSibling;
                        while(tmpNode && tmpNode.nodeType == 3){
                            firstIndex += tmpNode.nodeValue.replace(fillCharReg,'').length;
                            tmpNode = tmpNode.previousSibling;
                        }
                        firstIndex +=  (isStart ? me.startOffset : me.endOffset)// - (fillCharReg.test(node.nodeValue) ? 1 : 0 )
                    }else{
                        node =  node.childNodes[ isStart ? me.startOffset : me.endOffset];
                        if(node){
                            firstIndex = domUtils.getNodeIndex(node,ignoreTxt);
                        }else{
                            node = isStart ? me.startContainer : me.endContainer;
                            var first = node.firstChild;
                            while(first){
                                if(domUtils.isFillChar(first)){
                                    first = first.nextSibling;
                                    continue;
                                }
                                firstIndex++;
                                if(first.nodeType == 3){
                                    while( first && first.nodeType == 3){
                                        first = first.nextSibling;
                                    }
                                }else{
                                    first = first.nextSibling;
                                }
                            }
                        }
                    }

                }else{
                    firstIndex = isStart ? domUtils.isFillChar(node) ? 0 : me.startOffset  : me.endOffset
                }
                if(firstIndex < 0){
                    firstIndex = 0;
                }
                addrs.push(firstIndex);
                return addrs;
            }
            addr.startAddress = getAddress(true);
            if(!ignoreEnd){
                addr.endAddress = me.collapsed ? [].concat(addr.startAddress) : getAddress();
            }
            return addr;
        },

        /**
         * 儲存
         * @method createAddress
         * @private
         * @return { Boolean } 返回開始和結束的位置
         * @example
         * ```html
         * <body>
         *     <p>
         *         aaaa
         *         <em>
         *             <!-- 選區開始 -->
         *             bbbb
         *             <!-- 選區結束 -->
         *         </em>
         *     </p>
         *
         *     <script>
         *         var range = editor.selection.getRange();
         *         range.moveToAddress({startAddress:[0,1,0,0],endAddress:[0,1,0,4]});
         *         range.select();
         *         //output: 'bbbb'
         *         console.log(editor.selection.getText());
         *     </script>
         * </body>
         * ```
         */
        moveToAddress : function(addr,ignoreEnd){
            var me = this;
            function getNode(address,isStart){
                var tmpNode = me.document.body,
                    parentNode,offset;
                for(var i= 0,ci,l=address.length;i<l;i++){
                    ci = address[i];
                    parentNode = tmpNode;
                    tmpNode = tmpNode.childNodes[ci];
                    if(!tmpNode){
                        offset = ci;
                        break;
                    }
                }
                if(isStart){
                    if(tmpNode){
                        me.setStartBefore(tmpNode)
                    }else{
                        me.setStart(parentNode,offset)
                    }
                }else{
                    if(tmpNode){
                        me.setEndBefore(tmpNode)
                    }else{
                        me.setEnd(parentNode,offset)
                    }
                }
            }
            getNode(addr.startAddress,true);
            !ignoreEnd && addr.endAddress &&  getNode(addr.endAddress);
            return me;
        },

        /**
         * 判斷給定的Range物件是否和當前Range物件表示的是同一個選區
         * @method equals
         * @param { UE.dom.Range } 需要判斷的Range物件
         * @return { Boolean } 如果給定的Range物件與當前Range物件表示的是同一個選區， 則返回true， 否則返回false
         */
        equals : function(rng){
            for(var p in this){
                if(this.hasOwnProperty(p)){
                    if(this[p] !== rng[p])
                        return false
                }
            }
            return true;

        },

        /**
         * 遍歷range內的節點。每當遍歷一個節點時， 都會執行引數項 doFn 指定的函式， 該函式的接受當前遍歷的節點
         * 作為其引數。
         * @method traversal
         * @param { Function }  doFn 對每個遍歷的節點要執行的方法， 該方法接受當前遍歷的節點作為其引數
         * @return { UE.dom.Range } 當前range物件
         * @example
         * ```html
         *
         * <body>
         *
         *     <!-- 選區開始 -->
         *     <span></span>
         *     <a></a>
         *     <!-- 選區結束 -->
         * </body>
         *
         * <script>
         *
         *     //output: <span></span><a></a>
         *     console.log( range.cloneContents() );
         *
         *     range.traversal( function ( node ) {
         *
         *         if ( node.nodeType === 1 ) {
         *             node.className = "test";
         *         }
         *
         *     } );
         *
         *     //output: <span class="test"></span><a class="test"></a>
         *     console.log( range.cloneContents() );
         *
         * </script>
         * ```
         */

        /**
         * 遍歷range內的節點。
         * 每當遍歷一個節點時， 都會執行引數項 doFn 指定的函式， 該函式的接受當前遍歷的節點
         * 作為其引數。
         * 可以通過引數項 filterFn 來指定一個過濾器， 只有符合該過濾器過濾規則的節點才會觸
         * 發doFn函式的執行
         * @method traversal
         * @param { Function } doFn 對每個遍歷的節點要執行的方法， 該方法接受當前遍歷的節點作為其引數
         * @param { Function } filterFn 過濾器， 該函式接受當前遍歷的節點作為引數， 如果該節點滿足過濾
         *                      規則， 請返回true， 該節點會觸發doFn， 否則， 請返回false， 則該節點不
         *                      會觸發doFn。
         * @return { UE.dom.Range } 當前range物件
         * @see UE.dom.Range:traversal(Function)
         * @example
         * ```html
         *
         * <body>
         *
         *     <!-- 選區開始 -->
         *     <span></span>
         *     <a></a>
         *     <!-- 選區結束 -->
         * </body>
         *
         * <script>
         *
         *     //output: <span></span><a></a>
         *     console.log( range.cloneContents() );
         *
         *     range.traversal( function ( node ) {
         *
         *         node.className = "test";
         *
         *     }, function ( node ) {
         *          return node.nodeType === 1;
         *     } );
         *
         *     //output: <span class="test"></span><a class="test"></a>
         *     console.log( range.cloneContents() );
         *
         * </script>
         * ```
         */
        traversal:function(doFn,filterFn){
            if (this.collapsed)
                return this;
            var bookmark = this.createBookmark(),
                end = bookmark.end,
                current = domUtils.getNextDomNode(bookmark.start, false, filterFn);
            while (current && current !== end && (domUtils.getPosition(current, end) & domUtils.POSITION_PRECEDING)) {
                var tmpNode = domUtils.getNextDomNode(current,false,filterFn);
                doFn(current);
                current = tmpNode;
            }
            return this.moveToBookmark(bookmark);
        }
    };
})();

// core/Selection.js
/**
 * 選集
 * @file
 * @module UE.dom
 * @class Selection
 * @since 1.2.6.1
 */

/**
 * 選區集合
 * @unfile
 * @module UE.dom
 * @class Selection
 */
(function () {

    function getBoundaryInformation( range, start ) {
        var getIndex = domUtils.getNodeIndex;
        range = range.duplicate();
        range.collapse( start );
        var parent = range.parentElement();
        //如果節點裡沒有子節點，直接退出
        if ( !parent.hasChildNodes() ) {
            return  {container:parent, offset:0};
        }
        var siblings = parent.children,
            child,
            testRange = range.duplicate(),
            startIndex = 0, endIndex = siblings.length - 1, index = -1,
            distance;
        while ( startIndex <= endIndex ) {
            index = Math.floor( (startIndex + endIndex) / 2 );
            child = siblings[index];
            testRange.moveToElementText( child );
            var position = testRange.compareEndPoints( 'StartToStart', range );
            if ( position > 0 ) {
                endIndex = index - 1;
            } else if ( position < 0 ) {
                startIndex = index + 1;
            } else {
                //trace:1043
                return  {container:parent, offset:getIndex( child )};
            }
        }
        if ( index == -1 ) {
            testRange.moveToElementText( parent );
            testRange.setEndPoint( 'StartToStart', range );
            distance = testRange.text.replace( /(\r\n|\r)/g, '\n' ).length;
            siblings = parent.childNodes;
            if ( !distance ) {
                child = siblings[siblings.length - 1];
                return  {container:child, offset:child.nodeValue.length};
            }

            var i = siblings.length;
            while ( distance > 0 ){
                distance -= siblings[ --i ].nodeValue.length;
            }
            return {container:siblings[i], offset:-distance};
        }
        testRange.collapse( position > 0 );
        testRange.setEndPoint( position > 0 ? 'StartToStart' : 'EndToStart', range );
        distance = testRange.text.replace( /(\r\n|\r)/g, '\n' ).length;
        if ( !distance ) {
            return  dtd.$empty[child.tagName] || dtd.$nonChild[child.tagName] ?
            {container:parent, offset:getIndex( child ) + (position > 0 ? 0 : 1)} :
            {container:child, offset:position > 0 ? 0 : child.childNodes.length}
        }
        while ( distance > 0 ) {
            try {
                var pre = child;
                child = child[position > 0 ? 'previousSibling' : 'nextSibling'];
                distance -= child.nodeValue.length;
            } catch ( e ) {
                return {container:parent, offset:getIndex( pre )};
            }
        }
        return  {container:child, offset:position > 0 ? -distance : child.nodeValue.length + distance}
    }

    /**
     * 將ieRange轉換為Range物件
     * @param {Range}   ieRange    ieRange物件
     * @param {Range}   range      Range物件
     * @return  {Range}  range       返回轉換後的Range物件
     */
    function transformIERangeToRange( ieRange, range ) {
        if ( ieRange.item ) {
            range.selectNode( ieRange.item( 0 ) );
        } else {
            var bi = getBoundaryInformation( ieRange, true );
            range.setStart( bi.container, bi.offset );
            if ( ieRange.compareEndPoints( 'StartToEnd', ieRange ) != 0 ) {
                bi = getBoundaryInformation( ieRange, false );
                range.setEnd( bi.container, bi.offset );
            }
        }
        return range;
    }

    /**
     * 獲得ieRange
     * @param {Selection} sel    Selection物件
     * @return {ieRange}    得到ieRange
     */
    function _getIERange( sel ) {
        var ieRange;
        //ie下有可能報錯
        try {
            ieRange = sel.getNative().createRange();
        } catch ( e ) {
            return null;
        }
        var el = ieRange.item ? ieRange.item( 0 ) : ieRange.parentElement();
        if ( ( el.ownerDocument || el ) === sel.document ) {
            return ieRange;
        }
        return null;
    }

    var Selection = dom.Selection = function ( doc ) {
        var me = this, iframe;
        me.document = doc;
        if ( browser.ie9below ) {
            iframe = domUtils.getWindow( doc ).frameElement;
            domUtils.on( iframe, 'beforedeactivate', function () {
                me._bakIERange = me.getIERange();
            } );
            domUtils.on( iframe, 'activate', function () {
                try {
                    if ( !_getIERange( me ) && me._bakIERange ) {
                        me._bakIERange.select();
                    }
                } catch ( ex ) {
                }
                me._bakIERange = null;
            } );
        }
        iframe = doc = null;
    };

    Selection.prototype = {

        rangeInBody : function(rng,txtRange){
            var node = browser.ie9below || txtRange ? rng.item ? rng.item() : rng.parentElement() : rng.startContainer;

            return node === this.document.body || domUtils.inDoc(node,this.document);
        },

        /**
         * 獲取原生seleciton物件
         * @method getNative
         * @return { Object } 獲得selection物件
         * @example
         * ```javascript
         * editor.selection.getNative();
         * ```
         */
        getNative:function () {
            var doc = this.document;
            try {
                return !doc ? null : browser.ie9below ? doc.selection : domUtils.getWindow( doc ).getSelection();
            } catch ( e ) {
                return null;
            }
        },

        /**
         * 獲得ieRange
         * @method getIERange
         * @return { Object } 返回ie原生的Range
         * @example
         * ```javascript
         * editor.selection.getIERange();
         * ```
         */
        getIERange:function () {
            var ieRange = _getIERange( this );
            if ( !ieRange ) {
                if ( this._bakIERange ) {
                    return this._bakIERange;
                }
            }
            return ieRange;
        },

        /**
         * 快取當前選區的range和選區的開始節點
         * @method cache
         */
        cache:function () {
            this.clear();
            this._cachedRange = this.getRange();
            this._cachedStartElement = this.getStart();
            this._cachedStartElementPath = this.getStartElementPath();
        },

        /**
         * 獲取選區開始位置的父節點到body
         * @method getStartElementPath
         * @return { Array } 返回父節點集合
         * @example
         * ```javascript
         * editor.selection.getStartElementPath();
         * ```
         */
        getStartElementPath:function () {
            if ( this._cachedStartElementPath ) {
                return this._cachedStartElementPath;
            }
            var start = this.getStart();
            if ( start ) {
                return domUtils.findParents( start, true, null, true )
            }
            return [];
        },

        /**
         * 清空緩存
         * @method clear
         */
        clear:function () {
            this._cachedStartElementPath = this._cachedRange = this._cachedStartElement = null;
        },

        /**
         * 編輯器是否得到了選區
         * @method isFocus
         */
        isFocus:function () {
            try {
                if(browser.ie9below){

                    var nativeRange = _getIERange(this);
                    return !!(nativeRange && this.rangeInBody(nativeRange));
                }else{
                    return !!this.getNative().rangeCount;
                }
            } catch ( e ) {
                return false;
            }

        },

        /**
         * 獲取選區對應的Range
         * @method getRange
         * @return { Object } 得到Range物件
         * @example
         * ```javascript
         * editor.selection.getRange();
         * ```
         */
        getRange:function () {
            var me = this;
            function optimze( range ) {
                var child = me.document.body.firstChild,
                    collapsed = range.collapsed;
                while ( child && child.firstChild ) {
                    range.setStart( child, 0 );
                    child = child.firstChild;
                }
                if ( !range.startContainer ) {
                    range.setStart( me.document.body, 0 )
                }
                if ( collapsed ) {
                    range.collapse( true );
                }
            }

            if ( me._cachedRange != null ) {
                return this._cachedRange;
            }
            var range = new baidu.editor.dom.Range( me.document );

            if ( browser.ie9below ) {
                var nativeRange = me.getIERange();
                if ( nativeRange ) {
                    //備份的_bakIERange可能已經實效了，dom樹發生了變化比如從原始碼模式切回來，所以try一下，實效就放到body開始位置
                    try{
                        transformIERangeToRange( nativeRange, range );
                    }catch(e){
                        optimze( range );
                    }

                } else {
                    optimze( range );
                }
            } else {
                var sel = me.getNative();
                if ( sel && sel.rangeCount ) {
                    var firstRange = sel.getRangeAt( 0 );
                    var lastRange = sel.getRangeAt( sel.rangeCount - 1 );
                    range.setStart( firstRange.startContainer, firstRange.startOffset ).setEnd( lastRange.endContainer, lastRange.endOffset );
                    if ( range.collapsed && domUtils.isBody( range.startContainer ) && !range.startOffset ) {
                        optimze( range );
                    }
                } else {
                    //trace:1734 有可能已經不在dom樹上了，標識的節點
                    if ( this._bakRange && domUtils.inDoc( this._bakRange.startContainer, this.document ) ){
                        return this._bakRange;
                    }
                    optimze( range );
                }
            }
            return this._bakRange = range;
        },

        /**
         * 獲取開始元素，用於狀態反射
         * @method getStart
         * @return { Element } 獲得開始元素
         * @example
         * ```javascript
         * editor.selection.getStart();
         * ```
         */
        getStart:function () {
            if ( this._cachedStartElement ) {
                return this._cachedStartElement;
            }
            var range = browser.ie9below ? this.getIERange() : this.getRange(),
                tmpRange,
                start, tmp, parent;
            if ( browser.ie9below ) {
                if ( !range ) {
                    //todo 給第一個值可能會有問題
                    return this.document.body.firstChild;
                }
                //control元素
                if ( range.item ){
                    return range.item( 0 );
                }
                tmpRange = range.duplicate();
                //修正ie下<b>x</b>[xx] 閉合後 <b>x|</b>xx
                tmpRange.text.length > 0 && tmpRange.moveStart( 'character', 1 );
                tmpRange.collapse( 1 );
                start = tmpRange.parentElement();
                parent = tmp = range.parentElement();
                while ( tmp = tmp.parentNode ) {
                    if ( tmp == start ) {
                        start = parent;
                        break;
                    }
                }
            } else {
                range.shrinkBoundary();
                start = range.startContainer;
                if ( start.nodeType == 1 && start.hasChildNodes() ){
                    start = start.childNodes[Math.min( start.childNodes.length - 1, range.startOffset )];
                }
                if ( start.nodeType == 3 ){
                    return start.parentNode;
                }
            }
            return start;
        },

        /**
         * 得到選區中的文字
         * @method getText
         * @return { String } 選區中包含的文字
         * @example
         * ```javascript
         * editor.selection.getText();
         * ```
         */
        getText:function () {
            var nativeSel, nativeRange;
            if ( this.isFocus() && (nativeSel = this.getNative()) ) {
                nativeRange = browser.ie9below ? nativeSel.createRange() : nativeSel.getRangeAt( 0 );
                return browser.ie9below ? nativeRange.text : nativeRange.toString();
            }
            return '';
        },

        /**
         * 清除選區
         * @method clearRange
         * @example
         * ```javascript
         * editor.selection.clearRange();
         * ```
         */
        clearRange : function(){
            this.getNative()[browser.ie9below ? 'empty' : 'removeAllRanges']();
        }
    };
})();

// core/Editor.js
/**
 * 編輯器主類，包含編輯器提供的大部分公用介面
 * @file
 * @module UE
 * @class Editor
 * @since 1.2.6.1
 */

/**
 * UEditor公用空間，UEditor所有的功能都掛載在該空間下
 * @unfile
 * @module UE
 */

/**
 * UEditor的核心類，為使用者提供與編輯器互動的介面。
 * @unfile
 * @module UE
 * @class Editor
 */

(function () {
    var uid = 0, _selectionChangeTimer;

    /**
     * 獲取編輯器的html內容，賦值到編輯器所在表單的textarea文字域裡面
     * @private
     * @method setValue
     * @param { UE.Editor } editor 編輯器事例
     */
    function setValue(form, editor) {
        var textarea;
        if (editor.textarea) {
            if (utils.isString(editor.textarea)) {
                for (var i = 0, ti, tis = domUtils.getElementsByTagName(form, 'textarea'); ti = tis[i++];) {
                    if (ti.id == 'ueditor_textarea_' + editor.options.textarea) {
                        textarea = ti;
                        break;
                    }
                }
            } else {
                textarea = editor.textarea;
            }
        }
        if (!textarea) {
            form.appendChild(textarea = domUtils.createElement(document, 'textarea', {
                'name': editor.options.textarea,
                'id': 'ueditor_textarea_' + editor.options.textarea,
                'style': "display:none"
            }));
            //不要產生多個textarea
            editor.textarea = textarea;
        }
        !textarea.getAttribute('name') && textarea.setAttribute('name', editor.options.textarea );
        textarea.value = editor.hasContents() ?
            (editor.options.allHtmlEnabled ? editor.getAllHtml() : editor.getContent(null, null, true)) :
            ''
    }
    function loadPlugins(me){
        //初始化外掛
        for (var pi in UE.plugins) {
            UE.plugins[pi].call(me);
        }

    }
    function checkCurLang(I18N){
        for(var lang in I18N){
            return lang
        }
    }

    function langReadied(me){
        me.langIsReady = true;

        me.fireEvent("langReady");
    }

    /**
     * 編輯器準備就緒後會觸發該事件
     * @module UE
     * @class Editor
     * @event ready
     * @remind render方法執行完成之後,會觸發該事件
     * @remind
     * @example
     * ```javascript
     * editor.addListener( 'ready', function( editor ) {
     *     editor.execCommand( 'focus' ); //編輯器家在完成後，讓編輯器拿到焦點
     * } );
     * ```
     */
    /**
     * 執行destroy方法,會觸發該事件
     * @module UE
     * @class Editor
     * @event destroy
     * @see UE.Editor:destroy()
     */
    /**
     * 執行reset方法,會觸發該事件
     * @module UE
     * @class Editor
     * @event reset
     * @see UE.Editor:reset()
     */
    /**
     * 執行focus方法,會觸發該事件
     * @module UE
     * @class Editor
     * @event focus
     * @see UE.Editor:focus(Boolean)
     */
    /**
     * 語言載入完成會觸發該事件
     * @module UE
     * @class Editor
     * @event langReady
     */
    /**
     * 執行命令之後會觸發該命令
     * @module UE
     * @class Editor
     * @event beforeExecCommand
     */
    /**
     * 執行命令之後會觸發該命令
     * @module UE
     * @class Editor
     * @event afterExecCommand
     */
    /**
     * 執行命令之前會觸發該命令
     * @module UE
     * @class Editor
     * @event firstBeforeExecCommand
     */
    /**
     * 在getContent方法執行之前會觸發該事件
     * @module UE
     * @class Editor
     * @event beforeGetContent
     * @see UE.Editor:getContent()
     */
    /**
     * 在getContent方法執行之後會觸發該事件
     * @module UE
     * @class Editor
     * @event afterGetContent
     * @see UE.Editor:getContent()
     */
    /**
     * 在getAllHtml方法執行時會觸發該事件
     * @module UE
     * @class Editor
     * @event getAllHtml
     * @see UE.Editor:getAllHtml()
     */
    /**
     * 在setContent方法執行之前會觸發該事件
     * @module UE
     * @class Editor
     * @event beforeSetContent
     * @see UE.Editor:setContent(String)
     */
    /**
     * 在setContent方法執行之後會觸發該事件
     * @module UE
     * @class Editor
     * @event afterSetContent
     * @see UE.Editor:setContent(String)
     */
    /**
     * 每當編輯器內部選區發生改變時，將觸發該事件
     * @event selectionchange
     * @warning 該事件的觸發非常頻繁，不建議在該事件的處理過程中做重量級的處理
     * @example
     * ```javascript
     * editor.addListener( 'selectionchange', function( editor ) {
     *     console.log('選區發生改變');
     * }
     */
    /**
     * 在所有selectionchange的監聽函式執行之前，會觸發該事件
     * @module UE
     * @class Editor
     * @event beforeSelectionChange
     * @see UE.Editor:selectionchange
     */
    /**
     * 在所有selectionchange的監聽函式執行完之後，會觸發該事件
     * @module UE
     * @class Editor
     * @event afterSelectionChange
     * @see UE.Editor:selectionchange
     */
    /**
     * 編輯器內容發生改變時會觸發該事件
     * @module UE
     * @class Editor
     * @event contentChange
     */


    /**
     * 以預設引數構建一個編輯器例項
     * @constructor
     * @remind 通過 改構造方法例項化的編輯器,不帶ui層.需要render到一個容器,編輯器例項才能正常渲染到頁面
     * @example
     * ```javascript
     * var editor = new UE.Editor();
     * editor.execCommand('blod');
     * ```
     * @see UE.Config
     */

    /**
     * 以給定的引數集合建立一個編輯器例項，對於未指定的引數，將應用預設引數。
     * @constructor
     * @remind 通過 改構造方法例項化的編輯器,不帶ui層.需要render到一個容器,編輯器例項才能正常渲染到頁面
     * @param { Object } setting 建立編輯器的引數
     * @example
     * ```javascript
     * var editor = new UE.Editor();
     * editor.execCommand('blod');
     * ```
     * @see UE.Config
     */
    var Editor = UE.Editor = function (options) {
        var me = this;
        me.uid = uid++;
        EventBase.call(me);
        me.commands = {};
        me.options = utils.extend(utils.clone(options || {}), UEDITOR_CONFIG, true);
        me.shortcutkeys = {};
        me.inputRules = [];
        me.outputRules = [];
        //設定預設的常用屬性
        me.setOpt(Editor.defaultOptions(me));

        /* 嘗試非同步載入後臺配置 */
        me.loadServerConfig();

        if(!utils.isEmptyObject(UE.I18N)){
            //修改預設的語言型別
            me.options.lang = checkCurLang(UE.I18N);
            UE.plugin.load(me);
            langReadied(me);

        }else{
            utils.loadFile(document, {
                src: me.options.langPath + me.options.lang + "/" + me.options.lang + ".js",
                tag: "script",
                type: "text/javascript",
                defer: "defer"
            }, function () {
                UE.plugin.load(me);
                langReadied(me);
            });
        }

        UE.instants['ueditorInstant' + me.uid] = me;
    };
    Editor.prototype = {
         registerCommand : function(name,obj){
            this.commands[name] = obj;
         },
        /**
         * 編輯器對外提供的監聽ready事件的介面， 通過呼叫該方法，達到的效果與監聽ready事件是一致的
         * @method ready
         * @param { Function } fn 編輯器ready之後所執行的回撥, 如果在註冊事件之前編輯器已經ready，將會
         * 立即觸發該回調。
         * @remind 需要等待編輯器載入完成後才能執行的程式碼,可以使用該方法傳入
         * @example
         * ```javascript
         * editor.ready( function( editor ) {
         *     editor.setContent('初始化完畢');
         * } );
         * ```
         * @see UE.Editor.event:ready
         */
        ready: function (fn) {
            var me = this;
            if (fn) {
                me.isReady ? fn.apply(me) : me.addListener('ready', fn);
            }
        },

        /**
         * 該方法是提供給外掛裡面使用，設定配置項預設值
         * @method setOpt
         * @warning 三處設定配置項的優先順序: 例項化時傳入引數 > setOpt()設定 > config檔案裡設定
         * @warning 該方法僅供編輯器外掛內部和編輯器初始化時呼叫，其他地方不能呼叫。
         * @param { String } key 編輯器的可接受的選項名稱
         * @param { * } val  該選項可接受的值
         * @example
         * ```javascript
         * editor.setOpt( 'initContent', '歡迎使用編輯器' );
         * ```
         */

        /**
         * 該方法是提供給外掛裡面使用，以{key:value}集合的方式設定外掛內用到的配置項預設值
         * @method setOpt
         * @warning 三處設定配置項的優先順序: 例項化時傳入引數 > setOpt()設定 > config檔案裡設定
         * @warning 該方法僅供編輯器外掛內部和編輯器初始化時呼叫，其他地方不能呼叫。
         * @param { Object } options 將要設定的選項的鍵值對物件
         * @example
         * ```javascript
         * editor.setOpt( {
         *     'initContent': '歡迎使用編輯器'
         * } );
         * ```
         */
        setOpt: function (key, val) {
            var obj = {};
            if (utils.isString(key)) {
                obj[key] = val
            } else {
                obj = key;
            }
            utils.extend(this.options, obj, true);
        },
        getOpt:function(key){
            return this.options[key]
        },
        /**
         * 銷燬編輯器例項，使用textarea代替
         * @method destroy
         * @example
         * ```javascript
         * editor.destroy();
         * ```
         */
        destroy: function () {

            var me = this;
            me.fireEvent('destroy');
            var container = me.container.parentNode;
            var textarea = me.textarea;
            if (!textarea) {
                textarea = document.createElement('textarea');
                container.parentNode.insertBefore(textarea, container);
            } else {
                textarea.style.display = ''
            }

            textarea.style.width = me.iframe.offsetWidth + 'px';
            textarea.style.height = me.iframe.offsetHeight + 'px';
            textarea.value = me.getContent();
            textarea.id = me.key;
            container.innerHTML = '';
            domUtils.remove(container);
            var key = me.key;
            //trace:2004
            for (var p in me) {
                if (me.hasOwnProperty(p)) {
                    delete this[p];
                }
            }
            UE.delEditor(key);
        },

        /**
         * 渲染編輯器的DOM到指定容器
         * @method render
         * @param { String } containerId 指定一個容器ID
         * @remind 執行該方法,會觸發ready事件
         * @warning 必須且只能呼叫一次
         */

        /**
         * 渲染編輯器的DOM到指定容器
         * @method render
         * @param { Element } containerDom 直接指定容器物件
         * @remind 執行該方法,會觸發ready事件
         * @warning 必須且只能呼叫一次
         */
        render: function (container) {
            var me = this,
                options = me.options,
                getStyleValue=function(attr){
                    return parseInt(domUtils.getComputedStyle(container,attr));
                };
            if (utils.isString(container)) {
                container = document.getElementById(container);
            }
            if (container) {
                if(options.initialFrameWidth){
                    options.minFrameWidth = options.initialFrameWidth
                }else{
                    options.minFrameWidth = options.initialFrameWidth = container.offsetWidth;
                }
                if(options.initialFrameHeight){
                    options.minFrameHeight = options.initialFrameHeight
                }else{
                    options.initialFrameHeight = options.minFrameHeight = container.offsetHeight;
                }

                container.style.width = /%$/.test(options.initialFrameWidth) ?  '100%' : options.initialFrameWidth-
                    getStyleValue("padding-left")- getStyleValue("padding-right") +'px';
                container.style.height = /%$/.test(options.initialFrameHeight) ?  '100%' : options.initialFrameHeight -
                    getStyleValue("padding-top")- getStyleValue("padding-bottom") +'px';

                container.style.zIndex = options.zIndex;

                var html = ( ie && browser.version < 9  ? '' : '<!DOCTYPE html>') +
                    '<html xmlns=\'http://www.w3.org/1999/xhtml\' class=\'view\' ><head>' +
                    '<style type=\'text/css\'>' +
                    //設定四周的留邊
                    '.view{padding:0;word-wrap:break-word;cursor:text;height:90%;}\n' +
                    //設定預設字型和字號
                    //font-family不能呢隨便改，在safari下fillchar會有解析問題
                    'body{margin:8px;font-family:sans-serif;font-size:14px;}' +
                    //設定段落間距
                    'p{margin:5px 0;}</style>' +
                    ( options.iframeCssUrl ? '<link rel=\'stylesheet\' type=\'text/css\' href=\'' + utils.unhtml(options.iframeCssUrl) + '\'/>' : '' ) +
                    (options.initialStyle ? '<style>' + options.initialStyle + '</style>' : '') +
                    '</head><body class=\'view\' ></body>' +
                    '<script type=\'text/javascript\' ' + (ie ? 'defer=\'defer\'' : '' ) +' id=\'_initialScript\'>' +
                    'setTimeout(function(){editor = window.parent.UE.instants[\'ueditorInstant' + me.uid + '\'];editor._setup(document);},0);' +
                    'var _tmpScript = document.getElementById(\'_initialScript\');_tmpScript.parentNode.removeChild(_tmpScript);</script></html>';
                container.appendChild(domUtils.createElement(document, 'iframe', {
                    id: 'ueditor_' + me.uid,
                    width: "100%",
                    height: "100%",
                    frameborder: "0",
                    //先註釋掉了，加的原因忘記了，但開啟會直接導致全屏模式下內容多時不會出現滾動條
//                    scrolling :'no',
                    src: 'javascript:void(function(){document.open();' + (options.customDomain && document.domain != location.hostname ?  'document.domain="' + document.domain + '";' : '') +
                        'document.write("' + html + '");document.close();}())'
                }));
                container.style.overflow = 'hidden';
                //解決如果是給定的百分比，會導致高度算不對的問題
                setTimeout(function(){
                    if( /%$/.test(options.initialFrameWidth)){
                        options.minFrameWidth = options.initialFrameWidth = container.offsetWidth;
                        //如果這裡給定寬度，會導致ie在拖動視窗大小時，編輯區域不隨著變化
//                        container.style.width = options.initialFrameWidth + 'px';
                    }
                    if(/%$/.test(options.initialFrameHeight)){
                        options.minFrameHeight = options.initialFrameHeight = container.offsetHeight;
                        container.style.height = options.initialFrameHeight + 'px';
                    }
                })
            }
        },

        /**
         * 編輯器初始化
         * @method _setup
         * @private
         * @param { Element } doc 編輯器Iframe中的文件物件
         */
        _setup: function (doc) {

            var me = this,
                options = me.options;
            if (ie) {
                doc.body.disabled = true;
                doc.body.contentEditable = true;
                doc.body.disabled = false;
            } else {
                doc.body.contentEditable = true;
            }
            doc.body.spellcheck = false;
            me.document = doc;
            me.window = doc.defaultView || doc.parentWindow;
            me.iframe = me.window.frameElement;
            me.body = doc.body;
            me.selection = new dom.Selection(doc);
            //gecko初始化就能得到range,無法判斷isFocus了
            var geckoSel;
            if (browser.gecko && (geckoSel = this.selection.getNative())) {
                geckoSel.removeAllRanges();
            }
            this._initEvents();
            //為form提交提供一個隱藏的textarea
            for (var form = this.iframe.parentNode; !domUtils.isBody(form); form = form.parentNode) {
                if (form.tagName == 'FORM') {
                    me.form = form;
                    if(me.options.autoSyncData){
                        domUtils.on(me.window,'blur',function(){
                            setValue(form,me);
                        });
                    }else{
                        domUtils.on(form, 'submit', function () {
                            setValue(this, me);
                        });
                    }
                    break;
                }
            }
            if (options.initialContent) {
                if (options.autoClearinitialContent) {
                    var oldExecCommand = me.execCommand;
                    me.execCommand = function () {
                        me.fireEvent('firstBeforeExecCommand');
                        return oldExecCommand.apply(me, arguments);
                    };
                    this._setDefaultContent(options.initialContent);
                } else
                    this.setContent(options.initialContent, false, true);
            }

            //編輯器不能為空內容

            if (domUtils.isEmptyNode(me.body)) {
                me.body.innerHTML = '<p>' + (browser.ie ? '' : '<br/>') + '</p>';
            }
            //如果要求focus, 就把游標定位到內容開始
            if (options.focus) {
                setTimeout(function () {
                    me.focus(me.options.focusInEnd);
                    //如果自動清除開著，就不需要做selectionchange;
                    !me.options.autoClearinitialContent && me._selectionChange();
                }, 0);
            }
            if (!me.container) {
                me.container = this.iframe.parentNode;
            }
            if (options.fullscreen && me.ui) {
                me.ui.setFullScreen(true);
            }

            try {
                me.document.execCommand('2D-position', false, false);
            } catch (e) {
            }
            try {
                me.document.execCommand('enableInlineTableEditing', false, false);
            } catch (e) {
            }
            try {
                me.document.execCommand('enableObjectResizing', false, false);
            } catch (e) {
            }

            //掛接快捷鍵
            me._bindshortcutKeys();
            me.isReady = 1;
            me.fireEvent('ready');
            options.onready && options.onready.call(me);
            if (!browser.ie9below) {
                domUtils.on(me.window, ['blur', 'focus'], function (e) {
                    //chrome下會出現alt+tab切換時，導致選區位置不對
                    if (e.type == 'blur') {
                        me._bakRange = me.selection.getRange();
                        try {
                            me._bakNativeRange = me.selection.getNative().getRangeAt(0);
                            me.selection.getNative().removeAllRanges();
                        } catch (e) {
                            me._bakNativeRange = null;
                        }

                    } else {
                        try {
                            me._bakRange && me._bakRange.select();
                        } catch (e) {
                        }
                    }
                });
            }
            //trace:1518 ff3.6body不夠寛，會導致點選空白處無法獲得焦點
            if (browser.gecko && browser.version <= 10902) {
                //修復ff3.6初始化進來，不能點選獲得焦點
                me.body.contentEditable = false;
                setTimeout(function () {
                    me.body.contentEditable = true;
                }, 100);
                setInterval(function () {
                    me.body.style.height = me.iframe.offsetHeight - 20 + 'px'
                }, 100)
            }

            !options.isShow && me.setHide();
            options.readonly && me.setDisabled();
        },

        /**
         * 同步資料到編輯器所在的form
         * 從編輯器的容器節點向上查詢form元素，若找到，就同步編輯內容到找到的form裡，為提交資料做準備，主要用於是手動提交的情況
         * 後臺取得資料的鍵值，使用你容器上的name屬性，如果沒有就使用引數裡的textarea項
         * @method sync
         * @example
         * ```javascript
         * editor.sync();
         * form.sumbit(); //form變數已經指向了form元素
         * ```
         */

        /**
         * 根據傳入的formId，在頁面上查詢要同步資料的表單，若找到，就同步編輯內容到找到的form裡，為提交資料做準備
         * 後臺取得資料的鍵值，該鍵值預設使用給定的編輯器容器的name屬性，如果沒有name屬性則使用引數項裡給定的“textarea”項
         * @method sync
         * @param { String } formID 指定一個要同步資料的form的id,編輯器的資料會同步到你指定form下
         */
        sync: function (formId) {
            var me = this,
                form = formId ? document.getElementById(formId) :
                    domUtils.findParent(me.iframe.parentNode, function (node) {
                        return node.tagName == 'FORM'
                    }, true);
            form && setValue(form, me);
        },

        /**
         * 設定編輯器高度
         * @method setHeight
         * @remind 當配置項autoHeightEnabled為真時,該方法無效
         * @param { Number } number 設定的高度值，純數值，不帶單位
         * @example
         * ```javascript
         * editor.setHeight(number);
         * ```
         */
        setHeight: function (height,notSetHeight) {
            if (height !== parseInt(this.iframe.parentNode.style.height)) {
                this.iframe.parentNode.style.height = height + 'px';
            }
            !notSetHeight && (this.options.minFrameHeight = this.options.initialFrameHeight = height);
            this.body.style.height = height + 'px';
            !notSetHeight && this.trigger('setHeight')
        },

        /**
         * 為編輯器的編輯命令提供快捷鍵
         * 這個介面是為外掛擴充套件提供的介面,主要是為新新增的外掛，如果需要新增快捷鍵，所提供的介面
         * @method addshortcutkey
         * @param { Object } keyset 命令名和快捷鍵鍵值對物件，多個按鈕的快捷鍵用“＋”分隔
         * @example
         * ```javascript
         * editor.addshortcutkey({
         *     "Bold" : "ctrl+66",//^B
         *     "Italic" : "ctrl+73", //^I
         * });
         * ```
         */
        /**
         * 這個介面是為外掛擴充套件提供的介面,主要是為新新增的外掛，如果需要新增快捷鍵，所提供的介面
         * @method addshortcutkey
         * @param { String } cmd 觸發快捷鍵時，響應的命令
         * @param { String } keys 快捷鍵的字串，多個按鈕用“＋”分隔
         * @example
         * ```javascript
         * editor.addshortcutkey("Underline", "ctrl+85"); //^U
         * ```
         */
        addshortcutkey: function (cmd, keys) {
            var obj = {};
            if (keys) {
                obj[cmd] = keys
            } else {
                obj = cmd;
            }
            utils.extend(this.shortcutkeys, obj)
        },

        /**
         * 對編輯器設定keydown事件監聽，繫結快捷鍵和命令，當快捷鍵組合觸發成功，會響應對應的命令
         * @method _bindshortcutKeys
         * @private
         */
        _bindshortcutKeys: function () {
            var me = this, shortcutkeys = this.shortcutkeys;
            me.addListener('keydown', function (type, e) {
                var keyCode = e.keyCode || e.which;
                for (var i in shortcutkeys) {
                    var tmp = shortcutkeys[i].split(',');
                    for (var t = 0, ti; ti = tmp[t++];) {
                        ti = ti.split(':');
                        var key = ti[0], param = ti[1];
                        if (/^(ctrl)(\+shift)?\+(\d+)$/.test(key.toLowerCase()) || /^(\d+)$/.test(key)) {
                            if (( (RegExp.$1 == 'ctrl' ? (e.ctrlKey || e.metaKey) : 0)
                                && (RegExp.$2 != "" ? e[RegExp.$2.slice(1) + "Key"] : 1)
                                && keyCode == RegExp.$3
                                ) ||
                                keyCode == RegExp.$1
                                ) {
                                if (me.queryCommandState(i,param) != -1)
                                    me.execCommand(i, param);
                                domUtils.preventDefault(e);
                            }
                        }
                    }

                }
            });
        },

        /**
         * 獲取編輯器的內容
         * @method getContent
         * @warning 該方法獲取到的是經過編輯器內建的過濾規則進行過濾後得到的內容
         * @return { String } 編輯器的內容字串, 如果編輯器的內容為空，或者是空的標籤內容（如:”&lt;p&gt;&lt;br/&gt;&lt;/p&gt;“）， 則返回空字串
         * @example
         * ```javascript
         * //編輯器html內容:<p>1<strong>2<em>34</em>5</strong>6</p>
         * var content = editor.getContent(); //返回值:<p>1<strong>2<em>34</em>5</strong>6</p>
         * ```
         */

        /**
         * 獲取編輯器的內容。 可以通過引數定義編輯器內建的判空規則
         * @method getContent
         * @param { Function } fn 自定的判空規則， 要求該方法返回一個boolean型別的值，
         *                      代表當前編輯器的內容是否空，
         *                      如果返回true， 則該方法將直接返回空字串；如果返回false，則編輯器將返回
         *                      經過內建過濾規則處理後的內容。
         * @remind 該方法在處理包含有初始化內容的時候能起到很好的作用。
         * @warning 該方法獲取到的是經過編輯器內建的過濾規則進行過濾後得到的內容
         * @return { String } 編輯器的內容字串
         * @example
         * ```javascript
         * // editor 是一個編輯器的例項
         * var content = editor.getContent( function ( editor ) {
         *      return editor.body.innerHTML === '歡迎使用UEditor'; //返回空字串
         * } );
         * ```
         */
        getContent: function (cmd, fn,notSetCursor,ignoreBlank,formatter) {
            var me = this;
            if (cmd && utils.isFunction(cmd)) {
                fn = cmd;
                cmd = '';
            }
            if (fn ? !fn() : !this.hasContents()) {
                return '';
            }
            me.fireEvent('beforegetcontent');
            var root = UE.htmlparser(me.body.innerHTML,ignoreBlank);
            me.filterOutputRule(root);
            me.fireEvent('aftergetcontent', cmd,root);
            return  root.toHtml(formatter);
        },

        /**
         * 取得完整的html程式碼，可以直接顯示成完整的html文件
         * @method getAllHtml
         * @return { String } 編輯器的內容html文件字串
         * @eaxmple
         * ```javascript
         * editor.getAllHtml(); //返回格式大致是: <html><head>...</head><body>...</body></html>
         * ```
         */
        getAllHtml: function () {
            var me = this,
                headHtml = [],
                html = '';
            me.fireEvent('getAllHtml', headHtml);
            if (browser.ie && browser.version > 8) {
                var headHtmlForIE9 = '';
                utils.each(me.document.styleSheets, function (si) {
                    headHtmlForIE9 += ( si.href ? '<link rel="stylesheet" type="text/css" href="' + si.href + '" />' : '<style>' + si.cssText + '</style>');
                });
                utils.each(me.document.getElementsByTagName('script'), function (si) {
                    headHtmlForIE9 += si.outerHTML;
                });

            }
            return '<html><head>' + (me.options.charset ? '<meta http-equiv="Content-Type" content="text/html; charset=' + me.options.charset + '"/>' : '')
                + (headHtmlForIE9 || me.document.getElementsByTagName('head')[0].innerHTML) + headHtml.join('\n') + '</head>'
                + '<body ' + (ie && browser.version < 9 ? 'class="view"' : '') + '>' + me.getContent(null, null, true) + '</body></html>';
        },

        /**
         * 得到編輯器的純文字內容，但會保留段落格式
         * @method getPlainTxt
         * @return { String } 編輯器帶段落格式的純文字內容字串
         * @example
         * ```javascript
         * //編輯器html內容:<p><strong>1</strong></p><p><strong>2</strong></p>
         * console.log(editor.getPlainTxt()); //輸出:"1\n2\n
         * ```
         */
        getPlainTxt: function () {
            var reg = new RegExp(domUtils.fillChar, 'g'),
                html = this.body.innerHTML.replace(/[\n\r]/g, '');//ie要先去了\n在處理
            html = html.replace(/<(p|div)[^>]*>(<br\/?>|&nbsp;)<\/\1>/gi, '\n')
                .replace(/<br\/?>/gi, '\n')
                .replace(/<[^>/]+>/g, '')
                .replace(/(\n)?<\/([^>]+)>/g, function (a, b, c) {
                    return dtd.$block[c] ? '\n' : b ? b : '';
                });
            //取出來的空格會有c2a0會變成亂碼，處理這種情況\u00a0
            return html.replace(reg, '').replace(/\u00a0/g, ' ').replace(/&nbsp;/g, ' ');
        },

        /**
         * 獲取編輯器中的純文字內容,沒有段落格式
         * @method getContentTxt
         * @return { String } 編輯器不帶段落格式的純文字內容字串
         * @example
         * ```javascript
         * //編輯器html內容:<p><strong>1</strong></p><p><strong>2</strong></p>
         * console.log(editor.getPlainTxt()); //輸出:"12
         * ```
         */
        getContentTxt: function () {
            var reg = new RegExp(domUtils.fillChar, 'g');
            //取出來的空格會有c2a0會變成亂碼，處理這種情況\u00a0
            return this.body[browser.ie ? 'innerText' : 'textContent'].replace(reg, '').replace(/\u00a0/g, ' ');
        },

        /**
         * 設定編輯器的內容，可修改編輯器當前的html內容
         * @method setContent
         * @warning 通過該方法插入的內容，是經過編輯器內建的過濾規則進行過濾後得到的內容
         * @warning 該方法會觸發selectionchange事件
         * @param { String } html 要插入的html內容
         * @example
         * ```javascript
         * editor.getContent('<p>test</p>');
         * ```
         */

        /**
         * 設定編輯器的內容，可修改編輯器當前的html內容
         * @method setContent
         * @warning 通過該方法插入的內容，是經過編輯器內建的過濾規則進行過濾後得到的內容
         * @warning 該方法會觸發selectionchange事件
         * @param { String } html 要插入的html內容
         * @param { Boolean } isAppendTo 若傳入true，不清空原來的內容，在最後插入內容，否則，清空內容再插入
         * @example
         * ```javascript
         * //假設設定前的編輯器內容是 <p>old text</p>
         * editor.setContent('<p>new text</p>', true); //插入的結果是<p>old text</p><p>new text</p>
         * ```
         */
        setContent: function (html, isAppendTo, notFireSelectionchange) {
            var me = this;

            me.fireEvent('beforesetcontent', html);
            var root = UE.htmlparser(html);
            me.filterInputRule(root);
            html = root.toHtml();

            me.body.innerHTML = (isAppendTo ? me.body.innerHTML : '') + html;


            function isCdataDiv(node){
                return  node.tagName == 'DIV' && node.getAttribute('cdata_tag');
            }
            //給文字或者inline節點套p標籤
            if (me.options.enterTag == 'p') {

                var child = this.body.firstChild, tmpNode;
                if (!child || child.nodeType == 1 &&
                    (dtd.$cdata[child.tagName] || isCdataDiv(child) ||
                        domUtils.isCustomeNode(child)
                        )
                    && child === this.body.lastChild) {
                    this.body.innerHTML = '<p>' + (browser.ie ? '&nbsp;' : '<br/>') + '</p>' + this.body.innerHTML;

                } else {
                    var p = me.document.createElement('p');
                    while (child) {
                        while (child && (child.nodeType == 3 || child.nodeType == 1 && dtd.p[child.tagName] && !dtd.$cdata[child.tagName])) {
                            tmpNode = child.nextSibling;
                            p.appendChild(child);
                            child = tmpNode;
                        }
                        if (p.firstChild) {
                            if (!child) {
                                me.body.appendChild(p);
                                break;
                            } else {
                                child.parentNode.insertBefore(p, child);
                                p = me.document.createElement('p');
                            }
                        }
                        child = child.nextSibling;
                    }
                }
            }
            me.fireEvent('aftersetcontent');
            me.fireEvent('contentchange');

            !notFireSelectionchange && me._selectionChange();
            //清除儲存的選區
            me._bakRange = me._bakIERange = me._bakNativeRange = null;
            //trace:1742 setContent後gecko能得到焦點問題
            var geckoSel;
            if (browser.gecko && (geckoSel = this.selection.getNative())) {
                geckoSel.removeAllRanges();
            }
            if(me.options.autoSyncData){
                me.form && setValue(me.form,me);
            }
        },

        /**
         * 讓編輯器獲得焦點，預設focus到編輯器頭部
         * @method focus
         * @example
         * ```javascript
         * editor.focus()
         * ```
         */

        /**
         * 讓編輯器獲得焦點，toEnd確定focus位置
         * @method focus
         * @param { Boolean } toEnd 預設focus到編輯器頭部，toEnd為true時focus到內容尾部
         * @example
         * ```javascript
         * editor.focus(true)
         * ```
         */
        focus: function (toEnd) {
            try {
                var me = this,
                    rng = me.selection.getRange();
                if (toEnd) {
                    var node = me.body.lastChild;
                    if(node && node.nodeType == 1 && !dtd.$empty[node.tagName]){
                        if(domUtils.isEmptyBlock(node)){
                            rng.setStartAtFirst(node)
                        }else{
                            rng.setStartAtLast(node)
                        }
                        rng.collapse(true);
                    }
                    rng.setCursor(true);
                } else {
                    if(!rng.collapsed && domUtils.isBody(rng.startContainer) && rng.startOffset == 0){

                        var node = me.body.firstChild;
                        if(node && node.nodeType == 1 && !dtd.$empty[node.tagName]){
                            rng.setStartAtFirst(node).collapse(true);
                        }
                    }

                    rng.select(true);

                }
                this.fireEvent('focus selectionchange');
            } catch (e) {
            }

        },
        isFocus:function(){
            return this.selection.isFocus();
        },
        blur:function(){
            var sel = this.selection.getNative();
            if(sel.empty && browser.ie){
                var nativeRng = document.body.createTextRange();
                nativeRng.moveToElementText(document.body);
                nativeRng.collapse(true);
                nativeRng.select();
                sel.empty()
            }else{
                sel.removeAllRanges()
            }

            //this.fireEvent('blur selectionchange');
        },
        /**
         * 初始化UE事件及部分事件代理
         * @method _initEvents
         * @private
         */
        _initEvents: function () {
            var me = this,
                doc = me.document,
                win = me.window;
            me._proxyDomEvent = utils.bind(me._proxyDomEvent, me);
            domUtils.on(doc, ['click', 'contextmenu', 'mousedown', 'keydown', 'keyup', 'keypress', 'mouseup', 'mouseover', 'mouseout', 'selectstart'], me._proxyDomEvent);
            domUtils.on(win, ['focus', 'blur'], me._proxyDomEvent);
            domUtils.on(me.body,'drop',function(e){
                //阻止ff下預設的彈出新頁面開啟圖片
                if(browser.gecko && e.stopPropagation) { e.stopPropagation(); }
                me.fireEvent('contentchange')
            });
            domUtils.on(doc, ['mouseup', 'keydown'], function (evt) {
                //特殊鍵不觸發selectionchange
                if (evt.type == 'keydown' && (evt.ctrlKey || evt.metaKey || evt.shiftKey || evt.altKey)) {
                    return;
                }
                if (evt.button == 2)return;
                me._selectionChange(250, evt);
            });
        },
        /**
         * 觸發事件代理
         * @method _proxyDomEvent
         * @private
         * @return { * } fireEvent的返回值
         * @see UE.EventBase:fireEvent(String)
         */
        _proxyDomEvent: function (evt) {
            if(this.fireEvent('before' + evt.type.replace(/^on/, '').toLowerCase()) === false){
                return false;
            }
            if(this.fireEvent(evt.type.replace(/^on/, ''), evt) === false){
                return false;
            }
            return this.fireEvent('after' + evt.type.replace(/^on/, '').toLowerCase())
        },
        /**
         * 變化選區
         * @method _selectionChange
         * @private
         */
        _selectionChange: function (delay, evt) {
            var me = this;
            //有游標才做selectionchange 為了解決未focus時點選source不能觸發更改工具欄狀態的問題（source命令notNeedUndo=1）
//            if ( !me.selection.isFocus() ){
//                return;
//            }


            var hackForMouseUp = false;
            var mouseX, mouseY;
            if (browser.ie && browser.version < 9 && evt && evt.type == 'mouseup') {
                var range = this.selection.getRange();
                if (!range.collapsed) {
                    hackForMouseUp = true;
                    mouseX = evt.clientX;
                    mouseY = evt.clientY;
                }
            }
            clearTimeout(_selectionChangeTimer);
            _selectionChangeTimer = setTimeout(function () {
                if (!me.selection || !me.selection.getNative()) {
                    return;
                }
                //修復一個IE下的bug: 滑鼠點選一段已選擇的文字中間時，可能在mouseup後的一段時間內取到的range是在selection的type為None下的錯誤值.
                //IE下如果使用者是拖拽一段已選擇文字，則不會觸發mouseup事件，所以這裡的特殊處理不會對其有影響
                var ieRange;
                if (hackForMouseUp && me.selection.getNative().type == 'None') {
                    ieRange = me.document.body.createTextRange();
                    try {
                        ieRange.moveToPoint(mouseX, mouseY);
                    } catch (ex) {
                        ieRange = null;
                    }
                }
                var bakGetIERange;
                if (ieRange) {
                    bakGetIERange = me.selection.getIERange;
                    me.selection.getIERange = function () {
                        return ieRange;
                    };
                }
                me.selection.cache();
                if (bakGetIERange) {
                    me.selection.getIERange = bakGetIERange;
                }
                if (me.selection._cachedRange && me.selection._cachedStartElement) {
                    me.fireEvent('beforeselectionchange');
                    // 第二個引數causeByUi為true代表由使用者互動造成的selectionchange.
                    me.fireEvent('selectionchange', !!evt);
                    me.fireEvent('afterselectionchange');
                    me.selection.clear();
                }
            }, delay || 50);
        },

        /**
         * 執行編輯命令
         * @method _callCmdFn
         * @private
         * @param { String } fnName 函式名稱
         * @param { * } args 傳給命令函式的引數
         * @return { * } 返回命令函式執行的返回值
         */
        _callCmdFn: function (fnName, args) {
            var cmdName = args[0].toLowerCase(),
                cmd, cmdFn;
            cmd = this.commands[cmdName] || UE.commands[cmdName];
            cmdFn = cmd && cmd[fnName];
            //沒有querycommandstate或者沒有command的都預設返回0
            if ((!cmd || !cmdFn) && fnName == 'queryCommandState') {
                return 0;
            } else if (cmdFn) {
                return cmdFn.apply(this, args);
            }
        },

        /**
         * 執行編輯命令cmdName，完成富文字編輯效果
         * @method execCommand
         * @param { String } cmdName 需要執行的命令
         * @remind 具體命令的使用請參考<a href="#COMMAND.LIST">命令列表</a>
         * @return { * } 返回命令函式執行的返回值
         * @example
         * ```javascript
         * editor.execCommand(cmdName);
         * ```
         */
        execCommand: function (cmdName) {
            cmdName = cmdName.toLowerCase();
            var me = this,
                result,
                cmd = me.commands[cmdName] || UE.commands[cmdName];
            if (!cmd || !cmd.execCommand) {
                return null;
            }
            if (!cmd.notNeedUndo && !me.__hasEnterExecCommand) {
                me.__hasEnterExecCommand = true;
                if (me.queryCommandState.apply(me,arguments) != -1) {
                    me.fireEvent('saveScene');
                    me.fireEvent.apply(me, ['beforeexeccommand', cmdName].concat(arguments));
                    result = this._callCmdFn('execCommand', arguments);
                    //儲存場景時，做了內容對比，再看是否進行contentchange觸發，這裡多觸發了一次，去掉
//                    (!cmd.ignoreContentChange && !me._ignoreContentChange) && me.fireEvent('contentchange');
                    me.fireEvent.apply(me, ['afterexeccommand', cmdName].concat(arguments));
                    me.fireEvent('saveScene');
                }
                me.__hasEnterExecCommand = false;
            } else {
                result = this._callCmdFn('execCommand', arguments);
                (!me.__hasEnterExecCommand && !cmd.ignoreContentChange && !me._ignoreContentChange) && me.fireEvent('contentchange')
            }
            (!me.__hasEnterExecCommand && !cmd.ignoreContentChange && !me._ignoreContentChange) && me._selectionChange();
            return result;
        },

        /**
         * 根據傳入的command命令，查選編輯器當前的選區，返回命令的狀態
         * @method  queryCommandState
         * @param { String } cmdName 需要查詢的命令名稱
         * @remind 具體命令的使用請參考<a href="#COMMAND.LIST">命令列表</a>
         * @return { Number } number 返回放前命令的狀態，返回值三種情況：(-1|0|1)
         * @example
         * ```javascript
         * editor.queryCommandState(cmdName)  => (-1|0|1)
         * ```
         * @see COMMAND.LIST
         */
        queryCommandState: function (cmdName) {
            return this._callCmdFn('queryCommandState', arguments);
        },

        /**
         * 根據傳入的command命令，查選編輯器當前的選區，根據命令返回相關的值
         * @method queryCommandValue
         * @param { String } cmdName 需要查詢的命令名稱
         * @remind 具體命令的使用請參考<a href="#COMMAND.LIST">命令列表</a>
         * @remind 只有部分外掛有此方法
         * @return { * } 返回每個命令特定的當前狀態值
         * @grammar editor.queryCommandValue(cmdName)  =>  {*}
         * @see COMMAND.LIST
         */
        queryCommandValue: function (cmdName) {
            return this._callCmdFn('queryCommandValue', arguments);
        },

        /**
         * 檢查編輯區域中是否有內容
         * @method  hasContents
         * @remind 預設有文字內容，或者有以下節點都不認為是空
         * table,ul,ol,dl,iframe,area,base,col,hr,img,embed,input,link,meta,param
         * @return { Boolean } 檢查有內容返回true，否則返回false
         * @example
         * ```javascript
         * editor.hasContents()
         * ```
         */

        /**
         * 檢查編輯區域中是否有內容，若包含引數tags中的節點型別，直接返回true
         * @method  hasContents
         * @param { Array } tags 傳入陣列判斷時用到的節點型別
         * @return { Boolean } 若文件中包含tags數組裡對應的tag，返回true，否則返回false
         * @example
         * ```javascript
         * editor.hasContents(['span']);
         * ```
         */
        hasContents: function (tags) {
            if (tags) {
                for (var i = 0, ci; ci = tags[i++];) {
                    if (this.document.getElementsByTagName(ci).length > 0) {
                        return true;
                    }
                }
            }
            if (!domUtils.isEmptyBlock(this.body)) {
                return true
            }
            //隨時新增,定義的特殊標籤如果存在，不能認為是空
            tags = ['div'];
            for (i = 0; ci = tags[i++];) {
                var nodes = domUtils.getElementsByTagName(this.document, ci);
                for (var n = 0, cn; cn = nodes[n++];) {
                    if (domUtils.isCustomeNode(cn)) {
                        return true;
                    }
                }
            }
            return false;
        },

        /**
         * 重置編輯器，可用來做多個tab使用同一個編輯器例項
         * @method  reset
         * @remind 此方法會清空編輯器內容，清空回退列表，會觸發reset事件
         * @example
         * ```javascript
         * editor.reset()
         * ```
         */
        reset: function () {
            this.fireEvent('reset');
        },

        /**
         * 設定當前編輯區域可以編輯
         * @method setEnabled
         * @example
         * ```javascript
         * editor.setEnabled()
         * ```
         */
        setEnabled: function () {
            var me = this, range;
            if (me.body.contentEditable == 'false') {
                me.body.contentEditable = true;
                range = me.selection.getRange();
                //有可能內容丟失了
                try {
                    range.moveToBookmark(me.lastBk);
                    delete me.lastBk
                } catch (e) {
                    range.setStartAtFirst(me.body).collapse(true)
                }
                range.select(true);
                if (me.bkqueryCommandState) {
                    me.queryCommandState = me.bkqueryCommandState;
                    delete me.bkqueryCommandState;
                }
                if (me.bkqueryCommandValue) {
                    me.queryCommandValue = me.bkqueryCommandValue;
                    delete me.bkqueryCommandValue;
                }
                me.fireEvent('selectionchange');
            }
        },
        enable: function () {
            return this.setEnabled();
        },

        /** 設定當前編輯區域不可編輯
         * @method setDisabled
         */

        /** 設定當前編輯區域不可編輯,except中的命令除外
         * @method setDisabled
         * @param { String } except 例外命令的字串
         * @remind 即使設定了disable，此處配置的例外命令仍然可以執行
         * @example
         * ```javascript
         * editor.setDisabled('bold'); //禁用工具欄中除加粗之外的所有功能
         * ```
         */

        /** 設定當前編輯區域不可編輯,except中的命令除外
         * @method setDisabled
         * @param { Array } except 例外命令的字串陣列，陣列中的命令仍然可以執行
         * @remind 即使設定了disable，此處配置的例外命令仍然可以執行
         * @example
         * ```javascript
         * editor.setDisabled(['bold','insertimage']); //禁用工具欄中除加粗和插入圖片之外的所有功能
         * ```
         */
        setDisabled: function (except) {
            var me = this;
            except = except ? utils.isArray(except) ? except : [except] : [];
            if (me.body.contentEditable == 'true') {
                if (!me.lastBk) {
                    me.lastBk = me.selection.getRange().createBookmark(true);
                }
                me.body.contentEditable = false;
                me.bkqueryCommandState = me.queryCommandState;
                me.bkqueryCommandValue = me.queryCommandValue;
                me.queryCommandState = function (type) {
                    if (utils.indexOf(except, type) != -1) {
                        return me.bkqueryCommandState.apply(me, arguments);
                    }
                    return -1;
                };
                me.queryCommandValue = function (type) {
                    if (utils.indexOf(except, type) != -1) {
                        return me.bkqueryCommandValue.apply(me, arguments);
                    }
                    return null;
                };
                me.fireEvent('selectionchange');
            }
        },
        disable: function (except) {
            return this.setDisabled(except);
        },

        /**
         * 設定預設內容
         * @method _setDefaultContent
         * @private
         * @param  { String } cont 要存入的內容
         */
        _setDefaultContent: function () {
            function clear() {
                var me = this;
                if (me.document.getElementById('initContent')) {
                    me.body.innerHTML = '<p>' + (ie ? '' : '<br/>') + '</p>';
                    me.removeListener('firstBeforeExecCommand focus', clear);
                    setTimeout(function () {
                        me.focus();
                        me._selectionChange();
                    }, 0)
                }
            }

            return function (cont) {
                var me = this;
                me.body.innerHTML = '<p id="initContent">' + cont + '</p>';

                me.addListener('firstBeforeExecCommand focus', clear);
            }
        }(),

        /**
         * 顯示編輯器
         * @method setShow
         * @example
         * ```javascript
         * editor.setShow()
         * ```
         */
        setShow: function () {
            var me = this, range = me.selection.getRange();
            if (me.container.style.display == 'none') {
                //有可能內容丟失了
                try {
                    range.moveToBookmark(me.lastBk);
                    delete me.lastBk
                } catch (e) {
                    range.setStartAtFirst(me.body).collapse(true)
                }
                //ie下focus實效，所以做了個延遲
                setTimeout(function () {
                    range.select(true);
                }, 100);
                me.container.style.display = '';
            }

        },
        show: function () {
            return this.setShow();
        },
        /**
         * 隱藏編輯器
         * @method setHide
         * @example
         * ```javascript
         * editor.setHide()
         * ```
         */
        setHide: function () {
            var me = this;
            if (!me.lastBk) {
                me.lastBk = me.selection.getRange().createBookmark(true);
            }
            me.container.style.display = 'none'
        },
        hide: function () {
            return this.setHide();
        },

        /**
         * 根據指定的路徑，獲取對應的語言資源
         * @method getLang
         * @param { String } path 路徑根據的是lang目錄下的語言檔案的路徑結構
         * @return { Object | String } 根據路徑返回語言資源的Json格式物件或者語言字串
         * @example
         * ```javascript
         * editor.getLang('contextMenu.delete'); //如果當前是中文，那返回是的是'刪除'
         * ```
         */
        getLang: function (path) {
            var lang = UE.I18N[this.options.lang];
            if (!lang) {
                throw Error("not import language file");
            }
            path = (path || "").split(".");
            for (var i = 0, ci; ci = path[i++];) {
                lang = lang[ci];
                if (!lang)break;
            }
            return lang;
        },

        /**
         * 計算編輯器html內容字串的長度
         * @method  getContentLength
         * @return { Number } 返回計算的長度
         * @example
         * ```javascript
         * //編輯器html內容<p><strong>132</strong></p>
         * editor.getContentLength() //返回27
         * ```
         */
        /**
         * 計算編輯器當前純文字內容的長度
         * @method  getContentLength
         * @param { Boolean } ingoneHtml 傳入true時，只按照純文字來計算
         * @return { Number } 返回計算的長度，內容中有hr/img/iframe標籤，長度加1
         * @example
         * ```javascript
         * //編輯器html內容<p><strong>132</strong></p>
         * editor.getContentLength() //返回3
         * ```
         */
        getContentLength: function (ingoneHtml, tagNames) {
            var count = this.getContent(false,false,true).length;
            if (ingoneHtml) {
                tagNames = (tagNames || []).concat([ 'hr', 'img', 'iframe']);
                count = this.getContentTxt().replace(/[\t\r\n]+/g, '').length;
                for (var i = 0, ci; ci = tagNames[i++];) {
                    count += this.document.getElementsByTagName(ci).length;
                }
            }
            return count;
        },

        /**
         * 註冊輸入過濾規則
         * @method  addInputRule
         * @param { Function } rule 要新增的過濾規則
         * @example
         * ```javascript
         * editor.addInputRule(function(root){
         *   $.each(root.getNodesByTagName('div'),function(i,node){
         *       node.tagName="p";
         *   });
         * });
         * ```
         */
        addInputRule: function (rule) {
            this.inputRules.push(rule);
        },

        /**
         * 執行註冊的過濾規則
         * @method  filterInputRule
         * @param { UE.uNode } root 要過濾的uNode節點
         * @remind 執行editor.setContent方法和執行'inserthtml'命令後，會執行該過濾函式
         * @example
         * ```javascript
         * editor.filterInputRule(editor.body);
         * ```
         * @see UE.Editor:addInputRule
         */
        filterInputRule: function (root) {
            for (var i = 0, ci; ci = this.inputRules[i++];) {
                ci.call(this, root)
            }
        },

        /**
         * 註冊輸出過濾規則
         * @method  addOutputRule
         * @param { Function } rule 要新增的過濾規則
         * @example
         * ```javascript
         * editor.addOutputRule(function(root){
         *   $.each(root.getNodesByTagName('p'),function(i,node){
         *       node.tagName="div";
         *   });
         * });
         * ```
         */
        addOutputRule: function (rule) {
            this.outputRules.push(rule)
        },

        /**
         * 根據輸出過濾規則，過濾編輯器內容
         * @method  filterOutputRule
         * @remind 執行editor.getContent方法的時候，會先執行該過濾函式
         * @param { UE.uNode } root 要過濾的uNode節點
         * @example
         * ```javascript
         * editor.filterOutputRule(editor.body);
         * ```
         * @see UE.Editor:addOutputRule
         */
        filterOutputRule: function (root) {
            for (var i = 0, ci; ci = this.outputRules[i++];) {
                ci.call(this, root)
            }
        },

        /**
         * 根據action名稱獲取請求的路徑
         * @method  getActionUrl
         * @remind 假如沒有設定serverUrl,會根據imageUrl設定預設的controller路徑
         * @param { String } action action名稱
         * @example
         * ```javascript
         * editor.getActionUrl('config'); //返回 "/ueditor/php/controller.php?action=config"
         * editor.getActionUrl('image'); //返回 "/ueditor/php/controller.php?action=uplaodimage"
         * editor.getActionUrl('scrawl'); //返回 "/ueditor/php/controller.php?action=uplaodscrawl"
         * editor.getActionUrl('imageManager'); //返回 "/ueditor/php/controller.php?action=listimage"
         * ```
         */
        getActionUrl: function(action){
            var actionName = this.getOpt(action) || action,
                imageUrl = this.getOpt('imageUrl'),
                serverUrl = this.getOpt('serverUrl');

            if(!serverUrl && imageUrl) {
                serverUrl = imageUrl.replace(/^(.*[\/]).+([\.].+)$/, '$1controller$2');
            }

            if(serverUrl) {
                serverUrl = serverUrl + (serverUrl.indexOf('?') == -1 ? '?':'&') + 'action=' + (actionName || '');
                return utils.formatUrl(serverUrl);
            } else {
                return '';
            }
        }
    };
    utils.inherits(Editor, EventBase);
})();


// core/Editor.defaultoptions.js
//維護編輯器一下預設的不在外掛中的配置項
UE.Editor.defaultOptions = function(editor){

    var _url = editor.options.UEDITOR_HOME_URL;
    return {
        isShow: true,
        initialContent: '',
        initialStyle:'',
        autoClearinitialContent: false,
        iframeCssUrl: _url + 'themes/iframe.css',
        textarea: 'editorValue',
        focus: false,
        focusInEnd: true,
        autoClearEmptyNode: true,
        fullscreen: false,
        readonly: false,
        zIndex: 999,
        imagePopup: true,
        enterTag: 'p',
        customDomain: false,
        lang: 'zh-cn',
        langPath: _url + 'lang/',
        theme: 'default',
        themePath: _url + 'themes/',
        allHtmlEnabled: false,
        scaleEnabled: false,
        tableNativeEditInFF: false,
        autoSyncData : true,
        fileNameFormat: '{time}{rand:6}'
    }
};

// core/loadconfig.js
(function(){

    UE.Editor.prototype.loadServerConfig = function(){
        var me = this;
        setTimeout(function(){
            try{
                me.options.imageUrl && me.setOpt('serverUrl', me.options.imageUrl.replace(/^(.*[\/]).+([\.].+)$/, '$1controller$2'));

                var configUrl = me.getActionUrl('config'),
                    isJsonp = utils.isCrossDomainUrl(configUrl);

                /* 發出ajax請求 */
                me._serverConfigLoaded = false;

                configUrl && UE.ajax.request(configUrl,{
                    'method': 'GET',
                    'dataType': isJsonp ? 'jsonp':'',
                    'async': true,
                    'onsuccess':function(r){
                        try {
                            var config = isJsonp ? r:eval("("+r.responseText+")");
                            utils.extend(me.options, config);
                            me.fireEvent('serverConfigLoaded');
                            me._serverConfigLoaded = true;
                        } catch (e) {
                            showErrorMsg(me.getLang('loadconfigFormatError'));
                        }
                    },
                    'onerror':function(){
                        showErrorMsg(me.getLang('loadconfigHttpError'));
                    }
                });
            } catch(e){
                showErrorMsg(me.getLang('loadconfigError'));
            }
        });

        function showErrorMsg(msg) {
            console && console.error(msg);
            //me.fireEvent('showMessage', {
            //    'title': msg,
            //    'type': 'error'
            //});
        }
    };

    UE.Editor.prototype.isServerConfigLoaded = function(){
        var me = this;
        return me._serverConfigLoaded || false;
    };

    UE.Editor.prototype.afterConfigReady = function(handler){
        if (!handler || !utils.isFunction(handler)) return;
        var me = this;
        var readyHandler = function(){
            handler.apply(me, arguments);
            me.removeListener('serverConfigLoaded', readyHandler);
        };

        if (me.isServerConfigLoaded()) {
            handler.call(me, 'serverConfigLoaded');
        } else {
            me.addListener('serverConfigLoaded', readyHandler);
        }
    };

})();


// core/ajax.js
/**
 * @file
 * @module UE.ajax
 * @since 1.2.6.1
 */

/**
 * 提供對ajax請求的支援
 * @module UE.ajax
 */
UE.ajax = function() {

    //建立一個ajaxRequest物件
    var fnStr = 'XMLHttpRequest()';
    try {
        new ActiveXObject("Msxml2.XMLHTTP");
        fnStr = 'ActiveXObject(\'Msxml2.XMLHTTP\')';
    } catch (e) {
        try {
            new ActiveXObject("Microsoft.XMLHTTP");
            fnStr = 'ActiveXObject(\'Microsoft.XMLHTTP\')'
        } catch (e) {
        }
    }
    var creatAjaxRequest = new Function('return new ' + fnStr);


    /**
     * 將json引數轉化成適合ajax提交的引數列表
     * @param json
     */
    function json2str(json) {
        var strArr = [];
        for (var i in json) {
            //忽略預設的幾個引數
            if(i=="method" || i=="timeout" || i=="async" || i=="dataType" || i=="callback") continue;
            //忽略控制
            if(json[i] == undefined || json[i] == null) continue;
            //傳遞過來的物件和函式不在提交之列
            if (!((typeof json[i]).toLowerCase() == "function" || (typeof json[i]).toLowerCase() == "object")) {
                strArr.push( encodeURIComponent(i) + "="+encodeURIComponent(json[i]) );
            } else if (utils.isArray(json[i])) {
            //支援傳陣列內容
                for(var j = 0; j < json[i].length; j++) {
                    strArr.push( encodeURIComponent(i) + "[]="+encodeURIComponent(json[i][j]) );
                }
            }
        }
        return strArr.join("&");
    }

    function doAjax(url, ajaxOptions) {
        var xhr = creatAjaxRequest(),
        //是否超時
            timeIsOut = false,
        //預設引數
            defaultAjaxOptions = {
                method:"POST",
                timeout:5000,
                async:true,
                data:{},//需要傳遞物件的話只能覆蓋
                onsuccess:function() {
                },
                onerror:function() {
                }
            };

        if (typeof url === "object") {
            ajaxOptions = url;
            url = ajaxOptions.url;
        }
        if (!xhr || !url) return;
        var ajaxOpts = ajaxOptions ? utils.extend(defaultAjaxOptions,ajaxOptions) : defaultAjaxOptions;

        var submitStr = json2str(ajaxOpts);  // { name:"Jim",city:"Beijing" } --> "name=Jim&city=Beijing"
        //如果使用者直接通過data引數傳遞json物件過來，則也要將此json物件轉化為字串
        if (!utils.isEmptyObject(ajaxOpts.data)){
            submitStr += (submitStr? "&":"") + json2str(ajaxOpts.data);
        }
        //超時檢測
        var timerID = setTimeout(function() {
            if (xhr.readyState != 4) {
                timeIsOut = true;
                xhr.abort();
                clearTimeout(timerID);
            }
        }, ajaxOpts.timeout);

        var method = ajaxOpts.method.toUpperCase();
        var str = url + (url.indexOf("?")==-1?"?":"&") + (method=="POST"?"":submitStr+ "&noCache=" + +new Date);
        xhr.open(method, str, ajaxOpts.async);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (!timeIsOut && xhr.status == 200) {
                    ajaxOpts.onsuccess(xhr);
                } else {
                    ajaxOpts.onerror(xhr);
                }
            }
        };
        if (method == "POST") {
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send(submitStr);
        } else {
            xhr.send(null);
        }
    }

    function doJsonp(url, opts) {

        var successhandler = opts.onsuccess || function(){},
            scr = document.createElement('SCRIPT'),
            options = opts || {},
            charset = options['charset'],
            callbackField = options['jsonp'] || 'callback',
            callbackFnName,
            timeOut = options['timeOut'] || 0,
            timer,
            reg = new RegExp('(\\?|&)' + callbackField + '=([^&]*)'),
            matches;

        if (utils.isFunction(successhandler)) {
            callbackFnName = 'bd__editor__' + Math.floor(Math.random() * 2147483648).toString(36);
            window[callbackFnName] = getCallBack(0);
        } else if(utils.isString(successhandler)){
            callbackFnName = successhandler;
        } else {
            if (matches = reg.exec(url)) {
                callbackFnName = matches[2];
            }
        }

        url = url.replace(reg, '\x241' + callbackField + '=' + callbackFnName);

        if (url.search(reg) < 0) {
            url += (url.indexOf('?') < 0 ? '?' : '&') + callbackField + '=' + callbackFnName;
        }

        var queryStr = json2str(opts);  // { name:"Jim",city:"Beijing" } --> "name=Jim&city=Beijing"
        //如果使用者直接通過data引數傳遞json物件過來，則也要將此json物件轉化為字串
        if (!utils.isEmptyObject(opts.data)){
            queryStr += (queryStr? "&":"") + json2str(opts.data);
        }
        if (queryStr) {
            url = url.replace(/\?/, '?' + queryStr + '&');
        }

        scr.onerror = getCallBack(1);
        if( timeOut ){
            timer = setTimeout(getCallBack(1), timeOut);
        }
        createScriptTag(scr, url, charset);

        function createScriptTag(scr, url, charset) {
            scr.setAttribute('type', 'text/javascript');
            scr.setAttribute('defer', 'defer');
            charset && scr.setAttribute('charset', charset);
            scr.setAttribute('src', url);
            document.getElementsByTagName('head')[0].appendChild(scr);
        }

        function getCallBack(onTimeOut){
            return function(){
                try {
                    if(onTimeOut){
                        options.onerror && options.onerror();
                    }else{
                        try{
                            clearTimeout(timer);
                            successhandler.apply(window, arguments);
                        } catch (e){}
                    }
                } catch (exception) {
                    options.onerror && options.onerror.call(window, exception);
                } finally {
                    options.oncomplete && options.oncomplete.apply(window, arguments);
                    scr.parentNode && scr.parentNode.removeChild(scr);
                    window[callbackFnName] = null;
                    try {
                        delete window[callbackFnName];
                    }catch(e){}
                }
            }
        }
    }

    return {
        /**
         * 根據給定的引數項，向指定的url發起一個ajax請求。 ajax請求完成後，會根據請求結果呼叫相應回撥： 如果請求
         * 成功， 則呼叫onsuccess回撥， 失敗則呼叫 onerror 回撥
         * @method request
         * @param { URLString } url ajax請求的url地址
         * @param { Object } ajaxOptions ajax請求選項的鍵值對，支援的選項如下：
         * @example
         * ```javascript
         * //向sayhello.php發起一個非同步的Ajax GET請求, 請求超時時間為10s， 請求完成後執行相應的回撥。
         * UE.ajax.requeset( 'sayhello.php', {
         *
         *     //請求方法。可選值： 'GET', 'POST'，預設值是'POST'
         *     method: 'GET',
         *
         *     //超時時間。 預設為5000， 單位是ms
         *     timeout: 10000,
         *
         *     //是否是非同步請求。 true為非同步請求， false為同步請求
         *     async: true,
         *
         *     //請求攜帶的資料。如果請求為GET請求， data會經過stringify後附加到請求url之後。
         *     data: {
         *         name: 'ueditor'
         *     },
         *
         *     //請求成功後的回撥， 該回調接受當前的XMLHttpRequest物件作為引數。
         *     onsuccess: function ( xhr ) {
         *         console.log( xhr.responseText );
         *     },
         *
         *     //請求失敗或者超時後的回撥。
         *     onerror: function ( xhr ) {
         *          alert( 'Ajax請求失敗' );
         *     }
         *
         * } );
         * ```
         */

        /**
         * 根據給定的引數項發起一個ajax請求， 引數項裡必須包含一個url地址。 ajax請求完成後，會根據請求結果呼叫相應回撥： 如果請求
         * 成功， 則呼叫onsuccess回撥， 失敗則呼叫 onerror 回撥。
         * @method request
         * @warning 如果在引數項裡未提供一個key為“url”的地址值，則該請求將直接退出。
         * @param { Object } ajaxOptions ajax請求選項的鍵值對，支援的選項如下：
         * @example
         * ```javascript
         *
         * //向sayhello.php發起一個非同步的Ajax POST請求, 請求超時時間為5s， 請求完成後不執行任何回撥。
         * UE.ajax.requeset( 'sayhello.php', {
         *
         *     //請求的地址， 該項是必須的。
         *     url: 'sayhello.php'
         *
         * } );
         * ```
         */
		request:function(url, opts) {
            if (opts && opts.dataType == 'jsonp') {
                doJsonp(url, opts);
            } else {
                doAjax(url, opts);
            }
		},
        getJSONP:function(url, data, fn) {
            var opts = {
                'data': data,
                'oncomplete': fn
            };
            doJsonp(url, opts);
		}
	};


}();


// core/filterword.js
/**
 * UE過濾word的靜態方法
 * @file
 */

/**
 * UEditor公用空間，UEditor所有的功能都掛載在該空間下
 * @module UE
 */


/**
 * 根據傳入html字串過濾word
 * @module UE
 * @since 1.2.6.1
 * @method filterWord
 * @param { String } html html字串
 * @return { String } 已過濾後的結果字串
 * @example
 * ```javascript
 * UE.filterWord(html);
 * ```
 */
var filterWord = UE.filterWord = function () {

    //是否是word過來的內容
    function isWordDocument( str ) {
        return /(class="?Mso|style="[^"]*\bmso\-|w:WordDocument|<(v|o):|lang=)/ig.test( str );
    }
    //去掉小數
    function transUnit( v ) {
        v = v.replace( /[\d.]+\w+/g, function ( m ) {
            return utils.transUnitToPx(m);
        } );
        return v;
    }

    function filterPasteWord( str ) {
        return str.replace(/[\t\r\n]+/g,' ')
                .replace( /<!--[\s\S]*?-->/ig, "" )
                //轉換圖片
                .replace(/<v:shape [^>]*>[\s\S]*?.<\/v:shape>/gi,function(str){
                    //opera能自己解析出image所這裡直接返回空
                    if(browser.opera){
                        return '';
                    }
                    try{
                        //有可能是bitmap佔為圖，無用，直接過濾掉，主要體現在貼上excel表格中
                        if(/Bitmap/i.test(str)){
                            return '';
                        }
                        var width = str.match(/width:([ \d.]*p[tx])/i)[1],
                            height = str.match(/height:([ \d.]*p[tx])/i)[1],
                            src =  str.match(/src=\s*"([^"]*)"/i)[1];
                        return '<img width="'+ transUnit(width) +'" height="'+transUnit(height) +'" src="' + src + '" />';
                    } catch(e){
                        return '';
                    }
                })
                //針對wps新增的多餘標籤處理
                .replace(/<\/?div[^>]*>/g,'')
                //去掉多餘的屬性
                .replace( /v:\w+=(["']?)[^'"]+\1/g, '' )
                .replace( /<(!|script[^>]*>.*?<\/script(?=[>\s])|\/?(\?xml(:\w+)?|xml|meta|link|style|\w+:\w+)(?=[\s\/>]))[^>]*>/gi, "" )
                .replace( /<p [^>]*class="?MsoHeading"?[^>]*>(.*?)<\/p>/gi, "<p><strong>$1</strong></p>" )
                //去掉多餘的屬性
                .replace( /\s+(class|lang|align)\s*=\s*(['"]?)([\w-]+)\2/ig, function(str,name,marks,val){
                    //保留list的標示
                    return name == 'class' && val == 'MsoListParagraph' ? str : ''
                })
                //清除多餘的font/span不能匹配&nbsp;有可能是空格
                .replace( /<(font|span)[^>]*>(\s*)<\/\1>/gi, function(a,b,c){
                    return c.replace(/[\t\r\n ]+/g,' ')
                })
                //處理style的問題
                .replace( /(<[a-z][^>]*)\sstyle=(["'])([^\2]*?)\2/gi, function( str, tag, tmp, style ) {
                    var n = [],
                        s = style.replace( /^\s+|\s+$/, '' )
                            .replace(/&#39;/g,'\'')
                            .replace( /&quot;/gi, "'" )
                            .replace(/[\d.]+(cm|pt)/g,function(str){
                                return utils.transUnitToPx(str)
                            })
                            .split( /;\s*/g );

                    for ( var i = 0,v; v = s[i];i++ ) {

                        var name, value,
                            parts = v.split( ":" );

                        if ( parts.length == 2 ) {
                            name = parts[0].toLowerCase();
                            value = parts[1].toLowerCase();
                            if(/^(background)\w*/.test(name) && value.replace(/(initial|\s)/g,'').length == 0
                                ||
                                /^(margin)\w*/.test(name) && /^0\w+$/.test(value)
                            ){
                                continue;
                            }

                            switch ( name ) {
                                case "mso-padding-alt":
                                case "mso-padding-top-alt":
                                case "mso-padding-right-alt":
                                case "mso-padding-bottom-alt":
                                case "mso-padding-left-alt":
                                case "mso-margin-alt":
                                case "mso-margin-top-alt":
                                case "mso-margin-right-alt":
                                case "mso-margin-bottom-alt":
                                case "mso-margin-left-alt":
                                //ie下會出現擠到一起的情況
                               //case "mso-table-layout-alt":
                                case "mso-height":
                                case "mso-width":
                                case "mso-vertical-align-alt":
                                    //trace:1819 ff下會解析出padding在table上
                                    if(!/<table/.test(tag))
                                        n[i] = name.replace( /^mso-|-alt$/g, "" ) + ":" + transUnit( value );
                                    continue;
                                case "horiz-align":
                                    n[i] = "text-align:" + value;
                                    continue;

                                case "vert-align":
                                    n[i] = "vertical-align:" + value;
                                    continue;

                                case "font-color":
                                case "mso-foreground":
                                    n[i] = "color:" + value;
                                    continue;

                                case "mso-background":
                                case "mso-highlight":
                                    n[i] = "background:" + value;
                                    continue;

                                case "mso-default-height":
                                    n[i] = "min-height:" + transUnit( value );
                                    continue;

                                case "mso-default-width":
                                    n[i] = "min-width:" + transUnit( value );
                                    continue;

                                case "mso-padding-between-alt":
                                    n[i] = "border-collapse:separate;border-spacing:" + transUnit( value );
                                    continue;

                                case "text-line-through":
                                    if ( (value == "single") || (value == "double") ) {
                                        n[i] = "text-decoration:line-through";
                                    }
                                    continue;
                                case "mso-zero-height":
                                    if ( value == "yes" ) {
                                        n[i] = "display:none";
                                    }
                                    continue;
//                                case 'background':
//                                    break;
                                case 'margin':
                                    if ( !/[1-9]/.test( value ) ) {
                                        continue;
                                    }

                            }

                            if ( /^(mso|column|font-emph|lang|layout|line-break|list-image|nav|panose|punct|row|ruby|sep|size|src|tab-|table-border|text-(?:decor|trans)|top-bar|version|vnd|word-break)/.test( name )
                                ||
                                /text\-indent|padding|margin/.test(name) && /\-[\d.]+/.test(value)
                            ) {
                                continue;
                            }

                            n[i] = name + ":" + parts[1];
                        }
                    }
                    return tag + (n.length ? ' style="' + n.join( ';').replace(/;{2,}/g,';') + '"' : '');
                })


    }

    return function ( html ) {
        return (isWordDocument( html ) ? filterPasteWord( html ) : html);
    };
}();

// core/node.js
/**
 * 編輯器模擬的節點類
 * @file
 * @module UE
 * @class uNode
 * @since 1.2.6.1
 */

/**
 * UEditor公用空間，UEditor所有的功能都掛載在該空間下
 * @unfile
 * @module UE
 */

(function () {

    /**
     * 編輯器模擬的節點類
     * @unfile
     * @module UE
     * @class uNode
     */

    /**
     * 通過一個鍵值對，建立一個uNode物件
     * @constructor
     * @param { Object } attr 傳入要建立的uNode的初始屬性
     * @example
     * ```javascript
     * var node = new uNode({
     *     type:'element',
     *     tagName:'span',
     *     attrs:{style:'font-size:14px;'}
     * }
     * ```
     */
    var uNode = UE.uNode = function (obj) {
        this.type = obj.type;
        this.data = obj.data;
        this.tagName = obj.tagName;
        this.parentNode = obj.parentNode;
        this.attrs = obj.attrs || {};
        this.children = obj.children;
    };

    var notTransAttrs = {
        'href':1,
        'src':1,
        '_src':1,
        '_href':1,
        'cdata_data':1
    };

    var notTransTagName = {
        style:1,
        script:1
    };

    var indentChar = '    ',
        breakChar = '\n';

    function insertLine(arr, current, begin) {
        arr.push(breakChar);
        return current + (begin ? 1 : -1);
    }

    function insertIndent(arr, current) {
        //插入縮排
        for (var i = 0; i < current; i++) {
            arr.push(indentChar);
        }
    }

    //建立uNode的靜態方法
    //支援標籤和html
    uNode.createElement = function (html) {
        if (/[<>]/.test(html)) {
            return UE.htmlparser(html).children[0]
        } else {
            return new uNode({
                type:'element',
                children:[],
                tagName:html
            })
        }
    };
    uNode.createText = function (data,noTrans) {
        return new UE.uNode({
            type:'text',
            'data':noTrans ? data : utils.unhtml(data || '')
        })
    };
    function nodeToHtml(node, arr, formatter, current) {
        switch (node.type) {
            case 'root':
                for (var i = 0, ci; ci = node.children[i++];) {
                    //插入新行
                    if (formatter && ci.type == 'element' && !dtd.$inlineWithA[ci.tagName] && i > 1) {
                        insertLine(arr, current, true);
                        insertIndent(arr, current)
                    }
                    nodeToHtml(ci, arr, formatter, current)
                }
                break;
            case 'text':
                isText(node, arr);
                break;
            case 'element':
                isElement(node, arr, formatter, current);
                break;
            case 'comment':
                isComment(node, arr, formatter);
        }
        return arr;
    }

    function isText(node, arr) {
        if(node.parentNode.tagName == 'pre'){
            //原始碼模式下輸入html標籤，不能做轉換處理，直接輸出
            arr.push(node.data)
        }else{
            arr.push(notTransTagName[node.parentNode.tagName] ? utils.html(node.data) : node.data.replace(/[ ]{2}/g,' &nbsp;'))
        }

    }

    function isElement(node, arr, formatter, current) {
        var attrhtml = '';
        if (node.attrs) {
            attrhtml = [];
            var attrs = node.attrs;
            for (var a in attrs) {
                //這裡就針對
                //<p>'<img src='http://nsclick.baidu.com/u.gif?&asdf=\"sdf&asdfasdfs;asdf'></p>
                //這裡邊的\"做轉換，要不用innerHTML直接被截斷了，屬性src
                //有可能做的不夠
                attrhtml.push(a + (attrs[a] !== undefined ? '="' + (notTransAttrs[a] ? utils.html(attrs[a]).replace(/["]/g, function (a) {
                   return '&quot;'
                }) : utils.unhtml(attrs[a])) + '"' : ''))
            }
            attrhtml = attrhtml.join(' ');
        }
        arr.push('<' + node.tagName +
            (attrhtml ? ' ' + attrhtml  : '') +
            (dtd.$empty[node.tagName] ? '\/' : '' ) + '>'
        );
        //插入新行
        if (formatter  &&  !dtd.$inlineWithA[node.tagName] && node.tagName != 'pre') {
            if(node.children && node.children.length){
                current = insertLine(arr, current, true);
                insertIndent(arr, current)
            }

        }
        if (node.children && node.children.length) {
            for (var i = 0, ci; ci = node.children[i++];) {
                if (formatter && ci.type == 'element' &&  !dtd.$inlineWithA[ci.tagName] && i > 1) {
                    insertLine(arr, current);
                    insertIndent(arr, current)
                }
                nodeToHtml(ci, arr, formatter, current)
            }
        }
        if (!dtd.$empty[node.tagName]) {
            if (formatter && !dtd.$inlineWithA[node.tagName]  && node.tagName != 'pre') {

                if(node.children && node.children.length){
                    current = insertLine(arr, current);
                    insertIndent(arr, current)
                }
            }
            arr.push('<\/' + node.tagName + '>');
        }

    }

    function isComment(node, arr) {
        arr.push('<!--' + node.data + '-->');
    }

    function getNodeById(root, id) {
        var node;
        if (root.type == 'element' && root.getAttr('id') == id) {
            return root;
        }
        if (root.children && root.children.length) {
            for (var i = 0, ci; ci = root.children[i++];) {
                if (node = getNodeById(ci, id)) {
                    return node;
                }
            }
        }
    }

    function getNodesByTagName(node, tagName, arr) {
        if (node.type == 'element' && node.tagName == tagName) {
            arr.push(node);
        }
        if (node.children && node.children.length) {
            for (var i = 0, ci; ci = node.children[i++];) {
                getNodesByTagName(ci, tagName, arr)
            }
        }
    }
    function nodeTraversal(root,fn){
        if(root.children && root.children.length){
            for(var i= 0,ci;ci=root.children[i];){
                nodeTraversal(ci,fn);
                //ci被替換的情況，這裡就不再走 fn了
                if(ci.parentNode ){
                    if(ci.children && ci.children.length){
                        fn(ci)
                    }
                    if(ci.parentNode) i++
                }
            }
        }else{
            fn(root)
        }

    }
    uNode.prototype = {

        /**
         * 當前節點物件，轉換成html文字
         * @method toHtml
         * @return { String } 返回轉換後的html字串
         * @example
         * ```javascript
         * node.toHtml();
         * ```
         */

        /**
         * 當前節點物件，轉換成html文字
         * @method toHtml
         * @param { Boolean } formatter 是否格式化返回值
         * @return { String } 返回轉換後的html字串
         * @example
         * ```javascript
         * node.toHtml( true );
         * ```
         */
        toHtml:function (formatter) {
            var arr = [];
            nodeToHtml(this, arr, formatter, 0);
            return arr.join('')
        },

        /**
         * 獲取節點的html內容
         * @method innerHTML
         * @warning 假如節點的type不是'element'，或節點的標籤名稱不在dtd列表裡，直接返回當前節點
         * @return { String } 返回節點的html內容
         * @example
         * ```javascript
         * var htmlstr = node.innerHTML();
         * ```
         */

        /**
         * 設定節點的html內容
         * @method innerHTML
         * @warning 假如節點的type不是'element'，或節點的標籤名稱不在dtd列表裡，直接返回當前節點
         * @param { String } htmlstr 傳入要設定的html內容
         * @return { UE.uNode } 返回節點本身
         * @example
         * ```javascript
         * node.innerHTML('<span>text</span>');
         * ```
         */
        innerHTML:function (htmlstr) {
            if (this.type != 'element' || dtd.$empty[this.tagName]) {
                return this;
            }
            if (utils.isString(htmlstr)) {
                if(this.children){
                    for (var i = 0, ci; ci = this.children[i++];) {
                        ci.parentNode = null;
                    }
                }
                this.children = [];
                var tmpRoot = UE.htmlparser(htmlstr);
                for (var i = 0, ci; ci = tmpRoot.children[i++];) {
                    this.children.push(ci);
                    ci.parentNode = this;
                }
                return this;
            } else {
                var tmpRoot = new UE.uNode({
                    type:'root',
                    children:this.children
                });
                return tmpRoot.toHtml();
            }
        },

        /**
         * 獲取節點的純文字內容
         * @method innerText
         * @warning 假如節點的type不是'element'，或節點的標籤名稱不在dtd列表裡，直接返回當前節點
         * @return { String } 返回節點的存文字內容
         * @example
         * ```javascript
         * var textStr = node.innerText();
         * ```
         */

        /**
         * 設定節點的純文字內容
         * @method innerText
         * @warning 假如節點的type不是'element'，或節點的標籤名稱不在dtd列表裡，直接返回當前節點
         * @param { String } textStr 傳入要設定的文字內容
         * @return { UE.uNode } 返回節點本身
         * @example
         * ```javascript
         * node.innerText('<span>text</span>');
         * ```
         */
        innerText:function (textStr,noTrans) {
            if (this.type != 'element' || dtd.$empty[this.tagName]) {
                return this;
            }
            if (textStr) {
                if(this.children){
                    for (var i = 0, ci; ci = this.children[i++];) {
                        ci.parentNode = null;
                    }
                }
                this.children = [];
                this.appendChild(uNode.createText(textStr,noTrans));
                return this;
            } else {
                return this.toHtml().replace(/<[^>]+>/g, '');
            }
        },

        /**
         * 獲取當前物件的data屬性
         * @method getData
         * @return { Object } 若節點的type值是elemenet，返回空字串，否則返回節點的data屬性
         * @example
         * ```javascript
         * node.getData();
         * ```
         */
        getData:function () {
            if (this.type == 'element')
                return '';
            return this.data
        },

        /**
         * 獲取當前節點下的第一個子節點
         * @method firstChild
         * @return { UE.uNode } 返回第一個子節點
         * @example
         * ```javascript
         * node.firstChild(); //返回第一個子節點
         * ```
         */
        firstChild:function () {
//            if (this.type != 'element' || dtd.$empty[this.tagName]) {
//                return this;
//            }
            return this.children ? this.children[0] : null;
        },

        /**
         * 獲取當前節點下的最後一個子節點
         * @method lastChild
         * @return { UE.uNode } 返回最後一個子節點
         * @example
         * ```javascript
         * node.lastChild(); //返回最後一個子節點
         * ```
         */
        lastChild:function () {
//            if (this.type != 'element' || dtd.$empty[this.tagName] ) {
//                return this;
//            }
            return this.children ? this.children[this.children.length - 1] : null;
        },

        /**
         * 獲取和當前節點有相同父親節點的前一個節點
         * @method previousSibling
         * @return { UE.uNode } 返回前一個節點
         * @example
         * ```javascript
         * node.children[2].previousSibling(); //返回子節點node.children[1]
         * ```
         */
        previousSibling : function(){
            var parent = this.parentNode;
            for (var i = 0, ci; ci = parent.children[i]; i++) {
                if (ci === this) {
                   return i == 0 ? null : parent.children[i-1];
                }
            }

        },

        /**
         * 獲取和當前節點有相同父親節點的後一個節點
         * @method nextSibling
         * @return { UE.uNode } 返回後一個節點,找不到返回null
         * @example
         * ```javascript
         * node.children[2].nextSibling(); //如果有，返回子節點node.children[3]
         * ```
         */
        nextSibling : function(){
            var parent = this.parentNode;
            for (var i = 0, ci; ci = parent.children[i++];) {
                if (ci === this) {
                    return parent.children[i];
                }
            }
        },

        /**
         * 用新的節點替換當前節點
         * @method replaceChild
         * @param { UE.uNode } target 要替換成該節點引數
         * @param { UE.uNode } source 要被替換掉的節點
         * @return { UE.uNode } 返回替換之後的節點物件
         * @example
         * ```javascript
         * node.replaceChild(newNode, childNode); //用newNode替換childNode,childNode是node的子節點
         * ```
         */
        replaceChild:function (target, source) {
            if (this.children) {
                if(target.parentNode){
                    target.parentNode.removeChild(target);
                }
                for (var i = 0, ci; ci = this.children[i]; i++) {
                    if (ci === source) {
                        this.children.splice(i, 1, target);
                        source.parentNode = null;
                        target.parentNode = this;
                        return target;
                    }
                }
            }
        },

        /**
         * 在節點的子節點列表最後位置插入一個節點
         * @method appendChild
         * @param { UE.uNode } node 要插入的節點
         * @return { UE.uNode } 返回剛插入的子節點
         * @example
         * ```javascript
         * node.appendChild( newNode ); //在node內插入子節點newNode
         * ```
         */
        appendChild:function (node) {
            if (this.type == 'root' || (this.type == 'element' && !dtd.$empty[this.tagName])) {
                if (!this.children) {
                    this.children = []
                }
                if(node.parentNode){
                    node.parentNode.removeChild(node);
                }
                for (var i = 0, ci; ci = this.children[i]; i++) {
                    if (ci === node) {
                        this.children.splice(i, 1);
                        break;
                    }
                }
                this.children.push(node);
                node.parentNode = this;
                return node;
            }


        },

        /**
         * 在傳入節點的前面插入一個節點
         * @method insertBefore
         * @param { UE.uNode } target 要插入的節點
         * @param { UE.uNode } source 在該引數節點前面插入
         * @return { UE.uNode } 返回剛插入的子節點
         * @example
         * ```javascript
         * node.parentNode.insertBefore(newNode, node); //在node節點後面插入newNode
         * ```
         */
        insertBefore:function (target, source) {
            if (this.children) {
                if(target.parentNode){
                    target.parentNode.removeChild(target);
                }
                for (var i = 0, ci; ci = this.children[i]; i++) {
                    if (ci === source) {
                        this.children.splice(i, 0, target);
                        target.parentNode = this;
                        return target;
                    }
                }

            }
        },

        /**
         * 在傳入節點的後面插入一個節點
         * @method insertAfter
         * @param { UE.uNode } target 要插入的節點
         * @param { UE.uNode } source 在該引數節點後面插入
         * @return { UE.uNode } 返回剛插入的子節點
         * @example
         * ```javascript
         * node.parentNode.insertAfter(newNode, node); //在node節點後面插入newNode
         * ```
         */
        insertAfter:function (target, source) {
            if (this.children) {
                if(target.parentNode){
                    target.parentNode.removeChild(target);
                }
                for (var i = 0, ci; ci = this.children[i]; i++) {
                    if (ci === source) {
                        this.children.splice(i + 1, 0, target);
                        target.parentNode = this;
                        return target;
                    }

                }
            }
        },

        /**
         * 從當前節點的子節點列表中，移除節點
         * @method removeChild
         * @param { UE.uNode } node 要移除的節點引用
         * @param { Boolean } keepChildren 是否保留移除節點的子節點，若傳入true，自動把移除節點的子節點插入到移除的位置
         * @return { * } 返回剛移除的子節點
         * @example
         * ```javascript
         * node.removeChild(childNode,true); //在node的子節點列表中移除child節點，並且吧child的子節點插入到移除的位置
         * ```
         */
        removeChild:function (node,keepChildren) {
            if (this.children) {
                for (var i = 0, ci; ci = this.children[i]; i++) {
                    if (ci === node) {
                        this.children.splice(i, 1);
                        ci.parentNode = null;
                        if(keepChildren && ci.children && ci.children.length){
                            for(var j= 0,cj;cj=ci.children[j];j++){
                                this.children.splice(i+j,0,cj);
                                cj.parentNode = this;

                            }
                        }
                        return ci;
                    }
                }
            }
        },

        /**
         * 獲取當前節點所代表的元素屬性，即獲取attrs物件下的屬性值
         * @method getAttr
         * @param { String } attrName 要獲取的屬性名稱
         * @return { * } 返回attrs物件下的屬性值
         * @example
         * ```javascript
         * node.getAttr('title');
         * ```
         */
        getAttr:function (attrName) {
            return this.attrs && this.attrs[attrName.toLowerCase()]
        },

        /**
         * 設定當前節點所代表的元素屬性，即設定attrs物件下的屬性值
         * @method setAttr
         * @param { String } attrName 要設定的屬性名稱
         * @param { * } attrVal 要設定的屬性值，型別視設定的屬性而定
         * @return { * } 返回attrs物件下的屬性值
         * @example
         * ```javascript
         * node.setAttr('title','標題');
         * ```
         */
        setAttr:function (attrName, attrVal) {
            if (!attrName) {
                delete this.attrs;
                return;
            }
            if(!this.attrs){
                this.attrs = {};
            }
            if (utils.isObject(attrName)) {
                for (var a in attrName) {
                    if (!attrName[a]) {
                        delete this.attrs[a]
                    } else {
                        this.attrs[a.toLowerCase()] = attrName[a];
                    }
                }
            } else {
                if (!attrVal) {
                    delete this.attrs[attrName]
                } else {
                    this.attrs[attrName.toLowerCase()] = attrVal;
                }

            }
        },

        /**
         * 獲取當前節點在父節點下的位置索引
         * @method getIndex
         * @return { Number } 返回索引數值，如果沒有父節點，返回-1
         * @example
         * ```javascript
         * node.getIndex();
         * ```
         */
        getIndex:function(){
            var parent = this.parentNode;
            for(var i= 0,ci;ci=parent.children[i];i++){
                if(ci === this){
                    return i;
                }
            }
            return -1;
        },

        /**
         * 在當前節點下，根據id查詢節點
         * @method getNodeById
         * @param { String } id 要查詢的id
         * @return { UE.uNode } 返回找到的節點
         * @example
         * ```javascript
         * node.getNodeById('textId');
         * ```
         */
        getNodeById:function (id) {
            var node;
            if (this.children && this.children.length) {
                for (var i = 0, ci; ci = this.children[i++];) {
                    if (node = getNodeById(ci, id)) {
                        return node;
                    }
                }
            }
        },

        /**
         * 在當前節點下，根據元素名稱查詢節點列表
         * @method getNodesByTagName
         * @param { String } tagNames 要查詢的元素名稱
         * @return { Array } 返回找到的節點列表
         * @example
         * ```javascript
         * node.getNodesByTagName('span');
         * ```
         */
        getNodesByTagName:function (tagNames) {
            tagNames = utils.trim(tagNames).replace(/[ ]{2,}/g, ' ').split(' ');
            var arr = [], me = this;
            utils.each(tagNames, function (tagName) {
                if (me.children && me.children.length) {
                    for (var i = 0, ci; ci = me.children[i++];) {
                        getNodesByTagName(ci, tagName, arr)
                    }
                }
            });
            return arr;
        },

        /**
         * 根據樣式名稱，獲取節點的樣式值
         * @method getStyle
         * @param { String } name 要獲取的樣式名稱
         * @return { String } 返回樣式值
         * @example
         * ```javascript
         * node.getStyle('font-size');
         * ```
         */
        getStyle:function (name) {
            var cssStyle = this.getAttr('style');
            if (!cssStyle) {
                return ''
            }
            var reg = new RegExp('(^|;)\\s*' + name + ':([^;]+)','i');
            var match = cssStyle.match(reg);
            if (match && match[0]) {
                return match[2]
            }
            return '';
        },

        /**
         * 給節點設定樣式
         * @method setStyle
         * @param { String } name 要設定的的樣式名稱
         * @param { String } val 要設定的的樣值
         * @example
         * ```javascript
         * node.setStyle('font-size', '12px');
         * ```
         */
        setStyle:function (name, val) {
            function exec(name, val) {
                var reg = new RegExp('(^|;)\\s*' + name + ':([^;]+;?)', 'gi');
                cssStyle = cssStyle.replace(reg, '$1');
                if (val) {
                    cssStyle = name + ':' + utils.unhtml(val) + ';' + cssStyle
                }

            }

            var cssStyle = this.getAttr('style');
            if (!cssStyle) {
                cssStyle = '';
            }
            if (utils.isObject(name)) {
                for (var a in name) {
                    exec(a, name[a])
                }
            } else {
                exec(name, val)
            }
            this.setAttr('style', utils.trim(cssStyle))
        },

        /**
         * 傳入一個函式，遞迴遍歷當前節點下的所有節點
         * @method traversal
         * @param { Function } fn 遍歷到節點的時，傳入節點作為引數，執行此函式
         * @example
         * ```javascript
         * traversal(node, function(){
         *     console.log(node.type);
         * });
         * ```
         */
        traversal:function(fn){
            if(this.children && this.children.length){
                nodeTraversal(this,fn);
            }
            return this;
        }
    }
})();


// core/htmlparser.js
/**
 * html字串轉換成uNode節點
 * @file
 * @module UE
 * @since 1.2.6.1
 */

/**
 * UEditor公用空間，UEditor所有的功能都掛載在該空間下
 * @unfile
 * @module UE
 */

/**
 * html字串轉換成uNode節點的靜態方法
 * @method htmlparser
 * @param { String } htmlstr 要轉換的html程式碼
 * @param { Boolean } ignoreBlank 若設定為true，轉換的時候忽略\n\r\t等空白字元
 * @return { uNode } 給定的html片段轉換形成的uNode物件
 * @example
 * ```javascript
 * var root = UE.htmlparser('<p><b>htmlparser</b></p>', true);
 * ```
 */

var htmlparser = UE.htmlparser = function (htmlstr,ignoreBlank) {
    //todo 原來的方式  [^"'<>\/] 有\/就不能配對上 <TD vAlign=top background=../AAA.JPG> 這樣的標籤了
    //先去掉了，加上的原因忘了，這裡先記錄
    var re_tag = /<(?:(?:\/([^>]+)>)|(?:!--([\S|\s]*?)-->)|(?:([^\s\/<>]+)\s*((?:(?:"[^"]*")|(?:'[^']*')|[^"'<>])*)\/?>))/g,
        re_attr = /([\w\-:.]+)(?:(?:\s*=\s*(?:(?:"([^"]*)")|(?:'([^']*)')|([^\s>]+)))|(?=\s|$))/g;

    //ie下取得的html可能會有\n存在，要去掉，在處理replace(/[\t\r\n]*/g,'');程式碼高量的\n不能去除
    var allowEmptyTags = {
        b:1,code:1,i:1,u:1,strike:1,s:1,tt:1,strong:1,q:1,samp:1,em:1,span:1,
        sub:1,img:1,sup:1,font:1,big:1,small:1,iframe:1,a:1,br:1,pre:1
    };
    htmlstr = htmlstr.replace(new RegExp(domUtils.fillChar, 'g'), '');
    if(!ignoreBlank){
        htmlstr = htmlstr.replace(new RegExp('[\\r\\t\\n'+(ignoreBlank?'':' ')+']*<\/?(\\w+)\\s*(?:[^>]*)>[\\r\\t\\n'+(ignoreBlank?'':' ')+']*','g'), function(a,b){
            //br暫時單獨處理
            if(b && allowEmptyTags[b.toLowerCase()]){
                return a.replace(/(^[\n\r]+)|([\n\r]+$)/g,'');
            }
            return a.replace(new RegExp('^[\\r\\n'+(ignoreBlank?'':' ')+']+'),'').replace(new RegExp('[\\r\\n'+(ignoreBlank?'':' ')+']+$'),'');
        });
    }

    var notTransAttrs = {
        'href':1,
        'src':1
    };

    var uNode = UE.uNode,
        needParentNode = {
            'td':'tr',
            'tr':['tbody','thead','tfoot'],
            'tbody':'table',
            'th':'tr',
            'thead':'table',
            'tfoot':'table',
            'caption':'table',
            'li':['ul', 'ol'],
            'dt':'dl',
            'dd':'dl',
            'option':'select'
        },
        needChild = {
            'ol':'li',
            'ul':'li'
        };

    function text(parent, data) {

        if(needChild[parent.tagName]){
            var tmpNode = uNode.createElement(needChild[parent.tagName]);
            parent.appendChild(tmpNode);
            tmpNode.appendChild(uNode.createText(data));
            parent = tmpNode;
        }else{

            parent.appendChild(uNode.createText(data));
        }
    }

    function element(parent, tagName, htmlattr) {
        var needParentTag;
        if (needParentTag = needParentNode[tagName]) {
            var tmpParent = parent,hasParent;
            while(tmpParent.type != 'root'){
                if(utils.isArray(needParentTag) ? utils.indexOf(needParentTag, tmpParent.tagName) != -1 : needParentTag == tmpParent.tagName){
                    parent = tmpParent;
                    hasParent = true;
                    break;
                }
                tmpParent = tmpParent.parentNode;
            }
            if(!hasParent){
                parent = element(parent, utils.isArray(needParentTag) ? needParentTag[0] : needParentTag)
            }
        }
        //按dtd處理巢狀
//        if(parent.type != 'root' && !dtd[parent.tagName][tagName])
//            parent = parent.parentNode;
        var elm = new uNode({
            parentNode:parent,
            type:'element',
            tagName:tagName.toLowerCase(),
            //是自閉合的處理一下
            children:dtd.$empty[tagName] ? null : []
        });
        //如果屬性存在，處理屬性
        if (htmlattr) {
            var attrs = {}, match;
            while (match = re_attr.exec(htmlattr)) {
                attrs[match[1].toLowerCase()] = notTransAttrs[match[1].toLowerCase()] ? (match[2] || match[3] || match[4]) : utils.unhtml(match[2] || match[3] || match[4])
            }
            elm.attrs = attrs;
        }
        //trace:3970
//        //如果parent下不能放elm
//        if(dtd.$inline[parent.tagName] && dtd.$block[elm.tagName] && !dtd[parent.tagName][elm.tagName]){
//            parent = parent.parentNode;
//            elm.parentNode = parent;
//        }
        parent.children.push(elm);
        //如果是自閉合節點返回父親節點
        return  dtd.$empty[tagName] ? parent : elm
    }

    function comment(parent, data) {
        parent.children.push(new uNode({
            type:'comment',
            data:data,
            parentNode:parent
        }));
    }

    var match, currentIndex = 0, nextIndex = 0;
    //設定根節點
    var root = new uNode({
        type:'root',
        children:[]
    });
    var currentParent = root;

    while (match = re_tag.exec(htmlstr)) {
        currentIndex = match.index;
        try{
            if (currentIndex > nextIndex) {
                //text node
                text(currentParent, htmlstr.slice(nextIndex, currentIndex));
            }
            if (match[3]) {

                if(dtd.$cdata[currentParent.tagName]){
                    text(currentParent, match[0]);
                }else{
                    //start tag
                    currentParent = element(currentParent, match[3].toLowerCase(), match[4]);
                }


            } else if (match[1]) {
                if(currentParent.type != 'root'){
                    if(dtd.$cdata[currentParent.tagName] && !dtd.$cdata[match[1]]){
                        text(currentParent, match[0]);
                    }else{
                        var tmpParent = currentParent;
                        while(currentParent.type == 'element' && currentParent.tagName != match[1].toLowerCase()){
                            currentParent = currentParent.parentNode;
                            if(currentParent.type == 'root'){
                                currentParent = tmpParent;
                                throw 'break'
                            }
                        }
                        //end tag
                        currentParent = currentParent.parentNode;
                    }

                }

            } else if (match[2]) {
                //comment
                comment(currentParent, match[2])
            }
        }catch(e){}

        nextIndex = re_tag.lastIndex;

    }
    //如果結束是文字，就有可能丟掉，所以這裡手動判斷一下
    //例如 <li>sdfsdfsdf<li>sdfsdfsdfsdf
    if (nextIndex < htmlstr.length) {
        text(currentParent, htmlstr.slice(nextIndex));
    }
    return root;
};

// core/filternode.js
/**
 * UE過濾節點的靜態方法
 * @file
 */

/**
 * UEditor公用空間，UEditor所有的功能都掛載在該空間下
 * @module UE
 */


/**
 * 根據傳入節點和過濾規則過濾相應節點
 * @module UE
 * @since 1.2.6.1
 * @method filterNode
 * @param { Object } root 指定root節點
 * @param { Object } rules 過濾規則json物件
 * @example
 * ```javascript
 * UE.filterNode(root,editor.options.filterRules);
 * ```
 */
var filterNode = UE.filterNode = function () {
    function filterNode(node,rules){
        switch (node.type) {
            case 'text':
                break;
            case 'element':
                var val;
                if(val = rules[node.tagName]){
                   if(val === '-'){
                       node.parentNode.removeChild(node)
                   }else if(utils.isFunction(val)){
                       var parentNode = node.parentNode,
                           index = node.getIndex();
                       val(node);
                       if(node.parentNode){
                           if(node.children){
                               for(var i = 0,ci;ci=node.children[i];){
                                   filterNode(ci,rules);
                                   if(ci.parentNode){
                                       i++;
                                   }
                               }
                           }
                       }else{
                           for(var i = index,ci;ci=parentNode.children[i];){
                               filterNode(ci,rules);
                               if(ci.parentNode){
                                   i++;
                               }
                           }
                       }


                   }else{
                       var attrs = val['$'];
                       if(attrs && node.attrs){
                           var tmpAttrs = {},tmpVal;
                           for(var a in attrs){
                               tmpVal = node.getAttr(a);
                               //todo 只先對style單獨處理
                               if(a == 'style' && utils.isArray(attrs[a])){
                                   var tmpCssStyle = [];
                                   utils.each(attrs[a],function(v){
                                       var tmp;
                                       if(tmp = node.getStyle(v)){
                                           tmpCssStyle.push(v + ':' + tmp);
                                       }
                                   });
                                   tmpVal = tmpCssStyle.join(';')
                               }
                               if(tmpVal){
                                   tmpAttrs[a] = tmpVal;
                               }

                           }
                           node.attrs = tmpAttrs;
                       }
                       if(node.children){
                           for(var i = 0,ci;ci=node.children[i];){
                               filterNode(ci,rules);
                               if(ci.parentNode){
                                   i++;
                               }
                           }
                       }
                   }
                }else{
                    //如果不在名單里扣出子節點並刪除該節點,cdata除外
                    if(dtd.$cdata[node.tagName]){
                        node.parentNode.removeChild(node)
                    }else{
                        var parentNode = node.parentNode,
                            index = node.getIndex();
                        node.parentNode.removeChild(node,true);
                        for(var i = index,ci;ci=parentNode.children[i];){
                            filterNode(ci,rules);
                            if(ci.parentNode){
                                i++;
                            }
                        }
                    }
                }
                break;
            case 'comment':
                node.parentNode.removeChild(node)
        }

    }
    return function(root,rules){
        if(utils.isEmptyObject(rules)){
            return root;
        }
        var val;
        if(val = rules['-']){
            utils.each(val.split(' '),function(k){
                rules[k] = '-'
            })
        }
        for(var i= 0,ci;ci=root.children[i];){
            filterNode(ci,rules);
            if(ci.parentNode){
               i++;
            }
        }
        return root;
    }
}();

// core/plugin.js
/**
 * Created with JetBrains PhpStorm.
 * User: campaign
 * Date: 10/8/13
 * Time: 6:15 PM
 * To change this template use File | Settings | File Templates.
 */
UE.plugin = function(){
    var _plugins = {};
    return {
        register : function(pluginName,fn,oldOptionName,afterDisabled){
            if(oldOptionName && utils.isFunction(oldOptionName)){
                afterDisabled = oldOptionName;
                oldOptionName = null
            }
            _plugins[pluginName] = {
                optionName : oldOptionName || pluginName,
                execFn : fn,
                //當外掛被禁用時執行
                afterDisabled : afterDisabled
            }
        },
        load : function(editor){
            utils.each(_plugins,function(plugin){
                var _export = plugin.execFn.call(editor);
                if(editor.options[plugin.optionName] !== false){
                    if(_export){
                        //後邊需要再做擴充套件
                        utils.each(_export,function(v,k){
                            switch(k.toLowerCase()){
                                case 'shortcutkey':
                                    editor.addshortcutkey(v);
                                    break;
                                case 'bindevents':
                                    utils.each(v,function(fn,eventName){
                                        editor.addListener(eventName,fn);
                                    });
                                    break;
                                case 'bindmultievents':
                                    utils.each(utils.isArray(v) ? v:[v],function(event){
                                        var types = utils.trim(event.type).split(/\s+/);
                                        utils.each(types,function(eventName){
                                            editor.addListener(eventName, event.handler);
                                        });
                                    });
                                    break;
                                case 'commands':
                                    utils.each(v,function(execFn,execName){
                                        editor.commands[execName] = execFn
                                    });
                                    break;
                                case 'outputrule':
                                    editor.addOutputRule(v);
                                    break;
                                case 'inputrule':
                                    editor.addInputRule(v);
                                    break;
                                case 'defaultoptions':
                                    editor.setOpt(v)
                            }
                        })
                    }

                }else if(plugin.afterDisabled){
                    plugin.afterDisabled.call(editor)
                }

            });
            //向下相容
            utils.each(UE.plugins,function(plugin){
                plugin.call(editor);
            });
        },
        run : function(pluginName,editor){
            var plugin = _plugins[pluginName];
            if(plugin){
                plugin.exeFn.call(editor)
            }
        }
    }
}();

// core/keymap.js
var keymap = UE.keymap  = {
    'Backspace' : 8,
    'Tab' : 9,
    'Enter' : 13,

    'Shift':16,
    'Control':17,
    'Alt':18,
    'CapsLock':20,

    'Esc':27,

    'Spacebar':32,

    'PageUp':33,
    'PageDown':34,
    'End':35,
    'Home':36,

    'Left':37,
    'Up':38,
    'Right':39,
    'Down':40,

    'Insert':45,

    'Del':46,

    'NumLock':144,

    'Cmd':91,

    '=':187,
    '-':189,

    "b":66,
    'i':73,
    //回退
    'z':90,
    'y':89,
    //貼上
    'v' : 86,
    'x' : 88,

    's' : 83,

    'n' : 78
};

// core/localstorage.js
//儲存媒介封裝
var LocalStorage = UE.LocalStorage = (function () {

    var storage = window.localStorage || getUserData() || null,
        LOCAL_FILE = 'localStorage';

    return {

        saveLocalData: function (key, data) {

            if (storage && data) {
                storage.setItem(key, data);
                return true;
            }

            return false;

        },

        getLocalData: function (key) {

            if (storage) {
                return storage.getItem(key);
            }

            return null;

        },

        removeItem: function (key) {

            storage && storage.removeItem(key);

        }

    };

    function getUserData() {

        var container = document.createElement("div");
        container.style.display = "none";

        if (!container.addBehavior) {
            return null;
        }

        container.addBehavior("#default#userdata");

        return {

            getItem: function (key) {

                var result = null;

                try {
                    document.body.appendChild(container);
                    container.load(LOCAL_FILE);
                    result = container.getAttribute(key);
                    document.body.removeChild(container);
                } catch (e) {
                }

                return result;

            },

            setItem: function (key, value) {

                document.body.appendChild(container);
                container.setAttribute(key, value);
                container.save(LOCAL_FILE);
                document.body.removeChild(container);

            },

            //// 暫時沒有用到
            //clear: function () {
            //
            //    var expiresTime = new Date();
            //    expiresTime.setFullYear(expiresTime.getFullYear() - 1);
            //    document.body.appendChild(container);
            //    container.expires = expiresTime.toUTCString();
            //    container.save(LOCAL_FILE);
            //    document.body.removeChild(container);
            //
            //},

            removeItem: function (key) {

                document.body.appendChild(container);
                container.removeAttribute(key);
                container.save(LOCAL_FILE);
                document.body.removeChild(container);

            }

        };

    }

})();

(function () {

    var ROOTKEY = 'ueditor_preference';

    UE.Editor.prototype.setPreferences = function(key,value){
        var obj = {};
        if (utils.isString(key)) {
            obj[ key ] = value;
        } else {
            obj = key;
        }
        var data = LocalStorage.getLocalData(ROOTKEY);
        if (data && (data = utils.str2json(data))) {
            utils.extend(data, obj);
        } else {
            data = obj;
        }
        data && LocalStorage.saveLocalData(ROOTKEY, utils.json2str(data));
    };

    UE.Editor.prototype.getPreferences = function(key){
        var data = LocalStorage.getLocalData(ROOTKEY);
        if (data && (data = utils.str2json(data))) {
            return key ? data[key] : data
        }
        return null;
    };

    UE.Editor.prototype.removePreferences = function (key) {
        var data = LocalStorage.getLocalData(ROOTKEY);
        if (data && (data = utils.str2json(data))) {
            data[key] = undefined;
            delete data[key]
        }
        data && LocalStorage.saveLocalData(ROOTKEY, utils.json2str(data));
    };

})();


// plugins/defaultfilter.js
///import core
///plugin 編輯器預設的過濾轉換機制

UE.plugins['defaultfilter'] = function () {
    var me = this;
    me.setOpt({
        'allowDivTransToP':true,
        'disabledTableInTable':true
    });
    //預設的過濾處理
    //進入編輯器的內容處理
    me.addInputRule(function (root) {
        var allowDivTransToP = this.options.allowDivTransToP;
        var val;
        function tdParent(node){
            while(node && node.type == 'element'){
                if(node.tagName == 'td'){
                    return true;
                }
                node = node.parentNode;
            }
            return false;
        }
        //進行預設的處理
        root.traversal(function (node) {
            if (node.type == 'element') {
                if (!dtd.$cdata[node.tagName] && me.options.autoClearEmptyNode && dtd.$inline[node.tagName] && !dtd.$empty[node.tagName] && (!node.attrs || utils.isEmptyObject(node.attrs))) {
                    if (!node.firstChild()) node.parentNode.removeChild(node);
                    else if (node.tagName == 'span' && (!node.attrs || utils.isEmptyObject(node.attrs))) {
                        node.parentNode.removeChild(node, true)
                    }
                    return;
                }
                switch (node.tagName) {
                    case 'style':
                    case 'script':
                        node.setAttr({
                            cdata_tag: node.tagName,
                            cdata_data: (node.innerHTML() || ''),
                            '_ue_custom_node_':'true'
                        });
                        node.tagName = 'div';
                        node.innerHTML('');
                        break;
                    case 'a':
                        if (val = node.getAttr('href')) {
                            node.setAttr('_href', val)
                        }
                        break;
                    case 'img':
                        node.setAttr('_src', node.getAttr('src'));
                        break;
                    case 'span':
                        if (browser.webkit && (val = node.getStyle('white-space'))) {
                            if (/nowrap|normal/.test(val)) {
                                node.setStyle('white-space', '');
                                if (me.options.autoClearEmptyNode && utils.isEmptyObject(node.attrs)) {
                                    node.parentNode.removeChild(node, true)
                                }
                            }
                        }
                        val = node.getAttr('id');
                        if(val && /^_baidu_bookmark_/i.test(val)){
                            node.parentNode.removeChild(node)
                        }
                        break;
                    case 'p':
                        if (val = node.getAttr('align')) {
                            node.setAttr('align');
                            node.setStyle('text-align', val)
                        }
                        //trace:3431
//                        var cssStyle = node.getAttr('style');
//                        if (cssStyle) {
//                            cssStyle = cssStyle.replace(/(margin|padding)[^;]+/g, '');
//                            node.setAttr('style', cssStyle)
//
//                        }
                        //p標籤不允許巢狀
                        utils.each(node.children,function(n){
                            if(n.type == 'element' && n.tagName == 'p'){
                                var next = n.nextSibling();
                                node.parentNode.insertAfter(n,node);
                                var last = n;
                                while(next){
                                    var tmp = next.nextSibling();
                                    node.parentNode.insertAfter(next,last);
                                    last = next;
                                    next = tmp;
                                }
                                return false;
                            }
                        });
                        if (!node.firstChild()) {
                            node.innerHTML(browser.ie ? '&nbsp;' : '<br/>')
                        }
                        break;
                    case 'div':
                        if(node.getAttr('cdata_tag')){
                            break;
                        }
                        //針對程式碼這裡不處理插入程式碼的div
                        val = node.getAttr('class');
                        if(val && /^line number\d+/.test(val)){
                            break;
                        }
                        if(!allowDivTransToP){
                            break;
                        }
                        var tmpNode, p = UE.uNode.createElement('p');
                        while (tmpNode = node.firstChild()) {
                            if (tmpNode.type == 'text' || !UE.dom.dtd.$block[tmpNode.tagName]) {
                                p.appendChild(tmpNode);
                            } else {
                                if (p.firstChild()) {
                                    node.parentNode.insertBefore(p, node);
                                    p = UE.uNode.createElement('p');
                                } else {
                                    node.parentNode.insertBefore(tmpNode, node);
                                }
                            }
                        }
                        if (p.firstChild()) {
                            node.parentNode.insertBefore(p, node);
                        }
                        node.parentNode.removeChild(node);
                        break;
                    case 'dl':
                        node.tagName = 'ul';
                        break;
                    case 'dt':
                    case 'dd':
                        node.tagName = 'li';
                        break;
                    case 'li':
                        var className = node.getAttr('class');
                        if (!className || !/list\-/.test(className)) {
                            node.setAttr()
                        }
                        var tmpNodes = node.getNodesByTagName('ol ul');
                        UE.utils.each(tmpNodes, function (n) {
                            node.parentNode.insertAfter(n, node);
                        });
                        break;
                    case 'td':
                    case 'th':
                    case 'caption':
                        if(!node.children || !node.children.length){
                            node.appendChild(browser.ie11below ? UE.uNode.createText(' ') : UE.uNode.createElement('br'))
                        }
                        break;
                    case 'table':
                        if(me.options.disabledTableInTable && tdParent(node)){
                            node.parentNode.insertBefore(UE.uNode.createText(node.innerText()),node);
                            node.parentNode.removeChild(node)
                        }
                }

            }
//            if(node.type == 'comment'){
//                node.parentNode.removeChild(node);
//            }
        })

    });

    //從編輯器出去的內容處理
    me.addOutputRule(function (root) {

        var val;
        root.traversal(function (node) {
            if (node.type == 'element') {

                if (me.options.autoClearEmptyNode && dtd.$inline[node.tagName] && !dtd.$empty[node.tagName] && (!node.attrs || utils.isEmptyObject(node.attrs))) {

                    if (!node.firstChild()) node.parentNode.removeChild(node);
                    else if (node.tagName == 'span' && (!node.attrs || utils.isEmptyObject(node.attrs))) {
                        node.parentNode.removeChild(node, true)
                    }
                    return;
                }
                switch (node.tagName) {
                    case 'div':
                        if (val = node.getAttr('cdata_tag')) {
                            node.tagName = val;
                            node.appendChild(UE.uNode.createText(node.getAttr('cdata_data')));
                            node.setAttr({cdata_tag: '', cdata_data: '','_ue_custom_node_':''});
                        }
                        break;
                    case 'a':
                        if (val = node.getAttr('_href')) {
                            node.setAttr({
                                'href': utils.html(val),
                                '_href': ''
                            })
                        }
                        break;
                        break;
                    case 'span':
                        val = node.getAttr('id');
                        if(val && /^_baidu_bookmark_/i.test(val)){
                            node.parentNode.removeChild(node)
                        }
                        break;
                    case 'img':
                        if (val = node.getAttr('_src')) {
                            node.setAttr({
                                'src': node.getAttr('_src'),
                                '_src': ''
                            })
                        }


                }
            }

        })


    });
};


// plugins/inserthtml.js
/**
 * 插入html字串外掛
 * @file
 * @since 1.2.6.1
 */

/**
 * 插入html程式碼
 * @command inserthtml
 * @method execCommand
 * @param { String } cmd 命令字串
 * @param { String } html 插入的html字串
 * @remaind 插入的標籤內容是在當前的選區位置上插入，如果當前是閉合狀態，那直接插入內容， 如果當前是選中狀態，將先清除當前選中內容後，再做插入
 * @warning 注意:該命令會對當前選區的位置，對插入的內容進行過濾轉換處理。 過濾的規則遵循html語意化的原則。
 * @example
 * ```javascript
 * //xxx[BB]xxx 當前選區為非閉合選區，選中BB這兩個文字
 * //執行命令，插入<b>CC</b>
 * //插入後的效果 xxx<b>CC</b>xxx
 * //<p>xx|xxx</p> 當前選區為閉合狀態
 * //插入<p>CC</p>
 * //結果 <p>xx</p><p>CC</p><p>xxx</p>
 * //<p>xxxx</p>|</p>xxx</p> 當前選區在兩個p標籤之間
 * //插入 xxxx
 * //結果 <p>xxxx</p><p>xxxx</p></p>xxx</p>
 * ```
 */

UE.commands['inserthtml'] = {
    execCommand: function (command,html,notNeedFilter){
        var me = this,
            range,
            div;
        if(!html){
            return;
        }
        if(me.fireEvent('beforeinserthtml',html) === true){
            return;
        }
        range = me.selection.getRange();
        div = range.document.createElement( 'div' );
        div.style.display = 'inline';

        if (!notNeedFilter) {
            var root = UE.htmlparser(html);
            //如果給了過濾規則就先進行過濾
            if(me.options.filterRules){
                UE.filterNode(root,me.options.filterRules);
            }
            //執行預設的處理
            me.filterInputRule(root);
            html = root.toHtml()
        }
        div.innerHTML = utils.trim( html );

        if ( !range.collapsed ) {
            var tmpNode = range.startContainer;
            if(domUtils.isFillChar(tmpNode)){
                range.setStartBefore(tmpNode)
            }
            tmpNode = range.endContainer;
            if(domUtils.isFillChar(tmpNode)){
                range.setEndAfter(tmpNode)
            }
            range.txtToElmBoundary();
            //結束邊界可能放到了br的前邊，要把br包含進來
            // x[xxx]<br/>
            if(range.endContainer && range.endContainer.nodeType == 1){
                tmpNode = range.endContainer.childNodes[range.endOffset];
                if(tmpNode && domUtils.isBr(tmpNode)){
                    range.setEndAfter(tmpNode);
                }
            }
            if(range.startOffset == 0){
                tmpNode = range.startContainer;
                if(domUtils.isBoundaryNode(tmpNode,'firstChild') ){
                    tmpNode = range.endContainer;
                    if(range.endOffset == (tmpNode.nodeType == 3 ? tmpNode.nodeValue.length : tmpNode.childNodes.length) && domUtils.isBoundaryNode(tmpNode,'lastChild')){
                        me.body.innerHTML = '<p>'+(browser.ie ? '' : '<br/>')+'</p>';
                        range.setStart(me.body.firstChild,0).collapse(true)

                    }
                }
            }
            !range.collapsed && range.deleteContents();
            if(range.startContainer.nodeType == 1){
                var child = range.startContainer.childNodes[range.startOffset],pre;
                if(child && domUtils.isBlockElm(child) && (pre = child.previousSibling) && domUtils.isBlockElm(pre)){
                    range.setEnd(pre,pre.childNodes.length).collapse();
                    while(child.firstChild){
                        pre.appendChild(child.firstChild);
                    }
                    domUtils.remove(child);
                }
            }

        }


        var child,parent,pre,tmp,hadBreak = 0, nextNode;
        //如果當前位置選中了fillchar要幹掉，要不會產生空行
        if(range.inFillChar()){
            child = range.startContainer;
            if(domUtils.isFillChar(child)){
                range.setStartBefore(child).collapse(true);
                domUtils.remove(child);
            }else if(domUtils.isFillChar(child,true)){
                child.nodeValue = child.nodeValue.replace(fillCharReg,'');
                range.startOffset--;
                range.collapsed && range.collapse(true)
            }
        }
        //列表單獨處理
        var li = domUtils.findParentByTagName(range.startContainer,'li',true);
        if(li){
            var next,last;
            while(child = div.firstChild){
                //針對hr單獨處理一下先
                while(child && (child.nodeType == 3 || !domUtils.isBlockElm(child) || child.tagName=='HR' )){
                    next = child.nextSibling;
                    range.insertNode( child).collapse();
                    last = child;
                    child = next;

                }
                if(child){
                    if(/^(ol|ul)$/i.test(child.tagName)){
                        while(child.firstChild){
                            last = child.firstChild;
                            domUtils.insertAfter(li,child.firstChild);
                            li = li.nextSibling;
                        }
                        domUtils.remove(child)
                    }else{
                        var tmpLi;
                        next = child.nextSibling;
                        tmpLi = me.document.createElement('li');
                        domUtils.insertAfter(li,tmpLi);
                        tmpLi.appendChild(child);
                        last = child;
                        child = next;
                        li = tmpLi;
                    }
                }
            }
            li = domUtils.findParentByTagName(range.startContainer,'li',true);
            if(domUtils.isEmptyBlock(li)){
                domUtils.remove(li)
            }
            if(last){

                range.setStartAfter(last).collapse(true).select(true)
            }
        }else{
            while ( child = div.firstChild ) {
                if(hadBreak){
                    var p = me.document.createElement('p');
                    while(child && (child.nodeType == 3 || !dtd.$block[child.tagName])){
                        nextNode = child.nextSibling;
                        p.appendChild(child);
                        child = nextNode;
                    }
                    if(p.firstChild){

                        child = p
                    }
                }
                range.insertNode( child );
                nextNode = child.nextSibling;
                if ( !hadBreak && child.nodeType == domUtils.NODE_ELEMENT && domUtils.isBlockElm( child ) ){

                    parent = domUtils.findParent( child,function ( node ){ return domUtils.isBlockElm( node ); } );
                    if ( parent && parent.tagName.toLowerCase() != 'body' && !(dtd[parent.tagName][child.nodeName] && child.parentNode === parent)){
                        if(!dtd[parent.tagName][child.nodeName]){
                            pre = parent;
                        }else{
                            tmp = child.parentNode;
                            while (tmp !== parent){
                                pre = tmp;
                                tmp = tmp.parentNode;

                            }
                        }


                        domUtils.breakParent( child, pre || tmp );
                        //去掉break後前一個多餘的節點  <p>|<[p> ==> <p></p><div></div><p>|</p>
                        var pre = child.previousSibling;
                        domUtils.trimWhiteTextNode(pre);
                        if(!pre.childNodes.length){
                            domUtils.remove(pre);
                        }
                        //trace:2012,在非ie的情況，切開後剩下的節點有可能不能點入游標新增br佔位

                        if(!browser.ie &&
                            (next = child.nextSibling) &&
                            domUtils.isBlockElm(next) &&
                            next.lastChild &&
                            !domUtils.isBr(next.lastChild)){
                            next.appendChild(me.document.createElement('br'));
                        }
                        hadBreak = 1;
                    }
                }
                var next = child.nextSibling;
                if(!div.firstChild && next && domUtils.isBlockElm(next)){

                    range.setStart(next,0).collapse(true);
                    break;
                }
                range.setEndAfter( child ).collapse();

            }

            child = range.startContainer;

            if(nextNode && domUtils.isBr(nextNode)){
                domUtils.remove(nextNode)
            }
            //用chrome可能有空白展位符
            if(domUtils.isBlockElm(child) && domUtils.isEmptyNode(child)){
                if(nextNode = child.nextSibling){
                    domUtils.remove(child);
                    if(nextNode.nodeType == 1 && dtd.$block[nextNode.tagName]){

                        range.setStart(nextNode,0).collapse(true).shrinkBoundary()
                    }
                }else{

                    try{
                        child.innerHTML = browser.ie ? domUtils.fillChar : '<br/>';
                    }catch(e){
                        range.setStartBefore(child);
                        domUtils.remove(child)
                    }

                }

            }
            //加上true因為在刪除表情等時會刪兩次，第一次是刪的fillData
            try{
                range.select(true);
            }catch(e){}

        }



        setTimeout(function(){
            range = me.selection.getRange();
            range.scrollToView(me.autoHeightEnabled,me.autoHeightEnabled ? domUtils.getXY(me.iframe).y:0);
            me.fireEvent('afterinserthtml', html);
        },200);
    }
};


// plugins/autosubmit.js
/**
 * 快捷鍵提交
 * @file
 * @since 1.2.6.1
 */

/**
 * 提交表單
 * @command autosubmit
 * @method execCommand
 * @param { String } cmd 命令字串
 * @example
 * ```javascript
 * editor.execCommand( 'autosubmit' );
 * ```
 */

UE.plugin.register('autosubmit',function(){
    return {
        shortcutkey:{
            "autosubmit":"ctrl+13" //手動提交
        },
        commands:{
            'autosubmit':{
                execCommand:function () {
                    var me=this,
                        form = domUtils.findParentByTagName(me.iframe,"form", false);
                    if (form){
                        if(me.fireEvent("beforesubmit")===false){
                            return;
                        }
                        me.sync();
                        $(form).find("input[type=submit]").click();
                    }
                }
            }
        }
    }
});

// plugins/image.js
/**
 * 圖片插入、排版外掛
 * @file
 * @since 1.2.6.1
 */

/**
 * 圖片對齊方式
 * @command imagefloat
 * @method execCommand
 * @remind 值center為獨佔一行居中
 * @param { String } cmd 命令字串
 * @param { String } align 對齊方式，可傳left、right、none、center
 * @remaind center表示圖片獨佔一行
 * @example
 * ```javascript
 * editor.execCommand( 'imagefloat', 'center' );
 * ```
 */

/**
 * 如果選區所在位置是圖片區域
 * @command imagefloat
 * @method queryCommandValue
 * @param { String } cmd 命令字串
 * @return { String } 返回圖片對齊方式
 * @example
 * ```javascript
 * editor.queryCommandValue( 'imagefloat' );
 * ```
 */

UE.commands['imagefloat'] = {
    execCommand:function (cmd, align) {
        var me = this,
            range = me.selection.getRange();
        if (!range.collapsed) {
            var img = range.getClosedNode();
            if (img && img.tagName == 'IMG') {
                switch (align) {
                    case 'left':
                    case 'right':
                    case 'none':
                        var pN = img.parentNode, tmpNode, pre, next;
                        while (dtd.$inline[pN.tagName] || pN.tagName == 'A') {
                            pN = pN.parentNode;
                        }
                        tmpNode = pN;
                        if (tmpNode.tagName == 'P' && domUtils.getStyle(tmpNode, 'text-align') == 'center') {
                            if (!domUtils.isBody(tmpNode) && domUtils.getChildCount(tmpNode, function (node) {
                                return !domUtils.isBr(node) && !domUtils.isWhitespace(node);
                            }) == 1) {
                                pre = tmpNode.previousSibling;
                                next = tmpNode.nextSibling;
                                if (pre && next && pre.nodeType == 1 && next.nodeType == 1 && pre.tagName == next.tagName && domUtils.isBlockElm(pre)) {
                                    pre.appendChild(tmpNode.firstChild);
                                    while (next.firstChild) {
                                        pre.appendChild(next.firstChild);
                                    }
                                    domUtils.remove(tmpNode);
                                    domUtils.remove(next);
                                } else {
                                    domUtils.setStyle(tmpNode, 'text-align', '');
                                }


                            }

                            range.selectNode(img).select();
                        }
                        domUtils.setStyle(img, 'float', align == 'none' ? '' : align);
                        if(align == 'none'){
                            domUtils.removeAttributes(img,'align');
                        }

                        break;
                    case 'center':
                        if (me.queryCommandValue('imagefloat') != 'center') {
                            pN = img.parentNode;
                            domUtils.setStyle(img, 'float', '');
                            domUtils.removeAttributes(img,'align');
                            tmpNode = img;
                            while (pN && domUtils.getChildCount(pN, function (node) {
                                return !domUtils.isBr(node) && !domUtils.isWhitespace(node);
                            }) == 1
                                && (dtd.$inline[pN.tagName] || pN.tagName == 'A')) {
                                tmpNode = pN;
                                pN = pN.parentNode;
                            }
                            range.setStartBefore(tmpNode).setCursor(false);
                            pN = me.document.createElement('div');
                            pN.appendChild(tmpNode);
                            domUtils.setStyle(tmpNode, 'float', '');

                            me.execCommand('insertHtml', '<p id="_img_parent_tmp" style="text-align:center">' + pN.innerHTML + '</p>');

                            tmpNode = me.document.getElementById('_img_parent_tmp');
                            tmpNode.removeAttribute('id');
                            tmpNode = tmpNode.firstChild;
                            range.selectNode(tmpNode).select();
                            //去掉後邊多餘的元素
                            next = tmpNode.parentNode.nextSibling;
                            if (next && domUtils.isEmptyNode(next)) {
                                domUtils.remove(next);
                            }

                        }

                        break;
                }

            }
        }
    },
    queryCommandValue:function () {
        var range = this.selection.getRange(),
            startNode, floatStyle;
        if (range.collapsed) {
            return 'none';
        }
        startNode = range.getClosedNode();
        if (startNode && startNode.nodeType == 1 && startNode.tagName == 'IMG') {
            floatStyle = domUtils.getComputedStyle(startNode, 'float') || startNode.getAttribute('align');

            if (floatStyle == 'none') {
                floatStyle = domUtils.getComputedStyle(startNode.parentNode, 'text-align') == 'center' ? 'center' : floatStyle;
            }
            return {
                left:1,
                right:1,
                center:1
            }[floatStyle] ? floatStyle : 'none';
        }
        return 'none';


    },
    queryCommandState:function () {
        var range = this.selection.getRange(),
            startNode;

        if (range.collapsed)  return -1;

        startNode = range.getClosedNode();
        if (startNode && startNode.nodeType == 1 && startNode.tagName == 'IMG') {
            return 0;
        }
        return -1;
    }
};


/**
 * 插入圖片
 * @command insertimage
 * @method execCommand
 * @param { String } cmd 命令字串
 * @param { Object } opt 屬性鍵值對，這些屬性都將被複制到當前插入圖片
 * @remind 該命令第二個引數可接受一個圖片配置項物件的陣列，可以插入多張圖片，
 * 此時陣列的每一個元素都是一個Object型別的圖片屬性集合。
 * @example
 * ```javascript
 * editor.execCommand( 'insertimage', {
 *     src:'a/b/c.jpg',
 *     width:'100',
 *     height:'100'
 * } );
 * ```
 * @example
 * ```javascript
 * editor.execCommand( 'insertimage', [{
 *     src:'a/b/c.jpg',
 *     width:'100',
 *     height:'100'
 * },{
 *     src:'a/b/d.jpg',
 *     width:'100',
 *     height:'100'
 * }] );
 * ```
 */

UE.commands['insertimage'] = {
    execCommand:function (cmd, opt) {

        opt = utils.isArray(opt) ? opt : [opt];
        if (!opt.length) {
            return;
        }
        var me = this,
            range = me.selection.getRange(),
            img = range.getClosedNode();

        if(me.fireEvent('beforeinsertimage', opt) === true){
            return;
        }

        function unhtmlData(imgCi) {

            utils.each('width,height,border,hspace,vspace'.split(','), function (item) {

                if (imgCi[item]) {
                    imgCi[item] = parseInt(imgCi[item], 10) || 0;
                }
            });

            utils.each('src,_src'.split(','), function (item) {

                if (imgCi[item]) {
                    imgCi[item] = utils.unhtmlForUrl(imgCi[item]);
                }
            });
            utils.each('title,alt'.split(','), function (item) {

                if (imgCi[item]) {
                    imgCi[item] = utils.unhtml(imgCi[item]);
                }
            });
        }

        if (img && /img/i.test(img.tagName) && (img.className != "edui-faked-video" || img.className.indexOf("edui-upload-video")!=-1) && !img.getAttribute("word_img")) {
            var first = opt.shift();
            var floatStyle = first['floatStyle'];
            delete first['floatStyle'];
            domUtils.setAttributes(img, first);
            me.execCommand('imagefloat', floatStyle);
            if (opt.length > 0) {
                range.setStartAfter(img).setCursor(false, true);
                me.execCommand('insertimage', opt);
            }

        } else {
            var html = [], str = '', ci;
            ci = opt[0];
            if (opt.length == 1) {
                unhtmlData(ci);

                str = '<img src="' + ci.src + '" ' + (ci._src ? ' _src="' + ci._src + '" ' : '') +
                    (ci.width ? 'width="' + ci.width + '" ' : '') +
                    (ci.height ? ' height="' + ci.height + '" ' : '') +
                    (ci['floatStyle'] == 'left' || ci['floatStyle'] == 'right' ? ' style="float:' + ci['floatStyle'] + ';"' : '') +
                    (ci.title && ci.title != "" ? ' title="' + ci.title + '"' : '') +
                    (ci.border && ci.border != "0" ? ' border="' + ci.border + '"' : '') +
                    (ci.alt && ci.alt != "" ? ' alt="' + ci.alt + '"' : '') +
                    (ci.hspace && ci.hspace != "0" ? ' hspace = "' + ci.hspace + '"' : '') +
                    (ci.vspace && ci.vspace != "0" ? ' vspace = "' + ci.vspace + '"' : '') + '/>';
                if (ci['floatStyle'] == 'center') {
                    str = '<p style="text-align: center">' + str + '</p>';
                }
                html.push(str);

            } else {
                for (var i = 0; ci = opt[i++];) {
                    unhtmlData(ci);
                    str = '<p ' + (ci['floatStyle'] == 'center' ? 'style="text-align: center" ' : '') + '><img src="' + ci.src + '" ' +
                        (ci.width ? 'width="' + ci.width + '" ' : '') + (ci._src ? ' _src="' + ci._src + '" ' : '') +
                        (ci.height ? ' height="' + ci.height + '" ' : '') +
                        ' style="' + (ci['floatStyle'] && ci['floatStyle'] != 'center' ? 'float:' + ci['floatStyle'] + ';' : '') +
                        (ci.border || '') + '" ' +
                        (ci.title ? ' title="' + ci.title + '"' : '') + ' /></p>';
                    html.push(str);
                }
            }

            me.execCommand('insertHtml', html.join(''));
        }

        me.fireEvent('afterinsertimage', opt)
    }
};

// plugins/justify.js
/**
 * 段落格式
 * @file
 * @since 1.2.6.1
 */

/**
 * 段落對齊方式
 * @command justify
 * @method execCommand
 * @param { String } cmd 命令字串
 * @param { String } align 對齊方式：left => 居左，right => 居右，center => 居中，justify => 兩端對齊
 * @example
 * ```javascript
 * editor.execCommand( 'justify', 'center' );
 * ```
 */
/**
 * 如果選區所在位置是段落區域，返回當前段落對齊方式
 * @command justify
 * @method queryCommandValue
 * @param { String } cmd 命令字串
 * @return { String } 返回段落對齊方式
 * @example
 * ```javascript
 * editor.queryCommandValue( 'justify' );
 * ```
 */

UE.plugins['justify']=function(){
    var me=this,
        block = domUtils.isBlockElm,
        defaultValue = {
            left:1,
            right:1,
            center:1,
            justify:1
        },
        doJustify = function (range, style) {
            var bookmark = range.createBookmark(),
                filterFn = function (node) {
                    return node.nodeType == 1 ? node.tagName.toLowerCase() != 'br' && !domUtils.isBookmarkNode(node) : !domUtils.isWhitespace(node);
                };

            range.enlarge(true);
            var bookmark2 = range.createBookmark(),
                current = domUtils.getNextDomNode(bookmark2.start, false, filterFn),
                tmpRange = range.cloneRange(),
                tmpNode;
            while (current && !(domUtils.getPosition(current, bookmark2.end) & domUtils.POSITION_FOLLOWING)) {
                if (current.nodeType == 3 || !block(current)) {
                    tmpRange.setStartBefore(current);
                    while (current && current !== bookmark2.end && !block(current)) {
                        tmpNode = current;
                        current = domUtils.getNextDomNode(current, false, null, function (node) {
                            return !block(node);
                        });
                    }
                    tmpRange.setEndAfter(tmpNode);
                    var common = tmpRange.getCommonAncestor();
                    if (!domUtils.isBody(common) && block(common)) {
                        domUtils.setStyles(common, utils.isString(style) ? {'text-align':style} : style);
                        current = common;
                    } else {
                        var p = range.document.createElement('p');
                        domUtils.setStyles(p, utils.isString(style) ? {'text-align':style} : style);
                        var frag = tmpRange.extractContents();
                        p.appendChild(frag);
                        tmpRange.insertNode(p);
                        current = p;
                    }
                    current = domUtils.getNextDomNode(current, false, filterFn);
                } else {
                    current = domUtils.getNextDomNode(current, true, filterFn);
                }
            }
            return range.moveToBookmark(bookmark2).moveToBookmark(bookmark);
        };

    UE.commands['justify'] = {
        execCommand:function (cmdName, align) {
            var range = this.selection.getRange(),
                txt;

            //閉合時單獨處理
            if (range.collapsed) {
                txt = this.document.createTextNode('p');
                range.insertNode(txt);
            }
            doJustify(range, align);
            if (txt) {
                range.setStartBefore(txt).collapse(true);
                domUtils.remove(txt);
            }

            range.select();


            return true;
        },
        queryCommandValue:function () {
            var startNode = this.selection.getStart(),
                value = domUtils.getComputedStyle(startNode, 'text-align');
            return defaultValue[value] ? value : 'left';
        },
        queryCommandState:function () {
            var start = this.selection.getStart(),
                cell = start && domUtils.findParentByTagName(start, ["td", "th","caption"], true);

            return cell? -1:0;
        }

    };
};


// plugins/font.js
/**
 * 字型顏色,背景色,字號,字型,下劃線,刪除線
 * @file
 * @since 1.2.6.1
 */

/**
 * 字型顏色
 * @command forecolor
 * @method execCommand
 * @param { String } cmd 命令字串
 * @param { String } value 色值(必須十六進位制)
 * @example
 * ```javascript
 * editor.execCommand( 'forecolor', '#000' );
 * ```
 */
/**
 * 返回選區字型顏色
 * @command forecolor
 * @method queryCommandValue
 * @param { String } cmd 命令字串
 * @return { String } 返回字型顏色
 * @example
 * ```javascript
 * editor.queryCommandValue( 'forecolor' );
 * ```
 */

/**
 * 字型背景顏色
 * @command backcolor
 * @method execCommand
 * @param { String } cmd 命令字串
 * @param { String } value 色值(必須十六進位制)
 * @example
 * ```javascript
 * editor.execCommand( 'backcolor', '#000' );
 * ```
 */
/**
 * 返回選區字型顏色
 * @command backcolor
 * @method queryCommandValue
 * @param { String } cmd 命令字串
 * @return { String } 返回字型背景顏色
 * @example
 * ```javascript
 * editor.queryCommandValue( 'backcolor' );
 * ```
 */

/**
 * 字型大小
 * @command fontsize
 * @method execCommand
 * @param { String } cmd 命令字串
 * @param { String } value 字型大小
 * @example
 * ```javascript
 * editor.execCommand( 'fontsize', '14px' );
 * ```
 */
/**
 * 返回選區字型大小
 * @command fontsize
 * @method queryCommandValue
 * @param { String } cmd 命令字串
 * @return { String } 返回字型大小
 * @example
 * ```javascript
 * editor.queryCommandValue( 'fontsize' );
 * ```
 */

/**
 * 字型樣式
 * @command fontfamily
 * @method execCommand
 * @param { String } cmd 命令字串
 * @param { String } value 字型樣式
 * @example
 * ```javascript
 * editor.execCommand( 'fontfamily', '微軟雅黑' );
 * ```
 */
/**
 * 返回選區字型樣式
 * @command fontfamily
 * @method queryCommandValue
 * @param { String } cmd 命令字串
 * @return { String } 返回字型樣式
 * @example
 * ```javascript
 * editor.queryCommandValue( 'fontfamily' );
 * ```
 */

/**
 * 字型下劃線,與刪除線互斥
 * @command underline
 * @method execCommand
 * @param { String } cmd 命令字串
 * @example
 * ```javascript
 * editor.execCommand( 'underline' );
 * ```
 */

/**
 * 字型刪除線,與下劃線互斥
 * @command strikethrough
 * @method execCommand
 * @param { String } cmd 命令字串
 * @example
 * ```javascript
 * editor.execCommand( 'strikethrough' );
 * ```
 */

/**
 * 字型邊框
 * @command fontborder
 * @method execCommand
 * @param { String } cmd 命令字串
 * @example
 * ```javascript
 * editor.execCommand( 'fontborder' );
 * ```
 */

UE.plugins['font'] = function () {
    var me = this,
        fonts = {
            'forecolor': 'color',
            'backcolor': 'background-color',
            'fontsize': 'font-size',
            'fontfamily': 'font-family',
            'underline': 'text-decoration',
            'strikethrough': 'text-decoration',
            'fontborder': 'border'
        },
        needCmd = {'underline': 1, 'strikethrough': 1, 'fontborder': 1},
        needSetChild = {
            'forecolor': 'color',
            'backcolor': 'background-color',
            'fontsize': 'font-size',
            'fontfamily': 'font-family'

        };
    me.setOpt({
        'fontfamily': [
            { name: 'songti', val: '宋體,SimSun'},
            { name: 'yahei', val: '微軟雅黑,Microsoft YaHei'},
            { name: 'kaiti', val: '楷體,楷體_GB2312, SimKai'},
            { name: 'heiti', val: '黑體, SimHei'},
            { name: 'lishu', val: '隸書, SimLi'},
            { name: 'andaleMono', val: 'andale mono'},
            { name: 'arial', val: 'arial, helvetica,sans-serif'},
            { name: 'arialBlack', val: 'arial black,avant garde'},
            { name: 'comicSansMs', val: 'comic sans ms'},
            { name: 'impact', val: 'impact,chicago'},
            { name: 'timesNewRoman', val: 'times new roman'}
        ],
        'fontsize': [10, 11, 12, 14, 16, 18, 20, 24, 36]
    });

    function mergeWithParent(node){
        var parent;
        while(parent = node.parentNode){
            if(parent.tagName == 'SPAN' && domUtils.getChildCount(parent,function(child){
                return !domUtils.isBookmarkNode(child) && !domUtils.isBr(child)
            }) == 1) {
                parent.style.cssText += node.style.cssText;
                domUtils.remove(node,true);
                node = parent;

            }else{
                break;
            }
        }

    }
    function mergeChild(rng,cmdName,value){
        if(needSetChild[cmdName]){
            rng.adjustmentBoundary();
            if(!rng.collapsed && rng.startContainer.nodeType == 1){
                var start = rng.startContainer.childNodes[rng.startOffset];
                if(start && domUtils.isTagNode(start,'span')){
                    var bk = rng.createBookmark();
                    utils.each(domUtils.getElementsByTagName(start, 'span'), function (span) {
                        if (!span.parentNode || domUtils.isBookmarkNode(span))return;
                        if(cmdName == 'backcolor' && domUtils.getComputedStyle(span,'background-color').toLowerCase() === value){
                            return;
                        }
                        domUtils.removeStyle(span,needSetChild[cmdName]);
                        if(span.style.cssText.replace(/^\s+$/,'').length == 0){
                            domUtils.remove(span,true)
                        }
                    });
                    rng.moveToBookmark(bk)
                }
            }
        }

    }
    function mergesibling(rng,cmdName,value) {
        var collapsed = rng.collapsed,
            bk = rng.createBookmark(), common;
        if (collapsed) {
            common = bk.start.parentNode;
            while (dtd.$inline[common.tagName]) {
                common = common.parentNode;
            }
        } else {
            common = domUtils.getCommonAncestor(bk.start, bk.end);
        }
        utils.each(domUtils.getElementsByTagName(common, 'span'), function (span) {
            if (!span.parentNode || domUtils.isBookmarkNode(span))return;
            if (/\s*border\s*:\s*none;?\s*/i.test(span.style.cssText)) {
                if(/^\s*border\s*:\s*none;?\s*$/.test(span.style.cssText)){
                    domUtils.remove(span, true);
                }else{
                    domUtils.removeStyle(span,'border');
                }
                return
            }
            if (/border/i.test(span.style.cssText) && span.parentNode.tagName == 'SPAN' && /border/i.test(span.parentNode.style.cssText)) {
                span.style.cssText = span.style.cssText.replace(/border[^:]*:[^;]+;?/gi, '');
            }
            if(!(cmdName=='fontborder' && value=='none')){
                var next = span.nextSibling;
                while (next && next.nodeType == 1 && next.tagName == 'SPAN' ) {
                    if(domUtils.isBookmarkNode(next) && cmdName == 'fontborder') {
                        span.appendChild(next);
                        next = span.nextSibling;
                        continue;
                    }
                    if (next.style.cssText == span.style.cssText) {
                        domUtils.moveChild(next, span);
                        domUtils.remove(next);
                    }
                    if (span.nextSibling === next)
                        break;
                    next = span.nextSibling;
                }
            }


            mergeWithParent(span);
            if(browser.ie && browser.version > 8 ){
                //拷貝父親們的特別的屬性,這裡只做背景顏色的處理
                var parent = domUtils.findParent(span,function(n){return n.tagName == 'SPAN' && /background-color/.test(n.style.cssText)});
                if(parent && !/background-color/.test(span.style.cssText)){
                    span.style.backgroundColor = parent.style.backgroundColor;
                }
            }

        });
        rng.moveToBookmark(bk);
        mergeChild(rng,cmdName,value)
    }

    me.addInputRule(function (root) {
        utils.each(root.getNodesByTagName('u s del font strike'), function (node) {
            if (node.tagName == 'font') {
                var cssStyle = [];
                for (var p in node.attrs) {
                    switch (p) {
                        case 'size':
                            cssStyle.push('font-size:' +
                                ({
                                '1':'10',
                                '2':'12',
                                '3':'16',
                                '4':'18',
                                '5':'24',
                                '6':'32',
                                '7':'48'
                            }[node.attrs[p]] || node.attrs[p]) + 'px');
                            break;
                        case 'color':
                            cssStyle.push('color:' + node.attrs[p]);
                            break;
                        case 'face':
                            cssStyle.push('font-family:' + node.attrs[p]);
                            break;
                        case 'style':
                            cssStyle.push(node.attrs[p]);
                    }
                }
                node.attrs = {
                    'style': cssStyle.join(';')
                };
            } else {
                var val = node.tagName == 'u' ? 'underline' : 'line-through';
                node.attrs = {
                    'style': (node.getAttr('style') || '') + 'text-decoration:' + val + ';'
                }
            }
            node.tagName = 'span';
        });
//        utils.each(root.getNodesByTagName('span'), function (node) {
//            var val;
//            if(val = node.getAttr('class')){
//                if(/fontstrikethrough/.test(val)){
//                    node.setStyle('text-decoration','line-through');
//                    if(node.attrs['class']){
//                        node.attrs['class'] = node.attrs['class'].replace(/fontstrikethrough/,'');
//                    }else{
//                        node.setAttr('class')
//                    }
//                }
//                if(/fontborder/.test(val)){
//                    node.setStyle('border','1px solid #000');
//                    if(node.attrs['class']){
//                        node.attrs['class'] = node.attrs['class'].replace(/fontborder/,'');
//                    }else{
//                        node.setAttr('class')
//                    }
//                }
//            }
//        });
    });
//    me.addOutputRule(function(root){
//        utils.each(root.getNodesByTagName('span'), function (node) {
//            var val;
//            if(val = node.getStyle('text-decoration')){
//                if(/line-through/.test(val)){
//                    if(node.attrs['class']){
//                        node.attrs['class'] += ' fontstrikethrough';
//                    }else{
//                        node.setAttr('class','fontstrikethrough')
//                    }
//                }
//
//                node.setStyle('text-decoration')
//            }
//            if(val = node.getStyle('border')){
//                if(/1px/.test(val) && /solid/.test(val)){
//                    if(node.attrs['class']){
//                        node.attrs['class'] += ' fontborder';
//
//                    }else{
//                        node.setAttr('class','fontborder')
//                    }
//                }
//                node.setStyle('border')
//
//            }
//        });
//    });
    for (var p in fonts) {
        (function (cmd, style) {
            UE.commands[cmd] = {
                execCommand: function (cmdName, value) {
                    value = value || (this.queryCommandState(cmdName) ? 'none' : cmdName == 'underline' ? 'underline' :
                        cmdName == 'fontborder' ? '1px solid #000' :
                            'line-through');
                    var me = this,
                        range = this.selection.getRange(),
                        text;

                    if (value == 'default') {

                        if (range.collapsed) {
                            text = me.document.createTextNode('font');
                            range.insertNode(text).select();

                        }
                        me.execCommand('removeFormat', 'span,a', style);
                        if (text) {
                            range.setStartBefore(text).collapse(true);
                            domUtils.remove(text);
                        }
                        mergesibling(range,cmdName,value);
                        range.select()
                    } else {
                        if (!range.collapsed) {
                            if (needCmd[cmd] && me.queryCommandValue(cmd)) {
                                me.execCommand('removeFormat', 'span,a', style);
                            }
                            range = me.selection.getRange();

                            range.applyInlineStyle('span', {'style': style + ':' + value});
                            mergesibling(range, cmdName,value);
                            range.select();
                        } else {

                            var span = domUtils.findParentByTagName(range.startContainer, 'span', true);
                            text = me.document.createTextNode('font');
                            if (span && !span.children.length && !span[browser.ie ? 'innerText' : 'textContent'].replace(fillCharReg, '').length) {
                                //for ie hack when enter
                                range.insertNode(text);
                                if (needCmd[cmd]) {
                                    range.selectNode(text).select();
                                    me.execCommand('removeFormat', 'span,a', style, null);

                                    span = domUtils.findParentByTagName(text, 'span', true);
                                    range.setStartBefore(text);

                                }
                                span && (span.style.cssText += ';' + style + ':' + value);
                                range.collapse(true).select();


                            } else {
                                range.insertNode(text);
                                range.selectNode(text).select();
                                span = range.document.createElement('span');

                                if (needCmd[cmd]) {
                                    //a標籤內的不處理跳過
                                    if (domUtils.findParentByTagName(text, 'a', true)) {
                                        range.setStartBefore(text).setCursor();
                                        domUtils.remove(text);
                                        return;
                                    }
                                    me.execCommand('removeFormat', 'span,a', style);
                                }

                                span.style.cssText = style + ':' + value;


                                text.parentNode.insertBefore(span, text);
                                //修復，span套span 但樣式不繼承的問題
                                if (!browser.ie || browser.ie && browser.version == 9) {
                                    var spanParent = span.parentNode;
                                    while (!domUtils.isBlockElm(spanParent)) {
                                        if (spanParent.tagName == 'SPAN') {
                                            //opera合併style不會加入";"
                                            span.style.cssText = spanParent.style.cssText + ";" + span.style.cssText;
                                        }
                                        spanParent = spanParent.parentNode;
                                    }
                                }


                                if (opera) {
                                    setTimeout(function () {
                                        range.setStart(span, 0).collapse(true);
                                        mergesibling(range, cmdName,value);
                                        range.select();
                                    });
                                } else {
                                    range.setStart(span, 0).collapse(true);
                                    mergesibling(range,cmdName,value);
                                    range.select();
                                }

                                //trace:981
                                //domUtils.mergeToParent(span)
                            }
                            domUtils.remove(text);
                        }


                    }
                    return true;
                },
                queryCommandValue: function (cmdName) {
                    var startNode = this.selection.getStart();

                    //trace:946
                    if (cmdName == 'underline' || cmdName == 'strikethrough') {
                        var tmpNode = startNode, value;
                        while (tmpNode && !domUtils.isBlockElm(tmpNode) && !domUtils.isBody(tmpNode)) {
                            if (tmpNode.nodeType == 1) {
                                value = domUtils.getComputedStyle(tmpNode, style);
                                if (value != 'none') {
                                    return value;
                                }
                            }

                            tmpNode = tmpNode.parentNode;
                        }
                        return 'none';
                    }
                    if (cmdName == 'fontborder') {
                        var tmp = startNode, val;
                        while (tmp && dtd.$inline[tmp.tagName]) {
                            if (val = domUtils.getComputedStyle(tmp, 'border')) {

                                if (/1px/.test(val) && /solid/.test(val)) {
                                    return val;
                                }
                            }
                            tmp = tmp.parentNode;
                        }
                        return ''
                    }

                    if( cmdName == 'FontSize' ) {
                        var styleVal = domUtils.getComputedStyle(startNode, style),
                            tmp = /^([\d\.]+)(\w+)$/.exec( styleVal );

                        if( tmp ) {

                            return Math.floor( tmp[1] ) + tmp[2];

                        }

                        return styleVal;

                    }

                    return  domUtils.getComputedStyle(startNode, style);
                },
                queryCommandState: function (cmdName) {
                    if (!needCmd[cmdName])
                        return 0;
                    var val = this.queryCommandValue(cmdName);
                    if (cmdName == 'fontborder') {
                        return /1px/.test(val) && /solid/.test(val)
                    } else {
                        return  cmdName == 'underline' ? /underline/.test(val) : /line\-through/.test(val);

                    }

                }
            };
        })(p, fonts[p]);
    }
};

// plugins/link.js
/**
 * 超連結
 * @file
 * @since 1.2.6.1
 */

/**
 * 插入超連結
 * @command link
 * @method execCommand
 * @param { String } cmd 命令字串
 * @param { Object } options   設定自定義屬性，例如：url、title、target
 * @example
 * ```javascript
 * editor.execCommand( 'link', '{
 *     url:'ueditor.baidu.com',
 *     title:'ueditor',
 *     target:'_blank'
 * }' );
 * ```
 */
/**
 * 返回當前選中的第一個超連結節點
 * @command link
 * @method queryCommandValue
 * @param { String } cmd 命令字串
 * @return { Element } 超連結節點
 * @example
 * ```javascript
 * editor.queryCommandValue( 'link' );
 * ```
 */

/**
 * 取消超連結
 * @command unlink
 * @method execCommand
 * @param { String } cmd 命令字串
 * @example
 * ```javascript
 * editor.execCommand( 'unlink');
 * ```
 */

UE.plugins['link'] = function(){
    function optimize( range ) {
        var start = range.startContainer,end = range.endContainer;

        if ( start = domUtils.findParentByTagName( start, 'a', true ) ) {
            range.setStartBefore( start );
        }
        if ( end = domUtils.findParentByTagName( end, 'a', true ) ) {
            range.setEndAfter( end );
        }
    }


    UE.commands['unlink'] = {
        execCommand : function() {
            var range = this.selection.getRange(),
                bookmark;
            if(range.collapsed && !domUtils.findParentByTagName( range.startContainer, 'a', true )){
                return;
            }
            bookmark = range.createBookmark();
            optimize( range );
            range.removeInlineStyle( 'a' ).moveToBookmark( bookmark ).select();
        },
        queryCommandState : function(){
            return !this.highlight && this.queryCommandValue('link') ?  0 : -1;
        }

    };
    function doLink(range,opt,me){
        var rngClone = range.cloneRange(),
            link = me.queryCommandValue('link');
        optimize( range = range.adjustmentBoundary() );
        var start = range.startContainer;
        if(start.nodeType == 1 && link){
            start = start.childNodes[range.startOffset];
            if(start && start.nodeType == 1 && start.tagName == 'A' && /^(?:https?|ftp|file)\s*:\s*\/\//.test(start[browser.ie?'innerText':'textContent'])){
                start[browser.ie ? 'innerText' : 'textContent'] =  utils.html(opt.textValue||opt.href);

            }
        }
        if( !rngClone.collapsed || link){
            range.removeInlineStyle( 'a' );
            rngClone = range.cloneRange();
        }

        if ( rngClone.collapsed ) {
            var a = range.document.createElement( 'a'),
                text = '';
            if(opt.textValue){

                text =   utils.html(opt.textValue);
                delete opt.textValue;
            }else{
                text =   utils.html(opt.href);

            }
            domUtils.setAttributes( a, opt );
            start =  domUtils.findParentByTagName( rngClone.startContainer, 'a', true );
            if(start && domUtils.isInNodeEndBoundary(rngClone,start)){
                range.setStartAfter(start).collapse(true);

            }
            a[browser.ie ? 'innerText' : 'textContent'] = text;
            range.insertNode(a).selectNode( a );
        } else {
            range.applyInlineStyle( 'a', opt );

        }
    }
    UE.commands['link'] = {
        execCommand : function( cmdName, opt ) {
            var range;
            opt._href && (opt._href = utils.unhtml(opt._href,/[<">]/g));
            opt.href && (opt.href = utils.unhtml(opt.href,/[<">]/g));
            opt.textValue && (opt.textValue = utils.unhtml(opt.textValue,/[<">]/g));
            doLink(range=this.selection.getRange(),opt,this);
            //閉合都不加佔位符，如果加了會在a後邊多個佔位符節點，導致a是圖片背景組成的列表，出現空白問題
            range.collapse().select(true);

        },
        queryCommandValue : function() {
            var range = this.selection.getRange(),
                node;
            if ( range.collapsed ) {
//                    node = this.selection.getStart();
                //在ie下getstart()取值偏上了
                node = range.startContainer;
                node = node.nodeType == 1 ? node : node.parentNode;

                if ( node && (node = domUtils.findParentByTagName( node, 'a', true )) && ! domUtils.isInNodeEndBoundary(range,node)) {

                    return node;
                }
            } else {
                //trace:1111  如果是<p><a>xx</a></p> startContainer是p就會找不到a
                range.shrinkBoundary();
                var start = range.startContainer.nodeType  == 3 || !range.startContainer.childNodes[range.startOffset] ? range.startContainer : range.startContainer.childNodes[range.startOffset],
                    end =  range.endContainer.nodeType == 3 || range.endOffset == 0 ? range.endContainer : range.endContainer.childNodes[range.endOffset-1],
                    common = range.getCommonAncestor();
                node = domUtils.findParentByTagName( common, 'a', true );
                if ( !node && common.nodeType == 1){

                    var as = common.getElementsByTagName( 'a' ),
                        ps,pe;

                    for ( var i = 0,ci; ci = as[i++]; ) {
                        ps = domUtils.getPosition( ci, start ),pe = domUtils.getPosition( ci,end);
                        if ( (ps & domUtils.POSITION_FOLLOWING || ps & domUtils.POSITION_CONTAINS)
                            &&
                            (pe & domUtils.POSITION_PRECEDING || pe & domUtils.POSITION_CONTAINS)
                            ) {
                            node = ci;
                            break;
                        }
                    }
                }
                return node;
            }

        },
        queryCommandState : function() {
            //判斷如果是視訊的話連線不可用
            //fix 853
            var img = this.selection.getRange().getClosedNode(),
                flag = img && (img.className == "edui-faked-video" || img.className.indexOf("edui-upload-video")!=-1);
            return flag ? -1 : 0;
        }
    };
};

// plugins/removeformat.js
/**
 * 清除格式
 * @file
 * @since 1.2.6.1
 */

/**
 * 清除文字樣式
 * @command removeformat
 * @method execCommand
 * @param { String } cmd 命令字串
 * @param   {String}   tags     以逗號隔開的標籤。如：strong
 * @param   {String}   style    樣式如：color
 * @param   {String}   attrs    屬性如:width
 * @example
 * ```javascript
 * editor.execCommand( 'removeformat', 'strong','color','width' );
 * ```
 */

UE.plugins['removeformat'] = function(){
    var me = this;
    me.setOpt({
       'removeFormatTags': 'b,big,code,del,dfn,em,font,i,ins,kbd,q,samp,small,span,strike,strong,sub,sup,tt,u,var',
       'removeFormatAttributes':'class,style,lang,width,height,align,hspace,valign'
    });
    me.commands['removeformat'] = {
        execCommand : function( cmdName, tags, style, attrs,notIncludeA ) {

            var tagReg = new RegExp( '^(?:' + (tags || this.options.removeFormatTags).replace( /,/g, '|' ) + ')$', 'i' ) ,
                removeFormatAttributes = style ? [] : (attrs || this.options.removeFormatAttributes).split( ',' ),
                range = new dom.Range( this.document ),
                bookmark,node,parent,
                filter = function( node ) {
                    return node.nodeType == 1;
                };

            function isRedundantSpan (node) {
                if (node.nodeType == 3 || node.tagName.toLowerCase() != 'span'){
                    return 0;
                }
                if (browser.ie) {
                    //ie 下判斷實效，所以只能簡單用style來判斷
                    //return node.style.cssText == '' ? 1 : 0;
                    var attrs = node.attributes;
                    if ( attrs.length ) {
                        for ( var i = 0,l = attrs.length; i<l; i++ ) {
                            if ( attrs[i].specified ) {
                                return 0;
                            }
                        }
                        return 1;
                    }
                }
                return !node.attributes.length;
            }
            function doRemove( range ) {

                var bookmark1 = range.createBookmark();
                if ( range.collapsed ) {
                    range.enlarge( true );
                }

                //不能把a標籤切了
                if(!notIncludeA){
                    var aNode = domUtils.findParentByTagName(range.startContainer,'a',true);
                    if(aNode){
                        range.setStartBefore(aNode);
                    }

                    aNode = domUtils.findParentByTagName(range.endContainer,'a',true);
                    if(aNode){
                        range.setEndAfter(aNode);
                    }

                }


                bookmark = range.createBookmark();

                node = bookmark.start;

                //切開始
                while ( (parent = node.parentNode) && !domUtils.isBlockElm( parent ) ) {
                    domUtils.breakParent( node, parent );

                    domUtils.clearEmptySibling( node );
                }
                if ( bookmark.end ) {
                    //切結束
                    node = bookmark.end;
                    while ( (parent = node.parentNode) && !domUtils.isBlockElm( parent ) ) {
                        domUtils.breakParent( node, parent );
                        domUtils.clearEmptySibling( node );
                    }

                    //開始去除樣式
                    var current = domUtils.getNextDomNode( bookmark.start, false, filter ),
                        next;
                    while ( current ) {
                        if ( current == bookmark.end ) {
                            break;
                        }

                        next = domUtils.getNextDomNode( current, true, filter );

                        if ( !dtd.$empty[current.tagName.toLowerCase()] && !domUtils.isBookmarkNode( current ) ) {
                            if ( tagReg.test( current.tagName ) ) {
                                if ( style ) {
                                    domUtils.removeStyle( current, style );
                                    if ( isRedundantSpan( current ) && style != 'text-decoration'){
                                        domUtils.remove( current, true );
                                    }
                                } else {
                                    domUtils.remove( current, true );
                                }
                            } else {
                                //trace:939  不能把list上的樣式去掉
                                if(!dtd.$tableContent[current.tagName] && !dtd.$list[current.tagName]){
                                    domUtils.removeAttributes( current, removeFormatAttributes );
                                    if ( isRedundantSpan( current ) ){
                                        domUtils.remove( current, true );
                                    }
                                }

                            }
                        }
                        current = next;
                    }
                }
                //trace:1035
                //trace:1096 不能把td上的樣式去掉，比如邊框
                var pN = bookmark.start.parentNode;
                if(domUtils.isBlockElm(pN) && !dtd.$tableContent[pN.tagName] && !dtd.$list[pN.tagName]){
                    domUtils.removeAttributes(  pN,removeFormatAttributes );
                }
                pN = bookmark.end.parentNode;
                if(bookmark.end && domUtils.isBlockElm(pN) && !dtd.$tableContent[pN.tagName]&& !dtd.$list[pN.tagName]){
                    domUtils.removeAttributes(  pN,removeFormatAttributes );
                }
                range.moveToBookmark( bookmark ).moveToBookmark(bookmark1);
                //清除冗餘的程式碼 <b><bookmark></b>
                var node = range.startContainer,
                    tmp,
                    collapsed = range.collapsed;
                while(node.nodeType == 1 && domUtils.isEmptyNode(node) && dtd.$removeEmpty[node.tagName]){
                    tmp = node.parentNode;
                    range.setStartBefore(node);
                    //trace:937
                    //更新結束邊界
                    if(range.startContainer === range.endContainer){
                        range.endOffset--;
                    }
                    domUtils.remove(node);
                    node = tmp;
                }

                if(!collapsed){
                    node = range.endContainer;
                    while(node.nodeType == 1 && domUtils.isEmptyNode(node) && dtd.$removeEmpty[node.tagName]){
                        tmp = node.parentNode;
                        range.setEndBefore(node);
                        domUtils.remove(node);

                        node = tmp;
                    }


                }
            }



            range = this.selection.getRange();
            doRemove( range );
            range.select();

        }

    };

};


// plugins/blockquote.js
/**
 * 新增引用
 * @file
 * @since 1.2.6.1
 */

/**
 * 新增引用
 * @command blockquote
 * @method execCommand
 * @param { String } cmd 命令字串
 * @example
 * ```javascript
 * editor.execCommand( 'blockquote' );
 * ```
 */

/**
 * 新增引用
 * @command blockquote
 * @method execCommand
 * @param { String } cmd 命令字串
 * @param { Object } attrs 節點屬性
 * @example
 * ```javascript
 * editor.execCommand( 'blockquote',{
 *     style: "color: red;"
 * } );
 * ```
 */


UE.plugins['blockquote'] = function(){
    var me = this;
    function getObj(editor){
        return domUtils.filterNodeList(editor.selection.getStartElementPath(),'blockquote');
    }
    me.commands['blockquote'] = {
        execCommand : function( cmdName, attrs ) {
            var range = this.selection.getRange(),
                obj = getObj(this),
                blockquote = dtd.blockquote,
                bookmark = range.createBookmark();

            if ( obj ) {

                    var start = range.startContainer,
                        startBlock = domUtils.isBlockElm(start) ? start : domUtils.findParent(start,function(node){return domUtils.isBlockElm(node)}),

                        end = range.endContainer,
                        endBlock = domUtils.isBlockElm(end) ? end :  domUtils.findParent(end,function(node){return domUtils.isBlockElm(node)});

                    //處理一下li
                    startBlock = domUtils.findParentByTagName(startBlock,'li',true) || startBlock;
                    endBlock = domUtils.findParentByTagName(endBlock,'li',true) || endBlock;


                    if(startBlock.tagName == 'LI' || startBlock.tagName == 'TD' || startBlock === obj || domUtils.isBody(startBlock)){
                        domUtils.remove(obj,true);
                    }else{
                        domUtils.breakParent(startBlock,obj);
                    }

                    if(startBlock !== endBlock){
                        obj = domUtils.findParentByTagName(endBlock,'blockquote');
                        if(obj){
                            if(endBlock.tagName == 'LI' || endBlock.tagName == 'TD'|| domUtils.isBody(endBlock)){
                                obj.parentNode && domUtils.remove(obj,true);
                            }else{
                                domUtils.breakParent(endBlock,obj);
                            }

                        }
                    }

                    var blockquotes = domUtils.getElementsByTagName(this.document,'blockquote');
                    for(var i=0,bi;bi=blockquotes[i++];){
                        if(!bi.childNodes.length){
                            domUtils.remove(bi);
                        }else if(domUtils.getPosition(bi,startBlock)&domUtils.POSITION_FOLLOWING && domUtils.getPosition(bi,endBlock)&domUtils.POSITION_PRECEDING){
                            domUtils.remove(bi,true);
                        }
                    }




            } else {

                var tmpRange = range.cloneRange(),
                    node = tmpRange.startContainer.nodeType == 1 ? tmpRange.startContainer : tmpRange.startContainer.parentNode,
                    preNode = node,
                    doEnd = 1;

                //調整開始
                while ( 1 ) {
                    if ( domUtils.isBody(node) ) {
                        if ( preNode !== node ) {
                            if ( range.collapsed ) {
                                tmpRange.selectNode( preNode );
                                doEnd = 0;
                            } else {
                                tmpRange.setStartBefore( preNode );
                            }
                        }else{
                            tmpRange.setStart(node,0);
                        }

                        break;
                    }
                    if ( !blockquote[node.tagName] ) {
                        if ( range.collapsed ) {
                            tmpRange.selectNode( preNode );
                        } else{
                            tmpRange.setStartBefore( preNode);
                        }
                        break;
                    }

                    preNode = node;
                    node = node.parentNode;
                }

                //調整結束
                if ( doEnd ) {
                    preNode = node =  node = tmpRange.endContainer.nodeType == 1 ? tmpRange.endContainer : tmpRange.endContainer.parentNode;
                    while ( 1 ) {

                        if ( domUtils.isBody( node ) ) {
                            if ( preNode !== node ) {

                                tmpRange.setEndAfter( preNode );

                            } else {
                                tmpRange.setEnd( node, node.childNodes.length );
                            }

                            break;
                        }
                        if ( !blockquote[node.tagName] ) {
                            tmpRange.setEndAfter( preNode );
                            break;
                        }

                        preNode = node;
                        node = node.parentNode;
                    }

                }


                node = range.document.createElement( 'blockquote' );
                domUtils.setAttributes( node, attrs );
                node.appendChild( tmpRange.extractContents() );
                tmpRange.insertNode( node );
                //去除重複的
                var childs = domUtils.getElementsByTagName(node,'blockquote');
                for(var i=0,ci;ci=childs[i++];){
                    if(ci.parentNode){
                        domUtils.remove(ci,true);
                    }
                }

            }
            range.moveToBookmark( bookmark ).select();
        },
        queryCommandState : function() {
            return getObj(this) ? 1 : 0;
        }
    };
};



// plugins/convertcase.js
/**
 * 大小寫轉換
 * @file
 * @since 1.2.6.1
 */

/**
 * 把選區內文字變大寫，與“tolowercase”命令互斥
 * @command touppercase
 * @method execCommand
 * @param { String } cmd 命令字串
 * @example
 * ```javascript
 * editor.execCommand( 'touppercase' );
 * ```
 */

/**
 * 把選區內文字變小寫，與“touppercase”命令互斥
 * @command tolowercase
 * @method execCommand
 * @param { String } cmd 命令字串
 * @example
 * ```javascript
 * editor.execCommand( 'tolowercase' );
 * ```
 */
UE.commands['touppercase'] =
UE.commands['tolowercase'] = {
    execCommand:function (cmd) {
        var me = this;
        var rng = me.selection.getRange();
        if(rng.collapsed){
            return rng;
        }
        var bk = rng.createBookmark(),
            bkEnd = bk.end,
            filterFn = function( node ) {
                return !domUtils.isBr(node) && !domUtils.isWhitespace( node );
            },
            curNode = domUtils.getNextDomNode( bk.start, false, filterFn );
        while ( curNode && (domUtils.getPosition( curNode, bkEnd ) & domUtils.POSITION_PRECEDING) ) {

            if ( curNode.nodeType == 3 ) {
                curNode.nodeValue = curNode.nodeValue[cmd == 'touppercase' ? 'toUpperCase' : 'toLowerCase']();
            }
            curNode = domUtils.getNextDomNode( curNode, true, filterFn );
            if(curNode === bkEnd){
                break;
            }

        }
        rng.moveToBookmark(bk).select();
    }
};



// plugins/indent.js
/**
 * 首行縮排
 * @file
 * @since 1.2.6.1
 */

/**
 * 縮排
 * @command indent
 * @method execCommand
 * @param { String } cmd 命令字串
 * @example
 * ```javascript
 * editor.execCommand( 'indent' );
 * ```
 */
UE.commands['indent'] = {
    execCommand : function() {
         var me = this,value = me.queryCommandState("indent") ? "0em" : (me.options.indentValue || '2em');
         me.execCommand('Paragraph','p',{style:'text-indent:'+ value});
    },
    queryCommandState : function() {
        var pN = domUtils.filterNodeList(this.selection.getStartElementPath(),'p h1 h2 h3 h4 h5 h6');
        return pN && pN.style.textIndent && parseInt(pN.style.textIndent) ?  1 : 0;
    }

};

// plugins/preview.js
/**
 * 預覽
 * @file
 * @since 1.2.6.1
 */

/**
 * 預覽
 * @command preview
 * @method execCommand
 * @param { String } cmd 命令字串
 * @example
 * ```javascript
 * editor.execCommand( 'preview' );
 * ```
 */
UE.commands['preview'] = {
    execCommand : function(){
        var w = window.open('', '_blank', ''),
            d = w.document;
        d.open();
        d.write('<!DOCTYPE html><html><head><meta charset="utf-8"/><script src="'+this.options.UEDITOR_HOME_URL+'ueditor.parse.js"></script><script>' +
            "setTimeout(function(){uParse('div',{rootPath: '"+ this.options.UEDITOR_HOME_URL +"'})},300)" +
            '</script></head><body><div>'+this.getContent(null,null,true)+'</div></body></html>');
        d.close();
    },
    notNeedUndo : 1
};


// plugins/paragraph.js
/**
 * 段落樣式
 * @file
 * @since 1.2.6.1
 */

/**
 * 段落格式
 * @command paragraph
 * @method execCommand
 * @param { String } cmd 命令字串
 * @param {String}   style               標籤值為：'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'
 * @param {Object}   attrs               標籤的屬性
 * @example
 * ```javascript
 * editor.execCommand( 'Paragraph','h1','{
 *     class:'test'
 * }' );
 * ```
 */

/**
 * 返回選區內節點標籤名
 * @command paragraph
 * @method queryCommandValue
 * @param { String } cmd 命令字串
 * @return { String } 節點標籤名
 * @example
 * ```javascript
 * editor.queryCommandValue( 'Paragraph' );
 * ```
 */

UE.plugins['paragraph'] = function() {
    var me = this,
        block = domUtils.isBlockElm,
        notExchange = ['TD','LI','PRE'],

        doParagraph = function(range,style,attrs,sourceCmdName){
            var bookmark = range.createBookmark(),
                filterFn = function( node ) {
                    return   node.nodeType == 1 ? node.tagName.toLowerCase() != 'br' &&  !domUtils.isBookmarkNode(node) : !domUtils.isWhitespace( node );
                },
                para;

            range.enlarge( true );
            var bookmark2 = range.createBookmark(),
                current = domUtils.getNextDomNode( bookmark2.start, false, filterFn ),
                tmpRange = range.cloneRange(),
                tmpNode;
            while ( current && !(domUtils.getPosition( current, bookmark2.end ) & domUtils.POSITION_FOLLOWING) ) {
                if ( current.nodeType == 3 || !block( current ) ) {
                    tmpRange.setStartBefore( current );
                    while ( current && current !== bookmark2.end && !block( current ) ) {
                        tmpNode = current;
                        current = domUtils.getNextDomNode( current, false, null, function( node ) {
                            return !block( node );
                        } );
                    }
                    tmpRange.setEndAfter( tmpNode );
                    
                    para = range.document.createElement( style );
                    if(attrs){
                        domUtils.setAttributes(para,attrs);
                        if(sourceCmdName && sourceCmdName == 'customstyle' && attrs.style){
                            para.style.cssText = attrs.style;
                        }
                    }
                    para.appendChild( tmpRange.extractContents() );
                    //需要內容佔位
                    if(domUtils.isEmptyNode(para)){
                        domUtils.fillChar(range.document,para);
                        
                    }

                    tmpRange.insertNode( para );

                    var parent = para.parentNode;
                    //如果para上一級是一個block元素且不是body,td就刪除它
                    if ( block( parent ) && !domUtils.isBody( para.parentNode ) && utils.indexOf(notExchange,parent.tagName)==-1) {
                        //儲存dir,style
                        if(!(sourceCmdName && sourceCmdName == 'customstyle')){
                            parent.getAttribute('dir') && para.setAttribute('dir',parent.getAttribute('dir'));
                            //trace:1070
                            parent.style.cssText && (para.style.cssText = parent.style.cssText + ';' + para.style.cssText);
                            //trace:1030
                            parent.style.textAlign && !para.style.textAlign && (para.style.textAlign = parent.style.textAlign);
                            parent.style.textIndent && !para.style.textIndent && (para.style.textIndent = parent.style.textIndent);
                            parent.style.padding && !para.style.padding && (para.style.padding = parent.style.padding);
                        }

                        //trace:1706 選擇的就是h1-6要刪除
                        if(attrs && /h\d/i.test(parent.tagName) && !/h\d/i.test(para.tagName) ){
                            domUtils.setAttributes(parent,attrs);
                            if(sourceCmdName && sourceCmdName == 'customstyle' && attrs.style){
                                parent.style.cssText = attrs.style;
                            }
                            domUtils.remove(para,true);
                            para = parent;
                        }else{
                            domUtils.remove( para.parentNode, true );
                        }

                    }
                    if(  utils.indexOf(notExchange,parent.tagName)!=-1){
                        current = parent;
                    }else{
                       current = para;
                    }


                    current = domUtils.getNextDomNode( current, false, filterFn );
                } else {
                    current = domUtils.getNextDomNode( current, true, filterFn );
                }
            }
            return range.moveToBookmark( bookmark2 ).moveToBookmark( bookmark );
        };
    me.setOpt('paragraph',{'p':'', 'h1':'', 'h2':'', 'h3':'', 'h4':'', 'h5':'', 'h6':''});
    me.commands['paragraph'] = {
        execCommand : function( cmdName, style,attrs,sourceCmdName ) {
            var range = this.selection.getRange();
             //閉合時單獨處理
            if(range.collapsed){
                var txt = this.document.createTextNode('p');
                range.insertNode(txt);
                //去掉冗餘的fillchar
                if(browser.ie){
                    var node = txt.previousSibling;
                    if(node && domUtils.isWhitespace(node)){
                        domUtils.remove(node);
                    }
                    node = txt.nextSibling;
                    if(node && domUtils.isWhitespace(node)){
                        domUtils.remove(node);
                    }
                }

            }
            range = doParagraph(range,style,attrs,sourceCmdName);
            if(txt){
                range.setStartBefore(txt).collapse(true);
                pN = txt.parentNode;

                domUtils.remove(txt);

                if(domUtils.isBlockElm(pN)&&domUtils.isEmptyNode(pN)){
                    domUtils.fillNode(this.document,pN);
                }

            }

            if(browser.gecko && range.collapsed && range.startContainer.nodeType == 1){
                var child = range.startContainer.childNodes[range.startOffset];
                if(child && child.nodeType == 1 && child.tagName.toLowerCase() == style){
                    range.setStart(child,0).collapse(true);
                }
            }
            //trace:1097 原來有true，原因忘了，但去了就不能清除多餘的佔位符了
            range.select();


            return true;
        },
        queryCommandValue : function() {
            var node = domUtils.filterNodeList(this.selection.getStartElementPath(),'p h1 h2 h3 h4 h5 h6');
            return node ? node.tagName.toLowerCase() : '';
        }
    };
};


// plugins/directionality.js
/**
 * 設定文字輸入的方向的外掛
 * @file
 * @since 1.2.6.1
 */
(function() {
    var block = domUtils.isBlockElm ,
        getObj = function(editor){
//            var startNode = editor.selection.getStart(),
//                parents;
//            if ( startNode ) {
//                //查詢所有的是block的父親節點
//                parents = domUtils.findParents( startNode, true, block, true );
//                for ( var i = 0,ci; ci = parents[i++]; ) {
//                    if ( ci.getAttribute( 'dir' ) ) {
//                        return ci;
//                    }
//                }
//            }
            return domUtils.filterNodeList(editor.selection.getStartElementPath(),function(n){return n && n.nodeType == 1 && n.getAttribute('dir')});

        },
        doDirectionality = function(range,editor,forward){
            
            var bookmark,
                filterFn = function( node ) {
                    return   node.nodeType == 1 ? !domUtils.isBookmarkNode(node) : !domUtils.isWhitespace(node);
                },

                obj = getObj( editor );

            if ( obj && range.collapsed ) {
                obj.setAttribute( 'dir', forward );
                return range;
            }
            bookmark = range.createBookmark();
            range.enlarge( true );
            var bookmark2 = range.createBookmark(),
                current = domUtils.getNextDomNode( bookmark2.start, false, filterFn ),
                tmpRange = range.cloneRange(),
                tmpNode;
            while ( current &&  !(domUtils.getPosition( current, bookmark2.end ) & domUtils.POSITION_FOLLOWING) ) {
                if ( current.nodeType == 3 || !block( current ) ) {
                    tmpRange.setStartBefore( current );
                    while ( current && current !== bookmark2.end && !block( current ) ) {
                        tmpNode = current;
                        current = domUtils.getNextDomNode( current, false, null, function( node ) {
                            return !block( node );
                        } );
                    }
                    tmpRange.setEndAfter( tmpNode );
                    var common = tmpRange.getCommonAncestor();
                    if ( !domUtils.isBody( common ) && block( common ) ) {
                        //遍歷到了block節點
                        common.setAttribute( 'dir', forward );
                        current = common;
                    } else {
                        //沒有遍歷到，新增一個block節點
                        var p = range.document.createElement( 'p' );
                        p.setAttribute( 'dir', forward );
                        var frag = tmpRange.extractContents();
                        p.appendChild( frag );
                        tmpRange.insertNode( p );
                        current = p;
                    }

                    current = domUtils.getNextDomNode( current, false, filterFn );
                } else {
                    current = domUtils.getNextDomNode( current, true, filterFn );
                }
            }
            return range.moveToBookmark( bookmark2 ).moveToBookmark( bookmark );
        };

    /**
     * 文字輸入方向
     * @command directionality
     * @method execCommand
     * @param { String } cmdName 命令字串
     * @param { String } forward 傳入'ltr'表示從左向右輸入，傳入'rtl'表示從右向左輸入
     * @example
     * ```javascript
     * editor.execCommand( 'directionality', 'ltr');
     * ```
     */

    /**
     * 查詢當前選區的文字輸入方向
     * @command directionality
     * @method queryCommandValue
     * @param { String } cmdName 命令字串
     * @return { String } 返回'ltr'表示從左向右輸入，返回'rtl'表示從右向左輸入
     * @example
     * ```javascript
     * editor.queryCommandValue( 'directionality');
     * ```
     */
    UE.commands['directionality'] = {
        execCommand : function( cmdName,forward ) {
            var range = this.selection.getRange();
            //閉合時單獨處理
            if(range.collapsed){
                var txt = this.document.createTextNode('d');
                range.insertNode(txt);
            }
            doDirectionality(range,this,forward);
            if(txt){
                range.setStartBefore(txt).collapse(true);
                domUtils.remove(txt);
            }

            range.select();
            return true;
        },
        queryCommandValue : function() {
            var node = getObj(this);
            return node ? node.getAttribute('dir') : 'ltr';
        }
    };
})();



// plugins/horizontal.js
/**
 * 插入分割線外掛
 * @file
 * @since 1.2.6.1
 */

/**
 * 插入分割線
 * @command horizontal
 * @method execCommand
 * @param { String } cmdName 命令字串
 * @example
 * ```javascript
 * editor.execCommand( 'horizontal' );
 * ```
 */
UE.plugins['horizontal'] = function(){
    var me = this;
    me.commands['horizontal'] = {
        execCommand : function( cmdName ) {
            var me = this;
            if(me.queryCommandState(cmdName)!==-1){
                me.execCommand('insertHtml','<hr>');
                var range = me.selection.getRange(),
                    start = range.startContainer;
                if(start.nodeType == 1 && !start.childNodes[range.startOffset] ){

                    var tmp;
                    if(tmp = start.childNodes[range.startOffset - 1]){
                        if(tmp.nodeType == 1 && tmp.tagName == 'HR'){
                            if(me.options.enterTag == 'p'){
                                tmp = me.document.createElement('p');
                                range.insertNode(tmp);
                                range.setStart(tmp,0).setCursor();

                            }else{
                                tmp = me.document.createElement('br');
                                range.insertNode(tmp);
                                range.setStartBefore(tmp).setCursor();
                            }
                        }
                    }

                }
                return true;
            }

        },
        //邊界在table裡不能加分隔線
        queryCommandState : function() {
            return domUtils.filterNodeList(this.selection.getStartElementPath(),'table') ? -1 : 0;
        }
    };
//    me.addListener('delkeyup',function(){
//        var rng = this.selection.getRange();
//        if(browser.ie && browser.version > 8){
//            rng.txtToElmBoundary(true);
//            if(domUtils.isStartInblock(rng)){
//                var tmpNode = rng.startContainer;
//                var pre = tmpNode.previousSibling;
//                if(pre && domUtils.isTagNode(pre,'hr')){
//                    domUtils.remove(pre);
//                    rng.select();
//                    return;
//                }
//            }
//        }
//        if(domUtils.isBody(rng.startContainer)){
//            var hr = rng.startContainer.childNodes[rng.startOffset -1];
//            if(hr && hr.nodeName == 'HR'){
//                var next = hr.nextSibling;
//                if(next){
//                    rng.setStart(next,0)
//                }else if(hr.previousSibling){
//                    rng.setStartAtLast(hr.previousSibling)
//                }else{
//                    var p = this.document.createElement('p');
//                    hr.parentNode.insertBefore(p,hr);
//                    domUtils.fillNode(this.document,p);
//                    rng.setStart(p,0);
//                }
//                domUtils.remove(hr);
//                rng.setCursor(false,true);
//            }
//        }
//    })
    me.addListener('delkeydown',function(name,evt){
        var rng = this.selection.getRange();
        rng.txtToElmBoundary(true);
        if(domUtils.isStartInblock(rng)){
            var tmpNode = rng.startContainer;
            var pre = tmpNode.previousSibling;
            if(pre && domUtils.isTagNode(pre,'hr')){
                domUtils.remove(pre);
                rng.select();
                domUtils.preventDefault(evt);
                return true;

            }
        }

    })
};


// plugins/insertcode.js
/**
 * 插入程式碼外掛
 * @file
 * @since 1.2.6.1
 */

UE.plugins['insertcode'] = function() {
    var me = this;
    me.ready(function(){
        utils.cssRule('pre','pre{margin:.5em 0;padding:.4em .6em;border-radius:8px;background:#f8f8f8;}',
            me.document)
    });
    me.setOpt('insertcode',{
            'as3':'ActionScript3',
            'bash':'Bash/Shell',
            'cpp':'C/C++',
            'css':'Css',
            'cf':'CodeFunction',
            'c#':'C#',
            'delphi':'Delphi',
            'diff':'Diff',
            'erlang':'Erlang',
            'groovy':'Groovy',
            'html':'Html',
            'java':'Java',
            'jfx':'JavaFx',
            'js':'Javascript',
            'pl':'Perl',
            'php':'Php',
            'plain':'Plain Text',
            'ps':'PowerShell',
            'python':'Python',
            'ruby':'Ruby',
            'scala':'Scala',
            'sql':'Sql',
            'vb':'Vb',
            'xml':'Xml'
    });

    /**
     * 插入程式碼
     * @command insertcode
     * @method execCommand
     * @param { String } cmd 命令字串
     * @param { String } lang 插入程式碼的語言
     * @example
     * ```javascript
     * editor.execCommand( 'insertcode', 'javascript' );
     * ```
     */

    /**
     * 如果選區所在位置是插入插入程式碼區域，返回程式碼的語言
     * @command insertcode
     * @method queryCommandValue
     * @param { String } cmd 命令字串
     * @return { String } 返回程式碼的語言
     * @example
     * ```javascript
     * editor.queryCommandValue( 'insertcode' );
     * ```
     */

    me.commands['insertcode'] = {
        execCommand : function(cmd,lang){
            var me = this,
                rng = me.selection.getRange(),
                pre = domUtils.findParentByTagName(rng.startContainer,'pre',true);
            if(pre){
                pre.className = 'brush:'+lang+';toolbar:true;';
            }else{
                var code = '';
                if(rng.collapsed){
                    code = browser.ie && browser.ie11below ? (browser.version <= 8 ? '&nbsp;':''):'<br/>';
                }else{
                    var frag = rng.extractContents();
                    var div = me.document.createElement('div');
                    div.appendChild(frag);

                    utils.each(UE.filterNode(UE.htmlparser(div.innerHTML.replace(/[\r\t]/g,'')),me.options.filterTxtRules).children,function(node){
                        if(browser.ie && browser.ie11below && browser.version > 8){

                            if(node.type =='element'){
                                if(node.tagName == 'br'){
                                    code += '\n'
                                }else if(!dtd.$empty[node.tagName]){
                                    utils.each(node.children,function(cn){
                                        if(cn.type =='element'){
                                            if(cn.tagName == 'br'){
                                                code += '\n'
                                            }else if(!dtd.$empty[node.tagName]){
                                                code += cn.innerText();
                                            }
                                        }else{
                                            code += cn.data
                                        }
                                    })
                                    if(!/\n$/.test(code)){
                                        code += '\n';
                                    }
                                }
                            }else{
                                code += node.data + '\n'
                            }
                            if(!node.nextSibling() && /\n$/.test(code)){
                                code = code.replace(/\n$/,'');
                            }
                        }else{
                            if(browser.ie && browser.ie11below){

                                if(node.type =='element'){
                                    if(node.tagName == 'br'){
                                        code += '<br>'
                                    }else if(!dtd.$empty[node.tagName]){
                                        utils.each(node.children,function(cn){
                                            if(cn.type =='element'){
                                                if(cn.tagName == 'br'){
                                                    code += '<br>'
                                                }else if(!dtd.$empty[node.tagName]){
                                                    code += cn.innerText();
                                                }
                                            }else{
                                                code += cn.data
                                            }
                                        });
                                        if(!/br>$/.test(code)){
                                            code += '<br>';
                                        }
                                    }
                                }else{
                                    code += node.data + '<br>'
                                }
                                if(!node.nextSibling() && /<br>$/.test(code)){
                                    code = code.replace(/<br>$/,'');
                                }

                            }else{
                                code += (node.type == 'element' ? (dtd.$empty[node.tagName] ?  '' : node.innerText()) : node.data);
                                if(!/br\/?\s*>$/.test(code)){
                                    if(!node.nextSibling())
                                        return;
                                    code += '<br>'
                                }
                            }

                        }

                    });
                }
                me.execCommand('inserthtml','<pre id="coder"class="brush:'+lang+';toolbar:true">'+code+'</pre>',true);

                pre = me.document.getElementById('coder');
                domUtils.removeAttributes(pre,'id');
                var tmpNode = pre.previousSibling;

                if(tmpNode && (tmpNode.nodeType == 3 && tmpNode.nodeValue.length == 1 && browser.ie && browser.version == 6 ||  domUtils.isEmptyBlock(tmpNode))){

                    domUtils.remove(tmpNode)
                }
                var rng = me.selection.getRange();
                if(domUtils.isEmptyBlock(pre)){
                    rng.setStart(pre,0).setCursor(false,true)
                }else{
                    rng.selectNodeContents(pre).select()
                }
            }



        },
        queryCommandValue : function(){
            var path = this.selection.getStartElementPath();
            var lang = '';
            utils.each(path,function(node){
                if(node.nodeName =='PRE'){
                    var match = node.className.match(/brush:([^;]+)/);
                    lang = match && match[1] ? match[1] : '';
                    return false;
                }
            });
            return lang;
        }
    };

    me.addInputRule(function(root){
       utils.each(root.getNodesByTagName('pre'),function(pre){
           var brs = pre.getNodesByTagName('br');
           if(brs.length){
               browser.ie && browser.ie11below && browser.version > 8 && utils.each(brs,function(br){
                   var txt = UE.uNode.createText('\n');
                   br.parentNode.insertBefore(txt,br);
                   br.parentNode.removeChild(br);
               });
               return;
            }
           if(browser.ie && browser.ie11below && browser.version > 8)
                return;
            var code = pre.innerText().split(/\n/);
            pre.innerHTML('');
            utils.each(code,function(c){
                if(c.length){
                    pre.appendChild(UE.uNode.createText(c));
                }
                pre.appendChild(UE.uNode.createElement('br'))
            })
       })
    });
    me.addOutputRule(function(root){
        utils.each(root.getNodesByTagName('pre'),function(pre){
            var code = '';
            utils.each(pre.children,function(n){
               if(n.type == 'text'){
                   //在ie下文字內容有可能末尾帶有\n要去掉
                   //trace:3396
                   code += n.data.replace(/[ ]/g,'&nbsp;').replace(/\n$/,'');
               }else{
                   if(n.tagName == 'br'){
                       code  += '\n'
                   }else{
                       code += (!dtd.$empty[n.tagName] ? '' : n.innerText());
                   }

               }

            });

            pre.innerText(code.replace(/(&nbsp;|\n)+$/,''))
        })
    });
    //不需要判斷highlight的command列表
    me.notNeedCodeQuery ={
        help:1,
        undo:1,
        redo:1,
        source:1,
        print:1,
        fullscreen:1,
        preview:1,
        insertparagraph:1,
        elementpath:1,
        insertcode:1,
        inserthtml:1,
        insertpbefore:1,
        insertpafter:1
    };
    //將queyCommamndState重置
    var orgQuery = me.queryCommandState;
    me.queryCommandState = function(cmd){
        var me = this;

        if(!me.notNeedCodeQuery[cmd.toLowerCase()] && me.selection && me.queryCommandValue('insertcode')){
            return -1;
        }
        return UE.Editor.prototype.queryCommandState.apply(this,arguments)
    };
    me.addListener('beforeenterkeydown',function(){
        var rng = me.selection.getRange();
        var pre = domUtils.findParentByTagName(rng.startContainer,'pre',true);
        if(pre){
            me.fireEvent('saveScene');
            if(!rng.collapsed){
               rng.deleteContents();
            }
            if(!browser.ie || browser.ie9above){
                var tmpNode = me.document.createElement('br'),pre;
                rng.insertNode(tmpNode).setStartAfter(tmpNode).collapse(true);
                var next = tmpNode.nextSibling;
                if(!next && (!browser.ie || browser.version > 10)){
                    rng.insertNode(tmpNode.cloneNode(false));
                }else{
                    rng.setStartAfter(tmpNode);
                }
                pre = tmpNode.previousSibling;
                var tmp;
                while(pre ){
                    tmp = pre;
                    pre = pre.previousSibling;
                    if(!pre || pre.nodeName == 'BR'){
                        pre = tmp;
                        break;
                    }
                }
                if(pre){
                    var str = '';
                    while(pre && pre.nodeName != 'BR' &&  new RegExp('^[\\s'+domUtils.fillChar+']*$').test(pre.nodeValue)){
                        str += pre.nodeValue;
                        pre = pre.nextSibling;
                    }
                    if(pre.nodeName != 'BR'){
                        var match = pre.nodeValue.match(new RegExp('^([\\s'+domUtils.fillChar+']+)'));
                        if(match && match[1]){
                            str += match[1]
                        }

                    }
                    if(str){
                        str = me.document.createTextNode(str);
                        rng.insertNode(str).setStartAfter(str);
                    }
                }
                rng.collapse(true).select(true);
            }else{
                if(browser.version > 8){

                    var txt = me.document.createTextNode('\n');
                    var start = rng.startContainer;
                    if(rng.startOffset == 0){
                        var preNode = start.previousSibling;
                        if(preNode){
                            rng.insertNode(txt);
                            var fillchar = me.document.createTextNode(' ');
                            rng.setStartAfter(txt).insertNode(fillchar).setStart(fillchar,0).collapse(true).select(true)
                        }
                    }else{
                        rng.insertNode(txt).setStartAfter(txt);
                        var fillchar = me.document.createTextNode(' ');
                        start = rng.startContainer.childNodes[rng.startOffset];
                        if(start && !/^\n/.test(start.nodeValue)){
                            rng.setStartBefore(txt)
                        }
                        rng.insertNode(fillchar).setStart(fillchar,0).collapse(true).select(true)
                    }

                }else{
                    var tmpNode = me.document.createElement('br');
                    rng.insertNode(tmpNode);
                    rng.insertNode(me.document.createTextNode(domUtils.fillChar));
                    rng.setStartAfter(tmpNode);
                    pre = tmpNode.previousSibling;
                    var tmp;
                    while(pre ){
                        tmp = pre;
                        pre = pre.previousSibling;
                        if(!pre || pre.nodeName == 'BR'){
                            pre = tmp;
                            break;
                        }
                    }
                    if(pre){
                        var str = '';
                        while(pre && pre.nodeName != 'BR' &&  new RegExp('^[ '+domUtils.fillChar+']*$').test(pre.nodeValue)){
                            str += pre.nodeValue;
                            pre = pre.nextSibling;
                        }
                        if(pre.nodeName != 'BR'){
                            var match = pre.nodeValue.match(new RegExp('^([ '+domUtils.fillChar+']+)'));
                            if(match && match[1]){
                                str += match[1]
                            }

                        }

                        str = me.document.createTextNode(str);
                        rng.insertNode(str).setStartAfter(str);
                    }
                    rng.collapse(true).select();
                }


            }
            me.fireEvent('saveScene');
            return true;
        }


    });

    me.addListener('tabkeydown',function(cmd,evt){
        var rng = me.selection.getRange();
        var pre = domUtils.findParentByTagName(rng.startContainer,'pre',true);
        if(pre){
            me.fireEvent('saveScene');
            if(evt.shiftKey){

            }else{
                if(!rng.collapsed){
                    var bk = rng.createBookmark();
                    var start = bk.start.previousSibling;

                    while(start){
                        if(pre.firstChild === start && !domUtils.isBr(start)){
                            pre.insertBefore(me.document.createTextNode('    '),start);

                            break;
                        }
                        if(domUtils.isBr(start)){
                            pre.insertBefore(me.document.createTextNode('    '),start.nextSibling);

                            break;
                        }
                        start = start.previousSibling;
                    }
                    var end = bk.end;
                    start = bk.start.nextSibling;
                    if(pre.firstChild === bk.start){
                        pre.insertBefore(me.document.createTextNode('    '),start.nextSibling)

                    }
                    while(start && start !== end){
                        if(domUtils.isBr(start) && start.nextSibling){
                            if(start.nextSibling === end){
                                break;
                            }
                            pre.insertBefore(me.document.createTextNode('    '),start.nextSibling)
                        }

                        start = start.nextSibling;
                    }
                    rng.moveToBookmark(bk).select();
                }else{
                    var tmpNode = me.document.createTextNode('    ');
                    rng.insertNode(tmpNode).setStartAfter(tmpNode).collapse(true).select(true);
                }
            }


            me.fireEvent('saveScene');
            return true;
        }


    });


    me.addListener('beforeinserthtml',function(evtName,html){
        var me = this,
            rng = me.selection.getRange(),
            pre = domUtils.findParentByTagName(rng.startContainer,'pre',true);
        if(pre){
            if(!rng.collapsed){
                rng.deleteContents()
            }
            var htmlstr = '';
            if(browser.ie && browser.version > 8){

                utils.each(UE.filterNode(UE.htmlparser(html),me.options.filterTxtRules).children,function(node){
                    if(node.type =='element'){
                        if(node.tagName == 'br'){
                            htmlstr += '\n'
                        }else if(!dtd.$empty[node.tagName]){
                            utils.each(node.children,function(cn){
                                if(cn.type =='element'){
                                    if(cn.tagName == 'br'){
                                        htmlstr += '\n'
                                    }else if(!dtd.$empty[node.tagName]){
                                        htmlstr += cn.innerText();
                                    }
                                }else{
                                    htmlstr += cn.data
                                }
                            })
                            if(!/\n$/.test(htmlstr)){
                                htmlstr += '\n';
                            }
                        }
                    }else{
                        htmlstr += node.data + '\n'
                    }
                    if(!node.nextSibling() && /\n$/.test(htmlstr)){
                        htmlstr = htmlstr.replace(/\n$/,'');
                    }
                });
                var tmpNode = me.document.createTextNode(utils.html(htmlstr.replace(/&nbsp;/g,' ')));
                rng.insertNode(tmpNode).selectNode(tmpNode).select();
            }else{
                var frag = me.document.createDocumentFragment();

                utils.each(UE.filterNode(UE.htmlparser(html),me.options.filterTxtRules).children,function(node){
                    if(node.type =='element'){
                        if(node.tagName == 'br'){
                            frag.appendChild(me.document.createElement('br'))
                        }else if(!dtd.$empty[node.tagName]){
                            utils.each(node.children,function(cn){
                                if(cn.type =='element'){
                                    if(cn.tagName == 'br'){

                                        frag.appendChild(me.document.createElement('br'))
                                    }else if(!dtd.$empty[node.tagName]){
                                        frag.appendChild(me.document.createTextNode(utils.html(cn.innerText().replace(/&nbsp;/g,' '))));

                                    }
                                }else{
                                    frag.appendChild(me.document.createTextNode(utils.html( cn.data.replace(/&nbsp;/g,' '))));

                                }
                            })
                            if(frag.lastChild.nodeName != 'BR'){
                                frag.appendChild(me.document.createElement('br'))
                            }
                        }
                    }else{
                        frag.appendChild(me.document.createTextNode(utils.html( node.data.replace(/&nbsp;/g,' '))));
                    }
                    if(!node.nextSibling() && frag.lastChild.nodeName == 'BR'){
                       frag.removeChild(frag.lastChild)
                    }


                });
                rng.insertNode(frag).select();

            }

            return true;
        }
    });
    //方向鍵的處理
    me.addListener('keydown',function(cmd,evt){
        var me = this,keyCode = evt.keyCode || evt.which;
        if(keyCode == 40){
            var rng = me.selection.getRange(),pre,start = rng.startContainer;
            if(rng.collapsed && (pre = domUtils.findParentByTagName(rng.startContainer,'pre',true)) && !pre.nextSibling){
                var last = pre.lastChild
                while(last && last.nodeName == 'BR'){
                    last = last.previousSibling;
                }
                if(last === start || rng.startContainer === pre && rng.startOffset == pre.childNodes.length){
                    me.execCommand('insertparagraph');
                    domUtils.preventDefault(evt)
                }

            }
        }
    });
    //trace:3395
    me.addListener('delkeydown',function(type,evt){
        var rng = this.selection.getRange();
        rng.txtToElmBoundary(true);
        var start = rng.startContainer;
        if(domUtils.isTagNode(start,'pre') && rng.collapsed && domUtils.isStartInblock(rng)){
            var p = me.document.createElement('p');
            domUtils.fillNode(me.document,p);
            start.parentNode.insertBefore(p,start);
            domUtils.remove(start);
            rng.setStart(p,0).setCursor(false,true);
            domUtils.preventDefault(evt);
            return true;
        }
    })
};



// plugins/anchor.js
/**
 * 錨點外掛，為UEditor提供插入錨點支援
 * @file
 * @since 1.2.6.1
 */
UE.plugin.register('anchor', function (){

    return {
        bindEvents:{
            'ready':function(){
                utils.cssRule('anchor',
                    '.anchorclass{background: url(\''
                        + this.options.themePath
                        + this.options.theme +'/images/anchor.gif\') no-repeat scroll left center transparent;cursor: auto;display: inline-block;height: 16px;width: 15px;}',
                    this.document);
            }
        },
       outputRule: function(root){
           utils.each(root.getNodesByTagName('img'),function(a){
               var val;
               if(val = a.getAttr('anchorname')){
                   a.tagName = 'a';
                   a.setAttr({
                       anchorname : '',
                       name : val,
                       'class' : ''
                   })
               }
           })
       },
       inputRule:function(root){
           utils.each(root.getNodesByTagName('a'),function(a){
               var val;
               if((val = a.getAttr('name')) && !a.getAttr('href')){
                   a.tagName = 'img';
                   a.setAttr({
                       anchorname :a.getAttr('name'),
                       'class' : 'anchorclass'
                   });
                   a.setAttr('name')

               }
           })

       },
       commands:{
           /**
            * 插入錨點
            * @command anchor
            * @method execCommand
            * @param { String } cmd 命令字串
            * @param { String } name 錨點名稱字串
            * @example
            * ```javascript
            * //editor 是編輯器例項
            * editor.execCommand('anchor', 'anchor1');
            * ```
            */
           'anchor':{
               execCommand:function (cmd, name) {
                   var range = this.selection.getRange(),img = range.getClosedNode();
                   if (img && img.getAttribute('anchorname')) {
                       if (name) {
                           img.setAttribute('anchorname', name);
                       } else {
                           range.setStartBefore(img).setCursor();
                           domUtils.remove(img);
                       }
                   } else {
                       if (name) {
                           //只在選區的開始插入
                           var anchor = this.document.createElement('img');
                           range.collapse(true);
                           domUtils.setAttributes(anchor,{
                               'anchorname':name,
                               'class':'anchorclass'
                           });
                           range.insertNode(anchor).setStartAfter(anchor).setCursor(false,true);
                       }
                   }
               }
           }
       }
    }
});


// plugins/pagebreak.js
/**
 * 分頁功能外掛
 * @file
 * @since 1.2.6.1
 */
UE.plugins['pagebreak'] = function () {
    var me = this,
        notBreakTags = ['td'];
    me.setOpt('pageBreakTag','_ueditor_page_break_tag_');

    function fillNode(node){
        if(domUtils.isEmptyBlock(node)){
            var firstChild = node.firstChild,tmpNode;

            while(firstChild && firstChild.nodeType == 1 && domUtils.isEmptyBlock(firstChild)){
                tmpNode = firstChild;
                firstChild = firstChild.firstChild;
            }
            !tmpNode && (tmpNode = node);
            domUtils.fillNode(me.document,tmpNode);
        }
    }
    //分頁符樣式新增

    me.ready(function(){
        utils.cssRule('pagebreak','.pagebreak{display:block;clear:both !important;cursor:default !important;width: 100% !important;margin:0;}',me.document);
    });
    function isHr(node){
        return node && node.nodeType == 1 && node.tagName == 'HR' && node.className == 'pagebreak';
    }
    me.addInputRule(function(root){
        root.traversal(function(node){
            if(node.type == 'text' && node.data == me.options.pageBreakTag){
                var hr = UE.uNode.createElement('<hr class="pagebreak" noshade="noshade" size="5" style="-webkit-user-select: none;">');
                node.parentNode.insertBefore(hr,node);
                node.parentNode.removeChild(node)
            }
        })
    });
    me.addOutputRule(function(node){
        utils.each(node.getNodesByTagName('hr'),function(n){
            if(n.getAttr('class') == 'pagebreak'){
                var txt = UE.uNode.createText(me.options.pageBreakTag);
                n.parentNode.insertBefore(txt,n);
                n.parentNode.removeChild(n);
            }
        })

    });

    /**
     * 插入分頁符
     * @command pagebreak
     * @method execCommand
     * @param { String } cmd 命令字串
     * @remind 在表格中插入分頁符會把表格切分成兩部分
     * @remind 獲取編輯器內的資料時， 編輯器會把分頁符轉換成“_ueditor_page_break_tag_”字串，
     *          以便於提交資料到伺服器端後處理分頁。
     * @example
     * ```javascript
     * editor.execCommand( 'pagebreak'); //插入一個hr標籤，帶有樣式類名pagebreak
     * ```
     */

    me.commands['pagebreak'] = {
        execCommand:function () {
            var range = me.selection.getRange(),hr = me.document.createElement('hr');
            domUtils.setAttributes(hr,{
                'class' : 'pagebreak',
                noshade:"noshade",
                size:"5"
            });
            domUtils.unSelectable(hr);
            //table單獨處理
            var node = domUtils.findParentByTagName(range.startContainer, notBreakTags, true),

                parents = [], pN;
            if (node) {
                switch (node.tagName) {
                    case 'TD':
                        pN = node.parentNode;
                        if (!pN.previousSibling) {
                            var table = domUtils.findParentByTagName(pN, 'table');
//                            var tableWrapDiv = table.parentNode;
//                            if(tableWrapDiv && tableWrapDiv.nodeType == 1
//                                && tableWrapDiv.tagName == 'DIV'
//                                && tableWrapDiv.getAttribute('dropdrag')
//                                ){
//                                domUtils.remove(tableWrapDiv,true);
//                            }
                            table.parentNode.insertBefore(hr, table);
                            parents = domUtils.findParents(hr, true);

                        } else {
                            pN.parentNode.insertBefore(hr, pN);
                            parents = domUtils.findParents(hr);

                        }
                        pN = parents[1];
                        if (hr !== pN) {
                            domUtils.breakParent(hr, pN);

                        }
                        //table要重寫繫結一下拖拽
                        me.fireEvent('afteradjusttable',me.document);
                }

            } else {

                if (!range.collapsed) {
                    range.deleteContents();
                    var start = range.startContainer;
                    while ( !domUtils.isBody(start) && domUtils.isBlockElm(start) && domUtils.isEmptyNode(start)) {
                        range.setStartBefore(start).collapse(true);
                        domUtils.remove(start);
                        start = range.startContainer;
                    }

                }
                range.insertNode(hr);

                var pN = hr.parentNode, nextNode;
                while (!domUtils.isBody(pN)) {
                    domUtils.breakParent(hr, pN);
                    nextNode = hr.nextSibling;
                    if (nextNode && domUtils.isEmptyBlock(nextNode)) {
                        domUtils.remove(nextNode);
                    }
                    pN = hr.parentNode;
                }
                nextNode = hr.nextSibling;
                var pre = hr.previousSibling;
                if(isHr(pre)){
                    domUtils.remove(pre);
                }else{
                    pre && fillNode(pre);
                }

                if(!nextNode){
                    var p = me.document.createElement('p');

                    hr.parentNode.appendChild(p);
                    domUtils.fillNode(me.document,p);
                    range.setStart(p,0).collapse(true);
                }else{
                    if(isHr(nextNode)){
                        domUtils.remove(nextNode);
                    }else{
                        fillNode(nextNode);
                    }
                    range.setEndAfter(hr).collapse(false);
                }

                range.select(true);

            }

        }
    };
};


// plugins/dragdrop.js
UE.plugins['dragdrop'] = function (){

    var me = this;
    me.ready(function(){
        domUtils.on(this.body,'dragend',function(){
            var rng = me.selection.getRange();
            var node = rng.getClosedNode()||me.selection.getStart();

            if(node && node.tagName == 'IMG'){

                var pre = node.previousSibling,next;
                while(next = node.nextSibling){
                    if(next.nodeType == 1 && next.tagName == 'SPAN' && !next.firstChild){
                        domUtils.remove(next)
                    }else{
                        break;
                    }
                }


                if((pre && pre.nodeType == 1 && !domUtils.isEmptyBlock(pre) || !pre) && (!next || next && !domUtils.isEmptyBlock(next))){
                    if(pre && pre.tagName == 'P' && !domUtils.isEmptyBlock(pre)){
                        pre.appendChild(node);
                        domUtils.moveChild(next,pre);
                        domUtils.remove(next);
                    }else  if(next && next.tagName == 'P' && !domUtils.isEmptyBlock(next)){
                        next.insertBefore(node,next.firstChild);
                    }

                    if(pre && pre.tagName == 'P' && domUtils.isEmptyBlock(pre)){
                        domUtils.remove(pre)
                    }
                    if(next && next.tagName == 'P' && domUtils.isEmptyBlock(next)){
                        domUtils.remove(next)
                    }
                    rng.selectNode(node).select();
                    me.fireEvent('saveScene');

                }

            }

        })
    });
    me.addListener('keyup', function(type, evt) {
        var keyCode = evt.keyCode || evt.which;
        if (keyCode == 13) {
            var rng = me.selection.getRange(),node;
            if(node = domUtils.findParentByTagName(rng.startContainer,'p',true)){
                if(domUtils.getComputedStyle(node,'text-align') == 'center'){
                    domUtils.removeStyle(node,'text-align')
                }
            }
        }
    })
};


// plugins/undo.js
/**
 * undo redo
 * @file
 * @since 1.2.6.1
 */

/**
 * 撤銷上一次執行的命令
 * @command undo
 * @method execCommand
 * @param { String } cmd 命令字串
 * @example
 * ```javascript
 * editor.execCommand( 'undo' );
 * ```
 */

/**
 * 重做上一次執行的命令
 * @command redo
 * @method execCommand
 * @param { String } cmd 命令字串
 * @example
 * ```javascript
 * editor.execCommand( 'redo' );
 * ```
 */

UE.plugins['undo'] = function () {
    var saveSceneTimer;
    var me = this,
        maxUndoCount = me.options.maxUndoCount || 20,
        maxInputCount = me.options.maxInputCount || 20,
        fillchar = new RegExp(domUtils.fillChar + '|<\/hr>', 'gi');// ie會產生多餘的</hr>
    var noNeedFillCharTags = {
        ol:1,ul:1,table:1,tbody:1,tr:1,body:1
    };
    var orgState = me.options.autoClearEmptyNode;
    function compareAddr(indexA, indexB) {
        if (indexA.length != indexB.length)
            return 0;
        for (var i = 0, l = indexA.length; i < l; i++) {
            if (indexA[i] != indexB[i])
                return 0
        }
        return 1;
    }

    function compareRangeAddress(rngAddrA, rngAddrB) {
        if (rngAddrA.collapsed != rngAddrB.collapsed) {
            return 0;
        }
        if (!compareAddr(rngAddrA.startAddress, rngAddrB.startAddress) || !compareAddr(rngAddrA.endAddress, rngAddrB.endAddress)) {
            return 0;
        }
        return 1;
    }

    function UndoManager() {
        this.list = [];
        this.index = 0;
        this.hasUndo = false;
        this.hasRedo = false;
        this.undo = function () {
            if (this.hasUndo) {
                if (!this.list[this.index - 1] && this.list.length == 1) {
                    this.reset();
                    return;
                }
                while (this.list[this.index].content == this.list[this.index - 1].content) {
                    this.index--;
                    if (this.index == 0) {
                        return this.restore(0);
                    }
                }
                this.restore(--this.index);
            }
        };
        this.redo = function () {
            if (this.hasRedo) {
                while (this.list[this.index].content == this.list[this.index + 1].content) {
                    this.index++;
                    if (this.index == this.list.length - 1) {
                        return this.restore(this.index);
                    }
                }
                this.restore(++this.index);
            }
        };

        this.restore = function () {
            var me = this.editor;
            var scene = this.list[this.index];
            var root = UE.htmlparser(scene.content.replace(fillchar, ''));
            me.options.autoClearEmptyNode = false;
            me.filterInputRule(root);
            me.options.autoClearEmptyNode = orgState;
            //trace:873
            //去掉展位符
            me.document.body.innerHTML = root.toHtml();
            me.fireEvent('afterscencerestore');
            //處理undo後空格不展位的問題
            if (browser.ie) {
                utils.each(domUtils.getElementsByTagName(me.document,'td th caption p'),function(node){
                    if(domUtils.isEmptyNode(node)){
                        domUtils.fillNode(me.document, node);
                    }
                })
            }

            try{
                var rng = new dom.Range(me.document).moveToAddress(scene.address);
                rng.select(noNeedFillCharTags[rng.startContainer.nodeName.toLowerCase()]);
            }catch(e){}

            this.update();
            this.clearKey();
            //不能把自己reset了
            me.fireEvent('reset', true);
        };

        this.getScene = function () {
            var me = this.editor;
            var rng = me.selection.getRange(),
                rngAddress = rng.createAddress(false,true);
            me.fireEvent('beforegetscene');
            var root = UE.htmlparser(me.body.innerHTML);
            me.options.autoClearEmptyNode = false;
            me.filterOutputRule(root);
            me.options.autoClearEmptyNode = orgState;
            var cont = root.toHtml();
            //trace:3461
            //這個會引起回退時導致空格丟失的情況
//            browser.ie && (cont = cont.replace(/>&nbsp;</g, '><').replace(/\s*</g, '<').replace(/>\s*/g, '>'));
            me.fireEvent('aftergetscene');

            return {
                address:rngAddress,
                content:cont
            }
        };
        this.save = function (notCompareRange,notSetCursor) {
            clearTimeout(saveSceneTimer);
            var currentScene = this.getScene(notSetCursor),
                lastScene = this.list[this.index];

            if(lastScene && lastScene.content != currentScene.content){
                me.trigger('contentchange')
            }
            //內容相同位置相同不存
            if (lastScene && lastScene.content == currentScene.content &&
                ( notCompareRange ? 1 : compareRangeAddress(lastScene.address, currentScene.address) )
                ) {
                return;
            }
            this.list = this.list.slice(0, this.index + 1);
            this.list.push(currentScene);
            //如果大於最大數量了，就把最前的剔除
            if (this.list.length > maxUndoCount) {
                this.list.shift();
            }
            this.index = this.list.length - 1;
            this.clearKey();
            //跟新undo/redo狀態
            this.update();

        };
        this.update = function () {
            this.hasRedo = !!this.list[this.index + 1];
            this.hasUndo = !!this.list[this.index - 1];
        };
        this.reset = function () {
            this.list = [];
            this.index = 0;
            this.hasUndo = false;
            this.hasRedo = false;
            this.clearKey();
        };
        this.clearKey = function () {
            keycont = 0;
            lastKeyCode = null;
        };
    }

    me.undoManger = new UndoManager();
    me.undoManger.editor = me;
    function saveScene() {
        this.undoManger.save();
    }

    me.addListener('saveScene', function () {
        var args = Array.prototype.splice.call(arguments,1);
        this.undoManger.save.apply(this.undoManger,args);
    });

//    me.addListener('beforeexeccommand', saveScene);
//    me.addListener('afterexeccommand', saveScene);

    me.addListener('reset', function (type, exclude) {
        if (!exclude) {
            this.undoManger.reset();
        }
    });
    me.commands['redo'] = me.commands['undo'] = {
        execCommand:function (cmdName) {
            this.undoManger[cmdName]();
        },
        queryCommandState:function (cmdName) {
            return this.undoManger['has' + (cmdName.toLowerCase() == 'undo' ? 'Undo' : 'Redo')] ? 0 : -1;
        },
        notNeedUndo:1
    };

    var keys = {
            //  /*Backspace*/ 8:1, /*Delete*/ 46:1,
            /*Shift*/ 16:1, /*Ctrl*/ 17:1, /*Alt*/ 18:1,
            37:1, 38:1, 39:1, 40:1

        },
        keycont = 0,
        lastKeyCode;
    //輸入法狀態下不計算字元數
    var inputType = false;
    me.addListener('ready', function () {
        domUtils.on(this.body, 'compositionstart', function () {
            inputType = true;
        });
        domUtils.on(this.body, 'compositionend', function () {
            inputType = false;
        })
    });
    //快捷鍵
    me.addshortcutkey({
        "Undo":"ctrl+90", //undo
        "Redo":"ctrl+89" //redo

    });
    var isCollapsed = true;
    me.addListener('keydown', function (type, evt) {

        var me = this;
        var keyCode = evt.keyCode || evt.which;
        if (!keys[keyCode] && !evt.ctrlKey && !evt.metaKey && !evt.shiftKey && !evt.altKey) {
            if (inputType)
                return;

            if(!me.selection.getRange().collapsed){
                me.undoManger.save(false,true);
                isCollapsed = false;
                return;
            }
            if (me.undoManger.list.length == 0) {
                me.undoManger.save(true);
            }
            clearTimeout(saveSceneTimer);
            function save(cont){
                cont.undoManger.save(false,true);
                cont.fireEvent('selectionchange');
            }
            saveSceneTimer = setTimeout(function(){
                if(inputType){
                    var interalTimer = setInterval(function(){
                        if(!inputType){
                            save(me);
                            clearInterval(interalTimer)
                        }
                    },300)
                    return;
                }
                save(me);
            },200);

            lastKeyCode = keyCode;
            keycont++;
            if (keycont >= maxInputCount ) {
                save(me)
            }
        }
    });
    me.addListener('keyup', function (type, evt) {
        var keyCode = evt.keyCode || evt.which;
        if (!keys[keyCode] && !evt.ctrlKey && !evt.metaKey && !evt.shiftKey && !evt.altKey) {
            if (inputType)
                return;
            if(!isCollapsed){
                this.undoManger.save(false,true);
                isCollapsed = true;
            }
        }
    });
    //擴充套件例項，新增關閉和開啟命令undo
    me.stopCmdUndo = function(){
        me.__hasEnterExecCommand = true;
    };
    me.startCmdUndo = function(){
        me.__hasEnterExecCommand = false;
    }
};



// plugins/paste.js
///import core
///import plugins/inserthtml.js
///import plugins/undo.js
///import plugins/serialize.js
///commands 貼上
///commandsName  PastePlain
///commandsTitle  純文字貼上模式
/**
 * @description 貼上
 * @author zhanyi
 */
UE.plugins['paste'] = function () {
    function getClipboardData(callback) {
        var doc = this.document;
        if (doc.getElementById('baidu_pastebin')) {
            return;
        }
        var range = this.selection.getRange(),
            bk = range.createBookmark(),
        //建立剪貼的容器div
            pastebin = doc.createElement('div');
        pastebin.id = 'baidu_pastebin';
        // Safari 要求div必須有內容，才能貼上內容進來
        browser.webkit && pastebin.appendChild(doc.createTextNode(domUtils.fillChar + domUtils.fillChar));
        doc.body.appendChild(pastebin);
        //trace:717 隱藏的span不能得到top
        //bk.start.innerHTML = '&nbsp;';
        bk.start.style.display = '';
        pastebin.style.cssText = "position:absolute;width:1px;height:1px;overflow:hidden;left:-1000px;white-space:nowrap;top:" +
            //要在現在游標平行的位置加入，否則會出現跳動的問題
            domUtils.getXY(bk.start).y + 'px';

        range.selectNodeContents(pastebin).select(true);

        setTimeout(function () {
            if (browser.webkit) {
                for (var i = 0, pastebins = doc.querySelectorAll('#baidu_pastebin'), pi; pi = pastebins[i++];) {
                    if (domUtils.isEmptyNode(pi)) {
                        domUtils.remove(pi);
                    } else {
                        pastebin = pi;
                        break;
                    }
                }
            }
            try {
                pastebin.parentNode.removeChild(pastebin);
            } catch (e) {
            }
            range.moveToBookmark(bk).select(true);
            callback(pastebin);
        }, 0);
    }

    var me = this;

    me.setOpt({
        retainOnlyLabelPasted : false
    });

    var txtContent, htmlContent, address;

    function getPureHtml(html){
        return html.replace(/<(\/?)([\w\-]+)([^>]*)>/gi, function (a, b, tagName, attrs) {
            tagName = tagName.toLowerCase();
            if ({img: 1}[tagName]) {
                return a;
            }
            attrs = attrs.replace(/([\w\-]*?)\s*=\s*(("([^"]*)")|('([^']*)')|([^\s>]+))/gi, function (str, atr, val) {
                if ({
                    'src': 1,
                    'href': 1,
                    'name': 1
                }[atr.toLowerCase()]) {
                    return atr + '=' + val + ' '
                }
                return ''
            });
            if ({
                'span': 1,
                'div': 1
            }[tagName]) {
                return ''
            } else {

                return '<' + b + tagName + ' ' + utils.trim(attrs) + '>'
            }

        });
    }
    function filter(div) {
        var html;
        if (div.firstChild) {
            //去掉cut中新增的邊界值
            var nodes = domUtils.getElementsByTagName(div, 'span');
            for (var i = 0, ni; ni = nodes[i++];) {
                if (ni.id == '_baidu_cut_start' || ni.id == '_baidu_cut_end') {
                    domUtils.remove(ni);
                }
            }

            if (browser.webkit) {

                var brs = div.querySelectorAll('div br');
                for (var i = 0, bi; bi = brs[i++];) {
                    var pN = bi.parentNode;
                    if (pN.tagName == 'DIV' && pN.childNodes.length == 1) {
                        pN.innerHTML = '<p><br/></p>';
                        domUtils.remove(pN);
                    }
                }
                var divs = div.querySelectorAll('#baidu_pastebin');
                for (var i = 0, di; di = divs[i++];) {
                    var tmpP = me.document.createElement('p');
                    di.parentNode.insertBefore(tmpP, di);
                    while (di.firstChild) {
                        tmpP.appendChild(di.firstChild);
                    }
                    domUtils.remove(di);
                }

                var metas = div.querySelectorAll('meta');
                for (var i = 0, ci; ci = metas[i++];) {
                    domUtils.remove(ci);
                }

                var brs = div.querySelectorAll('br');
                for (i = 0; ci = brs[i++];) {
                    if (/^apple-/i.test(ci.className)) {
                        domUtils.remove(ci);
                    }
                }
            }
            if (browser.gecko) {
                var dirtyNodes = div.querySelectorAll('[_moz_dirty]');
                for (i = 0; ci = dirtyNodes[i++];) {
                    ci.removeAttribute('_moz_dirty');
                }
            }
            if (!browser.ie) {
                var spans = div.querySelectorAll('span.Apple-style-span');
                for (var i = 0, ci; ci = spans[i++];) {
                    domUtils.remove(ci, true);
                }
            }

            //ie下使用innerHTML會產生多餘的\r\n字元，也會產生&nbsp;這裡過濾掉
            html = div.innerHTML;//.replace(/>(?:(\s|&nbsp;)*?)</g,'><');

            //過濾word貼上過來的冗餘屬性
            html = UE.filterWord(html);
            //取消了忽略空白的第二個引數，貼上過來的有些是有空白的，會被套上相關的標籤
            var root = UE.htmlparser(html);
            //如果給了過濾規則就先進行過濾
            if (me.options.filterRules) {
                UE.filterNode(root, me.options.filterRules);
            }
            //執行預設的處理
            me.filterInputRule(root);
            //針對chrome的處理
            if (browser.webkit) {
                var br = root.lastChild();
                if (br && br.type == 'element' && br.tagName == 'br') {
                    root.removeChild(br)
                }
                utils.each(me.body.querySelectorAll('div'), function (node) {
                    if (domUtils.isEmptyBlock(node)) {
                        domUtils.remove(node,true)
                    }
                })
            }
            html = {'html': root.toHtml()};
            me.fireEvent('beforepaste', html, root);
            //搶了預設的貼上，那後邊的內容就不執行了，比如表格貼上
            if(!html.html){
                return;
            }
            root = UE.htmlparser(html.html,true);
            //如果開啟了純文字模式
            if (me.queryCommandState('pasteplain') === 1) {
                me.execCommand('insertHtml', UE.filterNode(root, me.options.filterTxtRules).toHtml(), true);
            } else {
                //文字模式
                UE.filterNode(root, me.options.filterTxtRules);
                txtContent = root.toHtml();
                //完全模式
                htmlContent = html.html;

                address = me.selection.getRange().createAddress(true);
                me.execCommand('insertHtml', me.getOpt('retainOnlyLabelPasted') === true ?  getPureHtml(htmlContent) : htmlContent, true);
            }
            me.fireEvent("afterpaste", html);
        }
    }

    me.addListener('pasteTransfer', function (cmd, plainType) {

        if (address && txtContent && htmlContent && txtContent != htmlContent) {
            var range = me.selection.getRange();
            range.moveToAddress(address, true);

            if (!range.collapsed) {

                while (!domUtils.isBody(range.startContainer)
                    ) {
                    var start = range.startContainer;
                    if(start.nodeType == 1){
                        start = start.childNodes[range.startOffset];
                        if(!start){
                            range.setStartBefore(range.startContainer);
                            continue;
                        }
                        var pre = start.previousSibling;

                        if(pre && pre.nodeType == 3 && new RegExp('^[\n\r\t '+domUtils.fillChar+']*$').test(pre.nodeValue)){
                            range.setStartBefore(pre)
                        }
                    }
                    if(range.startOffset == 0){
                        range.setStartBefore(range.startContainer);
                    }else{
                        break;
                    }

                }
                while (!domUtils.isBody(range.endContainer)
                    ) {
                    var end = range.endContainer;
                    if(end.nodeType == 1){
                        end = end.childNodes[range.endOffset];
                        if(!end){
                            range.setEndAfter(range.endContainer);
                            continue;
                        }
                        var next = end.nextSibling;
                        if(next && next.nodeType == 3 && new RegExp('^[\n\r\t'+domUtils.fillChar+']*$').test(next.nodeValue)){
                            range.setEndAfter(next)
                        }
                    }
                    if(range.endOffset == range.endContainer[range.endContainer.nodeType == 3 ? 'nodeValue' : 'childNodes'].length){
                        range.setEndAfter(range.endContainer);
                    }else{
                        break;
                    }

                }

            }

            range.deleteContents();
            range.select(true);
            me.__hasEnterExecCommand = true;
            var html = htmlContent;
            if (plainType === 2 ) {
                html = getPureHtml(html);
            } else if (plainType) {
                html = txtContent;
            }
            me.execCommand('inserthtml', html, true);
            me.__hasEnterExecCommand = false;
            var rng = me.selection.getRange();
            while (!domUtils.isBody(rng.startContainer) && !rng.startOffset &&
                rng.startContainer[rng.startContainer.nodeType == 3 ? 'nodeValue' : 'childNodes'].length
                ) {
                rng.setStartBefore(rng.startContainer);
            }
            var tmpAddress = rng.createAddress(true);
            address.endAddress = tmpAddress.startAddress;
        }
    });

    me.addListener('ready', function () {
        domUtils.on(me.body, 'cut', function () {
            var range = me.selection.getRange();
            if (!range.collapsed && me.undoManger) {
                me.undoManger.save();
            }
        });

        //ie下beforepaste在點選右鍵時也會觸發，所以用監控鍵盤才處理
        domUtils.on(me.body, browser.ie || browser.opera ? 'keydown' : 'paste', function (e) {
            if ((browser.ie || browser.opera) && ((!e.ctrlKey && !e.metaKey) || e.keyCode != '86')) {
                return;
            }
            getClipboardData.call(me, function (div) {
                filter(div);
            });
        });

    });

    me.commands['paste'] = {
        execCommand: function (cmd) {
            if (browser.ie) {
                getClipboardData.call(me, function (div) {
                    filter(div);
                });
                me.document.execCommand('paste');
            } else {
                alert(me.getLang('pastemsg'));
            }
        }
    }
};



// plugins/puretxtpaste.js
/**
 * 純文字貼上外掛
 * @file
 * @since 1.2.6.1
 */

UE.plugins['pasteplain'] = function(){
    var me = this;
    me.setOpt({
        'pasteplain':false,
        'filterTxtRules' : function(){
            function transP(node){
                node.tagName = 'p';
                node.setStyle();
            }
            function removeNode(node){
                node.parentNode.removeChild(node,true)
            }
            return {
                //直接刪除及其位元組點內容
                '-' : 'script style object iframe embed input select',
                'p': {$:{}},
                'br':{$:{}},
                div: function (node) {
                    var tmpNode, p = UE.uNode.createElement('p');
                    while (tmpNode = node.firstChild()) {
                        if (tmpNode.type == 'text' || !UE.dom.dtd.$block[tmpNode.tagName]) {
                            p.appendChild(tmpNode);
                        } else {
                            if (p.firstChild()) {
                                node.parentNode.insertBefore(p, node);
                                p = UE.uNode.createElement('p');
                            } else {
                                node.parentNode.insertBefore(tmpNode, node);
                            }
                        }
                    }
                    if (p.firstChild()) {
                        node.parentNode.insertBefore(p, node);
                    }
                    node.parentNode.removeChild(node);
                },
                ol: removeNode,
                ul: removeNode,
                dl:removeNode,
                dt:removeNode,
                dd:removeNode,
                'li':removeNode,
                'caption':transP,
                'th':transP,
                'tr':transP,
                'h1':transP,'h2':transP,'h3':transP,'h4':transP,'h5':transP,'h6':transP,
                'td':function(node){
                        //沒有內容的td直接刪掉
                        var txt = !!node.innerText();
                        if(txt){
                         node.parentNode.insertAfter(UE.uNode.createText(' &nbsp; &nbsp;'),node);
                    }
                    node.parentNode.removeChild(node,node.innerText())
                }
            }
        }()
    });
    //暫時這裡支援一下老版本的屬性
    var pasteplain = me.options.pasteplain;

    /**
     * 啟用或取消純文字貼上模式
     * @command pasteplain
     * @method execCommand
     * @param { String } cmd 命令字串
     * @example
     * ```javascript
     * editor.queryCommandState( 'pasteplain' );
     * ```
     */

    /**
     * 查詢當前是否處於純文字貼上模式
     * @command pasteplain
     * @method queryCommandState
     * @param { String } cmd 命令字串
     * @return { int } 如果處於純文字模式，返回1，否則，返回0
     * @example
     * ```javascript
     * editor.queryCommandState( 'pasteplain' );
     * ```
     */
    me.commands['pasteplain'] = {
        queryCommandState: function (){
            return pasteplain ? 1 : 0;
        },
        execCommand: function (){
            pasteplain = !pasteplain|0;
        },
        notNeedUndo : 1
    };
};

// plugins/list.js
/**
 * 有序列表,無序列表外掛
 * @file
 * @since 1.2.6.1
 */

UE.plugins['list'] = function () {
    var me = this,
        notExchange = {
            'TD':1,
            'PRE':1,
            'BLOCKQUOTE':1
        };
    var customStyle = {
        'cn' : 'cn-1-',
        'cn1' : 'cn-2-',
        'cn2' : 'cn-3-',
        'num':  'num-1-',
        'num1' : 'num-2-',
        'num2' : 'num-3-',
        'dash'  : 'dash',
        'dot':'dot'
    };

    me.setOpt( {
        'autoTransWordToList':false,
        'insertorderedlist':{
            'decimal':'',
            'lower-alpha':'',
            'lower-roman':'',
            'upper-alpha':'',
            'upper-roman':''
        },
        'insertunorderedlist':{
            'circle':'',
            'disc':'',
            'square':''
        },
        listDefaultPaddingLeft : '30',
        listiconpath : 'http://bs.baidu.com/listicon/',
        maxListLevel : -1,//-1不限制
        disablePInList:false
    } );
    function listToArray(list){
        var arr = [];
        for(var p in list){
            arr.push(p)
        }
        return arr;
    }
    var listStyle = {
        'OL':listToArray(me.options.insertorderedlist),
        'UL':listToArray(me.options.insertunorderedlist)
    };
    var liiconpath = me.options.listiconpath;

    //根據使用者配置，調整customStyle
    for(var s in customStyle){
        if(!me.options.insertorderedlist.hasOwnProperty(s) && !me.options.insertunorderedlist.hasOwnProperty(s)){
            delete customStyle[s];
        }
    }

    me.ready(function () {
        var customCss = [];
        for(var p in customStyle){
            if(p == 'dash' || p == 'dot'){
                customCss.push('li.list-' + customStyle[p] + '{background-image:url(' + liiconpath +customStyle[p]+'.gif)}');
                customCss.push('ul.custom_'+p+'{list-style:none;}ul.custom_'+p+' li{background-position:0 3px;background-repeat:no-repeat}');
            }else{
                for(var i= 0;i<99;i++){
                    customCss.push('li.list-' + customStyle[p] + i + '{background-image:url(' + liiconpath + 'list-'+customStyle[p] + i + '.gif)}')
                }
                customCss.push('ol.custom_'+p+'{list-style:none;}ol.custom_'+p+' li{background-position:0 3px;background-repeat:no-repeat}');
            }
            switch(p){
                case 'cn':
                    customCss.push('li.list-'+p+'-paddingleft-1{padding-left:25px}');
                    customCss.push('li.list-'+p+'-paddingleft-2{padding-left:40px}');
                    customCss.push('li.list-'+p+'-paddingleft-3{padding-left:55px}');
                    break;
                case 'cn1':
                    customCss.push('li.list-'+p+'-paddingleft-1{padding-left:30px}');
                    customCss.push('li.list-'+p+'-paddingleft-2{padding-left:40px}');
                    customCss.push('li.list-'+p+'-paddingleft-3{padding-left:55px}');
                    break;
                case 'cn2':
                    customCss.push('li.list-'+p+'-paddingleft-1{padding-left:40px}');
                    customCss.push('li.list-'+p+'-paddingleft-2{padding-left:55px}');
                    customCss.push('li.list-'+p+'-paddingleft-3{padding-left:68px}');
                    break;
                case 'num':
                case 'num1':
                    customCss.push('li.list-'+p+'-paddingleft-1{padding-left:25px}');
                    break;
                case 'num2':
                    customCss.push('li.list-'+p+'-paddingleft-1{padding-left:35px}');
                    customCss.push('li.list-'+p+'-paddingleft-2{padding-left:40px}');
                    break;
                case 'dash':
                    customCss.push('li.list-'+p+'-paddingleft{padding-left:35px}');
                    break;
                case 'dot':
                    customCss.push('li.list-'+p+'-paddingleft{padding-left:20px}');
            }
        }
        customCss.push('.list-paddingleft-1{padding-left:0}');
        customCss.push('.list-paddingleft-2{padding-left:'+me.options.listDefaultPaddingLeft+'px}');
        customCss.push('.list-paddingleft-3{padding-left:'+me.options.listDefaultPaddingLeft*2+'px}');
        //如果不給寬度會在自定應樣式裡出現滾動條
        utils.cssRule('list', 'ol,ul{margin:0;pading:0;'+(browser.ie ? '' : 'width:95%')+'}li{clear:both;}'+customCss.join('\n'), me.document);
    });
    //單獨處理剪下的問題
    me.ready(function(){
        domUtils.on(me.body,'cut',function(){
            setTimeout(function(){
                var rng = me.selection.getRange(),li;
                //trace:3416
                if(!rng.collapsed){
                    if(li = domUtils.findParentByTagName(rng.startContainer,'li',true)){
                        if(!li.nextSibling && domUtils.isEmptyBlock(li)){
                            var pn = li.parentNode,node;
                            if(node = pn.previousSibling){
                                domUtils.remove(pn);
                                rng.setStartAtLast(node).collapse(true);
                                rng.select(true);
                            }else if(node = pn.nextSibling){
                                domUtils.remove(pn);
                                rng.setStartAtFirst(node).collapse(true);
                                rng.select(true);
                            }else{
                                var tmpNode = me.document.createElement('p');
                                domUtils.fillNode(me.document,tmpNode);
                                pn.parentNode.insertBefore(tmpNode,pn);
                                domUtils.remove(pn);
                                rng.setStart(tmpNode,0).collapse(true);
                                rng.select(true);
                            }
                        }
                    }
                }

            })
        })
    });

    function getStyle(node){
        var cls = node.className;
        if(domUtils.hasClass(node,/custom_/)){
            return cls.match(/custom_(\w+)/)[1]
        }
        return domUtils.getStyle(node, 'list-style-type')

    }

    me.addListener('beforepaste',function(type,html){
        var me = this,
            rng = me.selection.getRange(),li;
        var root = UE.htmlparser(html.html,true);
        if(li = domUtils.findParentByTagName(rng.startContainer,'li',true)){
            var list = li.parentNode,tagName = list.tagName == 'OL' ? 'ul':'ol';
            utils.each(root.getNodesByTagName(tagName),function(n){
                n.tagName = list.tagName;
                n.setAttr();
                if(n.parentNode === root){
                    type = getStyle(list) || (list.tagName == 'OL' ? 'decimal' : 'disc')
                }else{
                    var className = n.parentNode.getAttr('class');
                    if(className && /custom_/.test(className)){
                        type = className.match(/custom_(\w+)/)[1]
                    }else{
                        type = n.parentNode.getStyle('list-style-type');
                    }
                    if(!type){
                        type = list.tagName == 'OL' ? 'decimal' : 'disc';
                    }
                }
                var index = utils.indexOf(listStyle[list.tagName], type);
                if(n.parentNode !== root)
                    index = index + 1 == listStyle[list.tagName].length ? 0 : index + 1;
                var currentStyle = listStyle[list.tagName][index];
                if(customStyle[currentStyle]){
                    n.setAttr('class', 'custom_' + currentStyle)

                }else{
                    n.setStyle('list-style-type',currentStyle)
                }
            })

        }

        html.html = root.toHtml();
    });
    //匯出時，去掉p標籤
    me.getOpt('disablePInList') === true && me.addOutputRule(function(root){
        utils.each(root.getNodesByTagName('li'),function(li){
            var newChildrens = [],index=0;
            utils.each(li.children,function(n){
                if(n.tagName == 'p'){
                    var tmpNode;
                    while(tmpNode = n.children.pop()) {
                        newChildrens.splice(index,0,tmpNode);
                        tmpNode.parentNode = li;
                        lastNode = tmpNode;
                    }
                    tmpNode = newChildrens[newChildrens.length-1];
                    if(!tmpNode || tmpNode.type != 'element' || tmpNode.tagName != 'br'){
                        var br = UE.uNode.createElement('br');
                        br.parentNode = li;
                        newChildrens.push(br);
                    }

                    index = newChildrens.length;
                }
            });
            if(newChildrens.length){
                li.children = newChildrens;
            }
        });
    });
    //進入編輯器的li要套p標籤
    me.addInputRule(function(root){
        utils.each(root.getNodesByTagName('li'),function(li){
            var tmpP = UE.uNode.createElement('p');
            for(var i= 0,ci;ci=li.children[i];){
                if(ci.type == 'text' || dtd.p[ci.tagName]){
                    tmpP.appendChild(ci);
                }else{
                    if(tmpP.firstChild()){
                        li.insertBefore(tmpP,ci);
                        tmpP = UE.uNode.createElement('p');
                        i = i + 2;
                    }else{
                        i++;
                    }

                }
            }
            if(tmpP.firstChild() && !tmpP.parentNode || !li.firstChild()){
                li.appendChild(tmpP);
            }
            //trace:3357
            //p不能為空
            if (!tmpP.firstChild()) {
                tmpP.innerHTML(browser.ie ? '&nbsp;' : '<br/>')
            }
            //去掉末尾的空白
            var p = li.firstChild();
            var lastChild = p.lastChild();
            if(lastChild && lastChild.type == 'text' && /^\s*$/.test(lastChild.data)){
                p.removeChild(lastChild)
            }
        });
        if(me.options.autoTransWordToList){
            var orderlisttype = {
                    'num1':/^\d+\)/,
                    'decimal':/^\d+\./,
                    'lower-alpha':/^[a-z]+\)/,
                    'upper-alpha':/^[A-Z]+\./,
                    'cn':/^[\u4E00\u4E8C\u4E09\u56DB\u516d\u4e94\u4e03\u516b\u4e5d]+[\u3001]/,
                    'cn2':/^\([\u4E00\u4E8C\u4E09\u56DB\u516d\u4e94\u4e03\u516b\u4e5d]+\)/
                },
                unorderlisttype = {
                    'square':'n'
                };
            function checkListType(content,container){
                var span = container.firstChild();
                if(span &&  span.type == 'element' && span.tagName == 'span' && /Wingdings|Symbol/.test(span.getStyle('font-family'))){
                    for(var p in unorderlisttype){
                        if(unorderlisttype[p] == span.data){
                            return p
                        }
                    }
                    return 'disc'
                }
                for(var p in orderlisttype){
                    if(orderlisttype[p].test(content)){
                        return p;
                    }
                }

            }
            utils.each(root.getNodesByTagName('p'),function(node){
                if(node.getAttr('class') != 'MsoListParagraph'){
                    return
                }

                //word貼上過來的會帶有margin要去掉,但這樣也可能會誤命中一些央視
                node.setStyle('margin','');
                node.setStyle('margin-left','');
                node.setAttr('class','');

                function appendLi(list,p,type){
                    if(list.tagName == 'ol'){
                        if(browser.ie){
                            var first = p.firstChild();
                            if(first.type =='element' && first.tagName == 'span' && orderlisttype[type].test(first.innerText())){
                                p.removeChild(first);
                            }
                        }else{
                            p.innerHTML(p.innerHTML().replace(orderlisttype[type],''));
                        }
                    }else{
                        p.removeChild(p.firstChild())
                    }

                    var li = UE.uNode.createElement('li');
                    li.appendChild(p);
                    list.appendChild(li);
                }
                var tmp = node,type,cacheNode = node;

                if(node.parentNode.tagName != 'li' && (type = checkListType(node.innerText(),node))){

                    var list = UE.uNode.createElement(me.options.insertorderedlist.hasOwnProperty(type) ? 'ol' : 'ul');
                    if(customStyle[type]){
                        list.setAttr('class','custom_'+type)
                    }else{
                        list.setStyle('list-style-type',type)
                    }
                    while(node && node.parentNode.tagName != 'li' && checkListType(node.innerText(),node)){
                        tmp = node.nextSibling();
                        if(!tmp){
                            node.parentNode.insertBefore(list,node)
                        }
                        appendLi(list,node,type);
                        node = tmp;
                    }
                    if(!list.parentNode && node && node.parentNode){
                        node.parentNode.insertBefore(list,node)
                    }
                }
                var span = cacheNode.firstChild();
                if(span && span.type == 'element' && span.tagName == 'span' && /^\s*(&nbsp;)+\s*$/.test(span.innerText())){
                    span.parentNode.removeChild(span)
                }
            })
        }

    });

    //調整索引標籤
    me.addListener('contentchange',function(){
        adjustListStyle(me.document)
    });

    function adjustListStyle(doc,ignore){
        utils.each(domUtils.getElementsByTagName(doc,'ol ul'),function(node){

            if(!domUtils.inDoc(node,doc))
                return;

            var parent = node.parentNode;
            if(parent.tagName == node.tagName){
                var nodeStyleType = getStyle(node) || (node.tagName == 'OL' ? 'decimal' : 'disc'),
                    parentStyleType = getStyle(parent) || (parent.tagName == 'OL' ? 'decimal' : 'disc');
                if(nodeStyleType == parentStyleType){
                    var styleIndex = utils.indexOf(listStyle[node.tagName], nodeStyleType);
                    styleIndex = styleIndex + 1 == listStyle[node.tagName].length ? 0 : styleIndex + 1;
                    setListStyle(node,listStyle[node.tagName][styleIndex])
                }

            }
            var index = 0,type = 2;
            if( domUtils.hasClass(node,/custom_/)){
                if(!(/[ou]l/i.test(parent.tagName) && domUtils.hasClass(parent,/custom_/))){
                    type = 1;
                }
            }else{
                if(/[ou]l/i.test(parent.tagName) && domUtils.hasClass(parent,/custom_/)){
                    type = 3;
                }
            }

            var style = domUtils.getStyle(node, 'list-style-type');
            style && (node.style.cssText = 'list-style-type:' + style);
            node.className = utils.trim(node.className.replace(/list-paddingleft-\w+/,'')) + ' list-paddingleft-' + type;
            utils.each(domUtils.getElementsByTagName(node,'li'),function(li){
                li.style.cssText && (li.style.cssText = '');
                if(!li.firstChild){
                    domUtils.remove(li);
                    return;
                }
                if(li.parentNode !== node){
                    return;
                }
                index++;
                if(domUtils.hasClass(node,/custom_/) ){
                    var paddingLeft = 1,currentStyle = getStyle(node);
                    if(node.tagName == 'OL'){
                        if(currentStyle){
                            switch(currentStyle){
                                case 'cn' :
                                case 'cn1':
                                case 'cn2':
                                    if(index > 10 && (index % 10 == 0 || index > 10 && index < 20)){
                                        paddingLeft = 2
                                    }else if(index > 20){
                                        paddingLeft = 3
                                    }
                                    break;
                                case 'num2' :
                                    if(index > 9){
                                        paddingLeft = 2
                                    }
                            }
                        }
                        li.className = 'list-'+customStyle[currentStyle]+ index + ' ' + 'list-'+currentStyle+'-paddingleft-' + paddingLeft;
                    }else{
                        li.className = 'list-'+customStyle[currentStyle]  + ' ' + 'list-'+currentStyle+'-paddingleft';
                    }
                }else{
                    li.className = li.className.replace(/list-[\w\-]+/gi,'');
                }
                var className = li.getAttribute('class');
                if(className !== null && !className.replace(/\s/g,'')){
                    domUtils.removeAttributes(li,'class')
                }
            });
            !ignore && adjustList(node,node.tagName.toLowerCase(),getStyle(node)||domUtils.getStyle(node, 'list-style-type'),true);
        })
    }
    function adjustList(list, tag, style,ignoreEmpty) {
        var nextList = list.nextSibling;
        if (nextList && nextList.nodeType == 1 && nextList.tagName.toLowerCase() == tag && (getStyle(nextList) || domUtils.getStyle(nextList, 'list-style-type') || (tag == 'ol' ? 'decimal' : 'disc')) == style) {
            domUtils.moveChild(nextList, list);
            if (nextList.childNodes.length == 0) {
                domUtils.remove(nextList);
            }
        }
        if(nextList && domUtils.isFillChar(nextList)){
            domUtils.remove(nextList);
        }
        var preList = list.previousSibling;
        if (preList && preList.nodeType == 1 && preList.tagName.toLowerCase() == tag && (getStyle(preList) || domUtils.getStyle(preList, 'list-style-type') || (tag == 'ol' ? 'decimal' : 'disc')) == style) {
            domUtils.moveChild(list, preList);
        }
        if(preList && domUtils.isFillChar(preList)){
            domUtils.remove(preList);
        }
        !ignoreEmpty && domUtils.isEmptyBlock(list) && domUtils.remove(list);
        if(getStyle(list)){
            adjustListStyle(list.ownerDocument,true)
        }
    }

    function setListStyle(list,style){
        if(customStyle[style]){
            list.className = 'custom_' + style;
        }
        try{
            domUtils.setStyle(list, 'list-style-type', style);
        }catch(e){}
    }
    function clearEmptySibling(node) {
        var tmpNode = node.previousSibling;
        if (tmpNode && domUtils.isEmptyBlock(tmpNode)) {
            domUtils.remove(tmpNode);
        }
        tmpNode = node.nextSibling;
        if (tmpNode && domUtils.isEmptyBlock(tmpNode)) {
            domUtils.remove(tmpNode);
        }
    }

    me.addListener('keydown', function (type, evt) {
        function preventAndSave() {
            evt.preventDefault ? evt.preventDefault() : (evt.returnValue = false);
            me.fireEvent('contentchange');
            me.undoManger && me.undoManger.save();
        }
        function findList(node,filterFn){
            while(node && !domUtils.isBody(node)){
                if(filterFn(node)){
                    return null
                }
                if(node.nodeType == 1 && /[ou]l/i.test(node.tagName)){
                    return node;
                }
                node = node.parentNode;
            }
            return null;
        }
        var keyCode = evt.keyCode || evt.which;
        if (keyCode == 13 && !evt.shiftKey) {//回車
            var rng = me.selection.getRange(),
                parent = domUtils.findParent(rng.startContainer,function(node){return domUtils.isBlockElm(node)},true),
                li = domUtils.findParentByTagName(rng.startContainer,'li',true);
            if(parent && parent.tagName != 'PRE' && !li){
                var html = parent.innerHTML.replace(new RegExp(domUtils.fillChar, 'g'),'');
                if(/^\s*1\s*\.[^\d]/.test(html)){
                    parent.innerHTML = html.replace(/^\s*1\s*\./,'');
                    rng.setStartAtLast(parent).collapse(true).select();
                    me.__hasEnterExecCommand = true;
                    me.execCommand('insertorderedlist');
                    me.__hasEnterExecCommand = false;
                }
            }
            var range = me.selection.getRange(),
                start = findList(range.startContainer,function (node) {
                    return node.tagName == 'TABLE';
                }),
                end = range.collapsed ? start : findList(range.endContainer,function (node) {
                    return node.tagName == 'TABLE';
                });

            if (start && end && start === end) {

                if (!range.collapsed) {
                    start = domUtils.findParentByTagName(range.startContainer, 'li', true);
                    end = domUtils.findParentByTagName(range.endContainer, 'li', true);
                    if (start && end && start === end) {
                        range.deleteContents();
                        li = domUtils.findParentByTagName(range.startContainer, 'li', true);
                        if (li && domUtils.isEmptyBlock(li)) {

                            pre = li.previousSibling;
                            next = li.nextSibling;
                            p = me.document.createElement('p');

                            domUtils.fillNode(me.document, p);
                            parentList = li.parentNode;
                            if (pre && next) {
                                range.setStart(next, 0).collapse(true).select(true);
                                domUtils.remove(li);

                            } else {
                                if (!pre && !next || !pre) {

                                    parentList.parentNode.insertBefore(p, parentList);


                                } else {
                                    li.parentNode.parentNode.insertBefore(p, parentList.nextSibling);
                                }
                                domUtils.remove(li);
                                if (!parentList.firstChild) {
                                    domUtils.remove(parentList);
                                }
                                range.setStart(p, 0).setCursor();


                            }
                            preventAndSave();
                            return;

                        }
                    } else {
                        var tmpRange = range.cloneRange(),
                            bk = tmpRange.collapse(false).createBookmark();

                        range.deleteContents();
                        tmpRange.moveToBookmark(bk);
                        var li = domUtils.findParentByTagName(tmpRange.startContainer, 'li', true);

                        clearEmptySibling(li);
                        tmpRange.select();
                        preventAndSave();
                        return;
                    }
                }


                li = domUtils.findParentByTagName(range.startContainer, 'li', true);

                if (li) {
                    if (domUtils.isEmptyBlock(li)) {
                        bk = range.createBookmark();
                        var parentList = li.parentNode;
                        if (li !== parentList.lastChild) {
                            domUtils.breakParent(li, parentList);
                            clearEmptySibling(li);
                        } else {

                            parentList.parentNode.insertBefore(li, parentList.nextSibling);
                            if (domUtils.isEmptyNode(parentList)) {
                                domUtils.remove(parentList);
                            }
                        }
                        //巢狀不處理
                        if (!dtd.$list[li.parentNode.tagName]) {

                            if (!domUtils.isBlockElm(li.firstChild)) {
                                p = me.document.createElement('p');
                                li.parentNode.insertBefore(p, li);
                                while (li.firstChild) {
                                    p.appendChild(li.firstChild);
                                }
                                domUtils.remove(li);
                            } else {
                                domUtils.remove(li, true);
                            }
                        }
                        range.moveToBookmark(bk).select();


                    } else {
                        var first = li.firstChild;
                        if (!first || !domUtils.isBlockElm(first)) {
                            var p = me.document.createElement('p');

                            !li.firstChild && domUtils.fillNode(me.document, p);
                            while (li.firstChild) {

                                p.appendChild(li.firstChild);
                            }
                            li.appendChild(p);
                            first = p;
                        }

                        var span = me.document.createElement('span');

                        range.insertNode(span);
                        domUtils.breakParent(span, li);

                        var nextLi = span.nextSibling;
                        first = nextLi.firstChild;

                        if (!first) {
                            p = me.document.createElement('p');

                            domUtils.fillNode(me.document, p);
                            nextLi.appendChild(p);
                            first = p;
                        }
                        if (domUtils.isEmptyNode(first)) {
                            first.innerHTML = '';
                            domUtils.fillNode(me.document, first);
                        }

                        range.setStart(first, 0).collapse(true).shrinkBoundary().select();
                        domUtils.remove(span);
                        var pre = nextLi.previousSibling;
                        if (pre && domUtils.isEmptyBlock(pre)) {
                            pre.innerHTML = '<p></p>';
                            domUtils.fillNode(me.document, pre.firstChild);
                        }

                    }
//                        }
                    preventAndSave();
                }


            }


        }
        if (keyCode == 8) {
            //修中ie中li下的問題
            range = me.selection.getRange();
            if (range.collapsed && domUtils.isStartInblock(range)) {
                tmpRange = range.cloneRange().trimBoundary();
                li = domUtils.findParentByTagName(range.startContainer, 'li', true);
                //要在li的最左邊，才能處理
                if (li && domUtils.isStartInblock(tmpRange)) {
                    start = domUtils.findParentByTagName(range.startContainer, 'p', true);
                    if (start && start !== li.firstChild) {
                        var parentList = domUtils.findParentByTagName(start,['ol','ul']);
                        domUtils.breakParent(start,parentList);
                        clearEmptySibling(start);
                        me.fireEvent('contentchange');
                        range.setStart(start,0).setCursor(false,true);
                        me.fireEvent('saveScene');
                        domUtils.preventDefault(evt);
                        return;
                    }

                    if (li && (pre = li.previousSibling)) {
                        if (keyCode == 46 && li.childNodes.length) {
                            return;
                        }
                        //有可能上邊的兄弟節點是個2級選單，要追加到2級選單的最後的li
                        if (dtd.$list[pre.tagName]) {
                            pre = pre.lastChild;
                        }
                        me.undoManger && me.undoManger.save();
                        first = li.firstChild;
                        if (domUtils.isBlockElm(first)) {
                            if (domUtils.isEmptyNode(first)) {
//                                    range.setEnd(pre, pre.childNodes.length).shrinkBoundary().collapse().select(true);
                                pre.appendChild(first);
                                range.setStart(first, 0).setCursor(false, true);
                                //first不是唯一的節點
                                while (li.firstChild) {
                                    pre.appendChild(li.firstChild);
                                }
                            } else {

                                span = me.document.createElement('span');
                                range.insertNode(span);
                                //判斷pre是否是空的節點,如果是<p><br/></p>型別的空節點，幹掉p標籤防止它佔位
                                if (domUtils.isEmptyBlock(pre)) {
                                    pre.innerHTML = '';
                                }
                                domUtils.moveChild(li, pre);
                                range.setStartBefore(span).collapse(true).select(true);

                                domUtils.remove(span);

                            }
                        } else {
                            if (domUtils.isEmptyNode(li)) {
                                var p = me.document.createElement('p');
                                pre.appendChild(p);
                                range.setStart(p, 0).setCursor();
//                                    range.setEnd(pre, pre.childNodes.length).shrinkBoundary().collapse().select(true);
                            } else {
                                range.setEnd(pre, pre.childNodes.length).collapse().select(true);
                                while (li.firstChild) {
                                    pre.appendChild(li.firstChild);
                                }
                            }
                        }
                        domUtils.remove(li);
                        me.fireEvent('contentchange');
                        me.fireEvent('saveScene');
                        domUtils.preventDefault(evt);
                        return;

                    }
                    //trace:980

                    if (li && !li.previousSibling) {
                        var parentList = li.parentNode;
                        var bk = range.createBookmark();
                        if(domUtils.isTagNode(parentList.parentNode,'ol ul')){
                            parentList.parentNode.insertBefore(li,parentList);
                            if(domUtils.isEmptyNode(parentList)){
                                domUtils.remove(parentList)
                            }
                        }else{

                            while(li.firstChild){
                                parentList.parentNode.insertBefore(li.firstChild,parentList);
                            }

                            domUtils.remove(li);
                            if(domUtils.isEmptyNode(parentList)){
                                domUtils.remove(parentList)
                            }

                        }
                        range.moveToBookmark(bk).setCursor(false,true);
                        me.fireEvent('contentchange');
                        me.fireEvent('saveScene');
                        domUtils.preventDefault(evt);
                        return;

                    }


                }


            }

        }
    });

    me.addListener('keyup',function(type, evt){
        var keyCode = evt.keyCode || evt.which;
        if (keyCode == 8) {
            var rng = me.selection.getRange(),list;
            if(list = domUtils.findParentByTagName(rng.startContainer,['ol', 'ul'],true)){
                adjustList(list,list.tagName.toLowerCase(),getStyle(list)||domUtils.getComputedStyle(list,'list-style-type'),true)
            }
        }
    });
    //處理tab鍵
    me.addListener('tabkeydown',function(){

        var range = me.selection.getRange();

        //控制級數
        function checkLevel(li){
            if(me.options.maxListLevel != -1){
                var level = li.parentNode,levelNum = 0;
                while(/[ou]l/i.test(level.tagName)){
                    levelNum++;
                    level = level.parentNode;
                }
                if(levelNum >= me.options.maxListLevel){
                    return true;
                }
            }
        }
        //只以開始為準
        //todo 後續改進
        var li = domUtils.findParentByTagName(range.startContainer, 'li', true);
        if(li){

            var bk;
            if(range.collapsed){
                if(checkLevel(li))
                    return true;
                var parentLi = li.parentNode,
                    list = me.document.createElement(parentLi.tagName),
                    index = utils.indexOf(listStyle[list.tagName], getStyle(parentLi)||domUtils.getComputedStyle(parentLi, 'list-style-type'));
                index = index + 1 == listStyle[list.tagName].length ? 0 : index + 1;
                var currentStyle = listStyle[list.tagName][index];
                setListStyle(list,currentStyle);
                if(domUtils.isStartInblock(range)){
                    me.fireEvent('saveScene');
                    bk = range.createBookmark();
                    parentLi.insertBefore(list, li);
                    list.appendChild(li);
                    adjustList(list,list.tagName.toLowerCase(),currentStyle);
                    me.fireEvent('contentchange');
                    range.moveToBookmark(bk).select(true);
                    return true;
                }
            }else{
                me.fireEvent('saveScene');
                bk = range.createBookmark();
                for(var i= 0,closeList,parents = domUtils.findParents(li),ci;ci=parents[i++];){
                    if(domUtils.isTagNode(ci,'ol ul')){
                        closeList = ci;
                        break;
                    }
                }
                var current = li;
                if(bk.end){
                    while(current && !(domUtils.getPosition(current, bk.end) & domUtils.POSITION_FOLLOWING)){
                        if(checkLevel(current)){
                            current = domUtils.getNextDomNode(current,false,null,function(node){return node !== closeList});
                            continue;
                        }
                        var parentLi = current.parentNode,
                            list = me.document.createElement(parentLi.tagName),
                            index = utils.indexOf(listStyle[list.tagName], getStyle(parentLi)||domUtils.getComputedStyle(parentLi, 'list-style-type'));
                        var currentIndex = index + 1 == listStyle[list.tagName].length ? 0 : index + 1;
                        var currentStyle = listStyle[list.tagName][currentIndex];
                        setListStyle(list,currentStyle);
                        parentLi.insertBefore(list, current);
                        while(current && !(domUtils.getPosition(current, bk.end) & domUtils.POSITION_FOLLOWING)){
                            li = current.nextSibling;
                            list.appendChild(current);
                            if(!li || domUtils.isTagNode(li,'ol ul')){
                                if(li){
                                    while(li = li.firstChild){
                                        if(li.tagName == 'LI'){
                                            break;
                                        }
                                    }
                                }else{
                                    li = domUtils.getNextDomNode(current,false,null,function(node){return node !== closeList});
                                }
                                break;
                            }
                            current = li;
                        }
                        adjustList(list,list.tagName.toLowerCase(),currentStyle);
                        current = li;
                    }
                }
                me.fireEvent('contentchange');
                range.moveToBookmark(bk).select();
                return true;
            }
        }

    });
    function getLi(start){
        while(start && !domUtils.isBody(start)){
            if(start.nodeName == 'TABLE'){
                return null;
            }
            if(start.nodeName == 'LI'){
                return start
            }
            start = start.parentNode;
        }
    }

    /**
     * 有序列表，與“insertunorderedlist”命令互斥
     * @command insertorderedlist
     * @method execCommand
     * @param { String } command 命令字串
     * @param { String } style 插入的有序列表型別，值為：decimal,lower-alpha,lower-roman,upper-alpha,upper-roman,cn,cn1,cn2,num,num1,num2
     * @example
     * ```javascript
     * editor.execCommand( 'insertorderedlist','decimal');
     * ```
     */
    /**
     * 查詢當前選區內容是否有序列表
     * @command insertorderedlist
     * @method queryCommandState
     * @param { String } cmd 命令字串
     * @return { int } 如果當前選區是有序列表返回1，否則返回0
     * @example
     * ```javascript
     * editor.queryCommandState( 'insertorderedlist' );
     * ```
     */
    /**
     * 查詢當前選區內容是否有序列表
     * @command insertorderedlist
     * @method queryCommandValue
     * @param { String } cmd 命令字串
     * @return { String } 返回當前有序列表的型別，值為null或decimal,lower-alpha,lower-roman,upper-alpha,upper-roman,cn,cn1,cn2,num,num1,num2
     * @example
     * ```javascript
     * editor.queryCommandValue( 'insertorderedlist' );
     * ```
     */

    /**
     * 無序列表，與“insertorderedlist”命令互斥
     * @command insertunorderedlist
     * @method execCommand
     * @param { String } command 命令字串
     * @param { String } style 插入的無序列表型別，值為：circle,disc,square,dash,dot
     * @example
     * ```javascript
     * editor.execCommand( 'insertunorderedlist','circle');
     * ```
     */
    /**
     * 查詢當前是否有word文件貼上進來的圖片
     * @command insertunorderedlist
     * @method insertunorderedlist
     * @param { String } command 命令字串
     * @return { int } 如果當前選區是無序列表返回1，否則返回0
     * @example
     * ```javascript
     * editor.queryCommandState( 'insertunorderedlist' );
     * ```
     */
    /**
     * 查詢當前選區內容是否有序列表
     * @command insertunorderedlist
     * @method queryCommandValue
     * @param { String } command 命令字串
     * @return { String } 返回當前無序列表的型別，值為null或circle,disc,square,dash,dot
     * @example
     * ```javascript
     * editor.queryCommandValue( 'insertunorderedlist' );
     * ```
     */

    me.commands['insertorderedlist'] =
    me.commands['insertunorderedlist'] = {
            execCommand:function (command, style) {

                if (!style) {
                    style = command.toLowerCase() == 'insertorderedlist' ? 'decimal' : 'disc';
                }
                var me = this,
                    range = this.selection.getRange(),
                    filterFn = function (node) {
                        return   node.nodeType == 1 ? node.tagName.toLowerCase() != 'br' : !domUtils.isWhitespace(node);
                    },
                    tag = command.toLowerCase() == 'insertorderedlist' ? 'ol' : 'ul',
                    frag = me.document.createDocumentFragment();
                //去掉是因為會出現選到末尾，導致adjustmentBoundary縮到ol/ul的位置
                //range.shrinkBoundary();//.adjustmentBoundary();
                range.adjustmentBoundary().shrinkBoundary();
                var bko = range.createBookmark(true),
                    start = getLi(me.document.getElementById(bko.start)),
                    modifyStart = 0,
                    end =  getLi(me.document.getElementById(bko.end)),
                    modifyEnd = 0,
                    startParent, endParent,
                    list, tmp;

                if (start || end) {
                    start && (startParent = start.parentNode);
                    if (!bko.end) {
                        end = start;
                    }
                    end && (endParent = end.parentNode);

                    if (startParent === endParent) {
                        while (start !== end) {
                            tmp = start;
                            start = start.nextSibling;
                            if (!domUtils.isBlockElm(tmp.firstChild)) {
                                var p = me.document.createElement('p');
                                while (tmp.firstChild) {
                                    p.appendChild(tmp.firstChild);
                                }
                                tmp.appendChild(p);
                            }
                            frag.appendChild(tmp);
                        }
                        tmp = me.document.createElement('span');
                        startParent.insertBefore(tmp, end);
                        if (!domUtils.isBlockElm(end.firstChild)) {
                            p = me.document.createElement('p');
                            while (end.firstChild) {
                                p.appendChild(end.firstChild);
                            }
                            end.appendChild(p);
                        }
                        frag.appendChild(end);
                        domUtils.breakParent(tmp, startParent);
                        if (domUtils.isEmptyNode(tmp.previousSibling)) {
                            domUtils.remove(tmp.previousSibling);
                        }
                        if (domUtils.isEmptyNode(tmp.nextSibling)) {
                            domUtils.remove(tmp.nextSibling)
                        }
                        var nodeStyle = getStyle(startParent) || domUtils.getComputedStyle(startParent, 'list-style-type') || (command.toLowerCase() == 'insertorderedlist' ? 'decimal' : 'disc');
                        if (startParent.tagName.toLowerCase() == tag && nodeStyle == style) {
                            for (var i = 0, ci, tmpFrag = me.document.createDocumentFragment(); ci = frag.firstChild;) {
                                if(domUtils.isTagNode(ci,'ol ul')){
//                                  刪除時，子列表不處理
//                                  utils.each(domUtils.getElementsByTagName(ci,'li'),function(li){
//                                        while(li.firstChild){
//                                            tmpFrag.appendChild(li.firstChild);
//                                        }
//
//                                    });
                                    tmpFrag.appendChild(ci);
                                }else{
                                    while (ci.firstChild) {

                                        tmpFrag.appendChild(ci.firstChild);
                                        domUtils.remove(ci);
                                    }
                                }

                            }
                            tmp.parentNode.insertBefore(tmpFrag, tmp);
                        } else {
                            list = me.document.createElement(tag);
                            setListStyle(list,style);
                            list.appendChild(frag);
                            tmp.parentNode.insertBefore(list, tmp);
                        }

                        domUtils.remove(tmp);
                        list && adjustList(list, tag, style);
                        range.moveToBookmark(bko).select();
                        return;
                    }
                    //開始
                    if (start) {
                        while (start) {
                            tmp = start.nextSibling;
                            if (domUtils.isTagNode(start, 'ol ul')) {
                                frag.appendChild(start);
                            } else {
                                var tmpfrag = me.document.createDocumentFragment(),
                                    hasBlock = 0;
                                while (start.firstChild) {
                                    if (domUtils.isBlockElm(start.firstChild)) {
                                        hasBlock = 1;
                                    }
                                    tmpfrag.appendChild(start.firstChild);
                                }
                                if (!hasBlock) {
                                    var tmpP = me.document.createElement('p');
                                    tmpP.appendChild(tmpfrag);
                                    frag.appendChild(tmpP);
                                } else {
                                    frag.appendChild(tmpfrag);
                                }
                                domUtils.remove(start);
                            }

                            start = tmp;
                        }
                        startParent.parentNode.insertBefore(frag, startParent.nextSibling);
                        if (domUtils.isEmptyNode(startParent)) {
                            range.setStartBefore(startParent);
                            domUtils.remove(startParent);
                        } else {
                            range.setStartAfter(startParent);
                        }
                        modifyStart = 1;
                    }

                    if (end && domUtils.inDoc(endParent, me.document)) {
                        //結束
                        start = endParent.firstChild;
                        while (start && start !== end) {
                            tmp = start.nextSibling;
                            if (domUtils.isTagNode(start, 'ol ul')) {
                                frag.appendChild(start);
                            } else {
                                tmpfrag = me.document.createDocumentFragment();
                                hasBlock = 0;
                                while (start.firstChild) {
                                    if (domUtils.isBlockElm(start.firstChild)) {
                                        hasBlock = 1;
                                    }
                                    tmpfrag.appendChild(start.firstChild);
                                }
                                if (!hasBlock) {
                                    tmpP = me.document.createElement('p');
                                    tmpP.appendChild(tmpfrag);
                                    frag.appendChild(tmpP);
                                } else {
                                    frag.appendChild(tmpfrag);
                                }
                                domUtils.remove(start);
                            }
                            start = tmp;
                        }
                        var tmpDiv = domUtils.createElement(me.document, 'div', {
                            'tmpDiv':1
                        });
                        domUtils.moveChild(end, tmpDiv);

                        frag.appendChild(tmpDiv);
                        domUtils.remove(end);
                        endParent.parentNode.insertBefore(frag, endParent);
                        range.setEndBefore(endParent);
                        if (domUtils.isEmptyNode(endParent)) {
                            domUtils.remove(endParent);
                        }

                        modifyEnd = 1;
                    }


                }

                if (!modifyStart) {
                    range.setStartBefore(me.document.getElementById(bko.start));
                }
                if (bko.end && !modifyEnd) {
                    range.setEndAfter(me.document.getElementById(bko.end));
                }
                range.enlarge(true, function (node) {
                    return notExchange[node.tagName];
                });

                frag = me.document.createDocumentFragment();

                var bk = range.createBookmark(),
                    current = domUtils.getNextDomNode(bk.start, false, filterFn),
                    tmpRange = range.cloneRange(),
                    tmpNode,
                    block = domUtils.isBlockElm;

                while (current && current !== bk.end && (domUtils.getPosition(current, bk.end) & domUtils.POSITION_PRECEDING)) {

                    if (current.nodeType == 3 || dtd.li[current.tagName]) {
                        if (current.nodeType == 1 && dtd.$list[current.tagName]) {
                            while (current.firstChild) {
                                frag.appendChild(current.firstChild);
                            }
                            tmpNode = domUtils.getNextDomNode(current, false, filterFn);
                            domUtils.remove(current);
                            current = tmpNode;
                            continue;

                        }
                        tmpNode = current;
                        tmpRange.setStartBefore(current);

                        while (current && current !== bk.end && (!block(current) || domUtils.isBookmarkNode(current) )) {
                            tmpNode = current;
                            current = domUtils.getNextDomNode(current, false, null, function (node) {
                                return !notExchange[node.tagName];
                            });
                        }

                        if (current && block(current)) {
                            tmp = domUtils.getNextDomNode(tmpNode, false, filterFn);
                            if (tmp && domUtils.isBookmarkNode(tmp)) {
                                current = domUtils.getNextDomNode(tmp, false, filterFn);
                                tmpNode = tmp;
                            }
                        }
                        tmpRange.setEndAfter(tmpNode);

                        current = domUtils.getNextDomNode(tmpNode, false, filterFn);

                        var li = range.document.createElement('li');

                        li.appendChild(tmpRange.extractContents());
                        if(domUtils.isEmptyNode(li)){
                            var tmpNode = range.document.createElement('p');
                            while(li.firstChild){
                                tmpNode.appendChild(li.firstChild)
                            }
                            li.appendChild(tmpNode);
                        }
                        frag.appendChild(li);
                    } else {
                        current = domUtils.getNextDomNode(current, true, filterFn);
                    }
                }
                range.moveToBookmark(bk).collapse(true);
                list = me.document.createElement(tag);
                setListStyle(list,style);
                list.appendChild(frag);
                range.insertNode(list);
                //當前list上下看能否合併
                adjustList(list, tag, style);
                //去掉冗餘的tmpDiv
                for (var i = 0, ci, tmpDivs = domUtils.getElementsByTagName(list, 'div'); ci = tmpDivs[i++];) {
                    if (ci.getAttribute('tmpDiv')) {
                        domUtils.remove(ci, true)
                    }
                }
                range.moveToBookmark(bko).select();

            },
            queryCommandState:function (command) {
                var tag = command.toLowerCase() == 'insertorderedlist' ? 'ol' : 'ul';
                var path = this.selection.getStartElementPath();
                for(var i= 0,ci;ci = path[i++];){
                    if(ci.nodeName == 'TABLE'){
                        return 0
                    }
                    if(tag == ci.nodeName.toLowerCase()){
                        return 1
                    };
                }
                return 0;

            },
            queryCommandValue:function (command) {
                var tag = command.toLowerCase() == 'insertorderedlist' ? 'ol' : 'ul';
                var path = this.selection.getStartElementPath(),
                    node;
                for(var i= 0,ci;ci = path[i++];){
                    if(ci.nodeName == 'TABLE'){
                        node = null;
                        break;
                    }
                    if(tag == ci.nodeName.toLowerCase()){
                        node = ci;
                        break;
                    };
                }
                return node ? getStyle(node) || domUtils.getComputedStyle(node, 'list-style-type') : null;
            }
        };
};



// plugins/source.js
/**
 * 原始碼編輯外掛
 * @file
 * @since 1.2.6.1
 */

(function (){
    var sourceEditors = {
        textarea: function (editor, holder){
            var textarea = holder.ownerDocument.createElement('textarea');
            textarea.style.cssText = 'position:absolute;resize:none;width:100%;height:100%;border:0;padding:0;margin:0;overflow-y:auto;';
            // todo: IE下只有onresize屬性可用... 很糾結
            if (browser.ie && browser.version < 8) {
                textarea.style.width = holder.offsetWidth + 'px';
                textarea.style.height = holder.offsetHeight + 'px';
                holder.onresize = function (){
                    textarea.style.width = holder.offsetWidth + 'px';
                    textarea.style.height = holder.offsetHeight + 'px';
                };
            }
            holder.appendChild(textarea);
            return {
                setContent: function (content){
                    textarea.value = content;
                },
                getContent: function (){
                    return textarea.value;
                },
                select: function (){
                    var range;
                    if (browser.ie) {
                        range = textarea.createTextRange();
                        range.collapse(true);
                        range.select();
                    } else {
                        //todo: chrome下無法設定焦點
                        textarea.setSelectionRange(0, 0);
                        textarea.focus();
                    }
                },
                dispose: function (){
                    holder.removeChild(textarea);
                    // todo
                    holder.onresize = null;
                    textarea = null;
                    holder = null;
                }
            };
        }
    };

    UE.plugins['source'] = function (){
        var me = this;
        var opt = this.options;
        var sourceMode = false;
        var sourceEditor;
        var orgSetContent;
        opt.sourceEditor = 'textarea';

        me.setOpt({
            sourceEditorFirst:false
        });
        function createSourceEditor(holder){
            return sourceEditors['textarea'](me, holder);
        }

        var bakCssText;
        //解決在原始碼模式下getContent不能得到最新的內容問題
        var oldGetContent,
            bakAddress;

        /**
         * 切換原始碼模式和編輯模式
         * @command source
         * @method execCommand
         * @param { String } cmd 命令字串
         * @example
         * ```javascript
         * editor.execCommand( 'source');
         * ```
         */

        /**
         * 查詢當前編輯區域的狀態是原始碼模式還是視覺化模式
         * @command source
         * @method queryCommandState
         * @param { String } cmd 命令字串
         * @return { int } 如果當前是原始碼編輯模式，返回1，否則返回0
         * @example
         * ```javascript
         * editor.queryCommandState( 'source' );
         * ```
         */

        me.commands['source'] = {
            execCommand: function (){

                sourceMode = !sourceMode;
                if (sourceMode) {
                    bakAddress = me.selection.getRange().createAddress(false,true);
                    me.undoManger && me.undoManger.save(true);
                    if(browser.gecko){
                        me.body.contentEditable = false;
                    }

                    bakCssText = me.iframe.style.cssText;
                    me.iframe.style.cssText += 'position:absolute;left:-32768px;top:-32768px;';


                    me.fireEvent('beforegetcontent');
                    var root = UE.htmlparser(me.body.innerHTML);
                    me.filterOutputRule(root);
                    root.traversal(function (node) {
                        if (node.type == 'element') {
                            switch (node.tagName) {
                                case 'td':
                                case 'th':
                                case 'caption':
                                if(node.children && node.children.length == 1){
                                    if(node.firstChild().tagName == 'br' ){
                                        node.removeChild(node.firstChild())
                                    }
                                };
                                break;
                                case 'pre':
                                    node.innerText(node.innerText().replace(/&nbsp;/g,' '))

                            }
                        }
                    });

                    me.fireEvent('aftergetcontent');

                    var content = root.toHtml(true);

                    sourceEditor = createSourceEditor(me.iframe.parentNode);

                    sourceEditor.setContent(content);

                    orgSetContent = me.setContent;

                    me.setContent = function(html){
                        //這裡暫時不觸發事件，防止報錯
                        var root = UE.htmlparser(html);
                        me.filterInputRule(root);
                        html = root.toHtml();
                        sourceEditor.setContent(html);
                    };

                    setTimeout(function (){
                        sourceEditor.select();
                        me.addListener('fullscreenchanged', function(){
                            try{
                                sourceEditor.getCodeMirror().refresh()
                            }catch(e){}
                        });
                    });

                    //重置getContent，原始碼模式下取值也能是最新的資料
                    oldGetContent = me.getContent;
                    me.getContent = function (){
                        return sourceEditor.getContent() || '<p>' + (browser.ie ? '' : '<br/>')+'</p>';
                    };
                } else {
                    me.iframe.style.cssText = bakCssText;
                    var cont = sourceEditor.getContent() || '<p>' + (browser.ie ? '' : '<br/>')+'</p>';
                    //處理掉block節點前後的空格,有可能會誤命中，暫時不考慮
                    cont = cont.replace(new RegExp('[\\r\\t\\n ]*<\/?(\\w+)\\s*(?:[^>]*)>','g'), function(a,b){
                        if(b && !dtd.$inlineWithA[b.toLowerCase()]){
                            return a.replace(/(^[\n\r\t ]*)|([\n\r\t ]*$)/g,'');
                        }
                        return a.replace(/(^[\n\r\t]*)|([\n\r\t]*$)/g,'')
                    });

                    me.setContent = orgSetContent;

                    me.setContent(cont);
                    sourceEditor.dispose();
                    sourceEditor = null;
                    //還原getContent方法
                    me.getContent = oldGetContent;
                    var first = me.body.firstChild;
                    //trace:1106 都刪除空了，下邊會報錯，所以補充一個p佔位
                    if(!first){
                        me.body.innerHTML = '<p>'+(browser.ie?'':'<br/>')+'</p>';
                        first = me.body.firstChild;
                    }


                    //要在ifm為顯示時ff才能取到selection,否則報錯
                    //這裡不能比較位置了
                    me.undoManger && me.undoManger.save(true);

                    if(browser.gecko){

                        var input = document.createElement('input');
                        input.style.cssText = 'position:absolute;left:0;top:-32768px';

                        document.body.appendChild(input);

                        me.body.contentEditable = false;
                        setTimeout(function(){
                            domUtils.setViewportOffset(input, { left: -32768, top: 0 });
                            input.focus();
                            setTimeout(function(){
                                me.body.contentEditable = true;
                                me.selection.getRange().moveToAddress(bakAddress).select(true);
                                domUtils.remove(input);
                            });

                        });
                    }else{
                        //ie下有可能報錯，比如在程式碼頂頭的情況
                        try{
                            me.selection.getRange().moveToAddress(bakAddress).select(true);
                        }catch(e){}

                    }
                }
                this.fireEvent('sourcemodechanged', sourceMode);
            },
            queryCommandState: function (){
                return sourceMode|0;
            },
            notNeedUndo : 1
        };
        var oldQueryCommandState = me.queryCommandState;

        me.queryCommandState = function (cmdName){
            cmdName = cmdName.toLowerCase();
            if (sourceMode) {
                //原始碼模式下可以開啟的命令
                return cmdName in {
                    'source' : 1,
                    'fullscreen' : 1
                } ? 1 : -1
            }
            return oldQueryCommandState.apply(this, arguments);
        };
    };

})();

// plugins/enterkey.js
///import core
///import plugins/undo.js
///commands 設定回車標籤p或br
///commandsName  EnterKey
///commandsTitle  設定回車標籤p或br
/**
 * @description 處理回車
 * @author zhanyi
 */
UE.plugins['enterkey'] = function() {
    var hTag,
        me = this,
        tag = me.options.enterTag;
    me.addListener('keyup', function(type, evt) {

        var keyCode = evt.keyCode || evt.which;
        if (keyCode == 13) {
            var range = me.selection.getRange(),
                start = range.startContainer,
                doSave;

            //修正在h1-h6裡邊回車後不能巢狀p的問題
            if (!browser.ie) {

                if (/h\d/i.test(hTag)) {
                    if (browser.gecko) {
                        var h = domUtils.findParentByTagName(start, [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6','blockquote','caption','table'], true);
                        if (!h) {
                            me.document.execCommand('formatBlock', false, '<p>');
                            doSave = 1;
                        }
                    } else {
                        //chrome remove div
                        if (start.nodeType == 1) {
                            var tmp = me.document.createTextNode(''),div;
                            range.insertNode(tmp);
                            div = domUtils.findParentByTagName(tmp, 'div', true);
                            if (div) {
                                var p = me.document.createElement('p');
                                while (div.firstChild) {
                                    p.appendChild(div.firstChild);
                                }
                                div.parentNode.insertBefore(p, div);
                                domUtils.remove(div);
                                range.setStartBefore(tmp).setCursor();
                                doSave = 1;
                            }
                            domUtils.remove(tmp);

                        }
                    }

                    if (me.undoManger && doSave) {
                        me.undoManger.save();
                    }
                }
                //沒有站位符，會出現多行的問題
                browser.opera &&  range.select();
            }else{
                me.fireEvent('saveScene',true,true)
            }
        }
    });

    me.addListener('keydown', function(type, evt) {
        var keyCode = evt.keyCode || evt.which;
        if (keyCode == 13) {//回車
            if(me.fireEvent('beforeenterkeydown')){
                domUtils.preventDefault(evt);
                return;
            }
            me.fireEvent('saveScene',true,true);
            hTag = '';


            var range = me.selection.getRange();

            if (!range.collapsed) {
                //跨td不能刪
                var start = range.startContainer,
                    end = range.endContainer,
                    startTd = domUtils.findParentByTagName(start, 'td', true),
                    endTd = domUtils.findParentByTagName(end, 'td', true);
                if (startTd && endTd && startTd !== endTd || !startTd && endTd || startTd && !endTd) {
                    evt.preventDefault ? evt.preventDefault() : ( evt.returnValue = false);
                    return;
                }
            }
            if (tag == 'p') {


                if (!browser.ie) {

                    start = domUtils.findParentByTagName(range.startContainer, ['ol','ul','p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6','blockquote','caption'], true);

                    //opera下執行formatblock會在table的場景下有問題，回車在opera原生支援很好，所以暫時在opera去掉呼叫這個原生的command
                    //trace:2431
                    if (!start && !browser.opera) {

                        me.document.execCommand('formatBlock', false, '<p>');

                        if (browser.gecko) {
                            range = me.selection.getRange();
                            start = domUtils.findParentByTagName(range.startContainer, 'p', true);
                            start && domUtils.removeDirtyAttr(start);
                        }


                    } else {
                        hTag = start.tagName;
                        start.tagName.toLowerCase() == 'p' && browser.gecko && domUtils.removeDirtyAttr(start);
                    }

                }

            } else {
                evt.preventDefault ? evt.preventDefault() : ( evt.returnValue = false);

                if (!range.collapsed) {
                    range.deleteContents();
                    start = range.startContainer;
                    if (start.nodeType == 1 && (start = start.childNodes[range.startOffset])) {
                        while (start.nodeType == 1) {
                            if (dtd.$empty[start.tagName]) {
                                range.setStartBefore(start).setCursor();
                                if (me.undoManger) {
                                    me.undoManger.save();
                                }
                                return false;
                            }
                            if (!start.firstChild) {
                                var br = range.document.createElement('br');
                                start.appendChild(br);
                                range.setStart(start, 0).setCursor();
                                if (me.undoManger) {
                                    me.undoManger.save();
                                }
                                return false;
                            }
                            start = start.firstChild;
                        }
                        if (start === range.startContainer.childNodes[range.startOffset]) {
                            br = range.document.createElement('br');
                            range.insertNode(br).setCursor();

                        } else {
                            range.setStart(start, 0).setCursor();
                        }


                    } else {
                        br = range.document.createElement('br');
                        range.insertNode(br).setStartAfter(br).setCursor();
                    }


                } else {
                    br = range.document.createElement('br');
                    range.insertNode(br);
                    var parent = br.parentNode;
                    if (parent.lastChild === br) {
                        br.parentNode.insertBefore(br.cloneNode(true), br);
                        range.setStartBefore(br);
                    } else {
                        range.setStartAfter(br);
                    }
                    range.setCursor();

                }

            }

        }
    });
};


// plugins/keystrokes.js
/* 處理特殊鍵的相容性問題 */
UE.plugins['keystrokes'] = function() {
    var me = this;
    var collapsed = true;
    me.addListener('keydown', function(type, evt) {
        var keyCode = evt.keyCode || evt.which,
            rng = me.selection.getRange();

        //處理全選的情況
        if(!rng.collapsed && !(evt.ctrlKey || evt.shiftKey || evt.altKey || evt.metaKey) && (keyCode >= 65 && keyCode <=90
            || keyCode >= 48 && keyCode <= 57 ||
            keyCode >= 96 && keyCode <= 111 || {
                    13:1,
                    8:1,
                    46:1
                }[keyCode])
            ){

            var tmpNode = rng.startContainer;
            if(domUtils.isFillChar(tmpNode)){
                rng.setStartBefore(tmpNode)
            }
            tmpNode = rng.endContainer;
            if(domUtils.isFillChar(tmpNode)){
                rng.setEndAfter(tmpNode)
            }
            rng.txtToElmBoundary();
            //結束邊界可能放到了br的前邊，要把br包含進來
            // x[xxx]<br/>
            if(rng.endContainer && rng.endContainer.nodeType == 1){
                tmpNode = rng.endContainer.childNodes[rng.endOffset];
                if(tmpNode && domUtils.isBr(tmpNode)){
                    rng.setEndAfter(tmpNode);
                }
            }
            if(rng.startOffset == 0){
                tmpNode = rng.startContainer;
                if(domUtils.isBoundaryNode(tmpNode,'firstChild') ){
                    tmpNode = rng.endContainer;
                    if(rng.endOffset == (tmpNode.nodeType == 3 ? tmpNode.nodeValue.length : tmpNode.childNodes.length) && domUtils.isBoundaryNode(tmpNode,'lastChild')){
                        me.fireEvent('saveScene');
                        me.body.innerHTML = '<p>'+(browser.ie ? '' : '<br/>')+'</p>';
                        rng.setStart(me.body.firstChild,0).setCursor(false,true);
                        me._selectionChange();
                        return;
                    }
                }
            }
        }

        //處理backspace
        if (keyCode == keymap.Backspace) {
            rng = me.selection.getRange();
            collapsed = rng.collapsed;
            if(me.fireEvent('delkeydown',evt)){
                return;
            }
            var start,end;
            //避免按兩次刪除才能生效的問題
            if(rng.collapsed && rng.inFillChar()){
                start = rng.startContainer;

                if(domUtils.isFillChar(start)){
                    rng.setStartBefore(start).shrinkBoundary(true).collapse(true);
                    domUtils.remove(start)
                }else{
                    start.nodeValue = start.nodeValue.replace(new RegExp('^' + domUtils.fillChar ),'');
                    rng.startOffset--;
                    rng.collapse(true).select(true)
                }
            }

            //解決選中control元素不能刪除的問題
            if (start = rng.getClosedNode()) {
                me.fireEvent('saveScene');
                rng.setStartBefore(start);
                domUtils.remove(start);
                rng.setCursor();
                me.fireEvent('saveScene');
                domUtils.preventDefault(evt);
                return;
            }
            //阻止在table上的刪除
            if (!browser.ie) {
                start = domUtils.findParentByTagName(rng.startContainer, 'table', true);
                end = domUtils.findParentByTagName(rng.endContainer, 'table', true);
                if (start && !end || !start && end || start !== end) {
                    evt.preventDefault();
                    return;
                }
            }

        }
        //處理tab鍵的邏輯
        if (keyCode == keymap.Tab) {
            //不處理以下標籤
            var excludeTagNameForTabKey = {
                'ol' : 1,
                'ul' : 1,
                'table':1
            };
            //處理元件裡的tab按下事件
            if(me.fireEvent('tabkeydown',evt)){
                domUtils.preventDefault(evt);
                return;
            }
            var range = me.selection.getRange();
            me.fireEvent('saveScene');
            for (var i = 0,txt = '',tabSize = me.options.tabSize|| 4,tabNode =  me.options.tabNode || '&nbsp;'; i < tabSize; i++) {
                txt += tabNode;
            }
            var span = me.document.createElement('span');
            span.innerHTML = txt + domUtils.fillChar;
            if (range.collapsed) {
                range.insertNode(span.cloneNode(true).firstChild).setCursor(true);
            } else {
                var filterFn = function(node) {
                    return domUtils.isBlockElm(node) && !excludeTagNameForTabKey[node.tagName.toLowerCase()]

                };
                //普通的情況
                start = domUtils.findParent(range.startContainer, filterFn,true);
                end = domUtils.findParent(range.endContainer, filterFn,true);
                if (start && end && start === end) {
                    range.deleteContents();
                    range.insertNode(span.cloneNode(true).firstChild).setCursor(true);
                } else {
                    var bookmark = range.createBookmark();
                    range.enlarge(true);
                    var bookmark2 = range.createBookmark(),
                        current = domUtils.getNextDomNode(bookmark2.start, false, filterFn);
                    while (current && !(domUtils.getPosition(current, bookmark2.end) & domUtils.POSITION_FOLLOWING)) {
                        current.insertBefore(span.cloneNode(true).firstChild, current.firstChild);
                        current = domUtils.getNextDomNode(current, false, filterFn);
                    }
                    range.moveToBookmark(bookmark2).moveToBookmark(bookmark).select();
                }
            }
            domUtils.preventDefault(evt)
        }
        //trace:1634
        //ff的del鍵在容器空的時候，也會刪除
        if(browser.gecko && keyCode == 46){
            range = me.selection.getRange();
            if(range.collapsed){
                start = range.startContainer;
                if(domUtils.isEmptyBlock(start)){
                    var parent = start.parentNode;
                    while(domUtils.getChildCount(parent) == 1 && !domUtils.isBody(parent)){
                        start = parent;
                        parent = parent.parentNode;
                    }
                    if(start === parent.lastChild)
                        evt.preventDefault();
                    return;
                }
            }
        }
    });
    me.addListener('keyup', function(type, evt) {
        var keyCode = evt.keyCode || evt.which,
            rng,me = this;
        if(keyCode == keymap.Backspace){
            if(me.fireEvent('delkeyup')){
                return;
            }
            rng = me.selection.getRange();
            if(rng.collapsed){
                var tmpNode,
                    autoClearTagName = ['h1','h2','h3','h4','h5','h6'];
                if(tmpNode = domUtils.findParentByTagName(rng.startContainer,autoClearTagName,true)){
                    if(domUtils.isEmptyBlock(tmpNode)){
                        var pre = tmpNode.previousSibling;
                        if(pre && pre.nodeName != 'TABLE'){
                            domUtils.remove(tmpNode);
                            rng.setStartAtLast(pre).setCursor(false,true);
                            return;
                        }else{
                            var next = tmpNode.nextSibling;
                            if(next && next.nodeName != 'TABLE'){
                                domUtils.remove(tmpNode);
                                rng.setStartAtFirst(next).setCursor(false,true);
                                return;
                            }
                        }
                    }
                }
                //處理當刪除到body時，要重新給p標籤展位
                if(domUtils.isBody(rng.startContainer)){
                    var tmpNode = domUtils.createElement(me.document,'p',{
                        'innerHTML' : browser.ie ? domUtils.fillChar : '<br/>'
                    });
                    rng.insertNode(tmpNode).setStart(tmpNode,0).setCursor(false,true);
                }
            }


            //chrome下如果刪除了inline標籤，瀏覽器會有記憶，在輸入文字還是會套上剛才刪除的標籤，所以這裡再選一次就不會了
            if( !collapsed && (rng.startContainer.nodeType == 3 || rng.startContainer.nodeType == 1 && domUtils.isEmptyBlock(rng.startContainer))){
                if(browser.ie){
                    var span = rng.document.createElement('span');
                    rng.insertNode(span).setStartBefore(span).collapse(true);
                    rng.select();
                    domUtils.remove(span)
                }else{
                    rng.select()
                }

            }
        }


    })
};

// plugins/fiximgclick.js
///import core
///commands 修復chrome下圖片不能點選的問題，出現八個角可改變大小
///commandsName  FixImgClick
///commandsTitle  修復chrome下圖片不能點選的問題，出現八個角可改變大小
//修復chrome下圖片不能點選的問題，出現八個角可改變大小

UE.plugins['fiximgclick'] = (function () {

    var elementUpdated = false;
    function Scale() {
        this.editor = null;
        this.resizer = null;
        this.cover = null;
        this.doc = document;
        this.prePos = {x: 0, y: 0};
        this.startPos = {x: 0, y: 0};
    }

    (function () {
        var rect = [
            //[left, top, width, height]
            [0, 0, -1, -1],
            [0, 0, 1, 1],
            [0, 0, -1, -1],
            [0, 0, 1, 1]
        ];

        Scale.prototype = {
            init: function (editor) {
                var me = this;
                me.editor = editor;
                me.startPos = this.prePos = {x: 0, y: 0};
                me.dragId = -1;

                var hands = [],
                    cover = me.cover = document.createElement('div'),
                    resizer = me.resizer = document.createElement('div');

                cover.id = me.editor.ui.id + '_imagescale_cover';
                cover.style.cssText = 'position:absolute;display:none;z-index:' + (me.editor.options.zIndex) + ';filter:alpha(opacity=0); opacity:0;background:#CCC;';
                domUtils.on(cover, 'mousedown click', function () {
                    me.hide();
                });

                for (i = 0; i < 4; i++) {
                    hands.push('<span class="edui-editor-imagescale-hand' + i + '"></span>');
                }
                resizer.id = me.editor.ui.id + '_imagescale';
                resizer.className = 'edui-editor-imagescale';
                resizer.innerHTML = hands.join('');
                resizer.style.cssText += ';display:none;border:1px solid #3b77ff;z-index:' + (me.editor.options.zIndex) + ';';

                me.editor.ui.getDom().appendChild(cover);
                me.editor.ui.getDom().appendChild(resizer);

                me.initStyle();
                me.initEvents();
            },
            initStyle: function () {
                utils.cssRule('imagescale', '.edui-editor-imagescale{display:none;position:absolute;border:1px solid #38B2CE;cursor:hand;-webkit-box-sizing: content-box;-moz-box-sizing: content-box;box-sizing: content-box;}' +
                    '.edui-editor-imagescale span{position:absolute;width:6px;height:6px;overflow:hidden;font-size:0px;display:block;background-color:#3C9DD0;}'
                    + '.edui-editor-imagescale .edui-editor-imagescale-hand0{cursor:nw-resize;top:0;margin-top:-4px;left:0;margin-left:-4px;}'
                    + '.edui-editor-imagescale .edui-editor-imagescale-hand1{cursor:ne-resize;top:0;margin-top:-4px;left:100%;margin-left:-3px;}'
                    + '.edui-editor-imagescale .edui-editor-imagescale-hand2{cursor:sw-resize;top:100%;margin-top:-3px;left:0;margin-left:-4px;}'
                    + '.edui-editor-imagescale .edui-editor-imagescale-hand3{cursor:se-resize;top:100%;margin-top:-3px;left:100%;margin-left:-3px;}');
            },
            initEvents: function () {
                var me = this;

                me.startPos.x = me.startPos.y = 0;
                me.isDraging = false;
            },
            _eventHandler: function (e) {
                var me = this;
                switch (e.type) {
                    case 'mousedown':
                        var hand = e.target || e.srcElement, hand;
                        if (hand.className.indexOf('edui-editor-imagescale-hand') != -1 && me.dragId == -1) {
                            me.dragId = hand.className.slice(-1);
                            me.startPos.x = me.prePos.x = e.clientX;
                            me.startPos.y = me.prePos.y = e.clientY;
                            domUtils.on(me.doc,'mousemove', me.proxy(me._eventHandler, me));
                        }
                        break;
                    case 'mousemove':
                        if (me.dragId != -1) {
                            me.updateContainerStyle(me.dragId, {x: e.clientX - me.prePos.x, y: e.clientY - me.prePos.y});
                            me.prePos.x = e.clientX;
                            me.prePos.y = e.clientY;
                            elementUpdated = true;
                            me.updateTargetElement();
                        }
                        break;
                    case 'mouseup':
                        if (me.dragId != -1) {
                            me.updateContainerStyle(me.dragId, {x: e.clientX - me.prePos.x, y: e.clientY - me.prePos.y});
                            me.updateTargetElement();
                            if (me.target.parentNode) me.attachTo(me.target);
                            me.dragId = -1;
                        }
                        domUtils.un(me.doc,'mousemove', me.proxy(me._eventHandler, me));
                       //修復只是點選挪動點，但沒有改變大小，不應該觸發contentchange
                        if(elementUpdated){
                            elementUpdated = false;
                            me.editor.fireEvent('contentchange');
                        }

                        break;
                    default:
                        break;
                }
            },
            updateTargetElement: function () {
                var me = this;
                domUtils.setStyles(me.target, {
                    'width': me.resizer.style.width,
                    'height': me.resizer.style.height
                });
                me.target.width = parseInt(me.resizer.style.width);
                me.target.height = parseInt(me.resizer.style.height);
                me.attachTo(me.target);
            },
            updateContainerStyle: function (dir, offset) {
                var me = this,
                    dom = me.resizer, tmp;
                var vname = offset.x;
                if(dir != 3 &&  dir != 1){
	                vname == offset.y;
                }
                console.log(vname)

                if (rect[dir][0] != 0) {
                    tmp = parseInt(dom.style.left) + offset.x;
                    dom.style.left = me._validScaledProp('left', tmp) + 'px';
                }
                if (rect[dir][1] != 0) {
                    tmp = parseInt(dom.style.top) + offset.y;
                    dom.style.top = me._validScaledProp('top', tmp) + 'px';
                }
                if (rect[dir][2] != 0) {
                    tmp = dom.clientWidth + rect[dir][2] * vname;
                    dom.style.width = me._validScaledProp('width', tmp) + 'px';
                }
                if (rect[dir][3] != 0) {
                    tmp = dom.clientHeight + rect[dir][3] * vname;
                    dom.style.height = me._validScaledProp('height', tmp) + 'px';
                }
            },
            _validScaledProp: function (prop, value) {
                var ele = this.resizer,
                    wrap = document;

                value = isNaN(value) ? 0 : value;
                switch (prop) {
                    case 'left':
                        return value < 0 ? 0 : (value + ele.clientWidth) > wrap.clientWidth ? wrap.clientWidth - ele.clientWidth : value;
                    case 'top':
                        return value < 0 ? 0 : (value + ele.clientHeight) > wrap.clientHeight ? wrap.clientHeight - ele.clientHeight : value;
                    case 'width':
                        return value <= 0 ? 1 : (value + ele.offsetLeft) > wrap.clientWidth ? wrap.clientWidth - ele.offsetLeft : value;
                    case 'height':
                        return value <= 0 ? 1 : (value + ele.offsetTop) > wrap.clientHeight ? wrap.clientHeight - ele.offsetTop : value;
                }
            },
            hideCover: function () {
	            var me = this;
	            
	            domUtils.setStyles(me.cover,{
		            'width':"0px",
		            'height':'0px',
		            'top':"-9999999px",
		            'left':"-9999999px",
		            "position":"absolute",
		            "z-index":"-1",
		            "display":"none"
	            });
            },
            showCover: function () {
                var me = this,
                    editorPos = domUtils.getXY(me.editor.ui.getDom()),
                    iframePos = domUtils.getXY(me.editor.iframe);

                domUtils.setStyles(me.cover, {
                    'width': me.editor.iframe.offsetWidth + 'px',
                    'height': me.editor.iframe.offsetHeight + 'px',
                    'top': iframePos.y - editorPos.y + 'px',
                    'left': iframePos.x - editorPos.x + 'px',
                    'position': 'absolute',
                    'z-index':me.editor.options.zIndex,
                    'display': ''
                })
            },
            show: function (targetObj) {
                var me = this;
                me.resizer.style.display = 'block';
                if(targetObj) me.attachTo(targetObj);

                domUtils.on(this.resizer, 'mousedown', me.proxy(me._eventHandler, me));
                domUtils.on(me.doc, 'mouseup', me.proxy(me._eventHandler, me));

                me.showCover();
                me.editor.fireEvent('afterscaleshow', me);
                me.editor.fireEvent('saveScene');
            },
            hide: function () {
                var me = this;
                me.hideCover();
                me.resizer.style.display = 'none';
                domUtils.un(me.resizer, 'mousedown', me.proxy(me._eventHandler, me));
                domUtils.un(me.doc, 'mouseup', me.proxy(me._eventHandler, me));
                me.editor.fireEvent('afterscalehide', me);
            },
            proxy: function( fn, context ) {
                return function(e) {
                    return fn.apply( context || this, arguments);
                };
            },
            attachTo: function (targetObj) {
                var me = this,
                    target = me.target = targetObj,
                    resizer = this.resizer,
                    imgPos = domUtils.getXY(target),
                    iframePos = domUtils.getXY(me.editor.iframe),
                    editorPos = domUtils.getXY(resizer.parentNode);

                domUtils.setStyles(resizer, {
                    'width': target.width + 'px',
                    'height': target.height + 'px',
                    'left': iframePos.x + imgPos.x - me.editor.document.body.scrollLeft - editorPos.x - parseInt(resizer.style.borderLeftWidth) + 'px',
                    'top': iframePos.y + imgPos.y - me.editor.document.body.scrollTop - editorPos.y - parseInt(resizer.style.borderTopWidth) + 'px'
                });
            }
        }
    })();

    return function () {
        var me = this,
            imageScale;

        //me.setOpt('imageScaleEnabled', true);
        me.setOpt('imageScaleEnabled', true);

        if (!browser.ie && me.options.imageScaleEnabled) {
            me.addListener('click', function (type, e) {
                var range = me.selection.getRange(),
                    img = range.getClosedNode();
                var phpok_target = e.target;

                if (img && img.tagName == 'IMG' &&  me.body.contentEditable!="false" && phpok_target == img) {

                    if (img.className.indexOf("edui-faked-music") != -1 ||
                        img.getAttribute("anchorname") ||
                        domUtils.hasClass(img, 'loadingclass') ||
                        domUtils.hasClass(img, 'loaderrorclass')) { return }

                    if (!imageScale) {
                        imageScale = new Scale();
                        imageScale.init(me);
                        me.ui.getDom().appendChild(imageScale.resizer);

                        var _keyDownHandler = function (e) {
                            imageScale.hide();
                            if(imageScale.target){
	                            me.selection.getRange().selectNode(imageScale.target).select();
                            }
                        }, _mouseDownHandler = function (e) {
                            var ele = e.target || e.srcElement;
                            if (ele && (ele.className===undefined || ele.className.indexOf('edui-editor-imagescale') == -1)) {
                                _keyDownHandler(e);
                            }
                        }, timer;

                        me.addListener('afterscaleshow', function (e) {
                            me.addListener('beforekeydown', _keyDownHandler);
                            me.addListener('beforemousedown', _mouseDownHandler);
                            domUtils.on(document, 'keydown', _keyDownHandler);
                            domUtils.on(document,'mousedown', _mouseDownHandler);
	                        me.selection.getNative().removeAllRanges();
                        });
                        me.addListener('afterscalehide', function (e) {
                            me.removeListener('beforekeydown', _keyDownHandler);
                            me.removeListener('beforemousedown', _mouseDownHandler);
                            domUtils.un(document, 'keydown', _keyDownHandler);
                            domUtils.un(document,'mousedown', _mouseDownHandler);
                            var target = imageScale.target;
                            if (target.parentNode) {
                                me.selection.getRange().selectNode(target).select();
                                me.focus(true);
                            }
                        });
                        //TODO 有iframe的情況，mousedown不能往下傳。。
                        domUtils.on(imageScale.resizer, 'mousedown', function (e) {
                            me.selection.getNative().removeAllRanges();
                            var ele = e.target || e.srcElement;
                            if (ele && ele.className.indexOf('edui-editor-imagescale-hand') == -1) {
                                timer = setTimeout(function () {
                                    imageScale.hide();
                                    if(imageScale.target){
	                                    me.selection.getRange().selectNode(ele).select();
                                    }
                                }, 200);
                            }
                        });
                        domUtils.on(imageScale.resizer, 'mouseup', function (e) {
                            var ele = e.target || e.srcElement;
                            if (ele && ele.className.indexOf('edui-editor-imagescale-hand') == -1) {
                                clearTimeout(timer);
                            }
                        });
                    }
                    imageScale.show(img);
                } else {
                    if (imageScale && imageScale.resizer.style.display != 'none'){
	                    imageScale.hide();
                    }
                }
            });
        }

        if (browser.webkit) {
            me.addListener('click', function (type, e) {
                if (e.target.tagName == 'IMG' && me.body.contentEditable!="false") {
                    var range = new dom.Range(me.document);
                    range.selectNode(e.target).select();
                    //me.focus(true);
                }
            });
        }
    }
})();

// plugins/autoheight.js
///import core
///commands 當輸入內容超過編輯器高度時，編輯器自動增高
///commandsName  AutoHeight,autoHeightEnabled
///commandsTitle  自動增高
/**
 * @description 自動伸展
 * @author zhanyi
 */
UE.plugins['autoheight'] = function () {
    var me = this;
    //提供開關，就算載入也可以關閉
    me.autoHeightEnabled = me.options.autoHeightEnabled !== false;
    if (!me.autoHeightEnabled) {
        return;
    }

    var bakOverflow,
        lastHeight = 0,
        options = me.options,
        currentHeight,
        timer;

    function adjustHeight() {
        var me = this;
        clearTimeout(timer);
        if(isFullscreen)return;
        if (!me.queryCommandState || me.queryCommandState && me.queryCommandState('source') != 1) {
            timer = setTimeout(function(){

                var node = me.body.lastChild;
                while(node && node.nodeType != 1){
                    node = node.previousSibling;
                }
                if(node && node.nodeType == 1){
                    node.style.clear = 'both';
                    currentHeight = Math.max(domUtils.getXY(node).y + node.offsetHeight + 25 ,Math.max(options.minFrameHeight, options.initialFrameHeight)) ;
                    if (currentHeight != lastHeight) {
                        if (currentHeight !== parseInt(me.iframe.parentNode.style.height)) {
                            me.iframe.parentNode.style.height = currentHeight + 'px';
                        }
                        me.body.style.height = currentHeight + 'px';
                        lastHeight = currentHeight;
                    }
                    domUtils.removeStyle(node,'clear');
                }


            },50)
        }
    }
    var isFullscreen;
    me.addListener('fullscreenchanged',function(cmd,f){
        isFullscreen = f
    });
    me.addListener('destroy', function () {
        me.removeListener('contentchange afterinserthtml keyup mouseup',adjustHeight)
    });
    me.enableAutoHeight = function () {
        var me = this;
        if (!me.autoHeightEnabled) {
            return;
        }
        var doc = me.document;
        me.autoHeightEnabled = true;
        bakOverflow = doc.body.style.overflowY;
        doc.body.style.overflowY = 'hidden';
        me.addListener('contentchange afterinserthtml keyup mouseup',adjustHeight);
        //ff不給事件算得不對

        setTimeout(function () {
            adjustHeight.call(me);
        }, browser.gecko ? 100 : 0);
        me.fireEvent('autoheightchanged', me.autoHeightEnabled);
    };
    me.disableAutoHeight = function () {

        me.body.style.overflowY = bakOverflow || '';

        me.removeListener('contentchange', adjustHeight);
        me.removeListener('keyup', adjustHeight);
        me.removeListener('mouseup', adjustHeight);
        me.autoHeightEnabled = false;
        me.fireEvent('autoheightchanged', me.autoHeightEnabled);
    };

    me.on('setHeight',function(){
        me.disableAutoHeight()
    });
    me.addListener('ready', function () {
        me.enableAutoHeight();
        //trace:1764
        var timer;
        domUtils.on(browser.ie ? me.body : me.document, browser.webkit ? 'dragover' : 'drop', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                //trace:3681
                adjustHeight.call(me);
            }, 100);

        });
        //修復內容過多時，回到頂部，頂部內容被工具欄遮擋問題
        var lastScrollY;
        window.onscroll = function(){
            if(lastScrollY === null){
                lastScrollY = this.scrollY
            }else if(this.scrollY == 0 && lastScrollY != 0){
                me.window.scrollTo(0,0);
                lastScrollY = null;
            }
        }
    });


};

// plugins/autofloat.js
///import core
///commands 懸浮工具欄
///commandsName  AutoFloat,autoFloatEnabled
///commandsTitle  懸浮工具欄
/**
 *  modified by chengchao01
 *  注意： 引入此功能後，在IE6下會將body的背景圖片覆蓋掉！
 */
UE.plugins['autofloat'] = function() {
    var me = this,
        lang = me.getLang();
    me.setOpt({
        topOffset:0
    });
    var optsAutoFloatEnabled = me.options.autoFloatEnabled !== false,
        topOffset = me.options.topOffset;


    //如果不固定toolbar的位置，則直接退出
    if(!optsAutoFloatEnabled){
        return;
    }
    var uiUtils = UE.ui.uiUtils,
        LteIE6 = browser.ie && browser.version <= 6,
        quirks = browser.quirks;

    function checkHasUI(){
        if(!UE.ui){
            alert(lang.autofloatMsg);
            return 0;
        }
        return 1;
    }
    function fixIE6FixedPos(){
        var docStyle = document.body.style;
        docStyle.backgroundImage = 'url("about:blank")';
        docStyle.backgroundAttachment = 'fixed';
    }
    var	bakCssText,
        placeHolder = document.createElement('div'),
        toolbarBox,orgTop,
        getPosition,
        flag =true;   //ie7模式下需要偏移
    function setFloating(){
        var toobarBoxPos = domUtils.getXY(toolbarBox),
            origalFloat = domUtils.getComputedStyle(toolbarBox,'position'),
            origalLeft = domUtils.getComputedStyle(toolbarBox,'left');
        toolbarBox.style.width = toolbarBox.offsetWidth + 'px';
        toolbarBox.style.zIndex = me.options.zIndex * 1 + 1;
        toolbarBox.parentNode.insertBefore(placeHolder, toolbarBox);
        if (LteIE6 || (quirks && browser.ie)) {
            if(toolbarBox.style.position != 'absolute'){
                toolbarBox.style.position = 'absolute';
            }
            toolbarBox.style.top = (document.body.scrollTop||document.documentElement.scrollTop) - orgTop + topOffset  + 'px';
        } else {
            if (browser.ie7Compat && flag) {
                flag = false;
                toolbarBox.style.left =  domUtils.getXY(toolbarBox).x - document.documentElement.getBoundingClientRect().left+2  + 'px';
            }
            if(toolbarBox.style.position != 'fixed'){
                toolbarBox.style.position = 'fixed';
                toolbarBox.style.top = topOffset +"px";
                ((origalFloat == 'absolute' || origalFloat == 'relative') && parseFloat(origalLeft)) && (toolbarBox.style.left = toobarBoxPos.x + 'px');
            }
        }
    }
    function unsetFloating(){
        flag = true;
        if(placeHolder.parentNode){
            placeHolder.parentNode.removeChild(placeHolder);
        }

        toolbarBox.style.cssText = bakCssText;
    }

    function updateFloating(){
        var rect3 = getPosition(me.container);
        var offset=me.options.toolbarTopOffset||0;
        if (rect3.top < 0 && rect3.bottom - toolbarBox.offsetHeight > offset) {
            setFloating();
        }else{
            unsetFloating();
        }
    }
    var defer_updateFloating = utils.defer(function(){
        updateFloating();
    },browser.ie ? 200 : 100,true);

    me.addListener('destroy',function(){
        domUtils.un(window, ['scroll','resize'], updateFloating);
        me.removeListener('keydown', defer_updateFloating);
    });

    me.addListener('ready', function(){
        if(checkHasUI(me)){
            //載入了ui元件，但在new時，沒有載入ui，導致編輯器例項上沒有ui類，所以這裡做判斷
            if(!me.ui){
                return;
            }
            getPosition = uiUtils.getClientRect;
            toolbarBox = me.ui.getDom('toolbarbox');
            orgTop = getPosition(toolbarBox).top;
            bakCssText = toolbarBox.style.cssText;
            placeHolder.style.height = toolbarBox.offsetHeight + 'px';
            if(LteIE6){
                fixIE6FixedPos();
            }
            domUtils.on(window, ['scroll','resize'], updateFloating);
            me.addListener('keydown', defer_updateFloating);

            me.addListener('beforefullscreenchange', function (t, enabled){
                if (enabled) {
                    unsetFloating();
                }
            });
            me.addListener('fullscreenchanged', function (t, enabled){
                if (!enabled) {
                    updateFloating();
                }
            });
            me.addListener('sourcemodechanged', function (t, enabled){
                setTimeout(function (){
                    updateFloating();
                },0);
            });
            me.addListener("clearDoc",function(){
                setTimeout(function(){
                    updateFloating();
                },0);

            })
        }
    });
};

// plugins/video.js
/**
 * video外掛， 為UEditor提供視訊插入支援
 * @file
 * @since 1.2.6.1
 */

UE.plugins['video'] = function (){
    var me =this;

    /**
     * 建立插入視訊字元竄
     * @param url 視訊地址
     * @param width 視訊寬度
     * @param height 視訊高度
     * @param align 視訊對齊
     * @param toEmbed 是否以flash代替顯示
     * @param addParagraph  是否需要新增P 標籤
     */
    function creatInsertStr(url,width,height,id,align,classname,type){

        url = utils.unhtmlForUrl(url);
        align = utils.unhtml(align);
        classname = utils.unhtml(classname);

        width = parseInt(width, 10) || 0;
        height = parseInt(height, 10) || 0;

        var str;
        switch (type){
            case 'image':
                str = '<img ' + (id ? 'id="' + id+'"' : '') + ' width="'+ width +'" height="' + height + '" _url="'+url+'" class="' + classname.replace(/\bvideo-js\b/, '') + '"'  +
                    ' src="' + me.options.UEDITOR_HOME_URL+'themes/default/images/spacer.gif" style="background:url('+me.options.UEDITOR_HOME_URL+'themes/default/images/videologo.gif) no-repeat center center; border:1px solid gray;'+(align ? 'float:' + align + ';': '')+'" />'
                break;
            case 'embed':
                str = '<embed type="application/x-shockwave-flash" class="' + classname + '" pluginspage="http://www.macromedia.com/go/getflashplayer"' +
                    ' src="' +  utils.html(url) + '" width="' + width  + '" height="' + height  + '"'  + (align ? ' style="float:' + align + '"': '') +
                    ' wmode="transparent" play="true" loop="false" menu="false" allowscriptaccess="never" allowfullscreen="true" >';
                break;
            case 'video':
                var ext = url.substr(url.lastIndexOf('.') + 1);
                if(ext == 'ogv') ext = 'ogg';
                str = '<video' + (id ? ' id="' + id + '"' : '') + ' class="' + classname + ' video-js" ' + (align ? ' style="float:' + align + '"': '') +
                    ' controls preload="none" width="' + width + '" height="' + height + '" src="' + url + '" data-setup="{}">' +
                    '<source src="' + url + '" type="video/' + ext + '" /></video>';
                break;
        }
        return str;
    }

    function switchImgAndVideo(root,img2video){
        utils.each(root.getNodesByTagName(img2video ? 'img' : 'embed video'),function(node){
            var className = node.getAttr('class');
            if(className && className.indexOf('edui-faked-video') != -1){
                var html = creatInsertStr( img2video ? node.getAttr('_url') : node.getAttr('src'),node.getAttr('width'),node.getAttr('height'),null,node.getStyle('float') || '',className,img2video ? 'embed':'image');
                node.parentNode.replaceChild(UE.uNode.createElement(html),node);
            }
            if(className && className.indexOf('edui-upload-video') != -1){
                var html = creatInsertStr( img2video ? node.getAttr('_url') : node.getAttr('src'),node.getAttr('width'),node.getAttr('height'),null,node.getStyle('float') || '',className,img2video ? 'video':'image');
                node.parentNode.replaceChild(UE.uNode.createElement(html),node);
            }
        })
    }

    me.addOutputRule(function(root){
        switchImgAndVideo(root,true)
    });
    me.addInputRule(function(root){
        switchImgAndVideo(root)
    });

    /**
     * 插入視訊
     * @command insertvideo
     * @method execCommand
     * @param { String } cmd 命令字串
     * @param { Object } videoAttr 鍵值對物件， 描述一個視訊的所有屬性
     * @example
     * ```javascript
     *
     * var videoAttr = {
     *      //視訊地址
     *      url: 'http://www.youku.com/xxx',
     *      //視訊寬高值， 單位px
     *      width: 200,
     *      height: 100
     * };
     *
     * //editor 是編輯器例項
     * //向編輯器插入單個視訊
     * editor.execCommand( 'insertvideo', videoAttr );
     * ```
     */

    /**
     * 插入視訊
     * @command insertvideo
     * @method execCommand
     * @param { String } cmd 命令字串
     * @param { Array } videoArr 需要插入的視訊的陣列， 其中的每一個元素都是一個鍵值對物件， 描述了一個視訊的所有屬性
     * @example
     * ```javascript
     *
     * var videoAttr1 = {
     *      //視訊地址
     *      url: 'http://www.youku.com/xxx',
     *      //視訊寬高值， 單位px
     *      width: 200,
     *      height: 100
     * },
     * videoAttr2 = {
     *      //視訊地址
     *      url: 'http://www.youku.com/xxx',
     *      //視訊寬高值， 單位px
     *      width: 200,
     *      height: 100
     * }
     *
     * //editor 是編輯器例項
     * //該方法將會向編輯器內插入兩個視訊
     * editor.execCommand( 'insertvideo', [ videoAttr1, videoAttr2 ] );
     * ```
     */

    /**
     * 查詢當前游標所在處是否是一個視訊
     * @command insertvideo
     * @method queryCommandState
     * @param { String } cmd 需要查詢的命令字串
     * @return { int } 如果當前游標所在處的元素是一個視訊物件， 則返回1，否則返回0
     * @example
     * ```javascript
     *
     * //editor 是編輯器例項
     * editor.queryCommandState( 'insertvideo' );
     * ```
     */
    me.commands["insertvideo"] = {
        execCommand: function (cmd, videoObjs, type){
            videoObjs = utils.isArray(videoObjs)?videoObjs:[videoObjs];
            var html = [],id = 'tmpVedio', cl;
            for(var i=0,vi,len = videoObjs.length;i<len;i++){
                vi = videoObjs[i];
                cl = (type == 'upload' ? 'edui-upload-video video-js vjs-default-skin':'edui-faked-video');
                html.push(creatInsertStr( vi.url, vi.width || 420,  vi.height || 280, id + i, null, cl, 'image'));
            }
            me.execCommand("inserthtml",html.join(""),true);
            var rng = this.selection.getRange();
            for(var i= 0,len=videoObjs.length;i<len;i++){
                var img = this.document.getElementById('tmpVedio'+i);
                domUtils.removeAttributes(img,'id');
                rng.selectNode(img).select();
                me.execCommand('imagefloat',videoObjs[i].align)
            }
        },
        queryCommandState : function(){
            var img = me.selection.getRange().getClosedNode(),
                flag = img && (img.className == "edui-faked-video" || img.className.indexOf("edui-upload-video")!=-1);
            return flag ? 1 : 0;
        }
    };
};

// plugins/table.core.js
/**
 * Created with JetBrains WebStorm.
 * User: taoqili
 * Date: 13-1-18
 * Time: 上午11:09
 * To change this template use File | Settings | File Templates.
 */
/**
 * UE表格操作類
 * @param table
 * @constructor
 */
(function () {
    var UETable = UE.UETable = function (table) {
        this.table = table;
        this.indexTable = [];
        this.selectedTds = [];
        this.cellsRange = {};
        this.update(table);
    };

    //===以下為靜態工具方法===
    UETable.removeSelectedClass = function (cells) {
        utils.each(cells, function (cell) {
            domUtils.removeClasses(cell, "selectTdClass");
        })
    };
    UETable.addSelectedClass = function (cells) {
        utils.each(cells, function (cell) {
            domUtils.addClass(cell, "selectTdClass");
        })
    };
    UETable.isEmptyBlock = function (node) {
        var reg = new RegExp(domUtils.fillChar, 'g');
        if (node[browser.ie ? 'innerText' : 'textContent'].replace(/^\s*$/, '').replace(reg, '').length > 0) {
            return 0;
        }
        for (var i in dtd.$isNotEmpty) if (dtd.$isNotEmpty.hasOwnProperty(i)) {
            if (node.getElementsByTagName(i).length) {
                return 0;
            }
        }
        return 1;
    };
    UETable.getWidth = function (cell) {
        if (!cell)return 0;
        return parseInt(domUtils.getComputedStyle(cell, "width"), 10);
    };

    /**
     * 獲取單元格或者單元格組的“對齊”狀態。 如果當前的檢測物件是一個單元格組， 只有在滿足所有單元格的 水平和豎直 對齊屬性都相同的
     * 條件時才會返回其狀態值，否則將返回null； 如果當前只檢測了一個單元格， 則直接返回當前單元格的對齊狀態；
     * @param table cell or table cells , 支援單個單元格dom物件 或者 單元格dom物件陣列
     * @return { align: 'left' || 'right' || 'center', valign: 'top' || 'middle' || 'bottom' } 或者 null
     */
    UETable.getTableCellAlignState = function ( cells ) {

        !utils.isArray( cells ) && ( cells = [cells] );

        var result = {},
            status = ['align', 'valign'],
            tempStatus = null,
            isSame = true;//狀態是否相同

        utils.each( cells, function( cellNode ){

            utils.each( status, function( currentState ){

                tempStatus = cellNode.getAttribute( currentState );

                if( !result[ currentState ] && tempStatus ) {
                    result[ currentState ] = tempStatus;
                } else if( !result[ currentState ] || ( tempStatus !== result[ currentState ] ) ) {
                    isSame = false;
                    return false;
                }

            } );

            return isSame;

        });

        return isSame ? result : null;

    };

    /**
     * 根據當前選區獲取相關的table資訊
     * @return {Object}
     */
    UETable.getTableItemsByRange = function (editor) {
        var start = editor.selection.getStart();

        //ff下會選中bookmark
        if( start && start.id && start.id.indexOf('_baidu_bookmark_start_') === 0 && start.nextSibling) {
            start = start.nextSibling;
        }

        //在table或者td邊緣有可能存在選中tr的情況
        var cell = start && domUtils.findParentByTagName(start, ["td", "th"], true),
            tr = cell && cell.parentNode,
            caption = start && domUtils.findParentByTagName(start, 'caption', true),
            table = caption ? caption.parentNode : tr && tr.parentNode.parentNode;

        return {
            cell:cell,
            tr:tr,
            table:table,
            caption:caption
        }
    };
    UETable.getUETableBySelected = function (editor) {
        var table = UETable.getTableItemsByRange(editor).table;
        if (table && table.ueTable && table.ueTable.selectedTds.length) {
            return table.ueTable;
        }
        return null;
    };

    UETable.getDefaultValue = function (editor, table) {
        var borderMap = {
                thin:'0px',
                medium:'1px',
                thick:'2px'
            },
            tableBorder, tdPadding, tdBorder, tmpValue;
        if (!table) {
            table = editor.document.createElement('table');
            table.insertRow(0).insertCell(0).innerHTML = 'xxx';
            editor.body.appendChild(table);
            var td = table.getElementsByTagName('td')[0];
            tmpValue = domUtils.getComputedStyle(table, 'border-left-width');
            tableBorder = parseInt(borderMap[tmpValue] || tmpValue, 10);
            tmpValue = domUtils.getComputedStyle(td, 'padding-left');
            tdPadding = parseInt(borderMap[tmpValue] || tmpValue, 10);
            tmpValue = domUtils.getComputedStyle(td, 'border-left-width');
            tdBorder = parseInt(borderMap[tmpValue] || tmpValue, 10);
            domUtils.remove(table);
            return {
                tableBorder:tableBorder,
                tdPadding:tdPadding,
                tdBorder:tdBorder
            };
        } else {
            td = table.getElementsByTagName('td')[0];
            tmpValue = domUtils.getComputedStyle(table, 'border-left-width');
            tableBorder = parseInt(borderMap[tmpValue] || tmpValue, 10);
            tmpValue = domUtils.getComputedStyle(td, 'padding-left');
            tdPadding = parseInt(borderMap[tmpValue] || tmpValue, 10);
            tmpValue = domUtils.getComputedStyle(td, 'border-left-width');
            tdBorder = parseInt(borderMap[tmpValue] || tmpValue, 10);
            return {
                tableBorder:tableBorder,
                tdPadding:tdPadding,
                tdBorder:tdBorder
            };
        }
    };
    /**
     * 根據當前點選的td或者table獲取索引物件
     * @param tdOrTable
     */
    UETable.getUETable = function (tdOrTable) {
        var tag = tdOrTable.tagName.toLowerCase();
        tdOrTable = (tag == "td" || tag == "th" || tag == 'caption') ? domUtils.findParentByTagName(tdOrTable, "table", true) : tdOrTable;
        if (!tdOrTable.ueTable) {
            tdOrTable.ueTable = new UETable(tdOrTable);
        }
        return tdOrTable.ueTable;
    };

    UETable.cloneCell = function(cell,ignoreMerge,keepPro){
        if (!cell || utils.isString(cell)) {
            return this.table.ownerDocument.createElement(cell || 'td');
        }
        var flag = domUtils.hasClass(cell, "selectTdClass");
        flag && domUtils.removeClasses(cell, "selectTdClass");
        var tmpCell = cell.cloneNode(true);
        if (ignoreMerge) {
            tmpCell.rowSpan = tmpCell.colSpan = 1;
        }
        //去掉寬高
        !keepPro && domUtils.removeAttributes(tmpCell,'width height');
        !keepPro && domUtils.removeAttributes(tmpCell,'style');

        tmpCell.style.borderLeftStyle = "";
        tmpCell.style.borderTopStyle = "";
        tmpCell.style.borderLeftColor = cell.style.borderRightColor;
        tmpCell.style.borderLeftWidth = cell.style.borderRightWidth;
        tmpCell.style.borderTopColor = cell.style.borderBottomColor;
        tmpCell.style.borderTopWidth = cell.style.borderBottomWidth;
        flag && domUtils.addClass(cell, "selectTdClass");
        return tmpCell;
    }

    UETable.prototype = {
        getMaxRows:function () {
            var rows = this.table.rows, maxLen = 1;
            for (var i = 0, row; row = rows[i]; i++) {
                var currentMax = 1;
                for (var j = 0, cj; cj = row.cells[j++];) {
                    currentMax = Math.max(cj.rowSpan || 1, currentMax);
                }
                maxLen = Math.max(currentMax + i, maxLen);
            }
            return maxLen;
        },
        /**
         * 獲取當前表格的最大列數
         */
        getMaxCols:function () {
            var rows = this.table.rows, maxLen = 0, cellRows = {};
            for (var i = 0, row; row = rows[i]; i++) {
                var cellsNum = 0;
                for (var j = 0, cj; cj = row.cells[j++];) {
                    cellsNum += (cj.colSpan || 1);
                    if (cj.rowSpan && cj.rowSpan > 1) {
                        for (var k = 1; k < cj.rowSpan; k++) {
                            if (!cellRows['row_' + (i + k)]) {
                                cellRows['row_' + (i + k)] = (cj.colSpan || 1);
                            } else {
                                cellRows['row_' + (i + k)]++
                            }
                        }

                    }
                }
                cellsNum += cellRows['row_' + i] || 0;
                maxLen = Math.max(cellsNum, maxLen);
            }
            return maxLen;
        },
        getCellColIndex:function (cell) {

        },
        /**
         * 獲取當前cell旁邊的單元格，
         * @param cell
         * @param right
         */
        getHSideCell:function (cell, right) {
            try {
                var cellInfo = this.getCellInfo(cell),
                    previewRowIndex, previewColIndex;
                var len = this.selectedTds.length,
                    range = this.cellsRange;
                //首行或者首列沒有前置單元格
                if ((!right && (!len ? !cellInfo.colIndex : !range.beginColIndex)) || (right && (!len ? (cellInfo.colIndex == (this.colsNum - 1)) : (range.endColIndex == this.colsNum - 1)))) return null;

                previewRowIndex = !len ? cellInfo.rowIndex : range.beginRowIndex;
                previewColIndex = !right ? ( !len ? (cellInfo.colIndex < 1 ? 0 : (cellInfo.colIndex - 1)) : range.beginColIndex - 1)
                    : ( !len ? cellInfo.colIndex + 1 : range.endColIndex + 1);
                return this.getCell(this.indexTable[previewRowIndex][previewColIndex].rowIndex, this.indexTable[previewRowIndex][previewColIndex].cellIndex);
            } catch (e) {
                showError(e);
            }
        },
        getTabNextCell:function (cell, preRowIndex) {
            var cellInfo = this.getCellInfo(cell),
                rowIndex = preRowIndex || cellInfo.rowIndex,
                colIndex = cellInfo.colIndex + 1 + (cellInfo.colSpan - 1),
                nextCell;
            try {
                nextCell = this.getCell(this.indexTable[rowIndex][colIndex].rowIndex, this.indexTable[rowIndex][colIndex].cellIndex);
            } catch (e) {
                try {
                    rowIndex = rowIndex * 1 + 1;
                    colIndex = 0;
                    nextCell = this.getCell(this.indexTable[rowIndex][colIndex].rowIndex, this.indexTable[rowIndex][colIndex].cellIndex);
                } catch (e) {
                }
            }
            return nextCell;

        },
        /**
         * 獲取視覺上的後置單元格
         * @param cell
         * @param bottom
         */
        getVSideCell:function (cell, bottom, ignoreRange) {
            try {
                var cellInfo = this.getCellInfo(cell),
                    nextRowIndex, nextColIndex;
                var len = this.selectedTds.length && !ignoreRange,
                    range = this.cellsRange;
                //末行或者末列沒有後置單元格
                if ((!bottom && (cellInfo.rowIndex == 0)) || (bottom && (!len ? (cellInfo.rowIndex + cellInfo.rowSpan > this.rowsNum - 1) : (range.endRowIndex == this.rowsNum - 1)))) return null;

                nextRowIndex = !bottom ? ( !len ? cellInfo.rowIndex - 1 : range.beginRowIndex - 1)
                    : ( !len ? (cellInfo.rowIndex + cellInfo.rowSpan) : range.endRowIndex + 1);
                nextColIndex = !len ? cellInfo.colIndex : range.beginColIndex;
                return this.getCell(this.indexTable[nextRowIndex][nextColIndex].rowIndex, this.indexTable[nextRowIndex][nextColIndex].cellIndex);
            } catch (e) {
                showError(e);
            }
        },
        /**
         * 獲取相同結束位置的單元格，xOrY指代了是獲取x軸相同還是y軸相同
         */
        getSameEndPosCells:function (cell, xOrY) {
            try {
                var flag = (xOrY.toLowerCase() === "x"),
                    end = domUtils.getXY(cell)[flag ? 'x' : 'y'] + cell["offset" + (flag ? 'Width' : 'Height')],
                    rows = this.table.rows,
                    cells = null, returns = [];
                for (var i = 0; i < this.rowsNum; i++) {
                    cells = rows[i].cells;
                    for (var j = 0, tmpCell; tmpCell = cells[j++];) {
                        var tmpEnd = domUtils.getXY(tmpCell)[flag ? 'x' : 'y'] + tmpCell["offset" + (flag ? 'Width' : 'Height')];
                        //對應行的td已經被上面行rowSpan了
                        if (tmpEnd > end && flag) break;
                        if (cell == tmpCell || end == tmpEnd) {
                            //只獲取單一的單元格
                            //todo 僅獲取單一單元格在特定情況下會造成returns為空，從而影響後續的拖拽實現，修正這個。需考慮效能
                            if (tmpCell[flag ? "colSpan" : "rowSpan"] == 1) {
                                returns.push(tmpCell);
                            }
                            if (flag) break;
                        }
                    }
                }
                return returns;
            } catch (e) {
                showError(e);
            }
        },
        setCellContent:function (cell, content) {
            cell.innerHTML = content || (browser.ie ? domUtils.fillChar : "<br />");
        },
        cloneCell:UETable.cloneCell,
        /**
         * 獲取跟當前單元格的右邊豎線為左邊的所有未合併單元格
         */
        getSameStartPosXCells:function (cell) {
            try {
                var start = domUtils.getXY(cell).x + cell.offsetWidth,
                    rows = this.table.rows, cells , returns = [];
                for (var i = 0; i < this.rowsNum; i++) {
                    cells = rows[i].cells;
                    for (var j = 0, tmpCell; tmpCell = cells[j++];) {
                        var tmpStart = domUtils.getXY(tmpCell).x;
                        if (tmpStart > start) break;
                        if (tmpStart == start && tmpCell.colSpan == 1) {
                            returns.push(tmpCell);
                            break;
                        }
                    }
                }
                return returns;
            } catch (e) {
                showError(e);
            }
        },
        /**
         * 更新table對應的索引表
         */
        update:function (table) {
            this.table = table || this.table;
            this.selectedTds = [];
            this.cellsRange = {};
            this.indexTable = [];
            var rows = this.table.rows,
                rowsNum = this.getMaxRows(),
                dNum = rowsNum - rows.length,
                colsNum = this.getMaxCols();
            while (dNum--) {
                this.table.insertRow(rows.length);
            }
            this.rowsNum = rowsNum;
            this.colsNum = colsNum;
            for (var i = 0, len = rows.length; i < len; i++) {
                this.indexTable[i] = new Array(colsNum);
            }
            //填充索引表
            for (var rowIndex = 0, row; row = rows[rowIndex]; rowIndex++) {
                for (var cellIndex = 0, cell, cells = row.cells; cell = cells[cellIndex]; cellIndex++) {
                    //修正整行被rowSpan時導致的行數計算錯誤
                    if (cell.rowSpan > rowsNum) {
                        cell.rowSpan = rowsNum;
                    }
                    var colIndex = cellIndex,
                        rowSpan = cell.rowSpan || 1,
                        colSpan = cell.colSpan || 1;
                    //當已經被上一行rowSpan或者被前一列colSpan了，則跳到下一個單元格進行
                    while (this.indexTable[rowIndex][colIndex]) colIndex++;
                    for (var j = 0; j < rowSpan; j++) {
                        for (var k = 0; k < colSpan; k++) {
                            this.indexTable[rowIndex + j][colIndex + k] = {
                                rowIndex:rowIndex,
                                cellIndex:cellIndex,
                                colIndex:colIndex,
                                rowSpan:rowSpan,
                                colSpan:colSpan
                            }
                        }
                    }
                }
            }
            //修復殘缺td
            for (j = 0; j < rowsNum; j++) {
                for (k = 0; k < colsNum; k++) {
                    if (this.indexTable[j][k] === undefined) {
                        row = rows[j];
                        cell = row.cells[row.cells.length - 1];
                        cell = cell ? cell.cloneNode(true) : this.table.ownerDocument.createElement("td");
                        this.setCellContent(cell);
                        if (cell.colSpan !== 1)cell.colSpan = 1;
                        if (cell.rowSpan !== 1)cell.rowSpan = 1;
                        row.appendChild(cell);
                        this.indexTable[j][k] = {
                            rowIndex:j,
                            cellIndex:cell.cellIndex,
                            colIndex:k,
                            rowSpan:1,
                            colSpan:1
                        }
                    }
                }
            }
            //當框選後刪除行或者列後撤銷，需要重建選區。
            var tds = domUtils.getElementsByTagName(this.table, "td"),
                selectTds = [];
            utils.each(tds, function (td) {
                if (domUtils.hasClass(td, "selectTdClass")) {
                    selectTds.push(td);
                }
            });
            if (selectTds.length) {
                var start = selectTds[0],
                    end = selectTds[selectTds.length - 1],
                    startInfo = this.getCellInfo(start),
                    endInfo = this.getCellInfo(end);
                this.selectedTds = selectTds;
                this.cellsRange = {
                    beginRowIndex:startInfo.rowIndex,
                    beginColIndex:startInfo.colIndex,
                    endRowIndex:endInfo.rowIndex + endInfo.rowSpan - 1,
                    endColIndex:endInfo.colIndex + endInfo.colSpan - 1
                };
            }
            //給第一行設定firstRow的樣式名稱,在排序圖示的樣式上使用到
            if(!domUtils.hasClass(this.table.rows[0], "firstRow")) {
                domUtils.addClass(this.table.rows[0], "firstRow");
                for(var i = 1; i< this.table.rows.length; i++) {
                    domUtils.removeClasses(this.table.rows[i], "firstRow");
                }
            }
        },
        /**
         * 獲取單元格的索引資訊
         */
        getCellInfo:function (cell) {
            if (!cell) return;
            var cellIndex = cell.cellIndex,
                rowIndex = cell.parentNode.rowIndex,
                rowInfo = this.indexTable[rowIndex],
                numCols = this.colsNum;
            for (var colIndex = cellIndex; colIndex < numCols; colIndex++) {
                var cellInfo = rowInfo[colIndex];
                if (cellInfo.rowIndex === rowIndex && cellInfo.cellIndex === cellIndex) {
                    return cellInfo;
                }
            }
        },
        /**
         * 根據行列號獲取單元格
         */
        getCell:function (rowIndex, cellIndex) {
            return rowIndex < this.rowsNum && this.table.rows[rowIndex].cells[cellIndex] || null;
        },
        /**
         * 刪除單元格
         */
        deleteCell:function (cell, rowIndex) {
            rowIndex = typeof rowIndex == 'number' ? rowIndex : cell.parentNode.rowIndex;
            var row = this.table.rows[rowIndex];
            row.deleteCell(cell.cellIndex);
        },
        /**
         * 根據始末兩個單元格獲取被框選的所有單元格範圍
         */
        getCellsRange:function (cellA, cellB) {
            function checkRange(beginRowIndex, beginColIndex, endRowIndex, endColIndex) {
                var tmpBeginRowIndex = beginRowIndex,
                    tmpBeginColIndex = beginColIndex,
                    tmpEndRowIndex = endRowIndex,
                    tmpEndColIndex = endColIndex,
                    cellInfo, colIndex, rowIndex;
                // 通過indexTable檢查是否存在超出TableRange上邊界的情況
                if (beginRowIndex > 0) {
                    for (colIndex = beginColIndex; colIndex < endColIndex; colIndex++) {
                        cellInfo = me.indexTable[beginRowIndex][colIndex];
                        rowIndex = cellInfo.rowIndex;
                        if (rowIndex < beginRowIndex) {
                            tmpBeginRowIndex = Math.min(rowIndex, tmpBeginRowIndex);
                        }
                    }
                }
                // 通過indexTable檢查是否存在超出TableRange右邊界的情況
                if (endColIndex < me.colsNum) {
                    for (rowIndex = beginRowIndex; rowIndex < endRowIndex; rowIndex++) {
                        cellInfo = me.indexTable[rowIndex][endColIndex];
                        colIndex = cellInfo.colIndex + cellInfo.colSpan - 1;
                        if (colIndex > endColIndex) {
                            tmpEndColIndex = Math.max(colIndex, tmpEndColIndex);
                        }
                    }
                }
                // 檢查是否有超出TableRange下邊界的情況
                if (endRowIndex < me.rowsNum) {
                    for (colIndex = beginColIndex; colIndex < endColIndex; colIndex++) {
                        cellInfo = me.indexTable[endRowIndex][colIndex];
                        rowIndex = cellInfo.rowIndex + cellInfo.rowSpan - 1;
                        if (rowIndex > endRowIndex) {
                            tmpEndRowIndex = Math.max(rowIndex, tmpEndRowIndex);
                        }
                    }
                }
                // 檢查是否有超出TableRange左邊界的情況
                if (beginColIndex > 0) {
                    for (rowIndex = beginRowIndex; rowIndex < endRowIndex; rowIndex++) {
                        cellInfo = me.indexTable[rowIndex][beginColIndex];
                        colIndex = cellInfo.colIndex;
                        if (colIndex < beginColIndex) {
                            tmpBeginColIndex = Math.min(cellInfo.colIndex, tmpBeginColIndex);
                        }
                    }
                }
                //遞迴呼叫直至所有完成所有框選單元格的擴充套件
                if (tmpBeginRowIndex != beginRowIndex || tmpBeginColIndex != beginColIndex || tmpEndRowIndex != endRowIndex || tmpEndColIndex != endColIndex) {
                    return checkRange(tmpBeginRowIndex, tmpBeginColIndex, tmpEndRowIndex, tmpEndColIndex);
                } else {
                    // 不需要擴充套件TableRange的情況
                    return {
                        beginRowIndex:beginRowIndex,
                        beginColIndex:beginColIndex,
                        endRowIndex:endRowIndex,
                        endColIndex:endColIndex
                    };
                }
            }

            try {
                var me = this,
                    cellAInfo = me.getCellInfo(cellA);
                if (cellA === cellB) {
                    return {
                        beginRowIndex:cellAInfo.rowIndex,
                        beginColIndex:cellAInfo.colIndex,
                        endRowIndex:cellAInfo.rowIndex + cellAInfo.rowSpan - 1,
                        endColIndex:cellAInfo.colIndex + cellAInfo.colSpan - 1
                    };
                }
                var cellBInfo = me.getCellInfo(cellB);
                // 計算TableRange的四個邊
                var beginRowIndex = Math.min(cellAInfo.rowIndex, cellBInfo.rowIndex),
                    beginColIndex = Math.min(cellAInfo.colIndex, cellBInfo.colIndex),
                    endRowIndex = Math.max(cellAInfo.rowIndex + cellAInfo.rowSpan - 1, cellBInfo.rowIndex + cellBInfo.rowSpan - 1),
                    endColIndex = Math.max(cellAInfo.colIndex + cellAInfo.colSpan - 1, cellBInfo.colIndex + cellBInfo.colSpan - 1);

                return checkRange(beginRowIndex, beginColIndex, endRowIndex, endColIndex);
            } catch (e) {
                //throw e;
            }
        },
        /**
         * 依據cellsRange獲取對應的單元格集合
         */
        getCells:function (range) {
            //每次獲取cells之前必須先清除上次的選擇，否則會對後續獲取操作造成影響
            this.clearSelected();
            var beginRowIndex = range.beginRowIndex,
                beginColIndex = range.beginColIndex,
                endRowIndex = range.endRowIndex,
                endColIndex = range.endColIndex,
                cellInfo, rowIndex, colIndex, tdHash = {}, returnTds = [];
            for (var i = beginRowIndex; i <= endRowIndex; i++) {
                for (var j = beginColIndex; j <= endColIndex; j++) {
                    cellInfo = this.indexTable[i][j];
                    rowIndex = cellInfo.rowIndex;
                    colIndex = cellInfo.colIndex;
                    // 如果Cells裡已經包含了此Cell則跳過
                    var key = rowIndex + '|' + colIndex;
                    if (tdHash[key]) continue;
                    tdHash[key] = 1;
                    if (rowIndex < i || colIndex < j || rowIndex + cellInfo.rowSpan - 1 > endRowIndex || colIndex + cellInfo.colSpan - 1 > endColIndex) {
                        return null;
                    }
                    returnTds.push(this.getCell(rowIndex, cellInfo.cellIndex));
                }
            }
            return returnTds;
        },
        /**
         * 清理已經選中的單元格
         */
        clearSelected:function () {
            UETable.removeSelectedClass(this.selectedTds);
            this.selectedTds = [];
            this.cellsRange = {};
        },
        /**
         * 根據range設定已經選中的單元格
         */
        setSelected:function (range) {
            var cells = this.getCells(range);
            UETable.addSelectedClass(cells);
            this.selectedTds = cells;
            this.cellsRange = range;
        },
        isFullRow:function () {
            var range = this.cellsRange;
            return (range.endColIndex - range.beginColIndex + 1) == this.colsNum;
        },
        isFullCol:function () {
            var range = this.cellsRange,
                table = this.table,
                ths = table.getElementsByTagName("th"),
                rows = range.endRowIndex - range.beginRowIndex + 1;
            return  !ths.length ? rows == this.rowsNum : rows == this.rowsNum || (rows == this.rowsNum - 1);

        },
        /**
         * 獲取視覺上的前置單元格，預設是左邊，top傳入時
         * @param cell
         * @param top
         */
        getNextCell:function (cell, bottom, ignoreRange) {
            try {
                var cellInfo = this.getCellInfo(cell),
                    nextRowIndex, nextColIndex;
                var len = this.selectedTds.length && !ignoreRange,
                    range = this.cellsRange;
                //末行或者末列沒有後置單元格
                if ((!bottom && (cellInfo.rowIndex == 0)) || (bottom && (!len ? (cellInfo.rowIndex + cellInfo.rowSpan > this.rowsNum - 1) : (range.endRowIndex == this.rowsNum - 1)))) return null;

                nextRowIndex = !bottom ? ( !len ? cellInfo.rowIndex - 1 : range.beginRowIndex - 1)
                    : ( !len ? (cellInfo.rowIndex + cellInfo.rowSpan) : range.endRowIndex + 1);
                nextColIndex = !len ? cellInfo.colIndex : range.beginColIndex;
                return this.getCell(this.indexTable[nextRowIndex][nextColIndex].rowIndex, this.indexTable[nextRowIndex][nextColIndex].cellIndex);
            } catch (e) {
                showError(e);
            }
        },
        getPreviewCell:function (cell, top) {
            try {
                var cellInfo = this.getCellInfo(cell),
                    previewRowIndex, previewColIndex;
                var len = this.selectedTds.length,
                    range = this.cellsRange;
                //首行或者首列沒有前置單元格
                if ((!top && (!len ? !cellInfo.colIndex : !range.beginColIndex)) || (top && (!len ? (cellInfo.rowIndex > (this.colsNum - 1)) : (range.endColIndex == this.colsNum - 1)))) return null;

                previewRowIndex = !top ? ( !len ? cellInfo.rowIndex : range.beginRowIndex )
                    : ( !len ? (cellInfo.rowIndex < 1 ? 0 : (cellInfo.rowIndex - 1)) : range.beginRowIndex);
                previewColIndex = !top ? ( !len ? (cellInfo.colIndex < 1 ? 0 : (cellInfo.colIndex - 1)) : range.beginColIndex - 1)
                    : ( !len ? cellInfo.colIndex : range.endColIndex + 1);
                return this.getCell(this.indexTable[previewRowIndex][previewColIndex].rowIndex, this.indexTable[previewRowIndex][previewColIndex].cellIndex);
            } catch (e) {
                showError(e);
            }
        },
        /**
         * 移動單元格中的內容
         */
        moveContent:function (cellTo, cellFrom) {
            if (UETable.isEmptyBlock(cellFrom)) return;
            if (UETable.isEmptyBlock(cellTo)) {
                cellTo.innerHTML = cellFrom.innerHTML;
                return;
            }
            var child = cellTo.lastChild;
            if (child.nodeType == 3 || !dtd.$block[child.tagName]) {
                cellTo.appendChild(cellTo.ownerDocument.createElement('br'))
            }
            while (child = cellFrom.firstChild) {
                cellTo.appendChild(child);
            }
        },
        /**
         * 向右合併單元格
         */
        mergeRight:function (cell) {
            var cellInfo = this.getCellInfo(cell),
                rightColIndex = cellInfo.colIndex + cellInfo.colSpan,
                rightCellInfo = this.indexTable[cellInfo.rowIndex][rightColIndex],
                rightCell = this.getCell(rightCellInfo.rowIndex, rightCellInfo.cellIndex);
            //合併
            cell.colSpan = cellInfo.colSpan + rightCellInfo.colSpan;
            //被合併的單元格不應存在寬度屬性
            cell.removeAttribute("width");
            //移動內容
            this.moveContent(cell, rightCell);
            //刪掉被合併的Cell
            this.deleteCell(rightCell, rightCellInfo.rowIndex);
            this.update();
        },
        /**
         * 向下合併單元格
         */
        mergeDown:function (cell) {
            var cellInfo = this.getCellInfo(cell),
                downRowIndex = cellInfo.rowIndex + cellInfo.rowSpan,
                downCellInfo = this.indexTable[downRowIndex][cellInfo.colIndex],
                downCell = this.getCell(downCellInfo.rowIndex, downCellInfo.cellIndex);
            cell.rowSpan = cellInfo.rowSpan + downCellInfo.rowSpan;
            cell.removeAttribute("height");
            this.moveContent(cell, downCell);
            this.deleteCell(downCell, downCellInfo.rowIndex);
            this.update();
        },
        /**
         * 合併整個range中的內容
         */
        mergeRange:function () {
            //由於合併操作可以在任意時刻進行，所以無法通過滑鼠位置等資訊實時生成range，只能通過快取例項中的cellsRange物件來訪問
            var range = this.cellsRange,
                leftTopCell = this.getCell(range.beginRowIndex, this.indexTable[range.beginRowIndex][range.beginColIndex].cellIndex);

            if (leftTopCell.tagName == "TH" && range.endRowIndex !== range.beginRowIndex) {
                var index = this.indexTable,
                    info = this.getCellInfo(leftTopCell);
                leftTopCell = this.getCell(1, index[1][info.colIndex].cellIndex);
                range = this.getCellsRange(leftTopCell, this.getCell(index[this.rowsNum - 1][info.colIndex].rowIndex, index[this.rowsNum - 1][info.colIndex].cellIndex));
            }

            // 刪除剩餘的Cells
            var cells = this.getCells(range);
            for(var i= 0,ci;ci=cells[i++];){
                if (ci !== leftTopCell) {
                    this.moveContent(leftTopCell, ci);
                    this.deleteCell(ci);
                }
            }
            // 修改左上角Cell的rowSpan和colSpan，並調整寬度屬性設定
            leftTopCell.rowSpan = range.endRowIndex - range.beginRowIndex + 1;
            leftTopCell.rowSpan > 1 && leftTopCell.removeAttribute("height");
            leftTopCell.colSpan = range.endColIndex - range.beginColIndex + 1;
            leftTopCell.colSpan > 1 && leftTopCell.removeAttribute("width");
            if (leftTopCell.rowSpan == this.rowsNum && leftTopCell.colSpan != 1) {
                leftTopCell.colSpan = 1;
            }

            if (leftTopCell.colSpan == this.colsNum && leftTopCell.rowSpan != 1) {
                var rowIndex = leftTopCell.parentNode.rowIndex;
                //解決IE下的表格操作問題
                if( this.table.deleteRow ) {
                    for (var i = rowIndex+ 1, curIndex=rowIndex+ 1, len=leftTopCell.rowSpan; i < len; i++) {
                        this.table.deleteRow(curIndex);
                    }
                } else {
                    for (var i = 0, len=leftTopCell.rowSpan - 1; i < len; i++) {
                        var row = this.table.rows[rowIndex + 1];
                        row.parentNode.removeChild(row);
                    }
                }
                leftTopCell.rowSpan = 1;
            }
            this.update();
        },
        /**
         * 插入一行單元格
         */
        insertRow:function (rowIndex, sourceCell) {
            var numCols = this.colsNum,
                table = this.table,
                row = table.insertRow(rowIndex), cell,
                isInsertTitle = typeof sourceCell == 'string' && sourceCell.toUpperCase() == 'TH';

            function replaceTdToTh(colIndex, cell, tableRow) {
                if (colIndex == 0) {
                    var tr = tableRow.nextSibling || tableRow.previousSibling,
                        th = tr.cells[colIndex];
                    if (th.tagName == 'TH') {
                        th = cell.ownerDocument.createElement("th");
                        th.appendChild(cell.firstChild);
                        tableRow.insertBefore(th, cell);
                        domUtils.remove(cell)
                    }
                }else{
                    if (cell.tagName == 'TH') {
                        var td = cell.ownerDocument.createElement("td");
                        td.appendChild(cell.firstChild);
                        tableRow.insertBefore(td, cell);
                        domUtils.remove(cell)
                    }
                }
            }

            //首行直接插入,無需考慮部分單元格被rowspan的情況
            if (rowIndex == 0 || rowIndex == this.rowsNum) {
                for (var colIndex = 0; colIndex < numCols; colIndex++) {
                    cell = this.cloneCell(sourceCell, true);
                    this.setCellContent(cell);
                    cell.getAttribute('vAlign') && cell.setAttribute('vAlign', cell.getAttribute('vAlign'));
                    row.appendChild(cell);
                    if(!isInsertTitle) replaceTdToTh(colIndex, cell, row);
                }
            } else {
                var infoRow = this.indexTable[rowIndex],
                    cellIndex = 0;
                for (colIndex = 0; colIndex < numCols; colIndex++) {
                    var cellInfo = infoRow[colIndex];
                    //如果存在某個單元格的rowspan穿過待插入行的位置，則修改該單元格的rowspan即可，無需插入單元格
                    if (cellInfo.rowIndex < rowIndex) {
                        cell = this.getCell(cellInfo.rowIndex, cellInfo.cellIndex);
                        cell.rowSpan = cellInfo.rowSpan + 1;
                    } else {
                        cell = this.cloneCell(sourceCell, true);
                        this.setCellContent(cell);
                        row.appendChild(cell);
                    }
                    if(!isInsertTitle) replaceTdToTh(colIndex, cell, row);
                }
            }
            //框選時插入不觸發contentchange，需要手動更新索引。
            this.update();
            return row;
        },
        /**
         * 刪除一行單元格
         * @param rowIndex
         */
        deleteRow:function (rowIndex) {
            var row = this.table.rows[rowIndex],
                infoRow = this.indexTable[rowIndex],
                colsNum = this.colsNum,
                count = 0;     //處理計數
            for (var colIndex = 0; colIndex < colsNum;) {
                var cellInfo = infoRow[colIndex],
                    cell = this.getCell(cellInfo.rowIndex, cellInfo.cellIndex);
                if (cell.rowSpan > 1) {
                    if (cellInfo.rowIndex == rowIndex) {
                        var clone = cell.cloneNode(true);
                        clone.rowSpan = cell.rowSpan - 1;
                        clone.innerHTML = "";
                        cell.rowSpan = 1;
                        var nextRowIndex = rowIndex + 1,
                            nextRow = this.table.rows[nextRowIndex],
                            insertCellIndex,
                            preMerged = this.getPreviewMergedCellsNum(nextRowIndex, colIndex) - count;
                        if (preMerged < colIndex) {
                            insertCellIndex = colIndex - preMerged - 1;
                            //nextRow.insertCell(insertCellIndex);
                            domUtils.insertAfter(nextRow.cells[insertCellIndex], clone);
                        } else {
                            if (nextRow.cells.length) nextRow.insertBefore(clone, nextRow.cells[0])
                        }
                        count += 1;
                        //cell.parentNode.removeChild(cell);
                    }
                }
                colIndex += cell.colSpan || 1;
            }
            var deleteTds = [], cacheMap = {};
            for (colIndex = 0; colIndex < colsNum; colIndex++) {
                var tmpRowIndex = infoRow[colIndex].rowIndex,
                    tmpCellIndex = infoRow[colIndex].cellIndex,
                    key = tmpRowIndex + "_" + tmpCellIndex;
                if (cacheMap[key])continue;
                cacheMap[key] = 1;
                cell = this.getCell(tmpRowIndex, tmpCellIndex);
                deleteTds.push(cell);
            }
            var mergeTds = [];
            utils.each(deleteTds, function (td) {
                if (td.rowSpan == 1) {
                    td.parentNode.removeChild(td);
                } else {
                    mergeTds.push(td);
                }
            });
            utils.each(mergeTds, function (td) {
                td.rowSpan--;
            });
            row.parentNode.removeChild(row);
            //瀏覽器方法本身存在bug,採用自定義方法刪除
            //this.table.deleteRow(rowIndex);
            this.update();
        },
        insertCol:function (colIndex, sourceCell, defaultValue) {
            var rowsNum = this.rowsNum,
                rowIndex = 0,
                tableRow, cell,
                backWidth = parseInt((this.table.offsetWidth - (this.colsNum + 1) * 20 - (this.colsNum + 1)) / (this.colsNum + 1), 10),
                isInsertTitleCol = typeof sourceCell == 'string' && sourceCell.toUpperCase() == 'TH';

            function replaceTdToTh(rowIndex, cell, tableRow) {
                if (rowIndex == 0) {
                    var th = cell.nextSibling || cell.previousSibling;
                    if (th.tagName == 'TH') {
                        th = cell.ownerDocument.createElement("th");
                        th.appendChild(cell.firstChild);
                        tableRow.insertBefore(th, cell);
                        domUtils.remove(cell)
                    }
                }else{
                    if (cell.tagName == 'TH') {
                        var td = cell.ownerDocument.createElement("td");
                        td.appendChild(cell.firstChild);
                        tableRow.insertBefore(td, cell);
                        domUtils.remove(cell)
                    }
                }
            }

            var preCell;
            if (colIndex == 0 || colIndex == this.colsNum) {
                for (; rowIndex < rowsNum; rowIndex++) {
                    tableRow = this.table.rows[rowIndex];
                    preCell = tableRow.cells[colIndex == 0 ? colIndex : tableRow.cells.length];
                    cell = this.cloneCell(sourceCell, true); //tableRow.insertCell(colIndex == 0 ? colIndex : tableRow.cells.length);
                    this.setCellContent(cell);
                    cell.setAttribute('vAlign', cell.getAttribute('vAlign'));
                    preCell && cell.setAttribute('width', preCell.getAttribute('width'));
                    if (!colIndex) {
                        tableRow.insertBefore(cell, tableRow.cells[0]);
                    } else {
                        domUtils.insertAfter(tableRow.cells[tableRow.cells.length - 1], cell);
                    }
                    if(!isInsertTitleCol) replaceTdToTh(rowIndex, cell, tableRow)
                }
            } else {
                for (; rowIndex < rowsNum; rowIndex++) {
                    var cellInfo = this.indexTable[rowIndex][colIndex];
                    if (cellInfo.colIndex < colIndex) {
                        cell = this.getCell(cellInfo.rowIndex, cellInfo.cellIndex);
                        cell.colSpan = cellInfo.colSpan + 1;
                    } else {
                        tableRow = this.table.rows[rowIndex];
                        preCell = tableRow.cells[cellInfo.cellIndex];

                        cell = this.cloneCell(sourceCell, true);//tableRow.insertCell(cellInfo.cellIndex);
                        this.setCellContent(cell);
                        cell.setAttribute('vAlign', cell.getAttribute('vAlign'));
                        preCell && cell.setAttribute('width', preCell.getAttribute('width'));
                        //防止IE下報錯
                        preCell ? tableRow.insertBefore(cell, preCell) : tableRow.appendChild(cell);
                    }
                    if(!isInsertTitleCol) replaceTdToTh(rowIndex, cell, tableRow);
                }
            }
            //框選時插入不觸發contentchange，需要手動更新索引
            this.update();
            this.updateWidth(backWidth, defaultValue || {tdPadding:10, tdBorder:1});
        },
        updateWidth:function (width, defaultValue) {
            var table = this.table,
                tmpWidth = UETable.getWidth(table) - defaultValue.tdPadding * 2 - defaultValue.tdBorder + width;
            if (tmpWidth < table.ownerDocument.body.offsetWidth) {
                table.setAttribute("width", tmpWidth);
                return;
            }
            var tds = domUtils.getElementsByTagName(this.table, "td th");
            utils.each(tds, function (td) {
                td.setAttribute("width", width);
            })
        },
        deleteCol:function (colIndex) {
            var indexTable = this.indexTable,
                tableRows = this.table.rows,
                backTableWidth = this.table.getAttribute("width"),
                backTdWidth = 0,
                rowsNum = this.rowsNum,
                cacheMap = {};
            for (var rowIndex = 0; rowIndex < rowsNum;) {
                var infoRow = indexTable[rowIndex],
                    cellInfo = infoRow[colIndex],
                    key = cellInfo.rowIndex + '_' + cellInfo.colIndex;
                // 跳過已經處理過的Cell
                if (cacheMap[key])continue;
                cacheMap[key] = 1;
                var cell = this.getCell(cellInfo.rowIndex, cellInfo.cellIndex);
                if (!backTdWidth) backTdWidth = cell && parseInt(cell.offsetWidth / cell.colSpan, 10).toFixed(0);
                // 如果Cell的colSpan大於1, 就修改colSpan, 否則就刪掉這個Cell
                if (cell.colSpan > 1) {
                    cell.colSpan--;
                } else {
                    tableRows[rowIndex].deleteCell(cellInfo.cellIndex);
                }
                rowIndex += cellInfo.rowSpan || 1;
            }
            this.table.setAttribute("width", backTableWidth - backTdWidth);
            this.update();
        },
        splitToCells:function (cell) {
            var me = this,
                cells = this.splitToRows(cell);
            utils.each(cells, function (cell) {
                me.splitToCols(cell);
            })
        },
        splitToRows:function (cell) {
            var cellInfo = this.getCellInfo(cell),
                rowIndex = cellInfo.rowIndex,
                colIndex = cellInfo.colIndex,
                results = [];
            // 修改Cell的rowSpan
            cell.rowSpan = 1;
            results.push(cell);
            // 補齊單元格
            for (var i = rowIndex, endRow = rowIndex + cellInfo.rowSpan; i < endRow; i++) {
                if (i == rowIndex)continue;
                var tableRow = this.table.rows[i],
                    tmpCell = tableRow.insertCell(colIndex - this.getPreviewMergedCellsNum(i, colIndex));
                tmpCell.colSpan = cellInfo.colSpan;
                this.setCellContent(tmpCell);
                tmpCell.setAttribute('vAlign', cell.getAttribute('vAlign'));
                tmpCell.setAttribute('align', cell.getAttribute('align'));
                if (cell.style.cssText) {
                    tmpCell.style.cssText = cell.style.cssText;
                }
                results.push(tmpCell);
            }
            this.update();
            return results;
        },
        getPreviewMergedCellsNum:function (rowIndex, colIndex) {
            var indexRow = this.indexTable[rowIndex],
                num = 0;
            for (var i = 0; i < colIndex;) {
                var colSpan = indexRow[i].colSpan,
                    tmpRowIndex = indexRow[i].rowIndex;
                num += (colSpan - (tmpRowIndex == rowIndex ? 1 : 0));
                i += colSpan;
            }
            return num;
        },
        splitToCols:function (cell) {
            var backWidth = (cell.offsetWidth / cell.colSpan - 22).toFixed(0),

                cellInfo = this.getCellInfo(cell),
                rowIndex = cellInfo.rowIndex,
                colIndex = cellInfo.colIndex,
                results = [];
            // 修改Cell的rowSpan
            cell.colSpan = 1;
            cell.setAttribute("width", backWidth);
            results.push(cell);
            // 補齊單元格
            for (var j = colIndex, endCol = colIndex + cellInfo.colSpan; j < endCol; j++) {
                if (j == colIndex)continue;
                var tableRow = this.table.rows[rowIndex],
                    tmpCell = tableRow.insertCell(this.indexTable[rowIndex][j].cellIndex + 1);
                tmpCell.rowSpan = cellInfo.rowSpan;
                this.setCellContent(tmpCell);
                tmpCell.setAttribute('vAlign', cell.getAttribute('vAlign'));
                tmpCell.setAttribute('align', cell.getAttribute('align'));
                tmpCell.setAttribute('width', backWidth);
                if (cell.style.cssText) {
                    tmpCell.style.cssText = cell.style.cssText;
                }
                //處理th的情況
                if (cell.tagName == 'TH') {
                    var th = cell.ownerDocument.createElement('th');
                    th.appendChild(tmpCell.firstChild);
                    th.setAttribute('vAlign', cell.getAttribute('vAlign'));
                    th.rowSpan = tmpCell.rowSpan;
                    tableRow.insertBefore(th, tmpCell);
                    domUtils.remove(tmpCell);
                }
                results.push(tmpCell);
            }
            this.update();
            return results;
        },
        isLastCell:function (cell, rowsNum, colsNum) {
            rowsNum = rowsNum || this.rowsNum;
            colsNum = colsNum || this.colsNum;
            var cellInfo = this.getCellInfo(cell);
            return ((cellInfo.rowIndex + cellInfo.rowSpan) == rowsNum) &&
                ((cellInfo.colIndex + cellInfo.colSpan) == colsNum);
        },
        getLastCell:function (cells) {
            cells = cells || this.table.getElementsByTagName("td");
            var firstInfo = this.getCellInfo(cells[0]);
            var me = this, last = cells[0],
                tr = last.parentNode,
                cellsNum = 0, cols = 0, rows;
            utils.each(cells, function (cell) {
                if (cell.parentNode == tr)cols += cell.colSpan || 1;
                cellsNum += cell.rowSpan * cell.colSpan || 1;
            });
            rows = cellsNum / cols;
            utils.each(cells, function (cell) {
                if (me.isLastCell(cell, rows, cols)) {
                    last = cell;
                    return false;
                }
            });
            return last;

        },
        selectRow:function (rowIndex) {
            var indexRow = this.indexTable[rowIndex],
                start = this.getCell(indexRow[0].rowIndex, indexRow[0].cellIndex),
                end = this.getCell(indexRow[this.colsNum - 1].rowIndex, indexRow[this.colsNum - 1].cellIndex),
                range = this.getCellsRange(start, end);
            this.setSelected(range);
        },
        selectTable:function () {
            var tds = this.table.getElementsByTagName("td"),
                range = this.getCellsRange(tds[0], tds[tds.length - 1]);
            this.setSelected(range);
        },
        setBackground:function (cells, value) {
            if (typeof value === "string") {
                utils.each(cells, function (cell) {
                    cell.style.backgroundColor = value;
                })
            } else if (typeof value === "object") {
                value = utils.extend({
                    repeat:true,
                    colorList:["#ddd", "#fff"]
                }, value);
                var rowIndex = this.getCellInfo(cells[0]).rowIndex,
                    count = 0,
                    colors = value.colorList,
                    getColor = function (list, index, repeat) {
                        return list[index] ? list[index] : repeat ? list[index % list.length] : "";
                    };
                for (var i = 0, cell; cell = cells[i++];) {
                    var cellInfo = this.getCellInfo(cell);
                    cell.style.backgroundColor = getColor(colors, ((rowIndex + count) == cellInfo.rowIndex) ? count : ++count, value.repeat);
                }
            }
        },
        removeBackground:function (cells) {
            utils.each(cells, function (cell) {
                cell.style.backgroundColor = "";
            })
        }


    };
    function showError(e) {
    }
})();

// plugins/table.cmds.js
/**
 * Created with JetBrains PhpStorm.
 * User: taoqili
 * Date: 13-2-20
 * Time: 下午6:25
 * To change this template use File | Settings | File Templates.
 */
;
(function () {
    var UT = UE.UETable,
        getTableItemsByRange = function (editor) {
            return UT.getTableItemsByRange(editor);
        },
        getUETableBySelected = function (editor) {
            return UT.getUETableBySelected(editor)
        },
        getDefaultValue = function (editor, table) {
            return UT.getDefaultValue(editor, table);
        },
        getUETable = function (tdOrTable) {
            return UT.getUETable(tdOrTable);
        };


    UE.commands['inserttable'] = {
        queryCommandState: function () {
            return getTableItemsByRange(this).table ? -1 : 0;
        },
        execCommand: function (cmd, opt) {
            function createTable(opt, tdWidth) {
                var html = [],
                    rowsNum = opt.numRows,
                    colsNum = opt.numCols;
                for (var r = 0; r < rowsNum; r++) {
                    html.push('<tr' + (r == 0 ? ' class="firstRow"':'') + '>');
                    for (var c = 0; c < colsNum; c++) {
                        html.push('<td width="' + tdWidth + '"  vAlign="' + opt.tdvalign + '" >' + (browser.ie && browser.version < 11 ? domUtils.fillChar : '<br/>') + '</td>')
                    }
                    html.push('</tr>')
                }
                //禁止指定table-width
                return '<table><tbody>' + html.join('') + '</tbody></table>'
            }

            if (!opt) {
                opt = utils.extend({}, {
                    numCols: this.options.defaultCols,
                    numRows: this.options.defaultRows,
                    tdvalign: this.options.tdvalign
                })
            }
            var me = this;
            var range = this.selection.getRange(),
                start = range.startContainer,
                firstParentBlock = domUtils.findParent(start, function (node) {
                    return domUtils.isBlockElm(node);
                }, true) || me.body;

            var defaultValue = getDefaultValue(me),
                tableWidth = firstParentBlock.offsetWidth,
                tdWidth = Math.floor(tableWidth / opt.numCols - defaultValue.tdPadding * 2 - defaultValue.tdBorder);

            //todo其他屬性
            !opt.tdvalign && (opt.tdvalign = me.options.tdvalign);
            me.execCommand("inserthtml", createTable(opt, tdWidth));
        }
    };

    UE.commands['insertparagraphbeforetable'] = {
        queryCommandState: function () {
            return getTableItemsByRange(this).cell ? 0 : -1;
        },
        execCommand: function () {
            var table = getTableItemsByRange(this).table;
            if (table) {
                var p = this.document.createElement("p");
                p.innerHTML = browser.ie ? '&nbsp;' : '<br />';
                table.parentNode.insertBefore(p, table);
                this.selection.getRange().setStart(p, 0).setCursor();
            }
        }
    };

    UE.commands['deletetable'] = {
        queryCommandState: function () {
            var rng = this.selection.getRange();
            return domUtils.findParentByTagName(rng.startContainer, 'table', true) ? 0 : -1;
        },
        execCommand: function (cmd, table) {
            var rng = this.selection.getRange();
            table = table || domUtils.findParentByTagName(rng.startContainer, 'table', true);
            if (table) {
                var next = table.nextSibling;
                if (!next) {
                    next = domUtils.createElement(this.document, 'p', {
                        'innerHTML': browser.ie ? domUtils.fillChar : '<br/>'
                    });
                    table.parentNode.insertBefore(next, table);
                }
                domUtils.remove(table);
                rng = this.selection.getRange();
                if (next.nodeType == 3) {
                    rng.setStartBefore(next)
                } else {
                    rng.setStart(next, 0)
                }
                rng.setCursor(false, true)
                this.fireEvent("tablehasdeleted")

            }

        }
    };
    UE.commands['cellalign'] = {
        queryCommandState: function () {
            return getSelectedArr(this).length ? 0 : -1
        },
        execCommand: function (cmd, align) {
            var selectedTds = getSelectedArr(this);
            if (selectedTds.length) {
                for (var i = 0, ci; ci = selectedTds[i++];) {
                    ci.setAttribute('align', align);
                }
            }
        }
    };
    UE.commands['cellvalign'] = {
        queryCommandState: function () {
            return getSelectedArr(this).length ? 0 : -1;
        },
        execCommand: function (cmd, valign) {
            var selectedTds = getSelectedArr(this);
            if (selectedTds.length) {
                for (var i = 0, ci; ci = selectedTds[i++];) {
                    ci.setAttribute('vAlign', valign);
                }
            }
        }
    };
    UE.commands['insertcaption'] = {
        queryCommandState: function () {
            var table = getTableItemsByRange(this).table;
            if (table) {
                return table.getElementsByTagName('caption').length == 0 ? 1 : -1;
            }
            return -1;
        },
        execCommand: function () {
            var table = getTableItemsByRange(this).table;
            if (table) {
                var caption = this.document.createElement('caption');
                caption.innerHTML = browser.ie ? domUtils.fillChar : '<br/>';
                table.insertBefore(caption, table.firstChild);
                var range = this.selection.getRange();
                range.setStart(caption, 0).setCursor();
            }

        }
    };
    UE.commands['deletecaption'] = {
        queryCommandState: function () {
            var rng = this.selection.getRange(),
                table = domUtils.findParentByTagName(rng.startContainer, 'table');
            if (table) {
                return table.getElementsByTagName('caption').length == 0 ? -1 : 1;
            }
            return -1;
        },
        execCommand: function () {
            var rng = this.selection.getRange(),
                table = domUtils.findParentByTagName(rng.startContainer, 'table');
            if (table) {
                domUtils.remove(table.getElementsByTagName('caption')[0]);
                var range = this.selection.getRange();
                range.setStart(table.rows[0].cells[0], 0).setCursor();
            }

        }
    };
    UE.commands['inserttitle'] = {
        queryCommandState: function () {
            var table = getTableItemsByRange(this).table;
            if (table) {
                var firstRow = table.rows[0];
                return firstRow.cells[firstRow.cells.length-1].tagName.toLowerCase() != 'th' ? 0 : -1
            }
            return -1;
        },
        execCommand: function () {
            var table = getTableItemsByRange(this).table;
            if (table) {
                getUETable(table).insertRow(0, 'th');
            }
            var th = table.getElementsByTagName('th')[0];
            this.selection.getRange().setStart(th, 0).setCursor(false, true);
        }
    };
    UE.commands['deletetitle'] = {
        queryCommandState: function () {
            var table = getTableItemsByRange(this).table;
            if (table) {
                var firstRow = table.rows[0];
                return firstRow.cells[firstRow.cells.length-1].tagName.toLowerCase() == 'th' ? 0 : -1
            }
            return -1;
        },
        execCommand: function () {
            var table = getTableItemsByRange(this).table;
            if (table) {
                domUtils.remove(table.rows[0])
            }
            var td = table.getElementsByTagName('td')[0];
            this.selection.getRange().setStart(td, 0).setCursor(false, true);
        }
    };
    UE.commands['inserttitlecol'] = {
        queryCommandState: function () {
            var table = getTableItemsByRange(this).table;
            if (table) {
                var lastRow = table.rows[table.rows.length-1];
                return lastRow.getElementsByTagName('th').length ? -1 : 0;
            }
            return -1;
        },
        execCommand: function (cmd) {
            var table = getTableItemsByRange(this).table;
            if (table) {
                getUETable(table).insertCol(0, 'th');
            }
            resetTdWidth(table, this);
            var th = table.getElementsByTagName('th')[0];
            this.selection.getRange().setStart(th, 0).setCursor(false, true);
        }
    };
    UE.commands['deletetitlecol'] = {
        queryCommandState: function () {
            var table = getTableItemsByRange(this).table;
            if (table) {
                var lastRow = table.rows[table.rows.length-1];
                return lastRow.getElementsByTagName('th').length ? 0 : -1;
            }
            return -1;
        },
        execCommand: function () {
            var table = getTableItemsByRange(this).table;
            if (table) {
                for(var i = 0; i< table.rows.length; i++ ){
                    domUtils.remove(table.rows[i].children[0])
                }
            }
            resetTdWidth(table, this);
            var td = table.getElementsByTagName('td')[0];
            this.selection.getRange().setStart(td, 0).setCursor(false, true);
        }
    };

    UE.commands["mergeright"] = {
        queryCommandState: function (cmd) {
            var tableItems = getTableItemsByRange(this),
                table = tableItems.table,
                cell = tableItems.cell;

            if (!table || !cell) return -1;
            var ut = getUETable(table);
            if (ut.selectedTds.length) return -1;

            var cellInfo = ut.getCellInfo(cell),
                rightColIndex = cellInfo.colIndex + cellInfo.colSpan;
            if (rightColIndex >= ut.colsNum) return -1; // 如果處於最右邊則不能向右合併

            var rightCellInfo = ut.indexTable[cellInfo.rowIndex][rightColIndex],
                rightCell = table.rows[rightCellInfo.rowIndex].cells[rightCellInfo.cellIndex];
            if (!rightCell || cell.tagName != rightCell.tagName) return -1; // TH和TD不能相互合併

            // 當且僅當兩個Cell的開始列號和結束列號一致時能進行合併
            return (rightCellInfo.rowIndex == cellInfo.rowIndex && rightCellInfo.rowSpan == cellInfo.rowSpan) ? 0 : -1;
        },
        execCommand: function (cmd) {
            var rng = this.selection.getRange(),
                bk = rng.createBookmark(true);
            var cell = getTableItemsByRange(this).cell,
                ut = getUETable(cell);
            ut.mergeRight(cell);
            rng.moveToBookmark(bk).select();
        }
    };
    UE.commands["mergedown"] = {
        queryCommandState: function (cmd) {
            var tableItems = getTableItemsByRange(this),
                table = tableItems.table,
                cell = tableItems.cell;

            if (!table || !cell) return -1;
            var ut = getUETable(table);
            if (ut.selectedTds.length)return -1;

            var cellInfo = ut.getCellInfo(cell),
                downRowIndex = cellInfo.rowIndex + cellInfo.rowSpan;
            if (downRowIndex >= ut.rowsNum) return -1; // 如果處於最下邊則不能向下合併

            var downCellInfo = ut.indexTable[downRowIndex][cellInfo.colIndex],
                downCell = table.rows[downCellInfo.rowIndex].cells[downCellInfo.cellIndex];
            if (!downCell || cell.tagName != downCell.tagName) return -1; // TH和TD不能相互合併

            // 當且僅當兩個Cell的開始列號和結束列號一致時能進行合併
            return (downCellInfo.colIndex == cellInfo.colIndex && downCellInfo.colSpan == cellInfo.colSpan) ? 0 : -1;
        },
        execCommand: function () {
            var rng = this.selection.getRange(),
                bk = rng.createBookmark(true);
            var cell = getTableItemsByRange(this).cell,
                ut = getUETable(cell);
            ut.mergeDown(cell);
            rng.moveToBookmark(bk).select();
        }
    };
    UE.commands["mergecells"] = {
        queryCommandState: function () {
            return getUETableBySelected(this) ? 0 : -1;
        },
        execCommand: function () {
            var ut = getUETableBySelected(this);
            if (ut && ut.selectedTds.length) {
                var cell = ut.selectedTds[0];
                ut.mergeRange();
                var rng = this.selection.getRange();
                if (domUtils.isEmptyBlock(cell)) {
                    rng.setStart(cell, 0).collapse(true)
                } else {
                    rng.selectNodeContents(cell)
                }
                rng.select();
            }


        }
    };
    UE.commands["insertrow"] = {
        queryCommandState: function () {
            var tableItems = getTableItemsByRange(this),
                cell = tableItems.cell;
            return cell && (cell.tagName == "TD" || (cell.tagName == 'TH' && tableItems.tr !== tableItems.table.rows[0])) &&
                getUETable(tableItems.table).rowsNum < this.options.maxRowNum ? 0 : -1;
        },
        execCommand: function () {
            var rng = this.selection.getRange(),
                bk = rng.createBookmark(true);
            var tableItems = getTableItemsByRange(this),
                cell = tableItems.cell,
                table = tableItems.table,
                ut = getUETable(table),
                cellInfo = ut.getCellInfo(cell);
            //ut.insertRow(!ut.selectedTds.length ? cellInfo.rowIndex:ut.cellsRange.beginRowIndex,'');
            if (!ut.selectedTds.length) {
                ut.insertRow(cellInfo.rowIndex, cell);
            } else {
                var range = ut.cellsRange;
                for (var i = 0, len = range.endRowIndex - range.beginRowIndex + 1; i < len; i++) {
                    ut.insertRow(range.beginRowIndex, cell);
                }
            }
            rng.moveToBookmark(bk).select();
            if (table.getAttribute("interlaced") === "enabled")this.fireEvent("interlacetable", table);
        }
    };
    //後插入行
    UE.commands["insertrownext"] = {
        queryCommandState: function () {
            var tableItems = getTableItemsByRange(this),
                cell = tableItems.cell;
            return cell && (cell.tagName == "TD") && getUETable(tableItems.table).rowsNum < this.options.maxRowNum ? 0 : -1;
        },
        execCommand: function () {
            var rng = this.selection.getRange(),
                bk = rng.createBookmark(true);
            var tableItems = getTableItemsByRange(this),
                cell = tableItems.cell,
                table = tableItems.table,
                ut = getUETable(table),
                cellInfo = ut.getCellInfo(cell);
            //ut.insertRow(!ut.selectedTds.length? cellInfo.rowIndex + cellInfo.rowSpan : ut.cellsRange.endRowIndex + 1,'');
            if (!ut.selectedTds.length) {
                ut.insertRow(cellInfo.rowIndex + cellInfo.rowSpan, cell);
            } else {
                var range = ut.cellsRange;
                for (var i = 0, len = range.endRowIndex - range.beginRowIndex + 1; i < len; i++) {
                    ut.insertRow(range.endRowIndex + 1, cell);
                }
            }
            rng.moveToBookmark(bk).select();
            if (table.getAttribute("interlaced") === "enabled")this.fireEvent("interlacetable", table);
        }
    };
    UE.commands["deleterow"] = {
        queryCommandState: function () {
            var tableItems = getTableItemsByRange(this);
            return tableItems.cell ? 0 : -1;
        },
        execCommand: function () {
            var cell = getTableItemsByRange(this).cell,
                ut = getUETable(cell),
                cellsRange = ut.cellsRange,
                cellInfo = ut.getCellInfo(cell),
                preCell = ut.getVSideCell(cell),
                nextCell = ut.getVSideCell(cell, true),
                rng = this.selection.getRange();
            if (utils.isEmptyObject(cellsRange)) {
                ut.deleteRow(cellInfo.rowIndex);
            } else {
                for (var i = cellsRange.beginRowIndex; i < cellsRange.endRowIndex + 1; i++) {
                    ut.deleteRow(cellsRange.beginRowIndex);
                }
            }
            var table = ut.table;
            if (!table.getElementsByTagName('td').length) {
                var nextSibling = table.nextSibling;
                domUtils.remove(table);
                if (nextSibling) {
                    rng.setStart(nextSibling, 0).setCursor(false, true);
                }
            } else {
                if (cellInfo.rowSpan == 1 || cellInfo.rowSpan == cellsRange.endRowIndex - cellsRange.beginRowIndex + 1) {
                    if (nextCell || preCell) rng.selectNodeContents(nextCell || preCell).setCursor(false, true);
                } else {
                    var newCell = ut.getCell(cellInfo.rowIndex, ut.indexTable[cellInfo.rowIndex][cellInfo.colIndex].cellIndex);
                    if (newCell) rng.selectNodeContents(newCell).setCursor(false, true);
                }
            }
            if (table.getAttribute("interlaced") === "enabled")this.fireEvent("interlacetable", table);
        }
    };
    UE.commands["insertcol"] = {
        queryCommandState: function (cmd) {
            var tableItems = getTableItemsByRange(this),
                cell = tableItems.cell;
            return cell && (cell.tagName == "TD" || (cell.tagName == 'TH' && cell !== tableItems.tr.cells[0])) &&
                getUETable(tableItems.table).colsNum < this.options.maxColNum ? 0 : -1;
        },
        execCommand: function (cmd) {
            var rng = this.selection.getRange(),
                bk = rng.createBookmark(true);
            if (this.queryCommandState(cmd) == -1)return;
            var cell = getTableItemsByRange(this).cell,
                ut = getUETable(cell),
                cellInfo = ut.getCellInfo(cell);

            //ut.insertCol(!ut.selectedTds.length ? cellInfo.colIndex:ut.cellsRange.beginColIndex);
            if (!ut.selectedTds.length) {
                ut.insertCol(cellInfo.colIndex, cell);
            } else {
                var range = ut.cellsRange;
                for (var i = 0, len = range.endColIndex - range.beginColIndex + 1; i < len; i++) {
                    ut.insertCol(range.beginColIndex, cell);
                }
            }
            rng.moveToBookmark(bk).select(true);
        }
    };
    UE.commands["insertcolnext"] = {
        queryCommandState: function () {
            var tableItems = getTableItemsByRange(this),
                cell = tableItems.cell;
            return cell && getUETable(tableItems.table).colsNum < this.options.maxColNum ? 0 : -1;
        },
        execCommand: function () {
            var rng = this.selection.getRange(),
                bk = rng.createBookmark(true);
            var cell = getTableItemsByRange(this).cell,
                ut = getUETable(cell),
                cellInfo = ut.getCellInfo(cell);
            //ut.insertCol(!ut.selectedTds.length ? cellInfo.colIndex + cellInfo.colSpan:ut.cellsRange.endColIndex +1);
            if (!ut.selectedTds.length) {
                ut.insertCol(cellInfo.colIndex + cellInfo.colSpan, cell);
            } else {
                var range = ut.cellsRange;
                for (var i = 0, len = range.endColIndex - range.beginColIndex + 1; i < len; i++) {
                    ut.insertCol(range.endColIndex + 1, cell);
                }
            }
            rng.moveToBookmark(bk).select();
        }
    };

    UE.commands["deletecol"] = {
        queryCommandState: function () {
            var tableItems = getTableItemsByRange(this);
            return tableItems.cell ? 0 : -1;
        },
        execCommand: function () {
            var cell = getTableItemsByRange(this).cell,
                ut = getUETable(cell),
                range = ut.cellsRange,
                cellInfo = ut.getCellInfo(cell),
                preCell = ut.getHSideCell(cell),
                nextCell = ut.getHSideCell(cell, true);
            if (utils.isEmptyObject(range)) {
                ut.deleteCol(cellInfo.colIndex);
            } else {
                for (var i = range.beginColIndex; i < range.endColIndex + 1; i++) {
                    ut.deleteCol(range.beginColIndex);
                }
            }
            var table = ut.table,
                rng = this.selection.getRange();

            if (!table.getElementsByTagName('td').length) {
                var nextSibling = table.nextSibling;
                domUtils.remove(table);
                if (nextSibling) {
                    rng.setStart(nextSibling, 0).setCursor(false, true);
                }
            } else {
                if (domUtils.inDoc(cell, this.document)) {
                    rng.setStart(cell, 0).setCursor(false, true);
                } else {
                    if (nextCell && domUtils.inDoc(nextCell, this.document)) {
                        rng.selectNodeContents(nextCell).setCursor(false, true);
                    } else {
                        if (preCell && domUtils.inDoc(preCell, this.document)) {
                            rng.selectNodeContents(preCell).setCursor(true, true);
                        }
                    }
                }
            }
        }
    };
    UE.commands["splittocells"] = {
        queryCommandState: function () {
            var tableItems = getTableItemsByRange(this),
                cell = tableItems.cell;
            if (!cell) return -1;
            var ut = getUETable(tableItems.table);
            if (ut.selectedTds.length > 0) return -1;
            return cell && (cell.colSpan > 1 || cell.rowSpan > 1) ? 0 : -1;
        },
        execCommand: function () {
            var rng = this.selection.getRange(),
                bk = rng.createBookmark(true);
            var cell = getTableItemsByRange(this).cell,
                ut = getUETable(cell);
            ut.splitToCells(cell);
            rng.moveToBookmark(bk).select();
        }
    };
    UE.commands["splittorows"] = {
        queryCommandState: function () {
            var tableItems = getTableItemsByRange(this),
                cell = tableItems.cell;
            if (!cell) return -1;
            var ut = getUETable(tableItems.table);
            if (ut.selectedTds.length > 0) return -1;
            return cell && cell.rowSpan > 1 ? 0 : -1;
        },
        execCommand: function () {
            var rng = this.selection.getRange(),
                bk = rng.createBookmark(true);
            var cell = getTableItemsByRange(this).cell,
                ut = getUETable(cell);
            ut.splitToRows(cell);
            rng.moveToBookmark(bk).select();
        }
    };
    UE.commands["splittocols"] = {
        queryCommandState: function () {
            var tableItems = getTableItemsByRange(this),
                cell = tableItems.cell;
            if (!cell) return -1;
            var ut = getUETable(tableItems.table);
            if (ut.selectedTds.length > 0) return -1;
            return cell && cell.colSpan > 1 ? 0 : -1;
        },
        execCommand: function () {
            var rng = this.selection.getRange(),
                bk = rng.createBookmark(true);
            var cell = getTableItemsByRange(this).cell,
                ut = getUETable(cell);
            ut.splitToCols(cell);
            rng.moveToBookmark(bk).select();

        }
    };

    UE.commands["adaptbytext"] =
        UE.commands["adaptbywindow"] = {
            queryCommandState: function () {
                return getTableItemsByRange(this).table ? 0 : -1
            },
            execCommand: function (cmd) {
                var tableItems = getTableItemsByRange(this),
                    table = tableItems.table;
                if (table) {
                    if (cmd == 'adaptbywindow') {
                        resetTdWidth(table, this);
                    } else {
                        var cells = domUtils.getElementsByTagName(table, "td th");
                        utils.each(cells, function (cell) {
                            cell.removeAttribute("width");
                        });
                        table.removeAttribute("width");
                    }
                }
            }
        };

    //平均分配各列
    UE.commands['averagedistributecol'] = {
        queryCommandState: function () {
            var ut = getUETableBySelected(this);
            if (!ut) return -1;
            return ut.isFullRow() || ut.isFullCol() ? 0 : -1;
        },
        execCommand: function (cmd) {
            var me = this,
                ut = getUETableBySelected(me);

            function getAverageWidth() {
                var tb = ut.table,
                    averageWidth, sumWidth = 0, colsNum = 0,
                    tbAttr = getDefaultValue(me, tb);

                if (ut.isFullRow()) {
                    sumWidth = tb.offsetWidth;
                    colsNum = ut.colsNum;
                } else {
                    var begin = ut.cellsRange.beginColIndex,
                        end = ut.cellsRange.endColIndex,
                        node;
                    for (var i = begin; i <= end;) {
                        node = ut.selectedTds[i];
                        sumWidth += node.offsetWidth;
                        i += node.colSpan;
                        colsNum += 1;
                    }
                }
                averageWidth = Math.ceil(sumWidth / colsNum) - tbAttr.tdBorder * 2 - tbAttr.tdPadding * 2;
                return averageWidth;
            }

            function setAverageWidth(averageWidth) {
                utils.each(domUtils.getElementsByTagName(ut.table, "th"), function (node) {
                    node.setAttribute("width", "");
                });
                var cells = ut.isFullRow() ? domUtils.getElementsByTagName(ut.table, "td") : ut.selectedTds;

                utils.each(cells, function (node) {
                    if (node.colSpan == 1) {
                        node.setAttribute("width", averageWidth);
                    }
                });
            }

            if (ut && ut.selectedTds.length) {
                setAverageWidth(getAverageWidth());
            }
        }
    };
    //平均分配各行
    UE.commands['averagedistributerow'] = {
        queryCommandState: function () {
            var ut = getUETableBySelected(this);
            if (!ut) return -1;
            if (ut.selectedTds && /th/ig.test(ut.selectedTds[0].tagName)) return -1;
            return ut.isFullRow() || ut.isFullCol() ? 0 : -1;
        },
        execCommand: function (cmd) {
            var me = this,
                ut = getUETableBySelected(me);

            function getAverageHeight() {
                var averageHeight, rowNum, sumHeight = 0,
                    tb = ut.table,
                    tbAttr = getDefaultValue(me, tb),
                    tdpadding = parseInt(domUtils.getComputedStyle(tb.getElementsByTagName('td')[0], "padding-top"));

                if (ut.isFullCol()) {
                    var captionArr = domUtils.getElementsByTagName(tb, "caption"),
                        thArr = domUtils.getElementsByTagName(tb, "th"),
                        captionHeight, thHeight;

                    if (captionArr.length > 0) {
                        captionHeight = captionArr[0].offsetHeight;
                    }
                    if (thArr.length > 0) {
                        thHeight = thArr[0].offsetHeight;
                    }

                    sumHeight = tb.offsetHeight - (captionHeight || 0) - (thHeight || 0);
                    rowNum = thArr.length == 0 ? ut.rowsNum : (ut.rowsNum - 1);
                } else {
                    var begin = ut.cellsRange.beginRowIndex,
                        end = ut.cellsRange.endRowIndex,
                        count = 0,
                        trs = domUtils.getElementsByTagName(tb, "tr");
                    for (var i = begin; i <= end; i++) {
                        sumHeight += trs[i].offsetHeight;
                        count += 1;
                    }
                    rowNum = count;
                }
                //ie8下是混雜模式
                if (browser.ie && browser.version < 9) {
                    averageHeight = Math.ceil(sumHeight / rowNum);
                } else {
                    averageHeight = Math.ceil(sumHeight / rowNum) - tbAttr.tdBorder * 2 - tdpadding * 2;
                }
                return averageHeight;
            }

            function setAverageHeight(averageHeight) {
                var cells = ut.isFullCol() ? domUtils.getElementsByTagName(ut.table, "td") : ut.selectedTds;
                utils.each(cells, function (node) {
                    if (node.rowSpan == 1) {
                        node.setAttribute("height", averageHeight);
                    }
                });
            }

            if (ut && ut.selectedTds.length) {
                setAverageHeight(getAverageHeight());
            }
        }
    };

    //單元格對齊方式
    UE.commands['cellalignment'] = {
        queryCommandState: function () {
            return getTableItemsByRange(this).table ? 0 : -1
        },
        execCommand: function (cmd, data) {
            var me = this,
                ut = getUETableBySelected(me);

            if (!ut) {
                var start = me.selection.getStart(),
                    cell = start && domUtils.findParentByTagName(start, ["td", "th", "caption"], true);
                if (!/caption/ig.test(cell.tagName)) {
                    domUtils.setAttributes(cell, data);
                } else {
                    cell.style.textAlign = data.align;
                    cell.style.verticalAlign = data.vAlign;
                }
                me.selection.getRange().setCursor(true);
            } else {
                utils.each(ut.selectedTds, function (cell) {
                    domUtils.setAttributes(cell, data);
                });
            }
        },
        /**
         * 查詢當前點選的單元格的對齊狀態， 如果當前已經選擇了多個單元格， 則會返回所有單元格經過統一協調過後的狀態
         * @see UE.UETable.getTableCellAlignState
         */
        queryCommandValue: function (cmd) {

            var activeMenuCell = getTableItemsByRange( this).cell;

            if( !activeMenuCell ) {
                activeMenuCell = getSelectedArr(this)[0];
            }

            if (!activeMenuCell) {

                return null;

            } else {

                //獲取同時選中的其他單元格
                var cells = UE.UETable.getUETable(activeMenuCell).selectedTds;

                !cells.length && ( cells = activeMenuCell );

                return UE.UETable.getTableCellAlignState(cells);

            }

        }
    };
    //表格對齊方式
    UE.commands['tablealignment'] = {
        queryCommandState: function () {
            if (browser.ie && browser.version < 8) {
                return -1;
            }
            return getTableItemsByRange(this).table ? 0 : -1
        },
        execCommand: function (cmd, value) {
            var me = this,
                start = me.selection.getStart(),
                table = start && domUtils.findParentByTagName(start, ["table"], true);

            if (table) {
                table.setAttribute("align",value);
            }
        }
    };

    //表格屬性
    UE.commands['edittable'] = {
        queryCommandState: function () {
            return getTableItemsByRange(this).table ? 0 : -1
        },
        execCommand: function (cmd, color) {
            var rng = this.selection.getRange(),
                table = domUtils.findParentByTagName(rng.startContainer, 'table');
            if (table) {
                var arr = domUtils.getElementsByTagName(table, "td").concat(
                    domUtils.getElementsByTagName(table, "th"),
                    domUtils.getElementsByTagName(table, "caption")
                );
                utils.each(arr, function (node) {
                    node.style.borderColor = color;
                });
            }
        }
    };
    //單元格屬性
    UE.commands['edittd'] = {
        queryCommandState: function () {
            return getTableItemsByRange(this).table ? 0 : -1
        },
        execCommand: function (cmd, bkColor) {
            var me = this,
                ut = getUETableBySelected(me);

            if (!ut) {
                var start = me.selection.getStart(),
                    cell = start && domUtils.findParentByTagName(start, ["td", "th", "caption"], true);
                if (cell) {
                    cell.style.backgroundColor = bkColor;
                }
            } else {
                utils.each(ut.selectedTds, function (cell) {
                    cell.style.backgroundColor = bkColor;
                });
            }
        }
    };

    UE.commands["settablebackground"] = {
        queryCommandState: function () {
            return getSelectedArr(this).length > 1 ? 0 : -1;
        },
        execCommand: function (cmd, value) {
            var cells, ut;
            cells = getSelectedArr(this);
            ut = getUETable(cells[0]);
            ut.setBackground(cells, value);
        }
    };

    UE.commands["cleartablebackground"] = {
        queryCommandState: function () {
            var cells = getSelectedArr(this);
            if (!cells.length)return -1;
            for (var i = 0, cell; cell = cells[i++];) {
                if (cell.style.backgroundColor !== "") return 0;
            }
            return -1;
        },
        execCommand: function () {
            var cells = getSelectedArr(this),
                ut = getUETable(cells[0]);
            ut.removeBackground(cells);
        }
    };

    UE.commands["interlacetable"] = UE.commands["uninterlacetable"] = {
        queryCommandState: function (cmd) {
            var table = getTableItemsByRange(this).table;
            if (!table) return -1;
            var interlaced = table.getAttribute("interlaced");
            if (cmd == "interlacetable") {
                //TODO 待定
                //是否需要待定，如果設定，則命令只能單次執行成功，但反射具備toggle效果；否則可以覆蓋前次命令，但反射將不存在toggle效果
                return (interlaced === "enabled") ? -1 : 0;
            } else {
                return (!interlaced || interlaced === "disabled") ? -1 : 0;
            }
        },
        execCommand: function (cmd, classList) {
            var table = getTableItemsByRange(this).table;
            if (cmd == "interlacetable") {
                table.setAttribute("interlaced", "enabled");
                this.fireEvent("interlacetable", table, classList);
            } else {
                table.setAttribute("interlaced", "disabled");
                this.fireEvent("uninterlacetable", table);
            }
        }
    };
    UE.commands["setbordervisible"] = {
        queryCommandState: function (cmd) {
            var table = getTableItemsByRange(this).table;
            if (!table) return -1;
            return 0;
        },
        execCommand: function () {
            var table = getTableItemsByRange(this).table;
            utils.each(domUtils.getElementsByTagName(table,'td'),function(td){
                td.style.borderWidth = '1px';
                td.style.borderStyle = 'solid';
            })
        }
    };
    function resetTdWidth(table, editor) {
        var tds = domUtils.getElementsByTagName(table,'td th');
        utils.each(tds, function (td) {
            td.removeAttribute("width");
        });
        table.setAttribute('width', getTableWidth(editor, true, getDefaultValue(editor, table)));
        var tdsWidths = [];
        setTimeout(function () {
            utils.each(tds, function (td) {
                (td.colSpan == 1) && tdsWidths.push(td.offsetWidth)
            })
            utils.each(tds, function (td,i) {
                (td.colSpan == 1) && td.setAttribute("width", tdsWidths[i] + "");
            })
        }, 0);
    }

    function getTableWidth(editor, needIEHack, defaultValue) {
        var body = editor.body;
        return body.offsetWidth - (needIEHack ? parseInt(domUtils.getComputedStyle(body, 'margin-left'), 10) * 2 : 0) - defaultValue.tableBorder * 2 - (editor.options.offsetWidth || 0);
    }

    function getSelectedArr(editor) {
        var cell = getTableItemsByRange(editor).cell;
        if (cell) {
            var ut = getUETable(cell);
            return ut.selectedTds.length ? ut.selectedTds : [cell];
        } else {
            return [];
        }
    }
})();

// plugins/table.action.js
/**
 * Created with JetBrains PhpStorm.
 * User: taoqili
 * Date: 12-10-12
 * Time: 上午10:05
 * To change this template use File | Settings | File Templates.
 */
UE.plugins['table'] = function () {
    var me = this,
        tabTimer = null,
        //拖動計時器
        tableDragTimer = null,
        //雙擊計時器
        tableResizeTimer = null,
        //單元格最小寬度
        cellMinWidth = 5,
        isInResizeBuffer = false,
        //單元格邊框大小
        cellBorderWidth = 5,
        //滑鼠偏移距離
        offsetOfTableCell = 10,
        //記錄在有限時間內的點選狀態， 共有3個取值， 0, 1, 2。 0代表未初始化， 1代表單擊了1次，2代表2次
        singleClickState = 0,
        userActionStatus = null,
        //雙擊允許的時間範圍
        dblclickTime = 360,
        UT = UE.UETable,
        getUETable = function (tdOrTable) {
            return UT.getUETable(tdOrTable);
        },
        getUETableBySelected = function (editor) {
            return UT.getUETableBySelected(editor);
        },
        getDefaultValue = function (editor, table) {
            return UT.getDefaultValue(editor, table);
        },
        removeSelectedClass = function (cells) {
            return UT.removeSelectedClass(cells);
        };

    function showError(e) {
//        throw e;
    }
    me.ready(function(){
        var me = this;
        var orgGetText = me.selection.getText;
        me.selection.getText = function(){
            var table = getUETableBySelected(me);
            if(table){
                var str = '';
                utils.each(table.selectedTds,function(td){
                    str += td[browser.ie?'innerText':'textContent'];
                })
                return str;
            }else{
                return orgGetText.call(me.selection)
            }

        }
    })

    //處理拖動及框選相關方法
    var startTd = null, //滑鼠按下時的錨點td
        currentTd = null, //當前滑鼠經過時的td
        onDrag = "", //指示當前拖動狀態，其值可為"","h","v" ,分別表示未拖動狀態，橫向拖動狀態，縱向拖動狀態，用於滑鼠移動過程中的判斷
        onBorder = false, //檢測滑鼠按下時是否處在單元格邊緣位置
        dragButton = null,
        dragOver = false,
        dragLine = null, //模擬的拖動線
        dragTd = null;    //發生拖動的目標td

    var mousedown = false,
    //todo 判斷混亂模式
        needIEHack = true;

    me.setOpt({
        'maxColNum':20,
        'maxRowNum':100,
        'defaultCols':5,
        'defaultRows':5,
        'tdvalign':'top',
        'cursorpath':me.options.UEDITOR_HOME_URL + "themes/default/images/cursor_",
        'tableDragable':false,
        'classList':["ue-table-interlace-color-single","ue-table-interlace-color-double"]
    });
    me.getUETable = getUETable;
    var commands = {
        'deletetable':1,
        'inserttable':1,
        'cellvalign':1,
        'insertcaption':1,
        'deletecaption':1,
        'inserttitle':1,
        'deletetitle':1,
        "mergeright":1,
        "mergedown":1,
        "mergecells":1,
        "insertrow":1,
        "insertrownext":1,
        "deleterow":1,
        "insertcol":1,
        "insertcolnext":1,
        "deletecol":1,
        "splittocells":1,
        "splittorows":1,
        "splittocols":1,
        "adaptbytext":1,
        "adaptbywindow":1,
        "adaptbycustomer":1,
        "insertparagraph":1,
        "insertparagraphbeforetable":1,
        "averagedistributecol":1,
        "averagedistributerow":1
    };
    me.ready(function () {
        utils.cssRule('table',
            //選中的td上的樣式
            '.selectTdClass{background-color:#edf5fa !important}' +
                'table.noBorderTable td,table.noBorderTable th,table.noBorderTable caption{border:1px dashed #ddd !important}' +
                //插入的表格的預設樣式
                'table{margin-bottom:10px;border-collapse:collapse;display:table;}' +
                'td,th{padding: 5px 10px;border: 1px solid #DDD;}' +
                'caption{border:1px dashed #DDD;border-bottom:0;padding:3px;text-align:center;}' +
                'th{border-top:1px solid #BBB;background-color:#F7F7F7;}' +
                'table tr.firstRow th{border-top-width:2px;}' +
                '.ue-table-interlace-color-single{ background-color: #fcfcfc; } .ue-table-interlace-color-double{ background-color: #f7faff; }' +
                'td p{margin:0;padding:0;}', me.document);

        var tableCopyList, isFullCol, isFullRow;
        //註冊del/backspace事件
        me.addListener('keydown', function (cmd, evt) {
            var me = this;
            var keyCode = evt.keyCode || evt.which;

            if (keyCode == 8) {

                var ut = getUETableBySelected(me);
                if (ut && ut.selectedTds.length) {

                    if (ut.isFullCol()) {
                        me.execCommand('deletecol')
                    } else if (ut.isFullRow()) {
                        me.execCommand('deleterow')
                    } else {
                        me.fireEvent('delcells');
                    }
                    domUtils.preventDefault(evt);
                }

                var caption = domUtils.findParentByTagName(me.selection.getStart(), 'caption', true),
                    range = me.selection.getRange();
                if (range.collapsed && caption && isEmptyBlock(caption)) {
                    me.fireEvent('saveScene');
                    var table = caption.parentNode;
                    domUtils.remove(caption);
                    if (table) {
                        range.setStart(table.rows[0].cells[0], 0).setCursor(false, true);
                    }
                    me.fireEvent('saveScene');
                }

            }

            if (keyCode == 46) {

                ut = getUETableBySelected(me);
                if (ut) {
                    me.fireEvent('saveScene');
                    for (var i = 0, ci; ci = ut.selectedTds[i++];) {
                        domUtils.fillNode(me.document, ci)
                    }
                    me.fireEvent('saveScene');
                    domUtils.preventDefault(evt);

                }

            }
            if (keyCode == 13) {

                var rng = me.selection.getRange(),
                    caption = domUtils.findParentByTagName(rng.startContainer, 'caption', true);
                if (caption) {
                    var table = domUtils.findParentByTagName(caption, 'table');
                    if (!rng.collapsed) {

                        rng.deleteContents();
                        me.fireEvent('saveScene');
                    } else {
                        if (caption) {
                            rng.setStart(table.rows[0].cells[0], 0).setCursor(false, true);
                        }
                    }
                    domUtils.preventDefault(evt);
                    return;
                }
                if (rng.collapsed) {
                    var table = domUtils.findParentByTagName(rng.startContainer, 'table');
                    if (table) {
                        var cell = table.rows[0].cells[0],
                            start = domUtils.findParentByTagName(me.selection.getStart(), ['td', 'th'], true),
                            preNode = table.previousSibling;
                        if (cell === start && (!preNode || preNode.nodeType == 1 && preNode.tagName == 'TABLE' ) && domUtils.isStartInblock(rng)) {
                            var first = domUtils.findParent(me.selection.getStart(), function(n){return domUtils.isBlockElm(n)}, true);
                            if(first && ( /t(h|d)/i.test(first.tagName) || first ===  start.firstChild )){
                                me.execCommand('insertparagraphbeforetable');
                                domUtils.preventDefault(evt);
                            }

                        }
                    }
                }
            }

            if ((evt.ctrlKey || evt.metaKey) && evt.keyCode == '67') {
                tableCopyList = null;
                var ut = getUETableBySelected(me);
                if (ut) {
                    var tds = ut.selectedTds;
                    isFullCol = ut.isFullCol();
                    isFullRow = ut.isFullRow();
                    tableCopyList = [
                        [ut.cloneCell(tds[0],null,true)]
                    ];
                    for (var i = 1, ci; ci = tds[i]; i++) {
                        if (ci.parentNode !== tds[i - 1].parentNode) {
                            tableCopyList.push([ut.cloneCell(ci,null,true)]);
                        } else {
                            tableCopyList[tableCopyList.length - 1].push(ut.cloneCell(ci,null,true));
                        }

                    }
                }
            }
        });
        me.addListener("tablehasdeleted",function(){
            toggleDraggableState(this, false, "", null);
            if (dragButton)domUtils.remove(dragButton);
        });

        me.addListener('beforepaste', function (cmd, html) {
            var me = this;
            var rng = me.selection.getRange();
            if (domUtils.findParentByTagName(rng.startContainer, 'caption', true)) {
                var div = me.document.createElement("div");
                div.innerHTML = html.html;
                //trace:3729
                html.html = div[browser.ie9below ? 'innerText' : 'textContent'];
                return;
            }
            var table = getUETableBySelected(me);
            if (tableCopyList) {
                me.fireEvent('saveScene');
                var rng = me.selection.getRange();
                var td = domUtils.findParentByTagName(rng.startContainer, ['td', 'th'], true), tmpNode, preNode;
                if (td) {
                    var ut = getUETable(td);
                    if (isFullRow) {
                        var rowIndex = ut.getCellInfo(td).rowIndex;
                        if (td.tagName == 'TH') {
                            rowIndex++;
                        }
                        for (var i = 0, ci; ci = tableCopyList[i++];) {
                            var tr = ut.insertRow(rowIndex++, "td");
                            for (var j = 0, cj; cj = ci[j]; j++) {
                                var cell = tr.cells[j];
                                if (!cell) {
                                    cell = tr.insertCell(j)
                                }
                                cell.innerHTML = cj.innerHTML;
                                cj.getAttribute('width') && cell.setAttribute('width', cj.getAttribute('width'));
                                cj.getAttribute('vAlign') && cell.setAttribute('vAlign', cj.getAttribute('vAlign'));
                                cj.getAttribute('align') && cell.setAttribute('align', cj.getAttribute('align'));
                                cj.style.cssText && (cell.style.cssText = cj.style.cssText)
                            }
                            for (var j = 0, cj; cj = tr.cells[j]; j++) {
                                if (!ci[j])
                                    break;
                                cj.innerHTML = ci[j].innerHTML;
                                ci[j].getAttribute('width') && cj.setAttribute('width', ci[j].getAttribute('width'));
                                ci[j].getAttribute('vAlign') && cj.setAttribute('vAlign', ci[j].getAttribute('vAlign'));
                                ci[j].getAttribute('align') && cj.setAttribute('align', ci[j].getAttribute('align'));
                                ci[j].style.cssText && (cj.style.cssText = ci[j].style.cssText)
                            }
                        }
                    } else {
                        if (isFullCol) {
                            cellInfo = ut.getCellInfo(td);
                            var maxColNum = 0;
                            for (var j = 0, ci = tableCopyList[0], cj; cj = ci[j++];) {
                                maxColNum += cj.colSpan || 1;
                            }
                            me.__hasEnterExecCommand = true;
                            for (i = 0; i < maxColNum; i++) {
                                me.execCommand('insertcol');
                            }
                            me.__hasEnterExecCommand = false;
                            td = ut.table.rows[0].cells[cellInfo.cellIndex];
                            if (td.tagName == 'TH') {
                                td = ut.table.rows[1].cells[cellInfo.cellIndex];
                            }
                        }
                        for (var i = 0, ci; ci = tableCopyList[i++];) {
                            tmpNode = td;
                            for (var j = 0, cj; cj = ci[j++];) {
                                if (td) {
                                    td.innerHTML = cj.innerHTML;
                                    //todo 定製處理
                                    cj.getAttribute('width') && td.setAttribute('width', cj.getAttribute('width'));
                                    cj.getAttribute('vAlign') && td.setAttribute('vAlign', cj.getAttribute('vAlign'));
                                    cj.getAttribute('align') && td.setAttribute('align', cj.getAttribute('align'));
                                    cj.style.cssText && (td.style.cssText = cj.style.cssText);
                                    preNode = td;
                                    td = td.nextSibling;
                                } else {
                                    var cloneTd = cj.cloneNode(true);
                                    domUtils.removeAttributes(cloneTd, ['class', 'rowSpan', 'colSpan']);

                                    preNode.parentNode.appendChild(cloneTd)
                                }
                            }
                            td = ut.getNextCell(tmpNode, true, true);
                            if (!tableCopyList[i])
                                break;
                            if (!td) {
                                var cellInfo = ut.getCellInfo(tmpNode);
                                ut.table.insertRow(ut.table.rows.length);
                                ut.update();
                                td = ut.getVSideCell(tmpNode, true);
                            }
                        }
                    }
                    ut.update();
                } else {
                    table = me.document.createElement('table');
                    for (var i = 0, ci; ci = tableCopyList[i++];) {
                        var tr = table.insertRow(table.rows.length);
                        for (var j = 0, cj; cj = ci[j++];) {
                            cloneTd = UT.cloneCell(cj,null,true);
                            domUtils.removeAttributes(cloneTd, ['class']);
                            tr.appendChild(cloneTd)
                        }
                        if (j == 2 && cloneTd.rowSpan > 1) {
                            cloneTd.rowSpan = 1;
                        }
                    }

                    var defaultValue = getDefaultValue(me),
                        width = me.body.offsetWidth -
                            (needIEHack ? parseInt(domUtils.getComputedStyle(me.body, 'margin-left'), 10) * 2 : 0) - defaultValue.tableBorder * 2 - (me.options.offsetWidth || 0);
                    me.execCommand('insertHTML', '<table  ' +
                        ( isFullCol && isFullRow ? 'width="' + width + '"' : '') +
                        '>' + table.innerHTML.replace(/>\s*</g, '><').replace(/\bth\b/gi, "td") + '</table>')
                }
                me.fireEvent('contentchange');
                me.fireEvent('saveScene');
                html.html = '';
                return true;
            } else {
                var div = me.document.createElement("div"), tables;
                div.innerHTML = html.html;
                tables = div.getElementsByTagName("table");
                if (domUtils.findParentByTagName(me.selection.getStart(), 'table')) {
                    utils.each(tables, function (t) {
                        domUtils.remove(t)
                    });
                    if (domUtils.findParentByTagName(me.selection.getStart(), 'caption', true)) {
                        div.innerHTML = div[browser.ie ? 'innerText' : 'textContent'];
                    }
                } else {
                    utils.each(tables, function (table) {
                        removeStyleSize(table, true);
                        domUtils.removeAttributes(table, ['style', 'border']);
                        utils.each(domUtils.getElementsByTagName(table, "td"), function (td) {
                            if (isEmptyBlock(td)) {
                                domUtils.fillNode(me.document, td);
                            }
                            removeStyleSize(td, true);
//                            domUtils.removeAttributes(td, ['style'])
                        });
                    });
                }
                html.html = div.innerHTML;
            }
        });

        me.addListener('afterpaste', function () {
            utils.each(domUtils.getElementsByTagName(me.body, "table"), function (table) {
                if (table.offsetWidth > me.body.offsetWidth) {
                    var defaultValue = getDefaultValue(me, table);
                    table.style.width = me.body.offsetWidth - (needIEHack ? parseInt(domUtils.getComputedStyle(me.body, 'margin-left'), 10) * 2 : 0) - defaultValue.tableBorder * 2 - (me.options.offsetWidth || 0) + 'px'
                }
            })
        });
        me.addListener('blur', function () {
            tableCopyList = null;
        });
        var timer;
        me.addListener('keydown', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                var rng = me.selection.getRange(),
                    cell = domUtils.findParentByTagName(rng.startContainer, ['th', 'td'], true);
                if (cell) {
                    var table = cell.parentNode.parentNode.parentNode;
                    if (table.offsetWidth > table.getAttribute("width")) {
                        cell.style.wordBreak = "break-all";
                    }
                }

            }, 100);
        });
        me.addListener("selectionchange", function () {
            toggleDraggableState(me, false, "", null);
        });


        //內容變化時觸發索引更新
        //todo 可否考慮標記檢測，如果不涉及表格的變化就不進行索引重建和更新
        me.addListener("contentchange", function () {
            var me = this;
            //儘可能排除一些不需要更新的狀況
            hideDragLine(me);
            if (getUETableBySelected(me))return;
            var rng = me.selection.getRange();
            var start = rng.startContainer;
            start = domUtils.findParentByTagName(start, ['td', 'th'], true);
            utils.each(domUtils.getElementsByTagName(me.document, 'table'), function (table) {
                if (me.fireEvent("excludetable", table) === true) return;
                table.ueTable = new UT(table);
                //trace:3742
//                utils.each(domUtils.getElementsByTagName(me.document, 'td'), function (td) {
//
//                    if (domUtils.isEmptyBlock(td) && td !== start) {
//                        domUtils.fillNode(me.document, td);
//                        if (browser.ie && browser.version == 6) {
//                            td.innerHTML = '&nbsp;'
//                        }
//                    }
//                });
//                utils.each(domUtils.getElementsByTagName(me.document, 'th'), function (th) {
//                    if (domUtils.isEmptyBlock(th) && th !== start) {
//                        domUtils.fillNode(me.document, th);
//                        if (browser.ie && browser.version == 6) {
//                            th.innerHTML = '&nbsp;'
//                        }
//                    }
//                });
                table.onmouseover = function () {
                    me.fireEvent('tablemouseover', table);
                };
                table.onmousemove = function () {
                    me.fireEvent('tablemousemove', table);
                    me.options.tableDragable && toggleDragButton(true, this, me);
                    utils.defer(function(){
                        me.fireEvent('contentchange',50)
                    },true)
                };
                table.onmouseout = function () {
                    me.fireEvent('tablemouseout', table);
                    toggleDraggableState(me, false, "", null);
                    hideDragLine(me);
                };
                table.onclick = function (evt) {
                    evt = me.window.event || evt;
                    var target = getParentTdOrTh(evt.target || evt.srcElement);
                    if (!target)return;
                    var ut = getUETable(target),
                        table = ut.table,
                        cellInfo = ut.getCellInfo(target),
                        cellsRange,
                        rng = me.selection.getRange();
//                    if ("topLeft" == inPosition(table, mouseCoords(evt))) {
//                        cellsRange = ut.getCellsRange(ut.table.rows[0].cells[0], ut.getLastCell());
//                        ut.setSelected(cellsRange);
//                        return;
//                    }
//                    if ("bottomRight" == inPosition(table, mouseCoords(evt))) {
//
//                        return;
//                    }
                    if (inTableSide(table, target, evt, true)) {
                        var endTdCol = ut.getCell(ut.indexTable[ut.rowsNum - 1][cellInfo.colIndex].rowIndex, ut.indexTable[ut.rowsNum - 1][cellInfo.colIndex].cellIndex);
                        if (evt.shiftKey && ut.selectedTds.length) {
                            if (ut.selectedTds[0] !== endTdCol) {
                                cellsRange = ut.getCellsRange(ut.selectedTds[0], endTdCol);
                                ut.setSelected(cellsRange);
                            } else {
                                rng && rng.selectNodeContents(endTdCol).select();
                            }
                        } else {
                            if (target !== endTdCol) {
                                cellsRange = ut.getCellsRange(target, endTdCol);
                                ut.setSelected(cellsRange);
                            } else {
                                rng && rng.selectNodeContents(endTdCol).select();
                            }
                        }
                        return;
                    }
                    if (inTableSide(table, target, evt)) {
                        var endTdRow = ut.getCell(ut.indexTable[cellInfo.rowIndex][ut.colsNum - 1].rowIndex, ut.indexTable[cellInfo.rowIndex][ut.colsNum - 1].cellIndex);
                        if (evt.shiftKey && ut.selectedTds.length) {
                            if (ut.selectedTds[0] !== endTdRow) {
                                cellsRange = ut.getCellsRange(ut.selectedTds[0], endTdRow);
                                ut.setSelected(cellsRange);
                            } else {
                                rng && rng.selectNodeContents(endTdRow).select();
                            }
                        } else {
                            if (target !== endTdRow) {
                                cellsRange = ut.getCellsRange(target, endTdRow);
                                ut.setSelected(cellsRange);
                            } else {
                                rng && rng.selectNodeContents(endTdRow).select();
                            }
                        }
                    }
                };
            });

            switchBorderColor(me, true);
        });

        domUtils.on(me.document, "mousemove", mouseMoveEvent);

        domUtils.on(me.document, "mouseout", function (evt) {
            var target = evt.target || evt.srcElement;
            if (target.tagName == "TABLE") {
                toggleDraggableState(me, false, "", null);
            }
        });
        /**
         * 表格隔行變色
         */
        me.addListener("interlacetable",function(type,table,classList){
            if(!table) return;
            var me = this,
                rows = table.rows,
                len = rows.length,
                getClass = function(list,index,repeat){
                    return list[index] ? list[index] : repeat ? list[index % list.length]: "";
                };
            for(var i = 0;i<len;i++){
                rows[i].className = getClass( classList|| me.options.classList,i,true);
            }
        });
        me.addListener("uninterlacetable",function(type,table){
            if(!table) return;
            var me = this,
                rows = table.rows,
                classList = me.options.classList,
                len = rows.length;
            for(var i = 0;i<len;i++){
                domUtils.removeClasses( rows[i], classList );
            }
        });

        me.addListener("mousedown", mouseDownEvent);
        me.addListener("mouseup", mouseUpEvent);
        //拖動的時候觸發mouseup
        domUtils.on( me.body, 'dragstart', function( evt ){
            mouseUpEvent.call( me, 'dragstart', evt );
        });
        me.addOutputRule(function(root){
            utils.each(root.getNodesByTagName('div'),function(n){
                if (n.getAttr('id') == 'ue_tableDragLine') {
                    n.parentNode.removeChild(n);
                }
            });
        });

        var currentRowIndex = 0;
        me.addListener("mousedown", function () {
            currentRowIndex = 0;
        });
        me.addListener('tabkeydown', function () {
            var range = this.selection.getRange(),
                common = range.getCommonAncestor(true, true),
                table = domUtils.findParentByTagName(common, 'table');
            if (table) {
                if (domUtils.findParentByTagName(common, 'caption', true)) {
                    var cell = domUtils.getElementsByTagName(table, 'th td');
                    if (cell && cell.length) {
                        range.setStart(cell[0], 0).setCursor(false, true)
                    }
                } else {
                    var cell = domUtils.findParentByTagName(common, ['td', 'th'], true),
                        ua = getUETable(cell);
                    currentRowIndex = cell.rowSpan > 1 ? currentRowIndex : ua.getCellInfo(cell).rowIndex;
                    var nextCell = ua.getTabNextCell(cell, currentRowIndex);
                    if (nextCell) {
                        if (isEmptyBlock(nextCell)) {
                            range.setStart(nextCell, 0).setCursor(false, true)
                        } else {
                            range.selectNodeContents(nextCell).select()
                        }
                    } else {
                        me.fireEvent('saveScene');
                        me.__hasEnterExecCommand = true;
                        this.execCommand('insertrownext');
                        me.__hasEnterExecCommand = false;
                        range = this.selection.getRange();
                        range.setStart(table.rows[table.rows.length - 1].cells[0], 0).setCursor();
                        me.fireEvent('saveScene');
                    }
                }
                return true;
            }

        });
        browser.ie && me.addListener('selectionchange', function () {
            toggleDraggableState(this, false, "", null);
        });
        me.addListener("keydown", function (type, evt) {
            var me = this;
            //處理在表格的最後一個輸入tab產生新的表格
            var keyCode = evt.keyCode || evt.which;
            if (keyCode == 8 || keyCode == 46) {
                return;
            }
            var notCtrlKey = !evt.ctrlKey && !evt.metaKey && !evt.shiftKey && !evt.altKey;
            notCtrlKey && removeSelectedClass(domUtils.getElementsByTagName(me.body, "td"));
            var ut = getUETableBySelected(me);
            if (!ut) return;
            notCtrlKey && ut.clearSelected();
        });

        me.addListener("beforegetcontent", function () {
            switchBorderColor(this, false);
            browser.ie && utils.each(this.document.getElementsByTagName('caption'), function (ci) {
                if (domUtils.isEmptyNode(ci)) {
                    ci.innerHTML = '&nbsp;'
                }
            });
        });
        me.addListener("aftergetcontent", function () {
            switchBorderColor(this, true);
        });
        me.addListener("getAllHtml", function () {
            removeSelectedClass(me.document.getElementsByTagName("td"));
        });
        //修正全屏狀態下插入的表格寬度在非全屏狀態下撐開編輯器的情況
        me.addListener("fullscreenchanged", function (type, fullscreen) {
            if (!fullscreen) {
                var ratio = this.body.offsetWidth / document.body.offsetWidth,
                    tables = domUtils.getElementsByTagName(this.body, "table");
                utils.each(tables, function (table) {
                    if (table.offsetWidth < me.body.offsetWidth) return false;
                    var tds = domUtils.getElementsByTagName(table, "td"),
                        backWidths = [];
                    utils.each(tds, function (td) {
                        backWidths.push(td.offsetWidth);
                    });
                    for (var i = 0, td; td = tds[i]; i++) {
                        td.setAttribute("width", Math.floor(backWidths[i] * ratio));
                    }
                    table.setAttribute("width", Math.floor(getTableWidth(me, needIEHack, getDefaultValue(me))))
                });
            }
        });

        //重寫execCommand命令，用於處理框選時的處理
        var oldExecCommand = me.execCommand;
        me.execCommand = function (cmd, datatat) {

            var me = this,
                args = arguments;

            cmd = cmd.toLowerCase();
            var ut = getUETableBySelected(me), tds,
                range = new dom.Range(me.document),
                cmdFun = me.commands[cmd] || UE.commands[cmd],
                result;
            if (!cmdFun) return;
            if (ut && !commands[cmd] && !cmdFun.notNeedUndo && !me.__hasEnterExecCommand) {
                me.__hasEnterExecCommand = true;
                me.fireEvent("beforeexeccommand", cmd);
                tds = ut.selectedTds;
                var lastState = -2, lastValue = -2, value, state;
                for (var i = 0, td; td = tds[i]; i++) {
                    if (isEmptyBlock(td)) {
                        range.setStart(td, 0).setCursor(false, true)
                    } else {
                        range.selectNode(td).select(true);
                    }
                    state = me.queryCommandState(cmd);
                    value = me.queryCommandValue(cmd);
                    if (state != -1) {
                        if (lastState !== state || lastValue !== value) {
                            me._ignoreContentChange = true;
                            result = oldExecCommand.apply(me, arguments);
                            me._ignoreContentChange = false;

                        }
                        lastState = me.queryCommandState(cmd);
                        lastValue = me.queryCommandValue(cmd);
                        if (domUtils.isEmptyBlock(td)) {
                            domUtils.fillNode(me.document, td)
                        }
                    }
                }
                range.setStart(tds[0], 0).shrinkBoundary(true).setCursor(false, true);
                me.fireEvent('contentchange');
                me.fireEvent("afterexeccommand", cmd);
                me.__hasEnterExecCommand = false;
                me._selectionChange();
            } else {
                result = oldExecCommand.apply(me, arguments);
            }
            return result;
        };


    });
    /**
     * 刪除obj的寬高style，改成屬性寬高
     * @param obj
     * @param replaceToProperty
     */
    function removeStyleSize(obj, replaceToProperty) {
        removeStyle(obj, "width", true);
        removeStyle(obj, "height", true);
    }

    function removeStyle(obj, styleName, replaceToProperty) {
        if (obj.style[styleName]) {
            replaceToProperty && obj.setAttribute(styleName, parseInt(obj.style[styleName], 10));
            obj.style[styleName] = "";
        }
    }

    function getParentTdOrTh(ele) {
        if (ele.tagName == "TD" || ele.tagName == "TH") return ele;
        var td;
        if (td = domUtils.findParentByTagName(ele, "td", true) || domUtils.findParentByTagName(ele, "th", true)) return td;
        return null;
    }

    function isEmptyBlock(node) {
        var reg = new RegExp(domUtils.fillChar, 'g');
        if (node[browser.ie ? 'innerText' : 'textContent'].replace(/^\s*$/, '').replace(reg, '').length > 0) {
            return 0;
        }
        for (var n in dtd.$isNotEmpty) {
            if (node.getElementsByTagName(n).length) {
                return 0;
            }
        }
        return 1;
    }


    function mouseCoords(evt) {
        if (evt.pageX || evt.pageY) {
            return { x:evt.pageX, y:evt.pageY };
        }
        return {
            x:evt.clientX + me.document.body.scrollLeft - me.document.body.clientLeft,
            y:evt.clientY + me.document.body.scrollTop - me.document.body.clientTop
        };
    }

    function mouseMoveEvent(evt) {

        if( isEditorDisabled() ) {
            return;
        }

        try {

            //普通狀態下滑鼠移動
            var target = getParentTdOrTh(evt.target || evt.srcElement),
                pos;

            //區分使用者的行為是拖動還是雙擊
            if( isInResizeBuffer  ) {

                me.body.style.webkitUserSelect = 'none';

                if( Math.abs( userActionStatus.x - evt.clientX ) > offsetOfTableCell || Math.abs( userActionStatus.y - evt.clientY ) > offsetOfTableCell ) {
                    clearTableDragTimer();
                    isInResizeBuffer = false;
                    singleClickState = 0;
                    //drag action
                    tableBorderDrag(evt);
                }
            }

            //修改單元格大小時的滑鼠移動
            if (onDrag && dragTd) {
                singleClickState = 0;
                me.body.style.webkitUserSelect = 'none';
                me.selection.getNative()[browser.ie9below ? 'empty' : 'removeAllRanges']();
                pos = mouseCoords(evt);
                toggleDraggableState(me, true, onDrag, pos, target);
                if (onDrag == "h") {
                    dragLine.style.left = getPermissionX(dragTd, evt) + "px";
                } else if (onDrag == "v") {
                    dragLine.style.top = getPermissionY(dragTd, evt) + "px";
                }
                return;
            }
            //當滑鼠處於table上時，修改移動過程中的游標狀態
            if (target) {
                //針對使用table作為容器的元件不觸發拖拽效果
                if (me.fireEvent('excludetable', target) === true)
                    return;
                pos = mouseCoords(evt);
                var state = getRelation(target, pos),
                    table = domUtils.findParentByTagName(target, "table", true);

                if (inTableSide(table, target, evt, true)) {
                    if (me.fireEvent("excludetable", table) === true) return;
                    me.body.style.cursor = "url(" + me.options.cursorpath + "h.png),pointer";
                } else if (inTableSide(table, target, evt)) {
                    if (me.fireEvent("excludetable", table) === true) return;
                    me.body.style.cursor = "url(" + me.options.cursorpath + "v.png),pointer";
                } else {
                    me.body.style.cursor = "text";
                    var curCell = target;
                    if (/\d/.test(state)) {
                        state = state.replace(/\d/, '');
                        target = getUETable(target).getPreviewCell(target, state == "v");
                    }
                    //位於第一行的頂部或者第一列的左邊時不可拖動
                    toggleDraggableState(me, target ? !!state : false, target ? state : '', pos, target);

                }
            } else {
                toggleDragButton(false, table, me);
            }

        } catch (e) {
            showError(e);
        }
    }

    var dragButtonTimer;

    function toggleDragButton(show, table, editor) {
        if (!show) {
            if (dragOver)return;
            dragButtonTimer = setTimeout(function () {
                !dragOver && dragButton && dragButton.parentNode && dragButton.parentNode.removeChild(dragButton);
            }, 2000);
        } else {
            createDragButton(table, editor);
        }
    }

    function createDragButton(table, editor) {
        var pos = domUtils.getXY(table),
            doc = table.ownerDocument;
        if (dragButton && dragButton.parentNode)return dragButton;
        dragButton = doc.createElement("div");
        dragButton.contentEditable = false;
        dragButton.innerHTML = "";
        dragButton.style.cssText = "width:15px;height:15px;background-image:url(" + editor.options.UEDITOR_HOME_URL + "dialogs/table/dragicon.png);position: absolute;cursor:move;top:" + (pos.y - 15) + "px;left:" + (pos.x) + "px;";
        domUtils.unSelectable(dragButton);
        dragButton.onmouseover = function (evt) {
            dragOver = true;
        };
        dragButton.onmouseout = function (evt) {
            dragOver = false;
        };
        domUtils.on(dragButton, 'click', function (type, evt) {
            doClick(evt, this);
        });
        domUtils.on(dragButton, 'dblclick', function (type, evt) {
            doDblClick(evt);
        });
        domUtils.on(dragButton, 'dragstart', function (type, evt) {
            domUtils.preventDefault(evt);
        });
        var timer;

        function doClick(evt, button) {
            // 部分瀏覽器下需要清理
            clearTimeout(timer);
            timer = setTimeout(function () {
                editor.fireEvent("tableClicked", table, button);
            }, 300);
        }

        function doDblClick(evt) {
            clearTimeout(timer);
            var ut = getUETable(table),
                start = table.rows[0].cells[0],
                end = ut.getLastCell(),
                range = ut.getCellsRange(start, end);
            editor.selection.getRange().setStart(start, 0).setCursor(false, true);
            ut.setSelected(range);
        }

        doc.body.appendChild(dragButton);
    }


//    function inPosition(table, pos) {
//        var tablePos = domUtils.getXY(table),
//            width = table.offsetWidth,
//            height = table.offsetHeight;
//        if (pos.x - tablePos.x < 5 && pos.y - tablePos.y < 5) {
//            return "topLeft";
//        } else if (tablePos.x + width - pos.x < 5 && tablePos.y + height - pos.y < 5) {
//            return "bottomRight";
//        }
//    }

    function inTableSide(table, cell, evt, top) {
        var pos = mouseCoords(evt),
            state = getRelation(cell, pos);

        if (top) {
            var caption = table.getElementsByTagName("caption")[0],
                capHeight = caption ? caption.offsetHeight : 0;
            return (state == "v1") && ((pos.y - domUtils.getXY(table).y - capHeight) < 8);
        } else {
            return (state == "h1") && ((pos.x - domUtils.getXY(table).x) < 8);
        }
    }

    /**
     * 獲取拖動時允許的X軸座標
     * @param dragTd
     * @param evt
     */
    function getPermissionX(dragTd, evt) {
        var ut = getUETable(dragTd);
        if (ut) {
            var preTd = ut.getSameEndPosCells(dragTd, "x")[0],
                nextTd = ut.getSameStartPosXCells(dragTd)[0],
                mouseX = mouseCoords(evt).x,
                left = (preTd ? domUtils.getXY(preTd).x : domUtils.getXY(ut.table).x) + 20 ,
                right = nextTd ? domUtils.getXY(nextTd).x + nextTd.offsetWidth - 20 : (me.body.offsetWidth + 5 || parseInt(domUtils.getComputedStyle(me.body, "width"), 10));

            left += cellMinWidth;
            right -= cellMinWidth;

            return mouseX < left ? left : mouseX > right ? right : mouseX;
        }
    }

    /**
     * 獲取拖動時允許的Y軸座標
     */
    function getPermissionY(dragTd, evt) {
        try {
            var top = domUtils.getXY(dragTd).y,
                mousePosY = mouseCoords(evt).y;
            return mousePosY < top ? top : mousePosY;
        } catch (e) {
            showError(e);
        }
    }

    /**
     * 移動狀態切換
     */
    function toggleDraggableState(editor, draggable, dir, mousePos, cell) {
        try {
            editor.body.style.cursor = dir == "h" ? "col-resize" : dir == "v" ? "row-resize" : "text";
            if (browser.ie) {
                if (dir && !mousedown && !getUETableBySelected(editor)) {
                    getDragLine(editor, editor.document);
                    showDragLineAt(dir, cell);
                } else {
                    hideDragLine(editor)
                }
            }
            onBorder = draggable;
        } catch (e) {
            showError(e);
        }
    }

    /**
     * 獲取與UETable相關的resize line
     * @param uetable UETable物件
     */
    function getResizeLineByUETable() {

        var lineId = '_UETableResizeLine',
            line = this.document.getElementById( lineId );

        if( !line ) {
            line = this.document.createElement("div");
            line.id = lineId;
            line.contnetEditable = false;
            line.setAttribute("unselectable", "on");

            var styles = {
                width: 2*cellBorderWidth + 1 + 'px',
                position: 'absolute',
                'z-index': 100000,
                cursor: 'col-resize',
                background: 'red',
                display: 'none'
            };

            //切換狀態
            line.onmouseout = function(){
                this.style.display = 'none';
            };

            utils.extend( line.style, styles );

            this.document.body.appendChild( line );

        }

        return line;

    }

    /**
     * 更新resize-line
     */
    function updateResizeLine( cell, uetable ) {

        var line = getResizeLineByUETable.call( this ),
            table = uetable.table,
            styles = {
                top: domUtils.getXY( table ).y + 'px',
                left: domUtils.getXY( cell).x + cell.offsetWidth - cellBorderWidth + 'px',
                display: 'block',
                height: table.offsetHeight + 'px'
            };

        utils.extend( line.style, styles );

    }

    /**
     * 顯示resize-line
     */
    function showResizeLine( cell ) {

        var uetable = getUETable( cell );

        updateResizeLine.call( this, cell, uetable );

    }

    /**
     * 獲取滑鼠與當前單元格的相對位置
     * @param ele
     * @param mousePos
     */
    function getRelation(ele, mousePos) {
        var elePos = domUtils.getXY(ele);

        if( !elePos ) {
            return '';
        }

        if (elePos.x + ele.offsetWidth - mousePos.x < cellBorderWidth) {
            return "h";
        }
        if (mousePos.x - elePos.x < cellBorderWidth) {
            return 'h1'
        }
        if (elePos.y + ele.offsetHeight - mousePos.y < cellBorderWidth) {
            return "v";
        }
        if (mousePos.y - elePos.y < cellBorderWidth) {
            return 'v1'
        }
        return '';
    }

    function mouseDownEvent(type, evt) {

        if( isEditorDisabled() ) {
            return ;
        }

        userActionStatus = {
            x: evt.clientX,
            y: evt.clientY
        };

        //右鍵選單單獨處理
        if (evt.button == 2) {
            var ut = getUETableBySelected(me),
                flag = false;

            if (ut) {
                var td = getTargetTd(me, evt);
                utils.each(ut.selectedTds, function (ti) {
                    if (ti === td) {
                        flag = true;
                    }
                });
                if (!flag) {
                    removeSelectedClass(domUtils.getElementsByTagName(me.body, "th td"));
                    ut.clearSelected()
                } else {
                    td = ut.selectedTds[0];
                    setTimeout(function () {
                        me.selection.getRange().setStart(td, 0).setCursor(false, true);
                    }, 0);

                }
            }
        } else {
            tableClickHander( evt );
        }

    }

    //清除表格的計時器
    function clearTableTimer() {
        tabTimer && clearTimeout( tabTimer );
        tabTimer = null;
    }

    //雙擊收縮
    function tableDbclickHandler(evt) {
        singleClickState = 0;
        evt = evt || me.window.event;
        var target = getParentTdOrTh(evt.target || evt.srcElement);
        if (target) {
            var h;
            if (h = getRelation(target, mouseCoords(evt))) {

                hideDragLine( me );

                if (h == 'h1') {
                    h = 'h';
                    if (inTableSide(domUtils.findParentByTagName(target, "table"), target, evt)) {
                        me.execCommand('adaptbywindow');
                    } else {
                        target = getUETable(target).getPreviewCell(target);
                        if (target) {
                            var rng = me.selection.getRange();
                            rng.selectNodeContents(target).setCursor(true, true)
                        }
                    }
                }
                if (h == 'h') {
                    var ut = getUETable(target),
                        table = ut.table,
                        cells = getCellsByMoveBorder( target, table, true );

                    cells = extractArray( cells, 'left' );

                    ut.width = ut.offsetWidth;

                    var oldWidth = [],
                        newWidth = [];

                    utils.each( cells, function( cell ){

                        oldWidth.push( cell.offsetWidth );

                    } );

                    utils.each( cells, function( cell ){

                        cell.removeAttribute("width");

                    } );

                    window.setTimeout( function(){

                        //是否允許改變
                        var changeable = true;

                        utils.each( cells, function( cell, index ){

                            var width = cell.offsetWidth;

                            if( width > oldWidth[index] ) {
                                changeable = false;
                                return false;
                            }

                            newWidth.push( width );

                        } );

                        var change = changeable ? newWidth : oldWidth;

                        utils.each( cells, function( cell, index ){

                            cell.width = change[index] - getTabcellSpace();

                        } );


                    }, 0 );
                }
            }
        }
    }

    function tableClickHander( evt ) {

        removeSelectedClass(domUtils.getElementsByTagName(me.body, "td th"));
        //trace:3113
        //選中單元格，點選table外部，不會清掉table上掛的ueTable,會引起getUETableBySelected方法返回值
        utils.each(me.document.getElementsByTagName('table'), function (t) {
            t.ueTable = null;
        });
        startTd = getTargetTd(me, evt);
        if( !startTd ) return;
        var table = domUtils.findParentByTagName(startTd, "table", true);
        ut = getUETable(table);
        ut && ut.clearSelected();

        //判斷當前滑鼠狀態
        if (!onBorder) {
            me.document.body.style.webkitUserSelect = '';
            mousedown = true;
            me.addListener('mouseover', mouseOverEvent);
        } else {
            //邊框上的動作處理
            borderActionHandler( evt );
        }


    }

    //處理表格邊框上的動作, 這裡做延時處理，避免兩種動作互相影響
    function borderActionHandler( evt ) {

        if ( browser.ie ) {
            evt = reconstruct(evt );
        }

        clearTableDragTimer();

        //是否正在等待resize的緩衝中
        isInResizeBuffer = true;

        tableDragTimer = setTimeout(function(){
            tableBorderDrag( evt );
        }, dblclickTime);

    }

    function extractArray( originArr, key ) {

        var result = [],
            tmp = null;

        for( var i = 0, len = originArr.length; i<len; i++ ) {

            tmp = originArr[ i ][ key ];

            if( tmp ) {
                result.push( tmp );
            }

        }

        return result;

    }

    function clearTableDragTimer() {
        tableDragTimer && clearTimeout(tableDragTimer);
        tableDragTimer = null;
    }

    function reconstruct( obj ) {

        var attrs = ['pageX', 'pageY', 'clientX', 'clientY', 'srcElement', 'target'],
            newObj = {};

        if( obj ) {

            for( var i = 0, key, val; key = attrs[i]; i++ ) {
                val=obj[ key ];
                val && (newObj[ key ] = val);
            }

        }

        return newObj;

    }

    //邊框拖動
    function tableBorderDrag( evt ) {

        isInResizeBuffer = false;

        startTd = evt.target || evt.srcElement;
        if( !startTd ) return;
        var state = getRelation(startTd, mouseCoords(evt));
        if (/\d/.test(state)) {
            state = state.replace(/\d/, '');
            startTd = getUETable(startTd).getPreviewCell(startTd, state == 'v');
        }
        hideDragLine(me);
        getDragLine(me, me.document);
        me.fireEvent('saveScene');
        showDragLineAt(state, startTd);
        mousedown = true;
        //拖動開始
        onDrag = state;
        dragTd = startTd;
    }

    function mouseUpEvent(type, evt) {

        if( isEditorDisabled() ) {
            return ;
        }

        clearTableDragTimer();

        isInResizeBuffer = false;

        if( onBorder ) {
            singleClickState = ++singleClickState % 3;

            userActionStatus = {
                x: evt.clientX,
                y: evt.clientY
            };

            tableResizeTimer = setTimeout(function(){
                singleClickState > 0 && singleClickState--;
            }, dblclickTime );

            if( singleClickState === 2 ) {

                singleClickState = 0;
                tableDbclickHandler(evt);
                return;

            }

        }

        if (evt.button == 2)return;
        var me = this;
        //清除表格上原生跨選問題
        var range = me.selection.getRange(),
            start = domUtils.findParentByTagName(range.startContainer, 'table', true),
            end = domUtils.findParentByTagName(range.endContainer, 'table', true);

        if (start || end) {
            if (start === end) {
                start = domUtils.findParentByTagName(range.startContainer, ['td', 'th', 'caption'], true);
                end = domUtils.findParentByTagName(range.endContainer, ['td', 'th', 'caption'], true);
                if (start !== end) {
                    me.selection.clearRange()
                }
            } else {
                me.selection.clearRange()
            }
        }
        mousedown = false;
        me.document.body.style.webkitUserSelect = '';
        //拖拽狀態下的mouseUP
        if ( onDrag && dragTd ) {

            me.selection.getNative()[browser.ie9below ? 'empty' : 'removeAllRanges']();

            singleClickState = 0;
            dragLine = me.document.getElementById('ue_tableDragLine');

            // trace 3973
            if (dragLine) {
                var dragTdPos = domUtils.getXY(dragTd),
                    dragLinePos = domUtils.getXY(dragLine);

                switch (onDrag) {
                    case "h":
                        changeColWidth(dragTd, dragLinePos.x - dragTdPos.x);
                        break;
                    case "v":
                        changeRowHeight(dragTd, dragLinePos.y - dragTdPos.y - dragTd.offsetHeight);
                        break;
                    default:
                }
                onDrag = "";
                dragTd = null;

                hideDragLine(me);
                me.fireEvent('saveScene');
                return;
            }
        }
        //正常狀態下的mouseup
        if (!startTd) {
            var target = domUtils.findParentByTagName(evt.target || evt.srcElement, "td", true);
            if (!target) target = domUtils.findParentByTagName(evt.target || evt.srcElement, "th", true);
            if (target && (target.tagName == "TD" || target.tagName == "TH")) {
                if (me.fireEvent("excludetable", target) === true) return;
                range = new dom.Range(me.document);
                range.setStart(target, 0).setCursor(false, true);
            }
        } else {
            var ut = getUETable(startTd),
                cell = ut ? ut.selectedTds[0] : null;
            if (cell) {
                range = new dom.Range(me.document);
                if (domUtils.isEmptyBlock(cell)) {
                    range.setStart(cell, 0).setCursor(false, true);
                } else {
                    range.selectNodeContents(cell).shrinkBoundary().setCursor(false, true);
                }
            } else {
                range = me.selection.getRange().shrinkBoundary();
                if (!range.collapsed) {
                    var start = domUtils.findParentByTagName(range.startContainer, ['td', 'th'], true),
                        end = domUtils.findParentByTagName(range.endContainer, ['td', 'th'], true);
                    //在table裡邊的不能清除
                    if (start && !end || !start && end || start && end && start !== end) {
                        range.setCursor(false, true);
                    }
                }
            }
            startTd = null;
            me.removeListener('mouseover', mouseOverEvent);
        }
        me._selectionChange(250, evt);
    }

    function mouseOverEvent(type, evt) {

        if( isEditorDisabled() ) {
            return;
        }

        var me = this,
            tar = evt.target || evt.srcElement;
        currentTd = domUtils.findParentByTagName(tar, "td", true) || domUtils.findParentByTagName(tar, "th", true);
        //需要判斷兩個TD是否位於同一個表格內
        if (startTd && currentTd &&
            ((startTd.tagName == "TD" && currentTd.tagName == "TD") || (startTd.tagName == "TH" && currentTd.tagName == "TH")) &&
            domUtils.findParentByTagName(startTd, 'table') == domUtils.findParentByTagName(currentTd, 'table')) {
            var ut = getUETable(currentTd);
            if (startTd != currentTd) {
                me.document.body.style.webkitUserSelect = 'none';
                me.selection.getNative()[browser.ie9below ? 'empty' : 'removeAllRanges']();
                var range = ut.getCellsRange(startTd, currentTd);
                ut.setSelected(range);
            } else {
                me.document.body.style.webkitUserSelect = '';
                ut.clearSelected();
            }

        }
        evt.preventDefault ? evt.preventDefault() : (evt.returnValue = false);
    }

    function setCellHeight(cell, height, backHeight) {
        var lineHight = parseInt(domUtils.getComputedStyle(cell, "line-height"), 10),
            tmpHeight = backHeight + height;
        height = tmpHeight < lineHight ? lineHight : tmpHeight;
        if (cell.style.height) cell.style.height = "";
        cell.rowSpan == 1 ? cell.setAttribute("height", height) : (cell.removeAttribute && cell.removeAttribute("height"));
    }

    function getWidth(cell) {
        if (!cell)return 0;
        return parseInt(domUtils.getComputedStyle(cell, "width"), 10);
    }

    function changeColWidth(cell, changeValue) {

        var ut = getUETable(cell);
        if (ut) {

            //根據當前移動的邊框獲取相關的單元格
            var table = ut.table,
                cells = getCellsByMoveBorder( cell, table );

            table.style.width = "";
            table.removeAttribute("width");

            //修正改變數
            changeValue = correctChangeValue( changeValue, cell, cells );

            if (cell.nextSibling) {

                var i=0;

                utils.each( cells, function( cellGroup ){

                    cellGroup.left.width = (+cellGroup.left.width)+changeValue;
                    cellGroup.right && ( cellGroup.right.width = (+cellGroup.right.width)-changeValue );

                } );

            } else {

                utils.each( cells, function( cellGroup ){
                    cellGroup.left.width -= -changeValue;
                } );

            }
        }

    }

    function isEditorDisabled() {
        return me.body.contentEditable === "false";
    }

    function changeRowHeight(td, changeValue) {
        if (Math.abs(changeValue) < 10) return;
        var ut = getUETable(td);
        if (ut) {
            var cells = ut.getSameEndPosCells(td, "y"),
            //備份需要連帶變化的td的原始高度，否則後期無法獲取正確的值
                backHeight = cells[0] ? cells[0].offsetHeight : 0;
            for (var i = 0, cell; cell = cells[i++];) {
                setCellHeight(cell, changeValue, backHeight);
            }
        }

    }

    /**
     * 獲取調整單元格大小的相關單元格
     * @isContainMergeCell 返回的結果中是否包含發生合併後的單元格
     */
    function getCellsByMoveBorder( cell, table, isContainMergeCell ) {

        if( !table ) {
            table = domUtils.findParentByTagName( cell, 'table' );
        }

        if( !table ) {
            return null;
        }

        //獲取到該單元格所在行的序列號
        var index = domUtils.getNodeIndex( cell ),
            temp = cell,
            rows = table.rows,
            colIndex = 0;

        while( temp ) {
            //獲取到當前單元格在未發生單元格合併時的序列
            if( temp.nodeType === 1 ) {
                colIndex += (temp.colSpan || 1);
            }
            temp = temp.previousSibling;
        }

        temp = null;

        //記錄想關的單元格
        var borderCells = [];

        utils.each(rows, function( tabRow ){

            var cells = tabRow.cells,
                currIndex = 0;

            utils.each( cells, function( tabCell ){

                currIndex += (tabCell.colSpan || 1);

                if( currIndex === colIndex ) {

                    borderCells.push({
                        left: tabCell,
                        right: tabCell.nextSibling || null
                    });

                    return false;

                } else if( currIndex > colIndex ) {

                    if( isContainMergeCell ) {
                        borderCells.push({
                            left: tabCell
                        });
                    }

                    return false;
                }


            } );

        });

        return borderCells;

    }


    /**
     * 通過給定的單元格集合獲取最小的單元格width
     */
    function getMinWidthByTableCells( cells ) {

        var minWidth = Number.MAX_VALUE;

        for( var i = 0, curCell; curCell = cells[ i ] ; i++ ) {

            minWidth = Math.min( minWidth, curCell.width || getTableCellWidth( curCell ) );

        }

        return minWidth;

    }

    function correctChangeValue( changeValue, relatedCell, cells ) {

        //為單元格的paading預留空間
        changeValue -= getTabcellSpace();

        if( changeValue < 0 ) {
            return 0;
        }

        changeValue -= getTableCellWidth( relatedCell );

        //確定方向
        var direction = changeValue < 0 ? 'left':'right';

        changeValue = Math.abs(changeValue);

        //只關心非最後一個單元格就可以
        utils.each( cells, function( cellGroup ){

            var curCell = cellGroup[direction];

            //為單元格保留最小空間
            if( curCell ) {
                changeValue = Math.min( changeValue, getTableCellWidth( curCell )-cellMinWidth );
            }


        } );


        //修正越界
        changeValue = changeValue < 0 ? 0 : changeValue;

        return direction === 'left' ? -changeValue : changeValue;

    }

    function getTableCellWidth( cell ) {

        var width = 0,
            //偏移糾正量
            offset = 0,
            width = cell.offsetWidth - getTabcellSpace();

        //最後一個節點糾正一下
        if( !cell.nextSibling ) {

            width -= getTableCellOffset( cell );

        }

        width = width < 0 ? 0 : width;

        try {
            cell.width = width;
        } catch(e) {
        }

        return width;

    }

    /**
     * 獲取單元格所在表格的最末單元格的偏移量
     */
    function getTableCellOffset( cell ) {

        tab = domUtils.findParentByTagName( cell, "table", false);

        if( tab.offsetVal === undefined ) {

            var prev = cell.previousSibling;

            if( prev ) {

                //最後一個單元格和前一個單元格的width diff結果 如果恰好為一個border width， 則條件成立
                tab.offsetVal = cell.offsetWidth - prev.offsetWidth === UT.borderWidth ? UT.borderWidth : 0;

            } else {
                tab.offsetVal = 0;
            }

        }

        return tab.offsetVal;

    }

    function getTabcellSpace() {

        if( UT.tabcellSpace === undefined ) {

            var cell = null,
                tab = me.document.createElement("table"),
                tbody = me.document.createElement("tbody"),
                trow = me.document.createElement("tr"),
                tabcell = me.document.createElement("td"),
                mirror = null;

            tabcell.style.cssText = 'border: 0;';
            tabcell.width = 1;

            trow.appendChild( tabcell );
            trow.appendChild( mirror = tabcell.cloneNode( false ) );

            tbody.appendChild( trow );

            tab.appendChild( tbody );

            tab.style.cssText = "visibility: hidden;";

            me.body.appendChild( tab );

            UT.paddingSpace = tabcell.offsetWidth - 1;

            var tmpTabWidth = tab.offsetWidth;

            tabcell.style.cssText = '';
            mirror.style.cssText = '';

            UT.borderWidth = ( tab.offsetWidth - tmpTabWidth ) / 3;

            UT.tabcellSpace = UT.paddingSpace + UT.borderWidth;

            me.body.removeChild( tab );

        }

        getTabcellSpace = function(){ return UT.tabcellSpace; };

        return UT.tabcellSpace;

    }

    function getDragLine(editor, doc) {
        if (mousedown)return;
        dragLine = editor.document.createElement("div");
        domUtils.setAttributes(dragLine, {
            id:"ue_tableDragLine",
            unselectable:'on',
            contenteditable:false,
            'onresizestart':'return false',
            'ondragstart':'return false',
            'onselectstart':'return false',
            style:"background-color:blue;position:absolute;padding:0;margin:0;background-image:none;border:0px none;opacity:0;filter:alpha(opacity=0)"
        });
        editor.body.appendChild(dragLine);
    }

    function hideDragLine(editor) {
        if (mousedown)return;
        var line;
        while (line = editor.document.getElementById('ue_tableDragLine')) {
            domUtils.remove(line)
        }
    }

    /**
     * 依據state（v|h）在cell位置顯示橫線
     * @param state
     * @param cell
     */
    function showDragLineAt(state, cell) {
        if (!cell) return;
        var table = domUtils.findParentByTagName(cell, "table"),
            caption = table.getElementsByTagName('caption'),
            width = table.offsetWidth,
            height = table.offsetHeight - (caption.length > 0 ? caption[0].offsetHeight : 0),
            tablePos = domUtils.getXY(table),
            cellPos = domUtils.getXY(cell), css;
        switch (state) {
            case "h":
                css = 'height:' + height + 'px;top:' + (tablePos.y + (caption.length > 0 ? caption[0].offsetHeight : 0)) + 'px;left:' + (cellPos.x + cell.offsetWidth);
                dragLine.style.cssText = css + 'px;position: absolute;display:block;background-color:blue;width:1px;border:0; color:blue;opacity:.3;filter:alpha(opacity=30)';
                break;
            case "v":
                css = 'width:' + width + 'px;left:' + tablePos.x + 'px;top:' + (cellPos.y + cell.offsetHeight );
                //必須加上border:0和color:blue，否則低版ie不支援背景色顯示
                dragLine.style.cssText = css + 'px;overflow:hidden;position: absolute;display:block;background-color:blue;height:1px;border:0;color:blue;opacity:.2;filter:alpha(opacity=20)';
                break;
            default:
        }
    }

    /**
     * 當表格邊框顏色為白色時設定為虛線,true為新增虛線
     * @param editor
     * @param flag
     */
    function switchBorderColor(editor, flag) {
        var tableArr = domUtils.getElementsByTagName(editor.body, "table"), color;
        for (var i = 0, node; node = tableArr[i++];) {
            var td = domUtils.getElementsByTagName(node, "td");
            if (td[0]) {
                if (flag) {
                    color = (td[0].style.borderColor).replace(/\s/g, "");
                    if (/(#ffffff)|(rgb\(255,255,255\))/ig.test(color))
                        domUtils.addClass(node, "noBorderTable")
                } else {
                    domUtils.removeClasses(node, "noBorderTable")
                }
            }

        }
    }

    function getTableWidth(editor, needIEHack, defaultValue) {
        var body = editor.body;
        return body.offsetWidth - (needIEHack ? parseInt(domUtils.getComputedStyle(body, 'margin-left'), 10) * 2 : 0) - defaultValue.tableBorder * 2 - (editor.options.offsetWidth || 0);
    }

    /**
     * 獲取當前拖動的單元格
     */
    function getTargetTd(editor, evt) {

        var target = domUtils.findParentByTagName(evt.target || evt.srcElement, ["td", "th"], true),
            dir = null;

        if( !target ) {
            return null;
        }

        dir = getRelation( target, mouseCoords( evt ) );

        //如果有前一個節點， 需要做一個修正， 否則可能會得到一個錯誤的td

        if( !target ) {
            return null;
        }

        if( dir === 'h1' && target.previousSibling ) {

            var position = domUtils.getXY( target),
                cellWidth = target.offsetWidth;

            if( Math.abs( position.x + cellWidth - evt.clientX ) > cellWidth / 3 ) {
                target = target.previousSibling;
            }

        } else if( dir === 'v1' && target.parentNode.previousSibling ) {

            var position = domUtils.getXY( target),
                cellHeight = target.offsetHeight;

            if( Math.abs( position.y + cellHeight - evt.clientY ) > cellHeight / 3 ) {
                target = target.parentNode.previousSibling.firstChild;
            }

        }


        //排除了非td內部以及用於程式碼高亮部分的td
        return target && !(editor.fireEvent("excludetable", target) === true) ? target : null;
    }

};


/**
 * 右鍵選單
 * @function
 * @name baidu.editor.plugins.contextmenu
 * @author zhanyi
 */

UE.plugins['contextmenu'] = function () {
    var me = this;
    me.setOpt('enableContextMenu',true);
    if(me.getOpt('enableContextMenu') === false){
        return;
    }
    var lang = me.getLang( "contextMenu" ),
            menu,
            items = me.options.contextMenu || [
                {
	                label:lang['selectall'],
	                cmdName:'selectall'
	            },
                {
                    label:lang.unlink,
                    cmdName:'unlink'
                },
                {
                    group:lang.paragraph,
                    icon:'justifyjustify',
                    subMenu:[
                        {
                            label:lang.justifyleft,
                            cmdName:'justify',
                            value:'left'
                        },
                        {
                            label:lang.justifyright,
                            cmdName:'justify',
                            value:'right'
                        },
                        {
                            label:lang.justifycenter,
                            cmdName:'justify',
                            value:'center'
                        }
                    ]
                },
                {
                    group:lang.table,
                    icon:'table',
                    subMenu:[
                        {
                            label:lang.inserttable,
                            cmdName:'inserttable'
                        },
                        {
                            label:lang.deletetable,
                            cmdName:'deletetable'
                        },
                        '-',
                        {
                            label:lang.deleterow,
                            cmdName:'deleterow'
                        },
                        {
                            label:lang.deletecol,
                            cmdName:'deletecol'
                        },
                        {
                            label:lang.insertcol,
                            cmdName:'insertcol'
                        },
                        {
                            label:lang.insertcolnext,
                            cmdName:'insertcolnext'
                        },
                        {
                            label:lang.insertrow,
                            cmdName:'insertrow'
                        },
                        {
                            label:lang.insertrownext,
                            cmdName:'insertrownext'
                        },
                        '-',
                        {
                            label:lang.insertcaption,
                            cmdName:'insertcaption'
                        },
                        {
                            label:lang.deletecaption,
                            cmdName:'deletecaption'
                        },
                        {
                            label:lang.inserttitle,
                            cmdName:'inserttitle'
                        },
                        {
                            label:lang.deletetitle,
                            cmdName:'deletetitle'
                        },
                        {
                            label:lang.inserttitlecol,
                            cmdName:'inserttitlecol'
                        },
                        {
                            label:lang.deletetitlecol,
                            cmdName:'deletetitlecol'
                        },
                        '-',
                        {
                            label:lang.mergecells,
                            cmdName:'mergecells'
                        },
                        {
                            label:lang.mergeright,
                            cmdName:'mergeright'
                        },
                        {
                            label:lang.mergedown,
                            cmdName:'mergedown'
                        },
                        '-',
                        {
                            label:lang.splittorows,
                            cmdName:'splittorows'
                        },
                        {
                            label:lang.splittocols,
                            cmdName:'splittocols'
                        },
                        {
                            label:lang.splittocells,
                            cmdName:'splittocells'
                        },
                        '-',
                        {
                            label:lang.averageDiseRow,
                            cmdName:'averagedistributerow'
                        },
                        {
                            label:lang.averageDisCol,
                            cmdName:'averagedistributecol'
                        },
                        '-',
                        {
                            label:lang.edittd,
                            cmdName:'edittd',
                            exec:function () {
                                if ( UE.ui['edittd'] ) {
                                    new UE.ui['edittd']( this );
                                }
                                this.getDialog('edittd').open();
                            }
                        },
                        {
                            label:lang.edittable,
                            cmdName:'edittable',
                            exec:function () {
                                if ( UE.ui['edittable'] ) {
                                    new UE.ui['edittable']( this );
                                }
                                this.getDialog('edittable').open();
                            }
                        },
                        {
                            label:lang.setbordervisible,
                            cmdName:'setbordervisible'
                        }
                    ]
                },
                {
                    group:lang.borderbk,
                    icon:'borderBack',
                    subMenu:[
                        {
                            label:lang.setcolor,
                            cmdName:"interlacetable",
                            exec:function(){
                                this.execCommand("interlacetable");
                            }
                        },
                        {
                            label:lang.unsetcolor,
                            cmdName:"uninterlacetable",
                            exec:function(){
                                this.execCommand("uninterlacetable");
                            }
                        },
                        {
                            label:lang.setbackground,
                            cmdName:"settablebackground",
                            exec:function(){
                                this.execCommand("settablebackground",{repeat:true,colorList:["#bbb","#ccc"]});
                            }
                        },
                        {
                            label:lang.unsetbackground,
                            cmdName:"cleartablebackground",
                            exec:function(){
                                this.execCommand("cleartablebackground");
                            }
                        },
                        {
                            label:lang.redandblue,
                            cmdName:"settablebackground",
                            exec:function(){
                                this.execCommand("settablebackground",{repeat:true,colorList:["red","blue"]});
                            }
                        },
                        {
                            label:lang.threecolorgradient,
                            cmdName:"settablebackground",
                            exec:function(){
                                this.execCommand("settablebackground",{repeat:true,colorList:["#aaa","#bbb","#ccc"]});
                            }
                        }
                    ]
                },
                {
                    group:lang.aligntd,
                    icon:'aligntd',
                    subMenu:[
                        {
                            cmdName:'cellalignment',
                            value:{align:'left',vAlign:'top'}
                        },
                        {
                            cmdName:'cellalignment',
                            value:{align:'center',vAlign:'top'}
                        },
                        {
                            cmdName:'cellalignment',
                            value:{align:'right',vAlign:'top'}
                        },
                        {
                            cmdName:'cellalignment',
                            value:{align:'left',vAlign:'middle'}
                        },
                        {
                            cmdName:'cellalignment',
                            value:{align:'center',vAlign:'middle'}
                        },
                        {
                            cmdName:'cellalignment',
                            value:{align:'right',vAlign:'middle'}
                        },
                        {
                            cmdName:'cellalignment',
                            value:{align:'left',vAlign:'bottom'}
                        },
                        {
                            cmdName:'cellalignment',
                            value:{align:'center',vAlign:'bottom'}
                        },
                        {
                            cmdName:'cellalignment',
                            value:{align:'right',vAlign:'bottom'}
                        }
                    ]
                },
                {
                    group:lang.aligntable,
                    icon:'aligntable',
                    subMenu:[
                        {
                            cmdName:'tablealignment',
                            className: 'left',
                            label:lang.tableleft,
                            value:"left"
                        },
                        {
                            cmdName:'tablealignment',
                            className: 'center',
                            label:lang.tablecenter,
                            value:"center"
                        },
                        {
                            cmdName:'tablealignment',
                            className: 'right',
                            label:lang.tableright,
                            value:"right"
                        }
                    ]
                },
                '-',
                {
                    label:lang.insertparagraphbefore,
                    cmdName:'insertparagraph',
                    value:true
                },
                {
                    label:lang.insertparagraphafter,
                    cmdName:'insertparagraph'
                },
                {
                    label:lang['copy'],
                    cmdName:'copy'
                },
                {
                    label:lang['paste'],
                    cmdName:'paste'
                }
            ];
    if ( !items.length ) {
        return;
    }
    var uiUtils = UE.ui.uiUtils;

    me.addListener( 'contextmenu', function ( type, evt ) {

        var offset = uiUtils.getViewportOffsetByEvent( evt );
        me.fireEvent( 'beforeselectionchange' );
        if ( menu ) {
            menu.destroy();
        }
        for ( var i = 0, ti, contextItems = []; ti = items[i]; i++ ) {
            var last;
            (function ( item ) {
                if ( item == '-' ) {
                    if ( (last = contextItems[contextItems.length - 1 ] ) && last !== '-' ) {
                        contextItems.push( '-' );
                    }
                } else if ( item.hasOwnProperty( "group" ) ) {
                    for ( var j = 0, cj, subMenu = []; cj = item.subMenu[j]; j++ ) {
                        (function ( subItem ) {
                            if ( subItem == '-' ) {
                                if ( (last = subMenu[subMenu.length - 1 ] ) && last !== '-' ) {
                                    subMenu.push( '-' );
                                }else{
                                    subMenu.splice(subMenu.length-1);
                                }
                            } else {
                                if ( (me.commands[subItem.cmdName] || UE.commands[subItem.cmdName] || subItem.query) &&
                                        (subItem.query ? subItem.query() : me.queryCommandState( subItem.cmdName )) > -1 ) {
                                    subMenu.push( {
                                        'label':subItem.label || me.getLang( "contextMenu." + subItem.cmdName + (subItem.value || '') )||"",
                                        'className':'edui-for-' +subItem.cmdName + ( subItem.className ? ( ' edui-for-' + subItem.cmdName + '-' + subItem.className ) : '' ),
                                        onclick:subItem.exec ? function () {
                                                subItem.exec.call( me );
                                        } : function () {
                                            me.execCommand( subItem.cmdName, subItem.value );
                                        }
                                    } );
                                }
                            }
                        })( cj );
                    }
                    if ( subMenu.length ) {
                        function getLabel(){
                            switch (item.icon){
                                case "table":
                                    return me.getLang( "contextMenu.table" );
                                case "justifyjustify":
                                    return me.getLang( "contextMenu.paragraph" );
                                case "aligntd":
                                    return me.getLang("contextMenu.aligntd");
                                case "aligntable":
                                    return me.getLang("contextMenu.aligntable");
                                case "tablesort":
                                    return lang.tablesort;
                                case "borderBack":
                                    return lang.borderbk;
                                default :
                                    return '';
                            }
                        }
                        contextItems.push( {
                            //todo 修正成自動獲取方式
                            'label':getLabel(),
                            className:'edui-for-' + item.icon,
                            'subMenu':{
                                items:subMenu,
                                editor:me
                            }
                        } );
                    }

                } else {
                    //有可能commmand沒有載入右鍵不能出來，或者沒有command也想能展示出來新增query方法
                    if ( (me.commands[item.cmdName] || UE.commands[item.cmdName] || item.query) &&
                            (item.query ? item.query.call(me) : me.queryCommandState( item.cmdName )) > -1 ) {

                        contextItems.push( {
                            'label':item.label || me.getLang( "contextMenu." + item.cmdName ),
                            className:'edui-for-' + (item.icon ? item.icon : item.cmdName + (item.value || '')),
                            onclick:item.exec ? function () {
                                item.exec.call( me );
                            } : function () {
                                me.execCommand( item.cmdName, item.value );
                            }
                        } );
                    }

                }

            })( ti );
        }
        if ( contextItems[contextItems.length - 1] == '-' ) {
            contextItems.pop();
        }

        menu = new UE.ui.Menu( {
            items:contextItems,
            className:"edui-contextmenu",
            editor:me
        } );
        menu.render();
        menu.showAt( offset );

        me.fireEvent("aftershowcontextmenu",menu);

        domUtils.preventDefault( evt );
        if ( browser.ie ) {
            var ieRange;
            try {
                ieRange = me.selection.getNative().createRange();
            } catch ( e ) {
                return;
            }
            if ( ieRange.item ) {
                var range = new dom.Range( me.document );
                range.selectNode( ieRange.item( 0 ) ).select( true, true );
            }
        }
    });

    // 新增複製的flash按鈕
    me.addListener('aftershowcontextmenu', function(type, menu) {
        if (me.zeroclipboard) {
            var items = menu.items;
            for (var key in items) {
                if (items[key].className == 'edui-for-copy') {
                    me.zeroclipboard.clip(items[key].getDom());
                }
            }
        }
    });

};


// plugins/basestyle.js
/**
 * B、I、sub、super命令支援
 * @file
 * @since 1.2.6.1
 */

UE.plugins['basestyle'] = function(){

    /**
     * 字型加粗
     * @command bold
     * @param { String } cmd 命令字串
     * @remind 對已加粗的文字內容執行該命令， 將取消加粗
     * @method execCommand
     * @example
     * ```javascript
     * //editor是編輯器例項
     * //對當前選中的文字內容執行加粗操作
     * //第一次執行， 文字內容加粗
     * editor.execCommand( 'bold' );
     *
     * //第二次執行， 文字內容取消加粗
     * editor.execCommand( 'bold' );
     * ```
     */


    /**
     * 字型傾斜
     * @command italic
     * @method execCommand
     * @param { String } cmd 命令字串
     * @remind 對已傾斜的文字內容執行該命令， 將取消傾斜
     * @example
     * ```javascript
     * //editor是編輯器例項
     * //對當前選中的文字內容執行斜體操作
     * //第一次操作， 文字內容將變成斜體
     * editor.execCommand( 'italic' );
     *
     * //再次對同一文字內容執行， 則文字內容將恢復正常
     * editor.execCommand( 'italic' );
     * ```
     */

    /**
     * 下標文字，與“superscript”命令互斥
     * @command subscript
     * @method execCommand
     * @remind  把選中的文字內容切換成下標文字， 如果當前選中的文字已經是下標， 則該操作會把文字內容還原成正常文字
     * @param { String } cmd 命令字串
     * @example
     * ```javascript
     * //editor是編輯器例項
     * //對當前選中的文字內容執行下標操作
     * //第一次操作， 文字內容將變成下標文字
     * editor.execCommand( 'subscript' );
     *
     * //再次對同一文字內容執行， 則文字內容將恢復正常
     * editor.execCommand( 'subscript' );
     * ```
     */

    /**
     * 上標文字，與“subscript”命令互斥
     * @command superscript
     * @method execCommand
     * @remind 把選中的文字內容切換成上標文字， 如果當前選中的文字已經是上標， 則該操作會把文字內容還原成正常文字
     * @param { String } cmd 命令字串
     * @example
     * ```javascript
     * //editor是編輯器例項
     * //對當前選中的文字內容執行上標操作
     * //第一次操作， 文字內容將變成上標文字
     * editor.execCommand( 'superscript' );
     *
     * //再次對同一文字內容執行， 則文字內容將恢復正常
     * editor.execCommand( 'superscript' );
     * ```
     */
    var basestyles = {
            'bold':['strong','b'],
            'italic':['em','i'],
            'subscript':['sub'],
            'superscript':['sup']
        },
        getObj = function(editor,tagNames){
            return domUtils.filterNodeList(editor.selection.getStartElementPath(),tagNames);
        },
        me = this;
    //新增快捷鍵
    me.addshortcutkey({
        "Bold" : "ctrl+66",//^B
        "Italic" : "ctrl+73", //^I
        "Underline" : "ctrl+85"//^U
    });
    me.addInputRule(function(root){
        utils.each(root.getNodesByTagName('b i'),function(node){
            switch (node.tagName){
                case 'b':
                    node.tagName = 'strong';
                    break;
                case 'i':
                    node.tagName = 'em';
            }
        });
    });
    for ( var style in basestyles ) {
        (function( cmd, tagNames ) {
            me.commands[cmd] = {
                execCommand : function( cmdName ) {
                    var range = me.selection.getRange(),obj = getObj(this,tagNames);
                    if ( range.collapsed ) {
                        if ( obj ) {
                            var tmpText =  me.document.createTextNode('');
                            range.insertNode( tmpText ).removeInlineStyle( tagNames );
                            range.setStartBefore(tmpText);
                            domUtils.remove(tmpText);
                        } else {
                            var tmpNode = range.document.createElement( tagNames[0] );
                            if(cmdName == 'superscript' || cmdName == 'subscript'){
                                tmpText = me.document.createTextNode('');
                                range.insertNode(tmpText)
                                    .removeInlineStyle(['sub','sup'])
                                    .setStartBefore(tmpText)
                                    .collapse(true);
                            }
                            range.insertNode( tmpNode ).setStart( tmpNode, 0 );
                        }
                        range.collapse( true );
                    } else {
                        if(cmdName == 'superscript' || cmdName == 'subscript'){
                            if(!obj || obj.tagName.toLowerCase() != cmdName){
                                range.removeInlineStyle(['sub','sup']);
                            }
                        }
                        obj ? range.removeInlineStyle( tagNames ) : range.applyInlineStyle( tagNames[0] );
                    }
                    range.select();
                },
                queryCommandState : function() {
                   return getObj(this,tagNames) ? 1 : 0;
                }
            };
        })( style, basestyles[style] );
    }
};



// plugins/customstyle.js
/**
 * 自定義樣式
 * @file
 * @since 1.2.6.1
 */

/**
 * 根據config配置檔案裡“customstyle”選項的值對匹配的標籤執行樣式替換。
 * @command customstyle
 * @method execCommand
 * @param { String } cmd 命令字串
 * @example
 * ```javascript
 * editor.execCommand( 'customstyle' );
 * ```
 */
UE.plugins['customstyle'] = function() {
    var me = this;
    me.setOpt({ 'customstyle':[
        {tag:'h1',name:'tc', style:'font-size:32px;font-weight:bold;border-bottom:#ccc 2px solid;padding:0 4px 0 0;text-align:center;margin:0 0 20px 0;'},
        {tag:'h1',name:'tl', style:'font-size:32px;font-weight:bold;border-bottom:#ccc 2px solid;padding:0 4px 0 0;text-align:left;margin:0 0 10px 0;'},
        {tag:'span',name:'im', style:'font-size:16px;font-style:italic;font-weight:bold;line-height:18px;'},
        {tag:'span',name:'hi', style:'font-size:16px;font-style:italic;font-weight:bold;color:rgb(51, 153, 204);line-height:18px;'}
    ]});
    me.commands['customstyle'] = {
        execCommand : function(cmdName, obj) {
            var me = this,
                    tagName = obj.tag,
                    node = domUtils.findParent(me.selection.getStart(), function(node) {
                        return node.getAttribute('label');
                    }, true),
                    range,bk,tmpObj = {};
            for (var p in obj) {
               if(obj[p]!==undefined)
                    tmpObj[p] = obj[p];
            }
            delete tmpObj.tag;
            if (node && node.getAttribute('label') == obj.label) {
                range = this.selection.getRange();
                bk = range.createBookmark();
                if (range.collapsed) {
                    //trace:1732 刪掉自定義標籤，要有p來回填站位
                    if(dtd.$block[node.tagName]){
                        var fillNode = me.document.createElement('p');
                        domUtils.moveChild(node, fillNode);
                        node.parentNode.insertBefore(fillNode, node);
                        domUtils.remove(node);
                    }else{
                        domUtils.remove(node,true);
                    }

                } else {

                    var common = domUtils.getCommonAncestor(bk.start, bk.end),
                            nodes = domUtils.getElementsByTagName(common, tagName);
                    if(new RegExp(tagName,'i').test(common.tagName)){
                        nodes.push(common);
                    }
                    for (var i = 0,ni; ni = nodes[i++];) {
                        if (ni.getAttribute('label') == obj.label) {
                            var ps = domUtils.getPosition(ni, bk.start),pe = domUtils.getPosition(ni, bk.end);
                            if ((ps & domUtils.POSITION_FOLLOWING || ps & domUtils.POSITION_CONTAINS)
                                    &&
                                    (pe & domUtils.POSITION_PRECEDING || pe & domUtils.POSITION_CONTAINS)
                                    )
                                if (dtd.$block[tagName]) {
                                    var fillNode = me.document.createElement('p');
                                    domUtils.moveChild(ni, fillNode);
                                    ni.parentNode.insertBefore(fillNode, ni);
                                }
                            domUtils.remove(ni, true);
                        }
                    }
                    node = domUtils.findParent(common, function(node) {
                        return node.getAttribute('label') == obj.label;
                    }, true);
                    if (node) {

                        domUtils.remove(node, true);

                    }

                }
                range.moveToBookmark(bk).select();
            } else {
                if (dtd.$block[tagName]) {
                    this.execCommand('paragraph', tagName, tmpObj,'customstyle');
                    range = me.selection.getRange();
                    if (!range.collapsed) {
                        range.collapse();
                        node = domUtils.findParent(me.selection.getStart(), function(node) {
                            return node.getAttribute('label') == obj.label;
                        }, true);
                        var pNode = me.document.createElement('p');
                        domUtils.insertAfter(node, pNode);
                        domUtils.fillNode(me.document, pNode);
                        range.setStart(pNode, 0).setCursor();
                    }
                } else {

                    range = me.selection.getRange();
                    if (range.collapsed) {
                        node = me.document.createElement(tagName);
                        domUtils.setAttributes(node, tmpObj);
                        range.insertNode(node).setStart(node, 0).setCursor();

                        return;
                    }

                    bk = range.createBookmark();
                    range.applyInlineStyle(tagName, tmpObj).moveToBookmark(bk).select();
                }
            }

        },
        queryCommandValue : function() {
            var parent = domUtils.filterNodeList(
                this.selection.getStartElementPath(),
                function(node){return node.getAttribute('label')}
            );
            return  parent ? parent.getAttribute('label') : '';
        }
    };
    //當去掉customstyle是，如果是塊元素，用p代替
    me.addListener('keyup', function(type, evt) {
        var keyCode = evt.keyCode || evt.which;

        if (keyCode == 32 || keyCode == 13) {
            var range = me.selection.getRange();
            if (range.collapsed) {
                var node = domUtils.findParent(me.selection.getStart(), function(node) {
                    return node.getAttribute('label');
                }, true);
                if (node && dtd.$block[node.tagName] && domUtils.isEmptyNode(node)) {
                        var p = me.document.createElement('p');
                        domUtils.insertAfter(node, p);
                        domUtils.fillNode(me.document, p);
                        domUtils.remove(node);
                        range.setStart(p, 0).setCursor();


                }
            }
        }
    });
};

// plugins/catchremoteimage.js
///import core
///commands 遠端圖片抓取
///commandsName  catchRemoteImage,catchremoteimageenable
///commandsTitle  遠端圖片抓取
/**
 * 遠端圖片抓取,當開啟本外掛時所有不符合本地域名的圖片都將被抓取成為本地伺服器上的圖片
 */
UE.plugins['catchremoteimage'] = function () {
    var me = this,
        ajax = UE.ajax;

    /* 設定預設值 */
    if (me.options.catchRemoteImageEnable === false) return;
    me.setOpt({
        catchRemoteImageEnable: false
    });

    me.addListener("afterpaste", function () {
       me.fireEvent("catchRemoteImage");
    });

    me.addListener("catchRemoteImage", function () {

        var catcherLocalDomain = me.getOpt('catcherLocalDomain'),
            catcherActionUrl = me.getActionUrl(me.getOpt('catcherActionName')),
            catcherUrlPrefix = me.getOpt('catcherUrlPrefix'),
            catcherFieldName = me.getOpt('catcherFieldName');

        var remoteImages = [],
            imgs = domUtils.getElementsByTagName(me.document, "img"),
            test = function (src, urls) {
                if (src.indexOf(location.host) != -1 || /(^\.)|(^\/)/.test(src)) {
                    return true;
                }
                if (urls) {
                    for (var j = 0, url; url = urls[j++];) {
                        if (src.indexOf(url) !== -1) {
                            return true;
                        }
                    }
                }
                return false;
            };

        for (var i = 0, ci; ci = imgs[i++];) {
            if (ci.getAttribute("word_img")) {
                continue;
            }
            var src = ci.getAttribute("_src") || ci.src || "";
            if (/^(https?|ftp):/i.test(src) && !test(src, catcherLocalDomain)) {
               remoteImages.push(src);
            }
        }

        if (remoteImages.length) {
            catchremoteimage(remoteImages, {
                //成功抓取
                success: function (r) {
                    try {
                        var info = r.state !== undefined ? r:eval("(" + r.responseText + ")");
                    } catch (e) {
                        return;
                    }

                    /* 獲取源路徑和新路徑 */
                    var i, j, ci, cj, oldSrc, newSrc, list = info.list;

                    for (i = 0; ci = imgs[i++];) {
                        oldSrc = ci.getAttribute("_src") || ci.src || "";
                        for (j = 0; cj = list[j++];) {
                            if (oldSrc == cj.source && cj.state == "SUCCESS") {  //抓取失敗時不做替換處理
                                newSrc = catcherUrlPrefix + cj.url;
                                domUtils.setAttributes(ci, {
                                    "src": newSrc,
                                    "_src": newSrc
                                });
                                break;
                            }
                        }
                    }
                    me.fireEvent('catchremotesuccess')
                },
                //回撥失敗，本次請求超時
                error: function () {
                    me.fireEvent("catchremoteerror");
                }
            });
        }

        function catchremoteimage(imgs, callbacks) {
            var params = utils.serializeParam(me.queryCommandValue('serverparam')) || '',
                url = utils.formatUrl(catcherActionUrl + (catcherActionUrl.indexOf('?') == -1 ? '?':'&') + params),
                isJsonp = utils.isCrossDomainUrl(url),
                opt = {
                    'method': 'POST',
                    'dataType': isJsonp ? 'jsonp':'',
                    'timeout': 60000, //單位：毫秒，回撥請求超時設定。目標使用者如果網速不是很快的話此處建議設定一個較大的數值
                    'onsuccess': callbacks["success"],
                    'onerror': callbacks["error"]
                };
            opt[catcherFieldName] = imgs;
            ajax.request(url, opt);
        }

    });
};

// plugins/insertparagraph.js
/**
 * 插入段落
 * @file
 * @since 1.2.6.1
 */


/**
 * 插入段落
 * @command insertparagraph
 * @method execCommand
 * @param { String } cmd 命令字串
 * @example
 * ```javascript
 * //editor是編輯器例項
 * editor.execCommand( 'insertparagraph' );
 * ```
 */

UE.commands['insertparagraph'] = {
    execCommand : function( cmdName,front) {
        var me = this,
            range = me.selection.getRange(),
            start = range.startContainer,tmpNode;
        while(start ){
            if(domUtils.isBody(start)){
                break;
            }
            tmpNode = start;
            start = start.parentNode;
        }
        if(tmpNode){
            var p = me.document.createElement('p');
            if(front){
                tmpNode.parentNode.insertBefore(p,tmpNode)
            }else{
                tmpNode.parentNode.insertBefore(p,tmpNode.nextSibling)
            }
            domUtils.fillNode(me.document,p);
            range.setStart(p,0).setCursor(false,true);
        }
    }
};


UE.commands['insertpbefore'] = {
    execCommand : function( cmdName ) {
	    this.execCommand("insertparagraph",true);
    }
};

UE.commands['insertpafter'] = {
    execCommand : function( cmdName ) {
	    this.execCommand("insertparagraph");
    }
};



// plugins/autoupload.js
/**
 * @description
 * 1.拖放檔案到編輯區域，自動上傳並插入到選區
 * 2.插入貼上板的圖片，自動上傳並插入到選區
 * @author Jinqn
 * @date 2013-10-14
 */
UE.plugin.register('autoupload', function (){

    function sendAndInsertFile(file, editor) {
        var me  = editor;
        //模擬資料
        var fieldName, urlPrefix, maxSize, allowFiles, actionUrl,
            loadingHtml, errorHandler, successHandler,
            filetype = /image\/\w+/i.test(file.type) ? 'image':'file',
            loadingId = 'loading_' + (+new Date()).toString(36);

        fieldName = me.getOpt(filetype + 'FieldName');
        urlPrefix = me.getOpt(filetype + 'UrlPrefix');
        maxSize = me.getOpt(filetype + 'MaxSize');
        allowFiles = me.getOpt(filetype + 'AllowFiles');
        actionUrl = me.getActionUrl(me.getOpt(filetype + 'ActionName'));
        errorHandler = function(title) {
            var loader = me.document.getElementById(loadingId);
            loader && domUtils.remove(loader);
            me.fireEvent('showmessage', {
                'id': loadingId,
                'content': title,
                'type': 'error',
                'timeout': 4000
            });
        };

        if (filetype == 'image') {
            loadingHtml = '<img class="loadingclass" id="' + loadingId + '" src="' +
                me.options.themePath + me.options.theme +
                '/images/spacer.gif" title="' + (me.getLang('autoupload.loading') || '') + '" >';
            successHandler = function(data) {
                var link = urlPrefix + data.url,
                    loader = me.document.getElementById(loadingId);
                if (loader) {
                    loader.setAttribute('src', link);
                    loader.setAttribute('_src', link);
                    loader.setAttribute('title', data.title || '');
                    loader.setAttribute('alt', data.original || '');
                    loader.removeAttribute('id');
                    domUtils.removeClasses(loader, 'loadingclass');
                }
            };
        } else {
            loadingHtml = '<p>' +
                '<img class="loadingclass" id="' + loadingId + '" src="' +
                me.options.themePath + me.options.theme +
                '/images/spacer.gif" title="' + (me.getLang('autoupload.loading') || '') + '" >' +
                '</p>';
            successHandler = function(data) {
                var link = urlPrefix + data.url,
                    loader = me.document.getElementById(loadingId);

                var rng = me.selection.getRange(),
                    bk = rng.createBookmark();
                rng.selectNode(loader).select();
                me.execCommand('insertfile', {'url': link});
                rng.moveToBookmark(bk).select();
            };
        }

        /* 插入loading的佔位符 */
        me.execCommand('inserthtml', loadingHtml);

        /* 判斷後端配置是否沒有載入成功 */
        if (!me.getOpt(filetype + 'ActionName')) {
            errorHandler(me.getLang('autoupload.errorLoadConfig'));
            return;
        }
        /* 判斷檔案大小是否超出限制 */
        if(file.size > maxSize) {
            errorHandler(me.getLang('autoupload.exceedSizeError'));
            return;
        }
        /* 判斷檔案格式是否超出允許 */
        var fileext = file.name ? file.name.substr(file.name.lastIndexOf('.')):'';
        if ((fileext && filetype != 'image') || (allowFiles && (allowFiles.join('') + '.').indexOf(fileext.toLowerCase() + '.') == -1)) {
            errorHandler(me.getLang('autoupload.exceedTypeError'));
            return;
        }

        /* 建立Ajax並提交 */
        var xhr = new XMLHttpRequest(),
            fd = new FormData(),
            params = utils.serializeParam(me.queryCommandValue('serverparam')) || '',
            url = utils.formatUrl(actionUrl + (actionUrl.indexOf('?') == -1 ? '?':'&') + params);

        fd.append(fieldName, file, file.name || ('blob.' + file.type.substr('image/'.length)));
        fd.append('type', 'ajax');
        xhr.open("post", url, true);
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.addEventListener('load', function (e) {
            try{
                var json = (new Function("return " + utils.trim(e.target.response)))();
                if (json.state == 'SUCCESS' && json.url) {
                    successHandler(json);
                } else {
                    errorHandler(json.state);
                }
            }catch(er){
                errorHandler(me.getLang('autoupload.loadError'));
            }
        });
        xhr.send(fd);
    }

    function getPasteImage(e){
        return e.clipboardData && e.clipboardData.items && e.clipboardData.items.length == 1 && /^image\//.test(e.clipboardData.items[0].type) ? e.clipboardData.items:null;
    }
    function getDropImage(e){
        return  e.dataTransfer && e.dataTransfer.files ? e.dataTransfer.files:null;
    }

    return {
        outputRule: function(root){
            utils.each(root.getNodesByTagName('img'),function(n){
                if (/\b(loaderrorclass)|(bloaderrorclass)\b/.test(n.getAttr('class'))) {
                    n.parentNode.removeChild(n);
                }
            });
            utils.each(root.getNodesByTagName('p'),function(n){
                if (/\bloadpara\b/.test(n.getAttr('class'))) {
                    n.parentNode.removeChild(n);
                }
            });
        },
        bindEvents:{
            //插入貼上板的圖片，拖放插入圖片
            'ready':function(e){
                var me = this;
                if(window.FormData && window.FileReader) {
                    domUtils.on(me.body, 'paste drop', function(e){
                        var hasImg = false,
                            items;
                        //獲取貼上板檔案列表或者拖放檔案列表
                        items = e.type == 'paste' ? getPasteImage(e):getDropImage(e);
                        if(items){
                            var len = items.length,
                                file;
                            while (len--){
                                file = items[len];
                                if(file.getAsFile) file = file.getAsFile();
                                if(file && file.size > 0) {
                                    sendAndInsertFile(file, me);
                                    hasImg = true;
                                }
                            }
                            hasImg && e.preventDefault();
                        }

                    });
                    //取消拖放圖片時出現的文字游標位置提示
                    domUtils.on(me.body, 'dragover', function (e) {
                        if(e.dataTransfer.types[0] == 'Files') {
                            e.preventDefault();
                        }
                    });

                    //設定loading的樣式
                    utils.cssRule('loading',
                        '.loadingclass{display:inline-block;cursor:default;background: url(\''
                            + this.options.themePath
                            + this.options.theme +'/images/loading.gif\') no-repeat center center transparent;border:1px solid #cccccc;margin-left:1px;height: 22px;width: 22px;}\n' +
                            '.loaderrorclass{display:inline-block;cursor:default;background: url(\''
                            + this.options.themePath
                            + this.options.theme +'/images/loaderror.png\') no-repeat center center transparent;border:1px solid #cccccc;margin-right:1px;height: 22px;width: 22px;' +
                            '}',
                        this.document);
                }
            }
        }
    }
});

// plugins/simpleupload.js
/**
 * @description
 * 簡單上傳:點選按鈕,直接選擇檔案上傳
 * @author Jinqn
 * @date 2014-03-31
 */
UE.plugin.register('simpleupload', function (){
    var me = this,
        isLoaded = false,
        containerBtn;

    function initUploadBtn(){
        var w = containerBtn.offsetWidth || 20,
            h = containerBtn.offsetHeight || 20,
            btnIframe = document.createElement('iframe'),
            btnStyle = 'display:block;width:' + w + 'px;height:' + h + 'px;overflow:hidden;border:0;margin:0;padding:0;position:absolute;top:0;left:0;filter:alpha(opacity=0);-moz-opacity:0;-khtml-opacity: 0;opacity: 0;cursor:pointer;';

        domUtils.on(btnIframe, 'load', function(){

            var timestrap = (+new Date()).toString(36),
                wrapper,
                btnIframeDoc,
                btnIframeBody;

            btnIframeDoc = (btnIframe.contentDocument || btnIframe.contentWindow.document);
            btnIframeBody = btnIframeDoc.body;
            wrapper = btnIframeDoc.createElement('div');

            wrapper.innerHTML = '<form id="edui_form_' + timestrap + '" target="edui_iframe_' + timestrap + '" method="POST" enctype="multipart/form-data" action="' + me.getOpt('serverUrl') + '" ' +
            'style="' + btnStyle + '">' +
            '<input id="edui_input_' + timestrap + '" type="file" accept="image/*" name="' + me.options.imageFieldName + '" ' +
            'style="' + btnStyle + '">' +
            '</form>' +
            '<iframe id="edui_iframe_' + timestrap + '" name="edui_iframe_' + timestrap + '" style="display:none;width:0;height:0;border:0;margin:0;padding:0;position:absolute;"></iframe>';

            wrapper.className = 'edui-' + me.options.theme;
            wrapper.id = me.ui.id + '_iframeupload';
            btnIframeBody.style.cssText = btnStyle;
            btnIframeBody.style.width = w + 'px';
            btnIframeBody.style.height = h + 'px';
            btnIframeBody.appendChild(wrapper);

            if (btnIframeBody.parentNode) {
                btnIframeBody.parentNode.style.width = w + 'px';
                btnIframeBody.parentNode.style.height = w + 'px';
            }

            var form = btnIframeDoc.getElementById('edui_form_' + timestrap);
            var input = btnIframeDoc.getElementById('edui_input_' + timestrap);
            var iframe = btnIframeDoc.getElementById('edui_iframe_' + timestrap);

            domUtils.on(input, 'change', function(){
                if(!input.value) return;
                var loadingId = 'loading_' + (+new Date()).toString(36);
                var params = utils.serializeParam(me.queryCommandValue('serverparam')) || '';

                var imageActionUrl = me.getActionUrl(me.getOpt('imageActionName'));
                var allowFiles = me.getOpt('imageAllowFiles');

                me.focus();
                me.execCommand('inserthtml', '<img class="loadingclass" id="' + loadingId + '" src="' + me.options.themePath + me.options.theme +'/images/spacer.gif" title="' + (me.getLang('simpleupload.loading') || '') + '" >');

                function callback(){
                    try{
                        var link, json, loader,
                            body = (iframe.contentDocument || iframe.contentWindow.document).body,
                            result = body.innerText || body.textContent || '';
                        json = (new Function("return " + result))();
                        link = me.options.imageUrlPrefix + json.url;
                        if(json.state == 'SUCCESS' && json.url) {
                            loader = me.document.getElementById(loadingId);
                            loader.setAttribute('src', link);
                            loader.setAttribute('_src', link);
                            loader.setAttribute('title', json.title || '');
                            loader.setAttribute('alt', json.original || '');
                            loader.removeAttribute('id');
                            domUtils.removeClasses(loader, 'loadingclass');
                        } else {
                            showErrorLoader && showErrorLoader(json.state);
                        }
                    }catch(er){
                        showErrorLoader && showErrorLoader(me.getLang('simpleupload.loadError'));
                    }
                    form.reset();
                    domUtils.un(iframe, 'load', callback);
                }
                function showErrorLoader(title){
                    if(loadingId) {
                        var loader = me.document.getElementById(loadingId);
                        loader && domUtils.remove(loader);
                        me.fireEvent('showmessage', {
                            'id': loadingId,
                            'content': title,
                            'type': 'error',
                            'timeout': 4000
                        });
                    }
                }

                /* 判斷後端配置是否沒有載入成功 */
                if (!me.getOpt('imageActionName')) {
                    errorHandler(me.getLang('autoupload.errorLoadConfig'));
                    return;
                }
                // 判斷檔案格式是否錯誤
                var filename = input.value,
                    fileext = filename ? filename.substr(filename.lastIndexOf('.')):'';
                if (!fileext || (allowFiles && (allowFiles.join('') + '.').indexOf(fileext.toLowerCase() + '.') == -1)) {
                    showErrorLoader(me.getLang('simpleupload.exceedTypeError'));
                    return;
                }

                domUtils.on(iframe, 'load', callback);
                form.action = utils.formatUrl(imageActionUrl + (imageActionUrl.indexOf('?') == -1 ? '?':'&') + params);
                form.submit();
            });

            var stateTimer;
            me.addListener('selectionchange', function () {
                clearTimeout(stateTimer);
                stateTimer = setTimeout(function() {
                    var state = me.queryCommandState('simpleupload');
                    if (state == -1) {
                        input.disabled = 'disabled';
                    } else {
                        input.disabled = false;
                    }
                }, 400);
            });
            isLoaded = true;
        });

        btnIframe.style.cssText = btnStyle;
        containerBtn.appendChild(btnIframe);
    }

    return {
        bindEvents:{
            'ready': function() {
                //設定loading的樣式
                utils.cssRule('loading',
                    '.loadingclass{display:inline-block;cursor:default;background: url(\''
                    + this.options.themePath
                    + this.options.theme +'/images/loading.gif\') no-repeat center center transparent;border:1px solid #cccccc;margin-right:1px;height: 22px;width: 22px;}\n' +
                    '.loaderrorclass{display:inline-block;cursor:default;background: url(\''
                    + this.options.themePath
                    + this.options.theme +'/images/loaderror.png\') no-repeat center center transparent;border:1px solid #cccccc;margin-right:1px;height: 22px;width: 22px;' +
                    '}',
                    this.document);
            },
            /* 初始化簡單上傳按鈕 */
            'simpleuploadbtnready': function(type, container) {
                containerBtn = container;
                me.afterConfigReady(initUploadBtn);
            }
        },
        outputRule: function(root){
            utils.each(root.getNodesByTagName('img'),function(n){
                if (/\b(loaderrorclass)|(bloaderrorclass)\b/.test(n.getAttr('class'))) {
                    n.parentNode.removeChild(n);
                }
            });
        },
        commands: {
            'simpleupload': {
                queryCommandState: function () {
                    return isLoaded ? 0:-1;
                }
            }
        }
    }
});


// plugins/serverparam.js
/**
 * 伺服器提交的額外引數列表設定外掛
 * @file
 * @since 1.2.6.1
 */
UE.plugin.register('serverparam', function (){

    var me = this,
        serverParam = {};

    return {
        commands:{
            /**
             * 修改伺服器提交的額外引數列表,清除所有項
             * @command serverparam
             * @method execCommand
             * @param { String } cmd 命令字串
             * @example
             * ```javascript
             * editor.execCommand('serverparam');
             * editor.queryCommandValue('serverparam'); //返回空
             * ```
             */
            /**
             * 修改伺服器提交的額外引數列表,刪除指定項
             * @command serverparam
             * @method execCommand
             * @param { String } cmd 命令字串
             * @param { String } key 要清除的屬性
             * @example
             * ```javascript
             * editor.execCommand('serverparam', 'name'); //刪除屬性name
             * ```
             */
            /**
             * 修改伺服器提交的額外引數列表,使用鍵值新增項
             * @command serverparam
             * @method execCommand
             * @param { String } cmd 命令字串
             * @param { String } key 要新增的屬性
             * @param { String } value 要新增屬性的值
             * @example
             * ```javascript
             * editor.execCommand('serverparam', 'name', 'hello');
             * editor.queryCommandValue('serverparam'); //返回物件 {'name': 'hello'}
             * ```
             */
            /**
             * 修改伺服器提交的額外引數列表,傳入鍵值對物件新增多項
             * @command serverparam
             * @method execCommand
             * @param { String } cmd 命令字串
             * @param { Object } key 傳入的鍵值對物件
             * @example
             * ```javascript
             * editor.execCommand('serverparam', {'name': 'hello'});
             * editor.queryCommandValue('serverparam'); //返回物件 {'name': 'hello'}
             * ```
             */
            /**
             * 修改伺服器提交的額外引數列表,使用自定義函式新增多項
             * @command serverparam
             * @method execCommand
             * @param { String } cmd 命令字串
             * @param { Function } key 自定義獲取引數的函式
             * @example
             * ```javascript
             * editor.execCommand('serverparam', function(editor){
             *     return {'key': 'value'};
             * });
             * editor.queryCommandValue('serverparam'); //返回物件 {'key': 'value'}
             * ```
             */

            /**
             * 獲取伺服器提交的額外引數列表
             * @command serverparam
             * @method queryCommandValue
             * @param { String } cmd 命令字串
             * @example
             * ```javascript
             * editor.queryCommandValue( 'serverparam' ); //返回物件 {'key': 'value'}
             * ```
             */
            'serverparam':{
                execCommand:function (cmd, key, value) {
                    if (key === undefined || key === null) { //不傳引數,清空列表
                        serverParam = {};
                    } else if (utils.isString(key)) { //傳入鍵值
                        if(value === undefined || value === null) {
                            delete serverParam[key];
                        } else {
                            serverParam[key] = value;
                        }
                    } else if (utils.isObject(key)) { //傳入物件,覆蓋列表項
                        utils.extend(serverParam, key, true);
                    } else if (utils.isFunction(key)){ //傳入函式,新增列表項
                        utils.extend(serverParam, key(), true);
                    }
                },
                queryCommandValue: function(){
                    return serverParam || {};
                }
            }
        }
    }
});


// plugins/insertfile.js
/**
 * 插入附件
 */
UE.plugin.register('insertfile', function (){

    var me = this;

    return {
        commands:{
            'insertfile': {
                execCommand: function (command, filelist){
                    filelist = utils.isArray(filelist) ? filelist : [filelist];

                    var i, item, icon, title,
                        html = '';
                    for (i = 0; i < filelist.length; i++) {
                        item = filelist[i];
                        title = item.title || item.url.substr(item.url.lastIndexOf('/') + 1);
                        html += '<p>[download='+item.id+']' + title + '[/download]</p>';
                    }
                    me.execCommand('insertHtml', html);
                }
            }
        }
    }
});




// plugins/xssFilter.js
/**
 * @file xssFilter.js
 * @desc xss過濾器
 * @author robbenmu
 */

UE.plugins.xssFilter = function() {

	var config = UEDITOR_CONFIG;
	var whitList = config.whitList;

	function filter(node) {

		var tagName = node.tagName;
		var attrs = node.attrs;

		if (!whitList.hasOwnProperty(tagName)) {
			node.parentNode.removeChild(node);
			return false;
		}

		UE.utils.each(attrs, function (val, key) {

			if (whitList[tagName].indexOf(key) === -1) {
				node.setAttr(key);
			}
		});
	}

	// 新增inserthtml\paste等操作用的過濾規則
	if (whitList && config.xssFilterRules) {
		this.options.filterRules = function () {

			var result = {};

			UE.utils.each(whitList, function(val, key) {
				result[key] = function (node) {
					return filter(node);
				};
			});

			return result;
		}();
	}

	var tagList = [];

	UE.utils.each(whitList, function (val, key) {
		tagList.push(key);
	});

	// 新增input過濾規則
	//
	if (whitList && config.inputXssFilter) {
		this.addInputRule(function (root) {

			root.traversal(function(node) {
				if (node.type !== 'element') {
					return false;
				}
				filter(node);
			});
		});
	}
	// 新增output過濾規則
	//
	if (whitList && config.outputXssFilter) {
		this.addOutputRule(function (root) {

			root.traversal(function(node) {
				if (node.type !== 'element') {
					return false;
				}
				filter(node);
			});
		});
	}

};


// ui/ui.js
var baidu = baidu || {};
baidu.editor = baidu.editor || {};
UE.ui = baidu.editor.ui = {};

// ui/uiutils.js
(function (){
    var browser = baidu.editor.browser,
        domUtils = baidu.editor.dom.domUtils;

    var magic = '$EDITORUI';
    var root = window[magic] = {};
    var uidMagic = 'ID' + magic;
    var uidCount = 0;

    var uiUtils = baidu.editor.ui.uiUtils = {
        uid: function (obj){
            return (obj ? obj[uidMagic] || (obj[uidMagic] = ++ uidCount) : ++ uidCount);
        },
        hook: function ( fn, callback ) {
            var dg;
            if (fn && fn._callbacks) {
                dg = fn;
            } else {
                dg = function (){
                    var q;
                    if (fn) {
                        q = fn.apply(this, arguments);
                    }
                    var callbacks = dg._callbacks;
                    var k = callbacks.length;
                    while (k --) {
                        var r = callbacks[k].apply(this, arguments);
                        if (q === undefined) {
                            q = r;
                        }
                    }
                    return q;
                };
                dg._callbacks = [];
            }
            dg._callbacks.push(callback);
            return dg;
        },
        createElementByHtml: function (html){
            var el = document.createElement('div');
            el.innerHTML = html;
            el = el.firstChild;
            el.parentNode.removeChild(el);
            return el;
        },
        getViewportElement: function (){
            return (browser.ie && browser.quirks) ?
                document.body : document.documentElement;
        },
        getClientRect: function (element){
            var bcr;
            //trace  IE6下在控制編輯器顯隱時可能會報錯，catch一下
            try{
                bcr = element.getBoundingClientRect();
            }catch(e){
                bcr={left:0,top:0,height:0,width:0}
            }
            var rect = {
                left: Math.round(bcr.left),
                top: Math.round(bcr.top),
                height: Math.round(bcr.bottom - bcr.top),
                width: Math.round(bcr.right - bcr.left)
            };
            var doc;
            while ((doc = element.ownerDocument) !== document &&
                (element = domUtils.getWindow(doc).frameElement)) {
                bcr = element.getBoundingClientRect();
                rect.left += bcr.left;
                rect.top += bcr.top;
            }
            rect.bottom = rect.top + rect.height;
            rect.right = rect.left + rect.width;
            return rect;
        },
        getViewportRect: function (){
            var viewportEl = uiUtils.getViewportElement();
            var width = (window.innerWidth || viewportEl.clientWidth) | 0;
            var height = (window.innerHeight ||viewportEl.clientHeight) | 0;
            return {
                left: 0,
                top: 0,
                height: height,
                width: width,
                bottom: height,
                right: width
            };
        },
        setViewportOffset: function (element, offset){
            var rect;
            var fixedLayer = uiUtils.getFixedLayer();
            if (element.parentNode === fixedLayer) {
                element.style.left = offset.left + 'px';
                element.style.top = offset.top + 'px';
            } else {
                domUtils.setViewportOffset(element, offset);
            }
        },
        getEventOffset: function (evt){
            var el = evt.target || evt.srcElement;
            var rect = uiUtils.getClientRect(el);
            var offset = uiUtils.getViewportOffsetByEvent(evt);
            return {
                left: offset.left - rect.left,
                top: offset.top - rect.top
            };
        },
        getViewportOffsetByEvent: function (evt){
            var el = evt.target || evt.srcElement;
            var frameEl = domUtils.getWindow(el).frameElement;
            var offset = {
                left: evt.clientX,
                top: evt.clientY
            };
            if (frameEl && el.ownerDocument !== document) {
                var rect = uiUtils.getClientRect(frameEl);
                offset.left += rect.left;
                offset.top += rect.top;
            }
            return offset;
        },
        setGlobal: function (id, obj){
            root[id] = obj;
            return magic + '["' + id  + '"]';
        },
        unsetGlobal: function (id){
            delete root[id];
        },
        copyAttributes: function (tgt, src){
            var attributes = src.attributes;
            var k = attributes.length;
            while (k --) {
                var attrNode = attributes[k];
                if ( attrNode.nodeName != 'style' && attrNode.nodeName != 'class' && (!browser.ie || attrNode.specified) ) {
                    tgt.setAttribute(attrNode.nodeName, attrNode.nodeValue);
                }
            }
            if (src.className) {
                domUtils.addClass(tgt,src.className);
            }
            if (src.style.cssText) {
                tgt.style.cssText += ';' + src.style.cssText;
            }
        },
        removeStyle: function (el, styleName){
            if (el.style.removeProperty) {
                el.style.removeProperty(styleName);
            } else if (el.style.removeAttribute) {
                el.style.removeAttribute(styleName);
            } else throw '';
        },
        contains: function (elA, elB){
            return elA && elB && (elA === elB ? false : (
                elA.contains ? elA.contains(elB) :
                    elA.compareDocumentPosition(elB) & 16
                ));
        },
        startDrag: function (evt, callbacks,doc){
            var doc = doc || document;
            var startX = evt.clientX;
            var startY = evt.clientY;
            function handleMouseMove(evt){
                var x = evt.clientX - startX;
                var y = evt.clientY - startY;
                callbacks.ondragmove(x, y,evt);
                if (evt.stopPropagation) {
                    evt.stopPropagation();
                } else {
                    evt.cancelBubble = true;
                }
            }
            if (doc.addEventListener) {
                function handleMouseUp(evt){
                    doc.removeEventListener('mousemove', handleMouseMove, true);
                    doc.removeEventListener('mouseup', handleMouseUp, true);
                    window.removeEventListener('mouseup', handleMouseUp, true);
                    callbacks.ondragstop();
                }
                doc.addEventListener('mousemove', handleMouseMove, true);
                doc.addEventListener('mouseup', handleMouseUp, true);
                window.addEventListener('mouseup', handleMouseUp, true);

                evt.preventDefault();
            } else {
                var elm = evt.srcElement;
                elm.setCapture();
                function releaseCaptrue(){
                    elm.releaseCapture();
                    elm.detachEvent('onmousemove', handleMouseMove);
                    elm.detachEvent('onmouseup', releaseCaptrue);
                    elm.detachEvent('onlosecaptrue', releaseCaptrue);
                    callbacks.ondragstop();
                }
                elm.attachEvent('onmousemove', handleMouseMove);
                elm.attachEvent('onmouseup', releaseCaptrue);
                elm.attachEvent('onlosecaptrue', releaseCaptrue);
                evt.returnValue = false;
            }
            callbacks.ondragstart();
        },
        getFixedLayer: function (){
            var layer = document.getElementById('edui_fixedlayer');
            if (layer == null) {
                layer = document.createElement('div');
                layer.id = 'edui_fixedlayer';
                document.body.appendChild(layer);
                if (browser.ie && browser.version <= 8) {
                    layer.style.position = 'absolute';
                    bindFixedLayer();
                    setTimeout(updateFixedOffset);
                } else {
                    layer.style.position = 'fixed';
                }
                layer.style.left = '0';
                layer.style.top = '0';
                layer.style.width = '0';
                layer.style.height = '0';
            }
            return layer;
        },
        makeUnselectable: function (element){
            if (browser.opera || (browser.ie && browser.version < 9)) {
                element.unselectable = 'on';
                if (element.hasChildNodes()) {
                    for (var i=0; i<element.childNodes.length; i++) {
                        if (element.childNodes[i].nodeType == 1) {
                            uiUtils.makeUnselectable(element.childNodes[i]);
                        }
                    }
                }
            } else {
                if (element.style.MozUserSelect !== undefined) {
                    element.style.MozUserSelect = 'none';
                } else if (element.style.WebkitUserSelect !== undefined) {
                    element.style.WebkitUserSelect = 'none';
                } else if (element.style.KhtmlUserSelect !== undefined) {
                    element.style.KhtmlUserSelect = 'none';
                }
            }
        }
    };
    function updateFixedOffset(){
        var layer = document.getElementById('edui_fixedlayer');
        uiUtils.setViewportOffset(layer, {
            left: 0,
            top: 0
        });
//        layer.style.display = 'none';
//        layer.style.display = 'block';

        //#trace: 1354
//        setTimeout(updateFixedOffset);
    }
    function bindFixedLayer(adjOffset){
        domUtils.on(window, 'scroll', updateFixedOffset);
        domUtils.on(window, 'resize', baidu.editor.utils.defer(updateFixedOffset, 0, true));
    }
})();


// ui/uibase.js
(function () {
    var utils = baidu.editor.utils,
        uiUtils = baidu.editor.ui.uiUtils,
        EventBase = baidu.editor.EventBase,
        UIBase = baidu.editor.ui.UIBase = function () {
        };

    UIBase.prototype = {
        className:'',
        uiName:'',
        initOptions:function (options) {
            var me = this;
            for (var k in options) {
                me[k] = options[k];
            }
            this.id = this.id || 'edui' + uiUtils.uid();
        },
        initUIBase:function () {
            this._globalKey = utils.unhtml(uiUtils.setGlobal(this.id, this));
        },
        render:function (holder) {
            var html = this.renderHtml();
            var el = uiUtils.createElementByHtml(html);

            //by xuheng 給每個node新增class
            var list = domUtils.getElementsByTagName(el, "*");
            var theme = "edui-" + (this.theme || this.editor.options.theme);
            var layer = document.getElementById('edui_fixedlayer');
            for (var i = 0, node; node = list[i++];) {
                domUtils.addClass(node, theme);
            }
            domUtils.addClass(el, theme);
            if(layer){
                layer.className="";
                domUtils.addClass(layer,theme);
            }

            var seatEl = this.getDom();
            if (seatEl != null) {
                seatEl.parentNode.replaceChild(el, seatEl);
                uiUtils.copyAttributes(el, seatEl);
            } else {
                if (typeof holder == 'string') {
                    holder = document.getElementById(holder);
                }
                holder = holder || uiUtils.getFixedLayer();
                domUtils.addClass(holder, theme);
                holder.appendChild(el);
            }
            this.postRender();
        },
        getDom:function (name) {
            if (!name) {
                return document.getElementById(this.id);
            } else {
                return document.getElementById(this.id + '_' + name);
            }
        },
        postRender:function () {
            this.fireEvent('postrender');
        },
        getHtmlTpl:function () {
            return '';
        },
        formatHtml:function (tpl) {
            var prefix = 'edui-' + this.uiName;
            return (tpl
                .replace(/##/g, this.id)
                .replace(/%%-/g, this.uiName ? prefix + '-' : '')
                .replace(/%%/g, (this.uiName ? prefix : '') + ' ' + this.className)
                .replace(/\$\$/g, this._globalKey));
        },
        renderHtml:function () {
            return this.formatHtml(this.getHtmlTpl());
        },
        dispose:function () {
            var box = this.getDom();
            if (box) baidu.editor.dom.domUtils.remove(box);
            uiUtils.unsetGlobal(this.id);
        }
    };
    utils.inherits(UIBase, EventBase);
})();


// ui/separator.js
(function (){
    var utils = baidu.editor.utils,
        UIBase = baidu.editor.ui.UIBase,
        Separator = baidu.editor.ui.Separator = function (options){
            this.initOptions(options);
            this.initSeparator();
        };
    Separator.prototype = {
        uiName: 'separator',
        initSeparator: function (){
            this.initUIBase();
        },
        getHtmlTpl: function (){
            return '<div id="##" class="edui-box %%"></div>';
        }
    };
    utils.inherits(Separator, UIBase);

})();


// ui/mask.js
///import core
///import uicore
(function (){
    var utils = baidu.editor.utils,
        domUtils = baidu.editor.dom.domUtils,
        UIBase = baidu.editor.ui.UIBase,
        uiUtils = baidu.editor.ui.uiUtils;
    
    var Mask = baidu.editor.ui.Mask = function (options){
        this.initOptions(options);
        this.initUIBase();
    };
    Mask.prototype = {
        getHtmlTpl: function (){
            return '<div id="##" class="edui-mask %%" onclick="return $$._onClick(event, this);" onmousedown="return $$._onMouseDown(event, this);"></div>';
        },
        postRender: function (){
            var me = this;
            domUtils.on(window, 'resize', function (){
                setTimeout(function (){
                    if (!me.isHidden()) {
                        me._fill();
                    }
                });
            });
        },
        show: function (zIndex){
            this._fill();
            this.getDom().style.display = '';
            this.getDom().style.zIndex = zIndex;
        },
        hide: function (){
            this.getDom().style.display = 'none';
            this.getDom().style.zIndex = '';
        },
        isHidden: function (){
            return this.getDom().style.display == 'none';
        },
        _onMouseDown: function (){
            return false;
        },
        _onClick: function (e, target){
            this.fireEvent('click', e, target);
        },
        _fill: function (){
            var el = this.getDom();
            var vpRect = uiUtils.getViewportRect();
            el.style.width = vpRect.width + 'px';
            el.style.height = vpRect.height + 'px';
        }
    };
    utils.inherits(Mask, UIBase);
})();


// ui/popup.js
///import core
///import uicore
(function () {
    var utils = baidu.editor.utils,
        uiUtils = baidu.editor.ui.uiUtils,
        domUtils = baidu.editor.dom.domUtils,
        UIBase = baidu.editor.ui.UIBase,
        Popup = baidu.editor.ui.Popup = function (options){
            this.initOptions(options);
            this.initPopup();
        };

    var allPopups = [];
    function closeAllPopup( evt,el ){
        for ( var i = 0; i < allPopups.length; i++ ) {
            var pop = allPopups[i];
            if (!pop.isHidden()) {
                if (pop.queryAutoHide(el) !== false) {
                    if(evt&&/scroll/ig.test(evt.type)&&pop.className=="edui-wordpastepop")   return;
                    pop.hide();
                }
            }
        }

        if(allPopups.length)
            pop.editor.fireEvent("afterhidepop");
    }

    Popup.postHide = closeAllPopup;

    var ANCHOR_CLASSES = ['edui-anchor-topleft','edui-anchor-topright',
        'edui-anchor-bottomleft','edui-anchor-bottomright'];
    Popup.prototype = {
        SHADOW_RADIUS: 5,
        content: null,
        _hidden: false,
        autoRender: true,
        canSideLeft: true,
        canSideUp: true,
        initPopup: function (){
            this.initUIBase();
            allPopups.push( this );
        },
        getHtmlTpl: function (){
            return '<div id="##" class="edui-popup %%" onmousedown="return false;">' +
                ' <div id="##_body" class="edui-popup-body">' +
                ' <iframe style="position:absolute;z-index:-1;left:0;top:0;background-color: transparent;" frameborder="0" width="100%" height="100%" src="about:blank"></iframe>' +
                ' <div class="edui-shadow"></div>' +
                ' <div id="##_content" class="edui-popup-content">' +
                this.getContentHtmlTpl() +
                '  </div>' +
                ' </div>' +
                '</div>';
        },
        getContentHtmlTpl: function (){
            if(this.content){
                if (typeof this.content == 'string') {
                    return this.content;
                }
                return this.content.renderHtml();
            }else{
                return ''
            }

        },
        _UIBase_postRender: UIBase.prototype.postRender,
        postRender: function (){


            if (this.content instanceof UIBase) {
                this.content.postRender();
            }

            //捕獲滑鼠滾輪
            if( this.captureWheel && !this.captured ) {

                this.captured = true;

                var winHeight = ( document.documentElement.clientHeight || document.body.clientHeight )  - 80,
                    _height = this.getDom().offsetHeight,
                    _top = uiUtils.getClientRect( this.combox.getDom() ).top,
                    content = this.getDom('content'),
                    ifr = this.getDom('body').getElementsByTagName('iframe'),
                    me = this;

                ifr.length && ( ifr = ifr[0] );

                while( _top + _height > winHeight ) {
                    _height -= 30;
                }
                content.style.height = _height + 'px';
                //同步更改iframe高度
                ifr && ( ifr.style.height = _height + 'px' );

                //阻止在combox上的滑鼠滾輪事件, 防止使用者的正常操作被誤解
                if( window.XMLHttpRequest ) {

                    domUtils.on( content, ( 'onmousewheel' in document.body ) ? 'mousewheel' :'DOMMouseScroll' , function(e){

                        if(e.preventDefault) {
                            e.preventDefault();
                        } else {
                            e.returnValue = false;
                        }

                        if( e.wheelDelta ) {

                            content.scrollTop -= ( e.wheelDelta / 120 )*60;

                        } else {

                            content.scrollTop -= ( e.detail / -3 )*60;

                        }

                    });

                } else {

                    //ie6
                    domUtils.on( this.getDom(), 'mousewheel' , function(e){

                        e.returnValue = false;

                        me.getDom('content').scrollTop -= ( e.wheelDelta / 120 )*60;

                    });

                }

            }
            this.fireEvent('postRenderAfter');
            this.hide(true);
            this._UIBase_postRender();
        },
        _doAutoRender: function (){
            if (!this.getDom() && this.autoRender) {
                this.render();
            }
        },
        mesureSize: function (){
            var box = this.getDom('content');
            return uiUtils.getClientRect(box);
        },
        fitSize: function (){
            if( this.captureWheel && this.sized ) {
                return this.__size;
            }
            this.sized = true;
            var popBodyEl = this.getDom('body');
            popBodyEl.style.width = '';
            popBodyEl.style.height = '';
            var size = this.mesureSize();
            if( this.captureWheel ) {
                popBodyEl.style.width =  -(-20 -size.width) + 'px';
                var height = parseInt( this.getDom('content').style.height, 10 );
                !window.isNaN( height ) && ( size.height = height );
            } else {
                popBodyEl.style.width =  size.width + 'px';
            }
            popBodyEl.style.height = size.height + 'px';
            this.__size = size;
            this.captureWheel && (this.getDom('content').style.overflow = 'auto');
            return size;
        },
        showAnchor: function ( element, hoz ){
            this.showAnchorRect( uiUtils.getClientRect( element ), hoz );
        },
        showAnchorRect: function ( rect, hoz, adj ){
            this._doAutoRender();
            var vpRect = uiUtils.getViewportRect();
            this.getDom().style.visibility = 'hidden';
            this._show();
            var popSize = this.fitSize();

            var sideLeft, sideUp, left, top;
            if (hoz) {
                sideLeft = this.canSideLeft && (rect.right + popSize.width > vpRect.right && rect.left > popSize.width);
                sideUp = this.canSideUp && (rect.top + popSize.height > vpRect.bottom && rect.bottom > popSize.height);
                left = (sideLeft ? rect.left - popSize.width : rect.right);
                top = (sideUp ? rect.bottom - popSize.height : rect.top);
            } else {
                sideLeft = this.canSideLeft && (rect.right + popSize.width > vpRect.right && rect.left > popSize.width);
                sideUp = this.canSideUp && (rect.top + popSize.height > vpRect.bottom && rect.bottom > popSize.height);
                left = (sideLeft ? rect.right - popSize.width : rect.left);
                top = (sideUp ? rect.top - popSize.height : rect.bottom);
            }

            var popEl = this.getDom();
            uiUtils.setViewportOffset(popEl, {
                left: left,
                top: top
            });
            domUtils.removeClasses(popEl, ANCHOR_CLASSES);
            popEl.className += ' ' + ANCHOR_CLASSES[(sideUp ? 1 : 0) * 2 + (sideLeft ? 1 : 0)];
            if(this.editor){
                popEl.style.zIndex = this.editor.container.style.zIndex * 1 + 10;
                baidu.editor.ui.uiUtils.getFixedLayer().style.zIndex = popEl.style.zIndex - 1;
            }
            this.getDom().style.visibility = 'visible';

        },
        showAt: function (offset) {
            var left = offset.left;
            var top = offset.top;
            var rect = {
                left: left,
                top: top,
                right: left,
                bottom: top,
                height: 0,
                width: 0
            };
            this.showAnchorRect(rect, false, true);
        },
        _show: function (){
            if (this._hidden) {
                var box = this.getDom();
                box.style.display = '';
                this._hidden = false;
//                if (box.setActive) {
//                    box.setActive();
//                }
                this.fireEvent('show');
            }
        },
        isHidden: function (){
            return this._hidden;
        },
        show: function (){
            this._doAutoRender();
            this._show();
        },
        hide: function (notNofity){
            if (!this._hidden && this.getDom()) {
                this.getDom().style.display = 'none';
                this._hidden = true;
                if (!notNofity) {
                    this.fireEvent('hide');
                }
            }
        },
        queryAutoHide: function (el){
            return !el || !uiUtils.contains(this.getDom(), el);
        }
    };
    utils.inherits(Popup, UIBase);
    
    domUtils.on( document, 'mousedown', function ( evt ) {
        var el = evt.target || evt.srcElement;
        closeAllPopup( evt,el );
    } );
    domUtils.on( window, 'scroll', function (evt,el) {
        closeAllPopup( evt,el );
    } );

})();


// ui/colorpicker.js
///import core
///import uicore
(function (){
    var utils = baidu.editor.utils,
        UIBase = baidu.editor.ui.UIBase,
        ColorPicker = baidu.editor.ui.ColorPicker = function (options){
            this.initOptions(options);
            this.noColorText = this.noColorText || this.editor.getLang("clearColor");
            this.initUIBase();
        };

    ColorPicker.prototype = {
        getHtmlTpl: function (){
            return genColorPicker(this.noColorText,this.editor);
        },
        _onTableClick: function (evt){
            var tgt = evt.target || evt.srcElement;
            var color = tgt.getAttribute('data-color');
            if (color) {
                this.fireEvent('pickcolor', color);
            }
        },
        _onTableOver: function (evt){
            var tgt = evt.target || evt.srcElement;
            var color = tgt.getAttribute('data-color');
            if (color) {
                this.getDom('preview').style.backgroundColor = color;
            }
        },
        _onTableOut: function (){
            this.getDom('preview').style.backgroundColor = '';
        },
        _onPickNoColor: function (){
            this.fireEvent('picknocolor');
        }
    };
    utils.inherits(ColorPicker, UIBase);

    var COLORS = (
        'ffffff,000000,eeece1,1f497d,4f81bd,c0504d,9bbb59,8064a2,4bacc6,f79646,' +
            'f2f2f2,7f7f7f,ddd9c3,c6d9f0,dbe5f1,f2dcdb,ebf1dd,e5e0ec,dbeef3,fdeada,' +
            'd8d8d8,595959,c4bd97,8db3e2,b8cce4,e5b9b7,d7e3bc,ccc1d9,b7dde8,fbd5b5,' +
            'bfbfbf,3f3f3f,938953,548dd4,95b3d7,d99694,c3d69b,b2a2c7,92cddc,fac08f,' +
            'a5a5a5,262626,494429,17365d,366092,953734,76923c,5f497a,31859b,e36c09,' +
            '7f7f7f,0c0c0c,1d1b10,0f243e,244061,632423,4f6128,3f3151,205867,974806,' +
            'c00000,ff0000,ffc000,ffff00,92d050,00b050,00b0f0,0070c0,002060,7030a0,').split(',');

    function genColorPicker(noColorText,editor){
        var html = '<div id="##" class="edui-colorpicker %%">' +
            '<div class="edui-colorpicker-topbar edui-clearfix">' +
            '<div unselectable="on" id="##_preview" class="edui-colorpicker-preview"></div>' +
            '<div unselectable="on" class="edui-colorpicker-nocolor" onclick="$$._onPickNoColor(event, this);">'+ noColorText +'</div>' +
            '</div>' +
            '<table  class="edui-box" style="border-collapse: collapse;" onmouseover="$$._onTableOver(event, this);" onmouseout="$$._onTableOut(event, this);" onclick="return $$._onTableClick(event, this);" cellspacing="0" cellpadding="0">' +
            '<tr style="border-bottom: 1px solid #ddd;font-size: 13px;line-height: 25px;color:#39C;padding-top: 2px"><td colspan="10">'+editor.getLang("themeColor")+'</td> </tr>'+
            '<tr class="edui-colorpicker-tablefirstrow" >';
        for (var i=0; i<COLORS.length; i++) {
            if (i && i%10 === 0) {
                html += '</tr>'+(i==60?'<tr style="border-bottom: 1px solid #ddd;font-size: 13px;line-height: 25px;color:#39C;"><td colspan="10">'+editor.getLang("standardColor")+'</td></tr>':'')+'<tr'+(i==60?' class="edui-colorpicker-tablefirstrow"':'')+'>';
            }
            html += i<70 ? '<td style="padding: 0 2px;"><a hidefocus title="'+COLORS[i]+'" onclick="return false;" href="javascript:" unselectable="on" class="edui-box edui-colorpicker-colorcell"' +
                ' data-color="#'+ COLORS[i] +'"'+
                ' style="background-color:#'+ COLORS[i] +';border:solid #ccc;'+
                (i<10 || i>=60?'border-width:1px;':
                    i>=10&&i<20?'border-width:1px 1px 0 1px;':

                        'border-width:0 1px 0 1px;')+
                '"' +
                '></a></td>':'';
        }
        html += '</tr></table></div>';
        return html;
    }
})();


// ui/tablepicker.js
///import core
///import uicore
(function (){
    var utils = baidu.editor.utils,
        uiUtils = baidu.editor.ui.uiUtils,
        UIBase = baidu.editor.ui.UIBase;
    
    var TablePicker = baidu.editor.ui.TablePicker = function (options){
        this.initOptions(options);
        this.initTablePicker();
    };
    TablePicker.prototype = {
        defaultNumRows: 10,
        defaultNumCols: 10,
        maxNumRows: 20,
        maxNumCols: 20,
        numRows: 10,
        numCols: 10,
        lengthOfCellSide: 22,
        initTablePicker: function (){
            this.initUIBase();
        },
        getHtmlTpl: function (){
            var me = this;
            return '<div id="##" class="edui-tablepicker %%">' +
                 '<div class="edui-tablepicker-body">' +
                  '<div class="edui-infoarea">' +
                   '<span id="##_label" class="edui-label"></span>' +
                  '</div>' +
                  '<div class="edui-pickarea"' +
                   ' onmousemove="$$._onMouseMove(event, this);"' +
                   ' onmouseover="$$._onMouseOver(event, this);"' +
                   ' onmouseout="$$._onMouseOut(event, this);"' +
                   ' onclick="$$._onClick(event, this);"' +
                  '>' +
                    '<div id="##_overlay" class="edui-overlay"></div>' +
                  '</div>' +
                 '</div>' +
                '</div>';
        },
        _UIBase_render: UIBase.prototype.render,
        render: function (holder){
            this._UIBase_render(holder);
            this.getDom('label').innerHTML = '0'+this.editor.getLang("t_row")+' x 0'+this.editor.getLang("t_col");
        },
        _track: function (numCols, numRows){
            var style = this.getDom('overlay').style;
            var sideLen = this.lengthOfCellSide;
            style.width = numCols * sideLen + 'px';
            style.height = numRows * sideLen + 'px';
            var label = this.getDom('label');
            label.innerHTML = numCols +this.editor.getLang("t_col")+' x ' + numRows + this.editor.getLang("t_row");
            this.numCols = numCols;
            this.numRows = numRows;
        },
        _onMouseOver: function (evt, el){
            var rel = evt.relatedTarget || evt.fromElement;
            if (!uiUtils.contains(el, rel) && el !== rel) {
                this.getDom('label').innerHTML = '0'+this.editor.getLang("t_col")+' x 0'+this.editor.getLang("t_row");
                this.getDom('overlay').style.visibility = '';
            }
        },
        _onMouseOut: function (evt, el){
            var rel = evt.relatedTarget || evt.toElement;
            if (!uiUtils.contains(el, rel) && el !== rel) {
                this.getDom('label').innerHTML = '0'+this.editor.getLang("t_col")+' x 0'+this.editor.getLang("t_row");
                this.getDom('overlay').style.visibility = 'hidden';
            }
        },
        _onMouseMove: function (evt, el){
            var style = this.getDom('overlay').style;
            var offset = uiUtils.getEventOffset(evt);
            var sideLen = this.lengthOfCellSide;
            var numCols = Math.ceil(offset.left / sideLen);
            var numRows = Math.ceil(offset.top / sideLen);
            this._track(numCols, numRows);
        },
        _onClick: function (){
            this.fireEvent('picktable', this.numCols, this.numRows);
        }
    };
    utils.inherits(TablePicker, UIBase);
})();


// ui/stateful.js
(function (){
    var browser = baidu.editor.browser,
        domUtils = baidu.editor.dom.domUtils,
        uiUtils = baidu.editor.ui.uiUtils;
    
    var TPL_STATEFUL = 'onmousedown="$$.Stateful_onMouseDown(event, this);"' +
        ' onmouseup="$$.Stateful_onMouseUp(event, this);"' +
        ( browser.ie ? (
        ' onmouseenter="$$.Stateful_onMouseEnter(event, this);"' +
        ' onmouseleave="$$.Stateful_onMouseLeave(event, this);"' )
        : (
        ' onmouseover="$$.Stateful_onMouseOver(event, this);"' +
        ' onmouseout="$$.Stateful_onMouseOut(event, this);"' ));
    
    baidu.editor.ui.Stateful = {
        alwalysHoverable: false,
        target:null,//目標元素和this指向dom不一樣
        Stateful_init: function (){
            this._Stateful_dGetHtmlTpl = this.getHtmlTpl;
            this.getHtmlTpl = this.Stateful_getHtmlTpl;
        },
        Stateful_getHtmlTpl: function (){
            var tpl = this._Stateful_dGetHtmlTpl();
            // 使用function避免$轉義
            return tpl.replace(/stateful/g, function (){ return TPL_STATEFUL; });
        },
        Stateful_onMouseEnter: function (evt, el){
            this.target=el;
            if (!this.isDisabled() || this.alwalysHoverable) {
                this.addState('hover');
                this.fireEvent('over');
            }
        },
        Stateful_onMouseLeave: function (evt, el){
            if (!this.isDisabled() || this.alwalysHoverable) {
                this.removeState('hover');
                this.removeState('active');
                this.fireEvent('out');
            }
        },
        Stateful_onMouseOver: function (evt, el){
            var rel = evt.relatedTarget;
            if (!uiUtils.contains(el, rel) && el !== rel) {
                this.Stateful_onMouseEnter(evt, el);
            }
        },
        Stateful_onMouseOut: function (evt, el){
            var rel = evt.relatedTarget;
            if (!uiUtils.contains(el, rel) && el !== rel) {
                this.Stateful_onMouseLeave(evt, el);
            }
        },
        Stateful_onMouseDown: function (evt, el){
            if (!this.isDisabled()) {
                this.addState('active');
            }
        },
        Stateful_onMouseUp: function (evt, el){
            if (!this.isDisabled()) {
                this.removeState('active');
            }
        },
        Stateful_postRender: function (){
            if (this.disabled && !this.hasState('disabled')) {
                this.addState('disabled');
            }
        },
        hasState: function (state){
            return domUtils.hasClass(this.getStateDom(), 'edui-state-' + state);
        },
        addState: function (state){
            if (!this.hasState(state)) {
                this.getStateDom().className += ' edui-state-' + state;
            }
        },
        removeState: function (state){
            if (this.hasState(state)) {
                domUtils.removeClasses(this.getStateDom(), ['edui-state-' + state]);
            }
        },
        getStateDom: function (){
            return this.getDom('state');
        },
        isChecked: function (){
            return this.hasState('checked');
        },
        setChecked: function (checked){
            if (!this.isDisabled() && checked) {
                this.addState('checked');
            } else {
                this.removeState('checked');
            }
        },
        isDisabled: function (){
            return this.hasState('disabled');
        },
        setDisabled: function (disabled){
            if (disabled) {
                this.removeState('hover');
                this.removeState('checked');
                this.removeState('active');
                this.addState('disabled');
            } else {
                this.removeState('disabled');
            }
        }
    };
})();


// ui/button.js
///import core
///import uicore
///import ui/stateful.js
(function (){
    var utils = baidu.editor.utils,
        UIBase = baidu.editor.ui.UIBase,
        Stateful = baidu.editor.ui.Stateful,
        Button = baidu.editor.ui.Button = function (options){
            if(options.name){
                var btnName = options.name;
                var cssRules = options.cssRules;
                if(!options.className){
                    options.className =  'edui-for-' + btnName;
                }
                options.cssRules = '.edui-default  .edui-for-'+ btnName +' .edui-icon {'+ cssRules +'}'
            }
            this.initOptions(options);
            this.initButton();
        };
    Button.prototype = {
        uiName: 'button',
        label: '',
        title: '',
        showIcon: true,
        showText: true,
        cssRules:'',
        initButton: function (){
            this.initUIBase();
            this.Stateful_init();
            if(this.cssRules){
                utils.cssRule('edui-customize-'+this.name+'-style',this.cssRules);
            }
        },
        getHtmlTpl: function (){
            return '<div id="##" class="edui-box %%">' +
                '<div id="##_state" stateful>' +
                 '<div class="%%-wrap"><div id="##_body" unselectable="on" ' + (this.title ? 'title="' + this.title + '"' : '') +
                 ' class="%%-body" onmousedown="return $$._onMouseDown(event, this);" onclick="return $$._onClick(event, this);">' +
                  (this.showIcon ? '<div class="edui-box edui-icon"></div>' : '') +
                  (this.showText ? '<div class="edui-box edui-label">' + this.label + '</div>' : '') +
                 '</div>' +
                '</div>' +
                '</div></div>';
        },
        postRender: function (){
            this.Stateful_postRender();
            this.setDisabled(this.disabled)
        },
        _onMouseDown: function (e){
            var target = e.target || e.srcElement,
                tagName = target && target.tagName && target.tagName.toLowerCase();
            if (tagName == 'input' || tagName == 'object' || tagName == 'object') {
                return false;
            }
        },
        _onClick: function (){
            if (!this.isDisabled()) {
                this.fireEvent('click');
            }
        },
        setTitle: function(text){
            var label = this.getDom('label');
            label.innerHTML = text;
        }
    };
    utils.inherits(Button, UIBase);
    utils.extend(Button.prototype, Stateful);

})();


// ui/splitbutton.js
///import core
///import uicore
///import ui/stateful.js
(function (){
    var utils = baidu.editor.utils,
        uiUtils = baidu.editor.ui.uiUtils,
        domUtils = baidu.editor.dom.domUtils,
        UIBase = baidu.editor.ui.UIBase,
        Stateful = baidu.editor.ui.Stateful,
        SplitButton = baidu.editor.ui.SplitButton = function (options){
            this.initOptions(options);
            this.initSplitButton();
        };
    SplitButton.prototype = {
        popup: null,
        uiName: 'splitbutton',
        title: '',
        initSplitButton: function (){
            this.initUIBase();
            this.Stateful_init();
            var me = this;
            if (this.popup != null) {
                var popup = this.popup;
                this.popup = null;
                this.setPopup(popup);
            }
        },
        _UIBase_postRender: UIBase.prototype.postRender,
        postRender: function (){
            this.Stateful_postRender();
            this._UIBase_postRender();
        },
        setPopup: function (popup){
            if (this.popup === popup) return;
            if (this.popup != null) {
                this.popup.dispose();
            }
            popup.addListener('show', utils.bind(this._onPopupShow, this));
            popup.addListener('hide', utils.bind(this._onPopupHide, this));
            popup.addListener('postrender', utils.bind(function (){
                popup.getDom('body').appendChild(
                    uiUtils.createElementByHtml('<div id="' +
                        this.popup.id + '_bordereraser" class="edui-bordereraser edui-background" style="width:' +
                        (uiUtils.getClientRect(this.getDom()).width -2) + 'px"></div>')
                    );
                popup.getDom().className += ' ' + this.className;
            }, this));
            this.popup = popup;
        },
        _onPopupShow: function (){
            this.addState('opened');
        },
        _onPopupHide: function (){
            this.removeState('opened');
        },
        getHtmlTpl: function (){
            return '<div id="##" class="edui-box %%">' +
                '<div '+ (this.title ? 'title="' + this.title + '"' : '') +' id="##_state" stateful><div class="%%-body">' +
                '<div id="##_button_body" class="edui-box edui-button-body" onclick="$$._onButtonClick(event, this);">' +
                '<div class="edui-box edui-icon"></div>' +
                '</div>' +
                '<div class="edui-box edui-splitborder"></div>' +
                '<div class="edui-box edui-arrow" onclick="$$._onArrowClick();"></div>' +
                '</div></div></div>';
        },
        showPopup: function (){
            // 當popup往上彈出的時候，做特殊處理
            var rect = uiUtils.getClientRect(this.getDom());
            rect.top -= this.popup.SHADOW_RADIUS;
            rect.height += this.popup.SHADOW_RADIUS;
            this.popup.showAnchorRect(rect);
        },
        _onArrowClick: function (event, el){
            if (!this.isDisabled()) {
                this.showPopup();
            }
        },
        _onButtonClick: function (){
            if (!this.isDisabled()) {
                this.fireEvent('buttonclick');
            }
        }
    };
    utils.inherits(SplitButton, UIBase);
    utils.extend(SplitButton.prototype, Stateful, true);

})();


// ui/colorbutton.js
///import core
///import uicore
///import ui/colorpicker.js
///import ui/popup.js
///import ui/splitbutton.js
(function (){
    var utils = baidu.editor.utils,
        uiUtils = baidu.editor.ui.uiUtils,
        ColorPicker = baidu.editor.ui.ColorPicker,
        Popup = baidu.editor.ui.Popup,
        SplitButton = baidu.editor.ui.SplitButton,
        ColorButton = baidu.editor.ui.ColorButton = function (options){
            this.initOptions(options);
            this.initColorButton();
        };
    ColorButton.prototype = {
        initColorButton: function (){
            var me = this;
            this.popup = new Popup({
                content: new ColorPicker({
                    noColorText: me.editor.getLang("clearColor"),
                    editor:me.editor,
                    onpickcolor: function (t, color){
                        me._onPickColor(color);
                    },
                    onpicknocolor: function (t, color){
                        me._onPickNoColor(color);
                    }
                }),
                editor:me.editor
            });
            this.initSplitButton();
        },
        _SplitButton_postRender: SplitButton.prototype.postRender,
        postRender: function (){
            this._SplitButton_postRender();
            this.getDom('button_body').appendChild(
                uiUtils.createElementByHtml('<div id="' + this.id + '_colorlump" class="edui-colorlump"></div>')
            );
            this.getDom().className += ' edui-colorbutton';
        },
        setColor: function (color){
            this.getDom('colorlump').style.backgroundColor = color;
            this.color = color;
        },
        _onPickColor: function (color){
            if (this.fireEvent('pickcolor', color) !== false) {
                this.setColor(color);
                this.popup.hide();
            }
        },
        _onPickNoColor: function (color){
            if (this.fireEvent('picknocolor') !== false) {
                this.popup.hide();
            }
        }
    };
    utils.inherits(ColorButton, SplitButton);

})();


// ui/tablebutton.js
///import core
///import uicore
///import ui/popup.js
///import ui/tablepicker.js
///import ui/splitbutton.js
(function (){
    var utils = baidu.editor.utils,
        Popup = baidu.editor.ui.Popup,
        TablePicker = baidu.editor.ui.TablePicker,
        SplitButton = baidu.editor.ui.SplitButton,
        TableButton = baidu.editor.ui.TableButton = function (options){
            this.initOptions(options);
            this.initTableButton();
        };
    TableButton.prototype = {
        initTableButton: function (){
            var me = this;
            this.popup = new Popup({
                content: new TablePicker({
                    editor:me.editor,
                    onpicktable: function (t, numCols, numRows){
                        me._onPickTable(numCols, numRows);
                    }
                }),
                'editor':me.editor
            });
            this.initSplitButton();
        },
        _onPickTable: function (numCols, numRows){
            if (this.fireEvent('picktable', numCols, numRows) !== false) {
                this.popup.hide();
            }
        }
    };
    utils.inherits(TableButton, SplitButton);

})();


// ui/cellalignpicker.js
///import core
///import uicore
(function () {
    var utils = baidu.editor.utils,
        Popup = baidu.editor.ui.Popup,
        Stateful = baidu.editor.ui.Stateful,
        UIBase = baidu.editor.ui.UIBase;

    /**
     * 該引數將新增一個引數： selected， 引數型別為一個Object， 形如{ 'align': 'center', 'valign': 'top' }， 表示單元格的初始
     * 對齊狀態為： 豎直居上，水平居中; 其中 align的取值為：'center', 'left', 'right'; valign的取值為: 'top', 'middle', 'bottom'
     * @update 2013/4/2 hancong03@baidu.com
     */
    var CellAlignPicker = baidu.editor.ui.CellAlignPicker = function (options) {
        this.initOptions(options);
        this.initSelected();
        this.initCellAlignPicker();
    };
    CellAlignPicker.prototype = {
        //初始化選中狀態， 該方法將根據傳遞進來的引數獲取到應該選中的對齊方式圖示的索引
        initSelected: function(){

            var status = {

                valign: {
                    top: 0,
                    middle: 1,
                    bottom: 2
                },
                align: {
                    left: 0,
                    center: 1,
                    right: 2
                },
                count: 3

                },
                result = -1;

            if( this.selected ) {
                this.selectedIndex = status.valign[ this.selected.valign ] * status.count + status.align[ this.selected.align ];
            }

        },
        initCellAlignPicker:function () {
            this.initUIBase();
            this.Stateful_init();
        },
        getHtmlTpl:function () {

            var alignType = [ 'left', 'center', 'right' ],
                COUNT = 9,
                tempClassName = null,
                tempIndex = -1,
                tmpl = [];


            for( var i= 0; i<COUNT; i++ ) {

                tempClassName = this.selectedIndex === i ? ' class="edui-cellalign-selected" ' : '';
                tempIndex = i % 3;

                tempIndex === 0 && tmpl.push('<tr>');

                tmpl.push( '<td index="'+ i +'" ' + tempClassName + ' stateful><div class="edui-icon edui-'+ alignType[ tempIndex ] +'"></div></td>' );

                tempIndex === 2 && tmpl.push('</tr>');

            }

            return '<div id="##" class="edui-cellalignpicker %%">' +
                '<div class="edui-cellalignpicker-body">' +
                '<table onclick="$$._onClick(event);">' +
                tmpl.join('') +
                '</table>' +
                '</div>' +
                '</div>';
        },
        getStateDom: function (){
            return this.target;
        },
        _onClick: function (evt){
            var target= evt.target || evt.srcElement;
            if(/icon/.test(target.className)){
                this.items[target.parentNode.getAttribute("index")].onclick();
                Popup.postHide(evt);
            }
        },
        _UIBase_render:UIBase.prototype.render
    };
    utils.inherits(CellAlignPicker, UIBase);
    utils.extend(CellAlignPicker.prototype, Stateful,true);
})();





// ui/pastepicker.js
///import core
///import uicore
(function () {
    var utils = baidu.editor.utils,
        Stateful = baidu.editor.ui.Stateful,
        uiUtils = baidu.editor.ui.uiUtils,
        UIBase = baidu.editor.ui.UIBase;

    var PastePicker = baidu.editor.ui.PastePicker = function (options) {
        this.initOptions(options);
        this.initPastePicker();
    };
    PastePicker.prototype = {
        initPastePicker:function () {
            this.initUIBase();
            this.Stateful_init();
        },
        getHtmlTpl:function () {
            return '<div class="edui-pasteicon" onclick="$$._onClick(this)"></div>' +
                '<div class="edui-pastecontainer">' +
                '<div class="edui-title">' + this.editor.getLang("pasteOpt") + '</div>' +
                '<div class="edui-button">' +
                '<div title="' + this.editor.getLang("pasteSourceFormat") + '" onclick="$$.format(false)" stateful>' +
                '<div class="edui-richtxticon"></div></div>' +
                '<div title="' + this.editor.getLang("tagFormat") + '" onclick="$$.format(2)" stateful>' +
                '<div class="edui-tagicon"></div></div>' +
                '<div title="' + this.editor.getLang("pasteTextFormat") + '" onclick="$$.format(true)" stateful>' +
                '<div class="edui-plaintxticon"></div></div>' +
                '</div>' +
                '</div>' +
                '</div>'
        },
        getStateDom:function () {
            return this.target;
        },
        format:function (param) {
            this.editor.ui._isTransfer = true;
            this.editor.fireEvent('pasteTransfer', param);
        },
        _onClick:function (cur) {
            var node = domUtils.getNextDomNode(cur),
                screenHt = uiUtils.getViewportRect().height,
                subPop = uiUtils.getClientRect(node);

            if ((subPop.top + subPop.height) > screenHt)
                node.style.top = (-subPop.height - cur.offsetHeight) + "px";
            else
                node.style.top = "";

            if (/hidden/ig.test(domUtils.getComputedStyle(node, "visibility"))) {
                node.style.visibility = "visible";
                domUtils.addClass(cur, "edui-state-opened");
            } else {
                node.style.visibility = "hidden";
                domUtils.removeClasses(cur, "edui-state-opened")
            }
        },
        _UIBase_render:UIBase.prototype.render
    };
    utils.inherits(PastePicker, UIBase);
    utils.extend(PastePicker.prototype, Stateful, true);
})();






// ui/toolbar.js
(function (){
    var utils = baidu.editor.utils,
        uiUtils = baidu.editor.ui.uiUtils,
        UIBase = baidu.editor.ui.UIBase,
        Toolbar = baidu.editor.ui.Toolbar = function (options){
            this.initOptions(options);
            this.initToolbar();
        };
    Toolbar.prototype = {
        items: null,
        initToolbar: function (){
            this.items = this.items || [];
            this.initUIBase();
        },
        add: function (item,index){
            if(index === undefined){
                this.items.push(item);
            }else{
                this.items.splice(index,0,item)
            }

        },
        getHtmlTpl: function (){
            var buff = [];
            for (var i=0; i<this.items.length; i++) {
                buff[i] = this.items[i].renderHtml();
            }
            return '<div id="##" class="edui-toolbar %%" onselectstart="return false;" onmousedown="return $$._onMouseDown(event, this);">' +
                buff.join('') +
                '</div>'
        },
        postRender: function (){
            var box = this.getDom();
            for (var i=0; i<this.items.length; i++) {
                this.items[i].postRender();
            }
            uiUtils.makeUnselectable(box);
        },
        _onMouseDown: function (e){
            var target = e.target || e.srcElement,
                tagName = target && target.tagName && target.tagName.toLowerCase();
            if (tagName == 'input' || tagName == 'object' || tagName == 'object') {
                return false;
            }
        }
    };
    utils.inherits(Toolbar, UIBase);

})();


// ui/menu.js
///import core
///import uicore
///import ui\popup.js
///import ui\stateful.js
(function () {
    var utils = baidu.editor.utils,
        domUtils = baidu.editor.dom.domUtils,
        uiUtils = baidu.editor.ui.uiUtils,
        UIBase = baidu.editor.ui.UIBase,
        Popup = baidu.editor.ui.Popup,
        Stateful = baidu.editor.ui.Stateful,
        CellAlignPicker = baidu.editor.ui.CellAlignPicker,

        Menu = baidu.editor.ui.Menu = function (options) {
            this.initOptions(options);
            this.initMenu();
        };

    var menuSeparator = {
        renderHtml:function () {
            return '<div class="edui-menuitem edui-menuseparator"><div class="edui-menuseparator-inner"></div></div>';
        },
        postRender:function () {
        },
        queryAutoHide:function () {
            return true;
        }
    };
    Menu.prototype = {
        items:null,
        uiName:'menu',
        initMenu:function () {
            this.items = this.items || [];
            this.initPopup();
            this.initItems();
        },
        initItems:function () {
            for (var i = 0; i < this.items.length; i++) {
                var item = this.items[i];
                if (item == '-') {
                    this.items[i] = this.getSeparator();
                } else if (!(item instanceof MenuItem)) {
                    item.editor = this.editor;
                    item.theme = this.editor.options.theme;
                    this.items[i] = this.createItem(item);
                }
            }
        },
        getSeparator:function () {
            return menuSeparator;
        },
        createItem:function (item) {
            //新增一個引數menu, 該引數儲存了menuItem所對應的menu引用
            item.menu = this;
            return new MenuItem(item);
        },
        _Popup_getContentHtmlTpl:Popup.prototype.getContentHtmlTpl,
        getContentHtmlTpl:function () {
            if (this.items.length == 0) {
                return this._Popup_getContentHtmlTpl();
            }
            var buff = [];
            for (var i = 0; i < this.items.length; i++) {
                var item = this.items[i];
                buff[i] = item.renderHtml();
            }
            return ('<div class="%%-body">' + buff.join('') + '</div>');
        },
        _Popup_postRender:Popup.prototype.postRender,
        postRender:function () {
            var me = this;
            for (var i = 0; i < this.items.length; i++) {
                var item = this.items[i];
                item.ownerMenu = this;
                item.postRender();
            }
            domUtils.on(this.getDom(), 'mouseover', function (evt) {
                evt = evt || event;
                var rel = evt.relatedTarget || evt.fromElement;
                var el = me.getDom();
                if (!uiUtils.contains(el, rel) && el !== rel) {
                    me.fireEvent('over');
                }
            });
            this._Popup_postRender();
        },
        queryAutoHide:function (el) {
            if (el) {
                if (uiUtils.contains(this.getDom(), el)) {
                    return false;
                }
                for (var i = 0; i < this.items.length; i++) {
                    var item = this.items[i];
                    if (item.queryAutoHide(el) === false) {
                        return false;
                    }
                }
            }
        },
        clearItems:function () {
            for (var i = 0; i < this.items.length; i++) {
                var item = this.items[i];
                clearTimeout(item._showingTimer);
                clearTimeout(item._closingTimer);
                if (item.subMenu) {
                    item.subMenu.destroy();
                }
            }
            this.items = [];
        },
        destroy:function () {
            if (this.getDom()) {
                domUtils.remove(this.getDom());
            }
            this.clearItems();
        },
        dispose:function () {
            this.destroy();
        }
    };
    utils.inherits(Menu, Popup);

    /**
     * @update 2013/04/03 hancong03 新增一個引數menu, 該引數儲存了menuItem所對應的menu引用
     * @type {Function}
     */
    var MenuItem = baidu.editor.ui.MenuItem = function (options) {
        this.initOptions(options);
        this.initUIBase();
        this.Stateful_init();
        if (this.subMenu && !(this.subMenu instanceof Menu)) {
            if (options.className && options.className.indexOf("aligntd") != -1) {
                var me = this;

                //獲取單元格對齊初始狀態
                this.subMenu.selected = this.editor.queryCommandValue( 'cellalignment' );

                this.subMenu = new Popup({
                    content:new CellAlignPicker(this.subMenu),
                    parentMenu:me,
                    editor:me.editor,
                    destroy:function () {
                        if (this.getDom()) {
                            domUtils.remove(this.getDom());
                        }
                    }
                });
                this.subMenu.addListener("postRenderAfter", function () {
                    domUtils.on(this.getDom(), "mouseover", function () {
                        me.addState('opened');
                    });
                });
            } else {
                this.subMenu = new Menu(this.subMenu);
            }
        }
    };
    MenuItem.prototype = {
        label:'',
        subMenu:null,
        ownerMenu:null,
        uiName:'menuitem',
        alwalysHoverable:true,
        getHtmlTpl:function () {
            return '<div id="##" class="%%" stateful onclick="$$._onClick(event, this);">' +
                '<div class="%%-body">' +
                this.renderLabelHtml() +
                '</div>' +
                '</div>';
        },
        postRender:function () {
            var me = this;
            this.addListener('over', function () {
                me.ownerMenu.fireEvent('submenuover', me);
                if (me.subMenu) {
                    me.delayShowSubMenu();
                }
            });
            if (this.subMenu) {
                this.getDom().className += ' edui-hassubmenu';
                this.subMenu.render();
                this.addListener('out', function () {
                    me.delayHideSubMenu();
                });
                this.subMenu.addListener('over', function () {
                    clearTimeout(me._closingTimer);
                    me._closingTimer = null;
                    me.addState('opened');
                });
                this.ownerMenu.addListener('hide', function () {
                    me.hideSubMenu();
                });
                this.ownerMenu.addListener('submenuover', function (t, subMenu) {
                    if (subMenu !== me) {
                        me.delayHideSubMenu();
                    }
                });
                this.subMenu._bakQueryAutoHide = this.subMenu.queryAutoHide;
                this.subMenu.queryAutoHide = function (el) {
                    if (el && uiUtils.contains(me.getDom(), el)) {
                        return false;
                    }
                    return this._bakQueryAutoHide(el);
                };
            }
            this.getDom().style.tabIndex = '-1';
            uiUtils.makeUnselectable(this.getDom());
            this.Stateful_postRender();
        },
        delayShowSubMenu:function () {
            var me = this;
            if (!me.isDisabled()) {
                me.addState('opened');
                clearTimeout(me._showingTimer);
                clearTimeout(me._closingTimer);
                me._closingTimer = null;
                me._showingTimer = setTimeout(function () {
                    me.showSubMenu();
                }, 250);
            }
        },
        delayHideSubMenu:function () {
            var me = this;
            if (!me.isDisabled()) {
                me.removeState('opened');
                clearTimeout(me._showingTimer);
                if (!me._closingTimer) {
                    me._closingTimer = setTimeout(function () {
                        if (!me.hasState('opened')) {
                            me.hideSubMenu();
                        }
                        me._closingTimer = null;
                    }, 400);
                }
            }
        },
        renderLabelHtml:function () {
            return '<div class="edui-arrow"></div>' +
                '<div class="edui-box edui-icon"></div>' +
                '<div class="edui-box edui-label %%-label">' + (this.label || '') + '</div>';
        },
        getStateDom:function () {
            return this.getDom();
        },
        queryAutoHide:function (el) {
            if (this.subMenu && this.hasState('opened')) {
                return this.subMenu.queryAutoHide(el);
            }
        },
        _onClick:function (event, this_) {
            if (this.hasState('disabled')) return;
            if (this.fireEvent('click', event, this_) !== false) {
                if (this.subMenu) {
                    this.showSubMenu();
                } else {
                    Popup.postHide(event);
                }
            }
        },
        showSubMenu:function () {
            var rect = uiUtils.getClientRect(this.getDom());
            rect.right -= 5;
            rect.left += 2;
            rect.width -= 7;
            rect.top -= 4;
            rect.bottom += 4;
            rect.height += 8;
            this.subMenu.showAnchorRect(rect, true, true);
        },
        hideSubMenu:function () {
            this.subMenu.hide();
        }
    };
    utils.inherits(MenuItem, UIBase);
    utils.extend(MenuItem.prototype, Stateful, true);
})();


// ui/combox.js
///import core
///import uicore
///import ui/menu.js
///import ui/splitbutton.js
(function (){
    // todo: menu和item提成通用list
    var utils = baidu.editor.utils,
        uiUtils = baidu.editor.ui.uiUtils,
        Menu = baidu.editor.ui.Menu,
        SplitButton = baidu.editor.ui.SplitButton,
        Combox = baidu.editor.ui.Combox = function (options){
            this.initOptions(options);
            this.initCombox();
        };
    Combox.prototype = {
        uiName: 'combox',
        onbuttonclick:function () {
            this.showPopup();
        },
        initCombox: function (){
            var me = this;
            this.items = this.items || [];
            for (var i=0; i<this.items.length; i++) {
                var item = this.items[i];
                item.uiName = 'listitem';
                item.index = i;
                item.onclick = function (){
                    me.selectByIndex(this.index);
                };
            }
            this.popup = new Menu({
                items: this.items,
                uiName: 'list',
                editor:this.editor,
                captureWheel: true,
                combox: this
            });

            this.initSplitButton();
        },
        _SplitButton_postRender: SplitButton.prototype.postRender,
        postRender: function (){
            this._SplitButton_postRender();
            this.setLabel(this.label || '');
            this.setValue(this.initValue || '');
        },
        showPopup: function (){
            var rect = uiUtils.getClientRect(this.getDom());
            rect.top += 1;
            rect.bottom -= 1;
            rect.height -= 2;
            this.popup.showAnchorRect(rect);
        },
        getValue: function (){
            return this.value;
        },
        setValue: function (value){
            var index = this.indexByValue(value);
            if (index != -1) {
                this.selectedIndex = index;
                this.setLabel(this.items[index].label);
                this.value = this.items[index].value;
            } else {
                this.selectedIndex = -1;
                this.setLabel(this.getLabelForUnknowValue(value));
                this.value = value;
            }
        },
        setLabel: function (label){
            this.getDom('button_body').innerHTML = label;
            this.label = label;
        },
        getLabelForUnknowValue: function (value){
            return value;
        },
        indexByValue: function (value){
            for (var i=0; i<this.items.length; i++) {
                if (value == this.items[i].value) {
                    return i;
                }
            }
            return -1;
        },
        getItem: function (index){
            return this.items[index];
        },
        selectByIndex: function (index){
            if (index < this.items.length && this.fireEvent('select', index) !== false) {
                this.selectedIndex = index;
                this.value = this.items[index].value;
                this.setLabel(this.items[index].label);
            }
        }
    };
    utils.inherits(Combox, SplitButton);
})();


// ui/dialog.js
///import core
///import uicore
///import ui/mask.js
///import ui/button.js
(function (){
    var utils = baidu.editor.utils,
        domUtils = baidu.editor.dom.domUtils,
        uiUtils = baidu.editor.ui.uiUtils,
        Mask = baidu.editor.ui.Mask,
        UIBase = baidu.editor.ui.UIBase,
        Button = baidu.editor.ui.Button,
        Dialog = baidu.editor.ui.Dialog = function (options){
            if(options.name){
                var name = options.name;
                var cssRules = options.cssRules;
                if(!options.className){
                    options.className =  'edui-for-' + name;
                }
                if(cssRules){
                    options.cssRules = '.edui-default .edui-for-'+ name +' .edui-dialog-content  {'+ cssRules +'}'
                }
            }
            this.initOptions(utils.extend({
                autoReset: true,
                draggable: true,
                onok: function (){},
                oncancel: function (){},
                onclose: function (t, ok){
                    return ok ? this.onok() : this.oncancel();
                },
                //是否控制dialog中的scroll事件， 預設為不阻止
                holdScroll: false
            },options));
            this.initDialog();
        };
    var modalMask;
    var dragMask;
    var activeDialog;
    Dialog.prototype = {
        draggable: false,
        uiName: 'dialog',
        initDialog: function (){
            var me = this,
                theme=this.editor.options.theme;
            if(this.cssRules){
                utils.cssRule('edui-customize-'+this.name+'-style',this.cssRules);
            }
            this.initUIBase();
            this.modalMask = (modalMask || (modalMask = new Mask({
                className: 'edui-dialog-modalmask',
                theme:theme,
                onclick: function (){
                    activeDialog && activeDialog.close(false);
                }
            })));
            this.dragMask = (dragMask || (dragMask = new Mask({
                className: 'edui-dialog-dragmask',
                theme:theme
            })));
            this.closeButton = new Button({
                className: 'edui-dialog-closebutton',
                title: me.closeDialog,
                theme:theme,
                onclick: function (){
                    me.close(false);
                }
            });

            this.fullscreen && this.initResizeEvent();

            if (this.buttons) {
                for (var i=0; i<this.buttons.length; i++) {
                    if (!(this.buttons[i] instanceof Button)) {
                        this.buttons[i] = new Button(utils.extend(this.buttons[i],{
                            editor : this.editor
                        },true));
                    }
                }
            }
        },
        initResizeEvent: function () {

            var me = this;

            domUtils.on( window, "resize", function () {

                if ( me._hidden || me._hidden === undefined ) {
                    return;
                }

                if ( me.__resizeTimer ) {
                    window.clearTimeout( me.__resizeTimer );
                }

                me.__resizeTimer = window.setTimeout( function () {

                    me.__resizeTimer = null;

                    var dialogWrapNode = me.getDom(),
                        contentNode = me.getDom('content'),
                        wrapRect = UE.ui.uiUtils.getClientRect( dialogWrapNode ),
                        contentRect = UE.ui.uiUtils.getClientRect( contentNode ),
                        vpRect = uiUtils.getViewportRect();

                    contentNode.style.width = ( vpRect.width - wrapRect.width + contentRect.width ) + "px";
                    contentNode.style.height = ( vpRect.height - wrapRect.height + contentRect.height ) + "px";

                    dialogWrapNode.style.width = vpRect.width + "px";
                    dialogWrapNode.style.height = vpRect.height + "px";

                    me.fireEvent( "resize" );

                }, 100 );

            } );

        },
        fitSize: function (){
            var popBodyEl = this.getDom('body');
//            if (!(baidu.editor.browser.ie && baidu.editor.browser.version == 7)) {
//                uiUtils.removeStyle(popBodyEl, 'width');
//                uiUtils.removeStyle(popBodyEl, 'height');
//            }
            var size = this.mesureSize();
            popBodyEl.style.width = size.width + 'px';
            popBodyEl.style.height = size.height + 'px';
            return size;
        },
        safeSetOffset: function (offset){
            var me = this;
            var el = me.getDom();
            var vpRect = uiUtils.getViewportRect();
            var rect = uiUtils.getClientRect(el);
            var left = offset.left;
            if (left + rect.width > vpRect.right) {
                left = vpRect.right - rect.width;
            }
            var top = offset.top;
            if (top + rect.height > vpRect.bottom) {
                top = vpRect.bottom - rect.height;
            }
            el.style.left = Math.max(left, 0) + 'px';
            el.style.top = Math.max(top, 0) + 'px';
        },
        showAtCenter: function (){

            var vpRect = uiUtils.getViewportRect();

            if ( !this.fullscreen ) {
                this.getDom().style.display = '';
                var popSize = this.fitSize();
                var titleHeight = this.getDom('titlebar').offsetHeight | 0;
                var left = vpRect.width / 2 - popSize.width / 2;
                var top = vpRect.height / 2 - (popSize.height - titleHeight) / 2 - titleHeight;
                var popEl = this.getDom();
                this.safeSetOffset({
                    left: Math.max(left | 0, 0),
                    top: Math.max(top | 0, 0)
                });
                if (!domUtils.hasClass(popEl, 'edui-state-centered')) {
                    popEl.className += ' edui-state-centered';
                }
            } else {
                var dialogWrapNode = this.getDom(),
                    contentNode = this.getDom('content');

                dialogWrapNode.style.display = "block";

                var wrapRect = UE.ui.uiUtils.getClientRect( dialogWrapNode ),
                    contentRect = UE.ui.uiUtils.getClientRect( contentNode );
                dialogWrapNode.style.left = "-100000px";

                contentNode.style.width = ( vpRect.width - wrapRect.width + contentRect.width ) + "px";
                contentNode.style.height = ( vpRect.height - wrapRect.height + contentRect.height ) + "px";

                dialogWrapNode.style.width = vpRect.width + "px";
                dialogWrapNode.style.height = vpRect.height + "px";
                dialogWrapNode.style.left = 0;

                //儲存環境的overflow值
                this._originalContext = {
                    html: {
                        overflowX: document.documentElement.style.overflowX,
                        overflowY: document.documentElement.style.overflowY
                    },
                    body: {
                        overflowX: document.body.style.overflowX,
                        overflowY: document.body.style.overflowY
                    }
                };

                document.documentElement.style.overflowX = 'hidden';
                document.documentElement.style.overflowY = 'hidden';
                document.body.style.overflowX = 'hidden';
                document.body.style.overflowY = 'hidden';

            }

            this._show();
        },
        getContentHtml: function (){
            var contentHtml = '';
            if (typeof this.content == 'string') {
                contentHtml = this.content;
            } else if (this.iframeUrl) {
                contentHtml = '<span id="'+ this.id +'_contmask" class="dialogcontmask"></span><iframe id="'+ this.id +
                    '_iframe" class="%%-iframe" height="100%" width="100%" frameborder="0" src="'+ this.iframeUrl +'"></iframe>';
            }
            return contentHtml;
        },
        getHtmlTpl: function (){
            var footHtml = '';

            if (this.buttons) {
                var buff = [];
                for (var i=0; i<this.buttons.length; i++) {
                    buff[i] = this.buttons[i].renderHtml();
                }
                footHtml = '<div class="%%-foot">' +
                     '<div id="##_buttons" class="%%-buttons">' + buff.join('') + '</div>' +
                    '</div>';
            }

            return '<div id="##" class="%%"><div '+ ( !this.fullscreen ? 'class="%%"' : 'class="%%-wrap edui-dialog-fullscreen-flag"' ) +'><div id="##_body" class="%%-body">' +
                '<div class="%%-shadow"></div>' +
                '<div id="##_titlebar" class="%%-titlebar">' +
                '<div class="%%-draghandle" onmousedown="$$._onTitlebarMouseDown(event, this);">' +
                 '<span class="%%-caption">' + (this.title || '') + '</span>' +
                '</div>' +
                this.closeButton.renderHtml() +
                '</div>' +
                '<div id="##_content" class="%%-content">'+ ( this.autoReset ? '' : this.getContentHtml()) +'</div>' +
                footHtml +
                '</div></div></div>';
        },
        postRender: function (){
            // todo: 保持居中/記住上次關閉位置選項
            if (!this.modalMask.getDom()) {
                this.modalMask.render();
                this.modalMask.hide();
            }
            if (!this.dragMask.getDom()) {
                this.dragMask.render();
                this.dragMask.hide();
            }
            var me = this;
            this.addListener('show', function (){
                me.modalMask.show(this.getDom().style.zIndex - 2);
            });
            this.addListener('hide', function (){
                me.modalMask.hide();
            });
            if (this.buttons) {
                for (var i=0; i<this.buttons.length; i++) {
                    this.buttons[i].postRender();
                }
            }
            domUtils.on(window, 'resize', function (){
                setTimeout(function (){
                    if (!me.isHidden()) {
                        me.safeSetOffset(uiUtils.getClientRect(me.getDom()));
                    }
                });
            });

            //hold住scroll事件，防止dialog的滾動影響頁面
//            if( this.holdScroll ) {
//
//                if( !me.iframeUrl ) {
//                    domUtils.on( document.getElementById( me.id + "_iframe"), !browser.gecko ? "mousewheel" : "DOMMouseScroll", function(e){
//                        domUtils.preventDefault(e);
//                    } );
//                } else {
//                    me.addListener('dialogafterreset', function(){
//                        window.setTimeout(function(){
//                            var iframeWindow = document.getElementById( me.id + "_iframe").contentWindow;
//
//                            if( browser.ie ) {
//
//                                var timer = window.setInterval(function(){
//
//                                    if( iframeWindow.document && iframeWindow.document.body ) {
//                                        window.clearInterval( timer );
//                                        timer = null;
//                                        domUtils.on( iframeWindow.document.body, !browser.gecko ? "mousewheel" : "DOMMouseScroll", function(e){
//                                            domUtils.preventDefault(e);
//                                        } );
//                                    }
//
//                                }, 100);
//
//                            } else {
//                                domUtils.on( iframeWindow, !browser.gecko ? "mousewheel" : "DOMMouseScroll", function(e){
//                                    domUtils.preventDefault(e);
//                                } );
//                            }
//
//                        }, 1);
//                    });
//                }
//
//            }
            this._hide();
        },
        mesureSize: function (){
            var body = this.getDom('body');
            var width = uiUtils.getClientRect(this.getDom('content')).width;
            var dialogBodyStyle = body.style;
            dialogBodyStyle.width = width;
            return uiUtils.getClientRect(body);
        },
        _onTitlebarMouseDown: function (evt, el){
            if (this.draggable) {
                var rect;
                var vpRect = uiUtils.getViewportRect();
                var me = this;
                uiUtils.startDrag(evt, {
                    ondragstart: function (){
                        rect = uiUtils.getClientRect(me.getDom());
                        me.getDom('contmask').style.visibility = 'visible';
                        me.dragMask.show(me.getDom().style.zIndex - 1);
                    },
                    ondragmove: function (x, y){
                        var left = rect.left + x;
                        var top = rect.top + y;
                        me.safeSetOffset({
                            left: left,
                            top: top
                        });
                    },
                    ondragstop: function (){
                        me.getDom('contmask').style.visibility = 'hidden';
                        domUtils.removeClasses(me.getDom(), ['edui-state-centered']);
                        me.dragMask.hide();
                    }
                });
            }
        },
        reset: function (){
            this.getDom('content').innerHTML = this.getContentHtml();
            this.fireEvent('dialogafterreset');
        },
        _show: function (){
            if (this._hidden) {
                this.getDom().style.display = '';

                //要高過編輯器的zindxe
                this.editor.container.style.zIndex && (this.getDom().style.zIndex = this.editor.container.style.zIndex * 1 + 10);
                this._hidden = false;
                this.fireEvent('show');
                baidu.editor.ui.uiUtils.getFixedLayer().style.zIndex = this.getDom().style.zIndex - 4;
            }
        },
        isHidden: function (){
            return this._hidden;
        },
        _hide: function (){
            if (!this._hidden) {
                var wrapNode = this.getDom();
                wrapNode.style.display = 'none';
                wrapNode.style.zIndex = '';
                wrapNode.style.width = '';
                wrapNode.style.height = '';
                this._hidden = true;
                this.fireEvent('hide');
            }
        },
        open: function (){
            if (this.autoReset) {
                //有可能還沒有渲染
                try{
                    this.reset();
                }catch(e){
                    this.render();
                    this.open()
                }
            }
            this.showAtCenter();
            if (this.iframeUrl) {
                try {
                    this.getDom('iframe').focus();
                } catch(ex){}
            }
            activeDialog = this;
        },
        _onCloseButtonClick: function (evt, el){
            this.close(false);
        },
        close: function (ok){
            if (this.fireEvent('close', ok) !== false) {
                //還原環境
                if ( this.fullscreen ) {

                    document.documentElement.style.overflowX = this._originalContext.html.overflowX;
                    document.documentElement.style.overflowY = this._originalContext.html.overflowY;
                    document.body.style.overflowX = this._originalContext.body.overflowX;
                    document.body.style.overflowY = this._originalContext.body.overflowY;
                    delete this._originalContext;

                }
                this._hide();

                //銷燬content
                var content = this.getDom('content');
                var iframe = this.getDom('iframe');
                if (content && iframe) {
                    var doc = iframe.contentDocument || iframe.contentWindow.document;
                    doc && (doc.body.innerHTML = '');
                    domUtils.remove(content);
                }
            }
        }
    };
    utils.inherits(Dialog, UIBase);
})();


// ui/menubutton.js
///import core
///import uicore
///import ui/menu.js
///import ui/splitbutton.js
(function (){
    var utils = baidu.editor.utils,
        Menu = baidu.editor.ui.Menu,
        SplitButton = baidu.editor.ui.SplitButton,
        MenuButton = baidu.editor.ui.MenuButton = function (options){
            this.initOptions(options);
            this.initMenuButton();
        };
    MenuButton.prototype = {
        initMenuButton: function (){
            var me = this;
            this.uiName = "menubutton";
            this.popup = new Menu({
                items: me.items,
                className: me.className,
                editor:me.editor
            });
            this.popup.addListener('show', function (){
                var list = this;
                for (var i=0; i<list.items.length; i++) {
                    list.items[i].removeState('checked');
                    if (list.items[i].value == me._value) {
                        list.items[i].addState('checked');
                        this.value = me._value;
                    }
                }
            });
            this.initSplitButton();
        },
        setValue : function(value){
            this._value = value;
        }
        
    };
    utils.inherits(MenuButton, SplitButton);
})();

// ui/multiMenu.js
///import core
///import uicore
 ///commands 表情
(function(){
    var utils = baidu.editor.utils,
        Popup = baidu.editor.ui.Popup,
        SplitButton = baidu.editor.ui.SplitButton,
        MultiMenuPop = baidu.editor.ui.MultiMenuPop = function(options){
            this.initOptions(options);
            this.initMultiMenu();
        };

    MultiMenuPop.prototype = {
        initMultiMenu: function (){
            var me = this;
            this.popup = new Popup({
                content: '',
                editor : me.editor,
                iframe_rendered: false,
                onshow: function (){
                    if (!this.iframe_rendered) {
                        this.iframe_rendered = true;
                        this.getDom('content').innerHTML = '<iframe id="'+me.id+'_iframe" src="'+ me.iframeUrl +'" frameborder="0"></iframe>';
                        me.editor.container.style.zIndex && (this.getDom().style.zIndex = me.editor.container.style.zIndex * 1 + 1);
                    }
                }
               // canSideUp:false,
               // canSideLeft:false
            });
            this.onbuttonclick = function(){
                this.showPopup();
            };
            this.initSplitButton();
        }

    };

    utils.inherits(MultiMenuPop, SplitButton);
})();



// ui/breakline.js
(function (){
    var utils = baidu.editor.utils,
        UIBase = baidu.editor.ui.UIBase,
        Breakline = baidu.editor.ui.Breakline = function (options){
            this.initOptions(options);
            this.initSeparator();
        };
    Breakline.prototype = {
        uiName: 'Breakline',
        initSeparator: function (){
            this.initUIBase();
        },
        getHtmlTpl: function (){
            return '<br/>';
        }
    };
    utils.inherits(Breakline, UIBase);

})();


// ui/message.js
///import core
///import uicore
(function () {
    var utils = baidu.editor.utils,
        domUtils = baidu.editor.dom.domUtils,
        UIBase = baidu.editor.ui.UIBase,
        Message = baidu.editor.ui.Message = function (options){
            this.initOptions(options);
            this.initMessage();
        };

    Message.prototype = {
        initMessage: function (){
            this.initUIBase();
        },
        getHtmlTpl: function (){
            return '<div id="##" class="edui-message %%">' +
            ' <div id="##_closer" class="edui-message-closer">×</div>' +
            ' <div id="##_body" class="edui-message-body edui-message-type-info">' +
            ' <iframe style="position:absolute;z-index:-1;left:0;top:0;background-color: transparent;" frameborder="0" width="100%" height="100%" src="about:blank"></iframe>' +
            ' <div class="edui-shadow"></div>' +
            ' <div id="##_content" class="edui-message-content">' +
            '  </div>' +
            ' </div>' +
            '</div>';
        },
        reset: function(opt){
            var me = this;
            if (!opt.keepshow) {
                clearTimeout(this.timer);
                me.timer = setTimeout(function(){
                    me.hide();
                }, opt.timeout || 4000);
            }

            opt.content !== undefined && me.setContent(opt.content);
            opt.type !== undefined && me.setType(opt.type);

            me.show();
        },
        postRender: function(){
            var me = this,
                closer = this.getDom('closer');
            closer && domUtils.on(closer, 'click', function(){
                me.hide();
            });
        },
        setContent: function(content){
            this.getDom('content').innerHTML = content;
        },
        setType: function(type){
            type = type || 'info';
            var body = this.getDom('body');
            body.className = body.className.replace(/edui-message-type-[\w-]+/, 'edui-message-type-' + type);
        },
        getContent: function(){
            return this.getDom('content').innerHTML;
        },
        getType: function(){
            var arr = this.getDom('body').match(/edui-message-type-([\w-]+)/);
            return arr ? arr[1]:'';
        },
        show: function (){
            this.getDom().style.display = 'block';
        },
        hide: function (){
            var dom = this.getDom();
            if (dom) {
                dom.style.display = 'none';
                dom.parentNode && dom.parentNode.removeChild(dom);
            }
        }
    };

    utils.inherits(Message, UIBase);

})();


// adapter/editorui.js
//ui跟編輯器的適配層
//那個按鈕彈出是dialog，是下拉筐等都是在這個js中配置
//自己寫的ui也要在這裡配置，放到baidu.editor.ui下邊，當編輯器例項化的時候會根據ueditor.config中的toolbars找到相應的進行例項化
(function () {
    var utils = baidu.editor.utils;
    var editorui = baidu.editor.ui;
    var _Dialog = editorui.Dialog;
    editorui.buttons = {};

    editorui.Dialog = function (options) {
        var dialog = new _Dialog(options);
        dialog.addListener('hide', function () {

            if (dialog.editor) {
                var editor = dialog.editor;
                try {
                    if (browser.gecko) {
                        var y = editor.window.scrollY,
                            x = editor.window.scrollX;
                        editor.body.focus();
                        editor.window.scrollTo(x, y);
                    } else {
                        editor.focus();
                    }


                } catch (ex) {
                }
            }
        });
        return dialog;
    };

    var iframeUrlMap = {
        'anchor':'~/dialogs/anchor/anchor.html',
        'insertimage':'~/dialogs/image/image.html',
        'link':'~/dialogs/link/link.html',
        'spechars':'~/dialogs/spechars/spechars.html',
        'map':'~/dialogs/map/map.html',
        'insertvideo':'~/dialogs/video/video.html',
        'help':'~/dialogs/help/help.html',
        'preview':'~/dialogs/preview/preview.html',
        'emotion':'~/dialogs/emotion/emotion.html',
        'attachment':'~/dialogs/attachment/attachment.html',
        'insertframe':'~/dialogs/insertframe/insertframe.html',
        'edittip':'~/dialogs/table/edittip.html',
        'edittable':'~/dialogs/table/edittable.html',
        'edittd':'~/dialogs/table/edittd.html'
    };
    //為工具欄新增按鈕，以下都是統一的按鈕觸發命令，所以寫在一起
    var btnCmds = ['undo', 'redo', 'formatmatch',
        'bold', 'italic', 'underline', 'fontborder', 'touppercase', 'tolowercase',
        'strikethrough', 'subscript', 'superscript', 'source', 'indent', 'outdent',
        'blockquote', 'pasteplain', 'pagebreak',
        'horizontal', 'removeformat', 'time', 'date', 'unlink',
        'insertparagraphbeforetable', 'insertrow', 'insertcol', 'mergeright', 'mergedown', 'deleterow',
        'deletecol', 'splittorows', 'splittocols', 'splittocells', 'mergecells', 'deletetable', 'drafts','insertpbefore','insertpafter'];

    for (var i = 0, ci; ci = btnCmds[i++];) {
        ci = ci.toLowerCase();
        editorui[ci] = function (cmd) {
            return function (editor) {
                var ui = new editorui.Button({
                    className:'edui-for-' + cmd,
                    title:editor.options.labelMap[cmd] || editor.getLang("labelMap." + cmd) || '',
                    onclick:function () {
                        editor.execCommand(cmd);
                    },
                    theme:editor.options.theme,
                    showText:false
                });
                editorui.buttons[cmd] = ui;
                editor.addListener('selectionchange', function (type, causeByUi, uiReady) {
                    var state = editor.queryCommandState(cmd);
                    if (state == -1) {
                        ui.setDisabled(true);
                        ui.setChecked(false);
                    } else {
                        if (!uiReady) {
                            ui.setDisabled(false);
                            ui.setChecked(state);
                        }
                    }
                });
                return ui;
            };
        }(ci);
    }

    //排版，圖片排版，文字方向
    var typeset = {
        'justify':['left', 'right', 'center', 'justify'],
        'imagefloat':['none', 'left', 'center', 'right'],
        'directionality':['ltr', 'rtl']
    };

    for (var p in typeset) {

        (function (cmd, val) {
            for (var i = 0, ci; ci = val[i++];) {
                (function (cmd2) {
                    editorui[cmd.replace('float', '') + cmd2] = function (editor) {
                        var ui = new editorui.Button({
                            className:'edui-for-' + cmd.replace('float', '') + cmd2,
                            title:editor.options.labelMap[cmd.replace('float', '') + cmd2] || editor.getLang("labelMap." + cmd.replace('float', '') + cmd2) || '',
                            theme:editor.options.theme,
                            onclick:function () {
                                editor.execCommand(cmd, cmd2);
                            }
                        });
                        editorui.buttons[cmd] = ui;
                        editor.addListener('selectionchange', function (type, causeByUi, uiReady) {
                            ui.setDisabled(editor.queryCommandState(cmd) == -1);
                            ui.setChecked(editor.queryCommandValue(cmd) == cmd2 && !uiReady);
                        });
                        return ui;
                    };
                })(ci)
            }
        })(p, typeset[p])
    }

    //字型顏色和背景顏色
    for (var i = 0, ci; ci = ['backcolor', 'forecolor'][i++];) {
        editorui[ci] = function (cmd) {
            return function (editor) {
                var ui = new editorui.ColorButton({
                    className:'edui-for-' + cmd,
                    color:'default',
                    title:editor.options.labelMap[cmd] || editor.getLang("labelMap." + cmd) || '',
                    editor:editor,
                    onpickcolor:function (t, color) {
                        editor.execCommand(cmd, color);
                    },
                    onpicknocolor:function () {
                        editor.execCommand(cmd, 'default');
                        this.setColor('transparent');
                        this.color = 'default';
                    },
                    onbuttonclick:function () {
                        editor.execCommand(cmd, this.color);
                    }
                });
                editorui.buttons[cmd] = ui;
                editor.addListener('selectionchange', function () {
                    ui.setDisabled(editor.queryCommandState(cmd) == -1);
                });
                return ui;
            };
        }(ci);
    }


    var dialogBtns = {
        noOk:['help', 'spechars','preview'],
        ok:['attachment', 'anchor', 'link', 'insertimage', 'map', 'insertframe', 
            'insertvideo', 'insertframe', 'edittip', 'edittable', 'edittd', 'background','phpokinfo']
    };

    for (var p in dialogBtns) {
        (function (type, vals) {
            for (var i = 0, ci; ci = vals[i++];) {
                (function (cmd) {
                    editorui[cmd] = function (editor, iframeUrl, title) {
                        iframeUrl = iframeUrl || (editor.options.iframeUrlMap || {})[cmd] || iframeUrlMap[cmd];
                        title = editor.options.labelMap[cmd] || editor.getLang("labelMap." + cmd) || '';

                        var dialog;
                        //沒有iframeUrl不建立dialog
                        if (iframeUrl) {
                            dialog = new editorui.Dialog(utils.extend({
                                iframeUrl:editor.ui.mapUrl(iframeUrl),
                                editor:editor,
                                className:'edui-for-' + cmd,
                                title:title,
                                holdScroll: cmd === 'insertimage',
                                fullscreen: /charts|preview/.test(cmd),
                                closeDialog:editor.getLang("closeDialog")
                            }, type == 'ok' ? {
                                buttons:[
                                    {
                                        className:'edui-okbutton',
                                        label:editor.getLang("ok"),
                                        editor:editor,
                                        onclick:function () {
                                            dialog.close(true);
                                        }
                                    },
                                    {
                                        className:'edui-cancelbutton',
                                        label:editor.getLang("cancel"),
                                        editor:editor,
                                        onclick:function () {
                                            dialog.close(false);
                                        }
                                    }
                                ]
                            } : {}));

                            editor.ui._dialogs[cmd + "Dialog"] = dialog;
                        }

                        var ui = new editorui.Button({
                            className:'edui-for-' + cmd,
                            title:title,
                            onclick:function () {
                                if (dialog) {
                                    switch (cmd) {
                                        default:
                                            dialog.render();
                                            dialog.open();
                                    }
                                }
                            },
                            theme:editor.options.theme,
                            disabled:(cmd == 'scrawl' && editor.queryCommandState("scrawl") == -1) || ( cmd == 'charts' )
                        });
                        editorui.buttons[cmd] = ui;
                        editor.addListener('selectionchange', function () {
                            //只存在於右鍵選單而無工具欄按鈕的ui不需要檢測狀態
                            var unNeedCheckState = {'edittable':1};
                            if (cmd in unNeedCheckState)return;

                            var state = editor.queryCommandState(cmd);
                            if (ui.getDom()) {
                                ui.setDisabled(state == -1);
                                ui.setChecked(state);
                            }

                        });

                        return ui;
                    };
                })(ci.toLowerCase())
            }
        })(p, dialogBtns[p]);
    }

    editorui.insertcode = function (editor, list, title) {
        list = editor.options['insertcode'] || [];
        title = editor.options.labelMap['insertcode'] || editor.getLang("labelMap.insertcode") || '';
       // if (!list.length) return;
        var items = [];
        utils.each(list,function(key,val){
            items.push({
                label:key,
                value:val,
                theme:editor.options.theme,
                renderLabelHtml:function () {
                    return '<div class="edui-label %%-label" >' + (this.label || '') + '</div>';
                }
            });
        });

        var ui = new editorui.Combox({
            editor:editor,
            items:items,
            onselect:function (t, index) {
                editor.execCommand('insertcode', this.items[index].value);
            },
            onbuttonclick:function () {
                this.showPopup();
            },
            title:title,
            initValue:title,
            className:'edui-for-insertcode',
            indexByValue:function (value) {
                if (value) {
                    for (var i = 0, ci; ci = this.items[i]; i++) {
                        if (ci.value.indexOf(value) != -1)
                            return i;
                    }
                }

                return -1;
            }
        });
        editorui.buttons['insertcode'] = ui;
        editor.addListener('selectionchange', function (type, causeByUi, uiReady) {
            if (!uiReady) {
                var state = editor.queryCommandState('insertcode');
                if (state == -1) {
                    ui.setDisabled(true);
                } else {
                    ui.setDisabled(false);
                    var value = editor.queryCommandValue('insertcode');
                    if(!value){
                        ui.setValue(title);
                        return;
                    }
                    //trace:1871 ie下從原始碼模式切換回來時，字型會帶單引號，而且會有逗號
                    value && (value = value.replace(/['"]/g, '').split(',')[0]);
                    ui.setValue(value);

                }
            }

        });
        return ui;
    };
    editorui.fontfamily = function (editor, list, title) {

        list = editor.options['fontfamily'] || [];
        title = editor.options.labelMap['fontfamily'] || editor.getLang("labelMap.fontfamily") || '';
        if (!list.length) return;
        for (var i = 0, ci, items = []; ci = list[i]; i++) {
            var langLabel = editor.getLang('fontfamily')[ci.name] || "";
            (function (key, val) {
                items.push({
                    label:key,
                    value:val,
                    theme:editor.options.theme,
                    renderLabelHtml:function () {
                        return '<div class="edui-label %%-label" style="font-family:' +
                            utils.unhtml(this.value) + '">' + (this.label || '') + '</div>';
                    }
                });
            })(ci.label || langLabel, ci.val)
        }
        var ui = new editorui.Combox({
            editor:editor,
            items:items,
            onselect:function (t, index) {
                editor.execCommand('FontFamily', this.items[index].value);
            },
            onbuttonclick:function () {
                this.showPopup();
            },
            title:title,
            initValue:title,
            className:'edui-for-fontfamily',
            indexByValue:function (value) {
                if (value) {
                    for (var i = 0, ci; ci = this.items[i]; i++) {
                        if (ci.value.indexOf(value) != -1)
                            return i;
                    }
                }

                return -1;
            }
        });
        editorui.buttons['fontfamily'] = ui;
        editor.addListener('selectionchange', function (type, causeByUi, uiReady) {
            if (!uiReady) {
                var state = editor.queryCommandState('FontFamily');
                if (state == -1) {
                    ui.setDisabled(true);
                } else {
                    ui.setDisabled(false);
                    var value = editor.queryCommandValue('FontFamily');
                    //trace:1871 ie下從原始碼模式切換回來時，字型會帶單引號，而且會有逗號
                    value && (value = value.replace(/['"]/g, '').split(',')[0]);
                    ui.setValue(value);

                }
            }

        });
        return ui;
    };

    editorui.fontsize = function (editor, list, title) {
        title = editor.options.labelMap['fontsize'] || editor.getLang("labelMap.fontsize") || '';
        list = list || editor.options['fontsize'] || [];
        if (!list.length) return;
        var items = [];
        for (var i = 0; i < list.length; i++) {
            var size = list[i] + 'px';
            items.push({
                label:size,
                value:size,
                theme:editor.options.theme,
                renderLabelHtml:function () {
                    return '<div class="edui-label %%-label" style="line-height:1;font-size:' +
                        this.value + '">' + (this.label || '') + '</div>';
                }
            });
        }
        var ui = new editorui.Combox({
            editor:editor,
            items:items,
            title:title,
            initValue:title,
            onselect:function (t, index) {
                editor.execCommand('FontSize', this.items[index].value);
            },
            onbuttonclick:function () {
                this.showPopup();
            },
            className:'edui-for-fontsize'
        });
        editorui.buttons['fontsize'] = ui;
        editor.addListener('selectionchange', function (type, causeByUi, uiReady) {
            if (!uiReady) {
                var state = editor.queryCommandState('FontSize');
                if (state == -1) {
                    ui.setDisabled(true);
                } else {
                    ui.setDisabled(false);
                    ui.setValue(editor.queryCommandValue('FontSize'));
                }
            }

        });
        return ui;
    };

    editorui.paragraph = function (editor, list, title) {
        title = editor.options.labelMap['paragraph'] || editor.getLang("labelMap.paragraph") || '';
        list = editor.options['paragraph'] || [];
        if (utils.isEmptyObject(list)) return;
        var items = [];
        for (var i in list) {
            items.push({
                value:i,
                label:list[i] || editor.getLang("paragraph")[i],
                theme:editor.options.theme,
                renderLabelHtml:function () {
                    return '<div class="edui-label %%-label"><span class="edui-for-' + this.value + '">' + (this.label || '') + '</span></div>';
                }
            })
        }
        var ui = new editorui.Combox({
            editor:editor,
            items:items,
            title:title,
            initValue:title,
            className:'edui-for-paragraph',
            onselect:function (t, index) {
                editor.execCommand('Paragraph', this.items[index].value);
            },
            onbuttonclick:function () {
                this.showPopup();
            }
        });
        editorui.buttons['paragraph'] = ui;
        editor.addListener('selectionchange', function (type, causeByUi, uiReady) {
            if (!uiReady) {
                var state = editor.queryCommandState('Paragraph');
                if (state == -1) {
                    ui.setDisabled(true);
                } else {
                    ui.setDisabled(false);
                    var value = editor.queryCommandValue('Paragraph');
                    var index = ui.indexByValue(value);
                    if (index != -1) {
                        ui.setValue(value);
                    } else {
                        ui.setValue(ui.initValue);
                    }
                }
            }

        });
        return ui;
    };


    //自定義標題
    editorui.customstyle = function (editor) {
        var list = editor.options['customstyle'] || [],
            title = editor.options.labelMap['customstyle'] || editor.getLang("labelMap.customstyle") || '';
        if (!list.length)return;
        var langCs = editor.getLang('customstyle');
        for (var i = 0, items = [], t; t = list[i++];) {
            (function (t) {
                var ck = {};
                ck.label = t.label ? t.label : langCs[t.name];
                ck.style = t.style;
                ck.className = t.className;
                ck.tag = t.tag;
                items.push({
                    label:ck.label,
                    value:ck,
                    theme:editor.options.theme,
                    renderLabelHtml:function () {
                        return '<div class="edui-label %%-label">' + '<' + ck.tag + ' ' + (ck.className ? ' class="' + ck.className + '"' : "")
                            + (ck.style ? ' style="' + ck.style + '"' : "") + '>' + ck.label + "<\/" + ck.tag + ">"
                            + '</div>';
                    }
                });
            })(t);
        }

        var ui = new editorui.Combox({
            editor:editor,
            items:items,
            title:title,
            initValue:title,
            className:'edui-for-customstyle',
            onselect:function (t, index) {
                editor.execCommand('customstyle', this.items[index].value);
            },
            onbuttonclick:function () {
                this.showPopup();
            },
            indexByValue:function (value) {
                for (var i = 0, ti; ti = this.items[i++];) {
                    if (ti.label == value) {
                        return i - 1
                    }
                }
                return -1;
            }
        });
        editorui.buttons['customstyle'] = ui;
        editor.addListener('selectionchange', function (type, causeByUi, uiReady) {
            if (!uiReady) {
                var state = editor.queryCommandState('customstyle');
                if (state == -1) {
                    ui.setDisabled(true);
                } else {
                    ui.setDisabled(false);
                    var value = editor.queryCommandValue('customstyle');
                    var index = ui.indexByValue(value);
                    if (index != -1) {
                        ui.setValue(value);
                    } else {
                        ui.setValue(ui.initValue);
                    }
                }
            }

        });
        return ui;
    };
    editorui.inserttable = function (editor, iframeUrl, title) {
        title = editor.options.labelMap['inserttable'] || editor.getLang("labelMap.inserttable") || '';
        var ui = new editorui.TableButton({
            editor:editor,
            title:title,
            className:'edui-for-inserttable',
            onpicktable:function (t, numCols, numRows) {
                editor.execCommand('InsertTable', {numRows:numRows, numCols:numCols, border:1});
            },
            onbuttonclick:function () {
                this.showPopup();
            }
        });
        editorui.buttons['inserttable'] = ui;
        editor.addListener('selectionchange', function () {
            ui.setDisabled(editor.queryCommandState('inserttable') == -1);
        });
        return ui;
    };

    editorui.lineheight = function (editor) {
        var val = editor.options.lineheight || [];
        if (!val.length)return;
        for (var i = 0, ci, items = []; ci = val[i++];) {
            items.push({
                //todo:寫死了
                label:ci,
                value:ci,
                theme:editor.options.theme,
                onclick:function () {
                    editor.execCommand("lineheight", this.value);
                }
            })
        }
        var ui = new editorui.MenuButton({
            editor:editor,
            className:'edui-for-lineheight',
            title:editor.options.labelMap['lineheight'] || editor.getLang("labelMap.lineheight") || '',
            items:items,
            onbuttonclick:function () {
                var value = editor.queryCommandValue('LineHeight') || this.value;
                editor.execCommand("LineHeight", value);
            }
        });
        editorui.buttons['lineheight'] = ui;
        editor.addListener('selectionchange', function () {
            var state = editor.queryCommandState('LineHeight');
            if (state == -1) {
                ui.setDisabled(true);
            } else {
                ui.setDisabled(false);
                var value = editor.queryCommandValue('LineHeight');
                value && ui.setValue((value + '').replace(/cm/, ''));
                ui.setChecked(state)
            }
        });
        return ui;
    };

    var rowspacings = ['top', 'bottom'];
    for (var r = 0, ri; ri = rowspacings[r++];) {
        (function (cmd) {
            editorui['rowspacing' + cmd] = function (editor) {
                var val = editor.options['rowspacing' + cmd] || [];
                if (!val.length) return null;
                for (var i = 0, ci, items = []; ci = val[i++];) {
                    items.push({
                        label:ci,
                        value:ci,
                        theme:editor.options.theme,
                        onclick:function () {
                            editor.execCommand("rowspacing", this.value, cmd);
                        }
                    })
                }
                var ui = new editorui.MenuButton({
                    editor:editor,
                    className:'edui-for-rowspacing' + cmd,
                    title:editor.options.labelMap['rowspacing' + cmd] || editor.getLang("labelMap.rowspacing" + cmd) || '',
                    items:items,
                    onbuttonclick:function () {
                        var value = editor.queryCommandValue('rowspacing', cmd) || this.value;
                        editor.execCommand("rowspacing", value, cmd);
                    }
                });
                editorui.buttons[cmd] = ui;
                editor.addListener('selectionchange', function () {
                    var state = editor.queryCommandState('rowspacing', cmd);
                    if (state == -1) {
                        ui.setDisabled(true);
                    } else {
                        ui.setDisabled(false);
                        var value = editor.queryCommandValue('rowspacing', cmd);
                        value && ui.setValue((value + '').replace(/%/, ''));
                        ui.setChecked(state)
                    }
                });
                return ui;
            }
        })(ri)
    }
    //有序，無序列表
    var lists = ['insertorderedlist', 'insertunorderedlist'];
    for (var l = 0, cl; cl = lists[l++];) {
        (function (cmd) {
            editorui[cmd] = function (editor) {
                var vals = editor.options[cmd],
                    _onMenuClick = function () {
                        editor.execCommand(cmd, this.value);
                    }, items = [];
                for (var i in vals) {
                    items.push({
                        label:vals[i] || editor.getLang()[cmd][i] || "",
                        value:i,
                        theme:editor.options.theme,
                        onclick:_onMenuClick
                    })
                }
                var ui = new editorui.MenuButton({
                    editor:editor,
                    className:'edui-for-' + cmd,
                    title:editor.getLang("labelMap." + cmd) || '',
                    'items':items,
                    onbuttonclick:function () {
                        var value = editor.queryCommandValue(cmd) || this.value;
                        editor.execCommand(cmd, value);
                    }
                });
                editorui.buttons[cmd] = ui;
                editor.addListener('selectionchange', function () {
                    var state = editor.queryCommandState(cmd);
                    if (state == -1) {
                        ui.setDisabled(true);
                    } else {
                        ui.setDisabled(false);
                        var value = editor.queryCommandValue(cmd);
                        ui.setValue(value);
                        ui.setChecked(state)
                    }
                });
                return ui;
            };
        })(cl)
    };

    editorui.fullscreen = function (editor, title) {
        title = editor.options.labelMap['fullscreen'] || editor.getLang("labelMap.fullscreen") || '';
        var ui = new editorui.Button({
            className:'edui-for-fullscreen',
            title:title,
            theme:editor.options.theme,
            onclick:function () {
                if (editor.ui) {
                    editor.ui.setFullScreen(!editor.ui.isFullScreen());
                }
                this.setChecked(editor.ui.isFullScreen());
            }
        });
        editorui.buttons['fullscreen'] = ui;
        editor.addListener('selectionchange', function () {
            var state = editor.queryCommandState('fullscreen');
            ui.setDisabled(state == -1);
            ui.setChecked(editor.ui.isFullScreen());
        });
        return ui;
    };

    // 表情
    editorui["emotion"] = function (editor, iframeUrl) {
        var cmd = "emotion";
        var ui = new editorui.MultiMenuPop({
            title:editor.options.labelMap[cmd] || editor.getLang("labelMap." + cmd + "") || '',
            editor:editor,
            className:'edui-for-' + cmd,
            iframeUrl:editor.ui.mapUrl(iframeUrl || (editor.options.iframeUrlMap || {})[cmd] || iframeUrlMap[cmd])
        });
        editorui.buttons[cmd] = ui;

        editor.addListener('selectionchange', function () {
            ui.setDisabled(editor.queryCommandState(cmd) == -1)
        });
        return ui;
    };

    /* 簡單上傳外掛 */
    editorui["simpleupload"] = function (editor) {
        var name = 'simpleupload',
            ui = new editorui.Button({
                className:'edui-for-' + name,
                title:editor.options.labelMap[name] || editor.getLang("labelMap." + name) || '',
                onclick:function () {},
                theme:editor.options.theme,
                showText:false
            });
        editorui.buttons[name] = ui;
        editor.addListener('ready', function() {
            var b = ui.getDom('body'),
                iconSpan = b.children[0];
            editor.fireEvent('simpleuploadbtnready', iconSpan);
        });
        editor.addListener('selectionchange', function (type, causeByUi, uiReady) {
            var state = editor.queryCommandState(name);
            if (state == -1) {
                ui.setDisabled(true);
                ui.setChecked(false);
            } else {
                if (!uiReady) {
                    ui.setDisabled(false);
                    ui.setChecked(state);
                }
            }
        });
        return ui;
    };

})();


// adapter/editor.js
///import core
///commands 全屏
///commandsName FullScreen
///commandsTitle  全屏
(function () {
    var utils = baidu.editor.utils,
        uiUtils = baidu.editor.ui.uiUtils,
        UIBase = baidu.editor.ui.UIBase,
        domUtils = baidu.editor.dom.domUtils;
    var nodeStack = [];

    function EditorUI(options) {
        this.initOptions(options);
        this.initEditorUI();
    }

    EditorUI.prototype = {
        uiName:'editor',
        initEditorUI:function () {
            this.editor.ui = this;
            this._dialogs = {};
            this.initUIBase();
            this._initToolbars();
            var editor = this.editor,
                me = this;

            editor.addListener('ready', function () {
                //提供getDialog方法
                editor.getDialog = function (name) {
                    return editor.ui._dialogs[name + "Dialog"];
                };
                domUtils.on(editor.window, 'scroll', function (evt) {
                    baidu.editor.ui.Popup.postHide(evt);
                });
                //提供編輯器實時寬高(全屏時寬高不變化)
                editor.ui._actualFrameWidth = editor.options.initialFrameWidth;

                UE.browser.ie && UE.browser.version === 6 && editor.container.ownerDocument.execCommand("BackgroundImageCache", false, true);

                //display bottom-bar label based on config
                if (editor.options.elementPathEnabled) {
                    editor.ui.getDom('elementpath').innerHTML = '<div class="edui-editor-breadcrumb">' + editor.getLang("elementPathTip") + ':</div>';
                }
                if (editor.options.wordCount) {
                    function countFn() {
                        setCount(editor,me);
                        domUtils.un(editor.document, "click", arguments.callee);
                    }
                    domUtils.on(editor.document, "click", countFn);
                    editor.ui.getDom('wordcount').innerHTML = editor.getLang("wordCountTip");
                }
                editor.ui._scale();
                if (editor.options.scaleEnabled) {
                    if (editor.autoHeightEnabled) {
                        editor.disableAutoHeight();
                    }
                    me.enableScale();
                } else {
                    me.disableScale();
                }
                if (!editor.options.elementPathEnabled && !editor.options.wordCount && !editor.options.scaleEnabled) {
                    editor.ui.getDom('elementpath').style.display = "none";
                    editor.ui.getDom('wordcount').style.display = "none";
                    editor.ui.getDom('scale').style.display = "none";
                }

                if (!editor.selection.isFocus())return;
                editor.fireEvent('selectionchange', false, true);


            });

            editor.addListener('mousedown', function (t, evt) {
                var el = evt.target || evt.srcElement;
                baidu.editor.ui.Popup.postHide(evt, el);

            });
            editor.addListener("delcells", function () {
                if (UE.ui['edittip']) {
                    new UE.ui['edittip'](editor);
                }
                editor.getDialog('edittip').open();
            });

            var pastePop, isPaste = false, timer;
            editor.addListener("afterpaste", function () {
                if(editor.queryCommandState('pasteplain'))
                    return;
                if(baidu.editor.ui.PastePicker){
                    pastePop = new baidu.editor.ui.Popup({
                        content:new baidu.editor.ui.PastePicker({editor:editor}),
                        editor:editor,
                        className:'edui-wordpastepop'
                    });
                    pastePop.render();
                }
                isPaste = true;
            });

            editor.addListener("afterinserthtml", function () {
                clearTimeout(timer);
                timer = setTimeout(function () {
                    if (pastePop && (isPaste || editor.ui._isTransfer)) {
                        if(pastePop.isHidden()){
                            var span = domUtils.createElement(editor.document, 'span', {
                                    'style':"line-height:0px;",
                                    'innerHTML':'\ufeff'
                                }),
                                range = editor.selection.getRange();
                            range.insertNode(span);
                            var tmp= getDomNode(span, 'firstChild', 'previousSibling');
                            tmp && pastePop.showAnchor(tmp.nodeType == 3 ? tmp.parentNode : tmp);
                            domUtils.remove(span);
                        }else{
                            pastePop.show();
                        }
                        delete editor.ui._isTransfer;
                        isPaste = false;
                    }
                }, 200)
            });
            editor.addListener('contextmenu', function (t, evt) {
                baidu.editor.ui.Popup.postHide(evt);
            });
            editor.addListener('keydown', function (t, evt) {
                if (pastePop)    pastePop.dispose(evt);
                var keyCode = evt.keyCode || evt.which;
                if(evt.altKey&&keyCode==90){
                    UE.ui.buttons['fullscreen'].onclick();
                }
            });
            editor.addListener('wordcount', function (type) {
                setCount(this,me);
            });
            function setCount(editor,ui) {
                editor.setOpt({
                    wordCount:true,
                    maximumWords:10000,
                    wordCountMsg:editor.options.wordCountMsg || editor.getLang("wordCountMsg"),
                    wordOverFlowMsg:editor.options.wordOverFlowMsg || editor.getLang("wordOverFlowMsg")
                });
                var opt = editor.options,
                    max = opt.maximumWords,
                    msg = opt.wordCountMsg ,
                    errMsg = opt.wordOverFlowMsg,
                    countDom = ui.getDom('wordcount');
                if (!opt.wordCount) {
                    return;
                }
                var count = editor.getContentLength(true);
                if (count > max) {
                    countDom.innerHTML = errMsg;
                    editor.fireEvent("wordcountoverflow");
                } else {
                    countDom.innerHTML = msg.replace("{#leave}", max - count).replace("{#count}", count);
                }
            }

            editor.addListener('selectionchange', function () {
                if (editor.options.elementPathEnabled) {
                    me[(editor.queryCommandState('elementpath') == -1 ? 'dis' : 'en') + 'ableElementPath']()
                }
                if (editor.options.scaleEnabled) {
                    me[(editor.queryCommandState('scale') == -1 ? 'dis' : 'en') + 'ableScale']();

                }
            });
            var popup = new baidu.editor.ui.Popup({
                editor:editor,
                content:'',
                className:'edui-bubble',
                _onEditButtonClick:function () {
                    this.hide();
                    editor.ui._dialogs.linkDialog.open();
                },
                _onImgEditButtonClick:function (name) {
                    this.hide();
                    editor.ui._dialogs[name] && editor.ui._dialogs[name].open();

                },
                _onImgSetFloat:function (value) {
                    this.hide();
                    editor.execCommand("imagefloat", value);

                },
                _setIframeAlign:function (value) {
                    var frame = popup.anchorEl;
                    var newFrame = frame.cloneNode(true);
                    switch (value) {
                        case -2:
                            newFrame.setAttribute("align", "");
                            break;
                        case -1:
                            newFrame.setAttribute("align", "left");
                            break;
                        case 1:
                            newFrame.setAttribute("align", "right");
                            break;
                    }
                    frame.parentNode.insertBefore(newFrame, frame);
                    domUtils.remove(frame);
                    popup.anchorEl = newFrame;
                    popup.showAnchor(popup.anchorEl);
                },
                _updateIframe:function () {
                    var frame = editor._iframe = popup.anchorEl;
                    if(domUtils.hasClass(frame, 'ueditor_baidumap')) {
                        editor.selection.getRange().selectNode(frame).select();
                        editor.ui._dialogs.mapDialog.open();
                        popup.hide();
                    } else {
                        editor.ui._dialogs.insertframeDialog.open();
                        popup.hide();
                    }
                },
                _onRemoveButtonClick:function (cmdName) {
                    editor.execCommand(cmdName);
                    this.hide();
                },
                queryAutoHide:function (el) {
                    if (el && el.ownerDocument == editor.document) {
                        if (el.tagName.toLowerCase() == 'img' || domUtils.findParentByTagName(el, 'a', true)) {
                            return el !== popup.anchorEl;
                        }
                    }
                    return baidu.editor.ui.Popup.prototype.queryAutoHide.call(this, el);
                }
            });
            popup.render();
            if (editor.options.imagePopup) {
                editor.addListener('mouseover', function (t, evt) {
                    evt = evt || window.event;
                    var el = evt.target || evt.srcElement;
                    if (editor.ui._dialogs.insertframeDialog && /iframe/ig.test(el.tagName)) {
                        var html = popup.formatHtml(
                            '<nobr>' + editor.getLang("property") + ': <span onclick=$$._setIframeAlign(-2) class="edui-clickable">' + editor.getLang("default") + '</span>&nbsp;&nbsp;<span onclick=$$._setIframeAlign(-1) class="edui-clickable">' + editor.getLang("justifyleft") + '</span>&nbsp;&nbsp;<span onclick=$$._setIframeAlign(1) class="edui-clickable">' + editor.getLang("justifyright") + '</span>&nbsp;&nbsp;' +
                                ' <span onclick="$$._updateIframe( this);" class="edui-clickable">' + editor.getLang("modify") + '</span></nobr>');
                        if (html) {
                            popup.getDom('content').innerHTML = html;
                            popup.anchorEl = el;
                            popup.showAnchor(popup.anchorEl);
                        } else {
                            popup.hide();
                        }
                    }
                });
                editor.addListener('selectionchange', function (t, causeByUi) {
                    if (!causeByUi) return;
                    var html = '', str = "",
                        img = editor.selection.getRange().getClosedNode(),
                        dialogs = editor.ui._dialogs;
                    if (img && img.tagName == 'IMG') {
                        var dialogName = 'insertimageDialog';
                        if (img.className.indexOf("edui-faked-video") != -1 || img.className.indexOf("edui-upload-video") != -1) {
                            dialogName = "insertvideoDialog"
                        }
                        if (img.src.indexOf("http://api.map.baidu.com") != -1) {
                            dialogName = "mapDialog"
                        }
                        if (img.className.indexOf("edui-faked-music") != -1) {
                            dialogName = "musicDialog"
                        }
                        if (img.getAttribute("anchorname")) {
                            dialogName = "anchorDialog";
                            html = popup.formatHtml(
                                '<nobr>' + editor.getLang("property") + ': <span onclick=$$._onImgEditButtonClick("anchorDialog") class="edui-clickable">' + editor.getLang("modify") + '</span>&nbsp;&nbsp;' +
                                    '<span onclick=$$._onRemoveButtonClick(\'anchor\') class="edui-clickable">' + editor.getLang("delete") + '</span></nobr>');
                        }
                        if (img.getAttribute("word_img")) {
                            //todo 放到dialog去做查詢
                            editor.word_img = [img.getAttribute("word_img")];
                            dialogName = "wordimageDialog"
                        }
                        if(domUtils.hasClass(img, 'loadingclass') || domUtils.hasClass(img, 'loaderrorclass')) {
                            dialogName = "";
                        }
                        if (!dialogs[dialogName]) {
                            return;
                        }
                        str = '<nobr>' + editor.getLang("property") + ': '+
                            '<span onclick=$$._onImgSetFloat("none") class="edui-clickable">' + editor.getLang("default") + '</span>&nbsp;&nbsp;' +
                            '<span onclick=$$._onImgSetFloat("left") class="edui-clickable">' + editor.getLang("justifyleft") + '</span>&nbsp;&nbsp;' +
                            '<span onclick=$$._onImgSetFloat("right") class="edui-clickable">' + editor.getLang("justifyright") + '</span>&nbsp;&nbsp;' +
                            '<span onclick=$$._onImgSetFloat("center") class="edui-clickable">' + editor.getLang("justifycenter") + '</span>&nbsp;&nbsp;'+
                            '<span onclick="$$._onImgEditButtonClick(\'' + dialogName + '\');" class="edui-clickable">' + editor.getLang("modify") + '</span></nobr>';

                        !html && (html = popup.formatHtml(str))

                    }
                    if (editor.ui._dialogs.linkDialog) {
                        var link = editor.queryCommandValue('link');
                        var url;
                        if (link && (url = (link.getAttribute('_href') || link.getAttribute('href', 2)))) {
                            var txt = url;
                            if (url.length > 30) {
                                txt = url.substring(0, 20) + "...";
                            }
                            if (html) {
                                html += '<div style="height:5px;"></div>'
                            }
                            html += popup.formatHtml(
                                '<nobr>' + editor.getLang("anthorMsg") + ': <a target="_blank" href="' + url + '" title="' + url + '" >' + txt + '</a>' +
                                    ' <span class="edui-clickable" onclick="$$._onEditButtonClick();">' + editor.getLang("modify") + '</span>' +
                                    ' <span class="edui-clickable" onclick="$$._onRemoveButtonClick(\'unlink\');"> ' + editor.getLang("clear") + '</span></nobr>');
                            popup.showAnchor(link);
                        }
                    }

                    if (html) {
                        popup.getDom('content').innerHTML = html;
                        popup.anchorEl = img || link;
                        popup.showAnchor(popup.anchorEl);
                    } else {
                        popup.hide();
                    }
                });
            }

        },
        _initToolbars:function () {
            var editor = this.editor;
            var toolbars = this.toolbars || [];
            var toolbarUis = [];
            for (var i = 0; i < toolbars.length; i++) {
                var toolbar = toolbars[i];
                var toolbarUi = new baidu.editor.ui.Toolbar({theme:editor.options.theme});
                for (var j = 0; j < toolbar.length; j++) {
                    var toolbarItem = toolbar[j];
                    var toolbarItemUi = null;
                    if (typeof toolbarItem == 'string') {
                        toolbarItem = toolbarItem.toLowerCase();
                        if (toolbarItem == '|') {
                            toolbarItem = 'Separator';
                        }
                        if(toolbarItem == '||'){
                            toolbarItem = 'Breakline';
                        }
                        if (baidu.editor.ui[toolbarItem]) {
                            toolbarItemUi = new baidu.editor.ui[toolbarItem](editor);
                        }

                        //fullscreen這裡單獨處理一下，放到首行去
                        if (toolbarItem == 'fullscreen') {
                            if (toolbarUis && toolbarUis[0]) {
                                toolbarUis[0].items.splice(0, 0, toolbarItemUi);
                            } else {
                                toolbarItemUi && toolbarUi.items.splice(0, 0, toolbarItemUi);
                            }

                            continue;


                        }
                    } else {
                        toolbarItemUi = toolbarItem;
                    }
                    if (toolbarItemUi && toolbarItemUi.id) {

                        toolbarUi.add(toolbarItemUi);
                    }
                }
                toolbarUis[i] = toolbarUi;
            }

            //接受外部定製的UI

            utils.each(UE._customizeUI,function(obj,key){
                var itemUI,index;
                if(obj.id && obj.id != editor.key){
                   return false;
                }
                itemUI = obj.execFn.call(editor,editor,key);
                if(itemUI){
                    index = obj.index;
                    if(index === undefined){
                        index = toolbarUi.items.length;
                    }
                    toolbarUi.add(itemUI,index)
                }
            });

            this.toolbars = toolbarUis;
        },
        getHtmlTpl:function () {
            return '<div id="##" class="%%">' +
                '<div id="##_toolbarbox" class="%%-toolbarbox">' +
                (this.toolbars.length ?
                    '<div id="##_toolbarboxouter" class="%%-toolbarboxouter"><div class="%%-toolbarboxinner">' +
                        this.renderToolbarBoxHtml() +
                        '</div></div>' : '') +
                '<div id="##_toolbarmsg" class="%%-toolbarmsg" style="display:none;">' +
                '<div id = "##_upload_dialog" class="%%-toolbarmsg-upload" onclick="$$.showWordImageDialog();">' + this.editor.getLang("clickToUpload") + '</div>' +
                '<div class="%%-toolbarmsg-close" onclick="$$.hideToolbarMsg();">x</div>' +
                '<div id="##_toolbarmsg_label" class="%%-toolbarmsg-label"></div>' +
                '<div style="height:0;overflow:hidden;clear:both;"></div>' +
                '</div>' +
                '<div id="##_message_holder" class="%%-messageholder"></div>' +
                '</div>' +
                '<div id="##_iframeholder" class="%%-iframeholder">' +
                '</div>' +
                //modify wdcount by matao
                '<div id="##_bottombar" class="%%-bottomContainer"><table><tr>' +
                '<td id="##_elementpath" class="%%-bottombar"></td>' +
                '<td id="##_wordcount" class="%%-wordcount"></td>' +
                '<td id="##_scale" class="%%-scale"><div class="%%-icon"></div></td>' +
                '</tr></table></div>' +
                '<div id="##_scalelayer"></div>' +
                '</div>';
        },
        showWordImageDialog:function () {
            this._dialogs['wordimageDialog'].open();
        },
        renderToolbarBoxHtml:function () {
            var buff = [];
            for (var i = 0; i < this.toolbars.length; i++) {
                buff.push(this.toolbars[i].renderHtml());
            }
            return buff.join('');
        },
        setFullScreen:function (fullscreen) {

            var editor = this.editor,
                container = editor.container.parentNode.parentNode;
            if (this._fullscreen != fullscreen) {
                this._fullscreen = fullscreen;
                this.editor.fireEvent('beforefullscreenchange', fullscreen);
                if (baidu.editor.browser.gecko) {
                    var bk = editor.selection.getRange().createBookmark();
                }
                if (fullscreen) {
                    while (container.tagName != "BODY") {
                        var position = baidu.editor.dom.domUtils.getComputedStyle(container, "position");
                        nodeStack.push(position);
                        container.style.position = "static";
                        container = container.parentNode;
                    }
                    this._bakHtmlOverflow = document.documentElement.style.overflow;
                    this._bakBodyOverflow = document.body.style.overflow;
                    this._bakAutoHeight = this.editor.autoHeightEnabled;
                    this._bakScrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);

                    this._bakEditorContaninerWidth = editor.iframe.parentNode.offsetWidth;
                    if (this._bakAutoHeight) {
                        //當全屏時不能執行自動長高
                        editor.autoHeightEnabled = false;
                        this.editor.disableAutoHeight();
                    }

                    document.documentElement.style.overflow = 'hidden';
                    //修復，滾動條不收起的問題

                    window.scrollTo(0,window.scrollY);
                    this._bakCssText = this.getDom().style.cssText;
                    this._bakCssText1 = this.getDom('iframeholder').style.cssText;
                    editor.iframe.parentNode.style.width = '';
                    this._updateFullScreen();
                } else {
                    while (container.tagName != "BODY") {
                        container.style.position = nodeStack.shift();
                        container = container.parentNode;
                    }
                    this.getDom().style.cssText = this._bakCssText;
                    this.getDom('iframeholder').style.cssText = this._bakCssText1;
                    if (this._bakAutoHeight) {
                        editor.autoHeightEnabled = true;
                        this.editor.enableAutoHeight();
                    }

                    document.documentElement.style.overflow = this._bakHtmlOverflow;
                    document.body.style.overflow = this._bakBodyOverflow;
                    editor.iframe.parentNode.style.width = this._bakEditorContaninerWidth + 'px';
                    window.scrollTo(0, this._bakScrollTop);
                }
                if (browser.gecko && editor.body.contentEditable === 'true') {
                    var input = document.createElement('input');
                    document.body.appendChild(input);
                    editor.body.contentEditable = false;
                    setTimeout(function () {
                        input.focus();
                        setTimeout(function () {
                            editor.body.contentEditable = true;
                            editor.fireEvent('fullscreenchanged', fullscreen);
                            editor.selection.getRange().moveToBookmark(bk).select(true);
                            baidu.editor.dom.domUtils.remove(input);
                            fullscreen && window.scroll(0, 0);
                        }, 0)
                    }, 0)
                }

                if(editor.body.contentEditable === 'true'){
                    this.editor.fireEvent('fullscreenchanged', fullscreen);
                    this.triggerLayout();
                }

            }
        },
        _updateFullScreen:function () {
            if (this._fullscreen) {
                var vpRect = uiUtils.getViewportRect();
                this.getDom().style.cssText = 'border:0;position:absolute;left:0;top:' + (this.editor.options.topOffset || 0) + 'px;width:' + vpRect.width + 'px;height:' + vpRect.height + 'px;z-index:' + (this.getDom().style.zIndex * 1 + 100);
                uiUtils.setViewportOffset(this.getDom(), { left:0, top:this.editor.options.topOffset || 0 });
                this.editor.setHeight(vpRect.height - this.getDom('toolbarbox').offsetHeight - this.getDom('bottombar').offsetHeight - (this.editor.options.topOffset || 0),true);
                //不手動調一下，會導致全屏失效
                if(browser.gecko){
                    try{
                        window.onresize();
                    }catch(e){

                    }

                }
            }
        },
        _updateElementPath:function () {
            var bottom = this.getDom('elementpath'), list;
            if (this.elementPathEnabled && (list = this.editor.queryCommandValue('elementpath'))) {

                var buff = [];
                for (var i = 0, ci; ci = list[i]; i++) {
                    buff[i] = this.formatHtml('<span unselectable="on" onclick="$$.editor.execCommand(&quot;elementpath&quot;, &quot;' + i + '&quot;);">' + ci + '</span>');
                }
                bottom.innerHTML = '<div class="edui-editor-breadcrumb" onmousedown="return false;">' + this.editor.getLang("elementPathTip") + ': ' + buff.join(' &gt; ') + '</div>';

            } else {
                bottom.style.display = 'none'
            }
        },
        disableElementPath:function () {
            var bottom = this.getDom('elementpath');
            bottom.innerHTML = '';
            bottom.style.display = 'none';
            this.elementPathEnabled = false;

        },
        enableElementPath:function () {
            var bottom = this.getDom('elementpath');
            bottom.style.display = '';
            this.elementPathEnabled = true;
            this._updateElementPath();
        },
        _scale:function () {
            var doc = document,
                editor = this.editor,
                editorHolder = editor.container,
                editorDocument = editor.document,
                toolbarBox = this.getDom("toolbarbox"),
                bottombar = this.getDom("bottombar"),
                scale = this.getDom("scale"),
                scalelayer = this.getDom("scalelayer");

            var isMouseMove = false,
                position = null,
                minEditorHeight = 0,
                minEditorWidth = editor.options.minFrameWidth,
                pageX = 0,
                pageY = 0,
                scaleWidth = 0,
                scaleHeight = 0;

            function down() {
                position = domUtils.getXY(editorHolder);

                if (!minEditorHeight) {
                    minEditorHeight = editor.options.minFrameHeight + toolbarBox.offsetHeight + bottombar.offsetHeight;
                }

                scalelayer.style.cssText = "position:absolute;left:0;display:;top:0;background-color:#41ABFF;opacity:0.4;filter: Alpha(opacity=40);width:" + editorHolder.offsetWidth + "px;height:"
                    + editorHolder.offsetHeight + "px;z-index:" + (editor.options.zIndex + 1);

                domUtils.on(doc, "mousemove", move);
                domUtils.on(editorDocument, "mouseup", up);
                domUtils.on(doc, "mouseup", up);
            }

            var me = this;
            //by xuheng 全屏時關掉縮放
            this.editor.addListener('fullscreenchanged', function (e, fullScreen) {
                if (fullScreen) {
                    me.disableScale();

                } else {
                    if (me.editor.options.scaleEnabled) {
                        me.enableScale();
                        var tmpNode = me.editor.document.createElement('span');
                        me.editor.body.appendChild(tmpNode);
                        me.editor.body.style.height = Math.max(domUtils.getXY(tmpNode).y, me.editor.iframe.offsetHeight - 20) + 'px';
                        domUtils.remove(tmpNode)
                    }
                }
            });
            function move(event) {
                clearSelection();
                var e = event || window.event;
                pageX = e.pageX || (doc.documentElement.scrollLeft + e.clientX);
                pageY = e.pageY || (doc.documentElement.scrollTop + e.clientY);
                scaleWidth = pageX - position.x;
                scaleHeight = pageY - position.y;

                if (scaleWidth >= minEditorWidth) {
                    isMouseMove = true;
                    scalelayer.style.width = scaleWidth + 'px';
                }
                if (scaleHeight >= minEditorHeight) {
                    isMouseMove = true;
                    scalelayer.style.height = scaleHeight + "px";
                }
            }

            function up() {
                if (isMouseMove) {
                    isMouseMove = false;
                    editor.ui._actualFrameWidth = scalelayer.offsetWidth - 2;
                    editorHolder.style.width = editor.ui._actualFrameWidth + 'px';

                    editor.setHeight(scalelayer.offsetHeight - bottombar.offsetHeight - toolbarBox.offsetHeight - 2,true);
                }
                if (scalelayer) {
                    scalelayer.style.display = "none";
                }
                clearSelection();
                domUtils.un(doc, "mousemove", move);
                domUtils.un(editorDocument, "mouseup", up);
                domUtils.un(doc, "mouseup", up);
            }

            function clearSelection() {
                if (browser.ie)
                    doc.selection.clear();
                else
                    window.getSelection().removeAllRanges();
            }

            this.enableScale = function () {
                //trace:2868
                if (editor.queryCommandState("source") == 1)    return;
                scale.style.display = "";
                this.scaleEnabled = true;
                domUtils.on(scale, "mousedown", down);
            };
            this.disableScale = function () {
                scale.style.display = "none";
                this.scaleEnabled = false;
                domUtils.un(scale, "mousedown", down);
            };
        },
        isFullScreen:function () {
            return this._fullscreen;
        },
        postRender:function () {
            UIBase.prototype.postRender.call(this);
            for (var i = 0; i < this.toolbars.length; i++) {
                this.toolbars[i].postRender();
            }
            var me = this;
            var timerId,
                domUtils = baidu.editor.dom.domUtils,
                updateFullScreenTime = function () {
                    clearTimeout(timerId);
                    timerId = setTimeout(function () {
                        me._updateFullScreen();
                    });
                };
            domUtils.on(window, 'resize', updateFullScreenTime);

            me.addListener('destroy', function () {
                domUtils.un(window, 'resize', updateFullScreenTime);
                clearTimeout(timerId);
            })
        },
        showToolbarMsg:function (msg, flag) {
            this.getDom('toolbarmsg_label').innerHTML = msg;
            this.getDom('toolbarmsg').style.display = '';
            //
            if (!flag) {
                var w = this.getDom('upload_dialog');
                w.style.display = 'none';
            }
        },
        hideToolbarMsg:function () {
            this.getDom('toolbarmsg').style.display = 'none';
        },
        mapUrl:function (url) {
            return url ? url.replace('~/', this.editor.options.UEDITOR_HOME_URL || '') : ''
        },
        triggerLayout:function () {
            var dom = this.getDom();
            if (dom.style.zoom == '1') {
                dom.style.zoom = '100%';
            } else {
                dom.style.zoom = '1';
            }
        }
    };
    utils.inherits(EditorUI, baidu.editor.ui.UIBase);


    var instances = {};


    UE.ui.Editor = function (options) {
        var editor = new UE.Editor(options);
        editor.options.editor = editor;
        utils.loadFile(document, {
            href:editor.options.themePath + editor.options.theme + "/css/ueditor.css",
            tag:"link",
            type:"text/css",
            rel:"stylesheet"
        });

        var oldRender = editor.render;
        editor.render = function (holder) {
            if (holder.constructor === String) {
                editor.key = holder;
                instances[holder] = editor;
            }
            utils.domReady(function () {
                editor.langIsReady ? renderUI() : editor.addListener("langReady", renderUI);
                function renderUI() {
                    editor.setOpt({
                        labelMap:editor.options.labelMap || editor.getLang('labelMap')
                    });
                    new EditorUI(editor.options);
                    if (holder) {
                        if (holder.constructor === String) {
                            holder = document.getElementById(holder);
                        }
                        holder && holder.getAttribute('name') && ( editor.options.textarea = holder.getAttribute('name'));
                        if (holder && /script|textarea/ig.test(holder.tagName)) {
                            var newDiv = document.createElement('div');
                            holder.parentNode.insertBefore(newDiv, holder);
                            var cont = holder.value || holder.innerHTML;
                            editor.options.initialContent = /^[\t\r\n ]*$/.test(cont) ? editor.options.initialContent :
                                cont.replace(/>[\n\r\t]+([ ]{4})+/g, '>')
                                    .replace(/[\n\r\t]+([ ]{4})+</g, '<')
                                    .replace(/>[\n\r\t]+</g, '><');
                            holder.className && (newDiv.className = holder.className);
                            holder.style.cssText && (newDiv.style.cssText = holder.style.cssText);
                            if (/textarea/i.test(holder.tagName)) {
                                editor.textarea = holder;
                                editor.textarea.style.display = 'none';


                            } else {
                                holder.parentNode.removeChild(holder);


                            }
                            if(holder.id){
                                newDiv.id = holder.id;
                                domUtils.removeAttributes(holder,'id');
                            }
                            holder = newDiv;
                            holder.innerHTML = '';
                        }

                    }
                    domUtils.addClass(holder, "edui-" + editor.options.theme);
                    editor.ui.render(holder);
                    var opt = editor.options;
                    //給例項新增一個編輯器的容器引用
                    editor.container = editor.ui.getDom();
                    var parents = domUtils.findParents(holder,true);
                    var displays = [];
                    for(var i = 0 ,ci;ci=parents[i];i++){
                        displays[i] = ci.style.display;
                        ci.style.display = 'block'
                    }
                    if (opt.initialFrameWidth) {
                        opt.minFrameWidth = opt.initialFrameWidth;
                    } else {
                        opt.minFrameWidth = opt.initialFrameWidth = holder.offsetWidth;
                        var styleWidth = holder.style.width;
                        if(/%$/.test(styleWidth)) {
                            opt.initialFrameWidth = styleWidth;
                        }
                    }
                    if (opt.initialFrameHeight) {
                        opt.minFrameHeight = opt.initialFrameHeight;
                    } else {
                        opt.initialFrameHeight = opt.minFrameHeight = holder.offsetHeight;
                    }
                    for(var i = 0 ,ci;ci=parents[i];i++){
                        ci.style.display =  displays[i]
                    }
                    //編輯器最外容器設定了高度，會導致，編輯器不佔位
                    //todo 先去掉，沒有找到原因
                    if(holder.style.height){
                        holder.style.height = ''
                    }
                    editor.container.style.width = opt.initialFrameWidth + (/%$/.test(opt.initialFrameWidth) ? '' : 'px');
                    editor.container.style.zIndex = opt.zIndex;
                    oldRender.call(editor, editor.ui.getDom('iframeholder'));
                    editor.fireEvent("afteruiready");
                }
            })
        };
        return editor;
    };


    /**
     * @file
     * @name UE
     * @short UE
     * @desc UEditor的頂部名稱空間
     */
    /**
     * @name getEditor
     * @since 1.2.4+
     * @grammar UE.getEditor(id,[opt])  =>  Editor例項
     * @desc 提供一個全域性的方法得到編輯器例項
     *
     * * ''id''  放置編輯器的容器id, 如果容器下的編輯器已經存在，就直接返回
     * * ''opt'' 編輯器的可選引數
     * @example
     *  UE.getEditor('containerId',{onready:function(){//建立一個編輯器例項
     *      this.setContent('hello')
     *  }});
     *  UE.getEditor('containerId'); //返回剛建立的例項
     *
     */
    UE.getEditor = function (id, opt) {
        var editor = instances[id];
        if (!editor) {
            editor = instances[id] = new UE.ui.Editor(opt);
            editor.render(id);
        }
        return editor;
    };


    UE.delEditor = function (id) {
        var editor;
        if (editor = instances[id]) {
            editor.key && editor.destroy();
            delete instances[id]
        }
    };

    UE.registerUI = function(uiName,fn,index,editorId){
        utils.each(uiName.split(/\s+/), function (name) {
            UE._customizeUI[name] = {
                id : editorId,
                execFn:fn,
                index:index
            };
        })

    }

})();

// adapter/message.js
UE.registerUI('message', function(editor) {

    var editorui = baidu.editor.ui;
    var Message = editorui.Message;
    var holder;
    var _messageItems = [];
    var me = editor;

    me.addListener('ready', function(){
        holder = document.getElementById(me.ui.id + '_message_holder');
        updateHolderPos();
        setTimeout(function(){
            updateHolderPos();
        }, 500);
    });

    me.addListener('showmessage', function(type, opt){
        opt = utils.isString(opt) ? {
            'content': opt
        } : opt;
        var message = new Message({
                'timeout': opt.timeout,
                'type': opt.type,
                'content': opt.content,
                'keepshow': opt.keepshow,
                'editor': me
            }),
            mid = opt.id || ('msg_' + (+new Date()).toString(36));
        message.render(holder);
        _messageItems[mid] = message;
        message.reset(opt);
        updateHolderPos();
        return mid;
    });

    me.addListener('updatemessage',function(type, id, opt){
        opt = utils.isString(opt) ? {
            'content': opt
        } : opt;
        var message = _messageItems[id];
        message.render(holder);
        message && message.reset(opt);
    });

    me.addListener('hidemessage',function(type, id){
        var message = _messageItems[id];
        message && message.hide();
    });

    function updateHolderPos(){
        var toolbarbox = me.ui.getDom('toolbarbox');
        if (toolbarbox) {
            holder.style.top = toolbarbox.offsetHeight + 3 + 'px';
        }
        holder.style.zIndex = Math.max(me.options.zIndex, me.iframe.style.zIndex) + 1;
    }

});





})();
