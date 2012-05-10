<?php defined('SYSPATH') or die('No direct access allowed.');

class Controller_Agenda extends Controller_Object
{

    /**
     * Zajisti vlozeni zakladnich JS souboru, ktere zajisti funkcnost formularu,
     * coz je potreba na formulari pro vlozeni noveho ukolu nebo udalosti, ktery
     * se nacita pres ajax do dialogu.
     */
    public function before()
    {
        //dale vlozim JS Set 'form', kvuli formularum, ktere mohou jQuery.objectDataPanel nacitat
        Web::instance()->addJSFileSet('form');

        return parent::before();
    }
    
    /**
     * Krome standardniho zpracovani akce 'table' navic do stranky vklada JS
     * ktery zajisti inicializaci funkcnosti pridani noveho ukolu a udalosti.
     * K tomu slouzi standardni tlacitko v horni liste, akorat ze pridani
     * noveho zaznamu je provadena pres ajax formulare.
     * @return <type>
     */
    public function action_table()
    {
        //inicializuje funkci tlacitek pro pridani noveho ukolu a nove udalosti
        //ty funguji pres ajax formulare
        Web::instance()->addCustomJSFile(View::factory('js/jquery.agendaController-init.js'));

        //standardni zpracovani akce
        return parent::action_table();
    }

    /**
     * Tato akce slouzi k nastaveni ukolu jako hotovy (finished=1) anebo
     * ne-hotovy (finished=0). Je vyvolavana ajaxem z vypisu ukolu.
     *
     * Akce ocekava tyto parametry:
     * 'f'  bool hodnota, rika zda ma byt ukol nastaveny jako hotovy nebo nehotovy
     * 'id' int  hodnota, identifikator zaznamu agenda (agendaid)
     *
     */
    public function action_task_finished()
    {
        //na vystup se nebude vkladat nic
        $this->template = View::factory('empty_ajax_template');

        $finished = arr::get($this->request_params, 'f', NULL);
        $agendaid = arr::get($this->request_params, 'id', NULL);

        $agenda = ORM::factory('agenda', $agendaid);

        //pokud byl ukol nalezen, tak nastavi jako hotovyw
        if ($agenda->loaded() && $agenda->IsTask())
        {
            $agenda->datedone = $finished ? date('Y-m-d') : NULL;
            $agenda->save();
        }  
    }

    protected function _view_table_empty_data_container($view_name = NULL)
    {
        return $this->_view_table_data_container($view_name);
    }

    protected function _view_table_data_container($view_name = NULL)
    {
        return parent::_view_table_data_container('table_data_container_for_agenda');
    }
}

?>
