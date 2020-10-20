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
    	$keep_login = $this->request->param('keep_login');
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
    	
    	// 查找规则
    	$rules = Db::name('auth_group_access')
    	->alias('a')
    	->leftJoin('auth_group ag', 'a.group_id = ag.id')
    	->field('a.group_id,ag.rules,ag.title')
    	->where('uid', $admininfo['id'])
    	->find();
    	$admininfo['expire_time'] = $keep_login == 1 ? true : time() + 7200;
    	session('admin',$admininfo);
    	
    	Session::set('admin.group_id' , $rules['group_id']);
    	Session::set('admin.rules'    , explode(',', $rules['rules']));
    	Session::set('admin.title'    , $rules['title']);
    	 
    	
    	$this->success('登陆成功');
    }
    public function logout(){
    	Session::delete('admin');
    	return redirect('index');
    }
	
	public function test(){
		if($this->request->isAjax()){
			$list = [
				['user_id' => 1, 'name' => '武则天'],
				['user_id' => 2, 'name' => '小乔'],
				['user_id' => 3, 'name' => '司马懿'],
				['user_id' => 4, 'name' => '妲己'],
				['user_id' => 5, 'name' => '张良'],
			];
			$data = [
				'code'  =>  1,
				'msg'   =>  null,
				'data'  =>  $list,
			];
			return json($data);
		}
		return View::fetch();
	}
}
