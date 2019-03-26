<?php
/*****************************************************************************************
	檔案： gateway/payment/offlinepay/submit.php
	備註： 線下付款方案
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2016年06月02日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class offlinepay_submit
{
	private $order;
	private $param;
	private $obj;
	public function __construct($order,$param)
	{
		$this->param = $param;
		$this->order = $order;
		$this->paydir = $GLOBALS['app']->dir_root.'gateway/payment/offlinepay/';
		$this->baseurl = $GLOBALS['app']->url;
	}

	public function submit()
	{
		global $app;
		if(!$this->param['param'] || !$this->param['param']['tplfile']){
			error(P_Lang('未指定模板檔案'),'','error');
		}
		$app->assign('order',$this->order);
		$app->assign('payment',$this->param);
		$app->view($this->param['param']['tplfile']);
	}
}