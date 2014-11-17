<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro autocomplete
 * Config parametry tohoto prvku: (? znaci nepovinne, ! znaci povinne)
 *  !'label'      => <string>     ... Label elementu ve formulari
 *  !'relobject'  => <string>     ... Relacni objekt ze ktereho bude autocomplete nabizet hodnoty
 *  ?'watermark'  => <string>     ... Retezec pro zobrazeni misto prazdne hodnoty
 *                                    V pripade jeho pouziti je inputu pridana css class=watermark
 *  ?'preview'    => <string>     ... Format pro zobrazeni polozek v autocomplete, pokud nechceme
 *                                    defaultni format daneho objektu.
 */
class AppFormItem_RelSelect extends AppFormItem_Base
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/relselect';

    /**
     * Pripojeni potrebnych JS souboru pro RelSelect
     */
    public function init()
    {
        parent::init();
        // Pripojime JS soubor s pluginem
        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemRelSelect.js'));
        // A jeho inicializaci
        $init_js = View::factory('js/jquery.AppFormItemRelSelect-init.js');
        $config['attr']    = $this->attr;
        //pozadovane preview relacnich zaznamu
        $config['preview'] = arr::get($this->config, 'preview', '');
        //max pocet vysledku, ktery se ma nacitat do autocompletu
        //(fallback hodnota je 15)
        $config['_ps'] = arr::get($this->config, 'page_size', 10);

        //pokud ma prvek umoznit vytvoreni noveho relacniho zaznamu, tak
        //jQuery pluginu predam URL pro nacteni editacniho formulare
        if (arr::get($this->config, 'new', ''))
        {
            $config['add_new_url'] = appurl::object_new_ajax($this->config['relobject'], arr::getifset($this->config, 'relformconfig', NULL), arr::get($this->config, 'new_defaults'), arr::get($this->config, 'overwrite'));
            //pridam konfiguraci dialogu
            $config['dialog'] = arr::getifset($this->config, 'dialog', array());
        }

        //url pro cteni dat - v parametrech se bude predavat i filtr, ktery je
        //nastaveny v konfiguraci prvku
        $config['data_url'] = appurl::object_cb_data($this->config['relobject'], $this->getConfigValue('filter', array()));

        //filter_by_parent definuje nazev atributu/prvku podle jehoz hodnoty se filtruje
        //na tomto prvku. Vetsinou se jedna o vazby typu firma - pobocky, nebo pobocka - pracovnici
        //apod. Zajistuje ze se filtruje podle nadrazeneho prvku a zarovne pri
        //vyberu na tomto prvku se automaticky doplni rodicovsky
        if (($filter_parent = arr::get($this->config, 'filter_parent')) != FALSE)
        {
            //bude se filtrovat podle tohoto nebo techto atributu - prvek musi na formulari
            //tento atribut najit a vzit si aktualni hodnotu - ocekava ze to
            //bude relselect
            $config['filter_parent_attr'] = (array)$filter_parent;
        }

        //filter_by_child zajistuje ze pri vyberu hodnoty tohoto prvku dojde
        //k vymazani hodnoty child prvku.
        if (($filter_child = arr::get($this->config, 'filter_child')) != FALSE)
        {
            $config['filter_child_attr'] = (array)$filter_child;
        }

        // dynamic filtering by values in the form
        if (($dynamic_filter = arr::get($this->config, 'dynamic_filter')) != FALSE)
        {
            $config['dynamic_filter'] = (array)$dynamic_filter;
        }

        $init_js->config = $config;
        //prida do sablony identifikator tohoto prvku a zajisti vlozeni do stranky
        parent::addInitJS($init_js);
    }


     /**
     * Tato metoda je vyvolana rodicovskym formularem a slouzi k vlozeni aktualni
     * hodnoty do ORM modelu.
     * Tato trida ma ve form_data asociativni pole ve tvaru
     *  Array('name'=>[nazev_objektu], 'value'=>[id_objektu]),
     * ktere je predano metode setValue bazovou metodou assignValue.
     * Do modelu chceme zapsat hodnotu pod klicem "value"
     * @param <mixed> $value
     */
    public function setValue($value)
    {
        $value = arr::get($value, 'value', NULL);

        if ($value !== NULL)
        {
            //pokud prisla prazdna hodnota, tak do modelu ukladam NULL
            if (empty($value))
            {
                $value = NULL;
            }

            parent::setValue($value);
        }
    }

    /**
     *
     * Generuje HTML kod formularoveho prvku
     * navic predava name, vatermark, preview
     *
     * @param <const> $render_style Definuje zpusob zobrazeni formularoveho prvku.
     * Ocekava jednu z konstant AppForm::RENDER_STYLE_*.
     *
     * @param <string> $error_message Definuje validacni chybu, ktera ma byt
     * u prvku zobrazena.
     *
     * @return <View>
     */
    public function Render($render_style = NULL, $error_message = NULL) {
        // Zavolame base Render, ktera vytvori pohled a preda zakladni atributy
        $view = parent::Render($render_style, $error_message);

        // Predame name - z form_data/ORM modelu/watermark
        $this->addNameToView($view);

        // Vratime $view
        return $view;
    }


    /**
     * Prida do pohledu jmeno a pripadne informaci o tom, ze je v nem watermark
     * @return void
     */
    protected function addNameToView($view) {
        // Pokud je name nastaveno ve form data, vezme se z nich
        $view->name = arr::get($this->form_data, 'name', '');

        //Pokud je name stale prazdne, zkusime ho nacit z modelu pomoci preview()
        //select ocekavam na atributu, ktery odpovida nazvu ciziho klice - tedy
        //[object]id
        $relobject = $this->virtual
                        ? $this->config['relobject']
                        : substr($this->attr, 0, -2);

        //pokud ma byt umozneno pridat relacni zaznam v ramci tohoto form. prvku
        //tak se zobrazi tlacitko pro to
        $view->new = arr::get($this->config, 'new', '');
        $view->new_label = arr::get($this->config, 'new_label', __($relobject.'.new_'.$relobject));

        //nazvy atributu musi vzdy jit pres metodu itemAttr rodicovske tridy Form
        $view->name_attr  = $this->form->itemAttr($this->attr.'[name]');
        $view->value_attr = $this->form->itemAttr($this->attr.'[value]');

        // Pokud je specifikovano preview pro tento formItem, pouzijeme ho
        $preview = arr::get($this->config, 'preview', '');
        // A prelozime ho, jinak se pouzije defaultni preview modelu - viz ORM::preview()
        $preview = $preview != '' ? __($preview) : NULL;

        if ($this->virtual)
        {
            $relobject = ORM::factory($relobject, arr::get($this->form_data, 'value'));

            $view->name = $relobject->preview();
        }
        else
        {
            if ($this->model->{$relobject}->loaded())
            {
                $view->name = $this->model->{$relobject}->preview($preview);
            }
            //pokud neni relacni zaznam podle hodnoty PK nalezen, tak uz neexistuje
            //a do prvku se musi propsat prazdna hodnota
            else
            {
                $view->name = $view->value = NULL;
            }
        }

        // Pokud je name stale prazdne, vlozime watermark, pokud je specifikovan
        if (empty($view->name) and ($watermark = arr::get($this->config, 'watermark', '')) != '') {
            $view->name = $watermark;
            $view->watermark = TRUE; // rika ze se ma inputu pridat class watermark
        }
        $view->input_class = arr::get($this->config, 'input_class', 'input-block-level');
    }
}