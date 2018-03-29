<?php
namespace app\wechat\controller;
use think\Db;
use wx_api;
use think\Controller;
use think\log;
use \php_log;
class Api extends Controller
{
    protected $appid = "";
    protected $secret = "";
    protected $token = "";
    protected $encodingAesKey = "";

    public function __construct()
    {
        parent::__construct();
        $this->appid = "wx844f289f684d283f";
        $this->secret = "be2dd179b8ea196adc8c8b8c103af539";
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
            //设置菜单
            $this->setMenu();

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
    private function setMenu()
    {
        //获取access_token
        return 1;
        $access_token = getAccessToken($this->appid, $this->secret);
        //定义菜单
        $data = Db::name('WxMenu')->select();
        $tree = list_to_tree($data, 'id', 'parent');
        //解析菜单
        $menu = tree_to_json_menu($tree);
        //将菜单发送给微信服务器
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $access_token;
        $result = https_request($url, $menu);
    }

    //接受用户请求
    private function responseMsg()
    {
        //获取返回的post数据包
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $b = new php_log();
        $b->logs($postStr);
        if (!empty($postStr)) {
            $signature = input('param.signature');
            $timestamp = input('param.timestamp');
            $nonce = input('param.nonce');
            $encrypt_type = input('param.encrypt_type');
            $msg_signature  = input('param.msg_signature');
            $a  = new wx_api($this->token,$this->encodingAesKey,"wx844f289f684d283f");
            $decryptMsg = "";  //解密后的明文
            $postStr = $a->decryptMsg($msg_signature,$timestamp,$nonce,$postStr,$decryptMsg);
            $b->logs($decryptMsg);
            $postStr = $decryptMsg;
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $msgType = $postObj->MsgType;
            $eventKey = $postObj->EventKey;
            $keyword = trim($postObj->Content);
            $time = time();

            $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
            //回复图文信息的模板
            $tpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <ArticleCount>1</ArticleCount>
                        <Articles>
                        <item>
                        <Title><![CDATA[%s]]></Title> 
                        <Description><![CDATA[%s]]></Description>
                        <PicUrl><![CDATA[%s]]></PicUrl>
                        <Url><![CDATA[%s]]></Url>
                        </item>
                        </Articles>
                        </xml> ";
            //事件消息
            if ($msgType == "event") { //关注事件
                if ($postObj->Event == "subscribe" && $eventKey == "") {
                    $msgType = "text";
                    $contentStr = "欢迎您关注此公众号！";
//                        $msg = Db::name('WxReply')->where('type',1)->value('msg');
//                        if($msg){
//                            $contentStr = $msg;
//                        }else{
//                            $contentStr = "欢迎您关注此公众号！";
//                        }
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    $encryptMsg = ''; //加密后的密文
                    $postStr = $a->encryptMsg($resultStr,time(),$nonce,$encryptMsg);
                    echo $encryptMsg;
                }
//                elseif($postObj->Event=="CLICK"){  //点击事件
//                    $msg = Db::name('WxMenu')->where('key',(string)$eventKey)->value('msg');
//                    $msgType = "text";
//                    $contentStr = $msg;
//                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
//                    echo $resultStr;
//                }elseif($postObj->Event=="subscribe" && $eventKey!=""){ //未关注扫溯源码事件
//                    //获取参数值
//                    $id = substr($eventKey,8);
//                    $info = Db::name('SourceGoodsData')
//                        ->alias('a')
//                        ->join('goods b ','a.goods_id= b.id')
//                        ->where(['a.id'=>$id])
//                        ->field('a.batch_number,b.name,b.description')
//                        ->find();
//                    $host = 'http://'.$_SERVER['SERVER_NAME'].__ROOT__;
//                    $tourl = $host."/wap.php/apptrace/tracelist.html?code=".$info['batch_number'];
//
//                    $msgType = "news";
//                    $title = $info['name'];
//                    $description = $info['description'];
//                    $picurl = $host."/themes/leya/index/images/wxsy.png";
//
//                    $resultStr = sprintf($tpl,$fromUsername,$toUsername,$time,$msgType,$title,$description,$picurl,$tourl);
//                    echo $resultStr;
//                }elseif($postObj->Event=="SCAN" && $eventKey!=""){ //已关注扫溯源码事件
//                    //获取参数值
//                    $info = Db::name('SourceGoodsData')
//                        ->alias('a')
//                        ->join('goods b ','a.goods_id= b.id')
//                        ->where(['a.id'=>$eventKey])
//                        ->field('a.batch_number,b.name,b.description')
//                        ->find();
//                    $host = 'http://'.$_SERVER['SERVER_NAME'].__ROOT__;
//                    $tourl = $host."/wap.php/apptrace/tracelist.html?code=".$info['batch_number'];
//
//                    $msgType = "news";
//                    $title = $info['name'];
//                    $description = $info['description'];
//                    $picurl = $host."/themes/leya/index/images/wxsy.png";
//
//                    $resultStr = sprintf($tpl,$fromUsername,$toUsername,$time,$msgType,$title,$description,$picurl,$tourl);
//                    echo $resultStr;
//                }
//            }
//            elseif($msgType=="text")
//            {
//                //是否设置关键词回复
//                $data= Db::name('WxReply')->where('type',3)->select();
//                $reply = "";
//                if($data){
//                    foreach ($data as $key => $value) {
//                        if($value['key']==$keyword){
//                            $reply = $value['msg'];
//                            break ;
//                        }
//                    }
//                }else{
//                    $reply = Db::name('WxReply')->where('type',2)->value('msg');
//                    if(empty($reply)){
//                        $reply = "";
//                    }
//                }
//                if($reply!=""){
//                    $data = $reply;
//                    $msgType = "text";
//                    $contentStr = $data;
//                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
//                    echo $resultStr;
//                }
//
//            }
//            else{
//                echo "错误！";
//            }

            } else if ($msgType == "text") {
                $reply = '1233333';
                if ($reply != "") {
                    $data = $reply;
                    $msgType = "text";
                    $contentStr = $data;
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    $encryptMsg = ''; //加密后的密文
                    $postStr = $a->encryptMsg($resultStr,time(),$nonce,$encryptMsg);
                    echo $encryptMsg;
                }
            } else {
                echo "错误！";
            }
        }
    }
}
