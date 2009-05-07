/**
 * NAVIGATION CALENDRIER
 */

function planning_nextmonth()
{
    if ($('planning_month').value < 12) $('planning_month').selectedIndex++;
    else {$('planning_month').selectedIndex = 0; $('planning_year').selectedIndex++;}

    $('planning_form_view').onsubmit();
}

function planning_prevmonth()
{
    if ($('planning_month').value > 1) $('planning_month').selectedIndex--;
    else {$('planning_month').selectedIndex = 11; $('planning_year').selectedIndex--;}

    $('planning_form_view').onsubmit();
}

function planning_nextweek()
{
    if ($('planning_week').selectedIndex < $('planning_week').length - 1) $('planning_week').selectedIndex++;
    else {$('planning_week').selectedIndex = 0; $('planning_year').selectedIndex++;}

    $('planning_form_view').onsubmit();
}

function planning_prevweek()
{
    if ($('planning_week').selectedIndex > 0) $('planning_week').selectedIndex--;
    else {$('planning_week_previousyear').value = '1'; $('planning_year').selectedIndex--;}

    $('planning_form_view').onsubmit();
}

function planning_nextday()
{
    if ($('planning_day').selectedIndex < $('planning_day').length - 1)
    {
        $('planning_day').selectedIndex++;
        $('planning_form_view').onsubmit();
    }
    else
    {
        $('planning_day').selectedIndex = 0;
        planning_nextmonth();
    }
}

function planning_prevday()
{
    if ($('planning_day').selectedIndex > 0)
    {
        $('planning_day').selectedIndex--;
        $('planning_form_view').onsubmit();
    }
    else
    {
        $('planning_week_previousmonth').value = '1'
        planning_prevmonth();
    }
}

/**
 * AJOUT D'UN EVENEMENT
 */
function planning_event_validate(form)
{
    if (ploopi_validatefield('Objet',form.planning_event_object, 'string'))
    if (ploopi_validatefield('Date de début',form._planning_event_timestp_begin_d, 'date'))
    if (!form._planning_event_timestp_end_d || ploopi_validatefield('Date de fin',form._planning_event_timestp_end_d, 'date'))
    {
        ploopi_hidepopup('popup_planning_event');
        return true;
    }

    return false;
}