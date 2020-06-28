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
class controller
{
    /**
     * Entité par défaut (home)
     * @var string
     */
    private $_strEntityDef = 'home';

    /**
     * Entité d'erreur
     * @var string
     */
    private $_strEntityError = 'error';

    /**
     * Action par défaut
     * @var string
     */
    private $_strActionDef = 'default';

    /**
     * Entité courante
     * @var string
     */
    private $_strEntity = '';

    /**
     * Action courante
     * @var string
     */
    private $_strAction = '';

    /**
     * Mode d'affichage (light true/false)
     * @var boolean
     */
    private $_booLight = false;

    /**
     * Identifiant du module (notamment pour appels alternatifs via CLI)
     * @var integer
     */
    private $_intModuleId = 0;

    /**
     * Identifiant du type de module (notamment pour appels alternatifs via CLI)
     * @var integer
     */
    private $_intModuleTypeId = 0;

    /**
     * Instance de block (gestion de l'intégration du menu dans Ploopi)
     * @var ploopi\block
     */
    private $_objBlock = null;

    /**
     * Nom du module
     * @var string
     */
    private $_strModuleName = '';


    /**
     * Liste des instances fabriquées
     * @var array
     */
    private static $_arrInstances = [];


    /**
     * factory du contrôleur du module
     * @param integer $intModuleId  Identifiant du module (optionnel)
     * @return ploopi\controller
     */
    public static function get($intModuleId = null) {

        if (empty($intModuleId)) $intModuleId = self::_getModuleId();

        if (!isset(self::$_arrInstances[$intModuleId])) {
            $strClassName = get_called_class();
            self::$_arrInstances[$intModuleId] = new $strClassName();
            self::$_arrInstances[$intModuleId]->_intModuleId = $intModuleId;
        }

        return self::$_arrInstances[$intModuleId];
    }

    /**
     * Contrôleur du module
     * @return void
     */
    public function dispatch()
    {
        $arrClassPath = explode('\\', get_called_class());
        $this->_strModuleName = $arrClassPath[1];
        $strModulePath = './modules/'.$this->_strModuleName;
        $strActionPath = $strModulePath.'/actions';

        // Appel light (save, ajax, cli...)
        $this->_booLight = php_sapi_name() == 'cli' || ploopi\loader::get_script() == 'admin-light' || ploopi\loader::get_script() == 'webservice';

        ploopi\module::init($this->_strModuleName, !$this->_booLight, !$this->_booLight, !$this->_booLight);

        $this->_strEntity = empty($_REQUEST['entity']) ? $this->_strEntityDef : self::_cleanParam($_REQUEST['entity']);
        $this->_strAction = empty($_REQUEST['action']) ? $this->_strActionDef : self::_cleanParam($_REQUEST['action']);

        $strFileAction = $strActionPath.'/'.$this->_strEntity.'/'.$this->_strAction.'.php';

        if(!file_exists($strFileAction))
        {
            $this->_strEntity = $this->_strEntityError;
            $this->_strAction = $this->_strActionDef;
            $strFileAction = $strActionPath.'/'.$this->_strEntity.'/'.$this->_strAction.'.php';
        }


        if(!$this->_booLight)
        {
            $strModuleHeader = $strModulePath.'/actions/_header.php';
            $strEntityHeader = $strActionPath.'/'.$this->_strEntity.'/_header.php';

            if (is_readable($strModuleHeader)) include_once $strModulePath.'/header.php';
            if (is_readable($strEntityHeader)) include_once $strEntityHeader;
        }
        else ploopi\buffer::clean();

        include_once $strFileAction;

        if(!$this->_booLight)
        {
            $strModuleFooter = $strModulePath.'/actions/_footer.php';
            $strEntityFooter = $strActionPath.'/'.$this->_strEntity.'/_footer.php';

            if (is_readable($strEntityFooter)) include_once $strEntityFooter;
            if (is_readable($strModuleFooter)) include_once $strModulePath.'/footer.php';
        }
        else ploopi\system::kill();
    }


    /**
     * Retourne l'entité appelée
     * @return string entité appelée
     */
    public function getEntity()
    {
        return $this->_strEntity;
    }

