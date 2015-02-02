<?php defined('SYSPATH') or die('No direct script access.');

class Model_emailqueue extends ORM_Authorized
{
    protected $_table_name = 'email_queue';

    protected $_has_many = array(
        'email_queue_attachment' => array('model' => 'email_queue_attachment', 'foreign_key' => 'email_queueid')
    );

    protected $update_on_delete = true;

}