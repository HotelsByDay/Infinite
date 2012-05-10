<?php defined('SYSPATH') or die('No direct access allowed.');

//nastavim routu pro pozadavky na zmenu aktivniho jazyka
Route::set('comment', '<controller>/<action>(/<relid>/<reltype>(/<userid>))',
        array(
            'controller' => 'comment',
            'action'     => 'record_dialog_overview|record_panel_overview|unsign',
            'relid'      => '[0-9]+',
            'reltype'    => '[0-9]+',
            'userid'     => '[0-9]+',
        ));
