<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/20
 * Time: 上午9:36
 */

namespace ctexthuang\router;

class route{
    function __construct(){

    }

    function toService($router,$req){
        $path = dirname($_SERVER['SCRIPT_NAME']).'/';
        $url = $_SERVER['REQUEST_URI'];

        list($u) = explode("?",$url);
        $contrl = str_ireplace($path,"",$u);

        if (array_key_exists($contrl,$router)){
            $service = $router[$contrl];

            return S($service[0],$service[1],$req);
        }else{
            return error(001,'路由没有注册');
        }
    }

    function methon(){
        $path = dirname($_SERVER['SCRIPT_NAME']).'/';
        $url = $_SERVER['REQUEST_URI'];

        list($u) = explode("?",$url);
        $contrl = str_ireplace($path,"",$u);

        $routh = explode("/",$contrl);

        $methon = array_pop($routh);
        $class = array_pop($routh);

        $namespace = implode($routh,"/");

        $class_file = $_SERVER['DOCUMENT_ROOT'].$path.'/'.$namespace.'/'.$class.'.php';

        if(!file_exists($class_file)){
            outErr('001','api地址错误');
        }

        include_once $class_file;
        $class = '\api\\'.$namespace.'\\'.$class;

        if(class_exists($class)){
            $outObj = new $class();
        }else{
            outErr('002','类不存在');
        }

        if(!method_exists($outObj,$methon)){
            outErr('003','方法不存在');
        }else{
            $outObj->$methon();
        }
    }

    function __destruct(){
        //
    }
}