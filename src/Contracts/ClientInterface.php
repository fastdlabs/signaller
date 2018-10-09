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
     * @param string $method
     * @param string $uri
     * @param array $parameters
     * @param array $options
     * @return ClientInterface|Response
     */
    public function invoke(string $method, string $uri, array $parameters = [], array $options = []);

}
