<?php defined('SYSPATH') or die('No direct access allowed.');

class Model_Core_Role extends Model_Auth_Role {

    //definice jazykove kotvy pro preview tohoto zaznamu
    protected $_preview = 'role.preview_format';

//    /**
//     * Kazdy uzivatel ma opravneni na cteni roli.
//     * @return <bool>
//     */
//    protected function applyUserSelectPermission()
//    {
//        return TRUE;
//    }

    /**
     * Chci zamezit odstraneni neverejnych roli.
     */
    public function delete($id = NULL, array $plan = array())
    {
        $this->where('public', '=', '0');

        return parent::delete($id, $plan);
    }
} // End Role Model