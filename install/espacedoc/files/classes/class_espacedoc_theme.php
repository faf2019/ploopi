<?
/**
 * Gestion des th�mes
 * 
 * @package espacedoc
 * @subpackage theme
 * @author St�phane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

/**
 * Inclusion de la classe parent
 */
include_once './include/classes/data_object.php';

/**
 * Classe d'acc�s � la table 'ploopi_mod_espacedoc_theme'
 * 
 * @package espacedoc
 * @subpackage theme
 * @author St�phane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

class espacedoc_theme extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return espacedoc_theme
     */
    
    function espacedoc_theme()
    {
        parent::data_object('ploopi_mod_espacedoc_theme', 'id');
    }
}
?>
