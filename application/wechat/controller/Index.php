<?php

namespace app\wechat\controller;

use think\Db;
use wx_api;
use think\Controller;
use think\log;
use \php_log;
use \wx_public;
use wxAuthLib;
use weekdate;
use think\User_config;
use think\Session;
use con_public;

class Index extends User_config
{
    private $wxopen_appid;

    public function __construct()
    {
        parent::__construct();
        $this->wxopen_appid = "wx9cefb7405a465206";
        $request=  \think\Request::instance();
        $this->assign('action',$request->action());
        $this->assign('menu',$request->controller());
    }

    /**
     * 测试用
     */
    public function index()
    {
        dump($_SERVER['HTTP_HOST']);die;
       $a = new con_public();
       dump($a->getImage('http://mmbiz.qpic.cn/mmbiz_jpg/G2a9VdDOvbribWrtK4skWZ7udJ2BOPMf8t8uGFgfibIrvIRxeadOib4gL4AsKuN16VT6EoRH8a1LbmV6KCoJ87sAw/0'));die;
    }

    /**
     * 测试用
     */
    public function wechat_list()
    {
        $uniacid = input('param.uniacid');
        $this->assign('uniacid', $uniacid);
        return $this->fetch();
    }

    /**
     * 测试用
     */
    public function wechat_user()
    {
//        $uniacid = input('param.uniacid');
        $a = new wx_public();
        $b = $a->get_user_list(1);
        dump($b);
    }

    /**
     * 公众号列表页
     */
    public function sign()
    {
        session('uniacid',null);
        $type = input('param.type');
        $count = input('param.count');
        if (!empty($type)) {
            $where['a.type'] = array('eq', $type);
        }
        if (!empty($count)) {
            $where['a.name'] = array('like', "%{$count}%");
        }
        if (empty($where)) {
            $where = "1=1";
        }

        $wxInfoLib = new wxAuthLib();
        $AuthCode = $wxInfoLib->getPreAuthCode();
        $redirect_uri = urlencode("http://platform.tuyuetech.com/index.php/wechat/index/auth");
        $url = "https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid={$this->wxopen_appid}&pre_auth_code={$AuthCode}&redirect_uri={$redirect_uri}";
        $wechats = Db::name('account_wechats')
            ->alias('a')
            ->where($where)
            ->paginate(10);
        $page = $wechats->render();
        $data = $wechats->all();
        foreach($data as $k=>$v){
            $find  = Db::name('account_personnel')->where('uniacid',$v['uniacid'])->where('date',date('Y-m-d', time()))->field('follow')->find();
            if($find){
                $data[$k]['follow'] = $find['follow'];
            }else{
                $data[$k]['follow'] = null;
            }
        }
        $this->assign('page', $page);
        $this->assign('type', $type);
        $this->assign('url', $url);
        $this->assign('wechats', $data);
        return $this->fetch();
    }

