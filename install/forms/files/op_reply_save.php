<?php
/**
 * Sauve un enregistrement du formulaire (front et back)
 */

ploopi_init_module('forms');

include_once './modules/forms/classes/formsForm.php';
include_once './modules/forms/classes/formsRecord.php';
include_once './modules/forms/classes/formsArithmeticParser.php';


$objForm = new formsForm();
if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']) && $objForm->open($_GET['forms_id']))
{

    /**
     * En frontoffice on vérifie qu'il s'agit d'un formulaire dédié (et donc public)
     */
    if ($_SESSION['ploopi']['mode'] == 'frontoffice' && $objForm->fields['typeform'] != 'cms') ploopi_redirect();


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

    /**
     * Traitement des champs et mise à jour de l'enregistrement
     */
    foreach($objForm->getFields() as $objField)
    {
        $strValue = '';
        $booFieldOk = false;
        $booError = false;

        // Valeur par défaut
        $arrVariables['C'.$objField->fields['position']] = 0;

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
                    $objQuery = new ploopi_query_select();
                    $objQuery->add_select("max(`{$objField->fields['fieldname']}`) as maxinc");
                    $objQuery->add_from($objForm->getDataTableName());
                    $strValue = current($objQuery->execute()->getarray(true)) + 1;
                }
            break;

            case 'calculation':
                // Non traité ici
                $booFieldOk = true;
                $booCalculation = true;
            break;

            default:
                if (isset($_POST['field_'.$objField->fields['id']]))
                {
                    $booFieldOk = true;

                    switch($objField->fields['format'])
                    {
                        case 'date':
                            $strValue = substr(ploopi_local2timestamp($_POST['field_'.$objField->fields['id']]), 0, 8);
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

        $arrVariables['C'.$objField->fields['position']] = $strValue;
        if (!is_numeric($arrVariables['C'.$objField->fields['position']])) $arrVariables['C'.$objField->fields['position']] = 0;

        if ($booFieldOk)
        {
            $objRecord->fields[$objField->fields['fieldname']] = (!(($objField->fields['type'] == 'autoincrement' || $objField->fields['type'] == 'file') && $strValue == '')) ? $strValue : '';

            $arrEmailContent['Contenu']["({$objField->fields['id']}) {$objField->fields['name']}"] = $objRecord->fields[$objField->fields['fieldname']];
        }
    }

    /**
     * Il y avait au moins un champ de type "calculation"
     */

    if ($booCalculation)
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

    $objRecord->save();

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

                    ploopi_makedir($strPath);
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


    $arrEmailContent['Formulaire']['Titre'] = $objForm->fields['label'];
    $arrEmailContent['Formulaire']['Date'] = $objRecord->fields['date_validation'];
    $arrEmailContent['Formulaire']['Adresse IP'] = $objRecord->fields['ip'];
    $arrEmailContent['Formulaire']['Opération'] = 'Nouvel enregistrement';

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

        /**
         * Envoi du formulaire par mail
         */

        ploopi_send_form($arrFrom, $arrTo, $arrEmailContent['Formulaire']['Titre'], $arrEmailContent);
    }

    if ($_SESSION['ploopi']['mode'] == 'backoffice') ploopi_redirect("admin.php?op=forms_viewreplies&forms_id={$objForm->fields['id']}");
    else
    {
        // Redirect frontoffice
        $arrUrlParams = array();
        if (!empty($_REQUEST['headingid'])) $arrUrlParams[] = "headingid={$_REQUEST['headingid']}";
        if (!empty($_REQUEST['articleid'])) $arrUrlParams[] = "articleid={$_REQUEST['articleid']}";
        if (!empty($_REQUEST['webedit_mode'])) $arrUrlParams[] = "webedit_mode={$_REQUEST['webedit_mode']}";
        $arrUrlParams[] = "op=end";
        $arrUrlParams[] = "forms_id={$objForm->fields['id']}";

        ploopi_redirect('index.php?'.implode('&',$arrUrlParams));
    }

}
else ploopi_redirect();
