# bugsnag-mini-php

This is the most minimal PHP client for [Bugsnag](https://bugsnag.com/) that 
I could conceive of and create.

I wanted to be able to add Bugsnag to an aging PHP codebase I'm working on,
but for various reasons, I couldn't easily incorporate the standard PHP library.
Fortunately, Bugsnag has an easy to approach JSON API, so I put this client
together, and now you can use it too!

## Getting Started

If you're adding this to a project that doesn't use Composer, just download
this repository, and copy the file `src/busnag-mini.php` into your project.

If you're using Composer, you can incorporate this snip of PHP like a library
as follows:

```
composer require withfatpanda/bugsnag-mini-php
```

The next thing you'll need to do is create a constant somewhere to store
your Bugsnag API key:

```php
define('BUGSNAG_API_KEY', 'your-key-goes-here');
```

The last and most important step is to hook up this client's exception and
error handling functions, like so:

```php
set_exception_handler('bugsnag_mini_handle_exception');
set_error_handler('bugsnag_mini_handle_error');
register_shutdown_function('bugsnag_mini_handle_shutdown');
```

Now anytime an `Exception` is thrown or an error is raised or the runtime 
shutsdown, errors will be sent to Bugsnag. 

## Customizing Meta Data

You can feed more meta data into Bugsnag by setting up functions that assess the
context of the error and provide more context. You can, for example, attach
information about affected user, about the app, about the device running the app,
and other meta data.

Each of the functions receives as their only argument the `Exception` that was
thrown and is being reported upon.

These functions *do not* exist until you define them in your own codebase.
The client will look for them, and will call them only if they exist—this is
*completely* and *totally* optional.

The functions are as follows:

### bugsnag_mini_user

Information about the user affected by the crash.

```php
function bugsnag_mini_user($ex) {
  return array(
    // A unique identifier for a user affected by this event. This could
    // be any distinct identifier that makes sense for your
    // application/platform.
    // (optional, searchable)
    "id" => "19",

    // The user's name, or a string you use to identify them.
    // (optional, searchable)
    "name" => "Simon Maynard",

    // The user's email address.
    // (optional, searchable)
    "email" => "simon@bugsnag.com"
  );
}
```

### bugsnag_mini_app

Information about the app that crashed.

```php
function bugsnag_mini_app($ex) {
  return array(
    // The version number of the application which generated the error.
    // If appVersion is set and an error is resolved in the dashboard
    // the error will not unresolve until a crash is seen in a newer
    // version of the app.
    // (optional, default none, filtered)
    "version" => "1.1.3",

    // The release stage that this error occurred in, for example
    // "development", "staging" or "production".
    // (optional, default "production", filtered)
    "releaseStage" => "production",

    // A specialized type of the application, such as the worker queue or web
    // framework used, like "rails", "mailman", or "celery"
    "type" => "rails"
  );
}
```

### bugsnag_mini_device

Information about the computer/device running the app

```php
function bugsnag_mini_app($ex) {
  return array(
    // The operating system version of the client that the error was
    // generated on. (optional, default none)
    "osVersion" => "2.1.1",

    // The hostname of the server running your code
    // (optional, default none)
    "hostname" => "web1.internal"
  );
}
```

### bugsnag_mini_meta

An object containing any further data you wish to attach to this
error event. This should contain one or more objects, with each
object being displayed in its own tab on the event details on the
Bugsnag website.

```php
function bugsnag_mini_meta($ex) {
  return array(
    // This will displayed as the first tab after the stacktrace on the
    // Bugsnag website.
    "someData" => array(
      // A key value pair that will be displayed in the first tab
      "key" => "value",

      // This is shown as a section within the first tab
      "setOfKeys" => array(
        "key" => "value",
        "key2" => "value"
      )
    ),

    // This would be the second tab on the Bugsnag website.
    "someMoreData" =>  array(
     
    )
  );
}
```

## Sending errors manually

If ever you want to push an Exception arbitrarily, all you have to 
do is call `bugsnag_mini_notify($ex)`. For example:

```php
bugsnag_mini_notify(new Exception('Some arbitrary error message'));
```
## Running the unit test

To ease development and testing, I put together a simple PHPUnit-based test. 
To run it, you'll need to edit `phpunit.xml`, uncomment and modify the following
section to suit:

```xml
<php>
  <!-- <env name="BUGSNAG_API_KEY" value="your-api-key-goes-here"/> -->
</php>
```

This won't actually halt the runtime.

## About Fat Panda

[Fat Panda](https://www.withfatpanda.com) is a software product consultancy 
located in Winchester, VA. We specialize in Laravel, WordPress, and Ionic. 
No matter where you are in the development of your product, we'll meet you 
there and work with you to propel you forward.

## Contributing

If you run into a problem using this framework, please 
[open an issue](https://github.com/withfatpanda/bugsnag-mini-php/issues).

If you want to help make this framework amazing, check out the 
[help wanted](https://github.com/withfatpanda/bugsnag-mini-php/issues?q=is%3Aissue+is%3Aopen+label%3A%22help+wanted%22) list.

If you'd like to support this and the other open source projects Fat Panda is 
building, please join our community of supporters on [Patreon](https://www.patreon.com/withfatpanda).

