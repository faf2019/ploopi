<?php
include_once './modules/doc/class_docfile.php';
include_once './modules/doc/class_docfolder.php';

// http://gipsy/projets/ploopidev/wsdoc/test?ploopi_login=admin&ploopi_password=Baghy.c0m
// http://gipsy/projets/ploopidev/wsdoc/list_modules?ploopi_login=admin&ploopi_password=Baghy.c0m
// http://gipsy/projets/ploopidev/wsdoc/list_content?ploopi_login=admin&ploopi_password=Baghy.c0m&module_id=6
// http://gipsy/projets/ploopidev/wsdoc/list_content?ploopi_login=admin&ploopi_password=Baghy.c0m&module_id=6&folder_id=1
// http://gipsy/projets/ploopidev/wsdoc/create_folder?ploopi_login=admin&ploopi_password=Baghy.c0m&module_id=6&folder_id=1&folder_name=prive&folder_type=private
// http://gipsy/projets/ploopidev/wsdoc/delete_folder?ploopi_login=admin&ploopi_password=Baghy.c0m&module_id=6&folder_id=1&folder_id=73
// http://gipsy/projets/ploopidev/wsdoc/put_file?ploopi_login=admin&ploopi_password=Baghy.c0m&module_id=6&folder_id=1&file_name=test.txt&base64=NDM1OTI5NTE4NCAvIG5pbmljZTA4MDQK&crc32=943997913
// http://gipsy/projets/ploopidev/wsdoc/get_file?ploopi_login=admin&ploopi_password=Baghy.c0m&module_id=6&file_md5id=749f97f451fd11c059d5eeb05a2e8c6e
// http://gipsy/projets/ploopidev/wsdoc/delete_file?ploopi_login=admin&ploopi_password=Baghy.c0m&module_id=6&file_md5id=749f97f451fd11c059d5eeb05a2e8c6e

/**
 * Non gÃ©rÃ© :
 * - Abonnements
 * - Partages
 * - Validateurs
 * - Brouillons
 * - Versions
 */

class docapi {

    /**
     * Liste des codes d'erreur
     */

    const _ERROR_ACTION_NOT_FOUND = 1;
    const _ERROR_PARAMETER_NOT_FOUND = 2;
    const _ERROR_FILE_NOT_FOUND_DB = 10;
    const _ERROR_FILE_NOT_FOUND_FS = 11;
    const _ERROR_FILE_CHECKSUM = 12;
    const _ERROR_FILE_NOT_WRITABLE = 13;
    const _ERROR_FOLDER_NOT_FOUND = 20;
    const _ERROR_FOLDER_NOT_WRITABLE = 21;
    const _ERROR_FOLDER_NOT_EMPTY = 22;
    const _ERROR_USER_NOT_ALLOWED = 98;
    const _ERROR_USER_NOT_CONNECTED = 99;

    /**
     * Liste des messages d'erreur
     */

    private static $_arrErrMsg = array(
        self::_ERROR_ACTION_NOT_FOUND => "L'action demandÃ©e n'existe pas",
        self::_ERROR_PARAMETER_NOT_FOUND => "Il manque au moins un paramÃ¨tre",
        self::_ERROR_FILE_NOT_FOUND_DB => "Fichier non trouvÃ© dans la base de donnÃ©es",
        self::_ERROR_FILE_NOT_FOUND_FS => "Fichier non trouvÃ© sur le systÃ¨me de fichiers",
        self::_ERROR_FILE_CHECKSUM => "Erreur dans la vÃ©rification de la somme de contrÃ´le lors du transfert des donnÃ©es",
        self::_ERROR_FILE_NOT_WRITABLE => "Impossible d'Ã©crire ou modifier ce fichier",
        self::_ERROR_FOLDER_NOT_FOUND => "Dossier non trouvÃ©",
        self::_ERROR_FOLDER_NOT_WRITABLE => "Impossible d'Ã©crire ou modifier ce dossier",
        self::_ERROR_FOLDER_NOT_EMPTY => "Dossier non vide",
        self::_ERROR_USER_NOT_ALLOWED => "Utilisateur non autorisÃ©",
        self::_ERROR_USER_NOT_CONNECTED => "Aucun utilisateur connectÃ©",
    );

    /**
     * Structure de retour
     */

    private $_arrResult = array();

