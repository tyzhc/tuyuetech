<?php
namespace app\ichioce\controller;
use think\Db;
use wx_api;
use think\Controller;
use think\log;
use \php_log;
use \wxAuthLib;
use \wx_public;

class Index extends Controller
{
	//选择发起活动  1-个人转盘 2-个人投票 3-群组转盘 4-群组投票 5聚会活动
	public function index(){
		return view('index');
	}

	public function test(){

		$data = array(
			'button'=>array(
				array(
					'name'=>'发起',
					'sub_button'=>array(
						array(
							'type'=>"view",
							'name'=>'个人转盘',
							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/add/ch_type/create_type/2.html"
						),
						array(
							'type'=>"view",
							'name'=>'个人投票',
							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/add/ch_type/create_type/2.html"
						),
						array(
							'type'=>"view",
							'name'=>'群组转盘',
							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/add/ch_type/create_type/4.html"
						),
						array(
							'type'=>"view",
							'name'=>'群组投票',
							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/add/ch_type/create_type/4.html"
						),
						array(
							'type'=>"view",
							'name'=>'聚会活动',
							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/add/activity_type/create_type/5.html"
						)
					)
				),
				array(
					'name'=>'我的',
					'sub_button'=>array(
						array(
							'type'=>"view",
							'name'=>'发起活动',
							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/mine/start"
						),
						array(
							'type'=>"view",
							'name'=>'参与活动',
							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/mine/part_in"
						)

					)
				),
				array(
					'name'=>'其他',
					'sub_button'=>array(
						array(
							'type'=>"view",
							'name'=>'联系我们',
							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/index/contact_us"
						),
						array(
							'type'=>"view",
							'name'=>'帮助文档',
							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/index/help"
						),
						array(
							'type'=>"view",
							'name'=>'建议反馈',
							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/index/feedback "
						)

					)
				)
			)
		);
		dump($data);
		$data1 = json_encode($data, JSON_UNESCAPED_UNICODE);
		dump($data1);
	}

	public function ceshi(){
		header("Content-Type:text/html;charset=UTF-8");
		date_default_timezone_set("PRC");
		$showapi_appid = '51262';  //替换此值,在官网的"我的应用"中找到相关值
		$showapi_secret = 'e188c6ea913d4254bbbfe2344fef6a83';  //替换此值,在官网的"我的应用"中找到相关值
		$wxInfoLib = new wxAuthLib();
		$paramArr = array(
			'showapi_appid'=> $showapi_appid,
			'keyword'=> "",
			'proId'=> "",
			'cityId'=> "",
			'areaId'=> "",
			'page'=> ""
			//添加其他参数
		);
//		$url = "http://api.nuomi.com/api/dailydeal";
//		$res = $wxInfoLib->httpGet($url);
		$param = $this->createParam($paramArr,$showapi_secret);
		$url = 'http://route.showapi.com/268-1?'.$param;
		$res = $wxInfoLib->httpGet($url);
		dump($res);

	}
	//创建参数(包括签名的处理)
	function createParam ($paramArr,$showapi_secret) {
		$paraStr = "";
		$signStr = "";
		ksort($paramArr);
		foreach ($paramArr as $key => $val) {
			if ($key != '' && $val != '') {
				$signStr .= $key.$val;
				$paraStr .= $key.'='.urlencode($val).'&';
			}
		}
		$signStr .= $showapi_secret;//排好序的参数加上secret,进行md5
		$sign = strtolower(md5($signStr));
		$paraStr .= 'showapi_sign='.$sign;//将md5后的值作为参数,便于服务器的效验
		echo "排好序的参数:".$signStr."<br>\r\n";
		return $paraStr;
	}

	public function jd_test(){
		$wxInfoLib = new wxAuthLib();
		$app_key ='be6350bb82e25ca7346c99778eeb2e95';
		$code_id = '10148747134';
		$url = "https://way.jd.com/JDCloud/mobilebigfield?skuid=$code_id&appkey=$app_key";
		$res = $wxInfoLib->httpGet($url);
		dump($res);
	}

	public function tb_short_long(){
//		$res = $this->geturl("http://z.oiax6.com/h.CxTecS"); //这里输入淘宝客短连接
		$wxInfoLib = new wxAuthLib();
		$short_url = 'http://z.oiax6.com/h.CxTecS';
		$con = file_get_contents($short_url);
		$parr = '/\?id=[0-9]+/';
		preg_match($parr,$con,$res);
		$good_id = $res[0];
		$api_url = 'https://eco.taobao.com/router/rest';
		$data_arr = array(
			'method'=>'',
			'app_key'=>'',
			'sign_method'=>'',
			'sign'=>'',
			'timestamp'=>'',
			'v'=>''
		);

	}

