<?php
// CLASS person
// Handles CRUD for persons
// Requires function "clean" to sanitize string

class Person{
	// Variables
	var $pid;
	var $fbid;
	var $name;
	var $visits;
	var $comments;
	var $location;
	
	function Person($pid,$name=false){
		global $sess;
		// Constructor
		if(!$sess->valid($pid,'int')) return false;
		if(!$pid) $pid = 0;
		if(($pid == 0) && strlen($name)>0){
			$sess->debug('Creating new person');
			$pid = $this->create($name);
		}
		if(!$pid){
			$sess->debug('$pid = false. Constructor failed');
			return false; // Posiblemente no haya sido posible conectar con base de datos
		}
		if($name) $this->name = $name;
		if($pid > 0) $this->pid = $pid;
		$sess->debug('Finished constructor: PID='.$this->pid.', Name='.$this->name);
	}
	function exists(){
		if($this->pid == 0) return false;
		global $sess, $db;
		if(!($db instanceof DB)){
			$db = $sess->db();
			if(!($db instanceof DB)) return false;
		}
		$id = $db->queryUniqueValue('SELECT `id` FROM `personas` WHERE `id` = \''.$this->pid.'\'');
		if($id==$this->pid) return true;
		return false; 
	}
	// Guardar comentario. Devuelve un array con la ID del comentario y el comentario
	function post($msg,$rep,$name,$email,$ident){
		if($this->pid==0) return false;
		global $sess, $db,$user;
		if(!($db instanceof DB)) $db = $sess->db();
		if(!($db instanceof DB) || !($user instanceof User)) return false;
		// Limpieza
		$msg = clean($_POST['msg']); // De style.php
		$name = strtolower($sess->clean($name));
		if($name = 'anonimo' || $name == 'anónimo') $name = '';
		if($email = $sess->valid($email,'email')){ $user->set('email',$email); }else{ $email = ''; }
		if($ident!=='0' && $ident!=='1') $ident = 0;
		if(!($rep = $sess->valid($rep,'int'))) $rep=0; // $rep es lo de responder
		// SPAM Notice, for now, if there is a link we consider it spam:
		$spam = 0; // Spam inicial
		$links = array();
		if(preg_match("/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/", $msg,$links)) $spam = sizeof($links); // Numero de links => votos de spam iniciales
		if($spam<4) $spam = 0;
		// Insercion
		$q = $db->execute('INSERT INTO `comments` (`id`, `pid`, `usid`, `msg`, `timestamp`,`spam`, `ident`) VALUES (NULL, \''.$this->pid.'\', \''.$user->id.'\', \''.$msg.'\', \''.time().'\',\''.$spam.'\', \''.$ident.'\');');
		if(!$q) return false;
		$cid = $db->lastInsertedId();
		if($rep>0 && $cid>0){
			// Guardar referencia de respuesta
			// Comprobamos si responde a una respuesta, y de ser asi, simplemente responde al thread general
			$thread = $db->queryUniqueValue('SELECT `rid` FROM `replies` WHERE `id` = \''.$rep.'\'');
			if($thread && $thread>0) $rep = $thread;
			$db->execute('INSERT INTO `replies` (`id`, `rid`) VALUES (\''.$cid.'\', \''.$rep.'\');');	
		}
		return array($cid,$msg);
	}
	function create($name,$check=false){
		global $sess, $db,$user;
		if(!($db instanceof DB)) $db = $sess->db();
		if(!($db instanceof DB) || !($user instanceof User)) return false;
		if($check && !exists()) return false;
		if(!$name || strlen($name)<1) return false;
		// Remove multiple spaces in name:
		$name = preg_replace("/[[:blank:]]+/"," ",$name);
		$db->execute('INSERT INTO `personas` (`id`, `usid`, `name`, `visits`, `timestamp`) VALUES (NULL, \''.$user->id.'\', \''.$name.'\', \'0\', \''.time().'\');');
		$pid = $db->lastInsertedId();
		$sess->debug('Creada persona pid='.$pid);
		return $pid;
	}
	function getData(){
		global $sess, $db;
		$sess->debug('Person::getData() $this->pid = '.$this->pid);
		if($this->pid == 0) return false;
		if(!($db instanceof DB)){
			$db = $sess->db();
			if(!($db instanceof DB)) return false;
		}
		$data = $db->queryUniqueObject('SELECT personas.name, personas.visits, personas.fbid FROM personas, users WHERE personas.id = \''.$this->pid.'\' AND personas.usid = users.id');
		if(!$data){
			$sess->debug('Query returned false');
			return false;
		}
		$this->name = $data->name;
		$this->fbid = $data->fbid;
		$this->visits = $data->visits;
		$this->location['city'] = $data->city;
		$this->location['region'] = $data->region;
		$this->location['country'] = $data->country;
		return true;	
	}
	function addVisit(){
		if($this->pid == 0) return false;
		global $sess, $db;
		if(!($db instanceof DB)){
			$db = $sess->db();
			if(!($db instanceof DB)) return false;
		}
		return $db->execute('UPDATE personas SET visits = visits+1 WHERE id = \''.$this->pid.'\' LIMIT 1');
	}
	function addRelation($count=1){
		if($this->pid == 0) return false;
		global $sess, $db, $user;
		if(!($db instanceof DB)){
			$db = $sess->db();
			if(!($db instanceof DB)) return false;
		}
		$current = $db->queryUniqueValue('SELECT relation FROM relations WHERE pid = \''.$this->pid.'\' AND usid = \''.$user->id.'\'');
		if($current && $current>0){
			return $db->execute('UPDATE `relations` SET relation = \''.($current+$count).'\' WHERE pid = \''.$this->pid.'\' AND usid = \''.$user->id.'\' LIMIT 1;');
		}else{
			return $db->execute('INSERT INTO `relations` (`pid`, `usid`, `relation`, `follow`, `timestamp`) VALUES (\''.$this->pid.'\', \''.$user->id.'\',3, \'0\', \''.time().'\');');
		}
	}
};