<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/10/30
 * Time: 15:50
 */
namespace app\index\controller;
use \wx_public;
use think\Db;
use think\Log;
use think\User_config;

class Test
{
	public function index(){
//		echo '1231121aaaa';
		$appid='wx844f289f684d283f';
//		$redirect_url = 'http://'.$_SERVER['HTTP_HOST'].url('getUserInfo');
//		$redirect_url = 'http://www.baidu.com';
		$redirect_uri = urlencode('http://'.$_SERVER['HTTP_HOST'].url('getUserInfo'));

		$url ="https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_base&state=12#wechat_redirect";
		$this->httpGet($url);

	}

	public function getUserInfo(){
		$code = $_GET['code'];
		$appid='wx844f289f684d283f';
		$secret ='be2dd179b8ea196adc8c8b8c103af539';

		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
		$ass = $this->httpGet($url);
		$ass_token = json_decode($ass,true);
		$openid = $ass_token['openid'];
		$wx = new wx_public();
		$access_token = $wx->access_token(1);
		$user_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$openid&lang=zh_CN";
		$user_info = $this->httpGet($user_url);
		$user_info = json_decode($user_info,true);
		$data = array(
			'openid'=>$openid,
			'nickname'=>$user_info['nickname'],
			'sex'=>$user_info['sex'],
			"province"=>$user_info['province'],
		);
		$res  = Db::table('ty_account_user')->where('openid',$openid)->find();
		if(empty($res)){
			Db::table('ty_account_user')->insert($data);
		}else{
			Db::table('ty_account_user')->where('id',$res['id'])->update($data);
		}

	}

	public function test(){
		$wx = new wx_public();
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=fDyASdz2gmYxpGiDZH469NJbtIz87n4CxFPTdpAkTWi63pLUxpu_MUODS5ZfKxqw7h2rO_4z4EM0PGekr9Fvmzm-vKa-Bt7oap64CER8pAQj9fsZuGIPTjda0jYF5Z2MWOChAHANFT";
//		{"access_token":"fDyASdz2gmYxpGiDZH469NJbtIz87n4CxFPTdpAkTWi63pLUxpu_MUODS5ZfKxqw7h2rO_4z4EM0PGekr9Fvmzm-vKa-Bt7oap64CER8pAQj9fsZuGIPTjda0jYF5Z2MWOChAHANFT","expires_in":7200}
		$data = array(
			'button'=>array(
				array(
					'name'=>'菜单',
					'sub_button'=>array(
						array(
							'type'=>"view",
							'name'=>'百度',
							'url'=>"http://www.baidu.com"
						),
						array(
							'type'=>"view",
							'name'=>'搜搜',
							'url'=>"http://www.soso.com"
						)
					)
				)
			)
		);
		$data1 = json_encode($data, JSON_UNESCAPED_UNICODE);
//		dump($data1);
		$a = $this->httpPost($url,$data1);
		dump($a);
	}

	public function huoqu(){
		$wx = new wx_public();
		$access_token = $wx->access_token(1);

		$openid = Db::table('ty_account_user')->where('nickname','-莫如空许')->find()['openid'];
		$tmp_id = 'qZ-Ym4sbOS7DpLag6QqRO_6rktInVPFtfOikFc-k2fI';
		$url  = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token."";
		$data = array(
			"touser"=>$openid,
           	"template_id"=>"b7dfeO35LPs4UyDWM9TzS4w6N6JR8GmmicuHPtSazDY",
		    "url"=>"http://www.baidu.com",
//          "miniprogram"=>'',
//			"appid"=>"xiaochengxuappid12345",
//			"pagepath"=>"index?foo=bar",
			'data'=>array(
				'first'=>array(
					'value'=>'恭喜您中大奖',
//					'color'=>'red'
				),
				'issueInfo'=>array(
					'value'=>'123123'
				),
				'betTime'=>array(
					'value'=>date('Y-m-d H:i:s')
				),
				'fee'=>array(
					'value'=>12312312
				),
				'drawTime'=>array(
					'value'=>'2017-01-01'
				),
				'remark'=>array(
					'value'=>'奖金即将到账'
				)
			)
		);
		$data = json_encode($data, JSON_UNESCAPED_UNICODE);
		$a = $this->httpPost($url,$data);
		dump($a);


	}

	public function openid(){
		$appid='wx844f289f684d283f';
		$secret ='be2dd179b8ea196adc8c8b8c103af539';
		$redirect_uri = url('ceshi');
		$url ="https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_base&state=1#wechat_redirect";

	}

	public function ceshi(){

		define("TOKEN", "weixin");

	}

