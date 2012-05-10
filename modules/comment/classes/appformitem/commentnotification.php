<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Tento formularovy prvek se pouziva specialne na formulari pro vlozeni noveho
 * komentare. Prvek zobrazuje seznam uzivatelu, kteri maji opravneni pro cteni
 * daneho zaznamu (ke kteremu se vklada komentar) a kazdy z nich muze byt pomoci
 * checkboxu oznacen. Pri ulozeni komentare bude oznacenym uzivatelum odeslana
 * e-mailova notifikace.
 */
class AppFormItem_CommentNotification extends AppFormItem_RelNNSelect
{
    protected $view_name = 'appformitem/commentnotification';

    //relacni zaznam je vzdy 'user'
    protected $config = array
    (
        'rel'    => 'user',
        //budou filtrovani pouze verejni uzivatele
        'filter' => array(
            array('public', '=', '1')
        )
    );

    /**
     * Po ulozeni relace dojde k odeslani komentaru emailem.
     * @param <int> $type
     * @param <array> $data
     */
    public function processFormEvent($type, $data)
    {
        switch($type)
        {

            case AppForm::FORM_EVENT_AFTER_SAVE:

                $rel = $this->config['rel'];

                //z vazebni tabulky odstranim vsechny vazby, ktere nejsou v aktualnich
                //datech z formulare. Tj. odstranim vsechny vazby, ktere jsou v DB
                //navic proti stavu formulare.

                $delete_query = DB::delete('commentusermap')->where('relid',   '=', $this->model->relid)
                                                            ->where('reltype', '=', $this->model->reltype);
                
                //pokud na formulari neni zaskrtnuto nic, tak mazu vse
                if ( ! empty($this->form_data))
                {
                    $delete_query->where('userid', 'NOT IN', (array)$this->form_data);
                }

                $delete_query->execute();

                //ziskam z DB aktualni vazby (ty budu porovnavat s tim co prislo
                //z formulare) a doplnim ty ktere v DB jeste nejsou
                $current_id_list = $this->getRelItems();

                //uzivatel, ktery vklada kmentar bude automaticky prihlasen
                //k odberu emailovych notifikaci
                $current_user_id = Auth::instance()->get_user()->pk();

                if ( ! in_array($current_user_id, $current_id_list))
                {
                    (array)$this->form_data[] = $current_user_id;
                }

                //vsechny polozky, ktere prisly z komentare a nejsou v
                //aktualnich datech budou pridany
                $new_items_id_list = array_diff((array)$this->form_data, $current_id_list);

                //poud jsou nove polozky k vytvoreni, tak provedu insert
                if ( ! empty($new_items_id_list))
                {
                    //zaklad SQL dotazu
                    $insert_query = DB::insert('commentusermap', array('userid', 'relid', 'reltype'));

                    //pridam jednotlive radky
                    foreach ($new_items_id_list as $new_item_id)
                    {
                        $insert_query->values(array($new_item_id, $this->model->relid, $this->model->reltype));
                    }

                    //pustim insert
                    $insert_query->execute();
                }

            break;
        }
    }

    /**
     * Metoda vraci pole, ktere obsahuje ID relacnich zaznamu na ktere je nastavena
     * relace.
     * @return <array>
     */
    protected function getRelItems()
    {
        return array_merge(Array('id' => array(), 'note' => array()), (array)$this->form_data);
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

        //Account manager role model
        //Loading it here so that I dont have to do a double join 
        $accountmanager_role = ORM::factory('role')->where('name', '=', 'accountmanager')->find();

        //load list of all Super Admins
        $rel_models['superadmin'] = ORM::factory('user')
                                            ->where('user.isroot', '=', '1')
                                            ->find_all();

        //load list of all Account Managers
        $rel_models['accountmanager'] = ORM::factory('user')
                                            ->join('role_user', 'LEFT')
                                            ->on('user.userid', '=', 'role_user.userid')
                                            ->where('role_user.roleid', '=', $accountmanager_role->pk())
                                            ->where('user.isroot', '!=', '1')
                                            ->find_all();

        return $rel_models;
    }
}