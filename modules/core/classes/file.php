<?php defined('SYSPATH') or die('No direct script access.');

class File extends Kohana_File
{
    /**
     * Provadi procisteni obsahu temp adresare systemu.
     * Maze vsechny soubory starsi nez 48 hodin.
     */
    public static function cleanTempDir()
    {
        $temp_dir = AppConfig::instance()->get('temp_dir', 'system');

        //upravi cestu
        $temp_dir = realpath(DOCROOT . $temp_dir);

        //par "ochranych" podminek aby se aplikace sama nesmazala
        if ( ! is_dir($temp_dir)
                || empty($temp_dir)
                || $temp_dir == realpath(DOCROOT)
                || str_replace(DOCROOT, '', $temp_dir) == $temp_dir) //pokud DOCROOT neni obsazen v temp_dir
        {
            kohana::$log->add(KOHANA::ERROR, 'Unable to clean temp dir ":temp_dir".', array(
                ':temp_dir' => $temp_dir
            ));

            return FALSE;
        }

        //chci smazat vsechen obsah starsi nez 48 hodin
        file::cleanDir($temp_dir, FALSE, 3600 * 24 * 2);

        return TRUE;
    }

    /**
     * Rekursivne smaze cely obsah daneho adresare.
     * Volitelne maze i dany 'root' adresar jehoz obsah ma byt smazan.
     * Volitelne take provadi kontrolu "stari" souboru a maze jen ty, ktere jsou
     * starsi nez definovany cas.
     *
     * @param <string> $dir Cesta k adresari jehoz obsah ma byt smazan.
     * @param <bool> $rm_dir Pokud je TRUE tak bude smazan i samotny $dir adresar.
     * @param <int> $min_file_age Minimalni stari souboru ktere budou smazany.
     * Pokud je hodnota prazdna neprovadi se kontrola stari souboru a smazany budou vsechny.
     */
    public static function cleanDir($dir, $rm_dir, $min_file_age)
    {
        //glob nekdy vraci false
        $scan = (array)glob(rtrim($dir, '/').'/*');

        //abych funkci time() nemusel opakovat v cyklu volat
        $time = time();

        foreach ($scan as $file_path)
        {
            if (is_file($file_path))
            {
                //min_file_age neni definovan (kontrola nabude) anebo je definovan
                //a kontrola casu prosla v poradku
                if ( ! $min_file_age || filemtime($file_path) < ($time - $min_file_age))
                {
                    @unlink($file_path);
                }
            }
            else if (is_dir($file_path))
            {
                self::cleanDir($file_path, TRUE, $min_file_age);
            }
        }

        //ma byt smazan i predany korenovy adresar ?
        if ($rm_dir)
        {
            @unlink($dir);
        }
    }
}