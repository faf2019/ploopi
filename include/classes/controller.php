<?php
/**
 * Noyau et contrôleur principal d'un module
 *
 * @author OVENSIA
 */

namespace ploopi;

use ploopi;

/**
 * Noyau et contôleur du module.
 */
abstract class controller
{
    /**
     * Entité par défaut (home)
     * @var string
     */
    private static $_strEntityDef = 'home';

    /**
     * Entité d'erreur
     * @var string
     */
    private static $_strEntityError = 'error';

    /**
     * Action par défaut
     * @var string
     */
    private static $_strActionDef = 'default';

    /**
     * Entité courante
     * @var string
     */
    private static $_strEntity = '';

    /**
     * Action courante
     * @var string
     */
    private static $_strAction = '';

    /**
     * Mode d'affichage (light true/false)
     * @var boolean
     */
    private static $_booLight = false;

    /**
     * Identifiant du module (notamment pour appels alternatifs via CLI)
     * @var integer
     */
    private static $_intModuleId = 0;

    /**
     * Identifiant du type de module (notamment pour appels alternatifs via CLI)
     * @var integer
     */
    private static $_intModuleTypeId = 0;

    /**
     * Instance de block (gestion de l'intégration du menu dans Ploopi)
     * @var ploopi\block
     */
    private static $_objBlock = null;

    /**
     * Nom du module
     * @var string
     */
    private static $_strModuleName = '';

    /**
     * Contrôleur du module
     * @return void
     */
    public static function dispatch()
    {
        $arrClassPath = explode('\\', get_called_class());
        self::$_strModuleName = $arrClassPath[1];
        $strModulePath = './modules/'.self::$_strModuleName;
        $strActionPath = $strModulePath.'/actions';

        // Appel light (save, ajax, cli...)
        self::$_booLight = php_sapi_name() == 'cli' || ploopi\loader::getscript() == 'admin-light' || ploopi\loader::getscript() == 'webservice';

        ploopi\module::init(self::$_strModuleName, !self::$_booLight, !self::$_booLight, !self::$_booLight);

        self::$_strEntity = empty($_REQUEST['entity']) ? self::$_strEntityDef : self::_cleanParam($_REQUEST['entity']);
        self::$_strAction = empty($_REQUEST['action']) ? self::$_strActionDef : self::_cleanParam($_REQUEST['action']);

        $strFileAction = $strActionPath.'/'.self::$_strEntity.'/'.self::$_strAction.'.php';

        if(!file_exists($strFileAction))
        {
            self::$_strEntity = self::$_strEntityError;
            self::$_strAction = self::$_strActionDef;
            $strFileAction = $strActionPath.'/'.self::$_strEntity.'/'.self::$_strAction.'.php';
        }


        if(!self::$_booLight)
        {
            $strModuleHeader = $strModulePath.'/actions/_header.php';
            $strEntityHeader = $strActionPath.'/'.self::$_strEntity.'/_header.php';

            if (is_readable($strModuleHeader)) include_once $strModulePath.'/header.php';
            if (is_readable($strEntityHeader)) include_once $strEntityHeader;
        }
        else ploopi\buffer::clean();

        include_once $strFileAction;

        if(!self::$_booLight)
        {
            $strModuleFooter = $strModulePath.'/actions/_footer.php';
            $strEntityFooter = $strActionPath.'/'.self::$_strEntity.'/_footer.php';

            if (is_readable($strEntityFooter)) include_once $strEntityFooter;
            if (is_readable($strModuleFooter)) include_once $strModulePath.'/footer.php';
        }
        else ploopi\system::kill();
    }

    /**
     * Retourne l'entité appelée
     * @return string entité appelée
     */
    public static function getEntity()
    {
        return self::$_strEntity;
    }

    /**
     * Retourne l'action appelée
     * @return string action appelée
     */
    public static function getAction()
    {
        return self::$_strAction;
    }

