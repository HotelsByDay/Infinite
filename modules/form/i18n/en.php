<?php defined('SYSPATH') or die('No direct script access.');


return array(

    'upload.error.temp_not_writeable' => 'An error occurred while uploading the file. Please contact the support.',
    'upload.error.file_not_defined' => 'There was an error while uploading the file.',
    'upload.error.file_empty' => 'An empty file cannot be uploaded.',
    'upload.error.file_too_large' => 'The file is too large - it has :file_size. The maximum allowed size is :max_file_size.',

    'form_action_status.model_save_failed' => 'There was a validation error.',
    'form_action_result.message_save_failed' => 'An error occurred while saving the form.',
    'form_action_result.message_process_success' => 'Saved succesfully',
    
    //ZAKLADNI HLASKY PRO FORMULAROVE FUNKCE
    'form_action_result.model_validation_failed' => 'There was a validation error. Please check marked fields on the form.',
    'form_action_result.model_save_failed' => 'There was an error while saving.',
    'form_action_status.model_saved_but_may_be_incosistent' => 'Saved succesfully, but there was an error during the post processing.',
    'form_action_result.message_save_success' => 'Saved succesfully',

    'form_action_button.update_label' => 'Save',
    'form_action_button.insert_label' => 'Save',
    'form_action_button.close_label'  => 'Close',
    'form_action_button.delete_label' => 'Delete',
    'form_action_button.delete_ptitle' => 'Deleting...',

    'form_action_button.update_ptitle' => 'Saving...',
    'form_action_button.insert_ptitle' => 'Saving...',


    'appformiteminteresttask.invalid_must_choose_active' => 'Zvolte prosím zda si přejete vytvořit úkol k tomuto Zájmu.',
    'appformiteminteresttask.invalid_user_date'          => 'Vložené datum není ve správném formátu. Vložte datum ve formátu DD.MM.YYYY.',
    'appformiteminteresttask.default_task_name'          => 'Zájem k nabídce ":preview".',
    'appformiteminteresttask.last_task'                    => 'Poslední úkol k tomuto zájmu:',

    'appformiteminteresttask.task_succesfully_created'   => 'Úkol byl úspěšně vytvořen.',

    //APP FORM ITEM REFERENCE
    'formitemreference.rel_item_not_loaded' => 'selected item was not found',

    //APP FORM ITEM PASSWORD
    'appformitempassword.password_label' => 'Password:',
    'appformitempassword.password_confirm_label' => 'Confirm password:',
    
    'appformitempassword.pwd_strength_level_1_message' => 'Password is too short.',
    'appformitempassword.pwd_strength_level_2_message' => 'Password is weak.',
    'appformitempassword.pwd_strength_level_3_message' => 'Password is average.',
    'appformitempassword.pwd_strength_level_4_message' => 'Password is strong.',
    'appformitempassword.passwords_dont_match_message' => 'Passwords do not match.',

    //APP FORM ITEM SERIENUMBER
    'appformitem_serienumber.number_not_generated_yet' => 'Kód bude vygenerován při uložení.',

    //APP FORM SIMPLE ITEM LIST
    'form.AppFormItemSimplteItemList.confirm_delete' => 'Are you sure you want to delete the item?',
    'appformitemsimpleitemlist.delete_ptitle' => 'Deleting item...',

    //APP FORM FILE
    'appformitemfile.maximum_allowed_file_count_is' => 'There can be :count files at most.',
    'appformitemfile.maximum_allowed_file_count_is_one' => 'Only one file is permitted.',
    'appformitemfile.confirm_file_delete' => 'Are you sure you want to delete this file?',
    'appformitemfile.cannot_delete' => 'An error occurred while deleting the file. The file was not removed.',
    'appformitemfile.delete_ptitle' => 'Deleting file...',
    'appformitemfile.remove_file' => 'Delete',
    'valumsUpload.error_download_cancel' => 'If you close the window the file upload will be cancelled.',

    'appformitemadvanceditemlist.add_pi' => 'Loading new item...',
    'appformitemadvancedselect.delete_label' => 'Delete item',

    'valumsUpload.drop_files_here_to_upload' => 'Drop files here',
    'valumsUpload.upload_file' => 'Upload',
    'valumsUpload.cancel_upload' => 'cancel',
    'valumsUpload.error_invalid_extension' => 'Invalid file type.',
    'valumsUpload.error_file_too_large' => 'File is too small.',
    'valumsUpload.error_file_too_small' => 'File is too large.',
    'valumsUpload.error_empty_file' => 'File cannot be empty.',
    'valumsUpload.amount_of' => 'of',
    'valumsUpload.qq_upload_label' => 'Uploading a file...',

    //APP FORM ADVANCED ITEM LIST
    'appformitemadvanceditemlist.cannot_add_new_there_is_empty_item' => 'Another item can be added only after filling the last added item.',
    'form.AppFormItemAdvancedItemlist.order_update.info_message' => 'Click \"Apply changes\" at the bottom of the form to apply new order of items.',
    'form.AppFormItemFile.order_update.info_message' => 'Click \"Apply changes\" at the bottom of the form to apply new order of images.',

    'appformitem_datetime.format_error' => 'The date format is invalid.',

    //APP FORM ITEM INT
    'appformitemint.validation.digit' => 'The value must be numeric.',

    //APP FORM ITEM PASSWORD
    'appformitem.password.validation.not_empty' => 'Password must not be empty.',
    'appformitem.password.validation.match' => 'Passwords do not match.',
    'appformitem.password.validation.min_length' => 'Password must be at least 8 characters long.',
    'appformitem.password.validation.max_length' => 'Password must be at most 50 characters long.',

    //APP FORM ITEM PROPERTY ADDRESS
    'appformitempropertyaddress.property_locationid' => 'Location:',
    'appformitempropertyaddress.postal_code' => 'Postal code:',
    'appformitempropertyaddress.address' => 'Address:',
    'appformitempropertyaddress.latitude' => 'Latitude:',
    'appformitempropertyaddress.longitude' => 'Longitude:',
    'appformitemadvertaddress.show_original_position' => 'Move back to the original position',

    // APP FORM ITEM DATE INTERVAL
    'appformitem.dateinterval.to_label' => ' - ',

    // OBJECT IMAGE SELECTOR
    'objectimageselector.select_image' => 'Select image',

    // AppFormItemLang
    'appformitemlang.remove_btn' => 'X',

    'appformitemlang.remove_lang_from_master.confirm' => 'Are you sure you want to remove the language and all translates for it?',

);