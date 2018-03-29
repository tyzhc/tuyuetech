<?php

namespace app\wechat\controller;

use app\index\model\questionModel;
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

class Reply extends User_config
{
    public $uniacid;
    public $action;
    public $menu;

    /**
     * Reply constructor.
     * 获取当前微信号、控制器名称、方法名称
     */
    public function __construct()
    {
        parent::__construct();
        $request=  \think\Request::instance();
        $verification = new verification();
        $this->action = $request->action();
        $this->menu = $request->controller();
        $this->assign('action',$request->action());
        $this->assign('menu',$request->controller());
        $this->uniacid = $verification->wechat_action();
    }

    /**
     * 文字回复
     */
    public function basic_lis()
    {
        $state = input('param.state');
        $count = input('param.count');
        $where['start'] = array('eq', 1);
        $where['is_del'] =array('eq', 0);
        $where['uniacid'] = array('eq', $this->uniacid);
        if (!empty($state)) {
            $where['type'] =array('eq', $state);
        }
        if (!empty($count)) {
            $where['title'] = array('like', "%{$count}%");
        }
        $reply =  Db::name('reply')
            ->where($where)
            ->paginate(10);
        $page = $reply->render();
        $this->assign('page',$page);
        $this->assign('type',1);
        $this->assign('reply',$reply);
        $this->assign('action',$this->action);
        $this->assign('menu',$this->menu);
        $this->assign('state',$state);
        return $this -> fetch();
    }

    public function basic_add()
    {
        if($_POST){
            dump($_POST);die;
            $id = $_POST['id'];
            $data = array(
                "title"=>$_POST['title'],
                "keywords"=>$_POST['keywords'],
                "addtime"=>date('Y-m-d H:i:s',time()),
                "start"=>1,
                "uniacid"=>$this->uniacid,
            );
            if(!empty($_POST['type'])){
                $data['type']=$_POST['type'];
            }
            if(!empty($_POST['grade'])){
                $data['grade']=$_POST['grade'];
            }
//            if($_POST['class']=='on'){
//                $data['class']=1;
//            }
            if(empty($id)){
                $reply = Db::name('reply')->insertGetId($data);
                if($reply){
                    $count = $_POST['count'];
                    foreach($count as $k=>$v){
                        $data_count[]=array(
                            'rid'=>$reply,
                            'count'=>$v,
                        );
                    }
                    $result = Db::name('reply_content')->insertAll($data_count);
                    if($result){
                        $this->success('添加成功',url('reply/basic_add',array('id'=>$reply)));
                    }
                }
            }else{
                $reply = Db::name('reply')->where('id',$id)->update($data);
                if($reply){
                    Db::name('reply_content')->where('rid',$id)->delete();
                    $count = $_POST['count'];
                    foreach($count as $k=>$v){
                        $data_count[]=array(
                            'rid'=>$id,
                            'count'=>$v,
                        );
                    }
                    $result = Db::name('reply_content')->insertAll($data_count);
                    if($result){
                        $this->success('修改成功',url('reply/basic_add',array('id'=>$id)));
                    }
                }
            }
            $this->error('添加失败');
        }else{
            $id =  input('param.id');
            if(!empty($id)){
                $reply = Db::name('reply')->where('id',$id)->find();
                $count =  Db::name('reply_content')->where('rid',$id)->select();
                $type = 3;
                $num = count($count);
            }else{
                $type = 2;
                $reply = null;
                $count = null;
                $num = 0;
            }
            $this->assign('num',$num);
            $this->assign('type',$type);
            $this->assign('reply',$reply);
            $this->assign('count',$count);
            return $this -> fetch();
        }

    }

    /**
     * 图片回复
     */
    public function pic_lis()
    {
        $state = input('param.state');
        $count = input('param.count');
        $where['start'] = array('eq', 2);;
        $where['is_del'] =array('eq', 0);;
        $where['uniacid'] = array('eq', $this->uniacid);
        if (!empty($state)) {
            $where['type'] =array('eq', $state);;
        }
        if (!empty($count)) {
            $where['title'] = array('like', "%{$count}%");
        }
        $reply =  Db::name('reply')
            ->where($where)
            ->paginate(10);
        $page = $reply->render();
        $this->assign('page',$page);
        $this->assign('type',1);
        $this->assign('action',$this->action);
        $this->assign('menu',$this->menu);
        $this->assign('reply',$reply);
        $this->assign('state',$state);
        return $this -> fetch();

        $this->assign('type',1);
        return $this -> fetch();
    }

