<?php

namespace app\index\model;

use think\Model;
use think\Db;
use think\Session;

class CompatibleModel extends Model{
    /*
     *  param-$v:整道题数组
     *  $i:题号
     *  $k:li后的id
     */
    public function text($v,$i,$k){
        $id = $v['id'];
        $title = $v['title'];
        $must = $v['must'];
        $text = '';
        $text = '<li id="li_'.$k.'" class="kuai">';
        if($must=='0'){
//            $text .= '<span >'.$i.'.'.$title.'</span><input class="Textc-input inpu demo" name="'.$id.'" type="text">';
			$text .= '<span >'.$title.'</span><div><input class="Textc-input inpu demo" name="'.$id.'" type="text"></div>';
        }else{
//            $text .= '<span >'.$i.'.'.$title.'<span class="title-imp"> * </span></span><input class="Textc-input inpu demo" name="'.$id.'" type="text">';
			$text .= '<span >'.$title.'<span class="title-imp"> * </span></span><div><input class="Textc-input inpu demo" name="'.$id.'" type="text"></div>';
        }
        $text .= '</li>';
        return $text;
    }

    public function textarea($v,$i,$k){
        $id = $v['id'];
        $title = $v['title'];
        $must = $v['must'];
        $str = '';
        $str = ' <li id="li_'.$k.'" class="kuai">';
        if($must=='0'){
            $str .= '<span >'.$title.'</span><input class="Textc-input inpu demo" name="'.$id.'" type="text"><textarea id="textarea" name="'.$id.'" rows="4"></textarea>';
        }else{
            $str .= '<div><span >'.$title.'<span class="title-imp"> * </span></span></div><textarea id="textarea" name="'.$id.'" rows="4"></textarea>';
        }
        $str .='</li>';
        return $str;
    }

    public function checkbox($v,$i,$k){
        $id = $v['id'];
        $title = $v['title'];
        $must = $v['must'];
        $str = '';
        $str = ' <li id="li_'.$k.'" class="abcd xiala ">';
        if($must=='0'){
            $str .= '<div>'.$title.'</div><div class="radio_show row ">  ';
        }else{
            $str .= '<div>'.$title.'<span class="title-imp "> * </span></div><div class="radio_show row ">  ';
        }
        if(strpos($v['option'],'@&')){
            $option = explode('@&',$v['option']);
            foreach($option as $k1=>$v1){
            	if($v1=='其他'){
					$str .= '<span class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><input type="checkbox" name="'.$id.'[other][]" value="其他"  onchange="other_chec('.$id.')"><span >&nbsp;其他&nbsp;&nbsp;</span><input type="text" id="answer_'.$id.'_other" name="'.$id.'[other][]" disabled style="border-left: 0;border-right: 0;border-top: 0;border-radius: 0;background: transparent;"></span>';
				}else{
					$str .= '<span class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><input type="checkbox" name="'.$id.'[]" value="'.$v1.'"><span >&nbsp;'.$v1.'&nbsp;&nbsp;&nbsp;&nbsp;</span></span>';
				}
            }
        }else{
             $str .= '<span class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><input type="checkbox" name="'.$id.'" value="'.$v['option'].'"><span >&nbsp;'.$v['option'].'&nbsp;&nbsp;&nbsp;&nbsp;</span>';
        }
        $str .= '</div></li>';
        return $str;
    }

