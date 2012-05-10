<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vkladani date hodnoty pomoci datePickeru
 */
class AppFormItem_ColumnConfig extends AppFormItem_NNSimpleSelect
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/columnconfig';

    //zde budou pripravene hodnoty
    protected $values = array();

    /**
     * Metoda slouzici pro inicializaci objektu, predevsim v odvozenych tridach.
     * Resi se v ni predevsim pripojovani JS souboru, uprava configu atp.
     * Abychom nemuseli pretezovat konstruktor, ktery ma mnoho parametru, pretizime
     * pouze tuto metodu, ktera v konstruktoru bude vzdy volana.
     */
    public function init()
    {
        //plugin zajistujici funkcionalitu prvku AppFormItemColumnConfig
        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemColumnConfig.js'));

        //vlozim do stranky inicializacni soubor
        parent::addInitJS(View::factory('js/jquery.AppFormItemColumnConfig-init.js'));

        //vezmu nazev modelu pro ktery se bude export ukladat
        $object_name = LogNumber::getTableName($this->model->reltype);

        //pokus o nacteni konfiguracniho sobuoru
        $columns_config = (array)kohana::config($object_name.'_export.columns');

        $default_values = array();

        //priprava hodnot
        foreach ($columns_config as $attr => $info)
        {
            $this->values[$attr] = $info['label'];

            //pokud je nastaven klic s hodnotou 'default' = true, tak tento
            //sloupec bude defaultne vybran
            if (arr::get($info, 'default') == TRUE)
            {
                $default_values[] = $attr;
            }
        }

        //pokud neni model nacten a hodnota prvku je rovna NULL - tzn. neprisly
        //jeste zadne data z formulare
        if ( ! $this->model->loaded() && $this->model->{$this->attr} === NULL)
        {
            //nastavim defaultni hodnotu
            $this->setValue($default_values);
        }

        return parent::init();
    }

    /**
     * Vrati asociativni pole s vyctem hodnot pro tento prvek.
     *
     * Metoda nacita konfiguracni souboru pro ORM model se kterym formular pracuje
     * a z nej vybira vycet sloupcu, ktere bude mit uzivatel na vyber.
     *
     * @return <array> Asoc. pole kde klicem je nazev atributu a hodnotou je jeji
     * jazykovy popisek.
     */
    protected function getValues()
    {
        return $this->values;
    }
}