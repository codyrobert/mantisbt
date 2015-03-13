<?php

use Core\Action;
use Core\App;
use Core\Config;
use Core\Helper;
use Core\HTML;
use Core\String;
use Core\Utility;

?><meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="description" content="">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?php echo App::page_title(); ?></title>

<?php
$t_meta = Config::get_global( 'meta_include_file' );
if( !Utility::is_blank( $t_meta ) ) {
	include( $t_meta );
}
global $g_robots_meta;
if( !Utility::is_blank( $g_robots_meta ) ) {
	echo "\t", '<meta name="robots" content="', $g_robots_meta, '" />', "\n";
}

HTML::rss_link();

$t_favicon_image = Config::mantis_get( 'favicon_image' );
if( !Utility::is_blank( $t_favicon_image ) ) {
	echo "\t", '<link rel="shortcut icon" href="', Helper::mantis_url( $t_favicon_image ), '" type="image/x-icon" />', "\n";
}

# Advertise the availability of the browser search plug-ins.
echo "\t", '<link rel="search" type="application/opensearchdescription+xml" title="MantisBT: Text Search" href="' . String::sanitize_url( 'browser_search_plugin.php?type=text', true ) . '" />' . "\n";
echo "\t", '<link rel="search" type="application/opensearchdescription+xml" title="MantisBT: Issue Id" href="' . String::sanitize_url( 'browser_search_plugin.php?type=id', true ) . '" />' . "\n";

Action::perform('after_head');
?>