<?php defined('SYSPATH') or die('No direct script access.');

class Helper_FormItem
{

    /**
     * Vrati nazev pivotni tabulky spocteny z nazvu dvou tabulek ktere se k ni vazou.
     * @static
     * @param $tableA
     * @param $tableB
     * @return string pivot table name
     */
    public static function NNTableName($tableA, $tableB)
    {
        // Nazvy dame do pole a seradime podle abecedy
        $tables = Array($tableA, $tableB);
        sort($tables);
        // Vratime vysledny nazev pivotni tabulky
        return $tables[0].'_'.$tables[1].'map';
    }
     
    /**
     * Dekoduje retezec s NN hodnotou a vraci pole hodnot
     * @param <string> $value = N z N hodnot zakodovanych v jednom retezci
     * @return <array> pole dekodovanych hodnot
     */
    public static function NNDecode($value) 
    {
        return empty($value) ? array() : explode("|", trim($value, '|'));
    }
    
    /**
     * Inverzni funkce k NNDecode
     * @param <array> $values pole hodnot pro zakodovani 
     * @return <string> hodnoty zakodovane do jednoho retezce
     */
    public static function NNEncode(array $values) 
    {
        return empty($values) ? '' : '|'.implode('|', $values).'|';
    }

    
    
    /**
     * Vraci label pro prvek na zaklade nazvu jeho modelu a 
     * nazvu jeho atributu
     * @param type $model_name
     * @param type $attr_name 
     */
    public static function getLabel($model_name, $attr_name)
    {
        return __("$model_name.form_{$attr_name}_label");
    }
}

?>
