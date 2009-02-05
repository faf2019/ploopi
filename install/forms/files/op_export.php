<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
 * Export des données d'un formulaire aux formats XLS et CSV
 *
 * @package forms
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 * 
 * @see ploopi_ob_clean
 * @link http://pear.php.net/package/Spreadsheet_Excel_Writer
 */

/**
 * On supprime tous les buffers autres que le buffer principal et on vide le buffer principal
 */

ploopi_ob_clean();

include_once './modules/forms/class_form.php';

$form = new form();
$form->open($_GET['forms_id']);

if (!empty($_GET['forms_export_format']))
{
    switch($_GET['forms_export_format'])
    {
        case 'XLS':
            require_once 'Spreadsheet/Excel/Writer.php';

            // Creating a workbook
            $workbook = new Spreadsheet_Excel_Writer();

            // sending HTTP headers
            $workbook->send("export.xls");

            $format_title =& $workbook->addFormat( array( 'Align' => 'center', 'Bold'  => 1, 'Color'  => 'black', 'Size'  => 10, 'FgColor' => 'silver'));
            $format =& $workbook->addFormat( array( 'TextWrap' => 1, 'Align' => 'left', 'Bold'  => 0, 'Color'  => 'black', 'Size'  => 10));

            $l=0;

            $id_rub1= '';

            $worksheet =& $workbook->addWorksheet("export");

            $c=0;
            foreach ($_SESSION['forms']['export_title'] as $key => $value)
            {
                $value = $value['label'];
                $display = false;
                switch($key)
                {
                    case 'datevalidation':
                        $display = ($form->fields['option_displaydate']);
                    break;

                    case 'user':
                        $display = ($form->fields['option_displayuser']);
                    break;

                    case 'group':
                        $display = ($form->fields['option_displaygroup']);
                    break;

                    default:
                        $display = (isset($_SESSION['forms']['export_fields'][$key]) && $_SESSION['forms']['export_fields'][$key]['option_exportview']);
                    break;
                }

                if ($display) $worksheet->write(0, $c++, $value, $format_title);
            }

            $l=1;

            foreach ($_SESSION['forms']['export'] as $reply_id => $detail)
            {
                $c=0;
                foreach ($detail as $key => $value)
                {
                    $display = false;
                    switch($key)
                    {
                        case 'datevalidation':
                            $display = ($form->fields['option_displaydate']);
                        break;

                        case 'user':
                            $display = ($form->fields['option_displayuser']);
                        break;

                        case 'group':
                            $display = ($form->fields['option_displaygroup']);
                        break;

                        default:
                            $display = (isset($_SESSION['forms']['export_fields'][$key]) && $_SESSION['forms']['export_fields'][$key]['option_exportview']);
                        break;
                    }

                    $value = str_replace('||',';',$value);
                    if ($display) $worksheet->write($l, $c++, $value, $format);
                }
                $l++;
            }

            $workbook->close();
        break;

        case 'CSV':
        default:

            function forms_convertchar($str)
            {
                $str = str_replace('(tab)',"\t",$str);
                $str = str_replace('(cr)',"\r",$str);
                $str = str_replace('(lf)',"\n",$str);

                return($str);
            }

            /*
            (tab) = \t
            (cr) = \r
            (lf) = \n
            */

            $extension = (empty($_SESSION['ploopi']['modules'][$id_module]['forms_export_csvextension'])) ? 'csv' : $_SESSION['ploopi']['modules'][$id_module]['forms_export_csvextension'];
            $fieldseparator = (empty($_SESSION['ploopi']['modules'][$id_module]['forms_export_fieldseparator'])) ? ';' : forms_convertchar($_SESSION['ploopi']['modules'][$id_module]['forms_export_fieldseparator']);
            $lineseparator = (empty($_SESSION['ploopi']['modules'][$id_module]['forms_export_lineseparator'])) ? '\r\n' : forms_convertchar($_SESSION['ploopi']['modules'][$id_module]['forms_export_lineseparator']);
            $textseparator = (empty($_SESSION['ploopi']['modules'][$id_module]['forms_export_textseparator'])) ? '"' : forms_convertchar($_SESSION['ploopi']['modules'][$id_module]['forms_export_textseparator']);

            header("Cache-control: private");
            header("Content-type: text/x-csv; charset=ISO-8859-15");
            header("Content-Disposition: attachment; filename=export.{$extension}");
            header("Pragma: public");

            $first_element = true;
            foreach ($_SESSION['forms']['export_title'] as $key => $value)
            {
                $value = $value['label'];
                $display = false;
                switch($key)
                {
                    case 'datevalidation':
                        $display = ($form->fields['option_displaydate']);
                    break;

                    case 'user':
                        $display = ($form->fields['option_displayuser']);
                    break;

                    case 'group':
                        $display = ($form->fields['option_displaygroup']);
                    break;

                    default:
                        $display = (isset($_SESSION['forms']['export_fields'][$key]) && $_SESSION['forms']['export_fields'][$key]['option_exportview']);
                    break;
                }

                if ($display)
                {
                    if (!$first_element) echo $fieldseparator;
                    else $first_element = false;
                    $value = str_replace($textseparator,"{$textseparator}{$textseparator}", $value);
                    echo "{$textseparator}{$value}{$textseparator}";
                }
            }

            foreach ($_SESSION['forms']['export'] as $reply_id => $detail)
            {
                echo $lineseparator;

                $first_element = true;
                foreach ($detail as $key => $value)
                {
                    $display = false;
                    switch($key)
                    {
                        case 'datevalidation':
                            $display = ($form->fields['option_displaydate']);
                        break;

                        case 'user':
                            $display = ($form->fields['option_displayuser']);
                        break;

                        case 'group':
                            $display = ($form->fields['option_displaygroup']);
                        break;

                        default:
                            $display = (isset($_SESSION['forms']['export_fields'][$key]) && $_SESSION['forms']['export_fields'][$key]['option_exportview']);
                        break;
                    }
                    if ($display)
                    {
                        if (!$first_element) echo $fieldseparator;
                        else $first_element = false;
                        $value = str_replace('<br>','<CR>', ploopi_nl2br(str_replace($textseparator,"{$textseparator}{$textseparator}", $value)));
                        echo "{$textseparator}{$value}{$textseparator}";
                    }
                }
            }

        break;

    }
}

        
ploopi_die();
?>
