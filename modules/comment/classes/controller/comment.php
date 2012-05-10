<?php defined('SYSPATH') OR die('No direct access allowed.');


class Controller_Comment extends Controller_Object
{

    public function action_record_panel_overview($relid = NULL, $reltype = NULL)
    {
        //nazev tabulky podle reltype
        $table_name = lognumber::getTableName($reltype);

        $this->template = new View('overview_subcontent_response');

        $view = $this->get_comments_panel('record_overview_panel', $relid, $reltype);

        //vystup vracim ve forme JSONu tak aby jej bylo mozne dobre zpracovat
        //na strane klienta
        $this->template->content = array(
                                       'html' => (string)$view
                                   );
    }
    
    /**
     * Generuje panel, ktery slouzi k zobrazeni nejnovejsich neprectenych zprav
     * pro konkretni zaznam a zaroven umoznuje vlozit novy komentar (jako reakci
     * na nove vlozene).
     */
    public function action_record_dialog_overview()
    {
        $reltype = arr::get($this->request_params, 'reltype', NULL);
        $relid   = arr::get($this->request_params, 'relid'  , NULL);
        
        //nazev tabulky podle reltype
        $table_name = lognumber::getTableName($reltype);

        $this->template = View::factory('comment/record_overview_template');

        $this->template->record_overview = $this->get_comments_panel('record_overview_dialog', $relid, $reltype);

        $this->template->widget = View::factory('comment/widget', array('model' => ORM::factory($table_name, $relid)));
    }


    protected function get_comments_panel($view_name, $relid, $reltype)
    {
        //nazev tabulky podle reltype
        $table_name = lognumber::getTableName($reltype);

        //do stranky vlozim specialni sablonu, ktera obsahuje tlacitko (a formular)
        //a object data panel s vypisem nejnovejsich neprectenych komentaru
        $view = View::factory('comment/'.$view_name);

        //metoda vraci nazev tridy, ktera implementuje praci s formulari
        //jedna se bud o bazovou tridu AppForm nebo nejakou z ni dedici
        $form_class_name = $this->_action_edit_form_class_name();

        //parametry pro formular - potrebuji predat defaultni hodnoty reltype
        //a relid
        $params = array(
            'defaults' => array(
                'reltype' => $reltype,
                'relid'   => $relid
            )
        );

        //vytvorim si novy objekt formulare
        $form = new AppForm(ORM::factory($this->object_name), $this->_config_form(), $params, TRUE);

        //vlozim jej do sablony
        $view->form = $form->Render(appurl::object_new_ajax('comment', 'comment_form', array('relid' => $relid, 'reltype' => $reltype)));

        //do sablony vlozim inicializacni soubory 
        $view->script_include_tag = Web::instance()->getJSFiles(TRUE);

        //predam cilovy zaznam ke kteremu komentare patri
        $view->model = ORM::factory($table_name, $relid);

        //ted vsechny neprectene komentare nastavim jako prectene - pro daneho
        //uzivatele a dany zaznam
        $user_id = Auth::instance()->get_user()->pk();

        ORM::factory('comment')->setAllAsRead($user_id, $relid, $reltype);

        return $view;
    }

    /**
     * Uzivatele, ktery je dan parametrem $userid odhlasi z odberu komentarovych
     * notifikaci u zaznamu, ktery je tan parametry $reltype a $relid.
     *
     * @param <type> $reltype   Reltype ciloveho zaznamu
     * @param <type> $relid     Relid ciloveho zaznamu
     * @param <type> $userid    ID uzivatele ktery ma byt odhlasen z odberu
     * komentarovych notifikaci u ciloveho zaznamu.
     */
    public function action_unsign($reltype, $relid, $userid)
    {
        //odstranim prislusnou vazbu v DB
        $rows_deleted = DB::delete('commentusermap')->where('reltype',  '=', (int)$reltype)
                                                    ->where('relid',    '=', (int)$relid)
                                                    ->where('userid',   '=', (int)$userid)
                                                    ->execute();

        //ciloby zaznam (ke kteremu se komentare vztahuji) si nactu abych
        //mohl jeho preview zobrazit ve strance
        $model = ORM::factory($reltype, $relid);

        //pokud byl alespon jeden (spravne by to mel byt vzdy prave jeden) zaznam
        //odstranen tak doslo k odhlaseni uspesne
        if ($rows_deleted != 0)
        {
            $this->template = View::factory('comment/unsigned_from_notification_succesfully', array('preview' => $model->preview()));
        }
        else
        {
            $this->template = View::factory('comment/unsigned_from_notification_unsuccesfully', array('preview' => $model->preview()));
        }
    }

    /**
     * Pro tento kontroler je specialni trida AppForm, ktera navic zajistuje
     * odeslani notifikacnich emailu pri vlozeni noveho nebo editaci komentare.
     *
     * @return <string>
     */
    protected function _action_edit_form_class_name()
    {
        return 'Form_Comment';
    }
}