<?php
/**
 * This file features the User class
 */
 
/**
 * User class, generate a User object for every user you want to interact with
 *
 * It will hold all info about current user
 * and ensure consistency. Also take care of cache.
 *
 * @author	Alejandro U. Alvarez
 * @version	1.1
 * @package	Users
 */
class User{
	
	/**
	 * User ID
	 * @var User ID, 0 if the user is not logged in
	 * @access private
	 */
	private	$id;
	/**
	 * Facebook ID
	 * @var User ID, 0 if not linked
	 * @access private
	 */
	private $fbuser;
	/**
	 * Twitter ID
	 * @var User ID, 0 if not linked
	 * @access private
	 */
	private $twuser;
	/**
	 * User Name
	 * @var User ID, 0 if the user is not logged in
	 * @access private
	 */
	private $name;
	/**
	 * User description
	 * @var Description
	 * @access private
	 */
	private $bio;
	/**
	 * Profile pic src
	 * @var Profile pic src if set
	 * @access private
	 */
	private $pic;
	/**
	 * User ID
	 * @var What picture the user wants displayed
	 * @access private
	 */
	private $usePic;
	/**
	 * User email
	 * @var User email address
	 * @access private
	 */
	private $email;
	/**
	 * The date in UNIX timestamp when the user first appeared
	 * @var Join time
	 * @access private
	 */
	private $jtime;
	/**
	 * Last time in UNIX timestamp that the user was active
	 * @var Last time the user was active
	 * @access private
	 */
	private $ltime;
	
	/**
	 *	User constructor, builds an object containing a user with all methods for interacting with it
	 *	@param	int		Required, set to 0 if you want an anonymous user.
	 *	@param	string	User name
	 *	@param	string	User email
	 *	@return	object	User object
	 */
	function User($id,$name=false,$email=false){
		if($id>0){
			$this->set('id',$id);
			if($name) $this->set('name',$name);
			if($email) $this->set('email',$email);
			if(!$this->hasLoc()) $this->getLoc($this->id,ip());
			// Only update every minute
			if(!isset($_SESSION['user']['lastUpdateTime'])){
				$_SESSION['user']['lastUpdateTime'] = time();
				$this->set('ltime',time(),true);
			}else if(time()-$_SESSION['user']['lastUpdateTime']>60){
				$_SESSION['user']['lastUpdateTime'] = time();
				$this->set('ltime',time(),true);
			}
		}else{
			global $sess;
			$db = $sess->db();
			// Check if IP exists, if it does, assign that ID
			$usid = $db->queryUniqueValue('SELECT id FROM users WHERE ip = INET_ATON(\''.ip().'\') AND email IS NULL AND pass IS NULL and fbuser IS NULL and twuser IS NULL');
			if($usid>0 && is_numeric($usid)){
				$this->set('id',$usid);
				$this->set('ltime',time(),true);
				$sess->debug('Loading user from SAME IP: ID='.$this->id);
			}
			// If the user doesn't exist, we will not assign it a new ID
			// instead we will leave it as is, and create the ID on the fly when needed
			// And probably the same for location, since it has to connect to an external server it would
			// be better to get it via AJAX whenever it's needed.
			// $this->getLoc($this->id,ip());
		}
	}
	
	/**
	 * Returns the current User ID
	 * 
	 * Probably one of the most important functions here, since we allow anonymous users we will
	 * call this funcion whenever an ID is needed. If the current user is anonymous, his ID won't be
	 * created until we need it, so this function takes care of that.
	 * @return int User ID
	 */
	function id(){
		if($this->id > 0) return $this->id;
		if(isset($_SESSION['user']['id']) && $_SESSION['user']['id'] > 0) return $this->id = $_SESSION['user']['id'];
		// ID not available in Session
		// Since there is no ID already defined, we can assume that the user is anonymous
		// because logging in/registering actually sets an ID
		global $sess;
		$db = $sess->db();
		$ip = 'INET_ATON(\''.ip().'\')';
		$db->execute('INSERT INTO `users` (`name`, `email`, `pass`, `ip`, `ltime`, `jtime`) VALUES (\''.$this->g('name').'\', \''.$this->g('email').'\', \'0\', '.$ip.', \''.time().'\', \''.time().'\');');
		$this->set('id',$db->lastInsertedId());
		$sess->debug('Created new user: ID='.$this->id);
		die('{{{Generated new ID}}}');
		return $this->id;
	}
	
