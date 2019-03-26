<?php
/**
 * 縮略/水印圖生成
 * @package phpok
 * @作者 qinggan <admin@phpok.com>
 * @版權 2015-2016 深圳市錕鋙科技有限公司
 * @主頁 http://www.phpok.com
 * @版本 4.x
 * @授權 http://www.phpok.com/lgpl.html PHPOK開源授權協議：GNU Lesser General Public License
 * @時間 2016年10月17日
**/

class gd_lib
{
	/**
	 * 要處理的圖片地址，支援網站根目錄相對地址和絕對地址
	**/
	private $filename;

	/**
	 * 是否啟用GD
	**/
	private $isgd = true;

	/**
	 * JPG圖片質量，僅限JPG格式有效
	**/
	private $quality = 80;

	/**
	 * 圖片寬度，為0表示自適應
	**/
	private $width = 0;

	/**
	 * 圖片高度，為0表示自適應
	**/
	private $height = 0;

	/**
	 * 邊框的顏色
	**/
	private $border = "";

	/**
	 * 背景色，不要帶#號，僅支援16進位制
	**/
	private $bgcolor = "";

	/**
	 * 版權圖片，如果使用圖片版權，請在這裡設定
	**/
	private $mark = "";

	/**
	 * 版權放置的位置，預設是bottom-right，支援：top-left/top-middle/top-right/middle-left/middle-middle/middle-right/bottom-left/bottom-middle/bottom-right
	**/
	private $position = "bottom-right";

	/**
	 * 透明度，適用於水印透明度
	**/
	private $transparence = 80;
	#[可用於整個圖片要呼叫變數]

	/**
	 * 檔案路徑
	**/
	private $filepath = "";

	/**
	 * 圖片資料
	**/
	private $imginfo;

	/**
	 * 是否使用裁剪法
	**/
	private $iscut = 0;

	/**
	 * 如果水印圖檔案已經存在，是否覆蓋
	**/
	public $isrecover = true;

	public function __construct($isgd=1)
	{
		if(!$isgd || !function_exists('imagecreate')){
			$this->isgd = false;
		}
		@ini_set('memory_limit','256M');
	}

	/**
	 * 引數設定
	 * @引數 $var 變數名
	 * @引數 $val 變數值
	**/
	public function Set($var,$val="")
	{
		if($var){
			return false;
		}
		if($val != ''){
			$this->$var = $val;
		}
		return $this->$var;
	}

	/**
	 * 檔名，含路徑，支援網站根目錄相對路徑及絕對路徑
	 * @引數 $file
	 * @返回 字串
	**/
	public function filename($file='')
	{
		if($file){
			$this->filename = $file;
		}
		return $this->filename;
	}

	/**
	 * 檢查或配置gd支援
	 * @引數 $isgd 是否支援gd 為0或false表示不支援，其他支援 
	 * @返回 
	 * @更新時間 
	**/
	public function isgd($isgd=true)
	{
		if($isgd){
			$this->isgd = true;
			if(!function_exists('imagecreate')){
				$this->isgd = false;
			}
		}else{
			$this->isgd = false;
		}
		return $this->isgd;
	}

	/**
	 * 設定補白操作
	 * @引數 $bgcolor 16位顏色程式碼值，不支援#
	**/
	public function Filler($bgcolor="FFFFFF")
	{
		if(!$bgcolor){
			return $this->bgcolor;
		}
		$bgcolor = trim($bgcolor);
		if(substr($bgcolor,0,1) == '#'){
			$bgcolor = substr($bgcolor,1);
		}
		if($bgcolor && strlen($bgcolor) == 6){
			$this->bgcolor = $bgcolor;
		}
		return $this->bgcolor;
	}

	/**
	 * 設定版權
	 * @引數 $mark 版權圖片
	 * @引數 $position 版權位置
	 * @引數 $transparence 透明度
	**/
	public function CopyRight($mark="",$position="bottom-right",$transparence=80)
	{
		$this->mark = ($mark && file_exists($mark)) ? $mark : "";
		$this->position = $this->_check_position($position);
		$this->transparence = $transparence;
		return true;
	}

