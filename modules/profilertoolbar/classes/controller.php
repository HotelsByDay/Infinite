<?php defined('SYSPATH') or die('No direct script access.');

abstract class Controller extends Kohana_Controller {

  public function after(){
    if (Request::$instance === Request::$current && ProfilerToolbar::cfg('firebug.enabled') && ProfilerToolbar::cfg('firebug.showEverywhere')) {
      ProfilerToolbar::firebug();
    }
  }

}
