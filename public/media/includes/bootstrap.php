<?php
require dirname(dirname(__FILE__)).'/vendor/autoload.php';
require dirname(__FILE__).'/class.pingdom.php';

R::setup('mysql:host=localhost;dbname=flickerbox_status', 'fb_status','fb_status');

require dirname(__FILE__).'/cron.php';