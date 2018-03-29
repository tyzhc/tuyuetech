<?php

namespace app\index\model;

use think\Model;
use think\Db;
use think\Session;
class questionModel extends Model{

    public function test(){
        echo 'test';
    }

//题型入库
    //矩阵填空入题库
    public function addfill($v){
        $tiku['con'] = $v['con'];
        $tiku['uid'] = Session::get('loginData')['id'];
        $tiku['tzid'] = '1';
        $tiku['type'] = $v['type'];
        $tiku['wid'] = '1';

        if(count($v['title'])>1){
            $tiku['title'] = implode('*',$v['title']);
        }
        // dump($tiku);
        //题库表插入操作
        $res = Db::table('question_database')->insertGetId($tiku);
        return $res;
    }

    //单选多选入题库
    public function addradio($v,$wid ){

        $tiku['title'] = $v['title'];
        $tiku['type'] = $v['type'];
        $tiku['uid'] = Session::get('loginData')['id'];
        $tiku['tzid'] = $wid;
        $tiku['wid'] = $wid;
        if(isset($v['value'])){

		}else{
			$v['value'][] = '选项';
		}
        $tiku['option'] = implode('@&',$v['value']);

        if(array_key_exists('must', $v)){
            $tiku['must'] = 1;
        }else{
            $tiku['must'] = '';
        }
        if(array_key_exists("remark",$v)){
            $tiku['remark'] = $v['remark'];
        }else{
            $tiku['remark'] = '';
        }
        if(array_key_exists("range_start",$v) && array_key_exists("range_end",$v) ){
            $tiku['range'] = $v['range_start'].'-'.$v['range_end'];
        }else{
            $tiku['range'] = '';
        }
        return $tiku;
//        $res = Db::table('question_database')->insertGetId($tiku);
//          return $res;
    }

    //文本问题入题库
    public function addtext($v,$wid){
        $tiku['title'] = $v['title'];
        $tiku['type'] = $v['type'];
        $tiku['uid'] = Session::get('loginData')['id'];
        $tiku['tzid'] = $wid;
        $tiku['wid'] = $wid;
        $tiku['option'] = '';
        if(array_key_exists('must', $v)){
            $tiku['must'] = 1;
        }else{
            $tiku['must'] = '';
        }
        if(array_key_exists("remark",$v)){
            $tiku['remark'] = $v['remark'];
        }else{
            $tiku['remark'] = '';
        }
        if(array_key_exists("range_start",$v) && array_key_exists("range_end",$v) ){
            $tiku['range'] = $v['range_start'].'-'.$v['range_end'];
        }else{
            $tiku['range'] = '';
        }

        return $tiku;
//        $res = Db::table('question_database')->insertGetId($tiku);
//        return $res;
    }

    //复合型问题入题库
    public function addmixed($v){
//        dump($v);
        $tiku['type'] = $v['type'];
        $tiku['title'] = $v['title'];
        $tiku['con'] = $v['con'];
        $tiku['uid'] = '2';
        $tiku['tzid'] = '1';
        $tiku['wid'] = '1';
//        dump($tiku);

        $id = Db::table('question_database')->insertGetId($tiku);
        foreach($v as $key=>$val){
            if(is_array($val)){
                if($val['type']=='文本'){
                    $tiku = $val;
                    $tiku['uid'] = '2';
                    $tiku['tzid'] = '1';
                    $tiku['wid'] = '1';
                    $tiku['pid'] = $id;
                    Db::table('question_database')->insertGetId($tiku);

                }elseif($val['type']=='单选'|| $val['type'] == '多选' || $val['type'] == '下拉选择' ){

//                   $tiku['con'] = $val['con'];
                    $tiku['uid'] = '2';
                    $tiku['tzid'] = '1';
                    $tiku['wid'] = '1';
                    $tiku['type'] = $val['type'];
                    $tiku['title'] = $val['title'];
                    $tiku['pid'] = $id;
                    $tiku['option'] = implode('@&',$val['value']);
//                   dump($tiku);
                    Db::table('question_database')->insertGetId($tiku);
                }
            }
        }
    }

