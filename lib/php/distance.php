<?php
// Distance calculations functions
function distance($loc){
	// Returns SQL sentence to setup distance in users table
	// $loc must be an array with 3=>lat, 4=>long
	if(!is_float($lat[3]) || !is_float($lat[4])) return '0 AS distance';
	return '(DEGREES( ACOS( SIN(RADIANS('.$loc[3].')) * SIN(RADIANS(location.lat)) + COS(RADIANS('.$loc[3].')) * COS(RADIANS(location.lat)) * COS(RADIANS('.$loc[4].' - location.long)) ) ) * 60 * 1.1515) AS distance';
}