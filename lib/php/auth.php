<?php
/* AUTH Security System
 * La clase Auth gestion toda la seguridad de C13, mediante el sistema
 * de secciones y subsecciones. La tabla auth contiene las definiciones
 * de seguridad de todas las areas de C13 que tengan acceso restringido
 * Aqui NO se gestionan sesiones o identificacion, unicamente hay funciones
 * para seguridad
 * SECURITY WRAPPER
 */

// PRE and POST constants
// NEVER, EVER, EEEEVER CHANGE!
define("PRE", "!qp&$(");
define("POST", "_sa-63}{");

class Auth{
	// Security handling for C13
	
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
	
	function sendActivationMail($id,$name,$email,$secret){
		global $sess;
		if($id < 1 || !is_numeric($id) || strlen($email)<1 || strlen($secret)<1) return false;
		$code = sha1($secret);
		$msg = "Hola $name,\n\nBienvenido a Que Piensas!\nPara empezar a usar el servicio solo te falta verificar tu email:\nhttp://quepiensas.es/verify/$id/$code\n\nSi no te has registrado simplemente ignora este email\n\nAtentamente,\nEquipo Que Piensas";
		if($sess instanceof Session) $sess->debug('Sending email: '.$msg);
		return mail($email,'Activacion Que Piensas',$msg,"From:no-reply@quepiensas.es\r\n");
	}
	
	function emailExists($email){
		global $db, $sess;
		if(!($sess instanceof Session)) return false;
		if(!($db instanceof DB)) $db = $sess->db();
		if(!$sess->valid($email,'email')) return 1;
		$ids = $db->queryUniqueObject('(SELECT 1 AS type, users.id FROM users WHERE users.email LIKE \''.$email.'\') UNION (SELECT 2 AS type, unrev_users.id FROM unrev_users WHERE unrev_users.email LIKE \''.$email.'\')');
		if($ids) return $ids->type;
		return false;
	}
	
	function addUser($name,$email,$pass=0,$fbuser=0,$twuser=0){
		global $db, $sess;
		if(!($sess instanceof Session)) return false;
		if(!($db instanceof DB)) $db = $sess->db();
		if(strlen($pass)>0 && $pass !== '0') $pass = sha1(PRE.$pass.POST);
		$reg = $db->execute('INSERT INTO `users` (`id`, `fbuser`, `twuser`, `name`, `email`, `pass`, `ltime`, `jtime`, `ip`) VALUES (NULL, \''.$fbuser.'\', \''.$twuser.'\', \''.$name.'\', \''.$email.'\', UNHEX(\''.$pass.'\'), \''.time().'\', \''.time().'\', INET_ATON(\''.ip().'\'));');
		$usid = $db->lastInsertedId();
		if($usid>0){
			// Send welcome PM
			$msg = "Hola $name!\nBienvenido a Que Piensas, te saludo en nombre de todo el equipo! Siempre que tengas alguna duda podrás dirigirte a mi respondiendo a este privado. También puedes ponerte en contacto con soporte@quepiensas.es desde tu email\n\nEspero que disfrutes de la experiencia, y la compartas con tus amigos,\nUn abrazo";
			$db->execute('INSERT INTO `msg` (`thread`,`from`,`to`,`msg`,`status`,`timestamp`) VALUES (\'\',\'1\',\''.$usid.'\',\''.$msg.'\',\'0\',\''.time().'\');');
		}
		if($reg) return $usid;
		return false;
	}
	
	function changePass($usid,$pass){
		global $db, $sess;
		if(!($sess instanceof Session)) return false;
		if(!($db instanceof DB)) $db = $sess->db();
		if(!$sess->valid($usid,'int')) return false;
		if(strlen($pass)>0 && $pass !== '0') $pass = sha1(PRE.$pass.POST); else return false;
		return $db->execute('UPDATE users SET pass = UNHEX(\''.$pass.'\') WHERE id = \''.$usid.'\' LIMIT 1');
	}
};