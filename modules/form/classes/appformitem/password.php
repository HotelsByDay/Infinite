<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Tento formularovy prvek slouzi k nastaveni hesla uzivatele.
 *
 * Prvek funguje pouze nad modelem 'user'.
 *
 *
 * Config parametry tohoto prvku: (? znaci nepovinne, ! znaci povinne)
 *  ?'label'               => <string>  ... Label elementu ve formulari
 *  ?'placeholder'         => <string>  ... Polaceholder password inputu - pokud prohlicec nepodporuje html5 pak se pouzije JS
 *  ?'placeholder_confirm' => <string>  ... Polaceholder password_confirm inputu - pokud prohlicec nepodporuje html5 pak se pouzije JS
 */
class AppFormItem_Password extends AppFormItem_Base
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/password';

    /**
     * Vklada do stranky jquery plugin, ktery zajisuje kontrolu shody hesel a
     * informativni mereni sily hesla (to nijak neovlivnuje ulozeni hesla).
     * @return <type>
     */
    public function init()
    {
        //plugin pro funkci tohoto prvku
        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemPassword.js'));

        //inicializacni soubor
        parent::addInitJS(View::factory('js/jquery.AppFormItemPassword-init.js'));

        return parent::init();
    }

    /**
     * Provadi validacni kontrolu hodnoty prvku. V pripade ze prvek neni virtualni
     * zadna validace provedena neni - ta se nechava na Model. V pripade
     * virtualniho prvku se validuje delka hesla (min, max) a shoda password
     * a password_confirm.
     *
     * @return <type>
     */
    public function check()
    {
        if ($this->virtual)
        {
            //z hodnoty prvku si vytahnu obe hodnoty hesla a potvrzeni hesla
            $password         = arr::get($this->form_data, 'password');
            $password_confirm = arr::get($this->form_data, 'password_confirm');

            //heslo musi byt zadano
            if ( ! arr::get($this->config, 'required') && mb_strlen($password) == 0)
            {
                return;
            }

            if (mb_strlen($password) == 0)
            {
                return __('appformitem.password.validation.not_empty');
            }

            //pokud nesedi delka hesla, tak dochazi k validacni chybe
            if ($password != $password_confirm)
            {
                return __('appformitem.password.validation.match');
            }

            //min. delka hesla je 8 znaku
            if (mb_strlen($password) < 8)
            {
                return __('appformitem.password.validation.min_length');
            }

            //max delka hesla je 50 znaku
            if (mb_strlen($password) > 50)
            {
                return __('appformitem.password.validation.max_length');
            }

            //validace v poradku, vraci se prazdna hodnota
        }
    }

    /**
     * Tato metoda je vyvolana rodicovskym formularem a slouzi k vlozeni aktualni
     * hodnoty do ORM modelu.
     * Tato trida ma ve form_data asociativni pole ve tvaru
     * Array('relid'=>[ID relacniho zaznamu], 'reltype'=>[ciselne oznaceni relacni tabulky]),
     * ktere je predano metode setValue bazovou metodou assignValue.
     * Do modelu chceme zapsat hodnotu pod klicem "value"
     * @param <mixed> $value
     */
    public function setValue($value)
    {
        if ( ! $this->virtual)
        {
            if (arr::get($value, 'password') != '' || arr::get($value, 'password_confirm') != '')
            {
                $this->model->password         = arr::get($value, 'password');
                $this->model->password_confirm = arr::get($value, 'password_confirm');
            }
            //validace se provadi pri kazdem ulozeni a potrebuji aby proslo pravidlo
            //'password_confirm' => 'match' => array('password')
            //Pokud uzivatel nemenil heslo tak atribut password obsahuje hashovane
            //heslo
            else
            {
                $this->model->password_confirm = $this->model->password;
            }
        }
        else
        {
            $this->virtual_value = $value;
        }
    }

    /**
     * Vraci skalarni hodnotu tohoto prvku - plaintext podobu hesla.
     *
     * @return <string>
     */
    public function getValue()
    {
        return arr::get($this->form_data, 'password');
    }

    public function Render($render_style = NULL, $error_message = NULL)
    {
        // Zavolame base Render, ktera vytvori pohled a preda zakladni atributy
        $view = parent::Render($render_style, $error_message);

        $view->label = arr::get($this->config, 'label', __('appformitempassword.password_label'));
        $view->label_confirm = arr::get($this->config, 'label_confirm',  __('appformitempassword.password_confirm_label'));

        //pokud je prvek required, tak se automaticky prida znacka k labelu
        if (((arr::get($this->config, 'required')
                //nektere prvky pouzivaji spcialne tento atribut aby se vyhly standardnimu zpracovani
                //a mohli udelat svoje custom (napr. AppFormItemFile)
                //@TODO: Tohle by asi slo vyresit lepe, ale neni na to ted cas (31.1.2012)
                || arr::get($this->config, '_required'))
                || $this->model->IsRequired($this->attr)) && ! $this->form->is_readonly())
        {
            $view->label .= '<span class="required_label"></span>';
        }

        // If placeholder is defined - add it into view
        if (isset($this->config['placeholder']) and ! empty($this->config['placeholder'])) {
            $view->placeholder = $this->config['placeholder'];
        }
        // If placeholder is defined - add it into view
        if (isset($this->config['placeholder_confirm']) and ! empty($this->config['placeholder_confirm'])) {
            $view->placeholder_confirm = $this->config['placeholder_confirm'];
        }

        return $view;
    }
}