<?php defined('SYSPATH') or die('No direct script access.');

class AppFormItem_LangWysiwyg extends AppFormItem_LangString
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/langwysiwyg';

    public function init()
    {
        // Pripojime JS soubor s pluginem
        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemLangWysiwyg.js'));

        // A jeho inicializaci
        $init_js = View::factory('js/jquery.AppFormItemLangWysiwyg-init.js');

        // Poskladame config prvku
        $config = Array(
            // Predame seznam jazyku do pluginu
            //      'locales' => $this->locales,
            'locales_count' => count($this->locales),
            'mode'          => $this->mode,
        );

        $init_js->config = $config;

        //prida do sablony identifikator tohoto prvku a zajisti vlozeni do stranky
        parent::addInitJS($init_js);

        $this->assignValue();

        return;
    }
}