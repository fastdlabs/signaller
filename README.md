# SDK for Services

## 外部调用 (经由网关)

```php
<?php

use Sdk\Service\Service;

$service = new Service(Service::MODEL_GATEWAY, 'xxxxxxxxx', true);

$response = $sdk->get(Service::SERVICE_ORDER, '/orders/ZWZJ151324829100000020');

if ($response->isSuccessful()) {
    return $response->toArray();
}

```

## 服务间调用 (经由SLB)
```php
<?php
use Sdk\Service\Service;

/**
 * 可以不用设置消费者, 会从请求中自动获取消费者 ID
 */

$service = new Service(null, true);

$response = $sdk->get(Service::SERVICE_ORDER, '/orders/ZWZJ151324829100000020');

if ($response->isSuccessful()) {
    return $response->toArray();
}
```

## 上传文件
上传普通文件
```php
<?php
use Sdk\Service\Service;

$service = new Service(Service::MODEL_SLB, null, true);

$response = $service->post(Service::SERVICE_ORDER, '/orders', [
    'images' => '/Users/runner/Downloads/demo.gid',
]);

if ($response->isSuccessful()) {
    return $response->toArray();
}

```