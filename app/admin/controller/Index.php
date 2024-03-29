<?php

namespace app\admin\controller;



use think\facade\View;

use think\facade\Db;

use think\facade\Lang;



class Index extends AdminBase

{

    public function lang($lang)

    {

        Lang::setLangSet($lang);

    }

    public function index()

    {

        // 模板输出

        // 模板变量赋值

        $admin = session('admin');

        $admininfo = Db::name('admin')->where('id', $admin['id'])->find();

        View::assign('admininfo', $admininfo);

        return View::fetch();

    }

    public function welcome()

    {

        return View::fetch();

    }

    public function menu()
    {
         $menus = \app\admin\model\Base::getMenusJson();
         file_put_contents(public_path().'static/admin/data/menu.json', json_encode($menus,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));

         return json($menus)->header(['contentType'=>'application/json']);
    }

   

}

