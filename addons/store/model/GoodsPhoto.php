<?php
namespace addons\store\model;

use think\Model;

class GoodsPhoto extends Model
{
    protected $pk = 'id';
    protected $createTime = 'add_time';
    protected $autoWriteTimestamp = true;

    protected static function init(){

    }

    public function getIsShowAttr($value)
    {
        $status = [0=>'否',1=>'是'];
        return $status[$value];
    }

    public function getAddTimeAttr($value)
    {
        return date('Y-m-s h:i:s',$value);
    }

}
