<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Jazykovy soubor, ktery obsahuje hlasky spojene s prihlasovanim a opravnenim uzivatelu.
 */
return array(
    'invalid_login_or_password' => 'Invalid username or password.',
    'footer_copyright' => 'Footer copyright...',

    'error.404.title' => 'The requested page was not found.',
    'error.404.message' => '',

    'error.500.title' => 'Unexpected error occured.',
    'error.500.message' => 'Pleaase contact our support at <a href="mailto:support@infinite.cz">support@infinite.cz</a>.',


    //ZAKLADNI POPISKY
    'jquery-ui._dialog.message_window_ok_button_label' => 'Close',
    'general.submit_comment' => 'Question',
    'general.my_profile' => 'My profile',
    'general.logout' => 'Logout',

    'general.bool_yes' => 'Yes',
    'general.bool_no' => 'No',

    'main_menu.settings' => 'Settings',

    'object_data_panel.search' => 'Search',

    'object.action.delete.message_ok' => ':count records were succesfully removed.',
    'object.action.delete.message_error' => 'There was an error when removing records:',
    'object.action.delete.undo_message_ok' => ':count records were succesfully restored.',
    'object.action.delete.undo_message_error' => 'There was an error restoring these records:',

    //PRIHLASOVACI OBRAZOVKA
    'login_page.login' => 'Username:',
    'login_page.password' => 'Password:',
    'login_page.login_action' => 'Login',
    'login_page.remember' => 'Remember me on this computer',

    //RESET PASSWORD
    'resetpassword_page.email' => 'E-mail:',
    'resetpassword_page.reset_action' => 'Send me new password',
    'resetpassword.validation.email' => 'The e-mail address is invalid.',
    'resetpasswod.help' => 'Please submit e-mail address associated with you account and you password will be reset and sent to you.',
    'resetpassword.processed' => 'Your password has been reset and sent to your email.',
    'resetpassword.goback_to_login' => 'Go back to login page',

    //OBJECT/TABLE VYPIS DAT
    'blockUI_filtering_table_data' => 'Loading...',
    'object_data_table_pager_goto' => 'Go to line::',
    'object_data_table_page_size' => 'Number of reslults on a page:',
    'pager_info' => 'page :current_page_index out of :total_page_count',
    'object.succesfully_deleted_items' => ':count records have been sucesfully removed.',
    'object.succesfully_deleted_one_item' => 'Record ":preview" has been sucesfully removed.',
    'object.item_not_deleted_due_to_error' => 'Record ":preview" could not be removed due to error: :error',

    'object.section_table' => 'List',
    'object.item_action_take_back' => 'Undo',
    'object.form_return_link_label' => 'Go back to the list',

    'general.loading_table_data' => 'Loading...',
    'general.blockUI_loading_overview_subcontent' => 'Loading...',
    'general.blockUI_filtering_table_data' => 'Loading...',

    'general.pager_goto_previous_page' => 'previous',
    'general.pager_goto_next_page' => 'next',


    //FILTRY
    'filter.submit_filter' => 'Search',
    'filter.save_filter_state' => 'Save the filter state',
    'filter.reset_filter_state' => 'Reset',
    'filter.confirm_filterstate_remove_1' => 'Are you sure you want filter "',
    'filter.confirm_filterstate_remove_2' => '" to be removed ?',
    'filter.no_items_selected' => 'No items were selected.',
    'general.user_saved_filters_panel' => 'Saved filters',
    'general.saved_filters_empty' => 'Nemáte uloženy žádné filtry, vyzkoušejte si tuto funkci tak, že vyhledáte záznamy podle Vašich kriterií a následně kliknete na tlačítko "Uložit stav filtru".',

    'object.edit_filter' => 'Upravit filtr',
    'object.reset_filter' => 'Deaktivovat filtr',
    'object.cancel_edit_filter' => 'Zrušit editaci filtru',
    'object.totally_found_items' => 'Found <strong>:total_found</strong> records.',
    'object.no_data_found' => 'There are not records.',

    'object.action_delete.not_authorized' => 'You are not authorized to remove selected items..',
    'object.action_delete.item_not_found' => 'The item was not found.',
    'object.action_delete.not_authorized_on_item' => 'You are not authorized to remove selected item.',
    'object.action_delete.error_occured' => 'An error occured when removing the item.',

    'more.menu_name' => 'Next',

    'setting.menu_name' => 'Settings',

    'filterstate_form.name.label' => 'Filter name',

    
    //LOGACTION
    'logaction.inserted_message' => 'New item ":preview" was created.',
    'logaction.delete_message'   => 'Item ":preview" was deleted.',
    'logaction.undelete_message' => 'Item ":preview" was undeleted.',
    'logaction.updated_message'  => 'Item ":preview" was updated.',

    //USER_ACTION
    'user_action.th_to' => 'Action time',
    'logaction.th_ip'   => 'IP adress',
    'logaction.th_useragent' => 'Typ přístupu',
    'logaction.th_locality' => 'Lokalita',
    'user_activity.filter_fulltext' => 'Obsahuje:',
    'user_activity.filter_to_from' => 'Od:',
    'user_activity.filter_to_to' => 'Do:',

    //obecne pomocne texty
    'codebook.default_prepend_value' => '-not selected-',


    //TEMATA NAPOVEDY
    'tip_help.tip_title' => 'Help',
    'tip_help.topic_1'  => 'Tema napovedy cislo 1.',

    'error.noscript_title' => 'Your internet browser does not support JavaScript.',
    'error.noscript_message' => 'In order to access the application please enable JavaScript in your browser.',
    'error.incombatible_browser_title' => 'Your browser is not compatible with the current version of the application.',
    'error.incombatible_browser_message' => 'Aplikace podporuje prohlížeče <a href="https://www.google.com/chrome/index.html?brand=CHNQ">Google Chrome</a>, <a href="http://www.apple.com/safari/download/">Safari</a>, <a href="http://www.mozilla.org/en-US/firefox/new/">Mozilla Firefox</a>, <a href="http://www.opera.com/download/?custom=yes">Opera</a>, <a href="http://windows.microsoft.com/cs-CZ/internet-explorer/downloads/ie-9/worldwide-languages">Internet Explorer 9</a> a <a href="http://windows.microsoft.com/cs-CZ/internet-explorer/downloads/ie-8">Internet Explorer 8</a>. Pokud si přejete s aplikací pracovat, stáhněte si jeden z těchto prohlížečů.',
    'error.relogin_again_part_1' => 'Pokud se chyba vyskytuje opakovaně, zvolte',
    'error.relogin_again_part_2' => 'Znovu se přihlásit',

    //UPLOAD
    'upload.error_message' => 'Error while uploading :filename - :message',
    'upload.error.invalid_image_dimension.min_width'  => 'The image width is :width px which does not meet the minimal required width of :min_width px.',
    'upload.error.invalid_image_dimension.max_width'  => 'The image width is :width px which exceeds the maximal width of :max_width px.',
    'upload.error.invalid_image_dimension.min_height' => 'The image height is :height px which does not meet the minimal required height of :min_height px.',
    'upload.error.invalid_image_dimension.max_height' => 'The image height is :height px which exceeds the maximal height of :max_height px.',
);