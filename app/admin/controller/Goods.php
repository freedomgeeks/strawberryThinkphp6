<?php
//  *+-----------------------------------------------------------------------
//  *                      .::::.
//  *                    .::::::::.            | Author: 丶长情
//  *                    :::::::::::           | Email: zeng1144318071@gmail.com
//  *                 ..:::::::::::'           | Datetime: 2020/09/24
//  *             '::::::::::::'               | Remarks: 11
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

use think\facade\View;
use think\facade\Db;

class Goods extends AdminBase
{

    public function index(){
        return View::fetch();
    }

   public function getList(){
   		$page = $this->request->param('page',1,'intval');
   		$limit = $this->request->param('limit',10,'intval');
   		$count = Db::name('goods')->count();
   		$data = Db::name('goods')->page($page,$limit)->select()->each(function($item,$k){
   			return $item;
   		});
        foreach ($data as $key => $value) {
           foreach ($value as $elt => $item) {
               $s = explode('_',$elt);
               if (end($s) === 'time'){
                   $value[$elt] = date('Y-m-d H:i:s',$item);
               }else if (count($s) >= 2 && end($s) === 'id'){
                   array_pop($s);
                   $dbanme = implode('_',$s);
                   $name = Db::name("$dbanme")->where('id',$item)->field('name')->find();
                   $value[$elt] = $name['name'];
               }else if (end($s) === 'ids'){
                   array_pop($s);
                   $dbanme = implode('_',$s);
                   $where = [];
                   $where[] = ['id','in',explode(',',$item)];
                   $name = Db::name("$dbanme")->where([$where])->field('name')->select();
                   $arr = [];
                   foreach ($name as $e => $v){
                       array_push($arr,$v['name']);
                   }
                   $value[$elt] = implode(',',$arr);
               } else {
                    $sql = "show full columns from ".config('database.connections.mysql.prefix')."goods";
                    $res = Db::query($sql);
                    $set = [];
                    $str = "";
                    foreach ($res as $e => $i) {
                        if ($i['Field'] == 'state' && explode('(', $i['Type'])[0] == 'enum') {
                            $arrs = explode(",", explode(':', $i['Comment'])[1]);
                            foreach ($arrs as $keys => $items) {
                                if (in_array($value['state'], explode('=', $items))) {
                                    $value['state'] = explode('=', $items)[1];
                                }
                            }
                        } else if ($elt != 'id' && explode('(', $i['Type'])[0] == 'enum') {
                            $xiala = explode(",", explode(':', $i['Comment'])[1]);
                            foreach ($xiala as $elts => $its) {
                                if (in_array($value["$elt"], explode('=', $its))) {
                                    $str = explode('=', $its)[1];
                                }
                            }
                        } else if (explode('(', $i['Type'])[0] == 'set') {
                            $duoxuan = explode(",", explode(':', $i['Comment'])[1]);
                            foreach ($duoxuan as $ds => $d1s) {
                                if (in_array(explode('=', $d1s)[0], explode(',', $value["$elt"]))) {
                                    array_push($set, explode('=', $d1s)[1]);
                                }
                            }
                        }
                    }
                    if ($set)
                        $value["$elt"] = implode(',', $set);
                    if ($str)
                        $value["$elt"] = $str;
                }
           }
           $data[$key] = $value;
        }
   		return json([
				'code'=> 0,
				'count'=> $count,
   				'data'=>$data,
   				'msg'=>'查询用户成功'
   		]);
   }

   public function add(){
	   	if($this->request->isPost()){
	   		$data = $this->request->post();
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $arr = [];
                    foreach ($value as $elt => $item) {
                        array_push($arr, $elt);
                    }
                    $data[$key] = implode(',', $arr);
                }
                $s = explode('_',$key);
                if (end($s) === 'time'){
                    $data[$key] = strtotime($data[$key]);
                }
            }
            if (!isset($data['switch']))
                $data['switch'] = 'off';
	   		if(Db::name('goods')->insert($data)){
	   			$this->success("添加成功");
	   		}else{
	   			$this->error("添加失败");
	   		}
	   	}
	   	return View::fetch();
   }

   public function edit(){
	   	if($this->request->isPost()){
	   		$data = $this->request->post();
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $arr = [];
                    foreach ($value as $elt => $item) {
                        array_push($arr, $elt);
                    }
                    $data[$key] = implode(',', $arr);
                }
                $s = explode('_',$key);
                if (end($s) === 'time'){
                    $data[$key] = strtotime($data[$key]);
                }
            }
            if (!isset($data['switch']))
                $data['switch'] = 'off';
            if(Db::name('goods')->where('id',$data['id'])->update($data)){
	   			$this->success("编辑成功");
	   		}else{
	   			$this->error("编辑失败");
	   		}
	   	}
	   	$id = $this->request->param('id');
	   	if(!$id){
	   		$this->success("参数错误");
	   	}
	   	$goods = Db::name('goods')->where('id',$id)->find();
   		if(!$goods){
	   		$this->success("参数错误");
	   	}
	   	View::assign('goods',$goods);
        return View::fetch();
   }

   public function delete(){
   		$idsStr = $this->request->param('idsStr');
   		if(!$idsStr){
   			$this->success("参数错误");
   		}
   		if(Db::name('goods')->where('id','in',$idsStr)->delete()){
   			$this->success("删除成功");
   		}else{
   			$this->error("删除失败");
   		}
   }

   public function sw(){
      		$id = $this->request->param('id');
      		$switch = $this->request->param('switch');
      		if(Db::name('goods')->where('id',$id)->update(['switch' => $switch])){
      			$this->success("编辑成功");
      		}else{
      			$this->error("编辑失败");
      		}
      }

}
