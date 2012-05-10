<?php defined('SYSPATH') or die('No direct script access.');

class Route extends Kohana_Route
{
    public static function reset_all()
    {
        Route::$_routes = array();
    }
}
