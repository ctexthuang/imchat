<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/20
 * Time: 下午3:52
 */

namespace model;

use \ctexthuang\model\base;

class user extends base{
    function __construct(){
        $this->config = [
            'table' => 'user',
        ];

        parent::__construct();
    }

    protected function _e($r){
        return $r;
    }

    protected function _s($m, $v){
        switch($m){
            case 'new':
                parent::order('id','desc');
                break;
            case 'account':
                parent::whereAnd('account',$v,'=');
                break;
            case 'token':
                parent::whereAnd('token',$v,'=');
                break;
        }
    }

    protected function beforeAdd(){
        //
    }

    protected function beforeSave(){
        //
    }

    protected function beforeDelById(){
        //
    }

    function __destruct(){
        parent::__destruct();
    }
}
