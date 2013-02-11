<?php
/**
 * Database connection system to avoid having to include Session.
 */
 
/** Use where you need a database connection but don't need all Session variables
 * or users. Mainly for administration pages
 */
include('db.class.php');
$db = new DB('djsmusic_piensas','localhost','djsmusic_quepien','6Flw98cciCc1');