<?php defined('SYSPATH') or die('No direct access allowed.');

class Model_User_Token extends Model_Auth_User_Token {

    /**
     * V bazovem ORM metoda nastavuje hodnotu atributu 'created'. V tomto modelu
     * je tento sloupecek taky, ale ma jiny vyznam, takze chci zamezit tomu
     * aby se automaticky doplnovala jeho hodnota.
     */
    protected function setDefaults()
    {
        return;
    }

    protected function applyUserSelectPermission()
    {
        return TRUE;
    }

    protected function applyUserDeletePermission()
    {
        return TRUE;
    }

    protected function applyUserInsertPermission()
    {
        return TRUE;
    }

} // End User Token Model