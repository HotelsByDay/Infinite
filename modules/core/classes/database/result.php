<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Rozsireni standardni funkcnost Result objektu o moznost ziskani poctu
 * nalezenych zaznamu - tato metoda souvisi s rozsirenim objektu DB o metodu
 * select_count_records(), ktera nastavi Select dotaz tak aby na specifickem
 * klici byla hodnota COUNT(*) a prave metoda count_records_value v teto
 * tride vraci hodnotu tohoto klice.
 *
 * 
 *
 */
abstract class Database_Result extends Kohana_Database_Result {

    /**
     * Vraci hodnotu na specifickem klici, ktery byl nastaven jako ALIAS
     * pro COUNT(*) metodou DB::select_count_records().
     * @return <int>
     */
    public function count_records_value()
    {
        return $this->rewind()->get('__cr');
    }

}
