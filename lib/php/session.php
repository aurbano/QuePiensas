<?php
/**
 * Main file, should be included everywhere where Users are tracked
 */
ini_set('display_errors','Off');
session_start();
date_default_timezone_set('Europe/Madrid');

include('lib/php/db.class.php');
include('lib/php/auth.php');
include('lib/php/user.php');
include('lib/php/getip.php');
include('lib/php/fb.class.php');
include('lib/php/twitter.php');

/** 
 * Session management class, include whenever you need authenticated users
 * 
 * This class handles most of the site important operations, it should be included
 * in all dynamic pages. If the site was to be put in maintenance this file will ensure
 * that all pages where it is included display the maintenance page instead.
 * @author Alejandro U. Alvarez
 * @version 2.0
 * @package Session
 */
class Session{
	/** Last recorded site page viewed
	 */
	var $referrer;
	/** The current page name
	 */
	var $curPage;
	/** Start of page load, to calculate load speed
	 */
	var $mtStart;
	/** Home directory
	 */
	var $home;
	/** User object handler
	 */
	var $user;
	/** Database handle
	 */
	var $dbH;
	/** Block bots and display CAPTCHA page
	 */
	var $captcha = false;
	/** Put entire site in maintenance mode
	 */
	var $maintenance = false;
   
   /**
	 * Session constructor
	 *
	 * It stores the appropriate values in most of the class attributes
	 */
	function Session(){
		// Arregla problemas de includes
		set_include_path(substr(dirname(__FILE__),0,38));
		
		list($msec, $sec) = explode(' ', microtime());
		$this->mtStart    = floor($sec / 1000) + $msec;
		
		$file = explode('.',$_SERVER["SCRIPT_NAME"]);
		$this->curPage = $this->clean($file[0]);
		
		$this->captcha = $_SESSION['captcha'];
		
		$this->home = substr(dirname(__FILE__),0,38).'/';
		// Maintenance
		if($this->maintenance){
			include($this->home.'lib/content/maintenance.php');
			die();  
		}
		// Display CAPTCHA
		if($this->captcha && $this->curPage !== 'stop'){
			include($this->home.'stop.php');
			die();  
		}
		/*
		//--------------- BETA PRIVADA ------------------
		if(!$this->logged()){
			// Comprobamos que no este en alguna seccion permitida
			$allowedPages = array('info','invitation','contacto');
			if(!in_array($this->curPage,$allowedPages)){
				if(isset($_COOKIE['qpin']) && strlen($_COOKIE['qpin'])>40){
					// el valor de la cookie contiene el token
					// y un codigo que lo verifica
					// Tecnicamente no es posible calcular el codigo sin saber como se calcula
					// por lo que no deberia ser posible descubrir el sistema que genera cookies validas
					$p = explode('_',$_COOKIE['qpin']);
					if($p[0] == sha1($p[1].(12345))) $valid = true;
				}
				if(!$valid){
					include('beta.php');
					die();
				}
			}
		}*/
		//-----------------------------------------------
		
		/* Debugger */
		if($_GET['debug']=='set') $_SESSION['debug'] = true;
		if($_GET['debug']=='unset') unset($_SESSION['debug']);
		
		if(isset($_SESSION['url'])){
			$this->referrer = $_SESSION['url'];
		}else{
			$this->referrer = $_SERVER['HTTP_REFERER'];
		}
		
		/* Session keeping via Cookie */
		if(!$this->logged()){
			$this->debug('Not logged. Get session from Cookie');
			$usid = $this->sessionCookie(2);
			$this->debug('Loaded user: '.$usid);
			if($usid){
				$this->debug('Valid user from cookie: '.$usid);
				$this->loginUser($usid);
			}
			else $this->debug('No valid user found.');
		}
		
		$this->debug('Constructor for Session. Current contents of SESSION:');
		$this->debug('<pre>'.print_r($_SESSION,true).'</pre>');
	}
   
   /**
    * Create a new Database connection
	* @return object Database connection object
	*/
   function db(){
   		global $db;
		if(!$this->dbH) $this->dbH = new DB('djsmusic_piensas','localhost','djsmusic_quepien','6Flw98cciCc1');
		return $this->dbH;
   }
   
