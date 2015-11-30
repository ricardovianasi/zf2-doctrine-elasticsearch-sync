# ZF2 Doctrine Elasticsearch Sync

## Introduction
This module is intended for usage with a default directory structure of a [ZendSkeletonApplication](https://github.com/zendframework/ZendSkeletonApplication/). It provides functionality to synchronize doctrine entities with elasticsearch indexes.

In a nutshell, this module allows you to configure doctrine entities to map elasticsearch indexes with automatic synchronization working out of the box.

## Installation

### Composer
```
./composer.phar require fabiankoestring/zf2-doctrine-elasticsearch-sync
```

## Usage

### Sample module config:

```php
<?php
use Filemanager\Entity\File;

return [
    'zf2-doctrine-elasticsearch-sync' => [
        File::class   => [
            'index'   => 'my_index',
            'type'    => 'my_type',
            'mapping' => [
                'id'       => 'id',
                'label'    => 'label',
                'mimetype' => 'mimetype',
                'status'   => 'status'
            ]
        ]
    ]
];
```

## Questions / support
- [Issue Tracker](https://github.com/FabianKoestring/zf2-doctrine-elasticsearch-sync/issues)
