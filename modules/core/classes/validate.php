<?php defined('SYSPATH') or die('No direct script access.');

class Validate extends Kohana_Validate {

    /**
     * Tato validancni metoda je temer stejna jako metoda 'numeric', ale
     * akcetuje pouze positivni cisla.
     *
     * @param <string> $str
     * @return <bool> Vraci TRUE v pripade ze se jedna o pozitivni cislo
     * (muze mit desetinnou cast), FALSE v opacnem pripade.
     */
    public static function positive_numeric($str)
    {
        // Get the decimal point for the current locale
	list($decimal) = array_values(localeconv());

	// A lookahead is used to make sure the string contains at least one digit (before or after the decimal point)
	return (bool) preg_match('/^(\+|)(?=.*[0-9])[0-9]*+'.preg_quote($decimal).'?+[0-9]*+$/D', (string) $str);
    }

    /**
     * Metoda provadi validaci IČa.
     * Pravidla:
     * - musi se jednat pouze o cislice
     * - delka 8 znaku
     *
     * @param <string> $str
     */
    public static function ic($str)
    {
        return (bool) preg_match('/^[0-9]{8}$/', (string)$str);
    }

    /**
     * Metoda provadi validaci DIČa.
     * Pravidla"
     * - 2 znaky na zacarku
     * - 8 nebo 10 cislic nasleduje
     *
     * @param <string> $str
     */
    public static function dic($str)
    {
        return (bool) preg_match('/^[a-zA-Z]{2}([0-9]{8,10})$/', (string)$str);
    }

    /**
     * Tato validacni metoda kontroluje zda je format tel. cisla v poradku.
     * Kontrola probiha tak ze jsou odstraneny vsechny bile znaky, a pote se
     * provadi kontrola zda zbyvaji pouze cislice a zda je jejich
     * pocet v poradku - tedy 9 nebo 12.
     *
     */
    public static function phone($number, $lengths = NULL)
    {
        if ( ! is_array($lengths))
	{
            $lengths = array(9, 12);
	}

        //odstrani vsechny bile znaky
        $number = preg_replace('/\s/', '', $number);

        //ze zacatku odstranim znak '+'
        $number = preg_replace('/^\+/', '', $number);

	    // Check if the number is within range
	    return preg_match('/^[0-9]+$/', $number) && in_array(strlen($number), $lengths);
    }

    /**
     * This validation rule is less strict then other validation rules for phone numbers.
     *
     * It is accepting both Europian and USA number formats.
     *
     * It only removes whitespaces and '-' characters and accepts length of the resulting
     * number to be 7,9,10,11,12.
     *
     */
    public static function phone_global($number, $lengths = NULL)
    {
        if ( ! is_array($lengths))
        {
            //alowed lengths of numbers
            $lengths = array(7, 9, 10, 11, 12, 14);

            //00420 724 763 532 [14]
            //  420 724 763 532 [12]
            //      724 763 532 [9]
            //     111 111 1111 [10]
        }

        //remove white spaces
        $number = preg_replace('/\s/', '', $number);

        //remove any dashes
        $number = preg_replace('/\-/', '', $number);

        // Check if the number length is within range
        return in_array(strlen($number), $lengths);
    }

