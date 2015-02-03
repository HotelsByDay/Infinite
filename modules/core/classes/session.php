<?php defined('SYSPATH') or die('No direct script access.');

abstract class Session extends Kohana_Session {


    /**
     * Set flash message
     * @param $msg
     * @param string $type
     */
    public function setFlash($msg, $type='success')
    {
        $this->set('flash_message', $msg);
        $this->set('flash_type', $type);
    }

    /**
     * Return flash message HTML code
     * @return null|string
     */
    public function flash()
    {
        $msg = $this->get_once('flash_message');
        $type = $this->get_once('flash_type');
        if ($msg === NULL) {
            return NULL;
        }
        else {
            $icon = $this->getIconForFlashType($type);
            return '<div id="flash_message" class="flash-'.$type.'">'.$icon.$msg.'</div>';
        }
    }

    protected function getIconForFlashType($type)
    {
        if ($type == 'error') {
            //   return '<i class="icon-white icon-exclamation-sign"></i> &nbsp;';
        }
        return NULL;
    }

}