    public function pic_add()
    {
        if($_POST){
            $id = $_POST['id'];
            $data = array(
                'title'=>$_POST['title'],
                'type'=>$_POST['type'],
                'keywords'=>$_POST['keywords'],
                'pic_title'=>$_POST['pic_title'],
                'pic_count'=>$_POST['pic_count'],
                'addtime'=>date('Y-m-d H:i:s',time()),
                'start'=>2,
                "uniacid"=>$this->uniacid,
            );
            $files = request()->file('file');
            if($files){
                $info = $files->move(ROOT_PATH . 'public' . DS . 'uploads/images');
                $data['file'] = $info->getSaveName();
                $data['file_name'] = $info->getinfo()['name'];
            }
            if(empty($id)){
                $result =  Db::name('reply')->insertGetId($data);
                $id =$result;
                $name = "添加";
            }else{
                $result =  Db::name('reply')->where('id',$id)->update($data);
                $name = "修改";
            }

            if($result){
                $this->success($name.'成功',url('reply/pic_add',array('id'=>$id)));
            }else{
                $this->error($name.'失败');
            }

        }else{
            $id =  input('param.id');
            if(!empty($id)){
                $type = 3;
                $reply = Db::name('reply')->where('id',$id)->find();

            }else{
                $reply = "";
                $type = 2;
            }
            $this->assign('type',$type);
            $this->assign('reply',$reply);
            return $this -> fetch();
        }
    }

    /**
     * 音乐回复
     */
    public function music_lis()
    {
        $state = input('param.state');
        $count = input('param.count');
        $where['start'] = array('eq', 5);;
        $where['is_del'] =array('eq', 0);;
        $where['uniacid'] = array('eq', $this->uniacid);
        if (!empty($state)) {
            $where['type'] =array('eq', $state);
        }
        if (!empty($count)) {
            $where['title'] = array('like', "%{$count}%");
        }
        $reply =  Db::name('reply')
            ->where($where)
            ->paginate(10);
        $page = $reply->render();
        $this->assign('page',$page);
        $this->assign('type',1);
        $this->assign('reply',$reply);
        $this->assign('action',$this->action);
        $this->assign('menu',$this->menu);
        $this->assign('state',$state);
        return $this -> fetch();
    }
    public function music_add()
    {
        if($_POST){
            $id = $_POST['id'];
            $data = array(
                "title"=>$_POST['title'],
                "keywords"=>$_POST['keywords'],
                "type"=>$_POST['type'],
                "start"=>5,
                "uniacid"=>$this->uniacid,
            );
            if(empty($id)){
                $data['addtime']=date('Y-m-d H:i:s',time());
                $reply = Db::name('reply')->insertGetId($data);
                if($reply){
                    $count = $_POST['count'];
                    foreach($count as $k=>$v){
                        $url = $v['mic_link'];
                        if(parse_url($url)['scheme'] == "http"){
                            $link = $v['mic_link'];
                        }else{
                            $files = request()->file('file'.$k);
                            $info = $files->move(ROOT_PATH . 'public' . DS . 'uploads/music');
                            $link = "http://".$_SERVER['HTTP_HOST'].PUBLIC_PATH."uploads/music/".str_replace("\\","/",$info->getSaveName());
                        }
                        $data_count[$k] = array(
                            'rid'=>$reply,
                            'mic_title'=>$v['mic_title'],
                            'mic_count'=>$v['mic_count'],
                            'mic_file'=>$link,
                        );
                    }
                    $result = Db::name('reply_content')->insertAll($data_count);
                    if($result){
                        $this->success('添加成功',url('reply/music_add',array('id'=>$reply)));
                    }
                }
            }else{
                $count = $_POST['count'];
                foreach($count as $k=>$v){
                    $url = $v['mic_link'];
                    if(parse_url($url)['scheme'] == "http"){
                        $link = $v['mic_link'];
                    }else{
                        $files = request()->file('file'.$k);
                        $info = $files->move(ROOT_PATH . 'public' . DS . 'uploads/music');
                        $link = "http://".$_SERVER['HTTP_HOST'].PUBLIC_PATH."uploads/music/".str_replace("\\","/",$info->getSaveName());
                    }
                    $data_count[$k] = array(
                        'rid'=>$id,
                        'mic_title'=>$v['mic_title'],
                        'mic_count'=>$v['mic_count'],
                        'mic_file'=>$link,
                    );
                }
                $result = Db::name('reply_content')->insertAll($data_count);
                if($result){
                    $this->success('修改成功',url('reply/music_add',array('id'=>$id)));
                }
            }
        }else{
            $id =  input('param.id');
            if(!empty($id)){
                $type = 3;
                $reply = Db::name('reply')->where('id',$id)->find();
                $count =  Db::name('reply_content')->where('rid',$id)->select();
            }else{
                $reply = "";
                $count = "";
                $type = 2;
            }
            $this->assign('type',$type);
            $this->assign('count',$count);
            $this->assign('reply',$reply);
            return $this -> fetch();
        }
    }

