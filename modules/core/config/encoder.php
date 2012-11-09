<?php defined('SYSPATH') or die('No direct access allowed.');

return array(

    /**
     * Definuje vychozi delku klice ktery vraci metoda Encoder->encode().
     * Kdyz dochazi ke generovani noveho unikatniho klice a je detekovana duplicita
     * je tato hodnota inkrementovana a dochazi k dalsimu cyklu generovani a kontroly
     * duplicity.
     *
     * Pokud je velikost tabulky pro ukladani dat velka, je lepsi vyssi hodnota,
     * protoze to minimalizuje pocet cyklu pri generovani noveho nahodneho
     * identifikatoru. Zaroven je treba si uvedomit ze mame odhadem $hash_length^38
     * moznosti, takze hodnota '10' by mela byt plne dostacujici.
     */
    'hash_length' => 10,

    /**
     * Nazev tabulky nad kterou trida Encoder pracuje.
     */
    'table_name' => 'encoder',

    /**
     * Vsechny zaznamy starsi nez tento interval budou pravidelne v cron udalosti
     * odstraneny z tabulky encoder.
     */
    'clean_old' => (86400 * 30),
);

?>