  /**
    * Create a new Twitter connection
	* @return object Twitter object
	*/
   function twitter(){
	    if($this->logged()){
			$twitter = new EpiTwitter('***REMOVED***','***REMOVED***',$_SESSION['logged']['oauth_token'],$_SESSION['logged']['oauth_secret']);
		}else $twitter = new EpiTwitter('***REMOVED***','***REMOVED***');
		$twitter->useAsynchronous(true);
		return $twitter;  
   }
   
   /**
    * Set a new secret value, used for cookie management
	* @param int User ID
	* @return boolean Whether the new secret code was updated
	*/
   function setSecret($usid){
		// Assign a secret code to user
		$db = $this->db();
		if(!$usid || $usid <1) return false;
		return $db->execute('UPDATE users SET secret = \''.$this->token($usid).'\' WHERE id = \''.$usid.'\' LIMIT 1');
   }
   
   /**
    * Get the current secret code
	* @param int User ID
	* @return string Secret code
	*/
   function getSecret($usid){
		// Assign a secret code to user
		$db = $this->db();
		return $db->queryUniqueValue('SELECT secret FROM users WHERE id = \''.$usid.'\'');
   }
   
   /**
    * Generates a string made up of randomized letters (lower and upper case) and digits
	* @param int Length of random string
	* @return string Randomly generated string
    */
	function generateRandStr($length){
		$randstr = "";
		for($i=0; $i<$length; $i++){
			$randnum = mt_rand(0,61);
			if($randnum < 10){
				$randstr .= chr($randnum+48);
			}else if($randnum < 36){
				$randstr .= chr($randnum+55);
			}else{
				$randstr .= chr($randnum+61);
			}
		}
		return $randstr;
	}
	/**
	 * Cleans an input string, only leaving alphanumeric characters
	 * @param string Input text
	 * @return string Cleaned text
	 */
	function clean($input){
		return preg_replace("/[^A-Za-z0-9\s\s+\]\[-]/",'',trim(rtrim($input)));
	}
	/**
	 * Set a new message, either error or informative
	 *
	 * The Message will be displayed on the next page load
	 * @param string The message to be stored
	 */
	function set_msg($msg){
		$_SESSION['msg'] .= '<li>'.$msg.'</li>';	
	}
	
	/**
	 * Displays stored messages using a Fancybox
	 * @see Session:set_msg
	 */
	function msg(){
		// Muestra la cadena guardada en msg
		if($_SESSION['msg']){
			echo '$.fancybox.open(\'<img src="http://static.quepiensas.es/img/icons/delete.png" style="float:left;" /><h3 style="padding-top:5px; text-indent:10px; ">Ha ocurrido un error</h3><div style="clear:left"></div><ul>'.$_SESSION['msg']."</ul>',{maxWidth:600});";
			unset($_SESSION['msg']);
		}
	}
	
	/**
	 * Create a debug file
	 * @param string Last message before creating the file
	 * @return true
	 */
	function createDebugFile($msg=false){
		global $db, $user;
		if(!($db instanceof DB)) $db = $this->db();
		if(!$_SESSION['debug']) return false;
		if($msg) $this->debug .= '<li>'.$msg.'</li>';
		// Add info
		if($_POST) $this->debug .= '<li>POST:<br /><pre>'.print_r($_POST,true).'</pre></li>';
		if($_GET) $this->debug .= '<li>GET<br /><pre>'.print_r($_GET,true).'</pre></li>';
		
		list($msec, $sec) = explode(' ', microtime());
		$logged = 'No';
		$fb = 'No';
		if($user->fb()) $fb = 'Yes ('.$user->fb().')';
		$tw = 'No';
		if($user->tw()) $tw = 'Yes ('.$user->tw().')';
		if($this->logged()) $logged = 'Yes';
		$content = '<div id="debug"><h2 style="cursor:pointer; text-align:center">Debug info</h2><div id="debugInfo"><hr /><ol>'.$this->debug.'</ol>';
		$content .= '<h3>Page debug info:</h3><ul>
			<li>Execute time: '.(round(((floor($sec / 1000) + $msec) - $this->mtStart) * 1000) / 1000).'</li>
			<li>SQL Queries: '.$db->getQueriesCount().'</li>
			<li>Referrer: '.$this->referrer.'</li>
			<li>Current: '.$this->curPage.'</li>
			<li>Logged in: '.$logged.'</li>
			<li>Has FB: '.$fb.'</li>
			<li>Has TW: '.$tw.'</li>
		</ul><hr /></div></div>';
		// Write to debug file
		$myFile = 'debug/'.$this->curPage.'['.date('g:i:sa').'--'.date('j').'-'.date('n').'-'.date('Y').'].htm';
		$fh = fopen($myFile, 'w');
		if($fh){
			fwrite($fh, $content);
			fclose($fh);
		}
		return true;
	}
	