    //联系人组件中文本框入库
    public function addlianxi($v,$wid){
        $tiku['title'] = $v['title'];
        $tiku['type'] = $v['type'];
        $tiku['uid'] = Session::get('loginData')['id'];
        $tiku['tzid'] = $wid;
        $tiku['wid'] = $wid;
        if(in_array("option",$v)){
            $tiku['option'] = $v['option'];
        }else{
            $tiku['option'] = '';
        }
        if(array_key_exists('must', $v)){
            $tiku['must'] =1;
        }else{
            $tiku['must'] = '';
        }
        if(array_key_exists("remark",$v)){
            $tiku['remark'] = $v['remark'];
        }else{
            $tiku['remark'] = '';
        }
        if(array_key_exists("range_start",$v) && array_key_exists("range_end",$v) ){
            $tiku['range'] = $v['range_start'].'-'.$v['range_end'];
        }else{
            $tiku['range'] = '';
        }
        return $tiku;
    }

    //联系人组件中性别组件
    public function addsex($v,$wid){
        $tiku['title'] = $v['title'];
        $tiku['type'] = $v['type'];
        $tiku['uid'] = Session::get('loginData')['id'];
        $tiku['tzid'] = $wid;
        $tiku['wid'] = $wid;
        $tiku['option'] = implode('@&',$v['option']);
//        dump($tiku);
        if(array_key_exists('must', $v)){
            $tiku['must'] = 1;
        }else{
            $tiku['must'] = '';
        }
        if(array_key_exists("remark",$v)){
            $tiku['remark'] = $v['remark'];
        }else{
            $tiku['remark'] = '';
        }
        if(array_key_exists("range_start",$v) && array_key_exists("range_end",$v) ){
            $tiku['range'] = $v['range_start'].'-'.$v['range_end'];
        }else{
            $tiku['range'] = '';
        }
        return $tiku;
    }

    //题组
    public function addtizu($v,$wid){
        $tiku['title'] = $v['title'];
        $tiku['type'] = $v['type'];
        $tiku['uid'] = Session::get('loginData')['id'];
        $tiku['tzid'] = $wid;
        $tiku['wid'] = $wid;
        if(in_array("option",$v)){
            $tiku['option'] = $v['option'];
        }else{
            $tiku['option'] = '';
        }
        if(array_key_exists('must', $v)){
            $tiku['must'] = 1;
        }else{
            $tiku['must'] = '';
        }
        if(array_key_exists("remark",$v)){
            $tiku['remark'] = $v['remark'];
        }else{
            $tiku['remark'] = '';
        }
        if(array_key_exists("range_start",$v) && array_key_exists("range_end",$v) ){
            $tiku['range'] = $v['range_start'].'-'.$v['range_end'];
        }else{
            $tiku['range'] = '';
        }
        return $tiku;
    }

    //数字
    public function addnumber($v,$wid){
        $tiku['title'] = $v['title'];
        $tiku['type'] = $v['type'];
        $tiku['uid'] = Session::get('loginData')['id'];
        $tiku['tzid'] = $wid;
        $tiku['wid'] = $wid;
        if(in_array("option",$v)){
            $tiku['option'] = $v['option'];
        }else{
            $tiku['option'] = '';
        }
        if(array_key_exists('must', $v)){
            $tiku['must'] = 1;
        }else{
            $tiku['must'] = '';
        }
        if(array_key_exists("remark",$v)){
            $tiku['remark'] = $v['remark'];
        }else{
            $tiku['remark'] = '';
        }
        if(array_key_exists("range_start",$v) && array_key_exists("range_end",$v) ){
            $tiku['range'] = $v['range_start'].'~'.$v['range_end'];
        }else{
            $tiku['range'] = '';
        }
        return $tiku;
    }

