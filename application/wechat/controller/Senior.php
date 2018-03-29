<?php

namespace app\wechat\controller;

use think\Db;
use wx_api;
use think\Controller;
use think\log;
use \php_log;
use \wx_public;
use think\User_config;
use wxAuthLib;
use weekdate;
use con_public;
use verification;
use \code;

class Senior extends User_config
{
    public $uniacid;
    public function __construct()
    {
        parent::__construct();
        $request=  \think\Request::instance();
        $verification = new verification();
        $this->assign('action',$request->action());
        $this->assign('menu',$request->controller());
        $this->uniacid = $verification->wechat_action();
    }

    //    长链接转二维码
    public function QR()
    {
        if($_POST){
            $url = $_POST['uri'];
            $parse_url = parse_url($url);
            if($parse_url['scheme']=="http" || $parse_url['scheme']=="https" || $parse_url['scheme']=="weixin"){
                $public = new wx_public();
                $short = $public->short_link($this->uniacid,$url);
                if($short->errcode!=0){
                    return json_encode(array('type'=>0,'errcode'=>'错误代码为'.$short->errcode),true);
                }
                $url = $short->short_url;
                $this->qrcode($url);
                return json_encode(array('type'=>1,'short'=>$url,'code'=>'qrcode'.$this->uniacid.'.png'),true);
            }else{
                return json_encode(array('type'=>0,'errcode'=>'url格式不正确'),true);
            }

        }else{
            return $this->fetch();
        }
    }

    //    粉丝管理

    public function follower()
    {
        $where = "a.uniacid={$this->uniacid}";
        if($_POST){
            if(!empty($_POST['type']) || $_POST['type']==0){
                $where .=" and a.subscribe = '{$_POST['type']}'";
                if($_POST['type']==1){
                    $form['type'] = $_POST['type'];
                }else{
                    $form['type'] = 2;
                }
            }
            if(!empty($_POST['time'])){
                $time = explode('~',$_POST['time']);
                $start = strtotime($time[0]);
                $end = strtotime($time[1]);
                $form['time'] = $time[0].'&nbsp;至&nbsp;'.$time[1];
                $where .=" and a.subscribe_time>= '{$start}' and a.subscribe_time<= '{$end}'";
            }
            if(!empty($_POST['openid'])){
                $form['openid'] = $_POST['openid'];
                $where .=" and a.nickname like '%{$_POST['openid']}%' or a.openid = '{$_POST['openid']}'";
            }
        }else{
            $form = null;
            $create_where = " and 1=1";
        }
        if(!empty($_POST['create'])){
            $create_where = " and a.create_id = {$_POST['create']}";
            $user =  Db::name('account_user')
                ->alias('a')
                ->join('ty_account_user_relation b','b.openid = a.openid')
                ->where('b.create_id',$_POST['create'])
                ->where($where)
                ->paginate(20);
            $page = $user->render();
            $data = $user->all();
            $form['create'] = $_POST['create'];
        }else{
            $user =  Db::name('account_user')->alias('a')->where($where)->paginate(20);
            $page = $user->render();
            $data = $user->all();
        }
        $create =  Db::name('account_create')->where('uniacid',$this->uniacid)->where('is_del',0)->select();
        foreach($data as $k=>$v){
            $creates =  Db::name('account_user_relation')
                ->alias('a')
                ->join('ty_account_create b','a.create_id = b.create_id')
                ->where("a.openid='".$v['openid']."'")
                ->field('b.*')
                ->select();
            $data[$k]['create'] = $creates;
        }
        $wechat =  Db::name('account_wechats')->where('uniacid',$this->uniacid)->find();
        $page = $user->render();
        $this->assign('wechat',$wechat);
        $this->assign('page',$page);
        $this->assign('create',$create);
        $this->assign('user',$data);
        $this->assign('form',$form);
        return $this -> fetch();
    }

    public function grouping(){
        $create = Db::name('account_create')->where('uniacid',$this->uniacid)->where('is_del',0)->paginate(10);
        $page = $create->render();
        $this->assign('page',$page);
        $this->assign('create',$create);
        return $this -> fetch();
    }

    public function qrcode($url)
    {
        require ROOT_PATH.'thinkphp/library/Vendor/Phpqrcode/phpqrcode.php';
        $route = ROOT_PATH.'public/qrcode/qrcode'.$this->uniacid.'.png';
        $class = new \QRcode();
        $class::png($url,$route,QR_ECLEVEL_L,20,2,false,0xFFFFFF,0x000000);
    }

