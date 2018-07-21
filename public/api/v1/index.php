<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/20
 * Time: 上午10:56
 */

namespace api;

use \ctexthuang\controller\api;
use \ctexthuang\router\route as router;
use ctexthuang\router\route;

include_once dirname(dirname(dirname(__FILE__))).'/entrance.php';

class index extends api{
    function __construct(){
        parent::__construct();

        $router = include_once './router.php';

        $r = new router();

        $this->req['authToken'] = $this->token;
        $this->res = $r->toService($router,$this->req);
//        var_dump($this->res);exit;
    }
}
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:x-requested-with,content-type,authorization');
header("Content-type: application/json; charset=utf-8");

new index;