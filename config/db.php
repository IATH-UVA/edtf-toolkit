<?php
  class db {
    private $dbHost = 'localhost';
    private $dbName = 'tombs_dev';
    // private $dbUser = $dbSettings['username'];
    // private $dbPass = $dbSettings['password'];

    public function connect() {
      $dbconn = pg_connect("host=$this->dbHost dbname=$this->dbName");
      return $dbconn;
    }
  }
?>