<?php
/**
 * Created by VeryStar.
 * Author: hsx
 * Create: 2017/12/11 10:33
 * Editor: created by PhpStorm
 */

namespace Verypay;

class Sls
{

    /**
     * @var Curl
     */
    public $curl;
    public $version = '0.6.0';//当前API版本
    public $region;
    public $endpoint = 'cn-hangzhou.log.aliyuncs.com:80';//服务入口
    public $project;
    public $logstore;//日志库名
    public $sign_type = 'hmac-sha1';
    private $access_key;
    private $access_secret;


    public function __construct($curl = null)
    {
        if ($curl === null) {
            $this->curl = new Curl();
        } else {
            $this->curl = $curl;
        }
        $this->curl->setTimeout(20);
    }

    public function curl()
    {
        return $this->curl;
    }

    /**
     * 初始化参数
     *
     * @param array $options
     * @return $this
     */
    public function init($options = [])
    {
        if ($options) {
            $this->access_key    = empty($options['access_key']) ? "" : $options['access_key'];
            $this->access_secret = empty($options['access_secret']) ? "" : $options['access_secret'];
            $this->endpoint      = empty($options['endpoint']) ? "" : $options['endpoint'];
            $this->project       = empty($options['project']) ? "" : $options['project'];
            $this->logstore      = empty($options['logstore']) ? "" : $options['logstore'];
        }

        return $this;
    }

    /**
     * 设置日志库
     *
     * @param $logstore
     * @return $this
     */
    public function setLogStore($logstore)
    {
        $this->logstore = $logstore;
        return $this;
    }

    public function getProjectLogs($data_arr)
    {
        return $this->calApi('GET', '/logs', $data_arr);
    }

    /**
     *
     * 查询指定 Project 下某个 Logstore 中的日志数据
     * 文档地址 https://help.aliyun.com/document_detail/29029.html?spm=5176.doc29045.6.729.PdbdDm
     *
     * @param array $data_arr
     *
     * $data_arr = [
     *   'from'    => time() - 60, //int 是
     *   'to'      => time(),      //int 是
     *   'topic'   => 2,           //string 否 查询日志主题
     *   'query'   => '',    //string 否 查询语法 >> https://help.aliyun.com/document_detail/29060.html?spm=5176.doc29029.2.3.wqzVgs
     *   'line'    => 100,   //int 否 即limit值，默认100
     *   'offset'  => 0,     //int 否 默认0
     *   'reverse' => false, //bool 否 排序，true=>表示逆序,false 表示顺序,默认false
     * ];
     *
     * @return array|mixed
     *
     */
    public function getLog($data_arr)
    {
        $data_arr['type'] = 'log';
        return $this->calApi('GET', '/logstores/' . $this->logstore, $data_arr);
    }

    /**
     *
     * 查询指定的 project 下某个 logstore 中日志的分布情况
     *
     * @param $data_arr
     * @return array|mixed
     */
    public function getHistograms($data_arr)
    {
        $data_arr['type'] = 'histogram';
        return $this->calApi('GET', '/logstores/' . $this->logstore, $data_arr);
    }

    /**
     * 获取指定 project 下的所有 logstore 的名称
     *
     * GET
     */
    public function getLogStoreList()
    {
        return $this->calApi('GET', '/logstores');
    }

    /**
     * 获取当前日志库
     *
     * @return mixed
     */
    public function getLogStore()
    {
        $data_arr['logstoreName'] = $this->logstore;
        return $this->calApi('GET', '/logstores/' . $this->logstore, $data_arr);
    }

    /**
     * 生成请求签名
     *
     * @param $method
     * @param $params
     * @param $headers
     * @param $resource
     * @return string
     */
    private function getSign($method, $resource, $params, $headers)
    {
        $content  = '';
        $content .= $method . "\n";
        if (isset($headers['Content-MD5'])) {
            $content .= $headers['Content-MD5'];
        }

        $content .= "\n";
        if (isset($headers['Content-Type'])) {
            $content .= $headers['Content-Type'];
        }
        $content .= "\n";
        $content .= $headers['Date'] . "\n";
        $content .= $this->canonicalizedLogHeaders($headers) . "\n";
        $content .= $this->canonicalizedResource($resource, $params);

        $sign     = base64_encode(hash_hmac("sha1", $content, $this->access_secret, true));
        return $sign;
    }

    /**
     * 自定义头构造的字符串
     *
     * @param $header
     * @return string
     */
    private function canonicalizedLogHeaders($header)
    {
        $header = Helper::argSort($header);
        $arg = [];
        foreach ($header as $key => $val) {
            if (strpos($key, "x-log-") === 0 || strpos ($key, "x-acs-") === 0) {
                $arg[] = $key . ":" . $val;
            }
        }
        return $ret = implode("\n", $arg);
    }

    /**
     * 请求资源构造的字符串
     *
     * @param $resource
     * @param $params
     * @return string
     */
    private function canonicalizedResource($resource, $params)
    {
        if ($params) {
            $params = Helper::argSort($params);
            return $resource . '?' . Helper::createLinkstring($params);
        }
        return $resource;
    }

    /**
     * header头中 Content-MD5字段
     *
     * @param $str string
     * @return string
     */
    private function contentMd5($str)
    {
        return strtoupper(md5($str));
    }

    /**
     * 转换成curl header头 格式
     *
     * @param $headers
     * @return array
     */
    private function generateHeader($headers)
    {
        $temp_headers = [];

        foreach ($headers as $k => $v) {
            $temp_headers[] = $k . ': ' . $v;
        }
        return $temp_headers;
    }

    /**
     * 请求API
     *
     * @param $method
     * @param $resource
     * @param array $get_param
     * @param null $post_body
     * @param bool $to_array
     * @return array|mixed
     */
    private function calApi($method, $resource, $get_param = [], $post_body = null, $to_array = true)
    {
        if ($post_body) {
            $headers["Content-Type"]       = "application/json";
            $headers['Content-Length']     = strlen($post_body);
            $headers['Content-MD5']        = $this->contentMd5($post_body);
        } else {
            $headers ['Content-Length']    = 0;
            $headers ['Content-Type']      = '';
        }
        $headers["x-log-bodyrawsize"]      = 0;
        $headers['x-log-apiversion']       = $this->version;
        $headers['x-log-signaturemethod']  = $this->sign_type;
        $headers['Date']                   = gmdate('D, d M Y H:i:s') . ' GMT';
        $headers['Host']                   = $this->project . '.' . $this->endpoint;
        $headers['Authorization']          = "LOG " . $this->access_key . ':' . $this->getSign($method, $resource, $get_param, $headers);

        if ($get_param) {
            $resource .= '?' . Helper::createLinkstringUrlencode($get_param);
        }

        $url       = 'http://' . $this->project . '.' . $this->endpoint . ':80' . $resource;
        $this->curl->setHeader($this->generateHeader($headers));

        if ($method == 'GET') {
            $res   = $this->curl->get($url);
        } else {
            $res   = $this->curl->post($url, $post_body);
        }

        if ($res->getError()) {
            $resp_data = [
                'errorCode'    => 28,
                'errorMessage' => '服务器繁忙，请重试:' . $res->getError(),
            ];
        } else {
            $resp_data = $to_array ? json_decode($res->getBody(), true) : $res->getBody();
        }
        return $resp_data;
    }

}