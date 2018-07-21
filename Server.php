<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/19
 * Time: 下午6:44
 */

require_once __DIR__.'/server/Socket.php';
if (php_sapi_name() !== 'cli'){
    exit('请使用cli模式');
}

//$server = new server\Socket('0.0.0.0',9501);
$server = new server\Socket('0.0.0.0','9501');
$server->set([
    'daemonize' => 0,
    'worker_num' => 2,
    'task_worker_num' => 2
]);

$redis = null;

$server->on('WorkerStart',function ($server,$workerId){
    global $redis;

    $redis = new \Redis();
    $redis->connect('127.0.0.1',6379) || die("redis 连接失败");

    echo "进程{$workerId}的redis连接成功\n";
});

$server->on('Open',function($server,$req){
    global $redis;
    $server->connect($redis,$req);
});

$server->on('Message',function($server,$frame){
    global $redis;
    $server->messages($redis,$frame);
});

$server->on('Close',function($server,$fd){
    global $redis;
    $server->closing($redis,$fd);
});

$server->on('Task',function($server,$taskId,$workId,$data){
    global $redis;
});

$server->on('Finish',function($serv,$taskId,$data){
    echo $data;
});

$server->start();