<?php
/* Constants
-------------------------------------------------------------- */
define('IS_LOCAL_ENVIRONMENT',	(bool)(substr($_SERVER['HTTP_HOST'], -4) == '.dev'));

define('ROOT',					$_SERVER['DOCUMENT_ROOT'].'/');
define('APP',					$_SERVER['DOCUMENT_ROOT'].'/app/');
define('VENDOR',				$_SERVER['DOCUMENT_ROOT'].'/vendor/');


/* Require config, globals, and autoloads
-------------------------------------------------------------- */
require VENDOR.'autoload.php';
require APP.'autoload.php';

require APP.'config.php';
require APP.'constants.php';
require APP.'config_defaults_inc.php';
require APP.'globals.php';
require APP.'core.php';
require APP.'settings.php';


# Make sure we always capture User-defined errors regardless of ini settings
# These can be disabled in config_inc.php, see $g_display_errors
error_reporting( error_reporting() | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE );

set_error_handler( '\Flickerbox\Error::handler' );