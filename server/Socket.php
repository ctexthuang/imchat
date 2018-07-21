<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/19
 * Time: 下午6:45
 */

namespace server;

class Socket extends \Swoole\Websocket\Server{
    //发送用户列表
    const SENDLIST = 1;
    //普通信息
    const COMMSG = 2;
    //错误用户名  已存在
    const ERRORNAME = 3;
    //错误的token
    const ERRORTOKEN = 4;
    //服务器异常
    const SEVEERERROR = 500;
    //添加用户列表
    const ADDUSER = 5;
    //删除用户列表
    const DELUSER = 6;
    //通知用户登录失败
    const UNLOGIN = 7;
    //私聊
    const SENDMSG = 8;
    //登录成功
    const LOGINSUCCESS = 9;

    /** 群发
     * @param int $fid
     * @param  $redis
     * @param string $account
     * @param int $type
     * @param string $mesg
     * @param string $group
     */
    public function groupchat(int $fid,\Redis $redis,string $account,int $type,$mesg = "",$group = "public"){
        //普通消息发送前的处理
        if ($type === self::COMMSG){
            $mesg = htmlspecialchars($mesg,ENT_NOQUOTES);
            $mesg = nl2br($mesg);
            $mesg = str_replace(["\n","\""],["","\\\""],$mesg);
        }
        //发新的消息发送给客户端
        foreach ($this->connections as $fd){
            $result = $redis->hGetAll($fd);
            if (self::DELUSER === $type && $fid === $fd || $result['group'] !== $group){
                continue;
            }

            switch ($type){
                case self::ADDUSER:
                    $this->push($fd,"{\"code\":\"5\",\"user\":\"{$account}\"}");
                    break;
                case self::COMMSG:
                    $this->push($fd,"{\"code\":\"2\",\"mesg\":\"{$mesg}\",\"account\":\"{$account}\"}");
                    break;
                case self::DELUSER:
                    $this->push($fd,"{\"code\":\"6\",\"user\":\"{$account}\"}");
                    break;
            }
        }
    }

    /** 私聊
     * @param int $fid
     * @param \Redis|null $redis
     * @param string $account
     * @param int $type
     * @param string $sendto
     * @param string $mesg
     */
    public function privatechat($fid,$account,$type,$sendto,$mesg = '',\Redis $redis = null){
        //信息处理
        $mesg = htmlspecialchars($mesg, ENT_NOQUOTES);
        $mesg = str_replace("Ø", ":", $mesg);

        switch ($type){
            case self::SENDLIST:
                $this->push($fid,"{\"code\":\"4\",\"users\":[{$mesg}]}");
                break;
            case self::SEVEERERROR:
                $this->push($fid,"{\"code\":\"-1\",\"mesg\":\"{$mesg}\"}");
                break;
            case self::UNLOGIN:
                $this->push($fid,"{\"code\":\"-1\",\"mesg\":\"{$mesg}\"}");
                break;
            case self::ERRORTOKEN:
                $this->push($fid,"{\"code\":\"-1\",\"mesg\":\"{$mesg}\"}");
                break;
            case self::SENDMSG:
                $mesg = htmlspecialchars_decode($mesg, ENT_NOQUOTES);
                foreach ($this->connections as $fd){
                    $result = $redis->hGetAll($fd);
                    if ($result['account'] === $sendto){
                        $mesg = str_replace(["\n", "\""], ['\n', "\\\""], $mesg);
                        $this->push($fd, "{\"code\":\"1\",\"mesg\":\"{$mesg}\",\"form\":\"{$account}\"}");
                        break;
                    }

                }
                break;
        }
    }

    /** 获取用户列表
     * @param \Redis $redis
     * @param string $group
     * @return bool|string
     */
    public function onLionList(\Redis $redis,$group = "public"){
        $userlist = '';
        $user = false;

        foreach ($this->connections as $fd) {
            $result = $redis->hGetAll($fd);
            if ($result["account"] == "" || $result["group"] !== $group) {
                continue;
            }
            $user = true;
            $userlist .= ('"' . $result["account"] . '",');
        }

        if (!$user) {
            return "";
        }

        $userlist = substr($userlist, 0, strlen($userlist) - 1);

        return $userlist;
    }

    /** 检测token
     * @param string $data
     * @return bool|int
     */
    public function checkToken(string $data){
        $user = explode(':', $data);
        if (count($user) !== 4) {
            return self::COMMSG;
        }

        $Now = [date("Y-m-d H:i", time()), date("Y-m-d H:i", time() - 60)];
        $timeStamp = [strtotime($Now[0]), strtotime($Now[1])];
        $hash = [hash('sha256', $timeStamp[0] . 'daimin' . $user[3]),
            hash('sha256', $timeStamp[1] . 'daimin' . $user[3])
        ];

        if ($user[1] == $hash[0] || $user[1] == $hash[1]) {
            return true;
        }else {
            return false;
        }
    }