    public function radio($v,$i,$k){
        $id = $v['id'];
        $title = $v['title'];
        $must = $v['must'];
        $str = '';
        $option = $v['option'];
        $str = ' <li id="li_'.$k.'" class="abcd xiala ">';
        if($must=='0'){
            $str .= '<div >'.$title.'</div><div class="radio_show row " onchange="other_rea('.$id.')"> <span><span > ';
        }else{
            $str .= '<div>'.$title.'<span class="title-imp "> * </span></div><div class="radio_show row" onchange="other_rea('.$id.')">';
        }

        if(strpos($option,'@&')){
            $option = explode('@&',$v['option']);

            if(in_array('其他',$option)){
				foreach($option as $k1=>$v1){
					if ($v1=='其他'){
						$str .= '<span "><input type="radio" name="'.$id.'[other][]" value="其他"><span >&nbsp;其他&nbsp;&nbsp;<input  id="answer_'.$id.'_other" type="text" name="'.$id.'[other][]" disabled style="border-left: 0;border-right: 0;border-top: 0;border-radius: 0;background: transparent;"></span></span>';
					}else{
						$str .= '<span class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><input type="radio" name="'.$id.'[other][]" value="'.$v1.'"><span >&nbsp;'.$v1.'&nbsp;&nbsp;&nbsp;&nbsp;</span></span>';
					}
				}
			}else{
				foreach($option as $k1=>$v1) {
					$str .= '<span class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><input type="radio" name="' . $id . '[]" value="' . $v1 . '"><span >&nbsp;' . $v1 . '&nbsp;&nbsp;&nbsp;&nbsp;</span></span>';
				}
			}
//            foreach($option as $k1=>$v1){
//            	if ($v1=='其他'){
//					$str .= '<span "><input type="radio" name="'.$id.'[other][]" value="其他"><span >&nbsp;其他<input type="text" name="'.$id.'[other][]">&nbsp;&nbsp;&nbsp;&nbsp;</span></span>';
//				}else{
//					$str .= '<span class="col-xs-4 col-sm-4 col-md-2 col-lg-2"><input type="radio" name="'.$id.'[]" value="'.$v1.'"><span >&nbsp;'.$v1.'&nbsp;&nbsp;&nbsp;&nbsp;</span></span>';
//				}
//            }
        }else{
            $str .= '<span class="col-xs-4 col-sm-4 col-md-2 col-lg-2"><input type="radio" name="'.$id.'" value="'.$option.'"><span >&nbsp;'.$option.'&nbsp;&nbsp;&nbsp;&nbsp;</span></span>';
        }
        $str .= '</div></li>';
        return $str;
    }

    public function select($v,$i,$k){
        $id = $v['id'];
        $str = '';
        $option = $v['option'];
        $str = ' <li id="li_'.$k.'" class="kuai">';
        if($v['must']=='0'){
            $str .= '<span >'.$v['title'].'</span><span><br/><select class="inpu bg_white" ><option>—请选择—</option>';
        }else{
            $str .= '<span>'.$v['title'].'<span class="title-imp "> * </span></span><span><br/><select class="inpu bg_white"  name="'.$id.'[]"><option>—请选择—</option>';
        }
        if(strpos($option,'@&')){
            $option = explode('@&',$v['option']);
            foreach($option as $k1=>$v1){
                $str .= '<option  value="'.$v1.'">'.$v1.'</option>';
            }
        }else{
            $str .= '<option  value="'.$option.'">'.$option.'</option>';
        }
        $str .= '</select></span></li>';
        return $str;
    }

    public function date($v,$i,$k){
		$id = $v['id'];
        $str = '';
		$date_type = '年-月-日,例:2017-01-01';
        $str = ' <li id="li_'.$k.'" class="kuai">';
        if($v['must']=='0'  && $v['range'] =='~'){
            $str .= '<span >'.$v['title'].'</span><br/>';
            $str .= '<span><input id="answer_'.$v['id'].'" type="text " name="'.$v['id'].'" placeholder="请选择日期 " class="inpu date_Date date_Date'.$v['id'].'" ></span>';
        }else if($v['must']=='0'  && $v['range'] !=='~'){
            $str .= '<span >'.$v['title'].'<span>填写范围:'.$v['range'].'</span></span><br/>';
            $str .= '<span><input id="answer_'.$v['id'].'" type="text " name="'.$v['id'].'" placeholder="请选择日期  " class="inpu date_Date date_Date'.$v['id'].'" ></span>';
        }else if($v['must']=='1'  && $v['range'] =='~'){
            $str .= '<span >'.$v['title'].'<span class="title-imp "> * </span></span><br/>';
            $str .= '<span><input id="answer_'.$v['id'].'" type="text " name="'.$v['id'].'" placeholder="请选择日期  " class="inpu date_Date  date_Date'.$v['id'].'" ></span>';
        }else if($v['must']=='1'  && $v['range'] !=='~'){
            $str .= '<span >'.$v['title'].'<span class="title-imp "> * </span></span><br/>';
            $str .= '<span><input id="answer_'.$v['id'].'" type="text " name="'.$v['id'].'" placeholder="请选择日期 " class="inpu date_Date date_Date'.$v['id'].'" ></span>';
        }
        return $str;
    }

