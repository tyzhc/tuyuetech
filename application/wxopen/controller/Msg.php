<?php

namespace app\wxopen\controller;

use think\Db;
use wx_api;
use think\Controller;
use think\log;
use \php_log;
use \wxAuthLib;
use wx_public;

class Msg extends Controller
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

    /**
     * 自定义菜单
     */
//    private function setMenu()
//    {
//        //获取access_token
//        return 1;
//        $access_token = getAccessToken($this->appid, $this->secret);
//        //定义菜单
//        $data = Db::name('WxMenu')->select();
//        $tree = list_to_tree($data, 'id', 'parent');
//        //解析菜单
//        $menu = tree_to_json_menu($tree);
//        //将菜单发送给微信服务器
//        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $access_token;
//        $result = https_request($url, $menu);
//    }

    //接受用户请求
    private function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $b = new php_log();
        $wxInfoLib = new wxAuthLib();
        $public = new wx_public();
        if (!empty($postStr)) {
//            $signature = input('param.signature');
            $timestamp = input('param.timestamp');
            $nonce = input('param.nonce');
//            $encrypt_type = input('param.encrypt_type');
            $msg_signature = input('param.msg_signature');
            $a = new wx_api($this->token, $this->encodingAesKey, $this->appid);
            $msg = "";  //解密后的明文
            $errCode = $a->decryptMsg($msg_signature, $timestamp, $nonce, $postStr, $msg);
            $b->logs($msg);
            if($errCode == 0){
                $data = $wxInfoLib->xmlToArr ( $msg );
                $fromUsername = $data['FromUserName'];
                $toUsername = $data['ToUserName'];
                $msgType = trim($data['MsgType']);
                //默认返回
                $sendtime = time();
                $sendtextTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
                $sendMsgType = "text";
                $sendContentStr = "什么都没发生!";
                $sendResultStr = sprintf($sendtextTpl, $fromUsername, $toUsername, time(), $sendContentStr);
                //默认返回
                $encryptMsg = "";
                //事件
                if($msgType == 'event')
                {
                    $event = trim($data['Event']);
                    //全网发布
                    if($toUsername == "gh_3c884a361561")
                    {
                        $sendMsgType = "text";
                        $sendContentStr = $event."from_callback";
                        $sendResultStr = sprintf($sendtextTpl, $fromUsername, $toUsername, time(), $sendContentStr);
                        $encryptMsg = "";
                        $errorcode = $a->encryptMsg($sendResultStr, $sendtime, $nonce, $encryptMsg);
                        echo $encryptMsg;exit;
                    }
                    //全网发布

                    if($event == "subscribe")
                    {
                        $public->subscribe($toUsername,$fromUsername);
                        $content = "欢迎关注途阅微平台123 ";
                        $sendResultStr = sprintf($sendtextTpl, $fromUsername, $toUsername, time(), $content);
                        $encryptMsg = "";
                        $errorcode = $a->encryptMsg($sendResultStr, $sendtime, $nonce, $encryptMsg);
                        echo $encryptMsg;exit;
                    }

                    if($event == "unsubscribe")
                    {
                        $public->unsubscribe($toUsername,$fromUsername);
                        exit;
                    }
//                    elseif($event == "unsubscribe")
//                    {
////                        $wxInfoLib->update_user($fromUsername, $toUsername, 0);
//                        return "";
//                    }
//                    elseif($event == "SCAN"){
////                        //扫描二维码信息
////
////                        //发送关注消息
////                        //判断用户是否通过ticket扫二维码
////
////                        if(trim($data['Ticket']) != ""){
////                            $is_scan = False;
////                            $is_scan = $wxInfoLib->get_scan_num(trim($data['Ticket']));
////                        }
////                        $resultStr = $wxInfoLib->get_subscribe($fromUsername, $toUsername);
////                        $encryptMsg = $this->encode_msg($resultStr, $timeStamp, $nonce, $encryptMsg);
//                        return $encryptMsg;
//                    }elseif($event == "MASSSENDJOBFINISH")
//                    {
////                        $mass['MsgID'] = $data['MsgID'];
////                        $mass['Status'] = $data['Status'];
////                        $mass['TotalCount'] = $data['TotalCount'];
////                        $mass['FilterCount'] = $data['FilterCount'];
////                        $mass['SentCount'] = $data['SentCount'];
////                        $mass['ErrorCount'] = $data['ErrorCount'];
////                        $wxInfoLib->update_mass_msg($mass, $toUsername);
//                    }
                    //文本消息
                }elseif($msgType == "text"){
                    $keyword = trim($data['Content']);

                    //全网发布
                    if($toUsername == "gh_3c884a361561" && $keyword == "TESTCOMPONENT_MSG_TYPE_TEXT")
                    {
                        $sendMsgType = "text";
                        $sendContentStr = "TESTCOMPONENT_MSG_TYPE_TEXT_callback";
                        $sendResultStr = sprintf($sendtextTpl, $fromUsername, $toUsername, time(), $sendContentStr);
//                        $b->logs('33'.$sendResultStr);
////                        $encryptMsg = $this->encode_msg($sendResultStr, $timeStamp, $nonce, $encryptMsg);
                        $encryptMsg = "";
                        $errorcode = $a->encryptMsg($sendResultStr, $sendtime, $nonce, $encryptMsg);
                        echo $encryptMsg;exit;
                    }

                    if($toUsername == "gh_3c884a361561" && strpos($keyword, "QUERY_AUTH_CODE") > -1)
                    {
                        $ticket = str_replace("QUERY_AUTH_CODE:", "", $keyword);
                        $sendcus['content'] = $ticket."_from_api";

                        $res = $wxInfoLib->getAuthorizationAppid($ticket); //使用授权码换取公众号的接口调用凭据和授权信息
//                        $this->helper->set_php_file(dirname(__FILE__)."/test.php", json_encode($res));
                        if($res != "")
                        {
                            $authorizer_access_token = $res['authorization_info']['authorizer_access_token'];
                            $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$authorizer_access_token;
                            $curlPost['touser'] = $fromUsername;
                            $curlPost['msgtype'] = "text";

                            $curlPost['text']['content'] = $ticket."_from_api";
                            $curlPost = json_encode($curlPost);
                            $res = json_decode($wxInfoLib->httpPost($curlPost, $url), true);
                            //$this->helper->set_php_file(dirname(__FILE__)."/test.php", $authorizer_access_token);

                        }
                        return "";
                    }
                    $reply = $public->reply($keyword,$toUsername,$fromUsername);
                    $b->logs($reply);
                    if(!empty($reply)){
//                        $content = "欢迎关注途阅微平台 ";
//                        $sendResultStr = sprintf($sendtextTpl, $fromUsername, $toUsername, time(), $content);
                        $encryptMsg = "";
                        $errorcode = $a->encryptMsg($reply, $sendtime, $nonce, $encryptMsg);
                        echo $encryptMsg;exit;
                    }else{
                        echo '';exit;
                    }

                    if(!empty( $keyword ))
                    {
                        $resultStr = $wxInfoLib->get_keyword($keyword, $fromUsername, $toUsername);
                        if(empty($resultStr))
                        {
                            $resultStr = $wxInfoLib->get_reply($fromUsername, $toUsername);
                        }
                        $encryptMsg = $a->encryptMsg($resultStr, $sendtime, $nonce, $encryptMsg);
//                        $encryptMsg = $this->encode_msg($resultStr, $timeStamp, $nonce, $encryptMsg);
                        return $encryptMsg;
                    }else{
                        $encryptMsg = $a->encryptMsg($sendResultStr, $sendtime, $nonce, $encryptMsg);
//                        $encryptMsg = $this->encode_msg($sendResultStr, $timeStamp, $nonce, $encryptMsg);
                        return $encryptMsg;
                    }
                    //图片消息
                }
            }else{
                return $errCode;
            }
        } else {
            echo "错误！";
        }
    }

    public function encode_msg($resultStr, $timeStamp, $nonce, $encryptMsg)
    {
        $a = new wx_api($this->token, $this->encodingAesKey, $this->appid);
        $encryptMsg = "";
        $serrCode = $a->encryptMsg($resultStr, $timeStamp, $nonce, $encryptMsg);
        if($serrCode == 0)
        {
            return $encryptMsg;
        }else
        {
            return $serrCode;
        }
    }

    private function transmitText($object, $content, $flag = 0)
    {
        $timestamp = input('param.timestamp');
        $nonce = input('param.nonce');
        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $resultStr;
    }

}
