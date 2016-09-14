<?php

/**
 * Tato trida slouzi jako rozhrani pro stazeni JavaScript souboru z cache.
 */
class Controller_Web extends Controller {


    /**
     * /default-property_image/123/photo_120x100.jpg
     *
     * @param $object_name
     * @param $object_id
     * @param $requested_filename
     */
    public function action_resize_variant($object_name, $object_id, $requested_filename)
    {
        if (substr($object_name, 0, 8) == 'default-') {
            $object_name = substr($object_name, 8);
        }

        try {
            // Process requested filename - it have to contain width and height of the image
            $pattern = '/.+_([0-9]+)x([0-9]+)_?(none|width|height|auto|inverse|)\.[a-zA-Z]{2,5}$/';
            $res = preg_match($pattern, $requested_filename, $matches);
            if ( ! $res or ! in_array(count($matches), array(3, 4))) {
                throw new Exception('Requested filename ('.$requested_filename.') does not match the pattern: '.$pattern);
            }
            // Get width and height
            list($_foo, $width, $height) = $matches;
            $resize_type = arr::get($matches, 3, 'auto');
            // Transform string constant to it's internal value
            $resize_type = constant('Image::' . strtoupper($resize_type));

            // Create image instance
            $image = ORM::factory($object_name);
            // Disable permission checking
            $image::$permissions_enabled = false;
            $image = $image->find($object_id);
            if ( ! $image instanceof Model_File) {
                throw new Exception('Model for given object name ('.$object_name.') is not instance of Model_File class.');
            }
            if ( ! $image->loaded()) {
                throw new Exception('Given object does not exist in database ('.$object_name.' : '.$object_id);
            }

            // Generate resize variant
            $resized = $image->createExactResizeAndCroppedVariant($width, $height, $resize_type);
            if ( ! $resized) {
                throw new Exception('Unable to create resize_variant '.$width.'x'.$height.' ('.$object_name.' : '.$object_id .') ');
            }

            // Get resize variant file diskname and send the file as response
            $filename = $image->getExactVariantDiskName($width, $height, $requested_filename);

            $this->request->send_file($filename, NULL, array('inline' => true));

        } catch (Exception $e) {
            Kohana::$log->add(Kohana::ERROR, 'Unable to create resize_variant: '.$e->getMessage());
            echo "Error while generating image resize variant - {$e->getMessage()}";
        }
    }



    /**
     * Zakladni povinna akce - nedela nic.
     */
    public function action_index()
    {
        
    }

    /**
     * Metoda na vystup generuje obsah zacachovaneho JS souboru.
     */
    public function action_js($cache_key)
    {
        //podivam se zda dany klic v cache existuje
        //zkusim vytahnout data z cache - pokud tam neco najdu tak to rovnou vracim
        $data = Cache::instance()->get($cache_key);

        //spolecne s daty by melo byt ulozena i doba platnosti pro cache prohlizece
        //fallback hodnota je 7 dnu
        $cache_time = arr::get((array)$data, 'maxage', 7*24*60*60);

        //vlastni obsah souboru
        $content = arr::get((array)$data, 'content', '');

        //nastavim hlavicku pro spravnou interpretaci v prohlizeci
        $this->request->headers[] = 'Content-type:text/javascript';

        if ( ! arr::get($data, 'static'))
        {
            $this->request->headers[] = 'Pragma: public';
            $this->request->headers[] = 'Cache-Control: maxage='.($cache_time);
            $this->request->headers[] = 'Expires: ' . gmdate('D, d M Y H:i:s', time()+(3600)) . ' GMT';
        }
        else if (arr::get($data, 'compiled'))
        {
            //dale pridam hlavicky, ktere zajisti cachovani souboru
            $this->request->headers[] = 'Pragma: public';
            $this->request->headers[] = 'Cache-Control: maxage='.($cache_time);
            $this->request->headers[] = 'Expires: ' . gmdate('D, d M Y H:i:s', time()+($cache_time)) . ' GMT';
        }

        //tuto hlavicku pridam vzdy - pro pripad ze by se soubor jakkoli zmenil
        //tak by mel prohlizec zneplatnit svou cache
        $this->request->headers[] = 'Content-Length: '.strlen($content);
        
        //vyechuju obsah souboru, ktery ma byt na klici 'content'
        $this->request->response = $content;
    }
}

?>
