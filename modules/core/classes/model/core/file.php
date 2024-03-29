<?php defined('SYSPATH') or die('No direct access allowed.');


/**
 * @TODO: Pridat hromadne mazani souboru pri pouziti metody delete_all()
 */
abstract class Model_Core_File extends ORM
{
    const FILE_TYPE_FILE = 'file';
    const FILE_TYPE_IMAGE = 'image';

    static public $allowed_image_dimension = array();

    /**
     *
     * @var <array> V tomto poli jsou definovane resize varianty, ktere budou
     * automaticky po ulozeni vytvoreny (tj. originalni soubor bude ulozen
     * a pomoci knihovny Image budou vytvoreny kopie sjinou velikosti obrazku.
     *
     * Resize varianty se vytvareji v metode createResizedVariants, ktera je
     * volana po uspesnem ulozeni modelu.
     *
     * Definice ma vypadat napr. takto:
     *
     * $resize_variants => array(
     *  'large' => array(1000, 1000, Image::None),
     *  'thumbnail' => array(50, 50, Image::Height),
     * )
     *
     */
    protected $resize_variants = array();

    /**
     *
     * @var <array> V tomto poli jsou definovane crop varianty, ktere budou
     * automaticky po ulozeni vytvoreny (tj. originalni soubor bude ulozen
     * a pomoci knihovny Image budou vytvoreny kopie sjinou velikosti obrazku.
     *
     * Resize varianty se vytvareji v metode createCroppedVariants, ktera je
     * volana po uspesnem ulozeni modelu.
     *
     * Definice ma vypadat napr. takto:
     *
     * $crop_variants => array(
     *  'large' => array(1000, 1000, 5, 5), //vyrez 1000x1000px zacinajici na pixelu [5,5]
     *  'thumbnail' => array(50, 50, 0, 0), //vyrez 50x50px zacinajici na pixelu [0,0]
     * )
     *
     */
    protected $crop_variants = array();

    /**
     * Pri odstraneni souboru chci skutecne odstranit zaznam z DB vcetne souboru
     * na disku.
     */
    protected $update_on_delete = FALSE;

    /**
     * Kazdy zaznam v tabulce advert_photo ma prave jednu fotografii, ktera je ulozena
     * v adresari s nazvem, ktery odpovida hodnote PK. Navic tento adresar je zanoren
     * v jinem adresari, jehoz nazev je spocitan takto:
     * (int)(primary_key_value / folder_count-limit). Coz zajisti ze nedojde k
     * prekroceni na limit poctu adresaru v adresari. Tedy napriklad:
     * "advert_photos/2/9874/file.png" nebo
     * "advert_photos/1/1234/picture.png"
     */
    protected $folder_count_limit = 5000;

    /**
     * V pripade vytvareni kopie zaznamu se bude kopirovat tento soubor
     * v metode save - z nej ak budou standardne vytvoreny resize varianty
     * dle konfigurace modelu.
     * @var <string>
     */
    protected $_copy_source_filepath = NULL;

    protected $_temp_file = NULL;


    /**
     * Returns type of files managed by the model.
     * Used to determine if images are managed to save their width & height
     */
    public function file_type()
    {
        // @todo - determine file type based on it's extension
        return static::FILE_TYPE_FILE;
    }

    public function pk()
    {
        //podminka prevzata z bazoveho ORM - pokud tam pouziju $this->loaded() tak dojde k zacykleni
        if ($this->_temp_file != NULL)
        {
            return $this->_temp_file->pk();
        }
        return parent::pk();
    }

    /**
     * Tato metoda slouzi k rozliseni jiz radne ulozeneho souboru a docasneho
     * souboru, ktery se nachazi v temp adresari a nebyl jeste prirazen konkretnimu
     * modelu v DB.
     * 
     * @return <bool> Vraci TRUE pokud se jedna o docasny soubor. V opavnem pripade
     * vraci FALSE.
     */
    public function IsTempFile()
    {
        return $this->_temp_file != NULL;
    }

