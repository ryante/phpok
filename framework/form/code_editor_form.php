<?php
/*****************************************************************************************
	檔案： {phpok}/form/code_editor_form.php
	備註： 程式碼編輯框
	版本： 4.x
	網站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	時間： 2015年03月12日 22時03分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class code_editor_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function phpok_config()
	{
		$this->view($this->dir_phpok.'form/html/code_admin.html',"abs-file");
	}

	public function phpok_format($rs,$appid="admin")
	{
		$this->addjs('js/codemirror/codemirror.js');
		$this->addcss('js/codemirror/codemirror.css');
		$this->assign("_rs",$rs);
		return $this->fetch($this->dir_phpok.'form/html/code_admin_tpl.html','abs-file');
	}

	public function phpok_get($rs,$appid="admin")
	{
		return $this->get($rs['identifier'],'html_js');
	}

	public function phpok_show($rs,$appid="admin")
	{
		return $rs['content'];
	}
}
?>