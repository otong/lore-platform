<?php

use App\Modules\Knowledge\Infrastructure\Search\Engines\DatabaseSearchEngine;
use App\Modules\Knowledge\Infrastructure\Search\Engines\ElasticSearchEngine;
use App\Modules\Knowledge\Infrastructure\Search\Engines\MeilisearchEngine;
use App\Modules\Knowledge\Infrastructure\Search\Engines\OpenSearchEngine;
use App\Modules\Knowledge\Infrastructure\Search\Engines\VectorSearchEngine;

return [
    'attachments' => [
        'default_disk' => env('KNOWLEDGE_ATTACHMENT_DISK', 'local'),
        'max_file_size_kb' => env('KNOWLEDGE_ATTACHMENT_MAX_SIZE_KB', 20480), // 20 MB
        'allowed_mimes' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/png',
            'image/jpeg',
            'application/zip',
            'text/plain',
        ],
        'signed_url_ttl_minutes' => [5, 30, 60],
        'physical_delete_mode' => env('KNOWLEDGE_ATTACHMENT_DELETE_MODE', 'immediate'), // immediate | scheduled
    ],

    'antivirus' => [
        'enabled' => env('KNOWLEDGE_ANTIVIRUS_ENABLED', false),
        'driver' => env('KNOWLEDGE_ANTIVIRUS_DRIVER', 'null'),
    ],

    'search' => [
        'default_driver' => env('KNOWLEDGE_SEARCH_DRIVER', 'database'),
        'drivers' => [
            'database' => DatabaseSearchEngine::class,
            'meilisearch' => MeilisearchEngine::class,
            'elasticsearch' => ElasticSearchEngine::class,
            'opensearch' => OpenSearchEngine::class,
            'vector' => VectorSearchEngine::class,
        ],
    ],
];
