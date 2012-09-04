<?php defined('SYSPATH') or die('No direct script access.');

/**
 * tento prvek zatim slouzi pouze pro pridani defaultnich roli uzivateli
 * je implementovan tak, aby zadani defaultnich hodnot bylo co nejjednodussi
 * Pouziti:
 * formulari v defaults dame
 * array(
 *     <attr> => array('admin', 'client'),
 * )
 * kde <attr> je nazev atributu nad kterym stoji tento prvek a 'admin' a 'client' jsou role ktere uzivateli chceme nastavit.
 */
class AppFormItem_UserRoleSelect extends AppFormItem_Base
{
    //Nazev sablony pro tento formularovy prvek
    // @todo - create view - it does not exist for now because it is not needed
    protected $view_name = 'appformitem/userroleselect';

    //Tento formularovy prvek je urcen k pouziti jako virtualni
    protected $virtual = TRUE;



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

                // Pokud nestojime nad modelem user pak nic neprovedeme
                if ($this->model->object_name() != 'user') {
                    return;
                }

                Kohana::$log->add(Kohana::INFO, 'userroleselect.virtual_value is: '.json_encode($this->virtual_value));

                // Uzivatelske role ( jejich names )
                $add_roles = (array)$this->virtual_value;

                if ( ! empty($add_roles)) {
                    $add_roles = array_combine($add_roles, $add_roles);
                }

                // precteme role z DB
                $selected_roles = ORM::factory('role')->where_in('name', $add_roles)->find_all();

                // Precteme vsechny aktualni role uziavatele
                // (!) vyzaduje aby user mel nastavenou has many through vazbu na role
                $user_roles = $this->model->role->find_all();


                // Projdeme vsechny role uziavatele a ty ktere mu nenastavujeme mu odebereme
                foreach ($user_roles as $role) {
                    // Odebereme roli uzivateli
                    if ( ! in_array($role->name, $add_roles)) {
                        $this->model->remove('role', $role);
                    } else {
                        // Odebereme roli ze seznamu pro pridani
                        unset($add_roles[$role->name]);
                    }
                }

                // Projdeme zvolene role a pridame je uzivateli (pokud je zatim nema)
                foreach ($selected_roles as $role) {
                    if (in_array($role->name, $add_roles)) {
                        $this->model->add('role', $role);
                    }
                }

            break;
        }
    }









}