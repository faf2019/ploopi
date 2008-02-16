
<table cellpadding="2" cellspacing="1" width="100%" bgcolor="<? echo $skin->values['bgline2']; ?>">
<tr>
    <td align="left" valign="bottom" width="1%" nowrap style="font-size:1.8em; font-weight:bold;"><? echo $forms->fields['label']; ?></td>
    <td align="right" valign="bottom" style="font-size:1em; font-weight:bold;"><?
    if ($pubdate_start['date'] != '') echo "Publié depuis le {$pubdate_start['date']}";
    if ($pubdate_end['date'] != '') echo "<br>Clôture le {$pubdate_end['date']}";
    ?>
    </td>
</tr>

<?
if ($forms->fields['description'] != '')
{
    ?>
    <tr>
        <td colspan="2"><? echo nl2br($forms->fields['description']); ?></td>
    </tr>
    <?
}
?>
</table>


<table cellpadding="0" cellspacing="0" width="100%">
<tr bgcolor="<? echo $skin->values['colsec']; ?>"><td colspan="2"></td></tr>
<?
while ($fields = $db->fetchrow($rs_fields))
{
    $color = (!isset($color) || $color == $skin->values['bgline2']) ? $skin->values['bgline1'] : $skin->values['bgline2'];
    ?>
    <tr <? if ($fields['description'] != '') echo ploopi_showpopup($fields['description']); ?>>
        <?
        if ($fields['separator'])
        {
            ?>
            <td colspan="2" style="padding:4px;padding-top:<? echo $fields['interline']; ?>px;font-size:<? echo $fields['separator_fontsize']; ?>px; font-weight:bold;"><? echo $fields['name']; ?></td>
            <?
        }
        else
        {
        ?>
            <td width="25%" valign="top" align="right" style="padding:4px;padding-top:<? echo $fields['interline']; ?>px;font-size:1em; font-weight:bold;"><? echo $fields['name']; ?><? if ($fields['option_needed']) echo " *"; ?>&nbsp;</td>
            <td width="75%" style="padding:4px;padding-top:<? echo $fields['interline']; ?>px;">
            <?
            include './modules/forms/public_forms_model_field.php';
            ?>
            </td>
        <?
        }
        ?>
    </tr>
<?
}
?>
</table>