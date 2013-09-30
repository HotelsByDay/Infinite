<?php defined('SYSPATH') OR die('No direct access allowed.');

interface DataExport_iDriver
{

    public function __construct($config, $data, DataExport_FileStorage $file_storage);

    /**
     * Generates a locally store file with the export data.
     *
     * @chainable
     */
    public function generateExport();

    /**
     * Returns path to the generated "export file" relative to DOCROOT.
     *
     * @return string
     */
    public function getFilePath();
}