	/**
	 * Validate a URL.
	 *
	 * @param   string   URL
	 * @return  boolean
	 */
	public static function nonstrict_url($url)
	{
		// Based on http://www.apps.ietf.org/rfc/rfc1738.html#sec-5
		if ( ! preg_match(
			'~^

			# scheme
			(?:
                                [-a-z0-9+.]++://
                        )?
			# username:password (optional)
			(?:
				    [-a-z0-9$_.+!*\'(),;?&=%]++   # username
				(?::[-a-z0-9$_.+!*\'(),;?&=%]++)? # password (optional)
				@
			)?

			(?:
				# ip address
				\d{1,3}+(?:\.\d{1,3}+){3}+

				| # or

				# hostname (captured)
				(
					     (?!-)[-a-z0-9]{1,63}+(?<!-)
					(?:\.(?!-)[-a-z0-9]{1,63}+(?<!-)){0,126}+
				)
			)

			# port (optional)
			(?::\d{1,5}+)?

			# path (optional)
			(?:/.*)?

			$~iDx', $url, $matches))
			return FALSE;

		// We matched an IP address
		if ( ! isset($matches[1]))
			return TRUE;

		// Check maximum length of the whole hostname
		// http://en.wikipedia.org/wiki/Domain_name#cite_note-0
		if (strlen($matches[1]) > 253)
			return FALSE;

		// An extra check for the top level domain
		// It must start with a letter
		$tld = ltrim(substr($matches[1], (int) strrpos($matches[1], '.')), '.');
		return ctype_alpha($tld[0]);
	}

	/**
	 * Returns the error messages. If no file is specified, the error message
	 * will be the name of the rule that failed. When a file is specified, the
	 * message will be loaded from `$field.$rule`, or if no rule-specific message
	 * exists, `$field.default` will be used. If neither is set, the returned
	 * message will be `validate.$rule`. If `validate.$rule` is empty,
	 * then `$file.$field.$rule` will be returned.
	 *
	 * By default all messages are translated using the default language.
	 * A string can be used as the second parameter to specified the language
	 * that the message was written in.
	 *
	 *     // Get errors from messages/forms/login.php
	 *     $errors = $validate->errors('forms/login');
	 *
	 * @uses    Kohana::message
	 * @param   string  file to load error messages from
	 * @param   mixed   translate the message
	 * @return  array
	 */
	public function errors($file = NULL, $translate = TRUE, $object = NULL)
	{
		if ($file === NULL)
		{
			// Return the error list
			return $this->_errors;
		}

		// Create a new message list
		$messages = array();

		foreach ($this->_errors as $field => $set)
		{
			list($error, $params) = $set;

			// Get the label for this field
			$label = $this->_labels[$field];

			if ($translate)
			{
				if (is_string($translate))
				{
					// Translate the label using the specified language
					$label = __($label, NULL, $translate);
				}
				else
				{
					// Translate the label
					$label = __($label);
				}
			}

			// Start the translation values list
			$values = array(
				':field' => $label,
				':value' => $this[$field],
			);

			if (is_array($values[':value']))
			{
				// All values must be strings
				$values[':value'] = implode(', ', Arr::flatten($values[':value']));
			}

			if ($params)
			{
				foreach ($params as $key => $value)
				{
					if (is_array($value))
					{
						// All values must be strings
						$value = implode(', ', Arr::flatten($value));
					}

					// Check if a label for this parameter exists
					if (isset($this->_labels[$value]))
					{
						// Use the label as the value, eg: related field name for "matches"
						$value = $this->_labels[$value];

						if ($translate)
						{
							if (is_string($translate))
							{
								// Translate the value using the specified language
								$value = __($value, NULL, $translate);
							}
							else
							{
								// Translate the value
								$value = __($value);
							}
						}
					}

					// Add each parameter as a numbered value, starting from 1
					$values[':param'.($key + 1)] = $value;
				}
			}

			if ($message = Kohana::message($file, "{$field}.{$error}") AND is_string($message))
			{
				// Found a message for this field and error
			}
			elseif ($message = Kohana::message($file, "{$field}.default") AND is_string($message))
			{
				// Found a default message for this field
			}
			elseif ($message = Kohana::message($file, $error) AND is_string($message))
			{
				// Found a default message for this error
			}
			elseif ($message = Kohana::message('validate', $error))
			{
				// Found a default message for this error
			}
			else
			{
				$message = "$object.validation.{$field}.{$error}";
			}

			if ($translate)
			{
				if (is_string($translate))
				{
					// Translate the message using specified language
					$message = __($message, $values, $translate);
				}
				else
				{
					// Translate the message using the default language
					$message = __($message, $values);
				}
			}
			else
			{
				// Do not translate, just replace the values
				$message = strtr($message, $values);
			}

			// Set the message for this field
			$messages[$field] = $message;
		}

		return $messages;
	}
}