
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
      # A URL with connection information. 
      # Any parameter value parsed from this string will override explicitly set parameters. 
      # Format: {driver}+{vendor}://{user}:{password}@{host}:{port}/{queue}?{option}=value
      url: '%env(resolve:BDF_QUEUE_CONNETION_URL)%'
      
      # Use those attribute to declare the connection if no url has been provided.
      driver:   ~
      vendor:   ~
      queue:    ~
      host:     ~
      port:     ~
      user:     ~
      password: ~
    
      serializer:
        # The serializer ID. This ID will be prefix by "bdf_queue.serializer". Defined values: native, bdf, bdf_json.
        id: 'native'
        # The serializer service ID (without '@').
        #service : ~
        
      # Options of the connection. See https://github.com/b2pweb/bdf-queue for the list of available options.
      options:
        #key: ~ 
  
      
      # Use a custom service to create your connection (with '@').
      # Use the Bdf\QueueBundle\ConnectionFactory\ConnectionDriverFactory::createDriver() by default.
      connection_factory: ~
      
  destinations:
    bus:
      # A URL with destination information; Format: [queue|queues|topic]://{connection}/{queue}
      url: 'queue://gearman/bus'
      
      consumer:
        # Set unique handler as outlet receiver
        handler: 'var_dump'
        
        # Retry failed jobs (i.e. throwing an exception)
        #retry: 0
        
        # Limit the number of received message. When the limit is reached, the consumer is stopped
        #max: 2
        
        # Limit the received message rate
        #limit: 100
        
        # Limit the total memory usage of the current runtime in bytes. When the limit is reached, the consumer is stopped
        #memory: 128
        
        # Store the failed messages
        #save: true
        
        # Catch all exceptions to ensure that the consumer will no crash (and will silently fail)
        #no_failure: true
        
        # Stops consumption when the destination is empty (i.e. no messages are received during the waiting duration)
        #stop_when_empty: true
        
        # Set auto discover as outlet receiver. The message should contain target hint.
        #auto_handle: true
        
        # Define your own middleware. They should be added in the receiver factory.
        # See the Bdf\Queue\Consumer\Receiver\Builder\ReceiverFactory::addFactory()
        #middlewares:
        #  - 'bench'
```
