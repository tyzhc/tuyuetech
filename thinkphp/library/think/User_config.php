<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;
use think\Controller;

class User_config extends Controller
{

    public function __construct()
    {
        parent::__construct();
        $loginData = Session::get('loginData');
        if(empty($loginData)){
            $this->redirect('index/login/index')->remember();
//            $this->error('','index/login/index');
        }
    }

}
