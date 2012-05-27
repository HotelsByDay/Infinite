<?php defined('SYSPATH') or die('No direct script access.');


class AppFormItem_AdvancedItemList extends AppFormItem_Base
{
    //nazev sablony pro GUI prvku
    protected $view_name = 'appformitem/advanceditemlist';

    //nazev ORM modelu se kterym budu pracovat (ktery bude reprezentovat ulozene soubory)
    protected $rel_object_name = NULL;

    //pro tento prvek neni ocekavan prislusny atribut v ORM modelu
    protected $virtual = TRUE;

    //zde budou modely relacnich zaznamu
    protected $itemlist_form = array();
    protected $itemlist_model = array();

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
        $this->rel_object_name = $this->config['rel_object'];

        //z konfigu si vezmu popisek na tlacitko pro pridani dalsiho relacniho zaznamu
        $this->add_button_label = arr::get($this->config, 'add_button_label', $this->add_button_label);

        //plugin pro tento formularovy prvek
        $js_file = View::factory('js/jquery.AppFormItemAdvancedItemList-init.js');

        $params = array(
            //URL na ktere se nacte formular pro novou polozku
            'new_item_url' => appurl::object_itemlist_new_ajax($this->config['rel_object'],
                                                               $this->config['rel_form'],
                                                               $this->attr,
                                                               array($this->model->primary_key() => $this->model->pk())
            ),
            //bude mozne polozky radit (drag&drop na ".drag_handler")
            'sortable' => arr::get($this->config, 'sortable', NULL)
        );

        //predam parametry sablone
        $js_file->params = $params;

        //vlozim do stranky
        parent::addInitJS($js_file);

        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemAdvancedItemList.js'));
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
        foreach ($this->itemlist_form as $id => $item_form)
        {
            //pokud se na formulari ma provest akce odstraneni, tak se nebude
            //formular validovat
            if ($item_form->getRequestedAction() == Core_AppForm::ACTION_DELETE)
            {
                continue;
            }

            //provede akci validace
            $item_form->Process(Core_AppForm::ACTION_VALIDATE);

            //pokud nebyla validace uspesna vracim neprazdnou validacni chybu
            if ($item_form->getRequestedActionResult() != Core_AppForm::ACTION_RESULT_SUCCESS)
            {
                $error_messages[$id] = FALSE;
            }
        }

        //@TODO: nejak upravit
        return empty($error_messages) ? NULL : $error_messages;
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
            $model = $this->model->{$this->rel_object_name}->where($this->rel_object_name.'.deleted', 'IS', DB::Expr('NULL'));

            //aplikace razeni (pokud je v konfiguraci definovan atribut, podle ktereho se ma radit)
            if (($sequence_field = arr::get($this->config, 'sortable')))
            {
                $model->order_by($sequence_field, 'asc');
            }

            $rel_models = $model->find_all();

            foreach ($rel_models as $rel_model)
            {
                $this->itemlist_form[$rel_model->pk()] = $this->loadRelModelForm($rel_model);
                $this->itemlist_model[$rel_model->pk()] = $rel_model;
            }
        }
        else
        {
            foreach ($this->form_data as $id => $item_data)
            {
                //pridam pozadovanou akci - ulozeni
                $item_data['_a'] = arr::get($item_data, '_a', Core_AppForm::ACTION_SAVE);

                $model = ORM::factory($this->rel_object_name, $id);
                
                $this->itemlist_form[$id] = $this->loadRelModelForm($model, $item_data);
                $this->itemlist_model[$id] = $model;
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

                foreach ($this->itemlist_form as $id => $item_form)
                {
                    $item_form->Process();

                    //pokud doslo uspesne k odstraneni polozky, tak ji odstranim
                    //z $this->itemlist_form
                    if ($item_form->getRequestedAction() == Core_AppForm::ACTION_DELETE
                            && $item_form->getRequestedActionResult() == Core_AppForm::ACTION_RESULT_SUCCESS)
                    {
                        unset($this->itemlist_form[$id], $this->itemlist_model[$id]);
                    }
                }

            break;
        }

        //necham i rodicovsky prvek udelat svou prci
        return parent::processFormEvent($type, $data);
    }

    /**
     * Nacita sablonu pro relacni zaznam a vklada do ni zakladni atributy.
     *
     * @param <type> $view_name
     * @param ORM $model
     * @return <type>
     */
    protected function loadRelModelForm(Kohana_ORM $model, $form_data = array())
    {
        $form_data['itemlist'] = $this->attr;

        //mezi defaultni polozky, pridam relaci na "hlavni" zaznam
        $form_data['overwrite'] = array(
            $this->model->primary_key() => $this->model->pk()
        );

        //predam readonly priznak
        $rel_form_config = Kohana::config($this->config['rel_form']);

        //pass the readonly parameter
        if ($this->form->is_readonly($this->attr))
        {
            $rel_form_config['readonly'] = TRUE;
        }

        //vytvorim si novy objekt formulare
        $form = new Core_AppForm($model, $rel_form_config, $form_data, TRUE);

        //vlozim jej do sablony
        return $form;
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
        foreach ($this->itemlist_form as $id => $item_form)
        {
            //nejdrive zkontroluju zda na formulari nedoslo ke smazani zaznamu
            $model = $this->itemlist_model[$id];

            //pridam do seznamu sablon, ktere reprezentuji relacni polozky
            $rel_items[] = View::factory('appformitem/advanceditemlist/item_container',array(
                'form'  => $item_form,
                'model' => $model,
                'attr'  => $this->attr
            ));
        }

        $view->rel_items = $rel_items;

        return $view;
    }
}