<?php

return [
    'manager' => \Toolborg\ChatField\Support\ChatThreadManager::class,

    'models' => [
        'thread' => \Toolborg\ChatField\Models\ChatThread::class,
        'message' => \Toolborg\ChatField\Models\ChatMessage::class,
    ],

    'author_name_column' => 'name',

    'timezone' => null,

    'messages_per_page' => 10,

    'uploads' => [
        'disk' => 'public',
        'directory' => 'chat-field-attachments',
        'visibility' => 'public',
        's3' => [
            'directory' => 'chat-field-attachments',
            'visibility' => 'private',
        ],
        'mime_types' => [
            'audio/m4a',
            'audio/wav',
            'audio/mpeg',
            'audio/ogg',
            'audio/aac',
            'audio/flac',
            'audio/midi',
            'image/png',
            'image/jpeg',
            'image/jpg',
            'image/gif',
            'image/webp',
            'video/mp4',
            'video/avi',
            'video/quicktime',
            'video/webm',
            'video/x-matroska',
            'video/x-flv',
            'video/mpeg',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/csv',
            'text/plain',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ],
        'max_file_size' => 12288,
        'min_file_size' => 1,
        'max_files' => 10,
        'min_files' => 0,
    ],

];
