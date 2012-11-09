<?php defined('SYSPATH') or die('No direct access allowed.');

return Array(
    
    /**
     * Nastaveni maximalniho poctu cekacich cyklu
     * na odemceni pocitadla. Po vyprseni poctu je generovana vyjimka.
     */
     'max_unlock_wait_cycle_count' => 50,

    /**
     * Cas cekani na odemknuti serie v jedne iteraci cyklu
     * v mikro-sekundach
     */
    'cycle_wait_time' => 20000, // 20 ms
);

?>