    /**
     * Metoda ocekava jako parametr instanci tridy Model_File, ktera reprezentuje
     * soubor a podle tohoto modelu inicializuje aktualni isntanci modelu.
     * Tzn. okopiruje hodnoty spolecnych atributu a provede kopii souboru.
     * @param Model_File $file
     *
     * @chainable
     */
    public function initByTempFile(Model_TempFile $file)
    {
        //nactu hodnoty ze zdrojoveho modelu
        $this->values($file->as_array());

        //nazev souboru na disku odpovida 'nicename' - coz je webalized puvodni nazev souboru
        $this->filename = $file->nicename;

        //ulozim si referenci na instanci Model_File tridy, ktera inicializovala
        //tento model abych po ulozeni zaznamu mohl prekopirovat i prislusne
        //soubory na disku (po ulozeni protoze jeste nemusi znat PK tohoto zaznamu)
        $this->_temp_file = $file;

        //zachovam moznost retezeni metod
        return $this;
    }

    /**
     *
     * @param <string> $filepath Absolutni cesta ke zdrojovemu souboru. Z tohoto
     * souboru vznikne prislusny zaznam v DB.
     * @param <string> $nicename Nicename souboru - pokud neni definovan, tak
     * se vezme nazev souboru bez pripony.
     *
     *
     * @chainable
     */
    public function initByFile($filepath, $nicename = NULL)
    {
        //pri ukladani souboru ze veme tento soubor jako zdrojovy
        //a bude nakopirovan na prislusne misto v datovem ulozisti
        $this->_copy_source_filepath = $filepath;

        //tohle zjisti informace o nazvu souboru, adresari apod.
        $pathinfo = pathinfo($filepath);

        //nazev souboru s priponou
        $this->nicename =
        $this->filename = $nicename ? $nicename : arr::get($pathinfo, 'basename');

        return $this;
    }

    /**
     * Vraci originalni nazev souboru - nazev ktery prisel od uzivatele.
     * @return <string>
     */
    public function getOriginalFilename()
    {
        return $this->nicename;
    }

    /**
     * Vraci nazev souboru tak jak je zapsan na disku.
     * @return <string>
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Vraci hodnotu primarniho klice zaznamu.
     * @return <int>
     */
    public function getID()
    {
        return $this->pk();
    }

    /**
     * Metoda vraci typ Mime typ souboru.
     * Mime typ zjistuje pomoci metody mime tridy File, ktera patri do jadra
     * Kohany.
     *
     * @return <string>
     */
    public function getFileType()
    {
        return File::mime($this->getFileDiskName());
    }

    /**
     * Vraci URL k obrazku, ktery reprezentuje type daneho souboru.
     *
     * Je to ikona, ktera popisuje dany mime typ nebo v pripade obrazku to je
     * primo zmensenina daneho obrazku.
     *
     * @param <string> $resize_variant V pripade ze se jedna o obrazek, tak
     * je jako ikona zobrazen vlastni obrazek a timto argumentem se predava
     * pozadovana resize varianta. V pripade ze neni definovan, tak bude
     * vracena URL pro plnou verzi
     * @return <string> Vraci URL k obrazku, ktery reprezentuje dany soubor.
     */
    public function getFileTypeIcon($resize_variant = NULL)
    {
        $image_name = '';

        //mimetype si rozdelim na kategorie a typ
        list($category, $type) = explode('/', $this->getFileType());

        //pokud nezabere zadna vetev ve switch, tak se puzije tento defaultni
        //obrazek pro zobrazeni typu souboru
        $image_name = 'unknown';

        switch ($category)
        {
            case 'application':
            break;

            case 'audio':
            break;

            case 'image':
                //v pripade obrazku bude zobrazena zmensenina daneho obrazku
                return appurl::object_file($this, $resize_variant);
            break;

            case 'message':
            break;

            case 'model':
            break;

            case 'multipart':
            break;

            case 'text':
                switch ($type)
                {
                    case 'csv':
                        $image_name = 'image_csv';
                    break;
                }
            break;

            case 'video':
            break;

            case 'vnd':
            break;
        }

        return url::base().'css/images/mime/'.$image_name.'.png';
    }

    /**
     * Vraci velikost souboru, ktery tento model reprezentuje.
     * @return <string>
     */
    public function getFileSize($formatted=true)
    {
        if ( ! empty($this->filesize)) {
            $file_size = $this->filesize;
        } else {
            // kontrola existence souboru - pokud neexistuje vracime 0
            if (!is_file($this->getFileDiskName())) {
                return 0;
            }
            //pokud ma vice nez 1 MB tak zobrazim velikost v MB, jinak v KB
            $file_size = filesize($this->getFileDiskName());
        }
        return $formatted ? Format::fileSize($file_size) : $file_size;
    }

