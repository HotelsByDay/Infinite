<?php

class LogAction_Object_Base {
    
    /**
     * Seznam sloupců, jejichž hodnoty se mají logovat při editaci záznamu
     *  - definuje se až v odvozených třídách
     * @var <array>
     */
    protected $watch_update = Array();
    
    
    public function updated($orm, $message = NULL, $log_action_categoryid = NULL, $overwrite = array())
    {        
        // Vytvorime pole se detaily zmen
        $details = Array();
        // Precteme puvodni hodnoty sloupcu na kterych doslo ke zmene hodnoty
        $original_data = $orm->getLogOriginalData();
        // Projdeme sledované sloupce
        foreach ($this->watch_update as $column) {
            // Pokud na sloupci doslo ke smene hodnoty, vytvori se o tom zaznam - detail
            if (isset($original_data[$column])) {
                $details[] = Array(
                    'attr' => LogNumber::getColNumber($orm->object_name(), $column),
                    'old_value' => $original_data[$column],
                    'new_value' => $orm->{$column},
                );
            }
        }
        
        //pokud neni explicitne definovana zprava, tak vytvorim defaultni (obecnou)
        if ($message === NULL)
        {
            //sestavim zpravu ktera bude zapsana v historii
            $message = ___($orm->object_name().'.syslog.inserted', array(':preview' => $orm->preview()), 'logaction.updated_message');
        }

        $this->log(arr::get($overwrite, 'relAid', $orm->pk()),
                   arr::get($overwrite, 'relAtype', $orm->reltype()),
                   NULL,
                   NULL,
                   $message,
                   $log_action_categoryid,
                   $details);
    }
    
    /**
     * Vklada do historie obenou zpravu o tom ze byl vytoren novy zaznam.
     * @param <ORM> $orm
     */
    public function inserted($orm, $message = NULL, $log_action_categoryid = NULL, $overwrite = array())
    {
        //pokud neni explicitne definovana zprava, tak vytvorim defaultni (obecnou)
        if ($message === NULL)
        {
            //sestavim zpravu ktera bude zapsana v historii
            $message = __('logaction.inserted_message', array(':preview' => $orm->preview()));
        }

        $this->log(arr::get($overwrite, 'relAid', $orm->pk()),
                   arr::get($overwrite, 'relAtype', $orm->reltype()),
                   NULL,
                   NULL,
                   $message,
                   $log_action_categoryid,
                   NULL);
    }
    
    
    public function deleted($orm, $message = NULL, $log_action_categoryid = NULL, $overwrite = array())
    {
        //pokud neni explicitne definovana zprava, tak vytvorim defaultni (obecnou)
        if ($message === NULL)
        {
            //sestavim zpravu ktera bude zapsana v historii
            $message = __('logaction.deleted_message', array(':preview' => $orm->preview()));
        }

        $this->log(arr::get($overwrite, 'relAid', $orm->pk()),
                   arr::get($overwrite, 'relAtype', $orm->reltype()),
                   NULL,
                   NULL,
                   $message,
                   $log_action_categoryid,
                   NULL);
    }
    
    public function undeleted($orm, $message = NULL, $log_action_categoryid = NULL)
    {
        //pokud neni explicitne definovana zprava, tak vytvorim defaultni (obecnou)
        if ($message === NULL)
        {
            //sestavim zpravu ktera bude zapsana v historii
            $message = __('logaction.undeleted_message', array(':preview' => $orm->preview()));
        }

        $this->log($orm->pk(), $orm->reltype(), NULL, NULL, $message, $log_action_categoryid, NULL);
    }
    
    /**
     * Provede vlastni zalogovani
     * @param type $relAid - hlavni vazba. Ukazuje na zaznam kteremu tento zapis do historie patri.
     * @param type $relAtype 
     * @param type $relBid - pomocna vazba. Ukazuje na zaznam, ktery mohl zmenu vyvolat (napr k nabidce (vazba A) pribyla poptavka (vazba B))
     * @param type $relBtype
     * @param type $text - popis akce ve forme jazykove kotvy
     * @param int  $log_action_categoryid
     * @param type $details - seznam detailovych zaznamu - indexy hodnot v detailu musi odpovidat nazvum sloupcu v tabulce log_action_detail
     */
    protected function log($relAid, $relAtype, $relBid, $relBtype, $text, $log_action_categoryid = NULL, $details = Array())
    {
        if (empty($relAid) || empty($relAtype))
        {
            throw new AppException('LogAction_Base unable to write new log action because relAid or relAtype is not defined. Category: "'.$log_action_categoryid.'", Message: "'.$text.'".');
        }
        
        $logaction = ORM::factory('logaction');
        // Cislo tabulky
        $logaction->relAid = $relAid;
        // ID updatovaneho zaznamu
        $logaction->relAtype = $relAtype;
        
        // Cislo tabulky - pomocna vazba
        $logaction->relBid = $relBid;
        // ID updatovaneho zaznamu
        $logaction->relBtype = $relBtype;

        //kategorie
        $logaction->cb_log_action_categoryid = $log_action_categoryid;

        // text
        $logaction->text = $text;
        
        // Ulozeni log_action zaznamu
        $logaction->save();
        // Ulozeni detailnich zaznamu - s hodnotami jednotlivych atributu
        if ($logaction->loaded()) {
            foreach ((array)$details as $detail) {
                $log_detail = ORM::factory('LogActionDetail');
                // Nacteme hodnoty detailu - klice odpovidaji nazvum sloupcu
                $log_detail->values($detail);
                // Id log_action zaznamu
                $log_detail->log_actionid = $logaction->pk();
                $log_detail->save();
            }
        }
    }
    
}