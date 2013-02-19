<?php
/**
 * Form processing and AJAX handler
 *
 * Archivo de gestion de las llamadas de AJAX. Es decir, todas las respuestas que salgan de este archivo
 * tienen que salir en XML, JSON, o un dato solo. No se puede redirigir con header.
 * Las siguientes variables son necesarias:
 *  	- type: Que accion queremos hacer, debera estar presente en el switch
 *	Obligatoriamente se tiene que usar POST, ya que mejora la seguridad de la aplicacion
 */
if(!isset($_POST['type'])){ header('Location: /'); die(); }

include('lib/php/session.php');

$type = $sess->clean($_POST['type']); // limpieza ;)

function finish($msg,$done=false,$redir=false,$json=false){
	global $sess;
	$msg = stripslashes($msg);
	// Debug msg before moving on
	$sess->createDebugFile($msg);
	if($_POST['ajax']=='true'){
		if(!$done) $array = array("msg"=>$msg,"done"=>"false");
		else $array = array("msg"=>$msg,"done"=>"true");
		if($json) $array = array_merge($array,$json);
		die(json_encode($array));
	}else{
		if(strlen($msg)>0) $sess->set_msg($msg);
		if(!$redir && $_POST['next']) $redir = $_POST['next'];
		if($redir) header('Location: '.$redir);
		else header('Location: /');
		die($msg);
	}
}

