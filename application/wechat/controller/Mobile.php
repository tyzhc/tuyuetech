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

class Mobile extends Controller
{
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
    //    手机版文章列表
    public function article()
    {
        $id = input('param.id');
        $uniacid = input('param.uniacid');
        $article =  Db::name('article')->where('id',$id)->where('uniacid',$uniacid)->find();
        if($article){
            if($article['state']==1){
                $this->error('文章已被删除!');
            }else{
                $this->assign('article',$article);
                return $this -> fetch();
            }
        }else{
            $this->error('文章不存在或已被删除!');
        }
    }
}