    /**
     * Constructeur privÃ© (singleton)
     */

    private function __construct() {

        $this->_arrResult = array(
            'method_called' => '',
            'error' => false,
            'error_code' => 0,
            'error_msg' => '',
            'data' => null
        );

    }

    /**
     * CrÃ©e/retourne le singleton
     */

    static public function getInstance()
    {
        static $objSingleton = null;

        if ($objSingleton === null) $objSingleton = new self();

        return $objSingleton;
    }

    /**
     * Convertit une action en nom de mÃ©thode
     */

    private function _getMethodNameFromOp($op) {
        $arrComp = explode('_', $op);
        return 'action'.implode('', array_map('ucfirst', $arrComp));
    }

    /**
     * ExÃ©cute une action du webservice
     */

    public function launch($arrRequest) {

        if (!$_SESSION['ploopi']['connected']) {
            $this->_setError(self::_ERROR_USER_NOT_CONNECTED);
            return $this;
        }

        // Lecture action
        $strAction = isset($arrRequest['op']) ? $arrRequest['op'] : '';
        unset($arrRequest['op']);

        if (method_exists($this, $strMethod = $this->_getMethodNameFromOp($strAction))) {
            $this->_arrResult['method_called'] = $strMethod;
            $this->$strMethod($arrRequest);
        }
        else $this->_setError(self::_ERROR_ACTION_NOT_FOUND);

        return $this;
    }

    /**
     * Met Ã  jour le code d'erreur Ã  retourner
     */

    private function _setError($intErrorCode) {
        $this->_arrResult['error'] = true;
        $this->_arrResult['error_code'] = $intErrorCode;
        $this->_arrResult['error_msg'] = self::$_arrErrMsg[$intErrorCode];

        return $this;
    }

    /**
     * Met Ã  jour les donnÃ©es Ã  retourner
     */

    private function _setData($arrData = array()) {
        $this->_arrResult['data'] = $arrData;

        return $this;
    }

    /**
     * Retourne le rÃ©sultat de l'action demandÃ©e
     */

    public function getResult() {
        return (object) $this->_arrResult;
    }

    /**
     * Imprime le rÃ©sultat dans la sortie standard au format JSON
     */

    public function printResult($strFormat = 'json') {
        // Nettoyage du buffer
        // ploopi\buffer::clean();

        switch($strFormat) {
            case 'internal':
                ploopi\output::print_r($this->getResult());
            break;

            case 'json':
            default:
                header('Content-disposition: inline; filename="wsdoc.json"');
                ploopi\str::print_json($this->getResult(), true, false);
            break;
        }
    }

    /**
     * VÃ©rification du module_id et du rÃ´le de l'utilisateur
     */

    private function _isAllowed($arrParams) {

        if (empty($arrParams['module_id']) || !is_numeric($arrParams['module_id'])) {
            $this->_setError(self::_ERROR_PARAMETER_NOT_FOUND);
            return false;
        }

        if (!ploopi\acl::isactionallowed(_DOC_ACTION_WEBSERVICE, -1, $arrParams['module_id'])) {
            $this->_setError(self::_ERROR_USER_NOT_ALLOWED);
            return false;
        }

        return true;
    }

    /**
     * Retourne la liste des instances du module DOC
     */

    public function actionListModules($arrParams) {

        $objQuery = new ploopi\query_select();
        $objQuery->add_select('m.*');
        $objQuery->add_from('ploopi_module m');
        $objQuery->add_innerjoin('ploopi_module_type mt ON mt.id = m.id_module_type');
        $objQuery->add_where('mt.label = %s', 'doc');
        $objRs = $objQuery->execute();
        $arrData = array();
        while ($row = $objRs->fetchrow()) {
            // VÃ©rification des autorisations d'accÃ¨s au webservice
            if (ploopi\acl::isactionallowed(_DOC_ACTION_WEBSERVICE, -1, $row['id'])) $arrData[] = $row;
        }

        $this->_setData($arrData);

        return $this;
    }