	#[]
	/**
	 * 設定新圖片的寬度和高度值
	 * @引數 $width 圖片寬度，為0表示自適應
	 * @引數 $height 圖片高度，為0表示自適應
	 * @返回 true/false
	**/
	public function SetWH($width=0,$height=0)
	{
		if(!$this->filename){
			return false;
		}
		$imginfo = $this->GetImgInfo($this->filename);
		if(!$width && !$height){
			$width = $imginfo["width"];
			$height = $imginfo["height"];
			$this->iscut = false;
		}elseif(!$width && $height && $imginfo['height']){
			$width =  ( $height * $imginfo["width"] ) / $imginfo["height"];
			$this->iscut = false;
		}elseif($width && !$height && $imginfo['width']){
			$height = ($width * $imginfo["height"]) / $imginfo["width"];
			$this->iscut = false;
		}
		$this->width = $width;
		$this->height = $height;
		return true;
	}

	#[]
	/**
	 * 設定是否使用裁剪法來生成縮圖
	 * @引數 $iscut 為0或false表示使用縮放法，其他值表示使用裁剪法
	 * @返回 true/false
	**/
	public function SetCut($iscut=0)
	{
		$this->iscut = $iscut;
		return $this->iscut;
	}

	/**
	 * 判斷是否寫入版權
	 * @引數 $iscopyright 為0或false表示不寫入版權，其他值表示寫入版權
	 * @返回 true/false
	**/
	public function iscopyright($iscopyright=true)
	{
		$this->iscopyright = $iscopyright ? true : false;
		return $this->iscopyright;
	}

	/**
	 * 判斷是否覆蓋新圖片
	 * @引數 $isrecover 為0或false表示不復蓋原圖，其他表示覆蓋
	 * @返回 
	 * @更新時間 
	**/
	public function isrecover($isrecover=true)
	{
		$this->isrecover = $isrecover ? true : false;
		return $this->isrecover;
	}

	/**
	 * 根據提供圖片生成新圖片
	 * @引數 $source 源圖必須含有路徑
	 * @引數 $newpic 新圖名稱
	 * @引數 $folder 新圖片自定義的地址，留空使用源圖的地址
	 * @更新時間 2019年1月20日
	**/
	public function Create($source="",$newpic="",$folder='')
	{
		if(!$this->isgd){
			return false;
		}
		if(!file_exists($source)){
			return false;
		}
		$img_info_source = $this->GetImgInfo($source);
		if(!in_array($img_info_source["ext"],array("jpg","gif","png"))){
			return false;
		}
		$this->filepath = substr($source,0,-(strlen(basename($source))));# 檔案目錄
		if($folder){
			$this->filepath = $folder;
		}
		if($newpic){
			$newpic = str_replace(array(".jpg",".gif",".png",".jpeg"),"",$newpic);
		}
		$newpic = $this->_cc_picname($newpic);
		$this->imginfo = $img_info_source;
		if(file_exists($this->filepath."/".$newpic.".".$this->imginfo["ext"]) && !$this->isrecover){
			$newpic .= "_".substr(md5(rand(0,9999)),9,16);
		}
		if($this->iscut){
			$getPicWH = $this->_cutimg();
		}else{
			$getPicWH = $this->_get_newpicWH();
		}
		if(!$getPicWH){
			return false;
		}
		$allpicheight = $this->height;
		$allpicwidth = $this->width;
		return $this->_create_img($source,$newpic,$allpicwidth,$allpicheight,$getPicWH);
	}

	/**
	 * 獲取圖片的相關資料
	 * @引數 $picture 圖片地址
	 * @返回 
	 * @更新時間 
	**/
	public function GetImgInfo($picture="")
	{
		if(!$picture || !file_exists($picture)){
			return false;
		}
		$infos = getimagesize($picture);
		$info["width"] = $infos[0];
		$info["height"] = $infos[1];
		$info["type"] = $infos[2];
		$info["ext"] = $infos[2] == 1 ? "gif" : ($infos[2] == 2 ? "jpg" : "png");
		$info["name"] = substr(basename($picture),0,strrpos(basename($picture),"."));
		return $info;
	}

