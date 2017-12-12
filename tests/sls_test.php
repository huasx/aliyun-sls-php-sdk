<?php
/**
 * Created by VeryStar.
 * Author: hsx
 * Create: 2017/12/11 14:56
 * Editor: created by PhpStorm
 */

include_once "../src/Helper.php";
include_once "../src/Curl.php";
include_once "../src/Sls.php";
define('DS', DIRECTORY_SEPARATOR);
define('APP_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DS);
@date_default_timezone_set('Asia/Shanghai');

$sls = new \Verypay\Sls();
$sls->init([
    'access_key'    => '',
    'access_secret' => '',
    'endpoint'      => '',
    'project'       => '',
    'logstore'      => '',
]);

$time = 1512989165;
//$ret = $sls->getLogStoreList();
//$ret = $sls->getLog([
//    'from' => time() - 180,
//    'to'   => time(),
////    'line' => 3,
//]);
//$ret = $sls->getLogStore();

//$ret = $sls->getHistograms([
//    'from' =>time() - 180,
//    'to'   => time(),
//]);
$ret = $sls->getProjectLogs([
    'query' => "SELECT * FROM pay_log WHERE __topic__ = '1' AND __time__ BETWEEN 151298900 AND 1512989165",
]);

//$ret = $sls->getLogStore();
var_dump($ret);