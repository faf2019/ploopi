<?php
/*
    Copyright (c) 2007-2026 Ovensia
    Contributors hold Copyright (c) to their code submissions.

    This file is part of Ploopi.

    Ploopi is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Ploopi is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Import d'utilisateurs depuis un fichier CSV dans un groupe, gabarits de génération 4 :
 * envoi du fichier, aperçu avant import, compte rendu.
 *
 * Remplace admin_index_users_import_form.php, admin_index_users_preview.php et le
 * compte rendu (op=end) sous ploopi4. Le fichier est lu par l'opération
 * system_user_import (op.php), l'import fait par op=import (admin_index_users.php).
 *
 * Variable fournie : $op.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

use ploopi\system\ui;

switch($op)
{
    /**
     * Compte rendu
     */
    case 'end':
        if (empty($_SESSION['system']['user_import_errors'])) echo ui::callout('success', ui::e(_SYSTEM_IMP_DONE));
        else
        {
            $strList = '';
            foreach($_SESSION['system']['user_import_errors'] as $strMsg) $strList .= '<li>'.ui::e($strMsg).'</li>';
            echo ui::callout('warning', '<strong>'.ui::e(_SYSTEM_IMP_DONE_ERRORS).'</strong><ul class="p4-msglist">'.$strList.'</ul>');
        }

        echo '<div class="p4-row p4-mt-16 p4-ui">'.ui::button(array('label' => _SYSTEM_USERS_BACKTOLIST, 'icon' => 'users', 'href' => ploopi\crypt::urlencode('admin.php?usrTabItem=tabUserList'))).ui::button(array('label' => _SYSTEM_IMP_AGAIN, 'icon' => 'upload', 'class' => 'ghost', 'href' => ploopi\crypt::urlencode('admin.php?usrTabItem=tabUserImport'))).'</div>';
    break;

    /**
     * Aperçu : contrôle des colonnes et des lignes
     */
    case 'preview':
        $objUser = new ploopi\user();
        $objUser->init_description();

        $arrImport = empty($_SESSION['system']['user_import']) ? array() : $_SESSION['system']['user_import'];
        $arrErrors = array();
        $booCritical = false;
        $arrNeeded = array('login' => false, 'password' => false, 'lastname' => false, 'firstname' => false);

        if (empty($arrImport[0]))
        {
            $arrErrors[] = _SYSTEM_IMP_INVALIDFILE;
            $booCritical = true;
        }
        else
        {
            foreach($arrImport[0] as $strField)
            {
                if (!array_key_exists($strField, $objUser->fields) || $strField == 'id') $arrErrors[] = str_replace('<FIELD>', $strField, _SYSTEM_IMP_INVALIDFIELD);
                if (isset($arrNeeded[$strField])) $arrNeeded[$strField] = true;
            }

            foreach($arrNeeded as $strField => $booFound)
            {
                if (!$booFound)
                {
                    $arrErrors[] = str_replace('<FIELD>', $strField, _SYSTEM_IMP_MISSINGFIELD);
                    $booCritical = true;
                }
            }

            for ($intI = 1; $intI < count($arrImport); $intI++)
            {
                if (count($arrImport[$intI]) != count($arrImport[0])) $arrErrors[] = str_replace('<NUM>', $intI, _SYSTEM_IMP_BADROW);
            }
        }

        if (empty($arrErrors)) echo ui::callout('success', ui::e(_SYSTEM_IMP_VALID));
        else
        {
            $strList = '';
            foreach($arrErrors as $strError) $strList .= '<li>'.ui::e($strError).'</li>';
            echo ui::callout($booCritical ? 'danger' : 'warning', '<strong>'.ui::e($booCritical ? _SYSTEM_IMP_CRITICAL : _SYSTEM_IMP_WARNINGS).'</strong><ul class="p4-msglist">'.$strList.'</ul>');
        }

        if (!$booCritical)
        {
            $intRows = count($arrImport) - 1;
            echo ui::card_open(_SYSTEM_IMP_PREVIEW, 'list-details', array(
                array('label' => _SYSTEM_IMP_CANCEL, 'class' => 'ghost', 'href' => ploopi\crypt::urlencode('admin.php?usrTabItem=tabUserImport')),
                array('label' => str_replace('<NB>', $intRows, _SYSTEM_IMP_CONFIRM), 'icon' => 'check', 'class' => 'primary', 'href' => ploopi\crypt::urlencode('admin.php?usrTabItem=tabUserImport&op=import'), 'confirm' => str_replace('<NB>', $intRows, _SYSTEM_IMP_CONFIRM_MSG))
            ), array('class' => 'p4-card-flush p4-mt-16'));
            ?>
            <div class="p4-table-wrap">
                <table class="p4-table p4-table-compact">
                    <thead>
                        <tr>
                            <th class="p4-num">#</th>
                            <?php foreach($arrImport[0] as $strField) echo '<th>'.ui::e($strField).'</th>'; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        for ($intI = 1; $intI < count($arrImport); $intI++)
                        {
                            echo '<tr><td class="p4-num p4-muted">'.$intI.'</td>';
                            foreach(array_keys($arrImport[0]) as $intJ)
                            {
                                $strField = $arrImport[0][$intJ];
                                $strValue = isset($arrImport[$intI][$intJ]) ? $arrImport[$intI][$intJ] : '';
                                // Le mot de passe n'est pas affiché
                                echo '<td>'.($strField == 'password' && $strValue != '' ? '<span class="p4-muted">••••••</span>' : ui::e($strValue)).'</td>';
                            }
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php
            echo ui::card_close();
        }
        else echo '<div class="p4-row p4-mt-16 p4-ui">'.ui::button(array('label' => _SYSTEM_IMP_AGAIN, 'icon' => 'upload', 'href' => ploopi\crypt::urlencode('admin.php?usrTabItem=tabUserImport'))).'</div>';
    break;

    /**
     * Envoi du fichier
     */
    default:
        $arrFields = array(
            'lastname' => _SYSTEM_LABEL_LASTNAME,
            'firstname' => _SYSTEM_LABEL_FIRSTNAME,
            'login' => _SYSTEM_LABEL_LOGIN,
            'password' => _SYSTEM_USERF_PASSWORD,
            'email' => _SYSTEM_LABEL_EMAIL,
            'phone' => _SYSTEM_LABEL_PHONE,
            'mobile' => _SYSTEM_LABEL_MOBILE,
            'fax' => _SYSTEM_LABEL_FAX,
            'number' => _SYSTEM_LABEL_NUMBER,
            'civility' => _SYSTEM_LABEL_CIVILITY.' (M/Mme/Mlle)',
            'entity' => _SYSTEM_LABEL_ENTITY,
            'service' => _SYSTEM_LABEL_SERVICE,
            'service2' => _SYSTEM_LABEL_SERVICE2,
            'function' => _SYSTEM_LABEL_FUNCTION,
            'rank' => _SYSTEM_LABEL_RANK,
            'address' => _SYSTEM_LABEL_ADDRESS,
            'postalcode' => _SYSTEM_LABEL_POSTALCODE,
            'city' => _SYSTEM_LABEL_CITY,
            'country' => _SYSTEM_LABEL_COUNTRY,
            'building' => _SYSTEM_LABEL_BUILDING,
            'floor' => _SYSTEM_LABEL_FLOOR,
            'office' => _SYSTEM_LABEL_OFFICE,
            'date_expire' => _SYSTEM_LABEL_EXPIRATION_DATE.' (AAAAMMJJHHMMSS)',
            'ticketsbyemail' => _SYSTEM_LABEL_TICKETSBYEMAIL.' (1/0)',
            'servertimezone' => _SYSTEM_LABEL_SERVERTIMEZONE.' (1/0)',
            'timezone' => _SYSTEM_LABEL_TIMEZONE.' (Europe/Paris…)',
            'color' => _SYSTEM_LABEL_COLOR.' (#0F0F0F)',
            'password_force_update' => _SYSTEM_USERF_FORCEUPDATE.' (1/0)',
            'password_validity' => _SYSTEM_USERF_PASSWORD_VALIDITY,
            'disabled' => _SYSTEM_USERF_DISABLED.' (1/0)',
            'comments' => _SYSTEM_LABEL_COMMENTS
        );
        $arrRequired = array('lastname', 'firstname', 'login', 'password');
        ?>
        <form class="p4-form p4-ui" action="<?php echo ploopi\crypt::urlencode('admin.php?ploopi_op=system_user_import'); ?>" method="post" enctype="multipart/form-data">
            <?php
            echo ui::section_open(_SYSTEM_IMP_FILE, _SYSTEM_IMP_FILE_DESC, 'file-import');
            echo ui::field(_SYSTEM_IMP_SOURCE, '<input class="p4-input p4-file" type="file" name="system_user_file" id="system_user_file" accept=".csv,text/csv,text/plain" required="required" />', array('for' => 'system_user_file', 'width' => 'c12', 'required' => true));
            echo ui::field(_SYSTEM_IMP_SEPARATOR, ui::select('system_user_sep', array(',' => _SYSTEM_IMP_SEP_COMMA, ';' => _SYSTEM_IMP_SEP_SEMICOLON)), array('for' => 'system_user_sep'));
            echo ui::section_close();

            echo ui::section_open(_SYSTEM_IMP_COLUMNS, _SYSTEM_IMP_COLUMNS_DESC, 'list-details');
            ?>
            <div class="p4-c12 p4-table-wrap">
                <table class="p4-table p4-table-compact">
                    <thead><tr><th><?php echo ui::e(_SYSTEM_IMP_COLUMN); ?></th><th><?php echo ui::e(_SYSTEM_LABEL_DESCRIPTION); ?></th></tr></thead>
                    <tbody>
                        <?php
                        foreach($arrFields as $strField => $strLabel)
                        {
                            echo '<tr><td class="p4-mono">'.ui::e($strField).'</td><td>'.ui::e($strLabel).(in_array($strField, $arrRequired) ? ' <abbr class="p4-req" title="obligatoire">*</abbr>' : '').'</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php
            echo ui::section_close();

            echo ui::formactions(array(array('label' => _SYSTEM_IMP_SEND, 'icon' => 'upload', 'type' => 'submit', 'class' => 'primary')), ui::e(_SYSTEM_IMP_FORMAT));
            ?>
        </form>
        <?php
    break;
}

echo ui::script();
