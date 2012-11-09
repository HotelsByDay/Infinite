<<?php defined('SYSPATH') OR die('No direct access allowed.');


/**
 * Trida slouzi pro precteni uploadovaneho souboru pres standardni formular ($_FILES)
 */
class Upload_Driver_Form {



    /**
     * Uklada soubor do specifikovaneho souboru.
     * @return <bool> Vraci TRUE v pripade uspesneho zapsani souboru, jinak FALSE.
     */
    function save($path)
    {
        $copy_fce = (Kohana::$environment === Kohana::PRODUCTION) ? 'move_uploaded_file' : 'copy';
        if( ! $copy_fce($_FILES['file']['tmp_name'], $path))
        {
            return false;
        }
        return true;
    }

    /**
     * Vraci nazev uploadovaneho souboru.
     * @return <string>
     */
    function getName()
    {
        return text::webalize($_FILES['file']['name'], '.');
    }

    /**
     * Vraci velikost uploadovaho souboru.
     * @return <int>
     */
    function getSize()
    {
        return $_FILES['file']['size'];
    }
}

?>
