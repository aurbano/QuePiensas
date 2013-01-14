<?php
// Twitter Connection class
// By Alejandro U. Alvarez
// Version 2
// Requires SESSION class for debugging

include('EpiCurl.php');
include('EpiOAuth.php');
include('EpiTwitter.php');

class Twitter{
	var $twitter;
	var $twid = false;
	protected $appid = 'RUIPLnk0zQGnA20iUnzVUw';
	protected $secret = 'c28YlpEb8ca5Uku0nJWHScxJNJK0EJaN07SlFpEcA8';
	protected $name;
	protected $pic_normal;
	protected $pic_bigger;
	protected $pic;
	protected $oauth_token;
	protected $oauth_secret;
	
	// Constructor
	function Twitter($twid=false){
		// First: Load from Session
		global $sess;
		$this->loadFromSession();
		if(strlen($this->oauth_token)>0) return $this->init();
		// Second: DB
		if($twid>0){
			$sess->debug('Loading Twitter data from DB cache for twid='.$twid);
			$this->twid = $twid;
			$this->getDB();
		}
		return $this->init();
	}
	
	// Start up Twitter API
	function init(){
		global $sess;
		$sess->debug('Twitter Constructor');		
		// Start Twitter object
		try{
			if(strlen($this->oauth_token)>0){
				$sess->debug('Using tokens to connect');
				$this->twitter = new EpiTwitter($this->appid,$this->secret,$this->oauth_token,$this->oauth_secret);
				$this->loginUser();
			}
			else $this->twitter = new EpiTwitter($this->appid,$this->secret);
		}catch(Exception $e){
			$sess->debug('ERROR: '.$e);
			$sess->set_msg('No ha sido posible conectar con Twitter');
			return false;	
		}
	}
	
	function loadFromSession(){
		global $sess;
		// Loads FB data from Session
		// To ensure session integrity, it depends on fbid
		if(!isset($_SESSION['twitter'])) return false;
		$attrs = array('name','pic_normal','pic_bigger','pic','oauth_token','oauth_secret');
		for($i=0;$i<count($attrs);$i++){ 
			if(strlen($_SESSION['twitter'][$attrs[$i]])>0) $this->$attrs[$i] = $_SESSION['twitter'][$attrs[$i]];	
		}
	}
	
	// Update DB cache (Fields & values can be arrays)
	function updateDB($fields,$values){
		// Build update query
		if(!$this->twid || $this->twid<1) return false;
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
		return $db->execute('UPDATE `twitter` SET '.$update.' WHERE `twid`= \''.$this->twid.'\'');
	}
	
	// Load data from DB cache
	function getDB($what=false){
		// Carga los datos de la base de datos
		// Para disponer de ellos si no has iniciado sesion
		// con la red social
		if(!$this->twid || $this->twid<1) return false;
		global $db;
		if(!($db instanceof DB)){
			global $sess;
			if(!($sess instanceof Session)) return false;
			$db = $sess->db();	
		}
		$data = $db->queryUniqueObject('SELECT `name`, `pic`, `oauth_token`, `oauth_token_secret` FROM twitter WHERE twid = \''.$this->twid.'\'');
		// Store data:
		$this->set('name',$data->name);
		$this->set('oauth_token',$data->oauth_token);
		$this->set('oauth_secret',$data->oauth_token_secret);
		$this->set('pic',$data->pic);
		
		if($what) return $this->$what;
	}
	
	function getFromTwitter($what){
		if(!$this->logged()) return false;
		global $sess;
		try{
			$info = $this->twitter->get_accountVerify_credentials();
			$sess->debug('Gotten from twitter: screen_name='.$info->screen_name.', pic='.$info->pic_normal);
			// Every call gets all possible options, to minimize calls
			$this->set('name',$info->name);
			$this->set('pic_normal',$info->profile_image_url);
			return $info->$what;
		}catch(Exception $e){$sess->debug('Error al conectar a twitter: '.$e); $sess->set_msg('No ha sido posible conectar con Twitter'); return false;}
		return false;
	}
	
