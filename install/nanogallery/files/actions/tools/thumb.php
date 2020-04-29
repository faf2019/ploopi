<?php
/*

Rendu non optimal.
Il faudra faire une copie modifiée de image::resize()

ob_clean();
header('Content-Type: image/jpeg');

$height = empty($_GET['h']) ? 200 : $_GET['h'];
$width = empty($_GET['w']) ? 200 : $_GET['w'];
$thumbs = new ploopi\mimethumb($width , $height, 0, 'jpg');
$thumbs->getThumbnail(ploopi\str::html_entity_decode($_GET['f']);
ploopi\system::kill();
*/
