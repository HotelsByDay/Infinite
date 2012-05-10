<?php defined('SYSPATH') OR die('No direct access allowed.');


/**
 * Trida slouzi pro precteni uploadovaneho souboru z XMLHttpRequest
 */
class Upload_Driver_Xhr {

    /**
     * Uklada soubor do specifikovaneho souboru.
     * @return <bool> Vraci TRUE v pripade uspesneho zapsani souboru, jinak FALSE.
     */
    function save($path)
    {
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);

        if ($realSize != $this->getSize())
        {
            return FALSE;
        }

        $target = fopen($path, "w");
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);

        return TRUE;
    }

    /**
     * Vraci nazev uploadovaneho souboru.
     * @return <string>
     */
    function getName()
    {
        return text::webalize($_GET['file'], '.');
    }

    /**
     * Vraci velikost uploadovaho souboru.
     * @return <int>
     */
    function getSize()
    {
        if (isset($_SERVER["CONTENT_LENGTH"]))
        {
            return (int)$_SERVER["CONTENT_LENGTH"];
        }
        else
        {
            throw new Exception('Getting content length is not supported.');
        }
    }
}

?>
