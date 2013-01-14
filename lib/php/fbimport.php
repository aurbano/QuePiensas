<?php
ini_set('display_errors','On');
/*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*
                           EL IMPORTADOR
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
// (Importa amigos de facebook de un archivo creado por fb.class.php)

// 1) Seleccionar archivo
$dir = '../tmp/facebook/';
// DB config
$_GET['db'] = 'noerrorfie';
include('db.php');

$parsed = array();

if(is_dir($dir)){
	if($dh = opendir($dir)){
		while(($file = readdir($dh)) !== false){ 
			if($file!=='.' && $file!=='..' && $file!=='_notes'){
				$usid = substr($file,0,strlen($file)-4);
				// 2) Insertemos:
				$sql = "LOAD DATA LOCAL INFILE '../tmp/facebook/$file' INTO TABLE fbimport FIELDS TERMINATED BY ',' ENCLOSED BY '' ESCAPED BY ''
				LINES TERMINATED BY '\n' STARTING BY '';";
				if($db->execute($sql)){
					$parsed[] = $file;
					unlink('../tmp/facebook/'.$file);
				}
			}
		}
        closedir($dh);
    }
}

if(count($parsed)<1) die();
// Write to log
$saveFile = '../tmp/fbimport.log.txt';
$fh = fopen($saveFile, 'a');
if(!$fh) die('Cant open file');

$days = array('','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo');
$mons = array('','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dec');
fwrite($fh,'['.date('j').' de '.$mons[date('n')].', '.date('H:i')."] Procesando archivos:\n");

for($i=0; $i<count($parsed); $i++){
	fwrite($fh, "\t".$parsed[$i]."\n");	
}
fclose($fh);