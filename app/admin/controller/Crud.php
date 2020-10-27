<?php
//  *+-----------------------------------------------------------------------
//  *                      .::::.
//  *                    .::::::::.            | Author: 丶长情
//  *                    :::::::::::           | Email: zeng1144318071@gmail.com
//  *                 ..:::::::::::'           | Datetime: 2020/09/30
//  *             '::::::::::::'               | Remarks: 自动api生成插件
//  *                .::::::::::
//  *           '::::::::::::::..
//  *                ..::::::::::::.
//  *              ``::::::::::::::::
//  *               ::::``:::::::::'        .:::.
//  *              ::::'   ':::::'       .::::::::.
//  *            .::::'      ::::     .:::::::'::::.
//  *           .:::'       :::::  .:::::::::' ':::::.
//  *          .::'        :::::.:::::::::'      ':::::.
//  *         .::'         ::::::::::::::'         ``::::.
//  *     ...:::           ::::::::::::'              ``::.
//  *   ```` ':.          ':::::::::'                  ::::..
//  *                      '.:::::'                    ':'````..
//  * +-----------------------------------------------------------------------
namespace app\admin\controller;

use app\common\library\Menu;
use app\admin\model\MyCrud;
use think\Request;
use app\admin\validate\Applets as adminValidate;
use think\exception\PDOException;
use think\facade\Db;
use think\Exception;
use think\facade\View;

/**
 * Api自动生成
 *
 * @icon fa fa-circle-o
 */
class Crud extends Admin
{

    public function getList()
    {
        // 查询所有表
        $sql = "SELECT TABLE_NAME,TABLE_COMMENT FROM information_schema.TABLES WHERE table_schema='" . config('database.connections.mysql.database') . "'";
        $tables = Db::query($sql);
        $data = [];
        foreach ($tables as $k => $v) {
            $data[$k]['id'] = $k + 1;
            $name = explode('_', $v['TABLE_NAME']);
            array_shift($name);
            $data[$k]['name'] = implode('_', $name);
            $data[$k]['comment'] = $v['TABLE_COMMENT'];
        }
        return json([
            'code' => 0,
            'count' => count($tables),
            'data' => $data,
            'msg' => '查询成功'
        ]);
    }

    public function crud(Request $request)
    {

        try {
            $table = $request->post('name');
            $sql = 'show table status';
            $tableList = Db::query($sql);
            $tableList = array_map('array_change_key_case', $tableList);
            $fix = "";
            foreach ($tableList as $key => $value) {
                if ($value['name'] === config('database.connections.mysql.prefix') . $table) {
                    $fix = $value['comment'];
                }
            }

//            $menu = [
//                [
//                    'name' => 'admin/' . $table . '/index',
//                    'title' => $fix . '管理',
//                    'icon' => 'fa-list',
//                    'remark' => '',
//                    'ismenu' => 1,
//                    'sublist' => [
//                        ['name' => 'admin/' . $table . '/add', 'title' => '添加'],
//                        ['name' => 'admin/' . $table . '/edit', 'title' => '编辑 '],
//                        ['name' => 'admin/' . $table . '/del', 'title' => '删除']
//                    ]
//                ]
//            ];
//            Menu::create($menu);

            // 生成controller
            $controllerFile = fopen("../app/admin/controller/" . ucwords($table) . ".php", "w");
            $controllerText = sprintf(file_get_contents('../addons/crud/control.txt'),
                ucwords($table), $table, $table, $table, $table, $table, $table, $table, $table, $table, $table, $table,$table
            );
            fwrite($controllerFile, $controllerText);
            fclose($controllerFile);
            // 生成view index
            $path = "../app/admin/view/" . $table;
            if (!file_exists($path))
                mkdir($path, 0777, true);

            $viewFile = fopen($path . "/" . "index.html", "w");
            $viewText = sprintf(file_get_contents('../addons/crud/index.txt'),
                self::getViewFiledList($table), '90%', '90%', '90%', '90%', '100%'
            );
            fwrite($viewFile, $viewText);
            fclose($viewFile);

            // 生成view add
            $path = "../app/admin/view/" . $table;
            if (!file_exists($path))
                mkdir($path, 0777, true);

            $viewFile = fopen($path . "/" . "add.html", "w");
            $viewText = sprintf(file_get_contents('../addons/crud/add.txt'),
                self::getViewAddHtml($table), self::xm($table), self::timejsadd($table)
            );
            fwrite($viewFile, $viewText);
            fclose($viewFile);

            // 生成view edit
            $path = "../app/admin/view/" . $table;
            if (!file_exists($path))
                mkdir($path, 0777, true);

            $viewFile = fopen($path . "/" . "edit.html", "w");
            $viewText = sprintf(file_get_contents('../addons/crud/edit.txt'),
                self::getViewEditHtml($table), self::xm($table), self::timejs($table)
            );
            fwrite($viewFile, $viewText);
            fclose($viewFile);
            $this->success("生成成功");
        } catch (Exception $e) {
            $this->error($e->getMessage());
            $this->error("生成失败,请先删除原有菜单");
        }

    }

