<?php defined('SYSPATH') OR die('No direct access allowed.');

class DataExport_FileStorage
{
    protected $file_path    = NULL;
    protected $file_handler = NULL;
    protected $config       = NULL;

    const MAX_MKDIR_ATTEMPTS_HARD_LIMIT = 100;

    public static function factory()
    {
        $file_storage = new DataExport_FileStorage();

        return $file_storage;
    }

    public function __construct()
    {
        $this->config = kohana::$config->load('dataexport_filestorage');
    }

    public function Init($requested_filename)
    {
        if ( ! empty($this->file_path))
        {
            throw new DataExport_Exception_StorageAlreadyInitialized('The Init() method can be only called one time per instance lifecycle.');
        }

        if (empty($requested_filename))
        {
            throw new Exception('Requested filename must not be empty');
        }

        $this->file_path = $this->generateRandomFilePath($requested_filename);

        return $this;
    }

    public function getFilePath()
    {
        if ( ! file_exists($this->file_path))
        {
            throw new Exception('Cannot return file path - file does not exist.');
        }

        return $this->file_path;
    }

    public function fileOpen()
    {
        if (empty($this->file_path))
        {
            throw new DataExport_Exception_FilenameEmpty('Internal fileName has not been generated, did you call the Init() method?');
        }

        $this->file_handler = fopen($this->file_path, 'w');

        if ( ! $this->file_handler)
        {
            throw new Exception('DataExport_Driver_CSV: Unable to open temporary file for reading');
        }

        return TRUE;
    }

    public function fileAppend($string)
    {
        $string_len = mb_strlen($string);

        if (($written_bytes = fwrite($this->file_handler, $string, $string_len)) !== $string_len)
        {
            throw new Exception('DataExport_Driver_CSV: Unable to write data header to target file: "'.$temp_filepath.'".');
        }

        if ($written_bytes != $string_len)
        {
            throw new Exception("Unable to write all bytes to file - $written_bytes/$string_len (written/expected).");
        }

        return TRUE;
    }

    public function fileClose()
    {
        return fclose($this->file_handler);
    }

    /**
     * This method is used only in Unit Tests at the time of implementation.
     */
    public function fileRemove()
    {
        unlink($this->file_path);
        rmdir(dirname($this->file_path));
    }

    /**
     * Generates a new random file that can be used to store exported data.
     *
     * TODO: The way of finding a random file name that does not exist yet, is not very elegant.
     * TODO: DI for AppConfig class
     *
     * @return string
     * @throws Exception
     */
    protected function generateRandomFilePath($requested_filename)
    {
        $temp_dir    = AppConfig::instance()->get('temp_dir', 'system');
        $temp_subdir = arr::get($this->config, 'temp_subdir');

        if (empty($temp_dir))
        {
            throw new Exception('The [system]"temp_dir" attribute read from the main config file cannot be empty.');
        }

        if (empty($temp_subdir))
        {
            throw new Exception('The "temp_subdir" attribute read from export configuration cannot be empty');
        }

        $max_mkdir_attemps = min((int)arr::get($this->config, 'max_mkdir_attempts'), self::MAX_MKDIR_ATTEMPTS_HARD_LIMIT);

        $path = $temp_dir . DIRECTORY_SEPARATOR . $temp_subdir;

        while($i++ < $max_mkdir_attemps)
        {
            $random_filename = md5(mt_rand());

            $dirname = $path . DIRECTORY_SEPARATOR . $random_filename;

            if (file_exists($dirname) || ! mkdir($dirname, 0777, TRUE))
            {
                continue;
            }
        }

        $filepath = $dirname . DIRECTORY_SEPARATOR . $requested_filename;

        $fh = fopen($filepath, 'w');

        if ( ! $fh)
        {
            throw New Exception('DataExport_TempStorage: Unable to generate temporary random file');
        }

        fclose($fh);

        return $filepath;
    }
}
