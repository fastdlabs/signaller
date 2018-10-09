<?php
/**
 * @author: ZhaQiu <34485431@qq.com>
 * @time: 2018/9/3
 */

namespace FastD\Signaller;

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use FastD\Http\Response as FastDResponse;

class Response extends FastDResponse
{

    /**
     * @return array
     */
    public function toArray()
    {
        $result = json_decode((string)$this->getBody(), true);
        is_null($result) && $result = [(string)$this->getBody()];

        return $result;
    }

    /**
     * @param $response
     * @return Response
     * @throws \Exception
     */
    public static function createFromResponse($response)
    {

        if ($response instanceof GuzzleResponse) {
            return new static(
                $response->getBody(),
                $response->getStatusCode(),
                $response->getHeaders()
            );
        } elseif ($response instanceof FastDResponse) {
            return $response;
        }

        throw new \Exception('');
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->getStatusCode() >= 200 && $this->getStatusCode() < 300;
    }
}
