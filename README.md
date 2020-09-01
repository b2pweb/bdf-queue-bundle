
[![Build Status](https://travis-ci.org/b2pweb/bdf-queue-bundle.svg?branch=master)](https://travis-ci.org/b2pweb/bdf-queue-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/b2pweb/bdf-queue-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/b2pweb/bdf-queue-bundle/?branch=master)
[![Packagist Version](https://img.shields.io/packagist/v/b2pweb/bdf-queue-bundle.svg)](https://packagist.org/packages/b2pweb/bdf-queue-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/b2pweb/bdf-queue-bundle.svg)](https://packagist.org/packages/b2pweb/bdf-queue-bundle)

Installation
============

1 Download the Bundle
---------------------

Download the latest stable version of this bundle with composer:

```bash
    $ composer require b2pweb/bdf-queue-bundle
```

2 Enable the Bundle
-------------------

Adding the following line in the ``config/bundles.php`` file of your project::

```php
<?php
// config/bundles.php

return [
    // ...
    Bdf\QueueBundle\BdfQueueBundle::class => ['all' => true],
    // ...
];
```

3 Set environment
-----------------

Add your dsn on the`.env` file

```
BDF_QUEUE_CONNETION_URL=gearman://root@127.0.0.1?client-timeout=10
```

4 Add configuration
-------------------

Add a default config file to `./config/packages/bdf_queue.yaml`

```yaml
bdf_queue:
  default_connection: 'gearman'
  default_serializer: 'bdf'
  connections:
    gearman:
      url: '%env(resolve:BDF_QUEUE_CONNETION_URL)%'
      serializer:
        id: 'native'
      options:
        client-timeout: 1
  destinations:
    bus:
      url: 'queue://gearman/bus'
      consumer:
        handler: 'var_dump'
        #retry: 0
        #max: 2
        #limit: 100
        #memory: 128
        #save: true
        #no_failure: true
        #stop_when_empty: true
        #auto_handle: true
        #middlewares:
        #  - 'bench'
```
