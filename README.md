Tyr Component
=============

PHP library which makes curl calls to Tyr API.


## Composer

Install via composer

``` js
{
    "require": {
        "canaltp/tyr-component": "1.x"
    }
}
```


## Usage

Instanciate TyrService as a plain PHP object:

``` php
$tyrUrl = 'http://tyr.dev.canaltp.fr/v0/';
$endPointId = 2;

// Instanciating api
$tyrApi = new CanalTP\TyrComponent\TyrService($tyrUrl, $endPointId);

// Creating request
$user = $tyrApi->createUser('email', 'login', 'user type');

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

$tyrApi = new CanalTP\TyrComponent\TyrService($tyrUrl, $endPointId);

// Creating GuzzleHttp\Client mock...

$tyrApi->setClient($mockedClient);
```


## License

This project is under [GPL-3.0 License](LICENSE).