    public function number($v,$i,$k){
        $str = '';
        $str = ' <li id="li_'.$k.'" class="kuai">';
        if($v['must']=='0'  && $v['range'] ==''){
            if($v['range'])
            $str .= '<span >'.$v['title'].'</span><input type="text " class="f_name Textc-input inpu " name="'.$v['id'].'" onkeyup="(this.v=function(){this.value=this.value.replace(/[^0-9-]+/,\'\');}).call(this)" onblur="this.v();"  value="">';
            $str .= '<div style="position:relative; "><input type="text "  class="inpu Num_kg "><div class="kgs">'.$v['remark'].'</div></div>';
        }else if($v['must']=='0'  && $v['range'] !=='~'){
            $str .= '<span >'.$v['title'].'<span>填写范围:'.$v['range'].'</span></span>';
            $str .= '<div style="position:relative; "><input type="text "  name="'.$v['id'].'" class="inpu Num_kg " onkeyup="(this.v=function(){this.value=this.value.replace(/[^0-9-]+/,\'\');}).call(this)" onblur="this.v();" ><div class="kgs ">'.$v['remark'].'</div></div>';
        }else if($v['must']=='1'  && $v['range'] =='~'){
            $str .= '<span >'.$v['title'].'<span class="title-imp "> * </span></span>';
            $str .= '<div style="position:relative; "><input type="text "  name="'.$v['id'].'" class="inpu Num_kg " onkeyup="(this.v=function(){this.value=this.value.replace(/[^0-9-]+/,\'\');}).call(this)" onblur="this.v();" ><div class="kgs ">'.$v['remark'].'</div></div>';
        }else if($v['must']=='1'  && $v['range'] !=='~'){
            $str .= '<span >'.$v['title'].'<span class="title-imp "> * </span><span>填写范围:'.$v['range'].'</span></span>';
            $str .= '<div style="position:relative; "><input type="text "  name="'.$v['id'].'" class="inpu Num_kg " onkeyup="(this.v=function(){this.value=this.value.replace(/[^0-9-]+/,\'\');}).call(this)" onblur="this.v();" ><div class="kgs ">'.$v['remark'].'</div></div>';
        }
        $str .= '</span></li>';
        return $str;
    }

    public function addre($v,$i,$k){
        $str = '';
        $str = '<li id="li_'.$k.' " class="kuai ">';
        if($v['must']=='0'){
            $str .= '<span > '.$v['title'].'</span><div class="addre-information-input outers">';
            $str .= '<select id="province_'.$v['id'].'" name="'.$v['id'].'[]" class="sel bg_white" style="width:10%;" onchange="ajax_guding('."'province_".$v['id']."'".','."'city_".$v['id']."'".')"><option value="请选择 ">请选择</option><option value="1 ">大兴区</option></select>';
            $str .= '<select id="city_'.$v['id'].' "  name="'.$v['id'].'[]" class="sel zhen bg_white" style="width:10%;" onchange="ajax_guding('."'city_".$v['id']."'".','."'town_".$v['id']."'".')"><option value="请选择 ">请选择</option></select>';
            $str .= '<select id="town_'.$v['id'].' " name="'.$v['id'].'[]" class="sel cun bg_white" style="width:15%;"><option value="请选择 ">请选择</option></select>';
            $str .= '<input type="text " id="adress_mes " name="'.$v['id'].'[]" class="sel bg_white" style="width:30%;" placeholder="请输入详细地址 "></div></li>';
        }else{
            $str .= '<span> '.$v['title'].'<span class="title-imp "> * </span></span><div class="addre-information-input outers">';
            $str .= '<select id="province_'.$v['id'].'" class="sel bg_white" name="'.$v['id'].'[]" style="width:10%;" onchange="ajax_guding('."'province_".$v['id']."'".','."'city_".$v['id']."'".')"> <option value="请选择">请选择</option><option value="1">大兴区</option> </select>';
            $str .= '<select id="city_'.$v['id'].'" class="sel zhen bg_white" name="'.$v['id'].'[]" style="width:10%;" onchange="ajax_guding('."'city_".$v['id']."'".','."'town_".$v['id']."'".')"><option value="请选择">请选择</option> </select>';
            $str .= '<select id="town_'.$v['id'].'" class="sel cun bg_white"  name="'.$v['id'].'[]" style="width:15%;"> <option value="请选择">请选择</option> </select>';
            $str .= '<input type="text" id="adress_mes " name="'.$v['id'].'[]" class="sel bg_white" style="width:30%;" placeholder="请输入详细地址" style="width:150px;text-align:left;"><br></div>';
        }

        return $str;
    }

