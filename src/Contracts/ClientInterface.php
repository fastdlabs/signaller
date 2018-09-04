<?php
/**
 * @author: ZhaQiu <34485431@qq.com>
 * @time: 2018/9/3
 */

namespace Sdk\Signaller\Contracts;

interface ClientInterface
{

    /**
     * @param string $uri
     * @param array $route
     * @param array $parameters
     * @param array $headers
     * @return mixed
     */
    public function select(string $method, string $uri, array $parameters = [], array $headers = []);

    public function send();
}
