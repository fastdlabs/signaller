<?php
/**
 * @author: ZhaQiu <34485431@qq.com>
 * @time: 2018/9/4
 */

namespace FastD\Signaller\Client;

use FastD\Signaller\Contracts\ClientInterface;

class SwooleClient implements ClientInterface
{

    /**
     * @param string $uri
     * @param array $route
     * @param array $parameters
     * @param array $headers
     * @return mixed
     */
    public function select(string $method, string $uri, array $parameters = [], array $headers = [])
    {
        // TODO: Implement select() method.
    }

    public function send()
    {
        // TODO: Implement send() method.
    }
}
