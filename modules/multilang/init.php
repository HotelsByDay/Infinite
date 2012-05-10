<?php defined('SYSPATH') or die('No direct access allowed.');


//metoda attach_panel zajisti vlozeni panlu pro prepinani jazyku do stranky
Dispatcher::instance()
    ->register_listener('system.layout_after_post', array('Multilang', 'attach_lang_panel'));

//nastavuje aktivni jazyk v systemu na zaklade uzivatelskeho nastaveni nebo
//globalniho nastaveni v config.ini
Dispatcher::instance()
    ->register_listener('system.ready', array('Multilang', 'set_active_lang'));

//nastavim routu pro pozadavky na zmenu aktivniho jazyka
Route::set('multilang', '<controller>/<action>',
        array(
            'controller' => 'multilang',
            'action'     => 'set'
        ));

