<?php
/**
 * @author: ZhaQiu <34485431@qq.com>
 * @time: 2018/9/3
 */

namespace Sdk\Signaller\Contracts;

use Sdk\Signaller\Response;

/**
 * Interface ClientInterface
 * @package Sdk\Signaller\Contracts
 */
interface ClientInterface
{

    /**
     * @param string $method
     * @param string $uri
     * @param array $parameters
     * @param array $options
     * @return $this
     */
    public function asyncRequest(string $method, string $uri, array $parameters = [], array $options = []);

    /**
     * @param string $method
     * @param string $uri
     * @param array $parameters
     * @param array $options
     * @return ClientInterface|Response
     */
    public function request(string $method, string $uri, array $parameters = [], array $options = []);

    /**
     * @return Response|array
     */
    public function select();
}
