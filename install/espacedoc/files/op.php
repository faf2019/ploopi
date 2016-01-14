<?php
/**
 * Op�rations du module CCEN
 * 
 * @package espacedoc
 * @subpackage op
 * @author St�phane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

/**
 * On v�rifie qu'on est bien dans le module ESPACEDOC.
 */

if (ploopi_ismoduleallowed('espacedoc'))
{
    /**
     * Op�rations sur les th�mes
     */
    include_once './modules/espacedoc/op_theme.php';
    
    /**
     * Op�rations sur les sous-th�mes
     */
    include_once './modules/espacedoc/op_sstheme.php';

    /**
     * Op�rations sur les documents
     */
    include_once './modules/espacedoc/op_document.php';
    
    switch($ploopi_op)
    {
        /**
         * Retourne le status de l'upload en cours (MODE CGI)
         */
        case 'espacedoc_getstatus':
            /**
             * @ignore UPLOAD_PATH
             */
            if (substr(_PLOOPI_CGI_UPLOADTMP, -1, 1) != '/') define ('UPLOAD_PATH', _PLOOPI_CGI_UPLOADTMP.'/');
            else define ('UPLOAD_PATH', _PLOOPI_CGI_UPLOADTMP);
            include_once './lib/cupload/status.php';
            ploopi_die();
        break;        

        /**
         * Propose le t�l�chargement d'un fichier (rubrique aide)
         */
        case 'espacedoc_download_doc':
            if (!empty($_GET['espacedoc_doctype']))
            {
                $sql = "SELECT * FROM ploopi_mod_espacedoc_modele_document WHERE type = '{$_GET['espacedoc_doctype']}'";
    
                $db->query($sql);
                if ($row = $db->fetchrow())
                {
                    $libelle_fichier = $row['libelle'];
                    $filepath = _PLOOPI_PATHDATA._PLOOPI_SEP.'espacedoc'._PLOOPI_SEP.'modeles'._PLOOPI_SEP.$row['fichier'];
                    if (file_exists($filepath)) ploopi_downloadfile($filepath, $row['fichier']); 
                }
            }
            ploopi_die();
        break;
    }
    
}
?>
