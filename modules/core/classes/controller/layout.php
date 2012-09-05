<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Tento kontroler zajistuje zobrazeni domovske stranky v administraci.
 *
 * 
 *
 * @author: Jiri Melichar
 */
abstract class Controller_Layout extends Controller_AuthTemplate {

    /**
     * Sablona, ktera bude nactena pro vygenerovani login stranky
     * @var <string>
     */
    public $template = 'base/layout';

    /**
     * Nazev konfiguracniho souboru podle ktereho se bude generovat hlavni menu.
     * @var <string>
     */
    protected $menu_config = '_main_menu';

    /**
     * Nazev aktivniho objektu. Dedici kontrolery by mely definovat.
     * @var <string>
     */
    protected $object_name = '';

    /**
     * Zajistuje zavolani parent konstruktoru
     * @TODO: Jak zajistit vystup profileru do stranky ?
     */
    public function before()
    {
        $retval = parent::before();

        //pokud je aktivni funkce fulltextoveho vyhledavani, tak vlozim do sablony i formular
        //Formular se zamerne do sablony vklada v metode before, tak aby bylo mozne v prubehu
        //stranky do nej vlozit defaultni filtrovaci retezec
        if (AppConfig::instance()->get('fulltext', 'application'))
        {
            $this->template->fulltext_form = View::factory('fulltext_form');
        }

        return $retval;
    }

    public function after()
    {        
        //pokud se zpracovava ajaxovy pozadavek, tak se nebudou do sablony vkladat
        //zadne zakladni polozky
        if ( ! Request::$is_ajax && $this->template != NULL)
        {
            //do stranky vlozim hodnotu title
            $this->template->header_title = Web::instance()->getPageTitle();

            //vlozim paticku stranky
            $this->template->page_footer = View::factory('base/page_footer');

            //navigace vpravo nahore
            $this->template->top_navigation = View::factory('base/top_navigation');

            //panel napovedy
            //$this->template->top_navigation->global_help_panel = component::GlobalHelpPanel();
            //$this->template->left_navigation = View::factory('left_navigation');
            //navigace mezi poradaci v hlavnim menu
            //navigaci predm nazev aktualniho objektu (pokud nektery z dedicich kontroleru)
            //toto definuje a podle toho se nastavi aktivni polozka v menu
            $this->template->navigation = Menu::instance()->getMenu($this->menu_config);
        }

        //vyvolani globalni udalosti 'system.layout_after'
        Dispatcher::instance()->trigger_event('system.layout_after_post', Dispatcher::event(array('controller' => $this)));

        //uplne na zaver pridam JS soubory do stranky - je to proto ze v udalosti
        //'system.layout_after_post' mohou byt pridany dalsi JS soubory do stranky
        if ( ! Request::$is_ajax && $this->template != NULL)
        {
            //do stranky vlozim JS soubory
            $this->template->js_files_include = Web::instance()->getJSFiles();
        }
                
        return parent::after();
    }

}