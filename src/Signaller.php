<?php
/**
 * @author: ZhaQiu <34485431@qq.com>
 * @time: 2018/9/3
 */

namespace FastD\Signaller;

use FastD\Signaller\Client\GuzzleClient;
use FastD\Signaller\Client\SwooleClient;
use FastD\Signaller\Contracts\ClientInterface;
use FastD\Signaller\Exception\NodeException;
use FastD\Signaller\Exception\SignallerException;

class Signaller
{

    const VERSION = '0.0.1-beta';
    const SWOOLE_CLIENT = SwooleClient::class;
    const GUZZLE_CLIENT = GuzzleClient::class;
    /**
     * @var ClientInterface
     */
    protected $client;
    /**
     * @var Sentinel
     */
    protected $sentinel;
    /**
     * @var array
     */
    protected $fallback;
    /**
     * @var int
     */
    protected $atomic = -1;
    /**
     * @var bool
     */
    protected $nodeError = false;
    /**
     * @var null
     */
    protected $nodeMsg = false;
    /**
     * @var bool
     */
    protected $isRecord = true;

    /**
     * Client constructor.
     * @param string $client
     * @param string $path
     */
    public function __construct(string $client = self::GUZZLE_CLIENT, string $path = '/tmp/services', $isRecord = true)
    {
        $this->setClient($client);
        $this->sentinel = new Sentinel($path);
        $this->isRecord = $isRecord;
    }

    /**
     * @param string $serverName
     * @param string $route
     * @param array $parameters
     * @param array $options
     * @return Response
     */
    public function simpleInvoke(string $serverName, string $route, array $parameters = [], array $options = [], $callback = null)
    {
        try {
            // 解析route, 分离uri参数
            [$route, $config] = explode('|', false === strpos('|', $route) ? $route . '|' : $route);
            $route = $this->sentinel->route($serverName, $route);
            $uri = $this->getUri($serverName, $route[1]);
            if ('' !== $config) {
                // 动态路由进行赋值
                $keys = [];
                foreach (explode(',', $config) as $item) {
                    list($key, $values[]) = explode(':', $item);
                    $keys[] = "{{$key}}";
                }
                $uri = str_replace($keys, $values, $uri);
            }

            return $this->client->simpleInvoke($route[0], $uri, $parameters, $options);
        } catch (\Exception $exception) {
            if (null !== $callback && $callback instanceof \Closure) {
                $this->isRecord && logger()->error('Signaller error: ' . $exception->getMessage());

                return $callback();
            } else {
                throw new SignallerException($exception->getMessage());
            }
        }
    }

    /**
     * @param string $serverName
     * @param string $route
     * @param array $parameters
     * @param array $options
     * @return $this
     */
    public function invoke(string $serverName, string $route, array $parameters = [], array $options = [])
    {
        /**
         * 请求计数器
         */
        $this->atomic++;
        $this->client->atomic($this->atomic);
        $this->nodeError = false;
        try {
            // 解析route, 分离uri参数
            [$route, $config] = explode('|', false === strpos('|', $route) ? $route . '|' : $route);
            $route = $this->sentinel->route($serverName, $route);
            $uri = $this->getUri($serverName, $route[1]);
            if ('' !== $config) {
                // 动态路由进行赋值
                $keys = [];
                foreach (explode(',', $config) as $item) {
                    list($key, $values[]) = explode(':', $item);
                    $keys[] = "{{$key}}";
                }
                $uri = str_replace($keys, $values, $uri);
            }
            $this->client->invoke($route[0], $uri, $parameters, $options);
            //$this->client->atomic($this->atomic++);
        } catch (\Exception $exception) {
            if (!$this->nodeError) {
                /**
                 * 节点错误，记录错误信息
                 */
                $this->nodeMsg = $exception->getMessage();
                $this->nodeError = true;
            } else {
                /**
                 * 连续两次调用invoke但没有用fallback,直接抛出错误
                 */
                throw new NodeException($exception->getMessage());
            }
        }

        return $this;
    }

    /**
     * @param \Closure $closure
     * @return $this
     */
    public function fallback(\Closure $closure)
    {
        if (!$this->nodeError) {
            $this->client->fallback($closure, $this->nodeMsg);
            $this->nodeMsg = null;
        } else {
            $this->isRecord && logger()->error($this->nodeMsg);
            $this->fallback[$this->atomic] = $closure;
        }

        return $this;
    }

    /**
     * @return array|Response
     */
    public function send()
    {
        $responses = $this->client->send();
        if (!empty($this->fallback)) {
            /**
             * @var $item \Closure
             */
            foreach ($this->fallback as $key => $item) {
                $responses[$key] = $item();
            }
        }
        if (1 === count($responses)) {
            return current($responses);
        } else {
            return $responses;
        }
    }

    /**
     * @param $serverName
     * @param $path
     * @return string
     */
    public function getUri($serverName, $path)
    {
        return $this->sentinel->protocol($serverName) . '://' .
            $this->sentinel->host($serverName) . ':' .
            $this->sentinel->port($serverName) . $path;
    }

    /**
     * @param string $client
     */
    public function setClient(string $client)
    {
        switch ($client) {
            case self::SWOOLE_CLIENT:
                $this->client = new SwooleClient();
                break;
            case self::GUZZLE_CLIENT:
                $this->client = new GuzzleClient();
                break;
            default:
                $this->client = new GuzzleClient();
        }
    }
}
