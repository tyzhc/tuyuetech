<?php
namespace app\wxopen\controller;
use think\Db;
use wx_api;
use think\Controller;
use think\log;
use \php_log;
use \wxAuthLib;
use \wx_public;
class Index extends Controller
{
    private $wxopen_appid;

    public function __construct()
    {
        $this->wxopen_appid = "wx9cefb7405a465206";
    }
    public function index()
    {
        $wxInfoLib = new wxAuthLib();
        $AuthCode = $wxInfoLib->getPreAuthCode();
        $redirect_uri = urlencode("http://platform.tuyuetech.com/index.php/wxopen/index/auth");
        $url = "https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid={$this->wxopen_appid}&pre_auth_code={$AuthCode}&redirect_uri={$redirect_uri}";
        $this->assign('url',$url);
        return $this -> fetch();
    }

    public function auth(){
        $auth_code = input('param.auth_code');
        if(!empty($auth_code)){
            $wxInfoLib = new wxAuthLib();
            $res = $wxInfoLib->getAuthorizationAppid($auth_code);
            if($res!=""){
                $appid = $res['authorization_info']['authorizer_appid'];
                $data['key']=$appid;
                $power = "";
                foreach($res['authorization_info']['func_info'] as $k=>$v){
                    $power .= $v['funcscope_category']['id'].',';
                }
                $data['power'] = rtrim($power,',');
                $data['auth_refresh_token'] = $res['authorization_info']['authorizer_refresh_token'];
                $result = Db::name('account_wechats')->insertGetId($data);
                if($result){
                    $time = time()+7000;
                    Db::name('account_token')->insert(['uniacid'=>$result,'sccess_token'=>$res['authorization_info']['authorizer_access_token'],'time'=>$time]);
                    $res_info=$wxInfoLib->getAuthorizinfoAppid($appid);
                    if($res_info!=""){
                        $info_data['name']=$res_info['authorizer_info']['nick_name'];
                        $info_data['head_img']=$res_info['authorizer_info']['head_img'];
                        $info_data['level']=$res_info['authorizer_info']['service_type_info']['id'];
                        $info_data['original']=$res_info['authorizer_info']['user_name'];
                        $info_data['level_info']=$res_info['authorizer_info']['verify_type_info']['id'];
                        $info_data['qrcode_url']=$res_info['authorizer_info']['qrcode_url'];
                        if(!empty($res_info['authorizer_info']['alias'])){
                            $info_data['account']=$res_info['authorizer_info']['alias'];
                        }
                        $result_info = Db::name('account_wechats')->where('uniacid',$result)->update($info_data);
                        if($result_info){
                            $this->success('公众号授权成功!','index');
                        }else{
                            $this->error('公众号授权失败!');
                        }
                    }else{
                        $this->error('公众号授权失败!');
                    }
                }else{
                    $this->error('公众号授权失败!');
                }
            }else{
                $this->error('公众号授权失败!');
            }

        }else{
            $this->error('公众号授权失败!');
        }

    }

    public function ceshi(){
        $data = Db::name('account_token')->where('id',7)->find();
       $url = "https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token={$data['sccess_token']}";
        $wxInfoLib = new wxAuthLib();
        $res = $wxInfoLib->httpGet($url);
        dump($res);
    }

    private function httpPost($url,$data) {

        $ch = curl_init ();
        curl_setopt ($ch, CURLOPT_URL, $url );//地址
        curl_setopt ($ch, CURLOPT_POST, 1 );//请求方式为post
        curl_setopt ($ch, CURLOPT_HEADER, 0 );//不打印header信息
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1 );//返回结果转成字符串
        curl_setopt ($ch, CURLOPT_POSTFIELDS,$data);//post传输的数据。
        $return = curl_exec ($ch );
        curl_close ($ch );

        return $return;

    }

}