	function get($what,$obtain=true){
		if(isset($_SESSION['twitter'][$what]) && strlen($_SESSION['twitter'][$what])>0) return $this->set($what,$_SESSION['twitter'][$what]);
		if(!$obtain) return $this->$what;
		$fromTwitter = $this->getFromTwitter($what);
		if($fromTwitter) return $this->set($what,$fromTwitter);
		return $this->getDB($what);
	}
	
	function set($what,$data,$updateDB=false){
		  $this->$what = $_SESSION['twitter'][$what] = $data;
		if(!$updateDB) return $this->$what;
		// Now update DB
		$this->updateDB($what, $data);
		return $this->$what;
	}
	
	function name(){
		if(!$this->name){
			$this->set('name',$this->getFromTwitter('screen_name'));
			if($this->name) return $this->name;
			return $this->getDB('name');
		}
		return $this->name;	
	}
	function pic($size=false){
		global $sess;
		switch($size){
			case 'normal':
				$sess->debug('Normal pic');
				return $this->get('pic_normal');
				break;
			case 'bigger':
				$sess->debug('Bigger');
				if($this->get('pic_bigger')) return $this->pic_bigger;
				$this->set('pic_bigger',str_replace('_normal','_bigger',$this->get('pic_normal')));
				return $this->pic_bigger;
				break;
			case false:
			case '':
			default:
				$sess->debug('Default');
				if($this->get('pic')) return $this->pic;
				$this->set('pic',str_replace('_normal','',$this->get('pic_normal')));
				return $this->pic;
		}
	}
	
	// Determine whether user has logged in Facebook
	function logged(){
		global $sess, $user;
		try{  
			$info = $this->twitter->get_accountVerify_credentials();
			if($info && $info->id){
				$user->set('twuser',$info->id);
				return true;
			}
		}catch(Exception $e){ $sess->debug('Error: '.$e); return false; }
		$sess->debug('Cant check if logged');
		return false;
	}
	
	// Get account ID from tokens
	function loginUser(){
		global $sess, $user;
		try{
			$info = $this->twitter->get_accountVerify_credentials();
			if($info->id>0){
				$this->set('twid',$info->id);
				$user->set('twuser',$info->id);
				$sess->debug('Logged Twitter user: '.$this->twid);
			}else{
				$sess->debug('No valid user returned');
				$this->set('twid',0);	
			}
			return $this->twid;
		}catch(Exception $e){ $sess->debug('Exception raised: '.$e); return false; }
		$sess->debug('Nothing happened');
		return false;	
	}
	
	// Check if twitter ID is asigned to a user
	function checkTWuser($twid=false){
		global $sess, $db;
		if(!($sess instanceof Session)) return false;
		$sess->debug('');
		if(!($db instanceof DB)) $db = $sess->db();
		if(!$twid) $twid = $this->twid;
		$sess->debug('twid='.$twid);
		if(!$twid) return false;
		$usid = $db->queryUniqueValue('SELECT id FROM users WHERE twuser = \''.$twid.'\'');
		$sess->debug('TW user for twid='.$twid.' is usid='.$usid);
		return $usid;
	}
	
	// Link TWuser to a QuePiensas user
	function addTWuser($oauth, $oauth_secret, $checked=false){
		global $sess, $db;
		if(!($sess instanceof Session)) return false;
		if(!($db instanceof DB)) $db = $sess->db();
		if(!$this->logged()) return false;
		
		if(!$checked){
			// Check if twitter ID is asigned to a user
			$usid = $this->checkTWuser($this->twid);
			if($usid && is_numeric($usid) && $usid>0) return false;
		}
		$sess->debug('Storing twitter data in DB: Name='.$this->name().', Pic='.$this->pic());
		
		// INSERT or UPDATE into DB cache
		$db->execute('REPLACE INTO `twitter` (`twid`, `name`, `pic`, `oauth_token`, `oauth_token_secret`) VALUES (\''.$this->twid.'\', \''.$this->name().'\', \''.$this->pic().'\', \''.$oauth.'\', \''.$oauth_secret.'\');');
		return true;
	}
	
	// Return Twitter login link
	function loginLink(){
		try{
			$ret = $this->twitter->getAuthenticateUrl();
		}catch(Exception $e){ $ret = '#';}
		return $ret;	
	}
};