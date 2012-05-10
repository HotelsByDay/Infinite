<?php defined('SYSPATH') or die('No direct script access.');


/**
 * Tento formularovy prvek slouzi k vytvoreni noveho ukolu do Agendy pri vytvareni
 * noveho Zajmu. Uzivateli nabizi na vyber terminu daneho ukolu a moznost upravit
 * predvyplneny nazve ukolu. Uzivatel ma take moznost deaktivovat vytvoreni noveho
 * ukolu.
 */
class AppFormItem_InterestTask extends AppFormItem_Base
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/interesttask';

    //vycet defaultnich hodnot
    protected $form_data = array(
        'date_type' => 'tommorow',  //defaultni den pro vytvoreni ukolu
        'name'      => '',          //defaultni poznamka se bude generovat dynamicky
                                    //podle nastavene vazby na Nabidku
        'user_date' => '',          //defaultni uzivatelske datum (uzivatel usi vepsat sam)
    );

    //zde je reference na ORM model typu agenda, ktery se bude ukladat.
    //Model se incializaje pri validaci a uklada az pri udalosti FORM_EVENT_AFTER_SAVE
    //takze abych nemusel model vytvaret znovu tak si jej ulozim
    protected $agenda_model = NULL;

    //tento prvek je virtualni
    protected $virtual = true;

    /**
     * Zajistuje vlozeni potrebnych JS souboru do stranky.
     */
    public function init()
    {
        //nastavi defaultni nazev ukolu
        if (empty($this->form_data['name']))
        {
            $this->form_data['name'] = __('appformiteminteresttask.default_task_name', array(':preview' => $this->model->advert->preview()));
        }

        //nastaveni defaultniho stavu 'active'
        if ( ! isset($this->form_data['active']))
        {
            $this->form_data['active'] = $this->getDefaultActiveStatus();
        }

        //standardni plugin pro tento prvek
        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemInterestTask.js'));

        //kod, ktery provadi inicializaci pluginu
        parent::addInitJS(View::factory('js/jquery.AppFormItemInterestTask-init.js'));

    }

    /**
     * Vraci hodnotu datumu pro jednu z hodnot 'today', 'tommorow' a dalsi, ktere
     * formularovy prvek nabizi k rychleu vyberu.
     * @param <string> $date_type Jedna z hodnot 'today', 'tommorow', 'after_tommorow'
     * @return <string> Vraci datum ve formatu MySQL. Pokud ma parametr $date_type
     * neznamou hodnotu vraci NULL.
     *
     */
    public function getDateTypeValue($date_type)
    {
        switch ($date_type)
        {
            case 'today':
                return date('Y-m-d');

            case 'tommorow':
                return date('Y-m-d', time() + (3600*24));

            case 'after_tommorow':
                return date('Y-m-d', time() + (3600*24*2));
        }

        //neznama hodnota parametru $date_type
        return NULL;
    }

    /**
     * Provadi validacni kontrolu - kontroluje se hodnota 'user_date' a inicializovany
     * relacni ORM model agenda.
     */
    public function check()
    {
        //pokud uzivatel zvolil ze nechce vytvaret novy ukol, tak se nebude
        //provadet validacni kontrola
        if (arr::get($this->form_data, 'active', 0) == 0)
        {
            return NULL;
        }

        //tady sesbiram vsechny chybove hlasky abych je uzivateli mohl zobrazit
        $error_messages = array();

        //pokud je vybrana hodnota 'user_date' - tak musi byt datum specifikovano
        if (arr::get($this->form_data, 'date_type') == 'user_date'
                && Validate::date(format::mysqlDate(arr::get($this->form_data, 'user_date'))) !== TRUE)
        {
            $error_messages = array_merge($error_messages, array('user_date' => __('appformiteminteresttask.invalid_user_date')));
        }

        //inicializuje ORM model 'agenda' a naplni podle dat v $this->form_data
        $this->agenda_model = $this->getAgendaModel();

        //nad zaznamem pustim validaci
        if ( ! $this->agenda_model->check())
        {
           $error_messages = array_merge($error_messages, $this->agenda->validate()->errors($this->agenda->table_name()));
        }

        //pokud nedoslo k zadnym validacnim chybam, tak vraci NULL (usesna validace)
        return ! empty($error_messages)
                ? $error_messages
                : NULL;
    }

    /**
     * Vytvari instanci agenda ORM modelu, kterou inicializuje hodnotamy z
     * $this->form_data - jedna se o zaznam ukolu, ktery tento prvek vytvari.
     *
     * @return <Model_Agenda> Vraci instanci modelu Model_Agenda, ve ktere
     * jsou hodnoty z formulare, ktere uzivatel zadal.
     */
    public function getAgendaModel()
    {
        //instance noveho modelu
        $agenda = ORM::factory('agenda');

        //typ - ukol
        $agenda->cb_agenda_typeid = 1;
        
        //nastaveni parametru
        $agenda->datedue = $this->form_data['date_type'] == 'user_date'
                            //vraci uzivatelem vlozene datum v mysql formatu
                            ? format::mysqlDate($this->form_data['user_date'])
                            //provadi preklad jedne z hodnot today, tommorow atd.
                            : $this->getDateTypeValue($this->form_data['date_type']);

        //nazev ukolu
        $agenda->name = $this->form_data['name'];

        //relace na hlavni zaznam (Zajem)
        $agenda->relid   = $this->model->pk();
        $agenda->reltype = $this->model->reltype();

        //nastavim kategorii ukolu na "Upozorneni na zajmy"
        $agenda->cb_agenda_categoryid = 100;

        //codebook::id('cb_agenda_category.interest_notification')

        //inicializovanou instanci vracim
        return $agenda;
    }

    /**
     * Metoda vyhleda posledni nedokonceny ukol k danemu Zajmu - ten bude uzivateli
     * zobrazen pro vetsi prehlednost.
     *
     * @return <ORM|FALSE> Pokud existuje alespon jeden nedokonceny ukol pro tento
     * zajem, tak vraci jeho ORM model. Jinak vraci FALSE.
     */
    protected function getLastTaskForInterest()
    {
        //vyfiltruju posledni nedokonceny ukol
        $latest_unfinished_task = ORM::factory('agenda')->onlyTasks()
                                                        ->onlyUnfinished()
                                                        ->where('relid', '=', $this->model->pk())
                                                        ->where('reltype', '=', $this->model->reltype())
                                                        ->limit(1)
                                                        ->order_by('created', 'desc')
                                                        ->find();

        //pokud byl nejaky nalezen, tak jej vracim, jinak false
        return $latest_unfinished_task->loaded()
                ? $latest_unfinished_task
                : FALSE;
    }

    /**
     * Metoda detekuje zda by mel byt defaultni stav prvku aktivni nebo neaktivni.
     * Kdyz je prvek aktivni tak musi uzivatel vyplnit nazev ukolu a muze si vybrat
     * na kdy chce ukol naplanovat. Pokud je prvek neaktivni tak jej muze uzivatel
     * aktivovat a pokud tak neucini tak se zadny ukol vytvaret nebude.
     * 
     * @return <bool>
     */
    public function getDefaultActiveStatus()
    {
        //pokud neni hlavni zaznam ulozeny tak je prvek aktivni
        //a dale pokud uz v agende existuje zaznam pro tento model, tak prvek neni aktivni

        $model_loaded = $this->model->loaded();

        $some_future_agenda_exists = (bool)ORM::factory('agenda')->where('relid', '=', $this->model->pk())
                                                                 ->where('reltype', '=', $this->model->reltype())
                                                                 ->where('datedone', 'IS', DB::expr('NULL'))
                                                                 ->count_all();

        return ! $model_loaded || ! $some_future_agenda_exists;
    }

    /**
     * V udalosti FORM_EVENT_AFTER_SAVE provadi vytvoreno noveho ukolu do Agendy.
     * @param <type> $type
     * @param <type> $data
     */
    public function processFormEvent($type, $data)
    {
        switch($type)
        {
            //po uspesnem ulozeni hlavniho zaznamu formulare dojde k vytvoreni ukolu
            case AppForm::FORM_EVENT_AFTER_SAVE:

                //pokud je v datech hodnota 'active' - '0' tak se nebude ukol
                //ukladat
                if (arr::get($this->form_data, 'active', 0) == 0)
                {
                    return;
                }

                //do agenda zaznamu doplnim vazbu - ta pri validaci nemusela
                //byt znama, protoze hlavni zaznam nemusel byt ulozen
                $this->agenda_model->relid = $this->model->pk();

                //ulozeni zaznamu
                $this->agenda_model->save();

                //ukol byl uspesne vytvoren, takze deaktivuji prvek a zobrazim v nem
                //hlasku o tom ze ukol byl uspesne vytvoren
                $this->form_data['active'] = 0;
                $this->form_data['message'] = __('appformiteminteresttask.task_succesfully_created');

                //ukol byl vytvoren - aktivuji priznak naplanovany u Zajmu
                $this->model->planned = TRUE;
                $this->model->Save();

            break;
        }
    }

    public function Render($render_style = NULL, $error_messages = NULL)
    {
        $view = parent::Render($render_style, $error_messages);

        //najdu posledni nedokonceny ukol pro dany zajem, ktery bude uzivateli
        //zobrazen pro lepsi orientaci
        $view->last_task = $this->getLastTaskForInterest();

        return $view;
    }
}