<?php
namespace addons\test;	// 注意命名空间规范

use app\common\library\Menu;
use think\Addons;

/**
 * 插件测试
 * @
 */
class Plugin extends Addons	// 需继承think\Addons类
{
	// 该插件的基础信息
	public $info = [
		'name' => 'test',	// 插件标识
		'title' => '插件测试',	// 插件名称
		'description' => 'thinkph6插件测试',	// 插件简介
		'status' => 1,	// 状态
		'author' => 'bytest',
		'version' => '0.1',
		'install'     => 0,                 // 是否已安装[1 已安装，0 未安装]
	];

	/**
	 * 插件安装方法
	 * @return bool
	 */
	public function install()
	{
		$menu = [
            [
                'name'    => 'test',
                'title'   => '测试插件菜单',
                'icon'    => '&#xe66f;',
                'ismenu'  => 1,//是否是菜单
                'sublist' => [
                    ['name' => 'addons/test/index/index', 'title' => '查看'],
                    ['name' => 'addons/test/index/add', 'title' => '添加'],
                    ['name' => 'addons/test/index/detail', 'title' => '详情'],
                ]
            ]
        ];
        Menu::create($menu);
		return true;
	}

	/**
	 * 插件卸载方法
	 * @return bool
	 */
	public function uninstall()
	{
		
		Menu::delete('test');
		return true;
	}

	/**
	 * 实现的testhook钩子方法
	 * @return mixed
	 */
	public function testhook($param)
	{
		// 调用钩子时候的参数信息
		print_r($param);
		// 当前插件的配置信息，配置信息存在当前目录的config.php文件中，见下方
		print_r($this->getConfig());
		// 可以返回模板，模板文件默认读取的为插件目录中的文件。模板名不能为空！
		return $this->fetch('info');
	}


}