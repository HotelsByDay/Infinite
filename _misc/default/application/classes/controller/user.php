<?php

class Controller_User extends Controller_Object {


    /**
     * Vyvolava editaci zaznamu 'user' aktualne prihlaseneho uzivatele.
     *
     */
    public function action_my_profile()
    {
        $item_id = Auth::instance()->get_user()->pk();

        return parent::action_edit($item_id);
    }

}

?>
