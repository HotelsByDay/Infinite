<?php defined('SYSPATH') or die('No direct access allowed.');

class Controller_Error extends Controller_Template
{

    /**
     * Error template - jednoducha sablona s inline styly.
     */
    public $template = 'error/template';

    /**
     * Akce zbrazuje chybovou stranku se zpravou o tom ze ma uzivatel
     * nekompatibilni prohlizec.
     */
    public function action_incombatible_browser()
    {
        $this->template->user_title   = __('error.incombatible_browser_title');
        $this->template->user_message = __('error.incombatible_browser_message');
        //nechci zobrazit zpravu "Znovu se prihlaste..."
        $this->template->show_reloggin_message = FALSE;
    }

    public function action_404()
    {
        $this->template->user_title = __('error.404.title');
        $this->template->user_message = __('error.404.message');
    }

    public function action_500()
    {
        $this->template->user_title = __('error.500.title');
        $this->template->user_message = __('error.500.message');
    }

    /**
     * Metoda slouzi k nahlaseni JS chyb ke kterym dojde u klient.
     */
    public function action_js()
    {
        $this->template = NULL;

        //parametry popisujici chybu
        $msg  = arr::get($_POST, 'msg');
        $url  = arr::get($_POST, 'url');
        $line = arr::get($_POST, 'line');

        //zalogovani do sys logu
        Kohana::$log->add(Kohana::ERROR, 'JS: '.$msg.' ['.$url.':'.$line.']');
    }
}