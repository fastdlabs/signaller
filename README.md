
# signaller


## example

```php

require __DIR__ . '/vendor/autoload.php';

use Sdk\Signaller\Sentinel;
use Sdk\Signaller\Service;


$sdk = new Service();

$sdk->asyncRequest('demo', 'demo', ['以后'], [
    'headers' => [
        'x_consumer_custom_id' => 1,
        'accept_language' => 'zh-TW'
    ],
    'body' => '以后'
]);
$sdk->asyncRequest('demo', 'demo', ['我们以后'], [
    'headers' => [
        'x_consumer_custom_id' => 1,
        'accept_language' => 'zh-TW'
    ],
]);

$responses = $sdk->select();

foreach ($responses as $response) {
    var_dump($response->isSuccessful());
    var_dump($response->toArray());
}


```