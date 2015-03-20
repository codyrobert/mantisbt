<?php
/* Constants
-------------------------------------------------------------- */
define('IS_LOCAL_ENVIRONMENT',	(bool)(substr($_SERVER['HTTP_HOST'], -4) == '.dev'));

define('ROOT',					dirname(__DIR__).'/');
define('APP',					ROOT.'app/');
define('MEDIA',					ROOT.'public/media/');
define('VENDOR',				ROOT.'vendor/');


/* Require config, globals, and autoloads
-------------------------------------------------------------- */
require VENDOR.'autoload.php';
require APP.'autoload.php';

require APP.'config.php';
require APP.'constants.php';
require APP.'config_defaults_inc.php';
require APP.'require_functions.php';

define( 'ADODB_DIR', \Core\Config::mantis_get( 'library_path' ) . 'adodb' );
require_lib( 'adodb' . DIRECTORY_SEPARATOR . 'adodb.inc.php' );

require_lib( 'phpmailer' . DIRECTORY_SEPARATOR . 'class.phpmailer.php' );

require APP.'globals.php';

require APP.'core.php';
require APP.'settings.php';


# Make sure we always capture User-defined errors regardless of ini settings
# These can be disabled in config_inc.php, see $g_display_errors
error_reporting(E_ALL);//error_reporting() | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE );

set_error_handler( '\\Core\\Error::handler' );