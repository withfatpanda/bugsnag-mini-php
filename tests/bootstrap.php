<?php
require_once __DIR__.'/../vendor/autoload.php';

// Just set this to your Bugsnag API key to test:
define('BUGSNAG_API_KEY', $_ENV['BUGSNAG_API_KEY']);

// Setup the custom metadata functions:

function bugsnag_mini_user()
{
  return array(
    'id' => 1,
    'name' => 'Aaron Collegeman',
    'email' => 'aaron@withfatpanda.com'
  );
}

function bugsnag_mini_app()
{
  return array(
    'version' => '1.0.0',
    'releaseStage' => 'production', // anything else will probably be ignored
    'type' => 'phpunit'
  );
}

function bugsnag_mini_device()
{
  return array(
    'osVersion' => php_uname('v'),
    'hostname' => gethostname()
  );
}