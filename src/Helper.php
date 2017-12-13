<?php

namespace Verypay\SlsLog;

/**
 * Defines a few helper methods.
 *
 * @author hsx huashunxin01@gmail.com
 */
class Helper
{
    /**
     *
     * 检测一个字符串否为Json字符串
     *
     * @param string $string
     *
     * @return true/false
     *
     */
    public static function isJson($string)
    {
        if (strpos($string, "{") !== false) {
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        } else {
            return false;
        }
    }

    /**
     * 除去数组中的空值和签名参数
     *
     * @param array $para   签名参数组
     * @param array $filter 过滤的参数
     *
     * @return array
     */
    public static function paraFilter($para, $filter = ['sign'])
    {
        $para_filter = array();
        foreach ($para as $key => $val) {
            if (in_array(strtolower(trim($key)), $filter) || trim($val) === "") {
                continue;
            } else {
                $para_filter[$key] = $para[$key];
            }
        }
        return $para_filter;
    }

    /**
     * 对数组排序
     *
     * @param array $para 排序前的数组
     *
     * @return array
     */
    public static function argSort($para)
    {
        ksort($para, SORT_STRING);
        reset($para);
        return $para;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     *
     * @param array $para 需要拼接的数组
     *
     * @return string
     */
    public static function createLinkstring($para)
    {
        $arg = [];
        foreach ($para as $key => $val) {
            $arg[] = $key . "=" . $val;
        }
        return implode('&', $arg);
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     *
     * @param array $para 需要拼接的数组
     *
     * @return string
     */
    public static function createLinkstringUrlencode($para)
    {
        $arg = [];
        foreach ($para as $key => $val) {
            $arg[] = $key . "=" . rawurlencode($val);
        }
        return implode('&', $arg);
    }

    /**
     * 转化方法 很重要
     *
     * @param object $object
     *
     * @return mixed
     */
    public static function object2array($object)
    {
        //return @json_decode(@json_encode($object), 1);
        return @json_decode(preg_replace('/{}/', '""', @json_encode($object)), 1);
    }


    /**
     * array转xml
     *
     * @param array $arr
     *
     * @return string
     */
    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 将xml转为array
     *
     * @param string $xml
     *
     * @return array
     */
    public static function xmlToArray($xml)
    {
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    /**
     * 随机生成16位字符串
     *
     * @param int $length
     *
     * @return string 生成的字符串
     */
    public static function getRandomStr($length = 16)
    {
        return substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwzxyABCDEFGHIJKLMNOPQRSTUVWZXY'), 0, $length);
    }

    /****
     *
     * 安全获取数组key值
     *
     * @param      $array
     * @param      $key
     * @param null $default
     * @return bool|mixed|null
     */
    public static function safeGet($array, $key, $default = null)
    {
        if (!is_array($array) || !$array) {
            return false;
        }
        if (isset($array[$key])) {
            return $array[$key];
        }
        return $default;
    }
}
