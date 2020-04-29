<?php
/*
switch($ploopi_op) {

	case 'nano_thumb' :
        if (isset($_GET['md5id'])) {
            $intTimeCache = 2592000; // 30 jours
            $width = (!empty($_GET['w']) && is_numeric($_GET['w'])) ? $_GET['w'] : 800;
            $height = (!empty($_GET['h']) && is_numeric($_GET['h'])) ? $_GET['h'] : 400;
            $coef = (!empty($_GET['c']) && is_numeric($_GET['c'])) ? $_GET['h'] : 1;
            ploopi\buffer::clean();
            $objCache = new ploopi\cache(md5('doc_thumb_'.$_GET['md5id'].'_'.$_GET['v']), $intTimeCache); 
			// Attribution d'un groupe spécifique pour le cache pour permettre un clean précis
            $objCache->set_groupe('module_doc_'.$_SESSION['ploopi']['workspaceid'].'_'.$_SESSION['ploopi']['moduleid']);

            if(!$objCache->start()) { // si pas de cache on le crée
                ploopi\module::init('doc', false, false, false);
                include_once './modules/doc/class_docfile.php';
                $objDoc = new docfile();
                $objThumb = new ploopi\mimethumb($width, $height, $coef, 'png', 'transparent');
                if($objDoc->openmd5($_GET['md5id']))
                    $objThumb->getThumbnail($objDoc->getfilepath(),$objDoc->fields['extension']);
                if(isset($objCache)) $objCache->end();
            } else {
                header("Content-Type: image/png");
            }
        }
        ploopi\system::kill();

}
*/
