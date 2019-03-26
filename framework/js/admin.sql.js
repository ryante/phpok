/**
 * SQL操作類
 * @作者 qinggan <admin@phpok.com>
 * @版權 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2017年10月04日
**/
;(function($){
	$.admin_sql = {

		/**
		 * 選擇只有碎片的表
		**/
		select_free:function()
		{
			$.input.checkbox_none();
			$("input[sign='free']").prop("checked",true);
			return true;
		},

		/**
		 * 優化資料表
		**/
		optimize:function()
		{
			var id = $.input.checkbox_join();
			if(!id){
				$.dialog.alert(p_lang('請選擇資料表'));
				return false;
			}
			var url = get_url('sql','optimize','id='+$.str.encode(id));
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.alert(p_lang('資料優化成功'),function(){
						$.phpok.reload();
					},'succeed');
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			})
		},

		/**
		 * 修復資料表
		**/
		repair:function()
		{
			var id = $.input.checkbox_join();
			if(!id){
				$.dialog.alert(p_lang('請選擇資料表'));
				return false;
			}
			var url = get_url('sql','repair','id='+$.str.encode(id));
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.alert(p_lang('資料表修復成功'),function(){
						$.phpok.reload();
					},'succeed');
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
		},

		/**
		 * 備份資料表
		**/
		backup:function()
		{
			$.dialog.confirm(p_lang('確定要執行備份操作嗎？未選定表將備份全部！'),function(){
				var id = $.input.checkbox_join();
				if(!id){
					id = 'all';
				}
				var url = get_url('sql','backup','id='+$.str.encode(id));
				$.phpok.go(url);
			});
		},

		/**
		 * 恢復指定的備份檔案
		**/
		recover:function(id)
		{
			$.dialog.confirm(p_lang('確定要恢復到這個備份'),function(){
				var url = get_url('sql','recover','id='+id);
				$.phpok.go(url);
			});
		},

		/**
		 * 刪除指定的備份檔案
		**/
		del:function(id)
		{
			$.dialog.confirm(p_lang('確定要刪除這個備份嗎？刪除後就不能恢復了'),function(){
				var url = get_url('sql','delete','id='+id);
				$.phpok.go(url);
			});
		},

		/**
		 * 查看錶明細資訊
		**/
		show:function(tbl)
		{
			var url = get_url('sql','show','table='+$.str.encode(tbl));
			$.dialog.open(url,{
				'title':p_lang('查看錶 {tbl} 明細',tbl),
				'lock':true,
				'width':'500px',
				'height':'500px',
				'cancel':true
			});
		},

		/**
		 * 刪除表操作
		**/
		tbl_delete:function(tbl)
		{
			$.dialog.confirm(p_lang('確定要刪除表 {tbl} 資訊嗎？',tbl),function(){
				var url = get_url('sql','table_delete','tbl='+$.str.encode(tbl));
				$.phpok.json(url,function(rs){
					if(rs.status){
						$.dialog.alert(p_lang('刪除成功'),function(){
							$.phpok.reload();
						},'succeed');
						return false;
					}
					$.dialog.alert(rs.info);
					return false;
				});
			});
		}
	}
})(jQuery);
$(document).ready(function(){
	top.$.desktop.title(p_lang('資料庫管理'));
});