    //地址
    public function add_addre($v,$wid){
        $tiku['title'] = $v['title'];
        $tiku['type'] = $v['type'];
        $tiku['uid'] = Session::get('loginData')['id'];
        $tiku['tzid'] = $wid;
        $tiku['wid'] = $wid;
        if(in_array("option",$v)){
            $tiku['option'] = $v['option'];
        }else{
            $tiku['option'] = '';
        }
        if(array_key_exists('must', $v)){
            $tiku['must'] = 1;
        }else{
            $tiku['must'] = '';
        }
        if(array_key_exists("remark",$v)){
            $tiku['remark'] = $v['remark'];
        }else{
            $tiku['remark'] = '';
        }
        if(array_key_exists("range_start",$v) && array_key_exists("range_end",$v) ){
            $tiku['range'] = $v['range_start'].'-'.$v['range_end'];
        }else{
            $tiku['range'] = '';
        }
//        dump($tiku);
        return $tiku;
    }

//    附件上传
    public function addfujian($v,$wid){
        dump($v);
        $tiku['title'] = $v['title'];
        $tiku['type'] = $v['type'];
        $tiku['uid'] = Session::get('loginData')['id'];
        $tiku['tzid'] = $wid;
        $tiku['wid'] = $wid;
        if(in_array("option",$v)){
            $tiku['option'] = $v['option'];
        }else{
            $tiku['option'] = '';
        }
        if(array_key_exists('must', $v)){
            $tiku['must'] = 1;
        }else{
            $tiku['must'] = '';
        }
        if(array_key_exists("remark",$v)){
            $tiku['remark'] = $v['remark'];
        }else{
            $tiku['remark'] = '';
        }
        if(array_key_exists("range_start",$v) && array_key_exists("range_end",$v) ){
            $tiku['range'] = $v['range_start'].'-'.$v['range_end'];
        }else{
            $tiku['range'] = '';
        }
        return $tiku;
    }

    //时间日期
    public function adddate($v,$wid){

        $tiku['title'] = $v['title'];
        $tiku['type'] = $v['type'];
        $tiku['uid'] = Session::get('loginData')['id'];
        $tiku['tzid'] = $wid;
        $tiku['wid'] = $wid;
        if(in_array("option",$v)){
            $tiku['option'] = $v['option'];
        }else{
            $tiku['option'] = '';
        }
        if(array_key_exists('must', $v)){
            $tiku['must'] = 1;
        }else{
            $tiku['must'] = '';
        }
        if(array_key_exists("remark",$v)){
            $tiku['remark'] = $v['remark'];
        }else{
            $tiku['remark'] = '';
        }
        if(array_key_exists("range_start",$v) && array_key_exists("range_end",$v) ){

            $tiku['range'] = $v['range_start'].'~'.$v['range_end'];
//            dump($tiku);
        }else{
            $tiku['range'] = '';
        }
        return $tiku;
    }

    //图片上传
    public function addpic($v,$wid){
        $tiku['title'] = $v['title'];
        $tiku['type'] = $v['type'];
        $tiku['uid'] = Session::get('loginData')['id'];
        $tiku['tzid'] = $wid;
        $tiku['wid'] = $wid;
        if(in_array("option",$v)){
            $tiku['option'] = $v['option'];
        }else{
            $tiku['option'] = '';
        }
        if(array_key_exists('must', $v)){
            $tiku['must'] = 1;
        }else{
            $tiku['must'] = '';
        }
        if(array_key_exists("remark",$v)){
            $tiku['remark'] = $v['remark'];
        }else{
            $tiku['remark'] = '';
        }
        if(array_key_exists("range_start",$v) && array_key_exists("range_end",$v) ){

            $tiku['range'] = $v['range_start'].'~'.$v['range_end'];
//            dump($tiku);
        }else{
            $tiku['range'] = '';
        }
        return $tiku;
    }



//题型预览处理

    //文本的显示数据处理
    public function text($v,$k){
//        dump($v);
        $id = $v['id'];
        $text = '';
        $must = $v['must'];
        if($must=='0'){
            $text .="<br/>".$k.'.&nbsp;'.$v['title']."<br/><input type='text' class='Textc-input inpu  ' name='".$v['id']."'><br/><br/>";
        }else if($must=='1'){
            $text .="<br/>".$k.'.&nbsp;'.$v['title']."<span class='title-imp' > * </span><br/><span class='title-imp span_$k' style='display:none'> 此问题必须填写 </span><br/><input type='text' id='answer_$id' attr-id='$k' class='Textc-input inpu nonull' name='".$v['id']."' onblur='must_nonull($id,$k)'><br/><br/>";
        }
        return $text;
    }