    /**
     * Retourne le contenu d'un dossier
     * ParamÃ¨tres :
     * - module_id (voir actionListModules)
     * - folder_id (optionnel, racine par dÃ©faut)
     */
    public function actionListContent($arrParams) {

        $db = ploopi\db::get();

        if (!$this->_isAllowed($arrParams)) return $this;

        // VÃ©rification des paramÃ¨tres
        $intModuleId = $arrParams['module_id'];
        $intFolderId = isset($arrParams['folder_id']) ? intval($arrParams['folder_id']) : 0;

        // Chargement workflow
        doc_getvalidation($intModuleId);
        $wf_validator = in_array($intFolderId, $_SESSION['doc'][$intModuleId]['validation']['folders']);

        // Chargement partages
        doc_getshare($intModuleId);

        // Construction de la requÃªte SQL des DOSSIERS
        $arrWhere = array();

        // Module
        $arrWhere['module'] = "f.id_module = {$intModuleId}";
        // Dossier
        $arrWhere['folder'] = "f.id_folder = {$intFolderId}";

        // Utilisateur "standard"
        if (!$wf_validator && !ploopi\acl::isadmin() && !ploopi\acl::isactionallowed(_DOC_ACTION_ADMIN, -1, $intModuleId))
        {
            // PubliÃ© (ou propriÃ©taire)
            $arrWhere['published'] = "(f.published = 1 OR f.id_user = {$_SESSION['ploopi']['userid']})";

            // PrioriÃ©taire
            $arrWhere['visibility']['user'] = "f.id_user = {$_SESSION['ploopi']['userid']}";
            // PartagÃ©
            if (!empty($_SESSION['doc'][$intModuleId]['share']['folders'])) $arrWhere['visibility']['shared'] = "(f.foldertype = 'shared' AND f.id IN (".implode(',', $_SESSION['doc'][$intModuleId]['share']['folders'])."))";
            // Public
            $arrWhere['visibility']['public'] = "(f.foldertype = 'public' AND f.id_workspace IN (".ploopi\system::viewworkspaces($intModuleId)."))";

            // SynthÃ¨se visibilitÃ©
            $arrWhere['visibility'] = '('.implode(' OR ', $arrWhere['visibility']).')';
        }

        $strWhere = implode(' AND ', $arrWhere);

        $sql = "
            SELECT      f.*,
                        u.id as user_id,
                        u.login,
                        u.lastname,
                        u.firstname,
                        w.id as workspace_id,
                        w.label

            FROM        ploopi_mod_doc_folder f

            LEFT JOIN   ploopi_user u
            ON          f.id_user = u.id

            LEFT JOIN   ploopi_workspace w
            ON          f.id_workspace = w.id

            LEFT JOIN   ploopi_mod_doc_folder f_val
            ON          f_val.id = f.waiting_validation

            WHERE  {$strWhere}
        ";

        $rs_folders = $db->query($sql);

        // Construction de la requÃªte SQL des FICHIERS
        $arrWhere = array();

        // Module
        $arrWhere['module'] = "f.id_module = {$intModuleId}";

        // Dossier : /!\ l'admin system voit tous les fichiers dans 'racine'
        $arrWhere['folder'] = ($intFolderId || ploopi\acl::isadmin() || ploopi\acl::isactionallowed(_DOC_ACTION_ADMIN, -1, $intModuleId)) ? "f.id_folder = {$intFolderId}" : "f.id_folder = {$intFolderId} AND f.id_user = {$_SESSION['ploopi']['userid']}";

        $strWhere = implode(' AND ', $arrWhere);

        $sql = "
            SELECT      f.*,
                        u.id as user_id,
                        u.login,
                        u.lastname,
                        u.firstname,
                        w.id as workspace_id,
                        w.label,
                        e.filetype

            FROM        ploopi_mod_doc_file f

            LEFT JOIN   ploopi_user u
            ON          f.id_user = u.id

            LEFT JOIN   ploopi_workspace w
            ON          f.id_workspace = w.id

            LEFT JOIN   ploopi_mimetype e
            ON          e.ext = f.extension

            WHERE   {$strWhere}
        ";

        $rs_files = $db->query($sql);

        $this->_setData(array(
            'folders' => $db->getarray($rs_folders),
            'files' => $db->getarray($rs_files)
        ));

        return $this;
    }

    /**
     * DÃ©pose un fichier
     * ParamÃ¨tres :
     * - module_id (voir actionListModules)
     * - folder_id
     * - file_name
     * - file_description
     * - file_readonly
     * - base64
     * - crc32
     */

