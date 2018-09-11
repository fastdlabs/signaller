<?php
/**
 * @author: ZhaQiu <34485431@qq.com>
 * @time: 2018/9/3
 */

namespace Sdk\Signaller;

use Sdk\Signaller\Client\GuzzleClient;
use Sdk\Signaller\Client\SwooleClient;
use Sdk\Signaller\Contracts\ClientInterface;

class Service
{

    const SWOOLE_CLIENT = 'swoole';
    const GUZZLE_CLIENT = 'guzzle';

    const TIMEOUT = 'timeout';

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var Sentinel
     */
    protected $sentinel;

    /**
     * Service constructor.
     * @param string $client
     */
    public function __construct(string $client = self::GUZZLE_CLIENT, string $path = '/tmp/services')
    {
        $this->setClient($client);
        $this->sentinel = new Sentinel($path);
    }

    /**
     * @param string $serverName
     * @param string $route
     * @param null $parameters
     * @param array $options
     * @return $this
     */
    public function asyncRequest(string $serverName, string $route, $parameters = null, array $options = [])
    {
        $route = $this->sentinel->route($serverName, $route);
        $uri = $this->getUri($serverName, $route[1]);

        $this->client->asyncRequest($route[0], $uri, $parameters, $options);

        return $this;
    }

    /**
     * @param string $serverName
     * @param string $route
     * @param null $parameters
     * @param array $options
     * @return ClientInterface|Response
     */
    public function request(string $serverName, string $route, $parameters = null, array $options = [])
    {
        $route = $this->sentinel->route($serverName, $route);
        $uri = $this->getUri($serverName, $route[1]);

        return $this->client->request($route[0], $uri, $parameters, $options);
    }

    /**
     * @return Response
     */
    public function select()
    {
        return $this->client->select();
    }

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