    //单选的显示数据处理
    public function  radio($v,$k){
        $id = $v['id'];
        $must = $v['must'];

        $radio='';
        if($must == '0'){
            $radio.="<br/>".$k.'.&nbsp;'.$v['title']."<br>";
            $option = explode('@&',$v['option']);
            if(in_array('other',$option)){
                foreach($option as $key=>$val){
                    if($key=='0'){
                        $radio .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' name='".$v['id']."[]'  value='".$val."' ><span>".$val."</span><br>";
                    }elseif($val == 'other'){
                        $radio .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' name='".$v['id']."[]' value='".$val."'><span>其他</span> <input type='text' class='del' name='".$v['id']."[]'><br>";
                    }else{
                        $radio .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' name='".$v['id']."[]' value='".$val."'><span>".$val."</span><br>";
                    }
                }
            }else{
                foreach($option as $key=>$val){
                    if($key=='0'){
                        $radio .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' name='".$v['id']."'  value='".$val."' ><span>".$val."</span><br>";
                    }elseif($val == 'other'){
                        $radio .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' name='".$v['id']."' value='".$val."'><span>其他</span> <input type='text' class='del'><br>";
                    }else{
                        $radio .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' name='".$v['id']."' value='".$val."'><span>".$val."</span><br>";
                    }
                }
            }

        }else if($must=='1'){
            $radio.="&nbsp;&nbsp;".$k.'.&nbsp;'.$v['title']."<span class='title-imp'> * </span><br/><br>";
            $option = explode('@&',$v['option']);
            if(in_array('other',$option)){
                foreach($option as $key=>$val){
                    if($key=='0'){
                        $radio .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' class='nonull' name='".$v['id']."[]'  value='".$val."'><span>".$val."</span><br>";
                    }elseif($val == 'other'){
                        $radio .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' class='nonull' name='".$v['id']."[]' value='".$val."'><span>其他</span><input type='text' name='".$v['id']."[]' ><br>";
                    }else{
                        $radio .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' class='nonull' name='".$v['id']."[]' value='".$val."'><span>".$val."</span><br>";
                    }
                }
            }else{
                foreach($option as $key=>$val){
                    if($key=='0'){
                        $radio .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' class='nonull' name='".$v['id']."'  value='".$val."'><span>".$val."</span><br>";
                    }elseif($val == 'other'){
                        $radio .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' class='nonull' name='".$v['id']."' value='".$val."'><span>其他</span><input type='text' name='".$v['id']."' ><br>";
                    }else{
                        $radio .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' class='nonull' name='".$v['id']."' value='".$val."'><span>".$val."</span><br>";
                    }
                }
            }


        }
        return $radio;
    }

    //多选的显示数据处理
    public function checkbox($v,$k){
        $id = $v['id'];
        $must = $v['must'];
        $checkbox='';
        if($must=='0'){
            $checkbox.= "<br/>".$k.'.&nbsp;'.$v['title']."<br>";
            $option = explode('@&',$v['option']);
            foreach($option as $key=>$val){
                if($key=='0'){
                    $checkbox .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='".$v['id']."[]' value='".$val."' >".$val."<br>";
                }elseif($val=='other'){
                    $checkbox .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='".$v['id']."[]' value='".$val."'>其他<br>";
                }else{
                    $checkbox .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='".$v['id']."[]' value='".$val."'>".$val."<br>";
                }

            }
        }else if($must=='1'){
            $checkbox.= "<br/>".$k.'.&nbsp;'.$v['title']."<span class='title-imp'> * </span><br/><br>";
            $option = explode('@&',$v['option']);
            foreach($option as $key=>$val){
                if($key=='0'){
                    $checkbox .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' class='nonull'  name='".$v['id']."[]' value='".$val."'>".$val."<br>";
                }elseif($val=='other'){
                    $checkbox .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' class='nonull' name='".$v['id']."[]' value='".$val."'>其他<br>";
                }else{
                    $checkbox .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' class='nonull' name='".$v['id']."[]' value='".$val."'>".$val."<br>";
                }

            }
        }


        return $checkbox;
    }

    //填空的显示数据处理
    public function fill($v){
//        dump($v);
        $fill='';
        $title = explode('*',$v['title']);
        foreach($title as $key =>$val){
            if($key=='0'){
                $fill .= $title[$key];
            }else{
                $fill.= "".$title[$key]."<input type='text' name='".$v['id']."[]' ><br>";
            }
        }
        return $fill;
    }



    //文本域的显示数据处理
    public function textarea($v,$k){
//        dump($v);
        $id = $v['id'];
        $textarea = '';
        $must = $v['must'];
        if($must=='0'){
            $textarea .="".$k.'.&nbsp;'.$v['title']."<br/>&nbsp;&nbsp;&nbsp;&nbsp;<textarea rows='5%' cols='109%' name='".$v['id']."'></textarea><br/><br/>";
        }else if($must=='1'){
            $textarea .="".$k.'.&nbsp;'.$v['title']."<span class='title-imp' > * </span><span class='title-imp span_$k' style='display:none'>  此问题必须填写 </span><br>&nbsp;&nbsp;&nbsp;<textarea class='nonull_text' attr-id='$k' id='answer_$id'  rows='5%' cols='130%' name='".$v['id']."' onblur='must_nonull($id,$k)'></textarea><br/><br/>";
        }
        return $textarea;
    }

