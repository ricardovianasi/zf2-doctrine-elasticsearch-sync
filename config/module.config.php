<?php

use Zf2DoctrineElasticsearchSync\Factory;

return array(
    'zf2-doctrine-elasticsearch-sync' => [],
    'service_manager'                 => [
        'factories' => [
            'zf2-doctrine-elasticsearch-service' => Factory\Elasticsearch::class
        ]
    ]
);
