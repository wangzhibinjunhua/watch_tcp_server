<?php
/**
 * PhalApi客户端SDK包（PHP版）
 *
 * - 以接口查询语言（ASQL）的方式来实现接口请求
 * - 出于简明客户端，将全部的类都归于同一个文件，避免过多的加载
 *
 * <br>使用示例：<br>
 ```
 * $rs = HttpClient::create()
 *   ->withHost('http://demo.phalapi.net/')
 *   ->withService('Default.Index')
 *   ->withParams('name', 'dogstar')
 *   ->withTimeout(3000)
 *   ->request();
 *
 * var_dump($rs->getRet(), $rs->getData(), $rs->getMsg());
```
    *
    * @package     PhalApi\SDK
    * @license     http://www.phalapi.net/license GPL 协议
    * @link        http://www.phalapi.net/
    * @author      dogstar <chanzonghuang@gmail.com> 2015-10-16
 */

class Common_HttpClient {

    protected $host;
    protected $filter;
    protected $parser;
    protected $service;
    protected $timeoutMs;
    protected $params = array();

    /**
     * 创建一个接口实例，注意：不是单例模式
     * @return HttpClient
     */
    public static function create() {
        return new self();
    }

    protected function __construct() {
        $this->host = "";

        $this->parser = new HttpClientParserJson();
    }

    /**
     * 设置接口域名
     * @param string $host
     * @return HttpClient
     */
    public function withHost($host) {
        $this->host = $host;
        return $this;
    }

    /**
     * 设置过滤器，与服务器的DI()->filter对应
     * @param HttpClientFilter $filter 过滤器
     * @return HttpClient
     */
    public function withFilter(HttpClientFilter $filter) {
        $this->filter = $filter;
        return $this;
    }

    /**
     * 设置结果解析器，仅当不是JSON返回格式时才需要设置
     * @param HttpClientParser $parser 结果解析器
     * @return HttpClient
     */
    public function withParser(HttpClientParser $parser) {
        $this->parser = $parser;
        return $this;
    }

    /**
     * 重置，将接口服务名称、接口参数、请求超时进行重置，便于重复请求
     * @return HttpClient
     */
    public function reset() {
        $this->service = "";
        $this->timeoutMs = 3000;
        $this->params = array();

        return $this;
    }

    /**
     * 设置将在调用的接口服务名称，如：Default.Index
     * 这个是基于phalapiclient 修改而来,http 请求不需要用这个service
     * @param string $service 接口服务名称
     * @return HttpClient
     */
    public function withService($service) {
        $this->service = $service;
        return $this;
    }

    /**
     * 设置接口参数，此方法是唯一一个可以多次调用并累加参数的操作
     * @param string $name 参数名字
     * @param string $value 值
     * @return HttpClient
     */
    public function withParams($name, $value) {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * 设置超时时间，单位毫秒
     * @param int $timeoutMS 超时时间，单位毫秒
     * @return HttpClient
     */
    public function withTimeout($timeoutMS) {
        $this->timeoutMS = $timeoutMS;
        return $this;
    }

    /**
     * 发起接口请求
     * @return HttpClientResponse
     */
    public function request() {
        $url = $this->host;

        //del ?service=
        // if (!empty($this->service)) {
        //     $url .= '?service=' . $this->service;
        // }
        if ($this->filter !== null) {
            $this->filter->filter($this->service, $this->params);
        }
        $rs = $this->doRequest($url, $this->params, $this->timeoutMs);
        //return $this->parser->parse($rs);
        return $rs;
    }

    protected function doRequest($url, $data, $timeoutMs = 3000)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $timeoutMs);

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $rs = curl_exec($ch);

        curl_close($ch);

        return $rs;
    }
}

/**
 * 接口返回结果
 *
 * - 与接口返回的格式对应，即有：ret/data/msg
 */
class HttpClientResponse {

    protected $ret = 200;
    protected $data = array();
    protected $msg = '';

    public function __construct($ret, $data = array(), $msg = '') {
        $this->ret = $ret;
        $this->data = $data;
        $this->msg = $msg;
    }

    public function getRet() {
        return $this->ret;
    }

    public function getData() {
        return $this->data;
    }

    public function getMsg() {
        return $this->msg;
    }
}

/**
 * 接口过滤器
 *
 * - 可用于接口签名生成
 */
interface HttpClientFilter {

    /**
     * 过滤操作
     * @param string $service 接口服务名称
     * @param array $params 接口参数，注意是引用。可以直接修改
     * @return null
     */
    public function filter($service, array &$params);
}

/**
 * 接口结果解析器
 *
 * - 可用于不同接口返回格式的处理
 */
interface HttpClientParser {

    /**
     * 结果解析
     * @param string $apiResult
     * @return HttpClientResponse
     */
    public function parse($apiResult);
}

/**
 * JSON解析
 */
class HttpClientParserJson implements HttpClientParser {

    public function parse($apiResult) {
        if ($apiResult === false) {
            return new HttpClientResponse(408, array(), 'Request Timeout');
        }

        $arr = json_decode($apiResult, true);

        if ($arr === false || empty($arr)) {
            return new HttpClientResponse(500, array(), 'Internal Server Error');
        }

        return new HttpClientResponse($arr['ret'], $arr['data'], $arr['msg']);
    }
}
