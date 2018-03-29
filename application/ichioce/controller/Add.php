<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/11/20
 * Time: 11:00
 */

namespace app\ichioce\controller;
use think\Db;
use wx_api;
use think\Controller;
use think\log;
use \php_log;
use \wxAuthLib;
use \wx_public;


class add extends Controller
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
		$this->showapi_appid = '51262';  //替换此值,在官网的"我的应用"中找到相关值
		$this->showapi_secret = 'e188c6ea913d4254bbbfe2344fef6a83';
		$this->app_key ='be6350bb82e25ca7346c99778eeb2e95'; //京东万象 appkey
	}

	public function index(){

	}

	//选择发起活动的类型  1文字 2餐饮娱乐 3景点线路 4图片 5商品
	public function ch_type(){
		$create_type = input('param.create_type');
		return view('ch_type',['create_type'=>$create_type]);
	}

	//选择发起活动的类型的内容添加页
	public function add(){
		$jssdk = new \JSSDK();
		$signPackage = $jssdk->GetSignPackage();

		$add_type = input('param.add_type');
		$create_type = input('param.create_type');
		if($add_type=='1'){
			echo '文字';
			return view('add',['add_type'=>$add_type,'create_type'=>$create_type,'$signPackage'=>$signPackage]);
		}elseif ($add_type=='2'){
//			return view('add_pic',['add_type'=>$add_type,'create_type'=>$create_type]);
		}elseif ($add_type=='3'){ //景点
			echo '景点';
			return view('add_scenic',['add_type'=>$add_type,'create_type'=>$create_type,'$signPackage'=>$signPackage]);
		}elseif ($add_type=='4'){
			echo '图片';
			return view('add_pic',['add_type'=>$add_type,'create_type'=>$create_type,'$signPackage'=>$signPackage]);
		}elseif ($add_type=='5'){
			echo '商品';
			return view('add_good',['add_type'=>$add_type,'create_type'=>$create_type,'$signPackage'=>$signPackage]);
		}else{


		}
	}

	//选项内容入库操作
	public function insert(){

		$jssdk = new \JSSDK();
		$signPackage = $jssdk->GetSignPackage();
		$add_type = input('param.add_type');
		$create_type = input('param.create_type');
		$wx = new \wx_public();
		$wxInfoLib = new wxAuthLib();
		$time = date('Y-m-d H:i:s');
		$b = new php_log();
		if($_POST){
			$state = $_POST['state'];
			if(array_key_exists('state_num',$_POST)){
				$state_num = $_POST['state_num'];
			}else{
				$state_num = 1;
			}
			if(array_key_exists('other',$_POST)){
				$other = $_POST['other'];
			}else{
				$other = null;
			}

			if($add_type=='1' || $add_type=='5'){
				$data['title']=$_POST['title'];
				$data['con']=$_POST['con'];
				$data['con']=$_POST['end_time'];
				$data['create_type']=$create_type;
				$data['add_type']=$add_type;
				$data['ans_type']=$_POST['type'];
				$data['open']=$_POST['gongkai'];
				$data['state']=$state;
				$data['state_num']=$state_num;
				$data['create_time']=$time;
				if($add_type=='1'){
					$data['other']=$other;
				}
				$option = $_POST['opt'];
			}elseif ($add_type=='3'){
				$data = array(
					'title'=>$_POST['title'],
					'con'=>$_POST['con'],
					'endtime'=>$_POST['end_time'],
					'create_type'=>$create_type,
					'add_type'=>$add_type,
					'ans_type'=>$_POST['type'],
					'open'=>$_POST['gongkai'],
					'state'=>$state,
					'state_num'=>$state_num,
					'create_time'=>$time
				);
				$option = $_POST['opt'];
			}elseif ($add_type=='4'){
				$files = request()->file('opt');
				$pic = null;
				if ($files){
					foreach($files as $file){
						// 移动到框架应用根目录/public/uploads/ 目录下
						$info = $file->rule('uniqid')->move(ROOT_PATH . 'public' . DS . 'upload/ichoose_img');
						if($info){
							// 成功上传后 获取上传信息
							// 输出 jpg
							// 输出 42a79759f284b767dfcb2a0197904287.jpg
//						$pic  .=  $info->getFilename().',';
							$opt[] = $info->getFilename();
						}else{
							echo $file->getError();
						}
					}
				}
				foreach($opt as $k=>$v){
					$option[$k]['file'] = $v;
					$option[$k]['con'] = $_POST['con'][$k];
				}
				$data = array(
					'title'=>$_POST['title'],
					'con'=>$_POST['con'],
					'endtime'=>$_POST['end_time'],
					'create_type'=>$create_type,
					'add_type'=>$add_type,
					'ans_type'=>$_POST['type'],
					'open'=>$_POST['gongkai'],
					'state'=>$state,
					'create_time'=>$time
				);
			}

			$tid = Db::table('i_vote_list')->insertGetId($data);
			if($tid){
				$opt=null;
				if ($add_type=='3'){
					foreach($option as $k=>$v){
						$opt[$k]['tid'] = $tid;
						$opt[$k]['option'] = $v['option'];
						$opt[$k]['keyword'] = $v['keyword'];
						$opt[$k]['id_code'] = $v['id'];
					}
				}elseif($add_type=='5'){
					foreach($option as $k=>$v){
						$opt[$k]['tid'] = $tid;
						$opt[$k]['option'] = $v['option'];
						$opt[$k]['pic_url'] = $v['pic_url'];
						$opt[$k]['id_code'] = $v['good_id'];
						$opt[$k]['merchant'] = $v['merchant'];
						$opt[$k]['url'] = $v['url'];
					}
				}elseif($add_type=='1'){
					foreach($option as $k=>$v){
						$opt[$k]['tid'] = $tid;
						$opt[$k]['option'] = $v;
					}
					if ($other){
						$num = count($opt);
						$opt[$num]['tid'] = $tid;
						$opt[$num]['option']='other';
					}
				}elseif($add_type=='4'){
					foreach($option as $k=>$v){
						$opt[$k]['tid'] = $tid;
						$opt[$k]['option'] = $v['file'];
						$opt[$k]['con'] = $v['con'];
					}
				}else{
					foreach($option as $k=>$v){
						$opt[$k]['tid'] = $tid;
						$opt[$k]['option'] = $v;
					}
				}
				$res = Db::table('i_option')->insertAll($opt);
				if ($res){
					$this->auath($tid,'text');
				}
			}
		}else{
			$id = input('param.id');
			$appid = input('param.key');
			$code = $_GET['code'];
			$aa = $wxInfoLib->getComponentAccessToken();
			$url = "https://api.weixin.qq.com/sns/oauth2/component/access_token?appid=$appid&code=$code&grant_type=authorization_code&component_appid=$this->appid&component_access_token=$aa";
			$res = $wxInfoLib->httpGet($url);
			$res = json_decode($res,true);
			$openid = $res['openid'];
			$access_token = $wx->access_token(1);
			$uid = Db::table('ty_account_user')->where('openid',$openid)->field('id')->find()['id'];
			$res = Db::table('i_vote_list')->where('id',$id)->update(['uid'=>$uid]);
			 if ($res){
				$ll = Db::table('i_vote_list')->where('id',$id)->find();
				if($ll['create_type']=='1'){
					$value='您刚刚发起了投票';
				}elseif($ll['create_type']=='3'){
					$value='您刚刚发起了换乐聚会';
				}else{
					$value='您刚刚下发了通知';
				}
				$title = $ll['title'];
				$cat_url = 'http://'.$_SERVER['HTTP_HOST'].url('cat',array('id'=>$id,'create_type'=>$ll['create_type'],'add_type'=>$ll['add_type'],'ans_type'=>$ll['ans_type']));
				$url1 = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";
				$data = array(
					"touser"=>"$openid",
					"template_id"=>"jTGk4vSgEJ4XdwFGXOwjwYGTLnDBDgKNIaJtEkUU8mU",
					"url"=>"$cat_url",
					'data'=>array(
						'first'=>array(
							'value'=>$value,
//							'color'=>'red'
						),
						'keyword1'=>array(
							'value'=>"$title",
							'color'=>'red'
						),
						'keyword2'=>array(
							'value'=>date('Y-m-d H:i:s'),
							'color'=>'green'
						),
						'remark'=>array(
							'value'=>'备注1111111'
						)
					)
				);
				$data = json_encode($data, JSON_UNESCAPED_UNICODE);
				$res = $wxInfoLib->httpPost($data,$url1);
				$b->logs($res);
				return view('end',['signPackage'=>$signPackage,'tishi'=>'发起成功']);
			}
		}
	}

	public function find_nickname($id,$object){
		foreach($object as $k=>$v){
			if($v['id']== $id){
				return $v['nickname'];
			}
		}
	}

	public function find_reply($id,$object){
		foreach($object as $k=>$v){
			if($v['id']== $id){
				return $v['option'];

			}
		}
	}

	//生成的投票查看
	public function cat(){
		$wx = new \wx_public();
		$wxInfoLib = new wxAuthLib();
		$jssdk = new \JSSDK();
		$signPackage = $jssdk->GetSignPackage();
		$id = input('param.id');
		$status = input('param.status');
		$ll = Db::table('i_vote_list')->where('id',$id)->find();
		$option = Db::table('i_option')->where('tid',$id)->select();

		if(empty($status)){
			$this->auath($id,'cat');
		}else{
			$code = $_GET['code'];
			$appid = input('param.key');
			$aa = $wxInfoLib->getComponentAccessToken();
			$url = "https://api.weixin.qq.com/sns/oauth2/component/access_token?appid=$appid&code=$code&grant_type=authorization_code&component_appid=$this->appid&component_access_token=$aa";
			$res = $wxInfoLib->httpGet($url);
			$res = json_decode($res,true);
			$openid = $res['openid'];
			$ass = $wx->access_token(1);
			$has = Db::table('i_reply_user')->where('openid',$openid)->find();
			//是否为该投票发起者  发起者nickname
			$creator_res = Db::table('ty_account_user')->where('openid',$openid)->field('id,nickname')->find();
			if ($creator_res){
				if ($creator_res['id']==$ll['uid']){
					$creator['status'] = 'yes';
					$creator['nickname'] = $creator_res['nickname'];
				}else{
					$creator['status'] = 'no';
					$creatorres = Db::table('ty_account_user')->where('id',$ll['uid'])->field('id,nickname')->find();
					$creator['nickname'] = $creatorres['nickname'];
				}
			}else{
				$creatorres = Db::table('ty_account_user')->where('id',$ll['uid'])->field('id,nickname')->find();
				$creator['nickname'] = $creatorres['nickname'];
				$creator['status'] = 'no';
			}
			//判断i_reply_user 表中是否有该用户
			if($has){
				$user_info = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$ass&openid=$openid&lang=zh_CN";
				$ew = $wxInfoLib->httpGet($user_info);
				$res = json_decode($ew,true);
				//判断该用户是否关注了公众号
				if($res['subscribe']==0){
					$attention = 'no';
				}else{
					$attention = 'yes';
				}
				if($ll['create_type']=='5'){

				}else{
					//该用户是否对该投票进行过选择
					$have = Db::table('i_vote_reply')->where('uid',$has['id'])->where('vo_id',$id)->find();
					if (strstr($have['reply'],'@#')){
						$have['reply'] = explode('@#',$have['reply']);
					}
					$user_obj = Db::table('i_reply_user')->field('id,nickname')->select();
					if($have){
						$ans_status = 'yes';
						$ans_list = Db::table('i_vote_reply')->where('vo_id',$id)->select();
						foreach($option as $k=>$v){
							foreach($ans_list as $k1=>$v1){
								if (strstr($v1['reply'],'@#')){
									$new_v1 = explode('@#',$v1['reply']);
									foreach($new_v1 as $k2=>$v2){
										if($v2==$v['id']){
											$ans_arr[$v['id']][] = $v1['uid'];
										}
									}
								}else{
									if($v1['reply']==$v['id']){
										$ans_arr[$v['id']][] = $v1['uid'];
									}
								}
							}
						}

						foreach($option as $ko=>$vo){
							if(array_key_exists($vo['id'],$ans_arr)){
								$ans_count[$vo['id']] = count($ans_arr[$vo['id']]);
							}else{
								$ans_count[$vo['id']] = 0;
							}
						}

						foreach($ans_list as $ko1=>$vo1){
							$ans_nlist[$ko1]['uid'] = $vo1['uid'];
							$ans_nlist[$ko1]['user'] = $this->find_nickname($vo1['uid'],$user_obj);

							if(strstr($vo1['reply'],'@&')){
								$other_text = explode('@&',$vo1['reply'])[1];
								$reply = $this->find_reply($vo1['reply'],$option);
								if(empty($other_text)){
									$ans_nlist[$ko1]['reply'] = '其他';
								}else{
									$ans_nlist[$ko1]['reply'] = '其他,补充内容:'.$other_text;
								}
							}else{
								$ans_nlist[$ko1]['reply'] = $this->find_reply($vo1['reply'],$option);
							}
						}
					}else{
						$ans_arr = null;
						$ans_count = null;
						$have = null;
						$ans_nlist = null;
						$ans_status = 'no';
					}
				}
				$reply_uid = $has['id'];
			}else{
				$ans_arr = null;
				$ans_count = null;
				$have = null;
				$ans_status = 'no';
				$access_token = $res['access_token'];
//			$user_info = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
				$user_info = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$ass&openid=$openid&lang=zh_CN";
				$ew = $wxInfoLib->httpGet($user_info);
				$res = json_decode($ew,true);
				if($res['subscribe']==0){
					$attention = 'no';
					$user_url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
					$user= $wxInfoLib->httpGet($user_url);
					$user = json_decode($user,true);
					$user_arr['openid'] = $user['openid'];
					$user_arr['nickname'] = $user['nickname'];
					$user_arr['sex'] = $user['sex'];
					$user_arr['language'] = $user['language'];
					$user_arr['city'] = $user['city'];
					$user_arr['province'] = $user['province'];
					$user_arr['country'] = $user['country'];
					$user_arr['headimgurl'] = $user['headimgurl'];
				}else{
					$attention = 'yes';
					$user_arr['openid'] = $res['openid'];
					$user_arr['nickname'] = $res['nickname'];
					$user_arr['sex'] = $res['sex'];
					$user_arr['language'] = $res['language'];
					$user_arr['city'] = $res['city'];
					$user_arr['province'] = $res['province'];
					$user_arr['country'] = $res['country'];
					$user_arr['headimgurl'] = $res['headimgurl'];
				}
				$reply_uid = Db::table('i_reply_user')->insertGetId($user_arr);
				$ans_nlist = null;
			}
			$user = Db::table('ty_account_user')->field('id,nickname')->select();
			$create_user = $this->find_nickname($ll['uid'],$user);
			//判断征集选项的情况
			$opt_sta = Db::table('i_option')->where('uid',$reply_uid)->where('tid',$id)->select();

			if(empty($opt_sta)){
				$option_state['status'] = 'yes';
				$option_state['num'] = $ll['state_num'];
			}else{
				if(count($opt_sta)>=$ll['state_num']){
					$option_state['status'] = 'no';
					$option_state['num'] = '0';
				}else{
					$option_state['status'] = 'yes';
					$option_state['num'] = $ll['state_num']-count($opt_sta);
				}
			}

			if($ll['create_type']=='1' || $ll['create_type']=='3'){


			}elseif ($ll['create_type']=='2' || $ll['create_type']=='4'){
				if($ll['add_type']=='1'){//文字

					return view('cat_radio_text',['signPackage'=>$signPackage,'list'=>$ll,'creator'=>$creator,'option'=>$option,'reply_uid'=>$reply_uid,'ans_status'=>$ans_status,'attention'=>$attention,'self'=>$have, 'ans_count'=>$ans_count,'ans_arr'=>$ans_nlist,'create_user'=>$create_user,'option_state'=>$option_state]);
				}elseif($ll['add_type']=='2'){//餐饮娱乐

				}elseif($ll['add_type']=='3'){//景点线路
					return view('cat_radio_scenic',['signPackage'=>$signPackage,'list'=>$ll,'creator'=>$creator,'option'=>$option,'reply_uid'=>$reply_uid,'ans_status'=>$ans_status,'attention'=>$attention,'self'=>$have, 'ans_count'=>$ans_count,'ans_arr'=>$ans_nlist,'create_user'=>$create_user,'option_state'=>$option_state]);
				}elseif($ll['add_type']=='4'){//图片
					return view('cat_radio_img',['signPackage'=>$signPackage,'list'=>$ll,'creator'=>$creator,'option'=>$option,'reply_uid'=>$reply_uid,'ans_status'=>$ans_status,'attention'=>$attention,'self'=>$have, 'ans_count'=>$ans_count,'ans_arr'=>$ans_nlist,'create_user'=>$create_user,'option_state'=>$option_state]);
				}elseif($ll['add_type']=='5'){//商品
					return view('cat_radio_good',['signPackage'=>$signPackage,'list'=>$ll,'creator'=>$creator,'option'=>$option,'reply_uid'=>$reply_uid,'ans_status'=>$ans_status,'attention'=>$attention,'self'=>$have, 'ans_count'=>$ans_count,'ans_arr'=>$ans_nlist,'create_user'=>$create_user,'option_state'=>$option_state]);
				}
			}
		}


	}

	public function activity(){
		$create_type = input('param.create_type');
		if($create_type=='2'){
			return view('add_notice',['create_type'=>$create_type]);
		}else if($create_type=='3'){
			return view('add_activity',['create_type'=>$create_type]);
		}

	}

	public function activity_type(){
		$create_type = input('param.create_type');
		return view('activity_type');
	}

	public function activity_insert(){
		$create_type = input('param.create_type');
		$wx = new \wx_public();
		$wxInfoLib = new wxAuthLib();
		if($_POST){
			dump($_POST);exit;
			$data['title']=$_POST['title'];
			$data['con']=$_POST['con'];
			$data['time']=$_POST['time'];
			$data['address']=$_POST['address'];
			$data['guests']=$_POST['friend'];
			$data['location']=$_POST['location'];
			$data['create_type']=$create_type;
			$data['create_time'] = date('Y-m-d H:i:s');
			if ($create_type=='3'){
				$files = request()->file('con_img');
				$pic = null;
				if ($files){
					$info = $files->rule('uniqid')->move(ROOT_PATH . 'public' . DS . 'upload/ichoose_img');
					if($info){
						// 成功上传后 获取上传信息
						// 输出 jpg
						// 输出 42a79759f284b767dfcb2a0197904287.jpg
//						$pic  .=  $info->getFilename().',';
						$pic = $info->getFilename();
					}else{
						echo $files->getError();
					}
				}
				$data['img']=$pic;
				$data['type']='party';
				$data['open']=$_POST['open'];
				$data['cost_form']=$_POST['cost_form'];
				$data['bring']=$_POST['bring'];
				$data['people_num']=$_POST['party_num'];
			}else{
				$data['type']='notice';
			}
			$id = Db::table('i_activity_list')->insertGetId($data);
			$this->auath($id,'activity');
		}else{
			$id = input('param.id');
			$appid = input('param.key');
			$code = $_GET['code'];
			$aa = $wxInfoLib->getComponentAccessToken();
			$url = "https://api.weixin.qq.com/sns/oauth2/component/access_token?appid=$appid&code=$code&grant_type=authorization_code&component_appid=$this->appid&component_access_token=$aa";
			$res = $wxInfoLib->httpGet($url);
			$res = json_decode($res,true);
			$openid = $res['openid'];
			$access_token = $wx->access_token(1);
			$uid = Db::table('ty_account_user')->where('openid',$openid)->field('id')->find()['id'];
			$res = Db::table('i_activity_list')->where('id',$id)->update(['uid'=>$uid]);
			$b = new php_log();
			$b->logs($access_token);
			if($res){
				$ll = Db::table('i_activity_list')->where('id',$id)->find();
				$title = $ll['title'];
//				$cat_url = 'http://'.$_SERVER['HTTP_HOST'].url('cat_party',array('id'=>$id));
//				$url1 = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
//				$data = '{
//							"touser":"'.$openid.'",
//							"msgtype":"news",
//							"news": {
//								"articles": [
//									{
//										"title": "'.$ll['title'].'",
//										"description": "'.$ll['con'].'",
//										"picurl": "http://img.taopic.com/uploads/allimg/140306/235051-140306135P622.jpg",
//										"url": "'.$cat_url.'"
//									}
//       							]
//  							}
//						}';
//				$res = $wxInfoLib->httpPost($data,$url1);
				$cat_url = 'http://'.$_SERVER['HTTP_HOST'].url('cat_activity',array('id'=>$id));
				$url1 = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";
				$data = array(
					"touser"=>"$openid",
					"template_id"=>"uClry6KJvrOp5CnoBzgntJfPmjjOBmX_xYnLK2fTnZo",
					"url"=>"$cat_url",
					'data'=>array(
						'first'=>array(
							'value'=>'发起通知成功',
//							'color'=>'red'
						),
						'keyword1'=>array(
							'value'=>"$title",
							'color'=>'red'
						),
						'keyword2'=>array(
							'value'=>date('Y-m-d H:i:s'),
							'color'=>'red'
						),
						'remark'=>array(
							'value'=>'备注1111111'
						)
					)
				);
				$data = json_encode($data, JSON_UNESCAPED_UNICODE);
				$res = $wxInfoLib->httpPost($data,$url1);
				$b->logs($res);
				$jssdk = new \JSSDK();
				$signPackage = $jssdk->GetSignPackage();
				return view('end',['signPackage'=>$signPackage,'tishi'=>'发起成功']);
			}
		}

	}

	public function cat_activity(){
		$wx = new \wx_public();
		$wxInfoLib = new wxAuthLib();
		$id = input('param.id');
		$status = input('param.status');
		$list = Db::table('i_activity_list')->where('id',$id)->find();
		if(empty($status)){
			$this->auath($id,'cat_activity');
		}else{
			$code = $_GET['code'];
			$appid = input('param.key');
			$aa = $wxInfoLib->getComponentAccessToken();
			$url = "https://api.weixin.qq.com/sns/oauth2/component/access_token?appid=$appid&code=$code&grant_type=authorization_code&component_appid=$this->appid&component_access_token=$aa";
			$res = $wxInfoLib->httpGet($url);
			$res = json_decode($res,true);
			$openid = $res['openid'];
			$creator_res = Db::table('ty_account_user')->where('openid',$openid)->field('id,nickname')->find();
			if ($creator_res){
				if ($creator_res['id']==$list['uid']){
					$creator['status'] = 'yes';
					$creator['nickname'] = $creator_res['nickname'];
					$peo = Db::table('i_activity_reply')->where('a_id',$id)->select();
					$user_obj = Db::table('i_reply_user')->field('id,nickname')->select();
					if (!empty($peo)){
						foreach($peo as $k=>$v)
						{
							$join_peo[$k]['uid'] = $v['uid'];
							$join_peo[$k]['nickname'] = $this->find_nickname($v['uid'],$user_obj);
							if($v['join'] == 'yes'){
								$join_peo[$k]['join'] = '确定参加';
								$join_peo[$k]['peo_num'] = $v['peo_num'];
								$join_peo[$k]['remark'] = $v['remark'];
							}else{
								$join_peo[$k]['join'] = '确定不参加';
								$join_peo[$k]['remark'] = $v['remark'];
							}
						}
					}else{
						$join_peo = null;
					}
				}else{
					$join_peo = null;
					$creator['status'] = 'no';
					$creatorres = Db::table('ty_account_user')->where('id',$list['uid'])->field('id,nickname')->find();
					$creator['nickname'] = $creatorres['nickname'];
				}
			}else{

				$creatorres = Db::table('ty_account_user')->where('id',$list['uid'])->field('id,nickname')->find();
				$creator['nickname'] = $creatorres['nickname'];
				$creator['status'] = 'no';
				$join_peo = null;
			}

			$ass = $wx->access_token(1);
			$has = Db::table('i_reply_user')->where('openid',$openid)->find();
			if ($has){
				$user_info = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$ass&openid=$openid&lang=zh_CN";
				$ew = $wxInfoLib->httpGet($user_info);
				$res = json_decode($ew,true);
				if($res['subscribe']==0){
					$attention = 'no';
				}else{
					$attention = 'yes';
				}
				$have = Db::table('i_activity_reply')->where('uid',$has['id'])->where('a_id',$id)->find();
				if($have){
					$ans_status = 'yes';
				}else{
					$ans_status = 'no';
				}
				$reply_uid = $has['id'];
			}else{

				$ans_arr = null;
				$ans_count = null;
				$have = null;
				$ans_status = 'no';
				$access_token = $res['access_token'];
				//$user_info = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
				$user_info = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$ass&openid=$openid&lang=zh_CN";
				$ew = $wxInfoLib->httpGet($user_info);
				$res = json_decode($ew,true);
				if($res['subscribe']==0){
					$attention = 'no';
					$user_url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
					$user= $wxInfoLib->httpGet($user_url);
					$user = json_decode($user,true);
					$user_arr['openid'] = $user['openid'];
					$user_arr['nickname'] = $user['nickname'];
					$user_arr['sex'] = $user['sex'];
					$user_arr['language'] = $user['language'];
					$user_arr['city'] = $user['city'];
					$user_arr['province'] = $user['province'];
					$user_arr['country'] = $user['country'];
					$user_arr['headimgurl'] = $user['headimgurl'];
				}else{
					$attention = 'yes';
					$user_arr['openid'] = $res['openid'];
					$user_arr['nickname'] = $res['nickname'];
					$user_arr['sex'] = $res['sex'];
					$user_arr['language'] = $res['language'];
					$user_arr['city'] = $res['city'];
					$user_arr['province'] = $res['province'];
					$user_arr['country'] = $res['country'];
					$user_arr['headimgurl'] = $res['headimgurl'];
				}
				$reply_uid = Db::table('i_reply_user')->insertGetId($user_arr);
			}
		}
		if(count($join_peo)>=$list['people_num']){
			$join_state = 'no';
		}else{
			$join_state = 'yes';
		}
		$jssdk = new \JSSDK();
		$signPackage = $jssdk->GetSignPackage();
		if($list['create_type']=='2'){
			return view('cat_notice',['signPackage'=>$signPackage,'join_state'=>$join_state,'list'=>$list,'ans_status'=>$ans_status,'attention'=>$attention,'reply_uid'=>$reply_uid,'creator'=>$creator,'join_peo'=>$join_peo]);
		}else{
			return view('cat_party',['signPackage'=>$signPackage,'join_state'=>$join_state,'list'=>$list,'ans_status'=>$ans_status,'attention'=>$attention,'reply_uid'=>$reply_uid,'creator'=>$creator,'join_peo'=>$join_peo]);
		}



	}

	public function cat_notice(){
		$wx = new \wx_public();
		$wxInfoLib = new wxAuthLib();
		$id = input('param.id');
		$status = input('param.status');
		$list = Db::table('i_activity_list')->where('id',$id)->find();
		if(empty($status)){
			$this->auath($id,'cat_party');
		}else{
			$code = $_GET['code'];
			$appid = input('param.key');
			$aa = $wxInfoLib->getComponentAccessToken();
			$url = "https://api.weixin.qq.com/sns/oauth2/component/access_token?appid=$appid&code=$code&grant_type=authorization_code&component_appid=$this->appid&component_access_token=$aa";
			$res = $wxInfoLib->httpGet($url);
			$res = json_decode($res,true);
			$openid = $res['openid'];
			$creator_res = Db::table('ty_account_user')->where('openid',$openid)->field('id,nickname')->find();
		}
		if ($creator_res){

		}else{

		}
		$ass = $wx->access_token(1);
		$has = Db::table('i_reply_user')->where('openid',$openid)->find();
		if ($has){
			$user_info = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$ass&openid=$openid&lang=zh_CN";
			$ew = $wxInfoLib->httpGet($user_info);
			$res = json_decode($ew,true);
			if($res['subscribe']==0){
				$attention = 'no';
			}else{
				$attention = 'yes';
			}
			$have = Db::table('i_activity_reply')->where('uid',$has['id'])->where('a_id',$id)->find();
			if($have){
				$ans_status = 'yes';
			}else{
				$ans_status = 'no';
			}
			$reply_uid = $has['id'];
		}else{
			$ans_arr = null;
			$ans_count = null;
			$have = null;
			$ans_status = 'no';
			$access_token = $res['access_token'];
			//$user_info = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
			$user_info = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$ass&openid=$openid&lang=zh_CN";
			$ew = $wxInfoLib->httpGet($user_info);
			$res = json_decode($ew,true);
			if($res['subscribe']==0){
				$attention = 'no';
				$user_url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
				$user= $wxInfoLib->httpGet($user_url);
				$user = json_decode($user,true);
				$user_arr['openid'] = $user['openid'];
				$user_arr['nickname'] = $user['nickname'];
				$user_arr['sex'] = $user['sex'];
				$user_arr['language'] = $user['language'];
				$user_arr['city'] = $user['city'];
				$user_arr['province'] = $user['province'];
				$user_arr['country'] = $user['country'];
				$user_arr['headimgurl'] = $user['headimgurl'];
			}else{
				$attention = 'yes';
				$user_arr['openid'] = $res['openid'];
				$user_arr['nickname'] = $res['nickname'];
				$user_arr['sex'] = $res['sex'];
				$user_arr['language'] = $res['language'];
				$user_arr['city'] = $res['city'];
				$user_arr['province'] = $res['province'];
				$user_arr['country'] = $res['country'];
				$user_arr['headimgurl'] = $res['headimgurl'];
			}
			$reply_uid = Db::table('i_reply_user')->insertGetId($user_arr);
			return view('cat_party');
		}
	}

	public function insert_join_ajax(){
		$id = input('param.id');
		$uid = input('param.uid');
		$has = Db::table('i_activity_reply')->where('a_id',$id)->where('uid',$uid)->find();
		$data['a_id'] = $id;
		$data['uid'] = $uid;
		if ($has){
			return 3;
		}else{
			if($_POST['type']=='party'){
				if($_POST['join']=='yes') {
					$data['join'] = $_POST['join'];
					$data['peo_num'] = $_POST['peo_num'];
					$data['remark'] = $_POST['remark'];
				}else{
					$data['join'] = $_POST['join'];
					$data['remark'] = $_POST['remark'];
				}
			}else{
				$data['join'] = $_POST['join'];
				$data['remark'] = $_POST['remark'];
			}
			$res = Db::table('i_activity_reply')->insert($data);
			if($res){
				return 1;
			}else{
				return 2;
			}
		}

	}

	public function auath($id,$type){

		//==========================appid做成活的====
		$appid = 'wx844f289f684d283f';

		$wxInfoLib = new wxAuthLib();
		$wx = new \wx_public();
		if($type=='activity'){
			$redirect_uri = urlencode('http://'.$_SERVER['HTTP_HOST'].url('activity_insert',array('key'=>$appid,'id'=>$id)));
			$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_base&state=123&component_appid=$this->appid#wechat_redirect";
			$this->redirect($url);
		}else if($type=='text'){
			$redirect_uri = urlencode('http://'.$_SERVER['HTTP_HOST'].url('insert',array('key'=>$appid,'id'=>$id)));
			$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_base&state=123&component_appid=$this->appid#wechat_redirect";
			$this->redirect($url);
		}else if($type=='cat'){
			$redirect_uri = urlencode('http://'.$_SERVER['HTTP_HOST'].url('cat',array('key'=>$appid,'id'=>$id,'status'=>'yes')));
			$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_userinfo&state=123&component_appid=$this->appid#wechat_redirect";
			$this->redirect($url);
		}else if($type=='cat_activity'){
			$redirect_uri = urlencode('http://'.$_SERVER['HTTP_HOST'].url('cat_activity',array('key'=>$appid,'id'=>$id,'status'=>'yes')));
			$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_userinfo&state=123&component_appid=$this->appid#wechat_redirect";
			$this->redirect($url);
		}
	}

	//投票内容单选
	public function insert_radio_ajax(){
		$id = input('param.id');
		$uid = input('param.uid');

		$res = Db::table('i_vote_reply')->where('vo_id',$id)->where('uid',$uid)->find();
		if($res){
			$aa = Db::table('i_vote_reply')->where('vo_id',$id)->select();
			return $aa;
		}else{

			if(isset($_POST[$id]['text'])){
				$data['uid'] = $uid;
				$data['vo_id'] = $id;
				$data['reply'] = implode('@&',$_POST[$id]);

			}else{
				$data['uid'] = $uid;
				$data['vo_id'] = $id;
				$data['reply'] = $_POST[$id]['value'];

			}

			$result = Db::table('i_vote_reply')->insertGetId($data);
			if($result){
//				$aa = Db::table('i_vote_reply')->where('vo_id',$id)->select();
				return 1;
			}else{
				return 2;
			}
		}

	}

	public function insert_check_ajax(){
		$id = input('param.id');
		$uid = input('param.uid');
		$arr = $_POST[$id];
		$res = Db::table('i_vote_reply')->where('vo_id',$id)->where('uid',$uid)->find();
		if($res){
			$aa = Db::table('i_vote_reply')->where('vo_id',$id)->select();
			return $aa;
		}else{
			$aa = array_search("other",$arr);
			if($aa){
				$other = $_POST['other'];
				unset($arr[$aa]);
				$arr[$aa] = 'other@&'.$other;
				$val = implode('@#',$arr);
			}else{
				$val = implode('@#',$arr);
			}

			$data = array(
				'uid'=>$uid,
				'vo_id'=>$id,
				'reply'=>$val
			);
			$result = Db::table('i_vote_reply')->insertGetId($data);
			if($result){
				return 1;
			}else{
				return 2;
			}
		}
	}

	public function zhengji_insert(){

		$jssdk = new \JSSDK();
		$signPackage = $jssdk->GetSignPackage();

		$id = input('param.id');
		$uid = input('param.uid');
		$add_type = input('add_type');
		if($add_type=='3'){
			$option = $_POST['opt'];
			if(count($option)>1){
				foreach($option as $k=>$v){
					$data[$k]['tid'] = $id;
					$data[$k]['uid'] = $uid;
					$data[$k]['option'] = $v['option'];
					$data[$k]['keyword'] = $v['keyword'];
					$data[$k]['id_code'] = $v['id'];
				}
				$res = Db::table('i_option')->insertAll($data);
			}else{
				$data['tid'] = $id;
				$data['option'] = $option[1]['option'];
				$data['uid'] = $uid;
				$data['keyword'] = $option[1]['keyword'];
				$data['id_code'] = $option[1]['id'];
				$res = Db::table('i_option')->insertGetId($data);
			}
		}elseif($add_type=='5'){
			$option = $_POST['opt'];
			if(count($option)>1){
				foreach($option as $k=>$v){
					$data[$k]['tid'] = $id;
					$data[$k]['uid'] = $uid;
					$data[$k]['option'] = $v['option'];
					$data[$k]['pic_url'] = $v['pic_url'];
					$data[$k]['id_code'] = $v['good_id'];
					$data[$k]['url'] = $v['url'];
					$data[$k]['merchant'] = $v['merchant'];
				}
				$res = Db::table('i_option')->insertAll($data);
			}else{
				$data['tid'] = $id;
				$data['uid'] = $uid;
				$data['option'] = $option[1]['option'];
				$data['pic_url'] = $option[1]['pic_url'];
				$data['id_code'] = $option[1]['good_id'];
				$data['url'] = $option[1]['url'];
				$data['merchant'] = $option[1]['merchant'];
				$res = Db::table('i_option')->insertGetId($data);
			}
		}else{
			$option = $_POST['option'];
			if(count($option)>1){
				foreach($option as $k=>$v){
					$data[$k]['tid'] = $id;
					$data[$k]['uid'] = $uid;
					$data[$k]['option'] = $v;
				}
				$res = Db::table('i_option')->insertAll($data);
			}else{
				$data['tid'] = $id;
				$data['option'] = $option[0];
				$data['uid'] = $uid;
				$res = Db::table('i_option')->insertGetId($data);
			}
		}
		if($res){
			return 1;
		}else{
			return 2;
		}
	}

	public function end_zhengji(){
		$id = input('param.id');
		$data['state'] = '1';
		$res = Db::table('i_vote_list')->where('id',$id)->update($data);
		if($res){
			return 1;
		}else{
			return 2;
		}
	}

	//关键词搜索景点:
	public function scenic_search(){
		$wxInfoLib = new wxAuthLib();
		$num = input('param.num');

		if($_POST && !empty($_POST['keyword'])){
			return 12;
			$keyword = $_POST['keyword'];
			$showapi_appid = $this->showapi_appid;
			$showapi_secret =$this->showapi_secret;
			$paramArr = array(
				'showapi_appid'=> $showapi_appid,
				'keyword'=> $keyword,
				'proId'=> "",
				'cityId'=> "",
				'areaId'=> "",
				'page'=> ""
				//添加其他参数
			);
			$param = $this->createParam($paramArr,$showapi_secret);
			$url = 'http://route.showapi.com/268-1?'.$param;
			$res = $wxInfoLib->httpGet($url);

			$result = json_decode($res,true);
			$page = $result['showapi_res_body']['pagebean']['allPages'];

			if($page>1){
				for ($i=2;$i<=$page;$i++){
					$paramArr_new = array(
						'showapi_appid'=> $showapi_appid,
						'keyword'=> $keyword,
						'proId'=> "",
						'cityId'=> "",
						'areaId'=> "",
						'page'=> $i
						//添加其他参数
					);
				}
				$param = $this->createParam($paramArr_new,$showapi_secret);
			}elseif($page==1){
				$content = $result['showapi_res_body']['pagebean']['contentlist'];
				foreach($content as $k=>$v){
					$con_list[$k]['name'] = $v['name'];
					if(array_key_exists($v['name'],$v)){
						$con_list[$k]['cityName'] = $v['cityName'];
					}else{
						$con_list[$k]['cityName'] = $v['proName'];
					}
					$con_list[$k]['id'] = $v['id'];
					$con_list[$k]['summary'] = $v['summary'];
				}

			}else{
				$con_list = '搜索内容为空';
			}

		}else{
			return 'kong';
			$con_list=null;
			$keyword =null;
		}
		return view('scenic_search',array('num'=>$num,'list'=>$con_list,'keyword'=>$keyword));

	}

	public function scenic_search_ajax(){
		$keyword = $_POST['keyword'];

		$wxInfoLib = new wxAuthLib();
		$showapi_appid = $this->showapi_appid;
		$showapi_secret =$this->showapi_secret;
		$paramArr = array(
			'showapi_appid'=> $showapi_appid,
			'keyword'=> $keyword,
			'proId'=> "",
			'cityId'=> "",
			'areaId'=> "",
			'page'=> ""
			//添加其他参数
		);
		$param = $this->createParam($paramArr,$showapi_secret);
		$url = 'http://route.showapi.com/268-1?'.$param;
		$res = $wxInfoLib->httpGet($url);
		$result = json_decode($res,true);
		$page = $result['showapi_res_body']['pagebean']['allPages'];

		if($page>1){
			$content = $result['showapi_res_body']['pagebean']['contentlist'];
			for ($i=2;$i<=$page;$i++){
				$paramArr_new = array(
					'showapi_appid'=> $showapi_appid,
					'keyword'=> $keyword,
					'proId'=> "",
					'cityId'=> "",
					'areaId'=> "",
					'page'=> $i
					//添加其他参数
				);
				$param = $this->createParam($paramArr_new,$showapi_secret);
				$url = 'http://route.showapi.com/268-1?'.$param;
				$res = $wxInfoLib->httpGet($url);
				$result = json_decode($res,true);
//				$content[] = $result['showapi_res_body']['pagebean']['contentlist'];
//				array_push($content,$result['showapi_res_body']['pagebean']['contentlist']);
				$content = array_merge($content,$result['showapi_res_body']['pagebean']['contentlist']);
			}			
			foreach($content as $k=>$v){
				$con_list[$k]['name'] = $v['name'];
				if(array_key_exists($v['name'],$v)){
					$con_list[$k]['cityName'] = $v['cityName'];
				}else{
					$con_list[$k]['cityName'] = $v['proName'];
				}
				$con_list[$k]['id'] = $v['id'];
				$con_list[$k]['summary'] = $v['summary'];
			}
			return $con_list;
		}elseif($page==1){
			$content = $result['showapi_res_body']['pagebean']['contentlist'];
			foreach($content as $k=>$v){
				$con_list[$k]['name'] = $v['name'];
				if(array_key_exists($v['name'],$v)){
					$con_list[$k]['cityName'] = $v['cityName'];
				}else{
					$con_list[$k]['cityName'] = $v['proName'];
				}
				$con_list[$k]['id'] = $v['id'];
				$con_list[$k]['summary'] = $v['summary'];
			}

		}else{
			$con_list = '11';
		}
		return $con_list;
	}

	public function scenic_con(){
		$wxInfoLib = new wxAuthLib();
		$showapi_appid = $this->showapi_appid;
		$showapi_secret =$this->showapi_secret;
		$id = input('param.id');
		$keyword = input('param.keyword');
		$paramArr = array(
			'showapi_appid'=> $showapi_appid,
			'keyword'=> $keyword,
			'proId'=> "",
			'cityId'=> "",
			'areaId'=> "",
			'page'=> ""
			//添加其他参数
		);
		$param = $this->createParam($paramArr,$showapi_secret);
		$url = 'http://route.showapi.com/268-1?'.$param;
		$res = $wxInfoLib->httpGet($url);
		$res = json_decode($res,true);
		$content = $res['showapi_res_body']['pagebean']['contentlist'];
		foreach($content as $k=>$v){
			if($v['id']==$id){
				$con = $v;
			}
		}

		return view('scenic_con',['list'=>$con]);

	}


	public function scenic_con_ajax(){
		$wxInfoLib = new wxAuthLib();
		$showapi_appid = $this->showapi_appid;
		$showapi_secret =$this->showapi_secret;
		$id = input('param.id');
		$keyword = input('param.keyword');
		$paramArr = array(
			'showapi_appid'=> $showapi_appid,
			'keyword'=> $keyword,
			'proId'=> "",
			'cityId'=> "",
			'areaId'=> "",
			'page'=> ""
			//添加其他参数
		);
		$param = $this->createParam($paramArr,$showapi_secret);
		$url = 'http://route.showapi.com/268-1?'.$param;
		$res = $wxInfoLib->httpGet($url);
		$res = json_decode($res,true);
		$content = $res['showapi_res_body']['pagebean']['contentlist'];
		foreach($content as $k=>$v){
			if($v['id']==$id){
				$con = $v;
			}
		}
		return $con;
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
		return $paraStr;
	}

    public  function cx_jdcode_ajax(){
		$code_id = $_POST['code'];
		$app_key =$this->app_key;
		$wxInfoLib = new wxAuthLib();

		$url = "https://way.jd.com/JDCloud/baseproduct?ids=$code_id&basefields=name,weight,height,imagePath&appkey=$app_key";
		$res = $wxInfoLib->httpGet($url);
		$res = json_decode($res,true);
		$data = $res['result']['jingdong_new_ware_baseproduct_get_responce']['listproductbase_result'][0];
		return $data;
	}






}