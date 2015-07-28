Tyr Component
=============

PHP library which makes curl calls to Tyr API.


## Composer

Install via composer

``` js
{
    "require": {
        "canalTP/tyr-component": "1.x"
    }
}
```


## Usage

Instanciate TyrService as a plain PHP object:

``` php
$tyrUrl = 'http://tyr.dev.canaltp.fr/v0/';
$endPointId = 2;

$tyrApi = new CanalTP\TyrComponent\TyrService($tyrUrl, $endPointId);

$user = $tyrApi->createUser('email', 'login', 'user type');
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
