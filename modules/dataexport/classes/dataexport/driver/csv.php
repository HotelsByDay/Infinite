<?php defined('SYSPATH') OR die('No direct access allowed.');

class DataExport_Driver_CSV implements DataExport_iDriver
{
    protected $config = NULL;
    protected $data = NULL;
    protected $file_storage = NULL;
    protected $source_filename = NULL;

    const DEFAULT_CSV_NL        = "\r\n";
    const DEFAULT_CSV_DELIMITER = ";";

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
        $this->file_storage->fileOpen();

        $this->file_storage->fileAppend($this->getHeaderLine());

        foreach ($this->data as $model)
        {
            $this->file_storage->fileAppend($this->getDataLine($model));
        }

        $this->file_storage->fileClose();

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

    /**
     * Returns a single line of the CSV file as a string for the given model.
     *
     * @param $model
     * @return string
     */
    protected function getDataLine($model)
    {
        $line_generator_callable = $this->getLineGenerator();

        return $this->getDataLineFromCallable($line_generator_callable, $model);
    }

    /**
     * Returns the header line of the CSV file as a string.
     *
     * @return string
     */
    protected function getHeaderLine()
    {
        $header_generator_callable = $this->getHeaderGenerator();

        if ($header_generator_callable === FALSE)
        {
            return '';
        }

        return $this->getDataLineFromCallable($header_generator_callable, NULL);
    }

    /**
     * Uses callable object to generate a line of the target file.
     * Also escapes all values and applies iconv conversion according to configuration.
     *
     * @param $line_generator_function
     * @param null $model
     * @return string
     */
    protected function getDataLineFromCallable($line_generator_function, $model = NULL)
    {
        $csv_delimiter = $this->getDelimiter();
        $csv_nl        = $this->getNL();

        //model may be NULL for the Header Line Generator, but that does not cause any issues
        $line_values_array = call_user_func($line_generator_function, $model);

        $iconv_setting = arr::get($this->config, 'iconv');

        foreach ($line_values_array as $i => $value)
        {
            if ($iconv_setting != NULL)
            {
                $value = $this->applyIconv($iconv_setting, $value);
            }

            $value = $this->escapeValue($csv_delimiter, $value);

            $line_values_array[$i] = $value;
        }

        return implode($csv_delimiter, $line_values_array).$csv_nl;
    }

    /**
     * Escapes any occurrences of the delimiter in the value.
     *
     * @param $csv_delimiter
     * @param $value
     *
     * @return string
     */
    protected function escapeValue($csv_delimiter, $value)
    {
        return str_replace($csv_delimiter, '\\'.$csv_delimiter, $value);
    }

    /**
     * Applies 'iconv' conversion according to the driver configuration.
     *
     * @param $iconv_setting
     * @param $line_values_array
     * @return mixed
     * @throws DataExport_Exception_InvalidConfiguration
     */
    protected function applyIconv($iconv_setting, $value)
    {
        if ( ! ($iconv_setting) || count($iconv_setting) != 2)
        {
            throw new DataExport_Exception_InvalidConfiguration('DataExport_Driver_CSV: DataExport_Driver_CSV: Iconv not configured properly. The "iconv" array must have 2 keys: source encoding and target encoding.');
        }

        $iconv_from = $iconv_setting[0];
        $iconv_to   = $iconv_setting[1];

        return iconv($iconv_from, $iconv_to, $value);
    }

    /**
     * Returns a callable object (closure) from configuration that accepts a single parameter - a record model
     * and generates an array that represents values in a single line of the generated CSV file.
     *
     * @return callable
     * @throws DataExport_Exception_InvalidConfiguration If the 'line_generator' value in configuration file is not callable.
     */
    protected function getLineGenerator()
    {
        $line_generator = $this->config['line_generator'];

        if ( ! is_callable($line_generator))
        {
            throw new DataExport_Exception_InvalidConfiguration('DataExport_Driver_CSV: The "line_generator" is not callable');
        }

        return $line_generator;
    }

    /**
     * Returns a callable object (closure) from configuration that accepts a single parameter - a record model
     * and generates an array that represents the header row of the generated CSV file.
     *
     * @return callable | bool If the 'header_configuration' option is not defined or is NULL, then FALSE is returned,
     * otherwise the callable object is returned.
     *
     * @throws DataExport_Exception_InvalidConfiguration If the 'header_generator' value in configuration file is
     * defined and not callable.
     */
    protected function getHeaderGenerator()
    {
        $header_generator = $this->config['header_generator'];

        if ($header_generator === NULL)
        {
            return FALSE;
        }

        if ( ! is_callable($header_generator))
        {
            throw new DataExport_Exception_InvalidConfiguration('DataExport_Driver_CSV: The "header_generator" is not callable');
        }

        return $header_generator;
    }

    /**
     * Returns a delimiter defined in the configuration file.
     * If no delimiter is defined, then returns a default one ';'.
     *
     * @return string
     */
    protected function getDelimiter()
    {
        return arr::get($this->config, 'delimiter', self::DEFAULT_CSV_DELIMITER);
    }

    /**
     * Returns a 'new line' byte(s) defined in the configuration file.
     * If the 'new line' byte(s) are not defined, then returns default "\r\n".
     *
     * @return string
     */
    protected function getNL()
    {
        $nl = arr::get($this->config, 'nl', self::DEFAULT_CSV_NL);

        if (strlen($nl) != 1 && strlen($nl) != 2)
        {
            throw new DataExport_Exception_InvalidConfiguration('DataExport_Driver_CSV: The "nl" configuration is invalid. It must be 1 or 2 byte value.');
        }

        return $nl;
    }
}