    public function actionPutFile($arrParams) {

        if (!$this->_isAllowed($arrParams)) return $this;

        if (empty($arrParams['file_name'])) {
            $this->_setError(self::_ERROR_PARAMETER_NOT_FOUND);
            return $this;
        }

        if (empty($arrParams['base64'])) {
            $this->_setError(self::_ERROR_PARAMETER_NOT_FOUND);
            return $this;
        }

        $objDocFolder = new docfolder();

        // Racine ?
        if (empty($arrParams['folder_id'])) $objDocFolder->init_description();
        else {
            // VÃ©rification de l'existence du dossier
            if (!$objDocFolder->open($arrParams['folder_id'])) {
                $this->_setError(self::_ERROR_FOLDER_NOT_FOUND);
                return $this;
            }
        }

        // Dossier accessible ?
        if (doc_folder_contentisreadonly($objDocFolder->fields, _DOC_ACTION_ADDFILE, $arrParams['module_id'])) {
            $this->_setError(self::_ERROR_FOLDER_NOT_WRITABLE);
            return $this;
        }


        // DÃ©codage du contenu
        $strContent = base64_decode($arrParams['base64']);

        // VÃ©rification du checksum
        if (isset($arrParams['crc32']) && $arrParams['crc32'] != crc32($strContent)) {
            $this->_setError(self::_ERROR_FILE_CHECKSUM);
            return $this;
        }

        // CrÃ©ation d'un fichier temporaire pour l'import
        file_put_contents($tmpfname = tempnam(sys_get_temp_dir(), 'wsdoc'), $strContent);

        if (!is_readable($tmpfname)) {
            $this->_setError(self::_ERROR_FILE_NOT_WRITABLE);
            return $this;
        }

        $objDocFile = new docfile();
        $objDocFile->fields['description'] = isset($arrParams['file_description']) ? $arrParams['file_description'] : '';
        $objDocFile->fields['readonly'] = empty($arrParams['file_readonly']) ? 0 : 1;
        $objDocFile->fields['id_folder'] = $arrParams['folder_id'];
        $objDocFile->fields['id_user_modify'] = $_SESSION['ploopi']['userid'];
        $objDocFile->tmpfile = $tmpfname;
        $objDocFile->fields['name'] = $arrParams['file_name'];
        $objDocFile->fields['size'] = filesize($tmpfname);
        $objDocFile->setuwm();
        $objDocFile->fields['id_module'] = $arrParams['module_id'];

        $error = $objDocFile->save();

        if ($error) {
            $this->_setError(self::_ERROR_FILE_NOT_WRITABLE);
            return $this;
        }

        $this->_setData(array(
            'file_id' => $objDocFile->fields['id'],
            'file_md5id' => $objDocFile->fields['md5id']
        ));

        return $this;
    }

    /**
     * Supprime un fichier
     * ParamÃ¨tres :
     * - module_id (voir actionListModules)
     * - file_md5id
     */

    public function actionDeleteFile($arrParams) {

        if (!$this->_isAllowed($arrParams)) return $this;

        if (empty($arrParams['file_md5id'])) {
            $this->_setError(self::_ERROR_PARAMETER_NOT_FOUND);
            return $this;
        }

        $objDocFile = new docfile();

        // VÃ©rification d'existence du fichier dans la DB
        if (!$objDocFile->openmd5($arrParams['file_md5id'])) {
            $this->_setError(self::_ERROR_FILE_NOT_FOUND_DB);
            return $this;
        }

        // VÃ©rification d'existence du fichier sur le FS
        if (!file_exists($objDocFile->getfilepath())) {
            $this->_setError(self::_ERROR_FILE_NOT_FOUND_FS);
            return $this;
        }

        if (doc_file_isreadonly($objDocFile->fields, _DOC_ACTION_DELETEFILE, $arrParams['module_id'])) {
            $this->_setError(self::_ERROR_FILE_NOT_WRITABLE);
        }

        $objDocFile->delete();

        return $this;
    }

    /**
     * Retourne un fichier
     * ParamÃ¨tres :
     * - module_id (voir actionListModules)
     * - file_md5id
     */

