<?php

use Zf2DoctrineElasticsearchSync\Service;
use Zf2DoctrineElasticsearchSync\Option;
use Zf2DoctrineElasticsearchSync\Listener;

return array(
    'zf2-doctrine-elasticsearch-sync' => [],
    'service_manager'                 => [
        'factories' => [
            'zf2-doctrine-elasticsearch-service' => Service\ElasticsearchFactory::class,
            Option\Sync::class                   => Option\SyncFactory::class,
            Listener\Sync::class                 => Listener\SyncFactory::class
        ]
    ]
);
