<?php

namespace BITbuilder\helpers;

class Str {
    /**
     * @param $haystack
     * @param $needle
     * @param $checkAll
     * @return boolean
     *
     * Returns true if the given string
     * contains the given substring
     */
    public static function contains($haystack, $needle, $checkAll = false) {
        $contains = false;

        if (is_string($needle)) {
            if (strstr($haystack, $needle)) {
                $contains =  true;
            } else {
                return $contains;
            }
        } else if (is_array($needle) && !$checkAll) {
            foreach ($needle as $check) {
                if (strstr($haystack, $check)) {
                    $contains = true;
                }
            }
        } else if (is_array($needle) && $checkAll) {
            $checked_needles = [];
            foreach ($needle as $check) {
                if (strstr($haystack, $check)) {
                    $checked_needles[] = $check;
                }
            }
            if (count($checked_needles) == count($needle)) {
                $contains = true;
            }
        }

        return $contains;
    }
}