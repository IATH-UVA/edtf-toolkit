<?php

// Should be set to 0 in production
error_reporting(E_ALL);

// Should be set to '0' in production
ini_set('display_errors', '1');

// Settings
$settings = [
  "db" =>
      [
      'driver'   => 'pgsql',
      'host'     => 'localhost',
      'port'     => '5432',
      'database' => 'tombs_dev',
      'username' => '',
      'password' => '',
      'prefix'   => ''
      // 'schema'   => 'schema2'
    ]
];

// ...

return $settings;