    /**
     * Vraci realnou cestu k souboru na disku.
     * @return <string>
     */
    public function getFileDiskName($resize_variant = NULL)
    {
        if ($this->_temp_file != NULL)
        {
            return $this->_temp_file->getFileDiskName();
        }

        // Not a tempfile - use filename from the model
        $filename = $this->filename;

        // If resize variant is properly defined - use resize variant diskname
        $size = arr::get($this->resize_variants, $resize_variant);
        if ( ! empty($size) and is_array($size) and isset($size[0], $size[1])) {
            $resize_type = arr::get($size, 2, 'auto');
            $filename = Format::imageExactResizeVariantName($filename, $size[0], $size[1], $resize_type);
        }
        // Return final path
        return $this->getDirName().'/'.$filename;
    }

    /**
     * Metoda vraci relativni URL k danemu souboru.
     * @return <string>
     */
    public function getURL($resize_variant=NULL)
    {
//        if ( ! $this->loaded()) return '';
        return url::base() . $this->getFileDiskName($resize_variant);
    }

    /**
     * Vraci nazev adresare ve kterem se nachazi tento soubor.
     * Kazdy specificky model musi tuto metodu implementovat.
     * @return <type>
     */
    protected function getDirName()
    {
        //soubory jsou ulozene v temp adresari
        $target_dir = AppConfig::instance()->get('data_storage', 'system');

        //cestu doplnim
        $target_dir .= '/'.($this->_db.'-'.$this->_object_name);

        $target_dir .= '/'.(int)($this->pk() / $this->folder_count_limit);

        $target_dir .= '/'.$this->pk();

        // Tohle je pouzito zaroven pro generovani URl, takze nemuzeme docroot pridat vzdy
        return (Kohana::$environment === Kohana::TESTING) ? DOCROOT.$target_dir : $target_dir;
    }


    public function getExactVariantDiskName($width, $height)
    {
        return $this->getDirName().'/'.Format::imageExactResizeVariantName($this->filename, $width, $height);
    }


    /**
     * Vytvori resize variantu s exaktne zadanym rozmerem a pojmenuje ji jako
     * <filename>_<width>x<height>.<ext>
     * @param $width
     * @param $height
     */
    public function createExactResizeAndCroppedVariant($width, $height, $resize_type=NULL)
    {
        // nazev souboru na disku
        $filepath = $this->getFileDiskName();
        $target_filepath = $this->getExactVariantDiskName($width, $height);

        //pokud uz varianta existuje, tak ji nebudu znovu vytvaret
        if (file_exists($target_filepath))
        {
            return true;
        }

        //pri vytvareni resize varianty muze dojit k chybam
        try
        {
            //jinak ji vytvorim
            $image = Image::factory(DOCROOT.$filepath);

            // Set transparent background
            $image->background('#fff', 0);

            //provede vlastni resize obrazku - pote se jeste vyrizne stred
            $image->resize($width, $height, Image::INVERSE);

            // Crop exact rectangle from the centre of the image
            $image->crop($width, $height);

            //pred vlastni nazev souboru vlozim prefix - nazev resize varianty
            $image->save($target_filepath, 80);
        }
        catch (Exception $e)
        {
            //chybu zaloguju a pokracuje se ve vytvareni dalsich resize variant
            Kohana::$log->add(Kohana::ERROR,
                'Unable to create resize variant ":variant_name" with target path ":target_path" due to "'.$e->getMessage().'".',
                array(':target_path' => $target_filepath));
            throw $e;
            return false;
        }
        return true;
    }



