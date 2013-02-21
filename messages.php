<?php
include('lib/php/session.php');

if(!$sess->logged()){
	header('Location: /do/login');
	die();	
}
include('lib/php/funciones.php');
include('lib/php/style.php');
include('lib/php/linker.php');

// Inicio de base de datos
$db=$sess->db();

// Pagination
$limit = 20;
if($_GET['p']>0 && $sess->valid($_GET['p'],'int')){ $limit = $limit*$_GET['p'].',20'; }

	// Privados
	$msg = $db->query('
		SELECT
			msg.id, msg.tid AS th, msg.usid, msg.msg, msg.timestamp,
			(SELECT COUNT(*) FROM msg WHERE msg.tid = th) AS total,
			users.name, msgThread.`from`, msgThread.`to`, msgThread.`ident`, msgThread.`status`, msgThread.com,
			IF(`to` = \''.$user->id().'\',`from`,`to`) AS hisId,
			(SELECT `name` FROM `users` WHERE `id` = `hisId`) AS hisName,
			(CASE users.usePic
				WHEN 0 THEN \'http://img.quepiensas.es/noimage.png\'
				WHEN 1 THEN CONCAT(\'http://img.quepiensas.es/\',`msgThread`.`from`,\'-square.png\')
				WHEN 2 THEN CONCAT(\'http://graph.facebook.com/\',users.fbuser,\'/picture?type=square\')
				WHEN 3 THEN (SELECT pic FROM twitter WHERE twid = users.twuser) END) AS pic
		FROM msgThread, (SELECT * FROM msg ORDER BY timestamp DESC) AS msg, users
		WHERE
			msg.tid = msgThread.tid
			AND (`from` = '.$user->id().' OR `to` = '.$user->id().')
			AND `usid` = users.id
		GROUP BY msgThread.tid
		ORDER BY timestamp DESC
		LIMIT '.$limit);
	
if($_GET['p']>0 && $db->numRows($msg)<1){
	// Algun capullo metiendo una pagina manualmente que no existe
	header('Location: /do/messages');
	die('La pagina '.$_GET['p'].' no tiene mensajes.');	
}

// Message templates, like the welcome message
include('lib/content/pmTemplates.php');

// Pagination
$back = false;
$next = false;
if($_GET['p']>0) $back = '<a href="?p='.($_GET['p']-1).'">&laquo; Anterior</a>';
if($db->numRows($msg) == $limit) $next = '<a href="?p='.($_GET['p']+1).'">Siguiente &raquo;</a>';
if($back && $next) $middle = ' &bull; ';
// ----------------------------

$content['title'] = 'Mensajes privados';

$content['css'][] = 'messages';
$content['css'][] = 'timeline'; // CSS de la caja de responder

$content['js'][] = 'messages';

// Columnas de contenido:
$content['cols'][] = menu();

include('lib/content/top.php');

/* --------------------------------------------- */

?>

<h1 style="border-bottom:#7dc3ff 1px solid;">Mis mensajes</h1>
<div class="paddedContent" style="padding-top:0px;">
	<ul class="messages">
	<?php if($db->numRows($msg)>0){ while($a = $db->fetchNextObject($msg)){
			
			/**
			 * 	STATUS GUIDE
			 *		0 -> Just sent, unread
			 *		1 -> Read
			 *		2 -> Deleted by sender, unread
			 *		3 -> Deleted by sender, read
			 *		4 -> Deleted by receiver
			 */
			// Check if message has been deleted
			if(($user->id() == $a->usid && ($a->status == 2 || $a->status == 3)) || ($user->id() !== $a->usid && $a->status == 4)){
				// Message deleted
				echo '<li>[Thread #'.$a->tid.' deleted]</li>';
				continue;
			}
			// Message status
			$unread = 'read';
			if($user->id() !== $a->usid && ($a->status == 0 || $a->status == 2)){
				// If you are the receiver
				$unread = 'unread';
			}
				
			// Use the binary system for Identification
			$ident = str_pad(decbin($a->ident), 2, 0, STR_PAD_LEFT);
			$toIdent = $ident[0];
			$fromIdent = $ident[1];
			// Convert to and from -> you and him
			$yourIdent = $toIdent;
			$hisIdent = $fromIdent;
			// Check if it must be the other way around
			if($user->id() == $a->from){
				$yourIdent = $fromIdent;
				$hisIdent = $toIdent;
			}
			$senderIdent = $fromIdent;
			if($a->usid == $a->to) $senderIdent = $toIdent;
				
			// General Data
			$name = $a->hisName;
			if(strlen($name)<1 || $hisIdent==0) $name = 'Anónimo';
			
			// Formatting
			$a->msg = decodePM(stripslashes($a->msg));
			$extract = $a->msg;
			if(strlen($extract)>50) $extract = substr($extract,0,50).'...';
			
			// Grey text for your name
			$linkStyle = '';
			if($a->usid == $user->id()) $linkStyle = 'color:#333';
			
			$from = '<a href="/user/'.$a->usid.'" style="'.$linkStyle.'"><strong>'.$a->name.'</strong></a>';
			
			if($senderIdent==0){
				// Privatize the sender identity
				$from = 'Él';
				if($user->id() == $a->usid) $from = 'Tú';
				$from .= ' en modo <strong>Anónimo</strong>';
				$a->pic = 'http://img.quepiensas.es/noimage.png';
			}
			
			$color = '';
			if($a->pic == 'http://img.quepiensas.es/noimage.png'){
				$color = '#ccc';
				if($senderIdent==1) $color = colorID($a->from);
			}
			
			// Display message
    		echo '<li id="'.$a->th.'">
    			  	<a href="#showMsg" data-com="'.$a->com.'" data-ident='.$fromIdent.'" class="header '.$unread.'" rel="">
    			  		<span class="name">'.$name.'</span> <span class="count">('.$a->total.')</span>
    			  		<span class="extract">'.$extract.'</span>
    			  		<span class="timestamp">'.dispTimeHour($a->timestamp).'</span>
    			  	</a>
    			  	<ul class="thread">
    			  		<li>
    			  			<img src="'.$a->pic.'" width="50" style="background:'.$color.'" />
    			  			<div class="header">'.$from.' <small>'.dispTimeHour($a->timestamp).'</small></div>
    			  			<div class="msgContent">'.nl2br(parse($a->msg)).'</div>
    			  		</li>
    			  	</ul>
    			  </li>';
     	  } }else{ echo '<div class="errorBox">No tienes mensajes privados</div>'; } ?>
    </ul>
    <div style="margin-top:20px; text-align:center"><?php echo $back.$middle.$next; ?></div>
</div>
<div id="replyBoxCopy" style="display:none;"><div class="replyBox replyBoxInline"><form action="/ajax.php" method="post" name="replyMessageForm">
    <input name="thread" type="hidden" value="" />
    <input name="ajax" type="hidden" value="false" />
    <input name="type" type="hidden" value="replyPM" />
    <div class="errorMsg" style="display:none"></div>
    <textarea name="msg" cols="6" rows="1" wrap="virtual" class="formNormal" placeholder="Responder..."></textarea>
    <div class="options">
    	<span class="replyPrivate">
        <label title="Comentar de manera Anonima" class="tooltip hoverLabel">
            <input type="radio" name="ident" value="0" checked="checked" style="display:none !important;" />Anónimo</label></span>
        <label title="Comentar como <?php echo $user->g('name'); ?>" class="tooltip">
            <input type="radio" name="ident" value="1" style="display:none !important;" /><?php echo $user->g('name'); ?></label>
        <div style="position:absolute; font-size:12px; top:3px; right:3px;">
        	<input name="save" type="submit" value="Enviar" class="btn btnBlue" />
        </div>
	</div>
</form></div></div>
<div id="loadContainer" style="display:none;"><div class="loader" style="font-size:12px; color:#095CC4; text-align:center;">
	<img src="http://static.quepiensas.es/img/load/transparent-circle.gif" align="absmiddle" /> Cargando...</div>
</div>
   
<p align="center"><a href="/do/profile">Volver a mi perfil</a></p>
<?php 
include('lib/content/footer.php');
?>