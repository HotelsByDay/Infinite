<?php defined('SYSPATH') or die('No direct script access.');


/**
 * Prezetuje standardni tridu formulare a navic pridava funkcnost, ktera
 * zajisti odeslani notifikacnich emailu v pripade vlozeni noveho nebo editaci
 * komentare.
 * 
 */
class Form_Comment extends Core_AppForm
{

    /**
     * Pri uspesnem ulozeni formulare dojde k vlozeni emailovych notifikaci
     * do fronty k odeslani na prislusne uzivatele.
     */
   protected function setActionResult($action, $result = NULL, $message = NULL)
    {
        $retval = parent::setActionResult($action, $result, $message);

        //pokud byla na formulari uspesne provedena akce ulozeni zaznamu, tak
        //dojde k vlozeni emailovych notifikaci do fronty emailu k doseslani
        if ($this->requested_action == Core_AppForm::ACTION_SAVE
                && $this->requested_action_result == Core_AppForm::ACTION_RESULT_SUCCESS)
        {
            $this->sendEmailNotifications();
        }

        return $retval;
    }

    /**
     * Do fronty emailu k odeslani prida notifikace o novem komentari. Tato
     * notifikace jde na vsechny uzivatele, kteri byly zaskrtnuti na formulari
     * a navic vzdy na uzivatele, ktery komentar vlozil.
     */
    public function sendEmailNotifications()
    {
        //z formularoveho prvku 'notifications' si vezmu aktualni vazby
        $user_id_list = $this->_form_items['notifications']->getValue();

        //pokud nebyly zvoleni zadni uzivatele, tak se emailova notifikace
        //nebude odesilat
        if (empty($user_id_list))
        {
            return;
        }

        //nactu si cilovy zaznam (ten ke kteremu delam komentar) - jeho preview bude v emailu
        $relid      = $this->_model->relid;
        $table_name = lognumber::getTableName($this->_model->reltype);
        $rel_record = ORM::factory($table_name, $relid);

        //ID aktualniho uzivatele
        $userid     = Auth::instance()->get_user()->pk();

        //pripravim si subject pro email
        $subject = __('comment.new_comment_notifications', array(
            ':rel_record_preview' => $rel_record->preview(),
        ));

        //pripravim si body pro email
        $body = View::factory('comment/email/body', array(
            'rel_record_preview' => $rel_record->preview(),
            'user_preview'       => Auth::instance()->get_user()->name(),
            'message'            => $this->_model->_text,
            'attachements'       => $this->_model->attachements->find_all(),
            //odkaz na podsekci preview kde jsou zobrazeny komentare
            'show_all_coments_link' => appurl::object_overview($table_name, $relid, NULL, 'comments'),
            //odkaz na akci kontroleru, ktera odhlasi uzivatele z odberu
            'unsign_from_notifications' => appurl::object_action('comment', 'unsign', array($this->_model->reltype, $this->_model->relid, $userid))
        ));

        //nactu si vschny uzivatele, na ktere pujde emailove upozorneni
        //nacitam kompletni modely, protoze budu potrebovat jejich emailove adresy
        //ktere mohou byt klidne i v jinych relacnich tabulkach (napriklad pri vazbe
        //uzivatel - makler, apod.)
        $users = ORM::factory('user')->where('public', '=', '1')
                                     ->where('userid', 'IN', (array)$user_id_list)
                                     ->find_all();

        //email jde na kazdeho z nich
        foreach ($users as $user)
        {
            //vlozi email do fronty k odeslani (ta je zpracovavana pomoci cronu)
            Emailq::factory()->add_email($user->contact_email(),
                                         NULL,
                                         NULL,
                                         $user->name(),
                                         $subject,
                                         $body);
        }
    }
}