    /**
     * Retourne l'action appelée
     * @return string action appelée
     */
    public function getAction()
    {
        return $this->_strAction;
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
    protected function addBlockMenu($strLabel, $strEntity, $strAction = '', $intAction = null)
    {
        if(empty($intAction) || ploopi\acl::isactionallowed($intAction, -1, $this->_intModuleId)) {

            $strUrl = "admin.php?ploopi_moduleid={$this->_intModuleId}&ploopi_action=public&entity={$strEntity}";
            if($strAction != '') $strUrl .= "&action={$strAction}";

            $booSelected = $_SESSION['ploopi']['moduleid'] == $this->_intModuleId && isset($_REQUEST['entity']) && current(explode('/', $_REQUEST['entity'])) == $strEntity;
            if($strAction != '') $booSelected = $booSelected && isset($_REQUEST['action']) && $_REQUEST['action'] == $strAction;

            $this->getBlock()->addmenu($strLabel, ploopi\crypt::urlencode($strUrl), $booSelected);

            return true;
        }

        return false;
    }

    /**
     * Retourne l'instance de block
     *
     * @return ploopi\block
     */
    public function getBlock() {
        if (is_null($this->_objBlock)) $this->_objBlock = new ploopi\block();

        return $this->_objBlock;
    }

    /**
     * Met à jour le block de menu
     */
    public function setBlock() { }


    /**
     * Retourne le module ID (notamment pour l'appel via CLI sans Session)
     *
     * @return integer Id du module
     */
    public function getModuleId()
    {
        // Cas lecture depuis le cache
        if (!empty($this->_intModuleId)) return $this->_intModuleId;

        // Recherche étendue
        return $this->_intModuleId = self::_getModuleId();

    }

    /**
     * Retourne le module ID (notamment pour l'appel via CLI sans Session)
     *
     * @return integer Id du module
     */

    private static function _getModuleId() {
        // Cas standard (lecture session)
        if (isset($_SESSION['ploopi']['moduleid'])) return $_SESSION['ploopi']['moduleid'];

        // Cas d'un appel depuis la CLI (lecture bdd)
        $objQuery = new ploopi\query_select();
        $objQuery->add_select('m.id');
        $objQuery->add_from('ploopi_module m');
        $objQuery->add_innerjoin('ploopi_module_type mt ON mt.id = m.id_module_type');
        $objQuery->add_where('mt.label = %s', self::_getModuleName());
        $objRs = $objQuery->execute();

        if ($row = $objRs->fetchrow()) return $row['id'];

        // Cas d'erreur
        return 0;
    }

    /**
     * Retourne le nom du module
     *
     * @return string Nom du module
     */
    private static function _getModuleName() {
        $arrClassPath = explode('\\', get_called_class());
        return $arrClassPath[1];
    }

    /**
     * Retourne l'ID du type de module (notamment pour l'appel via CLI sans Session)
     *
     * @return integer Id du type du module
     */
    public function getModuleTypeId()
    {
        // Cas lecture depuis le cache
        if (!empty($this->_intModuleTypeId)) return $this->_intModuleTypeId;

        // Cas standard (lecture session)
        if (isset($_SESSION['ploopi']['moduletypeid'])) return $this->_intModuleTypeId = $_SESSION['ploopi']['moduletypeid'];

        // Cas d'un appel depuis la CLI (lecture bdd)
        $objQuery = new ploopi\query_select();
        $objQuery->add_select('mt.id');
        $objQuery->add_from('ploopi_module_type mt');
        $objQuery->add_where('mt.label = %s', $this->_strModuleName);
        $objRs = $objQuery->execute();

        if ($row = $objRs->fetchrow()) return $this->_intModuleTypeId = $row['id'];

        // Cas d'erreur
        return $this->_intModuleTypeId = 0;
    }

    /**
     * Retourne la valeur d'un paramètre du module (uniquement système, pour appel CLI essentiellement)
     *
     * @param string $strParam nom du paramètre à retourner
     * @return string valeur du paramètre
     */
    public function getParam($strParam)
    {
        // Cas d'un appel depuis la CLI (lecture bdd)
        $objQuery = new ploopi\query_select();
        $objQuery->add_select('value');
        $objQuery->add_from('ploopi_param_default');
        $objQuery->add_where('name = %s', $strParam);
        $objQuery->add_where('id_module = %d', $this->_intModuleId);
        $objRs = $objQuery->execute();
        if ($row = $objRs->fetchrow()) return $row['value'];

        return '';
    }


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

}