    /**
     * 语音回复
     */
    public function speech_lis()
    {
        $state = input('param.state');
        $count = input('param.count');
        $where['start'] = array('eq', 3);;
        $where['is_del'] =array('eq', 0);;
        $where['uniacid'] = array('eq', $this->uniacid);
        if (!empty($state)) {
            $where['type'] =array('eq', $state);
        }
        if (!empty($count)) {
            $where['title'] = array('like', "%{$count}%");
        }
        $reply =  Db::name('reply')
            ->where($where)
            ->paginate(10);
        $page = $reply->render();
        $this->assign('page',$page);
        $this->assign('type',1);
        $this->assign('action',$this->action);
        $this->assign('menu',$this->menu);
        $this->assign('reply',$reply);
        $this->assign('state',$state);
        return $this -> fetch();
    }

    public function speech_add()
    {
        if($_POST){
            $id = $_POST['id'];
            $data = array(
                'title'=>$_POST['title'],
                'type'=>$_POST['type'],
                'keywords'=>$_POST['keywords'],
                'speech_title'=>$_POST['speech_title'],
                'addtime'=>date('Y-m-d H:i:s',time()),
                'start'=>3,
                "uniacid"=>$this->uniacid,
            );
            $files = request()->file('file');
//            dump($files);die;
            if($files){
                $info = $files->move(ROOT_PATH . 'public' . DS . 'uploads/voice');
                $link = "/phpstudy/www/http:/platform.tuyuetech.com/".PUBLIC_PATH."uploads/voice/".str_replace("\\","/",$info->getSaveName());
                $a = new wx_public();
                $url = "https://api.weixin.qq.com/cgi-bin/material/add_material?access_token={$a->access_token($this->uniacid)}&type=voice";
                $data_post = array(
                    'media'=>new \CURLFile($link),
                );
                $res = json_decode($this->https_request($url,$data_post));
                if(empty($res->errcode)){
                    $data['media_id'] = $res->media_id;
                }else{
                    $this->error('文件上传错误，错误代码'.$res->errcode);
                }
            }else{
                if(empty($id)){
                    $this->error('未检测到语音文件，请重试!');
                }
            }
            if(empty($id)){
                $result =  Db::name('reply')->insertGetId($data);
                $id =$result;
                $name = "添加";
            }else{
                $result =  Db::name('reply')->where('id',$id)->update($data);
                $name = "修改";
            }

            if($result){
                $this->success($name.'成功',url('reply/speech_add',array('id'=>$id)));
            }else{
                $this->error($name.'失败');
            }

        }else{
            $id = input('param.id');
            if(!empty($id)){
                $type = 3;
                $reply = Db::name('reply')->where('id',$id)->find();

            }else{
                $reply = "";
                $type = 2;
            }
            $this->assign('type',$type);
            $this->assign('reply',$reply);
            return $this -> fetch();
        }
    }

