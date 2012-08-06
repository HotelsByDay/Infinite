<?php

/**
 * This class sets up Selenium WebDriver for Firefox
 */
class Selenium_TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var WebDriver_Driver
     */
    protected $driver;

    public function setUp() {

        // If you want to set preferences in your Firefox profile
        $fp = new WebDriver_FirefoxProfile();

        $fp->set_preference("capability.policy.default.HTMLDocument.compatMode", "allAccess");

        // Choose one of the following

        // For tests running at Sauce Labs
//     $this->driver = WebDriver_Driver::InitAtSauce(
//       "my-sauce-username",
//       "my-sauce-api-key",
//       "WINDOWS",
//       "firefox",
//       "10",
//       array(
//         'firefox_profile' => $fp->get_profile()
//       ));
//     $sauce_job_name = get_class($this);
//     $this->driver->set_sauce_context("name", $sauce_job_name);

        // For a mock driver (for debugging)
//     $this->driver = new WebDriver_MockDriver();
//     define('kFestDebug', true);

        // For a local driver
        $this->driver = WebDriver_Driver::InitAtLocal("4444", "firefox");

        return parent::setup();
    }

    // Forward calls to main driver
    public function __call($name, $arguments) {
        if (method_exists($this->driver, $name)) {
            return call_user_func_array(array($this->driver, $name), $arguments);
        } else {
            return parent::__call($name, $arguments);
        }
    }


    /**
     * This may be placed directly in Driver but this way we can replace driver without worrying about this method.
     * @param $locator
     * @param int $max_wait_seconds
     */
    public function get_element_wait($locator, $max_wait_seconds = 5)
    {
        $stop_time = time()+$max_wait_seconds;
        $present = false;
        while ($stop_time >= time() and ! $present) {
            $present = $this->driver->is_element_present($locator);
        }
        // Element is present yet or timeout has exceeded
        return $this->driver->get_element($locator);
    }


    public function tearDown() {
        if ($this->driver) {
            if ($this->hasFailed()) {
                $this->driver->set_sauce_context("passed", false);
            } else {
                $this->driver->set_sauce_context("passed", true);
            }
            $this->driver->quit();
        }
        parent::tearDown();
    }
}