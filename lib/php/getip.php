<?php
/**
 * IP detection functions
 */
 
/**
 * Get first part of the IP address
 * @param string IP address
 * @access private
 */
function ip_first($ips) {
  if (($pos = strpos($ips, ',')) != false) {
    return substr($ips, 0, $pos);
  } else {
    return $ips;
  }
}

/**
 * Check if an IP address is valid
 * @access private
 * @param string IP address
 */
function ip_valid($ips) {
  if (isset($ips)) {
    $ip    = ip_first($ips);
    $ipnum = ip2long($ip);
    if ($ipnum !== -1 && $ipnum !== false && (long2ip($ipnum) === $ip)) { // PHP 4 and PHP 5
      if (($ipnum < 167772160   || $ipnum >   184549375) && // Not in 10.0.0.0/8
          ($ipnum < -1408237568 || $ipnum > -1407188993) && // Not in 172.16.0.0/12
          ($ipnum < -1062731776 || $ipnum > -1062666241))   // Not in 192.168.0.0/16
        return true;
    }
  }
  return false;
}

/**
 * Get user IP address
 */
function ip() {
  /*$check = array(
    'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR',
    'HTTP_FORWARDED', 'HTTP_VIA', 'HTTP_X_COMING_FROM', 'HTTP_COMING_FROM',
    'HTTP_CLIENT_IP'
   );

  foreach ($check as $c) {
    if (ip_valid($_SERVER[$c])) {
      return ip_first($_SERVER[$c]);
    }
  }*/

  return $_SERVER['REMOTE_ADDR'];
}