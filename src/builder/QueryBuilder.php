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

    /**
     * @var array
     * Datatypes that should not get quoted
     */
    private  $dont_quote = ['boolean', 'integer', 'double',
                            'array', 'object', 'resource', 'NULL'];

    public function __construct(\PDO $db) {
        $this->db = $db;
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
            $this->query .= ' WHERE ' . $column . $operator . $this->quote($value);
        } else {
            // The operator becomes the value
            // and the value becomes the operator
            // TODO: Is this clear enough?
            $this->query .= ' WHERE ' . $column . $value . $this->quote($operator);
        }

        return $this;
    }

    /**
     * @param $column
     * @param $operator
     * @param $value
     * @return $this
     *
     * Adds OR statement to WHERE clause:
     *
     * $select = $qb->table('users')
     *              ->select(['first_name', 'last_name'])
     *              ->where('id', '>', 1)
     *              ->or_('first', '!=', 'John')
     *              ->getAll();
     */
    public function or_ ($column, $operator, $value) {
        if (in_array($operator, $this->operators)) {
            $this->query .= ' OR ' . $column . $operator . $this->quote($value);
        } else {
            // The operator becomes the value
            // and the value becomes the operator
            $this->query .= ' OR ' . $column . $value . $this->quote($operator);
        }

        return $this;
    }

    /**
     * @param $column
     * @param $operator
     * @param $value
     * @return $this
     *
     * Adds AND statement to WHERE clause:
     *
     * $select = $qb->table('users')
     *              ->select(['first_name', 'last_name'])
     *              ->where('id', '>', 1)
     *              ->and_('first', '!=', 'John')
     *              ->getAll();
     */
    public function and_($column, $operator, $value) {
        if (in_array($operator, $this->operators)) {
            $this->query .= ' AND ' . $column . $operator . $this->quote($value);
        } else {
            // The operator becomes the value
            // and the value becomes the operator
            $this->query .= ' AND ' . $column . $value . $this->quote($operator);
        }

        return $this;
    }

    /**
     * @param $sort
     * @param string $order
     * @return $this
     *
     * Orders found record (ASC or DESC):
     * $qb->table('users')->select('*')->orderBy('id')->getAll();
     */
    public function orderBy($sort, $order = 'ASC') {
        if ($order == 'ASC' || $order == 'DESC') {
            $this->query .= ' ORDER BY ' . $sort . ' ' .$order;
        } else {
            echo $order . ' is not valid';
            return false;
        }

        return $this;
    }

    /**
     * @param $limit
     * @return $this
     *
     * Limits amount of rows:
     * $qb->table('users')->select('*')->limit(20)->getAll();
     */
    public function limit($limit) {
        if (!is_int($limit)) {
            var_dump('No valid limit');
            return false;
        }

        $this->query .= ' LIMIT ' . $limit;
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
     * TODO: Secure against SQL Injection
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
                    $values .= $this->quote($arr_values[$ii]) . ')';
                } else {
                    $fields .= $arr_keys[$ii] . ') VALUES (';

                    // If $arr_values[$ii] is an integer don't quote it
                    $values .= $this->quote($arr_values[$ii]) . ')';
                }
            }

            // Finalize query
            $this->query .= $fields . $values;

            try {
                $query = $this->db->prepare($this->query);
                $query->execute();
                $this->clearQuery();

                return true;
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

    /**
     * @param $var
     * @return mixed
     *
     * Don't quote the variable if it's datatype
     * is in the $this->dont_quote array
     */
    public function quote($var) {
        if (in_array(gettype($var), $this->dont_quote)) {
            return $var;
        }

        return $this->db->quote($var);
    }
}


