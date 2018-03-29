<?php

namespace app\wxopen\controller;

use think\Db;
use wx_api;
use think\Controller;
use think\log;
use \php_log;
use \wxAuthLib;

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
        $b->logs($postStr);
        if (!empty($postStr)) {
            $signature = input('param.signature');
            $timestamp = input('param.timestamp');
            $nonce = input('param.nonce');
            $encrypt_type = input('param.encrypt_type');
            $msg_signature = input('param.msg_signature');
            $a = new wx_api($this->token, $this->encodingAesKey, $this->appid);
            $decryptMsg = "";  //解密后的明文
            $Obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $postStr = $a->decryptMsg($msg_signature, $timestamp, $nonce, $postStr, $decryptMsg);
            $b->logs($decryptMsg);
            $postStr = $decryptMsg;
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $event = $postObj->Event;
            $MsgType = $postObj->MsgType;
            $this->receiveEvent($postObj);
//            if($MsgType=="text"){
//                $this->receiveText($postObj);
//            }else{
//                $this->receiveEvent($postObj);
//            }
        } else {
            echo "错误！";
        }
    }

////接收文本消息
//    private function receiveText($object)
//    {
//        $keyword = trim($object->Content);
//        if (strstr($keyword, "TESTCOMPONENT_MSG_TYPE_TEXT")){
//            $content = $keyword."_callback";
//        }
//
//        if(is_array($content)){
//            $result = $this->transmitNews($object, $content);
//        }else{
//            $result = $this->transmitText($object, $content);
//        }
//        return $result;
//    }



    private function receiveEvent($object)
    {
        $content = "";
        switch ($object->Event) {
            case "subscribe":
                $content = "欢迎关注途阅微平台 ";
                break;
            case "CLICK":
                switch ($object->EventKey) {
                    default:
                        $content = "点击菜单：" . $object->EventKey;
                        break;
                }
                break;
            case "LOCATION":
                $content = $object->Event."from_callback";
                break;
            default:
                $content = "receive a new event: " . $object->Event;
                break;
        }
        if (is_array($content)) {
            $result = $this->transmitNews($object, $content);
        } else {
            $result = $this->transmitText($object, $content);
        }
        return $result;
    }

    private function receiveText($object){
        $keyword = trim($object->Content);
        $content = null;
        if (strstr($keyword, "TESTCOMPONENT_MSG_TYPE_TEXT")){
            $content = $keyword."_callback";
        }
        $b = new php_log();
        $b->logs($content);
        if(!empty($content)){
            $result = $this->transmitText($object, $content);
            $b->logs($result);
            return $result;
        }
//        if(is_array($content)){
//            $result = $this->transmitNews($object, $content);
//        }else{
//            $result = $this->transmitText($object, $content);
//        }
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
//        $decryptMsg = "";
//        $a = new wx_api($this->token, $this->encodingAesKey, $this->appid);
//        $postStr = $a->encryptMsg($resultStr, $timestamp, $nonce,$decryptMsg);
//        $b = new php_log();
//        $b->logs('33'.$decryptMsg);
        return $resultStr;
    }

    private function transmitNews($object, $arr_item, $flag = 0)
    {
        if (!is_array($arr_item))
            return;

        $itemTpl = "    <item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
    </item>
";
        $item_str = "";
        foreach ($arr_item as $item)
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['Picurl'], $item['Url']);

        $newsTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<Content><![CDATA[]]></Content>
<ArticleCount>%s</ArticleCount>
<Articles>
$item_str</Articles>
<FuncFlag>%s</FuncFlag>
</xml>";

        $resultStr = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($arr_item), $flag);
        return $resultStr;
    }

    private function transmitMusic($object, $musicArray, $flag = 0)
    {
        $itemTpl = "<Music>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <MusicUrl><![CDATA[%s]]></MusicUrl>
    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
</Music>";

        $item_str = sprintf($itemTpl, $musicArray['Title'], $musicArray['Description'], $musicArray['MusicUrl'], $musicArray['HQMusicUrl']);

        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[music]]></MsgType>
$item_str
<FuncFlag>%d</FuncFlag>
</xml>";

        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $flag);
        return $resultStr;
    }
}
