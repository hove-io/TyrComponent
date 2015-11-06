Tyr Component
=============

PHP library which makes curl calls to Tyr API.

Supports Guzzle3 and Guzzle5 since version `1.2`.


## Composer

Install via composer

``` js
{
    "require": {
        "canaltp/tyr-component": "~1.2"
    }
}
```


## Usage

Instanciate TyrService as a plain PHP object:

``` php
$tyrUrl = 'http://tyr.dev.canaltp.fr/v0/';
$endPointId = 2;

// Instanciating api
$tyrApi = new CanalTP\TyrComponent\TyrService($tyrUrl, $endPointId); // For Guzzle5
$tyrApi = new CanalTP\TyrComponent\Guzzle3\TyrService($tyrUrl, $endPointId); // For Guzzle3

// Creating request
$user = $tyrApi->createUser('email', 'login');

// Get last Guzzle response instance (usefull to get status code...)
$response = $tyrApi->getLastResponse();
$statusCode = $response->getStatusCode();
```

See [full Tyr class](src/TyrService.php).


### Testing

Mock Guzzle client:

``` php
$tyrUrl = 'http://tyr.dev.canaltp.fr/v0/';
$endPointId = 2;

$tyrApi = new CanalTP\TyrComponent\TyrService($tyrUrl, $endPointId); // For Guzzle5
$tyrApi = new CanalTP\TyrComponent\Guzzle3\TyrService($tyrUrl, $endPointId); // For Guzzle3

// Creating GuzzleHttp\Client mock...

$tyrApi->setClient($mockedClient);
```


## License

This project is under [GPL-3.0 License](LICENSE).
