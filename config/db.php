<?php

  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();

  class db {

    public function connect() {
      $connection_string = "host={$_ENV['DB_HOST']} dbname={$_ENV['DB_NAME']}";
      if (!empty($_ENV['DB_USER']) && !empty($_ENV['DB_PASS'])) {
        $connection_string .= " user={$_ENV['DB_USER']} password={$_ENV['DB_PASS']}";
      } 
      $dbconn = pg_connect($connection_string);
      return $dbconn;
    }
  }
?>