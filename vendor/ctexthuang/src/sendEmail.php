<?php
/**
 * Created by PhpStorm.
 * User: ctexthuang
 * Date: 2018/7/19
 * Time: 下午5:39
 */

include_once APPPATH.'/vendor/aliyunemail/aliyun-php-sdk-core/Config.php';
use \Dm\Request\V20151123 as Dm;

class sendEamil{
    public function sendEamilAliyun($address,$title,$htmlbody){
        $config = include(APPPATH.'/config/config.php');
        $aliyunset = $config['aliyunemail'];
        $iClientProfile = DefaultProfile::getProfile($aliyunset['data'], $aliyunset['appid'], $aliyunset['appserect']);

        $client = new DefaultAcsClient($iClientProfile);
        $request = new Dm\SingleSendMailRequest();
        //新加坡或澳洲region需要设置SDK的版本，华东1（杭州）不需要设置。
        //$request->setVersion("2017-06-22");
        $request->setAccountName($aliyunset['formaccount']);
        $request->setFromAlias($aliyunset['formname']);
        $request->setAddressType(1);
        $request->setTagName("register");
        $request->setReplyToAddress("true");
        $request->setToAddress($address);
        $request->setSubject($title);
        $request->setHtmlBody($htmlbody);
        try {
            $response = $client->getAcsResponse($request);
             print_r($response);exit;
        }
        catch (ClientException  $e) {
             print_r($e->getErrorCode());
             print_r($e->getErrorMessage());
             exit;
        }
        catch (ServerException  $e) {
             print_r($e->getErrorCode());
             print_r($e->getErrorMessage());
             exit;
        }
    }
}