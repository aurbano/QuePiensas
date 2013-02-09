<?php
/**
 * Style functions
 */

/** 
 * Display time in a Twitter style (i.e. 8m, 2h)
 * @param int Initial time, in UNIX timestamp format
 * @param boolean Check against current date
 * @return string Formatted time
 */
function dispTime($stime,$now=true){
	if($now) $time = time()-$stime; else $time = $stime;
		
	if($time<86400){
		/*if($now) $ret = 'Hace ';
		if($time <= 60) $ret .= $time.' segundos';
		if(60 < $time && $time <= 3600) $ret .= floor($time/60).' minutos y '.floor($time-floor($time/60)*60).' segundos';
		if(3600 < $time && $time <= 86400) $ret .= floor($time/3600).' horas y '.floor(($time-floor($time/3600)*3600)/60).' minutos';*/
		if($time <= 60) $ret .= $time.'s';
		if(60 < $time && $time <= 3600) $ret .= floor($time/60).'m';
		if(3600 < $time && $time <= 86400) $ret .= floor($time/3600).'h';
	}else{
		if($time > time()-172800){
			$ret = 'Ayer a las '.date('g:ia',$time);
		}else{
			$days = array('','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo');
			$mons = array('','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dec');
			
			$ret = $days[date('N',$stime)].' '.date('j',$stime).' de '.$mons[date('n',$stime)];
		}
	}
	return $ret;
}
/** 
 * Display time in a Tuenti style (i.e. Hoy, 16:53)
 * @param int The time, in UNIX timestamp format
 * @return string Formatted time
 */
function dispTimeHour($stime){
	if($stime > mktime(0,0,0)){
		return 'Hoy, '.date('H:i',$stime);
	}else if($stime > mktime(0,0,0,date('m'), date('d')-1, date('Y'))){
		return 'Ayer, '.date('H:i',$stime);
	}else{
		$days = array('','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo');
		$mons = array('','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dec');
		return date('j',$stime).' de '.$mons[date('n',$stime)].', '.date('H:i',$stime);
	}
}

/**
 * Ensure that a text block contains no more than 2 spaces in a row.
 * @param string Text block
 * @return string Formatted text block
 */
function n2($string){ 
	return preg_replace('/\n{4,}/', '\n', preg_replace('/^\s+$/m', '', preg_replace('/\r/', '', $string)));
}
/**
 * Ensure that a text block doesn't contain malicius code
 * @param string Text to be cleaned
 * @return string Clean version
 */
function clean($m){
	return trim(addslashes(n2(htmlspecialchars($m,ENT_COMPAT,'UTF-8'))) );	
}
/**
 * Generate a random color from a user ID.
 *
 * The generated color will always be the same for the same user ID
 * it uses the MD5 hash of the ID to generate the color coordinates
 * @param int User ID
 * @return string Hex representation of the color
 */
function colorID($id){
	// Genera un color a partir de una ID de usuario
	$hash = md5($id);
	return '#'.$hash[0].$hash[1].$hash[2].$hash[3].$hash[5].$hash[8];	
}