<?php

return [
    'settings' => [
        'displayErrorDetails' => true, // Set to false in production
        'db' => [
            'driver' => 'mysql',
            'host' => getenv('DB_HOST'),
            'database' => getenv('DB_DATABASE'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ],
        'jwt' => [
            'secret' => $_ENV['JWT_SECRET']
        ]
    ],
];