    /**
     * @return int
     * ajax 同步粉丝信息
     */
    public function follower_action(){
        $uniacid =  $this->uniacid;
        $type = $_POST['type'];
        $public = new wx_public();
        if($type == 1){
            $ids = $_POST['id'];
            $ids = rtrim($ids,',');
            $result = $public->follower_action($uniacid,$ids);
        }else{
            $result = $public->follower_action($uniacid);
//            $result = $public->get_user_list($uniacid);
        }
        return $result;

    }

    /**
     * 同步标签组信息
     */
    public function create_action(){
        $uniacid =  $this->uniacid;
        $public = new wx_public();
        $result = $public->create($uniacid);
        return $result;
    }

    /**
     * 删除标签信息
     */
    public function create_del(){
        $id = $_POST['id'];
        if(!empty($id)){
            $uniacid =  $this->uniacid;
            $public = new wx_public();
            $result = $public->del_create($uniacid,$id);
            return $result;
        }else{
            return json_encode(array('type'=>0,'errcode'=>'未知错误!'),true);
        }
    }


    /**
     * 添加标签信息
     */
    public function create_add(){
        $count = $_POST['count'];
        if(!empty($count)){
            $uniacid =  $this->uniacid;
            $public = new wx_public();
            $result = $public->add_create($uniacid,$count);
            return $result;
        }else{
            return json_encode(array('type'=>0,'errcode'=>'未知错误!'),true);
        }
    }

    /**
     * @return string
     * 添加标签
     */
    public function create_user(){
        $select = $_POST['select'];
        $ids = $_POST['id'];
        if(!empty($select) && !empty($ids)){
            $user =  Db::name('account_user')->where('id',$ids)->find();
            $find = Db::name('account_user_relation')->where('create_id',$select)->where('openid',$user['openid'])->find();
            if(!$find){
                $user = Db::name('account_user')->where('id',$ids)->find();
                $openid = $user['openid'];
                $uniacid =  $this->uniacid;
                $public = new wx_public();
                $data[]=$openid;
                $result = $public->add_user_create($uniacid,$data,$select);
                return $result;
            }else{
                return json_encode(array('type'=>0,'errcode'=>'粉丝已经在此标签组，无法重复添加!'),true);
            }
        }else{
            return json_encode(array('type'=>0,'errcode'=>'未知错误!'),true);
        }

    }


    public function create_update(){
        $count = $_POST['count'];
        $id = $_POST['id'];
        if(!empty($count) && !empty($id)){
            $uniacid =  $this->uniacid;
            $public = new wx_public();
            $result = $public->updata_create($uniacid,$count,$id);
            return $result;
        }else{
            return json_encode(array('type'=>0,'errcode'=>'未知错误!'),true);
        }
    }

    /**
     * @return string
     * 哦批量添加标签
     */
    public function create_users(){
        $select = $_POST['count'];
        $ids = $_POST['id'];
        if(!empty($select) && !empty($ids)){
            $ids = rtrim($ids,',');
            $user =  Db::name('account_user')->where('id in ('.$ids.')')->select();
            foreach($user as $k=>$v){
                $find = Db::name('account_user_relation')->where('create_id',$select)->where('openid',$v['openid'])->find();
                if(!$find){
                    $openid[]=$v['openid'];
                }else{
                    return json_encode(array('type'=>0,'errcode'=>$v['nickname'].'已经在此标签组，无法重复添加!'),true);
                }
            }
            $uniacid =  $this->uniacid;
            $public = new wx_public();
            $result = $public->add_user_create($uniacid,$openid,$select);
            return $result;
        }else{
            return json_encode(array('type'=>0,'errcode'=>'未知错误!'),true);
        }
    }

    //    文章管理
    public function article_lis()
    {
        $uniacid = $this->uniacid;
        $article = Db::name('article')->where('uniacid',$uniacid)->where('is_del',0)->paginate(10);
        $page = $article->render();
        $this->assign('page',$page);
        $this->assign('article',$article);
        $this->assign('uniacid',$uniacid);
        return $this -> fetch();
    }

