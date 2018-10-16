<?php
/**
 * @author: ZhaQiu <34485431@qq.com>
 * @time: 2018/9/4
 */

namespace FastD\Signaller\Client;

use FastD\Signaller\Contracts\ClientInterface;
use FastD\Signaller\Response;

class SwooleClient implements ClientInterface
{

    public function send()
    {
        // TODO: Implement send() method.
    }

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
    public function simpleInvoke(string $method, string $uri, array $parameters = [], array $options = [])
    {
        // TODO: Implement simpleInvoke() method.
    }

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
    public function invoke(string $method, string $uri, array $parameters = [], array $options = [])
    {
        // TODO: Implement invoke() method.
    }
}