    public function score($v,$i,$k){

        $str ='';
        $str = '<li id="li_'.$k.' " class="kuai ">';
        if($v['must']=='0'){
			$str .= '<div> '.$v['title'].'</div><div class="starability-container">';
            $str .= '<div class="demo">';
            $str .= '<div id="function-demo'.$k.'" class="target-demo"></div><input type="hidden" id="function-hint'.$k.'" class="hint" value=""></div>';
        }else{
            $str .= '<div> '.$v['title'].'<span class="title-imp "> * </span></div><div class="starability-container">';
			$str .= '<div class="demo">';
			$str .= '<div id="function-demo'.$k.'" class="target-demo"></div><input type="hidden" id="function-hint'.$k.'" class="hint" name="'.$v['id'].'" value=""></div>';
        }
        $str .="<script>$('#function-demo".$k."').raty({number: 5,score: 0,argetType: 'number',path: '',cancelOff: '/static/imges/cancel-off-big.png',";
        $str .= "size: 24,starHalf: '/static/imges/star-half-big.png',starOff: '/static/imges/star-off-big.png',starOn: '/static/imges/star-on-big.png',";
        $str .= "target: '#function-hint".$k."',cancel: false,targetKeep: true,precision: false,});";

        $str .= '</script></li>';
        return $str;
    }

    public function tizu($v,$k){
       $str = '<li id="li_'.$k.'" class="kuai"><span class="timu">'.$v['title'].'</span>';
        return $str;
    }
    public function lianxi($v,$i,$k){
        $str = '';
        $str = '<li id="li_'.$k.' " class="kuai "><div class="'.$v['type'].' row ">';
        if($v['must']=='0'){
			if($v['type']=='phonenumber'){
				$str .= '<label class="col-xs-12 col-sm-12 col-md-12 col-lg-12 ">'.$v['title'].'：</label><input name="'.$v['type'].'" class="lianximes col-xs-12 col-md-6 bg_white"  style="background-color:white;" type="text " onblur="tel('.$v['id'].')" onchange="tt('.$v['id'].')">';
			}elseif($v['type']=='email'){
				$str .= '<label class="col-xs-12 col-sm-12 col-md-12 col-lg-12 ">'.$v['title'].'：</label><input name="'.$v['type'].'" class="lianximes col-xs-12 col-md-6 bg_white"  style="background-color:white;" type="text " onblur="email_check('.$v['id'].')" onchange="tt('.$v['id'].')">';
			}else{
				$str .= '<label class="col-xs-12 col-sm-12 col-md-12 col-lg-12 ">'.$v['title'].'：</label><input name="'.$v['type'].'" class="lianximes col-xs-12 col-md-6 bg_white"  style="background-color:white;" type="text "  onchange="tt('.$v['id'].')">';
			}
        }else{
			if($v['type']=='phonenumber'){
				$str .= '<label class="col-xs-12 col-sm-12 col-md-12 col-lg-12 ">'.$v['title'].'<span class="title-imp ">*</span>：</label><input class="lianximes col-xs-12 col-md-6 bg_white"  name="'.$v['type'].'"type="text " id="answer_'.$v['id'].'" onblur="tel('.$v['id'].')" onchange="tt('.$v['id'].')">';
			}elseif($v['type']=='email'){
				$str .= '<label class="col-xs-12 col-sm-12 col-md-12 col-lg-12 ">'.$v['title'].'<span class="title-imp ">*</span>：</label><input class="lianximes col-xs-12 col-md-6 bg_white"  name="'.$v['type'].'"type="text " id="answer_'.$v['id'].'" onblur="email_check('.$v['id'].')" onchange="tt('.$v['id'].')">';
			}else{
				$str .= '<label class="col-xs-12 col-sm-12 col-md-12 col-lg-12 ">'.$v['title'].'<span class="title-imp ">*</span>：</label><input class="lianximes col-xs-12 col-md-6 bg_white"  name="'.$v['type'].'"type="text " id="answer_'.$v['id'].'"  onchange="tt('.$v['id'].')">';
			}
        }
        $str .= '</div></li>';
        return $str;
    }

