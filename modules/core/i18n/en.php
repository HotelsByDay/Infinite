<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Jazykovy soubor, ktery obsahuje hlasky spojene s prihlasovanim a opravnenim uzivatelu.
 */
return array(

    'form.close_btn.confirm_label' => 'Are you sure?',
    'validation_error.alpha' => 'Value can contain letters only.',
    'validation_error.alpha_dash' => 'Value can contain only letters and dashes.',
    'validation_error.alpha_numeric' => 'Value must be alpha-numeric.',
    'validation_error.color' => 'Given value is not a valid color.',
    'validation_error.credit_card' => 'Given value is not a valid credit card number.',
    'validation_error.date' => 'Given value is not a valid date.',
    'validation_error.decimal' => 'Given value is not a valid number.',
    'validation_error.digit' => 'Given value is not a valid digit.',
    'validation_error.email' => 'Given value is not a valid e-mail.',
    'validation_error.email_domain' => 'Given value is not a valid e-mail domain.',
    'validation_error.equals' => 'Given values are not equal.',
    // @todo - translate later - when needed (also uncomment in messages/validate.php)
    'validation_error.exact_length' => '',
    'validation_error.in_array' => '',
    'validation_error.ip' => 'Given value is not a valid IP.',
    'validation_error.matches' => '',
    'validation_error.min_length' => '',
    'validation_error.max_length' => '',
    'validation_error.not_empty' => 'Value can not be empty.',
    'validation_error.required' => 'Value can not be empty.',
    'validation_error.numeric' => '',
    'validation_error.phone' => 'Given value is not a valid phone number.',
    'validation_error.phone_prefix_not_empty' => 'Phone country code can not be empty.',
    'validation_error.range' => '',
    'validation_error.regex' => '',
    'validation_error.url' => 'Given value is not a valid URL.',
    'validation_error.validation_unique' => 'Given value is already used in the system.',
    'validation_error.unique' => 'Given value is already used in the system.',



    
    'invalid_login_or_password' => 'Invalid username or password.',
    'footer_copyright' => 'Footer copyright...',

    'afi_url_name.uri_not_available' => 'Given URL name is already used for another object.',

    'error.404.title' => 'The requested page was not found.',
    'error.404.message' => '',

    'error.unexpected_error.title' => 'Unexpected error',
    'error.unexpected_error.message' => 'Unexpected error occurred.',
    'error.500.title' => 'Unexpected error occured.',
    'error.500.message' => 'Pleaase contact our support at <a href="mailto:support@infinite.cz">support@infinite.cz</a>.',

    'system.automatic_logout' => 'You have been automatically logged out due to being inactive.',

    //ZAKLADNI POPISKY
    'jquery-ui._dialog.message_window_ok_button_label' => 'Close',
    'general.submit_comment' => 'Question',
    'general.my_profile' => 'My profile',
    'general.logout' => 'Logout',

    'general.bool_yes' => 'Yes',
    'general.bool_no' => 'No',

    'main_menu.settings' => 'Settings',

    'object_data_panel.search' => 'Search',
    'objectimageselector.manage_images_link' => 'Manage Images',


    'object.inactive' => 'Inactive',
    'object.active' => 'Active',

    'object.activate_action' => 'Activate',
    'object.action.activate.message_ok' => ':count records were successfully activated.',
    'object.action.activate.message_error' => 'There was an error when activating records:',
    'object.action.activate.undo_message_ok' => ':count records were successfully restored (inactivated).',
    'object.action.activate.undo_message_error' => 'There was an error restoring (inactivating) these records:',
    'object.action.activate.confirm' => 'Are you sure to activate selected records?',

    'object.inactivate_action' => 'Inactivate',
    'object.action.inactivate.message_ok' => ':count records were successfully inactivated.',
    'object.action.inactivate.message_error' => 'There was an error when inactivating records:',
    'object.action.inactivate.undo_message_ok' => ':count records were successfully restored (activated).',
    'object.action.inactivate.undo_message_error' => 'There was an error restoring (activating) these records:',
    'object.action.inactivate.confirm' => 'Are you sure to inactivate selected records?',


    'object.action.delete.message_ok' => ':count records were successfully removed.',
    'object.action.delete.message_error' => 'There was an error when removing records:',
    'object.action.delete.undo_message_ok' => ':count records were successfully restored.',
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
    'object.no_data_found' => 'No records found.',

    'object.action_delete.not_authorized' => 'You are not authorized to remove selected items..',
    'object.action_delete.item_not_found' => 'The item was not found.',
    'object.action_delete.not_authorized_on_item' => 'You are not authorized to remove selected item.',
    'object.action_delete.error_occured' => 'An error occured when removing the item.',
    'object.delete_action_confirm' => 'Do you really want to delete selected item?',
    'object.action.delete.confirm' => 'Are you sure to delete selected items?',
    'more.menu_name' => 'Next',

    'setting.menu_name' => 'Settings',

    'filterstate_form.name.label' => 'Filter name',

    'delete' => 'Delete',
    'undelete' => 'Undelete',
    
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
    'user_activity.filter_to_from' => 'From:',
    'user_activity.filter_to_to' => 'To:',

    //obecne pomocne texty
    'codebook.default_prepend_value' => '-not selected-',


    // Form items
    'relnnselect.check_all' => 'Check All',
    'relnnselect.uncheck_all' => 'Uncheck All',

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

    'month_1' => 'Jan',
    'month_2' => 'Feb',
    'month_3' => 'Mar',
    'month_4' => 'Apr',
    'month_5' => 'May',
    'month_6' => 'Jun',
    'month_7' => 'Jul',
    'month_8' => 'Aug',
    'month_9' => 'Sep',
    'month_10' => 'Oct',
    'month_11' => 'Nov',
    'month_12' => 'Dec',

);