<?php

/**
 * If lang form item is in MASTER mode then form model must implement this interface
 */
interface Interface_AppFormItemLang_MasterCompatible extends Interface_AppFormItemLang_SlaveCompatible
{

    /**
     * Synchronizes DB based on defined enabled languages
     * @abstract
     * @param array enabled languages list - array('en', 'de', ...)
     */
    public function setEnabledLanguages(array $enabled_languages);

}