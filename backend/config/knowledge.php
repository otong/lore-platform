<?php

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
];
