<?php
/**
 * Created by VeryStar.
 * Author: hsx
 * Create: 2017/12/11 14:56
 * Editor: created by PhpStorm
 */
ini_set('memory_limit','256M');
include_once "../src/Helper.php";
include_once "../src/Curl.php";
include_once "../src/Sls.php";
define('DS', DIRECTORY_SEPARATOR);
define('APP_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DS);
@date_default_timezone_set('Asia/Shanghai');
$sls = new \Verypay\SlsLog\Sls();
$sls->init([
    'access_key'    => '',
    'access_secret' => '',
    'endpoint'      => 'cn-hangzhou.log.aliyuncs.com',
    'project'       => '',
    'logstore'      => '',
]);
$time = 1512989165;
//$ret = $sls->getLogStoreList();
$ret = $sls->getLog([
    'from'   => strtotime('2017-12-12 09:58:00'),
    'to'     => strtotime('2017-12-12 10:00:00'),
    'line'   => '100',
    'offset' => 0,
    'query' => 'http_status:0 and __topic__:2'
]);

//$ret = $sls->getLogStore();
$ret2 = $sls->getHistograms([
    'from'   => strtotime('2017-12-12 09:58:00'),
    'to'     => strtotime('2017-12-12 10:00:00'),
    'query' => 'http_status:0 and __topic__:2'
]);
//$ret = $sls->getProjectLogs([
//    'query' => "SELECT * FROM pay_log WHERE http_status = 200 AND __date__ BETWEEN '2017-12-12 09:50:00' AND '2017-12-12 10:00:00'",
//]);

//$ret = $sls->getLogStore();

//$ret = $sls->getShardList();

//$ret = $sls->getCursor([
//    'shard' => 0,
//    'from' => 'end',
//]);
//$ret = $sls->batchGetLogs([
//    'count'  => 2,
//    'shard'  => 0,
//    'cursor' => 'MTUxMTQ0MTk1MTk4MjU4NzMyNQ==',
//]);


print_r($ret);
print_r($sls->curl->getHeader());