    public function birth($v,$i,$k){
        $str = '';
        $str = '<li id="li_'.$k.' " class="kuai"><div class="row">';
        if($v['must']=='0'){
            $str .= '<label class="col-xs-12 col-sm-12 col-md-12 col-lg-12 ">'.$v['title'].'：</label><input id="answer_'.$v['id'].'" class="col-xs-12 col-md-6 birth date_Date bg_white date_Date'.$v['id'].'" type="text " id="answer_'.$v['id'].'" ><input type="hidden" name="'.$v['id'].'" id="h_'.$v['id'].'">';
            $str .= '</div></li>';
        }else {
            $str .= '<label class="col-xs-12 col-sm-12 col-md-12 col-lg-12 ">'.$v['title'].' <span class="title-imp ">*</span>：</label><input id="answer_'.$v['id'].'" name="'.$v['type'].'"  class="col-xs-12 col-md-6 birth bg_white date_Date date_Date'.$v['id'].'" type="text "><input type="hidden" name="'.$v['id'].'" id="h_'.$v['id'].'">';
            $str .= '';
        }
        $str .= '</div></li>';
        return $str;
    }

    public function sex($v,$i,$k){

        $str = '';
        $option = explode('@&',$v['option']);

        $str = '<li id="li_'.$k.' " class="kuai"><div class="sex row " style="border:0 ">';
        if($v['must']=='0'){
            $str .= '<label class="col-xs-12 col-sm-12 col-md-12 col-lg-12 ">性别：</label>';
            for($a=0;$a<count($option);$a++){
                $str .= '<span class="col-xs-12 col-sm-12 col-md-12 col-lg-12 "><input name="'.$v['type'].'" type="radio" value="'.$option[$a].'" id="sex_'.$a.'"  onclick="tt1('.$a.','.$v['id'].')"/><span>'.$option[$a].'</span> &nbsp;</span>';
            }

//            $str .= '<span class="col-xs-4 col-sm-4 col-md-2 col-lg-2 "><input name="'.$v['type'].'" type="radio" value="'.$option[$a].'"  onclick="tt1('.$i.','.$v['id'].')"/><span>'.$option[$a].'</span> &nbsp;</span>';
//            $str .= '<span class="col-xs-4 col-sm-4 col-md-2 col-lg-2 "><input name="'.$v['type'].'" type="radio" value="女"  onclick="tt1('.$i.','.$v['id'].')"/><span>女</span> &nbsp;</span>';
//            $str .= '<span class="col-xs-4 col-sm-4 col-md-2 col-lg-2 "><input name="'.$v['type'].'" type="radio" value="保密" /><span>保密</span></span>';
        }else{
            $str .= '<label class="col-xs-12 col-sm-12 col-md-12 col-lg-12 ">性别<span class="title-imp ">*</span>：</label>';
            for($a=0;$a<count($option);$a++){
                $str .= '<span class="col-xs-12 col-sm-12 col-md-12 col-lg-12 "><input name="'.$v['type'].'" type="radio" value="'.$option[$a].'" id="sex_'.$v['id'].'"  onclick="tt1('.$a.','.$v['id'].')"/><span>'.$option[$a].'</span> &nbsp;</span>';
            }
//            $str .= '<span class="col-xs-4 col-sm-4 col-md-2 col-lg-2 "><input name="'.$v['type'].'" type="radio" value="男" /><span>男</span> &nbsp;</span>';
//            $str .= '<span class="col-xs-4 col-sm-4 col-md-2 col-lg-2 "><input name="'.$v['type'].'" type="radio" value="女" /><span>女</span> &nbsp;</span>';
//            $str .= '<span class="col-xs-4 col-sm-4 col-md-2 col-lg-2 "><input name="'.$v['type'].'" type="radio" value="保密" /><span>保密</span></span>';
        }
        $str .= '</div></li>';
        return $str;
    }
}