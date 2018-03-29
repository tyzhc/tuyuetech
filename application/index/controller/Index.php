<?php
namespace app\index\controller;
use \wx_public;
use think\Db;
use think\Log;
use think\Controller;
use think\User_config;
use weather;

header('Content-type:text');
define("TOKEN", "weixin");


class Index extends User_config
{

    public function __construct()
    {
        parent::__construct();
        $request=  \think\Request::instance();
        $this->assign('action',$request->action());
        $this->assign('menu',$request->controller());
    }
    public function index()
    {
        header("Content-type: text/html; charset=utf-8");
//        $tq = new weather();
//        $weather = $tq->WeatherHtml();
//        $this->assign('weather',$weather);
        return $this -> fetch();
    }
    public function sign()
    {
//        $wechats = Db::name('account_wechats')->select();
//        $this->assign('wechats',$wechats);
        return $this -> fetch();
    }
    public function changsign()
    {
        return $this -> fetch();
    }
    public function addsign()
    {
        return $this -> fetch();
    }

    /**
     * @return mixed
     */
    public function basic()
    {
        return $this -> fetch();
    }


//    图文回复
    public function reply_lis()
    {
        return $this -> fetch();
    }
    public function reply_add()
    {
        return $this -> fetch();
    }
    public function reply_chang()
    {
        return $this -> fetch();
    }

//    自定义菜单
    public function custom_menu()
    {
        return $this -> fetch();
    }
    public function custom_menus()
    {
        return $this -> fetch();
    }
//    文章管理
    public function article_lis()
    {
        return $this -> fetch();
    }
    public function article_add()
    {
        return $this -> fetch();
    }

//    粉丝管理

    public function follower()
    {
        return $this -> fetch();
    }
//    用户列表
    public function users()
    {
        return $this -> fetch();
    }
    public function add_users()
    {
        return $this -> fetch();
    }
    public function users_chang()
    {
        return $this -> fetch();
    }
    public function users_changs()
    {
        return $this -> fetch();
    }
    public function users_saw()
    {
        return $this -> fetch();
    }
    //    通讯录
    public function mail()
    {
        return $this -> fetch();
    }
    //    数据统计
    public function rule()
    {
        return $this -> fetch();
    }
    public function keyword()
    {
        return $this -> fetch();
    }
//    权限
    public function authority()
    {
        if($_POST){
            dump('sssssss');die;
            $a = $_POST['cs'];
            dump($a);
        }else{
            return $this -> fetch();
        }
    }
//    账号操作员列表
    public function operator()
    {
        return $this->fetch();
    }
    //    手机版文章列表
    public function article()
    {
        return $this -> fetch();
    }
}
?>
