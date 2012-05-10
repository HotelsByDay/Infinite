<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Tento kontroler zajistuje prihlasovani a odhlasovani uzivatele v systemu.
 *
 *
 *
 * @author: Jiri Melichar
 */
class Controller_Base_Logout extends Controller_Authentication {

    /**
     * Provede odhlaseni uzivatele a presmeruje na prihlasovaci stranku.
     *
     */
    public function before()
    {    
        //odhlaseni a odstraneni session dat daneho uzivatele
        Auth::instance()->logout(true);
        $this->request->redirect(appurl::login_page());
    }
}

?>