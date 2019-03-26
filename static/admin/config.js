/**

 @Name：layuiAdmin iframe版全域性配置
 @Author：賢心
 @Site：http://www.layui.com/admin/
 @License：LPPL（layui付費產品協議）
    
 */
 
layui.define(['laytpl', 'layer', 'element', 'util'], function(exports){
  exports('setter', {
    container: 'LAY_app' //容器ID
    ,base: layui.cache.base //記錄靜態資源所在路徑
    ,views: layui.cache.base + 'tpl/' //動態模板所在目錄
    ,entry: 'index' //預設檢視檔名
    ,engine: '.html' //檢視檔案字尾名
    ,pageTabs: true //是否開啟頁面選項卡功能。iframe版推薦開啟
    
    ,name: 'layuiAdmin'
    ,tableName: 'layuiAdmin' //本地儲存表名
    ,MOD_NAME: 'admin' //模組事件名
    
    ,debug: false //是否開啟除錯模式。如開啟，介面異常時會丟擲異常 URL 等資訊

    //自定義請求欄位
    ,request: {
      tokenName: false //自動攜帶 token 的欄位名（如：access_token）。可設定 false 不攜帶。
    }
    
    //自定義響應欄位
    ,response: {
      statusName: 'status' //資料狀態的欄位名稱
      ,statusCode: {
        ok: 1 //資料狀態一切正常的狀態碼
        ,logout: 1001 //登入狀態失效的狀態碼
      }
      ,msgName: 'info' //狀態資訊的欄位名稱
      ,dataName: 'info' //資料詳情的欄位名稱
    }
    
    //擴充套件的第三方模組
    ,extend: [
      'echarts', //echarts 核心包
      'echartsTheme' //echarts 主題
    ]
    
    //主題配置
    ,theme: {
      //內建主題配色方案
      color: [{
        main: '#20222A' //主題色
        ,selected: '#009688' //選中色
        ,alias: 'default' //預設別名
      },{
        main: '#03152A'
        ,selected: '#3B91FF'
        ,alias: 'dark-blue' //藏藍
      },{
        main: '#2E241B'
        ,selected: '#A48566'
        ,alias: 'coffee' //咖啡
      },{
        main: '#50314F'
        ,selected: '#7A4D7B'
        ,alias: 'purple-red' //紫紅
      },{
        main: '#344058'
        ,logo: '#1E9FFF'
        ,selected: '#1E9FFF'
        ,alias: 'ocean' //海洋
      },{
        main: '#3A3D49'
        ,logo: '#2F9688'
        ,selected: '#5FB878'
        ,alias: 'green' //墨綠
      },{
        main: '#20222A'
        ,logo: '#F78400'
        ,selected: '#F78400'
        ,alias: 'red' //橙色
      },{
        main: '#28333E'
        ,logo: '#AA3130'
        ,selected: '#AA3130'
        ,alias: 'fashion-red' //時尚紅
      },{
        main: '#24262F'
        ,logo: '#3A3D49'
        ,selected: '#009688'
        ,alias: 'classic-black' //經典黑
      },{
        logo: '#226A62'
        ,header: '#2F9688'
        ,alias: 'green-header' //墨綠頭
      },{
        main: '#344058'
        ,logo: '#0085E8'
        ,selected: '#1E9FFF'
        ,header: '#1E9FFF'
        ,alias: 'ocean-header' //海洋頭
      },{
        header: '#393D49'
        ,alias: 'classic-black-header' //經典黑頭
      }]
      
      //初始的顏色索引，對應上面的配色方案陣列索引
      //如果本地已經有主題色記錄，則以本地記錄為優先，除非請求本地資料（localStorage）
      ,initColorIndex: 0
    }
  });
});
