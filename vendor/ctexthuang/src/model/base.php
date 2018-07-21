<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/20
 * Time: 下午3:54
 */

namespace ctexthuang\model;
use \ctexthuang\sql\mysql;

class base extends mysql{
    public  $config;
    private  $param;
    public  $del_model;
    protected  $addField;
    protected  $saveField;
    protected  $delId;

    function __construct(){
        parent::__construct();
        $this->table = $this->config['table'];

        $this->del_model = isset($this->config['del_model'])?$this->config['del_model']:1;
    }

    function setParam($param){
        $this->req = $this->param = $param;
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
            $pick = array();
            foreach ($key as $k){
                if(isset($this->req[$k])){
                    $pick[$k] = $this->req[$k];
                }else{
                    outErr('0001','参数:'.$k.'必须,或者值为空');
                }
            }
            return $pick;
        }
    }

    function pick( $arr = array(),$m = 1){
        if(empty($arr)) return $this->req;

        $pick = array();
        foreach ($arr as $key){
            if(array_key_exists($key,$this->req)){
                if($m == 1){
                    $pick[$key] = $this->req[$key];
                }else{
                    $pick[] = $this->req[$key];
                }
            }
        }
        return $pick;

    }

    function pickNotNull($arr = array(),$m = 1){
        if(empty($arr)){
            return $this->req;
        }

        $pick = array();
        foreach ($arr as $key){
            if(isset($this->req[$key])){
                if($m == 1){
                    $pick[$key] = $this->req[$key];
                }else{
                    $pick[] = $this->req[$key];
                }
            }
        }
        return $pick;
    }


    function isExist($key,$data = ''){
        if(isset($this->req[$key])){
            return 	$this->req[$key];
        }
        else{
            return $data;
        }
    }

    function delParam($key){
        unset($this->req[$key]);
    }

    protected function _s($m,$v){


    }

//  查询后处理
    protected function _e($r){

        return $r;
    }

    /********返回多行***********/

    function rows(){

        if(isset($this->param['field'])){
            parent::field($this->param['field']);
        }
        $this->pageIndex=isset($this->param['pageIndex'])?$this->param['pageIndex']:1;
        $this->maxSize=isset($this->param['maxSize'])?$this->param['maxSize']:10;
        $this->param=isset($this->param['param'])?$this->param['param']:$this->param;

        if(isset($this->param['new']) && $this->param['new']==false ){
            parent::order('id','asc');
        }else{
            parent::order('id','desc');
        }

        if(is_array($this->param)){
            foreach($this->param as $m=>$v){
                $this->_s($m,$v);
            }
        }
        /******删除模式**********/
        if($this->del_model==0){
            parent::whereAnd('deleteAt',0,'=');
        }
        //print_r($this->param);exit();
        $result_model=isset($this->param['result_model'])?$this->param['result_model']:0;
        $records=parent::select($result_model);

        foreach($records['rows'] as $k=>$record){
            $records['rows'][$k]=$this->_e($record);
        }
        return $records;
    }


    function all(){

        if(isset($this->param['field'])){
            parent::field($this->param['field']);
        }
        $this->pageIndex=0;

        $this->param=isset($this->param['param'])?$this->param['param']:$this->param;

        if(!isset($this->param['new'])){
            parent::order('id','desc');
        }

        if(is_array($this->param)){
            foreach($this->param as $m=>$v){
                $this->_s($m,$v);
            }
        }
        /******删除模式**********/
        if($this->del_model==0){
            parent::whereAnd('deleteAt',0,'=');
        }

        $result_model=isset($this->param['result_model'])?$this->param['result_model']:0;    //0为非ID索引模式
        $records=parent::select($result_model);
        $records=$records['rows'];
        foreach($records as $k=>$record){
            $records[$k]=$this->_e($record);
        }
        return $records;
    }

    /********返回单行***********/
    function one(){

        if(isset($this->param['field'])){
            parent::field($this->param['field']);
        }

        foreach($this->param as $m=>$v){
            //echo $m;
            if($m=='id'){
                parent::whereAnd('id',$v,'=');
            }else{
                $this->_s($m,$v);
            }

        }

        /******删除模式**********/
        if($this->del_model==0){
            parent::whereAnd('deleteAt',0);
        }

        $record=parent::find();
        if(empty($record)){
            return $record;
        }else{
            return 	$this->_e($record);
        }

    }

    /********根据ID 返回***********/
    function byId(){
        parent::whereAnd('id',$this->param);//

        if($this->del_model==0){
            parent::whereAnd('deleteAt',0);
        }

        //echo $this->del_model;

        $record=parent::find();

        //print_r($record);

        if(empty($record)){
            return $record;
        }else{
            return 	$this->_e($record);
        }
    }

    /****新增数据****/
    protected function beforeAdd(){


    }
    function add(){

        $this->addField=isset($this->param['param'])?$this->param['param']:$this->param;
        $this->beforeAdd();

        if(isset($this->config['createAt']) && $this->config['createAt']==1){
            $this->addField['createAt']=now();
        }

        if($id=parent::insert($this->addField)){
            $this->addField['id']=$id;
            $this->afterAdd();

            return $this->addField;
        }else{
            return false;
        }

    }

    protected function afterAdd(){

    }

    /****保存数据****/
    protected	function beforeSave(){


    }

//修改数据,根据ID保存
    function save(){
        $this->saveField=isset($this->param['param'])?$this->param['param']:$this->param;
        $this->beforeSave();

        $data['field']=$this->saveField;
        $data['keys']=array('id');
        if(parent::update($data)){
            $this->afterSave();
            return $this->saveField;
        }else{
            return  false;
        }

    }

    protected function afterSave(){


    }



    /******删除***********/
    protected	function beforeDelById(){


    }
    function delById(){
        $this->delId=$this->param;
        $this->beforeDelById();
        if($this->del_model==0){
            $data['field']=array('id'=>$this->delId,'deleteAt'=>now());
            $data['keys']=array('id');
            return parent::update($data);

        }else{
            parent::whereAnd('id',$this->delId);//	物理删除
            return parent::delete();
        }

        $this->afterDelById();
    }

    protected function afterDelById(){


    }

    /*********************/


    //计算总行数
    function counts(){
        if(is_array($this->param)){
            foreach($this->param as $m=>$v){
                $this->_s($m,$v);
            }
        }
        return parent::counts();
    }

    //ID 集合
    function idset(){
        return parent::sets('id');
    }


    function __destruct(){
        parent::__destruct();
    }
}