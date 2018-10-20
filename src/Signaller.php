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
    protected $atomic = 0;

    /**
     * @var bool
     */
    protected $nodeError = false;

    /**
     * @var bool
     */
    protected $nodeMsg = false;

    /**
     * Client constructor.
     * @param string $client
     * @param string $path
     */
    public function __construct(string $client = self::GUZZLE_CLIENT, string $path = '/tmp/services')
    {
        $this->setClient($client);
        $this->sentinel = new Sentinel($path);
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
            list($route, $config) = explode('|', false === strpos('|', $route) ? $route . '|' : $route);

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
                logger()->error('Signaller error: ' . $exception->getMessage());
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
        $this->client->atomic($this->atomic);

        try {
            // 解析route, 分离uri参数
            list($route, $config) = explode('|', false === strpos('|', $route) ? $route . '|' : $route);

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
            $this->client->atomic($this->atomic++);
        } catch (\Exception $exception) {
            if (!$this->nodeError) {
                /**
                 * 节点错误，生成一个错误调用，处理fallback
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
    public function fallback(\Closure $closure, $isRecord = true)
    {
        if (!$this->nodeError) {
            $this->client->fallback($closure, $isRecord, $this->nodeMsg);
        } else {
            $this->fallback[$this->atomic] = $closure;
            $this->atomic++;
        }

        // 重置节点错误
        $this->nodeError = false;

        return $this;
    }

    /**
     * @return Response
     */
    /**
     * @return mixed
     */
    public function send()
    {
        $responses = $this->client->send();
        if (!empty($this->fallback)) {
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
