<?php defined('SYSPATH') or die('No direct script access.');


/**
 * Model nad kterým tento prvek pracuje musi pro spravnou funkcnost obsahovat
 * has_many vazbu na relacni model a ten musi obsahovat belongs_to vazbu na model ciselniku.
 */

class AppFormItem_AdvancedNNSelect extends AppFormItem_Base
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/advancednnselect';

    //Tento formularovy prvek je urcen k pouziti jako virtualni
    protected $virtual = TRUE;

    // Priznak rikajici zda jsou data prvku ulozena do databaze a mohou z ni byt ctena
    // - pokud je FALSE, pak jsou ctena z $this->form_data
    // - na FALSE je nastavena v init(), pokud form_data jsou neprazdna
    // - na TRUE je nastavena po dokonceni udalosti FORM_EVENT_AFTER_SAVE
    protected $saved = TRUE;
    
    // Vychozi nastaveni configu
    protected $config = Array(
        // Kolik prvnich polozek (podle hodnoty 'sequence') se povazuje za hlavni a budou se vzdy zobrazovat
        'show_items' => 4,
        // Kolik sekund pockat pred odstranenim nehlavni polozky ze stranky
        'remove_interval' => 0,
    );
    
    protected $form_item_data = array();
    
     /**
     * Pripojeni potrebnych JS souboru pro RelSelect
     * Init je volano v base konstruktoru
     */
    public function init()
    {
        parent::init();
        // Pripojime JS soubor s pluginem 
        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemAdvancedNNSelect.js'));
        // A jeho inicializaci
        $init_js = View::factory('js/jquery.AppFormItemAdvancedNNSelect-init.js');
        $init_js->attr    = $this->attr;
      
        //url pro cteni dat - v parametrech se bude predavat i filtr, ktery je
        //nastaveny v konfiguraci prvku
        $init_js->data_url = appurl::object_cb_data($this->config['rel'], arr::get($this->config, 'filter', array()));
        // Timeout pro odebrani nehlavni polozky ze stranky
        $init_js->remove_interval = $this->config['remove_interval'];
        // veikost stranky pro Autocomplete - pocet vysledku co se natahne do autocompletu
        $init_js->page_size = arr::get($this->config, 'page_size', 10);
        //prida do sablony identifikator tohoto prvku a zajisti vlozeni do stranky
        parent::addInitJS($init_js);
        
        //if ( ! empty($this->form_data)) $this->saved = FALSE;
        // Kohana::$log->add(Kohana::ERROR, 'AAA: '.json_encode($this->form_data));
    }
    
    
    /**
     * Pokud prvek dostal nejaka form_data, pak si do $this->form_item_data ulozi
     * prijate definice vazeb ve tvaru
     * array(
     *      'codebook_id' => instance of rel_model with min_form data
     *      ...
     * );
     */
    protected function assignValue() 
    {
        if ($this->form_data !== NULL)
        {
            // Ziskame nazev relacniho modelu
            $rel_model = $this->model->{$this->config['rel']}->object_name();
            // Vyprazdnime - pro jistotu
            $this->form_item_data = Array();    
            // Zjistime jake polozky uzivatel zvolil (zatrzene checkboxy)
            $selected = arr::get($this->form_data, 'selected', array());
            // Projdeme je a ulozime do atributu form prvku
            foreach ((array)$selected as $item_id) {
                // V postu mohlo prijit i neco nevalidniho
                if (empty($item_id) or ! intval($item_id)) continue;
                // Tady jsem chtel pretypovat na object prislusna data prijata z POSTu
                // ale v sablone minformu by pak bylo slozitejsi zpracovani checkboxu
                // protoze by v objektu pro jejich nazev neexistoval atribut
                $this->form_item_data[$item_id] = ORM::factory($rel_model)->values(arr::get($this->form_data, $item_id, Array()));
            }
        }
    }
    
    /**
     * Tohle je volano tridou formulare pred ulozenim a jeste
     * pred vyvolanim udalosti FORM_EVENT_BEFORE_SAVE
     */
    public function check() 
    {
        if (arr::get($this->config, 'required'))
        {
            if (count($this->form_item_data) == 0)
            {
                return __($this->model->table_name().'.'.$this->attr.'.validation.required');
            }
        }

        // Zatim neni co kontrolovat 
        $result = array();
        foreach ($this->form_item_data as $item_id => $rel_model) {
            // Pokud vazba neprosla validaci pridame chybove hlasky
            if ( ! $rel_model->check()) {
                $result[$item_id] = $rel_model->getValidationErrors();
            }
        }
        // Vratime vysledne pole - do sablony bude nastaveno jako $error_message
        return empty($result) ? NULL : $result;
    }
    
    
    /**
     * Metoda vraci pole, ktere obsahuje vsechny relacni zaznamy, kde klicem je ID 
     * zaznamu v ciselniku (cizi klic)
     * @return <array>
     */
    protected function getRelItems()
    {
        // parametr 'rel' udava jak nazev vazby, tak nazev modelu ciselniku
        $rel = $this->config['rel'];
        
        // Navratova hodnota
        $list = array();
        
        // Precteme definovane vazby
        foreach ($this->model->{$rel}->find_all() as $model)
        {
            // Tady se spoleha na zavedenou konvenci pojmenovani PK a FK
            // Klicem v poli bude PK zanzamu v codebook tabulce
            // Hodnat bude zaznam v relacni tabulce (ten potrebuje pro data mini-formulare)
            $list[$model->{$rel.'id'}] = $model;
        }
        return $list;
    }

    
    /**
     * Metoda vraci pole, ktere obsahuje vsechny relacni zaznamy z ciselniku,
     * ktere se maji zobrazit na formulari.
     * uzivatel na vyber.
     * @param <array> pole ziskane metodou getRelItems - kvuli efektivite se predava a nemusi se pocitat znovu
     * @return <array>
     */
    protected function getRelItemList($rel_items)
    {
        if ($this->config['show_items'] == 0 && empty($rel_items))
        {
            return array();
        }

        //zde vlozim modely relacnich zaznamu, ze kterych bude mit uzivatel na vyber
        $rel_models = array();

        // Primarni klice vsech zaznamu v ciselniku na ktere je vytvorena relace
        $cb_pks = array_keys($rel_items);
        
        // Model ciselniku
        $model = ORM::factory($this->config['rel']);

        if ($this->config['show_items'] != 0 || ! empty($cb_pks))
        {
            // Chceme vzdy zobrazit prvnich N hodnot + vsechny na ktere je vytvorena vazba
            // (uzivatel je pridal autocompletem)
            $model->where_open();

            if ($this->config['show_items'] != 0)
            {
                $model->where('sequence', '<=', $this->config['show_items']);
            }

            // Predani prazdneho pole pro podminku s IN zpusobi Database_Exception, coz nechceme
            if ( ! empty($cb_pks))
            {
                $model->or_where($model->primary_key(), 'IN', $cb_pks);
            }

            $model->where_close();

            if ($this->config['show_items'] != 0)
            {
                $model->order_by('sequence', 'ASC');
            }
        }

        
        foreach ($model->find_all() as $model)
        {
            $rel_models[] = $model;
        }

        // Vratime vsechny zaznamy ciselniku, ktere se maji zobrazit
        return $rel_models;
    }

    
    
    /**
     * V udalosti FORM_EVENT_AFTER_SAVE provadi nastaveni vazeb na vybrane prvky ciselniku.
     * @param <type> $type
     * @param <type> $data
     */
    public function processFormEvent($type, $data)
    {
        switch($type)
        {
            //po uspesnem ulozeni hlavniho zaznamu formulare dojde k vytvoreni ukolu
            case AppForm::FORM_EVENT_AFTER_SAVE:

                // Kohana::$log->add(Kohana::ERROR, 'AAA: '.json_encode($this->form_data));
                
                // Nazev vazby a modelu ciselniku
                $rel = $this->config['rel'];

                // Ziskame nazev relacniho modelu
                $rel_table = $this->model->{$rel}->table_name();
            
                // Odstranime vsechny aktualne definovane vazby - jednim dotazem
                DB::query(Database::DELETE, "DELETE FROM `$rel_table` WHERE `{$this->model->primary_key()}`={$this->model->pk()}")
                    ->execute();
                

                // nastavim vsechny ktere maji byt nastavene
                foreach ($this->form_item_data as $item_id => $rel_model) 
                {
                    // Nastavime vazby
                    $rel_model->{$this->model->primary_key()} = $this->model->pk();
                    $rel_model->{$rel.'id'} = $item_id;
                    $rel_model->save();
                }

            break;
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

        // Pokud neni stav relace ulozen podle prijatych dat z form_data, tak se vezme z DB
        // Jinak ho zobrazime 
        $selected = empty($this->form_item_data) ? $this->getRelItems() : $this->form_item_data;

        // vycet polozek k vyberu
        $view->items = $this->getRelItemList($selected);
        
        // vycet zvolenych polozek
        $view->selected = $selected;
        
        $view->show_items = $this->config['show_items'];
        
        // Predame nazev sablony s min-formularem
        $form_view_name = arr::get($this->config, 'form', false);

        //pokud je prvek renderovan v readonly variante, tak se nacte
        //totozna sablona s nazvem ktery ma suffix '_readonly'
        if ($render_style == Core_AppForm::RENDER_STYLE_READONLY)
        {
            $form_view_name .= '_readonly';
        }

        $view->form = $form_view_name;
        // Vratime $view
        return $view;
    }
}