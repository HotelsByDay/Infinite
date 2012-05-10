<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Tento formularovy prvek slouzi k nastaveni vazby na jeden z vice relacnich objektu.
 * Uzivatle si muze vybrat z vice objektu na ktere chce vazbu vytvorit. Samotny vyber
 * relacniho zaznamu je realizovan pomoci naseptavace s fulltextovy hledanim.
 * 
 */
class AppFormItem_Reference extends AppFormItem_Base
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/reference';

    //prvek je virtualni
    protected $virtual = TRUE;

    //seznam objektu na ktere je mozne vytvorit referenci
    protected $rel_objects = array();

    //preview pro typy relacnich objektu mohou byt v konfiguraci explicitne
    //definovane
    protected $rel_objects_preview = array();

    /**
     * Pripojeni potrebnych JS souboru pro AppFormItemRefefence
     * Init je volano v base konstruktoru
     */
    public function init()
    {
        // Pripojime JS soubor s pluginem
        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemReference.js'));
        
        // A jeho inicializaci
        $init_js = View::factory('js/jquery.AppFormItemReference-init.js');
        
        //do inicializacniho souboru vlozim URL pro cteni dat jednotlivych objektu
        $object_data_url = array();

        //explicitne definovane preview pro zobrazeni relacnich objektu
        $rel_objects_preview = array();
        
        foreach (arr::get($this->config, 'objects') as $object_name => $info)
        {
            //relid pro dany objekt (taublku)
            $reltype = lognumber::getTableNumber($object_name);

            //url pro cteni dat daneho objektu
            $object_data_url[$reltype] = appurl::object_cb_data($object_name, arr::get($info, 'filter', array()));

            //tyto hodnoty budou zobrazeny v selectboxu na formulari
            $this->rel_objects[$reltype] = __($object_name.'.menu_name');

            //v konfiguraci muze byt explicitne definovane preview, ktere ma byt
            //pouzito pro relacni zaznam
            $preview = arr::get($info, 'preview', '');

            //pokud je preview relacniho objektu explicitne definovano, tak ho takve
            //vlozim do inicializacniho souboru
            if ( ! empty($preview))
            {
                $this->rel_objects_preview[$reltype] = $preview;
            }
        }

        $init_js->data_url = $object_data_url;
        $init_js->rel_objects_preview = $this->rel_objects_preview;

        //prida do sablony identifikator tohoto prvku a zajisti vlozeni do stranky
        parent::addInitJS($init_js);

        return parent::init();
    }

    /**
     * Nastavi defaultni hodnoty do $this->form_data (precte z ORM modelu) anebo
     * zpracuje hodnoty, ktere prisly z formulare.
     * @return <type>
     */
    public function assignValue()
    {
        if (empty($this->form_data))
        {
            //defaultni reltype bude prvni objekt v nabidce
            $this->form_data['reltype'] = $this->model->reltype;
            $this->form_data['relid']   = $this->model->relid;
        }

        //pokud je definovana hodnota reltype a relid
        if (isset($this->form_data['reltype']) && ! empty($this->form_data['reltype'])
                && isset($this->form_data['relid']) && ! empty($this->form_data['relid']))
        {
            //z reltype ziskam nazev objektu
            $reltype = $this->form_data['reltype'];
            $object_name = lognumber::getTableName($reltype);

            //pokud zaznam neexistuje, tak misto preview dam hlasku a vazbu zanecham
            $model = ORM::factory($object_name, $this->form_data['relid']);

            if ( ! $model->loaded())
            {
                //hlaska o tom ze relacni objekt nenalezen, ale vazbu nebudu rusit
                $this->form_data['relpreview'] = __('appformitemreference.rel_item_not_loaded');
            }
            else
            {
                //preview relacniho objektu (preview je podle aktualni konfigurace formu prvku)
                $this->form_data['relpreview'] = $model->preview(arr::get($this->rel_objects_preview, $reltype, NULL));
            }
        }
        else
        {
            //relace neni nastavena
            $this->form_data['relpreview'] = '';
        }

        return parent::assignValue();
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
        $this->model->relid   = $value['relid'];
        $this->model->reltype = $value['reltype'];
    }


    /**
     *
     * Generuje HTML kod formularoveho prvku
     * navic predava name, vatermark, preview
     *
     * @param <const> $render_style Definuje zpusob zobrazeni formularoveho prvku.
     * Ocekava jednu z konstant AppForm::RENDER_STYLE_*.
     *
     * @param <string> $error_message Definuje validacni chybu, ktera ma byt
     * u prvku zobrazena.
     *
     * @return <View>
     */
    public function Render($render_style = NULL, $error_message = NULL)
    {
        // Zavolame base Render, ktera vytvori pohled a preda zakladni atributy
        $view = parent::Render($render_style, $error_message);

        //seznam objektu na ktere muze uzivatel vytvorit referenci
        $view->rel_objects = $this->rel_objects;

        $view->reltype     = $this->form_data['reltype'];
        $view->relid       = $this->form_data['relid'];
        $view->rel_preview = $this->form_data['relpreview'];

        // Vratime $view
        return $view;
    }
}