	/**
	 * Store a debug message, including a full backtrace of the debug function call
	 * @param string Message to be added
	 * @param boolean Whether you want the debug window to appear after storing this message
	 * @param boolean Whether the debug window should appear with display:none
	 * @param boolean Whether to call die() after displaying the debug information
	 * @return true
	 */
	function debug($msg,$display=false,$hidden=true,$die=false){
		global $user;
		if(!$_SESSION['debug']) return false;
		global $db;
		if(!($db instanceof DB)) $db = $this->db();
		$debugData = debug_backtrace();
		$levels = sizeof($debugData);
		for($i=0;$i<$levels;$i++){
			$data = $debugData[$i];
			$start .= '['.basename($data['file']).'] Line '.$data['line'].', from '.$data['class'].$data['type'].$data['function'].'(';
			if($data['function']!=='debug' && $data['function']!=='include'){
				// Print args
				foreach($data['args'] as $value) $start .= $value.',';	
			}
			$start .= ')<br />';
		}
		$this->debug .= '<li>'.$start.'<strong>'.$msg.'</strong></li>';	
		if($display){
			$msg = $msg.'<br />Value of session twitter token: '.$_SESSION['twitter']['oauth_token'];
			list($msec, $sec) = explode(' ', microtime());
			$cssDisplay = 'none';
			if(!$hidden) $cssDisplay = 'block';
			$logged = 'No';
			if($this->logged()) $logged = 'Yes';
			$fb = 'No';
			if($user->fb()) $fb = 'Yes ('.$user->fb().')';
			$tw = 'No';
			if($user->tw()) $tw = 'Yes ('.$user->tw().')';
			$content = '<div id="debug"><h2 style="cursor:pointer; text-align:center">Debug info</h2><div id="debugInfo" style="display:'.$cssDisplay.';"><hr /><ol>'.$this->debug.'</ol>';
			$content .= '<h3>Page debug info:</h3><ul>
				<li>Execute time: '.(round(((floor($sec / 1000) + $msec) - $this->mtStart) * 1000) / 1000).'</li>
				<li>SQL Queries: '.$db->getQueriesCount().'</li>
				<li>Referrer: '.$this->referrer.'</li>
				<li>Current: '.$this->curPage.'</li>
				<li>Logged in: '.$logged.'</li>
				<li>Has FB: '.$fb.'</li>
				<li>Has TW: '.$tw.'</li>
			</ul><hr /></div></div>';
			$this->createDebugFile();
			if($die) die($content); else echo $content;
		}
		return true;
	}
	/**
	 * Unset a given cookie
	 * @param string Cookie name
	 */
	function unsetCookie($name){
		setcookie($name, '', time()-3600);	
	}
	/**
	 * Logs a user in, this should be called instead of directly accesing the Auth method
	 * @param string User email
	 * @param string User password
	 * @return boolean Whether the login was successful
	 */
	function login($email,$pass){
		// Esta funcion entra por $auth y genera las variables de sesion/cookies necesarias
		global $auth;
		$this->debug('Function: login');
		if($this->logged()){
			$this->debug('Ya has iniciado sesion');
			return false;
		}
		if(!($auth instanceof Auth)){
			$this->debug('Starting auth');
			$auth = new Auth;	
		}
		// User & Pass are validated in Auth
		$login = $auth->login($email,$pass);
		$this->debug('Return value of auth login: '.$login);
		if($login){
			if(is_numeric($login) && $login>0){
				$this->debug('Login OK -> loginUser('.$login.','.$email.')');
				return $this->loginUser($login,$email);
			}
		}
		$this->debug('Login failed');
		return false;
	}
	/**
	 * Register a new user, this should be called instead of directly accesing the Auth method
	 * @param string User email
	 * @param string User name
	 * @param string Password
	 * @param boolean Whether the email has already been checked
	 * @return boolean Whether the registration was successful
	 */
	function register($email,$name,$pass,$checkedEmail=false){
		global $auth;
		// User & Pass are validated in Auth
		return $auth->register($email,$name,$pass,$checkedEmail);
	}
	/**
	 * Setup all variables to login user
	 * @access private
	 * @param int User ID
	 * @param string User email address
	 * @return true
	 */
	function loginUser($usid,$email=false){
		// Session variables:
		$_SESSION['logged'] = true;
		$_SESSION['user']['id'] = $usid;
		if($email) $_SESSION['user']['email'] = $email;
		$this->sessionCookie(1,$usid); // Store cookie	
		return true;	
	}
	/**
	 * Generate a new security token
	 * @access private
	 * @param int User ID
	 * @param string Random seed to be used
	 * @return string Random token (From sha1)
	 */
	function token($usid,$seed=''){
		$this->debug('Function token, Usid='.$usid.'; Seed='.$seed);
		if(!$usid) $usid = $_SESSION['user']['id'];
		return sha1($usid.'/_qp()%+'.$seed);
	}
	/**
	 * Log current user out
	 */
	function logout(){
		global $fb;
		if(!($fb instanceof FB)) $fb = new FB;
		unset($_SESSION['user']);
		session_destroy();
		$this->unsetCookie('qpu');	
		$fb->facebook->destroySession();
	}
	/**
	 * Generate or check session cookie, depending on the mode
	 * @param int If mode=1 it generates a new cookie, if mode=2 it checks the current cookie
	 * @param int User ID to be used
	 * @return boolean Whether everything is OK
	 */
	function sessionCookie($mode=1,$usid=false){
		if($mode==1 && $usid>0){
			$token = $this->getSecret($usid);
			return setcookie('qpu',$token,time()+12*30*24*60*60,'/','.quepiensas.es',false, true);
		}
		if($mode==2){
			// Modo 2, get
			if(!isset($_COOKIE['qpu'])){
				$this->debug('No cookie');
				return false;
			}
			$token = $_COOKIE['qpu'];
			// Now check if its a valid token
			$db = $this->db();
			return $db->queryUniqueValue('SELECT id FROM users WHERE secret LIKE UNHEX(\''.$token.'\')');
		}
		return false;
	}
	/**
	 * Checks if a variable is valid for the type
	 * @param var Whatever you want to check
	 * @param string Type to be checked agains: [email, boolean, float, int, ip, regexp, url]
	 * @return boolean Whether the variable is of that type
	 */
	function valid($what,$type){
		// Usage: if($data = $sess->valid($data,'type'))
		$validate = array('email','boolean','float','int','ip','regexp','url');
		$clean = array('email'=>'email','url'=>'url','int'=>'number_int','float'=>'number_float','string','special');
		// Fix for 0
		if($what=='0' && $type=='int') return true;
		if(in_array($type,$validate)){
			if(!eval('return filter_var(\''.$what.'\', FILTER_VALIDATE_'.strtoupper($type).');')) return false;
		}
		if(strlen($clean[$type])>0) return eval('return filter_var(\''.$what.'\', FILTER_SANITIZE_'.strtoupper($clean[$type]).');');
		return true;
	}
	/**
	 * Check if current user is logged in
	 * @return boolean Whether the user is logged in
	 */
	function logged(){
		return $_SESSION['logged'];
	}
	/**
	 * Create a new user, if there is a user session variable it creates from there.
	 * @return object User object
	 */
	function user(){
		global $fb;
		// Generate the user object
		if($_SESSION['user']){
			$usid = $_SESSION['user']['id'];
			$user = new User($usid);
		}else{
			$user = new User(0);
			// Objeto user, crea un nuevo usuario
			// Para asignar una ID a los comentarios
			// y que queden vinculados a una ip
		}		
		return $user;
	}
	
	/**
	 * Block current user and display CAPTCHA page
	 */
	function blockWithCAPTCHA(){
		$this->captcha = $_SESSION['captcha'] = true;
	}
	
	/**
	 * Unblock current user and display CAPTCHA page
	 */
	function unblockWithCAPTCHA(){
		$this->captcha = false;
		unset($_SESSION['captcha']);
	}
};

$sess = new Session;

$user = $sess->user();
// Social networks
$fb = new FB($user->fb());
$tw = new Twitter($user->tw());
$auth = new Auth;