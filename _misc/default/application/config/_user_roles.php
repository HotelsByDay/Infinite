<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Tento konfiguracni souboru obsahuje vycet aplikacnich roli, ktere je mozne
 * uzivatelum priradit.
 * 
 */
return array(
    //definice opravneni uzivatelske role, ktera opravnuje uzivatele
    //k praci s komentari u vsech zaznamu
    'comment-enabled' => array(
        'functions' => array(
            'comment' => array(
                'table',
                'edit',
                'new'
            )
        )
    ),
    //definice opravneni uzivatelske role, ktera opravnuje uzivatele
    //k pristupu na dashboard
    'dashboard-enabled' => array(
        'functions' => array(
            'dashboard' => TRUE,
        )
    ),
    'admin' => array(
        'inherits' => array('comment-enabled', 'dashboard-enabled'),
        'functions' => array(
            //ma opravneni na vsechny akce v poradaci uzivatele
            'user' => array(
                'table',
                'edit',
                'new'
            )
        )
    )
);