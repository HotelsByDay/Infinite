<?php defined('SYSPATH') or die('No direct access allowed.');

return array(
    
    /**
     * Definice objektu a atributu na kterych maji byt pouzite serie.
     * Tzn. prislusne ORM modely si automaticky sahnou do tohoto konfiguraku
     * a doplni hodnoty do prislusneho/nych atributu.
     */
    'objects' => array(
        //objekt Nabidky
       'advert' => array(
           //sloupec 'code', typ serie 1
           'code' => '1',
       )
    )
);

?>
