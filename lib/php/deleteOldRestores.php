<?php
/**
 * CRON JOB page, deletes old password restores
 */

/**
 * Connect to the database
 */
include('db.php');

// Perform query
$db->execute('DELETE FROM `restore` WHERE `timestamp` < '.(time()-259200));

// Write to log
$saveFile = '../tmp/deleteOldRestores.log.txt';
$fh = fopen($saveFile, 'a');
if(!$fh) die('CANT OPEN FILE');
$days = array('','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo');
$mons = array('','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dec');
fwrite($fh,'['.date('j').' de '.$mons[date('n')].', '.date('H:i',$stime)."] Eliminadas ".mysql_affected_rows()." filas\n");
fclose($fh);