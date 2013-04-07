<?php
/**
 * Facebook abstraction layer
 */

/**
 * Include the Facebook API
 */
try{
	require_once('facebook/facebook.php');
}catch(Exception $e){  }

/**
 * Facebook Abstraction layer
 * It uses the official Facebook PHP API
 * @author Alejandro U. Alvarez
 * @version 2.0
 * @package Social
 * @subpackage Facebook
 */
class FB{
	/**
	 * Facebook API object
	 */
	var $facebook;
	/**
	 * Facebook account ID
	 */
	var $fbid = false;
	/**
	 * Facebook App ID
	 * @access protected
	 */
	protected $appid = '174758345954243';
	/**
	 * Facebook App secret
	 * @access protected
	 */
	protected $secret = '4ea51f49f6c49bcca3c42ad97e045174';
	/**
	 * Facebook user name
	 * @access protected
	 */
	protected $name;
	/**
	 * Facebook associated email address
	 * @access protected
	 */
	protected $email;
	/**
	 * Facebook profile picture: Original size
	 * @access protected
	 */
	protected $pic_big;
	/**
	 * Facebook profile picture: Squared version
	 * @access protected
	 */
	protected $pic_square;
	
	/**
	 * Creates a new Facebook Object
	 * @param int facebook Account ID
	 * @return object Facebook Object
	 */
	function FB($fbid=false){
		// In case we need to rebuild
		return $this->init($fbid);
	}
	
	/**
	 * Initialize Facebook Object
	 * @param int Facebook Account ID
	 * @see FB
	 */
	function init($fbid=false){
		global $sess;
		$sess->debug('Facebook constructor');
		if(!$this->facebook){
			$config = array();
			$config['appId'] = $this->appid;
			$config['secret'] = $this->secret;
			$this->facebook = new Facebook($config);
			// Load user if available
			try{
				if(!$this->fbid) $this->fbid = $this->facebook->getUser();
			}catch(Exception $e){ $this->fbid = $fbid; $this->getDB(); }
		}
		if(!$this->fbid && $fbid>0){
			$this->fbid = $fbid;	
			$this->getDB();
		}
		// Load cached data
		$this->loadFromSession();
	}
	
	/**
	 * Get an attribute from facebook
	 * @param string Attribute name
	 * @return string Attribute value
	 */
	function get($what){
		if(isset($_SESSION['facebook'][$what]) && strlen($_SESSION['facebook'][$what])>0) return $this->$what = $_SESSION['facebook'][$what];
		if($this->getFromFacebook($what)) return $this->$what;
		return $this->getDB($what);
	}
	
	/**
	 * Set an attribute
	 * @param string Attribute name
	 * @param string Attribute value
	 * @param boolean Whether to update the Database
	 * @return string Attribute value
	 */
	function set($what,$data,$updateDB=false){
		$this->$what = $_SESSION['facebook'][$what] = $data;
		if(!$updateDB) return $this->$what;
		// Now update DB
		$this->updateDB($what, $data);
		return $this->$what;
	}
	
	/**
	 * Load a Facebook account from a user Session
	 */
	function loadFromSession(){
		// Loads FB data from Session
		// To ensure session integrity, it depends on fbid
		if(!isset($_SESSION['facebook'])) return false;
		$attrs = array('name','email','pic_big','pic_small','pic_square');
		for($i=0;$i<count($attrs);$i++){
			if(strlen($_SESSION['facebook'][$attrs[$i]])>0) $this->$attrs[$i] = $_SESSION['facebook'][$attrs[$i]];	
		}
	}
	
	/**
	 * Update DB cache (Fields & values can be arrays)
	 * @param array Fields to be updated, it can also be a string to update only one field
	 * @param array Values, in the same format as the fields
	 * @return boolean Whether the operation was successful
	 */
	function updateDB($fields,$values){
		// Build update query
		if(!$this->fbid || $this->fbid<1) return false;
		global $sess, $db;
		if(!($sess instanceof Session)) return false;
		// update facebook SET 1 = one, 2 = two
		if(!is_array($fields)){
			$update = '`'.$fields.'`=\''.$values.'\'';
		}else{
			$total = count($fields);
			$update = '';
			for($i=0;$i<$total;$i++){
				$update .= '`'.$fields[$i].'`=\''.$values[$i].'\'';
				if($i<$total-1) $update .= ',';
			}
		}
		$sess->debug('UPDATING: '.$update);
		if(!($db instanceof DB)) $db = $sess->db();
		return $db->execute('UPDATE `facebook` SET '.$update.' WHERE `fbid`= \''.$this->fbid.'\'');
	}
	
	/**
	 * Get data from Facebook
	 * @param string Data to be fetched
	 * @access private
	 * @return string Data from facebook, false if not available
	 */
	function getFromFacebook($data){
		$fb = $this->init();
		try{  
			$user = $this->facebook->api('/me');
			if($user[$data] && strlen($user[$data])>0) return $this->set($data, $user[$data]);;
			return false;
		}catch (Exception $e){ return false; }
		return false;
	}
	
	/**
	 * Get data from Database cache
	 * @param string Data to be fetched
	 * @access private
	 * @return string Data from DB, false if not available
	 */
	function getDB($what=false){
		// Carga los datos de la base de datos
		// Para disponer de ellos si no has iniciado sesion
		// con la red social
		if(!$this->fbid || $this->fbid<1) return false;
		global $db;
		if(!($db instanceof DB)){
			global $sess;
			if(!($sess instanceof Session)) return false;
			$db = $sess->db();	
		}
		$data = $db->queryUniqueObject('SELECT `name`, `email`, `pic_big`, `pic_square` FROM facebook WHERE fbid = \''.$this->fbid.'\'');
		// Store data:
		$this->set('pic_big',$data->pic_big);
		$this->set('pic_square',$data->pic_square);
		$this->set('name',$data->name);
		$this->set('email',$data->email);
		
		if($what) return $this->$what;
	}
	
