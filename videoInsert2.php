<?php
// Esto carga los parametros de un video via AJAX, para hacer mas rapida la carga de las paginas del foro, como siempre
if(!$_POST['url']){ die(json_encode(array('done'=>'false'))); }

$doc = new DOMDocument;
$embedLyURL = 'http://api.embed.ly/1/oembed?url='.urlencode($_POST['url']).'&maxwidth=99192&maxheight=390&format=xml';
$opts = array(
    'http' => array(
        'user_agent' => 'Mozilla/5.0 (compatible; Chevismo/1.0; +http://blog.chevismo.com/)',
    )
);

// Display icons
$showIcons = false;

$context = stream_context_create($opts);
libxml_set_streams_context($context);

$doc->load($embedLyURL);
$title = $doc->getElementsByTagName("title")->item(0)->nodeValue;
$link = $doc->getElementsByTagName("url")->item(0)->nodeValue;
$aTitle = $doc->getElementsByTagName("description")->item(0)->nodeValue;
$provider = $doc->getElementsByTagName("provider_name")->item(0)->nodeValue;
$type = $doc->getElementsByTagName("type")->item(0)->nodeValue;
if(strlen($title)>80){
	$title=substr($title,0,80).'(...)';
}
if(!$link) $link = $_POST['url'];
// Iconos disponibles
$iconServer=array('aim','amazon','android','aol','apple','appstore','bebo','behance','bing','bleetbox','blinklist','blogger','brightkite-1','brightkite-2','cargocollective','coroflot','delicious','designfloat','designmoo','deviantart','digg','diglog','dopplr','dribbble','dzone','ebay','ember','evernote','facebook','feedburner-1','feedburner-2','flickr','flickr-1','flickr-2','foursquare','fresqui','friendfeed','friendster','furl','gamespot','gmail','google','googlebuzz','gowalla','gravee','grooveshark-1','grooveshark-2','gtalk','hi5-1','hi5-2','hyves-1','hyves-2','identica','ilike','isociety','lastfm','linkedin','livejournal','magnolia','metacafe','misterwong','mixx','mobileme','msn','mynameise','myspace','netvibes','newsvine','ning','openid-1','openid-2','orkut','pandora','paypal','picasa','pimpthisblog','plurk','posterous','qik','readernaut','reddit','rss','sharethis','skype','slashdot','sphere','sphinn','spotify','springpad','soundcloud','stumbleupon','technorati','tripadvisor','tuenti','tumblr','twitter','viddler','vimeo','virb','webshots','windows','wordpress','xing','yahoo','yahoobuzz','yelp','youtube','zanatic','zootool');
if(in_array(strtolower($provider),$iconServer)){
	$hasIcon=true;
	$provider = $provider.' <img src="http://static.quepiensas.es/img/icons/social/24x24/'.strtolower($provider).'.png" height="24" border="0" align="absmiddle" />';
}
if($type=='video'){
	$thumb = $doc->getElementsByTagName("thumbnail_url")->item(0)->nodeValue;
	$title= '<div class="thumb"><img src="'.$thumb.'" /></div><span class="text_title">'.$title.'</span>';
	$embedCode = '<div align="center">'.$doc->getElementsByTagName("html")->item(0)->nodeValue.'</div>';
	$aTitle = 'Descripcion: '.$aTitle;
}elseif($type=='photo'){
	$author = $doc->getElementsByTagName("author_name")->item(0)->nodeValue;
	$thumb = $doc->getElementsByTagName("thumbnail_url")->item(0)->nodeValue;
	$title = '<img src="'.$thumb.'" width="100" border="0" align="absmiddle" /> '.$title.', de '.$author;
	$embedCode = '';
}elseif($type=='link'){
	$thumb = $doc->getElementsByTagName("thumbnail_url")->item(0)->nodeValue;
	$author = $doc->getElementsByTagName("author_name")->item(0)->nodeValue;
	if(strlen($thumb)>0){
		$title = '<img src="'.$thumb.'" height="60" border="0" align="absmiddle" /> '.$title.', de '.$author;
		$embedCode = '';
	}else{
		if($hasIcon && $showIcons){
			$title= '<img src="http://static.quepiensas.es/img/icons/social/48x48/'.strtolower($doc->getElementsByTagName("provider_name")->item(0)->nodeValue).'.png" height="48" border="0" align="absmiddle" /> '.$title;
		}else{
			$title= $title;
		}
		$embedCode = '<strong>Escrito por '.$author.':</strong><br />'.$aTitle.'<p align="right"><a href="'.$link.'">Post completo &raquo;</a></p>';
	}
}elseif($type='rich'){
	$title= $title;
	$embedCode = str_replace('900','100%',$doc->getElementsByTagName("html")->item(0)->nodeValue);
	$link = $_POST['url'];
}
echo json_encode(array('done'=>'true','title'=>ucfirst($title),'embed'=>str_replace('99192','100%',$embedCode),'linkURL'=>$link,'aTitle'=>str_replace(array('"','<br/>'),array('\'',"\n"),strip_tags($aTitle)),'provider'=>$provider));