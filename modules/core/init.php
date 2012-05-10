<?php defined('SYSPATH') or die('No direct access allowed.');


Route::set('tip_help', '<controller>/<action>',
        array(
            'controller' => 'help',
            'action'     => 'tip',
        ));

//Url pro nahlaseni JS chyby ke kterym dojde u klienta
Route::set('js_error', '<controller>/<action>',
        array(
            'controller' => 'error',
            'action'     => 'js'
        ));

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
