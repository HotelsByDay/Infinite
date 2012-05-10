<?php defined('SYSPATH') OR die('No direct access allowed.');


return array(

    'preview' => '@name',

    'create_new_button' => array(
        //novy ukol
        'agenda.new_task'  => appurl::object_new_ajax('agenda', 'agenda_task_form', array('datedue' => date('Y-m-d'), 'cb_agenda_typeid' => '1')),
        //nova udalost
        'agenda.new_event' => appurl::object_new_ajax('agenda', 'agenda_event_form', array('datedue' => date('Y-m-d', time() + (24*3600)), 'cb_agenda_typeid' => '2')),
    ),

    //obecne akce, ktere lze nad zaznamem vyvolat (bude z /table vypisu nebo z /overview stranky)
    'actions' => array(

        'delete' => array(
            //tato akce nebude zobrazena v panelu pro hromadne akce, ale specialnim
            //tlacitkem na vypisu zaznamu ji bude mozne vyvolat
            'hidden' => TRUE
        )
    ),
);