	public function tmp_test()
	{
		$wx = new wx_public();
		$wxInfoLib = new wxAuthLib();
		$access_token = $wx->access_token(1);
//		$str = "{
//		'touser':'oscGBwkdThKoXB-ETernszC55SAw',
//        'template_id':'jTGk4vSgEJ4XdwFGXOwjwYGTLnDBDgKNIaJtEkUU8mU',
//        'url':'http://www.qq.com',
//        'data':{
//        	'first':{
//        		'value':'刚刚发起了投票'
//        	},
//        	'keyword1':{
//        		'value':'123标题',
//        		'color':green;
//        	},
//        	'keyword1':{
//        		'value':'2017-12-12',
//        		'color':green;
//        	},
//        	'remark':{
//        		'value':'备注',
//        		'color':red;
//        	}
//		}";
		$data = array(
			"touser"=>'oscGBwkdThKoXB-ETernszC55SAw',
			"template_id"=>"jTGk4vSgEJ4XdwFGXOwjwYGTLnDBDgKNIaJtEkUU8mU",
			"url"=>"http://www.baidu.com",
			'data'=>array(
				'first'=>array(
					'value'=>'刚刚发起了投票',
//					'color'=>'red'
				),
				'keyword1'=>array(
					'value'=>'123123'
				),
				'keyword2'=>array(
					'value'=>date('Y-m-d H:i:s')
				),
				'remark'=>array(
					'value'=>'备注'
				)
			)
		);
		$data = json_encode($data, JSON_UNESCAPED_UNICODE);
		dump($data);
		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";
		$res = $wxInfoLib->httpPost($data, $url);
		dump($res);
	}

	public function menu(){
		$wx = new \wx_public();
//		$data = array(
//			'button'=>array(
//				array(
//					'name'=>'发起1',
//					'sub_button'=>array(
//						array(
//							'type'=>"view",
//							'name'=>'个人转盘',
//							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/add/ch_type/create_type/2.html"
//						),
//						array(
//							'type'=>"view",
//							'name'=>'个人投票',
//							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/add/ch_type/create_type/2.html"
//						),
//						array(
//							'type'=>"view",
//							'name'=>'群组转盘',
//							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/add/ch_type/create_type/4.html"
//						),
//						array(
//							'type'=>"view",
//							'name'=>'群组投票',
//							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/add/ch_type/create_type/4.html"
//						),
//						array(
//							'type'=>"view",
//							'name'=>'聚会活动',
//							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/add/activity_type/create_type/5.html"
//						)
//					)
//				),
//				array(
//					'name'=>'我的',
//					'sub_button'=>array(
//						array(
//							'type'=>"view",
//							'name'=>'发起活动',
//							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/mine/start"
//						),
//						array(
//							'type'=>"view",
//							'name'=>'参与活动',
//							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/mine/part_in"
//						)
//
//					)
//				),
//				array(
//					'name'=>'其他',
//					'sub_button'=>array(
//						array(
//							'type'=>"view",
//							'name'=>'联系我们',
//							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/index/contact_us"
//						),
//						array(
//							'type'=>"view",
//							'name'=>'帮助文档',
//							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/index/help"
//						),
//						array(
//							'type'=>"view",
//							'name'=>'建议反馈',
//							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/index/feedback "
//						)
//
//					)
//				)
//			)
//		);
		$data = array(
			'button'=>array(
				array(
					'name'=>'发起',
					'type'=>"view",
					'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/index/index"
				),
				array(
					'name'=>'我的',
					'sub_button'=>array(
						array(
							'type'=>"view",
							'name'=>'发起活动',
							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/mine/start"
						),
						array(
							'type'=>"view",
							'name'=>'参与活动',
							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/mine/part_in"
						)

					)
				),
				array(
					'name'=>'其他',
					'sub_button'=>array(
						array(
							'type'=>"view",
							'name'=>'联系我们',
							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/index/contact_us"
						),
						array(
							'type'=>"view",
							'name'=>'帮助文档',
							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/index/help"
						),
						array(
							'type'=>"view",
							'name'=>'建议反馈',
							'url'=>"http://".$_SERVER['HTTP_HOST']."/index.php/ichoose/index/feedback "
						)

					)
				)
			)
		);
		$wx->menu(1,$data);
	}

	public function gaode(){
		return view('gaode_test');
	}




}