<?php defined('SYSPATH') or die('No direct access allowed.');

return array(
    //jednotlive polozky menu na leve strance
    'items_left' => array(
        //specialni polozka - tlacitko s ikonou Domecku - smeruje na domaci stranku
        array(
            //konstanta zajisti vygenerovani specialniho kodu pro tlacitko
            'label' => Menu::ITEM_TYPE_HOME,
            'link'  => appurl::homepage()
        ),
    ),
    //jednotlive polozky menu - na prave strance
    'items_right' => array(
        //LOGACTION
        array(
            'label' => __('logaction.menu_name'),
            'link'  => appurl::object_table('logaction'),
            'subnavigation' => array(
                //podsekce
                'sections' => array(
                    array(
                        'label' => __('object.section_table'),
                        'link'  => appurl::object_table('logaction'),
                    ),
                ),
            )
        ),
        //specialni polozka - tlacitko s ikonou Ozubeneho kola - smeruje do sekce nastaveni
        array(
            //konstanta zajisti vygenerovani specialniho kodu pro tlacitko
            'label' => Menu::ITEM_TYPE_SETTING,
            'submenu' => array(
                array(
                    'label' => __('user.menu_name'),
                    'link'  => appurl::object_table('user'),
                    // Bezne nefinovana subnavigation - se sections a new castmi
                    'subnavigation' => array(
                        //podsekce
                        'sections' => array(
                            array(
                                'label' => __('user.section_table'),
                                'link'  => appurl::object_table('user'),
                            ),
                        ),
                        //menu pro vyvoreni nove polozky
                        'new'   => array(
                            //Novy uzivatel
                            array(
                                'label' => __('user.new_user'),
                                'link'  => appurl::object_new('user'),
                                ),
                        )
                    ),
                ),
            )
        ),
    )
);