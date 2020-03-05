<?php
/**
 * Sauve un enregistrement du formulaire (front et back)
 */

ploopi\module::init('forms');

include_once './modules/forms/classes/formsForm.php';
include_once './modules/forms/classes/formsRecord.php';
include_once './modules/forms/classes/formsArithmeticParser.php';

$objForm = new formsForm();
if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']) && $objForm->open($_GET['forms_id']))
{

    /**
     * En frontoffice on vérifie qu'il s'agit d'un formulaire dédié (et donc public)
     */
    if ($_SESSION['ploopi']['mode'] == 'frontoffice' && $objForm->fields['typeform'] != 'cms') ploopi\output::redirect();


    /**
     * On instancie l'enregistrement
     * @var formsRecord
     */
    $objRecord = new formsRecord($objForm);

    /**
     * On prépare l'email
     * @var array
     */

    $arrEmailContent = array();

    if ($_SESSION['ploopi']['mode'] == 'backoffice')
    {
        /**
         * Modification d'un enregistrement existant
         */
        if (!empty($_GET['forms_record_id']))
        {
            $objRecord->open($_GET['forms_record_id']);
            $arrEmailContent['Formulaire']['Opération'] = 'Modification d\'Enregistrement';
        }
        else // Nouvel enregistrement
        {
            $arrEmailContent['Formulaire']['Opération'] = 'Nouvel Enregistrement';
        }
    }


    /**
     * Permet d'enregistrer la demande de stockage d'un champ de type fichier (il faut attendre de récupérer l'id de l'enregistrement avant de stocker le fichier)
     */
    $booFileToMove = false;

    /**
     * Permet de stocker le contenu des variables numériques (pour les champs calculés)
     */
    $arrVariables = array();
    $booCalculation = false;
    $booAggregate = false;

    /**
     * Permet de stocker les groupes conditionnels utilisés
     */
    $arrGroups = array();

    /**
     * Les champs du formulaire
     */
    $arrFields = $objForm->getFields();

    /**
     * Traitement des champs et mise à jour de l'enregistrement
     */

    foreach($arrFields as $objField)
    {
        $strValue = '';
        $booFieldOk = false;
        $booError = false;

        switch($objField->fields['type'])
        {
            case 'file':
                if (!empty($_FILES['field_'.$objField->fields['id']]['name']))
                {
                    $booFieldOk = true;
                    if ($_FILES['field_'.$objField->fields['id']]['size'] <= _PLOOPI_MAXFILESIZE)
                    {
                        $strValue = $_FILES['field_'.$objField->fields['id']]['name'];
                        $booFileToMove = true;
                    }
                }
            break;

            case 'autoincrement':
                if ($objRecord->isnew()) // not in form => need to be calculated
                {
                    $booFieldOk = true;
                    $objQuery = new ploopi\query_select();
                    $objQuery->add_select("max(`{$objField->fields['fieldname']}`) as maxinc");
                    $objQuery->add_from($objForm->getDataTableName());
                    $strValue = current($objQuery->execute()->getarray(true)) + 1;
                }
                else $strValue = $objRecord->fields[$objField->fields['fieldname']];
            break;

            case 'calculation':
                // Non traité ici
                $booFieldOk = true;
                $booCalculation = true;

                // On va détecter si le calcul fait appel à un agrégat (dans ce cas il faut recalculer toutes les données du formulaire)
                $objParser = new formsArithmeticParser($objField->fields['formula']);

                // Extraction des variables de l'expression
                $arrVars = $objParser->getVars();

                // Pour chaque variable attendue dans l'expression
                foreach($arrVars as $strVar)
                {
                    // Analyse de la variable
                    if (preg_match('/C([0-9]+)_?([A-Z]{0,3})/', $strVar, $arrMatches) > 0)
                    {
                        if (!empty($arrMatches[2])) $booAggregate = true;
                    }
                }

            break;

            default:
                if (isset($_POST['field_'.$objField->fields['id']]))
                {
                    $booFieldOk = true;

                    switch($objField->fields['format'])
                    {
                        case 'date':
                            $strValue = substr(ploopi\date::local2timestamp($_POST['field_'.$objField->fields['id']]), 0, 8);
                        break;

                        default:
                            if (is_array($_POST['field_'.$objField->fields['id']]))
                            {
                                foreach($_POST['field_'.$objField->fields['id']] as $val)
                                {
                                    if ($strValue != '') $strValue .= '||';
                                    $strValue .= $val;
                                }
                            }
                            else $strValue = $_POST['field_'.$objField->fields['id']];
                        break;
                    }
                }
            break;
        }

        switch($objField->fields['type'])
        {
            // Cas particulier, on n'écrase pas une valeur existante par une valeur vide
            case 'file':
                if (!empty($strValue)) $objRecord->fields[$objField->fields['fieldname']] = $strValue;
            break;

            // Cas général, on enregistre la nouvelle valeur
            default:
                $objRecord->fields[$objField->fields['fieldname']] = $strValue;
            break;
        }


        /*

        if ($booFieldOk)
        {
            //$objRecord->fields[$objField->fields['fieldname']] = (!(($objField->fields['type'] == 'autoincrement' || $objField->fields['type'] == 'file') && $strValue == '')) ? $strValue : '';

            $arrEmailContent['Contenu']["({$objField->fields['id']}) {$objField->fields['name']}"] = $objRecord->fields[$objField->fields['fieldname']];
        }
        **/
    }


    /**
     * Contrôle des champs autorisés selon les groupes conditionnels
     */

    foreach($arrFields as $objField)
    {
        if (!empty($objField->fields['id_group']))
        {
            // Groupe non chargé
            if (!isset($arrGroups[$objField->fields['id_group']]))
            {
                // On va ouvrir le groupe
                $objGroup = new formsGroup();
                if ($objGroup->open($objField->fields['id_group']))
                {
                    $arrGroups[$objField->fields['id_group']] = $objGroup;
                }
            }
            else $objGroup = $arrGroups[$objField->fields['id_group']];

            // Groupe ok
            if (isset($arrGroups[$objField->fields['id_group']]))
            {
                $arrConditions = $objGroup->getConditions();
                // Variables utilisées dans la condition
                $arrCondVars = array();

                // On va "calculer" chaque condition
                foreach($arrConditions as $key => $row)
                {
                    if (empty($row['field']) && empty($row['op'])) $arrCondVars["C{$key}"] = true;
                    else
                    {
                        $booRes = false;

                        // Le champ
                        $objFieldVar = $arrFields[$row['field']];
                        // La valeur saisie
                        $strValue = strtoupper(ploopi\str::convertaccents($objRecord->fields[$arrFields[$row['field']]->fields['fieldname']]));
                        $row['value'] = strtoupper(ploopi\str::convertaccents($row['value']));

                        $arrValues = array();

                        switch($objFieldVar->fields['type'])
                        {
                            case 'checkbox':
                                $arrValues = explode('||', $strValue);
                            break;

                            default:
                                $arrValues[] = $strValue;
                            break;
                        }

                        foreach($arrValues as $strValue)
                        {
                            switch($row['op'])
                            {
                                case 'begin':
                                    $booRes = $booRes || strpos($strValue, $row['value']) === 0;
                                break;

                                case 'like':
                                    $booRes = $booRes || strpos($strValue, $row['value']) !== false;
                                break;

                                case 'in':
                                    $booRes = $booRes || in_array($strValue, explode(',', $row['value']));
                                break;

                                case 'between':
                                    $values = explode(';', $row['value']);
                                    $booRes = $booRes || ($strValue >= $values[0] && (!isset($values[1]) || $strValue <= $values[1]));
                                break;

                                default:
                                    switch($row['op'])
                                    {
                                        case '=':
                                        case 'eq':
                                            $booRes = $booRes || $strValue == $row['value'];
                                        break;

                                        case '>':
                                        case 'gt':
                                            $booRes = $booRes || $strValue > $row['value'];
                                        break;

                                        case '>=':
                                        case 'ge':
                                            $booRes = $booRes || $strValue >= $row['value'];
                                        break;

                                        case '<':
                                        case 'lt':
                                            $booRes = $booRes || $strValue < $row['value'];
                                        break;

                                        case '<=':
                                        case 'le':
                                            $booRes = $booRes || $strValue <= $row['value'];
                                        break;

                                        case '<>':
                                        case 'ne':
                                            $booRes = $booRes || $strValue != $row['value'];
                                        break;
                                    }


                                break;
                            }
                        }

                        $arrCondVars["C{$key}"] = $booRes;
                    }
                }

                // Condition valide par défaut (si erreur dans l'expression)
                $booRes = true;

                // Calcul de l'expression booléenne globale du groupe
                try {
                    $objParser = new formsBooleanParser($objGroup->fields['formula'], $arrCondVars);
                    $booRes = $objParser->getVal();
                }
                catch (Exception $e) { }

                // Condition non valide, on ne garde pas le champ
                if (!$booRes) $objRecord->fields[$objField->fields['fieldname']] = $arrVariables['C'.$objField->fields['position']] = '';
            }
        }

        // Mise à jour des variables pour les calculs
        $arrVariables['C'.$objField->fields['position']] = $objRecord->fields[$objField->fields['fieldname']];
        if (!is_numeric($arrVariables['C'.$objField->fields['position']])) $arrVariables['C'.$objField->fields['position']] = 0;

    }


    /**
     * Il y avait au moins un champ de type "calculation"
     */

    if ($booCalculation)
    {
        if ($booAggregate)
        {
            // Pré-enregistrement (utile pour le calcul des agrégats)
            $objRecord->save();

            // Recalcul complet des données du formulaire
            $objForm->calculate();

            // Actualisation du l'enregistrement avec les données calculées
            $objRecord->open($objRecord->fields['#id']);
        }
        else // Calcul standard, juste l'enregistrement à mettre à jour
        {
            foreach($objForm->getFields() as $objField)
            {
                if ($objField->fields['type'] == 'calculation')
                {
                    try {
                        // Interprétation du calcul
                        $objParser = new formsArithmeticParser($objField->fields['formula'], $arrVariables);
                        $arrVariables['C'.$objField->fields['position']] = $objRecord->fields[$objField->fields['fieldname']] = $objParser->getVal();
                    }
                    catch(Exception $e) { }
                }
            }
        }
    }


    $objRecord->save();

    $strVarName = formsForm::getVarName($objForm->fields['id']).'_save';

    // Sauvegarde en session
    ploopi\session::setvar($strVarName, null);
    setcookie($strVarName, '', time() - 3600);

    /**
     * Il y avait au moins un champ de type "file"
     */
    if ($booFileToMove)
    {
        foreach($objForm->getFields() as $objField)
        {
            if ($objField->fields['type'] == 'file')
            {
                if (!empty($_FILES['field_'.$objField->fields['id']]['name']) && $_FILES['field_'.$objField->fields['id']]['size'] <= _PLOOPI_MAXFILESIZE)
                {
                    $strFile = $_FILES['field_'.$objField->fields['id']]['name'];
                    $strPath = formsField::getFilePath($objForm->fields['id'], $objRecord->fields['#id']);

                    ploopi\fs::makedir($strPath);
                    if (file_exists($strPath) && is_writable($strPath))
                    {
                        move_uploaded_file($_FILES['field_'.$objField->fields['id']]['tmp_name'], $strPath.$strFile);
                        {
                            chmod($strPath.$strFile, 0660);
                        }
                    }
                }
            }
        }
    }


    /**
     * Construction du mail
     */

    foreach($arrFields as $objField)
    {
        if (!$objField->fields['option_adminonly'] || ploopi\acl::isadmin())
        {
            $arrEmailContent['Contenu'][$objField->fields['name']] = $objRecord->fields[$objField->fields['fieldname']];
        }
    }

    /**
     * Contrôle des champs autorisés selon les groupes conditionnels
     */

    $arrEmailContent['Formulaire']['Titre'] = $objForm->fields['label'];
    $arrEmailContent['Formulaire']['Date'] = $objRecord->fields['date_validation'];
    $arrEmailContent['Formulaire']['Adresse IP'] = $objRecord->fields['ip'];
    $arrEmailContent['Formulaire']['Opération'] = 'Nouvel enregistrement';


    // On récupère les utilisateurs/groupes pour lesquels il faut envoyer un mail
    // Le dernier paramètre est très important depuis un appel WCE (l'id module étant déterminé par le formulaire)
    $arrShares = ploopi\share::get(-1, _FORMS_OBJECT_FORM, $objForm->fields['id'], $objForm->fields['id_module']);

    $_SESSION['ploopi']['tickets']['users_selected'] = array();
    foreach($arrShares as $row)
    {
        switch($row['type_share'])
        {
            case 'user':
                $_SESSION['ploopi']['tickets']['users_selected'][$row['id_share']] = $row['id_share'];
            break;

            case 'group':
                $objGroup = new ploopi\group();
                if ($objGroup->open($row['id_share'])) foreach(array_keys($objGroup->getusers()) as $id) $_SESSION['ploopi']['tickets']['users_selected'][$id] = $id;
            break;
        }
    }


    ploopi\ticket::send($arrEmailContent['Formulaire']['Titre'], '<table class="ploopi_array">'.ploopi\mail::form2html($arrEmailContent).'</table>', 0, 0, _FORMS_OBJECT_FORM, $objForm->fields['id'].','.$objRecord->fields['#id'], $arrEmailContent['Formulaire']['Titre']);


    /*
    if ($objForm->fields['email'] != '')
    {
        $arrFrom = $arrTo = array();

        $arrSearch = array(' ',',','|');
        $arrReplace = array('',';',';');

        // From
        $strFrom =  str_replace($arrSearch, $arrReplace, $objForm->fields['email_from']);
        $arrFrom[] = array('name' => $strFrom, 'address' => $strFrom);

        // To
        $strDest =  str_replace($arrSearch, $arrReplace, $objForm->fields['email']);
        $arrDest = explode(';',$strDest);
        foreach($arrDest as $strDest) $arrTo[] = array('name' => $strDest, 'address' => $strDest);

        // Envoi du formulaire par mail

        ploopi\mail::send_form($arrFrom, $arrTo, $arrEmailContent['Formulaire']['Titre'], $arrEmailContent);
    }
    */

    if ($_SESSION['ploopi']['mode'] == 'backoffice') ploopi\output::redirect("admin.php?op=forms_viewreplies&forms_id={$objForm->fields['id']}");
    else
    {
        // Redirect frontoffice
        $arrUrlParams = array();
        if (!empty($_REQUEST['headingid'])) $arrUrlParams[] = "headingid={$_REQUEST['headingid']}";
        if (!empty($_REQUEST['articleid'])) $arrUrlParams[] = "articleid={$_REQUEST['articleid']}";
        if (!empty($_REQUEST['webedit_mode'])) $arrUrlParams[] = "webedit_mode={$_REQUEST['webedit_mode']}";
        $arrUrlParams[] = "op=end";
        $arrUrlParams[] = "forms_id={$objForm->fields['id']}";

        ploopi\output::redirect('index.php?'.implode('&',$arrUrlParams));
    }

}
else ploopi\output::redirect();