    /**
     * Vytvorit cropped varianty obrazku podle $cropped_variants atributu.
     *
     * Nove vznikle soubory automaticky ulozi "vedle" originalniho souboru.
     * Cropped varianta ma prefix podle nazvu cropped varianty, pak nasleudje
     * znak '-' a zbytek nazvu souboru zustava stejny.
     *
     * Pokud cropped varianta s danym nazvem jiz existuje (kontroluje pomoci
     * file_exists), tak nevytvari novou.
     *
     */
    protected function createCroppedVariants()
    {
        //nazev souboru na disku
        $filepath = $this->getFileDiskName();

        //nazev samotneho souboru
        $filename = basename($filepath);

        //cesta k adresari
        $filedir  = dirname($filepath);

        //pro kazdou variantu vytvorim dalsi soubor na disku
        foreach ($this->crop_variants as $variant_name => $variant_setting)
        {
            //cilovy nazev souboru ve variante
            $target_filepath = DOCROOT.$filedir . DIRECTORY_SEPARATOR . $variant_name . '-' . $filename;

            //pokud uz varianta existuje, tak ji nebudu znovu vytvaret
            if (file_exists($target_filepath))
            {
                continue;
            }

            //pri vytvareni cropped varianty muze dojit k chybam
            try
            {
                //jinak ji vytvorim
                $image = Image::factory(DOCROOT.$filepath);

                //pokud jsou oba rozmery nedefinovane, tak se crop obrazku neprovede
                if ( ! empty($variant_setting[0]) && ! empty($variant_setting[1]))
                {
                    //provede vlastni crop obrazku
                    $image->crop(arr::get($variant_setting, 0),
                        arr::get($variant_setting, 1),
                        arr::get($variant_setting, 2),
                        arr::get($variant_setting, 3));
                }

                //pred ulozenim cropped varianty se vyvola metoda, ktera muze byt
                //pretizena v dedicich modelech a umozni udelat dalsi modifikace obrazku
                $this->cropVariant($variant_name, $image);

                //pred vlastni nazev souboru vlozim prefix - nazev crop varianty
                $image->save($target_filepath);
            }
            catch (Exception $e)
            {
                //chybu zaloguju a pokracuje se ve vytvareni dalsich crop variant
                Kohana::$log->add(Kohana::ERROR,
                    'Unable to create crop variant ":variant_name" with target path ":target_path" due to "'.$e->getMessage().'".',
                    array(':variant_name' => $variant_name, ':target_path' => $target_filepath));
            }
        }
    }

    /**
     * Tato metoda je vyvolana pred ulozenim kazde resize varianty obrazku.
     * @param <string> $variant_name Nazev vytvarene resize varianty.
     * @param Image $image
     */
    protected function resizeVariant($variant_name, Image $image)
    {
        
    }

    /**
     * Tato metoda je vyvolana pred ulozenim kazde crop varianty obrazku.
     * @param <string> $variant_name Nazev vytvarene crop varianty.
     * @param Image $image
     */
    protected function cropVariant($variant_name, Image $image)
    {

    }

    public function delete_all()
    {
        $files = $this->find_all();
        foreach ($files as $file) {
            $file->delete();
        }
    }


    /**
     * Metoda kontroluje hodnotu atributu $this->update_on_delete <bool> ktera
     * rozhoduje zda maji byt zaznamy fyzicky z tabulky odstraneny anebo pouze
     * aktualizovany (standardne se atribut 'deleted' nastavuje na hodnotu '1').
     *
     * @param <type> $id
     * @return ORM
     */
    public function delete($id = NULL, array $plan = array())
    {
        if ($id === NULL)
	    {
            // Use the the primary key value
            $id = $this->pk();
	    }

        //tato metoda provede standardni odstraneni zaznamu z DB
        $retval = parent::delete($id, $plan);

        if ( ! empty($id) OR $id === '0')
        {
            //v pripade update_on_delete pocitam s tim ze muze uzivatel
            //zaznam z aplikace obnovit a nebudu tedy mazat soubory na disku
            if ( ! $this->update_on_delete)
            {
                //zaznam bude z DB smazan - muzu bezpecne smazat vsechny soubory
                //k tomuto zaznamu
                //cesta k zakladni variante souboru
                $filepath = $this->getFileDiskName();

                //vymazu cely obsah adresare - muzou tam byt napr. resize varianty fotek
                $dirname = dirname($filepath);

                //pokud existuje adresar, tak se pokusim smazat nejdrive jeho obsah
                //a pak i samotny adresar
                if (file_exists($dirname))
                {
                    foreach ((array)glob($dirname.DIRECTORY_SEPARATOR.'*') as $file)
                    {
                        unlink(DOCROOT.$file);
                    }

                    //smazu samotny (prazdny) adresar
                    rmdir(DOCROOT.$dirname);
                }
            }
        }

        return $retval;
    }

