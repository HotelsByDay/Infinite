<?php defined('SYSPATH') or die('No direct script access.');

class Arr extends Kohana_Arr {

    /**
     * Hleda ve zdrojovem poli dany klic. Pokud nalezne tak vraci jeho hodnotu,
     * jinak hodnotu tretiho argumentu.
     * @param <arrray> $arr
     * @param <string> $key
     * @param <mixed> $default
     * @return <mixed> Pokud je v poli $arr definovan klic $key, tak vrati jeho
     * hodnotu. Jinak vraci $default. Pokud je $key roven NULL, tak vraci $default.
     */
    static public function getifset($arr, $key, $default = NULL)
    {
        if ($key === NULL || !isset($arr[$key]) ) {
            return $default;
        }
        return $arr[$key];
    }

    static public function sort_by_key(array $array, $key, $asc = TRUE)
    {
        $result = array();

        $values = array();

        foreach ($array as $id => $value)
        {
            $values[$id] = isset($value[$key]) ? $value[$key] : '';
        }

        if ($asc)
        {
            asort($values);
        }
        else
        {
            arsort($values);
        }

        foreach ($values as $key => $value)
        {
            $result[$key] = $array[$key];
        }

        return $result;
    }

}