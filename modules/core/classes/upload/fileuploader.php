<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Tato trida zajistuje zapisu uploadovanych souboru do adresare pro docasne soubory
 * a pro kazdy takovy souboru vytvori prislusny TempFile model.
 */
class Upload_FileUploader
{
    //Uploadovany soubor se kterym pracujeme (jedna se o instanci tridy X nebo Y)
    protected $file;

    //zde se ulozi maximalni mozna velikost souboru - ta se bude kontrolovat
    protected $size_limit = NULL;

    //zde se uloz vycet povolenych MIME-TYPU souboru - bude se kontrolovat
    protected $allowed_mimetypes = array();

    // zde se ulozi rozmezi povolenych rozmeru ve form Sirka-od, Sirka-do, Vyska-od, Vyska-do
    protected $image_dimension_limit = array();

    /**
     *
     * @param <type> $file Instance tridy Upload_Driver_Form nebo Upload_Driver_Xhr
     * reprezentujici uploadovany souboru
     * @param <array> $allowed_mimetypes Vycet povolenych MIME-TYPU pro soubor
     * @param <int> $size_limit Maximalni povolena velikost souboru v Bytech
     */
    public function __construct($file, $allowed_mimetypes, $size_limit, $image_dimension_limit = array())
    {
        //tridu, ktera obaluje jeste neulozeny soubor si ulozim
        $this->file = $file;

        //ulozim si limit na velikost souboru
        $this->size_limit = $size_limit;

        //limit na povolene mimetypy
        $this->allowed_mimetypes = $allowed_mimetypes;

        //ulozim si limit na velikost obrazku
        $this->image_dimension_limit = $image_dimension_limit;
    }

    /**
     * Provadi kontrolu MIME-TYPU souboru a jeho velikosti. Pokud kontroly projdou
     * tak soubor zapise do adresare pro docasne soubory a vytvori prislusny
     * TempFile model pro jeho reprezentaci.
     *
     * @param <string> $temp_dir Adresar pro ulozeni docasnych souboru
     * @return <Model_TempFile> Instanci modelu reprezentujici dany soubor.
     *
     * @throws Upload_Exception_InternalError V pripade ze dojde k interni chybe.
     * @throws Upload_Exception_UserError Pokud neprojde jedna z kontrol na MIME-TYPE
     * nebo velikost souboru.
     */
    public function saveUploadedFile($temp_dir)
    {
        //zkontrokuju zda je mozne do adresare pro docasne soubory zapsat
        if ( ! is_writable($temp_dir))
        {
            throw new Upload_Exception_InternalError(__('upload.error.temp_not_writeable'), array(), 1);
        }

        //soubor se kterym se ma pracovat nemusi byt definovan
        if ( ! $this->file)
        {
            throw new Upload_Exception_InternalError(__('upload.error.file_not_defined'), array(), 2);
        }

        //ted budu kontrolovat velikost
        $size = $this->file->getSize();

        //nulova velikost znamena prazdny soubor - takovy neakceptuju
        if ($size == 0)
        {
            throw new Upload_Exception_InternalError(__('upload.error.file_empty'), array(), 3);
        }

        //pokud je soubor vetsi nez max. limit tak taky neakceptujeme
        if ($size > $this->size_limit)
        {
            throw new Upload_Exception_UserError(__('upload.error.file_too_large'), array(':file_max_size' => text::bytes($this->size_limit), ':file_size' => text::bytes($size)), 4);
        }

        //ted soubor zapisu do temp adresare

        //vytahnu si nazev souboru
        $path_info = pathinfo($this->file->getName());
        //z nazvu souboru chci odstranit vsechny znaky, ktere by mohli delat problemy
        //v URL nebo na disku
        $file_name =
        $orig_file_name = text::webalize($path_info['filename']);

        //pripona souboru
        $file_ext  = $path_info['extension'];

        //pokud je v temp adresari soubor se stejnym jmenem tak se vygeneruje novy nazev
        while (file_exists($temp_dir . '/' . $file_name . '.' . $file_ext)) {
            $file_name .= rand(10, 99);
        }
        //mam nazev souboru, ktery tam jeste neexistuje
        $filepath = $temp_dir . '/' . $file_name . '.' . $file_ext;

        //pokus o zapis na disk
        if ($this->file->save($filepath))
        {
            //soubor sem zapsal do temp adresare - zkontroluju mime typ
            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            $file_mimetype = strtolower(finfo_file($finfo, $filepath));

            Kohana::$log->add(Kohana::INFO, 'File mime: '.$file_mimetype.'  allowed types: '.json_encode($this->allowed_mimetypes));

            finfo_close($finfo);

            //pokud se nejedna o jeden z povolenych typu tak vyhodim vyjimku
            if ( ! empty($this->allowed_mimetypes) && ! in_array($file_mimetype, $this->allowed_mimetypes))
            {
                throw new Upload_Exception_UserError(__('upload.error.not_allowed_file_type'));
            }

            //kontrola na povolene rozmery obrazku
            if ( ! empty($this->image_dimension_limit))
            {
                $image = Image::Factory($filepath);

                //min-sirka
                if (($min_width = arr::get($this->image_dimension_limit, 0)))
                {
                    if ($image->width < $min_width)
                    {
                        throw new Upload_Exception_UserError(__('upload.error.invalid_image_dimension.min_width', array(
                            ':width'     => $image->width,
                            ':min_width' => $min_width
                        )));
                    }
                }
                //max-sirka
                if (($max_width = arr::get($this->image_dimension_limit, 1)))
                {
                    if ($image->width > $max_width)
                    {
                        throw new Upload_Exception_UserError(__('upload.error.invalid_image_dimension.max_width', array(
                            ':width'     => $image->width,
                            ':max_width' => $max_width
                        )));
                    }
                }
                //min-vyska
                if (($min_height = arr::get($this->image_dimension_limit, 2)))
                {
                    if ($image->height < $min_height)
                    {
                        throw new Upload_Exception_UserError(__('upload.error.invalid_image_dimension.min_height', array(
                            ':height'     => $image->height,
                            ':min_height' => $min_height
                        )));
                    }
                }
                //max-vyska
                if (($max_height = arr::get($this->image_dimension_limit, 3)))
                {
                    if ($image->height > $max_height)
                    {
                        throw new Upload_Exception_UserError(__('upload.error.invalid_image_dimension.max_height', array(
                            ':height'     => $image->height,
                            ':max_height' => $max_height
                        )));
                    }
                }
            }


            //pokud se soubor podarilo nahrat uspesne tak vytvorim prislusny ORM model
            $temp_file = ORM::factory('TempFile');
            //pod timto nazvem bude soubor ulozen do ciloveho umisteni (_data/...)
            $temp_file->nicename = $orig_file_name . '.' . $file_ext;
            //pod timto nazvem bude ulozen do temp adresare
            $temp_file->filename = $file_name . '.' . $file_ext;
            //zapisu do DB
            return $temp_file->save();
        }
        else
        {
            //nepovedlo se - hodim vyjimku
            throw new Upload_Exception_InternalError('Unable to write file to "'.$filepath.'"');
        }
    }
}