	/**
	 *	Return a user attribute. It also manages cache through Session variables
	 *	@param	string	Attribute to be returned
	 *	@param	boolean	Whether you want to pull the attribute from the database
	 *	@return	string	The value if found, false otherwise
	 */
	function g($what,$db=true){
		// Special case for the ID
		if($what=='id') return $this->id();
		// Get data from session
		if(isset($_SESSION['user'][$what]) && strlen($_SESSION['user'][$what])>0 && $_SESSION['user'][$what]) return $this->set($what,stripslashes($_SESSION['user'][$what]));
		if(!$db) return false;
		// Get data from database
		global $sess;
		if(!($sess instanceof Session)) return false;
		global $db;
		if(!($db instanceof DB)) $db = $sess->db();
		return $this->set($what,stripslashes($db->queryUniqueValue('SELECT '.$what.' FROM users WHERE id = \''.$this->id.'\'')));
	}
	
	/**
	 * Same as g() above
	 * @param string Attribute to get
	 * @see	g
	 */
	function get($what){
		return $this->g($what);	
	}
	
	/**
	 *	Checks if user has picture uploaded
	 *	@return boolean True if user has uploaded a pic, false otherwise
	 */
	function hasPic(){
		return file_exists('img/user/uploads/'.$this->id.'.gif');
	}
	
	/**
	 *	Returns the associated picture for the current user
	 *	@param	string	Image type: profile or square
	 *	@return	string	The image full src
	 */
	function pic($type='profile'){
		// Devuelve la ruta a la imagen
		if($type=='profile'){
			switch($this->g('usePic')){
				case 1:
					// Uploaded pic:
					if($this->hasPic()){
						// Use this pic
						return $this->set('pic','http://img.quepiensas.es/'.$this->id.'.gif');
					}else{
						// No pic uploaded
						return $this->set('pic','http://img.quepiensas.es/noimage.png');
					}
				case 2:
					// Facebook pic
					if($this->fb()){
						global $fb;
						return $this->set('pic', $fb->pic());
					}
				case 3:
					// Twitter pic
					if($this->tw()){
						global $tw;
						return $this->set('pic', $tw->pic());
					}
				case 0:
				default:
					return $this->set('pic','http://img.quepiensas.es/noimage.png');
			}
			if(!$this->g('pic')) return $this->set('pic','http://img.quepiensas.es/noimage.png');
		}else if($type=='square'){
			switch($this->g('usePic')){
				case 2:
					// Facebook pic
					if($this->fb()){
						global $fb;
						return $fb->pic('square');
					}
				case 3:
					// Twitter pic
					if($this->tw()){
						global $tw;
						return $tw->pic('normal');
					}
				case 1:
					// User uploaded
					// Check if pic exists
					if($this->hasPic()){
						// Use this pic
						return 'http://img.quepiensas.es/'.$this->id.'.gif';
					}else{
						// No pic uploaded
						return 'http://img.quepiensas.es/noimage.png';
					}
					break;
				case 0:
				default:
					return 'http://img.quepiensas.es/noimage.png';
			}
		}
	}
	
	/**
	 *	Setter for user attributes
	 *	@param	string	Attribute to set
	 *	@param	string	New value to be assigned
	 *	@param	boolean	Whether to update the database
	 *	@param	boolean	Whether to strip html tags and escape the string
	 *	@return	string	The same thing that was passed to new
	 */
	function set($what,$new,$updateDB=false,$clean=true){
		if($updateDB){
			global $sess, $db;
			if(!($sess instanceof Session))return false;
			if(!($db instanceof DB)) $db = $sess->db();
			$setNew = $new;
			if($what == 'ip'){
				$new = 'INET_ATON(\''.$new.'\')';	
			}else if($clean){
				$new = "'".trim(addslashes(htmlspecialchars(stripslashes($new),ENT_COMPAT,'UTF-8')) )."'";		
			}
			$change = $db->execute('UPDATE users SET '.$what.' = '.$new.', ltime = \''.time().'\', ip = INET_ATON(\''.ip().'\') WHERE id = '.$this->id.' LIMIT 1');
			
			$this->$what = $_SESSION['user'][$what] = $setNew;
			return $new;
			
		}else{
			$this->$what = $_SESSION['user'][$what] = $new;
			return $new;
		}
	}
	
