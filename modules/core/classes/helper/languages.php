<?php

class Helper_Languages {

    /**
     * Nastavi seznam povolenych jazyku pro dany objekt
     * - jazyky ulozi v danem poradi
     * @static
     * @param ORM $model - model pro ktery nastavujeme povolene jazyky
     * @param array $languages - seznam povolenych jazyku
     */
    public static function setEnabledLanguages(ORM $model, array $languages)
    {
        $reltype = $model->reltype();
        $relid = $model->relid();
        // Zkusime najit nastaveni
        $setting = ORM::factory('Object_Language')->where('reltype', '=', $reltype)
            ->where('relid', '=', $relid)
            ->find();
        // At bylo nebo ne, ulozime nove
        $setting->relid = $relid;
        $setting->reltype = $reltype;
        $setting->languages = json_encode(array_values($languages));
        $setting->save();
    }


    /**
     * Vrati seznam povolenych jazyku pro zadany model
     * @static
     * @param ORM $model - model pro ktery chceme precist seznam povolenych jazyku
     */
    public static function getEnabledLanguages(ORM $model, array $defaults=array())
    {
        $reltype = $model->reltype();
        $relid = $model->relid();
        // Zkusime najit nastaveni
        $setting = ORM::factory('Object_Language')->where('reltype', '=', $reltype)
            ->where('relid', '=', $relid)
            ->find();
        // Precteme seznam jazyku
        $enabled = (array)json_decode($setting->languages, true);

        // Pokud seznam povolenych jazyku je prazdny
        if (empty($enabled)) {
            // Pridame defaultne povolene jazyky
            // @todo pouzit na to nejakou array funkci?
            foreach ($defaults as $default) {
                if ( ! in_array($default, $enabled)) {
                    $enabled[] = $default;
                }
            }
        }

        // Premenime na tvar kde jazyky jsou klici i hodnotami
        if ( ! empty($enabled)) {
            $enabled = array_combine($enabled, $enabled);
        }
        return $enabled;
    }


    /**
     * Vygeneruje nazvy jazyku do hodnot zadaneho pole.
     * @static
     * @param array $languages - jako klice je ocekavan kod jazyka
     * @return array languages - klice jsou stejne jako vstup, hodnoty jsou labely (prelozene nazvy) jazyku
     */
    public static function fillLanguagesLabels(array $languages)
    {
        foreach ($languages as $locale => $foo) {
            $languages[$locale] = __('locale.'.$locale);
        }
        return $languages;
    }
    
}

?>
