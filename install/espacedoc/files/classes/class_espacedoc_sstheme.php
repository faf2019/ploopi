<?
/**
 * Gestion des sous-thèmes
 * 
 * @package espacedoc
 * @subpackage sstheme
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

/**
 * Inclusion de la classe parent
 */
include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table 'ploopi_mod_espacedoc_sstheme'
 * 
 * @package espacedoc
 * @subpackage sstheme
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

class espacedoc_sstheme extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return espacedoc_sstheme
     */
    
    function espacedoc_sstheme()
    {
        parent::data_object('ploopi_mod_espacedoc_sstheme', 'id');
    }
}
?>
