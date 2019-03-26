/**
 * 會員頭像修改
 * @作者 qinggan <admin@phpok.com>
 * @版權 2008-2018 深圳市錕鋙科技有限公司
 * @網站 http://www.phpok.com
 * @版本 5.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @日期 2018年10月26日
**/

var chooseGallery;
var chooseCamera;
var cropImage;
var imgData;
var clipContent;
var clipAction;
var showContent;
var showImg;
var targetImg;
var targetImgCamera;

initPage();

function initPage() {
	initParams();
	initListeners();
	initImgClip();
}

function initParams() {
	targetImg = document.querySelector('#targetImg');
	targetImgCamera = document.querySelector('#targetImgCamera');
	chooseGallery = document.querySelector('.choose-gallery');
	chooseCamera = document.querySelector('.choose-camera');
	clipContent = document.querySelector('.clip-content');
	clipAction = document.querySelector('.clip-action');
	showContent = document.querySelector('.show-content');
	showImg = document.querySelector('.show-img');
}

function initImgClip() {
	new FileInput({
		container: '#targetImg',
		isMulti: false,
		type: 'Image_Camera',
		success: function(b64, file, detail) {
			loadImg(b64);
		},
		error: function(error) {
			console.error(error);
		}
	});
	new FileInput({
		container: '#targetImgCamera',
		isMulti: false,
		type: 'Camera',
		success: function(b64, file, detail) {
			loadImg(b64);
		},
		error: function(error) {
			console.error(error);
		}
	});
}

function loadImg(b64) {
	changeImgClipShow(true);

	var img = new Image();
	img.src = b64;

	img.onload = function() {
		EXIF.getData(img, function() {
			var orientation = EXIF.getTag(this, 'Orientation');
			
			cropImage && cropImage.destroy();
			cropImage = new ImageClip({
				container: '.img-clip',
				img,
				// 0代表按下才顯示，1恆顯示，-1不顯示
				sizeTipsStyle: 0,
				// 為1一般是螢幕畫素x2這個寬高
				// 最終的大小為：螢幕畫素*螢幕畫素比（手機中一般為2）*compressScaleRatio
				compressScaleRatio: 1.1,
				// iphone中是否繼續放大：x*iphoneFixedRatio
				// 最好compressScaleRatio*iphoneFixedRatio不要超過2
				iphoneFixedRatio: 1.8,
				// 減去頂部間距，底部bar,以及顯示間距
				maxCssHeight: window.innerHeight - 100 - 50 - 20,
				// 放大鏡捕獲的影象半徑
				captureRadius: 30,
				// 是否採用原影象素（不會壓縮）
				isUseOriginSize: false,
				// 增加最大寬度，增加後最大不會超過這個寬度
				maxWidth: 500,
				// 是否固定框高，優先順序最大，設定後其餘所有係數都無用直接使用這個固定的寬，高度自適應
				forceWidth: 0,
				// 同上，但是一般不建議設定，因為很可能會改變寬高比導致拉昇，特殊場景下使用
				forceHeight: 0,
				// 壓縮質量
				quality: 0.92,
				mime: 'image/jpeg',
			});

			// 6代表圖片需要順時針修復（預設逆時針處理了，所以需要順過來修復）
			switch (orientation) {
				case 6:
					cropImage.rotate(true);
					break;
				default:
					break;
			}

		});
	};
}

function resizeShowImg(b64) {
	var img = new Image();

	img.src = b64;
	img.onload = showImgOnload;
}

function showImgOnload() {
	// 必須用一個新的圖片載入，否則如果只用showImg的話永遠都是第1張
	// margin的話由於有樣式，所以自動控制了
	var width = this.width;
	var height = this.height;
	var wPerH = width / height;
	var MAX_WIDTH = Math.min(window.innerWidth, width);
	var MAX_HEIGHT = Math.min(window.innerHeight - 50 - 100, height);
	var legalWidth = MAX_WIDTH;
	var legalHeight = legalWidth / wPerH;

	if (MAX_WIDTH && legalWidth > MAX_WIDTH) {
		legalWidth = MAX_WIDTH;
		legalHeight = legalWidth / wPerH;
	}
	if (MAX_HEIGHT && legalHeight > MAX_HEIGHT) {
		legalHeight = MAX_HEIGHT;
		legalWidth = legalHeight * wPerH;
	}
	showImg.style.marginTop = '10%';
	showImg.style.width = legalWidth + 'px';
	showImg.style.height = legalHeight + 'px';
}

