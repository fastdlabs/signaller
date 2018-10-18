
# signaller


## example

```php


require __DIR__ . '/vendor/autoload.php';

use FastD\Signaller\Sentinel;
use FastD\Signaller\Service;

// usage

$sdk = signaller()->invoke('demo', 'demo', $parameter, [
    'headers' => [
        
    ],
    'body' => '以后'
]);

// $options see guzzle request $options
$sdk->asyncRequest('demo', 'demo', $parameter, $options);

$responses = $sdk->send();

foreach ($responses as $response) {
    var_dump($response->isSuccessful());
    var_dump($response->getBody());
}

// Also you can use the simpleInvoke method, It will return the response immediately

$response  = signaller()->simpleInvoke('demo', 'demo', $parameter, [
    'headers' => [
        
    ],
    'body' => '我们以后'
]);

```