<?php defined('SYSPATH') or die('No direct access allowed.');


/**
 * 
 */
class Model_TempFile extends Model_File {

    /**
     * Nazev DB tabulky nad kterou stoji tento model.
     * @var <string>
     */
    protected $_table_name = 'temp_file';


    public function file_type()
    {
        return static::FILE_TYPE_FILE;
    }


    /**
     * Tento model reprezentuje pouze docasne soubory.
     *
     * @return <bool> Vraci vzdy FALSE, protoze tento soubor reprezentuje
     * vzdy jen docasne soubory.
     */
    public function IsTempFile()
    {
        return TRUE;
    }

    /**
     * Vraci nazev adresare ve kterem se nachazi tento soubor.
     * @return <string>
     */
    protected function getDirName()
    {
        //soubory jsou ulozene v temp adresari
        $temp_dir = AppConfig::instance()->get('temp_dir', 'system');

        return (Kohana::$environment === Kohana::TESTING) ? DOCROOT.$temp_dir : $temp_dir;
    }

   /**
     * Hlavni ucel teto metody je zkontrolovat zda ma uzivatel opravneni
     * pro vlozeni noveho zaznamu a pripadne vyvolat metodu, ktera provede
     * aplikaci modifikatoru opravneni.
     */
    protected function applyUserInsertPermission()
    {
        return TRUE;
    }


    /**
     * Hlavni ucel teto metody je zkontrolovat zda ma uzivatel opravneni
     * pro odstranovani zaznamu a pripadne vyvolat metody, ktera provede
     * aplikaci modifikatoru opravneni pro odstranivani (db_delete).
     */
    protected function applyUserUpdatePermission()
    {
        return TRUE;
    }

    /**
     * Hlavni ucel teto metody je zkontrolovat zda ma uzivatel opravneni
     * pro odstranovani zaznamu a pripadne vyvolat metody, ktera provede
     * aplikaci modifikatoru opravneni pro odstranivani (db_delete).
     */
    protected function applyUserDeletePermission()
    {
        return TRUE;
    }

    /**
     * Tato metoda je volane vzdy pred ctenim z DB tabulky prislusneho modelu
     * a zajistuje nastaveni filtrovacich podminek podle nastaveni opravneni
     * aktualne prihlaseneho uzivatele.
     *
     * Hlavni ucel metody je zkontrolovat zda ma uzivatel vubec opravneni pro
     * cteni na tomto objektu a pripadne vyvolat metodu, ktera zajisti
     * aplikaci modifikatoru opravneni (prida dodatecne filtrovaci podminky).
     */
    protected function applyUserSelectPermission()
    {
        return TRUE;
    }

    /**
     * Tato metoda slouzi k procisteni tabulky pro docasne soubory.
     *
     * Odstrani vsechny zaznamy, ktere jsou starsi nez 48 hodin.
     */
    public function cleanTempTable()
    {
        DB::delete($this->_table_name)
                ->where('created', '<', date('Y-m-d H:i:s', time() - 24 * 3600 * 2))
                ->execute();
    }
}