    /**
     * 授权登录公众号回调页面
     */
    public function auth()
    {
        $auth_code = input('param.auth_code');
        if (!empty($auth_code)) {
            $wxInfoLib = new wxAuthLib();
            $res = $wxInfoLib->getAuthorizationAppid($auth_code);
            if ($res != "") {
                $appid = $res['authorization_info']['authorizer_appid'];
                $data['key'] = $appid;
                $find = Db::name('account_wechats')->where('key', $appid)->where('is_del', 0)->find();
                if ($find) {
                    $this->success('检测到次公众号已经绑定本平台,如有需要,请删除后重新绑定!','wechat/index/sign');
                }
                $power = "";
                foreach ($res['authorization_info']['func_info'] as $k => $v) {
                    $power .= $v['funcscope_category']['id'] . ',';
                }
                $data['power'] = rtrim($power, ',');
                $data['auth_refresh_token'] = $res['authorization_info']['authorizer_refresh_token'];
                $result = Db::name('account_wechats')->insertGetId($data);
                if ($result) {
                    $time = time() + 7000;
                    Db::name('account_token')->insert(['uniacid' => $result, 'access_token' => $res['authorization_info']['authorizer_access_token'], 'time' => $time]);
                    $res_info = $wxInfoLib->getAuthorizinfoAppid($appid);
                    if ($res_info != "") {
                        $info_data['name'] = $res_info['authorizer_info']['nick_name'];
                        $info_data['head_img'] = $res_info['authorizer_info']['head_img'];
                        $info_data['level'] = $res_info['authorizer_info']['service_type_info']['id'];
                        $info_data['original'] = $res_info['authorizer_info']['user_name'];
                        $info_data['level_info'] = $res_info['authorizer_info']['verify_type_info']['id'];
//                        $info_data['qrcode_url'] = $res_info['authorizer_info']['qrcode_url'];
                        $public = new con_public();
                        $img = $public->getImage($res_info['authorizer_info']['qrcode_url'],'./public/update/account_qrcode',$res_info['authorizer_info']['user_name'].'jpg');
                        $info_data['qrcode_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/public/update/account_qrcode/'.$res_info['authorizer_info']['user_name'].'jpg';
                        $info_data['type'] = 1;
                        $info_data['state'] = 1;
                        if (!empty($res_info['authorizer_info']['alias'])) {
                            $info_data['account'] = $res_info['authorizer_info']['alias'];
                        }
                        $result_info = Db::name('account_wechats')->where('uniacid', $result)->update($info_data);
                        if ($result_info) {
                            $public = new wx_public();
                            $public->get_user_list($result);
                            $this->success('公众号授权成功!', 'index/sign');
                        }
                    }
                }
            }

        }
        $this->error('公众号授权失败!','wechat/index/sign');

    }

    /**
     *公众号删除
     */

    public function is_del()
    {

    }

    /**
     * 公众号管理首页
     */
    public function basic()
    {
        $uniacid = input('param.uniacid');
        Session::set('uniacid', $uniacid);
        $wechat = Db::name('account_wechats')->where('uniacid', $uniacid)->find();
        $today_follow = Db::name('account_personnel')->where('uniacid', $uniacid)->where('date', date('Y-m-d', time()))->find();
        $lase_follow = Db::name('account_personnel')->where('uniacid', $uniacid)->where('date', date('Y-m-d', time() - 86400))->find();
        if (!$today_follow) {
            Db::name('account_personnel')->insert(['uniacid' => $uniacid, 'date' => date('Y-m-d', time()), 'total' => $wechat['total']]);
            $today_follow = array(
                'total' => $wechat['total'],
                'follow' => 0,
                'cancel' => 0,
                'growth' => 0,
            );
        } else {
            $today_follow['growth'] = $today_follow['follow'] - $today_follow['cancel'];
        }
        if (!$lase_follow) {
            $lase_follow = array(
                'total' => $wechat['total'],
                'follow' => 0,
                'cancel' => 0,
                'growth' => 0,
            );
        } else {
            $lase_follow['growth'] = $lase_follow['follow'] - $lase_follow['cancel'];
        }


        $public = new con_public();
        $follow_num = $public->follow_num($uniacid);
        $reply_num = $public->replay_num($uniacid,1);
        $this->assign('wechat', $wechat);
        $this->assign('uniacid', $uniacid);
        $this->assign('reply_num', $reply_num);
        $this->assign('follow_num', $follow_num);
        $this->assign('today_follow', $today_follow);
        $this->assign('lase_follow', $lase_follow);
        return $this->fetch();
    }


    public function ajax_tj(){
        $public = new con_public();
        $uniacid = $_POST['uniacid'];
        $type = $_POST['type'];
        $reply_num = $public->replay_num($uniacid,$type);
        return $reply_num;
    }

    //    权限
    public function authority()
    {
        if($_POST){
            $count = $_POST['count'];
            $uniacid = $_POST['uniacid'];
            if($count=="[]"){
                $count = null;
            }
            $result = Db::name('account_wechats')->where('uniacid',$uniacid)->update(['authority'=>$count]);
            if($result){
                return json_encode(array('type'=>1,'success'=>'权限修改成功'),true);
            }else{
                return json_encode(array('type'=>0,'errcode'=>'权限修改失败'),true);
            }
        }else{
            $id = input('param.id');
            $menus = Db::name('account_menus')->where('is_del',0)->where('fid',0)->select();
            foreach($menus as $k=>$v){
                $menus_fid = Db::name('account_menus')->where('is_del',0)->where('fid',$v['id'])->select();
                $menus[$k]['count'] = $menus_fid;
            }
            $verification = new \verification();
            $authority = $verification->wechat_authority($id);
            $this->assign('authority', $authority);
            $this->assign('menus', $menus);
            $this->assign('id', $id);
            return $this -> fetch();
        }
    }
//    账号操作员列表
    public function operator()
    {
        $id = input('param.id');
        if(Db::name('account_istration')->where('uniacid',$id)->where('uid',1)->find()){
            $admin = 1;
        }else{
            $admin = 0;
        }
        $istration = Db::name('account_istration')->where('uniacid',$id)->where('is_del',0)->paginate(10);
        $page = $istration->render();
        $this->assign('istration', $istration);
        $this->assign('admin', $admin);
        $this->assign('page', $page);
        return $this->fetch();
    }



}
