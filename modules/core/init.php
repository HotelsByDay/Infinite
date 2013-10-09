<?php defined('SYSPATH') or die('No direct access allowed.');



Route::set('polymorphic_cb_data', '<controller>/<action>/<object_name>/<object_id>/<config_key>',
    array(
        'controller'  => 'polymorphicnnselect',
        'action'      => 'cb_data',
        'object_name' => '[_a-zA-Z-]+',
        'object_id'   => '[0-9]+',
        'config_key'  => '.*',
    ));


Route::set('generate_image_resize_variant', '<controller>/<action>/<object_name>/<object_id>/<filename>',
    array(
        'controller' => 'web',
        'action'     => 'resize_variant',
        'filename'   => '.*',
    ));


Route::set('tip_help', '<controller>/<action>',
        array(
            'controller' => 'help',
            'action'     => 'tip',
        ));


Route::set('reset_password', '<controller>/<action>',
    array(
        'controller' => 'resetpassword',
        'action'     => 'index',
    ));


//Url pro nahlaseni JS chyby ke kterym dojde u klienta
Route::set('js_error', '<controller>/<action>',
        array(
            'controller' => 'error',
            'action'     => 'js'
        ));

//Url pro nahlaseni JS chyby ke kterym dojde u klienta
Route::set('form_sync_languages', '<controller>/<action>/<object_name>/<object_id>',
    array(
        'controller'  => 'synclanguages',
        'action'      => 'set_enabled_languages',
        'object_name' => '[a-zA-Z0-9_-]+',
        'object_id'   => '[0-9]+',
));

/**
 * Upload souboru
 * Napr.: "/upload/file/advert.photo/advert_photo_preview"
 */
Route::set('direct_upload_file', '<controller>/<action>/<config_key>',
    array(
        'controller'     => 'file',
        'action'         => 'direct_upload',
        'config_key'     => '[.a-z_]+'
    ));


/**
 * Log debug message - only if in debug mode.
 * @param $msg - message to be logged
 * @param $type - log item type
 */
function _log($msg, $type=Kohana::INFO)
{
    if (AppConfig::instance()->debugMode()) {
        Kohana::$log->add($type, $msg);
    }
}

/**
 * Kohana translation/internationalization function. The PHP function
 * [strtr](http://php.net/strtr) is used for replacing parameters.
 *
 *    __('Welcome back, :user', array(':user' => $username));
 *
 * [!!] The target language is defined by [I18n::$lang]. The default source
 * language is defined by [I18n::$source].
 *
 * @uses    I18n::get
 * @param   string  text to translate
 * @param   array   values to replace in the translated text
 * @param   string  source language
 * @return  string
 */
function ___($string, $values = NULL, $fallback = NULL, $source = NULL)
{
        //tady je jen pred-zpracovani argumentu tak aby bylo mozne funkci
        //volat jen s prvnimi dvema argumenty bez $values
        if (is_string($values) && $fallback == NULL)
        {
                $fallback = $values;
                $values = array();
        }
//@TODO: Otestovat - ted se mi zda ze to funguje divne - vraci to en-us
//	if ( ! $source)
//	{
//		// Use the default source language
//		$source = I18n::$source;
//	}

	if ($source !== I18n::$lang)
	{
                $init_string = $string;

                // The message and target languages are different
		// Get the translation for this message
		$string = I18n::get($string, $source);

                //pokud nebyl nalezen preklad, tak pouziju fallback variantu
                if ($string == $init_string)
                {
                    if (empty($fallback))
                    {
                        return $fallback;
                    }
                    
                    $string = I18n::get($fallback, $source);
                }
	}

	return empty($values) ? $string : strtr($string, $values);
}
