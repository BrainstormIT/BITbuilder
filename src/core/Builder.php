<?php

namespace BITbuilder\core;

use BITbuilder\helpers\Arr;

final class Builder {
    /**
     * @var Object
     * PDO Database Object
     */
    private $db;

    /**
     * @var String
     * Query that's going to be build
     */
    private $q;

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
     * @var int
     * Last inserted id
     */
    private $lastInsertId;

    /**
     * @var array
     * This variable will contain the error message if
     * something goes wrong
     */
    public $error;

    public function __construct($db) {
        if (!$db instanceof \PDO) {
            $this->setError('q', 'Invalid PDO Database object provided');
            die();
        }

        $this->db = $db;
        $this->q = new Query();
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
            $this->q->apd('SELECT ' . $columns . ' FROM ' . $this->table_name);
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

            $this->q->apd('SELECT ' . $fields . ' FROM ' . $this->table_name);
        }

        return $this;
    }

    /**
     * @param string $columns
     * @return $this
     *
     * Returns a select distinct statement:
     * $select = $qb->table('users')
     *       ->selectDistinct('first_name')
     *       ->getAll();
     *
     * You can also pass in an array with the fields you want to select:
     *
     * $fields = ['first_name', 'last_name'];
     * $this->qb->table('users')
     *        ->selectDistinct($fields)
     *        ->getAll();
     */
    public function selectDistinct($columns = '*') {
        if (!is_array($columns)) {
            $this->q->apd('SELECT DISTINCT ' . $columns . ' FROM ' . $this->table_name);
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

            $this->q->apd('SELECT DISTINCT ' . $fields . ' FROM ' . $this->table_name);
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
            $bind_column = str_replace(".", "", $column);
            $this->q->apd(' WHERE ' . $column . ' ' . $operator . ' :' . $bind_column);

            // Add the value/key to the bind_values/bind_keys array
            $this->bind_values[] = $value;
            $this->bind_keys[] = ':' . $bind_column;
        } else {
            $bind_column = str_replace(".", "", $column);
            $this->q->apd(' WHERE ' . $column . ' ' . $value . ' :' . $bind_column);

            // Add the value/key to the bind_values/bind_keys array
            $this->bind_values[] = $operator;
            $this->bind_keys[] = ':' . $bind_column;
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
            $bind_column = str_replace(".", "", $column);
            $this->q->apd(' OR ' . $column . $operator . ':' . $column);

            // Add the value/key to the bind_values/bind_keys array
            $this->bind_values[] = $value;
            $this->bind_keys[] = ':' . $bind_column;
        } else {
            $bind_column = str_replace(".", "", $column);
            $this->q->apd(' OR ' . $column . $value . ':' . $column);

            // Add the value/key to the bind_values/bind_keys array
            $this->bind_values[] = $operator;
            $this->bind_keys[] = ':' . $bind_column;
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
            $bind_column = str_replace(".", "", $column);
            $this->q->apd(' AND ' . $column . $operator . ' :' . $column);

            // Add the value/key to the bind_values/bind_keys array
            $this->bind_values[] = $value;
            $this->bind_keys[] = ':' . $bind_column;
        } else {
            $bind_column = str_replace(".", "", $column);
            $this->q->apd(' AND ' . $column . $value . ' :' . $column);

            // Add the value/key to the bind_values/bind_keys array
            $this->bind_values[] = $operator;
            $this->bind_keys[] = ':' . $bind_column;
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
            $this->q->apd(' ORDER BY ' . $sort . ' ' .$order);
        } else {
            $this->setError('q', 'No valid order');
            return false;
        }

        return $this;
    }

    /**
     * @param String $by
     * @return $this
     *
     * Groups record by for example a table name:
     * $qb->table('users')->select('count(*) AS count')
     *          ->groupBy('first_name')
     *          ->getAll();
     */
    public function groupBy($by) {
        $this->q->apd(' GROUP BY '  . $by);
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
            $this->setError('q', 'No valid Limit');
            return false;
        }

        $this->q->apd(' LIMIT ' . $limit);
        return $this;
    }

    /**
     * @param $table
     * @param null $column
     * @param null $operator
     * @param null $value
     * @return $this
     *
     * Returns a INNER JOIN statement:
     *
     * $select = $qb->table('vacancies v')
     *      ->select(['v.title', 'v.description'])
     *      ->join('candidates c', 'v.cid', 'c.id')
     *      ->getAll();
     */
    public function join($table, $column = null, $operator = null, $value = null) {
        $this->q->apd(' INNER JOIN ' . $table);

        if (!empty($column) || !empty($operator) || !empty($value)) {
            if (in_array($operator, $this->operators)) {
                $this->q->apd(' ON ' . $column . $operator . ' ' .  $value);
            } else {
                $value = '=';
                $this->q->apd(' ON ' . $column . ' ' . $value . ' ' . $operator);
            }

            return $this;
        } else {
            return $this;
        }
    }

    /**
     * @param $table
     * @param null $column
     * @param null $operator
     * @param null $value
     * @return $this
     *
     * Returns a LEFT JOIN statement:
     *
     * $select = $qb->table('vacancies v')
     *      ->select(['v.title', 'v.description'])
     *      ->leftJoin('candidates c', 'v.cid', 'c.id')
     *      ->getAll();
     */
    public function leftJoin($table, $column = null, $operator = null, $value = null) {
        $this->q->apd(' LEFT JOIN ' . $table);

        if (!empty($column) || !empty($operator) || !empty($value)) {
            if (in_array($operator, $this->operators)) {
                $this->q->apd(' ON ' . $column . $operator . ' ' .  $value);
            } else {
                $value = '=';
                $this->q->apd(' ON ' . $column . ' ' . $value . ' ' . $operator);
            }

            return $this;
        } else {
            return $this;
        }
    }

    /**
     * @param $table
     * @param null $column
     * @param null $operator
     * @param null $value
     * @return $this
     *
     * Returns a RIGHT JOIN statement:
     *
     * $select = $qb->table('vacancies v')
     *      ->select(['v.title', 'v.description'])
     *      ->rightJoin('candidates c', 'v.cid', 'c.id')
     *      ->getAll();
     */
    public function rightJoin($table, $column = null, $operator = null, $value = null) {
        $this->q->apd(' RIGHT JOIN ' . $table);

        if (!empty($column) || !empty($operator) || !empty($value)) {
            if (in_array($operator, $this->operators)) {
                $this->q->apd(' ON ' . $column . $operator . ' ' .  $value);
            } else {
                $value = '=';
                $this->q->apd(' ON ' . $column . ' ' . $value . ' ' . $operator);
            }

            return $this;
        } else {
            return $this;
        }
    }

    /**
     * @param $table
     * @param null $column
     * @param null $operator
     * @param null $value
     * @return $this
     *
     * Returns a FULL OUTER JOIN statement:
     *
     * $select = $qb->table('vacancies v')
     *      ->select(['v.title', 'v.description'])
     *      ->outerJoin('candidates c', 'v.cid', 'c.id')
     *      ->getAll();
     */
    public function outerJoin($table, $column = null, $operator = null, $value = null) {
        $this->q->apd(' FULL OUTER JOIN ' . $table);

        if (!empty($column) || !empty($operator) || !empty($value)) {
            if (in_array($operator, $this->operators)) {
                $this->q->apd(' ON ' . $column . $operator . ' ' .  $value);
            } else {
                $value = '=';
                $this->q->apd(' ON ' . $column . ' ' . $value . ' ' . $operator);
            }

            return $this;
        } else {
            return $this;
        }
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
            $this->q->apd('INSERT INTO ' . $this->table_name . '(');

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
            $this->q->apd($fields . $values);

            try {
                $query = $this->db->prepare($this->q->get());
                $query->execute($insert);
                $this->clear();

                // Set last inserted id
                $this->lastInsertId = $this->db->lastInsertId();

                return true;
            } catch (\PDOException $e) {
                $this->setError('d', $e->getMessage());
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
        $this->q->apd('DELETE FROM ' . $this->table_name);

        if (!empty($column) || !empty($operator) || !empty($value)) {
            if (in_array($operator, $this->operators)) {
                $this->q->apd(' WHERE ' . $column . $operator . ' :' . $column);

                // Add the value to the bind_values array
                $this->bind_values[] = $value;
                $this->bind_keys[] = ':' . $column;
            } else {
                $value = '=';
                $this->q->apd(' WHERE ' . $column . $value . ' :' . $column);

                // Add the value/key to the bind_values/bind_keys array
                $this->bind_values[] = $operator;
                $this->bind_keys[] = ':' . $column;
            }

            try {
                $query = $this->db->prepare($this->q->get());
                $this->bindValues($query);
                $query->execute();
                $this->clear();

                return true;
            } catch (\PDOException $e) {
                $this->setError('d', $e->getMessage());
                return false;
            }
        } else {
            return $this;
        }
    }

    /**
     * @param $fields
     * @param null $column
     * @param null $operator
     * @param null $value
     * @return $this|bool
     *
     * Updates record:
     *
     * $this-qb->table('users')->update(['first_name' => 'John'], 'id', 79);
     *
     * You can also manually add a where clause
     * by calling the 'where' method:
     *
     * $this-qb->table('users')->update(['first_name' => 'John', 'last_name' => 'Doe'])
     *      ->where('id', 79)
     *      ->exec();
     */
    public function update($fields, $column = null, $operator = null, $value = null) {
        if (is_array($fields) && Arr::is_assoc($fields)) {
            // Get array keys
            $arr_keys = array_keys($fields);

            // Get array values
            $arr_values = array_values($fields);

            // Build up the prepared update array
            $update = [];

            // Build up UPDATE statement
            $this->q->apd(' UPDATE ' . $this->table_name . ' SET ');

            for ($ii = 0; $ii < count($arr_keys); $ii++) {
                if ($ii != count($arr_keys) -1) {
                    $this->q->apd($arr_keys[$ii] . ' = :' .$arr_keys[$ii]. ', ');
                    $update[':' . $arr_keys[$ii]] = $arr_values[$ii];
                } else {
                    $this->q->apd($arr_keys[$ii] . ' = :' .$arr_keys[$ii]);
                    $update[':' . $arr_keys[$ii]] = $arr_values[$ii];
                }
            }

            if (!empty($column) || !empty($operator) || !empty($value)) {
                if (in_array($operator, $this->operators)) {
                    $this->q->apd(' WHERE ' . $column . $operator . ' :' . $column);
                    $update[':' . $column] = $value;
                } else {
                    $value = '=';
                    $this->q->apd(' WHERE ' . $column . $value . ' :' . $column);
                    $update[':' . $column] = $operator;
                }

                try {
                    $query = $this->db->prepare($this->q->get());
                    $query->execute($update);
                    $this->clear();

                    return true;
                } catch (\PDOException $e) {
                    $this->setError('d', $e->getMessage());
                    return false;
                }
            } else {
                foreach($update as $key => $value) {
                    // Add the value/key to the bind_values/bind_keys array
                    $this->bind_values[] = $value;
                    $this->bind_keys[] = $key;
                }

                return $this;
            }
        } else {
            $this->setError('q', 'Wrong array format or no array provided');
            return false;
        }
    }

    /**
     * @return array
     *
     * Returns assoc array of all records found:
     * $this->qb->table('users')->select('*')->getAll();
     */
    public function fetchAll() {
        try {
            $query = $this->db->prepare($this->q->get());
            $this->bindValues($query);
            $query->execute();
            $this->clear();

            return $query->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->setError('d', $e->getMessage());
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
    public function fetch() {
        try {
            $query = $this->db->prepare($this->q->get());
            $this->bindValues($query);
            $query->execute();
            $this->clear();

            return $query->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->setError('d', $e->getMessage());
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
            $query = $this->db->prepare($this->q->get());
            $this->bindValues($query);
            $query->execute();
            $this->clear();

            return $query->rowCount();
        } catch(\PDOException $e) {
            $this->setError('d', $e->getMessage());
            return false;
        }
    }

    /**
     * @return bool
     *
     * Executes $this->q
     */
    public function exec() {
        try {
            $query = $this->db->prepare($this->q->get());
            $this->bindValues($query);
            $query->execute();

            // Set the last inserted id but only
            // of $this->db->lastInsertId() is not empty
            if (!empty($this->db->lastInsertId())) {
                $this->lastInsertId = $this->db->lastInsertId();
            }

            $this->clear();

            return true;
        } catch (\PDOException $e) {
            $this->setError('d', $e->getMessage());
            return false;
        }
    }

    /**
     * @param $query
     * @return $this;
     *
     * Simply build up the query
     * however the user wants it to be
     */
    public function raw($query) {
        $this->q->apd($query);
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
    public function bindValues($query) {
        if (!empty($this->bind_values) && !empty($this->bind_keys)) {
            if (!$query instanceof \PDOStatement) {
                $this->setError('q', 'No valid PDO Statement provided');
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
     * Empties $this->q,
     * $this->bind_values and $this->bind_keys.
     * This needs to been done after executing
     * get, getAll, count, exec, insert etc.
     */
    public function clear() {
        $this->q->emptyQuery();
        $this->bind_values = [];
        $this->bind_keys = [];
    }

    /**
     * @param $type
     * @param $message
     *
     * Sets $this->error with a specific
     * Error type (QueryBuilder or Database)
     */
    public function setError($type, $message) {
        switch ($type) {
            case 'q':
                $this->error = array('QueryBuilderError' => $message);
                break;
            case 'd':
                $this->error = array('DatabaseError' => $message);
                break;
            default:
                $this->error = array('Error' => $message);
        }
    }

    /**
     * @return mixed
     */
    public function getError() {
        return $this->error;
    }

    /**
     * @return int
     */
    public function getLastInsertId() {
        return $this->lastInsertId;
    }
}