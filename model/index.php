<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/20
 * Time: 下午2:38
 */

use \ctexthuang\db\mysqlDB;

$connect = new mysqlDB($_C['datebase']['host'],$_C['datebase']['db_user'],$_C['datebase']['db_pwd'],$_C['datebase']['db']);
define('PF',$_C['datebase']['prefix']);

function M($model,$method = 'rows',$param = []){
    $modelArr = explode("\\",$model);
    $classFild = array_shift($modelArr);

    $newModelPath = __DIR__.'/'.$classFild.'.php';
    $newModelPath = str_replace('\\','/',$newModelPath);
    $class = "\model\\".$model;

    if(file_exists($newModelPath)){
        include_once($newModelPath);
        $modelObj = new $class();
        $modelObj->setParam($param);

        return $modelObj->$method();
    }else{
        return outErr('001',[$classFild.'模型不存在']);
    }
}