<?php

namespace BITbuilder\core;

class Query {
    /**
     * @var string
     */
    private $query;

    public function __construct() {
        $this->query = '';
    }
    
    /**
     * @param $append
     *
     * Appends a string to $this->query;
     * Usage in builder:
     *
     * public $q = new Query();
     * $this->q->apd([STRING]);
     */
    public function apd($append) {
        $query = $this->get();
        $this->set($query . $append);
    }

    /**
     * @return string
     */
    public function get() {
        return $this->query;
    }

    /**
     * @param $query
     */
    public function set($query) {
        $this->query = $query;
    }

    /**
     * Empties $this->query
     */
    public function emptyQuery() {
        $this->set('');
    }
}