	/**
	 * Function now, guarda algo solo para esta sesion
	 * @param	string	Attribute to set
	 * @param	string	Value for that attribute
	 */
	function now($what,$value){
		$this->$what = $_SESSION['user'][$what] = $value;
	}
	/**
	 * Get geolocation for user IP
	 *
	 * Returns an array 0->Country 1->Region 2->City
	 * It's called from getLoc() below
	 * @param string IP address to locate
	 * @access private
	 * @return array [country, region, city, lat, lng]
	 */
	private function locate($ip){
		$fp = @fopen('http://www.ipaddresslocation.org/ip-address-locator.php', 'r', false, stream_context_create(array('http' => array('method' => 'POST','content' => http_build_query(array('ip'=>$ip)) ))));
		if(!$fp)  return false;
		$res = @stream_get_contents($fp,-1,300);
		if ($res === false) {
			return false;
		}
		$p=explode("<div class='ipaddress'><i>",$res);
		$e=explode('<p align="center" class="network">',$p[1]);
		// Ahora vamos a filtrar cosas:
		$data = array();
		$c = preg_match('@Country Code:</i> <b>(.*)</b>@',$e[0],$data[0]);
		$c = preg_match('@Region:</i> <b>(.*)</b>@',$e[0],$data[1]);
		$c = preg_match('@Guessed City: <b>(.*)</b>@',$e[0],$data[2]);
		$c = preg_match('@Latitude:</i> <b>(.*)</b>@',$e[0],$data[3]);
		$c = preg_match('@Longitude:</i> <b>(.*)</b>@',$e[0],$data[4]);
		return array($data[0][1],$data[1][1],$data[2][1],$data[3][1],$data[4][1]);
	}
	/**
	 * Gets location for a given user ID
	 *
	 * Alternatively you can pass another IP address
	 * @param	int	User id whose location you want
	 * @param	string	Alternative IP to check
	 * @return	array	Array with user location [country, region, city, lat, lng]
	 */
	function getLoc($usid,$ip=false){
		// First check in session:
		if(isset($_SESSION['user']['loc']) && $usid == $this->id){
			return $_SESSION['user']['loc'];
		}else{
			// Try to fetch from DB, and if not, get from website:
			global $sess;
			$db = $sess->db();
			if(!$ip) $ip = $db->queryUniqueValue('SELECT INET_NTOA(ip) FROM users WHERE id = '.$usid);
			$loc = $db->queryUniqueObject('SELECT location.lat, location.long, location.city, location.region, location.country FROM location WHERE location.ip = INET_ATON(\''.$ip.'\')');
			if($loc){
				if(isset($_SESSION['user']['loc']) && $usid == $this->id){
					$_SESSION['user']['loc'][0] = $loc->country;
					$_SESSION['user']['loc'][1] = $loc->region;	
					$_SESSION['user']['loc'][2] = $loc->city;
					$_SESSION['user']['loc'][3] = $loc->lat;
					$_SESSION['user']['loc'][4] = $loc->long;
					return $_SESSION['user']['loc'];
				}
				return array($loc->country,$loc->region,$loc->city,$loc->lat,$loc->long);
			}else{
				// Get that users IP:
				if(!$ip) $ip = $db->queryUniqueValue('SELECT INET_NTOA(ip) FROM users WHERE id = '.$usid);
				$loc = $this->locate($ip);
				// Now insert on DB:
				if($loc[2]!==''){
					if(strlen($ip)>0){
						$db->execute('INSERT INTO `location` (`ip`, `lat`, `long`, `city`, `region`, `country`) VALUES (INET_ATON(\''.$ip.'\'), \''.$loc[3].'\', \''.$loc[4].'\',  \''.addslashes($loc[2]).'\', \''.addslashes($loc[1]).'\', \''.addslashes($loc[0]).'\');');
					}
				}
				if(isset($_SESSION['user']['loc']) && $usid == $this->id){
					$_SESSION['user']['loc'][0] = $loc[0]; // Pais
					$_SESSION['user']['loc'][1] = $loc[1]; // Region
					$_SESSION['user']['loc'][2] = $loc[2]; // Ciudad
					$_SESSION['user']['loc'][3] = $loc[3]; // Lat
					$_SESSION['user']['loc'][4] = $loc[4]; // Long
					return $_SESSION['user']['loc'];
				}
				return array($loc[0],$loc[1],$loc[2],$loc[3],$loc[4]);
			}
		}
	}
	/**
	 * Returns location of user, if available
	 *
	 * Type determines what data to return
	 *	0 = Country
	 *	1 = Region
	 *	2 = City
	 *	3 = Lat
	 *	4 = Lng
	 * @param	int	Check the values above
	 * @return	string	The location type requested or false if not available
	 */
	function location($type=1){
		if(isset($_SESSION['user']['loc'])){
			return $_SESSION['user']['loc'][$type];
		}else{
			$loc = $this->getLoc($this->id);
			return $loc[$type];
		}
	}
	/**
	 * Sends private message to user, from current user
	 *
	 * All variables are checked within this function
	 *	STATUS GUIDE
	 *		0 -> Just sent, unread
	 *		1 -> Read
	 *		2 -> Deleted by sender, unread
	 *		3 -> Deleted by sender, read
	 *		4 -> Deleted by receiver
	 *	IDENT GUIDE
	 *		# ->	To			Current User
	 *		0 ->	Public		Public
	 *		1 ->	Private		Public
	 *		2 ->	Public		Private
	 *		3 ->	Private		Private
	 * @param	int	User ID of receiver
	 * @param	string	Message content, no need to clean it
	 * @param	int	Thread ID, or 0 if PM is not a reply
	 * @param	int	Check description above
	 * @param	int	If the message is in reply to a comment, the ID of the comment
	 * @return	int	ID of the sent PM or false if something failed
	 */
	function sendPM($to,$msg,$thread=0,$ident=0,$com=0){
		global $sess, $db,$user;
		if(!$sess->logged()) return false;
		if(!($db instanceof DB)) $db = $sess->db();
		if(!($db instanceof DB) || !($user instanceof User)) return false;
		if(!$sess->valid($thread,'int')) $thread=0;
		if(!($to = $sess->valid($to,'int'))) $to=0;
		// Ahora guardamos el mensaje
		if(!function_exists('clean')) include('style.php');
		$msg = clean($_POST['msg']); // De style.php
		if(!$sess->valid($ident,'int')) $ident = 0;
		if(!$sess->valid($com,'int') || $com < 0) $com = 0;
		// Listos para guardar
		if($db->execute('INSERT INTO `msg` (`com`, `thread`,`ident`,`from`,`to`,`msg`,`status`,`timestamp`) VALUES (\''.$com.'\', \''.$thread.'\',\''.$ident.'\',\''.$this->id.'\',\''.$to.'\',\''.$msg.'\',\'0\',\''.time().'\');'))
			return $db->lastInsertedId();
		else return false;
	}
	/**
	 * Set PM to read status. If message is part of a thread
	 *
	 * It sets the status to read for the whole thread.
	 * @param	int	ID of the thread if PM is a reply
	 * @return	boolean	Whether status was set
	 */
	function updatePMstatus($thread){	
		// Updates Thread to READ status
		// Returns false if user has NO permission
		// Or if thread = 0
		global $sess, $db,$user;
		if(!$sess->logged()) return false;
		if(!($db instanceof DB)) $db = $sess->db();
		if(!($db instanceof DB) || !($user instanceof User)) return false;
		if(!($thread = $sess->valid($thread,'int'))) return false;
		// Check permission and current status
		$status = $db->queryUniqueObject('SELECT `status` FROM `msg` WHERE (`id` = \''.$thread.'\' OR `thread` = \''.$thread.'\') AND (`to` = \''.$user->id().'\' OR `from` = \''.$user->id().'\') ORDER BY id DESC');
		if(!$status) return false;
		$status = $status->status;
		// Actualiza el status, si yo soy el 
		$nextStatus = false;
		if($status == 0) $nextStatus = 1;
		if($status == 2) $nextStatus = 3;
		if($nextStatus) $db->execute('UPDATE msg SET status = \''.$nextStatus.'\' WHERE (`thread` = \''.$thread.'\' OR `id` = \''.$thread.'\')  AND `to`=\''.$user->id().'\' AND `status` = \''.$status.'\'');
		return true;
	}
	/**
	 * Check whether a user has been located
	 * @return boolean	true if user has location, false otherwise
	 */
	function hasLoc(){
		return isset($_SESSION['user']['loc']);
	}
	/**
	 * Link given facebook ID to the current user.
	 *
	 * If no ID is provided it checks whether there is
	 * a logged in facebook account
	 * @param	int	Facebook account ID, if false it will use currently logged fb account
	 * @return	boolean	Whether the account was linked
	 */
	function linkFB($fbid=false){
		global $sess, $db, $fb;
		if(!($sess instanceof Session)) return false;
		if(!($db instanceof DB)) $db = $sess->db();
		if(!($fb instanceof FB)) $fb = new FB;
		if(!$fbid || !is_numeric($fbid) || $fbid < 0){
			if($fb->logged()) $fbid = $fb->fbid; else return false;	
		}
		unset($_SESSION['user']['fbuser']);
		unset($this->fbuser);
		// We need to verify it has not been linked before
		$linked = $db->queryUniqueObject('SELECT id, name FROM users WHERE fbuser = \''.$fbid.'\'');
		if($linked){
			$sess->set_msg('Esta cuenta de Facebook ya esta vinculada a <a href="/user/'.$linked->id.'">'.$linked->name.'</a>');
			return false;
		}
		return $this->set('fbuser',$fbid,true);
	}
	/**
	 * Unlink Facebook account from the current user
	 * @return	boolean	Whether the account was unlinked
	 */
	function unlinkFB(){
		if(!$this->fb()) return true;
		global $sess, $db, $fb;
		if(!($sess instanceof Session)) return false;
		if(!($db instanceof DB)) $db = $sess->db();
		if(!($fb instanceof FB)) $fb = new FB;
		if(!$this->tw() && !$this->g('email')){
			// No puedes quedarte sin cuentas vinculadas
			$sess->set_msg('Debes tener alguna cuenta vinculada siempre. Vincula Twitter o especifica una contraseña para poder desvincular Facebook');
			return false;	
		}
		unset($_SESSION['user']['fbuser']);
		unset($_SESSION['fb']);
		unset($this->fbuser);
		$fb->facebook->destroySession();
		return $this->set('fbuser',0,true);
	}
	/** 
	 * link given Twitter account ID to the current user.
	 *
	 * It also checks whether it has already been linked
	 * @param	int	Twitter user ID, if false it will use currently logged in twitter account
	 * @return	boolean	Whether the account was linked
	 */
	function linkTW($twid=false){
		global $sess, $db, $tw;
		if(!($sess instanceof Session)) return false;
		if(!($db instanceof DB)) $db = $sess->db();
		if(!($tw instanceof Twitter)) $tw = new Twitter;
		$sess->debug('User::linkTW twid='.$twid);
		if(!$twid || !is_numeric($twid) || $twid < 0){
			if($tw->logged()) $twid = $tw->twid; else return false;	
		}
		unset($_SESSION['user']['twuser']);
		unset($this->twuser);
		// We need to verify it has not been linked before
		$linked = $db->queryUniqueObject('SELECT id, name FROM users WHERE twuser = \''.$twid.'\'');
		if($linked){
			$sess->set_msg('Esta cuenta de Twitter ya esta vinculada a <a href="/user/'.$linked->id.'">'.$linked->name.'</a>');
			return false;
		}
		return $this->set('twuser',$twid,true);
	}
	/**
	 * Unlink Twitter account for the current user
	 * @return	boolean	Whether the account was unlinked
	 */
	function unlinkTW(){
		if(!$this->tw()) return true;
		global $sess, $db;
		if(!($sess instanceof Session)) return false;
		if(!($db instanceof DB)) $db = $sess->db();
		if(!$this->fb() && !$this->g('email')){
			// No puedes quedarte sin cuentas vinculadas
			$sess->set_msg('Debes tener alguna cuenta vinculada siempre. Vincula Facebook o especifica una contraseña para poder desvincular Twitter');
			return false;	
		}
		unset($_SESSION['user']['twuser']);
		unset($_SESSION['twitter']);
		unset($this->twuser);
		return $this->set('twuser',0,true);
	}
	/**
	 * Check if user has Facebook linked
	 * @return	int	Facebook account ID or false if not linked
	 */
	function fb(){
		$fb = $this->g('fbuser',true);
		global $sess;
		$sess->debug('fb='.$fb);
		if(strlen($fb)>0 && $fb!=='0' && $fb!==0) return $fb;
		return false;
	}
	/**
	 * Check if user has Twitter linked
	 * @return	int	Twitter account ID or false if not linked
	 */
	function tw(){
		$tw = $this->g('twuser',true);
		global $sess;
		$sess->debug('tw='.$tw);
		if(strlen($tw)>0 && $tw!=='0') return $tw;
		return false;
	}
};