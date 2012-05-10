<?php defined('SYSPATH') OR die('No direct access allowed.');

return array (

    //ma byt formular resetovan po uspesnem ulozeni (resetovani znamena zda
    //ma byt do stranky vlozen prazdny formular pro vlozeni dalsiho noveho zaznamu)
    'reset_after_action' => TRUE,

    //sablona, ktera bude pouzita pro vykresleni formulare
    'view_name' => 'comment_form',

    //definice formularovych prvku pro jednotlive atributy objektu
    'items' => array(
        //text komentare
        'text'   => array('type' => 'text'),
        //telefoni cisla klienta
        'comment_attachement' => array(
            'label' => __('comment_form.attachements.label'),
            'type' => 'commentattachement',
            'model' => 'comment_attachement',
            'file_view_name' => 'file/file_preview',
            'multiple_files' => '1',
        ),
        //vycet uzivatelu na ktere ma byt odeslano upozorneni
        'notifications' => array(
            'label' => __('comment_form.notifications.label'),
            'type' => 'commentnotification',
        )
    )
);