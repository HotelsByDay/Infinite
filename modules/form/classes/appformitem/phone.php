<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vkladani date hodnoty pomoci datePickeru
 */
class AppFormItem_Phone extends AppFormItem_Base
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/string';


    /**
     * Vraci aktualni hodnotu tohoto prvku, kterou bere z ORM modelu.
     *
     * Uzitecne to je napriklad v pripade prvku pro vlozeni datumu. Od klienta
     * prijde datum v "ceskem" formatu. Metoda setValue zmeni na "MySQL" format.
     * A v teto metode bude opet prevedeno na cesky format.
     *
     * @return <string>
     */
    public function getValue()
    {
        $value = parent::getValue();

        //trimovani kvuli tomu ze by time_format mohl byt prazdny
        return Format::user_phone($value);
    }

    
    /**
     * Pro nastaveni hodnoty vne tridy slouzi metoda assignValue()
     * ve ktere je nacitani z form_data a vyhazovani vyjimky
     * Tato metoda slouzi pro vlastni zapis do modelu.
     * Zde prevadi datum do DB formatu
     */
    public function setValue($value)
    {

        //bazova metoda setValue zajisti zapis do modelu nebo do uloziste pro
        //virtualni formularove prvky
        parent::setValue(Format::db_phone($value));
    }


}