<?php
/**
 * Auth security class
 */
/**
 * AUTH Security System: Handle all security concerns of the website.
 *
 * Auth class manages all security in the website. It handles session ops (login/logout/register)
 * as well as permission checks for subsections and actions.
 * This doesn't manage session control, it only ensures that whatever is stored in session
 * is valid and secure.
 * @author Alejandro U. Alvarez
 * @version 1.2
 * @package Security
 */
class Auth{	
	/**
	 * Secure login using email/pass
	 *
	 * All variables are escaped and validated here
	 * @param string User email
	 * @param string User password
	 * @return boolean True if login was successful
	 */
	function login($email,$pass){
		// Checks login
		global $db, $sess;
		if(!($sess instanceof Session)){
			return false;
		}
		$sess->debug('AUTH class, login method');
		if(!($db instanceof DB)){ 
			$db = $sess->db();
		}
		$sess->debug('Checking email='.$email);
		// $pass is salted to further protect users passwords
		$pass = sha1(PRE.$pass.POST);
		if(strlen($email)<4 || strlen($pass)<4) return false;
		$sess->debug('User & Pass OK (User: '.$email.' - Pass: '.$pass.'). Check DB');
		$usid = $db->queryUniqueValue('SELECT users.`id` FROM `users` WHERE `email` LIKE \''.$email.'\' AND `pass` LIKE UNHEX(\''.$pass.'\')');
		$sess->debug('Returned ID: '.$usid);
		if(is_numeric($usid) && $usid>0) return $usid;
		$sess->debug('Return false');
		return false;
	}
	
	/**
	 * Register a new user
	 *
	 * All variables are escaped and validated here
	 * @param string Email
	 * @param string Name
	 * @param string Password
	 * @param boolean Whether the email has already been checked to avoid duplicates
	 * @param boolean Whether an actiation mail should be sent to the provided email
	 * @return boolean True if registration was successful
	 */
	function register($email,$name,$pass,$checkedEmail=false,$rev=false){
		global $db, $sess;
		if(!($sess instanceof Session)) return false;
		if(!($db instanceof DB)) $db = $sess->db();
		if(!$sess->valid($email,'email')) return false;
		$name = trim(addslashes(htmlspecialchars($name,ENT_COMPAT,'UTF-8')) );
		$pass = sha1(PRE.$pass.POST); // Added security
		$level=1;
		// Check for existing user
		if(!$checkedEmail) if($this->emailExists($email)>0) return false;
		// Insert data in DB
		if(!$rev){
			// User not verified
			// Create verification code
			$secret = rand(10000,100000);
			// Insertar en tabla temporal
			$db->execute('INSERT INTO `unrev_users` (`id`, `name`, `email`, `pass`, `timestamp`, `secret`) VALUES (NULL, \''.$name.'\', \''.$email.'\', UNHEX(\''.$pass.'\'), \''.time().'\', \''.$secret.'\');');
			$id = $db->lastInsertedId();
			$sess->debug('New user ID:'.$id.' with email='.$email.', verification secret:'.$secret.' (From code: '.$code.')');
			if($id>0) return $this->sendActivationMail($id,$name,$email,$secret);
			else return false;
		}else return $this->addUser($name, $email, $pass);
	}
	
	/**
	 * Send an activation mail to the provided user
	 * @param int User ID
	 * @param string	User name
	 * @param string	Email address
	 * @param secret	Secret code to be used for verification
	 * @return boolean	Whether the email was sent
	 */
	function sendActivationMail($id,$name,$email,$secret){
		global $sess;
		if($id < 1 || !is_numeric($id) || strlen($email)<1 || strlen($secret)<1) return false;
		$code = sha1($secret);
		$msg = "Hola $name,\n\nBienvenido a Que Piensas!\nPara empezar a usar el servicio solo te falta verificar tu email:\nhttp://quepiensas.es/verify/$id/$code\n\nSi no te has registrado simplemente ignora este email\n\nAtentamente,\nEquipo Que Piensas";
		if($sess instanceof Session) $sess->debug('Sending email: '.$msg);
		return mail($email,'Activacion Que Piensas',$msg,"From:no-reply@quepiensas.es\r\n");
	}
	
