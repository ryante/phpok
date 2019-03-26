
	var k=0;
	window.onscroll=function(){
		/*if(scrollBotom()){
			var oParent=document.getElementById('main');
			var oBox =document.createElement('div');
			oBox.className='box';
			oParent.appendChild(oBox);
			var oPic =document.createElement('div');
			oPic.className='pic';
			oBox.appendChild(oPic);
			var oImg =document.createElement('img');
			oImg.src='./img/'+k+'.jpg';
			oPic.appendChild(oImg);
			k=k%97;k++;
       		waterFall('main','box');
		}*/
	}
	var myMain=document.getElementById('main');
	var myBox=getByClass(myMain,'box');
	var myBoxW=myBox[0].offsetWidth;
	window.onresize= function(){
		var myNum=Math.floor(document.documentElement.clientWidth/myBoxW);//視窗改變大小時計算一行能存幾個Box
		waterFall('main','box',myNum);
	}
	function waterFall(oParent,box){
		var oParent=document.getElementById(oParent);
		var aBox=getByClass(oParent,box);
		var oBoxW=aBox[0].offsetWidth;//一個BOX的寬度
		var boxNum=Math.floor(document.documentElement.clientWidth/oBoxW);//看瀏覽器的寬度，一行能放下幾個box
		if(arguments[2]){//判斷引數集合第三個引數是否存在
			boxNum=arguments[2];
		}
		oParent.style.cssText='width:'+oBoxW*boxNum+'px;margin:0 auto';

		var boxArr=[]//用於儲存每列的高度。
		for(var i=0;i<aBox.length;i++){
			aBox[i].style.position='absolute';
			var boxH=aBox[i].offsetHeight;
			if(i<boxNum){//6
				boxArr[i]=boxH;//把第一行中的所有高度存入boxArr中以便，後續圖片排列；
				aBox[i].style.top=0;
				aBox[i].style.left=oBoxW*i+'px';
			}else{
				var minH=Math.min.apply(null,boxArr);//計算出最小的那個值；
				var minIndex=getMinIndex(boxArr,minH);
				aBox[i].style.top=minH+'px';
				aBox[i].style.left=minIndex*oBoxW+'px';//等於上一個最小值的left
				boxArr[minIndex]+=aBox[i].offsetHeight;//更新添加了塊框後的列高
				
			}
		}
		var maxH=Math.max.apply(null,boxArr);
		oParent.style.height=maxH+'px';
		
	}
	function getMinIndex(arr,minH){
		for(var i in arr){
			if(arr[i]==minH){
				return i;
			}
		}

	}
	function getByClass(oParent,oClass){
		var arr=[];
		var obj=oParent.getElementsByTagName('*');
		for(var i=0;i<obj.length;i++){
			if(obj[i].className.indexOf(oClass) >-1 ){
			    arr.push(obj[i]);
			}
		}
		return arr;
	}	
	function scrollBotom(){//判斷是否到底部了
	    var oParent=document.getElementById('main');
	    var aBox=getByClass(oParent,'box');
	    var lastBoxH=aBox[aBox.length-1].offsetTop+Math.floor(aBox[aBox.length-1].offsetHeight/2);
	    //獲取到最後一個圖片的offseTop+自身一半高，看是否小於滾動條的scroolTop；
	    var scrollTop=document.documentElement.scrollTop||document.body.scrollTop;//解決相容性
	    var documentH=document.documentElement.clientHeight;//頁面高度
	    return (lastBoxH<scrollTop+documentH)?true:false;//到達指定高度後 返回true
	}
	window.onload = function(){
		waterFall('main','box');//初始化。
	}