    public function article_add()
    {
        $uniacid = $this->uniacid;
        if($_POST){
            $id = $_POST['id'];
            $data = array(
                'title' => $_POST['title'],
                'uniacid' => $uniacid,
                'account_name' => $_POST['account_name'],
                'author' => $_POST['author'],
                'link' => $_POST['link'],
                'read' => $_POST['read'],
                'fabulous' => $_POST['fabulous'],
                'state' => $_POST['state'],
                'count' => $_POST['count'],
                'addtime' => date('Y-m-d H:i:s',time()),
            );
            if(!empty($_POST['no_link'])){
                $data['no_link'] = $_POST['no_link'];
            }
            if(!empty($_POST['no_friends'])){
                $data['no_friends'] = $_POST['no_friends'];
            }
            if(!empty($_POST['no_circle'])){
                $data['no_circle'] = $_POST['no_circle'];
            }
            if(empty($id)){
                $data['date'] = date('Y-m-d',time());
                $result =  Db::name('article')->insert($data);
                $name = '添加';
            }else{
                if(!empty($_POST['date'])){
                    $data['date'] = $_POST['date'];
                }
                $result =  Db::name('article')->where('id',$id)->update($data);
                $name = "修改";
            }

            if($result){
                $this->success($name.'成功！','senior/article_lis');
            }else{
                $this->error($name.'失败，请稍后重试！');
            }
        }else{
            $id = input('param.id');
            if(!empty($id)){
                $article = Db::name('article')->where('id',$id)->find();
            }else{
                $article = null;
            }

            $this->assign('id',$id);
            $this->assign('article',$article);
            return $this -> fetch();
        }
    }

    public function users(){
        $id=$_POST['id'];
        if(!empty($id)){
            $find = Db::name('account_user')->where('id',$id)->find();
            if($find){
                return json_encode(array('type'=>1,'count'=>$find),true);
            }else{
                return json_encode(array('type'=>0,'errcode'=>'未知错误!'),true);
            }
        }else{
            return json_encode(array('type'=>0,'errcode'=>'未知错误!'),true);
        }
    }

//    自定义菜单
    public function custom_menu()
    {
        return $this -> fetch();
    }
    public function custom_menus()
    {
        $uniacid =  $this->uniacid;
        if($_POST){
            $id = $_POST['id'];
//            $count = json_decode($_POST['cs']);
            $title = $_POST['title'];
            $data = array(
                'uniacid' =>$uniacid,
                'title' =>$title,
                'count' =>$_POST['count'],
            );
            if(empty($id)){
                $data['addtime'] = date('Y-m-d H:i:s',time());
                $result = Db::name('menu')->insert($data);
                $name = '添加';
            }else{
                $result = Db::name('menu')->where('id',$id)->update($data);
                $name = '修改';
            }
            if($result){
                return json_encode(array('type'=>1,'success'=>$name.'成功'),true);
            }else{
                return json_encode(array('type'=>0,'errcode'=>$name.'失败'),true);
            }
        }else{
            $id = input('param.id');
            if(!empty($id)){
                $result = Db::name('menu')->where('id',$id)->find();
            }else{
                $result = null;
            }

            $this->assign('menu',$result['count']);
            $this->assign('result',$result);
            return $this -> fetch();
        }
    }
//
    public function custom_menus_lis()
    {
        $uniacid =  $this->uniacid;
        $menu = Db::name('menu')->where('uniacid',$uniacid)->where('is_del',0)->order('id desc')->paginate(10);
        $page = $menu->render();
        $this->assign('page',$page);
        $this->assign('menu',$menu);
        return $this -> fetch();
    }

    public function wechat_menu(){
        $id = input('param.id');
        $uniacid = $this->uniacid;
//        $id = 10;
        $public = new wx_public();
        $res = $public->menu($uniacid,$id);
        if($res->errcode==0){
            Db::name('menu')->where('uniacid',$uniacid)->update(['take'=>0]);
            $result =  Db::name('menu')->where('uniacid',$uniacid)->where('id',$id)->update(['take'=>1]);
            if($result){
                return json_encode(array('type'=>1,'success'=>'已推送到微信端!'),true);
            }else{
                return json_encode(array('type'=>0,'errcode'=>'推送失败'),true);
            }
        }else{
            return json_encode(array('type'=>0,'errcode'=>'推送失败，错误代码为'.$res->errcode),true);
        }
    }

    public function menu_del(){
        $uniacid = $this->uniacid;
        $id = $_POST['id'];
        $find = Db::name('menu')->where('uniacid',$uniacid)->where('id',$id)->find();
        if($find['take']==1){
            return json_encode(array('type'=>0,'errcode'=>'该菜单已在微信端生效!'),true);
        }else{
            $result =  Db::name('menu')->where('uniacid',$uniacid)->where('id',$id)->update(['is_del'=>1]);
            if($result){
                return json_encode(array('type'=>1,'success'=>'删除成功!'),true);
            }else{
                return json_encode(array('type'=>0,'errcode'=>'删除失败'),true);
            }
        }
    }
    public function user_del(){
        $uniacid =  $this->uniacid;
        $ids = $_POST['id'];
        $ids = rtrim($ids,',');
        $result = Db::name('account_user')->where('id in ('.$ids.')')->delete();
        if($result){
            return 1;
        }else{
            return 0;
        }
    }

