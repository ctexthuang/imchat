<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/20
 * Time: 上午9:08
 */

namespace ctexthuang\controller;

class api{
    public $headers;
    public $req;
    public $res;
    public $token;

    function __construct(){
        $this->res = [];
        $this->req = [];

        $this->headers = apache_request_headers();

        if(isset($this->headers['Authorization']) && !empty($this->headers['Authorization'])){
            $authorization=$this->headers['Authorization'];
            $auth=explode(' ',$authorization);

            if(isset($auth[1])){
                $this->token = $auth[1];
            }
        }else{
            $this->token = NULL;
        }

        if(strcasecmp($_SERVER['REQUEST_METHOD'],"OPTIONS")===0){
            $this->err('009','不允许options方法');
        }

        $this->parasBody();
    }

    function parasBody(){
        $postdata = file_get_contents("php://input");

        $ContentType = isset($this->headers['Content-Type'])?$this->headers['Content-Type']:NULL;

        $req = !empty($postdata)?$postdata:NULL;

        if (!empty($req)){
            if (stripos($ContentType,"json") > 0 || stripos($ContentType,"json") === 0){
                if (is_not_json($req)){
                    $this->err('0002','json格式错误');
                }else{
                    $this->req = jsonStringToArray($req);
                }
            }else{
                $xmlObj = simplexml_load_string($req,"SimpleXMLElement",LIBXML_NOCDATA);
                $this->req = json_decode(json_encode($xmlObj),true);
            }
        }else{
            $this->req = $_POST;
        }
    }

    function scc($d){
        $this->res = success($d);
    }

    function err($code,$des){
        $this->req = error($code,$des);
        exit;
    }

    function setRes($res){
        $this->req = $res;
        exit;
    }

    function logs($message){
        return false;

        if (is_array($message)){
            $message = arrayToJsonString($message);
        }

        $log = [now().'__',$message];
        $file = dirname(dirname(dirname(__FILE__))).'/log/controller/'.now('Y_m_d').'.txt';

        saveLog($file,$log);
    }

    function __destruct(){
        if (!empty($this->res)){
            outData($this->res);
        }
    }
}