<?php

/**
 * If lang form item is in slave mode then form model must implement this interface
 */
interface Interface_AppFormItemLang_SlaveCompatible {

    /**
     * Returns list of all languages enabled in model as array with following structure:
     * return array(
     *   'en' => 'en',
     *   'de' => 'de',
     * );
     * @abstract
     * @param array defaults - array of defaults languages
     * @return array
     */
    public function getEnabledLanguagesList($defaults=array());

}