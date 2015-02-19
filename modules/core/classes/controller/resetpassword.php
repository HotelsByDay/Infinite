<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Tento kontroler zajistuje prihlasovani a odhlasovani uzivatele v systemu.
 *
 * 
 *
 * @author: Jiri Melichar
 */
class Controller_ResetPassword extends Controller_Template {

    /**
     * Sablona, ktera bude nactena pro vygenerovani stranky s formularem
     * pro vyresetovani hesla.
     * @var <string>
     */
    public $template = 'resetpassword';

    /**
     * Kontroluje stav prihlaseni uzivatele.
     * 
     */
//    public function before()
//    {
//        parent::before();
//        //Pokud je uzivatel prihlaseny, tak ani nevolam konstruktor rodice,
//        //ale rovnou presmeruju na defaultni stranku systemu.
//        if (Auth::instance()->logged_in()) {
//           //presmerovani uzivatele do systemu
//           $this->sendUserAlong();
//        }
//
//        //pokud neni v session priznak, ktery rika ze uzivatel provedl neuspesny
//        //pokud o prihlaseni, tak tato stranka neni pristupna
//        if ( ! Session::instance()->get('show_reset_password_option'))
//        {
//            $this->request->redirect(appurl::login_page());
//        }
//    }

    /**
     * Tato akce slouzi k prihlaseni uzivatele. Prihlasovaci formular musi smerovat
     * prave na tuto akci.
     *
     * Ocekava v POST poli atribut 'email'.
     */
    public function action_index()
    {
        //pokud jsou v POSTu prihlasovaci udaje uzivatele, tak
        if (isset($_POST['email'])) {

            //vytahnu si prihlasovaci udaje
            $email = trim($_POST['email']);

            //pokud je email prazdny nebo nevalidni tak bude uzivateli zobrazena
            //validacni hlaska
            if ( ! validate::email($email))
            {
                //do sablony se vlozi validacni hlaska
                $this->template->validation_error = __('resetpassword.validation.email');
                
                return;
            }

            //pokud uzivatelsky ucet s danou e-mailovou adresou neexistuje, tak 
            //budeme stejne tvarit jako ze se heslo uspesne resetovalo
            $user = ORM::factory('user')->where('email', '=', $email)->find();

            //pokud byl uzivatelsky ucet nalezen, tak bude vygenerovano nove
            //heslo a bude odeslan e-mail
            if ($user->loaded() == 1)
            {
                //vygeneruje se nove heslo
                $plaintext_password = text::random('@#$%^&*0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 12);

                //zahashovane heslo se zapise do modelu uzivatele
                $user->password = $plaintext_password;

                //zmeny budou ulozeny
                $user->save();

                try {
                    // Send mail via emailq
                    Mailer::resetPassword($user, $plaintext_password);
                } catch (Exception $e) {
                    Kohana::$log->add(Kohana::ERROR, $e->getMessage());
                }
            }

            //do sablony predam priznak, ktery rika ze doslo k uspesnemu
            //vyresetovani hesla - nezavysle na tom jestli byl uzivatel
            //s danou emailovou adresou nalezen
            $this->template->processed = TRUE;
        } 
    }

    /**
     * Metoda presmeruje uzivatele 'do systemu'. Ucel metody spociva v tom
     * ze je volana z nekolika mist a pouze zde je definovana adresa na kterou
     * je standardne uzivatel presmerovan.
     */
    protected function sendUserAlong()
    {
        $this->request->redirect(appurl::homepage());
    }

}

?>