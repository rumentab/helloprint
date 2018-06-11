<?php

/**
 * Define config variables for the producer
 */

$config = [
    'auth_key' => [
        'producer'  => '=kbF%m?CH4ZUR$nPzZPJpC8u'
    ],
    'db' => [
        'username' => 'helloprint',
        'password' => 'helloprint',
        'db_name' => 'helloprint',
        'db_port' => '4201'
    ],
    'smtp' => [
        'host' => 'smtp.example.com',                 // i.e. smtp.gmail.com
        'SMTPAuth' => true,                         // true or false
        'username' => 'example@example.com',
        'password' => 'password',
        'SMTPSecure' => 'ssl',                      // ssl or tls
        'port' => 465                               // 25 / 465 / 587
    ],
    'urls' => [
        'webserver' => "ip.of.webserver:4000",      // see v1/Vagrantfile
    ]
];