    /** 用户连接
     * @param \Redis $redis
     * @param $req
     */
    public function connect(\Redis $redis, $req){
        $redis->incr('useraccount');
        $group = isset($req->get['group'])?$req->get['group']:'public';
        $redis->hMset($req->fd, ["token" => "", "account" => "", "group" => $group, "ip" => $req->server['remote_addr']]);
        echo "新客户端连接: " . $req->fd . "时间:" . date("Y-m-d H:i:s") . "\n";
        $userlist = $this->onLionList($redis, $req->get['group']);

        $this->privatechat($req->fd,'', self::SENDLIST,'',$userlist);
    }

    /** 用户登录
     * @param \Redis $redis
     * @param string $account
     * @param string $reload
     * @param string $user_ip
     * @return bool
     */
    public function checkLogin(\Redis $redis, string $account, string $reload, string $user_ip){
        foreach ($this->connections as $fd) {
            $result = $redis->hGetAll($fd);
            if ($result["account"] === $account) {
                //此处只验证了是否为同一个ip,如果想更精确判断可以在写入一个sessionid到redis中,
                if ($reload === "yes" && $result['ip'] === $user_ip) {
                    $this->close($fd);
                    return false;
                }else {
                    return true;
                }
            }
        }
        return false;
    }

    public function closing(\Redis $redis,$fd){
        $redis->decr('useraccount');
        $userData = $redis->hGetAll($fd);
        $username = $userData['account'];
        $usergroup = $userData['group'];
        $res = $redis->del($fd);

        if ($res === false){
            echo "删除用户{$username}({$fd})失败\n";
        }

        if ($username){
            $this->groupchat($fd,$redis,$username,self::DELUSER,"",$usergroup);
        }
        echo "客户端{$username}({$fd})已断开连接\n";
    }

    public function userbehavior(\Redis $redis,$frame,array $userinfo){
        $prechar = mb_substr($frame->data,0,6);

        if (mb_strlen($prechar) < 6){
            return self::COMMSG;
        }

        switch ($prechar){
            case "tokenR":
                if ($userinfo['token'] === ''){
                    if ($this->checkToken($frame->data)){
                        $userData = explode(':',$frame->data);

                        if ($this->checkLogin($redis,$userData[3],$userData[4],$userinfo['ip'])){
                            return self::ERRORNAME;
                        }

                        if (7 === count($userData)){
                            $res = $redis->hMset($frame->fd,['token' => $userData[1],'account' => $userData[3],'group' => $userData[6],'ip' => $userinfo['ip']]);
                        }else{
                            $res = $redis->hMset($frame->fd,['token' => $userData[1],'account' => $userData[3]]);
                        }

                        if (!$res){
                            echo "更新用户信息失败 account=={$userData[3]} token = {$userData[1]}\n";
                            return self::SEVEERERROR;
                        }
                        echo "用户登录 {$userData[3]}({$frame->fd})\n";
                        return self::LOGINSUCCESS;
                    }else{
                        return self::ERRORTOKEN;
                    }
                }else{
                    return self::COMMSG;
                }
                break;
            case "sendTo":
                if ($userinfo['token'] === ''){
                    return self::UNLOGIN;
                }

                $userData = explode(":",$frame->data);
                if (6 !== count($userData)){
                    return self::COMMSG;
                }
                return self::SENDMSG;
                break;
        }
    }

    public function messages(\Redis $redis,$frame){
        $result = $redis->hGetAll($frame->fd);
        $account = $result['account'];
        $usergroup = $result['group'];
        print_r($result);
        echo "收到来自 {$usergroup}组中 {$account} ({$frame->fd})的消息: " . $frame->data . "\n";

        $eventtype = $this->userbehavior($redis,$frame,$result);
        switch ($eventtype){
            case self::COMMSG:
                if ($result['token'] !== ''){
                    $this->groupchat($frame->fd,$redis,$account,self::COMMSG,$frame->data,$usergroup);
                }else{
                    $this->privatechat($frame->fd,'',self::UNLOGIN,'',"请先登录");
                }
                break;
            case self::LOGINSUCCESS:
                $result = $redis->hGetAll($frame->fd);
                $account = $result['account'];
                $usergroup = $result['group'];
                $this->groupchat($frame->fd,$redis,$account,self::ADDUSER,"",$usergroup);
                $this->privatechat($frame->fd,$account,self::LOGINSUCCESS,'','');
                break;
            case self::SEVEERERROR:
                $this->privatechat($frame->fd,'',self::SEVEERERROR,'',"服务器异常,请联系管理员");
                break;
            case self::ERRORNAME:
                $this->privatechat($frame->fd,'',self::ERRORNAME,'','用户名已存在');
                break;
            case self::ERRORTOKEN:
                $this->privatechat($frame->fd,'',self::ERRORTOKEN,'','token错误');
                break;
            case self::UNLOGIN:
                $this->privatechat($frame->fd,'',self::UNLOGIN,'','请先登录');
                break;
            case self::SENDMSG:
                $userData = explode(':', $frame->data);
                $sendTo = $userData[1];
                $mesg = $userData[3];
                $account = $userData[5];

                $this->privatechat($frame->fd,$account, self::SENDMSG,$sendTo,$mesg,$redis);
                break;
        }
    }
}