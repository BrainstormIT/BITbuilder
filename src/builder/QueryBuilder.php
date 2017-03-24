<?php

namespace QueryBuilder\Builder;

use QueryBuilder\Helpers\Arr;

class QueryBuilder {
    /**
     * @var Object
     * PDO Database Object
     */
    private $db;

    /**
     * @var String
     * Query that's going to be build
     */
    private $query;

    /**
     * @var String
     * Selected table
     */
    private $table_name;

    /**
     * @var array
     * Operators
     */
    private $operators = ['=', '!=', '<', '>', '>=', '<='];

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * @param $table_name
     * @return $this
     *
     * Selects a table:
     * $this->qb->table('users');
     */
    public function table($table_name) {
        $this->table_name = $table_name;
        return $this;
    }


    /**
     * @param string $columns
     * @return $this
     *
     * Returns a simple select all statement:
     * $this->qb->table('users')->select()->getAll();
     *
     * You can also select a specific column:
     * $this->qb->table('users')->select('name')->getAll();
     *
     * You can also pass in an array with the fields you want to select:
     *
     * $fields = ['first_name', 'last_name'];
     * $this->qb->table('users')->select($fields)->getAll();
     */
    public function select($columns = '*') {
        if (!is_array($columns)) {
            $this->query .= 'SELECT ' . $columns . ' FROM ' . $this->table_name;
        } else {
            $fields = '';

            // Build $fields string by looping over $columns array
            for ($ii = 0; $ii < count($columns); $ii++) {
                // As long as the current item in the loop
                // isn't the last array item add a comma at the end
                if ($ii != count($columns) -1) {
                    $fields .= $columns[$ii]. ', ';
                } else {
                    $fields .= $columns[$ii];
                }
            }

            $this->query .= 'SELECT ' . $fields . ' FROM ' . $this->table_name;
        }

        return $this;
    }

    /**
     * @param $column
     * @param $value
     * @param string $operator
     * @return $this
     *
     * Adds where statement to selector:
     * $this->qb->table('users')->select()
     *      ->where('first_name', 'john')
     *      ->getAll();
     *
     * The operator can also be changed:
     * $this->qb->table('users')->select()
     *      ->where('last_name', '!=', 'smith')
     *      ->getAll();
     */
    public function where($column, $operator, $value = '=') {
        if (in_array($operator, $this->operators)) {
            $this->query .= ' WHERE ' . $column . $operator . $value;
        } else {
            // The operator becomes the value
            // and the value becomes the operator
            // TODO: Is this clear enough?
            $this->query .= ' WHERE ' . $column . $value . $operator;
        }

        return $this;
    }

    /**
     * @param $by
     * @param string $alphabetical
     * @return $this
     *
     * Returns order by statement:
     */
    public function orderBy($by, $alphabetical = 'ASC') {
        if ($alphabetical == 'ASC' || $alphabetical == 'DESC') {
            $this->query .= ' ORDER BY ' . $by . ' ' .$alphabetical;
        } else {
            echo $alphabetical . ' is not valid';
            return false;
        }

        return $this;
    }

    /**
     * @param $columns
     * @return bool
     *
     * Inserts a record:
     *
     * $user = [
     *     'first_name' => 'John'
     *     'last_name' => 'Doe'
     *     'admin' => 0
     * ]
     *
     * $this->table('users')->insert($record);
     * TODO: Secure against SQL Injection: $this->db->prepare()
     */
    public function insert($columns) {
        // Check if array is associative
        if (Arr::is_assoc($columns)) {
            // Get array keys
            $arr_keys = array_keys($columns);

            // Get array values
            $arr_values = array_values($columns);

            $values = '';
            $fields = '';

            // Build up the insert
            $this->query .= 'INSERT INTO ' . $this->table_name . '(';

            for ($ii = 0; $ii < count($arr_keys); $ii++) {
                if ($ii != count($arr_keys) -1) {
                    $fields .= $arr_keys[$ii] . ', ';

                    // If $arr_values[$ii] is an integer don't quote it
                    $values .= is_int($arr_values[$ii])
                        ? $arr_values[$ii] . ', '
                        : $this->db->quote($arr_values[$ii]) . ', ';
                } else {
                    $fields .= $arr_keys[$ii] . ') VALUES (';

                    // If $arr_values[$ii] is an integer don't quote it
                    $values .= is_int($arr_values[$ii])
                        ? $arr_values[$ii] . ')'
                        : $this->db->quote($arr_values[$ii]) . ')';
                }
            }

            // Finalize query
            $this->query .= $fields . $values;

            try {
                $query = $this->db->prepare($this->query);
                $query->execute();
            } catch (\PDOException $e) {
                var_dump($e->getMessage());
                return false;
            }
        } else {
            echo 'Wrong array format';
            return false;
        }
    }

    /**
     * @return array
     *
     * Returns assoc array of all records found:
     * $this->qb->table('users')->select('*')->getAll();
     */
    public function getAll() {
        $query = $this->db->prepare($this->query);
        $query->execute();

        // clear query
        $this->clearQuery();

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @return array
     *
     * Returns assoc array of the first record found:
     * $this->qb->table('users')->select('first_name')
     *      ->where('id', 33)->get();
     */
    public function get() {
        $query = $this->db->prepare($this->query);
        $query->execute();

        // clear query
        $this->clearQuery();

        return $query->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @return int
     *
     * Returns count of records found:
     * $this->qb->table('users')->select('*')->count();
     */
    public function count() {
        $query = $this->db->prepare($this->query);
        $query->execute();

        // clear query
        $this->clearQuery();

        return $query->rowCount();
    }

    /**
     * Empty's $this->query string,
     * this should be done after every
     * get, getAll, count, exec etc.
     */
    public function clearQuery() {
        $this->query = '';
    }

    /**
     * Prints $this->query
     */
    public function logQuery() {
        echo $this->query;
    }
}

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


