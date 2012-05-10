<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Tento kontroler zajistuje zobrazeni uvodni stranky pro prihlaseneho uzivatele.
 *
 * @author: Jiri Melichar
 */
class Controller_Dashboard extends Controller_Layout {

    /**
     * Generuje obsah domaci stranky, tzv. "dashboard".
     */
    public function action_index()
    {
        $dashboard = View::factory('dashboard');

        $this->template->content = $dashboard;
    }
}