	/**
	 * Checks if an email already exists on the database
	 * @param string Email address to be checked
	 * @return boolean Whether the email is already in use
	 */
	function emailExists($email){
		global $db, $sess;
		if(!($sess instanceof Session)) return false;
		if(!($db instanceof DB)) $db = $sess->db();
		if(!$sess->valid($email,'email')) return 1;
		$ids = $db->queryUniqueObject('(SELECT 1 AS type, users.id FROM users WHERE users.email LIKE \''.$email.'\') UNION (SELECT 2 AS type, unrev_users.id FROM unrev_users WHERE unrev_users.email LIKE \''.$email.'\')');
		if($ids) return $ids->type;
		return false;
	}
	
	/**
	 * Add a username, similar to register but used to add users automatically
	 * @param string User name
	 * @param string Email address
	 * @param string Password
	 * @param int Facebook account ID (Use NULL)
	 * @param int Twitter account ID
	 * @regurn boolean Whether the account was created
	 */
	function addUser($name,$email='NULL',$pass=0,$fbuser='NULL',$twuser='NULL'){
		global $db, $sess;
		if(!($sess instanceof Session)) return false;
		if(!($db instanceof DB)) $db = $sess->db();
		if(strlen($pass)>0 && $pass !== '0') $pass = sha1(PRE.$pass.POST);
		if(!$fbuser || $fbuser=='0' || $fbuser < 1) $fbuser = 'NULL';
		if(!$twuser || $twuser=='0' || $twuser < 1) $twuser = 'NULL';
		if(!$email || $email == '') $email = 'NULL';
		$reg = $db->execute('INSERT INTO `users` (`id`, `fbuser`, `twuser`, `name`, `email`, `pass`, `ltime`, `jtime`, `ip`) VALUES (NULL, '.$fbuser.', '.$twuser.', \''.$name.'\', '.$email.', UNHEX(\''.$pass.'\'), \''.time().'\', \''.time().'\', INET_ATON(\''.ip().'\'));');
		$usid = $db->lastInsertedId();
		if($usid>0){
			// Send welcome PM
			$this->sendWelcomePM($usid);
		}
		if($reg) return $usid;
		return false;
	}
	
	/**
	 * Send welcome PM
	 * @param int User ID
	 * @param string New password
	 * @return boolean Whether the password was changed
	 */
	function sendWelcomePM($usid){
		global $sess;
		$db = $sess->db();
		$db->execute('INSERT INTO `msgThread` (`from`, `to`, `ident`, `status`, `com`) VALUES (1,\''.$usid.'\',3,\'0\',0)');
		$thread = $db->lastInsertedId();
		$msg = "{[@101@]}";
		$db->execute('INSERT INTO `msg` (`tid`,`usid`,`msg`,`timestamp`) VALUES ('.$thread.',1,\''.$msg.'\',\''.time().'\');');
		return true;
	}
	
	/**
	 * Changes a user password
	 * @param int User ID
	 * @param string New password
	 * @return boolean Whether the password was changed
	 */
	function changePass($usid,$pass){
		global $db, $sess;
		if(!($sess instanceof Session)) return false;
		if(!($db instanceof DB)) $db = $sess->db();
		if(!$sess->valid($usid,'int')) return false;
		if(strlen($pass)>0 && $pass !== '0') $pass = sha1(PRE.$pass.POST); else return false;
		return $db->execute('UPDATE users SET pass = UNHEX(\''.$pass.'\') WHERE id = \''.$usid.'\' LIMIT 1');
	}
};
/**
 * Salt for passwords, initial part. Never change once live
 * @const PRE
 */
define("PRE", "!qp&$(");
/**
 * Salt for passwords, last part. Never change once live
 * @const POST
 */
define("POST", "_sa-63}{");