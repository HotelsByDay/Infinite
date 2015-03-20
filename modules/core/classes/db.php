<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Rozsiruje funkcnost tridy DB o moznost vytvorit select, ktery slouzi specificky
 * k ziskani poctu polozek (COUNT(*)).
 *
 * Tuto funkcnost zajistuje metoda select_count_records, ktera standardne vraci
 * Database_Query_Builder_Select coz umozni dalsi dokonceni sql dotazu. Vysledna
 * hodnota (COUNT(*)) bude dostupna v vracenem objektu Result pomoci metody
 * count_records_value().
 * 
 */
class DB extends Kohana_DB {


    /**
     * @var int - podpora pro zanorene volani transakci
     * - vsechny zanorene start transaction a commit volani jsou ignorovana
     */
    protected static $transaction_level = 0;

    public static function startTransaction()
    {
        if (static::$transaction_level === 0) {
            static::query(NULL, 'START TRANSACTION;')->execute();
        }
        static::$transaction_level++;
    }

    public static function commit()
    {
        static::$transaction_level--;
        if (static::$transaction_level === 0) {
            static::query(NULL, 'COMMIT;')->execute();
        }
    }


    /**
     * Generuje Database_Query_Builder_Select, ktery ma definovanou klauzuli
     * SELECT jako COUNT(*) nebo COUNT($column), cili slouzi k ziskani poctu
     * zaznamu.
     *
     * Vysledny pocet zaznamu bue dostupny v Result objektu pomoci metody
     * count_records_value().
     * 
     * @param string $column
     * @return Database_Query_Builder_Select 
     */
    public static function select_count_records($column = NULL)
    {
        $column != NULL or $column = '*';

        return new Database_Query_Builder_Select(array('COUNT("'.$column.'") as __cr'));
    }

}