	/**
	 * Determine whether user has logged in Facebook
	 * @return boolean Facebook session state
	 */
	function logged(){
		global $user;
		if($this->fbid==$this->facebook->getUser()){
			$user->set('fbuser',$this->fbid);
			return true;
		}
		return false;
	}
	
	/**
	 * Check if FB user is on database
	 * @param int [Optional] Facebook account ID
	 * @return int QuePiensas user ID if found, false otherwise
	 */
	function checkFBuser($fbid=false){
		global $sess, $db;
		if(!($sess instanceof Session)) return false;
		$sess->debug('FB class, checkFBuser method');
		if(!($db instanceof DB)) $db = $sess->db();
		if(!$fbid) $fbid = $this->fbid;
		$usid = $db->queryUniqueValue('SELECT id FROM users WHERE fbuser = \''.$fbid.'\'');
		$sess->debug('FB user for fbid='.$fbid.' is usid='.$usid);
		return $usid;
	}
	
	/**
	 * Update or Insert data for a facebook user (Using REPLACE)
	 * @param boolean Whether checkFBuser has been run
	 */
	function addFBuser($checked=false){
		global $sess, $db;
		if(!($sess instanceof Session)) return false;
		if(!($db instanceof DB)) $db = $sess->db();
		if(!$checked){
			$usid = $this->checkFBuser($this->fbid);
			if($usid && is_numeric($usid) && $usid>0) return false;
		}
		
		// UPDATE or INSERT facebook data in DB cache
		$db->execute('REPLACE INTO `facebook` (`fbid`, `name`, `email`, `pic_big`, `pic_square`, `code`, `access_token`) VALUES (\''.$this->fbid.'\', \''.$this->name().'\', \''.$this->email().'\', \''.$this->pic().'\', \''.$this->pic('square').'\', \''.$_SESSION['fb_'.$this->appid.'_code'].'\', \''.$_SESSION['fb_'.$this->appid.'_access_token'].'\');');
	}
	
	/**
	 * Check if a facebook account is already in the database
	 * @param int Facebook account id
	 */
	function checkDB($fbid=false){
		if(!$fbid && $this->logged()) $fbid = $this->fbid;
		global $sess, $db;
		if(!($db instanceof DB)){
			if(!($sess instanceof Session)) return false;
			$db = $sess->db();	
		}
		$res = $db->queryUniqueValue('SELECT fbid FROM facebook WHERE fbid = \''.$fbid.'\'');
		if($res && $res>0) return true;
		return false;
	}
	
	/**
	 * Get Facebook name
	 * @return string Facebook name
	 */
	function name(){
		if(!$this->name) return $this->get('name');
		return $this->name;	
	}
	
	/**
	 * Get Facebook associated email
	 * @return string associated email
	 */
	function email(){
		if(!$this->email) return $this->get('email');
		return $this->email;	
	}
	
	/**
	 * Get Facebook profile picture
	 * @param string Image type: big or square
	 * @return string User profile pic
	 */
	function pic($type='big'){
		global $sess;
		$types = array('big','square');
		if(!in_array($type,$types)) $type = 'big';
		$what = 'pic_'.$type;
		$sess->debug($what);
		if($this->get($what)) return $this->get($what);
		if($this->logged()) return $this->getPic($type);
		// From DB
		return $this->getDB($what);
	}
	
	/**
	 * Get Facebook profile picture from facebook
	 * @param string Image type: big or square
	 * @return string User profile pic
	 */
	function getPic($type='big'){
		$types = array('big','square');
		if(!in_array($type,$types)) $type = 'big';
		$which = 'pic_'.$type;
		$fb = $this->init();
		try{
			if(!$this->fbid) $this->fbid = $this->facebook->getUser();
			if(!$this->fbid) return false;
			$param  =   array(
				'method'    => 'fql.query',
				'query'     => "select pic_$type from user where uid=me()",
				'callback'  => ''
			);
			$result = $this->facebook->api($param);
			$this->$which = $result[0]['pic_'.$type];
		}catch (Exception $e){ $this->$which = ''; }
		return $this->set($which, $this->$which);
	}
	
	/**
	 * Get Facebook login link
	 * @return string Login link or # if not available
	 */
	function fbLogin(){
		// Get login URL
		$params = array(
		  scope => 'email, publish_stream, user_photos',
		  redirect_uri => 'http://quepiensas.es/do/fbAuth'
		);
		try{ $link = $this->facebook->getLoginUrl($params);
		}catch(Exception $e){ $link = '#'; }
		
		return $link;	
	}
	
	/**
	 * Generate a txt file with all friends from the user
	 * @return true
	 */
	function getFriends(){
		if(!$this->fbid || $this->fbid < 1) return false;
		try{
			$param  =   array(
				'method'    => 'fql.query',
				'query'     => "SELECT uid, name, pic_big, mutual_friend_count FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1 = me())",
				'callback'  => ''
			);
			$result = $this->facebook->api($param);
		}catch (Exception $e){ return false; }
		$total = sizeof($result);
		$saveFile = './lib/tmp/facebook/'.$this->fbid.'.txt';
		$fh = fopen($saveFile, 'w', 1);
		if(!$fh) return false;
		for($i=0; $i<$total; $i++){
			fwrite($fh, $result[$i]['uid'].','.$this->fbid.','.$result[$i]['mutual_friend_count'].','.addslashes(htmlspecialchars($result[$i]['name'],ENT_COMPAT,'UTF-8')).','.addslashes($result[$i]['pic_big'])."\n");	
		}
		fclose($fh);
		return true;
	}
};