
# signaller


## example

```php


require __DIR__ . '/vendor/autoload.php';

use FastD\Signaller\Sentinel;
use FastD\Signaller\Service;

// usage

$sdk = signaller()->asyncRequest('demo', 'demo', $parameter, [
    'headers' => [
        
    ],
    'body' => '以后'
]);

// $options see guzzle request $options
$sdk->asyncRequest('demo', 'demo', $parameter, $options);

$responses = $sdk->send();

foreach ($responses as $response) {
    var_dump($response->isSuccessful());
    var_dump($response->toArray());
}

// also you can use the simpleInvoke method, it will at once return the response

$response  = signaller()->asyncRequest('demo', 'demo', $parameter, [
    'headers' => [
        
    ],
    'body' => '以后'
]);

```