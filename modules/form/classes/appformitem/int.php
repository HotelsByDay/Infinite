<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vkladani int hodnoty.
 *
 * Pokud z formulare prijde prazdny retezec, tak jej pretypuje na NULL hodnotu.
 */
class AppFormItem_Int extends AppFormItem_String
{
    /**
     * Priznak, ktery rika zda je aktualni hodnota prvku v poradku co se tyce formatu.
     * Tedy zda je ciselna.
     */
    protected $check = TRUE;

    /**
     * Provadi validacni kontrolu hodnoty prvku.
     */
    public function check()
    {
        if (validate::not_empty($this->form_data) && ! validate::numeric($this->form_data))
        {
            $this->check = FALSE;

            return __('appformitemint.validation.digit');
        }
    }

    public function setValue($value)
    {
        if ($value === '')
        {
            $value = NULL;
        }

        // Upravime hodnotu do <min,max> intervalu - pokud je zadan
        if (isset($this->config['min']) and $value < $this->config['min']) {
            $value = $this->config['min'];
        }
        if (isset($this->config['max']) and $value > $this->config['max']) {
            $value = $this->config['max'];
        }
        
        parent::setValue($value);
    }

    public function getValue()
    {
        //pokud validace formatu hodnoty neprosla v poradku, tak je vracena
        //hodnota, kterou vlozil uzivatel - tedy $this->form_data
        //Tohle je potreba protoze pri vlozeni do ORM by se hodnota pretypovala na
        //int a puvodni hodnota co zadal uzivatel by byla ztracena
        if ( ! $this->check)
        {
            return $this->form_data;
        }

        $value = parent::getValue();

        return $value == ''
                ? ''
                : (int)$value;
    }
}