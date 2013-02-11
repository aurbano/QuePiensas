<?php
include('lib/php/session.php');

if(!$sess->logged()){ 
   header('Location: /do/login');
}

$content['title'] = 'Desactivar cuenta';

include('lib/content/top.php');

/* --------------------------------------------- */

?>

<h1 style="border-bottom:#7dc3ff 1px solid;">Desactivar cuenta</h1>
<div class="paddedContent hideOnAction">
<img src="http://www.pictures-of-kittens-and-cats.com/images/cute-kitten-pictures-002.jpg" height="220" style="margin:15px" align="right" />
<img src="http://www.digdang.com/media/images/please_dont_go_6396.jpg" height="220" style="margin:15px" align="right" />

 <h3>¿Desactivar?</h3>
 <p>Vamos que te marchas sin decir ni adiós. Pues ya te vale, después de todo lo que hemos pasado juntos. ¿Qué pensarás de nosotros para dejarnos tirados?</p>
  <p>Si te vas a marchar, al menos dinos por qué. Puedes dejarte caer por nuestra <a href="/do/contacto">sección de contacto</a>, explícanoslo y si así lo quieres desactivaremos la cuenta.</p>
  <p>Ten en cuenta que si desactivamos tu cuenta nadie podrá acceder a tu perfil, pero tus comentarios seguirán siendo visibles.</p>
</div>
<?php
include('lib/content/footer.php');
?>