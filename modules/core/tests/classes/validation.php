<?php defined('SYSPATH') or die('No direct access allowed!');

class ValidationTest extends Kohana_UnitTest_TestCase
{
    public function test_phone_global()
    {
        $post = array();

        $username = Arr::get($post, 'username');

        $this->assertEquals($username, null);
    }
}