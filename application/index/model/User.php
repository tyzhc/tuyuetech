<?php
/**
 * Created by PhpStorm.
 * User: 途阅科技
 * Date: 2017/5/8
 * Time: 13:43
 */
namespace app\index\model;
use think\Model;
use think\Db;

class User extends Model
{
    protected $pk = 'user_id';
    /**
     * 将原文加密成符合数据库存储要求的密文
     */
    function password($str)
    {
        $mdstr = $this -> md5lite($str);
        return $this -> md5litekey($mdstr);
    }

    /**
     * 返回8位的加密字符串
     * 适用于与客户端js加密的密文比对
     *
     * @param string $str 加密原文
     * @param int $length 返回长度
     * @return string
     */
    function md5lite($str, $length = 8)
    {
        $mdstr = strtoupper(md5($str));
        $mdstr = strtoupper(md5($mdstr . 'md5lite'));
        $strlen = strlen($mdstr);
        if ($length >= $strlen)
            return $mdstr;
        $len = $length / 2;
        return substr($mdstr, 0, $len) . substr($mdstr, $strlen - $len);
    }

    /**
     * 将md5lite加密后的密文与服务器加密密匙混淆后再加密
     * 适用于存储到服务器数据库
     *
     * @param string $str 经过md5lite加密过的8位密文
     * @param int $length 返回的长度
     * @param string $app_sign_key 应用程序签名密匙
     * @return string
     */
    function md5litekey($str, $length = 8, $app_sign_key = 'XYSPK_2014.03')
    {
        $mdstr = strtoupper(md5($str . $app_sign_key));
        $strlen = strlen($mdstr);
        if ($length >= $strlen)
            return $mdstr;
        $len = $length / 2;
        return substr($mdstr, 0, $len) . substr($mdstr, $strlen - $len);
    }

    /**
     * 添加事件日志
     * @param $list 用户信息
     * @param $msg 消息内容
     * @param bool $state 状态
     */
    public function add_event_log($list, $msg, $state = true)
    {
        $data['userID'] = $list['id'];
        $data['user_name'] = $list['user_name'];
        $data['ip'] = $list['ip'];
        $data['siteID'] = $list['siteID'];
        $data['msg'] = $msg;
        $data['state'] = $state;
        Db::table('think_event_log')->insert($data);
    }

}