    //下拉选择的显示数据处理
    public function select($v,$k){
        $id = $v['id'];
        $must = $v['must'];
        $select ='';
        $select .="<br/>".$k.'.&nbsp;'.$v['title']."&nbsp<select name='".$v['id']."'>";
        $option = explode('@&',$v['option']);
        foreach($option as $key =>$val){
            $select .= "&nbsp;&nbsp;&nbsp;&nbsp;<option value='".$val."' >".$val."</option><br/>";
        }
        $select .="</select><br>";
        return $select;
    }

    //联系人组件中文本框处理
    public function lianxi($v,$k){
//       dump($v);exit;

        $id = $v['id'];
        $lianxi = '';
        $must = $v['must'];


        if($must=='0'){
            $lianxi .= "<br/>&nbsp;&nbsp;<div>".$k.'.</div>&nbsp;'.$v['title']."<br/><input type='text' class='Textc-input inpu' name='".$v['type']."'  ><br/>";
        }else if($must=='1'){
            $lianxi .=" <br/>&nbsp;&nbsp;<span class='aaa'>".$k.'.</span>&nbsp;'.$v['title']."<span class='title-imp' > * </span><span class='title-imp span_$k' style='display:none'> 此问题必须填写 </span><br/><input type='text' id='answer_$id' attr-id='$k' class='Textc-input inpu nonull_text'  name='".$v['type']."' onblur='must_nonull($id,$k)' onchange='tt(".$id.")'><br/>";

        }
        return $lianxi;
    }

