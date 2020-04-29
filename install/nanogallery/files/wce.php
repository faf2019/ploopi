<?php
use ploopi\nanogallery\nanogallery;

$objGallery = new nanogallery();
if($objGallery->open($obj['object_id'])) {
	$objGallery->display();
} else {
	echo "Galerie ${obj['object_id']} non trouvée.<br>";
}


