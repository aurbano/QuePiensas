<?php
ini_set('display_errors','Off');
session_start();
date_default_timezone_set('Europe/Madrid');

include('lib/php/db.class.php');
include('lib/php/auth.php');
include('lib/php/user.php');
include('lib/php/getip.php');
include('lib/php/fb.class.php');
include('lib/php/twitter.php');

class Session{
   var $url;          // The page url current being viewed
   var $referrer;     // Last recorded site page viewed
   var $curPage;	  // Current page
   var $mtStart;	  // Start of page load, to calculate load speed
   var $home;		  // Home directory
   var $user;
   var $dbH;		  // Database handler
   
   var $maintenance = false; // Esto pone Que Piensas en MODO mantenimiento
   
   /**
    * Note: referrer should really only be considered the actual
    * page referrer in process.php, any other time it may be
    * inaccurate.
	* For DJs Music this doesn't actually work as expected because it is always index. It must be fixed in the future
    */

   /* Class constructor */
	function Session(){
		// Arregla problemas de includes
		set_include_path(substr(dirname(__FILE__),0,38));
		
		list($msec, $sec) = explode(' ', microtime());
		$this->mtStart    = floor($sec / 1000) + $msec;
		
		$file = explode('.',$_SERVER["SCRIPT_NAME"]);
		$this->curPage = $this->clean($file[0]);
		
		$this->home = substr(dirname(__FILE__),0,38).'/';
		if($this->maintenance){
			$_GET['err'] = 666;
			include($this->home.'errorDoc.php');
			die();  
		}
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
		}
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
   
   function db(){
   		global $db;
		if(!$this->dbH) $this->dbH = new DB('djsmusic_piensas','localhost','djsmusic_quepien','6Flw98cciCc1');
		return $this->dbH;
   }
   
   // TWITTER STUFF
   function twitter(){
	    if($this->logged()){
			$twitter = new EpiTwitter('***REMOVED***','***REMOVED***',$_SESSION['logged']['oauth_token'],$_SESSION['logged']['oauth_secret']);
		}else $twitter = new EpiTwitter('***REMOVED***','***REMOVED***');
		$twitter->useAsynchronous(true);
		return $twitter;  
   }
   
   function setSecret($usid){
		// Assign a secret code to user
		$db = $this->db();
		if(!$usid || $usid <1) return false;
		return $db->execute('UPDATE users SET secret = \''.$this->token($usid).'\' WHERE id = \''.$usid.'\' LIMIT 1');
   }
   
   function getSecret($usid){
		// Assign a secret code to user
		$db = $this->db();
		return $db->queryUniqueValue('SELECT secret FROM users WHERE id = \''.$usid.'\'');
   }
   
   /**
    * generateRandStr - Generates a string made up of randomized
    * letters (lower and upper case) and digits, the length
    * is a specified parameter.
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
	function clean($input){
		return preg_replace("/[^A-Za-z0-9\s\s+\]\[-]/",'',trim(rtrim($input)));
	}
	
	function set_msg($msg){
		$_SESSION['msg'] .= '<li>'.$msg.'</li>';	
	}
	
	function msg(){
		// Muestra la cadena guardada en msg
		if($_SESSION['msg']){
			echo '$.fancybox.open(\'<img src="http://static.quepiensas.es/img/icons/delete.png" style="float:left;" /><h3 style="padding-top:5px; text-indent:10px; ">Ha ocurrido un error</h3><div style="clear:left"></div><ul>'.$_SESSION['msg']."</ul>',{maxWidth:600});";
			unset($_SESSION['msg']);
		}
	}
	
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
	function unsetCookie($name){
		setcookie($name, '', time()-3600);	
	}
	function login($email,$pass,$usid=false){
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
	function register($email,$name,$pass,$checkedEmail=false){
		global $auth;
		// User & Pass are validated in Auth
		return $auth->register($email,$name,$pass,$checkedEmail);
		/* Ya no, hay que verificar la cuenta!
		if($usid){
			$this->setSecret($usid);
			return $this->loginUser($usid,$email);
		}*/
		return false;
	}
	function loginUser($usid,$email=false){
		// Setup all variables to login user:
		// Session variables:
		$_SESSION['logged'] = true;
		$_SESSION['user']['id'] = $usid;
		if($email) $_SESSION['user']['email'] = $email;
		$this->sessionCookie(1,$usid); // Store cookie	
		return true;	
	}
	function token($usid,$seed=''){
		$this->debug('Function token, Usid='.$usid.'; Seed='.$seed);
		if(!$usid) $usid = $_SESSION['user']['id'];
		return sha1($usid.'/_qp()%+'.$seed);
	}
	function logout(){
		global $fb;
		if(!($fb instanceof FB)) $fb = new FB;
		unset($_SESSION['user']);
		session_destroy();
		$this->unsetCookie('qpu');	
		$fb->facebook->destroySession();
	}
	function sessionCookie($mode=1,$usid=false){
		// Esta funcion genera una cookie
		// y tambien la comprueba, son el modo 1 y 2
		// respectivamente.
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
	function logged(){
		return $_SESSION['logged'];
	}
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
};

$sess = new Session;

$user = $sess->user();
// Social networks
$fb = new FB($user->fb());
$tw = new Twitter($user->tw());
$auth = new Auth;