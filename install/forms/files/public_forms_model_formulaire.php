<table cellpadding="0" cellspacing="0" width="<? echo $forms->fields['width']; ?>" style="border: solid 1px <? echo $skin->values['colsec']; ?>; margin:10px">
<tr>
    <td>

        <table cellpadding="2" cellspacing="1" width="100%" bgcolor="<? echo $skin->values['bgline2']; ?>">
        <tr>
            <td><h2><? echo $forms->fields['label']; ?></h2>
            <? 
            if ($pubdate_start['date'] != '' || $pubdate_end['date'] != '')
            {
                ?>
                <strong>Publié du <? echo $pubdate_start['date']; ?> au <? echo $pubdate_end['date']; ?></strong>
                <?
            }
            ?>
            </td>
        </tr>
        
        <?
        if ($forms->fields['description'] != '')
        {
            ?>
            <tr>
                <td><? echo nl2br($forms->fields['description']); ?></td>
            </tr>
            <?
        }
        ?>
        </table>


        <?
        while ($fields = $db->fetchrow())
        {
            $color = (!isset($color) || $color == $skin->values['bgline2']) ? $skin->values['bgline1'] : $skin->values['bgline2'];
            ?>
            <table cellpadding="0" cellspacing="0" bgcolor="<? echo $skin->values['colsec']; ?>"  width="100%"><tr><td height="1"></td></tr></table>

            <table cellpadding="2" cellspacing="1"  width="100%" bgcolor="<? echo $color; ?>">
            <tr>
                <td><h3><? echo $fields['position']; ?>. <? echo $fields['name']; ?><? if ($fields['option_needed']) echo " (*)"; ?></h3></td>
            </tr>
            <?
            if ($fields['description'] != '')
            {
                ?>
                <tr>
                    <td><? echo nl2br($fields['description']); ?></td>
                </tr>
                <?
            }
            if (isset($field_formats[$fields['format']]) && $fields['type'] == 'text')
            {
                ?>
                <tr>
                    <td>
                    <strong>Format de données : <? echo $field_formats[$fields['format']]; ?>
                    <?
                    switch ($fields['format'])
                    {
                        case 'date':
                            echo ' (jj/mm/aaaa)';
                        break;

                        case 'heure':
                            echo ' (hh:mm)';
                        break;
                    }
                    ?>
                    </strong>
                    </td>
                </tr>
                <?
            }
            ?>
            <tr>
                <td>
                <?
                $values = explode('||',$fields['values']);
                
                switch($fields['type'])
                {
                    
                    case 'textarea':
                    ?>
                        <textarea <? echo $style; ?> name="field_<? echo $fields['id']; ?>" class="textarea" rows="10"><? if (isset($replies[$fields['id']][0])) echo $replies[$fields['id']][0]; ?></textarea>
                    <?
                    break;
                    
                    case 'select':
                        ?>
                        <select <? echo $style; ?> name="field_<? echo $fields['id']; ?>" class="select">
                        <option></option>
                        <?
                        foreach($values as $value)
                        {
                            $selected = (isset($replies[$fields['id']]) && in_array($value, $replies[$fields['id']]))? 'selected' : '';
                            ?>
                            <option <? echo $selected; ?> value="<? echo $value; ?>"><? echo $value; ?></option>
                            <?
                        }
                        ?>
                        </select>
                        <?
                    break;

                    case 'checkbox':
                        ?>
                        <table cellpadding="2" cellspacing="1">
                        <?
                        foreach($values as $value)
                        {
                            $checked = (isset($replies[$fields['id']]) && in_array($value, $replies[$fields['id']]))? 'checked' : '';
                            ?>
                            <tr><td><input <? echo $checked; ?> type="checkbox" name="field_<? echo $fields['id']; ?>[]" value="<? echo $value; ?>"></td><td><? echo $value; ?></td></tr>
                            <?
                        }
                        ?>
                        </table>
                        <?
                    break;

                    case 'radio':
                        ?>
                        <table cellpadding="2" cellspacing="1">
                        <?
                        foreach($values as $value)
                        {
                            $checked = (isset($replies[$fields['id']]) && in_array($value, $replies[$fields['id']]))? 'checked' : '';
                            ?>
                            <tr><td><input <? echo $checked; ?> type="radio" name="field_<? echo $fields['id']; ?>" value="<? echo $value; ?>"></td><td><? echo $value; ?></td></tr>
                            <?
                        }
                        ?>
                        </table>
                        <?
                    break;

                    default:
                    case 'text':
                        $maxlength = ($fields['maxlength'] > 0 && $fields['maxlength'] != '') ? $fields['maxlength'] : '50';
                        ?>
                        <input type="text" name="field_<? echo $fields['id']; ?>" value="<? if (isset($replies[$fields['id']][0])) echo $replies[$fields['id']][0]; ?>" class="text" size="<? echo $maxlength; ?>" maxlength="<? echo $maxlength; ?>">
                        <?
                    break;
                }
                ?>
                </td>
            </tr>
            </table>
            <?
            
        }
    ?>

    </td>
</tr>
</table>
