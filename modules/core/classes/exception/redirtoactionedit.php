<?php defined('SYSPATH') or die('No direct script access.');

class Exception_RedirToActionEdit extends Exception_Redir
{
    public function getItemID()
    {
        return $this->getMessage();
    }
}