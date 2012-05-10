<?php defined('SYSPATH') or die('No direct access allowed.');


/**
 *
 */
class Model_Comment_Attachement extends Model_File {

    protected $_belongs_to = array(
        'comment' => array('model' => 'comment', 'foreign_key' => 'commentid')
    );

    /**
     * Definuje ze pri mazani zaznamu ma dojit pouze k aktualizaci a nastaveni
     * atributu 'deleted' na hodnotu '1' namisto skutecneho odstraneni z tabulky.
     * @var <bool>
     */
    protected $update_on_delete = FALSE;

    /**
     * Kazdy zaznam v tabulce estateagency_photo ma prave jednu fotografii, ktera je ulozena
     * v adresari s nazvem, ktery odpovida hodnote PK. Navic tento adresar je zanoren
     * v jinem adresari, jehoz nazev je spocitan takto:
     * (int)(primary_key_value / folder_count-limit). Coz zajisti ze nedojde k
     * prekroceni na limit poctu adresaru v adresari. Tedy napriklad:
     * "estateagency_photos/2/9874/file.png" nebo
     * "estateagency_photos/1/1234/picture.png"
     */
    protected $folder_count_limit = 5000;

    /**
     * Maximalni mozna velikost souboru Bytech
     * @var <int>
     */
    static public $allowed_max_filesize = 5242880; //5*1024*1024 = 5MB

    /**
     * Povolene jsou vsechny typy souboru
     */
    static public $allowed_mime_types = array();

    /**
     * Tento atribut slouzi k definici nazvu objektu od ktereho ma byt dedeno
     * opravneni.
     * Vysvetleni na prikladu: Mame napr. objekt "estateagency" (nabidky) a "estateagencyphoto"
     * (fotky k nabidkam). Uzivatel ma urcite opravneni na objekt "estateagency" a stejne
     * opravneni ma mit i na objekt "estateagencyphoto" a toho docilime prave natavenim
     * tohoto atributu v ORM Model_EstateAgencyPhoto na hodnotu "estateagency".
     * Jinak by bylo potreba v konfiguraci opravneni definovat explicitne opravneni
     * na objekt "estateagencyphoto", ktere by bylo stejne jako opravneni pro objekt
     * "estateagency".
     */
    protected $_inherit_permission = 'comment';

    /**
     * Vraci nazev adresare ve kterem se nachazi tento soubor.
     * @return <string>
     */
    protected function getDirName()
    {
        //soubory jsou ulozene v temp adresari
        $target_dir = AppConfig::instance()->get('data_storage', 'system');

        //cestu doplnim
        $target_dir .= '/'.$this->_object_name;

        //pokud neexistuje tak vytvorim
        if ( ! file_exists($target_dir))
        {
            mkdir($target_dir);
        }

        $target_dir .= '/'.(int)($this->pk() / $this->folder_count_limit);

        //pokud neexistuje tak vytvorim
        if ( ! file_exists($target_dir))
        {
            mkdir($target_dir);
        }

        $target_dir .= '/'.$this->pk();

        //pokud neexistuje tak vytvorim
        if ( ! file_exists($target_dir))
        {
            mkdir($target_dir);
        }

        return $target_dir;
    }



}