	/**
	 * 判斷設定的位置是否正確
	 * @引數 $position 位置
	**/
	private function _check_position($position = '')
	{
		if(!$position){
			return "bottom-right";
		}
		$position = strtolower($position);
		$l = "top-left,top-middle,top-right,middle-left,middle-middle,middle-right,";
		$l.= "bottom-left,bottom-middle,bottom-right";
		$list = explode(",",$l);
		if(in_array($position,$list)){
			return $position;
		}else{
			return "bottom-right";
		}
	}

	/**
	 * 判斷或建立一個新的圖片名稱
	 * @引數 $name 圖片名稱，僅限字母，數字，下劃線及中劃線，其他名稱暫時不支援，並且字母統一小寫
	 * @引數 $length 名稱長度
	**/
	private function _cc_picname($name="",$length=10)
	{
		$length = intval($length);
		if($length<2){
			$length = 2;
		}
		$newname = true;
		if($name){
			$newname = false;
			$name = strtolower($name);
			$w = "abcdefghijklmnopqrstuvwxyz_0123456789-";
			$length = strlen($name);
			if($length<1){
				$newname = true;
			}else{
				for($i=0;$i<$length;$i++){
					if(strpos($w,$name[$i]) === false){
						$newname = true;
					}
				}
			}
		}
		if($newname || !$name){
			$string = md5(rand(0,9999)."-".rand(0,9999)."-".rand(0,9999));
			$name = substr($string,rand(0,(32-$length)),10);
		}
		return $name;
	}

	/**
	 * 根據已提供的資訊計算出新圖的相關引數
	**/
	private function _get_newpicWH()
	{
		$info = ($this->imginfo["width"] && $this->imginfo["height"])  ? $this->imginfo : false;
		if(!$info){
			return false;
		}
		if($this->width > $info["width"] && $this->height > $info["height"]){
			$array["width"] = $info["width"];
			$array["tempx"] = $info["width"];
			$array["tempy"] = $info["height"];
			$array["height"] = $info["height"];
		}else{
			$rate_width = $info["width"]/$this->width;
			$rate_height = $info["height"]/$this->height;
			if($rate_width>$rate_height){
				$array["width"] = $this->width;
				$array["height"] = round(($this->width*$info["height"])/$info["width"]);
			}else{
				$array["height"] = $this->height;
				$array["width"] = round(($info["width"]*$this->height)/$info["height"]);
			}
			$array["tempx"] = $this->imginfo["width"];
			$array["tempy"] = $this->imginfo["height"];
		}
		$array["srcx"] = 0;
		$array["srcy"] = 0;
		return $array;
	}

	/**
	 * 將十六進位制轉成RGB格式
	 * @引數 
	 * @返回 
	 * @更新時間 
	**/
	private function _to_rgb($color="")
	{
		if(!$color){
			return false;
		}
		if(strlen($color) != 6){
			return false;
		}
		$color = strtolower($color);
		$array["red"] = hexdec(substr($color,0,2));
		$array["green"] = hexdec(substr($color,2,2));
		$array["blue"] = hexdec(substr($color,4,2));
		return $array;
	}