    /**
     * 视频回复
     */
    public function video_lis()
    {
        $state = input('param.state');
        $count = input('param.count');
        $where['start'] = array('eq', 6);;
        $where['is_del'] =array('eq', 0);;
        $where['uniacid'] = array('eq', $this->uniacid);
        if (!empty($state)) {
            $where['type'] =array('eq', $state);
        }
        if (!empty($count)) {
            $where['title'] = array('like', "%{$count}%");
        }
        $reply =  Db::name('reply')
            ->where($where)
            ->paginate(10);
        $page = $reply->render();
        $this->assign('page',$page);
        $this->assign('type',1);
        $this->assign('reply',$reply);
        $this->assign('action',$this->action);
        $this->assign('menu',$this->menu);
        $this->assign('state',$state);
        return $this -> fetch();
    }
    public function video_add()
    {
        if($_POST){
//            $id = $_POST['id'];
            $id = null;
            $data = array(
                'title'=>$_POST['title'],
                'type'=>$_POST['type'],
                'keywords'=>$_POST['keywords'],
                'video_title'=>$_POST['video_title'],
                'video_count'=>$_POST['video_count'],
                'addtime'=>date('Y-m-d H:i:s',time()),
                'start'=>6,
                "uniacid"=>$this->uniacid,
            );
            $files = request()->file('file');
//            dump($files);die;
            if($files){
                $info = $files->move(ROOT_PATH . 'public' . DS . 'uploads/video');
                $link = "/phpstudy/www/http:/platform.tuyuetech.com".PUBLIC_PATH."uploads/video/".str_replace("\\","/",$info->getSaveName());
                $a = new wx_public();
                $url = "https://api.weixin.qq.com/cgi-bin/material/add_material?access_token={$a->access_token($this->uniacid)}&type=video";
                $data_post = array(
                    'media'=>new \CURLFile($link),
                    'description'=>json_encode(array(
                        'title'=>$_POST['video_title'],
                        'introduction'=>$_POST['video_count'],
                    ),true)
                );
                $res = json_decode($this->https_request($url,$data_post));
                if(empty($res->errcode)){
                    $data['media_id'] = $res->media_id;
                }else{
                    $this->error('文件上传错误，错误代码'.$res->errcode);
                }
            }else{
                if(empty($id)){
                    $this->error('未检测到视频文件，请重试!');
                }
            }
            if(empty($id)){
                $result =  Db::name('reply')->insertGetId($data);
                $id =$result;
                $name = "添加";
            }else{
                $result =  Db::name('reply')->where('id',$id)->update($data);
                $name = "修改";
            }

            if($result){
                $this->success($name.'成功',url('reply/speech_add',array('id'=>$id)));
            }else{
                $this->error($name.'失败');
            }

        }else{
            $id = input('param.id');
            if(!empty($id)){
                $type = 3;
                $reply = Db::name('reply')->where('id',$id)->find();
            }else{
                $reply = "";
                $type = 2;
            }
            $this->assign('type',$type);
            $this->assign('reply',$reply);
            return $this -> fetch();
        }
    }