    public function actionGetFile($arrParams) {

        if (!$this->_isAllowed($arrParams)) return $this;

        if (empty($arrParams['file_md5id'])) {
            $this->_setError(self::_ERROR_PARAMETER_NOT_FOUND);
            return $this;
        }

        $objDocFile = new docfile();

        // VÃ©rification d'existence du fichier dans la DB
        if (!$objDocFile->openmd5($arrParams['file_md5id'])) {
            $this->_setError(self::_ERROR_FILE_NOT_FOUND_DB);
            return $this;
        }

        // VÃ©rification d'existence du fichier sur le FS
        if (!file_exists($objDocFile->getfilepath())) {
            $this->_setError(self::_ERROR_FILE_NOT_FOUND_FS);
            return $this;
        }

        $strContent = file_get_contents($objDocFile->getfilepath());

        $this->_setData(
            $objDocFile->fields + array('base64' => base64_encode($strContent), 'crc32' => crc32($strContent))
        );

        return $this;
    }

    /**
     * CrÃ©e un dossier
     * ParamÃ¨tres :
     * - module_id (voir actionListModules)
     * - folder_id : dossier parent
     * - folder_name : nom du dossier
     * - folder_description : description du dossier
     * - folder_type (optionnel) : type de dossier (public, private), public par dÃ©faut
     * - folder_readonly (optionnel) : 0/1
     *
     */

    public function actionCreateFolder($arrParams) {

        if (!$this->_isAllowed($arrParams)) return $this;

        if (empty($arrParams['folder_name'])) {
            $this->_setError(self::_ERROR_PARAMETER_NOT_FOUND);
            return $this;
        }

        $objParentFolder = new docfolder();

        // Racine ?
        if (empty($arrParams['folder_id'])) $objParentFolder->init_description();
        else {
            // VÃ©rification de l'existence du dossier
            if (!$objParentFolder->open($arrParams['folder_id'])) {
                $this->_setError(self::_ERROR_FOLDER_NOT_FOUND);
                return $this;
            }
        }

        // L'utilisateur peut Ã©crire dans ce dossier ?
        if (!doc_folder_contentisreadonly($objParentFolder->fields, _DOC_ACTION_ADDFOLDER, $arrParams['module_id'])) {

            $objDocFolder = new docfolder();
            $objDocFolder->fields['id_folder'] = $arrParams['folder_id'];
            $objDocFolder->fields['name'] = $arrParams['folder_name'];
            $objDocFolder->fields['description'] = isset($arrParams['folder_description']) ? $arrParams['folder_description'] : '';
            $objDocFolder->fields['readonly'] = empty($arrParams['folder_readonly']) ? 0 : 1;
            $objDocFolder->fields['foldertype'] = isset($arrParams['folder_type']) && in_array($arrParams['folder_type'], array('public', 'private')) ? $arrParams['folder_type'] : 'public';
            $objDocFolder->setuwm();
            $objDocFolder->fields['id_module'] = $arrParams['module_id'];

            $this->_setData(array(
                'folder_id' => $objDocFolder->save()
            ));

        }
        else {
            $this->_setError(self::_ERROR_FOLDER_NOT_WRITABLE);
            return $this;
        }

        return $this;
    }

    /**
     * Supprime un dossier
     * ParamÃ¨tres :
     * - module_id (voir actionListModules)
     * - folder_id : dossier Ã  supprimer
     */

    public function actionDeleteFolder($arrParams) {

        if (!$this->_isAllowed($arrParams)) return $this;

        if (empty($arrParams['folder_id'])) {
            $this->_setError(self::_ERROR_PARAMETER_NOT_FOUND);
            return $this;
        }

        $objDocFolder = new docfolder();

        // VÃ©rification que dossier existe
        if (!$objDocFolder->open($_GET['folder_id'])) {
            $this->_setError(self::_ERROR_FOLDER_NOT_FOUND);
            return $this;
        }

        // VÃ©rification que dossier modifiable
        if (doc_folder_isreadonly($objDocFolder->fields, _DOC_ACTION_DELETEFOLDER, $arrParams['module_id'])) {
            $this->_setError(self::_ERROR_FOLDER_NOT_WRITABLE);
            return $this;
        }

        // VÃ©rification que dossier vide (ou droit spÃ©cial)
        if ($objDocFolder->fields['nbelements'] > 0 && !ploopi\acl::isadmin() && !ploopi\acl::isactionallowed(_DOC_ACTION_ADMIN, -1, $arrParams['module_id'])) {
            $this->_setError(self::_ERROR_FOLDER_NOT_EMPTY);
            return $this;
        }

        $objDocFolder->delete();

        return $this;
    }
}

?>
