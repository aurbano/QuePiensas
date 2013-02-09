<?php
/**
 * Parsea los amigos importados de Facebook de {fbimport
 * Crea nuevas personas si hace falta y nuevas relaciones
 */
ini_set('display_errors','On');
$_GET['db'] = 'noerrorfie';
include('db.php');
if($db->countOfAll('fbimport')<1) die();
$a = $db->execute('INSERT IGNORE INTO personas (fbid, usid, name, visits, timestamp) SELECT fbid, (SELECT id FROM users WHERE fbuser = usid) AS usid, name, 0, '.time().' FROM fbimport');
// Now add relationships
//$b = $db->execute('INSERT INTO relations (pid, usid, relation, follow) SELECT (SELECT personas.id FROM personas WHERE personas.fbid = fbimport.fbid AND personas.fbid IS NOT NULL AND personas.fbid > 0) AS pid, (SELECT users.id FROM users WHERE users.fbuser = fbimport.usid AND users.fbuser IS NOT NULL AND users.fbuser > 0) AS usid, fbimport.relation, IF(fbimport.relation>10,1,0) AS f FROM fbimport ON DUPLICATE KEY UPDATE relations.follow=1;');
// Vaciamos la tabla fbimport
//if($a && $b) $db->execute('TRUNCATE TABLE fbimport');