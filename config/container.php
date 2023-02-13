<?php
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;

return [
  'settings' => function () {
      return require __DIR__ . '/settings.php';
  },

  DatabaseInterface::class => function (ContainerInterface $c) {
    $settings = $c->get('settings');

    $connStr = "host=localhost port=5432 dbname=tombs_dev";

    $dbconn = pg_connect($connStr);

    return $dbconn;
},

  App::class => function (ContainerInterface $container) {
      AppFactory::setContainer($container);

      return AppFactory::create();
  },
];