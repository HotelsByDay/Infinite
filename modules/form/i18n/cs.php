<?php defined('SYSPATH') or die('No direct script access.');


return array(

    'upload.error.temp_not_writeable' => 'Při nahrávání souboru došlo k chybě. Kontaktujte prosím technickou podporu.',
    'upload.error.file_not_defined' => 'Při nahrávání souboru došlo k chybě.',
    'upload.error.file_empty' => 'Prázdný soubor nelze nahrát.',
    'upload.error.file_too_large' => 'Soubor je příliš velký - má :file_size. Maximální povolená velikost je :max_file_size.',

    'form_action_status.model_save_failed' => 'Při validaci formuláře došlo k chybě',
    'form_action_result.message_save_failed' => 'Při ukládání záznamu došlo k chybě',
    'form_action_result.message_process_success' => 'Záznam byl úspěšně uložen',
    
    //ZAKLADNI HLASKY PRO FORMULAROVE FUNKCE
    'form_action_result.model_validation_failed' => 'Při validační kontrole došlo k chybě, zkontrolujte prosím zvýrazněné položky formuláře.',
    'form_action_result.model_save_failed' => 'Při ukládání záznamu došlo k chybě.',
    'form_action_status.model_saved_but_may_be_incosistent' => 'Záznam byl uložen, ale při následném zpracování došlo k chybě.',
    'form_action_result.message_save_success' => 'Uložení záznamu proběhlo úspěšně.',

    'form_action_button.update_label' => 'Uložit',
    'form_action_button.close_label'  => 'Zavřít',
    'form_action_button.delete_label' => 'Odstranit',
    'form_action_button.delete_ptitle' => 'Probíhá odstraňování záznamu.',

    'form_action_button.update_ptitle' => 'Probíhá ukládání záznamu',
    'form_action_button.insert_ptitle' => 'Probíhá ukládání záznamu',


    'appformiteminteresttask.invalid_must_choose_active' => 'Zvolte prosím zda si přejete vytvořit úkol k tomuto Zájmu.',
    'appformiteminteresttask.invalid_user_date'          => 'Vložené datum není ve správném formátu. Vložte datum ve formátu DD.MM.YYYY.',
    'appformiteminteresttask.default_task_name'          => 'Zájem k nabídce ":preview".',
    'appformiteminteresttask.last_task'                    => 'Poslední úkol k tomuto zájmu:',

    'appformiteminteresttask.task_succesfully_created'   => 'Úkol byl úspěšně vytvořen.',

    //APP FORM ITEM REFERENCE
    'formitemreference.rel_item_not_loaded' => 'vybraný záznam nebyl nalezen',

    //APP FORM ITEM PASSWORD
    'appformitempassword.password_label' => 'Heslo:',
    'appformitempassword.password_confirm_label' => 'Heslo znovu:',
    
    'appformitempassword.pwd_strength_level_1_message' => 'Heslo je příliš krátké.',
    'appformitempassword.pwd_strength_level_2_message' => 'Heslo je slabé.',
    'appformitempassword.pwd_strength_level_3_message' => 'Heslo je průměrné.',
    'appformitempassword.pwd_strength_level_4_message' => 'Heslo je silné.',
    'appformitempassword.passwords_dont_match_message' => 'Hesla se neshodují.',

    //APP FORM ITEM SERIENUMBER
    'appformitem_serienumber.number_not_generated_yet' => 'Kód bude vygenerován při uložení.',

    //APP FORM SIMPLE ITEM LIST
    'form.AppFormItemSimplteItemList.confirm_delete' => 'Opravdu si přejete položku odstranit ?',
    'appformitemsimpleitemlist.delete_ptitle' => 'Odstraňování položky...',

    //APP FORM FILE
    'appformitemfile.maximum_allowed_file_count_is' => 'Maximální počet nahraných souborů je :count.',
    'appformitemfile.confirm_file_delete' => 'Opravdu si přejete soubor odstranit ?',
    'appformitemfile.cannot_delete' => 'Při odstraňování souboru došlo k chybě. Soubor nelze odstranit.',
    'appformitemfile.delete_ptitle' => 'Odstraňování souboru...',
    'appformitemfile.remove_file' => 'Odstranit',
    'valumsUpload.error_download_cancel' => 'Pokud stránku zavřete dojde ke zrušení probíhajícího nahrávání souboru.',

    'appformitemadvanceditemlist.add_pi' => 'Načitání nové položky...',
    'appformitemadvancedselect.delete_label' => 'Odstranit položku',

    'valumsUpload.drop_files_here_to_upload' => 'Soubory které si přejete nahrát přetáhněte na tuto plochu.',
    'valumsUpload.upload_file' => 'Nahrát soubor',
    'valumsUpload.cancel_upload' => 'zrušit',
    'valumsUpload.error_invalid_extension' => 'Neplatný typ souboru.',
    'valumsUpload.error_file_too_large' => 'Soubor je příliš velký.',
    'valumsUpload.error_file_too_small' => 'Soubor je příliš malý.',
    'valumsUpload.error_empty_file' => 'Soubor nesmí být prázdný.',
    'valumsUpload.amount_of' => 'z',
    'valumsUpload.qq_upload_label' => 'Probíhá nahrávání souboru',

    //APP FORM ADVANCED ITEM LIST
    'appformitemadvanceditemlist.cannot_add_new_there_is_empty_item' => 'Dalši položka může být přidána až po vyplnění již vložené prázdné položky.',


    'appformitem_datetime.format_error' => 'Formát datumu je chybný.',

    //APP FORM ITEM INT
    'appformitemint.validation.digit' => 'Hodnota musí být číselná.',

    //APP FORM ITEM PASSWORD
    'appformitem.password.validation.not_empty' => 'Heslo musí být vyplněno.',
    'appformitem.password.validation.match' => 'Zadaná hesla se neshodují.',
    'appformitem.password.validation.min_length' => 'Minimální délka hesla je 8 znaků.',
    'appformitem.password.validation.max_length' => 'Maximální délka hesla je 50 znaků.',


    // AppFormItemGradientColorPicker
    'appformitem_gradientcolorpicker.gradient_enabled' => 'Použít gradient',
);