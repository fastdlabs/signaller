<?php
/**
 * @author: ZhaQiu <34485431@qq.com>
 * @time: 2018/9/4
 */

namespace FastD\Signaller\Client;

use CURLFile;
use GuzzleHttp\Client;
use FastD\Signaller\Contracts\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use FastD\Signaller\Response;
use Psr\Http\Message\ResponseInterface;

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
     * @var int
     */
    protected $atomic = 0;

    /**
     * GuzzleClient constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $parameters
     * @param array $options
     * @return \FastD\Http\Response|ClientInterface|Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function simpleInvoke(string $method, string $uri, array $parameters = [], array $options = [])
    {
        $options = array_merge([
            'connect_timeout' => 5,
            'timeout' => 5,
            'http_errors' => false,
        ], $options);

        $response = $this->client->request(
            $method,
            $uri,
            $this->createRequestOptions($method, $parameters, $options)
        );

        return Response::createFromResponse($response);
    }


    /**
     * @param string $method
     * @param string $uri
     * @param array $parameters
     * @param array $options
     * @return $this
     */
    public function invoke(string $method, string $uri, array $parameters = [], array $options = [])
    {
        $options = array_merge([
            'connect_timeout' => 5,
            'timeout' => 5,
            'http_errors' => false,
        ], $options);

        $options = $this->createRequestOptions($method, $parameters, $options);

        switch (strtoupper($method)) {
            case 'GET':
                $this->promises[$this->atomic] = $this->client->getAsync($uri, $options);
                break;
            case 'POST':
                $this->promises[$this->atomic] = $this->client->postAsync($uri, $options);
                break;
            case 'PUT':
                $this->promises[$this->atomic] = $this->client->putAsync($uri, $options);
                break;
            case 'PATCH':
                $this->promises[$this->atomic] = $this->client->patchAsync($uri, $options);
                break;
            case 'DELETE':
                $this->promises[$this->atomic] = $this->client->deleteAsync($uri, $options);
                break;
            case 'HEAD':
                $this->promises[$this->atomic] = $this->client->headAsync($uri, $options);
                break;
        }

        return $this;
    }

    public function fallback(\Closure $closure, $isRecord = true, $nodeMsg = null)
    {
        $this->promises[$this->atomic]->then(
            function (ResponseInterface $response) {
                return $response;
            },
            function (RequestException $exception) use ($closure, $isRecord, $nodeMsg) {
                $isRecord && logger()->error(null === $nodeMsg ? $exception->getMessage() : $nodeMsg);
                return $closure;
            }
        );

        return $this;
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function send()
    {
        $response = Promise\unwrap($this->promises);

        return $this->createResponse($response);
    }


    /**
     * @param $response
     * @return mixed
     * @throws \Exception
     */
    public function createResponse($response)
    {
        foreach ($response as $key => $item) {
            //yield Response::createFromResponse($item);
            $responses[$key] = Response::createFromResponse($item);
        }

        return $responses ?? [];
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

    /**
     * @param int $number
     * @return $this
     */
    public function atomic(int $number)
    {
        $this->atomic = $number;

        return $this;
    }
}
