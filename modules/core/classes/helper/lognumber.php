<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Tato trida zajistuje pristup k ciselnikum tabulek a sloupcu,
 * ktere se pouzivaji pri ukladani/cteni historie.
 * V pripade ze potrebujeme vyhledat podle hodnoty v poli (polde cisla),
 * trida si pri prvnim cteni vytvori inverzni pole a ulozi ho do staticke promenne.
 * 
 * // Kdyz se na to ted divam tak mi to prijde velmi krkolomne a asi by bylo lepsi
 *    ty metody nedelat staticky nebo ten config nepouzivat a definovat ciselniky 
 *    primo tady.
 * 
 * @author Jiri Dajc
 */
class Helper_LogNumber {
    
    /** 
     * Ciselnik tabulek, kterey se pri prvnim pouziti nacte z configu
     * @var <array>
     */
    protected static $all_tables = NULL;
    
    /**
     * Pole s inverznim slovnikem - vytvorise az pokud je potreba
     * @var <array>
     */
    protected static $all_tables_flipped = NULL;
    
    /**
     * Seznam ciselniku pro jednotlive tabulky, slouzici k prevodu
     * nazvu sloupce na cislo
     * @var <array>
     */
    protected static $tables = NULL;
    
    /**
     * Seznam inverznich ciselniku pro jednotlive tabulky
     * vytvari se po jednotlivych tabulkach v okamziku kdy je z nej cteno
     * Tvar pole navrhuji nasledujici:
     *  Array(
     *      'table_name_1' => Array(
     *          1 => 'col_name_1',
     *          2 => 'col_name_2',
     *          4 => 'col_name_4',
     *      ),
     *      'table_name_2' => Array(
     *          1 => 'col_name_1',
     *          7 => 'col_name_7',
     *      ),
     *  );  
     * @var <2D array>
     */
    protected static $tables_flipped = Array();
    
    
    /**
     * Nacte z configu ciselnik pro danou tabulku (prevod cloupec/cislo), pokud zatim nacteno nebylo
     * a stejne tak ciselnik pro prevod tabulka/cislo
     * @param <mixed> $table cislo nebo nazev tabulky
     * @param <bool> $flip pokud je true vynuti vytvoreni inverznich slovniku
     */
    protected static function loadConfig($table, $flip=false) 
    {
        if (self::$all_tables == NULL) {
            // Pokud nemame ciselnik tabulek, nacteme z configu
            self::$all_tables = Kohana::config('lognumber._');
        }
        
        // Predpokladame ze $table je string
        $table_name = $table;
        // Pokud je $table zadana cislem, vytvori se inverzni slovnik
        // a ziska se jeji nazev z nej
        if (is_int($table)) {
            self::$all_tables_flipped = array_flip(self::$all_tables);
            $table_name = isset(self::$all_tables_flipped[$table]) 
                    ? self::$all_tables_flipped[$table] : NULL;
        }
        // Pokud nemame ciselnik pro sloupce dane tabulky nacteme ho
        if ( ! isset(self::$tables[$table_name])) {
            self::$tables[$table_name] = Kohana::config("lognumber.$table_name");
        }
        
        // Pokud je tabulka zadana cislem, vypocteme rovnou inverzni ciselnik pro sloupce
        if ((is_int($table) or $flip) and isset(self::$tables[$table_name])) {
            self::$tables_flipped[$table_name] = array_flip(self::$tables[$table_name]);
        }
    }
    
    /**
     * Predikat udavajici zda se ma dany sloupec logovat
     * @param <string> table_name nazev tabulky
     * @param <string> col_name nazev sloupce
     */
    /*
    public static function logCol($table_name, $col_name) {
        return (self::getColNumber($table_name, $col_name) != NULL);
    }
     * 
     */
    
    /**
     * Preklad nazvu tabulky na jeji cislo
     * @param type $table_name nazev tabulky
     * @return <int> cislo tabulky
     */
    public static function getTableNumber($table_name) 
    {
        self::loadConfig($table_name);
        return isset(self::$all_tables[$table_name]) ? self::$all_tables[$table_name] : NULL;
    }
    
    /**
     * Preklad nazvu sloupce na jeho cislo
     * @param <string> $table_name nazev tabulky
     * @param <string> $col_name nazev sloupce
     * @return <int> cislo sloupce
     */
    public static function getColNumber($table_name, $col_name) 
    {
        self::loadConfig($table_name);
        return (isset(self::$tables[$table_name]) and isset(self::$tables[$table_name][$col_name]))
            ? self::$tables[$table_name][$col_name] : NULL;
    }
    
    
    
    
    /**
     * Vraci nazev tabuly - zatim NEIMPLEMENTOVANO - treba prijeme na lepsi princip
     * @param type $table_number cislo tabulky
     */
    public static function getTableName($table_number) 
    {
        self::loadConfig((int)$table_number);
        return isset(self::$all_tables_flipped[$table_number]) ? self::$all_tables_flipped[$table_number] : NULL;
    }
    
    
    /**
     * Vraci nazev sloupce k danemu cislu. Tabulka je identifikovana
     * nazvem - predpokladam ze VZDY budeme nejprve zjistovat nazev tabulky
     * podle cisla a dale pracovat prevazne se ziskanym nazvem, proto i tato 
     * metoda bere jako parametr nazev a ne cislo
     * @param type $table_name nazev tabulky
     * @param type $col_number cislo sloupce
     */
    public function getColName($table_name, $col_number) 
    {
        // Nacte config a vynuti vypocet inverzniho ciselniku
        self::loadConfig($table_name, true);
        return (isset(self::$tables_flipped[$table_name]) and isset(self::$tables_flipped[$table_name][$col_number]))
            ? self::$tables_flipped[$table_name][$col_number] : NULL;
    }
    
}

?>
