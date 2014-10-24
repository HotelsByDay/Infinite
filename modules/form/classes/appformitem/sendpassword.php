<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Class AppFormItem_SendPassword
 * Form item which will generate a password.
 */
class AppFormItem_SendPassword extends AppFormItem_Base
{

    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/send_password';


    protected $virtual = true;


    /**
     * Vklada do stranky jquery plugin, ktery zajisuje kontrolu shody hesel a
     * informativni mereni sily hesla (to nijak neovlivnuje ulozeni hesla).
     * @return <type>
     */
    public function init()
    {
        //inicializacni soubor
        $config = array(
            'reset_pass_url' => url::site('sendpassword/reset/'.$this->model->pk()),
        );
        parent::addInitJS(View::factory('js/jquery.AppFormItemSendPassword-init.js')->set('config', $config));

        return parent::init();
    }

    /**
     * V udalosti FORM_EVENT_AFTER_SAVE provadi nastaveni vazeb na vybrane role.
     * @param <type> $type
     * @param <type> $data
     */
    public function processFormEvent($type, $data)
    {
        switch($type)
        {
            //po uspesnem vytvoreni uzivatele dojde k vygenerovani noveho hesla
            case AppForm::FORM_EVENT_AFTER_SAVE:
                if (arr::get($this->form_data, 'generate_pass')) {
                    $plaintext_pass = Text::random();
                    $this->model->password = $plaintext_pass;
                    $this->model->save();

                    Mailer::welcomeNewUser($this->model, $plaintext_pass);
                }
                break;
        }
    }

    public function Render($render_style = NULL, $error_message = NULL)
    {
        // Zavolame base Render, ktera vytvori pohled a preda zakladni atributy
        $view = parent::Render($render_style, $error_message);

        $view->allow_reset = $this->model->loaded();
        return $view;
    }

}