switch($type){
	case('saveComment'):
		include('lib/php/style.php');
		include('lib/php/person.php');
	// -------------      Anti spam     ----------------------------------------//
		// Method 1: token, all comments will need a unique token
		// that should make it impossible to automate posting of comments
		// the token will depend on person ID, useragent and IP plus random seed
		
		// Method 2: time since last comment
		// there will be a minimum of 5 seconds between comments.
		if(!isset($_SESSION['lastComment'])) $_SESSION['lastComment'] = time();
		else if(time() - $_SESSION['lastComment'] < 5){
			finish('Ha ocurrido un error');
		}
		$_SESSION['lastComment'] = time();
	// --------------------------------------------------------------------------//
		if($_POST['pid']==0){
			// Persona nueva, verifica nombre
			if(strlen($sess->clean($_POST['pname']))>4){
				// Nombre valido, creamos una nueva persona
				// $p sera el objeto de la nueva persona
				$p = new Person(0,$_POST['pname']);
				$new = true;
			}else finish('Ha ocurrido un error');
		}else $p = new Person($_POST['pid']); // Iniciamos un objeto Person con la id
		if(!$p) finish('ID no valida'); // Verifico
		if($p->pid<1) finish('Ha ocurrido un error, prueba dentro de un rato'); // La ID no puede ser 0
		$name = $_POST['name'];
		// Nombre de usuario que postea:
		if($name=='anonimo' || $name == 'annimo'){ $name = ''; }else{ $user->set('name',$name); }
		$name = '<a href="/user/'.$user->id().'" title="Ir a su perfil">'.$name.'</a>'; // Enlace
		if($_POST['ident'] == '0') $name = 'Anonimo'; // Modo Anonimo
		$rid = $_POST['rid']; // Respuesta
		$msgType = 0; // Publico=0, Privado=1
		if(isset($_POST['msgType']) && $_POST['msgType']==1 && $rid>0) $msgType = 1;
		if($_POST['rid']>0 && $sess->valid($_POST['rid'],'int')) $rid = $_POST['rid'];
		if($p->exists()){
			if($msgType==0){
				if($msg = $p->post($_POST['msg'],$rid,$_POST['name'],$_POST['email'],$_POST['ident'])){
					$pic = $user->pic();
					if($_POST['ident']=='0') $pic = 'http://img.quepiensas.es/noimage.png';
					if(strlen($name)<1 || !$name) $name = 'Anónimo';
					if($new) finish($msg[1],true,'/'.$p->pid,array('time'=>'Ahora mismo','pid'=>$p->pid,'name'=>$name,'pic'=>$pic,'usid'=>$user->id(),'id'=>$msg[0]));
					finish($msg[1],true,'/'.$p->pid,array('time'=>'Ahora mismo','name'=>$name,'pic'=>$pic,'usid'=>$user->id(),'id'=>$msg[0]));
				}else{
					finish('No se pudo guardar el comentario');
				}
			}else{
				// Mensaje privado
				// Se envia al autor de $rid
				$db = $sess->db();
				$toData = $db->queryUniqueObject('SELECT usid, ident FROM comments WHERE id = \''.$rid.'\'');
				if($toData->usid == $user->id()) finish('No puedes enviarte un mensaje privado a ti mismo!');
				if(!$toData || $toData->usid<1) finish('No existe el usuario a quien respondes ('.$toData->usid.')');
				// IDENT GUIDE
				//	# ->	To			From
				//	0 ->	Public		Public
				//	1 ->	Private		Public
				//	2 ->	Public		Private
				//	3 ->	Private		Private
				// $_POST['ident'] is From (0 is Public, 1 is Private)
				// $toData->ident is To
				// So the final ident is the sum of both.
				$ident = $_POST['ident'] + $toData->ident;
				if($user->sendPM($toData->usid,$_POST['msg'],'NULL',$ident,$rid)){
					finish('',true,'/'.$_POST['pid'],array('time'=>'','name'=>'','pic'=>'','usid'=>'','id'=>''));
				}else{
					finish('No se pudo enviar el mensaje');
				}
			}
		}else{
			finish('La persona no existe');
		}
		break;
	case 'flagComment':
		if(!isset($_POST['cid']) || !is_numeric($sess->clean($_POST['cid']))) finish('Ha ocurrido un error');
		$opts = array('ofensivo'=>'offensive','spam'=>'spam');
		if(!$opts[$_POST['opt']]) finish('Ha ocurrido un error 2');
		// Ahora lo guardaremos
		$db = $sess->db();
		$db->execute('UPDATE comments SET `'.$opts[$_POST['opt']].'` = `'.$opts[$_POST['opt']].'`+1 WHERE id = \''.addslashes($sess->clean($_POST['cid'])).'\' LIMIT 1');
		finish('',true,'/'.$_SESSION['current']);
		break;
	case 'register': 
		if(strlen(trim(rtrim($_POST['name'])))<1 || strlen(trim(rtrim($_POST['email'])))<1 || strlen(trim(rtrim($_POST['pass'])))<1) finish('Debes rellenar todos los campos');
		if(!$email = $sess->valid($_POST['email'],'email')) finish('Introduce un email valido');
		$name = $sess->clean(trim(rtrim($_POST['name'])));
		$check = $auth->emailExists($email);
		if($check == 1) finish('El email introducido ya está registrado. Olvidaste tu contraseña? JAJAJA pringado, te jodes!');
		if($check == 2) finish('Ya te has registrado con ese email, <a href="/do/activation">¿No te llegó el email de confimación?</a>');
		if($sess->register($email,$name,$_POST['pass'],true)) finish('',true,'/do/profile');
		finish('No se ha podido completar el registro, por favor intentalo mas tarde');
		break;
	case 'login':
		if(strlen(trim(rtrim($_POST['email'])))<1 || strlen(trim(rtrim($_POST['pass'])))<1) finish('Debes rellenar todos los campos');
		// If cookie qp is already set, and is valid, user is logged in:
		$sess->debug('AJAX: Case login, email and pass OK');
		if($sess->logged()) finish('Ya has iniciado sesion');
		if($sess->login($_POST['email'],$_POST['pass'])){
			$sess->debug('Login ok');
			finish('',true);
		}else finish('Email/Contraseña incorrectos, <a href="/do/forgot-pass">¿Olvidaste tu contraseña?</a>');
		break;
	case 'unlink':
		if(!isset($_POST['acc'])) finish('Por favor intentalo mas tarde');
		switch($_POST['acc']){
			case 'fb':
				$user->unlinkFB();
				// Cambiamos foto de perfil si era esta
				if($user->g('usePic')==2) $user->set('usePic',0,true);
				finish('',true,'/do/profile');
				break;
			case 'tw':
				$user->unlinkTW();
				// Cambiamos foto de perfil si era esta
				if($user->g('usePic')==3) $user->set('usePic',0,true);
				finish('',true,'/do/profile');
				break;
		}
	case 'changePic':
		if(!isset($_POST['change'])) finish('Por favor intentalo mas tarde');
		$allowed = array('facebook','twitter','nopic','uploaded');
		if(!in_array($_POST['change'],$allowed)) finish('Por favor intentalo mas tarde');
		// Now perform the change
		unset($_SESSION['user']['pic']);
		if($_POST['change'] == 'nopic') $user->set('usePic',0,true);
		if($_POST['change'] == 'uploaded' && $user->hasPic()) $user->set('usePic',1,true);
		if($_POST['change'] == 'facebook' && $user->fb()) $user->set('usePic',2,true);
		if($_POST['change'] == 'twitter' && $user->tw()) $user->set('usePic',3,true);
		finish('',true,'/do/profile');
		break;
	case 'follow':
		if(!isset($_POST['id']) || !is_numeric($_POST['id']) || $_POST['id']<1) finish('Por favor intentalo mas tarde');
		if($db->queryUniqueValue('SELECT follow FROM relations WHERE pid='.$_POST['id'].' AND usid='.$user->id())!==false){
			$db->execute('UPDATE relations SET follow=1 WHERE pid='.$_POST['id'].' AND usid='.$user->id());
		}else{
			$db->execute('INSERT INTO relations (pid,usid,relation, follow, timestamp) VALUES ('.$_POST['id'].','.$user->id().',2,1,'.time().')');
		}
		finish('',true);
		break;
	case 'unfollow':
		if(!isset($_POST['id']) || !is_numeric($_POST['id']) || $_POST['id']<1) finish('Por favor intentalo mas tarde');
		else{
			$db->execute('UPDATE relations SET follow=0 WHERE pid='.$_POST['id'].' AND usid='.$user->id());
		}
		finish('',true);
		break;
	case 'getMsg':
		if(!$sess->logged()) finish('Fail');
		if(!isset($_POST['th']) || !is_numeric($_POST['th']) || $_POST['th']<1) finish('Por favor intentalo mas tarde');
		$thread = addslashes($sess->valid($_POST['th'],'int'));
		$limit = '1,5';
		if($sess->valid($_POST['more'],'int') && $_POST['more']>0) $limit = ($_POST['more']*10).',5';
		$com = 0;
		if($sess->valid($_POST['com'],'int')) $com = $_POST['com'];
		$db = $sess->db();
		// Comprobamos que el usuario tiene derecho a ver estos
		// Cargar y mostrar comentario "en respuesta"
		function inResponse($com){
			global $db, $user;
			if($com > 0){
				// Message in reply to a comment, also should be the last one...
				// Fetch comment and send it
				$comment = $db->queryUniqueObject('SELECT personas.name AS pname, comments.id, comments.msg, comments.pid, comments.timestamp, comments.ident, comments.state, comments.spam, users.name, users.id AS usid, (CASE users.usePic WHEN 0 THEN \'http://img.quepiensas.es/noimage.png\' WHEN 1 THEN CONCAT(\'http://img.quepiensas.es/\',comments.usid,\'-square.png\') WHEN 2 THEN CONCAT(\'http://graph.facebook.com/\',users.fbuser,\'/picture?type=square\') WHEN 3 THEN (SELECT pic FROM twitter WHERE twid = users.twuser) END) AS pic FROM personas, comments, users WHERE comments.id = '.$com.' AND comments.usid = users.id AND comments.pid = personas.id');
				
				$curUser = 0;
				if($comment->usid == $user->id()) $curUser = 1;
				$usid = 0;
				$uname = 'Anonimo';
				$color = '#ccc';
				// ident = 1 => Public, 0 => Private
				if($comment->ident == 1){
					$usid = $comment->usid;
					$uname = $comment->name;
					$color = colorID($comment->usid);
				}
				echo '<msg id="'.$comment->id.'" type="comment" color="'.$color.'" curUser="'.$curUser.'" src="'.$comment->pic.'" pname="'.$comment->pname.'" pid="'.$comment->pid.'" usid="'.$usid.'" uname="'.$uname.'"><timestamp>'.dispTimeHour($comment->timestamp).'</timestamp><content><![CDATA['.nl2br(parse(stripslashes($comment->msg))).']]></content></msg>';
			}
			return true;
		}
		if(!$user->updatePMstatus($thread)) finish('jajaja. NO');
		// Pues ale, sacar mensajes
		$msg = $db->query('SELECT msg.`id`, msg.`to`, msg.`from`, msg.`com`, msg.`msg`, msg.`timestamp`, msg.`status`, users.name, (CASE users.usePic WHEN 0 THEN \'http://img.quepiensas.es/noimage.png\' WHEN 1 THEN CONCAT(\'http://img.quepiensas.es/\',users.id,\'-square.png\') WHEN 2 THEN CONCAT(\'http://graph.facebook.com/\',users.fbuser,\'/picture?type=square\') WHEN 3 THEN (SELECT pic FROM twitter WHERE twid = users.twuser) END) AS pic FROM msg, users WHERE (msg.`thread` = \''.$thread.'\' OR msg.`id` = \''.$thread.'\') AND users.id = msg.from ORDER BY `msg`.timestamp DESC LIMIT '.$limit);
		if($db->numRows($msg)<1 && $com < 1) finish('No hay mas mensajes');
		include('lib/php/linker.php');
		include('lib/php/style.php'); // dispTime
		include('lib/content/pmTemplates.php'); // Plantillas de privados
		// Current user id
		$usid = $user->id();
		// XML headers
		header("Content-type: text/xml");
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past (no cache)
		echo '<?xml version="1.0"?>';
		echo '<messages>';
		if($com > 0) inResponse($com);
		if($db->numRows($msg) > 0){
			while($a = $db->fetchNextObject($msg)){
				if($a->to - $usid !== 0 && $a->from - $usid !== 0){
					echo '<error>No puedes ver los privados de otra gente... ['.$a->from.':'.$a->to.':'.$usid.']</error>';
					break;
				}
				// Mostrarlo en plan XML
				$curUser = 0;
				if($a->from == $user->id()) $curUser = 1;
				echo '<msg id="'.$a->id.'" color="'.colorID($a->from).'" type="msg" usid="'.$a->from.'" curUser="'.$curUser.'" user="'.$a->name.'" src="'.$a->pic.'"><timestamp>'.dispTimeHour($a->timestamp).'</timestamp><content><![CDATA['.nl2br(parse(decodePM(stripslashes($a->msg)))).']]></content></msg>';
				inResponse($a->com);
			}
		}
		die('</messages>');
		break;
	case 'replyPM':
		if(!$sess->logged()) finish('Fail');
		if(!isset($_POST['th']) || !$sess->valid($_POST['th'],'int') || $_POST['th']<1) finish('Por favor intentalo mas tarde');
		$thread = addslashes($sess->valid($_POST['th'],'int'));
		// Comprobamos que el usuario tiene derecho a responder
		// El primer mensaje del thread debe ser de o para este usuario:
		$db = $sess->db();
		$info = $db->queryUniqueObject('SELECT `to`, `from` FROM `msg` WHERE `id` = \''.$thread.'\' AND (`to` = \''.$user->id().'\' OR `from` = \''.$user->id().'\')');
		if(!$info) finish('jajaja, NO.');
		// ID del destinatario:
		$to = $info->to;
		if($to == $user->id()) $to = $info->from;
		// A responder :D
		if($pm = $user->sendPM($to,$_POST['msg'],$thread)){
			finish('',true,'/do/messages',array('time'=>dispTimeHour(time()),'name'=>$user->g('name'),'pic'=>$user->pic(),'usid'=>$user->id(),'id'=>$pm,'msg'=>$_POST['msg']));
		}else{
			finish('No se pudo enviar el mensaje');
		}
	case 'changeStatus':
		if(!$sess->logged()) finish('Fail');
		if(!isset($_POST['th']) || !$sess->valid($_POST['th'],'int') || $_POST['th']<1) finish('Por favor intentalo mas tarde');
		$thread = addslashes($sess->valid($_POST['th'],'int'));
		$db = $sess->db();
		if(!$user->updatePMstatus($thread)) finish('jajaja. NO');
		finish('',true);		
		break;
	case 'notifs':
		if(!$sess->logged()) finish('Debes iniciar sesion');
		// Buscamos privados no leidos:
		$db = $sess->db();
		$privs = $db->queryUniqueValue('SELECT COUNT(*) FROM msg WHERE `to` = \''.$user->id().'\' AND (`status`=0 OR `status`=2)');
		// Respuestas no leidas
		$new = $db->queryUniqueValue('SELECT COUNT(*) FROM (SELECT comments.id FROM comments, replies WHERE comments.id = replies.id AND replies.rid IN (SELECT id FROM comments WHERE usid = '.$user->id().') AND comments.state=0) AS comments');
		finish('',true,false,array('msgs'=>$privs,'nuevo'=>$new));
	case 'loadComments':
		// Loads new comments, depends on TL_TYPE and VAR
		//	* TL_TYPE:	Public timeline, user, profile... etc
		//	* VAR:		TL variable (User ID... )
		// Optional parameter "last" for MySQL LIMIT offset
		//
		// Check variables:
		$var = false;
		if($_POST['tl_type']<0 || $_POST['tl_type']>3 || !$sess->valid($_POST['tl_type'],'int')) die('-1');
		$type = $_POST['tl_type'];
		if($sess->valid($_POST['tl_var'],'int')) $var = $_POST['tl_var'];
		$last = 1;
		if($sess->valid($_POST['last'],'int') && $_POST['last'] > 0) $last = $_POST['last'];
		// Now start loading process
		include('lib/php/style.php');
		include('lib/php/timeline.php');
		$tl = new Timeline($type,$var,10,$last);
		$tl->displayComments(true);
		break;
	case 'editProfile':
		if(!$sess->logged()) finish('Debes iniciar sesion');
		$update = '';
		include('lib/php/style.php');
		if(strlen(trim($_POST['name'])) > 0 && $_POST['name'] !== $user->g('name')){
			$update .= ' AND SET name = \''.clean($_POST['name']).'\'';
			$user->set('name',trim($_POST['name']));
		}
		if($_POST['bio'] !== $user->g('bio')){
			$update .= ' AND SET bio = \''.clean($_POST['bio']).'\'';
			$user->set('bio',trim($_POST['bio']));
		}
		
		// Crear cuenta Que Piensas
		if(strlen($_POST['email']) > 0 && strlen($user->g('email'))<1 && $sess->valid($_POST['email'],'email') && strlen($_POST['pass'])>0){
			// Generando una cuenta Que Piensas
			$update .= ' AND SET email = \''.addslashes($_POST['email']).'\'';
			$auth->changePass($user->id(),$_POST['pass']);
		}
		
		// Ejecutar cambios
		$update = substr($update,5); // Quita el primer AND
		$db = $sess->db();
		if(strlen($update)>0) $db->execute('UPDATE users '.$update.' WHERE id = '.$user->id().' LIMIT 1');
		
		// Cambiar la contraseña
		if(strlen($_POST['oldpass']) > 0 && $_POST['pass1'] == $_POST['pass2']){
			if($auth->login($user->g('email'),$_POST['oldpass'])>0) $auth->changePass($user->id(),$_POST['pass1']);
			else $sess->set_msg('La contraseña actual no es correcta');
		}else if($_POST['pass1']!==$_POST['pass2']){
			$sess->set_msg('La contraseña nueva y la repetida no coinciden, inténtalo de nuevo');	
		}
		finish('',true);
		break;
	case 'loadReply':
		if(!$sess->valid($_POST['rid'],'int')) die('<div class="errorBox">Ha ocurrido un error</div>');
		// Now start loading process
		include('lib/php/style.php');
		include('lib/php/timeline.php');
		$tl = new Timeline(5,$_POST['rid']);
		$tl->displayComments(true);
		break;
	default:
		finish('Accion no especificada. Intentalo de nuevo mas tarde');
}