	/**
	 * 建立一張圖片
	 * @引數 $source 原圖資源
	 * @引數 $newpic 新圖名稱
	 * @引數 $width 圖片寬度
	 * @引數 $height 圖片高度
	 * @引數 $getPicWH 原圖寬高
	**/
	private function _create_img($source,$newpic,$width,$height,$getpicWH)
	{
		$truecolor = function_exists("imagecreatetruecolor") ? true : false;
		$img_create = $truecolor ? "imagecreatetruecolor" : "imagecreate";
		$img = $img_create($width,$height);
		$bg = $this->_to_rgb($this->bgcolor);
		$bg["red"] = $bg["red"] ? $bg["red"] : 0;
		$bg["green"] = $bg["green"] ? $bg["green"] : 0;
		$bg["blue"] = $bg["blue"] ? $bg["blue"] : 0;
		if($this->imginfo["ext"] == 'png'){
			$bgfill = imagecolorallocatealpha($img,$bg["red"],$bg["green"],$bg["blue"],127);
		} else {
			$bgfill = imagecolorallocate($img,$bg["red"],$bg["green"],$bg["blue"]);
		}
		imagefill($img,0,0,$bgfill);
		$picX = ($width-$getpicWH["width"])/2;
		$picY = ($height-$getpicWH["height"])/2;
		$tmpImg = $this->_get_imgfrom($source);
		if(!$tmpImg){
			return false;
		}
		$img_create = $truecolor ? "imagecopyresampled" : "imagecopyresized";
		$img_create($img,$tmpImg,$picX,$picY,$getpicWH["srcx"],$getpicWH["srcy"],$getpicWH["width"],$getpicWH["height"],$getpicWH["tempx"],$getpicWH["tempy"]);
		if($truecolor){
			imagesavealpha($img,true);
		}
		if($this->mark){
			$npicImg = $this->_get_imgfrom($this->mark);
			$npicInfo = $this->GetImgInfo($this->mark);
			$getPosition = $this->_set_position($npicInfo,$width,$height);
			if($npicInfo["type"] == 3){
				imagecopy($img,$npicImg,$getPosition["x"],$getPosition["y"],0,0,$npicInfo["width"],$npicInfo["height"]);
			}else{
				imagecopymerge($img,$npicImg,$getPosition["x"],$getPosition["y"],0,0,$npicInfo["width"],$npicInfo["height"],$this->transparence);
			}
		}
		$newpicfile = $this->filepath.$newpic.".".$this->imginfo["ext"];
		if(file_exists($newpicfile)){
			@unlink($newpicfile);
		}
		$this->_write_imgto($img,$newpicfile,$this->imginfo["type"]);
		imagedestroy($tmpImg);
		imagedestroy($img);
		if($npicImg){
			imagedestroy($npicImg);
		}
		return basename($newpicfile);
	}

	/**
	 * 獲取圖片資料流資訊
	 * @引數 $pic 圖片資訊
	**/
	private function _get_imgfrom($pic)
	{
		$info = $this->GetImgInfo($pic);
		$img = "";
		if($info["type"] == 1 && function_exists("imagecreatefromgif")){
			$img = imagecreatefromgif($pic);
			ImageAlphaBlending($img,true);
		}elseif($info["type"] == 2 && function_exists("imagecreatefromjpeg")){
			$img = imagecreatefromjpeg($pic);
			ImageAlphaBlending($img,true);
		}elseif($info["type"] == 3 && function_exists("imagecreatefrompng")){
			$img = imagecreatefrompng($pic);
			ImageAlphaBlending($img,true);
		}
		return $img;
	}

	/**
	 * 寫入圖片
	 * @引數 $temp_image 圖片資源
	 * @引數 $newfile 圖片儲存路徑
	 * @引數 $info_type 圖片型別
	**/
	private function _write_imgto($temp_image,$newfile,$info_type)
	{
		if($info_type == 1){
			imagegif($temp_image,$newfile);
		}elseif($info_type == 2){
			imagejpeg($temp_image,$newfile,$this->quality);
		}elseif($info_type == 3){
			imagepng($temp_image,$newfile);
		}else{
			$newfile = $newfile.".png";
			if(file_exists($newfile)){
				unlink($newfile);
			}
			imagepng($temp_image,$newfile);
		}
	}

