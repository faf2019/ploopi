<?
/**
 * Gestion des modèles de documents
 * 
 * @package espacedoc
 * @subpackage modele_document
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

/**
 * Inclusion de la classe parent
 */
include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table 'ploopi_mod_espacedoc_modele_document'
 * 
 * @package espacedoc
 * @subpackage modele_document
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

class espacedoc_modele_document extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return espacedoc_modele_document
     */
    
    function espacedoc_modele_document()
    {
        parent::data_object('ploopi_mod_espacedoc_modele_document', 'id');
    }
}
?>
