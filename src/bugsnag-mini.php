<?php
/**
 * BugSnag mini client for PHP.
 * The most minimal Bugsnag client possible.
 * @author Aaron Collegeman <aaron@withfatpanda.com>
 * @version 1.0.0
 * @copyright Fat Panda, LLC.
 * @license MIT
 */

/**
 * Handle an Exception (or a Throwable, after PHP 7); send details
 * about the exception to Bugsnag.
 * @param mixed Before PHP 7, an Exception; after PHP 7, a Throwable
 * @return void
 * @see http://php.net/manual/en/function.set-exception-handler.php
 * @see bugsnag_notify
 */
function bugsnag_mini_handle_exception($ex) 
{
  bugsnag_mini_notify($ex);
}

/**
 * Handle errors; when error type is within system reportable error threshold,
 * send details about the error to Bugsnag; when running on PHP >= 5,
 * the error is wrapped in an ErrorException; otherwise, a simpler message
 * is passed via a basic Exception instance.
 * @param int
 * @param String
 * @param String
 * @param int
 * @return void
 * @see http://php.net/manual/en/function.set-error-handler.php
 * @see error_reporting
 * @see ErrorException
 * @see bugsnag_notify
 */
function bugsnag_mini_handle_error($errno, $errstr, $errfile = null, $errline = null)
{
  if (error_reporting() & $errno) {
    if (class_exists('ErrorException')) {
      throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    } else {
      throw new Exception("Error Occurred: {$errstr} in {$errfile} at line {$errline}");
    }
  }
}

/** 
 * If a shutdown occurs, try to report the last error in the callstack,
 * if one exists and only if that error is a fatal error.
 */
function bugsnag_mini_handle_shutdown()
{
  // These errors constitute fatal errors:
  $errorCodes = array( E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE );
  if (defined('FATAL_ERROR')) {
    array_push($errorCodes, FATAL_ERROR);
  }

  if (!is_null($error = error_get_last()) && in_array($error['type'], $errorCodes)) {
    bugsnag_mini_notify(new Exception("Error Occurred: {$error['message']} of type {$error['type']} in {$error['file']} at line {$error['line']}"));
  }
}

/** 
 * Fire off an exception to Bugsnag; fail gracefully and silently.
 * @param mixed Before PHP 7, an Exception; after PHP 7, a Throwable
 * @return array The payload
 */
function bugsnag_mini_notify($ex)
{
  $payload = array();

  if (!defined('BUGSNAG_API_KEY') || !constant('BUGSNAG_API_KEY')) {
    echo "BUGSNAG_API_KEY is not set; can't connect to Bugsnag to report an Exception";
    exit;
  }

  $payload['apiKey'] = constant('BUGSNAG_API_KEY');

  $payload['notifier'] = array(
    'name' => 'Bugsnag Mini PHP',
    'version' => '1.0.0',
    'url' => 'https://github.com/withfatpanda/bugsnag-mini-php'
  );

  $stacktrace = array();

  $stack = $ex->getTrace();
  foreach($stack as $trace) {
    $t = array(
      'file' => !empty($trace['file']) ? $trace['file'] : $ex->getFile(),
      'lineNumber' => !empty($trace['line']) ? $trace['line'] : $ex->getLine(),
      'method' => ( !empty($trace['class']) ? $trace['class'] . ( !empty($trace['type']) ? $trace['type'] : '' ) : '' ) . $trace['function']
    );
    array_push($stacktrace, $t);
  }  

  $event = array(
    'payloadVersion' => '2',
    'exceptions' => array(
      array( 
        'errorClass' => get_class($ex),
        'message' => $ex->getMessage(),
        'stacktrace' => $stacktrace
      )
    ),
    'groupingHash' => get_class($ex)
  );

  if (function_exists('bugsnag_mini_user')) {
    $event['user'] = bugsnag_mini_user($ex);
  }

  if (function_exists('bugsnag_mini_app')) {
    $event['app'] = bugsnag_mini_app($ex);
  }

  if (function_exists('bugsnag_mini_device')) {
    $event['device'] = bugsnag_mini_device($ex);
  }

  if (function_exists('bugsnag_mini_meta')) {
    $event['metaData'] = bugsnag_mini_meta($ex);
  }

  $payload['events'] = array( $event );
  
  $data = json_encode($payload);

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'http://notify.bugsnag.com/');
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');                                                                     
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);                                                                  
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
    'Content-Type: application/json',                                                                                
    'Content-Length: ' . strlen($data)                                                                       
  )); 
  $result = curl_exec($ch);

  curl_close($ch);

  return $payload;
}