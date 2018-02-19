PHP Cache-Helper
================

PHP Caching helper. It supports several cache-engine.

- Apc;
- Apcu;
- Memcached;
- Redis;
- Wincache;
- Xcache;
- FileCache (as an alternative or fallback).

Background
----------

The original code taken from [Fatfree][1] Cache class. Because the original code was very complex for me, i decide to make this repo.

Installation
------------

`composer require eghojansu/cache-helper:dev-master`

Usage
-----

Main Cache class will proxy method call to the driver.
To use a driver you can pass simple dsn format to first constructor.

```php
<?php

use Fal\Cache\Cache;
use Fal\Cache\Serializer;

$cache = new Cache(
    // simple dsn, @see simple dsn table below, pass '' (empty string) to disable
    'apcu',
    // prefix, can pass '' (empty string) but not null value
    '',
    // fallback file cache dir
    '/path/to/cache/dir/fallback',
    // Serializer helper
    new Serializer()
);

// get cache, it will return empty array if cache does not exist or cache was expired
// if cache exist it return array of [data, time, ttl]
$cached = $cache->get('foo');
if ($cached) {
    list( $data ) = $cached;
} else {
    $data = 'foo';
    $cache->set('foo', $data);
}

echo $data;

```

DSN Format
----------

```
+-----------+----------------------------------------------------+------------------------+
| Driver    | DSN                                                | ~                      |
+-----------+----------------------------------------------------+------------------------+
| Apc       | apc                                                |                        |
| Apcu      | apcu                                               |                        |
| Memcached | memcached=host[:port];host2[:port];...hostn[:port] |                        |
| Redis     | redis=host[:port[:db]]                             |                        |
| Wincache  | wincache                                           |                        |
| Xcache    | xcache                                             |                        |
| Filecache | folder=/path/to/cache/dir/                         | Require trailing slash |
| NoCache   | (empty string)                                     | To disable cache       |
+-----------+----------------------------------------------------+------------------------+
```

If dsn supplied and no match based on above rule, FileCache will be used.

Credits
-------

- [Fatfree Framework Author and Contributors][1]


[1]: http://fatfreeframework.com