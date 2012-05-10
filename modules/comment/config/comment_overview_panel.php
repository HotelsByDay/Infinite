<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(

    //sablona, ktera bude pouzita pro vykresleni formulare
    'view_name' => 'comment_overview_panel',

//    //moznosti pro vyber velikosti stranky
//    'page_size' => array(
//        '15' => '15',
//        '30' => '30',
//        '50' => '50'
//    ),
//
//    //defaultni velikost stranky
//    'default_page_size' => 15,
//
//    //povolit funkci pro ulozeni stavu filtru ?
//    'save_filtere_state' => TRUE,
//
    //Definuje defaultni smer razeni.
    //Musi to byt jedna z hodnot z atributu $this->orderby_dir_types.
    'default_orderby_dir' => 'desc',

    //Definuje atribut objektu podle ktereho se ma defaultne radit.
    'default_orderby'     => 'created'
);