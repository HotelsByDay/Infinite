<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Tento formularovy prvek slouzi k editaci jednoduchy relacnich zaznamu.
 *
 * Oznaceni jednoduche znamena ze pro editaci relacniho zaznamu se nenacita
 * editacni formular do dialogu, ale ve strance je vlozena sablona, ktera
 * obsahuje vstupni pole pro zadani hodnot. Tento prvek je tedy uzivatecny
 * v pripade ze relacni prvek obsahuje maly pocet atributu, napr. Telefonni cislo a
 * poznamku.
 *
 * Prvek ocekava vstupni data z formulare, ktera si prevede do vnitrni podoby
 * kterou zapise do $this->form_data. Pro kazdy zaznam v $this-form_data take
 * vytvorni instanci relacniho modelu a tu ulozi do $this->rel_models. Indexy
 * v techto dvou polich si odpovidaji.
 *
 *
 */
class AppFormItem_SimpleItemList extends AppFormItem_Base
{
    //nazev sablony pro GUI prvku
    protected $view_name = 'appformitem/simpleitemlist';

    //nazev ORM modelu se kterym budu pracovat (ktery bude reprezentovat ulozene soubory)
    protected $rel_model_name = NULL;

    //pro tento prvek neni ocekavan prislusny atribut v ORM modelu
    protected $virtual = TRUE;

    //zde budou modely relacnich zaznamu
    protected $rel_models = array();

    //popisek tlacitka pro pridani dalsi relacni polozky
    protected $add_button_label = '+';

    /**
     * Do stranky vlozim potrebne JS soubory.
     */
    public function __construct($attr, $config, Kohana_ORM $model, ORM_Proxy $loaded_model, $form_data, $form)
    {
        //zakladni zpracovani konfigurace
        parent::__construct($attr, $config, $model, $loaded_model, $form_data, $form);

        //nazev relacniho modelu se kterym se bude pracovat
        $this->rel_model_name = $this->config['model'];

        //z konfigu si vezmu popisek na tlacitko pro pridani dalsiho relacniho zaznamu
        $this->add_button_label = arr::get($this->config, 'add_button_label', $this->add_button_label);

        //plugin pro tento formularovy prvek
        $js_file = View::factory('js/jquery.AppFormItemSimpleItemList-init.js');

        //inicializacnimu souboru predam sablonu relacniho zaznamu, protoze
        //ji nehci vkladat do sablony prvku - obsahuje totiz inputy, takze by
        //se dostala do formularovych dat. Sablonu generuju nad prazdnym modelem.
        $params = array(
            'new_template' => (string)$this->loadRelModelView($this->config['item_view_name'], ORM::factory($this->rel_model_name))
        );

        //predam parametry sablone
        $js_file->params = $params;

        //vlozim do stranky
        parent::addInitJS($js_file);

        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemSimpleItemList.js'));
    }

    /**
     * Metoda provadi preformatovani vstupni hodnoty, tak aby se s ni dale lepe
     * pracovalo. Prevadi vstup ve tvaru:
     *
     * Array
     *  (
     *              [type] => Array
     *                   (
     *                      [0] => s
     *                   )
     *              [id] => Array
     *                   (
     *                      [0] => 6
     *                   )
     *              [description] => Array
     *                   (
     *                      [0] => petkova motokara
     *                   )
     *  )
     *
     * Na tvar:
     *
     * Array
     * (
     *      [6] => Array
     *          (
     *              [action]      => s
     *              [description] => petkova motokara
     *          )
     * )
     *
     * @param <array> $data
     * @return <array>
     */
    protected function formatInputData($data)
    {
        $processed = array();

        foreach ((array)$data as $attr => $values)
        {
            foreach ($values as $i => $value)
            {
                $processed[$i][$attr] = $value;
            }
        }

        return $processed;
    }

    /**
     * Provadi inicializaci a validaci relacnich modelu, ktere jsou urceny
     * k ulozeni - tj. zaznamy v $this->save_rel_models.
     * Ty jsou do atributu vlozeny pri zpracovani vstupnich dat v metode assignValue.
     *
     * @return <array> Vraci asoc. pole, ktere obsahuje chybove hlasky.
     */
    public function check()
    {
        //validacni chyby budou vlozeny do tohoto pole a roztrizeny podle ID
        //relacnich zaznamu
        $error_messages = array();

        //modely, ktere jsou urcene k ulozeni zvaliduju
        foreach ($this->form_data as $i => $item_data)
        {
            if ($item_data['action'] == 's')
            {
                $rel_model = $this->rel_models[$i];

                //validuj
                if ( ! $rel_model->check())
                {
                    //z ORM si vytahnu validacni chyby
                    $error_messages[$i] = $rel_model->validate()->errors($rel_model->table_name());
                }
            }
        }

        return $error_messages;
    }

