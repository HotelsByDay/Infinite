<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Tento kontroler zajistuje zobrazeni domovske stranky v administraci.
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

            // Session flash message
            $this->template->flash_message = Session::instance()->flash();
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

    public function sendJson(array $data)
    {
        $this->auto_render = false;
        $this->request->sendJson($data);
    }

    /**
     * Zasle predany pohled/string jako PDF do prohlizece - s uplatnenim aktualniho CSS
     * Dokumentace mPDF: http://mpdf1.com/manual/index.php
     * @param string $view - pohled nebo string ktery se prevede na pdf, pokud se nezada, pouzije se $this->view
     * @param string $filename - nazev pod kterym bude pdf zaslano
     * @param string|array $css - css ktere bude predano do pdf generatoru
     */
    protected $mpdf = null;
    protected function sendPdf($view=NULL, $filename=NULL, $css=NULL, $inline=false)
    {
        $this->auto_render = false;

        // Pokud neni zadan pohled, pouzije se $this->view
        if (empty($view)) {
            $view = $this->view;
        }

        // Pokud neni zadan nazev souboru, pouzije se timestamp
        if (empty($filename)) {
            $filename = date('Y-m-d H:i:s').'.pdf';
        }

        if (substr($filename, -3, 3) != 'pdf') {
            $filename .= '.pdf';
        }

        // vlozeni souboru
        $this->mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8']);
        $this->mpdf->SetDisplayMode('fullpage');

        // Zapiseme pripadne CSS
        if ( ! empty($css)) {
            if ( ! is_array($css)) {
                $css = array((string)$css);
            }

            foreach($css as $style) {
                $this->mpdf->WriteHTML($style, 1);
            }
        }

        $this->mpdf->WriteHTML((string) $view, 2);

        // Clear output buffer
        ob_end_clean();

        // Send proper headers
        $this->headers['Content-Type'] = 'application/pdf';

        // Get binary PDF data
        $data = $this->mpdf->Output('', 'S');

        // Send response
        $this->request->response = $data;
        $this->request->send_file(TRUE, $filename, Array('inline'=>$inline));
    }

}