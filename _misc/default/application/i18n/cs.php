<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Jazykovy soubor, ktery obsahuje hlasky spojene s prihlasovanim a opravnenim uzivatelu.
 */
return array(

    //OBECNE JAZYKOVE HLASKY
    'object.edit_action'     => 'Upravit',
    'object.overview_action' => 'Přehled',
    'object.delete_action'   => 'Odstranit',
    'object.confirm_delete_action' => 'Opravdu si přejete záznam odstranit ?',
    'object.add_new' => 'Přidat:',
    'object.to' => 'do',
    'object.from' => 'od',

    'object.status_active' => 'Aktivní',
    'object.status_inactive' => 'Neaktivní',



    'agenda.menu_name' => 'Agenda',
    'agenda.new_task' => 'Nový úkol',
    'agenda.new_event' => 'Nová událost',
    'agenda.week_heading' => 'Agenda na týden :week_number:',
    'agenda.menu_name_cal' => 'Kalendář',
    'agenda.menu_name_table' => 'Výpis',



    //AGENDA
    'agenda.th_type' => 'Typ',
    'agenda.th_reference' => 'Reference',
    'agenda.th_name'      => 'Název',
    'agenda.th_info'      => 'Info',
    'agenda.th_note'      => 'Poznámka',
    'agenda.type_1_name'  => 'Úkol',
    'agenda.type_2_name'  => 'Událost',
    'agenda.section_label_with_day_spec' => '<strong>:day_spec</strong> - :date (:day_name)',
    'agenda.section_label_with_day_name' => ':day_name - :date',
    'agenda.section_label_with_date'     => ':date',
    'agenda.section_label_overdue'              => 'Po termínu',

    'agenda.day_spec_0' => 'Dnes',
    'agenda.day_spec_1' => 'Zítra',

    'agenda.day_name_1' => 'Pondělí',
    'agenda.day_name_2' => 'Úterý',
    'agenda.day_name_3' => 'Středa',
    'agenda.day_name_4' => 'Čtvrtek',
    'agenda.day_name_5' => 'Pátek',
    'agenda.day_name_6' => 'Sobota',
    'agenda.day_name_7' => 'Neděle',  

    'agenda.th_monday'    => 'Pondělí',
    'agenda.th_tuesday'   => 'Úterý',
    'agenda.th_wednesday' => 'Středa',
    'agenda.th_thursday'  => 'Čtvrtek',
    'agenda.th_friday'    => 'Pátek',
    'agenda.th_saturday'  => 'Sobota',
    'agenda.th_sunday'    => 'Neděle',

    'agenda.form_name_label' => 'Název',
    'agenda.form_datedue_label' => 'Splnit do',
    'agenda.form_note_label' => 'Poznámka',
    'agenda.form_time_from_label' => 'Od',
    'agenda.form_time_to_label' => 'Do',


    'agenda.filter_cb_adenga_typeid_all' => 'Vše',
    'agenda.filter_cb_adenga_typeid_task' => 'Jen úkoly',
    'agenda.filter_cb_adenga_typeid_event' => 'Jen události',
    'agenda.filter_cb_agenda_categoryid' => 'Kategorie:',
    'agenda.filter_fulltext' => 'Název nebo poznámka obsahuje:',
    'agenda.filter_this_week' => 'Tento týden',

    'agenda.object_data_panel_for_advert_label' => 'Agenda k této nabídce',

    //USER
    'user.filter_headline' => 'Uživatelé',
    'user.menu_name' => 'Uživatelé',
    'user.section_table' => 'Výpis',
    'user.active_value_0' => 'Neaktivní',
    'user.active_value_1' => 'Aktivní',

    'user.filter_fulltext' => 'Jméno nebo uživatelské jméno obsahuje:',

    'user.th_active' => 'Stav',
    'user.th_username' => 'Uživatelské jméno',
    'user.th_seller'    => 'Makléř',
    'user.th_roles' => 'Uživatelské role',
    'user.th_last_login' => 'Poslední přihlášení',
    'user.th_logins' => 'Počet příhlašení',
    'user.th_created' => 'Účet vytvořen',
    'user.th_action' => 'Akce',

    'user.form_new_headline' => 'Nový uživatel',
    'user.form_edit_headline' => 'Uživatel :preview',
    'user.form_active_label' => 'Akvitní:',
    'user.form_username_label' => 'Login:',
    'user.form_email_label' => 'E-mail:',
    'user.form_role_label' => 'Role:',
    //ROLE
    'role.preview_format' => 'Uživatelská role <strong>@name</strong>',

    //SERIE
    'serie.menu_name' => 'Číselné řady',
    'serie.section_table' => 'Výpis',
    'serie.filter_name' => 'Název:',
    'serie.th_name' => 'Název',
    'serie.th_format' => 'Formát',
    'serie.th_next_value' => 'Hodnota počítadla',
    'serie.th_action' => 'Akce',
    'serie.form_format_label' => 'Formát číselné řady:',
    
    //LOGACTION
    'logaction.filter_fulltext' => 'Obsahuje:',
    'logaction_filter.filter_created_from' => 'Vytvořeno od:',
    'logaction_filter.filter_created_to' => 'do:',
    'logaction.filter_userid' => 'Uživatel:',
    'logaction.filter_headline' => 'Historie',

    'logaction.menu_name' => 'Historie',
    'logaction.th_reference' => 'Váže se k',
    'logaction.th_userid' => 'Uživatel',
    'logaction.th_created' => 'Datum a čas',
    'logaction.th_text' => 'Popis',
    'logaction.th_user' => 'Uživatel',
);