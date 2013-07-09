<?php defined('SYSPATH') OR die('No direct access allowed.');

require_once SYSPATH.'../mpdf/mpdf.php';

class DataExport_Driver_PDF implements DataExport_iDriver
{
    protected $config = NULL;
    protected $data = NULL;
    protected $file_storage = NULL;
    protected $source_filename = NULL;

    public function __construct($config, $data, DataExport_FileStorage $file_storage)
    {
        $this->config = $config;
        $this->data   = $data;

        $this->file_storage = $file_storage;
        $this->file_storage->Init($this->getUserFileName());
    }

    public function getFilePath()
    {
        return $this->file_storage->getFilePath();
    }

    public function generateExport()
    {
        if ( ! isset($this->config['view_name'])) {
            throw new Exception('"view_name" must be defined for PDF export.');
        }
        $view_name = $this->config['view_name'];
        $filename = $this->file_storage->getFilePath();

        $html = View::factory($view_name)->set('results', $this->data);


        $mpdf = new mPDF();
        $mpdf->WriteHTML((string)$html);
        $mpdf->Output($filename, 'F');


        return $this;
    }

    /**
     * Returns target file name as it is defined by the configuration.
     *
     * @return string
     * @throws DataExport_Exception_InvalidConfiguration
     */
    protected function getUserFileName()
    {
        $filename_generator_function = arr::get($this->config, 'filename_generator');

        if ( ! is_callable($filename_generator_function))
        {
            throw new DataExport_Exception_InvalidConfiguration('DataExport_Driver_CSV: The "filename_generator" is not callable');
        }

        return call_user_func($filename_generator_function);
    }

}
