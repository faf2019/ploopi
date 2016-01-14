<?
/**
 * Gestion des thèmes
 * 
 * @package espacedoc
 * @subpackage departement
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

/**
 * Inclusion de la classe parent
 */
include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table 'ploopi_mod_espacedoc_departement'
 * 
 * @package espacedoc
 * @subpackage departement
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

class espacedoc_departement extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return espacedoc_departement
     */
    
    function espacedoc_departement()
    {
        parent::data_object('ploopi_mod_espacedoc_departement', 'id');
    }
}
?>
