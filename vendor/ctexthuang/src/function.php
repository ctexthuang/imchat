<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/20
 * Time: 上午10:06
 */

ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);
date_default_timezone_set("PRC");

if (!function_exists('apache_request_headers')) {
    eval('
         function apache_request_headers() {
             foreach($_SERVER as $key=>$value) {
                 if (substr($key,0,5)=="HTTP_") {
                     $key=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
                     $out[$key]=$value;
                 }
             }
             return $out;
         }
     ');
}

function outData($data,$dataType='json'){
    header("Content-type: application/json; charset=utf-8");
    echo json_encode($data);
    exit();
}

function outErr($code,$des,$dataType='json'){
    outData(error($code,$des));
}

function success($val){
    $res['succeeded'] = true;
    $res['data'] = $val;

    return $res;
}

function error($code,$des){
    $res['succeeded'] = false;
    $res['error']['code'] = $code;
    $res['error']['des'] = $des;

    return $res;
}

function obj_to_array($obj){
    $json = json_encode($obj);
    return json_decode($json,true);
}

function is_not_json($str){
    return is_null(json_decode($str));
}

function isNum($num){
    if (is_numeric($num)){
        return true;
    }else{
        return false;
    }
}

function selfUrl($add_parman = [],$del_parman = []){
    $rUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
    $parman = $_GET;

    if(!empty($add_parman)){
        $parman = array_merge($parman,$add_parman);
    }

    if(!empty($del_parman)){
        foreach($del_parman as $k=>$v){
            if(isset($parman[$v])){
                unset($parman[$v]);
            }
        }
    }

    $parman = http_build_query($parman);
    $rUrl = $rUrl.'?'.$parman;

    return $rUrl;
}

function getIP(){
    return $_SERVER["REMOTE_ADDR"];
}

function now($dateType = 'Y-m-d H:i:s'){
    return date($dateType,time());
}

function beforeDate($day = 1,$dateType = 'Y-m-d H:i:s'){
    $timeSp = 60*60*24*$day;
    return date($dateType,time()-$timeSp);
}

function toTimestamp($date){
    return strtotime($date);
}

function arr_url_encode($array){
    foreach($array as $key => $val){
        if(is_array($val)){
            $array[$key] = arr_url_encode($val);
        }else{
            $array[$key] = urlencode($val);
        }
    }
    return $array;
}

function arrayToJsonString($array){

    return json_encode($array);

}
function jsonStringToArray($string){
    return json_decode($string,true);
}

function sendDataFromCurl($url,$data = '',$m = "GET"){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,$m);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    if (curl_errno($ch)) {
        return curl_error($ch);
    }
    curl_close($ch);
    return $res;
}

function createNonceStr($length = 16) {

    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}

function createNonceDig($length = 16) {

    $chars = "0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}

function get_between($input, $start, $end) {
    $substr = substr($input, strlen($start)+strpos($input, $start),
        (strlen($input) - strpos($input, $end))*(-1));
    return $substr;
}

function saveLog($file,$log){
    $path=dirname($file);
    //echo $file;
    if(!file_exists($path))mkdir($path,0777,true);
    file_put_contents($file,$log,FILE_APPEND);
    file_put_contents($file,"\r\n",FILE_APPEND);

}

function make_string($length){
    $char = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9'];

    $bar = intval($length);
    $keys = array_rand($char,$length);

    for ($i=0; $i < $length ; $i++) {
        $password[] = $char[$keys[$i]];
    }
    $pass = implode($password);

    return $pass;
}