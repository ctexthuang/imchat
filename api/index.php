<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/20
 * Time: 上午11:54
 */

include_once(__DIR__.'/common.php');

function S($serviceName,$method,$param = NULL){
    $serviceArr = explode("\\",$serviceName);
    $serviceFild = array_shift($serviceArr);

    $newServicePath = __DIR__.'/'.$serviceFild.'.php';
    $newServicePath = str_replace('\\','/',$newServicePath);
    $serviceName = "\api\\".$serviceName;

    if (file_exists($newServicePath)){
        include_once($newServicePath);
        $serviceObj = new $serviceName();
        $serviceObj->setParam($param);

        return $serviceObj->$method();
    }else{
        return error('apiErr','api不存在'.$newServicePath);
    }
}