    //联系人组件中文本框处理
    public function birth($v,$k){
//       dump($v);exit;

        $id = $v['id'];
        $lianxi = '';
        $must = $v['must'];

        if($must=='0'){
            $lianxi .= "<br/>&nbsp;&nbsp;<div>".$k.'.</div>&nbsp;'.$v['title']."<br/><input type='text' class='Textc-input inpu' name='".$v['type']."'  ><br/>";
        }else if($must=='1'){
            $lianxi .=" <br/>&nbsp;&nbsp;<span class='aaa'>".$k.'.</span>&nbsp;'.$v['title']."<span class='title-imp' > * </span><span class='title-imp span_$k' style='display:none'> 此问题必须填写 </span><br/><input type='text' id='answer_$id' attr-id='$k' class='Textc-input inpu nonull_text '  name='".$v['type']."'><br/>";

        }
        return $lianxi;
    }
    //联系人组件中性别处理
    public function sex($v,$k){

        $id = $v['id'];
        $lianxi = '';
        $must = $v['must'];
        $sex = '';
        $sex .= "<div class='$v[type]' style='border:0'  id=".$id.">";
        if($must == '0'){
            $sex .="<h6 >".$k."&nbsp;$v[title]</h6><div class='$v[type]-input'>";
        }else if($must=='1'){
//            $sex .="<h6>$v[title]<span>*此问题必须填写</span></h6><div class='$v[type]-input'>";
            $sex .= " <br/>&nbsp;&nbsp;".$k.'.&nbsp;'.$v['title']."<span class='title-imp'> * </span><br/><br/>";
        }
        $option = explode('@&',$v['option']);
        for($i=0;$i<count($option);$i++){
            if($i=='0'){
                $sex .="&nbsp;&nbsp;&nbsp;<span>$option[$i]</span> <input id='sex_".$i."' type='radio' name='".$v['type']."' value='$option[$i]' onclick='tt1(".$i.",".$id.")'  /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            }else{
                $sex .="&nbsp;&nbsp;&nbsp;<span>$option[$i]</span> <input id='sex_".$i."' type='radio' name='".$v['type']."' value='$option[$i]' onclick='tt1(".$i.",".$id.")' /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            }

        }
//        foreach($option as $key=>$val){
//            $sex .="&nbsp;&nbsp;&nbsp;<span>$val</span> <input type='radio' name='".$v['type']."' value='$val' onclick='tt1(this,".$id.")' /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
//        }
        $sex .= "</div><div class='$v[type]-input-edit' style='display: none'>";
        $sex .= " <b class='iconfont input-del' onclick='del_tit($id)'>&#xe61d;</b> </div>";
        return $sex;

    }
    //数字类型
    public function number($v,$k){
        $id = $v['id'];
        $number = '';
        $must = $v['must'];
        $range = $v['range'];
        $danwei = $v['remark'];

        if($must=='0' && $v['range'] !==''){
            $number .= "<div>".$k.'.</div>&nbsp;'.$v['title']."<span></span><span>填写范围:$range</span><br/><br/>";
        }elseif($must=='0' && $v['range']==''){
            $number .= "<div>".$k.'.</div>&nbsp;'.$v['title']."<span></span><br/><br/>";
        }elseif($must=='1' && $v['range']!==''){
            $number .=" <span class='aaa'>".$k.'.</span>&nbsp;'.$v['title']."<span class='title-imp' > * </span><span class='title-imp span_$k' style='display:none'> 此问题必须填写 </span><span>填写范围:$range</span><br/>";
        }elseif($must=='1' && $v['range']==''){
            $number .=" <span class='aaa'>".$k.'.</span>&nbsp;'.$v['title']."<span class='title-imp' > * </span><span class='title-imp span_$k' style='display:none'> 此问题必须填写 </span><br/>";
        }else{
        }
        $number .= '<div style="position:relative;"><input type="text" name="'.$id.'"  class="inpu Num_kg" onblur="aa()"><div class="kgs" style="position:absolute;right:30px;top:6px;">'.$danwei.'</div></div><br/>';
//        print_r($number);
        return $number;
    }
    //复合题的显示数据处理
    public function mixed($v){
        $mixed = '';
        $mixed .= $v['title'];
        $res = Db::table('question_database')->where('pid = '.$v['id'])->select();
//        dump($res);
        foreach($res as $key=>$val){
            if($val['type']=='文本'){
                $mixed .= $this->text($val);
            }elseif($val['type']=='单选'){
                $mixed .= $this->radio($val);
            }elseif($val['type']=='矩阵填空'){
                $mixed .= $this->fill($val);
            }elseif($val['type']=='下拉选择') {
                $mixed .= $this->select($val);
            }elseif($val['type']=='多选') {
                $mixed .= $this->checkbox($val);
            }
        }
        return $mixed;
    }
    //题组
    public function tizu($v){
        $id = $v['id'];
        $tizu = '';
        $tizu .= "<br/>&nbsp;&nbsp;<div></div>&nbsp;".$v['title']."<br/><br/>";
        return $tizu;
    }
    //地址
    public function addre($v,$k){
        $addre = '';
        $id = $v['id'];
        $title = $v['title'];
        $must = $v['must'];
        if($must == '1'){
            $addre = "<br/><span class='aaa'>".$k.'.</span>&nbsp;'.$v['title']."<span class='title-imp' > * </span><span class='title-imp span_$k' style='display:none'> 此问题必须填写 </span><br/>";
        }elseif($must=='0'){
            $addre ="<br/><span class='aaa'>".$k.'.</span>&nbsp;'.$v['title'];
        }
        $addre .= '<div class="address-information-input outer" style="margin-bottom:20px;">';
        $addre .= '<select id="province" class="sel" name="'.$id.'[]" onchange="ajax_guding('."'province'".','."'city'".')"> <option value="请选择">请选择</option><option value="1">大兴区</option> </select>';
        $addre .= '<select id="city" class="sel zhen" name="'.$id.'[]" onchange="ajax_guding('."'city'".','."'town'".')"><option value="请选择">请选择</option> </select>';
        $addre .= '<select id="town" class="sel cun"  name="'.$id.'[]"> <option value="请选择">请选择</option> </select>';
        $addre .= '<input type="text" id="adress_mes" name="'.$id.'[]" class="sel" placeholder="请输入详细地址" style="width:150px;text-align:left;"><br></div>';
//        print_r($addre);
        return $addre;
    }
    //
    public function fujian($v,$k){

        $fujian = '';
        $id = $v['id'];
        $title = $v['title'];
        $must = $v['must'];
        if($must=='1'){
            $fujian = '<span  id="acc_up'.$k.'"> '.$k.'.'.$title.'<span class="title-imp"> * </span></span>';
            $fujian .= '<br/><br/><div id="acc_up'.$k.'" class="acc_up" >';
            $fujian .= '<input type="file" class="acc_upload" style="" name="'. $id.'" onchange="Javascript:validate_file(this,'.$k.');"><div class="acc_up_m acc_up_m'.$k.'">';
            $fujian .='<p class="acc_up_mes acc_up_mes'.$k.'">上传文件</p><span></span></div></div><br><br>';
        }elseif($must=='0'){
            $fujian = '<span  id="acc_up'.$k.'"> '.$k.'.'.$title.'</span>';
            $fujian .= '<br/><br/><div id="acc_up'.$k.'" class="acc_up" >';
            $fujian .= '<input type="file" class="acc_upload" style="" name="'. $id.'" onchange="Javascript:validate_file(this,'.$k.');"><div class="acc_up_m acc_up_m'.$k.'">';
            $fujian .='<p class="acc_up_mes acc_up_mes'.$k.'">上传文件</p><span></span></div></div><br><br>';
        }
        return $fujian;
    }
    //日期
    public function date($v,$k){
        $date = '';

        $range = $v['range'];
        $id = $v['id'];
        $title = $v['title'];
        $must = $v['must'];
        if($v['remark']=='0'){
            $date_type = '年-月-日,例:2017-01-01';
        }elseif($v['remark']=='1'){
            $date_type = '年-月,例:2017-01';
        }elseif($v['remark']=='3'){
            $date_type = '月-日,例:01-01';
        }else{

        }
        if($must=='0' && $v['range'] !=='~'){
            $date .= "<br/><div>".$k.'.</div>&nbsp;'.$v['title']."<span></span><span>填写范围:$range&nbsp;&nbsp;填写格式:$date_type</span><br/><br/>";
        }elseif($must=='0' && $v['range']=='~'){
            $date .= "<br/><div>".$k.'.</div>&nbsp;'.$v['title']."<span>&nbsp;&nbsp;填写格式:$date_type</span><br/><br/>";
        }elseif($must=='1' && $v['range']!=='~'){
            $date .=" <br/><span class='aaa'>".$k.'.</span>&nbsp;'.$v['title']."<span class='title-imp' > * </span><span class='title-imp span_$k' style='display:none'> 此问题必须填写 </span><span>填写范围:$range&nbsp;&nbsp;填写格式:$date_type</span><br/><br/>";
        }elseif($must=='1' && $v['range']=='~'){
            $date .=" <br/><span class='aaa'>".$k.'.</span>&nbsp;'.$v['title']."<span class='title-imp' > * </span><span class='title-imp span_$k' style='display:none'> 此问题必须填写 </span><span>填写格式:$date_type</span><br/><br/>";
        }else{
        }
        $date .= "<input type='text' placeholder='请按格式正确填写日期时间' name='$id' class='inpu date_Date' onblur='val_D($k)'></br><br/>";
        return $date;
    }

    public function pic_upload($v,$k){

        $pic = '';
        $range = $v['range'];
        $id = $v['id'];
        $title = $v['title'];
        $must = $v['must'];
        if($must=='0' && $v['remark'] =='1'){
            $pic = "<span> $k.$title</span><div class='picUpload row'><div id='preview'><img id='imghead' src=></div><input type='file' name='".$id."[]' onchange='previewImages(this)' />";
            $pic .= "<textarea class='addpicUpload' name='".$id."[]' style='disply:none;width:100%'>添加图片描述</textarea></div>";
        }elseif($must=='0' && $v['remark']=='0'){
            $pic = "<span> $k.$title</span><div class='picUpload row'><div id='preview'><img id='imghead' src=></div><input type='file' name='".$id."[]' onchange='previewImages(this)' /></div>";
        }elseif($must=='1' && $v['remark']=='1'){
            $pic = "<span> $k.$title<span class='title-imp'> * </span></span><div class='picUpload row'><div id='preview'><img id='imghead' src=></div><input type='file' name='".$id."[]' onchange='previewImages(this)' />";
            $pic .= "<textarea class='addpicUpload' name='".$id."[]' style='disply:none;width:100%'>添加图片描述</textarea></div>";
        }elseif($must=='1' && $v['remark']=='0'){
            $pic = "<span> $k.$title<span class='title-imp'> * </span></span><div class='picUpload row'><div id='preview'><img id='imghead' src=></div><input type='file' name='".$id."[]' onchange='previewImages(this)' /></div>";
        }else{
        }
        return $pic;
    }



}
?>