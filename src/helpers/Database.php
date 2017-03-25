<?php

namespace QueryBuilder\Helpers;

class Database extends \PDO {
    /**
     * Database type
     */
    private $db_type;
    /**
     * Database host
     */
    private $db_host;
    /**
     * Database name
     */
    private $db_name;
    /**
     * Database username
     */
    private $db_username;
    /**
     * Database password
     */
    private $db_password;

    public function __construct() {
        $this->db_type = 'mysql';
        $this->db_host = 'localhost';
        $this->db_name = 'qbtest';
        $this->db_username = 'root';
        $this->db_password = '';

        $dsn = "$this->db_type:dbname=$this->db_name;host=$this->db_host";
        parent::__construct($dsn, $this->db_username, $this->db_password);
    }
}