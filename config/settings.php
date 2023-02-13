<?php

// Should be set to 0 in production
error_reporting(E_ALL);

// Should be set to '0' in production
ini_set('display_errors', '1');

// Settings
$settings = [
  // "db" =>
  //     [
  //     'driver'   => 'pgsql',
  //     'host'     => 'localhost',
  //     'port'     => '5433',
  //     'database' => 'localhost',
  //     'username' => '',
  //     'password' => '',
  //     'prefix'   => '',
  //     'schema'   => 'schema2','public'
  //   ]
];

// ...

return $settings;