<?
/**
 * Gestion des th�mes
 * 
 * @package espacedoc
 * @subpackage departement
 * @author St�phane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

/**
 * Inclusion de la classe parent
 */
include_once './include/classes/data_object.php';

/**
 * Classe d'acc�s � la table 'ploopi_mod_espacedoc_departement'
 * 
 * @package espacedoc
 * @subpackage departement
 * @author St�phane Escaich
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
