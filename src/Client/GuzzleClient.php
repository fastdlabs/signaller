<?php
/**
 * @author: ZhaQiu <34485431@qq.com>
 * @time: 2018/9/4
 */

namespace Sdk\Signaller\Client;

use CURLFile;
use GuzzleHttp\Client;
use Sdk\Signaller\Contracts\ClientInterface;
use GuzzleHttp\Promise;
use Sdk\Signaller\Response;

class GuzzleClient implements ClientInterface
{

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Client[]
     */
    protected $promises;

    /**
     * GuzzleClient constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param string $route
     * @param array $parameters
     * @param array $headers
     * @return $this
     */
    public function select(string $method, string $uri, array $parameters = [], array $options = [])
    {
        $options = array_merge([
            'connect_timeout' => 5,
            'timeout' => 5,
            'http_errors' => false,
        ], $options);

        $options = $this->createRequestOptions($method, $parameters, $options);

        switch (strtoupper($method)) {
            case 'GET':
                $this->promises[] = $this->client->getAsync($uri, $options);
                break;
            case 'POST':
                $this->promises[] = $this->client->postAsync($uri, $options);
                break;
            case 'PUT':
                $this->promises[] = $this->client->putAsync($uri, $options);
                break;
            case 'PATCH':
                $this->promises[] = $this->client->patchAsync($uri, $options);
                break;
            case 'DELETE':
                $this->promises[] = $this->client->deleteAsync($uri, $options);
                break;
            case 'HEAD':
                $this->promises[] = $this->client->headAsync($uri, $options);
                break;
        }

        return $this;
    }

    /**
     * @throws \Throwable
     */
    public function send()
    {
        $response = Promise\unwrap($this->promises);
        if (1 === count($response)) {
            return Response::createFromResponse($response[0]);
        } else {
            return $this->createResponse($response);
        }
    }

    /**
     * @param $response
     * @return mixed
     * @throws \Exception
     */
    public function createResponse($response)
    {
        foreach ($response as $item) {
            //yield Response::createFromResponse($item);
            $responses[] = Response::createFromResponse($item);
        }

        return $responses;
    }

    /**
     * @param $method
     * @param array $parameters
     * @param array $options
     * @return array
     */
    protected function createRequestOptions($method, $parameters = [], array $options = [])
    {

        if ('GET' !== $method) {
            $multipart = $this->createMultipart($parameters);
            if (!empty($multipart)) {
                $options['multipart'] = $multipart;
            }
        } else {
            $options['query'] = $parameters;
        }

        return $options;
    }

    /**
     * @param array $parameters
     * @param string $prefix
     * @return array
     */
    protected function createMultipart(array $parameters, $prefix = '')
    {
        $return = [];

        foreach ($parameters as $name => $value) {
            $item = [
                'name' => empty($prefix) ? $name : "{$prefix}[{$name}]",
            ];
            switch (true) {
                case (is_object($value) && ($value instanceof CURLFile)):
                    $item['contents'] = fopen($value->getFilename(), 'r');
                    $item['filename'] = $value->getPostFilename();
                    $item['headers'] = [
                        'content-type' => $value->getMimeType(),
                    ];
                    break;
                case (is_string($value) && is_file($value)):
                    $item['contents'] = fopen($value, 'r');
                    break;
                case is_array($value):
                    $return = array_merge($return, $this->createMultipart($value, $item['name']));
                    continue 2;
                default:
                    $item['contents'] = $value;
            }
            $return[] = $item;
        }

        return $return;
    }
}