    /**
     * Fonction générique d'ajout de ligne dans le menu du module RAA (block.php)
     *
     * @param string $strLabel  Libellé du menu
     * @param string $strEntity  Entité pour la construction de l'url ex: [...]&entity=$strEntity
     * @param string $strAction  Action pour la construction de l'url ex: [...]&action=$strAction
     * @param integer $intAction  Action nécessaire pour accéder au menu
     * @return boolean true si le block a été ajouté, false sinon
     */
    protected static function addBlockMenu($strLabel, $strEntity, $strAction = '', $intAction = null)
    {
        global $menu_moduleid;

        if(empty($intAction) || ploopi\acl::isactionallowed($intAction, -1, $menu_moduleid)) {

            $strUrl = "admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&entity={$strEntity}";
            if($strAction != '') $strUrl .= "&action={$strAction}";

            $booSelected = $_SESSION['ploopi']['moduleid'] == $menu_moduleid && isset($_REQUEST['entity']) && current(explode('/', $_REQUEST['entity'])) == $strEntity;
            if($strAction != '') $booSelected = $booSelected && isset($_REQUEST['action']) && $_REQUEST['action'] == $strAction;

            self::getBlock()->addmenu($strLabel, ploopi\crypt::urlencode($strUrl), $booSelected);

            return true;
        }

        return false;
    }

    /**
     * Retourne l'instance de block
     *
     * @return ploopi\block
     */
    public static function getBlock() {
        if (is_null(self::$_objBlock)) self::$_objBlock = new ploopi\block();

        return self::$_objBlock;
    }

    /**
     * Met à jour le block de menu
     */
    public static function setBlock() { }

   /**
     * Nettoyage de paramètre d'url
     *  - Enlever les accents
     *  - Mettre en minuscule
     *  - Remplacer tout ce qui n'est pas a-z0-9/_ par une chaîne vide
     *
     * @param string $strParam  Chaîne à nettoyer
     * @return string
     */
    private static function _cleanParam($strParam)
    {
        return preg_replace('@[^a-z0-9/_]@', '', strtolower(ploopi\str::convertaccents($strParam)));
    }


    /**
     * Retourne le module ID (notamment pour l'appel via CLI sans Session)
     *
     * @return integer Id du module
     */
    public static function getModuleId()
    {
        // Cas lecture depuis le cache
        if (!empty(self::$_intModuleId)) return self::$_intModuleId;

        // Cas standard (lecture session)
        if (isset($_SESSION['ploopi']['moduleid'])) return self::$_intModuleId = $_SESSION['ploopi']['moduleid'];

        // Cas d'un appel depuis la CLI (lecture bdd)
        $objQuery = new ploopi\query_select();
        $objQuery->add_select('m.id');
        $objQuery->add_from('ploopi_module m');
        $objQuery->add_innerjoin('ploopi_module_type mt ON mt.id = m.id_module_type');
        $objQuery->add_where('mt.label = %s', self::$_strModuleName);
        $objRs = $objQuery->execute();

        if ($row = $objRs->fetchrow()) return self::$_intModuleId = $row['id'];

        // Cas d'erreur
        return self::$_intModuleId = 0;
    }

    /**
     * Retourne l'ID du type de module (notamment pour l'appel via CLI sans Session)
     *
     * @return integer Id du type du module
     */
    public static function getModuleTypeId()
    {
        // Cas lecture depuis le cache
        if (!empty(self::$_intModuleTypeId)) return self::$_intModuleTypeId;

        // Cas standard (lecture session)
        if (isset($_SESSION['ploopi']['moduletypeid'])) return self::$_intModuleTypeId = $_SESSION['ploopi']['moduletypeid'];

        // Cas d'un appel depuis la CLI (lecture bdd)
        $objQuery = new ploopi\query_select();
        $objQuery->add_select('mt.id');
        $objQuery->add_from('ploopi_module_type mt');
        $objQuery->add_where('mt.label = %s', self::$_strModuleName);
        $objRs = $objQuery->execute();

        if ($row = $objRs->fetchrow()) return self::$_intModuleTypeId = $row['id'];

        // Cas d'erreur
        return self::$_intModuleTypeId = 0;
    }

    /**
     * Retourne la valeur d'un paramètre du module (uniquement système, pour appel CLI essentiellement)
     *
     * @param string $strParam nom du paramètre à retourner
     * @return string valeur du paramètre
     */
    public static function getParam($strParam)
    {
        // Cas d'un appel depuis la CLI (lecture bdd)
        $objQuery = new ploopi\query_select();
        $objQuery->add_select('value');
        $objQuery->add_from('ploopi_param_default');
        $objQuery->add_where('name = %s', $strParam);
        $objQuery->add_where('id_module = %d', self::getModuleId());
        $objRs = $objQuery->execute();
        if ($row = $objRs->fetchrow()) return $row['value'];

        return '';
    }
}
