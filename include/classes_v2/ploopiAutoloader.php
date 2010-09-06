<?php
/**
 *
 * Autoloader de classes
 * @author ovensia
 *
 */
abstract class ploopiAutoloader
{
    private static $_booInit = false;

    private static $_strDefaultPath = '/include/classes_v2';

    public static function init()
    {
        if (!self::$_booInit)
        {
            self::addPath(self::$_strDefaultPath);
            spl_autoload_register(array('ploopiAutoloader','_autoload'));
            self::$_booInit = true;
        }
    }


    /**
     * Ajoute un chemin à l'include path global permettant le chargement automatique de classes
     */
    public static function addPath($strPath)
    {
        set_include_path(get_include_path().PATH_SEPARATOR. _PLOOPI_DIRNAME.$strPath);
    }

    private static function _autoload($strClassName)
    {
        require_once str_replace('_', '/', $strClassName).'.php';
    }
}
