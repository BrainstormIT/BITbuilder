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
     * Values that need to be passed in the
     * bindValue method
     */
    private $bind_values = [];

    /**
     * @var array
     * Keys that need to be passed in the
     * bindValue method
     */
    private $bind_keys = [];

    /**
     * @var array
     * Datatypes that should not get quoted
     */
    private  $dont_quote = ['boolean', 'integer', 'double',
                            'array', 'object', 'resource', 'NULL'];

    public function __construct(\PDO $db) {
        if (!$db instanceof \PDO) {
            var_dump('Invalid PDO Database object provided');
            die();
        }

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
            $this->query .= ' WHERE ' . $column . $operator . ' :' . $column;

            // Add the value/key to the bind_values/bind_keys array
            $this->bind_values[] = $value;
            $this->bind_keys[] = ':' . $column;
        } else {
            // The operator becomes the value
            // and the value becomes the operator
            $this->query .= ' WHERE ' . $column . $value . ' :' . $column;

            // Add the value/key to the bind_values/bind_keys array
            $this->bind_values[] = $operator;
            $this->bind_keys[] = ':' . $column;
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
            $this->query .= ' OR ' . $column . $operator . ':' . $column;

            // Add the value/key to the bind_values/bind_keys array
            $this->bind_values[] = $value;
            $this->bind_keys[] = ':' . $column;
        } else {
            // The operator becomes the value
            // and the value becomes the operator
            $this->query .= ' OR ' . $column . $value . ':' . $column;

            // Add the value/key to the bind_values/bind_keys array
            $this->bind_values[] = $operator;
            $this->bind_keys[] = ':' . $column;
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
            $this->query .= ' AND ' . $column . $operator . ' :' . $column;

            // Add the value/key to the bind_values/bind_keys array
            $this->bind_values[] = $value;
            $this->bind_keys[] = ':' . $column;
        } else {
            // The operator becomes the value
            // and the value becomes the operator
            $this->query .= ' AND ' . $column . $value . ' :' . $column;

            // Add the value/key to the bind_values/bind_keys array
            $this->bind_values[] = $operator;
            $this->bind_keys[] = ':' . $column;
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
     * $this->table('users')->insert($user);
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
            $insert  = [];

            // Build up the insert query
            $this->query .= 'INSERT INTO ' . $this->table_name . '(';

            for ($ii = 0; $ii < count($arr_keys); $ii++) {
                if ($ii != count($arr_keys) -1) {
                    $fields .= $arr_keys[$ii] . ', ';
                    $values .= ':' . $arr_keys[$ii] . ', ';

                    // Build up the prepared insert array
                    $insert[':' . $arr_keys[$ii]] = $arr_values[$ii];
                } else {
                    $fields .= $arr_keys[$ii] . ') VALUES (';
                    $values .= ':' . $arr_keys[$ii] . ')';

                    // Build up the prepared insert array
                    $insert[':' . $arr_keys[$ii]] = $arr_values[$ii];
                }
            }

            // Finalize query
            $this->query .= $fields . $values;

            try {
                $query = $this->db->prepare($this->query);
                $query->execute($insert);
                $this->clear();

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
     * @param null $column
     * @param null $operator
     * @param null $value
     * @return $this|bool
     *
     * Deletes row from record:
     *
     * $this->qb->table('users')->delete('id', 67);
     *
     * You can also manually add a where clause
     * by calling the 'where' method:
     *
     * $this->table('users')->delete()->where('id', 67)->exec();
     */
    public function delete($column = null, $operator = null, $value = null) {
        $this->query .= 'DELETE FROM ' . $this->table_name;

        if (!empty($column) || !empty($operator) || !empty($value)) {
            if (in_array($operator, $this->operators)) {
                $this->query .= ' WHERE ' . $column . $operator . ' :' . $column;

                // Add the value to the bind_values array
                $this->bind_values[] = $value;
                $this->bind_keys[] = ':' . $column;
            } else {
                $value = '=';
                $this->query .= ' WHERE ' . $column . $value . ' :' . $column;

                // Add the value/key to the bind_values/bind_keys array
                $this->bind_values[] = $operator;
                $this->bind_keys[] = ':' . $column;
            }

            try {
                $query = $this->db->prepare($this->query);
                $this->bindValues($query);
                $query->execute();
                $this->clear();

                return true;
            } catch (\PDOException $e) {
                var_dump($e->getMessage());
                return false;
            }
        } else {
            return $this;
        }
    }

    /**
     * @return array
     *
     * Returns assoc array of all records found:
     * $this->qb->table('users')->select('*')->getAll();
     */
    public function getAll() {
        try {
            $query = $this->db->prepare($this->query);
            $this->bindValues($query);
            $query->execute();
            $this->clear();

            return $query->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            var_dump($e->getMessage());
            return false;
        }
    }

    /**
     * @return array
     *
     * Returns assoc array of the first record found:
     * $this->qb->table('users')->select('first_name')
     *      ->where('id', 33)->get();
     */
    public function get() {
        try {
            $query = $this->db->prepare($this->query);
            $this->bindValues($query);
            $query->execute();
            $this->clear();

            return $query->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            var_dump($e->getMessage());
            return false;
        }
    }

    /**
     * @return int
     *
     * Returns count of records found:
     * $this->qb->table('users')->select('*')->count();
     */
    public function countRows() {
        try {
            $query = $this->db->prepare($this->query);
            $this->bindValues($query);
            $query->execute();
            $this->clear();

            return $query->rowCount();
        } catch(\PDOException $e) {
            var_dump($e->getMessage());
            return false;
        }
    }

    /**
     * Executes $this->query
     */
    public function exec() {
        try {
            $query = $this->db->prepare($this->query);
            $this->bindValues($query);
            $query->execute();
            $this->clear();

            return true;
        } catch (\PDOException $e) {
            var_dump($e->getMessage());
            return false;
        }
    }

    /**
     * Simply build up the query
     * however the user wants it to be
     */
    public function raw($query) {
        $this->query .= $query;
        return $this;
    }


    /**
     * @param $var
     * @return int|string
     *
     * Returns PDO param based on the
     * data type of the given variable
     */
    public function get_PDO_param($var) {
        switch($var) {
            case is_int($var):
                $param_datatype = \PDO::PARAM_INT;
                break;
            case is_string($var):
                $param_datatype = \PDO::PARAM_STR;
                break;
            default:
                $param_datatype = \PDO::PARAM_INT;
        }

        return $param_datatype;
    }

    /**
     * @param \PDOStatement $query
     *
     * Loop over the bind_values array and bind the
     * actual values to the PDO query
     */
    public function bindValues(\PDOStatement $query) {
        if (!empty($this->bind_values) && !empty($this->bind_keys)) {
            if (!$query instanceof \PDOStatement) {
                var_dump('No valid query provided');
            }

            for ($ii = 0; $ii < count($this->bind_values); $ii++) {
                $param = $this->get_PDO_param($this->bind_values[$ii]);
                $value = $this->bind_values[$ii];
                $key = $this->bind_keys[$ii];

                $query->bindValue($key, $value, $param);
            }
        }
    }

    /**
     * Empties $this->query,
     * $this->bind_values and $this->bind_keys.
     * This needs to been done after executing
     * get, getAll, count, exec, insert etc.
     */
    public function clear() {
        $this->query = '';
        $this->bind_values = [];
        $this->bind_keys = [];
    }

    /**
     * Prints $this->query
     */
    public function logQuery() {
        echo $this->query;
    }

    /**
     * @param $var
     * @param $exception
     * @return mixed
     *
     * Don't quote the variable if it's datatype
     * is in the $this->dont_quote array
     *
     * TODO: Is this still necessary?
     */
    public function quote($var, $exception = false) {
        if (!$exception) {
            if (in_array(gettype($var), $this->dont_quote)) {
                return $var;
            }
        }

        return $this->db->quote($var);
    }
}


