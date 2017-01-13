<?php
use PHPUnit\Framework\TestCase;

class TestBugsnagMini extends TestCase {

  /**
   * Make sure test/bootstrap.php is setup correctly.
   */
  function testConfig()
  {
    $this->assertTrue(defined('BUGSNAG_API_KEY'), "Constant BUGSNAG_API_KEY is missing");
    $this->assertNotEmpty(constant('BUGSNAG_API_KEY'), "Constant BUGSNAG_API_KEY is empty");
  }

  /**
   * Make sure that our event handling functions can be registered.
   */
  function testRegistration()
  {
    set_exception_handler('bugsnag_mini_handle_exception');
    set_error_handler('bugsnag_mini_handle_error');
    register_shutdown_function('bugsnag_mini_handle_shutdown');
  }

  /**
   * Fire off an event to Bugsnag.
   */
  function testBugsnagNotify()
  {
    error_reporting(-1);
    bugsnag_mini_notify(new Exception("This is a test. This is only a test."));
  }

}