/**
 * 購物車中涉及到的JS操作，此處使用jQuery封裝
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2016年09月01日
**/

;(function($){
	$.cart = {
		//新增到購物車中
		//id為產品ID
		add: function(id,qty){
			var self = this;
			var url = this._addurl(id,qty);
			if(!url){
				return false;
			}
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('成功加入購物車'));
					self.total();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
			return false;
		},
		/**
		 * 自定義產品加入購物車
		 * @引數 title 產品名稱
		 * @引數 price 價格 
		 * @引數 qty 數量
		 * @引數 thumb 縮圖
		**/
		add2: function(title,price,qty,thumb){
			var url = this._addurl2(title,price,qty,thumb);
			if(!url){
				return false;
			}
			var self = this;
			$.phpok.json(url,function(rs){
				if(rs.status){
					$.dialog.tips(p_lang('成功加入購物車'));
					self.total();
					return true;
				}
				$.dialog.alert(rs.info);
				return false;
			});
			return false;
		},
		/**
		 * 自定義產品立即訂購
		 * @引數 title 產品名稱
		 * @引數 price 價格 
		 * @引數 qty 數量
		 * @引數 thumb 縮圖
		**/
		onebuy2: function(title,price,qty,thumb){
			var url = this._addurl2(title,price,qty,thumb);
			if(!url){
				return false;
			}
			$.phpok.json(url+"&_clear=1",function(data){
				if(data.status){
					$.phpok.go(get_url('cart','checkout','id[]='+data.info));
					return true;
				}
				$.dialog.alert(data.info);
				return false;
			});
			return false;
		},
		/**
		 * 系統產品立即訂購
		 * @引數 id 產品ID
		 * @引數 qty 數量
		**/
		onebuy: function(id,qty){
			var url = this._addurl(id,qty);
			if(!url){
				return false;
			}
			$.phpok.json(url+"&_clear=1",function(data){
				if(data.status){
					$.phpok.go(get_url('cart','checkout','id[]='+data.info));
					return true;
				}
				$.dialog.alert(data.info);
				return false;
			});
			return false;
		},
		_addurl2: function(title,price,qty,thumb){
			if(!title || title == 'undefined'){
				$.dialog.alert(p_lang('名稱不能為空'));
				return false;
			}
			if(!price || price == 'undefined'){
				$.dialog.alert(p_lang('價格不能為空'));
				return false;
			}
			if(!qty || qty == 'undefined'){
				qty = 1;
			}
			qty = parseInt(qty,10);
			if(qty < 1){
				qty = 1;
			}
			var url = api_url('cart','add','title='+$.str.encode(title)+"&price="+$.str.encode(price)+"&qty="+qty);
			if(thumb && thumb != 'undefined'){
				url += "&thumb="+$.str.encode(thumb);
			}
			return url;
		},
		_addurl:function(id,qty){
			var url = api_url('cart','add','id='+id);
			if(qty && qty != 'undefined'){
				url += "&qty="+qty;
			}
			//判斷屬性
			if($("input[name=attr]").length>0){
				var attr = '';
				var showalert = false;
				$("input[name=attr]").each(function(i){
					var val = $(this).val();
					if(!val){
						showalert = true;
					}
					if(attr){
						attr += ",";
					}
					attr += val;
				});
				if(!attr || showalert){
					$.dialog.alert(p_lang('請選擇商品屬性'));
					return false;
				}
				url += "&ext="+attr;
			}
			return url;
		},
		//取得選中的產品價格
		price:function()
		{
			var ids = $.checkbox.join();
			if(!ids){
				$.dialog.alert(p_lang('請選擇要進入結算的產品'),function(){
					$("#total_price").text('--.--');
				});
				return true;
			}
			var url = api_url('cart','price','id='+$.str.encode(ids));
			$.phpok.json(url,function(data){
				if(data.status){
					$("#total_price").html(data.info.price);
					return true;
				}
				$("#total_price").text('--.--');
				$.dialog.alert(data.info);
				return false;
			});
		},
		//更新產品數量
		//id為購物車自動生成的ID號（不是產品ID號，請注意）
		update: function(id,showtip)
		{
			var qty = $("#qty_"+id).val();
			if(!qty || parseInt(qty) < 1){
				$.dialog.alert("購物車產品數量不能為空");
				return false;
			}
			var url = api_url('cart','qty')+"&id="+id+"&qty="+qty;
			if(showtip && showtip != 'undefined'){
				var tip = $.dialog.tips(showtip);
			}
			$.phpok.json(url,function(rs){
				if(showtip && showtip != 'undefined'){
					tip.close();
				}
				if(rs.status){
					$.phpok.reload();
				}else{
					if(!rs.info) rs.info = '更新失敗';
					$.dialog.alert(rs.info);
					return false;
				}
			});
		},
		//計算購物車數量
		//這裡使用非同步Ajax處理
		total:function(func){
			$.phpok.json(api_url('cart','total'),function(rs){
				if(rs.status){
					if(rs.info){
						$("#head_cart_num").html(rs.info).show();
					}else{
						$("#head_cart_num").html('0').hide();
					}
					if(func && func != 'undefined'){
						(func)(rs);
					}
				}
			});
			return false;
		},
		//產品增加操作
		//id為購物車裡的ID，不是產品ID
		//qty，是要增加的數值，
		plus:function(id,num){
			var qty = $("#qty_"+id).val();
			if(!qty){
				qty = 1;
			}
			if(!num || num == 'undefined'){
				num = 1;
			}
			qty = parseInt(qty) + parseInt(num);
			$("#qty_"+id).val(qty);
			this.update(id);
		},
		minus:function(id,num){
			var qty = $("#qty_"+id).val();
			if(!qty){
				qty = 1;
			}
			if(qty<2){
				$.dialog.alert('產品數量不能少於1');
				return false;
			}
			if(!num || num == 'undefined'){
				num = 1;
			}
			qty = parseInt(qty) - parseInt(num);
			$("#qty_"+id).val(qty);
			this.update(id);
		},
		//刪除產品資訊
		//id為購物車自動生成的ID號（不是產品ID號，請注意）
		del: function(id){
			if(!id || id == 'undefined'){
				var id = $.checkbox.join();
				if(!id){
					$.dialog.alert(p_lang('請選擇要刪除的產品'));
					return false;
				}
				var tmplist = id.split(',');
				var title = [];
				for(var i in tmplist){
					var t = $("#title_"+tmplist[i]).text();
					if(t){
						title.push(t);
					}
				}
				var tip = p_lang('確定要刪除產品<br><span style="color:red">{title}</span><br>刪除後不能恢復',title.join("<br/>"));
			}else{
				title = $("#title_"+id).text();
				var tip = p_lang('確定要刪除產品<br><span style="color:red">{title}</span><br>刪除後不能恢復',title);
			}
			$.dialog.confirm(tip,function(){
				var url = api_url('cart','delete','id='+$.str.encode(id));
				$.phpok.json(url,function(data){
					if(data.status){
						$.phpok.reload();
						return true;
					}
					if(!data.info){
						data.info = p_lang('刪除失敗');
					}
					$.dialog.alert(data.info);
					return false;
				});
			});
		}
	};
})(jQuery);
