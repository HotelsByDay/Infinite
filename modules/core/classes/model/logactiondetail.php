<?php defined('SYSPATH') or die('No direct access allowed.');



class Model_LogActionDetail extends ORM_Authorized {

    /**
     * Nazev DB tabulky nad kterou stoji tento model.
     * @var <string>
     */
    protected $_table_name = 'log_action_detail';
    
    /**
     * Seznam souvisejicich modelu
     * @var <array>
     */
    protected $_belongs_to = Array(
        Array('model' => 'LogAction', 'foreign_key' => 'log_actionid'),
    );
    
}