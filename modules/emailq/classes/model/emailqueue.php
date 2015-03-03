<?php defined('SYSPATH') or die('No direct script access.');

class Model_emailqueue extends ORM_Authorized
{
    protected $_table_name = 'email_queue';

    protected $_has_many = array(
        'email_queue_attachment' => array('model' => 'email_queue_attachment', 'foreign_key' => 'email_queueid')
    );

    protected $update_on_delete = true;

//    protected function getDefaults()
//    {
//        //loads table columns definition into $this->_table_columns
//        $this->reload_columns();
//
//        $data = array();
//        //ID uzivatele, ktery zaznam vytvari
//        if (array_key_exists('userid', $this->_table_columns) && $this->_primary_key != 'userid' && Auth::instance()->get_user() != NULL)
//        {
//            $data['userid'] = Auth::instance()->get_user()->pk();
//        }
//        return $data;
//    }

}