    public static function getViewFiledList($table)
    {
        $list = Db::query('SHOW FULL FIELDS FROM ' . config('database.connections.mysql.prefix') . $table);
        $list = array_map('array_change_key_case', $list);
        $str = "";
        foreach ($list as $elt => $item) {
            if ($item['field'] == 'switch'){
                $str .= "{field: '" . $item['field'] . "', title: '" . explode(':', $item['comment'])[0] . "',templet: function (d) {
        var state = \"\";
        if (d.switch == \"on\") {
            state = \"<input type='checkbox' value='\" + d.id + \"' id='switch' lay-filter='stat' checked='checked' name='switch'  lay-skin='switch' lay-text='开启|关闭' >\";
        } else {
            state = \"<input type='checkbox' value='\" + d.id + \"' id='switch' lay-filter='stat'  name='switch'  lay-skin='switch' lay-text='开启|关闭' >\";
        }
        return state;
    }}," . PHP_EOL;
            }else{
                $str .= "{field: '" . $item['field'] . "', title: '" . explode(':', $item['comment'])[0] . "'},". PHP_EOL;
            }
        }
        return $str;
    }

    public static function getViewAddHtml($table)
    {

        $list = Db::query('SHOW FULL FIELDS FROM ' . config('database.connections.mysql.prefix') . $table);
        $list = array_map('array_change_key_case', $list);
        $str = "";
        foreach ($list as $elt => $item) {
            $s = explode('_', $item['field']);
            if ($item['key'] === 'PRI') {
                continue;
            } else if (explode('(', $item['type'])[0] === 'enum' && $item['field'] === 'state') {
                $str .= "<div class=\"layui-form-item\">
    <label class=\"layui-form-label\">" . explode(':', $item['comment'])[0] . "</label>
    <div class=\"layui-input-block\">
        " . self::danxuan($item['field'], $item) . "
    </div>
  </div>";
            } else if (explode('(', $item['type'])[0] === 'enum') {
                $str .= "        <div class=\"layui-form-item\">
            <label class=\"layui-form-label\">" . explode(':', $item['comment'])[0] . "</label>
            <div class=\"layui-input-block\">
                <select name=\"" . $item['field'] . "\" lay-verify=\"required\">
                    <option value=\"\"></option>
                    " . self::xiala($item) . "
                </select>
            </div>
        </div>";
            } else if (explode('(', $item['type'])[0] === 'set') {
                $str .= "<div class=\"layui-form-item\">
    <label class=\"layui-form-label\">" . explode(':', $item['comment'])[0] . "</label>
    <div class=\"layui-input-block\">
    " . self::duoxuan($item['field'], $item) . "
    </div>
  </div>";
            } else if (explode('(', $item['type'])[0] === 'text') {
                $str .= " <div class=\"layui-form-item layui-form-text\">
    <label class=\"layui-form-label\">" . explode(':', $item['comment'])[0] . "</label>
    <div class=\"layui-input-block\">
      <textarea name=\"" . $item['field'] . "\" placeholder=\"请输入内容\" class=\"layui-textarea\"></textarea>
    </div>
  </div>";
            } else if ($item['field'] === 'switch') {
                $str .= "    <div class=\"layui-form-item\">
    <label class=\"layui-form-label\">" . explode(':', $item['comment'])[0] . "</label>
    <div class=\"layui-input-block\">
      <input type=\"checkbox\" name=\"" . $item['field'] . "\" lay-skin=\"switch\" lay-text=\"on|off\">
    </div>
  </div>";
            } else if (explode('(', $item['type'])[0] === 'int' && end($s) === 'time') {
                $str .= "  <div class=\"layui-form-item\">
    <div class=\"layui-inline\">
      <label class=\"layui-form-label\">" . explode(':', $item['comment'])[0] . "</label>
      <div class=\"layui-input-block\">
        <input type=\"text\" name=\"" . $item['field'] . "\" id=\"" . $item['field'] . "\" autocomplete=\"off\" class=\"layui-input\" placeholder='yy-mm-dd HH:ii:ss'>
      </div>
    </div>
  </div>";
            } else if (end($s) === 'id') {
                $str .= "  <div class=\"layui-form-item\">
            <label class=\"layui-form-label\">" . explode(':', $item['comment'])[0] . "</label>
            <div class=\"layui-input-block\">
                <select name=\"" . $item['field'] . "\" lay-verify=\"required\">
                    <option value=\"\"></option>
                    " . self::xialaid($item) . "
                </select>
            </div>
        </div>";
            } else if (end($s) === 'ids') {
                $str .= "
        <div class=\"layui-form-item\">
            <label class=\"layui-form-label\">关联ids</label>
            <div class=\"layui-input-block\">
                <div id=\"" . $item['field'] . "\"></div>
            </div>
        </div>";
            } else if (explode('(', $item['type'])[0] === 'datetime') {
                $str .= "  <div class=\"layui-form-item\">
    <div class=\"layui-inline\">
      <label class=\"layui-form-label\">" . explode(':', $item['comment'])[0] . "</label>
      <div class=\"layui-input-block\">
        <input type=\"text\" name=\"" . $item['field'] . "\" id=\"" . $item['field'] . "\" autocomplete=\"off\" class=\"layui-input\" placeholder='yy-mm-dd HH:ii:ss'>
      </div>
    </div>
  </div>";
            } else {
                $str .= "<div class=\"layui-form-item\">
                    <label class=\"layui-form-label\">" . explode(':', $item['comment'])[0] . "</label>
                    <div class=\"layui-input-block\">
                        <input type=\"text\" name=\"" . $item['field'] . "\" placeholder=\"请输入" . $item['comment'] . "\" autocomplete=\"off\" class=\"layui-input\"
                               lay-verify=\"required\">
                    </div>
                </div>";
            }
        }
        return $str;
    }

    public static function getViewEditHtml($table)
    {
//        SHOW FULL COLUMNS FROM tbl_name [FROM db_name]
        $list = Db::query('SHOW FULL FIELDS FROM ' . config('database.connections.mysql.prefix') . $table);
        $list = array_map('array_change_key_case', $list);
        $str = "";
        foreach ($list as $elt => $item) {
            $s = explode('_', $item['field']);
            if ($item['field'] === Db::name("$table")->getPk()) {
                $str .= "<input type=\"hidden\" name=\"" . $item['field'] . "\" placeholder=\"\" autocomplete=\"off\" class=\"layui-input\" value=\"" . '{$' . "" . $table . "." . $item['field'] . "}\">";
            } else if (explode('(', $item['type'])[0] === 'enum' && $item['field'] === 'state') {
                $str .= "<div class=\"layui-form-item\">
    <label class=\"layui-form-label\">" . explode(':', $item['comment'])[0] . "</label>
    <div class=\"layui-input-block\">
        " . self::danxuanedit($table, $item['field'], $item) . "
    </div>
  </div>";
            } else if (explode('(', $item['type'])[0] === 'enum') {
                $str .= "        <div class=\"layui-form-item\">
            <label class=\"layui-form-label\">" . explode(':', $item['comment'])[0] . "</label>
            <div class=\"layui-input-block\">
                <select name=\"" . $item['field'] . "\" lay-verify=\"required\">
                    <option value=\"\"></option>
                    " . self::xialaedit($table, $item['field'], $item) . "
                </select>
            </div>
        </div>";
            } else if (end($s) === 'id') {
                $str .= "        <div class=\"layui-form-item\">
            <label class=\"layui-form-label\">" . explode(':', $item['comment'])[0] . "</label>
            <div class=\"layui-input-block\">
                <select name=\"" . $item['field'] . "\" lay-verify=\"required\">
                    <option value=\"\"></option>
                    " . self::xialaidedit($table, $item['field'], $item) . "
                </select>
            </div>
        </div>";
            } else if (end($s) === 'ids') {
                $str .= "
        <div class=\"layui-form-item\">
            <label class=\"layui-form-label\">关联ids</label>
            <div class=\"layui-input-block\">
                <div id=\"" . $item['field'] . "\"></div>
            </div>
        </div>";
            } else if (explode('(', $item['type'])[0] === 'set') {
                $str .= "
<div class=\"layui-form-item\">
    <label class=\"layui-form-label\">" . explode(':', $item['comment'])[0] . "</label>
    <div class=\"layui-input-block\">
    " . self::duoxuanedit($table, $item['field'], $item) . "
    </div>
</div>";
            } else if (explode('(', $item['type'])[0] === 'text') {
                $str .= " <div class=\"layui-form-item layui-form-text\">
    <label class=\"layui-form-label\">" . explode(':', $item['comment'])[0] . "</label>
    <div class=\"layui-input-block\">
      <textarea name=\"" . $item['field'] . "\" placeholder=\"请输入内容\" class=\"layui-textarea\">" . '{$' . "" . $table . "." . $item['field'] . "}</textarea>
    </div>
  </div>";
            } else if ($item['field'] === 'switch') {
                $str .= "    <div class=\"layui-form-item\">
    <label class=\"layui-form-label\">" . explode(':', $item['comment'])[0] . "</label>
    <div class=\"layui-input-block\">
      <input type=\"checkbox\" name=\"" . $item['field'] . "\" lay-skin=\"switch\" lay-text=\"on|off\" {if $" . $table . "." . $item['field'] . " == 'on'}checked{/if}>
    </div>
  </div>";
            } else if (explode('(', $item['type'])[0] === 'int' && end($s) === 'time') {
                $str .= "  <div class=\"layui-form-item\">
    <div class=\"layui-inline\">
      <label class=\"layui-form-label\">" . explode(':', $item['comment'])[0] . "</label>
      <div class=\"layui-input-block\">
        <input type=\"text\" name=\"" . $item['field'] . "\" id=\"" . $item['field'] . "\" autocomplete=\"off\" class=\"layui-input\" placeholder='yy-mm-dd HH:ii:ss' value='" . '{$' . "" . $table . "." . $item['field'] . "|date=\"Y-m-d H:i:s\"}'>
      </div>
    </div>
  </div>";
            } else if (explode('(', $item['type'])[0] === 'datetime') {
                $str .= "  <div class=\"layui-form-item\">
    <div class=\"layui-inline\">
      <label class=\"layui-form-label\">" . explode(':', $item['comment'])[0] . "</label>
      <div class=\"layui-input-block\">
        <input type=\"text\" name=\"" . $item['field'] . "\" id=\"" . $item['field'] . "\" autocomplete=\"off\" class=\"layui-input\" placeholder='yy-mm-dd HH:ii:ss' value='" . '{$' . "" . $table . "." . $item['field'] . "}'>
      </div>
    </div>
  </div>";
            } else {
                $str .= "<div class=\"layui-form-item\">
                    <label class=\"layui-form-label\">" . $item['comment'] . "</label>
                    <div class=\"layui-input-block\">
                        <input type=\"text\" name=\"" . $item['field'] . "\" placeholder=\"请输入" . $item['comment'] . "\" autocomplete=\"off\" class=\"layui-input\"
                               lay-verify=\"required\" value=\"" . '{$' . "" . $table . "." . $item['field'] . "}\">
                    </div>
                </div>";
            }

        }
        return $str;
    }

    public static function timejsadd($table)
    {
        $list = Db::query('SHOW FULL FIELDS FROM ' . config('database.connections.mysql.prefix') . $table);
        $list = array_map('array_change_key_case', $list);
        $str = "
        //全局定义一次, 加载xmSelects
        //加载组件
        layui.config({
            base: '../../static/dist/'
        })";
        foreach ($list as $elt => $item) {
            $demo = $item['field'];
            $s = explode('_', $item['field']);
            if (end($s) === 'ids') {
                $data = Db::name(array_shift($s))->field('id,name')->select();
                $arr = [];
                if ($data) {
                    foreach ($data as $k => $v) {
                        $arr[$k]['name'] = $v['name'];
                        $arr[$k]['value'] = $v['id'];
                    }
                }

                $str .= "
            .extend({
                xmSelect: 'xm-select'
            }).use(['xmSelect'], function(){
                var xmSelect = layui.xmSelect;
                //渲染多选
                var " . $demo . " = xmSelect.render({
                    el: '#" . $demo . "',
                    name: '" . $demo . "',
                    data: 
                        " . json_encode($arr) . "
                });
            });
            ";
            }
        }
        return $str;
    }

    public static function timejs($table)
    {
        $list = Db::query('SHOW FULL FIELDS FROM ' . config('database.connections.mysql.prefix') . $table);
        $list = array_map('array_change_key_case', $list);
        $str = "
        //全局定义一次, 加载xmSelects
        //加载组件
        layui.config({
            base: '../../static/dist/'
        })";
        foreach ($list as $elt => $item) {
            $demo = $item['field'];
            $s = explode('_', $item['field']);
            if (end($s) === 'ids') {
                $data = Db::name(array_shift($s))->field('id,name')->select();
                $arr = [];
                $str1 = "[";
                if ($data) {
                    foreach ($data as $k => $v) {
//                        $arr[$k]['name'] = $v['name'];
//                        $arr[$k]['value'] = $v['id'];
//                        $arr[$k]['selected'] = "{if in_array('" . $v['id'] . "',explode(',',$" . $table . "." . $demo . "))}true{else /}false{/if}";
                        $str1 .= "{\"name\":\"" . $v['name'] . "\",\"value\":\"" . $v['id'] . "\",\"selected\":{if in_array('" . $v['id'] . "',explode(',',$$table." . $item['field'] . "))}true{else /}false{/if}},";
                    }
                }
                $str1 .= "]";

                $str .= "
            .extend({
                xmSelect: 'xm-select'
            }).use(['xmSelect'], function(){
                var xmSelect = layui.xmSelect;
                //渲染多选
                var " . $demo . " = xmSelect.render({
                    el: '#" . $demo . "',
                    name: '" . $demo . "',
                    data: 
                        " . $str1 . "
                });
            });
            ";
            }
        }
        return $str;
    }

    public static function xm($table)
    {
        $list = Db::query('SHOW FULL FIELDS FROM ' . config('database.connections.mysql.prefix') . $table);
        $list = array_map('array_change_key_case', $list);
        $str = "";
        foreach ($list as $elt => $item) {
            $s = explode('_', $item['field']);
            if (explode('(', $item['type'])[0] === 'datetime') {
                $str .= "laydate.render({ 
                          elem: \"#" . $item['field'] . "\"
                          ,type: 'datetime'
                        });";
            }
            if (explode('(', $item['type'])[0] === 'int' && end($s) === 'time') {
                $str .= "laydate.render({ 
                          elem: \"#" . $item['field'] . "\"
                          ,type: 'datetime'
                        });";
            }
        }
        return $str;
    }


    public static function danxuan($filed, $item)
    {
        $arr = explode(',', explode(':', $item['comment'])[1]);
        $str = "";
        foreach ($arr as $k => $v) {
            $array = explode('=', $v);
            $str .= "<input type=\"radio\" name=\"" . $filed . "\" value=\"$array[0]\" title=\"$array[1]\">";
        }
        return $str;
    }

    public static function danxuanedit($table, $filed, $item)
    {
        $arr = explode(',', explode(':', $item['comment'])[1]);
        $str = "";
        foreach ($arr as $k => $v) {
            $array = explode('=', $v);
            $str .= "<input type=\"radio\" name=\"" . $filed . "\" value=\"$array[0]\" title=\"$array[1]\" {if $" . $table . "." . $filed . " == $array[0]}checked{/if}>";
        }
        return $str;
    }

    public static function xiala($item)
    {
        $arr = explode(',', explode(':', $item['comment'])[1]);
        $str = "";
        foreach ($arr as $k => $v) {
            $array = explode('=', $v);
            $str .= "<option value=\"$array[0]\">$array[1]</option>";
        }
        return $str;
    }

    public static function xialaid($item)
    {
        $arr = Db::name(explode('_', $item['field'])[0])->field('id,name')->select();
        $str = "";
        foreach ($arr as $k => $v) {
            $str .= "<option value=\"" . $v["id"] . "\">" . $v["name"] . "</option>";
        }
        return $str;
    }

    public static function xialaids($item)
    {
        $arr = Db::name(explode('_', $item['field'])[0])->field('id,name')->select();
        $str = "";
        foreach ($arr as $k => $v) {
            $str .= "<option value=\"" . $v["id"] . "\" >" . $v["name"] . "</option>";
        }
        return $str;
    }

    public static function xialaedit($table, $filed, $item)
    {
        $arr = explode(',', explode(':', $item['comment'])[1]);
        $str = "";
        foreach ($arr as $k => $v) {
            $array = explode('=', $v);
            $str .= "<option {if $" . $table . "." . $filed . " == $array[0]}selected=\"\"{/if} value=\"$array[0]\">$array[1]</option>";
        }
        return $str;
    }

    public static function xialaidedit($table, $filed, $item)
    {
        $arr = Db::name(explode('_', $item['field'])[0])->field('id,name')->select();
        $str = "";
        foreach ($arr as $k => $v) {
            $str .= "<option {if $" . $table . "." . $filed . " == " . $v["id"] . "}selected=\"\"{/if} value=\"" . $v["id"] . "\">" . $v["name"] . "</option>";
        }
        return $str;
    }

    public static function duoxuan($filed, $item)
    {
        $arr = explode(',', explode(':', $item['comment'])[1]);
        $str = "";
        foreach ($arr as $k => $v) {
            $array = explode('=', $v);
            $str .= "<input type=\"checkbox\" name=\"" . $filed . "[$array[0]" . "]\" title=\"$array[1]\">";
        }
        return $str;
    }

    public static function duoxuanedit($table, $filed, $item)
    {
        $arr = explode(',', explode(':', $item['comment'])[1]);
        $str = "";
        foreach ($arr as $k => $v) {
            $array = explode('=', $v);
            $str .= "<input {if in_array('" . $array[0] . "',explode(',',$" . $table . "." . $filed . "))}checked=\"\"{/if} type=\"checkbox\" name=\"" . $filed . "[$array[0]" . "]\" title=\"$array[1]\">";
        }
        return $str;
    }

}