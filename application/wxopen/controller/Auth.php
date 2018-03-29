<?php

namespace app\wxopen\controller;

use think\Db;
use wx_api;
use think\Controller;
use think\log;
use \php_log;

class Auth extends Controller
{
    protected $appid = "";
    protected $secret = "";
    protected $token = "";
    protected $encodingAesKey = "";

    public function __construct()
    {
        parent::__construct();
        $this->appid = "wx9cefb7405a465206";
        $this->secret = "c2f0313091a0b753b614d9aed4f73e47";
        $this->token = "omJNpZEhZeHj1ZxFECKkP48B5VFbk1HP";
        $this->encodingAesKey = "H0VEFBYRYsSSsTjSMkoTQiV6CMLLjIoNP6hBIkhMi54";
    }


    //验证
    public function valid($echostr)
    {
        if ($this->checkSignature()) {
            echo $echostr;
            exit;
        } else {
            exit(0);
        }
    }

    public function index()
    {
        //初始化配置信息
//        $this->setParmeter();
        //微信服务器发送的随机字符串
        $echostr = input('get.echostr');
        if ($echostr) {
            $this->valid($echostr);
        } else {
            //相应客户端
            $this->responseMsg();


        }
    }

    /**
     * 校验微信是否是微信端
     * @author vaey
     * @return [type] [description]
     */
    private function checkSignature()
    {
        if (!($this->token)) {
            echo "token必填";
            exit(0);
        }
        $signature = input('get.signature');
        $timestamp = input('get.timestamp');
        $nonce = input('get.nonce');
        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce);
        // 字典排序
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 模拟提交数据，获得返回值
     * @author vaey
     * @param  [type] $url  [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function https_request($url, $data = null)
    {
        $curl = curl_init();
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

    //接受用户请求
    private function responseMsg()
    {
        //获取返回的post数据包
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)) {
            $signature = input('param.signature');
            $timestamp = input('param.timestamp');
            $nonce = input('param.nonce');
            $encrypt_type = input('param.encrypt_type');
            $msg_signature = input('param.msg_signature');
            $a = new wx_api($this->token, $this->encodingAesKey, $this->appid);
            $decryptMsg = "";  //解密后的明文
            $Obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $postStr = $a->decryptMsg($msg_signature, $timestamp, $nonce, $postStr, $decryptMsg, 1);
            $postStr = $decryptMsg;
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $INFO_TYPE = trim($postObj->InfoType);
            switch ($INFO_TYPE){
                case 'component_verify_ticket':    // 授权凭证
                    $component_verify_ticket = $postObj->ComponentVerifyTicket;
                    Db::name('verify_ticket')->where('id', 1)->update(['ticket'=>$component_verify_ticket,'time'=>time()]);
                    break;
                case 'unauthorized':               // 取消授权XQ
                    break;
                case 'authorized':                 // 授权
                    break;
                case 'updateauthorized':           // 更新授权
                    break;
            }
            echo "success";
        } else {
            echo "错误！";
        }
    }
}
