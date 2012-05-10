<?php

/**
 * Tato trida slouzi jako rozhrani pro stazeni JavaScript souboru z cache.
 */
class Controller_Web extends Controller {

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
