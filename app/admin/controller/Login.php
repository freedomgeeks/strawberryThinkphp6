<?php
namespace app\admin\controller;

use app\BaseController;
use think\facade\View;
use think\facade\Db;
use think\facade\Session;
class Login extends BaseController
{
	
    public function index(){
    	//var_dump($this->request->isPost());exit();
    	/* if($this->request->isPost()){
    		$this->error('111');
    	} */
    	if(Session::has('admin')){
    		$this->error('你已登陆','index/index');
    	}
    	
    	// 模板输出
        return View::fetch();
    }
	
    public function signin(){
    	if(Session::has('admin')){
    		$this->error('你已登陆','index/index');
    	}
    	$captcha = $this->request->param('captcha');
    	$username = $this->request->param('username');
    	$password = $this->request->param('password');
    	if(!captcha_check($captcha)){
    		// 验证失败
    		$this->error('验证码错误');
    	};
    	
    	$admininfo = Db::name('admin')->where('username',$username)->find();
    	if(empty($admininfo)){
    		$this->error('账号/密码错误.');
    	}
    	if($admininfo['status']!='normal'){
    		$this->error('账户被禁用');
    	}
    	if($admininfo['password']!=md5(md5($password).$admininfo['salt'])){
    		$this->error('账号/密码错误');
    	}
    	session('admin',$admininfo);
    	$this->success('登陆成功');
    }
    public function logout(){
    	Session::delete('admin');
    	$this->redirect("/admin/login/index");
    }
}
