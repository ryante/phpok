<?php
/***********************************************************
	Filename: {phpok}/api/opt_control.php
	Note	: OPT選項功能前後臺資料讀取
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月21日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class opt_control extends phpok_control
{
	private $symbol = '|';
	public function __construct()
	{
		parent::control();
	}

	//獲取
	public function index_f()
	{
		$val = $this->get("val");
		$group_id = $this->get("group_id",'int');
		if(!$group_id){
			exit(P_Lang('沒有指定選項組'));
		}
		$group_rs = $this->model('opt')->group_one($group_id);
		if($group_rs && $group_rs['link_symbol']){
			$this->symbol = $group_rs['link_symbol'];
		}
		$identifier = $this->get("identifier");
		if(!$identifier){
			exit(P_Lang('未定義變數'));
		}
		$rootlist = $this->model('opt')->opt_all('group_id='.$group_id.' AND parent_id=0');
		if(!$rootlist){
			exit(P_Lang('沒有內容選項'));
		}
		if(!$val){
			$html  = '<ul class="select"><li>';
			$html .= $this->_html_select($rootlist,$identifier,$group_id,true);
			$html .= '</li></ul>';
			exit($html);
		}
		$list = explode($this->symbol,$val);
		$count = count($list);
		$htmlist = array();
		$parent_id = 0;
		for($i=0;$i<=$count;$i++){
			$tmplist = $this->model('opt')->opt_all('group_id='.$group_id.' AND parent_id='.$parent_id);
			if($tmplist){
				$first = array();
				for($m=0;$m<$i;$m++){
					$first[] = $list[$m];
				}
				$first = implode($this->symbol,$first);
				//檢測是否有子項
				$sub = false;
				foreach($tmplist as $key=>$value){
					if($value['val'] == $list[$i]){
						$sub = $value['id'];
						break;
					}
				}
				if($sub){
					$parent_id = $sub;
					$tmplist2 = $this->model('opt')->opt_all('group_id='.$group_id.' AND parent_id='.$sub);
					if($tmplist2){
						$sub = true;
					}else{
						$sub = false;
					}
				}
				if($sub){
					$htmlist[] = $this->_html_select($tmplist,$identifier,$group_id,$list[$i],$first,false);
				}else{
					$htmlist[] = $this->_html_select($tmplist,$identifier,$group_id,$list[$i],$first,true);
				}
			}
		}
		if(count($htmlist) == 1){
			$htmlist[0] = $this->_html_select($rootlist,$identifier,$group_id,$list[0],true);
		}
		$html  = '<ul class="select">';
		foreach($htmlist as $key=>$value){
			$html .= '<li>'.$value.'</li>';
		}
		$html .= '</ul>';
		exit($html);
	}

	private function _html_select($rslist,$identifier,$group_id,$selected='',$first='',$in_name=false)
	{
		if(is_bool($selected)){
			$in_name = $selected;
			$selected = '';
		}
		if(is_bool($first)){
			$in_name = $first;
			$first = '';
		}
		$html  = '<select class="select form_select form_select_'.$identifier.'" ';
		if($in_name){
			$html .= 'name="'.$identifier.'" id="'.$identifier.'" ';
		}
		$html .= 'onchange="$.phpok_form_select.change('.$group_id.',\''.$identifier.'\',this.value)">';
		$html .= '<option value="'.$first.'">'.P_Lang('請選擇…').'</option>';
		foreach($rslist as $key=>$value){
			$tmp = $first ?  $first.$this->symbol.$value['val'] : $value['val'];
			$html .= '<option value="'.$tmp.'"';
			if($selected && $selected == $value['val']){
				$html .= ' selected';
			}
			$html .= '>'.$value["title"]."</option>";
		}
		$html .= "</select>";
		return $html;
	}

	private function _check_sonlist($parent_id,$group_id)
	{
		$son = false;
		foreach($rslist as $key=>$value){
			if($value['parent_id'] == $parent_id){
				$son = true;
				break;
			}
		}
		return $son;
	}

	private function ajax_admin_opt_tmp_list(&$tmp_array,$list,$pid)
	{
		if($pid){
			$tmp_all = $list[$pid];
			$tmp_array[] = $tmp_all["val"];
			$this->ajax_admin_opt_tmp_list($tmp_array,$list,$tmp_all["parent_id"]);
		}
	}

}
?>