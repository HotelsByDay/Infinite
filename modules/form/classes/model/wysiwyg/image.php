<?php

class Model_Wysiwyg_Image extends Model_File
{

    /**
     * Povolene MIME-typy souboru
     * @var <array>
     */
    static public $allowed_mime_types = array(
        'image/jpg',
        'image/jpeg',
        'image/png',
        'image/bmp',
        'image/gif',
        'image/x-ms-bmp',
    );

    /**
     * Allowed image dimensions.
     * @var array
     */
    static public $allowed_image_dimension = array(
        NULL,    //min width
        NULL,   //max width  - unlimited
        NULL,   //min height - unlimited
        NULL,   //max height - unlimited
    );

    /**
     * Maximalni mozna velikost souboru Bytech
     * @var <int>
     */
    static public $allowed_max_filesize = 10485760; //10*1024*1024 = 10MB


}