    /**
     * Zpracovava vstupni hodnotu prvku.
     * Nahraje prislusne relacni modely, provede v nich zmeny a roztridi na soubory
     * ktere jsou urceny k ulozeni a odstraneni. Samotne akce ulozeni a odstraneni
     * budou provedeny v ramci AFTER_SAVE udalosti.
     */
    protected function assignValue()
    {
        if (empty($this->form_data))
        {
            $this->rel_models = ORM::factory($this->rel_model_name)->where($this->model->primary_key(), '=', $this->model->pk())
                                                               ->where('deleted', 'IS', DB::Expr('NULL'))
                                                               ->find_all();

            $this->form_data = array();

            //vytvorim minimalni zaznam do $this->form_data
            foreach ($this->rel_models as $i => $rel_model)
            {
                $this->form_data[$i] = array(
                    'action' => 's'
                );
            }
        }
        else
        {
            //naformatuje vstupni data z formulare do podoby se kterou se s nimi bude lepe pracovat
            $this->form_data = $this->formatInputData($this->form_data);

            //z dat formulare vytahnu hodnotu aktualniho prvku - jedna se o asoc. pole
            //klice jsou 'n' - tam jsou nove soubory
            //nebo 'd' - tam jsou soubory k odstraneni
            //a 'l' kde jiz ulozene soubory

            //zpracuji nove soubory
            foreach ($this->form_data as $i => $item_data)
            {
                //akce - jedna z nasledujicich hodnot:
                //'s' - prvek urceny k ulozeni
                //'d' - prvek urceny k odstraneni
                $action = $item_data['action'];

                //vytvorim novou instanci relacniho ORM - to chci v pripade jakekoli akce
                $target_model = ORM::factory($this->rel_model_name, $item_data['id']);

                switch ($action)
                {

                    //v pripade ulozeni chci do modelu vlozit nove hodnoty
                    case 's':

                        //nastavim vazbu na model nad kterym stoji tento formular
                        $target_model->{$this->model->primary_key()} = $this->model->pk();

                        //do ciloveho modelu nasipu vsechny dalsi hodnoty, ktere prisly na danem klici
                        $this->applyModelValues($target_model, $item_data);

                        break;

                    case 'd':

                        //nic extra se neprovadi

                        break;
                }

                //data z formulare prepisu ORM modelem
                $this->rel_models[$i] = $target_model;

            }
        }

    }

    /**
     * Po ulozeni hlavniho zaznamu ulozim i nahrane soubory, ktere jsou v 1:N
     * relaci.
     */
    public function processFormEvent($type, $data)
    {
        switch($type)
        {
            //volano po uspesne ulozeni zaznamu
            case AppForm::FORM_EVENT_AFTER_SAVE:

                foreach ($this->form_data as $i => $item_data)
                {
                
                    //akce - jedna z nasledujicich hodnot:
                    //'s' - prvek urceny k ulozeni
                    //'d' - prvek urceny k odstraneni
                    $action = $item_data['action'];

                    switch ($action)
                    {
                        case 's':

                            //musim doplnit vazbu na hlavni zaznam, protoze pri
                            //vytvareni noveho zaznamu nemusela byt k dispozici
                            //v metode assignValue kde se vazba nastavuje
                            $this->rel_models[$i]->{$this->model->primary_key()} = $this->model->pk();

                            $this->rel_models[$i]->save();

                            break;

                        case 'd':
                            //zaznam odstranim z DB
                            $this->rel_models[$i]->delete();

                            //odstranim z pameti
                            $this->form_data[$i] = $this->rel_models[$i] = NULL;

                            break;
                    }
                }
                
            break;
        }

        //necham i rodicovsky prvek udelat svou prci
        return parent::processFormEvent($type, $data);
    }

    /**
     * Do modelu souboru vlozi hodnoty z pole v druhem argumentu
     *
     * Implementovane jako stamostatna metoda to je z duvodu moznosti pretizeni
     * v dedidich tridach.
     *
     * @param Model_File $model
     * @param <array> $values
     */
    public function applyModelValues(Kohana_ORM $model, array $values)
    {
        $model->values($values);
    }

    /**
     * Nacita sablonu pro relacni zaznam a vklada do ni zakladni atributy.
     *
     * @param <type> $view_name
     * @param ORM $model
     * @return <type>
     */
    protected function loadRelModelView($view_name, Kohana_ORM $model)
    {
        $view_params = array(
            'model' => $model,
            'attr'  => $this->attr,
            //defaultni akce
            'action' => 's',
        );

        return View::factory($this->config['item_view_name'], $view_params);
    }

    /**
     * Generuje vystup formularoveho prvku.
     * @param <int> $render_style
     * @param <string> $error_message
     */
    public function Render($render_style = NULL, $error_message = NULL)
    {
        $view = parent::Render($render_style, $error_message);

        //do sablony vlozim popisek na tlacitko pro pridani noveho zaznamu
        $view->add_button_label = $this->add_button_label;

        //do sablony vlozim sablony s jednotlivymi soubory
        $rel_items = array();

        //vsecny ulozene relacni modely vlozim do stranky
        foreach ($this->form_data as $i => $item_data)
        {
            //pokud byl relacni zaznam odstranen, tak je $item_data NULL
            //z pole jsem polozku primo neodstranil (pomocu unset) aby se neposunuly
            //indexy, ktere musi sedet ve $this->form_data, $this->rel_models a
            //$this->error_message
            if ($item_data === NULL)
            {
                continue;
            }

            //pozadovana akce
            $action = $item_data['action'];

            //nactu sablonu
            $item_view = $this->loadRelModelView($this->config['item_view_name'], $this->rel_models[$i]);

            //pridam hlasku validacni chyby (nemusi byt definovana, pokud prosla validace bez problemu)
            $item_view->error_message =  arr::getifset((array)$error_message, $i, '');

            //predam akci
            $item_view->action = $action;

            //pridam do seznamu sablon, ktere reprezentuji relacni polozky
            $rel_items[] = $item_view;
        }
        
        $view->rel_items = $rel_items;

        return $view;
    }
}