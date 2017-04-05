<?php

namespace BITbuilder\helpers;

class Arr {
    /**
     * @param $array
     * @return bool
     *
     * Returns true if given array is assoc
     */
    public static function is_assoc($array) {
        $is_assoc = false;

        if (array() === $array) {
            return $is_assoc;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }
}