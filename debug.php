<?php
$_GET['debug'] = 'set';
include('lib/php/session.php');
$sess->debug('END',true,false);