    public function article_dl(){
        $id = input('param.id');
        $resule = Db::name('article')->where('id',$id)->update(['is_del',1]);
        if($resule){
            $this->success('删除成功!');
        }else{
            $this->error('删除失败!');
        }
    }
//    素材&群发
    public function material_pic()
    {
        return $this -> fetch();
    }
    public function material_speech()
    {
        return $this -> fetch();
    }
    public function material_video()
    {
        return $this -> fetch();
    }
    public function material_reply()
    {
        $id = 43;
        $reply = Db::name('reply')->where('id',$id)->find();
        $group =  Db::name('reply_news')->where('rid',$id)->group('group')->field('group')->select();
        for($i=0;$i<10;$i++){
            foreach($group as $k=>$v){
                $select = Db::name('reply_news')->where('group',$v['group'])->where('rid',$id)->select();
                foreach($select as $k1=>$v1){
                    $data[$i]['id'] = $i+1;
                    $data[$i][$v1['type']] = array(
                        'title'=>$v1['title'],
                        'pic'=>$v1['pic_url'],
                        'url'=>$v1['url'],
                    );
                }
            }
        }
        $data =  json_encode($data,true);
        $this->assign('data',$data);
        return $this -> fetch();
    }
    public function material_reply_add()
    {
        return $this -> fetch();
    }


    public function ajax_cs(){
        $b = array(36,37,43,44);
        $a = rand(0,3);
        $num = $_POST['num'];
        $id = $b[$a];
        $reply = Db::name('reply')->where('id',$id)->find();
        $group =  Db::name('reply_news')->where('rid',$id)->group('group')->field('group')->select();
        for($i=$num*10;$i<=$num*10+10;$i++){
            foreach($group as $k=>$v){
                $select = Db::name('reply_news')->where('group',$v['group'])->where('rid',$id)->select();
                foreach($select as $k1=>$v1){
                    $data[$i]['id'] = $i;
                    $data[$i][$v1['type']] = array(
                        'title'=>$v1['title'],
                        'pic'=>$v1['pic_url'],
                        'url'=>$v1['url'],
                    );
                }
            }
        }
        $data =  json_encode($data,true);
        return $data;
    }

    public function ceshi(){
        $str = '110101,110102,110105,110106,110107,110108,110109,110111,110112,110113,110114,110115,110116,110117,110118,110119';
        $str1 = '东城区,西城区,朝阳区,丰台区,石景山区,海淀区,门头沟区,房山区,通州区,顺义区,昌平区,大兴区,怀柔区,平谷区,密云区,延庆区';
        $gaode = new \gaode_api();
        $arr = explode(',',$str);
        $arr1 = explode(',',$str1);
        foreach($arr as $k=>$v){
            $data[$k]['name'] =  $arr1[$k];
            $data[$k]['adcode'] = $v;
        }
        $types = '050000|060100|060400|061000|071100|071400|100100|100200|110100|110200';
        foreach($data as $ko=>$vo){
//			$result[] = $this->key_search($vo['name'],$vo['adcode'],$types,20,1);
            $num = $this->key_search($vo['name'],$vo['adcode'],$types,1,1)['count'];
            $count = ceil($num/20);
            $res = null;
            for($i=1;$i<=$count;$i++){
                $res = $this->key_search($vo['name'],$vo['adcode'],$types,20,$i)['pois'];
                foreach($res as $key=>$val){
                    $list[$k]['id'] = $val['id'];
                    $list[$k]['name'] = $val['name'];
                    $list[$k]['type'] = $val['type'];
                    if(empty($val['biz_ext']['rating'])){
                        $list[$k]['score'] = '暂无';
                    }else{
                        $list[$k]['score'] = $val['biz_ext']['rating'];
                    }
                    if(empty($val['biz_ext']['cost'])){
                        $list[$k]['cost'] = '暂无';
                    }else{
                        $list[$k]['cost'] = $val['biz_ext']['cost'];
                    }
                    $list[$k]['location'] = $val['location'];
                    $list[$k]['address'] = $val['address'];
                    $list[$k]['tel'] = $val['tel'];
                    if(empty($val['photos'])){
                        $list[$k]['pic_url'] = null;
                    }else{
                        $list[$k]['pic_url'] = $val['photos'][0]['url'];
                    }
                }
                $aa = Db::table('gaode_info')->insertAll($list);
                if($aa){

                }else{
                    $str = $vo['name'].'的第'.$i.'页失败';
                    dump($str);exit;
                }
            }
            exit;
        }





    }

}