function changeImgClipShow(isClip) {
	if (isClip) {
		chooseGallery.classList.add('hidden');
		chooseCamera.classList.add('hidden');
		clipAction.classList.remove('hidden');
	} else {
		chooseGallery.classList.remove('hidden');
		chooseCamera.classList.remove('hidden');
		clipAction.classList.add('hidden');
		targetImg.value = '';
		targetImgCamera.value = '';
	}
}

function initListeners() {
	document.querySelector('#btn-reload').addEventListener('click', function() {
		cropImage && cropImage.destroy();
		changeImgClipShow(false);
	});
	document.querySelector('#btn-back').addEventListener('click', function() {
		changeContent(false);
	});
	document.querySelector('#btn-save').addEventListener('click', function() {
		var obj = {};
		obj.data = imgData;
		var tipobj = $.dialog.tips('正在上傳中，請稍候…',100).lock();
		$.phpok.json(api_url("usercp","avatar","type=base64"),function(rs){
			tipobj.close();
			if(rs.status){
				$.dialog.alert('頭像更新成功',function(){
					$.phpok.go(get_url('usercp'));
				},'success');
				return true;
			}
			$.dialog.alert(rs.info);
			return false;
		},obj);
	});
	document.querySelector('#btn-detail').addEventListener('click', function() {
		showImgDataLen(imgData);
	});

	document.querySelector('#btn-maxrect').addEventListener('click', function() {
		if (!cropImage) {
			$.dialog.alert('請選擇圖片');
			return;
		}
		cropImage.resetClipRect();
	});

	document.querySelector('#btn-rotate-anticlockwise').addEventListener('click', function() {
		if (!cropImage) {
			$.dialog.alert('請選擇圖片');
			return;
		}
		cropImage.rotate(false);
	});

	document.querySelector('#btn-rotate-clockwise').addEventListener('click', function() {
		if (!cropImage) {
			$.dialog.alert('請選擇圖片');
			return;
		}
		cropImage.rotate(true);
	});

	document.querySelector('#btn-verify').addEventListener('click', function() {
		if (!cropImage) {
			$.dialog.alert('請選擇圖片');
			return;
		}
		$.dialog.confirm('是否裁剪圖片並處理？',function(){
			cropImage.clip(false);
			imgData = cropImage.getClipImgData();
			recognizeImg(function() {
				changeContent(true);
			}, function(error) {
				$.dialog.alert(JSON.stringify(error));
			});
		});
	});
}

function showImgDataLen(imgData) {
	var len = imgData.length;
	var sizeStr = len + 'B';

	if (len > 1024 * 1024) {
		sizeStr = (Math.round(len / (1024 * 1024))).toString() + 'MB';
	} else if (len > 1024) {
		sizeStr = (Math.round(len / 1024)).toString() + 'KB';
	}
	$.dialog.alert('處理後文件大小：'+sizeStr);
}

function changeContent(isShowContent) {
	if (isShowContent) {
		showContent.classList.remove('hidden');
		clipContent.classList.add('hidden');

		resizeShowImg(imgData);
		showImg.src = imgData;

	} else {
		showContent.classList.add('hidden');
		clipContent.classList.remove('hidden');
	}
}

function b64ToBlob(urlData) {
	var arr = urlData.split(',');
	var mime = arr[0].match(/:(.*?);/)[1] || 'image/png';
	// 去掉url的頭，並轉化為byte
	var bytes = window.atob(arr[1]);

	// 處理異常,將ascii碼小於0的轉換為大於0
	var ab = new ArrayBuffer(bytes.length);
	// 生成檢視（直接針對記憶體）：8位無符號整數，長度1個位元組
	var ia = new Uint8Array(ab);
	for (var i = 0; i < bytes.length; i++) {
		ia[i] = bytes.charCodeAt(i);
	}

	return new Blob([ab], {
		type: mime
	});
}

function downloadFile(content) {
	// Convert image to 'octet-stream' (Just a download, really)
	var imageObj = content.replace("image/jpeg", "image/octet-stream");
	window.location.href = imageObj;
}

function recognizeImg(success, error) {
	// 裡面正常有：裁邊，擺正，梯形矯正，銳化等演算法操作
	success();
}

function upload(success, error) {
	success();
}
$(document).ready(function(){
	$("body").css('background','#0e90d2');
});