    /**
     *
     * @param <bool> $resize Definuje zda maji byt po ulozeni ORM vytvoreny
     * i resize varianty obrazku. Pri prvnim ulozeni souboru se tento argument
     * v dedicim modelu nastavuje na false, protoze pred ulozenim souboru do
     * prislusneho adresare na disk musi byt ORM model ulozen (protoze v ceste
     * k obrazku je hodnota PK) a pak je nutne resize varianty vytvorit az po
     * ulozeni souboru na disk (coz je po ulozeni ORM).
     * 
     * @return Model_File 
     */
    public function save()
    {
        //pokud je tento model inicializovan z tempfile modelu, tak si pripravim
        //soubor, ktery tempfile modelu patri a ten se po ulozeni tohoto modelu
        //nakopiruje na prislusne misto
        if ($this->_temp_file != NULL)
        {
            $this->_copy_source_filepath = $this->_temp_file->getFileDiskName();

            //zrusim referenci na tempfile kterym byl tento model inicializovan
            $this->_temp_file = NULL;
        }

        $retval = parent::save();

        //pokud je definovana cesta k souboru ktery ma byt prirazen tomuto
        //zaznamu, tak vytvorim kopii. Zdrojovy soubor pochazi ze zaznamu,
        //ze ktereho byl tento vytvoren kopii
        if ( ! empty($this->_copy_source_filepath))
        {
            $target_filepath = $this->getFileDiskName();

            $target_filedir = dirname($target_filepath);

            if ( ! file_exists($target_filedir))
            {
                //rekurzivne vytvori adresarovou "cestu"
                mkdir($target_filedir, 0777, TRUE);
            }

            //provede kopii souboru
            copy($this->_copy_source_filepath, $target_filedir . DIRECTORY_SEPARATOR . $this->nicename );
        }

        if ($this->hasAttr('file_size') and empty($this->file_size)) {
            $this->file_size = $this->getFileSize(false);
        }
        if ($this->file_type() == static::FILE_TYPE_IMAGE
            and $this->hasAttr('width')
            and $this->hasAttr('height')
            and (empty($this->width) or empty($this->height))
        ) {
            $img = Image::factory($this->getFileDiskName());
            $this->width = $img->width;
            $this->height = $img->height;
        }
        return parent::save();
    }

    /**
     * Pretezuje standardni metodu copy - pridava jen zapis do atributu
     * $this->_copy_source_filepath - zapisuje tam cestu k souboru, ktery
     * bude patrit nove vytvorene kopii zaznamu.
     * 
     * @param array $plan
     * @param array $overwrite
     * @return <type> 
     */
    public function copy(array $plan, array $overwrite = array())
    {
        //tento souboru bude prirazen nove vytvorene kopii zaznamu
        $this->_copy_source_filepath = DOCROOT.$this->getFileDiskName();

        return parent::copy($plan, $overwrite);
    }

    /**
     * @param ORM $source
     * @param array $overwrite
     * @return ORM
     * TODO: Dopsat dokumentaci!
     */
    public function copyFrom(ORM $source, array $overwrite = array())
    {
        //tento souboru bude prirazen nove vytvorene kopii zaznamu
        $this->_copy_source_filepath = DOCROOT.$source->getFileDiskName();

        return parent::copyFrom($source, $overwrite);
    }

    public function checkNeedsWebP($fileDiskName) {
        $gd = gd_info();
        if(isset($_SERVER['HTTP_ACCEPT']) && strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false && isset($gd['WebP Support']) && $gd['WebP Support']) {
            $extension = pathinfo(DOCROOT . $fileDiskName, PATHINFO_EXTENSION);

            if(file_exists(DOCROOT . $fileDiskName) && in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $webpFileDiskName = str_replace(['_data', '.' . $extension], ['_data/_webp', '.webp'], $fileDiskName);
                if ( ! file_exists(DOCROOT . $webpFileDiskName) || (filemtime(DOCROOT . $fileDiskName) > filemtime(DOCROOT . $webpFileDiskName))){
                    if ( ! file_exists(dirname(DOCROOT . $webpFileDiskName))){
                        @mkdir(dirname(DOCROOT . $webpFileDiskName), 0770, true);
                    }

                    $image_webp = new ImageTool(DOCROOT . $fileDiskName);
                    $image_webp->save(DOCROOT . $webpFileDiskName, 100, 'webp');
                    $fileDiskName = $webpFileDiskName;
                }else{
                    $fileDiskName = $webpFileDiskName;
                }
            }
        }

        return $fileDiskName;
    }
}