    //    图文回复
    public function reply_lis()
    {
        $state = input('param.state');
        $count = input('param.count');
        $where['start'] = array('eq', 4);;
        $where['is_del'] =array('eq', 0);;
        $where['uniacid'] = array('eq', $this->uniacid);
        if (!empty($state)) {
            $where['type'] =array('eq', $state);
        }
        if (!empty($count)) {
            $where['title'] = array('like', "%{$count}%");
        }
        $reply =  Db::name('reply')
            ->where($where)
            ->paginate(10);
        $page = $reply->render();
        $this->assign('page',$page);
        $this->assign('type',1);
        $this->assign('action',$this->action);
        $this->assign('menu',$this->menu);
        $this->assign('reply',$reply);
        $this->assign('state',$state);
        return $this -> fetch();
    }
    public function reply_add()
    {
        if($_POST){
            $id = $_POST['id'];
            $data = array(
                'title'=>$_POST['title'],
                'type'=>$_POST['type'],
                'keywords'=>$_POST['keywords'],
                'uniacid'=>$this->uniacid,
                'start'=>4,
                'addtime'=>date("Y-m-d H:i:s",time()),
                'time'=>time()
            );
            if(empty($id)){
                $result = Db::name('reply')->insertGetId($data);
                $name = "添加";
            }else{
                $result = Db::name('reply')->where('id',$id)->update($data);
                Db::name('menu')->where('rid',$id)->delete();
                $name = "修改!";
            }
            if($result){
                $form = $_POST['form'];
                $i = 1;
                foreach($form as $k=>$v){
                    if(!empty($v)){
                        foreach($v as $k1=>$v1){
                            $data1[$i] = array(
                                'rid'=>$result,
                                'group'=>$k,
                                'title'=>$v1['title'],
                                'author'=>$v1['zuozhe'],
                                'describe'=>$v1['miaoshu'],
                                'url'=>$v1['url'],
                                'type'=>$k1,
                            );
                            $files = request()->file("pic_{$k}_{$k1}");
                            if($files){
                                $info = $files->move(ROOT_PATH . 'public' . DS . 'uploads/images');
                                $data1[$i]['pic_url'] = $info->getSaveName();
//                                $data['file_name'] = $info->getinfo()['name'];
                            }else{
                                $this->error('图片上传失败，请重试!');
                            }
                            $i++;
                        }
                    }
                }
                $result1 = Db::name('reply_news')->insertAll($data1);
                if($result1){
                    $this->success($name.'成功!',url('wechat/reply/reply_lis'));
                }else{
                    $this->success($name.'失败!',url('wechat/reply/reply_lis'));
                }
            }
        }else{
            $id = input('param.id');
            if(!empty($id)){
                $type = 3;
                $reply = Db::name('reply')->where('id',$id)->find();
                $group =  Db::name('reply_news')->where('rid',$id)->group('group')->field('group')->select();
                foreach($group as $k=>$v){
                    $select = Db::name('reply_news')->where('group',$v['group'])->where('rid',$id)->select();
                    foreach($select as $k1=>$v1){
                        $data[$k][$v1['type']] = array(
                            'title'=>$v1['title'],
                            'zuozhe'=>$v1['author'],
                            'pic'=>$v1['pic_url'],
                            'miaoshu'=>$v1['describe'],
                            'url'=>$v1['url'],
                        );
                    }
                }
                $data =  json_encode($data,true);
            }else{
                $reply = "";
                $type = 2;
                $data = null;
            }
            $this->assign('data',$data);
            $this->assign('type',$type);
            $this->assign('reply',$reply);
            return $this -> fetch();

        }
    }

    //    系统回复
    public function system()
    {
        $uniacid = $this->uniacid;
        if($_POST){
            $find = Db::name('account_config')->where('uniacid',$uniacid)->find();
            $data = array(
                'welcome'=>$_POST['welcome'],
                'default'=>$_POST['default'],
            );
            if($find){
                $result =  Db::name('account_config')->where('uniacid',$uniacid)->update($data);
            }else{
                $data['uniacid'] = $this->uniacid;
                $result =  Db::name('account_config')->insert($data);
            }
            if($result){
                $this->success('系统回复修改成功!',url('reply/system'));
            }else{
                $this->error('系统回复修改失败!',url('reply/system'));
            }
        }else{
            $config = Db::name('account_config')->where('uniacid',$uniacid)->find();
            $this->assign('config',$config);
            return $this -> fetch();
        }
    }
    private function https_request($url, $data = null)
    {
        $curl = curl_init();
        if(class_exists('\CURLFile')){
            curl_setopt($curl,CURLOPT_SAFE_UPLOAD,true);
//            $file = "/phpstudy/www/http:/platform.tuyuetech.com/public/uploads/voice/20171207/61d773325fdb2c3907837956477762e9.mp3";
        }else{
            curl_setopt($curl,CURLOPT_SAFE_UPLOAD,false);
//            $file = "/phpstudy/www/http:/platform.tuyuetech.com/public/uploads/voice/20171207/61d773325fdb2c3907837956477762e9.mp3";
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }


    public function is_del(){
        $id = input('param.id');
        $result = Db::name('reply')->where('id',$id)->update(['is_del'=>1]);
        if($result){
            $this->success('删除成功!');
        }else{
            $this->success('删除失败!');
        }
    }
}