	/**
	 * 設定圖片的位置
	 * @引數 $npicInfo 圖片資訊
	 * @引數 $width 寬度
	 * @引數 $height 高度
	 * @返回 
	 * @更新時間 
	**/
	private function _set_position($npicInfo,$width,$height)
	{
		if(!$npicInfo) return array("x"=>0,"y"=>0);
		$x = $this->border ? 1 : 0;
		$y = $this->border ? 1 : 0;
		if($this->position == "top-left"){
			$x = $this->border ? 1 : 0;
			$y = $this->border ? 1 : 0;
		}elseif($this->position == "top-middle"){
			if($npicInfo["width"] < $width){
				$x = ($width - $npicInfo["width"])/2 - ($this->border ? 1 : 0);
			}
		}elseif($this->position == "top-right"){
			if($npicInfo["width"] < $width){
				$x = $width - $npicInfo["width"] - ($this->border ? 1 : 0);
			}
		}elseif($this->position == "middle-left"){
			if($npicInfo["height"] < $height){
				$y = ($height - $npicInfo["height"])/2 - ($this->border ? 1 : 0);
			}
		}elseif($this->position == "middle-middle"){
			if($npicInfo["height"] < $height){
				$y = ($height - $npicInfo["height"])/2 - ($this->border ? 1 : 0);
			}
			if($npicInfo["width"] < $width){
				$x = ($width - $npicInfo["width"])/2 - ($this->border ? 1 : 0);
			}
		}elseif($this->position == "middle-right"){
			if($npicInfo["height"] < $height){
				$y = ($height - $npicInfo["height"])/2 - ($this->border ? 1 : 0);
			}
			if($npicInfo["width"] < $width){
				$x = $width - $npicInfo["width"] - ($this->border ? 1 : 0);
			}
		}elseif($this->position == "bottom-left"){
			if($npicInfo["height"] < $height){
				$y = $height - $npicInfo["height"] - ($this->border ? 1 : 0);
			}
		}elseif($this->position == "bottom-middle"){
			if($npicInfo["height"] < $height){
				$y = $height - $npicInfo["height"] - ($this->border ? 1 : 0);
			}
			if($npicInfo["width"] < $width){
				$x = ($width - $npicInfo["width"])/2 - ($this->border ? 1 : 0);
			}
		}else{
			if($npicInfo["height"] < $height){
				$y = $height - $npicInfo["height"] - ($this->border ? 1 : 0);
			}
			if($npicInfo["width"] < $width){
				$x = $width - $npicInfo["width"] - ($this->border ? 1 : 0);
			}
		}
		return array("x"=>$x,"y"=>$y);
	}

	/**
	 * 使用裁剪法根據已提供的資訊計算出新圖的相關引數
	**/
	private function _cutimg()
	{
		$width = $this->width;
		$height = $this->height;
		if(!$height || !$width){
			return false;
		}
		$info_width = $this->imginfo["width"];
		$info_height = $this->imginfo["height"];
		$info["width"] = $info_width ? $info_width : 1;
		$info["height"] = $info_height ? $info_height : 1;
		$info_rate = $info["width"]/$info["height"];
		$new_rate = $width/$height;
		if($info_rate > $new_rate){
			$tempx = $info["height"] * $new_rate;
			$tempy = $info["height"];
			$srcx = ($info["width"] - $tempx) / 2;
			$srcy = 0;
		}else{
			$tempx = $info["width"];
			$tempy = $info["width"] / $new_rate;
			$srcx = 0;
			$srcy = ($info["height"] - $tempy) / 2;
		}
		$array["height"] = $this->height;
		$array["width"] = $this->width;
		$array["tempx"] = $tempx;
		$array["tempy"] = $tempy;
		$array["srcx"] = $srcx;
		$array["srcy"] = $srcy;
		return $array;
	}

	/**
	 * 後臺使用到的縮圖
	 * @引數 $filename 檔名
	 * @引數 $id 檔案ID，也是縮圖檔名
	**/
	public function thumb($filename,$id,$width=200,$height=200)
	{
		if(!$filename || !$id){
			return false;
		}
		if(!$width){
			$width = 200;
		}
		if(!$height){
			$height = 200;
		}
		$this->isgd(true);
		$this->filename($filename);
		$this->SetCut(true);
		$this->Filler("FFFFFF");
		$this->SetWH($width,$height);
		$newfile = "_".$id;
		return $this->Create($filename,$newfile);
	}

	/**
	 * 根據實際情況生成各種規格圖片
	 * @引數 $filename 檔名
	 * @引數 $fileid 檔案ID
	 * @引數 $rs GD配置資訊
	**/
	public function gd($filename,$fileid,$rs)
	{
		$this->isgd(true);
		if(!$filename || !$fileid || !$rs){
			return false;
		}
		$this->filename($filename);
		$this->Filler($rs["bgcolor"]);
		if($rs["width"] && $rs["height"] && $rs["cut_type"]){
			$this->SetCut(true);
		}else{
			$this->SetCut(false);
		}
		$this->SetWH($rs["width"],$rs["height"]);
		$this->CopyRight($rs["mark_picture"],$rs["mark_position"],$rs["trans"]);
		if($rs["quality"]){
			$this->quality = $rs["quality"];
		}
		$newfile = $rs["identifier"]."_".$fileid;
		return $this->Create($filename,$newfile);
	}
}