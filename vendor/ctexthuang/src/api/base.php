<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/20
 * Time: 上午11:23
 */

namespace ctexthuang\api;

class base{
    public $req;

    function __construct(){

    }

    protected function init(){

    }

    function setParam($param){
        unset($this->req);

        $this->req = $param;
        $this->init();
    }

    function must($key){
        if(!is_array($key)){   //单个
            if(isset($this->req[$key])){
                return  $this->req[$key];
            }
            else{
                outErr('0001','参数:'.$key.'必须,或者值为空');
            }
        }else{     //数组
            $pick=array();
            foreach ($key as $k){
                if(isset($this->req[$k])){
                    $pick[$k]=$this->req[$k];
                }else{
                    outErr('0001','参数:'.$k.'必须,或者值为空');
                }
            }
            return $pick;
        }
    }

    function pick($arr = [],$m = 1){
        if (empty($arr)){
            return $this->req;
        }

        $pick = [];
        foreach ($arr as $key){
            if(array_key_exists($key,$this->req)){
              	if(1 == $m){
                    $pick[$key] = $this->req[$key];
                }else{
                    $pick[] = $this->req[$key];
                }
            }
        }

        return $pick;
    }

    function pickNotNull($arr = [],$m = 1){
        if (empty($arr)){
            return $this->req;
        }

        $pick = [];
        foreach ($arr as $key){
            if(isset($this->req[$key])){
                if(1 == $m){
                    $pick[$key] = $this->req[$key];
                }else{
                    $pick[] = $this->req[$key];
                }
            }
        }

        return $pick;
    }

    function isExist($key,$data = ''){
        if (isset($this->req[$key])){
            return $this->req[$key];
        }else{
            return $data;
        }
    }

    function delParam($key){
        unset($this->req[$key]);
    }

    function __destruct(){
        //
    }
}