	private function checkSignature()
	{
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

	private function httpGet($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 500);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, $url);
		$res = curl_exec($curl);
		curl_close($curl);
		return $res;
	}

	public function arr(){
		$arr = array(
			"-1"=>"系统繁忙，此时请开发者稍候再试",
			"0"=>"请求成功",
			"40001"=>"获取access_token时AppSecret错误，或者access_token无效。请开发者认真比对AppSecret的正确性，或查看是否正在为恰当的公众号调用接口",
			"40002"=>"不合法的凭证类型",
			"40003"=>"不合法的OpenID，请开发者确认OpenID（该用户）是否已关注公众号，或是否是其他公众号的OpenID",
			"40004"=>"不合法的媒体文件类型",
			"40005"=>"不合法的文件类型",
			"40006"=>"不合法的文件大小",
			"40007"=>"不合法的媒体文件id",
			"40008"=>"不合法的消息类型",
			"40009"=>"不合法的图片文件大小",
			"40010"=>"不合法的语音文件大小",
			"40011"=>"不合法的视频文件大小",
			"40012"=>"不合法的缩略图文件大小",
			"40013"=>"不合法的AppID，请开发者检查AppID的正确性，避免异常字符，注意大小写",
			"40014"=>"不合法的access_token，请开发者认真比对access_token的有效性（如是否过期），或查看是否正在为恰当的公众号调用接口",
			"40015"=>"不合法的菜单类型",
			"40016"=>"不合法的按钮个数",
			"40017"=>"不合法的按钮个数",
			"40018"=>"不合法的按钮名字长度",
			"40019"=>"不合法的按钮KEY长度",
			"40020"=>"不合法的按钮URL长度",
			"40021"=>"不合法的菜单版本号",
			"40022"=>"不合法的子菜单级数",
			"40023"=>"不合法的子菜单按钮个数",
			"40024"=>"不合法的子菜单按钮类型",
			"40025"=>"不合法的子菜单按钮名字长度",
			"40026"=>"不合法的子菜单按钮KEY长度",
			"40027"=>"不合法的子菜单按钮URL长度",
			"40028"=>"不合法的自定义菜单使用用户",
			"40029"=>"不合法的oauth_code",
			"40030"=>"不合法的refresh_token",
			"40031"=>"不合法的openid列表",
			"40032"=>"不合法的openid列表长度",
			"40033"=>"不合法的请求字符，不能包含\uxxxx格式的字符",
			"40035"=>"不合法的参数",
			"40038"=>"不合法的请求格式",
			"40039"=>"不合法的URL长度",
			"40050"=>"不合法的分组id",
			"40051"=>"分组名字不合法",
			"40060"=>"删除单篇图文时，指定的 article_idx 不合法",
			"40117"=>"分组名字不合法",
			"40118"=>"media_id大小不合法",
			"40119"=>"button类型错误",
			"40120"=>"button类型错误",
			"40121"=>"不合法的media_id类型",
			"40132"=>"微信号不合法",
			"40137"=>"不支持的图片格式",
			"40155"=>"请勿添加其他公众号的主页链接",
			"41001"=>"缺少access_token参数",
			"41002"=>"缺少appid参数",
			"41003"=>"缺少refresh_token参数",
			"41004"=>"缺少secret参数",
			"41005"=>"缺少多媒体文件数据",
			"41006"=>"缺少media_id参数",
			"41007"=>"缺少子菜单数据",
			"41008"=>"缺少oauth code",
			"41009"=>"缺少openid",
			"42001"=>"access_token超时，请检查access_token的有效期，请参考基础支持-获取access_token中，对access_token的详细机制说明",
			"42002"=>"refresh_token超时",
			"42003"=>"oauth_code超时",
			"42007"=>"用户修改微信密码，accesstoken和refreshtoken失效，需要重新授权",
			"43001"=>"需要GET请求",
			"43002"=>"需要POST请求",
			"43003"=>"需要HTTPS请求",
			"43004"=>"需要接收者关注",
			"43005"=>"需要好友关系",
			"43019"=>"需要将接收者从黑名单中移除",
			"44001"=>"多媒体文件为空",
			"44002"=>"POST的数据包为空",
			"44003"=>"图文消息内容为空",
			"44004"=>"文本消息内容为空",
			"45001"=>"多媒体文件大小超过限制",
			"45002"=>"消息内容超过限制",
			"45003"=>"标题字段超过限制",
			"45004"=>"描述字段超过限制",
			"45005"=>"链接字段超过限制",
			"45006"=>"图片链接字段超过限制",
			"45007"=>"语音播放时间超过限制",
			"45008"=>"图文消息超过限制",
			"45009"=>"接口调用超过限制",
			"45010"=>"创建菜单个数超过限制",
			"45011"=>"API调用太频繁，请稍候再试",
			"45015"=>"回复时间超过限制",
			"45016"=>"系统分组，不允许修改",
			"45017"=>"分组名字过长",
			"45018"=>"分组数量超过上限",
			"45047"=>"客服接口下行条数超过上限",
			"46001"=>"不存在媒体数据",
			"46002"=>"不存在的菜单版本",
			"46003"=>"不存在的菜单数据",
			"46004"=>"不存在的用户",
			"47001"=>"解析JSON/XML内容错误",
			"48001"=>"api功能未授权，请确认公众号已获得该接口，可以在公众平台官网-开发者中心页中查看接口权限",
			"48002"=>"粉丝拒收消息（粉丝在公众号选项中，关闭了“接收消息”）",
			"48004"=>"api接口被封禁，请登录mp.weixin.qq.com查看详情",
			"48005"=>"api禁止删除被自动回复和自定义菜单引用的素材",
			"48006"=>"api禁止清零调用次数，因为清零次数达到上限",
			"50001"=>"用户未授权该api",
			"50002"=>"用户受限，可能是违规后接口被封禁",
			"61451"=>"参数错误(invalid parameter)",
			"61452"=>"无效客服账号(invalid kf_account)",
			"61453"=>"客服帐号已存在(kf_account exsited)",
			"61454"=>"客服帐号名长度超过限制(仅允许10个英文字符，不包括@及@后的公众号的微信号)(invalid   kf_acount length)",
			"61455"=>"客服帐号名包含非法字符(仅允许英文+数字)(illegal character in     kf_account)",
			"61456"=>"客服帐号个数超过限制(10个客服账号)(kf_account count exceeded)",
			"61457"=>"无效头像文件类型(invalid   file type)",
			"61450"=>"系统错误(system error)",
			"61500"=>"日期格式错误",
			"65301"=>"不存在此menuid对应的个性化菜单",
			"65302"=>"没有相应的用户",
			"65303"=>"没有默认菜单，不能创建个性化菜单",
			"65304"=>"MatchRule信息为空",
			"65305"=>"个性化菜单数量受限",
			"65306"=>"不支持个性化菜单的帐号",
			"65307"=>"个性化菜单信息为空",
			"65308"=>"包含没有响应类型的button",
			"65309"=>"个性化菜单开关处于关闭状态",
			"65310"=>"填写了省份或城市信息，国家信息不能为空",
			"65311"=>"填写了城市信息，省份信息不能为空",
			"65312"=>"不合法的国家信息",
			"65313"=>"不合法的省份信息",
			"65314"=>"不合法的城市信息",
			"65316"=>"该公众号的菜单设置了过多的域名外跳（最多跳转到3个域名的链接）",
			"65317"=>"不合法的URL",
			"9001001"=>"POST数据参数不合法",
			"9001002"=>"远端服务不可用",
			"9001003"=>"Ticket不合法",
			"9001004"=>"获取摇周边用户信息失败",
			"9001005"=>"获取商户信息失败",
			"9001006"=>"获取OpenID失败",
			"9001007"=>"上传文件缺失",
			"9001008"=>"上传素材的文件类型不合法",
			"9001009"=>"上传素材的文件尺寸不合法",
			"9001010"=>"上传失败",
			"9001020"=>"帐号不合法",
			"9001021"=>"已有设备激活率低于50%，不能新增设备",
			"9001022"=>"设备申请数不合法，必须为大于0的数字",
			"9001023"=>"已存在审核中的设备ID申请",
			"9001024"=>"一次查询设备ID数量不能超过50",
			"9001025"=>"设备ID不合法",
			"9001026"=>"页面ID不合法",
			"9001027"=>"页面参数不合法",
			"9001028"=>"一次删除页面ID数量不能超过10",
			"9001029"=>"页面已应用在设备中，请先解除应用关系再删除",
			"9001030"=>"一次查询页面ID数量不能超过50",
			"9001031"=>"时间区间不合法",
			"9001032"=>"保存设备与页面的绑定关系参数错误",
			"9001033"=>"门店ID不合法",
			"9001034"=>"设备备注信息过长",
			"9001035"=>"设备申请参数不合法",
			"9001036"=>"查询起始值begin不合法"
		);
		dump($arr);

	}

	public function log(){

		Log::init([
			'type'  =>  'File',
			'path'  =>  APP_PATH.'logs/'
		]);
		Log::write('测试日志信息，这是警告级别，并且实时写入','info');
	}
}