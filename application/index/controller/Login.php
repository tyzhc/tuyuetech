<?php
namespace app\index\controller;
use think\Db;
use think\Log;
use think\Cookie;
use think\View;
use think\Request;
use think\Session;
use think\User_config;
use think\Controller;
use app\index\model\User;
use \code;

header('Content-type:text');
define("TOKEN", "weixin");


class Login extends Controller
{
    public function index()
    {
        return $this -> fetch();
    }

    public function login(){
        $user_name = $_POST['user_name'];
        $user_passwd = $_POST['user_passwd'];
        $list = Db::name('user')->where("user_name = " . "'" . $user_name . "'")->find();
        if ($list) {
            $user = new User();
            $user_passwd = $user->password($user_passwd);
            if ($list['passwd'] != $user_passwd) {
                $this->error('密码有误!');
            }elseif($list['avtivity'] == 0){
                $this->error('账号尚未生效，请联系管理员!');
            } else {
                $request = Request::instance();
                $request->ip();
                $list['ip'] = $request->ip();
                Session::set('loginData', $list);
                Cookie::set('loginData', $list, 3800);
//                $user->add_event_log($list,'用户登录',1);
                redirect()->restore();
                $this->success('登录成功', 'Index/index');
            }
        } else {
            return $this->error('用户不存在!');
        }
    }
}
?>
