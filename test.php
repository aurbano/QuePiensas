<?php
include('lib/php/session.php');
$sess->blockWithCAPTCHA();
header('Location: /');
