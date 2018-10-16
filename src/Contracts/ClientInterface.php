<?php
/**
 * @author: ZhaQiu <34485431@qq.com>
 * @time: 2018/9/3
 */

namespace FastD\Signaller\Contracts;

use FastD\Signaller\Response;

/**
 * Interface ClientInterface
 * @package Sdk\Signaller\Contracts
 */
interface ClientInterface
{
    /**
     *
     * Simple invoke,It will at once return the response object
     *
     * @param string $method
     * @param string $uri
     * @param array $parameters
     * @param array $options
     * @return Response
     */
    public function simpleInvoke(string $method, string $uri, array $parameters = [], array $options = []);

    /**
     *
     * When all the invoke are ready. You need call the send method and return the response
     *
     * @param string $method
     * @param string $uri
     * @param array $parameters
     * @param array $options
     * @return $this
     */
    public function invoke(string $method, string $uri, array $parameters = [], array $options = []);

    /**
     * @return Response
     */
    public function send();
}
