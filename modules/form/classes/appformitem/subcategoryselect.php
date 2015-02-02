<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Prvek slouzici k vyberu kategorie a N jejich podkategorii. Podkategorie musi byt ulzoeny v asociacni tabulce.
 $config => array(
        // Relobject je pouzit pro nacteni hlavnich kategorii - model musi mit standardni vazbu na relobject
        'relobject' => 'cb_performer_category',
        // Pro nacteni subkategorii - checkboxy - vazby uklada pres metody add()/remove() (standardne by mela byt nastavena has_many through relace)
        'subcategory_object' => 'cb_performer_subcategory',
        // Filter pro hlavni kategorie - viz SelectDataSource
        'filter' => array(
            'column' => 'value',
        ),
        // Filter pro subcategorie
        'subfilter' => array(
            array('active', '=', '1'),
            ...
        ),
 );
 */

class AppFormItem_SubCategorySelect extends AppFormItem_Select
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/subcategoryselect';

    //Tento formularovy prvek je urcen k pouziti jako virtualni
    protected $virtual = TRUE;

    // Nazev mapovaci tabulky/modelu
    protected $map = NULL;

    protected $config = Array(
        'columns_count' => 1,
    );


    public function __construct($attr, $config, Kohana_ORM $model, ORM_Proxy $loaded_model, $form_data, $form)
    {
        parent::__construct($attr, $config, $model, $loaded_model, $form_data, $form);
        // Spocteme nazev mapovaci tabulky
        $this->map = FormItem::NNTableName($this->model->object_name(), $this->config['subcategory_object']);
    }



    /**
     * Pripojeni potrebnych JS souboru pro RelSelect
     */
    public function init()
    {
        parent::init();

        // Pripojime JS soubor s pluginem
        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemSubCategorySelect.js'));
        // A jeho inicializaci
        $init_js = View::factory('js/jquery.AppFormItemSubCategorySelect-init.js');

        $config = Array(
            'attr' => $this->attr,
            'preview' => arr::get($this->config, 'preview', ''),
            'data_url' => appurl::object_cb_data($this->config['subcategory_object'], arr::get($this->config, 'subfilter', array())),
            'columns_count' => $this->config['columns_count'],
        );
        $init_js->config = $config;

        //prida do sablony identifikator tohoto prvku a zajisti vlozeni do stranky
        parent::addInitJS($init_js);

        /*
        // Plugin potrebujeme jen pokud jsou povoleny poznamky
        if (arr::get($this->config, 'note', false)) {
            // Pripojime JS soubor s pluginem
            Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemRelNNSelect.js'));
            // A jeho inicializaci
            $init_js = View::factory('js/jquery.AppFormItemRelNNSelect-init.js');
            $init_js->config = Array();
            //prida do sablony identifikator tohoto prvku a zajisti vlozeni do stranky
            parent::addInitJS($init_js);
        }
        */
    }




    /**
     * Metoda vraci pole, ktere obsahuje ID relacnich zaznamu na ktere je nastavena
     * relace. Pripadne obsahuje i poznamku ukladanou spolu s relaci.
     * @return <array>
     */
    protected function getRelItems()
    {
        if ($this->form_data !== NULL)
        {
            return array_merge(Array('id' => array(), 'note' => array()), (array)$this->form_data);
        }
        else
        {
            $rel_key = $this->config['subcategory_object'].'id';

            if (arr::get($this->config, 'note', false)) {
                $model_key = $this->model->object_name().'id';
                // Precteme vsechny relace daneho modelu z pivotni tabulky
                $map_data = ORM::factory($this->map)
                    ->where($model_key, '=', $this->model->pk())
                    ->find_all();
            }
            else {
                // Pokud neni poznamka, pak pivotni tabulka nemusi mit standardni nazev
                // a precteme ji tedy pres has_many relaci (kvuli zpetne kompatibilite)
                $map_data = $this->model->{$this->config['subcategory_object']}->find_all();
            }

            $id = $note = array();
            foreach ($map_data as $model)
            {
                $id[$model->{$rel_key}] = $model->{$rel_key};
                // Pokud pracujeme s poznamkou, pridame ji
                if (arr::get($this->config, 'note', false)) {
                    $note[$model->{$rel_key}] = $model->note;
                }
            }

            return Array('id' => $id, 'note' => $note);
        }
    }


    /**
     * Vrati ID aktualne zvolene kategorie
     * @return mixed
     */
    protected function getCategory()
    {
        return $this->model->{$this->config['relobject'].'id'};
    }


    /**
     * Metoda vraci pole, ktere obsahuje ID vsech relacnich zaznamu, ktere ma
     * uzivatel na vyber.
     * @return <array>
     */
    protected function getRelItemList()
    {
        //zde vlozim modely relacnich zaznamu, ze kterych bude mit uzivatel na vyber
        $rel_models = array();

        $model = ORM::factory($this->config['subcategory_object']);

        $category_attr = $this->config['relobject'].'id';
        $model->where($category_attr, '=', $this->getCategory());

        foreach (arr::get($this->config, 'subfilter', array()) as $filter_cond)
        {
            $model->where($filter_cond[0], $filter_cond[1], $filter_cond[2]);
        }

        foreach ($model->find_all() as $model)
        {
            $rel_models[] = $model;
        }

        return $rel_models;
    }

    /**
     * Zapouzdruje operaci odstraneni vazby na relacni zaznam.
     *
     * Tohle umoznuje v dedicich tridach upravit chovani pri mazani relacnich
     * zaznamu.
     *
     * @param <type> $rel
     * @param <type> $model
     */
    protected function removeRelItem($rel, $model)
    {
        $this->model->remove($rel, $model);
    }

    protected function addRelItem($rel, $model, $data=NULL)
    {
        // Pokud vazba zatim neexituje, pak ji vytvorime a ulozime i jeji $data
        if ( ! $this->model->has($rel, $model))
        {
            $this->model->add($rel, $model, $data);
        }
        // Jinak, pokud je povoleno zadavani poznamky, pak musime alespon aktualizovat poznamku
        elseif (arr::get($this->config, 'note', false)) {
            // Klice pro nalezeni pivotniho zaznamu
            $keys = Array(
                $this->model->object_name().'id' => $this->model->pk(),
                $this->config['subcategory_object'].'id' => $model->pk(),
            );
            $map = ORM::factory($this->map, $keys);
            if ($map->loaded()) {
                $map->values($data);
                $map->save();
            }
        }
    }

    /**
     * V teto metode se provadi nasledujici specialita:
     *
     * V pripade ze ma dany model vazbu na nekolik relacnich zaznamu, na formulari
     * uzivatel vsechny odskrtne a pak da ulozit, tak do $this->form_data prijde
     * hodnota NULL a v getRelItems by pak doslo k nacteni aktualnich vazeb z DB
     * namisto pouziti stavu formulare (0 polozek zaskrtnuto). Takze pokud je
     * na formulari vyvolana nejaka akce, tak se $this->form_data pretypuje na pole
     * takze v getRelItems se pak pouzije hodnota $this->form_data, protoze uz
     * nebude rovna NULL.
     *
     * @return <type>
     */
    protected function assignValue()
    {
        $requested_action = $this->form->getRequestedAction();
        
        if ( ! empty($requested_action))
        {
            $this->form_data = (array)$this->form_data;

            // Projdeme a smazeme hodnoty
        }

        return parent::assignValue();
    }


    public function setValue($value)
    {
        $category = arr::get($value, 'category');
        $this->model->{$this->attr} = $category;
    }


    /**
     * V udalosti FORM_EVENT_AFTER_SAVE provadi nastaveni vazeb na vybrane role.
     * @param <type> $type
     * @param <type> $data
     */
    public function processFormEvent($type, $data)
    {
        switch($type)
        {
            //po uspesnem ulozeni hlavniho zaznamu formulare dojde k vytvoreni ukolu
            case AppForm::FORM_EVENT_AFTER_SAVE:

                //jeden zpusob je odstranit vsechny aktualni vazby a vlozit nove, ale
                //v pripade nejake chyby by se mohlo stat ze se smazou vazby a nove nevlozi,
                //takze tuto moznost zavrhuji. Misto toho projdu kazdou aktualne nastavenou
                //polozku zvlast a budu testovat zda je vazba nastavena - toto je mene efektivni
                //reseni, ale je bezpecnejsi
                $rel = $this->config['subcategory_object'];

                // Prijata data si rozdelime na pole IDcek relacnich zaznamu (zatrzene checkboxy)
                // a na pole poznamek (tam mohou byt data i pro nezatrzene checkboxy)
                $data_id = (array)arr::get((array)$this->form_data, 'id');
                $data_note = (array)arr::get((array)$this->form_data, 'note');

                //odstranim vsechny 'prebyvajici' vazby
                foreach ($this->model->{$rel}->find_all() as $model)
                {
                    // Pokud jiz subkategorie neni zvolena nebo nepatri do zvolene kategorie
                    // - prvek stoji nad sloupcem pro ulozeni PK kategorie - $this->attr je tedy cizi klic kategorie ktery je zaroven pouzit
                    //   v tabulce subkategorii
                    if ( ! in_array($model->pk(), $data_id) or $model->{$this->attr} != $this->getCategory())
                    {
                        $this->removeRelItem($rel, $model);
                    }
                }


                // Nastavim vsechny ktere maji byt nastavene
                // - pokud jiz jsou nastavene, muze v metode addRelItem dojit k updatu
                foreach ($data_id as $relitemid)
                {
                    // Model sub-kategorie
                    $model = ORM::factory($rel, $relitemid);

                    // Pokud hlavni kategorie nesedi s aktualne zvolenou - skip
                    if ($model->{$this->attr} != $this->getCategory()) {
                        continue;
                    }

                    // Jinak pridame subkategorii
                    if (arr::get($this->config, 'note')) {
                        $data = Array(
                            'note' => trim(arr::get($data_note, $relitemid, '')),
                        );
                    } else {
                        $data = NULL;
                    }

                    $this->addRelItem($rel, $model, $data);
                }


            break;
        }
    }


    /**
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

        $view->value = $this->getCategory();
        $view->values = $this->getValues();
        $view->columns_count = $this->config['columns_count'];

        // Label pro checkboxy se sub-categoriemi s kategoriemi (parent label bude pouzit pro select s kategoriemi)
        $view->sub_label = FormItem::getLabel($this->model->object_name(), $this->attr.'.category');

        //komplet vycet polozek k vyberu
        $view->items = $this->getRelItemList();

        //vytahnu si z DB aktualni stav relace
        $view->selected = $this->getRelItems();

        $view->note = arr::get($this->config, 'note', false);
        // Vratime $view
        return $view;
    }



}