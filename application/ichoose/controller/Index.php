<?php
namespace app\ichoose\controller;
use think\Db;
use wx_api;
use think\Controller;
use think\log;
use \php_log;
use \wxAuthLib;
use \wx_public;
use \baidu_api;
use JonnyW\PhantomJs\Client;

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
							'name'=>'建议反馈1',
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

	public function canyin(){
		$wxInfoLib = new wxAuthLib();
		$wx = new \wx_public();
//		$url = 'http://api.map.baidu.com/place/v2/search?query=付小姐在成都&tag=&region=北京&output=json&ak=CgqpmlVkGWSLt14WD9Rc5GSW761vdZqo';
		$url = 'http://api.map.baidu.com/place/v2/search?query=肯德基&region=北京&output=json&ak=CgqpmlVkGWSLt14WD9Rc5GSW761vdZqo';
//		$url = $this->baidu();
		dump($url);
		$res = $wxInfoLib->httpGet($url);
		dump($res);


	}

	public function baidu(){
//		$ak = "HTyu35TpPsoXOqlnGYD07IdvbnIIMi0f";
		$ak = "CgqpmlVkGWSLt14WD9Rc5GSW761vdZqo";
		$sk = "Tgi3T444RXuGUQhEuGQ2ZF8YAEAujsVo";
		$wxInfoLib = new wxAuthLib();
//		CgqpmlVkGWSLt14WD9Rc5GSW761vdZqo
		$url = "http://api.map.baidu.com/place/v2/search?query=麦当劳&tag=美食&region=北京&output=json&ak=CgqpmlVkGWSLt14WD9Rc5GSW761vdZqo";
		$uri = '/place/v2/search/';
		$query='麦当劳';
		$tag='美食';
		$region = '北京';
		$output = 'json';
		$querystring_arrays = array (
			'query' => $query,
			'ak'=>$ak,
			'output' =>$output
		);
		$sn = $this->caculateAKSN($ak, $sk, $uri, $querystring_arrays);
		dump($sn);
		$time=time();
		$url .= "&scope=2&timestamp=$time";
		dump($url);
		$res = $wxInfoLib->httpGet($url);
		dump($res);
//		$url1 = 'http://api.map.baidu.com/place/detail?uid=06161088fbc9e0ffadb22a80&output=json&source=placeapi_v2';
//		$res = $wxInfoLib->httpGet($url1);
//		dump($res);
//		return $target;
	}

	public function baidu_t(){
		$baidu = new baidu_api();
		$res = $baidu->place_region('付小姐在成都');

	}



	public function aa(){
		$wxInfoLib = new wxAuthLib();
		$url = 'http://api.map.baidu.com/place/v2/detail?uid=06161088fbc9e0ffadb22a80&output=json&scope=2&ak=CgqpmlVkGWSLt14WD9Rc5GSW761vdZqo';

		$res = $wxInfoLib->httpGet($url);
		dump($res);

	}


	public function get_con($str){
//		$file = '/phpstudy/www/http:/platform.tuyuetech.com/application/ichoose/fu.html';
////		$file = '/phpstudy/www/http:/platform.tuyuetech.com/application/ichoose/kfc.html';
//		$str = file_get_contents($file);
//		dump($str);
		//当前页面主图
		preg_match_all('/<img src="(.*?)" class="head-img">/', $str, $pic);
		$pic_path =  $pic[1];
//		dump($pic_path);
		//共多少张照片  及 连接
		preg_match_all('/<a class="imgnum" nstat="click|{da_src:basicInfoBk.topimgLnk}" href="(.*?)" target="_blank">(.*?)<\/a>/', $str, $picnum);
		$pic_url = $picnum[1][1];
		$picnumn_con = $picnum[2][1];
//		dump($pic_url,$picnumn_con);
		//综合评分
		preg_match_all('/<span class="score-num">[-+]?[0-9]*\.?[0-9]*<\/span>/', $str, $zh_grade);
		$zh_grade = $zh_grade[0][0];
		preg_match_all('/>[-+]?[0-9]*\.?[0-9]*</', $zh_grade,$zh_grade_new);
		$zh_grade_new = trim($zh_grade_new[0][0],'<|>');
//		dump($zh_grade_new);
		//评价内容
		preg_match_all('/<span data-are=\"\">\s(.*?)<\/span>/', $str, $evaluate);
		//评分
		preg_match_all('/<span class="score-num">\s(.*?)<\/span>/', $str, $grade);
		//特色
		preg_match_all('/<span class=\"mrs mlm\">(.*?)<\/div>/', $str,$feature);
		if(!empty($feature[0]) && !empty($feature[1])){
			foreach($feature[0] as $k=>$v){
				preg_match_all('/>[^\s](.*?)</', $v,$arr);
				$feature_two[] = $arr[0];
			}
			foreach($feature_two as $k1=>$v1){
				$num = count($v1)/3;
				$new = count($v1)-1;
				for($i=1;$i<=$num;$i++){
					if($i!=1){
						$new_num = $new-($i-1)*3;
					}else{
						$new_num = $new;
					}
					unset($v1[$new_num]);
				}
				foreach($v1 as $k2=>$v2){
					$tese[$k1][] = trim($v2,'<|>');
				}
				$fenshu = trim($grade[1][$k1]);
				$tese_new[$k1]['val'] = $fenshu;
				foreach($tese[$k1] as $k3=>$v3){
					if ($k3%2==0){
						$tese_new[$k1][$v3] = $tese[$k1][$k3+1];
					}
				}
			}
		}else{

		}
		//用户评价时间
		preg_match_all('/<span class=\"time fcg9\">(.*?)<\/span>/', $str, $user_time);
		//判断数据 来源
		preg_match_all("/来自大众点评/sim", $str, $strResult, PREG_PATTERN_ORDER);
		preg_match_all("/来自百度糯米/sim", $str, $strresult, PREG_PATTERN_ORDER);
		//判断数据 来源
		if(count($strResult[0])>1 && count($strresult[0])<1){//大众
			echo '大众';
			//用户信息 --需要判断数据来自糯米还是大众点评
			preg_match_all('/<a target="_blank" monitor="review_uname" class="fcg9" href=(.*?) title=(.*?)>/', $str, $user_info);

			//大众url
			preg_match_all('/<a href="(.*?)" target="_blank" class="fcg comment-from" monitor="review_source">/', $str, $form_url);
			$form_url = $form_url[0][0];
			foreach($user_info[2] as $ko=>$vo){
				$content = trim($evaluate[1][$ko]);
				$time = trim($user_time[1][$ko]);
				$evaluate_arr[$ko]['grade'] = $tese_new[$ko];
				$evaluate_arr[$ko]['content'] = $content;
				$evaluate_arr[$ko]['name'] = $user_info[2][$ko];
				$evaluate_arr[$ko]['link'] = $user_info[1][$ko];
				$evaluate_arr[$ko]['time'] = $time;
				$evaluate_arr[$ko]['form'] = '来自大众点评';
				$evaluate_arr[$ko]['form_url'] = $form_url;
			}
			//大众评价  的数组 $evaluate_arr
		}else{//糯米
			echo '糯米';
			//用户信息 --需要判断数据来自糯米还是大众点评<span class="fcg9">\s(.*?)</span>
//			preg_match_all('/<a target="_blank" monitor="review_uname" class="fcg9" href=(.*?) title=(.*?)>/', $str, $user_info);
			preg_match_all('/<span class="fcg9">\s(.*?)<\/span>/', $str, $user_info);

			foreach($user_info[1] as $ko=>$vo){
				$fenshu =  trim($grade[1][$ko]);
				$content = trim($evaluate[1][$ko]);
				$time = trim($user_time[1][$ko]);
				$name = trim($vo);
				$evaluate_arr[$ko]['grade'] = $fenshu;
				$evaluate_arr[$ko]['content'] = $content;
				$evaluate_arr[$ko]['name'] = $name;
				$evaluate_arr[$ko]['link'] = '';
				$evaluate_arr[$ko]['time'] = $time;
				$evaluate_arr[$ko]['form'] = '来自大众点评';
				$evaluate_arr[$ko]['form_url'] = '';
			}
		}
//		dump($evaluate_arr);
		return $evaluate_arr;
	}
	public function phantomjs($url){
		$client = Client::getInstance();
		$client->getEngine()->setPath('/usr/local/bin/phantomjs');
		$request = $client->getMessageFactory()->createRequest("$url", 'GET');
		$response = $client->getMessageFactory()->createResponse();
		$client->send($request, $response);
		if($response->getStatus() === 200 || $response->getStatus() === 302) {
			// Dump the requested page content
			$str = $response->getContent();
			return $str;
		}
	}
	public function jj(){
		$uid = '06161088fbc9e0ffadb22a80';
		$baidu = new baidu_api();
		$res = $baidu->place_detail($uid,'json');
		dump($res);
	}




}