<?php
return [
    'settings' => [
        'displayErrorDetails' => true   , // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        'apiToken' => '28e336ac6c9423d946ba02d19c6a2632',
        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
        
        //Database
        'db' => [
            'driver' => 'sqlite',
            'database' => __DIR__.'/../db/rss-reader.db',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => ''
        ]
    ],
];
