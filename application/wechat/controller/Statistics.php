<?php

namespace app\wechat\controller;

use think\Db;
use wx_api;
use think\Controller;
use think\log;
use \php_log;
use think\User_config;
use \wx_public;
use wxAuthLib;
use weekdate;
use con_public;
use verification;
use think\Url;

class Statistics extends User_config
{
    public $uniacid;
    public $action;
    public $menu;
    public function __construct()
    {
        parent::__construct();
        $request=  \think\Request::instance();
        $verification = new verification();
        $menu = input('param.menu');
        $action = input('param.action');
        if(empty($action)){
            $this->assign('action',$request->action());
            $this->action = $request->action();
        }else{
            $this->assign('action',$action);
            $this->action = $action;
        }
        if(empty($menu)){
            $this->assign('menu',$request->controller());
            $this->menu = $request->controller();
        }else{
            $this->assign('menu',$menu);
            $this->action = $menu;
        }
        $this->uniacid = $verification->wechat_action();
    }

    /**
     * @return mixed
     * 关键指标详解
     */
    public function data_chart()
    {
        $public = new con_public();
        $id = input('param.id');
        $time = input('param.time');
        if(!empty($time)){
            $time = explode('~',$_POST['time']);
            $start =$time[0];
            $end = $time[1];
            $form['time'] = $time[0].'&nbsp;至&nbsp;'.$time[1];
        }else{
            $start = null;
            $end = null;
            $form = null;
        }
        $find =  Db::name('reply')->where('id',$id)->find();
        $data = $public->replay_chart($id,$start,$end);
        $this->assign('data',$data);
        $this->assign('id',$id);
        $this->assign('id',$id);
        $this->assign('form',$form);
        $this->assign('find',$find);
        $this->assign('menu',$this->menu);
        return $this -> fetch();
    }

    public function rule()
    {
        $type = input('param.type');
        if(empty($type)){
            $type = "already";
        }
        $time = input('param.time');
        $where = '1=1';
        if(!empty($time)){
            $time = explode('~',$time);
            $start =$time[0];
            $end = $time[1];
            $form['time'] = $time[0].'&nbsp;至&nbsp;'.$time[1];
            $where .=" and a.addtime>= '{$start}' and a.addtime<= '{$end}'";
        }else{
            $start = null;
            $end = null;
            $form = null;
        }
        $prefix = config('database.prefix');
        if($type=="already"){
            $subQuery = Db::table($prefix.'account_reply_num')
                ->where('uniacid',$this->uniacid)
                ->order('date desc')
                ->buildSql();
            $find =  Db::table($subQuery)
                ->alias('a')
                ->join($prefix.'reply b','a.replyid=b.id','right')
                ->where('a.uniacid',$this->uniacid)
                ->where($where)
                ->order('a.date desc')
                ->group('a.replyid')
                ->field('count(*) as count,a.date,a.replyid,b.title,b.start,b.keywords')
                ->paginate(10);
        }else{
            $find =  Db::table($prefix.'reply')
                ->alias('a')
                ->join($prefix.'account_reply_num b','a.id=b.replyid','left')
                ->where('a.uniacid',$this->uniacid)
                ->where('b.replyid',null)
                ->field('a.*,a.id as replyid')
                ->paginate(10);
        }
        $page = $find->render();
        $this->assign('form',$form);
        $this->assign('type',$type);
        $this->assign('find',$find);
        $this->assign('page',$page);
        return $this -> fetch();
    }
    public function keyword()
    {
        $type = input('param.type');
        if(empty($type)){
            $type = "already";
        }
        $time = input('param.time');
        $where = '1=1';
        if(!empty($time)){
            $time = explode('~',$time);
            $start =$time[0];
            $end = $time[1];
            $form['time'] = $time[0].'&nbsp;至&nbsp;'.$time[1];
            $where .=" and a.addtime>= '{$start}' and a.addtime<= '{$end}'";
        }else{
            $start = null;
            $end = null;
            $form = null;
        }
        $prefix = config('database.prefix');
        if($type=="already"){
            $subQuery = Db::table($prefix.'account_reply_num')
                ->where('uniacid',$this->uniacid)
                ->order('date desc')
                ->buildSql();
            $find =  Db::table($subQuery)
                ->alias('a')
                ->join($prefix.'reply b','a.replyid=b.id','right')
                ->where('a.uniacid',$this->uniacid)
                ->where($where)
                ->order('a.date desc')
                ->group('a.replyid')
                ->field('count(*) as count,a.date,a.replyid,b.title,b.start,b.keywords')
                ->paginate(10);
        }else{
            $find =  Db::table($prefix.'reply')
                ->alias('a')
                ->join($prefix.'account_reply_num b','a.id=b.replyid','left')
                ->where('a.uniacid',$this->uniacid)
                ->where('b.replyid',null)
                ->field('a.*,a.id as replyid')
                ->paginate(10);
        }
        $page = $find->render();
        $this->assign('form',$form);
        $this->assign('type',$type);
        $this->assign('find',$find);
        $this->assign('page',$page);
        return $this -> fetch();
    }
}
